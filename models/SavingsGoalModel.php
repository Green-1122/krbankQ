<?php
class SavingsGoalModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT sg.*, a.account_number FROM savings_goals sg JOIN accounts a ON sg.account_id=a.id WHERE sg.user_id=? ORDER BY sg.created_at DESC");
        $stmt->execute([$userId]); return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM savings_goals WHERE id = ?");
        $stmt->execute([$id]); return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO savings_goals (user_id, account_id, goal_name, target_amount, category, target_date, auto_save_amount, auto_save_frequency) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$data['user_id'], $data['account_id'], $data['goal_name'], $data['target_amount'], $data['category'] ?? 'custom', $data['target_date'] ?? null, $data['auto_save_amount'] ?? 0, $data['auto_save_frequency'] ?? 'monthly']);
        return (int)$this->db->lastInsertId();
    }

    public function addFunds(int $id, float $amount): bool {
        $stmt = $this->db->prepare("UPDATE savings_goals SET current_amount = current_amount + ? WHERE id = ?");
        return $stmt->execute([$amount, $id]);
    }

    public function update(int $id, array $data): bool {
        $f=[]; $v=[];
        foreach($data as $k=>$val){$f[]="$k=?"; $v[]=$val;}
        $v[]=$id;
        $stmt=$this->db->prepare("UPDATE savings_goals SET ".implode(',',$f)." WHERE id=?");
        return $stmt->execute($v);
    }
}
