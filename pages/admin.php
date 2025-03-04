<?php
if (!isAdmin()) {
    flashMessage('Access denied', 'danger');
    redirect('/');
}

$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'dashboard';

// Include the appropriate module based on the action
switch ($action) {
    case 'dashboard':
    case '':
        require_once __DIR__ . '/admin/dashboard.php';
        break;
    case 'create_club':
    case 'edit_club':
    case 'manage_members':
        require_once __DIR__ . '/admin/clubs.php';
        break;
    case 'manage_users':
        require_once __DIR__ . '/admin/users.php';
        break;
    case 'manage_leaders':
    case 'assign_leader':
        require_once __DIR__ . '/admin/leaders.php';
        break;
    case 'pending_events':
    case 'approve_event':
        require_once __DIR__ . '/admin/events.php';
        break;
    default:
        flashMessage('Đã có lỗi xảy ra', 'danger');
        redirect('/index.php?page=admin');
}
