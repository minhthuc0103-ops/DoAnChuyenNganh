<?php
class DonGiaModel {
    private PDO $db;
    public function __construct() {
        $this->db = Database::getInstance();
        try {
            $this->db->exec("ALTER TABLE don_gia ADD COLUMN IF NOT EXISTS phi_dv DECIMAL(12,2) DEFAULT 150000");
            $this->db->exec("UPDATE don_gia SET phi_dv=150000 WHERE phi_dv IS NULL OR phi_dv=0");
        } catch (\Exception $e) {}
    }

    public function getCurrent(): array {
        return $this->db->query("SELECT * FROM don_gia ORDER BY id DESC LIMIT 1")->fetch()
            ?: ['gia_dien' => 3500, 'gia_nuoc' => 15000, 'phi_dv' => 150000];
    }

    public function update(float $gia_dien, float $gia_nuoc, float $phi_dv = 150000): bool {
        $count = (int)$this->db->query("SELECT COUNT(*) FROM don_gia")->fetchColumn();
        if ($count === 0) {
            $s = $this->db->prepare("INSERT INTO don_gia (gia_dien,gia_nuoc,phi_dv) VALUES(?,?,?)");
        } else {
            $s = $this->db->prepare("UPDATE don_gia SET gia_dien=?,gia_nuoc=?,phi_dv=? ORDER BY id DESC LIMIT 1");
        }
        return $s->execute([$gia_dien, $gia_nuoc, $phi_dv]);
    }
}