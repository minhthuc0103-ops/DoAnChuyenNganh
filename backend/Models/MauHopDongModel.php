<?php

class MauHopDongModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        return $this->db->query("SELECT * FROM mau_hop_dong ORDER BY mac_dinh DESC, id DESC")->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $s = $this->db->prepare("SELECT * FROM mau_hop_dong WHERE id = ?");
        $s->execute([$id]);
        return $s->fetch();
    }

    public function getMacDinh(): array|false
    {
        $s = $this->db->query("SELECT * FROM mau_hop_dong WHERE mac_dinh = 1 LIMIT 1");
        return $s->fetch();
    }

    public function create(string $ten_mau, string $noi_dung, bool $mac_dinh = false): bool
    {
        if ($mac_dinh) {
            $this->db->exec("UPDATE mau_hop_dong SET mac_dinh = 0");
        }
        $s = $this->db->prepare("INSERT INTO mau_hop_dong (ten_mau, noi_dung, mac_dinh) VALUES (?, ?, ?)");
        return $s->execute([$ten_mau, $noi_dung, $mac_dinh ? 1 : 0]);
    }

    public function update(int $id, string $ten_mau, string $noi_dung, bool $mac_dinh = false): bool
    {
        if ($mac_dinh) {
            $this->db->exec("UPDATE mau_hop_dong SET mac_dinh = 0");
        }
        $s = $this->db->prepare("UPDATE mau_hop_dong SET ten_mau = ?, noi_dung = ?, mac_dinh = ? WHERE id = ?");
        return $s->execute([$ten_mau, $noi_dung, $mac_dinh ? 1 : 0, $id]);
    }

    public function delete(int $id): bool
    {
        $s = $this->db->prepare("DELETE FROM mau_hop_dong WHERE id = ?");
        return $s->execute([$id]);
    }
}
