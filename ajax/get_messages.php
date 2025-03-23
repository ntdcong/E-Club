<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']));
}

// Debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$club_id = sanitize($_GET['club_id']);
$last_id = sanitize($_GET['last_id']);

// Lấy tin nhắn mới
$sql = "SELECT m.*, u.name as sender_name 
        FROM club_messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.club_id = ? AND m.id > ? 
        ORDER BY m.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $club_id, $last_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Format tin nhắn
$formatted_messages = array_map(function($msg) {
    return [
        'id' => $msg['id'],
        'sender_id' => $msg['sender_id'],
        'sender_name' => htmlspecialchars($msg['sender_name']),
        'message' => htmlspecialchars($msg['message']),
        'created_at' => date('H:i', strtotime($msg['created_at']))
    ];
}, $messages);

echo json_encode([
    'success' => true,
    'messages' => $formatted_messages
]); 