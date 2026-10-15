<?php

class ChatModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureMessageActionColumns();
        $this->ensureAccountChatJoinColumn();
    }

    private function ensureMessageActionColumns(): void
    {
        $columns = [
            'reply_to_id' => 'INT NULL',
            'reply_text'  => 'VARCHAR(255) NULL',
            'reaction'    => 'VARCHAR(20) NULL',
            'pinned'      => 'TINYINT(1) NOT NULL DEFAULT 0',
        ];

        foreach ($columns as $column => $definition) {
            if (!$this->columnExists($column)) {
                $this->db->exec("ALTER TABLE chat_messages ADD COLUMN {$column} {$definition}");
            }
        }
    }

    private function columnExists(string $column): bool
    {
        $s = $this->db->prepare("SHOW COLUMNS FROM chat_messages LIKE ?");
        $s->execute([$column]);
        return (bool)$s->fetch();
    }

    private function ensureAccountChatJoinColumn(): void
    {
        $s = $this->db->prepare("SHOW COLUMNS FROM account LIKE ?");
        $s->execute(['chat_joined_at']);
        if (!$s->fetch()) {
            $this->db->exec("ALTER TABLE account ADD COLUMN chat_joined_at DATETIME NULL");
            $this->db->exec("UPDATE account SET chat_joined_at = '1970-01-01 00:00:00' WHERE chat_joined_at IS NULL");
        }
    }

    public function getUserChatJoinedAt(int $userId): string
    {
        $s = $this->db->prepare("SELECT chat_joined_at FROM account WHERE id = ? LIMIT 1");
        $s->execute([$userId]);
        $joinedAt = $s->fetchColumn();

        if (!$joinedAt) {
            $joinedAt = date('Y-m-d H:i:s');
            $u = $this->db->prepare("UPDATE account SET chat_joined_at = ? WHERE id = ?");
            $u->execute([$joinedAt, $userId]);
        }

        return (string)$joinedAt;
    }

    public function getMessages(int $limit = 100, int $beforeId = 0, ?string $joinedAt = null): array
    {
        $where = [];
        $params = [];

        if ($beforeId > 0) {
            $where[] = 'id < ?';
            $params[] = $beforeId;
        }
        if ($joinedAt) {
            $where[] = 'created_at >= ?';
            $params[] = $joinedAt;
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
        $limit = max(1, min(200, $limit));

        if ($beforeId > 0) {
            $s = $this->db->prepare(
                "SELECT * FROM chat_messages{$whereSql} ORDER BY id DESC LIMIT " . (int)$limit
            );
            $s->execute($params);
        } else {
            $s = $this->db->prepare(
                "SELECT * FROM chat_messages{$whereSql} ORDER BY id DESC LIMIT " . (int)$limit
            );
            $s->execute($params);
        }
        return array_reverse($s->fetchAll());
    }

    public function getNewMessages(int $afterId, ?string $joinedAt = null): array
    {
        $where = ['id > ?'];
        $params = [$afterId];

        if ($joinedAt) {
            $where[] = 'created_at >= ?';
            $params[] = $joinedAt;
        }

        $s = $this->db->prepare(
            "SELECT * FROM chat_messages WHERE " . implode(' AND ', $where) . " ORDER BY id ASC"
        );
        $s->execute($params);
        return $s->fetchAll();
    }

    public function getPinnedMessages(?string $joinedAt = null, int $limit = 20): array
    {
        $where = ['pinned = 1'];
        $params = [];

        if ($joinedAt) {
            $where[] = 'created_at >= ?';
            $params[] = $joinedAt;
        }

        $limit = max(1, min(50, $limit));
        $s = $this->db->prepare(
            "SELECT * FROM chat_messages WHERE " . implode(' AND ', $where) . " ORDER BY id DESC LIMIT " . (int)$limit
        );
        $s->execute($params);
        return $s->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $s = $this->db->prepare("SELECT * FROM chat_messages WHERE id = ?");
        $s->execute([$id]);
        $row = $s->fetch();
        return $row ?: null;
    }

    public function send(int $user_id, string $ho_ten, string $vai_tro, string $noi_dung, string $loai = 'text', ?int $reply_to_id = null, ?string $reply_text = null): int
    {
        $s = $this->db->prepare(
            "INSERT INTO chat_messages (user_id, ho_ten, vai_tro, noi_dung, loai, reply_to_id, reply_text) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $s->execute([$user_id, $ho_ten, $vai_tro, $noi_dung, $loai, $reply_to_id, $reply_text]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id, int $user_id): bool
    {
        // Chỉ xóa tin nhắn của mình hoặc admin xóa tất cả
        $s = $this->db->prepare("DELETE FROM chat_messages WHERE id = ? AND (user_id = ? OR ? = 1)");
        $isAdmin = in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro']) ? 1 : 0;
        return $s->execute([$id, $user_id, $isAdmin]);
    }

    public function react(int $id, string $reaction): bool
    {
        $s = $this->db->prepare("UPDATE chat_messages SET reaction = ? WHERE id = ?");
        return $s->execute([$reaction, $id]);
    }

    public function togglePin(int $id, int $user_id): bool
    {
        $isAdmin = in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro']) ? 1 : 0;
        $s = $this->db->prepare(
            "UPDATE chat_messages SET pinned = IF(pinned = 1, 0, 1) WHERE id = ? AND (user_id = ? OR ? = 1)"
        );
        return $s->execute([$id, $user_id, $isAdmin]);
    }

    public function countTotal(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn();
    }
}
