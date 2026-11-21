<?php
/**
 * Cảnh báo nguy cơ khách rời — chỉ chủ trọ / quản lý.
 */
class CanhBaoController
{
    private CanhBaoModel $model;

    public function __construct()
    {
        if (!in_array($_SESSION['vai_tro'] ?? '', ['quan_ly', 'chu_tro'], true)) {
            header('Location: index.php?controller=user&action=index');
            exit;
        }
        $this->model = new CanhBaoModel();
    }

    public function index()
    {
        $loc = $_GET['muc'] ?? 'all';
        if (!in_array($loc, ['all', 'da_bao', 'cao', 'tb', 'thap'], true)) {
            $loc = 'all';
        }
        $tatCa = $this->model->danhSach();
        $dem   = CanhBaoModel::dem($tatCa);
        $ds    = $loc === 'all' ? $tatCa : array_values(array_filter($tatCa, fn($x) => $x['muc'] === $loc));

        $title = 'Cảnh báo khách rời';
        require 'frontend/views/CanhBao/index.php';
    }
}