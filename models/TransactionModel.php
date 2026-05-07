<?php
/**
 * KrBank - Transaction Model
 */

class TransactionModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT t.*, a.account_number FROM transactions t JOIN accounts a ON t.account_id = a.id WHERE t.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $ref = 'TXN' . strtoupper(bin2hex(random_bytes(8)));
        $stmt = $this->db->prepare("
            INSERT INTO transactions (user_id, account_id, transaction_ref, type, category, amount, balance_after, currency, description, recipient_name, recipient_account, recipient_bank, status, metadata, transaction_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['user_id'],
            $data['account_id'],
            $ref,
            $data['type'],
            $data['category'] ?? 'other',
            $data['amount'],
            $data['balance_after'] ?? 0,
            $data['currency'] ?? 'USD',
            $data['description'] ?? null,
            $data['recipient_name'] ?? null,
            $data['recipient_account'] ?? null,
            $data['recipient_bank'] ?? null,
            $data['status'] ?? 'completed',
            isset($data['metadata']) ? json_encode($data['metadata']) : null,
            $data['transaction_date'] ?? date('Y-m-d H:i:s'),
            $data['created_at'] ?? date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByUser(int $userId, int $limit = 20, int $offset = 0, array $filters = []): array {
        $sql = "SELECT t.*, a.account_number, a.account_type FROM transactions t JOIN accounts a ON t.account_id = a.id WHERE t.user_id = ?";
        $params = [$userId];

        if (!empty($filters['account_id'])) {
            $sql .= " AND t.account_id = ?";
            $params[] = $filters['account_id'];
        }
        if (!empty($filters['type'])) {
            $sql .= " AND t.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND t.category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (t.description LIKE ? OR t.recipient_name LIKE ? OR t.transaction_ref LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY t.transaction_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByUser(int $userId, array $filters = []): int {
        $sql = "SELECT COUNT(*) FROM transactions t WHERE t.user_id = ?";
        $params = [$userId];

        if (!empty($filters['account_id'])) { $sql .= " AND t.account_id = ?"; $params[] = $filters['account_id']; }
        if (!empty($filters['type'])) { $sql .= " AND t.type = ?"; $params[] = $filters['type']; }
        if (!empty($filters['category'])) { $sql .= " AND t.category = ?"; $params[] = $filters['category']; }
        if (!empty($filters['date_from'])) { $sql .= " AND t.transaction_date >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= " AND t.transaction_date <= ?"; $params[] = $filters['date_to'] . ' 23:59:59'; }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getRecentByUser(int $userId, int $limit = 5): array {
        $stmt = $this->db->prepare("
            SELECT t.*, a.account_number FROM transactions t 
            JOIN accounts a ON t.account_id = a.id 
            WHERE t.user_id = ? ORDER BY t.transaction_date DESC LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function getMonthlyTotals(int $userId, int $months = 6): array {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(transaction_date, '%Y-%m') as month,
                SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as expenses
            FROM transactions 
            WHERE user_id = ? AND transaction_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
            ORDER BY month ASC
        ");
        $stmt->execute([$userId, $months]);
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
        $stmt = $this->db->prepare("UPDATE transactions SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public function getAll(int $limit = 50, int $offset = 0, string $search = ''): array {
        $sql = "SELECT t.*, a.account_number, u.first_name, u.last_name, u.email 
                FROM transactions t 
                JOIN accounts a ON t.account_id = a.id 
                JOIN users u ON t.user_id = u.id";
        $params = [];
        if ($search) {
            $sql .= " WHERE (t.transaction_ref LIKE ? OR t.description LIKE ? OR u.email LIKE ? OR u.first_name LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
        }
        $sql .= " ORDER BY t.transaction_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
