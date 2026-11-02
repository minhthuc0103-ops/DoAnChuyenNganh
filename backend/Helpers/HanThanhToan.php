<?php
/**
 * Hạn thanh toán hóa đơn — một nguồn duy nhất cho email hóa đơn và cảnh báo khách rời.
 *
 * Quy tắc (khớp thông báo hệ thống gửi người thuê: "thanh toán trong hạn 01-05 hàng tháng"):
 *   hạn = ngày 5 của tháng SAU kỳ hóa đơn, nhưng luôn cho ít nhất 5 ngày kể từ ngày lập
 *   (hóa đơn lập trễ thì người thuê không bị tính quá hạn ngay).
 */
class HanThanhToan
{
    public const NGAY_HAN_THANG_SAU    = 5;
    public const TOI_THIEU_NGAY_TU_LAP = 5;

    public static function cua(int $thang, int $nam, string $ngayLap): DateTimeImmutable
    {
        $thangSau = (new DateTimeImmutable(sprintf('%04d-%02d-01', $nam, $thang)))->modify('+1 month');
        $han = $thangSau->setDate((int)$thangSau->format('Y'), (int)$thangSau->format('n'), self::NGAY_HAN_THANG_SAU)->setTime(0, 0);
        $toiThieu = (new DateTimeImmutable($ngayLap))->setTime(0, 0)->modify('+' . self::TOI_THIEU_NGAY_TU_LAP . ' days');
        return $han >= $toiThieu ? $han : $toiThieu;
    }
}