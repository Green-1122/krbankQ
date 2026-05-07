<?php
class CardModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM cards WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT c.*, a.account_number FROM cards c JOIN accounts a ON c.account_id = a.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $cardNum = generateCardNumber($data['card_network'] ?? 'visa');
        $masked = '****' . substr($cardNum, -4);
        $cvv = str_pad((string)random_int(0, 999), 3, '0', STR_PAD_LEFT);
        $stmt = $this->db->prepare("INSERT INTO cards (user_id, account_id, card_number, card_number_masked, card_type, card_network, cardholder_name, expiry_month, expiry_year, cvv, spending_limit, daily_limit, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'active')");
        $stmt->execute([$data['user_id'], $data['account_id'], $cardNum, $masked, $data['card_type'] ?? 'virtual', $data['card_network'] ?? 'visa', $data['cardholder_name'], (int)date('m'), (int)date('Y')+5, $cvv, $data['spending_limit'] ?? 5000, $data['daily_limit'] ?? 2000]);
        return (int)$this->db->lastInsertId();
    }

    public function toggleFreeze(int $id): bool {
        $card = $this->findById($id);
        if (!$card) return false;
        $frozen = $card['is_frozen'] ? 0 : 1;
        $status = $frozen ? 'frozen' : 'active';
        $stmt = $this->db->prepare("UPDATE cards SET is_frozen = ?, status = ? WHERE id = ?");
        return $stmt->execute([$frozen, $status, $id]);
    }

    public function update(int $id, array $data): bool {
        $f = []; $v = [];
        foreach ($data as $k => $val) { $f[] = "$k = ?"; $v[] = $val; }
        $v[] = $id;
        $stmt = $this->db->prepare("UPDATE cards SET " . implode(', ', $f) . " WHERE id = ?");
        return $stmt->execute($v);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE cards SET status = 'cancelled' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function countByUser(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM cards WHERE user_id = ? AND status != 'cancelled'");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}
