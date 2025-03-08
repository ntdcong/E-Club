<?php
if (!isLoggedIn()) {
    flashMessage('Vui lòng đăng nhập để xem thông báo', 'warning');
    redirect('/index.php?page=login');
}

// Get notification ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    flashMessage('Thông báo không hợp lệ', 'danger');
    redirect('/index.php?page=notifications');
}

$notification_id = sanitize($_GET['id']);

// Get notification details
$sql = "SELECT n.*, c.name as club_name, nr.is_read, nr.read_at, u.name as sender_name
       FROM notifications n
       INNER JOIN clubs c ON n.club_id = c.id
       INNER JOIN notification_recipients nr ON n.id = nr.notification_id
       INNER JOIN users u ON n.sender_id = u.id
       WHERE n.id = ? AND nr.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
$stmt->execute();
$notification = $stmt->get_result()->fetch_assoc();

if (!$notification) {
    flashMessage('Không tìm thấy thông báo', 'danger');
    redirect('/index.php?page=notifications');
}

// Mark as read if not already read
if (!$notification['is_read']) {
    $sql = "UPDATE notification_recipients 
           SET is_read = 1, read_at = CURRENT_TIMESTAMP 
           WHERE notification_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
    $stmt->execute();
    $notification['is_read'] = 1;
    $notification['read_at'] = date('Y-m-d H:i:s');
}

// Format dates for display
$created_date = date('d/m/Y', strtotime($notification['created_at']));
$created_time = date('H:i', strtotime($notification['created_at']));
$read_date = isset($notification['read_at']) ? date('d/m/Y', strtotime($notification['read_at'])) : '';
$read_time = isset($notification['read_at']) ? date('H:i', strtotime($notification['read_at'])) : '';

// Get related notifications from the same club
$sql = "SELECT n.id, n.title, n.created_at 
       FROM notifications n
       INNER JOIN notification_recipients nr ON n.id = nr.notification_id
       WHERE n.club_id = ? AND nr.user_id = ? AND n.id != ?
       ORDER BY n.created_at DESC LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $notification['club_id'], $_SESSION['user_id'], $notification_id);
$stmt->execute();
$related_notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="index.php?page=notifications" class="text-decoration-none">Thông báo</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi tiết thông báo</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm rounded-lg border-0 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0 fw-bold"><?php echo htmlspecialchars($notification['title']); ?></h3>
                        <span class="badge bg-primary rounded-pill px-3 py-2">
                            <?php echo ($notification['is_read']) ? 'Đã đọc' : 'Chưa đọc'; ?>
                        </span>
                    </div>
                    <div class="mt-3 mb-3">
                        <span class="text-muted">
                            <i class="bi bi-calendar-event me-1"></i> <?php echo $created_date; ?> 
                            <i class="bi bi-clock ms-2 me-1"></i> <?php echo $created_time; ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="notification-content mb-4">
                        <?php echo nl2br(htmlspecialchars($notification['message'])); ?>
                    </div>

                    <div class="notification-meta">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="sender-avatar me-3">
                                        <div class="avatar-placeholder rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <?php echo strtoupper(substr($notification['sender_name'], 0, 1)); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="mb-0">
                                            <strong>Người gửi:</strong> 
                                            <span class="text-primary"><?php echo htmlspecialchars($notification['sender_name']); ?></span>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Câu lạc bộ:</strong> 
                                            <a href="index.php?page=club&id=<?php echo $notification['club_id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($notification['club_name']); ?>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <?php if ($notification['is_read']): ?>
                                    <p class="mb-0 text-muted">
                                        <i class="bi bi-check2-all me-1"></i>
                                        <strong>Đã đọc:</strong> 
                                        <?php echo $read_date; ?> lúc <?php echo $read_time; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light d-flex justify-content-between py-3">
                    <a href="index.php?page=notifications" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </a>
                    <div>
                        <button type="button" class="btn btn-primary me-2" onclick="shareNotification(<?php echo $notification_id; ?>)">
                            <i class="bi bi-share me-1"></i> Chia sẻ
                        </button>
                        <a href="index.php?page=print_notification&id=<?php echo $notification_id; ?>" target="_blank" class="btn btn-info text-white">
                            <i class="bi bi-printer me-1"></i> In
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm rounded-lg border-0 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-3">Thông báo liên quan</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($related_notifications) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($related_notifications as $related): ?>
                                <li class="list-group-item border-start-0 border-end-0">
                                    <a href="index.php?page=notification_detail&id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                        <div class="d-flex justify-content-between">
                                            <div class="notification-title text-truncate pe-2">
                                                <?php echo htmlspecialchars($related['title']); ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($related['created_at'])); ?>
                                            </small>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-info-circle-fill mb-2" style="font-size: 24px;"></i>
                            <p>Không có thông báo liên quan</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    <a href="index.php?page=notifications" class="btn btn-sm btn-outline-primary">
                        Xem tất cả thông báo từ CLB này
                    </a>
                </div>
            </div>
            
            <div class="card shadow-sm rounded-lg border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Hành động nhanh</h5>
                    <div class="d-grid gap-2">
                        <a href="index.php?page=clubs&id=<?php echo $notification['club_id']; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-people me-2"></i>Đến trang CLB
                        </a>
                        <a href="index.php?page=events?club_id=<?php echo $notification['club_id']; ?>" class="btn btn-outline-info">
                            <i class="bi bi-calendar-event me-2"></i>Xem sự kiện của CLB
                        </a>
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#contactModal">
                            <i class="bi bi-chat-dots me-2"></i>Liên hệ người gửi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Liên hệ với <?php echo htmlspecialchars($notification['sender_name']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">
                    <input type="hidden" name="recipient_id" value="<?php echo $notification['sender_id']; ?>">
                    <input type="hidden" name="notification_id" value="<?php echo $notification_id; ?>">
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Tiêu đề</label>
                        <input type="text" class="form-control" id="subject" name="subject" value="Phản hồi: <?php echo htmlspecialchars($notification['title']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Nội dung tin nhắn</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="sendMessage()">Gửi tin nhắn</button>
            </div>
        </div>
    </div>
</div>

<style>
.notification-content {
    font-size: 1.05rem;
    line-height: 1.8;
    white-space: pre-wrap;
    color: #333;
    padding: 1rem 0;
}

.notification-meta {
    padding-top: 1.5rem;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.card {
    border-radius: 0.75rem;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}

.notification-title {
    max-width: 75%;
}

.sender-avatar {
    flex-shrink: 0;
}

.list-group-item {
    transition: background-color 0.2s;
}

.list-group-item:hover {
    background-color: rgba(0,0,0,0.03);
}

@media (max-width: 767.98px) {
    .card-footer {
        flex-direction: column;
        gap: 1rem;
    }
    
    .card-footer .btn {
        width: 100%;
    }
}
</style>

<script>
// Function to share notification
function shareNotification(notificationId) {
    // Create share URL
    const shareUrl = window.location.origin + window.location.pathname + '?page=notification_detail&id=' + notificationId;
    
    // Check if Web Share API is supported
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes(htmlspecialchars($notification['title'])); ?>',
            text: 'Xem thông báo từ CLB <?php echo addslashes(htmlspecialchars($notification['club_name'])); ?>',
            url: shareUrl
        })
        .then(() => console.log('Shared successfully'))
        .catch((error) => console.log('Error sharing:', error));
    } else {
        // Fallback for browsers that don't support the Web Share API
        const tempInput = document.createElement('input');
        document.body.appendChild(tempInput);
        tempInput.value = shareUrl;
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        
        // Show alert
        alert('Đã sao chép đường dẫn vào clipboard!');
    }
}

// Function to send message
function sendMessage() {
    const form = document.getElementById('contactForm');
    const formData = new FormData(form);
    
    // You can replace this with your actual AJAX submission
    fetch('ajax/send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tin nhắn đã được gửi thành công!');
            $('#contactModal').modal('hide');
            form.reset();
        } else {
            alert('Có lỗi xảy ra: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi gửi tin nhắn');
    });
}

// Animation for notification content
document.addEventListener('DOMContentLoaded', function() {
    const content = document.querySelector('.notification-content');
    content.style.opacity = '0';
    content.style.transform = 'translateY(20px)';
    content.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    
    setTimeout(() => {
        content.style.opacity = '1';
        content.style.transform = 'translateY(0)';
    }, 200);
});
</script>