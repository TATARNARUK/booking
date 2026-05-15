<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // ฟังก์ชันอ่านไฟล์ .env แบบง่ายๆ (ไม่ต้องลง Plugin เพิ่ม)
        $env_file = __DIR__ . '/../.env';
        if (file_exists($env_file)) {
            $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue; // ข้ามบรรทัดคอมเมนต์
                list($name, $value) = explode('=', $line, 2);
                $_ENV[trim($name)] = trim($value);
            }
        }

        // ดึงค่าจาก $_ENV หรือใช้ค่า Default ถ้าหาไม่เจอ
        $this->host = $_ENV['DB_HOST'] ?? 'booking_db_mysql';
        $this->db_name = $_ENV['DB_NAME'] ?? 'booking_db';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
    }

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>