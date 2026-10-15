<?php
class HopDongController {
    private HopDongModel        $model;
    private PhongModel          $phongModel;
    private NguoiThueModel      $ntModel;
    private NguoiThuePhongModel $ntPhongModel;
    private XeModel             $xeModel;
    private KhuTroModel         $khuModel;

    public function __construct() {
        $this->model        = new HopDongModel();
        $this->phongModel   = new PhongModel();
        $this->ntModel      = new NguoiThueModel();
        $this->ntPhongModel = new NguoiThuePhongModel();
        $this->xeModel      = new XeModel();
        $this->khuModel     = new KhuTroModel();
    }

    public function index() {
        $filter = $_GET['filter'] ?? 'all';
        $trangThai = ($filter !== 'all') ? $filter : null;
        $list = $this->model->getAll($trangThai);
        foreach ($list as &$hd) {
            $hd['nguoi_o_cung'] = $this->ntPhongModel->getByHopDong($hd['id']);
            $hd['xe_list']      = $this->xeModel->getByHopDong($hd['id']);
        }
        unset($hd);
        $title = 'Quản lý hợp đồng';
        require 'frontend/views/HopDong/index.php';
    }

    // Tạo HĐ: chọn phòng → nhập thông tin người thuê → tạo luôn
    public function create() {
        $title  = 'Tạo hợp đồng mới';
        $error  = '';
        // Chỉ lấy phòng ĐANG TRỐNG
        $phongs = $this->phongModel->getTrong();
        $khuModel = new KhuTroModel();
        $khus   = $khuModel->getAll();
        // Danh sách người thuê đã đăng ký (chưa có HĐ hiệu lực)
        $db = \Database::getInstance();
        $stmtNT = $db->query(
            "SELECT nt.* FROM nguoi_thue nt
             LEFT JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id AND hd.trang_thai = 'hieu_luc'
             WHERE hd.id IS NULL
             ORDER BY nt.ho_ten"
        );
        $nguoiThueList = $stmtNT->fetchAll();
        $accountList = $db->query(
            "SELECT id, username, ho_ten, sdt, email
             FROM account
             WHERE vai_tro = 'user'
             ORDER BY ho_ten, username"
        )->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $phong_id  = (int)($_POST['phong_id']   ?? 0);
            $ngay_bd   = $_POST['ngay_bat_dau']     ?? '';
            $ngay_kt   = $_POST['ngay_ket_thuc']    ?? '';
            $tien_coc  = (float)($_POST['tien_coc'] ?? 0);
            $ghi_chu   = trim($_POST['ghi_chu']     ?? '');
            // Thông tin người thuê (tạo luôn trong form)
            $ho_ten    = trim($_POST['ho_ten']      ?? '');
            $cccd      = trim($_POST['cccd']        ?? '');
            $sdt       = trim($_POST['sdt']         ?? '');
            $dia_chi   = trim($_POST['dia_chi']     ?? '');
            $nguoi_thue_id_existing = (int)($_POST['nguoi_thue_id_existing'] ?? 0);
            $account_id = (int)($_POST['account_id'] ?? 0);

            if (!$phong_id || !$ngay_bd || !$ngay_kt || !$ho_ten) {
                $error = 'Vui lòng điền đầy đủ: phòng, ngày và họ tên người thuê!';
            } else {
                // 1. Tạo người thuê mới
                $db = Database::getInstance();
                $nguoi_thue_id = 0;

                if ($nguoi_thue_id_existing > 0) {
                    $existingStmt = $db->prepare(
                        "SELECT nt.id
                         FROM nguoi_thue nt
                         LEFT JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id AND hd.trang_thai = 'hieu_luc'
                         WHERE nt.id = ? AND hd.id IS NULL
                         LIMIT 1"
                    );
                    $existingStmt->execute([$nguoi_thue_id_existing]);
                    $nguoi_thue_id = (int)$existingStmt->fetchColumn();
                }

                if ($nguoi_thue_id <= 0 && $account_id > 0) {
                    $byAccountStmt = $db->prepare(
                        "SELECT nt.id
                         FROM nguoi_thue nt
                         LEFT JOIN hop_dong hd ON hd.nguoi_thue_id = nt.id AND hd.trang_thai = 'hieu_luc'
                         WHERE nt.account_id = ? AND hd.id IS NULL
                         ORDER BY nt.id DESC
                         LIMIT 1"
                    );
                    $byAccountStmt->execute([$account_id]);
                    $nguoi_thue_id = (int)$byAccountStmt->fetchColumn();
                }

                if ($nguoi_thue_id <= 0) {
                    $this->ntModel->create($ho_ten, $cccd, $sdt, $dia_chi);
                    $nguoi_thue_id = $this->ntModel->getLastId();
                }

                // Auto-link account nếu tìm thấy user có cùng SĐT hoặc tên
                if ($account_id <= 0) {
                    $linkStmt = $db->prepare(
                        "SELECT id FROM account WHERE vai_tro='user' AND ((sdt <> '' AND sdt=?) OR ho_ten=?) LIMIT 1"
                    );
                    $linkStmt->execute([$sdt, $ho_ten]);
                    $account_id = (int)$linkStmt->fetchColumn();
                }

                $db->prepare(
                    "UPDATE nguoi_thue
                     SET ho_ten=?, cccd=?, sdt=?, dia_chi=?, account_id=COALESCE(NULLIF(?, 0), account_id)
                     WHERE id=?"
                )->execute([$ho_ten, $cccd, $sdt, $dia_chi, $account_id, $nguoi_thue_id]);

                // 2. Tạo hợp đồng
                $this->model->create($phong_id, $nguoi_thue_id, $ngay_bd, $ngay_kt, $tien_coc, $ghi_chu);
                $hdId = $this->model->getLastInsertId();

                // 3. Lưu chữ ký (nếu có)
                $sig_path = '';
                $sig_data = $_POST['chu_ky'] ?? '';
                if ($sig_data && str_starts_with($sig_data, 'data:image/png;base64,')) {
                    $dir = 'public/uploads/signatures/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $img  = base64_decode(str_replace('data:image/png;base64,', '', $sig_data));
                    $name = 'sig_' . $hdId . '_' . time() . '.png';
                    if (file_put_contents($dir . $name, $img)) {
                        $sig_path = $dir . $name;
                    }
                }

                // 4. Lưu vào nguoi_thue_phong (chủ HĐ)
                $this->ntPhongModel->create([
                    'hop_dong_id'     => $hdId,
                    'phong_id'        => $phong_id,
                    'ho_ten'          => $ho_ten,
                    'cccd'            => $cccd,
                    'sdt'             => $sdt,
                    'ngay_sinh'       => $_POST['ngay_sinh'] ?? '',
                    'gioi_tinh'       => $_POST['gioi_tinh'] ?? 'nam',
                    'que_quan'        => $dia_chi,
                    'avatar'          => $sig_path,
                    'la_chu_hop_dong' => 1,
                ]);

                // 5. Lưu người ở cùng + xe của họ (nếu có)
                foreach ($_POST['nguoi_o_cung'] ?? [] as $ocIdx => $nt) {
                    $ht = trim($nt['ho_ten'] ?? '');
                    if (!$ht) continue;
                    $this->ntPhongModel->create([
                        'hop_dong_id'     => $hdId,
                        'phong_id'        => $phong_id,
                        'ho_ten'          => $ht,
                        'cccd'            => trim($nt['cccd'] ?? ''),
                        'sdt'             => trim($nt['sdt']  ?? ''),
                        'ngay_sinh'       => $nt['ngay_sinh'] ?? '',
                        'gioi_tinh'       => $nt['gioi_tinh'] ?? 'nam',
                        'que_quan'        => '',
                        'avatar'          => '',
                        'la_chu_hop_dong' => 0,
                    ]);
                    // Xe của người ở cùng này
                    foreach ($nt['xe'] ?? [] as $xe) {
                        $bs = trim($xe['bien_so'] ?? '');
                        if (!$bs) continue;
                        $this->xeModel->create([
                            'hop_dong_id' => $hdId,
                            'phong_id'    => $phong_id,
                            'bien_so'     => strtoupper($bs),
                            'loai_xe'     => $xe['loai_xe'] ?? 'xe_may',
                            'mau_sac'     => trim($xe['mau_sac'] ?? ''),
                            'ghi_chu'     => 'Xe của: ' . $ht,
                        ]);
                    }
                }

                // 6. Lưu xe (nếu có)
                foreach ($_POST['xe_list'] ?? [] as $xe) {
                    $bs = trim($xe['bien_so'] ?? '');
                    if (!$bs) continue;
                    $this->xeModel->create([
                        'hop_dong_id' => $hdId,
                        'phong_id'    => $phong_id,
                        'bien_so'     => strtoupper($bs),
                        'loai_xe'     => $xe['loai_xe'] ?? 'xe_may',
                        'mau_sac'     => trim($xe['mau_sac'] ?? ''),
                        'ghi_chu'     => '',
                    ]);
                }

                // 7. Cập nhật phòng → đang thuê
                $this->phongModel->updateTrangThai($phong_id, 'dang_thue');

                header('Location: index.php?controller=hopdong&action=index&msg=created');
                exit;
            }
        }
        require 'frontend/views/HopDong/create.php';
    }

    // Thêm người ở cùng (không phải chủ HĐ)
    public function themNguoi() {
        $hd_id = (int)($_GET['id'] ?? 0);
        $hd    = $this->model->getById($hd_id);
        if (!$hd) { header('Location: index.php?controller=hopdong&action=index'); exit; }
        $soNguoi = $this->ntPhongModel->countByHopDong($hd_id);
        $title   = 'Thêm người thuê chung';
        $error   = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();
            $ho_ten    = trim($_POST['ho_ten']   ?? '');
            $cccd      = trim($_POST['cccd']     ?? '');
            $sdt       = trim($_POST['sdt']      ?? '');
            $ngay_sinh = $_POST['ngay_sinh']     ?? '';
            $gioi_tinh = $_POST['gioi_tinh']     ?? 'nam';
            $que_quan  = trim($_POST['que_quan'] ?? '');
            $avatar    = '';
            $soNguoi   = $this->ntPhongModel->countByHopDong($hd_id);
            if (!$ho_ten) {
                $error = 'Vui lòng nhập họ tên!';
            } elseif ($soNguoi >= 4) {
                $error = 'Phòng này đã đủ 4 người, không thể thêm người ở chung!';
            } else {
                if (!empty($_FILES['avatar']['tmp_name'])) {
                    $dir = 'public/uploads/avatars/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $ext  = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $name = 'nt_' . time() . '_' . rand(100,999) . '.' . $ext;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir.$name))
                        $avatar = $dir.$name;
                }
                $this->ntPhongModel->create([
                    'hop_dong_id'     => $hd_id,
                    'phong_id'        => $hd['phong_id'],
                    'ho_ten'          => $ho_ten,
                    'cccd'            => $cccd,
                    'sdt'             => $sdt,
                    'ngay_sinh'       => $ngay_sinh,
                    'gioi_tinh'       => $gioi_tinh,
                    'que_quan'        => $que_quan,
                    'avatar'          => $avatar,
                    'la_chu_hop_dong' => 0,
                ]);
                header('Location: index.php?controller=hopdong&action=index&msg=created');
                exit;
            }
        }
        require 'frontend/views/HopDong/them_nguoi.php';
    }

    public function xoaNguoi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=hopdong&action=index');
            exit;
        }
        Csrf::check();
        $id = (int)($_POST['id'] ?? 0);
        $nt = $this->ntPhongModel->getById($id);
        if ($nt && $nt['la_chu_hop_dong'] == 1) {
            header('Location: index.php?controller=hopdong&action=index');
            exit;
        }
        $this->ntPhongModel->delete($id);
        header('Location: index.php?controller=hopdong&action=index&msg=deleted');
        exit;
    }

    public function ketThuc() {
        $id = (int)($_GET['id'] ?? 0);
        $hd = $this->model->getById($id);
        if ($hd) {
            // Kết thúc HĐ → phòng về trống
            $this->model->updateTrangThai($id, 'het_han');
            $this->phongModel->updateTrangThai($hd['phong_id'], 'trong');
        }
        header('Location: index.php?controller=hopdong&action=index&msg=ended');
        exit;
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $hd = $this->model->getById($id);
        if ($hd) {
            if ($hd['trang_thai'] === 'hieu_luc') {
                // Xóa HĐ hiệu lực → phòng về trống
                $this->phongModel->updateTrangThai($hd['phong_id'], 'trong');
            }
            // Xóa luôn người thuê gắn với HĐ này (nguoi_thue_phong cascade)
            // Xóa người thuê chính nếu không còn HĐ nào khác
            $nguoi_thue_id = $hd['nguoi_thue_id'];
            $this->model->delete($id);
            // Kiểm tra còn HĐ không, nếu không → xóa người thuê
            $conHD = $this->model->countByNguoiThue($nguoi_thue_id);
            if ($conHD === 0) {
                $this->ntModel->forceDelete($nguoi_thue_id);
            }
        }
        header('Location: index.php?controller=hopdong&action=index&msg=deleted');
        exit;
    }
}
