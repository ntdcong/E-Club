<?php
// Kiểm tra quyền truy cập
if (!isClubLeader()) {
    redirectWithMessage('danger', 'Bạn không có quyền truy cập vào trang này!', 'index.php');
    exit;
}

// Lấy club_id từ URL
$club_id = isset($_GET['club_id']) ? (int)$_GET['club_id'] : 0;

// Lấy thông tin CLB mà người dùng quản lý
$stmt = $conn->prepare("SELECT c.* FROM clubs c 
                       INNER JOIN club_leaders cl ON c.id = cl.club_id 
                       WHERE cl.user_id = ? AND c.id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $club_id);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

if (!$club) {
    redirectWithMessage('danger', 'Bạn không quản lý CLB này hoặc CLB không tồn tại!', 'index.php?page=club_leader');
    exit;
}

// Lấy danh sách thành viên của CLB
$stmt_members = $conn->prepare("SELECT u.id, u.name, u.email FROM users u
                               INNER JOIN club_members cm ON u.id = cm.user_id
                               WHERE cm.club_id = ? AND cm.status = 'approved'");
$stmt_members->bind_param("i", $club_id);
$stmt_members->execute();
$members = $stmt_members->get_result()->fetch_all(MYSQLI_ASSOC);

// Thêm cột notification_before_event và notification_on_event_day vào bảng events nếu chưa có
$conn->query("SHOW COLUMNS FROM events LIKE 'notification_before_event'");
if ($conn->affected_rows == 0) {
    $conn->query("ALTER TABLE events ADD COLUMN notification_before_event TINYINT(1) DEFAULT 0");
}

$conn->query("SHOW COLUMNS FROM events LIKE 'notification_on_event_day'");
if ($conn->affected_rows == 0) {
    $conn->query("ALTER TABLE events ADD COLUMN notification_on_event_day TINYINT(1) DEFAULT 0");
}

// Xử lý gửi thông báo chung
if (isset($_POST['send_announcement'])) {
    // Xác thực và lọc dữ liệu đầu vào
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $recipient_ids = isset($_POST['recipient_ids']) ? $_POST['recipient_ids'] : [];
    $send_to_all = isset($_POST['send_to_all']) && $_POST['send_to_all'] == '1';

    if (empty($subject) || empty($message)) {
        flashMessage('Vui lòng điền đầy đủ tiêu đề và nội dung thông báo.', 'warning');
    } elseif (!$send_to_all && empty($recipient_ids)) {
        flashMessage('Vui lòng chọn ít nhất một người nhận hoặc chọn gửi cho tất cả thành viên.', 'warning');
    } else {
        // Include file cấu hình PHPMailer và hàm gửi thông báo chung
        require_once __DIR__ . '/../../config/phpmailer_config.php';
        
        // Gọi hàm sendClubAnnouncement mới từ config
        $result = sendClubAnnouncement($club_id, $subject, $message, $recipient_ids, $send_to_all);
        
        // Sử dụng kết quả trả về để đặt flash message
        if ($result['success']) {
            flashMessage($result['message'], 'success'); // Sử dụng 'success' cho thành công
        } else {
            // Sử dụng 'warning' hoặc 'danger' tùy thuộc vào thông báo lỗi
            $message_type = ($result['message'] === 'Không có người nhận nào được chọn.' || $result['message'] === 'Không tìm thấy người nhận hợp lệ.') ? 'warning' : 'danger';
            flashMessage($result['message'], $message_type);
        }
    }

    // Redirect để tránh gửi lại form khi refresh
    header("Location: index.php?page=club_leader/email_notifications&club_id=$club_id");
    exit;
}

// Xử lý cài đặt thông báo tự động cho sự kiện
if (isset($_POST['schedule_notification'])) {
    $event_id = $_POST['event_id'];
    $before_event = isset($_POST['before_event']) ? 1 : 0;
    $on_event_day = isset($_POST['on_event_day']) ? 1 : 0;
    
    // Include file cấu hình PHPMailer
    require_once __DIR__ . '/../../config/phpmailer_config.php';
    
    $result = scheduleEventNotifications($event_id, $before_event, $on_event_day);
    
    if ($result) {
        flashMessage('Đã cập nhật cài đặt thông báo tự động cho sự kiện.');
    } else {
        flashMessage('Có lỗi xảy ra khi cập nhật cài đặt. Vui lòng thử lại sau.', 'danger');
    }
    
    // Redirect để tránh gửi lại form khi refresh
    header("Location: index.php?page=club_leader/email_notifications&club_id=$club_id");
    exit;
}

// Xử lý gửi thông báo thử nghiệm
if (isset($_POST['test_notification'])) {
    $event_id = $_POST['event_id'];
    $test_type = $_POST['test_type']; // 'before' hoặc 'day_of'
    $test_email = !empty($_POST['test_email']) ? trim($_POST['test_email']) : null;
    
    // Include file cấu hình PHPMailer
    require_once __DIR__ . '/../../config/phpmailer_config.php';
    
    // Gửi email thử nghiệm
    $days_before = ($test_type == 'before') ? 1 : 0;
    $result = sendTestEventReminder($event_id, $days_before, $test_email);
    
    if ($result) {
        flashMessage('Đã gửi email thử nghiệm thành công. Email thử nghiệm chỉ được gửi đến email bạn đã nhập hoặc email của bạn (nếu không nhập email cụ thể).');
    } else {
        flashMessage('Có lỗi xảy ra khi gửi email thử nghiệm. Vui lòng thử lại sau.', 'danger');
    }
    
    // Redirect để tránh gửi lại form khi refresh
    header("Location: index.php?page=club_leader/email_notifications&club_id=$club_id");
    exit;
}

// Lấy danh sách sự kiện sắp tới của CLB
$stmt = $conn->prepare("SELECT * FROM events WHERE club_id = ? AND event_date >= CURDATE() ORDER BY event_date");
$stmt->bind_param("i", $club['id']);
$stmt->execute();
$upcoming_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close(); // Đóng statement sau khi dùng
$stmt_members->close(); // Đóng statement sau khi dùng
?>

<style>
    .member-list {
        max-height: 300px; /* Giới hạn chiều cao */
        overflow-y: auto; /* Thêm thanh cuộn dọc */
        border: 1px solid #dee2e6; /* Thêm viền nhẹ */
        padding: 10px;
        border-radius: 0.25rem; /* Bo góc nhẹ */
        background-color: #f8f9fa; /* Nền xám nhạt */
    }
    .member-item {
        padding: 5px 0;
        border-bottom: 1px solid #e9ecef; /* Đường kẻ phân cách */
    }
    .member-item:last-child {
        border-bottom: none; /* Bỏ đường kẻ cuối */
    }
    .card-header {
        border-bottom: none; /* Bỏ đường kẻ dưới header */
    }
    .form-control, .btn {
        border-radius: 0.25rem; /* Đồng bộ bo góc */
    }
    /* Thêm một chút padding cho card */
    .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: none;
    }
    .card-title i {
        margin-right: 8px;
        font-size: 1.1em;
    }
    /* Style cho checkbox chọn tất cả */
    #select-all-container {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
</style>

<h2 class="mb-4">Quản lý Thông báo Email - <?php echo htmlspecialchars($club['name']); ?></h2>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-primary">
            <i class="bi bi-envelope-paper-heart"></i> Gửi thông báo đến thành viên
        </h5>
    </div>
    <div class="card-body">
        <form method="post" action="" id="announcement-form">
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
                        <textarea class="form-control" id="message" name="message" rows="8" required></textarea>
                        <div class="form-text">Bạn có thể sử dụng HTML cơ bản để định dạng nội dung.</div>
                    </div>
                </div>

                <!-- Cột chọn người nhận -->
                <div class="col-md-5">
                    <h6 class="mb-3">Chọn người nhận <span class="text-danger">*</span></h6>
                    <?php if (empty($members)): ?>
                        <div class="alert alert-warning">Không có thành viên nào trong CLB.</div>
                    <?php else: ?>
                        <div id="select-all-container" class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="select-all-members" name="send_to_all" value="1">
                            <label class="form-check-label fw-bold" for="select-all-members">
                                Gửi cho tất cả thành viên (<?php echo count($members); ?>)
                            </label>
                        </div>
                        <div class="member-list">
                            <?php foreach ($members as $member): ?>
                                <div class="form-check member-item">
                                    <input class="form-check-input member-checkbox" type="checkbox" name="recipient_ids[]" value="<?php echo $member['id']; ?>" id="member_<?php echo $member['id']; ?>">
                                    <label class="form-check-label" for="member_<?php echo $member['id']; ?>">
                                        <?php echo htmlspecialchars($member['name']); ?> <small class="text-muted">(<?php echo htmlspecialchars($member['email']); ?>)</small>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
             <div class="d-flex justify-content-end">
                 <button type="submit" name="send_announcement" class="btn btn-primary" <?php echo empty($members) ? 'disabled' : ''; ?>>
                    <i class="bi bi-send"></i> Gửi thông báo
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
     <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-primary">
            <i class="bi bi-calendar-event"></i> Thông báo tự động cho sự kiện
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($upcoming_events)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Không có sự kiện nào sắp tới để cài đặt thông báo.
            </div>
        <?php else: ?>
            <p class="card-text">Cài đặt hoặc xem trạng thái thông báo tự động (gửi email) cho thành viên đã đăng ký tham gia sự kiện.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sự kiện</th>
                            <th>Ngày diễn ra</th>
                            <th class="text-center">Thông báo 1 ngày trước</th>
                            <th class="text-center">Thông báo trong ngày</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming_events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($event['event_date'])); ?></td>
                                <td class="text-center">
                                    <?php if ($event['notification_before_event']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Đã bật</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bi bi-x-circle-fill"></i> Đã tắt</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($event['notification_on_event_day']): ?>
                                         <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Đã bật</span>
                                    <?php else: ?>
                                         <span class="badge bg-secondary"><i class="bi bi-x-circle-fill"></i> Đã tắt</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#eventModal<?php echo $event['id']; ?>">
                                        <i class="bi bi-gear"></i> Cài đặt
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
     <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-primary">
            <i class="bi bi-info-circle"></i> Hướng dẫn sử dụng
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
                <h6><i class="bi bi-envelope-check"></i> Gửi thông báo chung</h6>
                <ul>
                    <li>Soạn tiêu đề và nội dung thông báo.</li>
                    <li>Chọn thành viên cụ thể bạn muốn gửi hoặc đánh dấu vào ô "Gửi cho tất cả thành viên".</li>
                    <li>Nếu không chọn ai và không chọn "Gửi cho tất cả", thông báo sẽ không được gửi.</li>
                    <li>Nội dung hỗ trợ HTML cơ bản để định dạng.</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6><i class="bi bi-stopwatch"></i> Thông báo tự động cho sự kiện</h6>
                 <ul>
                    <li>Nhấn nút "Cài đặt" cho sự kiện tương ứng.</li>
                    <li>Chọn bật/tắt thông báo gửi trước 1 ngày và/hoặc trong ngày diễn ra sự kiện.</li>
                    <li>Thông báo này chỉ gửi đến những thành viên đã đăng ký tham gia sự kiện đó.</li>
                    <li>Sử dụng nút "Test" trong modal cài đặt để gửi email thử nghiệm đến địa chỉ email của bạn hoặc địa chỉ khác.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal cho cài đặt sự kiện (giữ nguyên cấu trúc, chỉ cần đảm bảo ID đúng) -->
<?php if (!empty($upcoming_events)): ?>
    <?php foreach ($upcoming_events as $event): ?>
        <div class="modal fade" id="eventModal<?php echo $event['id']; ?>" tabindex="-1" aria-labelledby="eventModalLabel<?php echo $event['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg"> <!-- Tăng kích thước modal -->
                <div class="modal-content">
                    <form method="post" action="">
                         <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="eventModalLabel<?php echo $event['id']; ?>">
                                <i class="bi bi-gear-fill"></i> Cài đặt thông báo: <?php echo htmlspecialchars($event['title']); ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                             <p><strong>Ngày diễn ra:</strong> <?php echo date('d/m/Y H:i', strtotime($event['event_date'])); ?></p>
                             <p>Chọn thời điểm gửi email nhắc nhở tự động đến các thành viên đã đăng ký tham gia.</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" role="switch" id="before_event<?php echo $event['id']; ?>"
                                               name="before_event" value="1" <?php echo $event['notification_before_event'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="before_event<?php echo $event['id']; ?>">
                                            Gửi thông báo <strong>1 ngày trước</strong> sự kiện
                                        </label>
                                    </div>
                                </div>
                                 <div class="col-md-6 mb-3">
                                     <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" role="switch" id="on_event_day<?php echo $event['id']; ?>"
                                               name="on_event_day" value="1" <?php echo $event['notification_on_event_day'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="on_event_day<?php echo $event['id']; ?>">
                                            Gửi thông báo <strong>vào ngày</strong> diễn ra sự kiện
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-2">
                                <i class="bi bi-info-circle-fill"></i> Email sẽ chỉ được gửi đến những thành viên đã xác nhận tham gia sự kiện này.
                            </div>

                            <hr>
                            <h6><i class="bi bi-send-check"></i> Thử nghiệm gửi thông báo</h6>
                            <p class="text-muted small">Gửi email thử nghiệm để xem trước nội dung và định dạng. Email thử nghiệm sẽ có tiêu đề "[THỬ NGHIỆM]".</p>

                            <div class="mb-3">
                                <label for="test_email<?php echo $event['id']; ?>" class="form-label">Email nhận thử nghiệm (tùy chọn)</label>
                                <input type="email" class="form-control form-control-sm" id="test_email<?php echo $event['id']; ?>" placeholder="Mặc định: <?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>">
                                <div class="form-text">Nếu để trống, email thử nghiệm sẽ được gửi đến địa chỉ email của bạn.</div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm test-button flex-grow-1"
                                    data-event-id="<?php echo $event['id']; ?>"
                                    data-test-type="before"
                                    title="Gửi thử email thông báo 1 ngày trước sự kiện">
                                    <i class="bi bi-clock-history"></i> Test (Trước 1 ngày)
                                </button>

                                <button type="button" class="btn btn-outline-secondary btn-sm test-button flex-grow-1"
                                    data-event-id="<?php echo $event['id']; ?>"
                                    data-test-type="day_of"
                                    title="Gửi thử email thông báo vào ngày diễn ra sự kiện">
                                    <i class="bi bi-calendar-check"></i> Test (Trong ngày)
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" name="schedule_notification" class="btn btn-primary">
                                <i class="bi bi-save"></i> Lưu cài đặt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Form ẩn để gửi thông báo thử nghiệm (giữ nguyên) -->
<form id="test-notification-form" method="post" action="" style="display: none;">
    <input type="hidden" name="event_id" id="test_event_id" value="">
    <input type="hidden" name="test_type" id="test_type" value="">
    <input type="hidden" name="test_email" id="test_email_value" value="">
    <input type="hidden" name="test_notification" value="1">
</form>

<!-- CSS Fix cho backdrop (giữ nguyên) -->
<style>
/* Fix cho vấn đề backdrop */
body.modal-open {
    overflow: auto !important;
    padding-right: 0 !important;
}
.modal-backdrop.show {
    opacity: 0.5;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Xử lý chọn người nhận thông báo ---
        const selectAllCheckbox = document.getElementById('select-all-members');
        const memberCheckboxes = document.querySelectorAll('.member-checkbox');
        const announcementForm = document.getElementById('announcement-form'); // Thêm ID cho form

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                memberCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    checkbox.disabled = isChecked; // Vô hiệu hóa checkbox thành viên nếu chọn tất cả
                });
            });
        }

        memberCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Nếu có bất kỳ thành viên nào được bỏ chọn, bỏ chọn "Chọn tất cả"
                if (!this.checked) {
                    if (selectAllCheckbox) selectAllCheckbox.checked = false;
                }
                // Kiểm tra nếu tất cả thành viên được chọn (ngoại trừ khi 'Chọn tất cả' đang check)
                 else if (!selectAllCheckbox || !selectAllCheckbox.checked) {
                    let allChecked = true;
                    memberCheckboxes.forEach(cb => {
                        if (!cb.checked) {
                            allChecked = false;
                        }
                    });
                    if (allChecked && selectAllCheckbox) {
                         selectAllCheckbox.checked = true;
                         // Kích hoạt lại việc vô hiệu hóa checkbox thành viên
                          memberCheckboxes.forEach(cb => { cb.disabled = true; });
                    }
                }
            });
        });

        // Đảm bảo các checkbox thành viên được kích hoạt khi bỏ chọn "Chọn tất cả"
         if (selectAllCheckbox) {
             selectAllCheckbox.addEventListener('click', function() { // Sử dụng click để xử lý ngay cả khi bỏ chọn
                 if (!this.checked) {
                      memberCheckboxes.forEach(checkbox => {
                         checkbox.disabled = false;
                     });
                 }
             });
              // Trigger initial state in case the page was reloaded with 'select all' checked
             if (selectAllCheckbox.checked) {
                 memberCheckboxes.forEach(checkbox => { checkbox.disabled = true; });
             }
         }

        // --- Xử lý backdrop modal (giữ nguyên) ---
        function removeBackdrop() {
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }

        document.body.addEventListener('hidden.bs.modal', function(e) {
            // Đợi một chút để hiệu ứng hoàn tất rồi mới xóa backdrop
             setTimeout(removeBackdrop, 50);
        });

        var modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(function(element) {
             // Khởi tạo nếu chưa có
             var modalInstance = bootstrap.Modal.getInstance(element);
             if (!modalInstance) {
                 modalInstance = new bootstrap.Modal(element, {
                    backdrop: true, // Nên để true để có backdrop
                    keyboard: true,
                    focus: true
                });
             }

            // Xử lý nút đóng modal
            var closeButtons = element.querySelectorAll('[data-bs-dismiss="modal"]');
            closeButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    // Lấy instance modal và ẩn nó đi
                    var currentModal = bootstrap.Modal.getInstance(element);
                    if (currentModal) {
                        currentModal.hide();
                    }
                     // Không cần gọi removeBackdrop ở đây vì đã có listener 'hidden.bs.modal'
                });
            });
        });

        var modalToggleButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
        modalToggleButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var targetModalSelector = this.getAttribute('data-bs-target');
                if (targetModalSelector) {
                    var modalElement = document.querySelector(targetModalSelector);
                    if (modalElement) {
                        // Không cần xóa backdrop cũ thủ công ở đây
                        var modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        modal.show();
                    }
                }
            });
        });

        // --- Xử lý nút gửi thông báo thử nghiệm (giữ nguyên) ---
        var testButtons = document.querySelectorAll('.test-button');
        testButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var eventId = this.getAttribute('data-event-id');
                var testType = this.getAttribute('data-test-type');
                var modal = this.closest('.modal');
                // Tìm input email trong modal tương ứng
                var emailInput = modal.querySelector('input[type="email"][id^="test_email"]');

                var form = document.getElementById('test-notification-form');
                document.getElementById('test_event_id').value = eventId;
                document.getElementById('test_type').value = testType;
                 // Lấy giá trị từ input email tìm được
                document.getElementById('test_email_value').value = emailInput ? emailInput.value : '';

                form.submit();
            });
        });
    });
</script> 