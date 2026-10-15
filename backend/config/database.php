<?php
class Database {
    private static $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    "mysql:host=localhost;dbname=ql_phong_tro;charset=utf8mb4",
                    'root', '',
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
                );
            } catch (PDOException $e) {
                // ═══ Lỗi #8: Không lộ chi tiết lỗi DB ra người dùng ═══
                error_log("DB Connection Error: " . $e->getMessage());
                die("Lỗi kết nối cơ sở dữ liệu. Vui lòng liên hệ quản trị viên.");
            }
        }
        return self::$instance;
    }

    public function connect(): PDO {
        return self::getInstance();
    }
}
