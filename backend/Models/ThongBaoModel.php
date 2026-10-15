<?php

class ThongBaoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(int $limit = 50): array
    {
        $s = $this->db->query(
            "SELECT * FROM thong_bao ORDER BY ghim DESC, created_at DESC LIMIT " . (int)$limit
        );
        return $s->fetchAll();
    }

    public function getChuaDoc(int $user_id, int $limit = 20): array
    {
        $s = $this->db->prepare(
            "SELECT tb.* FROM thong_bao tb
             WHERE tb.id NOT IN (
                SELECT thong_bao_id FROM thong_bao_da_doc WHERE user_id = ?
             )
             ORDER BY tb.ghim DESC, tb.created_at DESC
             LIMIT " . (int)$limit
        );
        $s->execute([$user_id]);
        return $s->fetchAll();
    }

    public function countChuaDoc(int $user_id): int
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM thong_bao tb
             WHERE tb.id NOT IN (
                SELECT thong_bao_id FROM thong_bao_da_doc WHERE user_id = ?
             )"
        );
        $s->execute([$user_id]);
        return (int)$s->fetchColumn();
    }

    public function getById(int $id): array|false
    {
        $s = $this->db->prepare("SELECT * FROM thong_bao WHERE id = ?");
        $s->execute([$id]);
        return $s->fetch();
    }

    public function create(string $tieu_de, string $noi_dung, string $loai = 'chung', string $nguoi_gui = '', bool $ghim = false): bool
    {
        $s = $this->db->prepare(
            "INSERT INTO thong_bao (tieu_de, noi_dung, loai, nguoi_gui, ghim) VALUES (?, ?, ?, ?, ?)"
        );
        return $s->execute([$tieu_de, $noi_dung, $loai, $nguoi_gui, $ghim ? 1 : 0]);
    }

    public function createAndGetId(string $tieu_de, string $noi_dung, string $loai = 'chung', string $nguoi_gui = '', bool $ghim = false): int
    {
        $ok = $this->create($tieu_de, $noi_dung, $loai, $nguoi_gui, $ghim);
        return $ok ? (int)$this->db->lastInsertId() : 0;
    }

    public function anVoiTatCaTruNguoiNhan(int $thong_bao_id, array $user_ids): bool
    {
        $user_ids = array_values(array_unique(array_filter(array_map('intval', $user_ids))));
        if (!$thong_bao_id) return false;

        if (empty($user_ids)) {
            $s = $this->db->prepare(
                "INSERT IGNORE INTO thong_bao_da_doc (thong_bao_id, user_id)
                 SELECT ?, id FROM account"
            );
            return $s->execute([$thong_bao_id]);
        }

        $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
        $s = $this->db->prepare(
            "INSERT IGNORE INTO thong_bao_da_doc (thong_bao_id, user_id)
             SELECT ?, id FROM account WHERE id NOT IN ($placeholders)"
        );
        return $s->execute(array_merge([$thong_bao_id], $user_ids));
    }

    public function update(int $id, string $tieu_de, string $noi_dung, string $loai, bool $ghim = false): bool
    {
        $s = $this->db->prepare(
            "UPDATE thong_bao SET tieu_de = ?, noi_dung = ?, loai = ?, ghim = ? WHERE id = ?"
        );
        return $s->execute([$tieu_de, $noi_dung, $loai, $ghim ? 1 : 0, $id]);
    }

    public function delete(int $id): bool
    {
        $s = $this->db->prepare("DELETE FROM thong_bao WHERE id = ?");
        return $s->execute([$id]);
    }

    public function toggleGhim(int $id): bool
    {
        $s = $this->db->prepare("UPDATE thong_bao SET ghim = NOT ghim WHERE id = ?");
        return $s->execute([$id]);
    }

    public function danhDauDaDoc(int $thong_bao_id, int $user_id): bool
    {
        $s = $this->db->prepare(
            "INSERT IGNORE INTO thong_bao_da_doc (thong_bao_id, user_id) VALUES (?, ?)"
        );
        return $s->execute([$thong_bao_id, $user_id]);
    }

    public function docTatCa(int $user_id): bool
    {
        $s = $this->db->prepare(
            "INSERT IGNORE INTO thong_bao_da_doc (thong_bao_id, user_id)
             SELECT id, ? FROM thong_bao
             WHERE id NOT IN (SELECT thong_bao_id FROM thong_bao_da_doc WHERE user_id = ?)"
        );
        return $s->execute([$user_id, $user_id]);
    }

    public function isDaDoc(int $thong_bao_id, int $user_id): bool
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*) FROM thong_bao_da_doc WHERE thong_bao_id = ? AND user_id = ?"
        );
        $s->execute([$thong_bao_id, $user_id]);
        return $s->fetchColumn() > 0;
    }
}
