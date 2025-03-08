<?php
if (!isLoggedIn() || !isClubLeader()) {
    flashMessage('Access denied', 'danger');
    redirect('/index.php');
}

// Get clubs managed by the current user
$sql = "SELECT c.* FROM clubs c 
       INNER JOIN club_leaders cl ON c.id = cl.club_id 
       WHERE cl.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$managed_clubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle form submission for new notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $club_id = sanitize($_POST['club_id']);
    $title = sanitize($_POST['title']);
    $message = sanitize($_POST['message']);
    
    // Verify that the user is the leader of this club
    $is_leader = false;
    foreach ($managed_clubs as $club) {
        if ($club['id'] == $club_id) {
            $is_leader = true;
            break;
        }
    }
    
    if ($is_leader) {
        // Insert notification
        $sql = "INSERT INTO notifications (club_id, sender_id, title, message) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $club_id, $_SESSION['user_id'], $title, $message);
        
        if ($stmt->execute()) {
            $notification_id = $stmt->insert_id;
            
            // Get all approved club members
            $sql = "INSERT INTO notification_recipients (notification_id, user_id)
                   SELECT ?, user_id FROM club_members 
                   WHERE club_id = ? AND status = 'approved'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $notification_id, $club_id);
            $stmt->execute();
            
            flashMessage('Thông báo đã được gửi thành công!', 'success');
        } else {
            flashMessage('Có lỗi xảy ra khi gửi thông báo.', 'danger');
        }
    } else {
        flashMessage('Bạn không có quyền gửi thông báo cho câu lạc bộ này.', 'danger');
    }
    redirect('/index.php?page=club_leader/notifications');  // Updated redirect path
}

// Get sent notifications
$sql = "SELECT n.*, c.name as club_name, 
       COUNT(CASE WHEN nr.is_read = 1 THEN 1 END) as read_count,
       COUNT(nr.id) as total_recipients
       FROM notifications n
       INNER JOIN clubs c ON n.club_id = c.id
       LEFT JOIN notification_recipients nr ON n.id = nr.notification_id
       WHERE n.sender_id = ?
       GROUP BY n.id
       ORDER BY n.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-bell me-2"></i>Quản lý thông báo</h2>
        </div>
    </div>

    <!-- New Notification Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-send me-2"></i>Gửi thông báo mới
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="club_id" class="form-label">Chọn câu lạc bộ</label>
                    <select class="form-select" name="club_id" id="club_id" required>
                        <?php foreach ($managed_clubs as $club): ?>
                            <option value="<?php echo $club['id']; ?>">
                                <?php echo htmlspecialchars($club['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label">Tiêu đề thông báo</label>
                    <input type="text" class="form-control" name="title" id="title" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Nội dung thông báo</label>
                    <textarea class="form-control" name="message" id="message" rows="4" required></textarea>
                </div>
                <button type="submit" name="send_notification" class="btn btn-primary">
                    <i class="bi bi-send me-2"></i>Gửi thông báo
                </button>
            </form>
        </div>
    </div>

    <!-- Sent Notifications -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">
                <i class="bi bi-clock-history me-2"></i>Lịch sử thông báo
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($notifications)): ?>
                <p class="text-muted text-center mb-0">Chưa có thông báo nào được gửi.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tiêu đề</th>
                                <th>Câu lạc bộ</th>
                                <th>Thời gian</th>
                                <th>Đã đọc</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $notification): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($notification['title']); ?></td>
                                    <td><?php echo htmlspecialchars($notification['club_name']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <?php 
                                            $percentage = ($notification['read_count'] / $notification['total_recipients']) * 100;
                                            ?>
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo $percentage; ?>%"
                                                 aria-valuenow="<?php echo $percentage; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $notification['read_count']; ?>/<?php echo $notification['total_recipients']; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>