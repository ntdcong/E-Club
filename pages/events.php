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
                    <div class="card-header bg-primary text-white p-4">
                        <h2 class="card-title mb-1"><?php echo htmlspecialchars($event['title']); ?></h2>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-people-fill me-2"></i>
                            <span><?php echo htmlspecialchars($event['club_name']); ?></span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="event-calendar text-center">
                                    <div class="calendar-month bg-light text-primary p-2">
                                        <?php 
                                        $months = array(
                                            'January' => 'Tháng 1', 'February' => 'Tháng 2', 'March' => 'Tháng 3',
                                            'April' => 'Tháng 4', 'May' => 'Tháng 5', 'June' => 'Tháng 6',
                                            'July' => 'Tháng 7', 'August' => 'Tháng 8', 'September' => 'Tháng 9',
                                            'October' => 'Tháng 10', 'November' => 'Tháng 11', 'December' => 'Tháng 12'
                                        );
                                        $month = date('F', strtotime($event['event_date']));
                                        echo $months[$month];
                                        ?>
                                    </div>
                                    <div class="calendar-day p-3">
                                        <span class="display-4 fw-bold"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                        <span class="d-block">
                                            <?php 
                                            $days = array(
                                                'Monday' => 'Thứ Hai', 'Tuesday' => 'Thứ Ba', 'Wednesday' => 'Thứ Tư',
                                                'Thursday' => 'Thứ Năm', 'Friday' => 'Thứ Sáu', 'Saturday' => 'Thứ Bảy',
                                                'Sunday' => 'Chủ Nhật'
                                            );
                                            $day = date('l', strtotime($event['event_date']));
                                            echo $days[$day];
                                            ?>
                                        </span>
                                        <span class="d-block text-muted">Năm <?php echo date('Y', strtotime($event['event_date'])); ?></span>
                                    </div>
                                    <div class="calendar-time bg-light text-primary p-2">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date('H:i', strtotime($event['event_date'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="event-stats">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="p-3 bg-light rounded-3">
                                                <div class="text-muted mb-1">Số người tham gia</div>
                                                <div class="h3 mb-0"><?php echo $event['attendance_count']; ?> người</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-3 bg-light rounded-3">
                                                <div class="text-muted mb-1">Trạng thái</div>
                                                <div class="h5 mb-0">
                                                    <?php if (strtotime($event['event_date']) > time()): ?>
                                                        <span class="badge bg-primary">Sắp diễn ra</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Đã kết thúc</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3">Chi tiết sự kiện</h5>
                        <div class="event-description mb-4">
                            <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                        </div>  
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people me-2"></i>Danh sách điểm danh
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($attendees as $attendee): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-500"><?php echo htmlspecialchars($attendee['name']); ?></div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            <?php 
                                            setlocale(LC_TIME, 'vi_VN');
                                            echo date('d/m/Y - H:i', strtotime($attendee['created_at'])); 
                                            ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $attendee['status'] === 'present' ? 'success' : 'danger'; ?>">
                                        <?php echo $attendee['status'] === 'present' ? 'Có mặt' : 'Vắng mặt'; ?>
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

<style>
.event-calendar {
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 10px;
    overflow: hidden;
}

.calendar-month {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.calendar-day {
    background: white;
}

.calendar-day .display-4 {
    line-height: 1;
    color: var(--primary-color);
}

.event-description {
    line-height: 1.7;
    color: #444;
}

.attendance-form {
    border: 1px solid rgba(0,0,0,0.1);
}

.fw-500 {
    font-weight: 500;
}
</style>
    
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
                <div class="card h-100 event-card">
                    <div class="card-body d-flex flex-column">
                        <div class="event-date-badge mb-3">
                            <?php 
                            $event_date = strtotime($event['event_date']);
                            $days = array(
                                'Monday' => 'Thứ Hai', 'Tuesday' => 'Thứ Ba', 'Wednesday' => 'Thứ Tư',
                                'Thursday' => 'Thứ Năm', 'Friday' => 'Thứ Sáu', 'Saturday' => 'Thứ Bảy',
                                'Sunday' => 'Chủ Nhật'
                            );
                            $day = date('l', $event_date);
                            ?>
                            <div class="date-box text-center">
                                <span class="month"><?php echo 'Tháng ' . date('n', $event_date); ?></span>
                                <span class="day"><?php echo date('d', $event_date); ?></span>
                                <span class="weekday"><?php echo $days[$day]; ?></span>
                            </div>
                            <div class="time">
                                <i class="bi bi-clock me-1"></i>
                                <?php echo date('H:i', $event_date); ?>
                            </div>
                        </div>

                        <div class="event-club mb-2">
                            <span class="badge bg-primary-subtle text-primary">
                                <i class="bi bi-people-fill me-1"></i>
                                <?php echo htmlspecialchars($event['club_name']); ?>
                            </span>
                        </div>

                        <h5 class="card-title mb-3"><?php echo htmlspecialchars($event['title']); ?></h5>
                        
                        <p class="card-text text-muted"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?></p>
                        
                        <div class="event-card-footer mt-auto pt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="event-stats">
                                    <small class="text-muted">
                                        <i class="bi bi-person-check me-1"></i>
                                        <?php echo $event['attendance_count']; ?> người tham gia
                                    </small>
                                </div>
                                <a href="index.php?page=events&id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">
                                    Xem chi tiết
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
<style>
.event-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
}

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.event-date-badge {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

.date-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    line-height: 1.2;
}

.date-box .month {
    font-size: 0.8rem;
    color: #666;
    text-transform: uppercase;
}

.date-box .day {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
}

.date-box .weekday {
    font-size: 0.8rem;
    color: #666;
}

.time {
    color: #666;
    font-size: 0.9rem;
}

.event-club {
    margin-top: -5px;
}

.event-club .badge {
    font-weight: 500;
    padding: 0.5em 1em;
}

.event-footer {
    border-top: 1px solid rgba(0,0,0,0.05);
    padding-top: 1rem;
    margin-top: auto;
}

.btn-primary {
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.card-title {
    font-weight: 600;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-text {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php } ?>