<?php
class NotificationModel {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function create(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): int {
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?,?,?,?,?)");
        $stmt->execute([$userId, $title, $message, $type, $link]);
        return (int)$this->db->lastInsertId();
    }

    public function getByUser(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]); return $stmt->fetchAll();
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
        $stmt->execute([$userId]); return (int)$stmt->fetchColumn();
    }

    public function markRead(int $id): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read=1 WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function markAllRead(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
        return $stmt->execute([$userId]);
    }
}
