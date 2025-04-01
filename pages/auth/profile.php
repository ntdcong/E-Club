<?php
require_once __DIR__ . '/../../config/cloudinary.php';
if (!isLoggedIn()) {
    flashMessage('Vui lòng đăng nhập để xem hồ sơ của bạn', 'warning');
    redirect('/index.php?page=login');
}

// Lấy thông tin người dùng
$sql = 'SELECT * FROM users WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Lấy thông tin câu lạc bộ mà người dùng tham gia
$sql = 'SELECT c.*, cm.status, cm.joined_at 
       FROM clubs c 
       INNER JOIN club_members cm ON c.id = cm.club_id 
       WHERE cm.user_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
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
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$upcoming_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Xử lý cập nhật hồ sơ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $avatar_url = $user['avatar_url'];
    $error = false;

    // Kiểm tra mật khẩu hiện tại nếu người dùng muốn đổi mật khẩu
    if ($new_password) {
        if (!password_verify($current_password, $user['password'])) {
            flashMessage('Mật khẩu hiện tại không chính xác', 'danger');
            $error = true;
        } elseif ($new_password !== $confirm_password) {
            flashMessage('Mật khẩu mới và xác nhận mật khẩu không khớp', 'danger');
            $error = true;
        }
    }

    // Handle avatar upload
    if (!$error && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadToCloudinary($_FILES['avatar'], 'avatars');
        if ($upload_result['success']) {
            $avatar_url = $upload_result['url'];
        } else {
            flashMessage('Không thể tải lên ảnh đại diện: ' . $upload_result['error'], 'danger');
            $error = true;
        }
    }

    if (!$error) {
        if ($new_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = 'UPDATE users SET name = ?, email = ?, password = ?, avatar_url = ? WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssi', $name, $email, $hashed_password, $avatar_url, $_SESSION['user_id']);
        } else {
            $sql = 'UPDATE users SET name = ?, email = ?, avatar_url = ? WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssi', $name, $email, $avatar_url, $_SESSION['user_id']);
        }

        if ($stmt->execute()) {
            flashMessage('Cập nhật hồ sơ thành công');
            redirect('/index.php?page=profile');
        } else {
            flashMessage('Không thể cập nhật hồ sơ', 'danger');
        }
    }
}

// Định dạng trạng thái thành tiếng Việt
function translateStatus($status)
{
    switch ($status) {
        case 'approved':
            return 'Đã duyệt';
        case 'pending':
            return 'Đang chờ';
        case 'rejected':
            return 'Bị từ chối';
        default:
            return ucfirst($status);
    }
}

// Chuyển đổi màu theo trạng thái
function getStatusColor($status)
{
    switch ($status) {
        case 'approved':
            return 'success';
        case 'pending':
            return 'warning';
        case 'rejected':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6a11cb, #2575fc);
        --primary-color: #6a11cb;
        --secondary-color: #2575fc;
        --bg-light: #f8f9fa;
        --text-dark: #343a40;
        --text-light: #6c757d;
        --border-radius: 10px;
        --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }

    body { background-color: #f5f7fa; }

    .profile-bg {
        background: url('assets/bg.jpg') no-repeat center/cover;
        height: 1300px;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
        z-index: -1;
    }

    .profile-container {
        max-width: 1340px;
        margin: 20px auto 40px;
        position: relative;
    }

    .profile-card {
        background: white;
        border-radius: 15px;
        box-shadow: var(--card-shadow);
        padding: 0;
        overflow: hidden;
    }

    .profile-header {
        padding: 25px;
        text-align: center;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .profile-avatar {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        margin: -60px auto 20px;
        border: 5px solid white;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar-text {
        font-size: 48px;
        font-weight: bold;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .profile-name {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .profile-username {
        color: var(--text-light);
        margin-bottom: 15px;
    }

    .profile-position {
        font-size: 15px;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .profile-actions {
        margin: 20px 0 10px;
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .btn-message {
        background: var(--primary-gradient);
        border: none;
        border-radius: 30px;
        padding: 8px 25px;
        color: white;
        font-weight: 500;
        transition: var(--transition);
    }

    .btn-message:hover {
        box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
        transform: translateY(-2px);
    }

    .stats-container {
        display: flex;
        justify-content: space-around;
        padding: 15px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .stat-item {
        text-align: center;
        padding: 0 20px;
    }

    .stat-value {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        color: var(--text-light);
        font-size: 14px;
    }

    .profile-content { padding: 30px; }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--text-dark);
    }

    .club-item, .event-item {
        border-radius: var(--border-radius);
        transition: var(--transition);
        margin-bottom: 15px;
        background: white;
        border-left: 3px solid var(--primary-color);
        padding: 15px;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .club-item:hover, .event-item:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .club-info, .event-info { flex-grow: 1; }

    .club-name, .event-title {
        margin-bottom: 5px;
        font-weight: 600;
    }

    .club-status, .event-time {
        color: var(--text-light);
        font-size: 14px;
    }

    .event-date {
        min-width: 60px;
        height: 60px;
        background: var(--primary-gradient);
        color: white;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }

    .event-date .day {
        font-size: 20px;
        font-weight: 700;
    }

    .event-date .month { font-size: 14px; }

    .modal-content {
        border: none;
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    .modal-header {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 20px;
    }

    .modal-body { padding: 25px; }
    .modal-footer { padding: 20px; }

    .avatar-upload {
        text-align: center;
        margin-bottom: 30px;
    }

    .avatar-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        margin: 0 auto 20px;
        border: 5px solid rgba(106, 17, 203, 0.1);
        overflow: hidden;
    }

    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .form-control {
        border-radius: 8px;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
    }

    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
    }

    .btn-primary {
        background: var(--primary-gradient);
        border: none;
        border-radius: 30px;
        padding: 12px 25px;
        font-weight: 600;
        transition: var(--transition);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
    }

    .toggle-password {
        border-radius: 0 8px 8px 0 !important;
    }

    @media (max-width: 768px) {
        .profile-container { margin-top: 60px; }
        .profile-avatar {
            width: 100px;
            height: 100px;
            margin-top: -50px;
        }
        .profile-name { font-size: 20px; }
        .stats-container { flex-wrap: wrap; }
        .stat-item {
            width: 33.33%;
            padding: 10px;
        }
    }
</style>

<div class="profile-bg"></div>

<div class="container profile-container">
    <div class="profile-card">
        <div class="profile-header">
            </br>
            </br>
            </br>
            <div class="profile-avatar">
                <?php if (!empty($user['avatar_url'])): ?>
                    <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                <?php else: ?>
                    <div class="profile-avatar-text"><?php echo strtoupper(mb_substr($user['name'], 0, 1, 'UTF-8')); ?></div>
                <?php endif; ?>
            </div>

            <h1 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h1>
            <p class="profile-username"><?php echo htmlspecialchars($user['email']); ?></p>

            <p class="profile-position">
                <?php echo ucfirst($user['role']); ?>
                <?php if ($user['role'] == 'club_leader'): ?>
                    • Trưởng CLB
                <?php endif; ?>
            </p>

            <div class="profile-actions">
                <button type="button" class="btn btn-message" data-bs-toggle="modal" data-bs-target="#updateProfileModal">
                    Chỉnh sửa hồ sơ
                </button>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-value"><?php echo count($memberships); ?></div>
                <div class="stat-label">CLB tham gia</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo count($upcoming_events); ?></div>
                <div class="stat-label">Sự kiện</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo count($memberships) > 0 ? round((time() - strtotime($memberships[0]['joined_at'])) / 86400) : 0; ?></div>
                <div class="stat-label">Ngày hoạt động</div>
            </div>
        </div>

        <div class="profile-content">
            <div class="row">
                <!-- Clubs Section -->
                <div class="col-md-6 mb-4">
                    <h5 class="section-title">Câu lạc bộ của tôi</h5>

                    <?php if (count($memberships) > 0): ?>
                        <?php foreach ($memberships as $club): ?>
                            <div class="club-item">
                                <div class="club-info">
                                    <h6 class="club-name"><?php echo htmlspecialchars($club['name']); ?></h6>
                                    <div class="club-status">
                                        <span class="badge bg-<?php echo getStatusColor($club['status']); ?>">
                                            <?php echo translateStatus($club['status']); ?>
                                        </span>
                                        <small class="ms-2 text-muted">
                                            Tham gia: <?php echo date('d/m/Y', strtotime($club['joined_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <a href="index.php?page=clubs&id=<?php echo $club['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users text-muted mb-3" style="font-size: 48px;"></i>
                            <p class="text-muted">Bạn chưa tham gia câu lạc bộ nào</p>
                            <a href="index.php?page=clubs" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Khám phá câu lạc bộ
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Events Section -->
                <div class="col-md-6 mb-4">
                    <h5 class="section-title">Sự kiện sắp tới</h5>

                    <?php if (count($upcoming_events) > 0): ?>
                        <?php foreach ($upcoming_events as $event): ?>
                            <div class="event-item">
                                <div class="event-date">
                                    <div class="day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                    <div class="month"><?php echo date('m/y', strtotime($event['event_date'])); ?></div>
                                </div>
                                <div class="event-info">
                                    <h6 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h6>
                                    <div class="event-time">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo date('H:i', strtotime($event['event_date'])); ?>
                                        <span class="ms-2">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo htmlspecialchars($event['club_name']); ?>
                                        </span>
                                    </div>
                                </div>
                                <a href="index.php?page=events&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-alt text-muted mb-3" style="font-size: 48px;"></i>
                            <p class="text-muted">Không có sự kiện sắp tới nào</p>
                            <a href="index.php?page=events" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Tìm kiếm sự kiện
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal cập nhật hồ sơ -->
<div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProfileModalLabel">Cập nhật thông tin cá nhân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="updateProfileForm">
                <div class="modal-body">
                    <!-- Avatar Upload -->
                    <div class="avatar-upload">
                        <div class="avatar-preview">
                            <img id="avatar-preview" src="<?php echo !empty($user['avatar_url']) ? htmlspecialchars($user['avatar_url']) : 'https://via.placeholder.com/150'; ?>" alt="Avatar">
                        </div>
                        <label for="avatar" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-camera me-2"></i>Thay đổi ảnh đại diện
                        </label>
                        <input type="file" class="d-none" id="avatar" name="avatar" accept="image/*">
                    </div>

                    <!-- Thông tin cơ bản -->
                    <div class="row mb-4">
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>

                    <!-- Thay đổi mật khẩu -->
                    <div class="p-3 bg-light rounded mb-3">
                        <h6 class="mb-3"><i class="fas fa-lock me-2"></i>Thay đổi mật khẩu</h6>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Để trống nếu bạn không muốn thay đổi mật khẩu
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý hiển thị/ẩn mật khẩu
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.querySelector('i').classList.remove('fa-eye');
                    this.querySelector('i').classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    this.querySelector('i').classList.remove('fa-eye-slash');
                    this.querySelector('i').classList.add('fa-eye');
                }
            });
        });

        // Xử lý xem trước ảnh đại diện
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatar-preview');

        if (avatarInput && avatarPreview) {
            avatarInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                        avatarPreview.style.display = 'block';
                    }

                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        // Xác nhận rời khỏi câu lạc bộ
        const leaveButtons = document.querySelectorAll('.leave-club');
        if (leaveButtons) {
            leaveButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Bạn có chắc chắn muốn rời khỏi câu lạc bộ này?')) {
                        e.preventDefault();
                    }
                });
            });
        }
    });
</script>