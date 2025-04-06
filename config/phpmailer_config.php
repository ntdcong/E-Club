<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function configureMailer() {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'duycong2580@gmail.com';
        $mail->Password = 'bfkr alnw rntj rkua'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom('duycong2580@gmail.com', 'Club Management System');
        return $mail;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return null;
    }
}

// --- Định nghĩa Header và Footer cho Email ---
$email_header = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 10px auto; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
    <div style='text-align: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;'>
        <h1 style='color: #6366f1; margin: 0;'>Hệ thống Quản lý CLB</h1> 
        <!-- Bạn có thể thay bằng logo: <img src='URL_LOGO_CUA_BAN' alt='Logo CLB' style='max-width: 150px;'> -->
    </div>
    <div style='font-size: 14px; color: #333;'>
";

$email_footer = "
    </div>
    <div style='border-top: 1px solid #eee; margin-top: 20px; padding-top: 10px; font-size: 12px; color: #777; text-align: center;'>
        <p>Email này được gửi tự động từ Hệ thống Quản lý CLB.</p>
        <p>&copy; " . date("Y") . " Club Management System. All rights reserved.</p>
        <!-- Thêm link hủy đăng ký nếu cần -->
    </div>
</div>
";
// --- Kết thúc định nghĩa Header và Footer ---


/**
 * Gửi email đến một danh sách người nhận
 * 
 * @param array $recipients Mảng chứa thông tin người nhận [email => tên]
 * @param string $subject Tiêu đề email
 * @param string $body Nội dung email (HTML)
 * @param array $attachments Mảng chứa đường dẫn đến các file đính kèm
 * @return bool Kết quả gửi email (true/false)
 */
function sendMail($recipients, $subject, $body, $attachments = []) {
    // Truy cập biến toàn cục header/footer
    global $email_header, $email_footer;
    
    $mail = configureMailer();
    
    if (!$mail) {
        return false;
    }
    
    try {
        // Thêm người nhận
        foreach ($recipients as $email => $name) {
            if (is_numeric($email)) {
                // Nếu chỉ truyền vào email mà không có tên
                $mail->addAddress($name);
            } else {
                $mail->addAddress($email, $name);
            }
        }
        
        // Cấu hình email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        // Ghép header, body và footer
        $mail->Body    = $email_header . $body . $email_footer;
        
        // Thêm các file đính kèm
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
        }
        
        // Gửi email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Lỗi gửi email: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Gửi email thông báo sự kiện sắp diễn ra
 * 
 * @param int $event_id ID của sự kiện
 * @param int $days_before Số ngày trước khi sự kiện diễn ra
 * @return bool Kết quả gửi email
 */
function sendEventReminder($event_id, $days_before = 1) {
    global $conn;
    
    // Lấy thông tin về sự kiện
    $stmt = $conn->prepare("SELECT e.*, c.name as club_name 
                           FROM events e 
                           INNER JOIN clubs c ON e.club_id = c.id 
                           WHERE e.id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    $stmt->close(); // Đóng statement
    
    if (!$event) {
        return false;
    }
    
    // Lấy danh sách thành viên tham gia sự kiện
    $stmt = $conn->prepare("SELECT u.email, u.name 
                           FROM users u 
                           INNER JOIN event_attendees ea ON u.id = ea.user_id 
                           WHERE ea.event_id = ? AND ea.status = 'attending'");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recipients = [];
    while ($row = $result->fetch_assoc()) {
        $recipients[$row['email']] = $row['name'];
    }
    $stmt->close(); // Đóng statement
    
    if (empty($recipients)) {
        // Không có ai đăng ký tham gia, không cần gửi mail
        return true; // Coi như thành công vì không có lỗi
    }
    
    // Chuẩn bị nội dung email
    $subject = "Nhắc nhở: {$event['title']} sẽ diễn ra";
    if ($days_before > 0) {
        $subject .= " sau {$days_before} ngày";
    } else {
        $subject .= " hôm nay";
    }
    
    $event_date = date('d/m/Y H:i', strtotime($event['event_date']));
    $location = htmlspecialchars($event['location'] ?? 'Chưa cập nhật');
    $description = nl2br(htmlspecialchars($event['description'] ?? 'Không có mô tả')); // Chuyển xuống dòng và chống XSS
    $club_name = htmlspecialchars($event['club_name']);
    $event_title = htmlspecialchars($event['title']);
    
    // Chỉ tạo phần nội dung chính
    $body = "
        <h3 style='color: #6366f1;'>Nhắc nhở sự kiện sắp diễn ra</h3>
        <p>Xin chào,</p>";
        
    if ($days_before > 0) {
        $body .= "<p>Chúng tôi gửi email này để nhắc bạn rằng sự kiện <strong>{$event_title}</strong> của CLB <strong>{$club_name}</strong> sẽ diễn ra sau {$days_before} ngày.</p>";
    } else {
        $body .= "<p>Chúng tôi gửi email này để nhắc bạn rằng sự kiện <strong>{$event_title}</strong> của CLB <strong>{$club_name}</strong> sẽ diễn ra hôm nay.</p>";
    }
        
    $body .= "
        <div style='background-color: #ffffff; padding: 15px; border: 1px solid #eee; border-radius: 5px; margin: 15px 0;'>
            <h4 style='margin-top: 0; color: #333;'>Chi tiết sự kiện:</h4>
            <p><strong>Thời gian:</strong> {$event_date}</p>
            <p><strong>Địa điểm:</strong> {$location}</p>
            <p><strong>Mô tả:</strong> {$description}</p>
        </div>
        
        <p>Hãy chuẩn bị và đến đúng giờ nhé!</p>
        <p>Trân trọng,<br>Ban quản lý CLB {$club_name}</p>
    ";
    
    // Giao cho sendMail xử lý việc thêm header/footer
    return sendMail($recipients, $subject, $body);
}

/**
 * Gửi email thông báo chung đến các thành viên được chọn hoặc tất cả thành viên CLB.
 * 
 * @param int $club_id ID của CLB.
 * @param string $subject Tiêu đề email (chưa bao gồm tên CLB).
 * @param string $message Nội dung thông báo (HTML).
 * @param array $recipient_ids Mảng ID của những người nhận cụ thể (nếu $send_to_all là false).
 * @param bool $send_to_all True nếu gửi cho tất cả thành viên đã duyệt, False nếu gửi cho $recipient_ids.
 * @return array Mảng kết quả ['success' => bool, 'message' => string, 'recipient_count' => int].
 */
function sendClubAnnouncement($club_id, $subject, $message, $recipient_ids = [], $send_to_all = false) {
    global $conn;
    
    // Lấy thông tin CLB
    $stmt_club = $conn->prepare("SELECT name FROM clubs WHERE id = ?");
    $stmt_club->bind_param("i", $club_id);
    $stmt_club->execute();
    $club = $stmt_club->get_result()->fetch_assoc();
    $stmt_club->close();
    
    if (!$club) {
        return ['success' => false, 'message' => 'Không tìm thấy CLB.', 'recipient_count' => 0];
    }
    $club_name = htmlspecialchars($club['name']);

    $recipients = [];
    // Xác định danh sách người nhận
    if ($send_to_all) {
        $stmt_recipients = $conn->prepare("SELECT u.email, u.name FROM users u INNER JOIN club_members cm ON u.id = cm.user_id WHERE cm.club_id = ? AND cm.status = 'approved'");
        $stmt_recipients->bind_param("i", $club_id);
    } elseif (!empty($recipient_ids)) {
        $placeholders = implode(',', array_fill(0, count($recipient_ids), '?'));
        $types = str_repeat('i', count($recipient_ids));
        $stmt_recipients = $conn->prepare("SELECT email, name FROM users WHERE id IN ($placeholders)");
        // Bind các ID vào câu lệnh
        $params = array_merge([$types], $recipient_ids);
        // Cần dùng call_user_func_array hoặc Reflection để bind động
        // Cách đơn giản hơn cho mysqli:
        $stmt_recipients->bind_param($types, ...$recipient_ids);
    } else {
        // Không chọn gửi tất cả và cũng không có ID người nhận cụ thể
        return ['success' => false, 'message' => 'Không có người nhận nào được chọn.', 'recipient_count' => 0];
    }

    // Thực thi truy vấn lấy người nhận
    $stmt_recipients->execute();
    $result_recipients = $stmt_recipients->get_result();
    while ($row = $result_recipients->fetch_assoc()) {
        $recipients[$row['email']] = htmlspecialchars($row['name']); // Chống XSS cho tên người nhận
    }
    $stmt_recipients->close();

    if (empty($recipients)) {
        return ['success' => false, 'message' => 'Không tìm thấy người nhận hợp lệ.', 'recipient_count' => 0];
    }

    // Chuẩn bị tiêu đề và nội dung
    $full_subject = "Thông báo từ CLB {$club_name}: " . htmlspecialchars($subject);
    // Nội dung message đã được xử lý (ví dụ: qua editor hoặc escape), chỉ cần đảm bảo nó là HTML
    $body = "
        <h3 style='color: #6366f1;'>Thông báo từ CLB {$club_name}</h3>
        <p>Xin chào,</p>
        <p>Ban quản lý CLB {$club_name} gửi đến bạn thông báo sau:</p>
        <div style='background-color: #ffffff; padding: 15px; border: 1px solid #eee; border-radius: 5px; margin: 15px 0;'>
            {$message} 
        </div>
        <p>Trân trọng,<br>Ban quản lý CLB {$club_name}</p>
    ";

    // Gửi email qua hàm sendMail
    if (sendMail($recipients, $full_subject, $body)) {
        $count = count($recipients);
        return ['success' => true, 'message' => "Đã gửi thông báo thành công đến {$count} thành viên.", 'recipient_count' => $count];
    } else {
        return ['success' => false, 'message' => 'Có lỗi xảy ra trong quá trình gửi email.', 'recipient_count' => 0];
    }
}

/**
 * Gửi email thông báo chung của hệ thống đến người dùng được chọn hoặc tất cả người dùng.
 * 
 * @param string $subject Tiêu đề email.
 * @param string $message Nội dung thông báo (HTML).
 * @param array $recipient_ids Mảng ID của những người nhận cụ thể (nếu $send_to_all là false).
 * @param bool $send_to_all True nếu gửi cho tất cả người dùng, False nếu gửi cho $recipient_ids.
 * @return array Mảng kết quả ['success' => bool, 'message' => string, 'recipient_count' => int].
 */
function sendSystemAnnouncement($subject, $message, $recipient_ids = [], $send_to_all = false) {
    global $conn;
    
    $recipients = [];
    // Xác định danh sách người nhận
    if ($send_to_all) {
        // Lấy tất cả người dùng (có thể thêm điều kiện lọc nếu cần, ví dụ: chỉ user active)
        $stmt_recipients = $conn->prepare("SELECT email, name FROM users");
    } elseif (!empty($recipient_ids)) {
        $placeholders = implode(',', array_fill(0, count($recipient_ids), '?'));
        $types = str_repeat('i', count($recipient_ids));
        $stmt_recipients = $conn->prepare("SELECT email, name FROM users WHERE id IN ($placeholders)");
        $stmt_recipients->bind_param($types, ...$recipient_ids);
    } else {
        // Không chọn gửi tất cả và cũng không có ID người nhận cụ thể
        return ['success' => false, 'message' => 'Không có người nhận nào được chọn.', 'recipient_count' => 0];
    }

    // Thực thi truy vấn lấy người nhận
    if ($send_to_all) {
        // Không cần bind param nếu lấy tất cả
        $stmt_recipients->execute(); 
    } else if (!empty($recipient_ids)) {
        $stmt_recipients->execute();
    }
    // Nếu không phải cả 2 trường hợp trên thì đã return ở else trước đó

    $result_recipients = $stmt_recipients->get_result();
    while ($row = $result_recipients->fetch_assoc()) {
        $recipients[$row['email']] = htmlspecialchars($row['name']); // Chống XSS cho tên người nhận
    }
    $stmt_recipients->close();

    if (empty($recipients)) {
        return ['success' => false, 'message' => 'Không tìm thấy người nhận hợp lệ.', 'recipient_count' => 0];
    }

    // Chuẩn bị tiêu đề và nội dung
    $full_subject = "Thông báo Hệ thống: " . htmlspecialchars($subject);
    $body = "
        <h3 style='color: #17a2b8;'>Thông báo từ Hệ thống Quản lý CLB</h3>
        <p>Xin chào,</p>
        <p>Chúng tôi gửi đến bạn thông báo quan trọng sau:</p>
        <div style='background-color: #ffffff; padding: 15px; border: 1px solid #eee; border-radius: 5px; margin: 15px 0;'>
            {$message} 
        </div>
        <p>Trân trọng,<br>Ban quản trị Hệ thống</p>
    ";

    // Gửi email qua hàm sendMail 
    if (sendMail($recipients, $full_subject, $body)) {
        $count = count($recipients);
        return ['success' => true, 'message' => "Đã gửi thông báo hệ thống thành công đến {$count} người dùng.", 'recipient_count' => $count];
    } else {
        return ['success' => false, 'message' => 'Có lỗi xảy ra trong quá trình gửi email hệ thống.', 'recipient_count' => 0];
    }
}

/**
 * Lên lịch gửi thông báo tự động cho sự kiện
 * 
 * @param int $event_id ID của sự kiện
 * @param bool $before_event Gửi thông báo trước sự kiện
 * @param bool $on_event_day Gửi thông báo vào ngày diễn ra sự kiện
 * @return bool Kết quả cập nhật lịch
 */
function scheduleEventNotifications($event_id, $before_event = true, $on_event_day = true) {
    global $conn;
    
    // Kiểm tra xem sự kiện có tồn tại không
    $stmt = $conn->prepare("SELECT id FROM events WHERE id = ?"); // Chỉ cần lấy id để kiểm tra
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->store_result(); // Lưu kết quả để kiểm tra số hàng
    $event_exists = $stmt->num_rows > 0;
    $stmt->close(); // Đóng statement
    
    if (!$event_exists) {
        return false;
    }
    
    // Ép kiểu bool sang int (0 hoặc 1) để bind_param
    $before_event_int = (int)$before_event;
    $on_event_day_int = (int)$on_event_day;

    // Cập nhật cài đặt thông báo cho sự kiện
    $stmt = $conn->prepare("UPDATE events SET 
                           notification_before_event = ?, 
                           notification_on_event_day = ? 
                           WHERE id = ?");
    $stmt->bind_param("iii", $before_event_int, $on_event_day_int, $event_id);
    $result = $stmt->execute();
    $stmt->close(); // Đóng statement
    
    return $result;
}

/**
 * Gửi email thử nghiệm thông báo sự kiện
 * 
 * @param int $event_id ID của sự kiện
 * @param int $days_before Số ngày trước khi sự kiện diễn ra
 * @param string $test_email Email để gửi thử nghiệm (nếu null sẽ gửi đến người trưởng CLB)
 * @return bool Kết quả gửi email
 */
function sendTestEventReminder($event_id, $days_before = 1, $test_email = null) {
    global $conn;
    
    // Lấy thông tin về sự kiện và trưởng CLB
    $stmt = $conn->prepare("SELECT e.*, c.name as club_name, u.email as leader_email, u.name as leader_name
                           FROM events e 
                           INNER JOIN clubs c ON e.club_id = c.id 
                           INNER JOIN club_leaders cl ON c.id = cl.club_id
                           INNER JOIN users u ON cl.user_id = u.id
                           WHERE e.id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    $stmt->close(); // Đóng statement
    
    if (!$event) {
        error_log("Không tìm thấy sự kiện ID: {$event_id} để gửi email thử nghiệm.");
        return false;
    }
    
    // Chuẩn bị danh sách người nhận
    $recipients = [];
    if (!empty($test_email) && filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        // Nếu có email thử nghiệm hợp lệ, gửi tới email đó
        $recipients[$test_email] = "Người nhận thử nghiệm";
    } elseif (!empty($event['leader_email']) && filter_var($event['leader_email'], FILTER_VALIDATE_EMAIL)) {
        // Nếu không, gửi tới trưởng CLB nếu email hợp lệ
        $recipients[$event['leader_email']] = htmlspecialchars($event['leader_name'] ?? 'Trưởng CLB');
    } else {
        error_log("Không có email hợp lệ để gửi thử nghiệm cho sự kiện ID: {$event_id}");
        return false; // Không có email nào để gửi
    }
    
    // Chuẩn bị nội dung email
    $event_title = htmlspecialchars($event['title']);
    $club_name = htmlspecialchars($event['club_name']);
    $subject = "[THỬ NGHIỆM] Nhắc nhở: {$event_title} sẽ diễn ra";
    if ($days_before > 0) {
        $subject .= " sau {$days_before} ngày";
    } else {
        $subject .= " hôm nay";
    }
    
    $event_date = date('d/m/Y H:i', strtotime($event['event_date']));
    $location = htmlspecialchars($event['location'] ?? 'Chưa cập nhật');
    $description = nl2br(htmlspecialchars($event['description'] ?? 'Không có mô tả'));
    
    // Chỉ tạo phần nội dung chính
    $body = "
        <h3 style='color: #dc3545;'>ĐÂY LÀ EMAIL THỬ NGHIỆM</h3>
        <h3 style='color: #6366f1;'>Nhắc nhở sự kiện sắp diễn ra</h3>
        <p>Xin chào,</p>";
        
    if ($days_before > 0) {
        $body .= "<p>Chúng tôi gửi email này để nhắc bạn rằng sự kiện <strong>{$event_title}</strong> của CLB <strong>{$club_name}</strong> sẽ diễn ra sau {$days_before} ngày.</p>";
    } else {
        $body .= "<p>Chúng tôi gửi email này để nhắc bạn rằng sự kiện <strong>{$event_title}</strong> của CLB <strong>{$club_name}</strong> sẽ diễn ra hôm nay.</p>";
    }
        
    $body .= "
        <div style='background-color: #ffffff; padding: 15px; border: 1px solid #eee; border-radius: 5px; margin: 15px 0;'>
             <h4 style='margin-top: 0; color: #333;'>Chi tiết sự kiện:</h4>
            <p><strong>Thời gian:</strong> {$event_date}</p>
            <p><strong>Địa điểm:</strong> {$location}</p>
            <p><strong>Mô tả:</strong> {$description}</p>
        </div>
        
        <p>Đây chỉ là email thử nghiệm, không phải thông báo chính thức.</p>
        <p>Trân trọng,<br>Ban quản lý CLB {$club_name}</p>
    ";
    
    // Giao cho sendMail xử lý việc thêm header/footer
    return sendMail($recipients, $subject, $body);
}
