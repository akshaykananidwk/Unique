<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use App\Core\Logger;
use App\Core\Settings;
use App\Core\WaEvents;

class OrderService
{
    /** Generate the next job number for a branch. MUST be called inside a transaction. */
    public static function generateJobNo(int $branchId): string
    {
        $branch = DB::get('SELECT code FROM `' . tbl('branches') . '` WHERE id = ?', [$branchId]);
        if (!$branch) {
            throw new \RuntimeException('Branch not found.');
        }
        $prefix = (string)Settings::get('job_prefix', 'JOB');
        $yymm = date('ym');

        $row = DB::get(
            'SELECT last_seq FROM `' . tbl('order_sequences') . '` WHERE branch_id = ? AND yymm = ? FOR UPDATE',
            [$branchId, $yymm]
        );
        if ($row === null) {
            // INSERT IGNORE handles the race where two firsts arrive together
            DB::run(
                'INSERT IGNORE INTO `' . tbl('order_sequences') . '` (branch_id, yymm, last_seq) VALUES (?,?,0)',
                [$branchId, $yymm]
            );
            DB::get(
                'SELECT last_seq FROM `' . tbl('order_sequences') . '` WHERE branch_id = ? AND yymm = ? FOR UPDATE',
                [$branchId, $yymm]
            );
        }
        DB::run(
            'UPDATE `' . tbl('order_sequences') . '` SET last_seq = last_seq + 1 WHERE branch_id = ? AND yymm = ?',
            [$branchId, $yymm]
        );
        $seq = (int)DB::val(
            'SELECT last_seq FROM `' . tbl('order_sequences') . '` WHERE branch_id = ? AND yymm = ?',
            [$branchId, $yymm]
        );
        return sprintf('%s-%s-%s-%04d', $prefix, $branch['code'], $yymm, $seq);
    }

    /** Next receipt number for a branch. MUST be called inside a transaction. */
    public static function generateReceiptNo(int $branchId): string
    {
        $branch = DB::get('SELECT code, invoice_prefix FROM `' . tbl('branches') . '` WHERE id = ?', [$branchId]);
        $yy = date('y');
        $row = DB::get(
            'SELECT last_seq FROM `' . tbl('receipt_sequences') . '` WHERE branch_id = ? AND yy = ? FOR UPDATE',
            [$branchId, $yy]
        );
        if ($row === null) {
            DB::run(
                'INSERT IGNORE INTO `' . tbl('receipt_sequences') . '` (branch_id, yy, last_seq) VALUES (?,?,0)',
                [$branchId, $yy]
            );
        }
        DB::run(
            'UPDATE `' . tbl('receipt_sequences') . '` SET last_seq = last_seq + 1 WHERE branch_id = ? AND yy = ?',
            [$branchId, $yy]
        );
        $seq = (int)DB::val(
            'SELECT last_seq FROM `' . tbl('receipt_sequences') . '` WHERE branch_id = ? AND yy = ?',
            [$branchId, $yy]
        );
        $prefix = $branch['invoice_prefix'] ?: ('RC' . $branch['code']);
        return sprintf('%s-%s-%05d', $prefix, $yy, $seq);
    }

    /**
     * Create a full order in one transaction.
     *
     * $payload:
     *  customer: [phone, name (the company), contact_name (the person), id? (an existing
     *             company to add this number to), whatsapp, address, gstin]
     *  branch_id, priority, delivery_type, delivery_address, customer_note, internal_note
     *  discount_type, discount_value, delivery_charge
     *  items: [ [item_id, qty, rate?, spec(array), due_date?, designer_id?, special_instructions?] ]
     *  advance: [amount, mode, reference]
     *  source: counter|public ; user_id (nullable for public)
     *
     * @return array{order_id:int,job_no:string,tracking_token:string,payment_id:?int}
     */
    public static function createOrder(array $payload): array
    {
        $result = DB::transaction(function () use ($payload) {
            $source = $payload['source'] ?? 'counter';
            $userId = $payload['user_id'] ?? null;
            $branchId = (int)$payload['branch_id'];

            // 1. Customer (the account) and the contact (the person who gave the work)
            [$customerId, $contactId] = self::upsertCustomer($payload['customer'], $branchId, $userId, $source);
            // Rate is always typed in by hand now, so no price list or group discount applies.

            // 2. Job number + tracking token. A number typed by hand wins (e.g. to match a
            //    GST bill); otherwise take the next one from the branch sequence.
            $jobNo = trim((string)($payload['job_no'] ?? ''));
            if ($jobNo !== '') {
                if (DB::val('SELECT id FROM `' . tbl('orders') . '` WHERE job_no = ?', [$jobNo])) {
                    throw new \RuntimeException('Job number "' . $jobNo . '" is already used by another order.');
                }
            } else {
                $jobNo = self::generateJobNo($branchId);
            }
            $token = random_token(16); // 32 hex chars

            // Back-dating an old order: everything hangs off the date given, not today.
            $orderDate = !empty($payload['order_date'])
                ? date('Y-m-d H:i:s', (int)strtotime((string)$payload['order_date']))
                : now();
            $baseTs = (int)strtotime($orderDate);

            // 3. Order + items
            $orderId = DB::insert('orders', [
                'job_no' => $jobNo,
                'tracking_token' => $token,
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'contact_id' => $contactId,
                'source' => $source,
                'taken_by_user_id' => $userId,
                'accepted_by_user_id' => $payload['accepted_by_user_id'] ?? $userId,
                'order_date' => $orderDate,
                'priority' => $payload['priority'] ?? 'normal',
                'status' => 'design_pending', // recomputed from the item lines below
                'needs_review' => ($source === 'public' && !Settings::getBool('public_order_auto_confirm')) ? 1 : 0,
                'delivery_type' => $payload['delivery_type'] ?? 'pickup',
                'delivery_address' => $payload['delivery_address'] ?? null,
                'customer_note' => $payload['customer_note'] ?? null,
                'internal_note' => $payload['internal_note'] ?? null,
                'discount_type' => null, // this shop does not give a discount
                'discount_value' => 0,
                'delivery_charge' => (float)($payload['delivery_charge'] ?? 0),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $orderDue = null;
            foreach (array_values($payload['items']) as $index => $line) {
                $row = self::buildLine($line, $baseTs, $index);
                $dueDate = $row['due_date'];
                if ($orderDue === null || $dueDate > $orderDue) {
                    $orderDue = $dueDate;
                }
                $designerId = $row['assigned_designer_id'];
                $requiresDesign = (int)$row['requires_design'];

                $itemRowId = DB::insert('order_items', ['order_id' => $orderId] + $row);
                self::history($orderId, $itemRowId, null, $row['status'], $userId, $source === 'public', 'Order created');

                // Auto-assign designer when enabled and none picked
                if ($requiresDesign && !$designerId && Settings::getBool('auto_assign_designer')) {
                    self::autoAssignDesigner($itemRowId, $branchId, $userId);
                }
            }

            self::recalcTotals($orderId);
            DB::update('orders', ['due_date' => $orderDue], ['id' => $orderId]);

            // 4. Advance payment
            $paymentId = null;
            $advance = (float)($payload['advance']['amount'] ?? 0);
            if ($advance > 0) {
                $paymentId = self::addPayment($orderId, [
                    'amount' => $advance,
                    'type' => 'advance',
                    'mode' => $payload['advance']['mode'] ?? 'cash',
                    'reference' => $payload['advance']['reference'] ?? null,
                ], $userId, false);
            }

            self::recomputeOrderStatus($orderId);
            Logger::activity('order', 'create', 'order', $orderId, "Order $jobNo created ($source)");

            return ['order_id' => $orderId, 'job_no' => $jobNo, 'tracking_token' => $token, 'payment_id' => $paymentId];
        });

        // 6. WhatsApp — queued after commit so the worker never sees a half-saved order.
        //    The customer confirmation + receipt are opt-in (staff pick Yes/No when saving);
        //    internal manager/designer alerts always go out via orderCreated().
        $notifyCustomer = ($payload['notify_customer'] ?? true) ? true : false;
        WaEvents::orderCreated($result['order_id'], $notifyCustomer);
        if ($result['payment_id'] && $notifyCustomer) {
            WaEvents::paymentReceived($result['payment_id']);
        }
        return $result;
    }

    /**
     * Reconcile an existing order's item lines against a submitted set (full edit).
     *  - line with `id` matching an existing item → manual update (qty/rate/amount/tax), keeps status/designer/proofs
     *  - line without `id` → inserted as a fresh line (full pricing, designer assignment, alerts)
     *  - existing item missing from the set → cancelled if it has design work, else hard-removed
     * @param array $items each: [id?, item_id, qty, rate?, spec(array), spec_text?, due_date?, designer_id?, special_instructions?]
     */
    public static function syncOrderItems(int $orderId, array $items, ?int $userId): void
    {
        if (count($items) === 0) {
            throw new \RuntimeException('An order must have at least one item.');
        }
        $newDesignerItemIds = DB::transaction(function () use ($orderId, $items, $userId) {
            $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ? FOR UPDATE', [$orderId]);
            if (!$order) {
                throw new \RuntimeException('Order not found.');
            }
            $branchId = (int)$order['branch_id'];

            $existing = DB::all('SELECT * FROM `' . tbl('order_items') . '` WHERE order_id = ?', [$orderId]);
            $existingById = [];
            foreach ($existing as $e) {
                $existingById[(int)$e['id']] = $e;
            }
            $keptIds = [];
            $newDesigner = [];

            foreach (array_values($items) as $index => $line) {
                $lineId = isset($line['id']) ? (int)$line['id'] : 0;

                $baseTs = time();
                if ($lineId && isset($existingById[$lineId])) {
                    $ex = $existingById[$lineId];
                    if ($ex['status'] === 'cancelled') {
                        $keptIds[] = $lineId; // leave cancelled lines untouched
                        continue;
                    }
                    // Recalculate through the SAME builder New Order uses, so every field on the
                    // line — name, category answers, qty, width, height, rate — is editable and
                    // the money always comes out identical to a freshly-taken order.
                    $row = self::buildLine($line + [
                        'category_id' => $line['category_id'] ?? self::categoryIdFor($ex),
                        'item_name' => $line['item_name'] ?? $ex['item_name_snapshot'],
                        'item_id' => $line['item_id'] ?? $ex['item_id'],
                        'calc_mode' => $ex['calc_mode'] ?? 'simple',
                        'unit' => $ex['unit'],
                        'requires_design' => $ex['requires_design'],
                    ], $baseTs, $index);

                    $changed = abs((float)$row['rate'] - (float)$ex['rate']) > 0.009
                        || abs((float)$row['qty'] - (float)$ex['qty']) > 0.0001
                        || abs((float)($row['width_ft'] ?? 0) - (float)($ex['width_ft'] ?? 0)) > 0.0001
                        || abs((float)($row['height_ft'] ?? 0) - (float)($ex['height_ft'] ?? 0)) > 0.0001;

                    // Keep the line's own life-cycle: status, designer and dates are not reset.
                    unset($row['status'], $row['assigned_designer_id'], $row['designer_assigned_at'],
                          $row['due_date'], $row['created_at'], $row['requires_design'], $row['item_id']);
                    if (!isset($line['special_instructions'])) {
                        $row['special_instructions'] = $ex['special_instructions'];
                    }
                    DB::update('order_items', $row, ['id' => $lineId]);
                    if ($changed) {
                        Logger::activity('order', 'edit_item', 'order_item', $lineId,
                            $order['job_no'] . ': line edited → ' . $row['item_name_snapshot']
                            . ' ' . rtrim(rtrim(number_format((float)$row['qty'], 2, '.', ''), '0'), '.')
                            . ' × ₹' . $row['rate'] . ' = ₹' . $row['amount']);
                    }
                    $keptIds[] = $lineId;
                    continue;
                }

                // Brand-new line — exactly the same builder as New Order.
                $row = self::buildLine($line, $baseTs, $index);
                $requiresDesign = (int)$row['requires_design'];
                $designerId = $row['assigned_designer_id'];
                $newId = DB::insert('order_items', ['order_id' => $orderId] + $row);
                self::history($orderId, $newId, null, $row['status'], $userId, false, 'Item added on edit');
                Logger::activity('order', 'add_item', 'order_item', $newId,
                    $order['job_no'] . ': line added — ' . $row['item_name_snapshot']);
                if ($requiresDesign && $designerId) {
                    $newDesigner[] = $newId;
                } elseif ($requiresDesign && !$designerId && Settings::getBool('auto_assign_designer')) {
                    if (self::autoAssignDesigner($newId, $branchId, $userId)) {
                        $newDesigner[] = $newId;
                    }
                }
                $keptIds[] = $newId;
            }

            // Anything left over was removed by the editor.
            foreach ($existing as $ex) {
                $exId = (int)$ex['id'];
                if (in_array($exId, $keptIds, true) || $ex['status'] === 'cancelled') {
                    continue; // already gone / never shown in the editor
                }
                $hasProofs = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('design_proofs') . '` WHERE order_item_id = ?', [$exId]) > 0;
                $advanced = in_array($ex['status'], ['printing', 'ready_for_delivery', 'delivered', 'completed'], true);
                if ($hasProofs || $advanced) {
                    if ($ex['status'] !== 'cancelled') {
                        DB::update('order_items', ['status' => 'cancelled', 'updated_at' => now()], ['id' => $exId]);
                        self::history($orderId, $exId, (string)$ex['status'], 'cancelled', $userId, false, 'Item removed on edit');
                        Logger::activity('order', 'remove_item', 'order_item', $exId, $order['job_no'] . ': line cancelled (had design work)');
                    }
                } else {
                    DB::run('DELETE FROM `' . tbl('order_attachments') . '` WHERE order_item_id = ?', [$exId]);
                    DB::run('DELETE FROM `' . tbl('order_status_history') . '` WHERE order_item_id = ?', [$exId]);
                    DB::delete('order_items', ['id' => $exId]);
                    Logger::activity('order', 'remove_item', 'order_item', $exId, $order['job_no'] . ': line removed — ' . $ex['item_name_snapshot']);
                }
            }

            self::recomputeOrderStatus($orderId);
            return $newDesigner;
        });

        foreach ($newDesignerItemIds as $iid) {
            WaEvents::designerAssigned((int)$iid);
        }
    }

    /**
     * Build one order_items row from a submitted line. The single place a line is turned into
     * stored numbers — used by both createOrder() and syncOrderItems(), so a line saved from
     * New Order and the same line re-saved from Edit Order can never come out different.
     *
     * A line carries either category_id + item_name (typed by hand at the counter) or, for the
     * public website which still orders from the catalogue, an item_id we resolve those from.
     *
     * @return array order_items columns, minus order_id
     */
    private static function buildLine(array $line, int $baseTs, int $index): array
    {
        $categoryId = (int)($line['category_id'] ?? 0);
        $name = trim((string)($line['item_name'] ?? ''));
        $unit = trim((string)($line['unit'] ?? ''));
        $catalogItemId = !empty($line['item_id']) ? (int)$line['item_id'] : null;
        $turnaround = 24;

        // Public-site path: fill category and name from the catalogue item.
        if ($catalogItemId && ($categoryId === 0 || $name === '')) {
            $item = DB::get(
                'SELECT i.*, c.name AS category_name FROM `' . tbl('items') . '` i
                 JOIN `' . tbl('categories') . '` c ON c.id = i.category_id
                 WHERE i.id = ? AND i.deleted_at IS NULL',
                [$catalogItemId]
            );
            if (!$item) {
                throw new \RuntimeException('Item not found.');
            }
            $categoryId = $categoryId ?: (int)$item['category_id'];
            $name = $name !== '' ? $name : (string)$item['name'];
            $unit = $unit !== '' ? $unit : (string)$item['unit'];
            $turnaround = (int)$item['default_turnaround_hours'];
            if (!isset($line['rate']) || $line['rate'] === '') {
                $line['rate'] = $item['base_price'];
            }
        }

        $category = DB::get('SELECT * FROM `' . tbl('categories') . '` WHERE id = ?', [$categoryId]);
        if (!$category) {
            throw new \RuntimeException('Every item needs a category.');
        }
        if ($name === '') {
            throw new \RuntimeException('Every item needs a name.');
        }

        // The LINE decides how it is worked out — a light board mixes sq.ft components
        // (acrylic, ACP) with per-piece ones (LED module, power supply) in one order.
        // Fall back to the category for lines that do not say.
        $lineMode = (string)($line['calc_mode'] ?? '');
        if (!in_array($lineMode, OrderCalc::MODES, true)) {
            $catMode = (string)($category['calc_mode'] ?? 'simple');
            $lineMode = in_array($catMode, OrderCalc::MODES, true) ? $catMode : 'simple';
        }

        $spec = is_array($line['spec'] ?? null) ? $line['spec'] : [];
        $calc = OrderCalc::line([
            'calc_mode'   => $lineMode,
            'qty'         => $line['qty'] ?? 1,
            'width_ft'    => $line['width_ft'] ?? 0,
            'height_ft'   => $line['height_ft'] ?? 0,
            'rate'        => $line['rate'] ?? 0,
            'tax_percent' => self::gstPercent($line, $category),
        ]);

        $requiresDesign = array_key_exists('requires_design', $line)
            ? (int)(bool)$line['requires_design']
            : (int)($category['requires_design'] ?? 1);
        $designerId = !empty($line['designer_id']) ? (int)$line['designer_id'] : null;

        return [
            'item_id' => $catalogItemId,
            'item_name_snapshot' => mb_substr($name, 0, 150),
            'category_name_snapshot' => (string)$category['name'],
            'calc_mode' => $calc['calc_mode'],
            'qty' => $calc['qty'],
            'width_ft' => $calc['width_ft'],
            'height_ft' => $calc['height_ft'],
            'total_sqft' => $calc['total_sqft'],
            'unit' => $unit !== '' ? $unit : ($calc['calc_mode'] === 'sqft' ? 'sqft' : 'pcs'),
            'rate' => $calc['rate'],
            'rate_overridden' => 0,
            'spec_json' => json_encode($spec, JSON_UNESCAPED_UNICODE),
            'spec_text' => self::specText($categoryId, $spec, $calc),
            'amount' => $calc['amount'],
            'tax_percent' => $calc['tax_percent'],
            'tax_amount' => $calc['tax_amount'],
            'line_total' => $calc['line_total'],
            'requires_design' => $requiresDesign,
            'assigned_designer_id' => $requiresDesign ? $designerId : null,
            'designer_assigned_at' => ($requiresDesign && $designerId) ? now() : null,
            'status' => $requiresDesign ? 'design_pending' : 'ready_for_print',
            'due_date' => !empty($line['due_date'])
                ? date('Y-m-d H:i:s', (int)strtotime((string)$line['due_date']))
                : date('Y-m-d H:i:s', $baseTs + $turnaround * 3600),
            'special_instructions' => $line['special_instructions'] ?? null,
            'sort_order' => $index,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * GST for a line, most specific wins:
     *   1. what was typed on the line itself
     *   2. the category's own GST %
     *   3. the shop-wide Default GST % from Settings
     * Nothing set anywhere means no GST — it is never forced on.
     */
    public static function gstPercent(array $line, ?array $category = null): float
    {
        if (isset($line['tax_percent']) && $line['tax_percent'] !== '' && $line['tax_percent'] !== null) {
            return max(0.0, min(100.0, (float)$line['tax_percent']));
        }
        if ($category !== null && (float)($category['tax_percent'] ?? 0) > 0) {
            return max(0.0, min(100.0, (float)$category['tax_percent']));
        }
        return max(0.0, min(100.0, (float)Settings::get('default_gst_percent', 0)));
    }

    /** Best-effort category for an existing line: its catalogue item, else the snapshot name. */
    private static function categoryIdFor(array $existingRow): int
    {
        if (!empty($existingRow['item_id'])) {
            $id = DB::val('SELECT category_id FROM `' . tbl('items') . '` WHERE id = ?', [(int)$existingRow['item_id']]);
            if ($id) {
                return (int)$id;
            }
        }
        $id = DB::val('SELECT id FROM `' . tbl('categories') . '` WHERE name = ?', [(string)$existingRow['category_name_snapshot']]);
        return (int)($id ?? 0);
    }

    /** Readable summary of the category answers plus the size, for job cards and bills. */
    private static function specText(int $categoryId, array $spec, array $calc): string
    {
        $parts = [];
        $size = OrderCalc::sizeText($calc);
        if ($size !== '') {
            $parts[] = $size;
        }
        if ($spec) {
            $options = DB::all(
                'SELECT label, field_key FROM `' . tbl('category_options') . '` WHERE category_id = ? ORDER BY sort_order, id',
                [$categoryId]
            );
            foreach ($options as $option) {
                $value = $spec[$option['field_key']] ?? null;
                if ($value === null || $value === '' || $value === []) {
                    continue;
                }
                $flat = is_array($value) ? implode(', ', array_map('strval', $value)) : (string)$value;
                $parts[] = $option['label'] . ': ' . mb_substr($flat, 0, 300);
            }
        }
        return implode(' | ', $parts);
    }

    /**
     * Resolve the account and the person for an order.
     *
     * A number is looked up against the contact book first, because that is where every
     * number lives now. Three outcomes:
     *   - the number is known           -> use that person and their company
     *   - a company was chosen by hand  -> add this number to it as a new person
     *   - neither                       -> a brand new company with this person as primary
     *
     * @return array{0:int,1:?int} [customer_id, contact_id]
     */
    private static function upsertCustomer(array $c, int $branchId, ?int $userId, string $source): array
    {
        $phone = local_phone($c['phone'] ?? '');
        if (!$phone) {
            throw new \RuntimeException('A valid 10-digit customer mobile number is required.');
        }

        // --- the number is already in the book ---------------------------------------
        // It may be under more than one firm: one man, two firms, one mobile. When it is,
        // the order has to say which firm the bill belongs to.
        $chosen = (int)($c['id'] ?? 0);
        $matches = CustomerBook::findAllByPhone($phone);

        // "Another firm on this number" — the counter said so explicitly, so skip the
        // matching entirely and open a second account.
        $newFirm = !empty($c['new_firm']) && trim((string)($c['name'] ?? '')) !== '';
        if ($newFirm && $chosen <= 0) {
            $matches = [];
        }

        if (count($matches) > 1 && $chosen <= 0) {
            $names = implode(' / ', array_column($matches, 'customer_name'));
            throw new \RuntimeException(
                'This number is used by ' . count($matches) . ' customers (' . $names
                . '). Pick which one the bill is for.'
            );
        }
        $contact = $matches ? CustomerBook::findByPhone($phone, $chosen > 0 ? $chosen : null) : null;

        // A known number with a DIFFERENT firm name typed against it is ambiguous: it is
        // either the second firm of the same man, or a slip. Never guess — billing the
        // wrong firm is worse than asking.
        if ($contact && $chosen <= 0) {
            $typed = trim((string)($c['name'] ?? ''));
            if ($typed !== '' && mb_strtolower($typed) !== mb_strtolower((string)$contact['customer_name'])) {
                throw new \RuntimeException(
                    'This number is already under "' . $contact['customer_name'] . '". If "' . $typed
                    . '" is a second firm on the same number, tick "Another firm on this number";'
                    . ' otherwise leave the name blank to bill ' . $contact['customer_name'] . '.'
                );
            }
        }

        if ($contact) {
            if ((int)$contact['is_blocked'] === 1) {
                throw new \RuntimeException('This customer is blocked. Please contact the manager.');
            }
            $customerId = (int)$contact['customer_id'];

            // Fill in a name only where there is not a real one yet — a placeholder contact
            // carries the account's own name. Renaming a person is done on the customer
            // screen, never as a side effect of writing an order.
            $person = trim((string)($c['contact_name'] ?? ''));
            $stored = trim((string)$contact['name']);
            if ($person !== '' && ($stored === '' || $stored === trim((string)$contact['customer_name']))) {
                DB::update('customer_contacts', ['name' => $person, 'updated_at' => now()], ['id' => (int)$contact['id']]);
            }
            self::touchCustomer($customerId, $c);
            return [$customerId, (int)$contact['id']];
        }

        // --- an existing firm was picked: add this number there ------------------------
        // Reached either because the number is brand new, or because it is known under a
        // DIFFERENT firm and this order is for the other one. Both are normal.
        $chosenId = $chosen;
        if ($chosenId > 0) {
            $customer = DB::get(
                'SELECT * FROM `' . tbl('customers') . '` WHERE id = ? AND deleted_at IS NULL',
                [$chosenId]
            );
            if ($customer) {
                if ((int)$customer['is_blocked'] === 1) {
                    throw new \RuntimeException('This customer is blocked. Please contact the manager.');
                }
                $contactId = CustomerBook::addContact($chosenId, [
                    'name' => $c['contact_name'] ?? $c['name'] ?? '',
                    'phone' => $phone,
                    'whatsapp' => $c['whatsapp'] ?? '',
                    'designation' => $c['designation'] ?? '',
                ]);
                self::touchCustomer($chosenId, $c);
                return [$chosenId, $contactId];
            }
        }

        // --- brand new account --------------------------------------------------------
        $name = trim((string)($c['name'] ?? ''));
        if ($name === '') {
            throw new \RuntimeException('Customer name is required for a new customer.');
        }
        $customerId = DB::insert('customers', [
            'name' => $name,
            'phone' => $phone,
            'whatsapp' => local_phone($c['whatsapp'] ?? '') ?: $phone,
            'email' => $c['email'] ?? null,
            'address' => $c['address'] ?? null,
            'pincode' => $c['pincode'] ?? null,
            'gstin' => $c['gstin'] ?? null,
            'source' => $source === 'public' ? 'public' : 'counter',
            'created_by' => $userId,
            'branch_id' => $branchId,
            'is_verified' => $source === 'public' ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $contactId = CustomerBook::addContact($customerId, [
            'name' => trim((string)($c['contact_name'] ?? '')) ?: $name,
            'phone' => $phone,
            'whatsapp' => $c['whatsapp'] ?? '',
            'designation' => $c['designation'] ?? '',
        ]);
        return [$customerId, $contactId];
    }

    /** Fill in account details that were left blank before, without overwriting good ones. */
    private static function touchCustomer(int $customerId, array $c): void
    {
        $existing = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [$customerId]);
        if (!$existing) {
            return;
        }
        // Only ever FILL BLANKS. Never overwrite what is already there, and never touch the
        // name: a different name typed against a known number means a second firm on that
        // number, not a rename, and renaming would quietly move every past order with it.
        $updates = [];
        foreach (['address', 'gstin', 'email'] as $field) {
            if (!empty($c[$field]) && trim((string)$existing[$field]) === '') {
                $updates[$field] = trim((string)$c[$field]);
            }
        }
        if ($updates) {
            $updates['updated_at'] = now();
            DB::update('customers', $updates, ['id' => $customerId]);
        }
    }

    public static function recalcTotals(int $orderId): void
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        $lines = DB::all(
            "SELECT amount, tax_amount FROM `" . tbl('order_items') . "` WHERE order_id = ? AND status <> 'cancelled'",
            [$orderId]
        );
        // Same roll-up the browser previews and the printed bill use.
        $totals = OrderCalc::totals($lines, (float)$order['delivery_charge']);

        DB::update('orders', [
            'subtotal' => $totals['subtotal'],
            'discount_amount' => 0,
            'tax_amount' => $totals['tax_amount'],
            'total' => $totals['total'],
            'round_off' => $totals['round_off'],
            'balance_amount' => OrderCalc::money($totals['total'] - (float)$order['paid_amount']),
            'updated_at' => now(),
        ], ['id' => $orderId]);
    }

    /** Insert a payment and refresh order paid/balance. Returns payment id. */
    public static function addPayment(int $orderId, array $p, ?int $userId, bool $fireEvents = true): int
    {
        $paymentId = DB::transaction(function () use ($orderId, $p, $userId) {
            $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ? FOR UPDATE', [$orderId]);
            if (!$order) {
                throw new \RuntimeException('Order not found.');
            }
            $amount = round((float)$p['amount'], 2);
            if ($amount <= 0) {
                throw new \RuntimeException('Payment amount must be greater than zero.');
            }
            $type = $p['type'] ?? 'part';
            $receiptNo = self::generateReceiptNo((int)$order['branch_id']);
            $id = DB::insert('payments', [
                'order_id' => $orderId,
                'customer_id' => (int)$order['customer_id'],
                'branch_id' => (int)$order['branch_id'],
                'receipt_no' => $receiptNo,
                'amount' => $type === 'refund' ? -abs($amount) : $amount,
                'type' => $type,
                'mode' => $p['mode'] ?? 'cash',
                'reference' => $p['reference'] ?? null,
                'received_by_user_id' => $userId,
                'paid_at' => $p['paid_at'] ?? now(),
                'note' => $p['note'] ?? null,
                'created_at' => now(),
            ]);
            self::recalcPayments($orderId);
            Logger::activity('payment', 'create', 'payment', $id,
                "Payment $receiptNo of ₹$amount ($type) on {$order['job_no']}");
            return $id;
        });
        if ($fireEvents) {
            WaEvents::paymentReceived($paymentId);
        }
        self::maybeComplete($orderId);
        return $paymentId;
    }

    public static function recalcPayments(int $orderId): void
    {
        $paid = (float)DB::val(
            'SELECT COALESCE(SUM(amount),0) FROM `' . tbl('payments') . '` WHERE order_id = ? AND deleted_at IS NULL',
            [$orderId]
        );
        $total = (float)DB::val('SELECT total FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        DB::update('orders', [
            'paid_amount' => $paid,
            'balance_amount' => round($total - $paid, 2),
            'updated_at' => now(),
        ], ['id' => $orderId]);
    }

    /** delivered + fully paid → completed. */
    public static function maybeComplete(int $orderId): void
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        if (!$order || $order['status'] !== 'delivered' || (float)$order['balance_amount'] > 0.009) {
            return;
        }
        DB::update('orders', ['status' => 'completed', 'completed_at' => now(), 'updated_at' => now()], ['id' => $orderId]);
        DB::run(
            'UPDATE `' . tbl('order_items') . '` SET status = ?, updated_at = ? WHERE order_id = ? AND status = ?',
            ['completed', now(), $orderId, 'delivered']
        );
        self::history($orderId, null, 'delivered', 'completed', null, false, 'Fully paid — auto-completed');
    }

    /**
     * Change one item's status with full validation.
     * @return array{ok:bool,error?:string}
     */
    public static function changeItemStatus(
        int $itemId,
        string $to,
        ?int $userId,
        string $note = '',
        bool $byCustomer = false,
        bool $isManager = false,
        bool $approvalOverride = false
    ): array {
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [$itemId]);
        if (!$item) {
            return ['ok' => false, 'error' => 'Job item not found.'];
        }
        $from = (string)$item['status'];
        [$allowed, $flag] = Status::validate($from, $to, (bool)$item['requires_design'], $isManager);
        if (!$allowed) {
            return ['ok' => false, 'error' => $flag];
        }
        // Staff moving a job past the design stages no longer wait on the customer's online
        // approval — the counter decides. Ticking "Customer approved in person" is now purely
        // a record of what happened, not a permission to proceed.
        if ($approvalOverride && (bool)$item['requires_design']) {
            self::recordCounterApproval($itemId, $userId);
            $note = trim($note) !== ''
                ? trim($note) . ' — approved in person at the counter'
                : 'Approved in person at the counter';
        }

        DB::update('order_items', ['status' => $to, 'updated_at' => now()], ['id' => $itemId]);
        // Pulled back into the design loop → the artwork is being reworked, so a previous
        // in-person approval no longer covers it and must be taken again.
        if (Status::isDesignStage($to) && Status::rank($to) < Status::rank($from)) {
            self::clearCounterApproval($itemId);
        }
        self::history((int)$item['order_id'], $itemId, $from, $to, $userId, $byCustomer, $note);
        self::recomputeOrderStatus((int)$item['order_id']);
        return ['ok' => true];
    }

    /**
     * Staff confirmed the customer approved the design face-to-face at the counter.
     * Marks the newest live proof approved (so the proof list is not left saying "pending")
     * and always writes an activity entry naming the staff member who vouched for it.
     */
    private static function recordCounterApproval(int $itemId, ?int $userId): void
    {
        // Both writes together: never leave a proof marked approved without the item stamp,
        // which would lock the customer out of approving online while the job stayed blocked.
        $proof = DB::transaction(function () use ($itemId, $userId) {
            $proof = DB::get(
                'SELECT * FROM `' . tbl('design_proofs') . "` WHERE order_item_id = ? AND status <> 'superseded'
                 ORDER BY version DESC, id DESC LIMIT 1",
                [$itemId]
            );
            // Stamp the item first so every later stage flows without asking again.
            DB::update('order_items', [
                'counter_approved_at' => now(),
                'counter_approved_by' => $userId,
                'updated_at' => now(),
            ], ['id' => $itemId]);
            if ($proof) {
                DB::update('design_proofs', [
                    'status' => 'approved',
                    'approval_confirmed' => 1,
                    'responded_at' => now(),
                    'response_ip' => request_ip(),
                    'response_user_agent' => 'Approved in person at the counter (staff-confirmed)',
                ], ['id' => (int)$proof['id']]);
            }
            return $proof;
        });
        Logger::activity('order', 'counter_approval', 'order_item', $itemId,
            'Customer approval taken in person at the counter'
            . ($proof ? ' (proof v' . (int)$proof['version'] . ')' : ' (no proof on file)'));
    }

    /**
     * Drop a previous "approved in person" stamp. Called whenever the artwork changes
     * (new proof version) or the job is pulled back into the design loop — the customer
     * vouched for the old design, so the new one must be approved again.
     */
    public static function clearCounterApproval(int $itemId): void
    {
        DB::run(
            'UPDATE `' . tbl('order_items') . '`
             SET counter_approved_at = NULL, counter_approved_by = NULL, updated_at = ?
             WHERE id = ? AND counter_approved_at IS NOT NULL',
            [now(), $itemId]
        );
    }

    /** Order status = lowest stage among active items (+ delivered/cancelled edge cases). */
    public static function recomputeOrderStatus(int $orderId): void
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        if (!$order) {
            return;
        }
        $items = DB::all('SELECT status FROM `' . tbl('order_items') . '` WHERE order_id = ?', [$orderId]);
        if (!$items) {
            return;
        }
        $statuses = array_column($items, 'status');
        $active = array_filter($statuses, fn($s) => $s !== 'cancelled');
        if (!$active) {
            $new = 'cancelled';
        } else {
            $new = null;
            $minRank = PHP_INT_MAX;
            foreach ($active as $s) {
                $rank = Status::rank($s);
                if ($rank < $minRank) {
                    $minRank = $rank;
                    $new = $s;
                }
            }
        }
        if ($new !== null && $new !== $order['status']) {
            $updates = ['status' => $new, 'updated_at' => now()];
            if ($new === 'delivered') {
                $updates['delivered_at'] = now();
            }
            // Moving a job back out of a finished state must clear that state completely,
            // otherwise the order still reads as delivered/completed/cancelled elsewhere.
            if ($new !== 'delivered' && Status::rank($new) < Status::rank('delivered')) {
                $updates['delivered_at'] = null;
            }
            if ($new !== 'completed') {
                $updates['completed_at'] = null;
            }
            if ($new === 'cancelled') {
                $updates['is_cancelled'] = 1;
                $updates['cancelled_at'] = $order['cancelled_at'] ?: now();
            } elseif ((int)$order['is_cancelled'] === 1) {
                $updates['is_cancelled'] = 0;
                $updates['cancelled_at'] = null;
                $updates['cancelled_reason'] = null;
                $updates['cancelled_by'] = null;
            }
            DB::update('orders', $updates, ['id' => $orderId]);
            WaEvents::statusChanged($orderId, $new);
            if ($new === 'delivered') {
                self::maybeComplete($orderId);
            }
        }
    }

    /** Manual designer assignment. */
    public static function assignDesigner(int $itemId, int $designerId, ?int $userId): array
    {
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [$itemId]);
        if (!$item) {
            return ['ok' => false, 'error' => 'Job item not found.'];
        }
        if (!(bool)$item['requires_design']) {
            return ['ok' => false, 'error' => 'This item does not require design.'];
        }
        // Anyone whose role can upload proofs may be given design work — the owner covering
        // for a busy designer is a normal Tuesday, and the job must count as theirs.
        if (!Designers::canDesign($designerId)) {
            return ['ok' => false, 'error' => 'That user cannot be given design work. '
                . 'Give their role the "Upload design proofs" permission on the Roles screen first.'];
        }
        $designer = DB::get('SELECT * FROM `' . tbl('users') . '` WHERE id = ?', [$designerId]);
        DB::update('order_items', [
            'assigned_designer_id' => $designerId,
            'designer_assigned_at' => now(),
            // Assigned counts the same as accepted: this is the moment it became theirs.
            'claimed_at' => now(),
            'claimed_by_user_id' => $userId ?? $designerId,
            'updated_at' => now(),
        ], ['id' => $itemId]);
        Logger::activity('order', 'assign', 'order_item', $itemId, 'Assigned designer ' . $designer['name']);
        WaEvents::designerAssigned($itemId);
        return ['ok' => true];
    }

    /**
     * A designer accepts a job from the shared board — it becomes theirs, and that is what
     * the monthly design report counts.
     *
     * The rules:
     *   - only a job that needs design can be accepted
     *   - only while it is still in a design stage; once it is printing it is too late
     *   - only if nobody else holds it. The claim is a conditional UPDATE, so two people
     *     pressing Accept at the same instant can never both win — the second is told who
     *     got there first
     *   - the person who accepts is the designer, whatever their job title
     */
    public static function claimDesign(int $itemId, int $userId): array
    {
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [$itemId]);
        if (!$item) {
            return ['ok' => false, 'error' => 'Job item not found.'];
        }
        if (!(bool)$item['requires_design']) {
            return ['ok' => false, 'error' => 'This item does not need design.'];
        }
        if (!Status::isDesignStage((string)$item['status'])) {
            return ['ok' => false, 'error' => 'This job has moved past the design stage.'];
        }

        // Whoever changes the row wins; everyone else sees zero rows affected.
        $claimed = DB::run(
            'UPDATE `' . tbl('order_items') . '`
             SET assigned_designer_id = ?, designer_assigned_at = NOW(),
                 claimed_at = NOW(), claimed_by_user_id = ?, updated_at = NOW()
             WHERE id = ? AND assigned_designer_id IS NULL',
            [$userId, $userId, $itemId]
        )->rowCount();

        if ($claimed === 0) {
            $holder = DB::val(
                'SELECT u.name FROM `' . tbl('order_items') . '` oi
                 JOIN `' . tbl('users') . '` u ON u.id = oi.assigned_designer_id WHERE oi.id = ?',
                [$itemId]
            );
            return ['ok' => false, 'error' => $holder
                ? (string)$holder . ' has already accepted this job.'
                : 'This job could not be accepted — please refresh and try again.'];
        }

        $name = (string)DB::val('SELECT name FROM `' . tbl('users') . '` WHERE id = ?', [$userId]);
        Logger::activity('design', 'claim', 'order_item', $itemId, $name . ' accepted this design job');
        WaEvents::designerAssigned($itemId);
        return ['ok' => true, 'designer' => $name];
    }

    /**
     * Hand a job back to the board. Only the person holding it (or a manager) may, and only
     * before any proof has gone out — after that the work is already half done and giving it
     * away silently would lose the thread.
     */
    public static function releaseDesign(int $itemId, int $userId, bool $isManager = false): array
    {
        $item = DB::get('SELECT * FROM `' . tbl('order_items') . '` WHERE id = ?', [$itemId]);
        if (!$item) {
            return ['ok' => false, 'error' => 'Job item not found.'];
        }
        if ($item['assigned_designer_id'] === null) {
            return ['ok' => false, 'error' => 'Nobody is holding this job.'];
        }
        if (!$isManager && (int)$item['assigned_designer_id'] !== $userId) {
            return ['ok' => false, 'error' => 'Only the designer holding this job can give it back.'];
        }
        $sent = (int)DB::val(
            'SELECT COUNT(*) FROM `' . tbl('design_proofs') . '` WHERE order_item_id = ?',
            [$itemId]
        );
        if ($sent > 0 && !$isManager) {
            return ['ok' => false, 'error' => 'A proof has already gone to the customer — ask a manager to reassign.'];
        }
        DB::update('order_items', [
            'assigned_designer_id' => null,
            'designer_assigned_at' => null,
            'claimed_at' => null,
            'claimed_by_user_id' => null,
            'updated_at' => now(),
        ], ['id' => $itemId]);
        Logger::activity('design', 'release', 'order_item', $itemId, 'Job put back on the board');
        return ['ok' => true];
    }

    /** Round-robin auto-assignment respecting designer_capacity. */
    public static function autoAssignDesigner(int $itemId, int $branchId, ?int $userId): ?int
    {
        $designers = DB::all(
            'SELECT u.id, u.designer_capacity,
                    (SELECT COUNT(*) FROM `' . tbl('order_items') . '` oi
                     WHERE oi.assigned_designer_id = u.id
                       AND oi.status IN (\'design_pending\',\'design_in_progress\',\'proof_sent\',\'change_requested\')) AS open_jobs,
                    (SELECT MAX(oi2.designer_assigned_at) FROM `' . tbl('order_items') . '` oi2
                     WHERE oi2.assigned_designer_id = u.id) AS last_assigned
             FROM `' . tbl('users') . '` u
             WHERE u.is_active = 1 AND u.deleted_at IS NULL AND ' . Designers::sqlCanDesign('u') . '
             ORDER BY open_jobs ASC, last_assigned ASC, u.id ASC'
        );
        foreach ($designers as $designer) {
            $capacity = $designer['designer_capacity'] !== null ? (int)$designer['designer_capacity'] : PHP_INT_MAX;
            if ((int)$designer['open_jobs'] < $capacity) {
                DB::update('order_items', [
                    'assigned_designer_id' => (int)$designer['id'],
                    'designer_assigned_at' => now(),
                    'claimed_at' => now(),
                    'claimed_by_user_id' => (int)$designer['id'],
                    'updated_at' => now(),
                ], ['id' => $itemId]);
                Logger::activity('order', 'auto_assign', 'order_item', $itemId,
                    'Auto-assigned designer #' . $designer['id'] . ' (round-robin)');
                return (int)$designer['id'];
            }
        }
        return null;
    }

    public static function cancelOrder(int $orderId, string $reason, ?int $userId): array
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        if (!$order) {
            return ['ok' => false, 'error' => 'Order not found.'];
        }
        if ((int)$order['is_cancelled'] === 1) {
            return ['ok' => false, 'error' => 'Order is already cancelled.'];
        }
        if (trim($reason) === '') {
            return ['ok' => false, 'error' => 'A cancellation reason is required.'];
        }
        DB::update('orders', [
            'status' => 'cancelled',
            'is_cancelled' => 1,
            'cancelled_reason' => substr($reason, 0, 255),
            'cancelled_by' => $userId,
            'cancelled_at' => now(),
            'updated_at' => now(),
        ], ['id' => $orderId]);
        DB::run(
            'UPDATE `' . tbl('order_items') . '` SET status = ?, updated_at = ? WHERE order_id = ? AND status NOT IN (?,?)',
            ['cancelled', now(), $orderId, 'delivered', 'completed']
        );
        self::history($orderId, null, (string)$order['status'], 'cancelled', $userId, false, $reason);
        Logger::activity('order', 'cancel', 'order', $orderId, "Cancelled {$order['job_no']}: $reason");
        WaEvents::orderCancelled($orderId);
        return ['ok' => true];
    }

    public static function history(
        int $orderId,
        ?int $itemId,
        ?string $from,
        string $to,
        ?int $userId,
        bool $byCustomer,
        string $note = ''
    ): void {
        DB::insert('order_status_history', [
            'order_id' => $orderId,
            'order_item_id' => $itemId,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by_user_id' => $userId,
            'changed_by_customer' => $byCustomer ? 1 : 0,
            'note' => substr($note, 0, 500),
            'ip' => request_ip(),
            'created_at' => now(),
        ]);
    }
}
