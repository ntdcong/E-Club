<?php
require_once __DIR__ . '/../../config/cloudinary.php';

// Check if user is logged in and is a club leader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'club_leader') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT club_id FROM club_leaders WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$club_id = $result->fetch_assoc()['club_id'];

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $image_url = null;

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $upload_result = uploadToCloudinary($_FILES['image'], 'club_posts');
                if ($upload_result['success']) {
                    $image_url = $upload_result['url'];
                }
            }

            if ($_POST['action'] === 'create') {
                $stmt = $conn->prepare("INSERT INTO club_posts (club_id, title, content, image_url, status, created_at, created_by) 
                                      VALUES (?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP, ?)");
                $stmt->bind_param("isssi", $club_id, $title, $content, $image_url, $user_id);
            }
            else if ($_POST['action'] === 'update') {
                $post_id = $_POST['post_id'];
                
                // Keep existing image if no new image uploaded
                if (!$image_url) {
                    $check_stmt = $conn->prepare("SELECT image_url FROM club_posts WHERE id = ? AND club_id = ?");
                    $check_stmt->bind_param("ii", $post_id, $club_id);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $image_url = $row['image_url'];
                    }
                    $check_stmt->close();
                }

                $stmt = $conn->prepare("UPDATE club_posts SET title = ?, content = ?, image_url = ?, status = 'pending' 
                                      WHERE id = ? AND club_id = ?");
                $stmt->bind_param("sssii", $title, $content, $image_url, $post_id, $club_id);
            }

            if ($stmt->execute()) {
                flashMessage($_POST['action'] === 'create' ? 'Bài viết đã được tạo và đang chờ duyệt' : 'Bài viết đã được cập nhật');
            } else {
                flashMessage('Có lỗi xảy ra: ' . $conn->error, 'danger');
            }
            $stmt->close();
        }
    }
    redirect('/index.php?page=club_leader/posts');
    exit();
}

// Get all posts for the club
$stmt = $conn->prepare("SELECT cp.* FROM club_posts cp 
                       WHERE cp.club_id = ? 
                       ORDER BY cp.created_at DESC");
$stmt->bind_param("i", $club_id);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="container mt-4">
    <h2>Manage Club Posts</h2>
    
    <!-- Create New Post Button -->
    <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#postModal">
        Create New Post
    </button>

    <!-- Posts List -->
    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php foreach ($posts as $post): ?>
        <div class="col">
            <div class="card h-100">
                <?php if (isset($post['image_url']) && $post['image_url']): ?>
                    <div class="card-img-top-wrapper" style="height: 200px; overflow: hidden;">
                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" 
                             class="card-img-top" 
                             alt="Post image"
                             style="object-fit: cover; height: 100%; width: 100%;">
                    </div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                    <p class="card-text flex-grow-1"><?php echo substr(strip_tags($post['content']), 0, 150) . '...'; ?></p>
                    <div class="mt-auto">
                        <p class="card-text">
                            <small class="text-muted">
                                Status: <span class="badge bg-<?php echo $post['status'] === 'approved' ? 'success' : ($post['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </small>
                        </p>
                        <a href="index.php?page=club_leader/edit_post&id=<?php echo $post['id']; ?>" 
                           class="btn btn-sm btn-primary">Edit</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Create Post Modal -->
    <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="index.php?page=club_leader/posts" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="postModalLabel">Create New Post</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#content',
        plugins: 'advlist autolink lists link image charmap preview anchor',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
        height: 300
    });
});
</script>