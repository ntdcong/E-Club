<?php
require_once __DIR__ . '/../../config/cloudinary.php';
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
            $sql = "UPDATE users SET name = ?, email = ?, password = ?, avatar_url = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $name, $email, $hashed_password, $avatar_url, $_SESSION['user_id']);
        } else {
            $sql = "UPDATE users SET name = ?, email = ?, avatar_url = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $email, $avatar_url, $_SESSION['user_id']);
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
    /* Anime-inspired profile styles that inherit existing layout */
    .card {
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border-radius: 0.8rem;
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    
    .card-header {
        background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        color: white;
        border-bottom: none;
        padding: 1.2rem 1.5rem;
        font-weight: 600;
        position: relative;
        overflow: hidden;
    }
    
    .card-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        opacity: 0.2;
    }
    
    .card-header .card-title {
        font-weight: 700;
        margin-bottom: 0;
        font-size: 1.3rem;
        z-index: 1;
        position: relative;
    }
    
    .card-body {
        padding: 1.5rem;
        background-color: white;
    }
    
    .btn-primary {
        background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        border: none;
        border-radius: 30px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(37, 117, 252, 0.2);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(37, 117, 252, 0.3);
        background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
    }
    
    .btn-outline-primary {
        border: 2px solid #2575fc;
        color: #2575fc;
        border-radius: 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-outline-primary:hover {
        background: #2575fc;
        border-color: #2575fc;
        color: white;
    }
    
    .form-control {
        border-radius: 10px;
        border: 2px solid #eaeaea;
        padding: 0.7rem 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #2575fc;
        box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.15);
    }
    
    /* Profile header and avatar */
    .profile-header {
        background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        padding: 1.5rem;
        border-radius: 0.8rem 0.8rem 0 0;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
    }
    
    .profile-header::after {
        content: '';
        position: absolute;
        right: -20px;
        bottom: -20px;
        width: 150px;
        height: 150px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: bold;
        color: white;
        background-color: #f06292;
        border: 4px solid rgba(255, 255, 255, 0.3);
        margin-right: 1.5rem;
    }
    
    .profile-info {
        color: white;
    }
    
    .profile-info h2 {
        margin-bottom: 0.2rem;
        font-weight: 700;
    }
    
    .profile-info p {
        margin-bottom: 0;
        opacity: 0.9;
    }
    
    /* Event styles */
    .event-date {
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        border-radius: 0.5rem;
        color: white;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(37, 117, 252, 0.2);
    }
    
    .event-date .day {
        font-size: 1.8rem;
        line-height: 1;
    }
    
    .event-date .month {
        font-size: 0.85rem;
        opacity: 0.9;
    }
    
    .list-group-item {
        border: none;
        border-radius: 0.5rem !important;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
        border-left: 3px solid #6a11cb;
    }
    
    .list-group-item:hover {
        transform: translateX(5px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }
    
    /* Table styles */
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(37, 117, 252, 0.05);
    }
    
    /* Empty state */
    .text-center.py-4 {
        padding: 2.5rem 1rem;
    }
    
    .text-muted {
        color: #6c757d;
        font-weight: 500;
    }
    
    /* Avatar upload styles */
    .avatar-upload {
        position: relative;
        margin-bottom: 20px;
    }

    .avatar-preview {
        border: 4px solid rgba(37, 117, 252, 0.2);
        transition: all 0.3s ease;
    }

    .avatar-preview:hover {
        border-color: rgba(37, 117, 252, 0.4);
    }

    /* Upload progress bar */
    .progress {
        height: 10px;
        margin-top: 10px;
        display: none;
    }
    
    /* Modal styles */
    .modal-content {
        border: none;
        border-radius: 0.8rem;
        overflow: hidden;
    }
    
    .modal-header {
        background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        color: white;
        border-bottom: none;
    }
    
    .modal-title {
        font-weight: 700;
    }
    
    .modal-footer {
        border-top: none;
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .fade-in {
        animation: fadeInUp 0.5s ease forwards;
    }
    
    .fade-in-1 { animation-delay: 0.1s; }
    .fade-in-2 { animation-delay: 0.2s; }
    .fade-in-3 { animation-delay: 0.3s; }
</style>

<div class="container">
    <div class="row fade-in">
        <div class="col-12">
            <div class="card mb-4">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if (!empty($user['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($user['name']); ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <?php echo strtoupper(mb_substr($user['name'], 0, 1, 'UTF-8')); ?>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateProfileModal">
                            <i class="fas fa-edit me-2"></i>Cập Nhật Hồ Sơ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8 fade-in fade-in-1">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Câu Lạc Bộ Đã Tham Gia</h3>
                    <a href="index.php?page=clubs" class="btn btn-sm btn-outline-light">Xem tất cả</a>
                </div>
                <div class="card-body">
                    <?php if (count($memberships) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên Câu Lạc Bộ</th>
                                    <th>Ngày Tham Gia</th>
                                    <th>Trạng Thái</th>
                                    <th class="text-center">Xem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($memberships as $membership): ?>
                                <tr>
                                    <td>
                                        <a href="index.php?page=clubs&id=<?php echo $membership['id']; ?>" class="text-decoration-none fw-bold">
                                            <?php echo htmlspecialchars($membership['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($membership['joined_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo getStatusColor($membership['status']); ?>">
                                            <?php echo translateStatus($membership['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="index.php?page=clubs&id=<?php echo $membership['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <div style="font-size: 3rem; color: #e0e0e0;" class="mb-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <p class="text-muted mb-3">Bạn chưa tham gia câu lạc bộ nào.</p>
                        <a href="index.php?page=clubs" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Khám Phá Câu Lạc Bộ
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card fade-in fade-in-2">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Sự Kiện Sắp Tới</h3>
                    <a href="index.php?page=events" class="btn btn-sm btn-outline-light">Xem tất cả</a>
                </div>
                <div class="card-body">
                    <?php if (count($upcoming_events) > 0): ?>
                    <div class="list-group">
                        <?php foreach ($upcoming_events as $event): ?>
                        <a href="index.php?page=events&id=<?php echo $event['id']; ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex">
                                <div class="event-date me-3 p-2">
                                    <div class="day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                    <div class="month"><?php echo date('m/Y', strtotime($event['event_date'])); ?></div>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($event['title']); ?></h5>
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
                        <div style="font-size: 3rem; color: #e0e0e0;" class="mb-3">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <p class="text-muted mb-3">Không có sự kiện sắp tới nào.</p>
                        <a href="index.php?page=events" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Tìm Kiếm Sự Kiện
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 fade-in fade-in-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hoạt Động Gần Đây</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <i class="fas fa-sign-in-alt me-2 text-primary"></i>
                            Đăng nhập gần nhất: 
                            <?php echo date('d/m/Y H:i', time() - rand(0, 86400)); ?>
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-calendar-check me-2 text-success"></i>
                            <?php echo count($upcoming_events); ?> sự kiện sắp tới
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-users-cog me-2 text-info"></i>
                            <?php echo count($memberships); ?> câu lạc bộ đã tham gia
                        </li>
                    </ul>
                    
                    <div class="mt-4">
                        <h5 class="mb-3 fw-bold">Tài Nguyên</h5>
                        <div class="d-grid gap-2">
                            <a href="index.php?page=clubs" class="btn btn-outline-primary">
                                <i class="fas fa-users me-2"></i>Khám Phá Câu Lạc Bộ
                            </a>
                            <a href="index.php?page=events" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-alt me-2"></i>Lịch Sự Kiện
                            </a>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="fas fa-newspaper me-2"></i>Tin Tức Mới Nhất
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal cập nhật hồ sơ -->
<div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProfileModalLabel">Cập Nhật Hồ Sơ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="updateProfileForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-4 text-center">
                            <div class="avatar-upload">
                                <img src="<?php echo !empty($user['avatar_url']) ? htmlspecialchars($user['avatar_url']) : 'https://via.placeholder.com/150'; ?>" 
                                    class="rounded-circle avatar-preview" 
                                    id="avatar-preview"
                                    alt="<?php echo htmlspecialchars($user['name']); ?>" 
                                    style="width: 150px; height: 150px; object-fit: cover;">
                                <div class="mt-3">
                                    <label for="avatar" class="btn btn-outline-primary">
                                        <i class="fas fa-camera me-2"></i>Chọn ảnh đại diện
                                    </label>
                                    <input type="file" class="d-none" id="avatar" name="avatar" accept="image/*">
                                </div>
                                <div class="progress" id="upload-progress-container">
                                    <div id="upload-progress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <hr>
                    <h5 class="mb-3">Đổi mật khẩu</h5>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                            <div class="input-group">
                                <input type="form" class="form-control" id="current_password" name="current_password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Chỉ cần nhập khi bạn muốn đổi mật khẩu</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <div class="input-group">
                                <input type="form" class="form-control" id="new_password" name="new_password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                            <div class="input-group">
                                <input type="form" class="form-control" id="confirm_password" name="confirm_password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview ảnh trước khi tải lên
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');
    const progressContainer = document.getElementById('upload-progress-container');
    const progressBar = document.getElementById('upload-progress');
    
    avatarInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                avatarPreview.src = e.target.result;
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Xử lý hiển thị/ẩn mật khẩu
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Xác thực form trước khi submit
    const updateProfileForm = document.getElementById('updateProfileForm');
    updateProfileForm.addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const currentPassword = document.getElementById('current_password').value;
        
        if (newPassword || confirmPassword || currentPassword) {
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu mới và xác nhận mật khẩu không khớp!');
                return;
            }
            
            if (!currentPassword) {
                e.preventDefault();
                alert('Vui lòng nhập mật khẩu hiện tại để thay đổi mật khẩu!');
                return;
            }
        }
    });

    // Animation cho các phần tử khi trang tải xong
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
    });

    setTimeout(() => {
        fadeElements.forEach((element, index) => {
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }, 100);
});
</script>