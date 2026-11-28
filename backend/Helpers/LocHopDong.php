<?php
/**
 * Bộ lọc dùng chung cho các trang QUẢN TRỊ: mặc định chỉ hiện dữ liệu thuộc hợp đồng ĐANG HIỆU LỰC,
 * bấm "Tất cả (gồm lịch sử cũ)" (tham số ?lich_su=1) để xem lại dữ liệu của người thuê trước / hợp đồng đã kết thúc.
 * Giao diện nút chuyển: frontend/views/Layouts/loc_hop_dong.php
 */
class LocHopDong
{
    /** Quản trị đang chọn xem cả lịch sử cũ? */
    public static function xemLichSu(): bool
    {
        return !empty($_GET['lich_su']);
    }

    /** Bản ghi có cột hop_dong_id: hợp đồng đó còn hiệu lực. */
    public static function theoHopDong(string $cotHopDongId): string
    {
        return "EXISTS (SELECT 1 FROM hop_dong hlq WHERE hlq.id = $cotHopDongId AND hlq.trang_thai = 'hieu_luc')";
    }

    /**
     * Bản ghi gắn với phòng + một mốc ngày: phòng đó có hợp đồng hiệu lực và bản ghi phát sinh từ ngày hợp đồng bắt đầu.
     * Dùng cho bảng không lưu hop_dong_id (lưu trú, sửa chữa...).
     */
    public static function theoPhongVaNgay(string $cotPhongId, string $cotNgay): string
    {
        return "EXISTS (SELECT 1 FROM hop_dong hlq
                        WHERE hlq.phong_id = $cotPhongId AND hlq.trang_thai = 'hieu_luc'
                          AND DATE($cotNgay) >= hlq.ngay_bat_dau)";
    }
}
