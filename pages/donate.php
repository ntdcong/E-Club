<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

// Check trạng thái login
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
use tttran\viet_qr_generator\Generator;

$clubs_query = 'SELECT id, name, bank_info FROM clubs WHERE bank_info IS NOT NULL AND JSON_VALID(bank_info) = 1';
$clubs_result = $conn->query($clubs_query);
$clubs = $clubs_result->fetch_all(MYSQLI_ASSOC);

$selected_club = null;
$bank_info = null;
$qr_code = '';
$amount = '';
$message = '';
$error = '';

if (isset($_GET['club_id']) && !empty($_GET['club_id'])) {
    $club_id = (int) $_GET['club_id'];
    $stmt = $conn->prepare('SELECT id, name, bank_info FROM clubs WHERE id = ? AND bank_info IS NOT NULL');
    $stmt->bind_param('i', $club_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $selected_club = $result->fetch_assoc();
        $bank_info = json_decode($selected_club['bank_info'], true);
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $club_id = isset($_POST['club_id']) ? (int) $_POST['club_id'] : 0;
    $amount = isset($_POST['amount']) ? filter_var(str_replace(['.', ','], '', $_POST['amount']), FILTER_SANITIZE_NUMBER_INT) : 0;
    $message = isset($_POST['message']) ? trim(htmlspecialchars($_POST['message'])) : '';
    if ($club_id <= 0) {
        $error = 'Vui lòng chọn Câu lạc bộ';
    } elseif ($amount <= 0) {
        $error = 'Vui lòng nhập số tiền hợp lệ';
    } else {
        $stmt = $conn->prepare('SELECT id, name, bank_info FROM clubs WHERE id = ? AND bank_info IS NOT NULL');
        $stmt->bind_param('i', $club_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $selected_club = $result->fetch_assoc();
            $bank_info = json_decode($selected_club['bank_info'], true);
            if (!$bank_info || json_last_error() !== JSON_ERROR_NONE) {
                $error = 'Định dạng thông tin tài khoản không hợp lệ';
            } else {
                try {
                    $transaction_code = uniqid('DON_', true);
                    $transfer_content = 'Đóng Góp CLB ' . $selected_club['name'];
                    if (!empty($message)) {
                        $transfer_content .= ': ' . $message;
                    }
                    $transfer_content .= ' - ' . $transaction_code;
                    $gen = Generator::create()
                        ->bankId($bank_info['bank_name'])
                        ->accountNo($bank_info['account_number'])
                        ->amount($amount)
                        ->info($transfer_content)
                        ->returnText(false)
                        ->generate();
                    $result = json_decode($gen);
                    if ($result && isset($result->data)) {
                        $qr_code = $result->data;
                        $_SESSION['pending_donation'] = [
                            'user_id' => $_SESSION['user_id'],
                            'club_id' => $club_id,
                            'amount' => $amount,
                            'message' => $message,
                            'transaction_code' => $transaction_code,
                            'created_at' => date('Y-m-d H:i:s'),
                            'qr_code' => $qr_code
                        ];
                    } else {
                        $error = 'Không thể tạo mã QR. Vui lòng thử lại sau.';
                    }
                } catch (Exception $e) {
                    $error = 'Có lỗi xảy ra khi tạo mã QR: ' . $e->getMessage();
                }
            }
        } else {
            $error = 'Không tìm thấy thông tin của CLB';
        }
        $stmt->close();
    }
}

$formatted_amount = '';
if (!empty($amount) && is_numeric($amount)) {
    $formatted_amount = number_format($amount, 0, ',', '.');
}

require_once __DIR__ . '/../templates/layout.php';
?>

<div class="donate-page">
    <div class="content-wrapper">
        <h1 class="title">Ủng hộ Câu lạc bộ</h1>
        <div class="text-center py-3 border-top">
            <a href="index.php?page=my_donations" class="btn btn-primary">
                <i class="bi bi-clock-fill me-2"></i>Lịch Sử Đóng Góp
            </a>
        </div>
        <?php if (empty($clubs)): ?>
            <div class="empty-state">
                <p>Hiện tại chưa có CLB nào có thông tin tài khoản để nhận đóng góp.</p>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="grid">
                <div class="form-section">
                    <form method="GET" action="index.php" class="club-form">
                        <input type="hidden" name="page" value="donate">
                        <label for="club_select" class="label">Chọn Câu lạc bộ</label>
                        <select class="select" id="club_select" name="club_id" required>
                            <option value="">Chọn CLB</option>
                            <?php foreach ($clubs as $club): ?>
                                <option value="<?php echo $club['id']; ?>" <?php echo (isset($_GET['club_id']) && $_GET['club_id'] == $club['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($club['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button button--select">Chọn</button>
                    </form>

                    <?php if ($selected_club && $bank_info): ?>
                        <div class="bank-details">
                            <h3 class="subtitle">Thông tin tài khoản</h3>
                            <div class="detail-item">
                                <span class="label">Ngân hàng</span>
                                <div class="bank-name">
                                    <?php if (!empty($bank_info['code'])): ?>
                                        <img src="https://api.vietqr.io/img/<?php echo htmlspecialchars($bank_info['code']); ?>.png" alt="<?php echo htmlspecialchars($bank_info['bank_name']); ?>" height="24">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($bank_info['bank_name']); ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <span class="label">Số tài khoản</span>
                                <div class="account-number">
                                    <?php echo htmlspecialchars($bank_info['account_number']); ?>
                                    <button type="button" class="copy-button" onclick="copyToClipboard('<?php echo htmlspecialchars($bank_info['account_number']); ?>')">Sao chép</button>
                                </div>
                            </div>
                            <div class="detail-item">
                                <span class="label">Chủ tài khoản</span>
                                <span><?php echo htmlspecialchars($bank_info['account_name']); ?></span>
                            </div>
                        </div>

                        <form method="POST" class="donate-form">
                            <input type="hidden" name="club_id" value="<?php echo $selected_club['id']; ?>">
                            <label for="amount" class="label">Số tiền ủng hộ (VNĐ)</label>
                            <input type="text" class="input" id="amount" name="amount" value="<?php echo $formatted_amount; ?>" placeholder="Nhập số tiền" required>
                            <label for="message" class="label">Lời nhắn (tùy chọn)</label>
                            <textarea class="textarea" id="message" name="message" placeholder="Nhập lời nhắn"><?php echo $message; ?></textarea>
                            <button type="submit" class="button button--primary">Tạo mã QR</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="qr-section">
                    <?php if ($qr_code): ?>
                        <div class="qr-container">
                            <div class="qr-header">
                                <span class="qr-club">CLB <?php echo htmlspecialchars($selected_club['name']); ?></span>
                                <span class="qr-amount"><?php echo number_format($amount, 0, ',', '.'); ?> VNĐ</span>
                                <?php if (isset($_SESSION['pending_donation'])): ?>
                                <span class="qr-transaction-code">Mã GD: <?php echo $_SESSION['pending_donation']['transaction_code']; ?></span>
                                <?php endif; ?>
                            </div>
                            <img src="<?php echo $qr_code; ?>" alt="QR Code" class="qr-image">
                            <div class="qr-actions">
                                <a href="<?php echo $qr_code; ?>" download="qr_code_<?php echo $selected_club['name']; ?>.png" class="button button--secondary">Tải mã QR</a>
                                <button type="button" class="button button--primary" data-bs-toggle="modal" data-bs-target="#confirmDonationModal">Xác nhận chuyển khoản</button>
                            </div>
                            <p class="qr-info">Quét mã QR bằng ứng dụng ngân hàng để chuyển khoản</p>
                        </div>
                    <?php else: ?>
                        <div class="qr-placeholder">
                            <p>Chọn CLB và nhập số tiền để tạo mã QR</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal xác nhận -->
    <div class="modal fade" id="confirmDonationModal" tabindex="-1" aria-labelledby="confirmDonationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content donation-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDonationModalLabel">Xác nhận đóng góp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="modal-note">Vui lòng đảm bảo bạn đã hoàn tất thanh toán trước khi xác nhận.</p>
                    <div class="modal-details">
                        <div><strong>CLB:</strong> <span id="modalClubName"></span></div>
                        <div><strong>Số tiền:</strong> <span id="modalAmount"></span></div>
                        <div><strong>Lời nhắn:</strong> <span id="modalMessage"></span></div>
                        <div><strong>Mã giao dịch:</strong> <span id="modalTransactionCode"></span></div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmTransferCheck" required>
                        <label class="form-check-label" for="confirmTransferCheck">Tôi xác nhận đã hoàn tất chuyển khoản</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button button--secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="button button--primary" id="confirmDonationBtn" disabled>Xác nhận</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary: #635bff;
        --secondary: #f7f7f9;
        --text: #1a1a1a;
        --text-muted: #666;
        --border: #e5e7eb;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .donate-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: var(--text);
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    .donate-page .content-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
        padding: 2rem;
    }

    .donate-page .title {
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 2rem;
        text-align: center;
        color: var(--text);
    }

    .donate-page .grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .donate-page .form-section,
    .donate-page .qr-section {
        padding: 1rem;
    }

    .donate-page .label {
        display: block;
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: var(--text);
    }

    .donate-page .select,
    .donate-page .input,
    .donate-page .textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 1rem;
        color: var(--text);
        background: white;
        transition: border-color 0.2s;
    }

    .donate-page .select:focus,
    .donate-page .input:focus,
    .donate-page .textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 91, 255, 0.2);
    }

    .donate-page .textarea {
        min-height: 100px;
        resize: vertical;
    }

    .donate-page .button {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .donate-page .button--primary {
        background: var(--primary);
        color: white;
        border: none;
    }

    .donate-page .button--primary:hover {
        background: #5448d8;
    }

    .donate-page .button--secondary {
        background: var(--secondary);
        color: var(--text);
        border: 1px solid var(--border);
    }

    .donate-page .button--secondary:hover {
        background: #ebedf0;
    }

    .donate-page .button--select {
        margin-top: 1rem;
        width: 100%;
    }

    .donate-page .bank-details {
        margin-top: 2rem;
        padding: 1.5rem;
        background: var(--secondary);
        border-radius: 8px;
    }

    .donate-page .subtitle {
        font-size: 1.25rem;
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .donate-page .detail-item {
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .donate-page .detail-item .label {
        font-weight: 500;
        color: var(--text-muted);
    }

    .donate-page .bank-name {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .donate-page .account-number {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .donate-page .copy-button {
        background: none;
        border: none;
        color: var(--primary);
        font-size: 0.875rem;
        cursor: pointer;
    }

    .donate-page .qr-container {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
    }

    .donate-page .qr-header {
        margin-bottom: 1.5rem;
    }

    .donate-page .qr-club {
        display: block;
        font-size: 1.125rem;
        color: var(--text-muted);
    }

    .donate-page .qr-amount {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text);
    }

    .donate-page .qr-transaction-code {
        display: block;
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-top: 5px;
    }

    .donate-page .qr-image {
        max-width: 240px;
        margin: 0 auto 1.5rem;
        border-radius: 8px;
    }

    .donate-page .qr-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .donate-page .qr-info {
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    .donate-page .qr-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 300px;
        background: var(--secondary);
        border-radius: 12px;
        color: var(--text-muted);
        text-align: center;
    }

    .donate-page .error-message {
        background: #fef2f2;
        color: #dc2626;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .donate-page .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
    }

    .donate-page .donation-modal {
        border-radius: 12px;
        border: none;
    }

    .donate-page .donation-modal .modal-header {
        border-bottom: none;
        padding: 1.5rem 1.5rem 0;
    }

    .donate-page .donation-modal .modal-title {
        font-weight: 600;
    }

    .donate-page .donation-modal .modal-body {
        padding: 1.5rem;
    }

    .donate-page .donation-modal .modal-note {
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .donate-page .donation-modal .modal-details {
        margin-bottom: 1.5rem;
    }

    .donate-page .donation-modal .modal-details div {
        margin-bottom: 0.5rem;
    }

    .donate-page .donation-modal .modal-footer {
        border-top: none;
        padding: 0 1.5rem 1.5rem;
        gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .donate-page .grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.getElementById('amount');
        if (amountInput) {
            amountInput.addEventListener('input', function() {
                let value = this.value.replace(/[^0-9]/g, '');
                if (value) {
                    value = parseInt(value, 10).toLocaleString('vi-VN');
                    this.value = value;
                }
            });
        }

        const confirmDonationModal = document.getElementById('confirmDonationModal');
        if (confirmDonationModal) {
            confirmDonationModal.addEventListener('show.bs.modal', function() {
                const clubSelect = document.getElementById('club_select');
                const clubName = clubSelect.options[clubSelect.selectedIndex].text;
                const amount = document.getElementById('amount').value;
                const message = document.getElementById('message').value;
                
                <?php if (isset($_SESSION['pending_donation'])): ?>
                const transactionCode = "<?php echo $_SESSION['pending_donation']['transaction_code']; ?>";
                document.getElementById('modalTransactionCode').textContent = transactionCode;
                <?php endif; ?>

                document.getElementById('modalClubName').textContent = clubName;
                document.getElementById('modalAmount').textContent = amount + ' VNĐ';
                document.getElementById('modalMessage').textContent = message || '(Không có lời nhắn)';

                const checkbox = document.getElementById('confirmTransferCheck');
                const confirmBtn = document.getElementById('confirmDonationBtn');
                checkbox.checked = false;
                confirmBtn.disabled = true;

                checkbox.addEventListener('change', function() {
                    confirmBtn.disabled = !this.checked;
                });
            });
        }

        const confirmDonationBtn = document.getElementById('confirmDonationBtn');
        if (confirmDonationBtn) {
            confirmDonationBtn.addEventListener('click', function() {
                this.disabled = true;
                this.textContent = 'Đang xử lý...';
                setTimeout(() => {
                    window.location.href = 'index.php?page=confirm_donation';
                }, 500); 
            });
        }
        window.copyToClipboard = function(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Đã sao chép!');
            });
        };
    });
</script>