<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            flashMessage('Chào mừng quay trở lại, ' . $user['name']);
            switch ($user['role']) {
                case 'admin':
                    redirect('/index.php?page=admin');
                    break;
                case 'club_leader':
                    if ($user['role'] === 'club_leader') {
                        redirect('/index.php?page=club_leader');
                    }
                    break;
                default:
                    redirect('/index.php?page=home');
            }
        } else {
            flashMessage('Invalid password', 'danger');
        }
    } else {
        flashMessage('User not found', 'danger');
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Đăng Nhập</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?page=login">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật Khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Đăng Nhập</button>
                    <a href="index.php?page=register" class="btn btn-link">Chưa có tài khoản ? Đăng Ký Ngay</a>
                </form>
            </div>
        </div>
    </div>
</div>