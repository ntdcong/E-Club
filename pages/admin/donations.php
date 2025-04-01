<?php
require_once __DIR__ . '/../../config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /club_management/');
    exit;
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donation_id']) && isset($_POST['status'])) {
    $donation_id = $_POST['donation_id'];
    $status = $_POST['status'];
    $valid_statuses = ['pending', 'completed', 'failed'];

    if (in_array($status, $valid_statuses)) {
        // Cập nhật trạng thái
        $update_query = "UPDATE donations SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $status, $donation_id);
        
        if ($stmt->execute()) {
            // Nếu cập nhật thành công và trạng thái là completed, gửi thông báo cho người dùng
            if ($status === 'completed') {
                // Lấy thông tin donation
                $get_donation = "SELECT d.*, u.id as user_id FROM donations d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?";
                $stmt = $conn->prepare($get_donation);
                $stmt->bind_param("i", $donation_id);
                $stmt->execute();
                $donation = $stmt->get_result()->fetch_assoc();

                if ($donation['user_id']) {
                    // Tạo thông báo
                    $notification_query = "INSERT INTO notifications (title, message, club_id, sender_id) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($notification_query);
                    $title = "Xác nhận đóng góp";
                    $message = "Khoản đóng góp của bạn đã được xác nhận. Cảm ơn bạn đã ủng hộ!";
                    $stmt->bind_param("ssii", $title, $message, $donation['club_id'], $_SESSION['user_id']);
                    $stmt->execute();
                    
                    // Thêm người nhận thông báo
                    $notification_id = $stmt->insert_id;
                    $recipient_query = "INSERT INTO notification_recipients (notification_id, user_id) VALUES (?, ?)";
                    $stmt = $conn->prepare($recipient_query);
                    $stmt->bind_param("ii", $notification_id, $donation['user_id']);
                    $stmt->execute();
                }
            }
            $_SESSION['success'] = 'Cập nhật trạng thái thành công';
        } else {
            $_SESSION['error'] = 'Không thể cập nhật trạng thái';
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Lấy danh sách các khoản đóng góp
$query = "SELECT d.*, u.name as donor_name, u.email as donor_email 
          FROM donations d 
          LEFT JOIN users u ON d.user_id = u.id 
          ORDER BY d.created_at DESC";
$result = $conn->query($query);
$donations = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid py-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">
            <i class="bi bi-heart-fill text-danger me-2"></i>Quản lý đóng góp
        </h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Người đóng góp</th>
                            <th>Số tiền</th>
                            <th>Lời nhắn</th>
                            <th>Mã giao dịch</th>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td><?php echo $donation['id']; ?></td>
                                <td>
                                    <?php if ($donation['donor_name']): ?>
                                        <?php echo htmlspecialchars($donation['donor_name']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo $donation['donor_email']; ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Ẩn danh</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php echo number_format($donation['amount'], 0, ',', '.'); ?> VNĐ
                                </td>
                                <td>
                                    <?php if ($donation['message']): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                            <?php echo htmlspecialchars($donation['message']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Không có</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?php echo $donation['transaction_code']; ?></code>
                                </td>
                                <td>
                                    <?php
                                    $status_class = [
                                        'pending' => 'warning',
                                        'completed' => 'success',
                                        'failed' => 'danger'
                                    ][$donation['status']];
                                    $status_text = [
                                        'pending' => 'Chờ xử lý',
                                        'completed' => 'Hoàn thành',
                                        'failed' => 'Thất bại'
                                    ][$donation['status']];
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($donation['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Cập nhật
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bi bi-check-circle me-2"></i>Xác nhận đã nhận
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                                    <input type="hidden" name="status" value="pending">
                                                    <button type="submit" class="dropdown-item text-warning">
                                                        <i class="bi bi-clock me-2"></i>Đánh dấu chờ xử lý
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>">
                                                    <input type="hidden" name="status" value="failed">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-x-circle me-2"></i>Đánh dấu thất bại
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($donations)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox h4 d-block"></i>
                                    Chưa có khoản đóng góp nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    white-space: nowrap;
}
</style>