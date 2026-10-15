<?php
/**
 * AiKnowledgeController — Trang admin quản lý kiến thức AI
 * Route: index.php?controller=aiknowledge&action=index
 */
class AiKnowledgeController
{
    private PDO $db;

    public function __construct()
    {
        // Chỉ admin/quản lý mới vào được
        if (!in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'admin'])) {
            header('Location: index.php?controller=dashboard&action=index');
            exit;
        }
        $this->db = Database::getInstance();
        $this->ensureTables();
    }

    // Tạo bảng nếu chưa có
    private function ensureTables(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS `ai_knowledge` (
                `id`          INT AUTO_INCREMENT PRIMARY KEY,
                `tu_khoa`     TEXT NOT NULL,
                `cau_hoi_mau` TEXT NOT NULL,
                `tra_loi`     TEXT NOT NULL,
                `so_lan_dung` INT DEFAULT 0,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS `ai_unknown` (
                `id`         INT AUTO_INCREMENT PRIMARY KEY,
                `cau_hoi`    TEXT NOT NULL,
                `user_id`    INT DEFAULT NULL,
                `ho_ten`     VARCHAR(100) DEFAULT NULL,
                `so_lan`     INT DEFAULT 1,
                `da_xu_ly`   TINYINT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // Nạp kiến thức mặc định
    public function seedDefault(): void
    {
        $seeds = [
            [
                'tu_khoa'     => 'thú cưng, nuôi chó, nuôi mèo, chó mèo, pet',
                'cau_hoi_mau' => 'có nuôi thú cưng được không',
                'tra_loi'     => "🐾 Về nuôi thú cưng:\n\n• Khu trọ KHÔNG cho phép nuôi thú cưng (chó, mèo...)\n• Quy định này để đảm bảo vệ sinh và không ảnh hưởng người xung quanh\n• Vi phạm có thể bị xử lý theo điều khoản hợp đồng\n\nXin thông cảm và hợp tác! 🙏",
            ],
            [
                'tu_khoa'     => 'máy giặt, giặt đồ, giặt quần áo, phơi đồ, giặt ủi',
                'cau_hoi_mau' => 'có máy giặt không, giặt đồ ở đâu',
                'tra_loi'     => "👕 Về giặt đồ:\n\n• Mỗi phòng tự trang bị thiết bị cá nhân\n• Có khu phơi đồ chung tại mỗi khu\n• Không phơi đồ ở ban công mặt tiền\n• Giữ khu phơi đồ gọn gàng, sạch sẽ\n\n📞 Liên hệ quản lý để biết thêm chi tiết.",
            ],
            [
                'tu_khoa'     => 'nấu ăn, bếp, bếp gas, bếp điện, nấu cơm, nồi cơm',
                'cau_hoi_mau' => 'có được nấu ăn trong phòng không',
                'tra_loi'     => "🍳 Về nấu ăn trong phòng:\n\n✅ Được phép nấu ăn\n❌ Không dùng bếp than hoặc bếp gas mini không an toàn\n• Nên dùng bếp điện/bếp từ để an toàn\n• Giữ phòng thông thoáng khi nấu\n• Không để mùi thức ăn gây phiền hàng xóm",
            ],
            [
                'tu_khoa'     => 'báo hỏng, sửa chữa, hư hỏng, bị hỏng, xin sửa, báo sự cố, điện hỏng, nước hỏng',
                'cau_hoi_mau' => 'muốn báo phòng bị hỏng thì làm sao',
                'tra_loi'     => "🔧 Quy trình báo hỏng/sửa chữa:\n\n1. Liên hệ quản lý qua chat hoặc SĐT\n2. Mô tả rõ sự cố (hỏng gì, ở đâu)\n3. Quản lý sẽ sắp xếp thợ sửa\n\n💡 Lưu ý:\n• Hỏng do sử dụng sai → Bên B tự chi phí\n• Hỏng tự nhiên → Bên A chịu chi phí",
            ],
            [
                'tu_khoa'     => 'tạm trú, đăng ký tạm trú, giấy tờ tạm trú, kk tạm trú, hộ khẩu tạm trú',
                'cau_hoi_mau' => 'làm thủ tục tạm trú như thế nào',
                'tra_loi'     => "📋 Thủ tục đăng ký tạm trú:\n\n1. Mang CCCD/CMND bản gốc cho quản lý\n2. Quản lý hỗ trợ thủ tục với công an phường\n3. Thời hạn: Trong 30 ngày sau khi chuyển vào\n4. Chi phí: Miễn phí\n\n⚠ Bắt buộc theo quy định pháp luật — không được bỏ qua!",
            ],
            [
                'tu_khoa'     => 'khóa vân tay, mã cổng, mở cổng, đăng ký vân tay, quên mã, mã pin cổng',
                'cau_hoi_mau' => 'đăng ký vân tay cổng như thế nào',
                'tra_loi'     => "🔐 Khóa vân tay & mã cổng:\n\n• Đăng ký vân tay: Liên hệ quản lý để được hỗ trợ\n• Mỗi phòng được cấp mã PIN riêng\n• Quên mã: Liên hệ quản lý để reset\n• Tuyệt đối không chia sẻ mã PIN cho người lạ\n\n📞 Liên hệ quản lý để đăng ký hoặc reset mã.",
            ],
            [
                'tu_khoa'     => 'điều hòa, máy lạnh, remote điều hòa, điều hòa hỏng, điều hòa không mát',
                'cau_hoi_mau' => 'phòng có điều hòa không, điều hòa bị hỏng',
                'tra_loi'     => "❄ Về điều hòa:\n\n• Một số phòng đã có điều hòa (xem mô tả từng phòng)\n• Điều hòa là tài sản của chủ nhà\n• Hỏng tự nhiên → Quản lý sửa miễn phí\n• Hỏng do dùng sai → Người thuê bồi thường\n\n💡 Tắt điều hòa khi ra ngoài để tiết kiệm điện!",
            ],
            [
                'tu_khoa'     => 'nước nóng, bình nóng lạnh, tắm nước nóng, nóng lạnh hỏng',
                'cau_hoi_mau' => 'phòng có bình nóng lạnh không',
                'tra_loi'     => "🚿 Về nước nóng lạnh:\n\n• Một số phòng trang bị bình nóng lạnh\n• Kiểm tra mô tả phòng để biết chi tiết\n• Bình hỏng: Báo ngay cho quản lý\n• Tiết kiệm: Tắt bình khi không sử dụng\n\nLiên hệ quản lý để kiểm tra phòng mình có không.",
            ],
            [
                'tu_khoa'     => 'gia hạn, tái ký, ký lại, tiếp tục thuê, muốn ở thêm, ở thêm, hết hạn thì sao',
                'cau_hoi_mau' => 'muốn gia hạn hợp đồng thì làm gì',
                'tra_loi'     => "📝 Gia hạn hợp đồng:\n\n1. Liên hệ quản lý TRƯỚC khi HĐ hết hạn 30 ngày\n2. Quản lý sẽ lập hợp đồng mới\n3. Giá thuê có thể điều chỉnh theo thỏa thuận\n\n⚠ Không gia hạn = cần báo trước 30 ngày để trả phòng.\n\n📞 Liên hệ sớm để tránh gián đoạn!",
            ],
            [
                'tu_khoa'     => 'thêm người, người ở ghép, ở ghép, cho bạn ở cùng, thêm thành viên, ở cùng',
                'cau_hoi_mau' => 'muốn thêm người vào ở cùng phòng',
                'tra_loi'     => "👥 Thêm người ở cùng:\n\n• PHẢI báo quản lý trước khi cho người khác vào ở\n• Người mới cần cung cấp CCCD để đăng ký tạm trú\n• Số người tối đa: Theo sức chứa trong hợp đồng\n• Tự ý cho người lạ vào ở = Vi phạm HĐ & MẤT CỌC\n\n⚠ Luôn thông báo quản lý trước khi cho ai vào ở!",
            ],
            [
                'tu_khoa'     => 'camera, an ninh, giám sát, cctv, trộm cắp, mất đồ, mất xe',
                'cau_hoi_mau' => 'khu trọ có camera an ninh không',
                'tra_loi'     => "📷 An ninh khu trọ:\n\n✅ Camera an ninh: Cổng & khu vực chung\n✅ Bảo vệ trông giữ xe\n✅ Khóa vân tay cổng chính\n✅ Mọi ra vào đều được ghi lại\n\n🔒 Khu trọ được bảo vệ 24/7 bằng hệ thống camera và khóa điện tử.",
            ],
            [
                'tu_khoa'     => 'đổ rác, thùng rác, rác sinh hoạt, thu rác, bỏ rác',
                'cau_hoi_mau' => 'đổ rác ở đâu, rác thu mấy giờ',
                'tra_loi'     => "🗑 Về rác sinh hoạt:\n\n• Thùng rác đặt tại khu vực quy định (gần cầu thang)\n• Đổ rác vào thùng, không vứt bừa bãi\n• Rác thu gom hàng ngày\n• Phí vệ sinh: Đã gộp trong 150.000đ/tháng dịch vụ\n\n🌿 Giữ vệ sinh chung là trách nhiệm của mọi người!",
            ],
            [
                'tu_khoa'     => 'khách thăm, bạn bè đến chơi, người thân đến, đăng ký khách, khách qua đêm',
                'cau_hoi_mau' => 'bạn bè đến thăm có cần báo quản lý không',
                'tra_loi'     => "👋 Về khách thăm:\n\n✅ Khách thăm ban ngày: Không cần báo\n⚠ Khách ở lại qua đêm: Phải báo quản lý + đăng ký CCCD\n❌ Sau 23h: Khách cần ra về\n❌ Không tổ chức tiệc ồn ào\n\n📌 Dắt bạn về phòng phải gặp quản lý để trình giấy tờ.",
            ],
            [
                'tu_khoa'     => 'đăng ký xe mới, thêm xe, mua xe mới, xe mới, gửi thêm xe',
                'cau_hoi_mau' => 'mua xe mới muốn đăng ký gửi thì làm sao',
                'tra_loi'     => "🏍 Đăng ký xe gửi mới:\n\n1. Liên hệ quản lý để đăng ký thêm xe\n2. Cung cấp: Biển số, loại xe, màu sắc\n3. Phí giữ xe: 100.000đ/xe/tháng\n4. Xe đăng ký mới tính từ tháng tiếp theo\n\n📞 Liên hệ quản lý để đăng ký!",
            ],
            [
                'tu_khoa'     => 'mất điện, cúp điện, điện bị cắt, mất nước, nước bị cắt, cúp nước',
                'cau_hoi_mau' => 'phòng bị mất điện mất nước phải làm gì',
                'tra_loi'     => "⚡ Khi mất điện/mất nước:\n\n🔌 Mất điện:\n• Kiểm tra CB (cầu dao) trong phòng trước\n• Nếu cả khu mất → Báo ngay quản lý\n• SĐT quản lý phụ trách sự cố khẩn cấp\n\n💧 Mất nước:\n• Báo quản lý để kiểm tra đường ống\n• Có thể do bể chứa cạn → Quản lý bơm lại\n\n📞 Liên hệ quản lý ngay!",
            ],
        ];

        $inserted = 0;
        foreach ($seeds as $s) {
            // Bỏ qua nếu từ khóa đã tồn tại
            $check = $this->db->prepare(
                "SELECT id FROM ai_knowledge WHERE tu_khoa=? LIMIT 1"
            );
            $check->execute([$s['tu_khoa']]);
            if ($check->fetch()) continue;

            $this->db->prepare(
                "INSERT INTO ai_knowledge (tu_khoa, cau_hoi_mau, tra_loi) VALUES (?,?,?)"
            )->execute([$s['tu_khoa'], $s['cau_hoi_mau'], $s['tra_loi']]);
            $inserted++;
        }

        header('Location: index.php?controller=aiknowledge&action=index&tab=knowledge&msg=' . urlencode("Đã nạp $inserted kiến thức mặc định!"));
        exit;
    }

    // Trang chính
    public function index(): void
    {
        $tab = $_GET['tab'] ?? 'unknown';
        $msg = $_GET['msg'] ?? '';
        $err = '';

        // Xử lý POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $action = $_POST['_action'] ?? '';

            // Thêm kiến thức mới
            if ($action === 'add_knowledge') {
                $tu_khoa     = trim($_POST['tu_khoa']     ?? '');
                $cau_hoi_mau = trim($_POST['cau_hoi_mau'] ?? '');
                $tra_loi     = trim($_POST['tra_loi']     ?? '');
                if (!$tu_khoa || !$cau_hoi_mau || !$tra_loi) {
                    $err = 'Vui lòng điền đầy đủ thông tin!';
                } else {
                    $this->db->prepare(
                        "INSERT INTO ai_knowledge (tu_khoa, cau_hoi_mau, tra_loi) VALUES (?,?,?)"
                    )->execute([$tu_khoa, $cau_hoi_mau, $tra_loi]);
                    // Đánh dấu đã xử lý nếu dạy từ câu hỏi chưa biết
                    if (!empty($_POST['unknown_id'])) {
                        $this->db->prepare("UPDATE ai_unknown SET da_xu_ly=1 WHERE id=?")
                                 ->execute([(int)$_POST['unknown_id']]);
                    }
                    $msg = 'Đã thêm kiến thức mới cho AI!';
                    $tab = 'knowledge';
                }
            }

            // Xóa kiến thức
            if ($action === 'delete_knowledge') {
                $this->db->prepare("DELETE FROM ai_knowledge WHERE id=?")
                         ->execute([(int)($_POST['id'] ?? 0)]);
                $msg = 'Đã xóa kiến thức.';
            }

            // Xóa câu hỏi chưa biết
            if ($action === 'delete_unknown') {
                $this->db->prepare("DELETE FROM ai_unknown WHERE id=?")
                         ->execute([(int)($_POST['id'] ?? 0)]);
                $msg = 'Đã xóa.';
            }

            // Đánh dấu đã xử lý
            if ($action === 'mark_done') {
                $this->db->prepare("UPDATE ai_unknown SET da_xu_ly=1 WHERE id=?")
                         ->execute([(int)($_POST['id'] ?? 0)]);
                $msg = 'Đã đánh dấu xử lý.';
            }
        }

        // Load dữ liệu
        $unknownList   = $this->db->query(
            "SELECT * FROM ai_unknown ORDER BY da_xu_ly ASC, so_lan DESC, created_at DESC"
        )->fetchAll();
        $knowledgeList = $this->db->query(
            "SELECT * FROM ai_knowledge ORDER BY so_lan_dung DESC, created_at DESC"
        )->fetchAll();
        $soUnknownMoi  = (int)$this->db->query(
            "SELECT COUNT(*) FROM ai_unknown WHERE da_xu_ly=0"
        )->fetchColumn();

        $title = 'Huấn luyện AI';
        require 'frontend/views/Ai/knowledge.php';
    }
}