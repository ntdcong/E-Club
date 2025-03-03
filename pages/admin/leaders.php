<?php
// Handle club leader assignment
if ($action === 'assign_leader' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $club_id = sanitize($_POST['club_id']);
    $user_id = sanitize($_POST['user_id']);
    
    // Validate club and user exist before proceeding
    $sql = "SELECT id FROM clubs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        flashMessage('Club not found', 'danger');
        redirect('/index.php?page=admin&action=manage_leaders');
    }
    
    if (empty($user_id)) {
        flashMessage('Please select a user', 'danger');
        redirect('/index.php?page=admin&action=manage_leaders');
    }
    
    $sql = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        flashMessage('User not found', 'danger');
        redirect('/index.php?page=admin&action=manage_leaders');
    }
    
    $conn->begin_transaction();
    try {
        // Update user role to club_leader
        $sql = "UPDATE users SET role = 'club_leader' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Remove existing leader if any and reset their role
        $sql = "SELECT user_id FROM club_leaders WHERE club_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $club_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $old_leader = $result->fetch_assoc();
            
            // Check if this user is not a leader for any other clubs
            $sql = "SELECT COUNT(*) as count FROM club_leaders 
                   WHERE user_id = ? AND club_id != ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $old_leader['user_id'], $club_id);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['count'];
            
            if ($count == 0) {
                // Reset role only if user is not a leader for any other club
                $sql = "UPDATE users SET role = 'member' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $old_leader['user_id']);
                $stmt->execute();
            }
        }
        
        // Delete existing club leader
        $sql = "DELETE FROM club_leaders WHERE club_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $club_id);
        $stmt->execute();
        
        // Insert new club leader
        $sql = "INSERT INTO club_leaders (user_id, club_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $club_id);
        $stmt->execute();
        
        $conn->commit();
        flashMessage('Club leader assigned successfully');
    } catch (Exception $e) {
        $conn->rollback();
        flashMessage('Failed to assign club leader: ' . $e->getMessage(), 'danger');
    }
    
    redirect('/index.php?page=admin&action=manage_leaders');
}

// Get all clubs and their leaders
$sql = "SELECT c.*, CONCAT(u.name, ' (', u.email, ')') as leader_name 
       FROM clubs c 
       LEFT JOIN club_leaders cl ON c.id = cl.club_id 
       LEFT JOIN users u ON cl.user_id = u.id 
       ORDER BY c.name";
$result = $conn->query($sql);
$clubs = $result->fetch_all(MYSQLI_ASSOC);

// Get all users who can be leaders (excluding admins)
$sql = "SELECT id, name, email FROM users WHERE role != 'admin' ORDER BY name";
$result = $conn->query($sql);
$available_users = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2>Quản lý Trưởng CLB</h2>
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
                                <th>Tên CLB</th>
                                <th>Trưởng CLB hiện tại</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clubs as $club): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($club['name']); ?></td>
                                <td>
                                    <?php if ($club['leader_name']): ?>
                                        <span class="badge bg-success"><?php echo htmlspecialchars($club['leader_name']); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Chưa có trưởng CLB</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="index.php?page=admin&action=assign_leader" class="d-flex gap-2">
                                        <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                        <select name="user_id" class="form-select form-select-sm" style="width: auto;">
                                            <option value="">Chọn trưởng CLB mới</option>
                                            <?php foreach ($available_users as $user): ?>
                                                <option value="<?php echo $user['id']; ?>">
                                                    <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Phân công</button>
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