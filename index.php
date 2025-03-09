<?php
session_start();
require_once 'config.php';
require_once 'templates/layout.php';
require_once 'templates/admin_layout.php';

// Get the current page from URL parameter
$page = isset($_GET['page']) ? sanitize($_GET['page']) : 'home';

// Define allowed pages and their access requirements
$allowed_pages = [
    'home' => ['public' => true],
    'login' => ['public' => true],
    'register' => ['public' => true],
    'clubs' => ['public' => true],
    'profile' => ['public' => false],
    'events' => ['public' => true],
    'admin' => ['public' => false, 'admin' => true],
    'club_leader' => ['public' => false, 'club_leader' => true],
    'club_leader/notifications' => ['public' => false, 'club_leader' => true],
    'notifications' => ['public' => false], 
    'notification_detail' => ['public' => false], 
    'logout' => ['public' => false],
    'list_events' => ['public' => false, 'admin' => true],
    'list_clubs' => ['public' => false, 'admin' => true],
    'upload_image' => ['public' => false, 'admin' => true],
    'about' => ['public' => true],
    'support' => ['public' => true],
    'contact' => ['public' => true],
    'privacy' => ['public' => true],
    'terms' => ['public' => true],
    'support' => ['public' => true],
    'faq' => ['public' => true]
];

// Check if the requested page exists and user has permission
if (!isset($allowed_pages[$page])) {
    $page = 'home';
}

// Check access permissions
if (!$allowed_pages[$page]['public'] && !isLoggedIn()) {
    flashMessage('Vui lòng đăng nhập để truy cập trang này', 'error');
    redirect('/index.php?page=login');
    exit();
}

if (isset($allowed_pages[$page]['admin']) && !isAdmin()) {
    flashMessage('Bạn không có quyền truy cập', 'error');
    redirect('/index.php');
    exit();
}

if (isset($allowed_pages[$page]['club_leader']) && !isClubLeader()) {
    flashMessage('Bạn không có quyền truy cập', 'error');
    redirect('/index.php');
    exit();
}

// Buffer the output
ob_start();

// Render the appropriate layout based on the page type
if ($page === 'admin') {
    renderAdminHeader();
    include "pages/admin.php";
    renderAdminFooter();
} elseif (in_array($page, ['list_events', 'list_clubs', 'upload_image'])) {
    renderAdminHeader();
    include "pages/admin/{$page}.php";
    renderAdminFooter();
} else {
    renderHeader($page);
    
    // Include the requested page
    // Update the page path resolution
    if (in_array($page, ['login', 'register', 'logout', 'profile'])) {
        $page_path = "pages/auth/{$page}.php";
    } elseif (in_array($page, ['about', 'support', 'contact', 'faq', 'privacy', 'terms'])) {
        $page_path = "pages/about/{$page}.php";
    } elseif ($page === 'club_leader/notifications') {
        $page_path = "pages/club_leader/notifications.php";
    } elseif (in_array($page, ['notifications', 'notification_detail'])) {
        $page_path = "pages/{$page}.php";
    } else {
        $page_path = "pages/{$page}.php";
    }
    
    if (file_exists($page_path)) {
        include $page_path;
    } else {
        echo "<div class='alert alert-danger'>Không tìm thấy trang</div>";
    }
    
    renderFooter();
}

// Flush the output buffer
ob_end_flush();
