<?php
// Add this line at the top of the file to include the Cloudinary configuration
require_once __DIR__ . '/../../config/cloudinary.php';

// Handle club creation
if ($action === 'create_club' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    
    if (empty($name)) {
        flashMessage('Club name is required', 'danger');
    } else {
        $image_url = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadToCloudinary($_FILES['image'], 'clubs');
            if ($upload_result['success']) {
                $image_url = $upload_result['url'];
            } else {
                flashMessage('Failed to upload image: ' . $upload_result['error'], 'danger');
                redirect('/index.php?page=admin&action=create_club');
            }
        }

        $sql = "INSERT INTO clubs (name, description, image_url) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $description, $image_url);
        
        if ($stmt->execute()) {
            flashMessage('Club created successfully');
            redirect('/index.php?page=clubs');
        } else {
            flashMessage('Failed to create club: ' . $conn->error, 'danger');
        }
    }
}

// Handle club editing
if ($action === 'edit_club') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $club_id = sanitize($_POST['club_id']);
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        
        if (empty($name)) {
            flashMessage('Club name is required', 'danger');
        } else {
            $image_url = null;
            $update_image = false;
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_result = uploadToCloudinary($_FILES['image'], 'clubs');
                if ($upload_result['success']) {
                    $image_url = $upload_result['url'];
                    $update_image = true;
                } else {
                    flashMessage('Failed to upload image: ' . $upload_result['error'], 'danger');
                    redirect('/index.php?page=admin&action=edit_club&id=' . $club_id);
                }
            }
            
            if ($update_image) {
                $sql = "UPDATE clubs SET name = ?, description = ?, image_url = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $name, $description, $image_url, $club_id);
            } else {
                $sql = "UPDATE clubs SET name = ?, description = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $name, $description, $club_id);
            }
            
            if ($stmt->execute()) {
                flashMessage('Club updated successfully');
                redirect('/index.php?page=clubs&id=' . $club_id);
            } else {
                flashMessage('Failed to update club: ' . $conn->error, 'danger');
            }
        }
    }
    
    $club_id = sanitize($_GET['id']);
    $sql = "SELECT * FROM clubs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $club = $stmt->get_result()->fetch_assoc();
    
    if (!$club) {
        flashMessage('Club not found', 'danger');
        redirect('/index.php?page=clubs');
    }
}

// Handle member management
if ($action === 'manage_members') {
    $club_id = sanitize($_GET['club_id']);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $member_id = sanitize($_POST['member_id']);
        $status = sanitize($_POST['status']);
        
        if (!in_array($status, ['approved', 'pending', 'rejected'])) {
            flashMessage('Invalid status', 'danger');
        } else {
            $sql = "UPDATE club_members SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $member_id);
            
            if ($stmt->execute()) {
                flashMessage('Member status updated successfully');
            } else {
                flashMessage('Failed to update member status: ' . $conn->error, 'danger');
            }
        }
    }
    
    // Verify club exists
    $sql = "SELECT id, name FROM clubs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $club = $stmt->get_result()->fetch_assoc();
    
    if (!$club) {
        flashMessage('Club not found', 'danger');
        redirect('/index.php?page=clubs');
    }
    
    $sql = "SELECT cm.*, u.name, u.email 
           FROM club_members cm 
           INNER JOIN users u ON cm.user_id = u.id 
           WHERE cm.club_id = ?
           ORDER BY cm.status, u.name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Display appropriate form based on action
if ($action === 'create_club'): ?>
    <div class="row mb-4">
        <div class="col-12">
            <h2>Tạo CLB mới</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="index.php?page=admin&action=create_club" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên CLB</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh CLB</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Tạo CLB</button>
                        <a href="index.php?page=list_clubs" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($action === 'edit_club' && isset($club)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <h2>Chỉnh sửa CLB</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="index.php?page=admin&action=edit_club" enctype="multipart/form-data">
                        <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên CLB</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($club['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($club['description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Hình ảnh CLB</label>
                            <?php if (!empty($club['image_url'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($club['image_url']); ?>" alt="Club image" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Cập nhật CLB</button>
                        <a href="index.php?page=list_clubs" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>