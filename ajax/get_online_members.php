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

// Cập nhật thời gian hoạt động của user hiện tại
$sql = "UPDATE users SET last_activity = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

// Lấy danh sách thành viên online (active trong 5 phút gần đây)
$sql = "SELECT DISTINCT u.id, u.name 
        FROM users u 
        JOIN club_members cm ON u.id = cm.user_id 
        WHERE cm.club_id = ? 
        AND cm.status = 'approved' 
        AND u.last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ORDER BY u.name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $club_id);
$stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'count' => count($members),
    'members' => array_map(function($member) {
        return [
            'id' => $member['id'],
            'name' => htmlspecialchars($member['name'])
        ];
    }, $members)
]); 