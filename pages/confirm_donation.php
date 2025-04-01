<?php
require_once __DIR__ . '/../config.php';

// Check if user is logged in and has pending donation
if (!isset($_SESSION['user_id']) || !isset($_SESSION['pending_donation'])) {
    header('Location: /index.php?page=donate');
    exit;
}

$donation = $_SESSION['pending_donation'];

// Get club name for display
$club_id = $donation['club_id'];
$stmt = $conn->prepare("SELECT name FROM clubs WHERE id = ?");
$stmt->bind_param("i", $club_id);
$stmt->execute();
$result = $stmt->get_result();
$club_name = $result->num_rows > 0 ? $result->fetch_assoc()['name'] : 'CLB';
$stmt->close();

// Insert donation record
$stmt = $conn->prepare("INSERT INTO donations (user_id, club_id, amount, message, transaction_code, status) VALUES (?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param(
    "iidss",
    $donation['user_id'],
    $donation['club_id'],
    $donation['amount'],
    $donation['message'],
    $donation['transaction_code']
);

if ($stmt->execute()) {
    unset($_SESSION['pending_donation']);
} else {
    header('Location: /index.php?page=donate');
    exit;
}
$stmt->close();

// Include layout after processing
require_once __DIR__ . '/../templates/layout.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="confirmation-icon mb-4">
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </div>
                    <h2 class="card-title mb-3">Cảm ơn bạn đã đóng góp!</h2>
                    <p class="card-text mb-4">
                        Chúng tôi đã nhận thông tin ủng hộ <strong><?php echo number_format($donation['amount'], 0, ',', '.'); ?> VNĐ</strong> 
                        cho <strong><?php echo htmlspecialchars($club_name); ?></strong>. 
                        Đóng góp của bạn sẽ được xác nhận sau khi chúng tôi nhận được thanh toán.
                    </p>
                    
                    <div class="card bg-light mb-4">
                        <div class="card-body text-start">
                            <div class="mb-2">
                                <span class="fw-bold">Mã giao dịch:</span> 
                                <?php echo htmlspecialchars($donation['transaction_code']); ?>
                            </div>
                            <?php if (!empty($donation['message'])): ?>
                                <div>
                                    <span class="fw-bold">Lời nhắn:</span> 
                                    "<?php echo htmlspecialchars($donation['message']); ?>"
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <a href="index.php?page=my_donations" class="btn btn-primary">
                        <i class="bi bi-list-ul me-2"></i>Xem lịch sử đóng góp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.confirmation-icon {
    font-size: 4rem;
    color: #28a745;
}
</style>