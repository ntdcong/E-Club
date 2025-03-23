<?php
$sql = "SELECT p.*, c.name as club_name, u.name as author_name 
        FROM club_posts p 
        JOIN clubs c ON p.club_id = c.id 
        JOIN users u ON p.author_id = u.id 
        WHERE p.status = 'pending' 
        ORDER BY p.created_at DESC";
$pending_posts = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Duyệt bài viết</h5>
    </div>
    <div class="card-body">
        <?php if (empty($pending_posts)): ?>
            <p class="text-muted">Không có bài viết nào đang chờ duyệt.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tiêu đề</th>
                            <th>CLB</th>
                            <th>Tác giả</th>
                            <th>Ngày gửi</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_posts as $post): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                            <td><?php echo htmlspecialchars($post['club_name']); ?></td>
                            <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                        onclick="previewPost(<?php echo $post['id']; ?>)">
                                    Xem trước
                                </button>
                                <button type="button" class="btn btn-sm btn-success" 
                                        onclick="approvePost(<?php echo $post['id']; ?>)">
                                    Duyệt
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="rejectPost(<?php echo $post['id']; ?>)">
                                    Từ chối
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal xem trước bài viết -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xem trước bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Nội dung bài viết sẽ được load bằng AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
function previewPost(postId) {
    fetch(`ajax/preview_post.php?id=${postId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('previewContent').innerHTML = `
                <h4>${data.title}</h4>
                ${data.thumbnail ? `<img src="${data.thumbnail}" class="img-fluid mb-3">` : ''}
                ${data.content}
            `;
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        });
}

function approvePost(postId) {
    if (confirm('Bạn có chắc chắn muốn duyệt bài viết này?')) {
        fetch('ajax/approve_post.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({post_id: postId})
        }).then(() => location.reload());
    }
}

function rejectPost(postId) {
    if (confirm('Bạn có chắc chắn muốn từ chối bài viết này?')) {
        fetch('ajax/reject_post.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({post_id: postId})
        }).then(() => location.reload());
    }
}
</script> 