<?php

class ChatController
{
    private ChatModel $model;

    public function __construct()
    {
        $this->model = new ChatModel();
    }

    /**
     * Trang chat nhóm
     */
    public function index()
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $joinedAt = $this->model->getUserChatJoinedAt($userId);
        $messages = $this->model->getMessages(50, 0, $joinedAt);
        $title = 'Chat nhóm';
        $pinnedMessages = $this->model->getPinnedMessages($joinedAt);
        require 'frontend/views/Chat/index.php';
    }

    /**
     * API: Gửi tin nhắn (AJAX POST)
     */
    public function send()
    {
        header('Content-Type: application/json');

        $input   = json_decode(file_get_contents('php://input'), true);
        $noi_dung = trim($input['noi_dung'] ?? '');

        if (!$noi_dung) {
            echo json_encode(['ok' => false, 'message' => 'Tin nhắn trống']);
            exit;
        }

        if (mb_strlen($noi_dung, 'UTF-8') > 1000) {
            echo json_encode(['ok' => false, 'message' => 'Tin nhắn quá dài (tối đa 1000 ký tự)']);
            exit;
        }

        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $ho_ten  = $_SESSION['ho_ten'] ?? $_SESSION['user'] ?? 'Ẩn danh';
        $vai_tro = $_SESSION['vai_tro'] ?? 'user';
        $joinedAt = $this->model->getUserChatJoinedAt($user_id);

        $isAdmin = in_array($vai_tro, ['quan_ly', 'chu_tro'], true);
        $loai = (($input['loai'] ?? 'text') === 'system' && $isAdmin) ? 'system' : 'text';
        $replyToId = (int)($input['reply_to_id'] ?? 0);
        $replyText = null;

        if ($replyToId > 0) {
            $reply = $this->model->getById($replyToId);
            if ($reply && ($reply['loai'] ?? '') !== 'system' && ($reply['created_at'] ?? '') >= $joinedAt) {
                $replyText = mb_substr(trim($reply['noi_dung'] ?? ''), 0, 120, 'UTF-8');
            } else {
                $replyToId = 0;
            }
        }

        $id = $this->model->send($user_id, $ho_ten, $vai_tro, $noi_dung, $loai, $replyToId ?: null, $replyText);

        echo json_encode([
            'ok' => true,
            'message' => [
                'id'         => $id,
                'user_id'    => $user_id,
                'ho_ten'     => $ho_ten,
                'vai_tro'    => $vai_tro,
                'noi_dung'   => $noi_dung,
                'loai'       => $loai,
                'reply_to_id'=> $replyToId ?: null,
                'reply_text' => $replyText,
                'reaction'   => null,
                'pinned'     => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ]);
        exit;
    }

    /**
     * API: Lấy tin nhắn mới (polling)
     */
    public function poll()
    {
        header('Content-Type: application/json');
        $afterId = (int)($_GET['after'] ?? 0);
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $joinedAt = $this->model->getUserChatJoinedAt($userId);
        $messages = $this->model->getNewMessages($afterId, $joinedAt);
        echo json_encode(['ok' => true, 'messages' => $messages]);
        exit;
    }

    /**
     * API: Xóa tin nhắn
     */
    public function delete()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
        $user_id = (int)($_SESSION['user_id'] ?? 0);
        $this->model->delete($id, $user_id);
        echo json_encode(['ok' => true]);
        exit;
    }

    public function react()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
        $reaction = trim($input['reaction'] ?? '');
        $allowed = ['❤️', '😆', '😮', '😢', '😡', '👍'];

        if ($id <= 0 || !in_array($reaction, $allowed, true)) {
            echo json_encode(['ok' => false]);
            exit;
        }

        $this->model->react($id, $reaction);
        echo json_encode(['ok' => true, 'reaction' => $reaction]);
        exit;
    }

    public function pin()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['id'] ?? 0);
        $user_id = (int)($_SESSION['user_id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['ok' => false]);
            exit;
        }

        $this->model->togglePin($id, $user_id);
        $msg = $this->model->getById($id);
        echo json_encode(['ok' => true, 'pinned' => (int)($msg['pinned'] ?? 0), 'message' => $msg]);
        exit;
    }

    public function pins()
    {
        header('Content-Type: application/json');
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $joinedAt = $this->model->getUserChatJoinedAt($userId);
        echo json_encode(['ok' => true, 'messages' => $this->model->getPinnedMessages($joinedAt)]);
        exit;
    }
}
