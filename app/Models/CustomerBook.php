<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

/**
 * The customer book: companies and the people inside them.
 *
 * A customer is an account — usually a company, sometimes a single walk-in person, which is
 * just a company of one. Every mobile number belongs to a contact, and every contact belongs
 * to a customer. That is what lets you open "Tata" and see every job it ever gave, no matter
 * which of its people phoned it in.
 *
 * Phone numbers are unique across contacts, so a number always resolves to exactly one
 * person and one account.
 */
class CustomerBook
{
    /**
     * Every firm this number appears under.
     *
     * One man often runs two firms and phones about both from the same mobile, so a number
     * is not unique any more — it is unique per customer. Callers that need one answer say
     * which firm they mean, or take the only match when there is only one.
     *
     * @return array<int,array<string,mixed>> newest first is not useful here, so oldest
     *         first: the firm he dealt with first is the one he usually means.
     */
    public static function findAllByPhone(?string $rawPhone): array
    {
        $phone = local_phone((string)$rawPhone);
        if (!$phone) {
            return [];
        }
        return DB::all(
            'SELECT cc.*, c.name AS customer_name, c.address, c.gstin, c.is_blocked, c.customer_type
             FROM `' . tbl('customer_contacts') . '` cc
             JOIN `' . tbl('customers') . '` c ON c.id = cc.customer_id
             WHERE cc.phone = ? AND cc.deleted_at IS NULL AND c.deleted_at IS NULL
             ORDER BY cc.id',
            [$phone]
        );
    }

    /**
     * The one contact meant by this number.
     *
     * @param int|null $customerId which firm, when the number is under more than one
     */
    public static function findByPhone(?string $rawPhone, ?int $customerId = null): ?array
    {
        $matches = self::findAllByPhone($rawPhone);
        if (!$matches) {
            return null;
        }
        if ($customerId) {
            foreach ($matches as $m) {
                if ((int)$m['customer_id'] === $customerId) {
                    return $m;
                }
            }
            return null;   // the number is known, but not under the firm that was asked for
        }
        return count($matches) === 1 ? $matches[0] : null;
    }

    /** @return array<int,array<string,mixed>> everyone in this account, primary first */
    public static function contacts(int $customerId): array
    {
        return DB::all(
            'SELECT * FROM `' . tbl('customer_contacts') . '`
             WHERE customer_id = ? AND deleted_at IS NULL
             ORDER BY is_primary DESC, name',
            [$customerId]
        );
    }

    public static function contact(int $contactId): ?array
    {
        return DB::get(
            'SELECT * FROM `' . tbl('customer_contacts') . '` WHERE id = ? AND deleted_at IS NULL',
            [$contactId]
        );
    }

    /**
     * Add a person to an account.
     *
     * @throws \RuntimeException if the number already belongs to somebody
     */
    public static function addContact(int $customerId, array $data): int
    {
        $phone = local_phone((string)($data['phone'] ?? ''));
        if (!$phone) {
            throw new \RuntimeException('A valid 10-digit mobile number is required for a contact.');
        }
        // The same number under another firm is fine and expected — one man, two firms.
        // Twice inside the SAME firm is not.
        foreach (self::findAllByPhone($phone) as $taken) {
            if ((int)$taken['customer_id'] === $customerId) {
                throw new \RuntimeException(
                    $phone . ' is already listed here, under ' . $taken['name'] . '.'
                );
            }
        }
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            // No person named — fall back to the account's own name so the row is never blank.
            $name = (string)DB::val('SELECT name FROM `' . tbl('customers') . '` WHERE id = ?', [$customerId]);
        }
        $hasPrimary = DB::val(
            'SELECT id FROM `' . tbl('customer_contacts') . '`
             WHERE customer_id = ? AND is_primary = 1 AND deleted_at IS NULL LIMIT 1',
            [$customerId]
        );
        return DB::insert('customer_contacts', [
            'customer_id' => $customerId,
            'name' => $name,
            'phone' => $phone,
            'whatsapp' => local_phone((string)($data['whatsapp'] ?? '')) ?: $phone,
            'email' => ($data['email'] ?? '') ?: null,
            'designation' => ($data['designation'] ?? '') ?: null,
            'is_primary' => $hasPrimary ? 0 : 1,
            'notes' => ($data['notes'] ?? '') ?: null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function updateContact(int $contactId, array $data): void
    {
        $contact = self::contact($contactId);
        if (!$contact) {
            throw new \RuntimeException('Contact not found.');
        }
        $update = ['updated_at' => now()];
        if (isset($data['phone'])) {
            $phone = local_phone((string)$data['phone']);
            if (!$phone) {
                throw new \RuntimeException('A valid 10-digit mobile number is required.');
            }
            if ($phone !== (string)$contact['phone']) {
                foreach (self::findAllByPhone($phone) as $taken) {
                    if ((int)$taken['customer_id'] === (int)$contact['customer_id']) {
                        throw new \RuntimeException(
                            $phone . ' is already listed here, under ' . $taken['name'] . '.'
                        );
                    }
                }
                $update['phone'] = $phone;
            }
        }
        foreach (['name', 'designation', 'email', 'notes'] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = trim((string)$data[$field]) ?: null;
            }
        }
        if (array_key_exists('whatsapp', $data)) {
            $update['whatsapp'] = local_phone((string)$data['whatsapp']) ?: ($update['phone'] ?? $contact['phone']);
        }
        if (($update['name'] ?? '!') === null) {
            unset($update['name']);   // a contact must keep a name
        }
        DB::update('customer_contacts', $update, ['id' => $contactId]);
        self::syncPrimaryToCustomer($contactId);
    }

    /** Make this the contact used when an order does not name anybody. */
    public static function makePrimary(int $contactId): void
    {
        $contact = self::contact($contactId);
        if (!$contact) {
            return;
        }
        DB::run(
            'UPDATE `' . tbl('customer_contacts') . '` SET is_primary = 0 WHERE customer_id = ?',
            [(int)$contact['customer_id']]
        );
        DB::update('customer_contacts', ['is_primary' => 1, 'updated_at' => now()], ['id' => $contactId]);
        self::syncPrimaryToCustomer($contactId);
    }

    /**
     * Remove a person. The last one standing cannot go — an account with no number could
     * never be found again.
     */
    public static function removeContact(int $contactId): void
    {
        $contact = self::contact($contactId);
        if (!$contact) {
            return;
        }
        $left = (int)DB::val(
            'SELECT COUNT(*) FROM `' . tbl('customer_contacts') . '`
             WHERE customer_id = ? AND deleted_at IS NULL',
            [(int)$contact['customer_id']]
        );
        if ($left <= 1) {
            throw new \RuntimeException('This is the only contact — a customer must keep at least one number.');
        }
        DB::update('customer_contacts', ['deleted_at' => now()], ['id' => $contactId]);
        if ((int)$contact['is_primary'] === 1) {
            $next = DB::val(
                'SELECT id FROM `' . tbl('customer_contacts') . '`
                 WHERE customer_id = ? AND deleted_at IS NULL ORDER BY id LIMIT 1',
                [(int)$contact['customer_id']]
            );
            if ($next) {
                self::makePrimary((int)$next);
            }
        }
    }

    /**
     * customers.phone is still the account's own number and is used all over the system
     * (WhatsApp, tracking, the public site), so it follows whoever is primary.
     */
    private static function syncPrimaryToCustomer(int $contactId): void
    {
        $contact = self::contact($contactId);
        if (!$contact || (int)$contact['is_primary'] !== 1) {
            return;
        }
        DB::update('customers', [
            'phone' => $contact['phone'],
            'whatsapp' => $contact['whatsapp'] ?: $contact['phone'],
            'updated_at' => now(),
        ], ['id' => (int)$contact['customer_id']]);
    }

    /** Companies matching a typed name, for the "add to an existing customer" picker. */
    public static function search(string $term, int $limit = 20): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }
        return DB::all(
            'SELECT c.id, c.name, c.phone,
                    (SELECT COUNT(*) FROM `' . tbl('customer_contacts') . '` cc
                     WHERE cc.customer_id = c.id AND cc.deleted_at IS NULL) AS contact_count
             FROM `' . tbl('customers') . '` c
             WHERE c.deleted_at IS NULL AND c.name LIKE ?
             ORDER BY c.name LIMIT ' . (int)$limit,
            ['%' . $term . '%']
        );
    }
}
