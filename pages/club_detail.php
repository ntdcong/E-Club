<?php
// Hiển thị nút "Viết bài" cho trưởng CLB
if (isClubLeader() && isset($_SESSION['user_id']) && isClubLeader($_SESSION['user_id'], $club_id)): ?>
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Quản lý bài viết</h5>
        <a href="index.php?page=club_leader&action=create_post&club_id=<?php echo $club_id; ?>" class="btn btn-primary">
            <i class="fas fa-pen"></i> Viết bài mới
        </a>
        <a href="index.php?page=club_leader&action=manage_posts&club_id=<?php echo $club_id; ?>" class="btn btn-outline-primary">
            <i class="fas fa-list"></i> Quản lý bài viết
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Hiển thị danh sách bài viết đã được duyệt -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Bài viết mới nhất</h5>
    </div>
    <div class="card-body">
        <?php
        $sql = "SELECT p.*, u.name as author_name 
                FROM club_posts p 
                JOIN users u ON p.author_id = u.id 
                WHERE p.club_id = ? AND p.status = 'approved' 
                ORDER BY p.created_at DESC 
                LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $club_id);
        $stmt->execute();
        $posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (empty($posts)): ?>
            <p class="text-muted">Chưa có bài viết nào.</p>
        <?php else: 
            foreach ($posts as $post): ?>
            <div class="post-item mb-3">
                <div class="row">
                    <?php if ($post['thumbnail']): ?>
                    <div class="col-md-3">
                        <img src="<?php echo htmlspecialchars($post['thumbnail']); ?>" class="img-fluid rounded" alt="Thumbnail">
                    </div>
                    <?php endif; ?>
                    <div class="<?php echo $post['thumbnail'] ? 'col-md-9' : 'col-md-12'; ?>">
                        <h5><a href="index.php?page=post&id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h5>
                        <p class="text-muted">
                            <small>
                                Đăng bởi: <?php echo htmlspecialchars($post['author_name']); ?> | 
                                <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                            </small>
                        </p>
                        <p><?php echo substr(strip_tags($post['content']), 0, 200) . '...'; ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach;
        endif; ?>
    </div>
</div>

