<?php
if (!isAdmin()) {
    flashMessage('Access denied', 'danger');
    redirect('/index.php');
}

// Get all events with club name and attendance count
$sql = "SELECT e.*, c.name as club_name, 
       COUNT(DISTINCT a.id) as attendance_count,
       (SELECT COUNT(*) FROM club_members WHERE club_id = c.id AND status = 'approved') as total_members
       FROM events e 
       INNER JOIN clubs c ON e.club_id = c.id 
       LEFT JOIN attendance a ON e.id = a.event_id AND a.status = 'present'
       GROUP BY e.id 
       ORDER BY e.event_date DESC";
$result = $conn->query($sql);
$events = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-event"></i> Quản lý Sự kiện</h2>
        <div>
            <a href="index.php?page=admin&action=pending_events" class="btn btn-warning me-2">
                <i class="bi bi-clock"></i> Sự kiện chờ duyệt
                <?php
                $pending_count = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'pending'")->fetch_assoc()['count'];
                if ($pending_count > 0): 
                ?>
                <span class="badge bg-danger ms-2"><?php echo $pending_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tên sự kiện</th>
                            <th>CLB tổ chức</th>
                            <th>Thời gian</th>
                            <th>Địa điểm</th>
                            <th>Điểm danh</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($event['title']); ?>
                                <small class="text-muted d-block"><?php echo substr(htmlspecialchars($event['description']), 0, 50) . '...'; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($event['club_name']); ?></td>
                            <td>
                                <?php echo date('d/m/Y H:i', strtotime($event['event_date'])); ?>
                                <small class="text-muted d-block">Thời lượng: <?php echo $event['duration']; ?> phút</small>
                            </td>
                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                            <td>
                                <?php echo $event['attendance_count']; ?>/<?php echo $event['total_members']; ?>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo ($event['total_members'] > 0 ? ($event['attendance_count'] / $event['total_members'] * 100) : 0); ?>%" 
                                         aria-valuenow="<?php echo $event['attendance_count']; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="<?php echo $event['total_members']; ?>"></div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $status_classes = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'cancelled' => 'danger',
                                    'completed' => 'info'
                                ];
                                $status_labels = [
                                    'pending' => 'Chờ duyệt',
                                    'approved' => 'Đã duyệt',
                                    'cancelled' => 'Đã hủy',
                                    'completed' => 'Đã hoàn thành'
                                ];
                                ?>
                                <span class="badge bg-<?php echo $status_classes[$event['status']]; ?>">
                                    <?php echo $status_labels[$event['status']]; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="index.php?page=admin&action=edit_event&id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="index.php?page=admin&action=view_attendance&id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-list-check"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmDelete(<?php echo $event['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
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

<script>
function confirmDelete(eventId) {
    if (confirm('Bạn có chắc chắn muốn xóa sự kiện này không?')) {
        window.location.href = `index.php?page=admin&action=delete_event&id=${eventId}`;
    }
}
</script>