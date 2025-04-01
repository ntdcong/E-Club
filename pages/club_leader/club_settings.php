<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use tttran\viet_qr_generator\Helper;

// Kiểm tra đăng nhập và quyền trưởng CLB
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Lấy danh sách CLB mà người dùng là trưởng CLB
$user_id = $_SESSION['user_id'];
$clubs_query = "SELECT c.* FROM clubs c 
               INNER JOIN club_leaders cl ON c.id = cl.club_id 
               WHERE cl.user_id = ?";
$clubs_stmt = $conn->prepare($clubs_query);
$clubs_stmt->bind_param('i', $user_id);
$clubs_stmt->execute();
$clubs_result = $clubs_stmt->get_result();
$clubs = $clubs_result->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách ngân hàng từ VietQR
$banks_json = file_get_contents(__DIR__ . '/../../vendor/tttran/viet_qr_generator/src/conf/banks.json');
$banks = json_decode($banks_json, true)['data'];
usort($banks, function($a, $b) {
    return strcmp($a['short_name'], $b['short_name']);
});

if (empty($clubs)) {
    header('Location: /pages/home.php');
    exit;
}

// Xử lý cập nhật thông tin tài khoản ngân hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_bank_info') {
    $club_id = (int)$_POST['club_id'];
    $bank_info = [
        'account_number' => $_POST['account_number'],
        'account_name' => $_POST['account_name'],
        'bank_name' => $_POST['bank_name'],
        'bank_bin' => $_POST['bank_bin'],
        'code' => $_POST['code']
    ];
    
    // Kiểm tra quyền cập nhật
    $check_query = "SELECT c.* FROM clubs c 
                   INNER JOIN club_leaders cl ON c.id = cl.club_id 
                   WHERE c.id = ? AND cl.user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param('ii', $club_id, $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        // Cập nhật thông tin tài khoản
        $update_stmt = $conn->prepare("UPDATE clubs SET bank_info = ? WHERE id = ?");
        $bank_info_json = json_encode($bank_info);
        $update_stmt->bind_param('si', $bank_info_json, $club_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = 'Đã cập nhật thông tin tài khoản thành công.';
        } else {
            $_SESSION['error_message'] = 'Có lỗi xảy ra khi cập nhật thông tin tài khoản.';
        }
    } else {
        $_SESSION['error_message'] = 'Bạn không có quyền cập nhật thông tin tài khoản này.';
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

?>

<?php require_once __DIR__ . '/../../templates/admin_layout.php'; ?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Cài đặt CLB</h2>
    
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
    
    <?php foreach ($clubs as $club): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0"><?php echo htmlspecialchars($club['name']); ?></h4>
            </div>
            <div class="card-body">
                <h5 class="mb-3">Thông tin tài khoản ngân hàng</h5>
                <?php 
                $bank_info = json_decode($club['bank_info'], true);
                ?>
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="update_bank_info">
                    <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="account_number" class="form-label">Số tài khoản</label>
                        <input type="text" class="form-control" id="account_number" name="account_number" 
                               value="<?php echo htmlspecialchars($bank_info['account_number'] ?? ''); ?>" required>
                        <div class="invalid-feedback">
                            Vui lòng nhập số tài khoản
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="account_name" class="form-label">Tên chủ tài khoản</label>
                        <input type="text" class="form-control" id="account_name" name="account_name" 
                               value="<?php echo htmlspecialchars($bank_info['account_name'] ?? ''); ?>" required>
                        <div class="invalid-feedback">
                            Vui lòng nhập tên chủ tài khoản
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Tên ngân hàng</label>
                        <select class="form-select" id="bank_name" name="bank_name" required>
                            <option value="">Chọn ngân hàng</option>
                            <?php foreach ($banks as $bank): ?>
                                <option value="<?php echo htmlspecialchars($bank['short_name']); ?>" 
                                        data-bin="<?php echo htmlspecialchars($bank['bin']); ?>"
                                        data-code="<?php echo htmlspecialchars($bank['code']); ?>"
                                        <?php echo (($bank_info['bank_name'] ?? '') === $bank['short_name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($bank['name'] . ' (' . $bank['short_name'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="bank_bin" id="bank_bin" value="<?php echo htmlspecialchars($bank_info['bank_bin'] ?? ''); ?>">
                        <input type="hidden" name="code" id="bank_code" value="<?php echo htmlspecialchars($bank_info['code'] ?? ''); ?>">
                        <div class="invalid-feedback">
                            Vui lòng chọn ngân hàng
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Lưu thay đổi
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Update bank BIN and code when selecting bank
document.querySelectorAll('#bank_name').forEach(function(select) {
    select.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const form = this.closest('form');
        const bankBinInput = form.querySelector('#bank_bin');
        const bankCodeInput = form.querySelector('#bank_code');
        
        bankBinInput.value = selectedOption.dataset.bin || '';
        bankCodeInput.value = selectedOption.dataset.code || '';
    });
});
</script>