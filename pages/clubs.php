<?php
// Handle club join requests
if (isLoggedIn() && isset($_POST['join_club'])) {
    // Prevent admin users from joining clubs
    if (isAdmin()) {
        flashMessage('Admin accounts cannot join clubs', 'warning');
        redirect('/index.php?page=admin');
    }
    
    // Prevent club leaders from joining other clubs
    if (isClubLeader()) {
        flashMessage('Trưởng câu lạc bộ không thể tham gia các câu lạc bộ khác', 'warning');
        redirect('/index.php?page=clubs');
    }

    $club_id = sanitize($_POST['club_id']);
    
    // Check if already a member
    $sql = "SELECT id FROM club_members WHERE user_id = ? AND club_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $club_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Set initial status as pending
        $sql = "INSERT INTO club_members (user_id, club_id, status) VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user_id'], $club_id);
        
        if ($stmt->execute()) {
            flashMessage('Yêu cầu tham gia đã được gửi thành công');
        } else {
            flashMessage('Không thể gửi yêu cầu tham gia', 'danger');
        }
    } else {
        flashMessage('Bạn đã gửi yêu cầu tham gia câu lạc bộ này', 'warning');
    }
}

// Handle club view/list logic
if (isset($_GET['id'])) {
    // Single club view
    $club_id = sanitize($_GET['id']);
    $sql = "SELECT c.*, COUNT(DISTINCT cm.id) as member_count, COUNT(DISTINCT e.id) as event_count 
           FROM clubs c 
           LEFT JOIN club_members cm ON c.id = cm.club_id AND cm.status = 'approved'
           LEFT JOIN events e ON c.id = e.club_id
           WHERE c.id = ? 
           GROUP BY c.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $club = $stmt->get_result()->fetch_assoc();
    
    if (!$club) {
        flashMessage('Không tìm thấy câu lạc bộ', 'danger');
        redirect('/index.php?page=clubs');
    }
    
    // Get upcoming events
    $sql = "SELECT * FROM events WHERE club_id = ? AND event_date >= CURDATE() ORDER BY event_date LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $upcoming_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Check if user is a member
    $is_member = false;
    if (isLoggedIn()) {
        $sql = "SELECT status FROM club_members WHERE user_id = ? AND club_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user_id'], $club_id);
        $stmt->execute();
        $member_status = $stmt->get_result()->fetch_assoc();
        $is_member = $member_status ? $member_status['status'] : false;
    }
    ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=clubs" class="text-decoration-none">Câu lạc bộ</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($club['name']); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm rounded-lg border-0 mb-4 overflow-hidden">
                    <?php if (!empty($club['image_url'])): ?>
                    <div class="position-relative">
                        <img src="<?php echo htmlspecialchars($club['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($club['name']); ?>" style="height: 250px; object-fit: cover;">
                        <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                            <h2 class="text-white mb-0 fw-bold"><?php echo htmlspecialchars($club['name']); ?></h2>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card-header bg-primary text-white p-4">
                        <h2 class="card-title mb-0 fw-bold"><?php echo htmlspecialchars($club['name']); ?></h2>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card-body p-4">
                        <div class="club-stats mb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 border rounded-lg text-center bg-light shadow-sm hover-lift">
                                        <i class="bi bi-people fs-2 text-primary"></i>
                                        <h5 class="mt-2 mb-0 fw-bold"><?php echo $club['member_count']; ?></h5>
                                        <small class="text-muted">Thành viên</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded-lg text-center bg-light shadow-sm hover-lift">
                                        <i class="bi bi-calendar-event fs-2 text-info"></i>
                                        <h5 class="mt-2 mb-0 fw-bold"><?php echo $club['event_count']; ?></h5>
                                        <small class="text-muted">Sự kiện</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded-lg text-center bg-light shadow-sm hover-lift">
                                        <i class="bi bi-trophy fs-2 text-warning"></i>
                                        <h5 class="mt-2 mb-0 fw-bold"><?php echo date('Y') - date('Y', strtotime($club['created_at'])); ?></h5>
                                        <small class="text-muted">Năm hoạt động</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="club-info mb-4">
                            <h4 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-info-circle me-2"></i>Giới thiệu</h4>
                            <div class="card-text club-description"><?php echo nl2br(htmlspecialchars($club['description'])); ?></div>
                        </div>

                        <div class="club-actions d-flex justify-content-between align-items-center mt-4">
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <?php if (!$is_member): ?>
                                    <form method="POST">
                                        <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                        <button type="submit" name="join_club" class="btn btn-success btn-lg px-4 rounded-pill">
                                            <i class="bi bi-person-plus me-2"></i> Tham gia ngay
                                        </button>
                                    </form>
                                <?php elseif ($is_member === 'pending'): ?>
                                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                                        <i class="bi bi-clock-history me-2 fs-4"></i>
                                        <div>Yêu cầu tham gia của bạn đang chờ phê duyệt</div>
                                    </div>
                                <?php elseif ($is_member === 'approved'): ?>
                                    <div class="alert alert-success d-flex align-items-center" role="alert">
                                        <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                                        <div>Bạn đã là thành viên của câu lạc bộ này</div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Club Posts Section -->
                <?php
                // Get approved posts for this club
                $sql = "SELECT cp.*, u.name as author_name 
                        FROM club_posts cp 
                        JOIN users u ON cp.created_by = u.id 
                        WHERE cp.club_id = ? AND cp.status = 'approved' 
                        ORDER BY cp.created_at DESC LIMIT 6";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $club_id);
                $stmt->execute();
                $club_posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                ?>

                <?php if (!empty($club_posts)): ?>
                <div class="card shadow-sm rounded-lg border-0 mb-4">
                    <div class="card-header bg-primary text-white p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-newspaper fs-4 me-2"></i>
                            <h3 class="card-title h5 mb-0 fw-bold">Bài viết mới nhất</h3>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <?php foreach ($club_posts as $post): ?>
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm hover-lift">
                                    <?php if ($post['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                         style="height: 200px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">
                                            <a href="index.php?page=post_detail&id=<?php echo $post['id']; ?>" 
                                               class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h5>
                                        <p class="card-text text-muted mb-3">
                                            <?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 120)) . '...'; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-person-circle me-1"></i>
                                                <?php echo htmlspecialchars($post['author_name']); ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-top-0 p-3">
                                        <a href="index.php?page=post_detail&id=<?php echo $post['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm w-100">
                                            Đọc thêm <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($upcoming_events)): ?>
                <div class="card shadow-sm rounded-lg border-0 mb-4">
                    <div class="card-header bg-info text-white p-3 d-flex align-items-center">
                        <i class="bi bi-calendar-week fs-4 me-2"></i>
                        <h3 class="card-title h5 mb-0 fw-bold">Sự kiện sắp tới</h3>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcoming_events as $event): ?>
                        <a href="index.php?page=events&id=<?php echo $event['id']; ?>" class="list-group-item list-group-item-action p-3 border-start-0 border-end-0">
                            <div class="d-flex">
                                <div class="event-date text-center me-3 p-2 rounded bg-light">
                                    <div class="fw-bold text-danger"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                    <div class="small"><?php echo date('m/Y', strtotime($event['event_date'])); ?></div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($event['title']); ?></h5>
                                        <span class="badge bg-info rounded-pill"><i class="bi bi-clock me-1"></i><?php echo date('H:i', strtotime($event['event_date'])); ?></span>
                                    </div>
                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars(substr($event['description'], 0, 120)) . '...'; ?></p>
                                    <div class="mt-2">
                                        <span class="text-primary small">Xem chi tiết <i class="bi bi-arrow-right"></i></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer bg-light p-3 text-center">
                        <a href="index.php?page=events?club_id=<?php echo $club['id']; ?>" class="btn btn-outline-info btn-sm rounded-pill px-4">
                            <i class="bi bi-calendar3 me-1"></i> Xem tất cả sự kiện
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <?php if (isAdmin()): ?>
                <div class="card shadow-sm rounded-lg border-0 mb-4">
                    <div class="card-header bg-primary text-white p-3">
                        <h3 class="card-title h5 mb-0 fw-bold"><i class="bi bi-gear me-2"></i>Quản trị</h3>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            <a href="index.php?page=admin&action=edit_club&id=<?php echo $club['id']; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-pencil-square me-2"></i> Chỉnh sửa thông tin
                            </a>
                            <a href="index.php?page=admin&action=manage_leaders&club_id=<?php echo $club['id']; ?>" class="btn btn-outline-success">
                                <i class="bi bi-people me-2"></i> Quản lý trưởng CLB
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card shadow-sm rounded-lg border-0 mb-4">
                    <div class="card-header bg-light p-3">
                        <h3 class="card-title h5 mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Thông tin liên hệ</h3>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 py-2 d-flex align-items-center border-0">
                                <i class="bi bi-envelope-fill text-primary me-3 fs-5"></i>
                                <span><?php echo htmlspecialchars($club['email'] ?? 'Chưa cập nhật'); ?></span>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex align-items-center border-0">
                                <i class="bi bi-geo-alt-fill text-danger me-3 fs-5"></i>
                                <span><?php echo htmlspecialchars($club['location'] ?? 'Chưa cập nhật'); ?></span>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex align-items-center border-0">
                                <i class="bi bi-calendar-check-fill text-success me-3 fs-5"></i>
                                <span>Thành lập: <?php echo date('d/m/Y', strtotime($club['created_at'])); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card shadow-sm rounded-lg border-0">
                    <div class="card-header bg-light p-3">
                        <h3 class="card-title h5 mb-0 fw-bold"><i class="bi bi-share me-2"></i>Chia sẻ</h3>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="shareClub(<?php echo $club['id']; ?>, '<?php echo addslashes(htmlspecialchars($club['name'])); ?>')">
                                <i class="bi bi-share me-2"></i> Chia sẻ câu lạc bộ này
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm rounded-lg border-0">
                     <div class="card-header bg-light p-3">
                         <h3 class="card-title h5 mb-0 fw-bold"><i class="bi bi-chat-dots me-2"></i>Chat</h3>
                     </div>
                     <div class="card-body p-3">
                         <div class="d-grid gap-2">
                             <a href="index.php?page=club_chat&club_id=<?php echo $club_id; ?>" class="btn btn-outline-primary">
                                 <i class="bi bi-chat-dots me-2"></i> Chat với thành viên
                                 <span class="badge bg-light text-dark ms-2" id="unreadCount">0</span>
                             </a>
                         </div>
                     </div>
                 </div>
            </div>
        </div>
    </div>
    
<?php } else {
    // Clubs listing
    $sql = "SELECT c.*, COUNT(DISTINCT cm.id) as member_count 
           FROM clubs c 
           LEFT JOIN club_members cm ON c.id = cm.club_id AND cm.status = 'approved'
           GROUP BY c.id";
    $result = $conn->query($sql);
    $clubs = $result->fetch_all(MYSQLI_ASSOC);
    ?>
    
    <div class="container py-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold"><i class="bi bi-people me-2"></i>Danh sách câu lạc bộ</h2>
                <p class="text-muted mb-0">Khám phá và tham gia các câu lạc bộ tại trường</p>
            </div>
            <?php if (isAdmin()): ?>
            <div class="col-md-4 text-end">
                <a href="index.php?page=admin&action=create_club" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-circle me-2"></i> Tạo câu lạc bộ mới
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($clubs)): ?>
            <div class="alert alert-info d-flex align-items-center p-4 shadow-sm">
                <i class="bi bi-info-circle-fill fs-3 me-3"></i>
                <div>Hiện tại chưa có câu lạc bộ nào. Hãy quay lại sau.</div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($clubs as $club): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm rounded-lg border-0 hover-lift">
                        <div class="position-relative">
                            <?php if (!empty($club['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($club['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($club['name']); ?>" style="height: 160px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-light text-center py-4" style="height: 160px;">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <?php endif; ?>
                            <span class="position-absolute top-0 end-0 badge bg-primary m-2 rounded-pill px-3 py-2">
                                <i class="bi bi-people me-1"></i> <?php echo $club['member_count']; ?>
                            </span>
                        </div>
                        <div class="card-body p-3">
                            <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($club['name']); ?></h5>
                            <p class="card-text text-muted mb-3" style="height: 4.5rem; overflow: hidden;">
                                <?php echo htmlspecialchars(substr($club['description'], 0, 120)) . '...'; ?>
                            </p>
                            <div class="d-grid">
                                <a href="index.php?page=clubs&id=<?php echo $club['id']; ?>" class="btn btn-outline-primary">
                                    Xem chi tiết <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 text-muted small p-3">
                            <i class="bi bi-calendar-check me-1"></i> Thành lập: <?php echo date('d/m/Y', strtotime($club['created_at'])); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php } ?>

<script>
// Function to share club
function shareClub(clubId, clubName) {
    if (navigator.share) {
        navigator.share({
            title: clubName,
            text: 'Tham gia câu lạc bộ ' + clubName + ' với tôi!',
            url: window.location.origin + '/index.php?page=clubs&id=' + clubId,
        })
        .then(() => console.log('Shared successfully'))
        .catch((error) => console.log('Error sharing:', error));
    } else {
        // Fallback for browsers that don't support Web Share API
        const url = window.location.origin + '/index.php?page=clubs&id=' + clubId;
        const textarea = document.createElement('textarea');
        textarea.value = url;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Đã sao chép đường dẫn: ' + url);
    }
}

// Add hover effect for cards
document.addEventListener('DOMContentLoaded', function() {
    const hoverElements = document.querySelectorAll('.hover-lift');
    hoverElements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
            this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
        });
        el.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
        });
    });
});
</script>

<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.club-description {
    line-height: 1.7;
}
.event-date {
    min-width: 60px;
}
</style>