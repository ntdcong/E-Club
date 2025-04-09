<?php
/**
 * Cron job để gửi thông báo tự động qua email cho sự kiện
 * 
 * Script này nên được chạy mỗi ngày (ví dụ: 8:00 sáng) để gửi thông báo:
 * - Thông báo trước 1 ngày cho các sự kiện sẽ diễn ra vào ngày mai
 * - Thông báo vào ngày diễn ra sự kiện
 * - PS C:\xampp\htdocs\club_management\cron_jobs> C:\xampp\php\php.exe send_notification_emails.php
 */

require_once __DIR__ . '/../config.php';
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

// Debug database connection
logMessage("Kiểm tra kết nối database...");
if ($conn->connect_error) {
    logMessage("Lỗi kết nối database: " . $conn->connect_error);
} else {
    logMessage("Kết nối database thành công");
}

// Lấy ngày hiện tại và ngày mai
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
logMessage("Ngày hiện tại: $today - Ngày mai: $tomorrow");

// Debug - Kiểm tra có sự kiện nào trong database không
$debug_stmt = $conn->prepare("SELECT COUNT(*) as total FROM events");
$debug_stmt->execute();
$debug_result = $debug_stmt->get_result();
$total_events = $debug_result->fetch_assoc()['total'];
logMessage("Tổng số sự kiện trong hệ thống: $total_events");

// Debug - Kiểm tra sự kiện gần nhất
$debug_stmt = $conn->prepare("SELECT title, event_date FROM events ORDER BY event_date DESC LIMIT 1");
$debug_stmt->execute();
$debug_result = $debug_stmt->get_result();
if ($debug_result->num_rows > 0) {
    $latest_event = $debug_result->fetch_assoc();
    logMessage("Sự kiện gần nhất: '{$latest_event['title']}' vào ngày {$latest_event['event_date']}");
} else {
    logMessage("Không tìm thấy sự kiện nào trong database");
}

// Xử lý thông báo trước 1 ngày
logMessage("Bắt đầu truy vấn sự kiện diễn ra vào ngày mai ($tomorrow)");
// Sửa từ name thành title trong các câu truy vấn
$stmt = $conn->prepare("SELECT e.*, c.name as club_name 
                      FROM events e 
                      INNER JOIN clubs c ON e.club_id = c.id 
                      WHERE DATE(e.event_date) = ? 
                      AND e.notification_before_event = 1");
$stmt->bind_param("s", $tomorrow);
$stmt->execute();
$result = $stmt->get_result();
$events_tomorrow = $result->num_rows;
logMessage("Tìm thấy $events_tomorrow sự kiện sẽ diễn ra vào ngày mai");

$before_event_count = 0;
while ($event = $result->fetch_assoc()) {
    logMessage("Processing event details:");
    logMessage("- Event ID: " . $event['id']);
    logMessage("- Title: " . $event['title']);
    logMessage("- Date: " . $event['event_date']);
    logMessage("- Status: " . ($event['status'] ?? 'unknown'));
    logMessage("- Club ID: " . $event['club_id']);
    logMessage("- Club Name: " . $event['club_name']);
    
    // Check club members before sending
    $check_stmt = $conn->prepare("SELECT COUNT(*) as member_count FROM club_members WHERE club_id = ? AND status = 'approved'");
    $check_stmt->bind_param("i", $event['club_id']);
    $check_stmt->execute();
    $member_count = $check_stmt->get_result()->fetch_assoc()['member_count'];
    logMessage("- Total club members: " . $member_count);
    
    // Gửi thông báo trước sự kiện
    if (sendEventReminder($event['id'], 1)) {
        $before_event_count++;
        logMessage("Successfully sent reminder for event: {$event['title']} (ID: {$event['id']})");
    } else {
        logMessage("Failed to send reminder for event: {$event['title']} (ID: {$event['id']}) - Check PHP error log for details");
    }
    
    // Log email configuration status
    logMessage("Checking email configuration:");
    $mail = configureMailer();
    if ($mail) {
        logMessage("- Email configuration successful");
        logMessage("- SMTP Host: " . $mail->Host);
        logMessage("- SMTP Port: " . $mail->Port);
        logMessage("- From Address: " . $mail->From);
    } else {
        logMessage("- Email configuration failed");
    }
}

logMessage("Đã gửi $before_event_count/$events_tomorrow thông báo trước sự kiện");

// Xử lý thông báo vào ngày diễn ra sự kiện
logMessage("Bắt đầu truy vấn sự kiện diễn ra hôm nay ($today)");
$stmt = $conn->prepare("SELECT e.*, c.name as club_name 
                      FROM events e 
                      INNER JOIN clubs c ON e.club_id = c.id 
                      WHERE DATE(e.event_date) = ? 
                      AND e.notification_on_event_day = 1");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$events_today = $result->num_rows;
logMessage("Tìm thấy $events_today sự kiện diễn ra hôm nay");

$on_event_day_count = 0;
while ($event = $result->fetch_assoc()) {
    logMessage("Xử lý sự kiện: {$event['title']} (ID: {$event['id']}) - CLB: {$event['club_name']}");
    // Gửi thông báo vào ngày sự kiện
    if (sendEventReminder($event['id'], 0)) {
        $on_event_day_count++;
        logMessage("Đã gửi thông báo vào ngày diễn ra sự kiện cho: {$event['title']} (ID: {$event['id']})");
    } else {
        logMessage("Lỗi khi gửi thông báo vào ngày diễn ra sự kiện cho: {$event['title']} (ID: {$event['id']})");
    }
}

logMessage("Đã gửi $on_event_day_count/$events_today thông báo vào ngày diễn ra sự kiện");

// Debug - Check all events around these dates
$debug_stmt = $conn->prepare("
    SELECT e.*, c.name as club_name 
    FROM events e 
    INNER JOIN clubs c ON e.club_id = c.id 
    WHERE DATE(e.event_date) BETWEEN DATE_SUB(?, INTERVAL 1 DAY) AND DATE_ADD(?, INTERVAL 1 DAY)
");
$debug_stmt->bind_param("ss", $today, $today);
$debug_stmt->execute();
$debug_result = $debug_stmt->get_result();

logMessage("=== Debug: Checking events around current dates ===");
while ($event = $debug_result->fetch_assoc()) {
    logMessage(sprintf(
        "Event: '%s' on %s - Notification flags: before=%d, on_day=%d",
        $event['title'],
        $event['event_date'],
        $event['notification_before_event'],
        $event['notification_on_event_day']
    ));
}
logMessage("=== End debug section ===");

logMessage("Kết thúc tiến trình gửi thông báo email tự động");

// Đóng kết nối
$conn->close();

// Nếu script này được chạy từ CLI, hiển thị thông tin
if (php_sapi_name() == 'cli') {
    echo "Đã gửi $before_event_count thông báo trước sự kiện\n";
    echo "Đã gửi $on_event_day_count thông báo vào ngày diễn ra sự kiện\n";
    echo "Quá trình hoàn tất!\n";
}