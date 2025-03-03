<?php
if (!isClubLeader()) {
    flashMessage('Bạn không có quyền truy cập', 'danger');
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

// Handle member management actions
if (isset($_POST['action'])) {
    $action = sanitize($_POST['action']);
    $member_id = sanitize($_POST['member_id']);
    $club_id = sanitize($_POST['club_id']);

    // Verify if the club leader manages this club
    if (!isClubLeaderOf($club_id)) {
        flashMessage('Bạn không có quyền quản lý CLB này', 'danger');
        redirect('/index.php?page=club_leader');
    }

    switch ($action) {
        case 'approve':
            $sql = "UPDATE club_members SET status = 'approved' WHERE id = ? AND club_id = ?";
            break;
        case 'reject':
            $sql = "DELETE FROM club_members WHERE id = ? AND club_id = ?";
            break;
    }

    if (isset($sql)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $member_id, $club_id);
        if ($stmt->execute()) {
            flashMessage($action === 'approve' ? 'Đã duyệt thành viên thành công' : 'Đã xóa thành viên thành công');
        } else {
            flashMessage('Không thể xử lý yêu cầu', 'danger');
        }
    }
}

// Handle event registration
if (isset($_POST['register_event'])) {
    $club_id = sanitize($_POST['club_id']);
    $event_name = sanitize($_POST['event_name']);
    $event_date = sanitize($_POST['event_date']);
    $event_time = sanitize($_POST['event_time']);
    $event_description = sanitize($_POST['event_description']);

    if (!isClubLeaderOf($club_id)) {
        flashMessage('Bạn không có quyền tạo sự kiện cho CLB này', 'danger');
    } else {
        $event_datetime = $event_date . ' ' . $event_time;
        $sql = "INSERT INTO events (club_id, title, description, event_date, status, created_by) 
               VALUES (?, ?, ?, ?, 'pending', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $club_id, $event_name, $event_description, $event_datetime, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            flashMessage('Đã đăng ký sự kiện, chờ phê duyệt từ quản trị viên');
        } else {
            flashMessage('Không thể đăng ký sự kiện', 'danger');
        }
    }
}
?>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="border-bottom pb-3">
                <i class="bi bi-person-badge me-2"></i>Trang Quản Lý CLB
            </h2>
        </div>
    </div>
    
    <?php if (empty($managed_clubs)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>Bạn chưa được phân công quản lý CLB nào.
        </div>
    <?php endif; ?>
    
    <?php foreach ($managed_clubs as $club): ?>
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="bi bi-people-fill me-2"></i><?php echo htmlspecialchars($club['name']); ?>
            </h3>
        </div>
        <div class="card-body">
            <!-- Club Statistics -->
            <?php
            $sql = "SELECT 
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_members,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_members
                    FROM club_members WHERE club_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $club['id']);
            $stmt->execute();
            $stats = $stmt->get_result()->fetch_assoc();
            
            // Get upcoming events
            $sql = "SELECT COUNT(*) as upcoming_events FROM events 
                   WHERE club_id = ? AND event_date >= CURDATE()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $club['id']);
            $stmt->execute();
            $events_count = $stmt->get_result()->fetch_assoc()['upcoming_events'];
            ?>
            
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <h5 class="text-primary">Thành viên đã duyệt</h5>
                            <h3 class="display-4"><?php echo $stats['approved_members']; ?></h3>
                            <p class="text-muted">Thành viên đang hoạt động</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <h5 class="text-warning">Thành viên chờ duyệt</h5>
                            <h3 class="display-4"><?php echo $stats['pending_members']; ?></h3>
                            <p class="text-muted">Cần xác nhận</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <h5 class="text-success">Sự kiện sắp tới</h5>
                            <h3 class="display-4"><?php echo $events_count; ?></h3>
                            <p class="text-muted">Hoạt động đã lên lịch</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs for different sections -->
            <ul class="nav nav-tabs mb-4" id="clubTabs<?php echo $club['id']; ?>" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="members-tab<?php echo $club['id']; ?>" data-bs-toggle="tab" 
                            data-bs-target="#members<?php echo $club['id']; ?>" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-people me-2"></i>Quản lý thành viên
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="events-tab<?php echo $club['id']; ?>" data-bs-toggle="tab" 
                            data-bs-target="#events<?php echo $club['id']; ?>" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-calendar-event me-2"></i>Đăng ký sự kiện
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="clubTabsContent<?php echo $club['id']; ?>">
                <!-- Member Management Tab -->
                <div class="tab-pane fade show active" id="members<?php echo $club['id']; ?>" role="tabpanel">
                    <h4 class="mb-3"><i class="bi bi-person-check me-2"></i>Quản lý thành viên</h4>
                    
                    <?php if ($stats['pending_members'] > 0): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>Có <?php echo $stats['pending_members']; ?> thành viên đang chờ duyệt
                    </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên thành viên</th>
                                    <th>Email</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tham gia</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT cm.*, u.name, u.email 
                                       FROM club_members cm 
                                       INNER JOIN users u ON cm.user_id = u.id 
                                       WHERE cm.club_id = ?
                                       ORDER BY cm.status ASC, cm.joined_at DESC";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $club['id']);
                                $stmt->execute();
                                $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                
                                if (empty($members)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Chưa có thành viên nào</td>
                                </tr>
                                <?php else:
                                    foreach ($members as $member):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $member['status'] === 'approved' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo $member['status'] === 'approved' ? 'Đã duyệt' : 'Chờ duyệt'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($member['joined_at'])); ?></td>
                                    <td>
                                        <?php if ($member['status'] === 'pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle me-1"></i>Duyệt
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên này?')">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash me-1"></i>Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Event Registration Tab -->
                <div class="tab-pane fade" id="events<?php echo $club['id']; ?>" role="tabpanel">
                    <h4 class="mb-3"><i class="bi bi-calendar-plus me-2"></i>Đăng ký sự kiện mới</h4>
                    
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="event_name" class="form-label">Tên sự kiện</label>
                                        <input type="text" class="form-control" id="event_name" name="event_name" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="event_date" class="form-label">Ngày diễn ra</label>
                                        <input type="date" class="form-control" id="event_date" name="event_date" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="event_time" class="form-label">Thời gian</label>
                                        <input type="time" class="form-control" id="event_time" name="event_time" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="event_description" class="form-label">Mô tả sự kiện</label>
                                    <textarea class="form-control" id="event_description" name="event_description" rows="4" required></textarea>
                                    <div class="form-text">Mô tả chi tiết về sự kiện, mục đích, đối tượng tham gia...</div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" name="register_event" class="btn btn-primary">
                                        <i class="bi bi-send me-2"></i>Gửi yêu cầu phê duyệt
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- List of pending and upcoming events -->
                    <?php
                    $sql = "SELECT e.*, 
                           CASE 
                               WHEN e.status = 'approved' THEN 'Đã duyệt'
                               WHEN e.status = 'pending' THEN 'Chờ duyệt'
                               WHEN e.status = 'rejected' THEN 'Đã từ chối'
                           END as status_text,
                           CASE 
                               WHEN e.status = 'approved' THEN 'success'
                               WHEN e.status = 'pending' THEN 'warning'
                               WHEN e.status = 'rejected' THEN 'danger'
                           END as status_class
                           FROM events e 
                           WHERE e.club_id = ? 
                           ORDER BY e.event_date DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $club['id']);
                    $stmt->execute();
                    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    ?>
                    
                    <h4 class="mt-4 mb-3"><i class="bi bi-calendar-check me-2"></i>Sự kiện của CLB</h4>
                    
                    <?php if (empty($events)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>CLB chưa có sự kiện nào
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên sự kiện</th>
                                        <th>Ngày diễn ra</th>
                                        <th>Trạng thái</th>
                                        <th>Người tham gia</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): 
                                        // Get attendance count
                                        $sql = "SELECT COUNT(*) as count FROM attendance WHERE event_id = ? AND status = 'present'";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $event['id']);
                                        $stmt->execute();
                                        $attendance_count = $stmt->get_result()->fetch_assoc()['count'];
                                        
                                        // Check if event date has passed
                                        $event_passed = strtotime($event['event_date']) < time();
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($event['event_date'])); ?>
                                            <?php if ($event_passed): ?>
                                                <span class="badge bg-secondary ms-1">Đã kết thúc</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $event['status_class']; ?>">
                                                <?php echo $event['status_text']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill">
                                                <?php echo $attendance_count; ?> người
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?page=events&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye me-1"></i>Xem
                                            </a>
                                            <?php if ($event['status'] === 'approved' && !$event_passed): ?>
                                                <a href="index.php?page=club_leader&action=attendance&event_id=<?php echo $event['id']; ?>" class="btn btn-sm btn-success ms-1">
                                                    <i class="bi bi-clipboard-check me-1"></i>Điểm danh
                                                </a>
                                            <?php endif; ?>
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
    </div>
    <?php endforeach; ?>
    
    <!-- Attendance Modal -->
    <?php if (isset($_GET['action']) && $_GET['action'] === 'attendance' && isset($_GET['event_id'])): 
        $event_id = sanitize($_GET['event_id']);
        
        // Get event details
        $sql = "SELECT e.*, c.name as club_name 
               FROM events e 
               INNER JOIN clubs c ON e.club_id = c.id 
               WHERE e.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        
        // Check if club leader manages this club
        if (!$event || !isClubLeaderOf($event['club_id'])) {
            flashMessage('Bạn không có quyền quản lý sự kiện này', 'danger');
            redirect('/index.php?page=club_leader');
        }
        
        // Handle attendance update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance'])) {
            $user_id = sanitize($_POST['user_id']);
            $status = sanitize($_POST['status']);
            
            // Prevent club leaders from marking their own attendance
            if ($user_id == $_SESSION['user_id']) {
                flashMessage('Trưởng câu lạc bộ không thể tự điểm danh cho mình', 'danger');
                redirect('/index.php?page=club_leader&action=attendance&event_id=' . $event_id);
            }
            
            // Kiểm tra xem bản ghi điểm danh đã tồn tại chưa
            $check_sql = "SELECT id FROM attendance WHERE event_id = ? AND user_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $event_id, $user_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Cập nhật bản ghi hiện có
                $update_sql = "UPDATE attendance SET status = ? WHERE event_id = ? AND user_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sii", $status, $event_id, $user_id);
                
                if ($update_stmt->execute()) {
                    flashMessage('Đã cập nhật điểm danh thành công');
                } else {
                    flashMessage('Không thể cập nhật điểm danh: ' . $conn->error, 'danger');
                }
            } else {
                // Thêm bản ghi mới
                $insert_sql = "INSERT INTO attendance (event_id, user_id, status) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iis", $event_id, $user_id, $status);
                
                if ($insert_stmt->execute()) {
                    flashMessage('Đã thêm điểm danh thành công');
                } else {
                    flashMessage('Không thể thêm điểm danh: ' . $conn->error, 'danger');
                }
            }
        }
        
        // Get club members
        $sql = "SELECT u.id, u.name, u.email, a.status 
               FROM users u 
               INNER JOIN club_members cm ON u.id = cm.user_id 
               LEFT JOIN attendance a ON u.id = a.user_id AND a.event_id = ? 
               WHERE cm.club_id = ? AND cm.status = 'approved' 
               AND u.id != ?  /* Exclude club leader from the list */
               ORDER BY u.name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $event_id, $event['club_id'], $_SESSION['user_id']);
        $stmt->execute();
        $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>
    <div class="modal fade show" id="attendanceModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-clipboard-check me-2"></i>Điểm danh sự kiện: <?php echo htmlspecialchars($event['title']); ?>
                    </h5>
                    <a href="index.php?page=club_leader" class="btn-close btn-close-white"></a>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>CLB:</strong> <?php echo htmlspecialchars($event['club_name']); ?><br>
                        <strong>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($event['event_date'])); ?>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tên thành viên</th>
                                    <th>Email</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td>
                                        <?php if ($member['status'] === 'present'): ?>
                                            <span class="badge bg-success">Có mặt</span>
                                        <?php elseif ($member['status'] === 'absent'): ?>
                                            <span class="badge bg-danger">Vắng mặt</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Chưa điểm danh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                            <input type="hidden" name="update_attendance" value="1">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="submit" name="status" value="present" class="btn <?php echo $member['status'] === 'present' ? 'btn-success' : 'btn-outline-success'; ?>">
                                                    <i class="bi bi-check-circle me-1"></i>Có mặt
                                                </button>
                                                <button type="submit" name="status" value="absent" class="btn <?php echo $member['status'] === 'absent' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                                                    <i class="bi bi-x-circle me-1"></i>Vắng mặt
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="index.php?page=club_leader" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>