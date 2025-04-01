<?php
require_once __DIR__ . '/../../config.php';

// Kiểm tra đăng nhập và quyền trưởng CLB
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Lấy danh sách CLB mà người dùng là trưởng CLB
$user_id = $_SESSION['user_id'];
$clubs_query = "SELECT c.* FROM clubs c 
               INNER JOIN club_members cm ON c.id = cm.club_id 
               WHERE cm.user_id = ? AND cm.role = 'leader'";
$clubs_stmt = $conn->prepare($clubs_query);
$clubs_stmt->bind_param('i', $user_id);
$clubs_stmt->execute();
$clubs_result = $clubs_stmt->get_result();
$clubs = $clubs_result->fetch_all(MYSQLI_ASSOC);

if (empty($clubs)) {
    header('Location: /pages/home.php');
    exit;
}

// Xử lý xác nhận khoản đóng góp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm') {
    $donation_id = (int)$_POST['donation_id'];
    
    // Kiểm tra quyền xác nhận
    $check_query = "SELECT d.* FROM donations d 
                   INNER JOIN club_members cm ON d.club_id = cm.club_id 
                   WHERE d.id = ? AND cm.user_id = ? AND cm.role = 'leader'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param('ii', $donation_id, $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        // Cập nhật trạng thái và người xác nhận
        $update_stmt = $conn->prepare("UPDATE donations SET status = 'confirmed', sender_id = ? WHERE id = ?");
        $update_stmt->bind_param('ii', $user_id, $donation_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = 'Đã xác nhận khoản đóng góp thành công.';
        } else {
            $_SESSION['error_message'] = 'Có lỗi xảy ra khi xác nhận khoản đóng góp.';
        }
    } else {
        $_SESSION['error_message'] = 'Bạn không có quyền xác nhận khoản đóng góp này.';
    }
}

// Lấy danh sách đóng góp cho CLB
$club_ids = array_column($clubs, 'id');
$club_ids_str = implode(',', $club_ids);

$donations_query = "SELECT d.*, u.name as donor_name, c.name as club_name, 
                    CASE WHEN d.sender_id IS NOT NULL THEN us.name ELSE NULL END as confirmer_name 
                    FROM donations d 
                    INNER JOIN users u ON d.user_id = u.id 
                    INNER JOIN clubs c ON d.club_id = c.id 
                    LEFT JOIN users us ON d.sender_id = us.id 
                    WHERE d.club_id IN ($club_ids_str) 
                    ORDER BY d.created_at DESC";
$donations = $conn->query($donations_query)->fetch_all(MYSQLI_ASSOC);
?>

<?php require_once __DIR__ . '/../../templates/admin_layout.php'; ?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Quản lý đóng góp</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>CLB</th>
                            <th>Người đóng góp</th>
                            <th>Số tiền</th>
                            <th>Lời nhắn</th>
                            <th>Trạng thái</th>
                            <th>Người xác nhận</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td><?php echo $donation['id']; ?></td>
                                <td><?php echo htmlspecialchars($donation['club_name']); ?></td>
                                <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                <td><?php echo number_format($donation['amount']); ?> VNĐ</td>
                                <td><?php echo htmlspecialchars($donation['message']); ?></td>
                                <td>
                                    <?php
                                    $status_class = [
                                        'pending' => 'warning',
                                        'completed' => 'info',
                                        'confirmed' => 'success',
                                        'failed' => 'danger'
                                    ][$donation['status']];
                                    $status_text = [
                                        'pending' => 'Chờ xử lý',
                                        'completed' => 'Đã chuyển khoản',
                                        'confirmed' => 'Đã xác nhận',
                                        'failed' => 'Thất bại'
                                    ][$donation['status']];
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td><?php echo $donation['confirmer_name'] ? htmlspecialchars($donation['confirmer_name']) : '-'; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($donation['created_at'])); ?></td>
                                <td>
                                    <?php if ($donation['status'] === 'completed'): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xác nhận khoản đóng góp này?');">
                                            <input type="hidden" name="action" value="confirm">
                                            <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i> Xác nhận
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
</div>