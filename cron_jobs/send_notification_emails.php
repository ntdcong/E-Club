<?php
/**
 * Cron job để gửi thông báo tự động qua email cho sự kiện
 * 
 * Script này nên được chạy mỗi ngày (ví dụ: 8:00 sáng) để gửi thông báo:
 * - Thông báo trước 1 ngày cho các sự kiện sẽ diễn ra vào ngày mai
 * - Thông báo vào ngày diễn ra sự kiện
 * 
 * Thiết lập cron job trong Linux: 
 * 0 8 * * * php /đường/dẫn/đến/send_notification_emails.php
 * 
 * Hoặc sử dụng Windows Task Scheduler để chạy script này mỗi ngày
 */

// Tải các file cần thiết
define('APP_NAME', 'Club Management');
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../config/phpmailer_config.php';

// Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Đăng nhập vào log
function logMessage($message) {
    $log_file = __DIR__ . '/email_notifications.log';
    $date = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$date] $message" . PHP_EOL, FILE_APPEND);
}

logMessage("Bắt đầu tiến trình gửi thông báo email tự động");

// Lấy ngày hiện tại và ngày mai
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Xử lý thông báo trước 1 ngày
$stmt = $conn->prepare("SELECT e.*, c.name as club_name 
                      FROM events e 
                      INNER JOIN clubs c ON e.club_id = c.id 
                      WHERE DATE(e.event_date) = ? 
                      AND e.notification_before_event = 1");
$stmt->bind_param("s", $tomorrow);
$stmt->execute();
$result = $stmt->get_result();

$before_event_count = 0;
while ($event = $result->fetch_assoc()) {
    // Gửi thông báo trước sự kiện
    if (sendEventReminder($event['id'], 1)) {
        $before_event_count++;
        logMessage("Đã gửi thông báo trước sự kiện cho: {$event['name']} (ID: {$event['id']})");
    } else {
        logMessage("Lỗi khi gửi thông báo trước sự kiện cho: {$event['name']} (ID: {$event['id']})");
    }
}

logMessage("Đã gửi $before_event_count thông báo trước sự kiện");

// Xử lý thông báo vào ngày diễn ra sự kiện
$stmt = $conn->prepare("SELECT e.*, c.name as club_name 
                      FROM events e 
                      INNER JOIN clubs c ON e.club_id = c.id 
                      WHERE DATE(e.event_date) = ? 
                      AND e.notification_on_event_day = 1");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$on_event_day_count = 0;
while ($event = $result->fetch_assoc()) {
    // Gửi thông báo vào ngày sự kiện
    if (sendEventReminder($event['id'], 0)) {
        $on_event_day_count++;
        logMessage("Đã gửi thông báo vào ngày diễn ra sự kiện cho: {$event['name']} (ID: {$event['id']})");
    } else {
        logMessage("Lỗi khi gửi thông báo vào ngày diễn ra sự kiện cho: {$event['name']} (ID: {$event['id']})");
    }
}

logMessage("Đã gửi $on_event_day_count thông báo vào ngày diễn ra sự kiện");
logMessage("Kết thúc tiến trình gửi thông báo email tự động");

// Đóng kết nối
$conn->close();

// Nếu script này được chạy từ CLI, hiển thị thông tin
if (php_sapi_name() == 'cli') {
    echo "Đã gửi $before_event_count thông báo trước sự kiện\n";
    echo "Đã gửi $on_event_day_count thông báo vào ngày diễn ra sự kiện\n";
    echo "Quá trình hoàn tất!\n";
} 