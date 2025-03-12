<?php
require_once __DIR__ . '/../../config/cloudinary.php';

// Check if user is logged in and is a club leader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'club_leader') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get club_id for the current user
$stmt = $conn->prepare("SELECT club_id FROM club_leaders WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$club_id = $result->fetch_assoc()['club_id'];
$stmt->close();

// Get post details
$stmt = $conn->prepare("SELECT * FROM club_posts WHERE id = ? AND club_id = ?");
$stmt->bind_param("ii", $post_id, $club_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    flashMessage('Bài viết không tồn tại hoặc bạn không có quyền chỉnh sửa', 'danger');
    redirect('/index.php?page=club_leader/posts');
    exit();
}

// Handle post update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_url = $post['image_url']; // Keep existing image by default

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_result = uploadToCloudinary($_FILES['image'], 'club_posts');
        if ($upload_result['success']) {
            $image_url = $upload_result['url'];
        }
    }

    $stmt = $conn->prepare("UPDATE club_posts SET title = ?, content = ?, image_url = ?, status = 'pending' 
                           WHERE id = ? AND club_id = ?");
    $stmt->bind_param("sssii", $title, $content, $image_url, $post_id, $club_id);

    if ($stmt->execute()) {
        flashMessage('Bài viết đã được cập nhật và đang chờ duyệt');
        redirect('/index.php?page=club_leader/posts');
        exit();
    } else {
        flashMessage('Có lỗi xảy ra: ' . $conn->error, 'danger');
    }
    $stmt->close();
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Post</h2>
        <a href="index.php?page=club_leader/posts" class="btn btn-secondary">Back to Posts</a>
    </div>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="image" class="form-label">Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
            <?php if ($post['image_url']): ?>
                <div class="mt-2">
                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Current image" style="max-height: 200px;" class="img-thumbnail">
                    <p class="mt-1 mb-0"><small>Current image (upload new to replace)</small></p>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Update Post</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#content',
        plugins: 'advlist autolink lists link image charmap preview anchor',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
        height: 400
    });
});
</script>