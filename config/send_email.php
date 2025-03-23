<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function sendWelcomeEmail($toEmail, $toName): bool|string
{
    $mail = new PHPMailer(true);
    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'duycong2580@gmail.com'; // Thay đổi thành email của bạn
        $mail->Password = 'bfkr alnw rntj rkua'; // Mật khẩu ứng dụng (App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Thiết lập thông tin người gửiS
        $mail->setFrom('duycong2580@gmail.com', 'E-Club');
        $mail->addAddress($toEmail, $toName);

        // Nội dung email
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8'; // Đặt mã hóa UTF-8 để hiển thị tiếng Việt đúng
        $mail->Subject = 'Chào mừng bạn đến với E-Club - Bắt đầu hành trình của bạn ngay hôm nay!';

        $mail->Body = "
        <div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; color: #333;'>
            <div style='background-color: #f5f5f5; padding: 20px; text-align: center;'>
                <h1 style='color: #2c3e50;'>Chào mừng bạn, $toName!</h1>
                <p style='font-size: 16px;'>Cảm ơn bạn đã gia nhập <strong>E-Club</strong> – nơi kết nối, học hỏi và phát triển!</p>
            </div>
            <div style='padding: 20px;'>
                <p style='font-size: 15px;'>Chúng tôi rất vui mừng chào đón bạn đến với hành trình tuyệt vời này. Tại E-Club, bạn sẽ có cơ hội:</p>
                <ul style='font-size: 15px; line-height: 1.6;'>
                    <li>🌟 Tham gia các sự kiện độc quyền và hấp dẫn.</li>
                    <li>📚 Truy cập tài nguyên học tập chất lượng cao.</li>
                    <li>🤝 Kết nối với những thành viên tài năng trong cộng đồng.</li>
                </ul>
                <p style='font-size: 15px;'>Để bắt đầu, bạn có thể <a href='https://eclub.com/login' style='color: #2980b9; text-decoration: none;'>đăng nhập ngay</a> và khám phá những điều thú vị đang chờ đón!</p>
            </div>
            <div style='background-color: #ecf0f1; padding: 15px; text-align: center;'>
                <p style='font-size: 14px; margin: 0;'>Cần hỗ trợ? Liên hệ chúng tôi qua <a href='mailto:support@eclub.com' style='color: #2980b9;'>support@eclub.com</a>.</p>
                <p style='font-size: 14px; margin: 5px 0 0 0;'>Theo dõi chúng tôi: 
                    <a href='https://facebook.com/eclub' style='color: #2980b9; text-decoration: none;'>Facebook</a> | 
                    <a href='https://twitter.com/eclub' style='color: #2980b9; text-decoration: none;'>Twitter</a>
                </p>
            </div>
            <div style='font-size: 12px; color: #777; text-align: center; padding: 10px;'>
                <p>© 2025 E-Club. Mọi quyền được bảo lưu.</p>
            </div>
        </div>";

        // Gửi email
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return "Không thể gửi email. Lỗi: {$mail->ErrorInfo}";
    }
}