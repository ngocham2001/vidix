<?php
/**
 * Đọc file .env từ thư mục gốc dự án
 * Hỗ trợ comment (#) và bỏ qua dòng trống
 */
function loadEnv($envPath) {
    if (!file_exists($envPath)) {
        die('[ERROR] Không tìm thấy file .env. Hãy tạo file .env từ .env.example');
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Bỏ qua comment và dòng trống
        if ($line === '' || strpos($line, '#') === 0) continue;
        // Tách KEY=VALUE
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Loại bỏ dấu nháy nếu có
        $value = trim($value, '"\'');
        if (!isset($_ENV[$key])) {
            $_ENV[$key]     = $value;
            putenv("$key=$value");
        }
    }
}

function connection_To_Database() {
    // Load .env từ thư mục gốc (một cấp trên /functions/)
    $envPath = dirname(__DIR__) . '/.env';
    loadEnv($envPath);

    $mysql_hostname = $_ENV['DB_HOST'] ?? 'localhost';
    $mysql_user     = $_ENV['DB_USER'] ?? '';
    $mysql_password = $_ENV['DB_PASS'] ?? '';
    $mysql_database = $_ENV['DB_NAME'] ?? '';

    $conn = mysqli_connect($mysql_hostname, $mysql_user, $mysql_password, $mysql_database)
        or die('[ERROR] Không thể kết nối database. Kiểm tra lại thông tin trong file .env');

    mysqli_set_charset($conn, 'utf8');
    return $conn;
}