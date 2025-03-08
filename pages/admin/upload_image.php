<?php
require_once __DIR__ . '/../../config/cloudinary.php';

if (!isAdmin()) {
    flashMessage('Access denied', 'danger');
    redirect('/index.php');
}

$upload_result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $image = $_FILES['image'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($image['type'], $allowed_types)) {
        flashMessage('Chỉ cho phép các file JPG, PNG và GIF', 'danger');
    } else {
        // Upload to Cloudinary
        $result = uploadToCloudinary($image);
        $upload_result = $result;
        
        if ($result['success']) {
            flashMessage('Upload ảnh thành công!', 'success');
        } else {
            flashMessage('Lỗi upload ảnh: ' . $result['error'], 'danger');
        }
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Test Upload Ảnh lên Cloudinary</h3>
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="image" class="form-label">Chọn ảnh để upload</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                            <div class="form-text">Định dạng cho phép: JPG, PNG, GIF</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cloud-upload"></i> Upload Ảnh
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if ($upload_result && $upload_result['success']): ?>
            <div class="card mt-4 shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="card-title mb-0">Kết quả Upload</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>Thông tin ảnh đã upload:</h5>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Public ID:</span>
                                <span class="badge bg-primary"><?php echo $upload_result['public_id']; ?></span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL ảnh:</label>
                        <input type="text" class="form-control" value="<?php echo $upload_result['url']; ?>" readonly>
                    </div>
                    
                    <div class="text-center">
                        <img src="<?php echo $upload_result['url']; ?>" alt="Uploaded image" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="mt-3 text-center">
                <a href="index.php?page=admin" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại trang Admin
                </a>
            </div>
        </div>
    </div>
</div>