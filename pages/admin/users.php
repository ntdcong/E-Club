<?php
// Handle user management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = sanitize($_POST['user_id']);
    $role = sanitize($_POST['role']);
    
    if (!in_array($role, ['admin', 'member', 'club_leader'])) {
        flashMessage('Invalid role', 'danger');
    } else {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $role, $user_id);
        
        if ($stmt->execute()) {
            flashMessage('User role updated successfully');
            redirect('/index.php?page=admin&action=manage_users');
        } else {
            flashMessage('Failed to update user role: ' . $conn->error, 'danger');
        }
    }
}

// Get all users
$sql = "SELECT * FROM users ORDER BY role, name";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Quản lý người dùng</h2>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'club_leader' ? 'success' : 'primary'); ?>">
                                        <?php 
                                        switch($user['role']) {
                                            case 'user':
                                                echo 'Người dùng';
                                                break;
                                            case 'club_leader':
                                                echo 'Trưởng CLB';
                                                break;
                                            case 'admin':
                                                echo 'Admin';
                                                break;
                                            default:
                                                echo ucfirst($user['role']);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="update_user" value="1">
                                        <select name="role" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Người dùng</option>
                                            <option value="club_leader" <?php echo $user['role'] === 'club_leader' ? 'selected' : ''; ?>>Trưởng CLB</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>