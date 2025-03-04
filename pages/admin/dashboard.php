<?php
// Dashboard statistics
// Get total users count by role
$sql = "SELECT role, COUNT(*) as total FROM users GROUP BY role";
$result = $conn->query($sql);
$users_by_role = $result->fetch_all(MYSQLI_ASSOC);

// Get total clubs count
$sql = "SELECT COUNT(*) as total FROM clubs";
$result = $conn->query($sql);
$total_clubs = $result->fetch_assoc()['total'];

// Get total events count
$sql = "SELECT COUNT(*) as total FROM events";
$result = $conn->query($sql);
$total_events = $result->fetch_assoc()['total'];

// Get pending members count
$sql = "SELECT COUNT(*) as count FROM club_members WHERE status = 'pending'";
$result = $conn->query($sql);
$pending_members_count = $result->fetch_assoc()['count'];

// Get clubs with most members
$sql = "SELECT c.name, COUNT(cm.id) as member_count 
       FROM clubs c 
       LEFT JOIN club_members cm ON c.id = cm.club_id AND cm.status = 'approved'
       GROUP BY c.id 
       ORDER BY member_count DESC 
       LIMIT 5";
$result = $conn->query($sql);
$top_clubs = $result->fetch_all(MYSQLI_ASSOC);

// Get events with highest attendance
$sql = "SELECT e.title, c.name as club_name, COUNT(a.id) as attendance_count 
       FROM events e 
       INNER JOIN clubs c ON e.club_id = c.id 
       LEFT JOIN attendance a ON e.id = a.event_id AND a.status = 'present'
       GROUP BY e.id 
       ORDER BY attendance_count DESC 
       LIMIT 5";
$result = $conn->query($sql);
$top_events = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h5 class="card-title">Quản lý CLB</h5>
                <a href="index.php?page=admin&action=create_club" class="btn btn-primary mb-2 d-block">Tạo CLB mới</a>
                <a href="index.php?page=list_clubs" class="btn btn-outline-primary mb-2 d-block">Xem tất cả CLB</a>
                <a href="index.php?page=admin&action=manage_leaders" class="btn btn-outline-success d-block">Quản lý Trưởng CLB</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h5 class="card-title">Quản lý Sự kiện</h5>
                <a href="index.php?page=admin&action=pending_events" class="btn btn-warning mb-2 d-block">
                    Duyệt sự kiện mới
                    <?php
                    $pending_count = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'pending'")->fetch_assoc()['count'];
                    if ($pending_count > 0): 
                    ?>
                    <span class="badge bg-danger ms-2"><?php echo $pending_count; ?></span>
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
                <a href="index.php?page=admin&action=stats" class="btn btn-info mb-2 d-block">Xem thống kê</a>
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
                        <h3><?php echo $total_clubs; ?></h3>
                        <p class="text-muted">Tổng số CLB</p>
                    </div>
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <h3><?php echo $total_events; ?></h3>
                        <p class="text-muted">Tổng số sự kiện</p>
                    </div>
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <h3><?php echo array_sum(array_column($users_by_role, 'total')); ?></h3>
                        <p class="text-muted">Tổng số người dùng</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h3><?php echo $pending_members_count; ?></h3>
                        <p class="text-muted">Thành viên chờ duyệt</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">CLB có nhiều thành viên nhất</h5>
            </div>
            <div class="card-body">
                <?php if (empty($top_clubs)): ?>
                    <p class="text-center text-muted">Chưa có dữ liệu</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($top_clubs as $club): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($club['name']); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $club['member_count']; ?> thành viên</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Sự kiện có nhiều người tham gia nhất</h5>
            </div>
            <div class="card-body">
                <?php if (empty($top_events)): ?>
                    <p class="text-center text-muted">Chưa có dữ liệu</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($top_events as $event): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <?php echo htmlspecialchars($event['title']); ?>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($event['club_name']); ?></small>
                                </div>
                                <span class="badge bg-success rounded-pill"><?php echo $event['attendance_count']; ?> người tham gia</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>