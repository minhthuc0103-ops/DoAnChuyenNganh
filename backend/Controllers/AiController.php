<?php

require_once __DIR__ . '/../config/ai.php';

class AiController
{
    public function chat()
    {
        header('Content-Type: application/json');
        $input   = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');
        if (!$message) {
            echo json_encode(['ok' => false, 'message' => 'Tin nhắn trống']);
            exit;
        }
        try {
            if (AI_PROVIDER === 'local') {
                $reply = $this->localAI($message);
            } elseif (AI_PROVIDER === 'claude') {
                $reply = $this->callClaude($message);
            } elseif (AI_PROVIDER === 'gemini') {
                $reply = $this->callGemini($message);
            } else {
                $reply = $this->callOpenAI($message);
            }
            echo json_encode(['ok' => true, 'reply' => $reply]);
        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function clear()
    {
        $_SESSION['ai_history'] = [];
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    // ═══════════════════════════════════════════════════
    //  LOCAL AI — Toàn bộ logic xử lý câu hỏi
    // ═══════════════════════════════════════════════════
    private function localAI(string $message): string
    {
        $msg       = mb_strtolower($message, 'UTF-8');
        $db        = Database::getInstance();
        $vaiTro    = $_SESSION['vai_tro']  ?? 'user';
        $accountId = (int)($_SESSION['user_id'] ?? 0);
        $hoTen     = $_SESSION['ho_ten']   ?? $_SESSION['user'] ?? 'bạn';
        $isAdmin   = in_array($vaiTro, ['quan_ly', 'chu_tro'], true);

        // Lấy thông tin phòng của user đang đăng nhập
        $myInfo = null;
        if ($accountId && $vaiTro === 'user') {
            $s = $db->prepare("
                SELECT nt.*, hd.id AS hop_dong_id, hd.trang_thai AS hd_tt,
                       hd.ngay_bat_dau, hd.ngay_ket_thuc, hd.tien_coc,
                       p.so_phong, p.id AS phong_id, p.gia,
                       k.ten_khu, k.dia_chi AS dia_chi_khu
                FROM nguoi_thue nt
                LEFT JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id AND hd.trang_thai = 'hieu_luc'
                LEFT JOIN phong p ON hd.phong_id = p.id
                LEFT JOIN khu_tro k ON p.khu_id = k.id
                WHERE nt.account_id = ?
            ");
            $s->execute([$accountId]);
            $myInfo = $s->fetch();
        }

        // ═══════════════════════════════════════════════════
        //  TẦNG 0: TÌM TRONG KHO KIẾN THỨC ĐÃ HỌC
        // ═══════════════════════════════════════════════════
        $learnedReply = $this->searchKnowledge($db, $msg);
        if ($learnedReply !== null) {
            return $learnedReply;
        }

        // ═══ CHÀO HỎI ═══
        if ($this->match($msg, ['xin chào','hello','chào bạn','hey','alo','chào ai'])) {
            return "Xin chào $hoTen! 👋\nTôi là trợ lý AI của RoomManager.\n\nTôi có thể giúp bạn:\n• Tra cứu hóa đơn, hợp đồng\n• Thông tin phòng, khu trọ\n• Nội quy, dịch vụ\n• Và nhiều hơn nữa...\n\nGõ \"giúp\" để xem danh sách lệnh nhé!";
        }

        // ═══ CẢM ƠN ═══
        if ($this->match($msg, ['cảm ơn','cam on','thanks','thank','ok bạn','oke'])) {
            return "Không có gì $hoTen! 😊 Nếu cần hỗ trợ thêm cứ hỏi mình nhé.";
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 1: NGƯỜI THUÊ HỎI VỀ PHÒNG / HĐ CỦA MÌNH
        // ═══════════════════════════════════════════════════

        // Tiền phòng tháng này của tôi
        if ($this->match($msg, ['tiền phòng tôi','tiền tháng này','tien thang nay','hóa đơn của tôi','hoa don cua toi','hóa đơn tháng','hoa don thang','tiền tháng tôi','tien thang toi','tôi nợ','toi no','tôi chưa đóng','toi chua dong','tổng chi phí','tong chi phi','chi phí tháng','chi phi thang','tổng tiền tháng','tong tien thang','tôi phải đóng','toi phai dong','bao nhiêu tiền','bao nhieu tien','tháng này bao nhiêu','thang nay bao nhieu','chi phí của tôi','chi phi cua toi'])) {
            if (!$myInfo || empty($myInfo['phong_id'])) {
                return "Bạn chưa được liên kết với phòng nào. Vui lòng liên hệ quản lý.";
            }
            $thang = (int)date('m'); $nam = (int)date('Y');
            $s = $db->prepare("SELECT * FROM hoa_don WHERE phong_id=? AND thang=? AND nam=? LIMIT 1");
            $s->execute([$myInfo['phong_id'], $thang, $nam]);
            $hd = $s->fetch();
            if (!$hd) return "📄 Chưa có hóa đơn tháng $thang/$nam cho phòng {$myInfo['so_phong']}. Liên hệ quản lý để biết thêm.";
            $tt = $hd['trang_thai'] === 'da_tt' ? '✅ Đã thanh toán' : '❌ Chưa thanh toán';
            return "🧾 Hóa đơn tháng $thang/$nam — Phòng {$myInfo['so_phong']}:\n\n"
                 . "• Tiền phòng: " . number_format($hd['tien_phong']) . "đ\n"
                 . "• Tiền điện (" . $hd['chi_so_dien_cu'] . "→" . $hd['chi_so_dien_moi'] . " kWh): " . number_format($hd['tien_dien']) . "đ\n"
                 . "• Tiền nước (" . $hd['chi_so_nuoc_cu'] . "→" . $hd['chi_so_nuoc_moi'] . " m³): " . number_format($hd['tien_nuoc']) . "đ\n"
                 . "━━━━━━━━━━━━━━\n"
                 . "💰 Tổng: " . number_format($hd['tong_tien']) . "đ\n"
                 . "Trạng thái: $tt";
        }

        // Hợp đồng của tôi còn bao lâu
        if ($this->match($msg, ['hợp đồng của tôi','hd của tôi','còn bao lâu','hết hạn chưa','ngày hết hạn','hạn hợp đồng'])) {
            if (!$myInfo || empty($myInfo['hop_dong_id'])) {
                return "Bạn hiện không có hợp đồng hiệu lực. Liên hệ quản lý để ký hợp đồng mới.";
            }
            $kt   = strtotime($myInfo['ngay_ket_thuc']);
            $diff = (int)ceil(($kt - time()) / 86400);
            $icon = $diff > 30 ? '✅' : ($diff > 0 ? '⚠' : '❌');
            return "$icon Hợp đồng phòng {$myInfo['so_phong']}:\n\n"
                 . "• Bắt đầu: " . date('d/m/Y', strtotime($myInfo['ngay_bat_dau'])) . "\n"
                 . "• Kết thúc: " . date('d/m/Y', $kt) . "\n"
                 . ($diff > 0
                     ? "• Còn lại: $diff ngày\n"
                     : "• Đã quá hạn " . abs($diff) . " ngày!\n")
                 . "• Tiền cọc: " . number_format($myInfo['tien_coc']) . "đ\n\n"
                 . ($diff <= 30 && $diff > 0 ? "⚠ Sắp hết hạn! Liên hệ quản lý để gia hạn." : "");
        }

        // Phòng tôi đang ở
        if ($this->match($msg, ['phòng tôi','phòng của tôi','tôi ở phòng','tôi đang thuê','số phòng của tôi','số phòng hiện tại','phòng số mấy','tôi ở phòng nào','phòng tôi là','mình ở phòng'])) {
            if (!$myInfo || empty($myInfo['so_phong'])) {
                return "Bạn chưa được liên kết với phòng nào trong hệ thống.";
            }
            return "🏠 Thông tin phòng của bạn:\n\n"
                 . "• Số phòng: " . $myInfo['so_phong'] . "\n"
                 . "• Khu: " . ($myInfo['ten_khu'] ?? '—') . "\n"
                 . "• Giá thuê: " . number_format($myInfo['gia']) . "đ/tháng\n"
                 . "• Địa chỉ: " . ($myInfo['dia_chi_khu'] ?? '—');
        }

        // Xe của tôi
        if ($this->match($msg, ['xe của tôi','xe tôi đăng ký','biển số xe tôi'])) {
            if (!$myInfo || empty($myInfo['hop_dong_id'])) {
                return "Bạn chưa có hợp đồng hiệu lực nên chưa có xe đăng ký.";
            }
            $s = $db->prepare("SELECT * FROM xe WHERE hop_dong_id=?");
            $s->execute([$myInfo['hop_dong_id']]);
            $xeList = $s->fetchAll();
            if (empty($xeList)) return "🚗 Bạn chưa đăng ký xe nào. Liên hệ quản lý để đăng ký.";
            $result = "🚗 Xe của bạn đang đăng ký:\n\n";
            $loaiLabel = ['xe_may'=>'Xe máy','xe_dien'=>'Xe điện','xe_dap'=>'Xe đạp'];
            foreach ($xeList as $x) {
                $result .= "• {$x['bien_so']} — " . ($loaiLabel[$x['loai_xe']] ?? $x['loai_xe']);
                if ($x['mau_sac']) $result .= " ({$x['mau_sac']})";
                $result .= "\n";
            }
            $result .= "\nPhí giữ xe: 100.000đ/xe/tháng";
            return $result;
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 2: DỊCH VỤ & TIỆN ÍCH
        // ═══════════════════════════════════════════════════

        if ($this->match($msg, ['wifi','mật khẩu wifi','pass wifi','password wifi','internet'])) {
            return "📶 Thông tin Wifi:\n\n• Wifi miễn phí trong khuôn viên khu trọ\n• Phí: 150.000đ/phòng/tháng (gộp trong hóa đơn)\n• Mật khẩu: Liên hệ quản lý để được cung cấp\n📞 " . $this->getSdtQuanLy($db);
        }

        if ($this->match($msg, ['phí dịch vụ','phí wifi','phí rác','phí vệ sinh','dịch vụ'])) {
            return "💡 Phí dịch vụ hàng tháng:\n\n• Wifi + Rác + Vệ sinh: 150.000đ/phòng\n• Giữ xe: 100.000đ/xe/tháng\n\nĐã bao gồm trong hóa đơn hàng tháng.";
        }

        if ($this->match($msg, ['giờ đóng cổng','đóng cổng','mở cổng','giờ cổng','ra vào'])) {
            return "🚪 Giờ ra vào:\n\n• Cổng mở: 05:00 — 23:00\n• Sau 23h dùng khóa vân tay cổng chính\n• Cần thêm thông tin: Liên hệ quản lý\n📞 " . $this->getSdtQuanLy($db);
        }

        if ($this->match($msg, ['liên hệ','số điện thoại quản lý','sdt quản lý','gọi cho quản lý','contact','liên lạc'])) {
            $ql = $db->query("SELECT ho_ten, sdt, email FROM account WHERE vai_tro IN ('quan_ly','chu_tro') LIMIT 1")->fetch();
            if (!$ql) return "Vui lòng liên hệ trực tiếp với quản lý khu trọ.";
            return "📞 Thông tin liên hệ quản lý:\n\n• Họ tên: {$ql['ho_ten']}\n• SĐT: {$ql['sdt']}\n• Email: " . ($ql['email'] ?? '—') . "\n\nLiên hệ trong giờ hành chính: 7h–21h30.";
        }

        if ($this->match($msg, ['địa chỉ','dia chi','khu trọ ở đâu','ở đâu','địa điểm'])) {
            $rows = $db->query("SELECT ten_khu, dia_chi FROM khu_tro ORDER BY id")->fetchAll();
            if (empty($rows)) return "Vui lòng liên hệ quản lý để biết địa chỉ khu trọ.";
            $result = "📍 Địa chỉ khu trọ:\n\n";
            foreach ($rows as $r) {
                $result .= "• {$r['ten_khu']}: " . ($r['dia_chi'] ?? '—') . "\n";
            }
            return $result;
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 3: QUY TRÌNH THUÊ / TRẢ PHÒNG / CỌC
        // ═══════════════════════════════════════════════════

        if ($this->match($msg, ['thuê phòng','đặt phòng','quy trình thuê','muốn thuê','cách thuê'])) {
            return "📋 Quy trình thuê phòng:\n\n1. Liên hệ quản lý xem phòng trống\n2. Đặt cọc giữ phòng\n3. Ký hợp đồng + cung cấp CCCD\n4. Đóng tiền tháng đầu\n5. Nhận chìa khóa & mã vân tay\n\n📞 " . $this->getSdtQuanLy($db);
        }

        if ($this->match($msg, ['trả phòng','dọn ra','chuyển đi','ngưng hợp đồng','hủy hợp đồng'])) {
            return "📦 Quy trình trả phòng:\n\n• Báo trước tối thiểu 30 ngày (từ ngày 1-5 hàng tháng)\n• Dọn dẹp phòng sạch sẽ trước khi bàn giao\n• Thanh toán hết điện, nước, các chi phí phát sinh\n• Nhận lại tiền cọc (nếu không vi phạm)\n\n⚠ Không báo trước hoặc dọn đi trước hạn = mất cọc.";
        }

        if ($this->match($msg, ['tiền cọc','cọc','đặt cọc','hoàn cọc','lấy lại cọc'])) {
            if ($myInfo && !empty($myInfo['tien_coc'])) {
                return "💰 Tiền cọc phòng {$myInfo['so_phong']} của bạn: " . number_format($myInfo['tien_coc']) . "đ\n\n"
                     . "Điều kiện hoàn cọc:\n• Báo trả phòng đúng quy định (trước 30 ngày)\n• Không vi phạm hợp đồng\n• Thanh toán đầy đủ các khoản phí\n• Phòng sạch sẽ khi bàn giao";
            }
            return "💰 Tiền cọc:\n\n• Thường bằng 1 tháng tiền phòng\n• Hoàn lại khi trả phòng đúng quy định và không vi phạm\n• Liên hệ quản lý để biết mức cọc cụ thể\n📞 " . $this->getSdtQuanLy($db);
        }

        if ($this->match($msg, ['thanh toán','đóng tiền','trả tiền','cách đóng','chuyển khoản','qr'])) {
            $ql = $db->query("SELECT * FROM account WHERE vai_tro IN ('quan_ly','chu_tro') LIMIT 1")->fetch();
            return "💳 Cách thanh toán tiền phòng:\n\n1. Tiền mặt: Gặp trực tiếp quản lý\n2. Chuyển khoản: Quét QR Banking trong trang hóa đơn\n3. MoMo hoặc VNPay: Chọn đúng tab QR và nhập đúng số tiền, nội dung\n4. Thời hạn: Từ ngày 01-05 hàng tháng\n\n⚠ Đóng trễ sau ngày 5 có thể bị nhắc nhở.\n📞 " . $this->getSdtQuanLy($db);
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 4: XE / GỬI XE (ADMIN & USER)
        // ═══════════════════════════════════════════════════

        if ($this->match($msg, ['gửi xe','bãi xe','phí xe','đăng ký xe','thông tin xe','xe máy','xe đạp','xe điện','danh sách xe','biển số'])) {
            $rows = $db->query("
                SELECT x.bien_so, x.loai_xe, x.mau_sac, p.so_phong, k.ten_khu
                FROM xe x
                JOIN phong p ON x.phong_id = p.id
                LEFT JOIN khu_tro k ON p.khu_id = k.id
                ORDER BY p.so_phong
            ")->fetchAll();
            $loaiLabel = ['xe_may'=>'Xe máy','xe_dien'=>'Xe điện','xe_dap'=>'Xe đạp'];
            if ($vaiTro === 'user') {
                return "🚗 Thông tin gửi xe:\n\n• Phí giữ xe: 100.000đ/xe/tháng\n• Bãi xe có camera an ninh\n• Đăng ký xe: Liên hệ quản lý khi ký HĐ\n• Hỏi xe của bạn: Nhắn \"xe của tôi\"\n📞 " . $this->getSdtQuanLy($db);
            }
            if (empty($rows)) return "Chưa có xe nào đăng ký.";
            $result = "🚗 Danh sách xe đang đăng ký: " . count($rows) . " xe\n\n";
            foreach ($rows as $x) {
                $result .= "• [{$x['so_phong']}] {$x['bien_so']} — " . ($loaiLabel[$x['loai_xe']] ?? $x['loai_xe']);
                if ($x['mau_sac']) $result .= " ({$x['mau_sac']})";
                $result .= "\n";
            }
            $result .= "\nPhí: 100.000đ/xe/tháng";
            return $result;
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 5: PHÒNG (ADMIN & USER)
        // ═══════════════════════════════════════════════════

        if ($this->match($msg, ['phòng trống','phong trong','còn trống','con trong','phòng nào trống'])) {
            $rows = $db->query("SELECT p.so_phong, p.gia, p.dien_tich, p.so_nguoi, k.ten_khu FROM phong p LEFT JOIN khu_tro k ON p.khu_id=k.id WHERE p.trang_thai='trong' ORDER BY p.so_phong")->fetchAll();
            if (empty($rows)) return "Hiện tại không có phòng trống nào. Liên hệ quản lý để đặt tên vào danh sách chờ.\n📞 " . $this->getSdtQuanLy($db);
            $result = "🏠 Có " . count($rows) . " phòng trống:\n\n";
            foreach ($rows as $r) {
                $result .= "• Phòng {$r['so_phong']}";
                if ($r['ten_khu']) $result .= " ({$r['ten_khu']})";
                $result .= " — " . number_format($r['gia']) . "đ/tháng";
                if ($r['dien_tich']) $result .= " · {$r['dien_tich']}m²";
                if ($r['so_nguoi']) $result .= " · {$r['so_nguoi']} người";
                $result .= "\n";
            }
            return $result;
        }

        if ($this->match($msg, ['tổng phòng','bao nhiêu phòng','mấy phòng','tong phong','thống kê phòng'])) {
            $stats = $db->query("SELECT trang_thai, COUNT(*) as sl FROM phong GROUP BY trang_thai")->fetchAll();
            $total = 0; $detail = [];
            $labels = ['trong'=>'🟢 Trống','dang_thue'=>'🔵 Đang thuê','bao_tri'=>'🟡 Bảo trì'];
            foreach ($stats as $s) { $total += $s['sl']; $detail[] = ($labels[$s['trang_thai']] ?? $s['trang_thai']) . ": {$s['sl']}"; }
            return "🏠 Tổng cộng $total phòng:\n• " . implode("\n• ", $detail);
        }

        if ($this->match($msg, ['giá phòng','gia phong','bảng giá','bang gia','giá thuê tất cả'])) {
            $rows = $db->query("SELECT so_phong, gia, trang_thai FROM phong ORDER BY gia")->fetchAll();
            if (empty($rows)) return "Chưa có phòng nào trong hệ thống.";
            $result = "💰 Bảng giá phòng:\n\n";
            foreach ($rows as $r) {
                $tt = $r['trang_thai'] === 'trong' ? '🟢' : ($r['trang_thai'] === 'dang_thue' ? '🔵' : '🟡');
                $result .= "$tt {$r['so_phong']}: " . number_format($r['gia']) . "đ/tháng\n";
            }
            $result .= "\n🟢 Trống · 🔵 Đang thuê · 🟡 Bảo trì";
            return $result;
        }

        if ($this->match($msg, ['phòng bảo trì','bao tri','đang sửa','phòng hỏng'])) {
            $rows = $db->query("SELECT p.so_phong, k.ten_khu FROM phong p LEFT JOIN khu_tro k ON p.khu_id=k.id WHERE p.trang_thai='bao_tri'")->fetchAll();
            if (empty($rows)) return "🟢 Không có phòng nào đang bảo trì.";
            $result = "🟡 Phòng đang bảo trì: " . count($rows) . " phòng\n\n";
            foreach ($rows as $r) { $result .= "• Phòng {$r['so_phong']}" . ($r['ten_khu'] ? " ({$r['ten_khu']})" : "") . "\n"; }
            return $result;
        }

        // Thông tin phòng cụ thể
        if (preg_match('/ph[oòóô]ng\s*([A-Za-z]?\d+)/iu', $msg, $m)) {
            $soPhong = strtoupper($m[1]);
            $s = $db->prepare("SELECT p.*, k.ten_khu FROM phong p LEFT JOIN khu_tro k ON p.khu_id=k.id WHERE UPPER(p.so_phong)=?");
            $s->execute([$soPhong]);
            $p = $s->fetch();
            if ($p) {
                $tt = ['trong'=>'🟢 Còn trống','dang_thue'=>'🔵 Đang thuê','bao_tri'=>'🟡 Bảo trì'];
                return "🏠 Phòng {$p['so_phong']}:\n"
                     . "• Khu: " . ($p['ten_khu'] ?? '—') . "\n"
                     . "• Giá: " . number_format($p['gia']) . "đ/tháng\n"
                     . "• Diện tích: " . ($p['dien_tich'] ?: '—') . " m²\n"
                     . "• Sức chứa: {$p['so_nguoi']} người\n"
                     . "• Trạng thái: " . ($tt[$p['trang_thai']] ?? $p['trang_thai']) . "\n"
                     . ($p['mo_ta'] ? "• Mô tả: {$p['mo_ta']}" : "");
            }
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 6: HÓA ĐƠN / CÔNG NỢ (ADMIN)
        // ═══════════════════════════════════════════════════

        if ($this->match($msg, ['công nợ','cong no','chưa thanh toán','chua thanh toan','phòng nào chưa đóng','ai chưa đóng tiền'])) {
            if (!$isAdmin) {
                return '🔒 Chức năng này chỉ dành cho quản lý.';
            }
            $rows = $db->query("SELECT hd.*, p.so_phong, nt.ho_ten FROM hoa_don hd JOIN phong p ON hd.phong_id=p.id LEFT JOIN hop_dong hopd ON hopd.phong_id=p.id AND hopd.trang_thai='hieu_luc' LEFT JOIN nguoi_thue nt ON nt.id=hopd.nguoi_thue_id WHERE hd.trang_thai='chua_tt' ORDER BY hd.nam DESC, hd.thang DESC")->fetchAll();
            if (empty($rows)) return "✅ Không có công nợ! Tất cả hóa đơn đã được thanh toán.";
            $total = 0;
            $result = "⚠ Có " . count($rows) . " hóa đơn chưa thanh toán:\n\n";
            foreach ($rows as $r) {
                $result .= "• Phòng {$r['so_phong']}";
                if (!empty($r['ho_ten'])) $result .= " ({$r['ho_ten']})";
                $result .= " — T{$r['thang']}/{$r['nam']}: " . number_format($r['tong_tien']) . "đ\n";
                $total += $r['tong_tien'];
            }
            $result .= "\n💰 Tổng nợ: " . number_format($total) . "đ";
            return $result;
        }

        if ($this->match($msg, ['hóa đơn','hoa don','tiền tháng','tien thang','hóa đơn tháng này'])) {
            if (!$isAdmin) {
                return '🔒 Chức năng này chỉ dành cho quản lý.';
            }
            $thang = (int)date('m'); $nam = (int)date('Y');
            $s = $db->prepare("SELECT hd.*, p.so_phong FROM hoa_don hd JOIN phong p ON hd.phong_id=p.id WHERE hd.thang=? AND hd.nam=? ORDER BY p.so_phong");
            $s->execute([$thang, $nam]);
            $list = $s->fetchAll();
            if (empty($list)) return "📄 Chưa có hóa đơn tháng $thang/$nam.";
            $paid = count(array_filter($list, fn($x) => $x['trang_thai'] === 'da_tt'));
            $result = "📄 Hóa đơn tháng $thang/$nam: " . count($list) . " phòng ($paid đã TT, " . (count($list)-$paid) . " chưa TT)\n\n";
            foreach ($list as $r) {
                $icon = $r['trang_thai'] === 'da_tt' ? '✅' : '❌';
                $result .= "$icon {$r['so_phong']}: " . number_format($r['tong_tien']) . "đ\n";
            }
            return $result;
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 7: HỢP ĐỒNG (ADMIN)
        // ═══════════════════════════════════════════════════

        if ($this->match($msg, ['sắp hết hạn','sap het han','hợp đồng hết hạn','hết hạn 30','hết hạn tháng này'])) {
            if (!$isAdmin) {
                return '🔒 Chức năng này chỉ dành cho quản lý.';
            }
            $rows = $db->query("SELECT hd.*, p.so_phong, nt.ho_ten, nt.sdt FROM hop_dong hd JOIN phong p ON hd.phong_id=p.id JOIN nguoi_thue nt ON hd.nguoi_thue_id=nt.id WHERE hd.trang_thai='hieu_luc' AND hd.ngay_ket_thuc BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY hd.ngay_ket_thuc")->fetchAll();
            if (empty($rows)) return "✅ Không có hợp đồng nào sắp hết hạn trong 30 ngày tới.";
            $result = "⚠ " . count($rows) . " hợp đồng sắp hết hạn:\n\n";
            foreach ($rows as $r) {
                $days = (int)ceil((strtotime($r['ngay_ket_thuc']) - time()) / 86400);
                $result .= "• Phòng {$r['so_phong']} — {$r['ho_ten']}";
                if ($r['sdt']) $result .= " (📱{$r['sdt']})";
                $result .= " — còn $days ngày (" . date('d/m/Y', strtotime($r['ngay_ket_thuc'])) . ")\n";
            }
            return $result;
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 8: NGƯỜI THUÊ (ADMIN)
        // ═══════════════════════════════════════════════════

        if ($this->match($msg, ['người thuê','nguoi thue','khách thuê','bao nhiêu người','danh sách người thuê'])) {
            if (!$isAdmin) {
                return '🔒 Chức năng này chỉ dành cho quản lý.';
            }
            $total  = (int)$db->query("SELECT COUNT(*) FROM nguoi_thue")->fetchColumn();
            $active = (int)$db->query("SELECT COUNT(DISTINCT nguoi_thue_id) FROM hop_dong WHERE trang_thai='hieu_luc'")->fetchColumn();
            return "👥 Thống kê người thuê:\n• Tổng hồ sơ: $total\n• Đang có HĐ: $active\n• Không có HĐ: " . ($total - $active);
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 9: DOANH THU (ADMIN)
        // ═══════════════════════════════════════════════════

        if ($this->match($msg, ['doanh thu','thu nhập','đã thu','tổng thu','so sánh tháng','doanh thu tháng trước'])) {
            if (!$isAdmin) {
                return "🔒 Bạn không có quyền xem thông tin doanh thu.
Chức năng này chỉ dành cho quản lý.";
            }
            $thang = (int)date('m'); $nam = (int)date('Y');
            $thangTruoc = $thang === 1 ? 12 : $thang - 1;
            $namTruoc   = $thang === 1 ? $nam - 1 : $nam;

            $s1 = $db->prepare("SELECT COALESCE(SUM(tong_tien),0) FROM hoa_don WHERE thang=? AND nam=? AND trang_thai='da_tt'");
            $s1->execute([$thang, $nam]); $dtThang = (float)$s1->fetchColumn();

            $s2 = $db->prepare("SELECT COALESCE(SUM(tong_tien),0) FROM hoa_don WHERE thang=? AND nam=? AND trang_thai='da_tt'");
            $s2->execute([$thangTruoc, $namTruoc]); $dtTruoc = (float)$s2->fetchColumn();

            $s3 = $db->prepare("SELECT COALESCE(SUM(tong_tien),0) FROM hoa_don WHERE nam=? AND trang_thai='da_tt'");
            $s3->execute([$nam]); $dtNam = (float)$s3->fetchColumn();

            $chenh = $dtThang - $dtTruoc;
            $icon  = $chenh >= 0 ? '↑' : '↓';

            return "💰 Doanh thu:\n\n"
                 . "• Tháng $thang/$nam: " . number_format($dtThang) . "đ\n"
                 . "• Tháng $thangTruoc/$namTruoc: " . number_format($dtTruoc) . "đ\n"
                 . "• Chênh lệch: $icon " . number_format(abs($chenh)) . "đ\n"
                 . "━━━━━━━━━━━━━━\n"
                 . "• Cả năm $nam: " . number_format($dtNam) . "đ";
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 10: ĐƠN GIÁ / KHU TRỌ
        // ═══════════════════════════════════════════════════

        if ($this->match($msg, ['giá điện','gia dien','giá nước','gia nuoc','đơn giá','don gia','điện bao nhiêu','nước bao nhiêu','tiền điện','tiền nước','tien dien','tien nuoc','điện nước','xem tiền điện','xem tiền nước','chi phí điện','chi phí nước'])) {
            $dg = $db->query("SELECT * FROM don_gia ORDER BY id DESC LIMIT 1")->fetch();
            if (!$dg) return "Chưa cài đặt đơn giá. Liên hệ quản lý.";
            $result = "⚡ Đơn giá hiện tại:\n\n• Điện: " . number_format($dg['gia_dien']) . "đ/kWh\n• Nước: " . number_format($dg['gia_nuoc']) . "đ/m³\n• Wifi + Rác: 150.000đ/phòng/tháng\n• Giữ xe: 100.000đ/xe/tháng";
            // Nếu user đang thuê phòng, hiện thêm chi phí điện/nước tháng này
            if ($myInfo && !empty($myInfo['phong_id'])) {
                $thang = (int)date('m'); $nam = (int)date('Y');
                $s = $db->prepare("SELECT * FROM hoa_don WHERE phong_id=? AND thang=? AND nam=? LIMIT 1");
                $s->execute([$myInfo['phong_id'], $thang, $nam]);
                $hd = $s->fetch();
                if ($hd) {
                    $result .= "\n\n📋 Tháng $thang/$nam — Phòng {$myInfo['so_phong']}:\n"
                             . "• Điện (" . $hd['chi_so_dien_cu'] . " → " . $hd['chi_so_dien_moi'] . " kWh): " . number_format($hd['tien_dien']) . "đ\n"
                             . "• Nước (" . $hd['chi_so_nuoc_cu'] . " → " . $hd['chi_so_nuoc_moi'] . " m³): " . number_format($hd['tien_nuoc']) . "đ";
                }
            }
            return $result;
        }

        if ($this->match($msg, ['khu trọ','khu tro','mấy khu','may khu','các khu'])) {
            $rows = $db->query("SELECT k.*, COUNT(p.id) as so_phong, SUM(p.trang_thai='trong') as so_trong FROM khu_tro k LEFT JOIN phong p ON p.khu_id=k.id GROUP BY k.id ORDER BY k.ma_khu")->fetchAll();
            if (empty($rows)) return "Chưa có khu trọ nào.";
            $result = "🏘 Danh sách khu trọ:\n\n";
            foreach ($rows as $r) {
                $result .= "• {$r['ten_khu']} ({$r['ma_khu']}) — {$r['so_phong']} phòng, {$r['so_trong']} trống";
                if ($r['dia_chi']) $result .= "\n  📍 {$r['dia_chi']}";
                $result .= "\n";
            }
            return $result;
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 11: NỘI QUY
        // ═══════════════════════════════════════════════════

        if ($this->match($msg, ['nội quy','noi quy','quy định','quy dinh','luật','luat','quy tắc'])) {
            return "📋 Nội quy khu trọ:\n\n"
                 . "1. 🔕 Không làm ồn sau 21h30\n"
                 . "2. 🚗 Để xe đúng nơi quy định, đúng vạch\n"
                 . "3. 🧹 Giữ vệ sinh khu vực chung\n"
                 . "4. 🐾 Không nuôi thú cưng (trừ khi được phép)\n"
                 . "5. 🚫 Không tàng trữ chất cấm, cờ bạc\n"
                 . "6. 📅 Báo trước 30 ngày nếu muốn trả phòng\n"
                 . "7. 💰 Đóng tiền phòng từ ngày 1-5 hàng tháng\n"
                 . "8. 📝 Đăng ký tạm trú theo quy định\n"
                 . "9. 👥 Dắt bạn về phải báo quản lý\n"
                 . "10. 🔧 Muốn sửa chữa phải xin phép trước";
        }

        // ═══════════════════════════════════════════════════
        //  NHÓM 12: GỢI Ý PHÒNG THEO NGÂN SÁCH
        // ═══════════════════════════════════════════════════

        if (preg_match('/(\d[\d\.,]*)\s*(triệu|tr|trieu|k|nghìn|ngan)/iu', $msg, $m) &&
            $this->match($msg, ['ngân sách','ngan sach','tầm','khoảng','dưới','trong tầm','phù hợp'])) {
            $num = (float)str_replace([',','.'], ['',''], $m[1]);
            $don = strtolower($m[2]);
            $budget = in_array($don, ['triệu','tr','trieu']) ? $num * 1000000 : $num * 1000;
            $s = $db->prepare("SELECT so_phong, gia, dien_tich FROM phong WHERE trang_thai='trong' AND gia <= ? ORDER BY gia DESC LIMIT 5");
            $s->execute([$budget]);
            $list = $s->fetchAll();
            if (empty($list)) return "😔 Hiện không có phòng trống nào trong ngân sách " . number_format($budget) . "đ. Liên hệ quản lý để tư vấn thêm.\n📞 " . $this->getSdtQuanLy($db);
            $result = "💡 Phòng phù hợp với ngân sách ~" . number_format($budget) . "đ:\n\n";
            foreach ($list as $r) {
                $result .= "• Phòng {$r['so_phong']}: " . number_format($r['gia']) . "đ/tháng";
                if ($r['dien_tich']) $result .= " · {$r['dien_tich']}m²";
                $result .= "\n";
            }
            $result .= "\n📞 Liên hệ để đặt lịch xem phòng: " . $this->getSdtQuanLy($db);
            return $result;
        }

        // ═══ HƯỚNG DẪN ═══
        if ($this->match($msg, ['giúp','help','hướng dẫn','huong dan','làm gì','lam gi','hỏi gì','lệnh'])) {
            $result = "🤖 Tôi có thể giúp bạn:\n\n";
            if (!$isAdmin) {
                $result .= "👤 Về phòng của bạn:\n"
                         . "• \"Tiền tháng này\" — Hóa đơn của bạn\n"
                         . "• \"Hợp đồng của tôi\" — Thời hạn HĐ\n"
                         . "• \"Phòng tôi\" — Thông tin phòng\n"
                         . "• \"Xe của tôi\" — Xe đăng ký\n\n";
            }
            $result .= "🏠 Phòng & Khu trọ:\n"
                     . "• \"Phòng trống\" — Xem phòng còn trống\n"
                     . "• \"Giá phòng\" — Bảng giá\n"
                     . "• \"Phòng A101\" — Thông tin phòng cụ thể\n"
                     . "• \"Ngân sách 3 triệu\" — Gợi ý phòng phù hợp\n\n"
                     . "💡 Dịch vụ:\n"
                     . "• \"Wifi\" — Thông tin wifi\n"
                     . "• \"Phí dịch vụ\" — Các loại phí\n"
                     . "• \"Giờ đóng cổng\" — Giờ ra vào\n"
                     . "• \"Liên hệ\" — SĐT quản lý\n"
                     . "• \"Đơn giá\" — Giá điện nước\n\n"
                     . "📋 Quy trình:\n"
                     . "• \"Thuê phòng\" — Quy trình thuê\n"
                     . "• \"Trả phòng\" — Quy trình trả\n"
                     . "• \"Tiền cọc\" — Thông tin cọc\n"
                     . "• \"Nội quy\" — Quy định khu trọ\n";
            if ($isAdmin) {
                $result .= "\n📊 Quản lý:\n"
                         . "• \"Công nợ\" — Hóa đơn chưa TT\n"
                         . "• \"Doanh thu\" — So sánh tháng\n"
                         . "• \"Sắp hết hạn\" — HĐ sắp hết\n"
                         . "• \"Xe\" — Danh sách xe\n";
            }
            return $result;
        }

        // ═══ MẶC ĐỊNH — LƯU CÂU HỎI CHƯA BIẾT ═══
        $this->saveUnknown($db, $message, $accountId, $hoTen);
        return "🤔 Tôi chưa hiểu câu hỏi này.\n\nMình đã ghi lại và sẽ học để trả lời bạn sau!\n\nThử hỏi:\n• \"Phòng trống\" · \"Giá phòng\"\n• \"Tiền tháng này\" · \"Hợp đồng tôi\"\n• \"Liên hệ\" · \"Nội quy\"\n• \"Giúp\" — Xem tất cả lệnh";
    }

    // ═══ TÌM TRONG KHO KIẾN THỨC ĐÃ HỌC ═══
    private function searchKnowledge(PDO $db, string $msg): ?string
    {
        try {
            $rows = $db->query("SELECT * FROM ai_knowledge ORDER BY so_lan_dung DESC")->fetchAll();
        } catch (\Exception $e) {
            return null; // Bảng chưa tồn tại
        }
        if (empty($rows)) return null;

        $bestMatch  = null;
        $bestScore  = 0.0;
        $threshold  = 0.35; // Ngưỡng tối thiểu để match

        foreach ($rows as $row) {
            // Bước 1: Match từ khóa cứng (ưu tiên cao nhất)
            $keywords = array_map('trim', explode(',', mb_strtolower($row['tu_khoa'], 'UTF-8')));
            foreach ($keywords as $kw) {
                if ($kw && mb_strpos($msg, $kw, 0, 'UTF-8') !== false) {
                    // Match từ khóa → trả lời ngay, tăng counter
                    $db->prepare("UPDATE ai_knowledge SET so_lan_dung=so_lan_dung+1 WHERE id=?")
                       ->execute([$row['id']]);
                    return $row['tra_loi'];
                }
            }

            // Bước 2: Fuzzy match với câu hỏi mẫu
            $score = $this->similarity($msg, mb_strtolower($row['cau_hoi_mau'], 'UTF-8'));
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $row;
            }
        }

        // Nếu độ giống nhau đủ ngưỡng → dùng câu trả lời tốt nhất
        if ($bestScore >= $threshold && $bestMatch) {
            $db->prepare("UPDATE ai_knowledge SET so_lan_dung=so_lan_dung+1 WHERE id=?")
               ->execute([$bestMatch['id']]);
            return $bestMatch['tra_loi'];
        }

        return null;
    }

    // ═══ TÍNH ĐỘ GIỐNG NHAU (JACCARD SIMILARITY) ═══
    private function similarity(string $a, string $b): float
    {
        // Chuẩn hóa: bỏ dấu câu, tách từ
        $normalize = fn(string $s) => preg_split('/\s+/', trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $s)));

        $wordsA = array_filter($normalize($a), fn($w) => mb_strlen($w) > 1);
        $wordsB = array_filter($normalize($b), fn($w) => mb_strlen($w) > 1);

        if (empty($wordsA) || empty($wordsB)) return 0.0;

        $intersection = count(array_intersect($wordsA, $wordsB));
        $union        = count(array_unique(array_merge($wordsA, $wordsB)));

        return $union > 0 ? $intersection / $union : 0.0;
    }

    // ═══ LƯU CÂU HỎI CHƯA BIẾT ═══
    private function saveUnknown(PDO $db, string $message, int $userId, string $hoTen): void
    {
        try {
            // Nếu câu hỏi đã tồn tại → tăng counter
            $existing = $db->prepare(
                "SELECT id FROM ai_unknown WHERE cau_hoi=? AND da_xu_ly=0 LIMIT 1"
            );
            $existing->execute([$message]);
            $row = $existing->fetch();

            if ($row) {
                $db->prepare("UPDATE ai_unknown SET so_lan=so_lan+1 WHERE id=?")
                   ->execute([$row['id']]);
            } else {
                $db->prepare(
                    "INSERT INTO ai_unknown (cau_hoi, user_id, ho_ten) VALUES (?,?,?)"
                )->execute([$message, $userId ?: null, $hoTen]);
            }
        } catch (\Exception $e) {
            // Bảng chưa tồn tại → bỏ qua
        }
    }

    // Helper lấy SĐT quản lý
    private function getSdtQuanLy(\PDO $db): string
    {
        $s = $db->query("SELECT sdt, ho_ten FROM account WHERE vai_tro IN ('quan_ly','chu_tro') LIMIT 1")->fetch();
        return $s ? "📞 {$s['ho_ten']}: {$s['sdt']}" : "Liên hệ trực tiếp quản lý khu trọ";
    }

    private function match(string $msg, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (mb_strpos($msg, $kw, 0, 'UTF-8') !== false) return true;
        }
        return false;
    }

    private function callClaude(string $message): string
    {
        $apiKey = CLAUDE_API_KEY;
        if (!$apiKey || str_starts_with($apiKey, 'sk-ant-xxx')) {
            return 'Chưa cấu hình CLAUDE_API_KEY trong config/ai.php';
        }

        $payload = json_encode([
            'model'      => CLAUDE_MODEL,
            'max_tokens' => 1024,
            'system'     => AI_SYSTEM_PROMPT,
            'messages'   => [
                ['role' => 'user', 'content' => $message]
            ],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: '        . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS  => $payload,
            CURLOPT_TIMEOUT     => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $err = json_decode($response, true);
            throw new \Exception($err['error']['message'] ?? "HTTP $httpCode");
        }

        $data = json_decode($response, true);
        return $data['content'][0]['text'] ?? 'Không có phản hồi.';
    }

    private function callGemini(string $message): string
    {
        $apiKey = GEMINI_API_KEY;
        if (!$apiKey) return 'Chưa cấu hình GEMINI_API_KEY trong config/ai.php';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . $apiKey;
        $payload = json_encode(['contents' => [['role' => 'user', 'parts' => [['text' => AI_SYSTEM_PROMPT . "\n\nNgười dùng hỏi: " . $message]]]],'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 1024]]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => $payload, CURLOPT_TIMEOUT => 30, CURLOPT_SSL_VERIFYPEER => false]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) { $err = json_decode($response, true); throw new \Exception($err['error']['message'] ?? "HTTP $httpCode"); }
        $data = json_decode($response, true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Không có phản hồi.';
    }

    private function callOpenAI(string $message): string
    {
        $apiKey = OPENAI_API_KEY;
        if (!$apiKey) return 'Chưa cấu hình OPENAI_API_KEY trong config/ai.php';
        $payload = json_encode(['model' => OPENAI_MODEL, 'messages' => [['role' => 'system', 'content' => AI_SYSTEM_PROMPT], ['role' => 'user', 'content' => $message]], 'temperature' => 0.7, 'max_tokens' => 1024]);
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey], CURLOPT_POSTFIELDS => $payload, CURLOPT_TIMEOUT => 30, CURLOPT_SSL_VERIFYPEER => false]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200) { $err = json_decode($response, true); throw new \Exception($err['error']['message'] ?? "HTTP $httpCode"); }
        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? 'Không có phản hồi.';
    }
}
