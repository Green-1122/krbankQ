<?php
/**
 * KrBank - Account Model
 */

class AccountModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM accounts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByAccountNumber(string $number): ?array {
        $stmt = $this->db->prepare("SELECT * FROM accounts WHERE account_number = ?");
        $stmt->execute([$number]);
        return $stmt->fetch() ?: null;
    }

    public function getByUserId(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM accounts WHERE user_id = ? ORDER BY is_primary DESC, created_at ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getPrimary(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM accounts WHERE user_id = ? AND is_primary = 1 LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $acctNum = generateAccountNumber();
        $stmt = $this->db->prepare("
            INSERT INTO accounts (user_id, account_number, account_type, account_name, balance, available_balance, currency, is_primary, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['user_id'],
            $acctNum,
            $data['account_type'] ?? 'checking',
            $data['account_name'] ?? ucfirst($data['account_type'] ?? 'checking') . ' Account',
            $data['balance'] ?? 0.00,
            $data['balance'] ?? 0.00,
            $data['currency'] ?? 'USD',
            $data['is_primary'] ?? 0,
            $data['created_at'] ?? date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateBalance(int $id, float $amount, string $type = 'credit'): bool {
        $op = $type === 'credit' ? '+' : '-';
        $stmt = $this->db->prepare("
            UPDATE accounts SET balance = balance $op ?, available_balance = available_balance $op ?, updated_at = NOW() WHERE id = ?
        ");
        return $stmt->execute([$amount, $amount, $id]);
    }

    public function setBalance(int $id, float $balance): bool {
        $stmt = $this->db->prepare("UPDATE accounts SET balance = ?, available_balance = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$balance, $balance, $id]);
    }

    public function getTotalBalance(int $userId): float {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(balance), 0) FROM accounts WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$userId]);
        return (float) $stmt->fetchColumn();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE accounts SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM accounts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function countByUser(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM accounts WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}
