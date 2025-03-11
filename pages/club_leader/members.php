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
        redirect('/index.php?page=club_leader/members');
    }

    switch ($action) {
        case 'approve':
            $sql = "UPDATE club_members SET status = 'approved' WHERE id = ? AND club_id = ?";
            break;
        case 'reject':
            $sql = "DELETE FROM club_members WHERE id = ? AND club_id = ?";
            break;
        case 'remove':
            $sql = "DELETE FROM club_members WHERE id = ? AND club_id = ?";
            $message = "Đã mời thành viên ra khỏi CLB thành công";
            break;
    }

    if (isset($sql)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $member_id, $club_id);
        if ($stmt->execute()) {
            if (isset($message)) {
                flashMessage($message);
            } else {
                flashMessage($action === 'approve' ? 'Đã duyệt thành viên thành công' : 'Đã xóa thành viên thành công');
            }
        } else {
            flashMessage('Không thể xử lý yêu cầu', 'danger');
        }
    }
    redirect('/index.php?page=club_leader/members');
}
?>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="border-bottom pb-3">
                <i class="bi bi-person-badge me-2"></i>Quản lý thành viên CLB
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
                <?php
                $sql = "SELECT 
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_members,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_members
                    FROM club_members WHERE club_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $club['id']);
                $stmt->execute();
                $stats = $stmt->get_result()->fetch_assoc();
                ?>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light h-100">
                            <div class="card-body text-center">
                                <h5 class="text-primary">Thành viên đã duyệt</h5>
                                <h3 class="display-4"><?php echo $stats['approved_members']; ?></h3>
                                <p class="text-muted">Thành viên đang hoạt động</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light h-100">
                            <div class="card-body text-center">
                                <h5 class="text-warning">Thành viên chờ duyệt</h5>
                                <h3 class="display-4"><?php echo $stats['pending_members']; ?></h3>
                                <p class="text-muted">Cần xác nhận</p>
                            </div>
                        </div>
                    </div>
                </div>

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
                            
                            foreach ($members as $member):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td>
                                    <?php if ($member['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Chờ duyệt</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($member['joined_at'])); ?></td>
                                <td>
                                    <?php if ($member['status'] === 'pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle me-1"></i>Duyệt
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn từ chối thành viên này?');">
                                                <i class="bi bi-x-circle me-1"></i>Từ chối
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn mời thành viên này ra khỏi CLB?');">
                                                <i class="bi bi-person-dash me-1"></i>Mời ra khỏi CLB
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>