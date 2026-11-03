<?php
class LuuTruModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(array $filter = []): array {
        $sql = "
            SELECT lt.*, p.so_phong, k.ten_khu, nt.avatar, nt.cccd_truoc, nt.cccd_sau
            FROM khai_bao_luu_tru lt
            JOIN phong p ON lt.phong_id = p.id
            LEFT JOIN khu_tro k ON p.khu_id = k.id
            LEFT JOIN nguoi_thue nt ON lt.nguoi_thue_id = nt.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filter['khu_id'])) {
            $sql .= " AND p.khu_id = ?";
            $params[] = (int)$filter['khu_id'];
        }
        if (!empty($filter['phong_id'])) {
            $sql .= " AND lt.phong_id = ?";
            $params[] = (int)$filter['phong_id'];
        }
        if (!empty($filter['trang_thai'])) {
            $sql .= " AND lt.trang_thai = ?";
            $params[] = $filter['trang_thai'];
        }
        if (!empty($filter['keyword'])) {
            $sql .= " AND (lt.ho_ten LIKE ? OR lt.cccd LIKE ? OR lt.sdt LIKE ? OR p.so_phong LIKE ?)";
            $kw = '%' . trim($filter['keyword']) . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        $sql .= " ORDER BY lt.trang_thai ASC, lt.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("
            SELECT lt.*, p.so_phong, p.gia, k.ten_khu, k.dia_chi AS dia_chi_khu,
                   nt.avatar, nt.cccd_truoc, nt.cccd_sau, nt.email
            FROM khai_bao_luu_tru lt
            JOIN phong p ON lt.phong_id = p.id
            LEFT JOIN khu_tro k ON p.khu_id = k.id
            LEFT JOIN nguoi_thue nt ON lt.nguoi_thue_id = nt.id
            WHERE lt.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO khai_bao_luu_tru 
                (phong_id, nguoi_thue_id, ho_ten, ngay_sinh, gioi_tinh, cccd, ngay_cap_cccd, noi_cap_cccd, sdt, thuong_tru, nghe_nghiep, ngay_den, ngay_di, ly_do, ma_dvc, trang_thai, ghi_chu)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['phong_id'],
            $data['nguoi_thue_id'],
            $data['ho_ten'],
            $data['ngay_sinh'] ?: null,
            $data['gioi_tinh'] ?? 'nam',
            $data['cccd'],
            $data['ngay_cap_cccd'] ?: null,
            $data['noi_cap_cccd'] ?? '',
            $data['sdt'] ?? '',
            $data['thuong_tru'] ?? '',
            $data['nghe_nghiep'] ?? '',
            $data['ngay_den'],
            $data['ngay_di'] ?: null,
            $data['ly_do'] ?? 'Thuê trọ để sinh sống, học tập và làm việc',
            $data['ma_dvc'] ?? null,
            $data['trang_thai'] ?? 'cho_khai_bao',
            $data['ghi_chu'] ?? ''
        ]);
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE khai_bao_luu_tru SET 
                phong_id = ?, ho_ten = ?, ngay_sinh = ?, gioi_tinh = ?, cccd = ?, 
                ngay_cap_cccd = ?, noi_cap_cccd = ?, sdt = ?, thuong_tru = ?, nghe_nghiep = ?, 
                ngay_den = ?, ngay_di = ?, ly_do = ?, ma_dvc = ?, trang_thai = ?, ghi_chu = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['phong_id'],
            $data['ho_ten'],
            $data['ngay_sinh'] ?: null,
            $data['gioi_tinh'] ?? 'nam',
            $data['cccd'],
            $data['ngay_cap_cccd'] ?: null,
            $data['noi_cap_cccd'] ?? '',
            $data['sdt'] ?? '',
            $data['thuong_tru'] ?? '',
            $data['nghe_nghiep'] ?? '',
            $data['ngay_den'],
            $data['ngay_di'] ?: null,
            $data['ly_do'] ?? 'Thuê trọ để sinh sống, học tập và làm việc',
            $data['ma_dvc'] ?? null,
            $data['trang_thai'] ?? 'cho_khai_bao',
            $data['ghi_chu'] ?? '',
            $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM khai_bao_luu_tru WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateStatus(int $id, string $status, ?string $ma_dvc = null): bool {
        if ($ma_dvc !== null) {
            $stmt = $this->db->prepare("UPDATE khai_bao_luu_tru SET trang_thai = ?, ma_dvc = ? WHERE id = ?");
            return $stmt->execute([$status, $ma_dvc, $id]);
        }
        $stmt = $this->db->prepare("UPDATE khai_bao_luu_tru SET trang_thai = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function getStats(): array {
        $total = (int)$this->db->query("SELECT COUNT(*) FROM khai_bao_luu_tru")->fetchColumn();
        $choKhaiBao = (int)$this->db->query("SELECT COUNT(*) FROM khai_bao_luu_tru WHERE trang_thai='cho_khai_bao'")->fetchColumn();
        $daGui = (int)$this->db->query("SELECT COUNT(*) FROM khai_bao_luu_tru WHERE trang_thai='da_gui_dvc'")->fetchColumn();
        $daTiepNhan = (int)$this->db->query("SELECT COUNT(*) FROM khai_bao_luu_tru WHERE trang_thai='da_tiep_nhan'")->fetchColumn();

        return [
            'total'         => $total,
            'cho_khai_bao'  => $choKhaiBao,
            'da_gui_dvc'    => $daGui,
            'da_tiep_nhan'  => $daTiepNhan
        ];
    }

    /**
     * Tự động đồng bộ người đang ở trong các hợp đồng hiệu lực vào bảng khai báo lưu trú
     */
    public function syncFromContracts(): int {
        // 1. Quét chủ hợp đồng
        $sql = "
            SELECT hd.phong_id, hd.ngay_bat_dau, hd.ngay_ket_thuc,
                   nt.id AS nguoi_thue_id, nt.ho_ten, nt.ngay_sinh, nt.gioi_tinh,
                   nt.cccd, nt.ngay_cap_cccd, nt.noi_cap_cccd, nt.sdt, nt.dia_chi, nt.nghe_nghiep
            FROM hop_dong hd
            JOIN nguoi_thue nt ON hd.nguoi_thue_id = nt.id
            WHERE hd.trang_thai = 'hieu_luc'
        ";
        $list = $this->db->query($sql)->fetchAll();
        $added = 0;

        foreach ($list as $r) {
            if (empty($r['cccd'])) continue;
            // Kiểm tra xem người này với CCCD này trong phòng này đã có bản ghi chưa
            $check = $this->db->prepare("SELECT id FROM khai_bao_luu_tru WHERE cccd = ? AND phong_id = ?");
            $check->execute([$r['cccd'], $r['phong_id']]);
            if (!$check->fetch()) {
                $this->create([
                    'phong_id'        => $r['phong_id'],
                    'nguoi_thue_id'   => $r['nguoi_thue_id'],
                    'ho_ten'          => $r['ho_ten'],
                    'ngay_sinh'       => $r['ngay_sinh'],
                    'gioi_tinh'       => $r['gioi_tinh'] ?: 'nam',
                    'cccd'            => $r['cccd'],
                    'ngay_cap_cccd'   => $r['ngay_cap_cccd'],
                    'noi_cap_cccd'    => $r['noi_cap_cccd'] ?: 'Cục Cảnh sát QLHC về TTXH',
                    'sdt'             => $r['sdt'],
                    'thuong_tru'      => $r['dia_chi'],
                    'nghe_nghiep'     => $r['nghe_nghiep'] ?: 'Lao động / Sinh viên',
                    'ngay_den'        => $r['ngay_bat_dau'],
                    'ngay_di'         => $r['ngay_ket_thuc'],
                    'ly_do'           => 'Thuê trọ sinh sống và làm việc',
                    'trang_thai'      => 'cho_khai_bao'
                ]);
                $added++;
            }
        }

        // 2. Quét người ở cùng trong nguoi_thue_phong
        $sqlO = "
            SELECT ntp.*, hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.nguoi_thue_id AS chu_hd_id
            FROM nguoi_thue_phong ntp
            JOIN hop_dong hd ON ntp.hop_dong_id = hd.id
            WHERE hd.trang_thai = 'hieu_luc'
        ";
        $listO = $this->db->query($sqlO)->fetchAll();
        foreach ($listO as $r) {
            if (empty($r['cccd'])) continue;
            $check = $this->db->prepare("SELECT id FROM khai_bao_luu_tru WHERE cccd = ? AND phong_id = ?");
            $check->execute([$r['cccd'], $r['phong_id']]);
            if (!$check->fetch()) {
                $this->create([
                    'phong_id'        => $r['phong_id'],
                    'nguoi_thue_id'   => $r['chu_hd_id'],
                    'ho_ten'          => $r['ho_ten'],
                    'ngay_sinh'       => $r['ngay_sinh'],
                    'gioi_tinh'       => $r['gioi_tinh'] ?: 'nam',
                    'cccd'            => $r['cccd'],
                    'ngay_cap_cccd'   => null,
                    'noi_cap_cccd'    => 'Cục Cảnh sát QLHC về TTXH',
                    'sdt'             => $r['sdt'],
                    'thuong_tru'      => $r['que_quan'],
                    'nghe_nghiep'     => 'Người ở cùng',
                    'ngay_den'        => $r['ngay_bat_dau'],
                    'ngay_di'         => $r['ngay_ket_thuc'],
                    'ly_do'           => 'Ở cùng phòng trọ',
                    'trang_thai'      => 'cho_khai_bao'
                ]);
                $added++;
            }
        }

        return $added;
    }

    public function getByAccountId(int $account_id): array {
        $stmt = $this->db->prepare("
            SELECT lt.*, p.so_phong, k.ten_khu, k.dia_chi AS dia_chi_khu
            FROM khai_bao_luu_tru lt
            JOIN phong p ON lt.phong_id = p.id
            LEFT JOIN khu_tro k ON p.khu_id = k.id
            JOIN nguoi_thue nt ON lt.nguoi_thue_id = nt.id
            WHERE nt.account_id = ?
            ORDER BY lt.id DESC
        ");
        $stmt->execute([$account_id]);
        return $stmt->fetchAll();
    }
}

