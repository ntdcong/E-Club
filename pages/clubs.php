<?php
// Handle club join requests
if (isLoggedIn() && isset($_POST['join_club'])) {
    // Prevent admin users from joining clubs
    if (isAdmin()) {
        flashMessage('Admin accounts cannot join clubs', 'warning');
        redirect('/index.php?page=admin');
    }
    
    // Prevent club leaders from joining other clubs
    if (isClubLeader()) {
        flashMessage('Trưởng câu lạc bộ không thể tham gia các câu lạc bộ khác', 'warning');
        redirect('/index.php?page=clubs');
    }

    $club_id = sanitize($_POST['club_id']);
    
    // Check if already a member
    $sql = "SELECT id FROM club_members WHERE user_id = ? AND club_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $club_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Set initial status as pending
        $sql = "INSERT INTO club_members (user_id, club_id, status) VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user_id'], $club_id);
        
        if ($stmt->execute()) {
            flashMessage('Yêu cầu tham gia đã được gửi thành công');
        } else {
            flashMessage('Không thể gửi yêu cầu tham gia', 'danger');
        }
    } else {
        flashMessage('Bạn đã gửi yêu cầu tham gia câu lạc bộ này', 'warning');
    }
}

// Handle club view/list logic
if (isset($_GET['id'])) {
    // Single club view
    $club_id = sanitize($_GET['id']);
    $sql = "SELECT c.*, COUNT(DISTINCT cm.id) as member_count, COUNT(DISTINCT e.id) as event_count 
           FROM clubs c 
           LEFT JOIN club_members cm ON c.id = cm.club_id AND cm.status = 'approved'
           LEFT JOIN events e ON c.id = e.club_id
           WHERE c.id = ? 
           GROUP BY c.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $club = $stmt->get_result()->fetch_assoc();
    
    if (!$club) {
        flashMessage('Không tìm thấy câu lạc bộ', 'danger');
        redirect('/index.php?page=clubs');
    }
    
    // Get upcoming events
    $sql = "SELECT * FROM events WHERE club_id = ? AND event_date >= CURDATE() ORDER BY event_date LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $upcoming_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Check if user is a member
    $is_member = false;
    if (isLoggedIn()) {
        $sql = "SELECT status FROM club_members WHERE user_id = ? AND club_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user_id'], $club_id);
        $stmt->execute();
        $member_status = $stmt->get_result()->fetch_assoc();
        $is_member = $member_status ? $member_status['status'] : false;
    }
    ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="card-title mb-0"><?php echo htmlspecialchars($club['name']); ?></h2>
                </div>
                <div class="card-body">
                    <div class="club-info mb-4">
                        <h4>Giới thiệu</h4>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($club['description'])); ?></p>
                    </div>
                    
                    <div class="club-stats mb-4">
                        <h4>Thống kê</h4>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="p-3 border rounded text-center">
                                    <i class="bi bi-people fs-2"></i>
                                    <h5 class="mt-2 mb-0"><?php echo $club['member_count']; ?></h5>
                                    <small class="text-muted">Thành viên</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded text-center">
                                    <i class="bi bi-calendar-event fs-2"></i>
                                    <h5 class="mt-2 mb-0"><?php echo $club['event_count']; ?></h5>
                                    <small class="text-muted">Sự kiện</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded text-center">
                                    <i class="bi bi-trophy fs-2"></i>
                                    <h5 class="mt-2 mb-0"><?php echo date('Y') - date('Y', strtotime($club['created_at'])); ?></h5>
                                    <small class="text-muted">Năm hoạt động</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="club-actions d-flex justify-content-between align-items-center">
                        <?php if (isLoggedIn() && !isAdmin()): ?>
                            <?php if (!$is_member): ?>
                                <form method="POST">
                                    <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                    <button type="submit" name="join_club" class="btn btn-success">
                                        <i class="bi bi-person-plus"></i> Tham gia
                                    </button>
                                </form>
                            <?php elseif ($is_member === 'pending'): ?>
                                <span class="badge bg-warning p-2">
                                    <i class="bi bi-clock"></i> Đang chờ phê duyệt
                                </span>
                            <?php elseif ($is_member === 'approved'): ?>
                                <span class="badge bg-success p-2">
                                    <i class="bi bi-check-circle"></i> Thành viên
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($upcoming_events)): ?>
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title h5 mb-0"><i class="bi bi-calendar-week"></i> Sự kiện sắp tới</h3>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($upcoming_events as $event): ?>
                    <a href="index.php?page=events&id=<?php echo $event['id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <small class="text-muted"><i class="bi bi-calendar3"></i> <?php echo date('d/m/Y', strtotime($event['event_date'])); ?></small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars(substr($event['description'], 0, 150)) . '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-primary">Xem chi tiết <i class="bi bi-arrow-right"></i></small>
                            <span class="badge bg-info"><i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($event['event_date'])); ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title h5 mb-0"><i class="bi bi-people"></i> Thành viên câu lạc bộ</h3>
                </div>
                <div class="card-body">
                    <?php
                    $sql = "SELECT u.name, u.email, cm.joined_at, cm.status 
                           FROM club_members cm 
                           INNER JOIN users u ON cm.user_id = u.id 
                           WHERE cm.club_id = ? AND cm.status = 'approved' 
                           ORDER BY cm.joined_at DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $club_id);
                    $stmt->execute();
                    $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    ?>

                    <?php if (!empty($members)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên thành viên</th>
                                    <th>Ngày tham gia</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['name']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($member['joined_at'])); ?></td>
                                    <td><span class="badge bg-success">Thành viên</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted text-center mb-0">Chưa có thành viên nào tham gia câu lạc bộ.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (isAdmin($club['id'])): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title h5 mb-0"><i class="bi bi-gear"></i> <?php echo isAdmin() ? 'Quản trị' : 'Quản lý câu lạc bộ'; ?></h3>
                </div>
                <div class="card-body">
                    <?php if (isAdmin()): ?>
                        <a href="index.php?page=admin&action=edit_club&id=<?php echo $club['id']; ?>" class="btn btn-primary d-block mb-2">
                            <i class="bi bi-pencil-square"></i> Chỉnh sửa thông tin
                        </a>
                        <a href="index.php?page=admin&action=manage_leaders&club_id=<?php echo $club['id']; ?>" class="btn btn-info d-block mb-2">
                            <i class="bi bi-people"></i> Quản lý trưởng CLB
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
<?php } else {
    // Clubs listing
    $sql = "SELECT c.*, COUNT(DISTINCT cm.id) as member_count 
           FROM clubs c 
           LEFT JOIN club_members cm ON c.id = cm.club_id AND cm.status = 'approved'
           GROUP BY c.id";
    $result = $conn->query($sql);
    $clubs = $result->fetch_all(MYSQLI_ASSOC);
    ?>
    
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-people"></i> Danh sách câu lạc bộ</h2>
        </div>
        <?php if (isAdmin()): ?>
        <div class="col-md-4 text-end">
            <a href="index.php?page=admin&action=create_club" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tạo câu lạc bộ mới
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (empty($clubs)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Hiện tại chưa có câu lạc bộ nào. Hãy quay lại sau.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($clubs as $club): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($club['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($club['description'], 0, 100)) . '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary"><i class="bi bi-people"></i> <?php echo $club['member_count']; ?> Thành viên</span>
                            <a href="index.php?page=clubs&id=<?php echo $club['id']; ?>" class="btn btn-primary btn-sm">
                                Chi tiết <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php } ?>