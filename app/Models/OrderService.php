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
     *  customer: [id?|phone,name,whatsapp,address,city,gstin]
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

            // 1. Customer
            $customerId = self::upsertCustomer($payload['customer'], $branchId, $userId, $source);
            $customer = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE id = ?', [$customerId]);
            $groupDiscount = 0.0;
            if ($customer['price_group_id']) {
                $groupDiscount = (float)(DB::val(
                    'SELECT discount_percent FROM `' . tbl('price_groups') . '` WHERE id = ?',
                    [(int)$customer['price_group_id']]
                ) ?? 0);
            }

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
                'source' => $source,
                'taken_by_user_id' => $userId,
                'order_date' => $orderDate,
                'priority' => $payload['priority'] ?? 'normal',
                'status' => 'design_pending', // recomputed from the item lines below
                'needs_review' => ($source === 'public' && !Settings::getBool('public_order_auto_confirm')) ? 1 : 0,
                'delivery_type' => $payload['delivery_type'] ?? 'pickup',
                'delivery_address' => $payload['delivery_address'] ?? null,
                'customer_note' => $payload['customer_note'] ?? null,
                'internal_note' => $payload['internal_note'] ?? null,
                'discount_type' => $payload['discount_type'] ?? null,
                'discount_value' => (float)($payload['discount_value'] ?? 0),
                'delivery_charge' => (float)($payload['delivery_charge'] ?? 0),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $orderDue = null;
            foreach (array_values($payload['items']) as $index => $line) {
                $item = DB::get(
                    'SELECT i.*, c.name AS category_name FROM `' . tbl('items') . '` i
                     JOIN `' . tbl('categories') . '` c ON c.id = i.category_id
                     WHERE i.id = ? AND i.is_active = 1 AND i.deleted_at IS NULL',
                    [(int)$line['item_id']]
                );
                if (!$item) {
                    throw new \RuntimeException('Item not found or inactive.');
                }
                $qty = max((float)$item['min_qty'], (float)($line['qty'] ?? 1));
                $spec = is_array($line['spec'] ?? null) ? $line['spec'] : [];
                $calc = Pricing::calculate($item, $qty, $spec, $groupDiscount);

                $rate = $calc['rate'];
                $overridden = 0;
                if ($source === 'counter' && isset($line['rate']) && $line['rate'] !== ''
                    && abs((float)$line['rate'] - $rate) > 0.009) {
                    $rate = round((float)$line['rate'], 2);
                    $overridden = 1;
                }
                $amount = $item['pricing_type'] === 'fixed' ? $rate : round($rate * $calc['billed_qty'], 2);
                $taxPercent = (float)$item['tax_percent'];
                $taxAmount = round($amount * $taxPercent / 100, 2);

                $dueDate = !empty($line['due_date'])
                    ? date('Y-m-d H:i:s', (int)strtotime((string)$line['due_date']))
                    : date('Y-m-d H:i:s', $baseTs + (int)$item['default_turnaround_hours'] * 3600);
                if ($orderDue === null || $dueDate > $orderDue) {
                    $orderDue = $dueDate;
                }

                $requiresDesign = (int)$item['requires_design'];
                $designerId = !empty($line['designer_id']) ? (int)$line['designer_id'] : null;
                $status = $requiresDesign
                    ? ($designerId ? 'design_pending' : 'design_pending')
                    : 'ready_for_print';

                $itemRowId = DB::insert('order_items', [
                    'order_id' => $orderId,
                    'item_id' => (int)$item['id'],
                    'item_name_snapshot' => $item['name'],
                    'category_name_snapshot' => $item['category_name'],
                    'qty' => $calc['billed_qty'],
                    'unit' => $item['unit'],
                    'rate' => $rate,
                    'rate_overridden' => $overridden,
                    'spec_json' => json_encode($spec, JSON_UNESCAPED_UNICODE),
                    'spec_text' => $calc['spec_text'],
                    'amount' => $amount,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'line_total' => round($amount + $taxAmount, 2),
                    'requires_design' => $requiresDesign,
                    'assigned_designer_id' => $requiresDesign ? $designerId : null,
                    'designer_assigned_at' => ($requiresDesign && $designerId) ? now() : null,
                    'status' => $status,
                    'due_date' => $dueDate,
                    'special_instructions' => $line['special_instructions'] ?? null,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                self::history($orderId, $itemRowId, null, $status, $userId, $source === 'public', 'Order created');

                if ($overridden) {
                    Logger::activity('order', 'rate_override', 'order_item', $itemRowId,
                        "Rate overridden on $jobNo: system ₹{$calc['rate']} → entered ₹$rate");
                }
                // 5. Auto-assign designer when enabled and none picked
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
            $customer = DB::get('SELECT price_group_id FROM `' . tbl('customers') . '` WHERE id = ?', [(int)$order['customer_id']]);
            $groupDiscount = ($customer && $customer['price_group_id'])
                ? (float)(DB::val('SELECT discount_percent FROM `' . tbl('price_groups') . '` WHERE id = ?', [(int)$customer['price_group_id']]) ?? 0)
                : 0.0;

            $existing = DB::all('SELECT * FROM `' . tbl('order_items') . '` WHERE order_id = ?', [$orderId]);
            $existingById = [];
            foreach ($existing as $e) {
                $existingById[(int)$e['id']] = $e;
            }
            $keptIds = [];
            $newDesigner = [];

            foreach (array_values($items) as $index => $line) {
                $lineId = isset($line['id']) ? (int)$line['id'] : 0;

                if ($lineId && isset($existingById[$lineId])) {
                    // Manual correction of an existing line — respect the entered qty/rate as-is.
                    $ex = $existingById[$lineId];
                    if ($ex['status'] === 'cancelled') {
                        $keptIds[] = $lineId; // leave cancelled lines untouched
                        continue;
                    }
                    $qty = max(0.01, (float)($line['qty'] ?? $ex['qty']));
                    $rate = isset($line['rate']) && $line['rate'] !== '' ? round((float)$line['rate'], 2) : (float)$ex['rate'];
                    $isFixed = (int)DB::val('SELECT COUNT(*) FROM `' . tbl('items') . "` WHERE id = ? AND pricing_type = 'fixed'", [(int)$ex['item_id']]) > 0;
                    $amount = $isFixed ? $rate : round($rate * $qty, 2);
                    $taxPercent = (float)$ex['tax_percent'];
                    $taxAmount = round($amount * $taxPercent / 100, 2);
                    $changed = abs($rate - (float)$ex['rate']) > 0.009 || abs($qty - (float)$ex['qty']) > 0.0001;
                    DB::update('order_items', [
                        'qty' => $qty,
                        'rate' => $rate,
                        'rate_overridden' => $changed ? 1 : (int)$ex['rate_overridden'],
                        'amount' => $amount,
                        'tax_amount' => $taxAmount,
                        'line_total' => round($amount + $taxAmount, 2),
                        'sort_order' => $index,
                        'updated_at' => now(),
                    ], ['id' => $lineId]);
                    if ($changed) {
                        Logger::activity('order', 'edit_item', 'order_item', $lineId,
                            $order['job_no'] . ': line edited → qty ' . rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.') . ' × ₹' . $rate);
                    }
                    $keptIds[] = $lineId;
                    continue;
                }

                // Brand-new line — price it the same way createOrder() does.
                $item = DB::get(
                    'SELECT i.*, c.name AS category_name FROM `' . tbl('items') . '` i
                     JOIN `' . tbl('categories') . '` c ON c.id = i.category_id
                     WHERE i.id = ? AND i.is_active = 1 AND i.deleted_at IS NULL',
                    [(int)($line['item_id'] ?? 0)]
                );
                if (!$item) {
                    throw new \RuntimeException('Item not found or inactive.');
                }
                $qty = max((float)$item['min_qty'], (float)($line['qty'] ?? 1));
                $spec = is_array($line['spec'] ?? null) ? $line['spec'] : [];
                $calc = Pricing::calculate($item, $qty, $spec, $groupDiscount);
                $rate = $calc['rate'];
                $overridden = 0;
                if (isset($line['rate']) && $line['rate'] !== '' && abs((float)$line['rate'] - $rate) > 0.009) {
                    $rate = round((float)$line['rate'], 2);
                    $overridden = 1;
                }
                $amount = $item['pricing_type'] === 'fixed' ? $rate : round($rate * $calc['billed_qty'], 2);
                $taxPercent = (float)$item['tax_percent'];
                $taxAmount = round($amount * $taxPercent / 100, 2);
                $dueDate = !empty($line['due_date'])
                    ? date('Y-m-d H:i:s', (int)strtotime((string)$line['due_date']))
                    : date('Y-m-d H:i:s', time() + (int)$item['default_turnaround_hours'] * 3600);
                $requiresDesign = (int)$item['requires_design'];
                $designerId = !empty($line['designer_id']) ? (int)$line['designer_id'] : null;
                $status = $requiresDesign ? 'design_pending' : 'ready_for_print';
                $newId = DB::insert('order_items', [
                    'order_id' => $orderId,
                    'item_id' => (int)$item['id'],
                    'item_name_snapshot' => $item['name'],
                    'category_name_snapshot' => $item['category_name'],
                    'qty' => $calc['billed_qty'],
                    'unit' => $item['unit'],
                    'rate' => $rate,
                    'rate_overridden' => $overridden,
                    'spec_json' => json_encode($spec, JSON_UNESCAPED_UNICODE),
                    'spec_text' => $calc['spec_text'],
                    'amount' => $amount,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'line_total' => round($amount + $taxAmount, 2),
                    'requires_design' => $requiresDesign,
                    'assigned_designer_id' => $requiresDesign ? $designerId : null,
                    'designer_assigned_at' => ($requiresDesign && $designerId) ? now() : null,
                    'status' => $status,
                    'due_date' => $dueDate,
                    'special_instructions' => $line['special_instructions'] ?? null,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                self::history($orderId, $newId, null, $status, $userId, false, 'Item added on edit');
                Logger::activity('order', 'add_item', 'order_item', $newId, $order['job_no'] . ': line added — ' . $item['name']);
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

    private static function upsertCustomer(array $c, int $branchId, ?int $userId, string $source): int
    {
        if (!empty($c['id'])) {
            $existing = DB::get('SELECT id FROM `' . tbl('customers') . '` WHERE id = ? AND deleted_at IS NULL', [(int)$c['id']]);
            if ($existing) {
                return (int)$existing['id'];
            }
        }
        $phone = local_phone($c['phone'] ?? '');
        if (!$phone) {
            throw new \RuntimeException('A valid 10-digit customer mobile number is required.');
        }
        $existing = DB::get('SELECT * FROM `' . tbl('customers') . '` WHERE phone = ? AND deleted_at IS NULL', [$phone]);
        if ($existing) {
            if ((int)$existing['is_blocked'] === 1) {
                throw new \RuntimeException('This customer is blocked. Please contact the manager.');
            }
            $updates = [];
            foreach (['name', 'address', 'city', 'gstin', 'email'] as $field) {
                if (!empty($c[$field]) && (string)$c[$field] !== (string)$existing[$field]) {
                    $updates[$field] = trim((string)$c[$field]);
                }
            }
            if ($updates) {
                $updates['updated_at'] = now();
                DB::update('customers', $updates, ['id' => $existing['id']]);
            }
            return (int)$existing['id'];
        }
        if (empty($c['name'])) {
            throw new \RuntimeException('Customer name is required for a new customer.');
        }
        return DB::insert('customers', [
            'name' => trim((string)$c['name']),
            'phone' => $phone,
            'whatsapp' => local_phone($c['whatsapp'] ?? '') ?: $phone,
            'email' => $c['email'] ?? null,
            'address' => $c['address'] ?? null,
            'city' => $c['city'] ?? null,
            'pincode' => $c['pincode'] ?? null,
            'gstin' => $c['gstin'] ?? null,
            'source' => $source === 'public' ? 'public' : 'counter',
            'created_by' => $userId,
            'branch_id' => $branchId,
            'is_verified' => $source === 'public' ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function recalcTotals(int $orderId): void
    {
        $order = DB::get('SELECT * FROM `' . tbl('orders') . '` WHERE id = ?', [$orderId]);
        $sums = DB::get(
            "SELECT COALESCE(SUM(amount),0) AS subtotal, COALESCE(SUM(tax_amount),0) AS tax
             FROM `" . tbl('order_items') . "` WHERE order_id = ? AND status <> 'cancelled'",
            [$orderId]
        );
        $subtotal = (float)$sums['subtotal'];
        $discount = 0.0;
        if ($order['discount_type'] === 'percent') {
            $discount = round($subtotal * (float)$order['discount_value'] / 100, 2);
        } elseif ($order['discount_type'] === 'flat') {
            $discount = min($subtotal, (float)$order['discount_value']);
        }
        $tax = (float)$sums['tax'];
        $delivery = (float)$order['delivery_charge'];
        $raw = $subtotal - $discount + $tax + $delivery;
        $total = round($raw);
        $roundOff = round($total - $raw, 2);

        DB::update('orders', [
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total' => $total,
            'round_off' => $roundOff,
            'balance_amount' => $total - (float)$order['paid_amount'],
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
        $designer = DB::get(
            'SELECT u.* FROM `' . tbl('users') . '` u JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
             WHERE u.id = ? AND r.slug = ? AND u.is_active = 1 AND u.deleted_at IS NULL',
            [$designerId, 'designer']
        );
        if (!$designer) {
            return ['ok' => false, 'error' => 'Designer not found.'];
        }
        DB::update('order_items', [
            'assigned_designer_id' => $designerId,
            'designer_assigned_at' => now(),
            'updated_at' => now(),
        ], ['id' => $itemId]);
        Logger::activity('order', 'assign', 'order_item', $itemId, 'Assigned designer ' . $designer['name']);
        WaEvents::designerAssigned($itemId);
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
             JOIN `' . tbl('roles') . '` r ON r.id = u.role_id
             WHERE r.slug = ? AND u.is_active = 1 AND u.deleted_at IS NULL
             ORDER BY open_jobs ASC, last_assigned ASC, u.id ASC',
            ['designer']
        );
        foreach ($designers as $designer) {
            $capacity = $designer['designer_capacity'] !== null ? (int)$designer['designer_capacity'] : PHP_INT_MAX;
            if ((int)$designer['open_jobs'] < $capacity) {
                DB::update('order_items', [
                    'assigned_designer_id' => (int)$designer['id'],
                    'designer_assigned_at' => now(),
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
