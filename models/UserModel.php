<?php
/**
 * KrBank - User Model
 * Handles all user-related database operations
 */

class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findByUuid(string $uuid): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE uuid = ?");
        $stmt->execute([$uuid]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $uuid = $this->generateUuid();
        $stmt = $this->db->prepare("
            INSERT INTO users (uuid, first_name, last_name, email, phone, password_hash, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([
            $uuid,
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'] ?? null,
            password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public function updateLastLogin(int $id): void {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function incrementLoginAttempts(int $id): void {
        $stmt = $this->db->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function lockAccount(int $id): void {
        $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
        $stmt = $this->db->prepare("UPDATE users SET locked_until = ?, status = 'locked' WHERE id = ?");
        $stmt->execute([$lockUntil, $id]);
    }

    public function isLocked(array $user): bool {
        if ($user['status'] === 'locked' && $user['locked_until']) {
            if (strtotime($user['locked_until']) > time()) {
                return true;
            }
            // Unlock if time has passed
            $this->update($user['id'], ['status' => 'active', 'locked_until' => null, 'login_attempts' => 0]);
        }
        return false;
    }

    public function setResetToken(int $id, string $token): void {
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $stmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $id]);
    }

    public function findByResetToken(string $token): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function resetPassword(int $id, string $password): void {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hash, $id]);
    }

    public function getAll(int $limit = 50, int $offset = 0, string $search = ''): array {
        $sql = "SELECT * FROM users WHERE role = 'user'";
        $params = [];
        if ($search) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int {
        $sql = "SELECT COUNT(*) FROM users WHERE role = 'user'";
        $params = [];
        if ($search) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        return $stmt->execute([$id]);
    }

    public function updatePin(int $id, string $pin): void {
        $hash = password_hash($pin, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $stmt = $this->db->prepare("UPDATE users SET pin = ?, pin_active = 1 WHERE id = ?");
        $stmt->execute([$hash, $id]);
    }

    public function deactivatePin(int $id): void {
        $stmt = $this->db->prepare("UPDATE users SET pin_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function verifyPin(int $id, string $pin): bool {
        $user = $this->findById($id);
        return $user && $user['pin'] && password_verify($pin, $user['pin']);
    }

    private function generateUuid(): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
