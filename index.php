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
    'logout' => ['public' => false],
    'list_events' => ['public' => false, 'admin' => true],
    'list_clubs' => ['public' => false, 'admin' => true]
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
if ($page === 'admin' || $page === 'list_events' || $page === 'list_clubs') {
    renderAdminHeader();
    if ($page === 'admin') {
        include "pages/admin.php";
    } else {
        include "pages/admin/{$page}.php";
    }
    renderAdminFooter();
} else {
    renderHeader($page);
    
    // Include the requested page
    if (in_array($page, ['login', 'register', 'logout', 'profile'])) {
        $page_path = "pages/auth/{$page}.php";
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
