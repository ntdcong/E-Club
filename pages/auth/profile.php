<?php
if (!isLoggedIn()) {
    flashMessage('Vui lòng đăng nhập để xem hồ sơ của bạn', 'warning');
    redirect('/index.php?page=login');
}

// Lấy thông tin người dùng
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Lấy thông tin câu lạc bộ mà người dùng tham gia
$sql = "SELECT c.*, cm.status, cm.joined_at 
       FROM clubs c 
       INNER JOIN club_members cm ON c.id = cm.club_id 
       WHERE cm.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$memberships = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy sự kiện sắp tới của người dùng
$sql = "SELECT e.*, c.name as club_name 
       FROM events e 
       INNER JOIN clubs c ON e.club_id = c.id 
       INNER JOIN club_members cm ON c.id = cm.club_id 
       WHERE cm.user_id = ? AND cm.status = 'approved' 
       AND e.event_date >= CURDATE() 
       ORDER BY e.event_date 
       LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$upcoming_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Xử lý cập nhật hồ sơ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $new_password = trim($_POST['new_password']);
    
    if ($new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $hashed_password, $_SESSION['user_id']);
    } else {
        $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);
    }
    
    if ($stmt->execute()) {
        flashMessage('Cập nhật hồ sơ thành công');
        redirect('/index.php?page=profile');
    } else {
        flashMessage('Không thể cập nhật hồ sơ', 'danger');
    }
}

// Định dạng trạng thái thành tiếng Việt
function translateStatus($status) {
    switch($status) {
        case 'approved': return 'Đã duyệt';
        case 'pending': return 'Đang chờ';
        case 'rejected': return 'Bị từ chối';
        default: return ucfirst($status);
    }
}

// Chuyển đổi màu theo trạng thái
function getStatusColor($status) {
    switch($status) {
        case 'approved': return 'success';
        case 'pending': return 'warning';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}
?>

<style>
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .card-header {
        background-color: white;
        border-bottom: 2px solid rgba(0, 0, 0, 0.05);
        padding: 1rem 1.5rem;
    }
    .btn-primary {
        padding: 0.5rem 1.5rem;
    }
    .badge {
        padding: 0.5rem 0.8rem;
        font-weight: 500;
    }
    .list-group-item {
        padding: 1rem 1.25rem;
        transition: all 0.2s;
    }
    .list-group-item:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    .event-date {
        background-color: #f8f9fa;
        border-radius: 0.25rem;
        padding: 0.5rem;
        text-align: center;
        min-width: 70px;
    }
    .event-date .day {
        font-size: 1.5rem;
        font-weight: bold;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Thông Tin Cá Nhân</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="Để trống nếu không thay đổi mật khẩu">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Cập Nhật Hồ Sơ
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Câu Lạc Bộ Đã Tham Gia</h3>
                    <a href="index.php?page=clubs" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                </div>
                <div class="card-body">
                    <?php if (count($memberships) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên Câu Lạc Bộ</th>
                                    <th>Ngày Tham Gia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($memberships as $membership): ?>
                                <tr>
                                    <td>
                                        <a href="index.php?page=clubs&id=<?php echo $membership['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($membership['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($membership['joined_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted mb-3">Bạn chưa tham gia câu lạc bộ nào.</p>
                        <a href="index.php?page=clubs" class="btn btn-outline-primary">Khám phá câu lạc bộ</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Sự Kiện Sắp Tới</h3>
                    <a href="index.php?page=events" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                </div>
                <div class="card-body">
                    <?php if (count($upcoming_events) > 0): ?>
                    <div class="list-group">
                        <?php foreach ($upcoming_events as $event): ?>
                        <a href="index.php?page=events&id=<?php echo $event['id']; ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex">
                                <div class="event-date me-3">
                                    <div class="day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                    <div class="month"><?php echo date('m/Y', strtotime($event['event_date'])); ?></div>
                                </div>
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <p class="mb-1">
                                        <i class="fas fa-users me-2"></i><?php echo htmlspecialchars($event['club_name']); ?>
                                    </p>
                                    <small class="text-muted">
                                        <i class="far fa-clock me-2"></i><?php echo date('H:i', strtotime($event['event_date'])); ?>
                                    </small>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted mb-3">Không có sự kiện sắp tới nào.</p>
                        <a href="index.php?page=events" class="btn btn-outline-primary">Tìm kiếm sự kiện</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>