<?php
// Get event details if ID is provided
if (isset($_GET['id'])) {
    $event_id = sanitize($_GET['id']);
    
    // Check if user has access to this event
    $can_view = false;
    if (isAdmin()) {
        $can_view = true;
    } else {
        $sql = "SELECT e.*, c.name as club_name 
               FROM events e 
               INNER JOIN clubs c ON e.club_id = c.id 
               LEFT JOIN club_members cm ON c.id = cm.club_id AND cm.user_id = ? 
               WHERE e.id = ? AND (e.status = 'approved' OR e.created_by = ?) 
               AND (cm.status = 'approved' OR EXISTS (SELECT 1 FROM club_leaders cl WHERE cl.club_id = c.id AND cl.user_id = ?))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $_SESSION['user_id'], $event_id, $_SESSION['user_id'], $_SESSION['user_id']);
        $stmt->execute();
        $can_view = $stmt->get_result()->num_rows > 0;
    }
    
    if (!$can_view) {
        flashMessage('Bạn phải là thành viên của câu lạc bộ này !', 'danger');
        redirect('/index.php?page=events');
    }
    
    $sql = "SELECT e.*, c.name as club_name, COUNT(a.id) as attendance_count 
           FROM events e 
           INNER JOIN clubs c ON e.club_id = c.id 
           LEFT JOIN attendance a ON e.id = a.event_id AND a.status = 'present'
           WHERE e.id = ? 
           GROUP BY e.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute(); // Removed duplicate prepare statement
    $event = $stmt->get_result()->fetch_assoc();
    
    if (!$event) {
        flashMessage('Event not found', 'danger');
        redirect('/index.php?page=events');
    }
    
    // Handle attendance marking
    if (isLoggedIn() && isset($_POST['mark_attendance'])) {
        $status = sanitize($_POST['status']);
        
        // Check if event date has passed
        $event_date = new DateTime($event['event_date']);
        $current_date = new DateTime();
        
        if ($current_date < $event_date && $status === 'present') {
            flashMessage('Cannot mark present for future events', 'warning');
        } else {
            // Check if attendance record exists
            $sql = "SELECT id FROM attendance WHERE event_id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing record
                $sql = "UPDATE attendance SET status = ? WHERE event_id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $status, $event_id, $_SESSION['user_id']);
            } else {
                // Insert new record
                $sql = "INSERT INTO attendance (event_id, user_id, status) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $event_id, $_SESSION['user_id'], $status);
            }
            
            if ($stmt->execute()) {
                flashMessage('Attendance marked successfully');
                redirect('/index.php?page=events&id=' . $event_id);
            } else {
                flashMessage('Failed to mark attendance: ' . $conn->error, 'danger');
            }
        }
    }
    
    // Get user's attendance status
    $user_attendance = null;
    if (isLoggedIn()) {
        $sql = "SELECT status FROM attendance WHERE event_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user_attendance = $result->fetch_assoc()['status'];
        }
    }
    
    // Get attendees list
    $sql = "SELECT u.name, a.status, a.created_at 
           FROM attendance a 
           INNER JOIN users u ON a.user_id = u.id 
           WHERE a.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $attendees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>
    
    <div class="container py-4">
        <div class="row g-4">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h2>
                        <h6 class="card-subtitle mb-2 text-muted"><?php echo __('club_name'); ?>: <?php echo htmlspecialchars($event['club_name']); ?></h6>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        <div class="mb-3">
                            <strong><?php echo __('event_date'); ?>:</strong> <?php echo date('d/m/Y', strtotime($event['event_date'])); ?>
                        </div>
                        <div class="mb-3">
                            <strong><?php echo __('status'); ?>:</strong> <?php echo $event['attendance_count']; ?> <?php echo __('present'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><i class="bi bi-people me-2"></i><?php echo __('attendees'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($attendees as $attendee): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php echo htmlspecialchars($attendee['name']); ?>
                                        <small class="text-muted d-block">
                                            <?php echo date('M j, Y', strtotime($attendee['created_at'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $attendee['status'] === 'present' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($attendee['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
<?php } else {
    // List all upcoming events
    $sql = "SELECT e.*, c.name as club_name, COUNT(a.id) as attendance_count 
           FROM events e 
           INNER JOIN clubs c ON e.club_id = c.id 
           LEFT JOIN attendance a ON e.id = a.event_id AND a.status = 'present'
           LEFT JOIN club_members cm ON c.id = cm.club_id AND cm.user_id = ? 
           LEFT JOIN club_leaders cl ON c.id = cl.club_id AND cl.user_id = ? 
           WHERE e.event_date >= CURDATE() 
           AND (? = 1 OR e.status = 'approved' OR e.created_by = ? OR cm.status = 'approved' OR cl.id IS NOT NULL) 
           GROUP BY e.id 
           ORDER BY e.event_date";
    $stmt = $conn->prepare($sql);
    $is_admin = isAdmin() ? 1 : 0;
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $stmt->bind_param("iiii", $user_id, $user_id, $is_admin, $user_id);
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <h2 class="mb-0"><i class="bi bi-calendar-event me-2"></i><?php echo __('upcoming_events'); ?></h2>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($events as $event): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($event['club_name']); ?></h6>
                        <p class="card-text"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?></p>
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> 
                                <?php echo date('F j, Y g:i A', strtotime($event['event_date'])); ?>
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary"><?php echo $event['attendance_count']; ?> Attending</span>
                            <a href="index.php?page=events&id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php } ?>