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
    redirect('/index.php?page=club_leader/events');
}

// Get upcoming events for each club
$club_events = [];
foreach ($managed_clubs as $club) {
    $sql = "SELECT * FROM events 
           WHERE club_id = ? AND event_date >= CURDATE()
           ORDER BY event_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $club['id']);
    $stmt->execute();
    $club_events[$club['id']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="border-bottom pb-3">
                <i class="bi bi-calendar-event me-2"></i>Quản lý sự kiện CLB
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
                <!-- Event Registration Form -->
                <div class="mb-4">
                    <h4 class="mb-3"><i class="bi bi-plus-circle me-2"></i>Đăng ký sự kiện mới</h4>
                    <form method="POST" action="">
                        <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="event_name" class="form-label">Tên sự kiện</label>
                                <input type="text" class="form-control" id="event_name" name="event_name" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="event_date" class="form-label">Ngày</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="event_time" class="form-label">Giờ</label>
                                <input type="time" class="form-control" id="event_time" name="event_time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="event_description" class="form-label">Mô tả sự kiện</label>
                            <textarea class="form-control" id="event_description" name="event_description" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="register_event" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Đăng ký sự kiện
                        </button>
                    </form>
                </div>

                <!-- Upcoming Events List -->
                <div class="mt-4">
                    <h4 class="mb-3"><i class="bi bi-calendar3 me-2"></i>Sự kiện sắp tới</h4>
                    <?php if (empty($club_events[$club['id']])): ?>
                        <p class="text-muted">Chưa có sự kiện nào được đăng ký.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tên sự kiện</th>
                                        <th>Thời gian</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($club_events[$club['id']] as $event): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($event['event_date'])); ?></td>
                                            <td>
                                                <?php if ($event['status'] === 'approved'): ?>
                                                    <span class="badge bg-success">Đã duyệt</span>
                                                <?php elseif ($event['status'] === 'rejected'): ?>
                                                    <span class="badge bg-danger">Từ chối</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Chờ duyệt</span>
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
    <?php endforeach; ?>
</div>