<?php
class DepositModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO deposits (user_id, account_id, deposit_method, amount, currency, crypto_type, reference, status) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$data['user_id'], $data['account_id'], $data['deposit_method'], $data['amount'], $data['currency'] ?? 'USD', $data['crypto_type'] ?? null, $data['reference'] ?? 'DEP'.strtoupper(bin2hex(random_bytes(6))), 'pending']);
        return (int)$this->db->lastInsertId();
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT d.*, a.account_number FROM deposits d JOIN accounts a ON d.account_id=a.id WHERE d.user_id=? ORDER BY d.created_at DESC");
        $stmt->execute([$userId]); return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM deposits WHERE id = ?");
        $stmt->execute([$id]); return $stmt->fetch() ?: null;
    }

    public function update(int $id, array $data): bool {
        $f=[]; $v=[];
        foreach($data as $k=>$val){$f[]="$k=?"; $v[]=$val;}
        $v[]=$id;
        $stmt=$this->db->prepare("UPDATE deposits SET ".implode(',',$f)." WHERE id=?");
        return $stmt->execute($v);
    }

    public function getAll(int $limit=50, int $offset=0): array {
        $stmt=$this->db->prepare("SELECT d.*, u.first_name, u.last_name, u.email, a.account_number FROM deposits d JOIN users u ON d.user_id=u.id JOIN accounts a ON d.account_id=a.id ORDER BY d.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit,$offset]); return $stmt->fetchAll();
    }
}
