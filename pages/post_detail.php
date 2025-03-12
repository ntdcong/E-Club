<?php
if (!isset($_GET['id'])) {
    flashMessage('Không tìm thấy bài viết', 'danger');
    redirect('/index.php?page=clubs');
}

$post_id = sanitize($_GET['id']);

// Get post details with club and author information
$sql = "SELECT cp.*, c.name as club_name, c.id as club_id, u.name as author_name 
        FROM club_posts cp 
        JOIN clubs c ON cp.club_id = c.id 
        JOIN users u ON cp.created_by = u.id 
        WHERE cp.id = ? AND cp.status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    flashMessage('Không tìm thấy bài viết hoặc bài viết chưa được duyệt', 'danger');
    redirect('/index.php?page=clubs');
}

// Get more posts from the same club
$sql = "SELECT id, title, image_url, created_at 
        FROM club_posts 
        WHERE club_id = ? AND status = 'approved' AND id != ? 
        ORDER BY created_at DESC LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post['club_id'], $post_id);
$stmt->execute();
$related_posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="index.php?page=clubs&id=<?php echo $post['club_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($post['club_name']); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Bài viết</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <article class="blog-post">
                <h1 class="display-4 mb-3"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="d-flex align-items-center text-muted mb-4">
                    <i class="bi bi-person-circle me-2"></i>
                    <span class="me-3"><?php echo htmlspecialchars($post['author_name']); ?></span>
                    <i class="bi bi-calendar3 me-2"></i>
                    <span><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></span>
                </div>

                <?php if ($post['image_url']): ?>
                <div class="post-featured-image mb-4">
                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" 
                         class="img-fluid rounded" 
                         alt="<?php echo htmlspecialchars($post['title']); ?>">
                </div>
                <?php endif; ?>

                <div class="post-content fs-5">
                    <?php echo $post['content']; ?>
                </div>

                <div class="post-footer mt-5 pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="post-club">
                            <small class="text-muted">Đăng bởi</small><br>
                            <a href="index.php?page=clubs&id=<?php echo $post['club_id']; ?>" class="text-decoration-none">
                                <strong><?php echo htmlspecialchars($post['club_name']); ?></strong>
                            </a>
                        </div>
                        <div class="post-share">
                            <button class="btn btn-outline-primary btn-sm" onclick="sharePost(<?php echo $post['id']; ?>, '<?php echo addslashes(htmlspecialchars($post['title'])); ?>')">
                                <i class="bi bi-share me-1"></i> Chia sẻ
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <div class="col-lg-4">
            <?php if (!empty($related_posts)): ?>
            <div class="card shadow-sm rounded-lg border-0 mb-4">
                <div class="card-header bg-light p-3">
                    <h3 class="card-title h5 mb-0 fw-bold">
                        <i class="bi bi-newspaper me-2"></i>Bài viết liên quan
                    </h3>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($related_posts as $related): ?>
                    <a href="index.php?page=post_detail&id=<?php echo $related['id']; ?>" 
                       class="list-group-item list-group-item-action p-3">
                        <?php if ($related['image_url']): ?>
                        <div class="row g-0">
                            <div class="col-4">
                                <img src="<?php echo htmlspecialchars($related['image_url']); ?>" 
                                     class="img-fluid rounded" 
                                     alt="<?php echo htmlspecialchars($related['title']); ?>">
                            </div>
                            <div class="col-8 ps-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars($related['title']); ?></h6>
                                <small class="text-muted">
                                    <?php echo date('d/m/Y', strtotime($related['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <?php else: ?>
                        <div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($related['title']); ?></h6>
                            <small class="text-muted">
                                <?php echo date('d/m/Y', strtotime($related['created_at'])); ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.blog-post {
    max-width: 800px;
    margin: 0 auto;
}

.post-content {
    line-height: 1.8;
    color: #2c3e50;
}

.post-content p {
    margin-bottom: 1.5rem;
}

.post-content img {
    max-width: 100%;
    height: auto;
    margin: 2rem 0;
    border-radius: 8px;
}

.post-featured-image img {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
}
</style>

<script>
function sharePost(postId, postTitle) {
    if (navigator.share) {
        navigator.share({
            title: postTitle,
            text: 'Đọc bài viết "' + postTitle + '" trên website câu lạc bộ!',
            url: window.location.origin + '/index.php?page=post_detail&id=' + postId,
        })
        .then(() => console.log('Shared successfully'))
        .catch((error) => console.log('Error sharing:', error));
    } else {
        const url = window.location.origin + '/index.php?page=post_detail&id=' + postId;
        const textarea = document.createElement('textarea');
        textarea.value = url;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Đã sao chép đường dẫn: ' + url);
    }
}
</script>