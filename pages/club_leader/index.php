<?php
if (!isset($_SESSION['user_id'])) {
    flashMessage('Vui lòng đăng nhập để tiếp tục', 'warning');
    redirect('/index.php?page=auth/login');
}

if (!isClubLeader()) {
    flashMessage('Bạn không có quyền truy cập trang này', 'danger');
    redirect('/');
}

// Get clubs managed by the current club leader
$sql = "SELECT c.* FROM clubs c 
       INNER JOIN club_leaders cl ON c.id = cl.club_id 
       WHERE cl.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$managed_clubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý câu lạc bộ</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f6f8;
            color: #2c3e50;     
            font-family: 'Segoe UI', sans-serif;
            line-height: 1.6;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .dashboard-header {
            border-bottom: 2px solid #e8eef3;
            padding-bottom: 1.5rem;
            margin-bottom: 2.5rem;
        }
        
        .club-section {
            background: #ffffff;
            border-radius: 12px;
            border-left: 5px solid #3498db;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .club-section:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .club-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-bottom: 1px solid #eef2f7;
        }
        
        .stat-container {
            padding: 1.5rem;
            background-color: #fff;
        }
        
        .divider {
            width: 2px;
            background: linear-gradient(to bottom, #e8eef3, #d5dce5);
            height: 50px;
            border-radius: 2px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.25rem;
            transition: color 0.3s ease;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .action-button {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-width: 2px;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }
        
        .badge-notification {
            position: relative;
            top: -2px;
            border-radius: 10px;
            padding: 0.25em 0.6em;
        }
        
        .empty-state {
            padding: 5rem 2rem;
            text-align: center;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .club-actions {
            padding: 1.5rem;
            background: #fafbfc;
        }
        
        .club-footer {
            padding: 1rem 1.5rem;
            background: #f1f4f8;
            font-size: 0.85rem;
            border-top: 1px solid #eef2f7;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin-right: 1rem;
            transition: transform 0.3s ease;
        }
        
        .stat-icon:hover {
            transform: scale(1.1);
        }
        
        .bg-primary-subtle {
            background-color: rgba(52, 152, 219, 0.15);
        }
        
        .bg-warning-subtle {
            background-color: rgba(241, 196, 15, 0.15);
        }
        
        .bg-success-subtle {
            background-color: rgba(46, 204, 113, 0.15);
        }
        
        h2, h3, h4 {
            font-weight: 600;
            color: #34495e;
        }
        
        .text-muted {
            color: #95a5a6 !important;
        }
        
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            transition: background-color 0.2s ease;
            border-radius: 4px;
            margin: 0.25rem 0.5rem;
        }
        
        .dropdown-item:hover {
            background-color: #eef2f7;
        }
    </style>
</head>
<body>

<div class="container dashboard-container py-4">
    <div class="dashboard-header pb-3 mb-4">
        <h2 class="mb-1">
            <i class="bi bi-speedometer2 me-2 text-primary"></i>
            Quản lý câu lạc bộ
        </h2>
        <p class="text-muted mb-0">Xem và quản lý thông tin các câu lạc bộ của bạn</p>
    </div>

    <?php if (empty($managed_clubs)): ?>
        <div class="empty-state">
            <i class="bi bi-exclamation-circle text-muted mb-3" style="font-size: 3rem;"></i>
            <h4>Bạn chưa được phân công quản lý CLB nào</h4>
            <p class="text-muted mb-4">Vui lòng liên hệ với quản trị viên để được cấp quyền quản lý.</p>
            <a href="/" class="btn btn-primary action-button">
                <i class="bi bi-house-door me-2"></i>Quay lại trang chủ
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($managed_clubs as $club): ?>
            <?php
            // Get club statistics
            $sql = "SELECT 
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_members,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_members
                FROM club_members WHERE club_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $club['id']);
            $stmt->execute();
            $stats = $stmt->get_result()->fetch_assoc();

            // Get upcoming events count
            $sql = "SELECT COUNT(*) as upcoming_events FROM events 
                   WHERE club_id = ? AND event_date >= CURDATE()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $club['id']);
            $stmt->execute();
            $events_count = $stmt->get_result()->fetch_assoc()['upcoming_events'];
            ?>

            <div class="club-section">
                <!-- Club header -->
                <div class="club-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h5 mb-0 fw-bold"><?php echo htmlspecialchars($club['name']); ?></h3>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php?page=club_leader/club_details&id=<?php echo $club['id']; ?>">
                                <i class="bi bi-info-circle me-2"></i>Chi tiết CLB
                            </a></li>
                            <li><a class="dropdown-item" href="index.php?page=club_leader/club_settings&id=<?php echo $club['id']; ?>">
                                <i class="bi bi-gear me-2"></i>Cài đặt CLB
                            </a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Club stats -->
                <div class="stat-container">
                    <div class="d-flex justify-content-between px-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <div class="stat-number"><?php echo $stats['approved_members']; ?></div>
                                <div class="stat-label">Thành viên</div>
                            </div>
                        </div>
                        
                        <div class="divider align-self-center"></div>
                        
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning-subtle text-warning">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div>
                                <div class="stat-number"><?php echo $stats['pending_members']; ?></div>
                                <div class="stat-label">Chờ duyệt</div>
                            </div>
                        </div>
                        
                        <div class="divider align-self-center"></div>
                        
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div>
                                <div class="stat-number"><?php echo $events_count; ?></div>
                                <div class="stat-label">Sự kiện</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Club actions -->
                <div class="club-actions d-flex flex-wrap gap-2">
                    <a href="index.php?page=club_leader/members&club_id=<?php echo $club['id']; ?>" class="action-button btn btn-outline-primary">
                        <i class="bi bi-people me-1"></i>Quản lý thành viên
                        <?php if ($stats['pending_members'] > 0): ?>
                            <span class="badge bg-danger badge-notification ms-1"><?php echo $stats['pending_members']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="index.php?page=club_leader/events&club_id=<?php echo $club['id']; ?>" class="action-button btn btn-outline-success">
                        <i class="bi bi-calendar-event me-1"></i>Quản lý sự kiện
                        <?php if ($events_count > 0): ?>
                            <span class="badge bg-success badge-notification ms-1"><?php echo $events_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="index.php?page=club_leader/notifications&club_id=<?php echo $club['id']; ?>" class="action-button btn btn-outline-info">
                        <i class="bi bi-bell me-1"></i>Gửi thông báo
                    </a>
                    <a href="index.php?page=club_leader/reports&club_id=<?php echo $club['id']; ?>" class="action-button btn btn-outline-secondary">
                        <i class="bi bi-graph-up me-1"></i>Báo cáo
                    </a>
                </div>
                
                <!-- Club footer -->
                <div class="club-footer d-flex justify-content-between align-items-center">
                    <span class="text-muted">Cập nhật lần cuối: <?php echo date('d/m/Y H:i'); ?></span>
                    <a href="index.php?page=club_leader/activity_log&club_id=<?php echo $club['id']; ?>" class="text-decoration-none text-muted">
                        <i class="bi bi-clock-history me-1"></i>Lịch sử hoạt động
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<div class="row mb-4">
    <div class="col-12">
        <a href="index.php?page=club_leader/posts" class="btn btn-primary">
            <i class="bi bi-pencil-square"></i> Viết bài viết
        </a>
    </div>
</div>