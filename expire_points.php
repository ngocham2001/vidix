<?php
// ============================================================
// cron/expire_points.php
// Chạy hàng ngày lúc 8:05 sáng (sau process_commissions)
// Lệnh crontab: 5 8 * * * php /var/www/html/mlm/cron/expire_points.php
// ============================================================
require_once __DIR__ . '/../define.php';
require_once __DIR__ . '/../' . PATH_MAIN_FUNCTION . '/conn-login-logout.php';

$conn = connection_to_database();

mysqli_query($conn,
    "UPDATE point_transaction
     SET    is_expired = 1
     WHERE  is_expired  = 0
       AND  expiry_date < CURDATE()"
);
$expired = mysqli_affected_rows($conn);

$log = "[" . date('Y-m-d H:i:s') . "] Hết hạn $expired bản ghi điểm (30 tháng).\n";
echo $log;
file_put_contents(__DIR__ . '/cron_points.log', $log, FILE_APPEND);
