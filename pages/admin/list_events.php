<?php
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isAdmin()) {
    flashMessage('Access denied', 'danger');
    redirect('/index.php');
}

// Process search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$club_filter = isset($_GET['club_id']) ? (int)$_GET['club_id'] : 0;
$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';

// Base SQL query
$sql = "SELECT e.*, c.name as club_name, COUNT(a.id) as attendance_count 
       FROM events e 
       INNER JOIN clubs c ON e.club_id = c.id 
       LEFT JOIN attendance a ON e.id = a.event_id AND a.status = 'present'";

// Add WHERE conditions based on filters
$where_conditions = [];
$params = [];
$types = '';

// Combine WHERE conditions if any
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

// Group by and order
$sql .= " GROUP BY e.id ORDER BY e.event_date DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle Excel export
if (isset($_GET['export']) && $_GET['export'] === 'excel') {  
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Tên sự kiện');
    $sheet->setCellValue('C1', 'Câu lạc bộ');
    $sheet->setCellValue('D1', 'Ngày diễn ra');
    $sheet->setCellValue('E1', 'Trạng thái');
    $sheet->setCellValue('F1', 'Số người tham gia');
    
    // Add data
    $row = 2;
    foreach ($events as $event) {
        $sheet->setCellValue('A' . $row, $event['id']);
        $sheet->setCellValue('B' . $row, $event['title']);
        $sheet->setCellValue('C' . $row, $event['club_name']);
        $sheet->setCellValue('D' . $row, date('d/m/Y H:i', strtotime($event['event_date'])));
        $sheet->setCellValue('E' . $row, $event['status'] === 'approved' ? 'Đã duyệt' : 
            ($event['status'] === 'pending' ? 'Chờ duyệt' : 'Từ chối'));
        $sheet->setCellValue('F' . $row, $event['attendance_count']);
        $row++;
    }
    
    // Auto size columns
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    ob_clean(); // Clear output buffer
    
    // Create file and force download
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="danh_sach_su_kien_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    $writer->save('php://output');
    exit;
}
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-event"></i> Quản lý Sự kiện</h2>
    </div>  

    <!-- Fix Excel export button URL -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="?page=admin&action=list_events&export=excel<?php 
                $export_params = $_GET;
                $export_params['export'] = 'excel';
                echo '&' . http_build_query($export_params);
            ?>" class="btn btn-success me-2">
                <i class="bi bi-file-excel"></i> Xuất Excel
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
                            <th>Câu lạc bộ</th>
                            <th>Ngày diễn ra</th>
                            <th>Số người tham gia</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Không có sự kiện nào</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td><?php echo htmlspecialchars($event['club_name']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($event['event_date'])); ?></td>
                            <td><?php echo $event['attendance_count']; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $event['status'] === 'approved' ? 'success' : 
                                        ($event['status'] === 'pending' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php 
                                    echo $event['status'] === 'approved' ? 'Đã duyệt' : 
                                        ($event['status'] === 'pending' ? 'Chờ duyệt' : 'Từ chối'); 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#eventDetailModal"
                                            data-event-id="<?php echo $event['id']; ?>"
                                            data-event-title="<?php echo htmlspecialchars($event['title']); ?>"
                                            data-event-description="<?php echo htmlspecialchars($event['description']); ?>"
                                            data-event-date="<?php echo date('d/m/Y H:i', strtotime($event['event_date'])); ?>"
                                            data-event-club="<?php echo htmlspecialchars($event['club_name']); ?>"
                                            data-event-status="<?php echo $event['status']; ?>"
                                            data-event-attendance="<?php echo $event['attendance_count']; ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if ($event['status'] === 'pending'): ?>
                                    <a href="index.php?page=admin&action=approve_event&id=<?php echo $event['id']; ?>&status=approved" 
                                       class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-check-circle"></i>
                                    </a>
                                    <a href="index.php?page=admin&action=approve_event&id=<?php echo $event['id']; ?>&status=rejected" 
                                       class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Event Detail Modal
document.addEventListener('DOMContentLoaded', function() {
    const eventDetailModal = document.getElementById('eventDetailModal');
    if (eventDetailModal) {
        eventDetailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Extract info from data attributes
            const eventId = button.getAttribute('data-event-id');
            const eventTitle = button.getAttribute('data-event-title');
            const eventDescription = button.getAttribute('data-event-description');
            const eventDate = button.getAttribute('data-event-date');
            const eventClub = button.getAttribute('data-event-club');
            const eventStatus = button.getAttribute('data-event-status');
            const eventAttendance = button.getAttribute('data-event-attendance');
            
            // Update the modal's content
            document.getElementById('modal-event-title').textContent = eventTitle;
            document.getElementById('modal-event-club').textContent = eventClub;
            document.getElementById('modal-event-date').textContent = eventDate;
            document.getElementById('modal-event-attendance').textContent = eventAttendance;
            document.getElementById('modal-event-description').textContent = eventDescription || 'Không có mô tả';
            
            // Set status badge
            const statusBadge = document.getElementById('modal-event-status-badge');
            let badgeClass = '';
            let statusText = '';
            
            switch(eventStatus) {
                case 'approved':
                    badgeClass = 'bg-success';
                    statusText = 'Đã duyệt';
                    break;
                case 'pending':
                    badgeClass = 'bg-warning';
                    statusText = 'Chờ duyệt';
                    break;
                case 'rejected':
                    badgeClass = 'bg-danger';
                    statusText = 'Từ chối';
                    break;
            }
            
            statusBadge.innerHTML = `<span class="badge ${badgeClass} w-100 p-2">${statusText}</span>`;
            
            // Add action buttons for pending events
            const actionsContainer = document.getElementById('modal-event-actions');
            actionsContainer.innerHTML = '';
            
            if (eventStatus === 'pending') {
                actionsContainer.innerHTML = `
                    <a href="index.php?page=admin&action=approve_event&id=${eventId}&status=approved" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>Duyệt sự kiện
                    </a>
                    <a href="index.php?page=admin&action=approve_event&id=${eventId}&status=rejected" class="btn btn-danger">
                        <i class="bi bi-x-circle me-2"></i>Từ chối
                    </a>
                `;
            }
        });
    }
});
</script>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailModalLabel">Chi tiết sự kiện</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 id="modal-event-title"></h4>
                        <p class="text-muted mb-2"><i class="bi bi-people me-2"></i><span id="modal-event-club"></span></p>
                        <p class="text-muted mb-2"><i class="bi bi-calendar me-2"></i><span id="modal-event-date"></span></p>
                        <p class="text-muted mb-2"><i class="bi bi-people-fill me-2"></i>Số người tham gia: <span id="modal-event-attendance"></span></p>
                        <div class="mt-3">
                            <h5>Mô tả:</h5>
                            <p id="modal-event-description"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Trạng thái</h5>
                                <div id="modal-event-status-badge" class="mb-3"></div>
                                
                                <div id="modal-event-actions" class="d-grid gap-2">
                                    <!-- Actions will be added dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
</script>
