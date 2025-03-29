<?php
if (!isClubLeader()) {
    flashMessage('Bạn không có quyền truy cập', 'danger');
    redirect('/');
}

// Get event details
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    flashMessage('Sự kiện không hợp lệ', 'danger');
    redirect('/index.php?page=club_leader/events');
}

$event_id = sanitize($_GET['event_id']);

// Get event details and verify club leader permission
$sql = "SELECT e.*, c.name as club_name 
       FROM events e 
       INNER JOIN clubs c ON e.club_id = c.id 
       INNER JOIN club_leaders cl ON c.id = cl.club_id 
       WHERE e.id = ? AND cl.user_id = ? AND e.status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    flashMessage('Không tìm thấy sự kiện hoặc bạn không có quyền truy cập', 'danger');
    redirect('/index.php?page=club_leader/events');
}

// Handle attendance update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance'])) {
    $member_id = sanitize($_POST['member_id']);
    $status = sanitize($_POST['status']);
    
    if ($status === 'present' || $status === 'absent') {
        // Check if attendance record exists
        $check_sql = "SELECT id FROM attendance WHERE event_id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $event_id, $member_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            // Update existing record
            $update_sql = "UPDATE attendance SET status = ? WHERE event_id = ? AND user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sii", $status, $event_id, $member_id);
            $update_stmt->execute();
        } else {
            // Create new record
            $insert_sql = "INSERT INTO attendance (event_id, user_id, status) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iis", $event_id, $member_id, $status);
            $insert_stmt->execute();
        }
        
        flashMessage('Đã cập nhật điểm danh thành công');
        redirect("/index.php?page=club_leader/event_attendance&event_id={$event_id}");
    }
}

// Get club members and their attendance status
$sql = "SELECT u.id, u.name, u.email, COALESCE(a.status, 'absent') as attendance_status 
       FROM users u 
       INNER JOIN club_members cm ON u.id = cm.user_id 
       LEFT JOIN attendance a ON u.id = a.user_id AND a.event_id = ? 
       WHERE cm.club_id = ? AND cm.status = 'approved' 
       ORDER BY u.name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $event_id, $event['club_id']);
$stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate attendance statistics
$total_members = count($members);
$present_count = 0;
$absent_count = 0;

foreach ($members as $member) {
    if ($member['attendance_status'] === 'present') {
        $present_count++;
    } else {
        $absent_count++;
    }
}

$attendance_rate = $total_members > 0 ? round(($present_count / $total_members) * 100, 1) : 0;
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">
                <i class="bi bi-calendar-check me-2"></i>Điểm danh sự kiện
            </h2>
            <p class="text-muted mt-2 mb-0"><?php echo htmlspecialchars($event['title']); ?></p>
            <p class="text-muted mb-0">
                <i class="bi bi-people me-2"></i><?php echo htmlspecialchars($event['club_name']); ?>
                <span class="mx-2">•</span>
                <i class="bi bi-calendar me-2"></i><?php echo date('d/m/Y H:i', strtotime($event['event_date'])); ?>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="index.php?page=club_leader/events" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Thống kê điểm danh</h5>
                    <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php echo $attendance_rate; ?>%" 
                             aria-valuenow="<?php echo $attendance_rate; ?>" 
                             aria-valuemin="0" aria-valuemax="100">
                            <?php echo $attendance_rate; ?>%
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>
                            <i class="bi bi-check-circle text-success"></i> Có mặt: <?php echo $present_count; ?>
                        </div>
                        <div>
                            <i class="bi bi-x-circle text-danger"></i> Vắng mặt: <?php echo $absent_count; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-4">Danh sách điểm danh</h5>
            
            <?php if (empty($members)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>Chưa có thành viên nào trong CLB.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Thành viên</th>
                                <th>Email</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td>
                                        <?php if ($member['attendance_status'] === 'present'): ?>
                                            <span class="badge bg-success">Có mặt</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Vắng mặt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline-block">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <input type="hidden" name="update_attendance" value="1">
                                            
                                            <?php if ($member['attendance_status'] === 'present'): ?>
                                                <button type="submit" name="status" value="absent" 
                                                        class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-x-circle me-1"></i>Đánh dấu vắng
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="status" value="present" 
                                                        class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-check-circle me-1"></i>Đánh dấu có mặt
                                                </button>
                                            <?php endif; ?>
                                        </form>
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

<style>
.progress {
    border-radius: 10px;
}

.progress-bar {
    transition: width 0.3s ease;
}

.card {
    border-radius: 10px;
}

.table th {
    border-top: none;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
}
</style>