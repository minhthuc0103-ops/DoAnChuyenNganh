<?php
/**
 * Cảnh báo nguy cơ khách rời — chấm điểm 0-100 cho từng hợp đồng đang hiệu lực
 * dựa trên 4 tín hiệu (tối đa 25 + 30 + 25 + 20 = 100 điểm):
 *
 *   1. Tiêu thụ điện nước ít  (≤ 25đ): dùng rất ít so với ngưỡng / so với các phòng khác
 *   2. Thanh toán trễ         (≤ 30đ): hóa đơn quá hạn chưa trả, hoặc hay trả muộn
 *   3. Phản ánh nhiều         (≤ 25đ): nhiều yêu cầu sửa chữa, để chờ lâu, chấm sao thấp
 *   4. Hợp đồng sắp hết       (≤ 20đ): còn ít ngày đến ngày kết thúc
 *
 * Mức: một tín hiệu mạnh đã là "Trung bình" (≥ 25đ), từ hai tín hiệu mạnh trở lên là "Cao" (≥ 50đ).
 * Khách ĐÃ BÁO trả phòng không phải "nguy cơ" mà là chắc chắn rời: xếp riêng lên đầu.
 * Điểm tính tại thời điểm xem, không lưu CSDL nên luôn phản ánh dữ liệu mới nhất.
 */
class CanhBaoModel
{
    // ── Ngưỡng: chỉnh tại đây ──
    public const DIEN_THAP_KWH       = 30;   // kWh / tháng
    public const NUOC_THAP_M3        = 2;    // m³ / tháng
    public const TI_LE_THAP          = 0.4;  // thấp nếu < 40% mức trung bình các phòng khác
    public const NGAY_PHAN_ANH       = 90;   // cửa sổ đếm yêu cầu sửa chữa
    public const NGAY_CHO_LAU        = 7;    // yêu cầu chờ xử lý quá N ngày là "để lâu"
    public const MUC_CAO             = 50;   // điểm >= 50: nguy cơ cao
    public const MUC_TB              = 25;   // điểm >= 25: trung bình
    public const NO_NANG_NGAY        = 30;   // hóa đơn trễ từ N ngày trở lên tính là nợ nặng

    public const MUC = [
        'da_bao' => ['🚪', 'Đã báo trả phòng'],
        'cao'    => ['🔴', 'Nguy cơ cao'],
        'tb'     => ['🟡', 'Trung bình'],
        'thap'   => ['🟢', 'Thấp'],
    ];

    private PDO $db;

    private static ?bool $coCotSao = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Danh sách mọi hợp đồng đang hiệu lực kèm điểm, cao nhất lên đầu. */
    public function danhSach(?DateTimeImmutable $homNay = null): array
    {
        $homNay = $homNay ?? new DateTimeImmutable('today');

        $hopDong = $this->db->query(
            "SELECT hd.id AS hop_dong_id, hd.phong_id, hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.yeu_cau_huy, hd.ngay_du_kien_ra,
                    p.so_phong, k.ten_khu, nt.id AS nguoi_thue_id, nt.ho_ten, nt.sdt, nt.account_id
             FROM hop_dong hd
             JOIN phong p       ON p.id  = hd.phong_id
             JOIN nguoi_thue nt ON nt.id = hd.nguoi_thue_id
             LEFT JOIN khu_tro k ON k.id = p.khu_id
             WHERE hd.trang_thai = 'hieu_luc'"
        )->fetchAll();
        if (!$hopDong) {
            return [];
        }

        $hoaDonTheoPhong = $this->hoaDonTheoPhong($hopDong);
        $suaChua         = $this->thongKeSuaChua($hopDong);

        // Mức tiêu thụ trung bình các phòng (để so sánh tương đối)
        $dsDien = $dsNuoc = [];
        foreach ($hopDong as $c) {
            $tt = self::tieuThuTrungBinh($hoaDonTheoPhong[$c['hop_dong_id']] ?? []);
            if ($tt) {
                $dsDien[] = $tt['dien'];
                $dsNuoc[] = $tt['nuoc'];
            }
        }
        $trungBinh = count($dsDien) >= 3
            ? ['dien' => array_sum($dsDien) / count($dsDien), 'nuoc' => array_sum($dsNuoc) / count($dsNuoc)]
            : null;   // ít hơn 3 phòng có dữ liệu thì so sánh tương đối không có ý nghĩa

        $ra = [];
        foreach ($hopDong as $c) {
            $r = self::chamDiem(
                $c,
                $hoaDonTheoPhong[$c['hop_dong_id']] ?? [],
                $suaChua[(int)$c['account_id']] ?? null,
                $trungBinh,
                $homNay
            );
            $ra[] = $c + $r;
        }

        usort($ra, function ($a, $b) {
            $uu = fn($x) => $x['muc'] === 'da_bao' ? 1 : 0;
            return [$uu($b), $b['diem'], $a['so_phong']] <=> [$uu($a), $a['diem'], $b['so_phong']];
        });
        return $ra;
    }

    /** Đếm theo mức: ['da_bao'=>n,'cao'=>n,'tb'=>n,'thap'=>n]. */
    public static function dem(array $ds): array
    {
        $d = ['da_bao' => 0, 'cao' => 0, 'tb' => 0, 'thap' => 0];
        foreach ($ds as $x) {
            $d[$x['muc']]++;
        }
        return $d;
    }

    /** Số khách cần chú ý ngay (nguy cơ cao + đã báo rời), dùng cho badge menu. */
    public function countCanChuY(): int
    {
        $d = self::dem($this->danhSach());
        return $d['cao'] + $d['da_bao'];
    }

    // ───────────────────────── Chấm điểm (hàm thuần, dễ kiểm thử) ─────────────────────────

    /**
     * @param array      $c         hợp đồng (ngay_ket_thuc, yeu_cau_huy, ngay_du_kien_ra)
     * @param array      $hoaDon    hóa đơn của phòng trong thời gian người thuê này ở (mới nhất trước)
     * @param array|null $sc        ['so_yc','cho_lau','sao_tb'] của người thuê
     * @param array|null $trungBinh ['dien','nuoc'] trung bình các phòng, null nếu không đủ dữ liệu
     * @return array{diem:int,muc:string,ly_do:array,thanh_phan:array,goi_y:string}
     */
    public static function chamDiem(array $c, array $hoaDon, ?array $sc, ?array $trungBinh, DateTimeImmutable $homNay): array
    {
        $lyDo = [];

        // 1. Tiêu thụ ít (tối đa 25)
        $diemTT = 0;
        $tt = self::tieuThuTrungBinh($hoaDon);
        if ($tt) {
            $dienThap = $tt['dien'] < self::DIEN_THAP_KWH
                || ($trungBinh && $trungBinh['dien'] > 0 && $tt['dien'] < self::TI_LE_THAP * $trungBinh['dien']);
            $nuocThap = $tt['nuoc'] < self::NUOC_THAP_M3
                || ($trungBinh && $trungBinh['nuoc'] > 0 && $tt['nuoc'] < self::TI_LE_THAP * $trungBinh['nuoc']);
            if ($dienThap) {
                $diemTT += 15;
                $lyDo[] = '⚡ Dùng điện rất ít: TB ' . self::so($tt['dien']) . ' kWh/tháng (' . $tt['so_ky'] . ' kỳ gần nhất)'
                        . ($trungBinh && $trungBinh['dien'] > 0
                            ? ', chỉ bằng ' . round($tt['dien'] / $trungBinh['dien'] * 100) . '% mức TB các phòng' : '');
            }
            if ($nuocThap) {
                $diemTT += 10;
                $lyDo[] = '💧 Dùng nước rất ít: TB ' . self::so($tt['nuoc']) . ' m³/tháng';
            }
        }

        // 2. Thanh toán trễ (tối đa 30)
        $treChuaTra = [];
        $traTre = 0;
        foreach ($hoaDon as $h) {
            $han = HanThanhToan::cua((int)$h['thang'], (int)$h['nam'], $h['created_at']);   // ngày 5 tháng sau kỳ HĐ
            if ($h['trang_thai'] === 'chua_tt') {
                if ($han < $homNay) {
                    $treChuaTra[] = ['ky' => $h['thang'] . '/' . $h['nam'], 'ngay' => (int)$han->diff($homNay)->days];
                }
            } elseif (!empty($h['ngay_thanh_toan']) && (new DateTimeImmutable($h['ngay_thanh_toan']))->setTime(0, 0) > $han) {
                $traTre++;
            }
        }
        // Mỗi hóa đơn quá hạn: 15đ; trễ từ 30 ngày trở lên (nợ nặng): 25đ. Trần 30đ cho cả nhóm thanh toán.
        $diemTH = 5 * min(2, $traTre);
        foreach ($treChuaTra as $t) {
            $diemTH += $t['ngay'] >= self::NO_NANG_NGAY ? 25 : 15;
        }
        $diemTH = min(30, $diemTH);
        foreach (array_slice($treChuaTra, 0, 3) as $t) {
            $lyDo[] = '💸 Hóa đơn ' . $t['ky'] . ' chưa thanh toán, trễ ' . $t['ngay'] . ' ngày';
        }
        if (count($treChuaTra) > 3) {
            $lyDo[] = '💸 …và ' . (count($treChuaTra) - 3) . ' hóa đơn quá hạn khác';
        }
        if ($traTre > 0) {
            $lyDo[] = '🕒 ' . $traTre . ' hóa đơn đã trả nhưng muộn hơn hạn';
        }

        // 3. Phản ánh nhiều (tối đa 25)
        $diemPA = 0;
        $soYC = (int)($sc['so_yc'] ?? 0);
        if ($soYC >= 4)      { $diemPA += 20; }
        elseif ($soYC >= 2)  { $diemPA += 10; }
        if ($soYC >= 2) {
            $lyDo[] = '🔧 ' . $soYC . ' yêu cầu sửa chữa trong ' . self::NGAY_PHAN_ANH . ' ngày';
        }
        if ((int)($sc['cho_lau'] ?? 0) > 0) {
            $diemPA += 5;
            $lyDo[] = '⏳ ' . (int)$sc['cho_lau'] . ' yêu cầu chờ xử lý quá ' . self::NGAY_CHO_LAU . ' ngày';
        }
        if (isset($sc['sao_tb']) && $sc['sao_tb'] !== null && $sc['sao_tb'] <= 2) {
            $diemPA += 5;
            $lyDo[] = '⭐ Chấm điểm xử lý thấp (TB ' . self::so((float)$sc['sao_tb']) . '/5 sao)';
        }
        $diemPA = min(25, $diemPA);

        // 4. Hợp đồng sắp hết (tối đa 20)
        $conLai = (int)$homNay->diff((new DateTimeImmutable($c['ngay_ket_thuc']))->setTime(0, 0))->format('%r%a');
        $diemHH = $conLai <= 15 ? 20 : ($conLai <= 30 ? 15 : ($conLai <= 60 ? 8 : 0));
        if ($diemHH > 0) {
            $lyDo[] = $conLai < 0
                ? '📅 Đã qua ngày kết thúc hợp đồng ' . abs($conLai) . ' ngày'
                : '📅 Hợp đồng còn ' . $conLai . ' ngày (đến ' . date('d/m/Y', strtotime($c['ngay_ket_thuc'])) . ')';
        }

        $thanhPhan = ['tieu_thu' => $diemTT, 'thanh_toan' => $diemTH, 'phan_anh' => $diemPA, 'het_han' => $diemHH];
        $diem = array_sum($thanhPhan);

        // Đã báo trả phòng: chắc chắn rời
        $muc = $diem >= self::MUC_CAO ? 'cao' : ($diem >= self::MUC_TB ? 'tb' : 'thap');
        if (!empty($c['yeu_cau_huy'])) {
            $muc = 'da_bao';
            $diem = 100;
            array_unshift($lyDo, '🚪 Đã báo trả phòng'
                . (!empty($c['ngay_du_kien_ra']) ? ', dự kiến ra ngày ' . date('d/m/Y', strtotime($c['ngay_du_kien_ra'])) : ''));
            if (!empty($c['ngay_du_kien_ra'])) {
                $tre = (int)(new DateTimeImmutable($c['ngay_du_kien_ra']))->setTime(0, 0)->diff($homNay)->format('%r%a');
                if ($tre > 0) {
                    array_splice($lyDo, 1, 0, ['⚠️ Quá ngày dự kiến ra ' . $tre . ' ngày mà chưa bàn giao phòng']);
                }
            }
        }

        return [
            'diem'       => (int)$diem,
            'muc'        => $muc,
            'ly_do'      => $lyDo,
            'thanh_phan' => $thanhPhan,
            'goi_y'      => self::goiY($muc, $thanhPhan),
        ];
    }

    /** Gợi ý hành động theo tín hiệu nặng nhất. */
    private static function goiY(string $muc, array $tp): string
    {
        if ($muc === 'da_bao') {
            return 'Chuẩn bị bàn giao phòng, chốt chỉ số điện nước, tính tiền cọc và tìm khách mới.';
        }
        if ($muc === 'thap' && array_sum($tp) === 0) {
            return 'Chưa cần can thiệp. Tiếp tục theo dõi.';
        }
        $goi = [
            'thanh_toan' => 'Nhắc thanh toán và thỏa thuận lịch trả; hỏi thăm khó khăn để tránh nợ kéo dài.',
            'phan_anh'   => 'Xử lý ngay các yêu cầu sửa chữa đang chờ và hỏi thăm trực tiếp mức độ hài lòng.',
            'tieu_thu'   => 'Liên hệ hỏi thăm: khách có còn ở thường xuyên không, hay đã chuyển đi?',
            'het_han'    => 'Chủ động đề xuất gia hạn hợp đồng, cân nhắc ưu đãi để giữ khách.',
        ];
        arsort($tp);
        return $goi[(string)array_key_first($tp)];
    }

    // ───────────────────────── Truy vấn dữ liệu ─────────────────────────

    /** Hóa đơn của từng hợp đồng: chỉ lấy kỳ từ tháng bắt đầu hợp đồng (tránh tính hóa đơn của người thuê cũ). */
    private function hoaDonTheoPhong(array $hopDong): array
    {
        $ids = array_values(array_unique(array_map(fn($c) => (int)$c['phong_id'], $hopDong)));
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $s = $this->db->prepare(
            "SELECT phong_id, thang, nam, chi_so_dien_cu, chi_so_dien_moi, chi_so_nuoc_cu, chi_so_nuoc_moi,
                    trang_thai, ngay_thanh_toan, created_at
             FROM hoa_don WHERE phong_id IN ($in) ORDER BY nam DESC, thang DESC, id DESC"
        );
        $s->execute($ids);
        $theoPhong = [];
        foreach ($s->fetchAll() as $h) {
            $theoPhong[(int)$h['phong_id']][] = $h;
        }

        $ra = [];
        foreach ($hopDong as $c) {
            $bd = new DateTimeImmutable($c['ngay_bat_dau']);
            $ky0 = (int)$bd->format('Y') * 12 + (int)$bd->format('n');
            $ra[$c['hop_dong_id']] = array_values(array_filter(
                $theoPhong[(int)$c['phong_id']] ?? [],
                fn($h) => ((int)$h['nam'] * 12 + (int)$h['thang']) >= $ky0
            ));
        }
        return $ra;
    }

    /** Bảng yêu cầu sửa chữa đã có cột chấm sao chưa (chức năng Sửa chữa có thể chưa được nâng cấp). */
    private function coCotSao(): bool
    {
        if (self::$coCotSao === null) {
            $s = $this->db->query(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'yeu_cau_sua_chua' AND COLUMN_NAME = 'danh_gia_sao'"
            );
            self::$coCotSao = (int)$s->fetchColumn() > 0;
        }
        return self::$coCotSao;
    }

    /**
     * Yêu cầu sửa chữa trong N ngày gần đây theo tài khoản người thuê.
     * Độc lập với chức năng Sửa chữa: thiếu bảng hoặc thiếu cột chấm sao thì bỏ qua phần đó, không báo lỗi.
     */
    private function thongKeSuaChua(array $hopDong): array
    {
        $ids = array_values(array_unique(array_filter(array_map(fn($c) => (int)$c['account_id'], $hopDong))));
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $saoTb = $this->coCotSao() ? 'AVG(danh_gia_sao)' : 'NULL';
        try {
            $s = $this->db->prepare(
                "SELECT user_id, COUNT(*) AS so_yc,
                        SUM(trang_thai = 'cho_xu_ly' AND created_at < DATE_SUB(NOW(), INTERVAL " . (int)self::NGAY_CHO_LAU . " DAY)) AS cho_lau,
                        $saoTb AS sao_tb
                 FROM yeu_cau_sua_chua
                 WHERE user_id IN ($in) AND created_at >= DATE_SUB(NOW(), INTERVAL " . (int)self::NGAY_PHAN_ANH . " DAY)
                 GROUP BY user_id"
            );
            $s->execute($ids);
        } catch (\PDOException $e) {
            error_log('CanhBao: khong doc duoc yeu_cau_sua_chua: ' . $e->getMessage());
            return [];
        }
        $ra = [];
        foreach ($s->fetchAll() as $r) {
            $ra[(int)$r['user_id']] = [
                'so_yc'   => (int)$r['so_yc'],
                'cho_lau' => (int)$r['cho_lau'],
                'sao_tb'  => $r['sao_tb'] !== null ? round((float)$r['sao_tb'], 1) : null,
            ];
        }
        return $ra;
    }

    /** Điện/nước TB mỗi tháng của 3 kỳ gần nhất có chỉ số hợp lệ (chỉ số mới >= cũ). */
    private static function tieuThuTrungBinh(array $hoaDon): ?array
    {
        $kyHopLe = [];
        foreach ($hoaDon as $h) {
            if ((float)$h['chi_so_dien_moi'] >= (float)$h['chi_so_dien_cu'] && (float)$h['chi_so_nuoc_moi'] >= (float)$h['chi_so_nuoc_cu']) {
                $kyHopLe[] = $h;
            }
            if (count($kyHopLe) === 3) {
                break;
            }
        }
        if (!$kyHopLe) {
            return null;
        }
        $n = count($kyHopLe);
        $dien = $nuoc = 0.0;
        foreach ($kyHopLe as $h) {
            $dien += (float)$h['chi_so_dien_moi'] - (float)$h['chi_so_dien_cu'];
            $nuoc += (float)$h['chi_so_nuoc_moi'] - (float)$h['chi_so_nuoc_cu'];
        }
        return ['dien' => $dien / $n, 'nuoc' => $nuoc / $n, 'so_ky' => $n];
    }

    private static function so(float $x): string
    {
        return rtrim(rtrim(number_format($x, 1, ',', ''), '0'), ',');
    }
}