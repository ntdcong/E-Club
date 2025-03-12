<?php
if (!isLoggedIn()) {
    flashMessage('Vui lòng đăng nhập để xem thông báo', 'warning');
    redirect('/index.php?page=login');
}

// Mark notification as read if specified
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notification_id = sanitize($_GET['mark_read']);
    $sql = "UPDATE notification_recipients 
           SET is_read = 1, read_at = CURRENT_TIMESTAMP 
           WHERE notification_id = ? AND user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
    $stmt->execute();
    
    // Add redirect to stay on the same page after marking as read
    redirect("index.php?page=notifications&marked=true");
}

// Mark all as read if requested
if (isset($_GET['mark_all_read']) && $_GET['mark_all_read'] === 'true') {
    $sql = "UPDATE notification_recipients 
           SET is_read = 1, read_at = CURRENT_TIMESTAMP 
           WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    
    redirect("/index.php?page=notifications&all_marked=true");
}

// Get user's notifications with pagination
$page = isset($_GET['p']) ? (int)sanitize($_GET['p']) : 1;
$limit = 10; // Notifications per page
$offset = ($page - 1) * $limit;

// Count total notifications for pagination
$count_sql = "SELECT COUNT(*) as total FROM notification_recipients WHERE user_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $_SESSION['user_id']);
$count_stmt->execute();
$total_count = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_count / $limit);

// Get user's notifications with pagination
$sql = "SELECT n.*, c.name as club_name, nr.is_read, nr.read_at, u.name as sender_name 
       FROM notifications n
       INNER JOIN clubs c ON n.club_id = c.id
       INNER JOIN notification_recipients nr ON n.id = nr.notification_id
       INNER JOIN users u ON n.sender_id = u.id
       WHERE nr.user_id = ?
       ORDER BY n.created_at DESC
       LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $_SESSION['user_id'], $limit, $offset);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get unread count
$unread_sql = "SELECT COUNT(*) as unread FROM notification_recipients WHERE user_id = ? AND is_read = 0";
$unread_stmt = $conn->prepare($unread_sql);
$unread_stmt->bind_param("i", $_SESSION['user_id']);
$unread_stmt->execute();
$unread_count = $unread_stmt->get_result()->fetch_assoc()['unread'];

// Check if notification was just marked as read
$just_marked = isset($_GET['marked']) && $_GET['marked'] === 'true';
$all_marked = isset($_GET['all_marked']) && $_GET['all_marked'] === 'true';
?>

<div class="container py-4">
    <?php if ($just_marked): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>Thông báo đã được đánh dấu là đã đọc.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($all_marked): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>Tất cả thông báo đã được đánh dấu là đã đọc.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="bi bi-bell-fill me-2 text-primary"></i>Thông báo của bạn
                <?php if ($unread_count > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $unread_count; ?> mới</span>
                <?php endif; ?>
            </h2>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <?php if ($unread_count > 0): ?>
            <a href="index.php?page=notifications&mark_all_read=true" class="btn btn-outline-primary">
                <i class="bi bi-check2-all me-1"></i>Đánh dấu tất cả đã đọc
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body text-center py-5">
                <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                <h4 class="mt-3 text-muted">Bạn chưa có thông báo nào</h4>
                <p class="text-muted">Khi có thông báo mới, chúng sẽ xuất hiện ở đây</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0 rounded-3">
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" 
                         data-id="<?php echo $notification['id']; ?>">
                        <div class="row align-items-center g-3">
                            <div class="col-auto">
                                <div class="notification-icon <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                                    <i class="bi bi-envelope<?php echo $notification['is_read'] ? '-open' : ''; ?>-fill"></i>
                                </div>
                            </div>
                            <div class="col">
                                <a href="index.php?page=notification_detail&id=<?php echo $notification['id']; ?>" 
                                   class="d-block w-100 text-decoration-none">
                                    <h5 class="mb-1 notification-title">
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="badge bg-primary me-2">Mới</span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                    </h5>
                                </a>
                                <p class="mb-2 notification-preview"><?php echo nl2br(htmlspecialchars(substr($notification['message'], 0, 150) . (strlen($notification['message']) > 150 ? '...' : ''))); ?></p>
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div class="notification-meta">
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="bi bi-people me-1"></i><?php echo htmlspecialchars($notification['club_name']); ?>
                                        </span>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($notification['sender_name']); ?>
                                        </span>
                                        <span class="ms-2 text-muted notification-time">
                                            <i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="notification-actions">
                                        <?php if (!$notification['is_read']): ?>
                                            <a href="?page=notifications&mark_read=<?php echo $notification['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary mark-read-btn">
                                                <i class="bi bi-check2 me-1"></i>Đánh dấu đã đọc
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted read-status">
                                                <i class="bi bi-check2-all me-1"></i>Đã đọc: 
                                                <?php echo date('d/m/Y H:i', strtotime($notification['read_at'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Phân trang thông báo">
                <ul class="pagination">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=notifications&p=<?php echo $page-1; ?>" aria-label="Trang trước">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($start_page + 4, $total_pages);
                    if ($end_page - $start_page < 4 && $start_page > 1) {
                        $start_page = max(1, $end_page - 4);
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=notifications&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=notifications&p=<?php echo $page+1; ?>" aria-label="Trang sau">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
/* Improved notification styles */
.notification-item {
    transition: all 0.2s ease;
    cursor: pointer;
    border-left: 3px solid transparent;
    padding: 1rem;
}

.notification-item:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.notification-item.unread {
    background-color: rgba(var(--bs-primary-rgb), 0.08);
    border-left-color: var(--bs-primary);
}

.notification-title {
    color: var(--bs-dark);
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.notification-preview {
    color: var(--bs-secondary);
    margin-bottom: 0.75rem;
    line-height: 1.5;
}

.notification-meta {
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.notification-actions {
    margin-left: auto;
}

.notification-time {
    font-size: 0.875rem;
}

.read-status {
    font-size: 0.875rem;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    transition: all 0.2s ease;
}

.notification-icon.unread {
    background-color: rgba(var(--bs-primary-rgb), 0.15);
    color: var(--bs-primary);
}

.notification-icon i {
    font-size: 1.25rem;
}

/* Pagination improvements */
.pagination .page-link {
    border-radius: 0.25rem;
    margin: 0 0.125rem;
    color: var(--bs-primary);
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

/* Badge adjustments */
.badge {
    font-weight: 500;
    padding: 0.4em 0.65em;
}

/* Card adjustments */
.card {
    overflow: hidden;
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .notification-actions {
        margin-top: 0.5rem;
        margin-left: 0;
    }
    
    .notification-meta {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Handle notification item clicks
    document.querySelectorAll('.notification-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            // If clicked on a link or button, don't redirect
            if (e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            
            // Otherwise navigate to detail page
            window.location.href = 'index.php?page=notification_detail&id=' + this.dataset.id;
        });
    });
    
    // Fade out success alert after 3 seconds
    setTimeout(function() {
        var alertElements = document.querySelectorAll('.alert-success');
        alertElements.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 3000);
});
</script>