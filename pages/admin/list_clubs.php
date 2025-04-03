<?php
if (!isAdmin()) {
    flashMessage('Access denied', 'danger');
    redirect('/index.php');
}

// Get all clubs with member count and event count
$sql = "SELECT c.*, 
       COUNT(DISTINCT cm.id) as member_count,
       COUNT(DISTINCT e.id) as event_count,
       MAX(c.created_at) as created_at
       FROM clubs c 
       LEFT JOIN club_members cm ON c.id = cm.club_id AND cm.status = 'approved'
       LEFT JOIN events e ON c.id = e.club_id
       GROUP BY c.id 
       ORDER BY c.name ASC";
$result = $conn->query($sql);
$clubs = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> Quản lý Câu lạc bộ</h2>
        <a href="index.php?page=admin&action=create_club" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tạo CLB mới
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tên CLB</th>
                            <th>Mô tả</th>
                            <th>Số thành viên</th>
                            <th>Số sự kiện</th>
                            <th>Ngày tạo</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clubs as $club): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($club['name']); ?></td>
                            <td><?php echo substr(htmlspecialchars($club['description']), 0, 100) . '...'; ?></td>
                            <td><?php echo $club['member_count']; ?></td>
                            <td><?php echo $club['event_count']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($club['created_at'])); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $club['status'] === 'active' ? 'success' : 'warning'; ?>">
                                    <?php echo $club['status'] === 'active' ? 'Hoạt động' : 'Tạm dừng'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="index.php?page=admin&action=edit_club&id=<?php echo $club['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>