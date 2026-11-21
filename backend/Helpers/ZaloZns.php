<?php
require_once __DIR__ . '/../config/notify.php';

/**
 * Gửi tin Zalo ZNS (Zalo Notification Service) qua API của Zalo.
 * Cần: Zalo OA đã xác minh, mẫu tin ZNS đã được Zalo duyệt, access token còn hạn.
 * Không ném lỗi ra ngoài: mọi sự cố trả về [false, 'lý do', null].
 */
class ZaloZns
{
    /** Đã bật và đủ cấu hình cho loại tin này chưa. @param string $loai 'hoa_don' | 'bien_nhan' */
    public static function sanSang(string $loai): bool
    {
        if (!ZALO_ZNS_ENABLED || ZALO_ZNS_ACCESS_TOKEN === '') {
            return false;
        }
        return self::templateId($loai) !== '';
    }

    public static function templateId(string $loai): string
    {
        return $loai === 'bien_nhan' ? ZALO_ZNS_TEMPLATE_BIENNHAN : ZALO_ZNS_TEMPLATE_HOADON;
    }

    /** 0912345678 | +84912345678 | 84912345678  ->  84912345678 ; sai định dạng -> null */
    public static function chuanHoaSdt(string $sdt): ?string
    {
        $so = preg_replace('/[\s.\-()]/', '', $sdt);
        if (preg_match('/^\+?84(\d{9})$/', $so, $m)) {
            return '84' . $m[1];
        }
        if (preg_match('/^0(\d{9})$/', $so, $m)) {
            return '84' . $m[1];
        }
        return null;
    }

    /**
     * @param array<string,string> $duLieu dữ liệu của app (ten_khach, phong, ky, tong_tien, han...)
     * @return array{0:bool,1:?string,2:?string} [thành công, lý do lỗi, msg_id]
     */
    public static function gui(string $loai, string $sdt, array $duLieu, string $trackingId): array
    {
        $phone = self::chuanHoaSdt($sdt);
        if ($phone === null) {
            return [false, 'Số điện thoại không hợp lệ.', null];
        }
        $anhXa = $loai === 'bien_nhan' ? ZALO_ZNS_PARAMS_BIENNHAN : ZALO_ZNS_PARAMS_HOADON;
        $templateData = [];
        foreach ($anhXa as $tenThamSo => $khoa) {
            $templateData[$tenThamSo] = (string)($duLieu[$khoa] ?? '');
        }
        $body = [
            'phone'         => $phone,
            'template_id'   => self::templateId($loai),
            'template_data' => $templateData,
            'tracking_id'   => mb_substr(preg_replace('/[^A-Za-z0-9_\-]/', '', $trackingId), 0, 48),
        ];
        if (ZALO_ZNS_MODE === 'development') {
            $body['mode'] = 'development';
        }

        $ch = curl_init(ZALO_ZNS_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int)ZALO_ZNS_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'access_token: ' . ZALO_ZNS_ACCESS_TOKEN],
            CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
        ]);
        $raw  = curl_exec($ch);
        $loiCurl = curl_error($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            return [false, 'Không kết nối được Zalo: ' . mb_substr($loiCurl, 0, 200, 'UTF-8'), null];
        }
        $j = json_decode($raw, true);
        if (!is_array($j)) {
            return [false, "Zalo trả về dữ liệu không đọc được (HTTP $http).", null];
        }
        // Zalo: error = 0 là thành công, khác 0 là lỗi kèm message
        if ((int)($j['error'] ?? -1) !== 0) {
            return [false, 'Zalo từ chối (mã ' . (int)($j['error'] ?? -1) . '): ' . mb_substr((string)($j['message'] ?? ''), 0, 200, 'UTF-8'), null];
        }
        return [true, null, isset($j['data']['msg_id']) ? (string)$j['data']['msg_id'] : null];
    }
}