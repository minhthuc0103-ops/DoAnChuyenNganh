<?php

class ChuyenPhongController
{
    private ChuyenPhongModel $model;

    public function __construct()
    {
        $this->model = new ChuyenPhongModel();
    }

    private function isAdmin(): bool
    {
        return in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro'], true);
    }

    private function redirect(string $msg = '', string $err = ''): void
    {
        $url = 'index.php?controller=chuyenphong&action=index';
        if ($msg !== '') {
            $url .= '&msg=' . urlencode($msg);
        }
        if ($err !== '') {
            $url .= '&err=' . urlencode($err);
        }
        header('Location: ' . $url);
        exit;
    }

    public function index(): void
    {
        if ($this->isAdmin()) {
            $requests = $this->model->getAll();
            $soChoDuyet = $this->model->countPending();
            $currentRental = null;
            $phongTrong = [];
        } else {
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $requests = $this->model->getByUser($userId);
            $currentRental = $this->model->getCurrentRentalByAccount($userId);
            $phongTrong = $currentRental
                ? $this->model->getPhongTrong((int)$currentRental['phong_id'])
                : [];
            $soChoDuyet = $this->model->countPendingByUser($userId);
        }

        $title = 'Chuyển phòng';
        require 'frontend/views/ChuyenPhong/index.php';
    }

    public function guiYeuCau(): void
    {
        if ($this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        try {
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $rental = $this->model->getCurrentRentalByAccount($userId);
            if (!$rental) {
                throw new RuntimeException('Bạn chưa có hợp đồng thuê phòng đang hiệu lực.');
            }

            $phongMoiId = (int)($_POST['phong_moi_id'] ?? 0);
            $lyDo = trim($_POST['ly_do'] ?? '');
            if ($phongMoiId <= 0) {
                throw new RuntimeException('Vui lòng chọn phòng muốn chuyển đến.');
            }
            if ($lyDo === '') {
                throw new RuntimeException('Vui lòng nhập lý do chuyển phòng.');
            }
            if (mb_strlen($lyDo, 'UTF-8') > 1000) {
                throw new RuntimeException('Lý do chuyển phòng tối đa 1000 ký tự.');
            }

            $this->model->createRequest([
                'user_id'       => $userId,
                'nguoi_thue_id' => (int)$rental['nguoi_thue_id'],
                'hop_dong_id'   => (int)$rental['hop_dong_id'],
                'phong_cu_id'   => (int)$rental['phong_id'],
                'phong_moi_id'  => $phongMoiId,
                'ly_do'         => $lyDo,
            ]);

            $noiDung = "Người thuê {$rental['ho_ten']} vừa gửi yêu cầu chuyển phòng.\n"
                     . "Phòng hiện tại: {$rental['so_phong']}"
                     . (!empty($rental['ten_khu']) ? " - {$rental['ten_khu']}" : '')
                     . "\nVui lòng mở mục Chuyển phòng để xem và duyệt yêu cầu.";
            $this->model->notifyAdmins('🔄 Có yêu cầu chuyển phòng mới', $noiDung, (string)$rental['ho_ten']);

            $this->redirect('transfer_sent');
        } catch (Throwable $e) {
            $this->redirect('transfer_error', $e->getMessage());
        }
    }

    public function huyYeuCau(): void
    {
        if ($this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $ok = $this->model->cancelByUser(
            (int)($_POST['id'] ?? 0),
            (int)($_SESSION['user_id'] ?? 0)
        );
        $this->redirect($ok ? 'transfer_cancelled' : 'transfer_error', $ok ? '' : 'Không thể hủy yêu cầu này.');
    }

    public function xuLy(): void
    {
        if (!$this->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        try {
            $id = (int)($_POST['id'] ?? 0);
            $quyetDinh = $_POST['quyet_dinh'] ?? '';
            $phanHoi = trim($_POST['phan_hoi_ql'] ?? '');
            $adminId = (int)($_SESSION['user_id'] ?? 0);
            $adminName = $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? 'Quản lý';

            if ($id <= 0 || !in_array($quyetDinh, ['duyet', 'tu_choi'], true)) {
                throw new RuntimeException('Thao tác xử lý không hợp lệ.');
            }

            if ($quyetDinh === 'duyet') {
                $request = $this->model->approve($id, $adminId, $phanHoi);
                $noiDung = "Yêu cầu chuyển từ phòng {$request['phong_cu']} sang phòng {$request['phong_moi']} đã được duyệt."
                         . ($phanHoi !== '' ? "\nPhản hồi quản lý: {$phanHoi}" : '');
                $this->model->notifyUser((int)$request['account_id'], '✅ Yêu cầu chuyển phòng đã được duyệt', $noiDung, (string)$adminName);
                $this->redirect('transfer_approved');
            }

            $request = $this->model->reject($id, $adminId, $phanHoi);
            $noiDung = "Yêu cầu chuyển từ phòng {$request['phong_cu']} sang phòng {$request['phong_moi']} chưa được chấp thuận."
                     . ($phanHoi !== '' ? "\nPhản hồi quản lý: {$phanHoi}" : '');
            $this->model->notifyUser((int)$request['account_id'], '❌ Yêu cầu chuyển phòng bị từ chối', $noiDung, (string)$adminName);
            $this->redirect('transfer_rejected');
        } catch (Throwable $e) {
            $this->redirect('transfer_error', $e->getMessage());
        }
    }
}
