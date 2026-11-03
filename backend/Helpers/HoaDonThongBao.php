<?php
require_once __DIR__ . '/../config/notify.php';
require_once __DIR__ . '/../config/payment.php';

/**
 * Gửi hóa đơn / biên nhận cho người thuê qua Gmail và Zalo ZNS.
 *
 *  - guiHoaDon():  gửi hóa đơn tiền phòng (tự động khi tạo hóa đơn, hoặc chủ trọ bấm "Gửi")
 *  - guiBienNhan(): gửi biên nhận khi chủ trọ xác nhận đã thu tiền
 *
 * Mọi lần gửi đều được ghi vào bảng hoa_don_gui. Hàm không ném lỗi: gửi thất bại chỉ ghi nhật ký.
 */
class HoaDonThongBao
{
    private const PHUONG_THUC = [
        'tien_mat' => 'Tiền mặt', 'chuyen_khoan' => 'Chuyển khoản', 'momo' => 'MoMo',
        'vnpay' => 'VNPay', 'zalopay' => 'ZaloPay', 'khac' => 'Khác',
    ];

    public static function guiHoaDon(int $hoaDonId, bool $tuDong = false): array
    {
        return self::gui($hoaDonId, 'hoa_don', $tuDong);
    }

    public static function guiBienNhan(int $hoaDonId, bool $tuDong = false): array
    {
        return self::gui($hoaDonId, 'bien_nhan', $tuDong);
    }

    /**
     * @return array{bat:bool,kenh:array<string,array{trang_thai:string,thong_diep:string}>,loi?:string}
     */
    private static function gui(int $id, string $loai, bool $tuDong): array
    {
        $hd = self::layHoaDon($id);
        if (!$hd) {
            return ['bat' => false, 'kenh' => [], 'loi' => 'Không tìm thấy hóa đơn.'];
        }
        if ($tuDong && (($loai === 'hoa_don' && !HOADON_TU_DONG_GUI) || ($loai === 'bien_nhan' && !HOADON_GUI_BIEN_NHAN))) {
            return ['bat' => false, 'kenh' => []];
        }

        $emailBat = Mailer::sanSang();
        $zaloBat  = ZaloZns::sanSang($loai);
        if (!$emailBat && !$zaloBat) {
            return ['bat' => false, 'kenh' => []];   // chưa bật kênh nào: im lặng, không ghi nhật ký
        }

        $nhatKy = new HoaDonGuiModel();
        $d      = self::duLieu($hd);
        $kq     = [];

        if ($emailBat) {
            if (empty($hd['email'])) {
                $kq['email'] = self::ghi($nhatKy, $id, $loai, 'email', 'bo_qua', '', 'Người thuê chưa có email.', $tuDong);
            } else {
                [$ok, $loi] = Mailer::gui($hd['email'], (string)($hd['ho_ten'] ?? ''), self::tieuDe($hd, $loai), self::html($hd, $d, $loai), self::text($hd, $d, $loai));
                $kq['email'] = $ok
                    ? self::ghi($nhatKy, $id, $loai, 'email', 'thanh_cong', $hd['email'], 'Đã gửi.', $tuDong)
                    : self::ghi($nhatKy, $id, $loai, 'email', 'that_bai', $hd['email'], (string)$loi, $tuDong);
            }
        }

        if ($zaloBat) {
            if (empty($hd['sdt'])) {
                $kq['zalo'] = self::ghi($nhatKy, $id, $loai, 'zalo', 'bo_qua', '', 'Người thuê chưa có số điện thoại.', $tuDong);
            } else {
                [$ok, $loi, $msgId] = ZaloZns::gui($loai, (string)$hd['sdt'], $d, 'hd' . $id . '_' . $loai . '_' . time());
                $kq['zalo'] = $ok
                    ? self::ghi($nhatKy, $id, $loai, 'zalo', 'thanh_cong', $hd['sdt'], 'Đã gửi' . ($msgId ? " (msg_id $msgId)" : '') . '.', $tuDong)
                    : self::ghi($nhatKy, $id, $loai, 'zalo', 'that_bai', $hd['sdt'], (string)$loi, $tuDong);
            }
        }
        return ['bat' => true, 'kenh' => $kq];
    }

    private static function ghi(HoaDonGuiModel $m, int $id, string $loai, string $kenh, string $tt, string $nguoiNhan, string $chiTiet, bool $tuDong): array
    {
        try {
            $m->ghi($id, $loai, $kenh, $tt, $nguoiNhan, $chiTiet, $tuDong);
        } catch (\Throwable $e) {
            error_log('Khong ghi duoc nhat ky gui hoa don: ' . $e->getMessage());
        }
        return ['trang_thai' => $tt, 'thong_diep' => $chiTiet];
    }

    /** Tóm tắt kết quả để hiện cho chủ trọ. @return array{0:bool,1:string} [có ít nhất 1 kênh thành công, nội dung] */
    public static function tomTat(array $ket): array
    {
        if (!empty($ket['loi'])) {
            return [false, $ket['loi']];
        }
        if (empty($ket['bat'])) {
            return [false, 'Chưa bật gửi hóa đơn. Cấu hình Gmail / Zalo trong backend/config/notify.local.php.'];
        }
        $ten = ['email' => 'Email', 'zalo' => 'Zalo'];
        $nhan = ['thanh_cong' => 'đã gửi', 'that_bai' => 'LỖI', 'bo_qua' => 'bỏ qua'];
        $dong = [];
        $coThanhCong = false;
        foreach ($ket['kenh'] as $kenh => $r) {
            $coThanhCong = $coThanhCong || $r['trang_thai'] === 'thanh_cong';
            $dong[] = $ten[$kenh] . ': ' . $nhan[$r['trang_thai']] . ($r['trang_thai'] === 'thanh_cong' ? '' : ' — ' . $r['thong_diep']);
        }
        return [$coThanhCong, implode(' · ', $dong)];
    }

    // ───────────────────────── Dữ liệu ─────────────────────────

    private static function layHoaDon(int $id): array|false
    {
        $s = Database::getInstance()->prepare(
            "SELECT hd.*, p.so_phong, k.ten_khu, k.dia_chi AS dia_chi_khu,
                    nt.ho_ten, nt.sdt, COALESCE(NULLIF(a.email, ''), NULLIF(nt.email, '')) AS email
             FROM hoa_don hd
             JOIN phong p ON p.id = hd.phong_id
             LEFT JOIN khu_tro k ON k.id = p.khu_id
             LEFT JOIN hop_dong h ON h.id = (SELECT id FROM hop_dong WHERE phong_id = hd.phong_id AND trang_thai = 'hieu_luc' ORDER BY id DESC LIMIT 1)
             LEFT JOIN nguoi_thue nt ON nt.id = h.nguoi_thue_id
             LEFT JOIN account a ON a.id = nt.account_id
             WHERE hd.id = ?"
        );
        $s->execute([$id]);
        return $s->fetch();
    }

    /** Người thuê đang ở (hợp đồng hiệu lực) của từng phòng: để hiện nút Zalo trong danh sách. */
    public static function nguoiNhanTheoPhong(array $phongIds): array
    {
        $phongIds = array_values(array_unique(array_map('intval', $phongIds)));
        if (!$phongIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($phongIds), '?'));
        $s = Database::getInstance()->prepare(
            "SELECT hd.phong_id, nt.ho_ten, nt.sdt, COALESCE(NULLIF(a.email, ''), NULLIF(nt.email, '')) AS email
             FROM hop_dong hd
             JOIN nguoi_thue nt ON nt.id = hd.nguoi_thue_id
             LEFT JOIN account a ON a.id = nt.account_id
             WHERE hd.trang_thai = 'hieu_luc' AND hd.phong_id IN ($in)"
        );
        $s->execute($phongIds);
        $ra = [];
        foreach ($s->fetchAll() as $r) {
            $ra[(int)$r['phong_id']] = $r;
        }
        return $ra;
    }

    private static function tien($x): string
    {
        return number_format((float)$x, 0, ',', '.') . 'đ';
    }

    private static function han(array $hd): string
    {
        return HanThanhToan::cua((int)$hd['thang'], (int)$hd['nam'], (string)$hd['created_at'])->format('d/m/Y');
    }

    private static function noiDungCK(array $hd): string
    {
        return 'TT phong ' . $hd['so_phong'] . ' T' . $hd['thang'] . '/' . $hd['nam'];   // giống mã QR trong trang chi tiết hóa đơn
    }

    private static function urlQr(array $hd): string
    {
        return 'https://img.vietqr.io/image/' . BANK_ID . '-' . BANK_ACCOUNT . '-' . VIETQR_TEMPLATE . '.jpg'
            . '?amount=' . (int)round((float)$hd['tong_tien'])
            . '&addInfo=' . urlencode(self::noiDungCK($hd))
            . '&accountName=' . urlencode(BANK_NAME);
    }

    /** Dữ liệu dùng chung cho email và tham số mẫu ZNS. */
    private static function duLieu(array $hd): array
    {
        return [
            'ten_khach'   => (string)($hd['ho_ten'] ?? ''),
            'phong'       => (string)$hd['so_phong'],
            'ky'          => $hd['thang'] . '/' . $hd['nam'],
            'tong_tien'   => number_format((float)$hd['tong_tien'], 0, ',', '.'),
            'han'         => self::han($hd),
            'tien_phong'  => number_format((float)$hd['tien_phong'], 0, ',', '.'),
            'tien_dien'   => number_format((float)$hd['tien_dien'], 0, ',', '.'),
            'tien_nuoc'   => number_format((float)$hd['tien_nuoc'], 0, ',', '.'),
            'so_tien_thu' => number_format((float)$hd['tong_tien'], 0, ',', '.'),
        ];
    }

    // ───────────────────────── Nội dung ─────────────────────────

    private static function tieuDe(array $hd, string $loai): string
    {
        return ($loai === 'bien_nhan' ? 'Biên nhận thanh toán' : 'Hóa đơn tiền phòng')
            . ' tháng ' . $hd['thang'] . '/' . $hd['nam'] . ' – Phòng ' . $hd['so_phong'];
    }

    private static function text(array $hd, array $d, string $loai): string
    {
        $t = 'Chào ' . ($d['ten_khach'] ?: 'bạn') . ",\n\n";
        if ($loai === 'bien_nhan') {
            $pt = self::PHUONG_THUC[$hd['phuong_thuc_tt'] ?? ''] ?? '';
            $t .= "Chủ trọ đã nhận thanh toán hóa đơn phòng {$d['phong']} tháng {$d['ky']}.\n"
                . "Số tiền: " . $d['tong_tien'] . "đ" . ($pt ? " ($pt)" : '') . "\n"
                . (!empty($hd['ngay_thanh_toan']) ? 'Ngày thanh toán: ' . date('d/m/Y H:i', strtotime($hd['ngay_thanh_toan'])) . "\n" : '')
                . "\nCảm ơn bạn!\n";
            return $t;
        }
        $t .= "Hóa đơn phòng {$d['phong']} tháng {$d['ky']}:\n"
            . '- Tiền phòng: ' . self::tien($hd['tien_phong']) . "\n"
            . '- Tiền điện: ' . self::tien($hd['tien_dien']) . ' (' . self::so(max(0, $hd['chi_so_dien_moi'] - $hd['chi_so_dien_cu'])) . " kWh)\n"
            . '- Tiền nước: ' . self::tien($hd['tien_nuoc']) . ' (' . self::so(max(0, $hd['chi_so_nuoc_moi'] - $hd['chi_so_nuoc_cu'])) . " m3)\n"
            . '- Phí dịch vụ: ' . self::tien($hd['phi_dich_vu'] ?? 0) . "\n"
            . '- Phí xe: ' . self::tien($hd['phi_xe'] ?? 0) . "\n"
            . 'TỔNG CỘNG: ' . self::tien($hd['tong_tien']) . "\n"
            . 'Hạn thanh toán: ' . $d['han'] . "\n\n"
            . 'Chuyển khoản: ' . BANK_DISPLAY . ' ' . BANK_ACCOUNT . ' - ' . BANK_NAME . "\n"
            . 'Nội dung: ' . self::noiDungCK($hd) . "\n";
        if (NOTIFY_APP_URL !== '') {
            $t .= 'Xem hóa đơn: ' . rtrim(NOTIFY_APP_URL, '/') . '/index.php?controller=hoadon&action=chiTiet&id=' . (int)$hd['id'] . "\n";
        }
        return $t . "\nEmail tự động từ RoomManager.\n";
    }

    private static function html(array $hd, array $d, string $loai): string
    {
        $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
        $hang = fn(string $nhan, string $giaTri, bool $dam = false) =>
            '<tr><td style="padding:8px 12px;border-bottom:1px solid #eee;color:#555">' . $e($nhan) . '</td>'
          . '<td style="padding:8px 12px;border-bottom:1px solid #eee;text-align:right;' . ($dam ? 'font-weight:700;font-size:16px;color:#111' : 'color:#222') . '">' . $e($giaTri) . '</td></tr>';

        $thanBang = '';
        $cuoi = '';
        if ($loai === 'bien_nhan') {
            $pt = self::PHUONG_THUC[$hd['phuong_thuc_tt'] ?? ''] ?? '—';
            $thanBang = $hang('Số tiền đã thanh toán', self::tien($hd['tong_tien']), true)
                      . $hang('Phương thức', $pt)
                      . $hang('Ngày thanh toán', !empty($hd['ngay_thanh_toan']) ? date('d/m/Y H:i', strtotime($hd['ngay_thanh_toan'])) : '—');
            $loiMo = 'Chủ trọ đã nhận thanh toán hóa đơn của bạn. Cảm ơn bạn!';
        } else {
            $thanBang = $hang('Tiền phòng', self::tien($hd['tien_phong']))
                      . $hang('Tiền điện (' . self::so(max(0, $hd['chi_so_dien_moi'] - $hd['chi_so_dien_cu'])) . ' kWh)', self::tien($hd['tien_dien']))
                      . $hang('Tiền nước (' . self::so(max(0, $hd['chi_so_nuoc_moi'] - $hd['chi_so_nuoc_cu'])) . ' m³)', self::tien($hd['tien_nuoc']))
                      . $hang('Phí dịch vụ', self::tien($hd['phi_dich_vu'] ?? 0))
                      . $hang('Phí xe' . (!empty($hd['so_xe']) ? ' (' . (int)$hd['so_xe'] . ' xe)' : ''), self::tien($hd['phi_xe'] ?? 0))
                      . $hang('TỔNG CỘNG', self::tien($hd['tong_tien']), true);
            $loiMo = 'Hóa đơn tiền phòng của bạn đã được lập. Vui lòng thanh toán trước ngày <b>' . $e($d['han']) . '</b>.';
            $cuoi = '<div style="margin-top:18px;padding:14px 16px;background:#f5f8ff;border:1px solid #d6e2ff;border-radius:8px;color:#333;font-size:14px;line-height:1.7">'
                  . '<b>Thông tin chuyển khoản</b><br>'
                  . 'Ngân hàng: ' . $e(BANK_DISPLAY) . '<br>Số tài khoản: <b>' . $e(BANK_ACCOUNT) . '</b><br>Chủ tài khoản: ' . $e(BANK_NAME) . '<br>'
                  . 'Nội dung: <b>' . $e(self::noiDungCK($hd)) . '</b><br>'
                  . '<img src="' . $e(self::urlQr($hd)) . '" alt="Mã QR thanh toán" width="220" style="margin-top:10px;border-radius:6px"></div>';
            if (NOTIFY_APP_URL !== '') {
                $cuoi .= '<p style="margin-top:16px"><a href="' . $e(rtrim(NOTIFY_APP_URL, '/') . '/index.php?controller=hoadon&action=chiTiet&id=' . (int)$hd['id'])
                       . '" style="background:#4f8ef7;color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:600;display:inline-block">Xem hóa đơn trên RoomManager</a></p>';
            }
        }

        return '<!DOCTYPE html><html lang="vi"><body style="margin:0;padding:20px;background:#f3f4f8;font-family:Arial,Helvetica,sans-serif">'
            . '<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e3e5ec">'
            . '<div style="background:#4f8ef7;color:#fff;padding:18px 22px"><div style="font-size:12px;opacity:.85">RoomManager</div>'
            . '<div style="font-size:20px;font-weight:700">' . $e($loai === 'bien_nhan' ? 'Biên nhận thanh toán' : 'Hóa đơn tiền phòng') . ' tháng ' . $e($d['ky']) . '</div></div>'
            . '<div style="padding:22px">'
            . '<p style="margin:0 0 6px;color:#222">Chào <b>' . $e($d['ten_khach'] ?: 'bạn') . '</b>,</p>'
            . '<p style="margin:0 0 16px;color:#444;line-height:1.6">' . $loiMo . '</p>'
            . '<div style="margin-bottom:10px;color:#666;font-size:13px">Phòng <b>' . $e($hd['so_phong']) . '</b>' . (!empty($hd['ten_khu']) ? ' · ' . $e($hd['ten_khu']) : '') . '</div>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px">' . $thanBang . '</table>'
            . $cuoi
            . '</div><div style="padding:12px 22px;background:#fafbfc;color:#888;font-size:12px">Email tự động, vui lòng không trả lời thư này.</div>'
            . '</div></body></html>';
    }

    private static function so($x): string
    {
        return rtrim(rtrim(number_format((float)$x, 1, ',', ''), '0'), ',');
    }

    /** Nội dung tin nhắn Zalo để chủ trọ dán thủ công (nút "Zalo" trong danh sách hóa đơn). */
    public static function vanBanZalo(array $hd, array $nguoi): string
    {
        $ten = trim((string)($nguoi['ho_ten'] ?? ''));
        return 'Chào ' . ($ten !== '' ? $ten : 'bạn') . ', hóa đơn phòng ' . $hd['so_phong'] . ' tháng ' . $hd['thang'] . '/' . $hd['nam']
            . ': tổng ' . self::tien($hd['tong_tien'])
            . ' (phòng ' . self::tien($hd['tien_phong']) . ', điện ' . self::tien($hd['tien_dien']) . ', nước ' . self::tien($hd['tien_nuoc']) . ').'
            . ' Hạn thanh toán: ' . self::han($hd) . '.'
            . ' Chuyển khoản ' . BANK_DISPLAY . ' ' . BANK_ACCOUNT . ' - ' . BANK_NAME . ', nội dung: ' . self::noiDungCK($hd) . '. Cảm ơn bạn!';
    }
}