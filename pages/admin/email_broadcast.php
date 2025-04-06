<?php
// Kiểm tra quyền truy cập Admin
if (!isAdmin()) {
    redirectWithMessage('danger', 'Bạn không có quyền truy cập vào trang này!', 'index.php');
    exit;
}

// Include file cấu hình PHPMailer và các hàm gửi email
require_once __DIR__ . '/../../config/phpmailer_config.php';

// Lấy danh sách tất cả người dùng
$stmt_users = $conn->prepare("SELECT id, name, email FROM users ORDER BY name ASC");
$stmt_users->execute();
$users = $stmt_users->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_users->close();

// Xử lý gửi thông báo hệ thống
if (isset($_POST['send_system_announcement'])) {
    // Xác thực và lọc dữ liệu đầu vào
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']); // Cần đảm bảo an toàn HTML nếu dùng editor
    $recipient_ids = isset($_POST['recipient_ids']) ? $_POST['recipient_ids'] : [];
    $send_to_all = isset($_POST['send_to_all']) && $_POST['send_to_all'] == '1';

    if (empty($subject) || empty($message)) {
        flashMessage('Vui lòng điền đầy đủ tiêu đề và nội dung thông báo.', 'warning');
    } elseif (!$send_to_all && empty($recipient_ids)) {
        flashMessage('Vui lòng chọn ít nhất một người nhận hoặc chọn gửi cho tất cả người dùng.', 'warning');
    } else {
        // Gọi hàm sendSystemAnnouncement từ config
        $result = sendSystemAnnouncement($subject, $message, $recipient_ids, $send_to_all);

        // Sử dụng kết quả trả về để đặt flash message
        if ($result['success']) {
            flashMessage($result['message'], 'success');
        } else {
            $message_type = ($result['message'] === 'Không có người nhận nào được chọn.' || $result['message'] === 'Không tìm thấy người nhận hợp lệ.') ? 'warning' : 'danger';
            flashMessage($result['message'], $message_type);
        }
    }

    // Redirect để tránh gửi lại form khi refresh
    header("Location: index.php?page=admin/email_broadcast");
    exit;
}

?>

<style>
    .user-list {
        max-height: 400px; /* Giới hạn chiều cao */
        overflow-y: auto; /* Thêm thanh cuộn dọc */
        border: 1px solid #dee2e6; /* Thêm viền nhẹ */
        padding: 10px;
        border-radius: 0.25rem; /* Bo góc nhẹ */
        background-color: #f8f9fa; /* Nền xám nhạt */
    }
    .user-item {
        padding: 5px 0;
        border-bottom: 1px solid #e9ecef; /* Đường kẻ phân cách */
    }
    .user-item:last-child {
        border-bottom: none; /* Bỏ đường kẻ cuối */
    }
    .card-header {
        border-bottom: none;
    }
    .form-control, .btn {
        border-radius: 0.25rem;
    }
    .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: none;
    }
    .card-title i {
        margin-right: 8px;
        font-size: 1.1em;
    }
    #select-all-container {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
</style>

<h2 class="mb-4"><i class="bi bi-megaphone-fill text-info"></i> Gửi Thông Báo Email Hệ Thống</h2>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-info">
            <i class="bi bi-envelope-plus"></i> Soạn và Gửi Thông Báo
        </h5>
    </div>
    <div class="card-body">
        <form method="post" action="" id="broadcast-form">
            <div class="row">
                <!-- Cột soạn thảo -->
                <div class="col-md-7">
                     <h6 class="mb-3">Soạn thảo thông báo</h6>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Tiêu đề<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Nội dung<span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="10" required></textarea>
                        <div class="form-text">Bạn có thể sử dụng HTML cơ bản để định dạng nội dung. Hãy cẩn thận với mã HTML bạn nhập vào.</div>
                        <!-- Cân nhắc tích hợp một trình soạn thảo WYSIWYG như TinyMCE hoặc CKEditor ở đây -->
                    </div>
                </div>

                <!-- Cột chọn người nhận -->
                <div class="col-md-5">
                    <h6 class="mb-3">Chọn người nhận <span class="text-danger">*</span></h6>
                    <?php if (empty($users)): ?>
                        <div class="alert alert-warning">Không có người dùng nào trong hệ thống.</div>
                    <?php else: ?>
                        <div id="select-all-container" class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="select-all-users" name="send_to_all" value="1">
                            <label class="form-check-label fw-bold" for="select-all-users">
                                Gửi cho tất cả người dùng (<?php echo count($users); ?>)
                            </label>
                        </div>
                        <div class="user-list">
                            <?php foreach ($users as $user): ?>
                                <div class="form-check user-item">
                                    <input class="form-check-input user-checkbox" type="checkbox" name="recipient_ids[]" value="<?php echo $user['id']; ?>" id="user_<?php echo $user['id']; ?>">
                                    <label class="form-check-label" for="user_<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['name']); ?> <small class="text-muted">(<?php echo htmlspecialchars($user['email']); ?>)</small>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
             <div class="d-flex justify-content-end">
                 <button type="submit" name="send_system_announcement" class="btn btn-info" <?php echo empty($users) ? 'disabled' : ''; ?>>
                    <i class="bi bi-send-fill"></i> Gửi Thông Báo Hệ Thống
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all-users');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    checkbox.disabled = isChecked;
                });
            });
        }

        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    if (selectAllCheckbox) selectAllCheckbox.checked = false;
                }
                 else if (!selectAllCheckbox || !selectAllCheckbox.checked) {
                    let allChecked = true;
                    userCheckboxes.forEach(cb => {
                        if (!cb.checked) {
                            allChecked = false;
                        }
                    });
                    if (allChecked && selectAllCheckbox) {
                         selectAllCheckbox.checked = true;
                          userCheckboxes.forEach(cb => { cb.disabled = true; });
                    }
                }
            });
        });

         if (selectAllCheckbox) {
             selectAllCheckbox.addEventListener('click', function() {
                 if (!this.checked) {
                      userCheckboxes.forEach(checkbox => {
                         checkbox.disabled = false;
                     });
                 }
             });
             if (selectAllCheckbox.checked) {
                 userCheckboxes.forEach(checkbox => { checkbox.disabled = true; });
             }
         }
    });
</script>
