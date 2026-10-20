<?php
declare(strict_types=1);

/*
 * Cấu hình kết nối MySQL.
 * Mặc định dùng XAMPP: host=localhost, user=root, password rỗng.
 * Hãy sửa các giá trị dưới đây nếu MySQL của bạn khác cấu hình này.
 */
$DB_HOST = 'localhost';
$DB_NAME = 'quan_ly_chi_tieu';
$DB_USER = 'root';
$DB_PASS = '';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        $options
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit(
        '<h2>Không thể kết nối MySQL</h2>' .
        '<p>Hãy kiểm tra MySQL đã chạy, đã import database.sql và thông tin trong config.php.</p>' .
        '<p><code>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</code></p>'
    );
}
