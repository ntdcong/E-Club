<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'club_management');

// Application configuration
define('SITE_URL', 'http://localhost/club_management');
define('APP_NAME', 'E-CLUB');

// Include language helper
require_once __DIR__ . '/includes/language.php';

// Database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions
function redirect($path) {
    header("Location: " . SITE_URL . $path);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Add this function if it doesn't exist
function isClubLeader() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    
    global $conn;
    $sql = "SELECT cl.id FROM club_leaders cl WHERE cl.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function isClubLeaderOf($club_id) {
    if (!isClubLeader()) return false;
    global $conn;
    $sql = "SELECT 1 FROM club_leaders WHERE user_id = ? AND club_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $club_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(trim($input));
}

function flashMessage($message, $type = 'success') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>