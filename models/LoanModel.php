<?php
class LoanModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM loans WHERE id = ?");
        $stmt->execute([$id]); return $stmt->fetch() ?: null;
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]); return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $loanNum = 'LN' . date('y') . str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);
        $stmt = $this->db->prepare("INSERT INTO loans (user_id, account_id, loan_type, loan_number, amount_requested, interest_rate, term_months, purpose, status) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$data['user_id'], $data['account_id'], $data['loan_type'], $loanNum, $data['amount'], $data['interest_rate'] ?? 5.50, $data['term_months'] ?? 12, $data['purpose'] ?? null, 'pending']);
        return (int)$this->db->lastInsertId();
    }

    public function approve(int $id, float $amount, float $rate, int $months): bool {
        $monthly = ($amount * ($rate/100/12)) / (1 - pow(1 + ($rate/100/12), -$months));
        $stmt = $this->db->prepare("UPDATE loans SET status='active', amount_approved=?, interest_rate=?, term_months=?, monthly_payment=?, remaining_balance=?, approved_at=NOW(), next_payment_date=DATE_ADD(NOW(), INTERVAL 1 MONTH) WHERE id=?");
        return $stmt->execute([$amount, $rate, $months, round($monthly,2), $amount, $id]);
    }

    public function update(int $id, array $data): bool {
        $f=[]; $v=[];
        foreach($data as $k=>$val){$f[]="$k=?";$v[]=$val;}
        $v[]=$id;
        $stmt=$this->db->prepare("UPDATE loans SET ".implode(',',$f)." WHERE id=?");
        return $stmt->execute($v);
    }

    public function getAll(int $limit=50, int $offset=0): array {
        $stmt=$this->db->prepare("SELECT l.*, u.first_name, u.last_name, u.email FROM loans l JOIN users u ON l.user_id=u.id ORDER BY l.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit,$offset]); return $stmt->fetchAll();
    }
}
