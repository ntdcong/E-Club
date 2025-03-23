<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'chat_error.log');

require_once '../config.php';

// Kiểm tra AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

try {
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Vui lòng đăng nhập');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Dữ liệu không hợp lệ');
    }

    $club_id = sanitize($data['club_id']);
    $message = trim($data['message']);

    if (empty($message)) {
        throw new Exception('Tin nhắn không được để trống');
    }

    // Kiểm tra quyền gửi tin nhắn
    $sql = "SELECT id FROM club_members WHERE club_id = ? AND user_id = ? AND status = 'approved'";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Lỗi chuẩn bị câu truy vấn: ' . $conn->error);
    }
    $stmt->bind_param("ii", $club_id, $_SESSION['user_id']);
    if (!$stmt->execute()) {
        throw new Exception('Lỗi kiểm tra quyền: ' . $stmt->error);
    }
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Bạn không có quyền gửi tin nhắn');
    }

    // Lưu tin nhắn
    $sql = "INSERT INTO club_messages (club_id, sender_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Lỗi chuẩn bị câu truy vấn: ' . $conn->error);
    }
    $stmt->bind_param("iis", $club_id, $_SESSION['user_id'], $message);
    if (!$stmt->execute()) {
        throw new Exception('Không thể gửi tin nhắn: ' . $stmt->error);
    }

    echo json_encode(['success' => true, 'message' => 'Tin nhắn đã được gửi']);

} catch (Exception $e) {
    error_log("Send message error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 