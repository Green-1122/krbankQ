<?php
/**
 * KrBank - Transfer Model
 */

class TransferModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM transfers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO transfers (user_id, from_account_id, transfer_type, recipient_name, recipient_email, recipient_account, 
            recipient_bank, recipient_bank_code, swift_code, routing_number, iban, wallet_address, crypto_type,
            amount, fee, currency, cot_code, imf_code, tax_code, require_cot, require_imf, require_tax, description, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['user_id'], $data['from_account_id'], $data['transfer_type'],
            $data['recipient_name'], $data['recipient_email'] ?? null, $data['recipient_account'] ?? null,
            $data['recipient_bank'] ?? null, $data['recipient_bank_code'] ?? null,
            $data['swift_code'] ?? null, $data['routing_number'] ?? null, $data['iban'] ?? null,
            $data['wallet_address'] ?? null, $data['crypto_type'] ?? null,
            $data['amount'], $data['fee'] ?? 0, $data['currency'] ?? 'USD',
            $data['cot_code'] ?? null, $data['imf_code'] ?? null, $data['tax_code'] ?? null,
            $data['require_cot'] ?? 0, $data['require_imf'] ?? 0, $data['require_tax'] ?? 0,
            $data['description'] ?? null, $data['status'] ?? 'pending',
            $data['created_at'] ?? date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByUser(int $userId, int $limit = 20, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT tf.*, a.account_number FROM transfers tf 
            JOIN accounts a ON tf.from_account_id = a.id 
            WHERE tf.user_id = ? ORDER BY tf.created_at DESC LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE transfers SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public function getAll(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT tf.*, u.first_name, u.last_name, u.email, a.account_number
            FROM transfers tf 
            JOIN users u ON tf.user_id = u.id
            JOIN accounts a ON tf.from_account_id = a.id
            ORDER BY tf.created_at DESC LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
}
