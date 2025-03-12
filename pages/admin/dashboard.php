<?php
// Prepare all queries to improve performance and security
$stmt = $conn->prepare("SELECT role, COUNT(*) as total FROM users GROUP BY role");
$stmt->execute();
$users_by_role = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM clubs");
$stmt->execute();
$total_clubs = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM events");
$stmt->execute();
$total_events = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM club_members WHERE status = 'pending'");
$stmt->execute();
$pending_members_count = $stmt->get_result()->fetch_assoc()['count'];

// Get clubs with most members - using prepared statement
$stmt = $conn->prepare("SELECT c.name, COUNT(cm.id) as member_count 
       FROM clubs c 
       LEFT JOIN club_members cm ON c.id = cm.club_id AND cm.status = 'approved'
       GROUP BY c.id 
       ORDER BY member_count DESC 
       LIMIT 5");
$stmt->execute();
$top_clubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get events with highest attendance - using prepared statement
$stmt = $conn->prepare("SELECT e.title, c.name as club_name, COUNT(a.id) as attendance_count 
       FROM events e 
       INNER JOIN clubs c ON e.club_id = c.id 
       LEFT JOIN attendance a ON e.id = a.event_id AND a.status = 'present'
       GROUP BY e.id 
       ORDER BY attendance_count DESC 
       LIMIT 5");
$stmt->execute();
$top_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get pending events count - using prepared statement
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE status = 'pending'");
$stmt->execute();
$pending_events_count = $stmt->get_result()->fetch_assoc()['count'];
?>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h5 class="card-title">Quản lý CLB</h5>
                <a href="index.php?page=admin&action=create_club" class="btn btn-primary mb-2 d-block">Tạo CLB mới</a>
                <a href="index.php?page=list_clubs" class="btn btn-outline-primary mb-2 d-block">Xem tất cả CLB</a>
                <a href="index.php?page=admin/posts" class="btn btn-outline-success mb-2 d-block">Quản lý Bài Viết</a>
                <a href="index.php?page=admin&action=manage_leaders" class="btn btn-outline-danger d-block">Quản lý Trưởng CLB</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h5 class="card-title">Quản lý Sự kiện</h5>
                <a href="index.php?page=admin&action=pending_events" class="btn btn-warning mb-2 d-block">
                    Duyệt sự kiện mới
                    <?php if ($pending_events_count > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $pending_events_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="index.php?page=list_events" class="btn btn-success d-block">Xem tất cả sự kiện</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h5 class="card-title">Thống kê & Quản lý</h5>
                <a href="index.php?page=admin/stats" class="btn btn-info mb-2 d-block">Xem thống kê</a>
                <a href="index.php?page=admin&action=manage_users" class="btn btn-outline-info d-block">Quản lý người dùng</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thống kê tổng quan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <h3><?php echo number_format($total_clubs); ?></h3>
                        <p class="text-muted">Tổng số CLB</p>
                    </div>
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <h3><?php echo number_format($total_events); ?></h3>
                        <p class="text-muted">Tổng số sự kiện</p>
                    </div>
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <h3><?php echo number_format(array_sum(array_column($users_by_role, 'total'))); ?></h3>
                        <p class="text-muted">Tổng số người dùng</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h3><?php echo number_format($pending_members_count); ?></h3>
                        <p class="text-muted">Thành viên chờ duyệt</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">CLB có nhiều thành viên nhất</h5>
                <span class="badge bg-primary"><?php echo count($top_clubs); ?> CLB</span>
            </div>
            <div class="card-body">
                <?php if (empty($top_clubs)): ?>
                    <div class="alert alert-info mb-0">Chưa có dữ liệu về CLB</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($top_clubs as $club): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($club['name']); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo number_format($club['member_count']); ?> thành viên</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Sự kiện có nhiều người tham gia nhất</h5>
                <span class="badge bg-success"><?php echo count($top_events); ?> sự kiện</span>
            </div>
            <div class="card-body">
                <?php if (empty($top_events)): ?>
                    <div class="alert alert-info mb-0">Chưa có dữ liệu về sự kiện</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($top_events as $event): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <?php echo htmlspecialchars($event['title']); ?>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($event['club_name']); ?></small>
                                </div>
                                <span class="badge bg-success rounded-pill"><?php echo number_format($event['attendance_count']); ?> người tham gia</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>