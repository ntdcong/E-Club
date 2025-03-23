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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý Bài Viết Của CLB</h2>
        <a href="index.php?page=club_leader/create_post" class="btn btn-primary">
            <i class="fas fa-plus"></i> Viết Bài Viết
        </a>
    </div>

    <!-- Posts List -->
    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php foreach ($posts as $post): ?>
        <div class="col">
            <div class="card h-100 shadow-sm">
                <?php if (isset($post['image_url']) && $post['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" 
                         class="card-img-top" 
                         alt="Post image"
                         style="object-fit: cover; height: 200px;">
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
                <form action="index.php?page=club_leader/posts" method="POST" enctype="multipart/form-data" id="postForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="postModalLabel">Viết Bài Viết</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu Đề</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editor" class="form-label">Nội Dung</label>
                            <div id="editor"></div>
                            <textarea name="content" id="content" style="display: none"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Hình Ảnh</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu Bài Viết</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/35.0.1/classic/ckeditor.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let editor;
    
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
            placeholder: 'Viết nội dung bài viết ở đây...'
        })
        .then(newEditor => {
            editor = newEditor;
        })
        .catch(error => {
            console.error(error);
        });

    // Update hidden textarea before form submission
    document.getElementById('postForm').addEventListener('submit', function() {
        document.getElementById('content').value = editor.getData();
    });
});
</script>