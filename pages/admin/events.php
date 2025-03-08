<?php
// Handle event approval
if ($action === 'approve_event' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = sanitize($_POST['event_id']);
    $status = sanitize($_POST['status']);
    
    if (!in_array($status, ['approved', 'pending', 'rejected'])) {
        flashMessage('Invalid status', 'danger');
    } else {
        $sql = "UPDATE events SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $event_id);
        
        if ($stmt->execute()) {
            flashMessage('Event ' . $status . ' successfully');
        } else {
            flashMessage('Failed to update event status: ' . $conn->error, 'danger');
        }
    }
    redirect('/index.php?page=admin&action=pending_events');
}

// Get pending events
$sql = "SELECT e.*, c.name as club_name 
       FROM events e 
       INNER JOIN clubs c ON e.club_id = c.id 
       WHERE e.status = 'pending'
       ORDER BY e.created_at DESC";
$result = $conn->query($sql);
$pending_events = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <?php if (empty($pending_events)): ?>
                    <p class="text-center text-muted">Không có sự kiện nào đang chờ duyệt</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tên sự kiện</th>
                                    <th>CLB</th>
                                    <th>Ngày diễn ra</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><?php echo htmlspecialchars($event['club_name']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($event['event_date'])); ?></td>
                                    <td>
                                        <form method="POST" action="index.php?page=admin&action=approve_event" class="d-inline">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" name="status" value="approved" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i> Duyệt
                                            </button>
                                            <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-circle"></i> Từ chối
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>