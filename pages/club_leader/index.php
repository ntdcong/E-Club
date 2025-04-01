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
    <title>Quản lý Câu lạc bộ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a73e8;
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            background: light;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .dashboard-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .club-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.06);
            margin-bottom: 2rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .club-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            background: linear-gradient(120deg, #f8f9fa, #ffffff);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 12px;
            background: #f8f9fa;
            transition: transform 0.2s ease;
        }

        .stat-item:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .action-buttons {
            padding: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }

        .btn-modern {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .footer-info {
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            color: #6c757d;
            background: #fff;
            border-top: 1px solid #eee;
            border-radius: 0 0 16px 16px;
        }

        .empty-card {
            background: white;
            border-radius: 16px;
            padding: 4rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .dropdown-menu {
            border-radius: 8px;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            margin: 0.25rem 0;
        }

        .nav-pills .nav-link {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .badge {
            padding: 0.35em 0.65em;
            border-radius: 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h2 class="mb-0 fw-bold">
                    <i class="bi bi-speedometer2 text-primary me-2"></i>
                    Bảng điều khiển Quản lý CLB
                </h2>
                <div class="d-flex gap-2">
                    <a href="index.php?page=club_leader/posts" class="btn btn-modern btn-primary">
                        <i class="bi bi-pencil-square"></i> Viết bài
                    </a>
                    <a href="index.php?page=club_leader/club_settings" class="btn btn-modern btn-primary">
                        <i class="bi bi-cash"></i> Tài khoản quỹ
                    </a>

                </div>
            </div>
            <p class="text-muted mt-2 mb-0">Quản lý chuyên nghiệp các hoạt động và thông tin câu lạc bộ</p>
        </div>

        <?php if (empty($managed_clubs)): ?>
            <div class="empty-card">
                <i class="bi bi-exclamation-circle text-muted mb-3" style="font-size: 3.5rem;"></i>
                <h3 class="fw-bold">Chưa quản lý CLB nào</h3>
                <p class="text-muted mb-4">Liên hệ quản trị viên để được phân quyền quản lý câu lạc bộ.</p>
                <a href="/" class="btn btn-modern btn-primary">
                    <i class="bi bi-arrow-left me-2"></i> Quay lại
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($managed_clubs as $club): ?>
                <?php
                $sql = "SELECT 
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_members,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_members
                    FROM club_members WHERE club_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $club['id']);
                $stmt->execute();
                $stats = $stmt->get_result()->fetch_assoc();

                $sql = "SELECT COUNT(*) as upcoming_events FROM events 
                       WHERE club_id = ? AND event_date >= CURDATE()";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $club['id']);
                $stmt->execute();
                $events_count = $stmt->get_result()->fetch_assoc()['upcoming_events'];
                ?>

                <div class="club-card">
                    <div class="club-header d-flex justify-content-between align-items-center">
                        <h3 class="h5 mb-0 fw-bold"><?php echo htmlspecialchars($club['name']); ?></h3>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <div class="fw-bold fs-4"><?php echo $stats['approved_members']; ?></div>
                                <div class="text-muted">Thành viên</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div>
                                <div class="fw-bold fs-4"><?php echo $stats['pending_members']; ?></div>
                                <div class="text-muted">Chờ duyệt</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div>
                                <div class="fw-bold fs-4"><?php echo $events_count; ?></div>
                                <div class="text-muted">Sự kiện</div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="index.php?page=club_leader/members&club_id=<?php echo $club['id']; ?>" class="btn btn-modern btn-primary">
                            <i class="bi bi-people"></i> Thành viên
                            <?php if ($stats['pending_members'] > 0): ?>
                                <span class="badge bg-danger"><?php echo $stats['pending_members']; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="index.php?page=club_leader/events&club_id=<?php echo $club['id']; ?>" class="btn btn-modern btn-success">
                            <i class="bi bi-calendar-event"></i> Sự kiện
                            <?php if ($events_count > 0): ?>
                                <span class="badge bg-success"><?php echo $events_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="index.php?page=club_leader/notifications&club_id=<?php echo $club['id']; ?>" class="btn btn-modern btn-info">
                            <i class="bi bi-bell"></i> Thông báo
                        </a>
                        <a href="index.php?page=club_leader/donations&club_id=<?php echo $club['id']; ?>" class="btn btn-modern btn-warning">
                            <i class="bi bi-heart"></i> Đóng góp
                        </a>
                        <a href="index.php?page=club_leader/reports&club_id=<?php echo $club['id']; ?>" class="btn btn-modern btn-secondary">
                            <i class="bi bi-graph-up"></i> Báo cáo
                        </a>
                    </div>

                    <div class="footer-info d-flex justify-content-between align-items-center">
                        <span>Cập nhật: <?php echo date('d/m/Y H:i'); ?></span>
                        <a href="index.php?page=club_leader/activity_log&club_id=<?php echo $club['id']; ?>" class="text-muted text-decoration-none">
                            <i class="bi bi-clock-history me-1"></i> Lịch sử
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>