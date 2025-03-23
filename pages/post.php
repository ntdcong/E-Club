<?php
$post_id = sanitize($_GET['id']);

$sql = "SELECT p.*, c.name as club_name, u.name as author_name 
        FROM club_posts p 
        JOIN clubs c ON p.club_id = c.id 
        JOIN users u ON p.author_id = u.id 
        WHERE p.id = ? AND p.status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    flashMessage('Bài viết không tồn tại hoặc chưa được duyệt', 'danger');
    redirect('/index.php');
}
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=club_detail&id=<?php echo $post['club_id']; ?>"><?php echo htmlspecialchars($post['club_name']); ?></a></li>
            <li class="breadcrumb-item active">Bài viết</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-body">
            <h1 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <p class="text-muted">
                Đăng bởi: <?php echo htmlspecialchars($post['author_name']); ?> | 
                <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
            </p>
            
            <?php if ($post['thumbnail']): ?>
            <img src="<?php echo htmlspecialchars($post['thumbnail']); ?>" class="img-fluid rounded mb-4" alt="Thumbnail">
            <?php endif; ?>
            
            <div class="post-content">
                <?php echo $post['content']; ?>
            </div>
            
            <!-- Hiển thị các hình ảnh khác trong bài viết -->
            <?php
            $sql = "SELECT image_path FROM post_images WHERE post_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            if (!empty($images)): ?>
            <div class="post-gallery mt-4">
                <h5>Hình ảnh trong bài viết</h5>
                <div class="row">
                    <?php foreach ($images as $image): ?>
                    <div class="col-md-4 mb-3">
                        <a href="<?php echo htmlspecialchars($image['image_path']); ?>" data-lightbox="post-gallery">
                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" class="img-fluid rounded" alt="Post image">
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link href="assets/css/lightbox.min.css" rel="stylesheet">
<script src="assets/js/lightbox.min.js"></script> 