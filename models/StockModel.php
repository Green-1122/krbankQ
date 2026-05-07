<?php
class StockModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getAllStocks(): array {
        $stmt = $this->db->prepare("SELECT * FROM stocks ORDER BY symbol ASC");
        $stmt->execute(); return $stmt->fetchAll();
    }

    public function findStock(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM stocks WHERE id = ?");
        $stmt->execute([$id]); return $stmt->fetch() ?: null;
    }

    public function getUserPortfolio(int $userId): array {
        $stmt = $this->db->prepare("SELECT us.*, s.symbol, s.company_name, s.current_price, s.previous_close FROM user_stocks us JOIN stocks s ON us.stock_id=s.id WHERE us.user_id=? ORDER BY us.created_at DESC");
        $stmt->execute([$userId]); return $stmt->fetchAll();
    }

    public function buyStock(int $userId, int $stockId, int $accountId, float $shares, float $price): int {
        $total = round($shares * $price, 2);
        // Check existing holding
        $stmt = $this->db->prepare("SELECT * FROM user_stocks WHERE user_id=? AND stock_id=? AND account_id=?");
        $stmt->execute([$userId, $stockId, $accountId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $newShares = $existing['shares'] + $shares;
            $newTotal = $existing['total_invested'] + $total;
            $newAvg = $newTotal / $newShares;
            $stmt = $this->db->prepare("UPDATE user_stocks SET shares=?, avg_buy_price=?, total_invested=? WHERE id=?");
            $stmt->execute([$newShares, round($newAvg,2), $newTotal, $existing['id']]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO user_stocks (user_id, stock_id, account_id, shares, avg_buy_price, total_invested) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$userId, $stockId, $accountId, $shares, $price, $total]);
        }

        $stmt = $this->db->prepare("INSERT INTO stock_trades (user_id, stock_id, account_id, trade_type, shares, price_per_share, total_amount, status) VALUES (?,?,?,'buy',?,?,?,'completed')");
        $stmt->execute([$userId, $stockId, $accountId, $shares, $price, $total]);
        return (int)$this->db->lastInsertId();
    }

    public function sellStock(int $userId, int $stockId, int $accountId, float $shares, float $price): bool {
        $stmt = $this->db->prepare("SELECT * FROM user_stocks WHERE user_id=? AND stock_id=? AND account_id=?");
        $stmt->execute([$userId, $stockId, $accountId]);
        $holding = $stmt->fetch();
        if (!$holding || $holding['shares'] < $shares) return false;

        $newShares = $holding['shares'] - $shares;
        $total = round($shares * $price, 2);
        if ($newShares <= 0) {
            $stmt = $this->db->prepare("DELETE FROM user_stocks WHERE id=?");
            $stmt->execute([$holding['id']]);
        } else {
            $newInvested = $holding['total_invested'] - ($holding['avg_buy_price'] * $shares);
            $stmt = $this->db->prepare("UPDATE user_stocks SET shares=?, total_invested=? WHERE id=?");
            $stmt->execute([$newShares, max(0, $newInvested), $holding['id']]);
        }

        $stmt = $this->db->prepare("INSERT INTO stock_trades (user_id, stock_id, account_id, trade_type, shares, price_per_share, total_amount, status) VALUES (?,?,?,'sell',?,?,?,'completed')");
        $stmt->execute([$userId, $stockId, $accountId, $shares, $price, $total]);
        return true;
    }

    public function getTrades(int $userId, int $limit=20): array {
        $stmt = $this->db->prepare("SELECT st.*, s.symbol, s.company_name FROM stock_trades st JOIN stocks s ON st.stock_id=s.id WHERE st.user_id=? ORDER BY st.created_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]); return $stmt->fetchAll();
    }
}
