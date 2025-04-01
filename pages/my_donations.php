<?php
require_once __DIR__ . '/../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: /club_management/index.php?page=login');
    exit;
}

// Lấy lịch sử đóng góp
$sql = "SELECT d.*, c.name AS club_name 
        FROM donations d 
        LEFT JOIN clubs c ON d.club_id = c.id 
        WHERE d.user_id = ? 
        ORDER BY d.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$donations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-0">
                <i class="bi bi-heart-fill text-danger me-2"></i>
                Lịch sử đóng góp
            </h2>
        </div>
    </div>

    <?php if (empty($donations)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                <h4>Chưa có khoản đóng góp nào</h4>
                <p class="text-muted mb-4">
                    Bạn chưa thực hiện đóng góp cho câu lạc bộ nào.
                </p>
                <a href="index.php?page=clubs" class="btn btn-primary">
                    <i class="bi bi-people-fill me-2"></i>
                    Xem danh sách CLB
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="fw-semibold">Ngày</th>
                                <th class="fw-semibold">CLB</th>
                                <th class="fw-semibold text-end">Số tiền</th>
                                <th class="fw-semibold">Lời nhắn</th>
                                <th class="fw-semibold">Mã giao dịch</th>
                                <th class="fw-semibold">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <?php echo date('d/m/Y H:i', strtotime($donation['created_at'])); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($donation['club_name']); ?>
                                    </td>
                                    <td class="text-end">
                                        <?php echo number_format($donation['amount'], 0, ',', '.') . ' VNĐ'; ?>
                                    </td>
                                    <td>
                                        <?php if ($donation['message']): ?>
                                            <span class="d-inline-block text-truncate" style="max-width: 200px;">
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
                                        $status_config = [
                                            'pending' => ['class' => 'warning', 'text' => 'Chờ xử lý'],
                                            'completed' => ['class' => 'success', 'text' => 'Hoàn thành'],
                                            'failed' => ['class' => 'danger', 'text' => 'Thất bại']
                                        ];
                                        $status = $status_config[$donation['status']];
                                        ?>
                                        <span class="badge bg-<?php echo $status['class']; ?>">
                                            <?php echo $status['text']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.table th {
    font-weight: 600;
    white-space: nowrap;
    padding: 12px 16px;
}
.table td {
    vertical-align: middle;
    padding: 12px 16px;
}
.card {
    border: none;
    border-radius: 8px;
}
</style>