<?php

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Handle post status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $post_id = $_POST['post_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE club_posts SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $post_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Post status updated successfully!';
    } else {
        $_SESSION['error'] = 'Error updating post status.';
    }
    
    header('Location: index.php?page=admin/posts');
    exit();
}

// Get all posts with club and author information
$query = "SELECT cp.*, c.name as club_name, u.name as author_name 
         FROM club_posts cp 
         JOIN clubs c ON cp.club_id = c.id 
         JOIN users u ON cp.created_by = u.id 
         ORDER BY cp.created_at DESC";
$result = $conn->query($query);
$posts = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid mt-4">
    <h2>Manage Club Posts</h2>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Club</th>
                    <th>Author</th>
                    <th>Created At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td><?php echo htmlspecialchars($post['club_name']); ?></td>
                    <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $post['status'] === 'approved' ? 'success' : ($post['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                            <?php echo ucfirst($post['status']); ?>
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary view-post"
                                data-bs-toggle="modal" data-bs-target="#postModal"
                                data-post-id="<?php echo $post['id']; ?>"
                                data-title="<?php echo htmlspecialchars($post['title']); ?>"
                                data-content="<?php echo htmlspecialchars($post['content']); ?>"
                                data-image="<?php echo htmlspecialchars($post['image_url']); ?>"
                                data-status="<?php echo $post['status']; ?>">
                            View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Post Modal -->
    <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="postModalLabel">View Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="post-image" class="mb-3 text-center"></div>
                    <div id="post-content"></div>
                </div>
                <div class="modal-footer">
                    <form action="index.php?page=admin/posts" method="POST" class="d-inline">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="post_id" id="modal-post-id">
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn btn-success approve-btn">Approve</button>
                    </form>
                    <form action="index.php?page=admin/posts" method="POST" class="d-inline">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="post_id" id="modal-post-id-reject">
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-danger reject-btn">Reject</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-post').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const title = this.dataset.title;
            const content = this.dataset.content;
            const imageUrl = this.dataset.image;
            const status = this.dataset.status;

            document.querySelector('#postModalLabel').textContent = title;
            document.querySelector('#post-content').innerHTML = content;
            document.querySelector('#modal-post-id').value = postId;
            document.querySelector('#modal-post-id-reject').value = postId;

            const imageContainer = document.querySelector('#post-image');
            imageContainer.innerHTML = imageUrl ? 
                `<img src="${imageUrl}" class="img-fluid mb-3" alt="Post image">` : '';

            // Update button visibility based on status
            const approveBtn = document.querySelector('.approve-btn');
            const rejectBtn = document.querySelector('.reject-btn');
            
            if (status === 'approved') {
                approveBtn.style.display = 'none';
                rejectBtn.style.display = 'inline-block';
            } else if (status === 'rejected') {
                approveBtn.style.display = 'inline-block';
                rejectBtn.style.display = 'none';
            } else {
                approveBtn.style.display = 'inline-block';
                rejectBtn.style.display = 'inline-block';
            }
        });
    });
});
</script>