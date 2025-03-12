<?php
// Kiểm tra quyền admin
if (!isAdmin()) {
    flashMessage('Access denied', 'danger');
    redirect('/');
}

/**
 * Lấy dữ liệu tăng trưởng người dùng theo tháng
 * @return array Dữ liệu tăng trưởng người dùng
 */
function getUserGrowthData($conn) {
    $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, 
                   COUNT(*) AS total
            FROM users 
            GROUP BY month 
            ORDER BY month ASC";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Lấy số liệu hoạt động của các câu lạc bộ
 * @return array Số liệu các câu lạc bộ
 */
function getClubMetrics($conn) {
    $sql = "SELECT c.name, 
                   COUNT(DISTINCT cm.id) AS member_count,
                   COUNT(DISTINCT e.id) AS event_count,
                   COUNT(DISTINCT cp.id) AS post_count
            FROM clubs c
            LEFT JOIN club_members cm ON c.id = cm.club_id AND cm.status = 'approved'
            LEFT JOIN events e ON c.id = e.club_id
            LEFT JOIN club_posts cp ON c.id = cp.club_id
            GROUP BY c.id
            ORDER BY member_count DESC";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Lấy tỷ lệ tham gia các sự kiện
 * @return array Dữ liệu tham gia sự kiện
 */
function getEventParticipation($conn) {
    $sql = "SELECT e.title, 
                   c.name AS club_name,
                   COUNT(DISTINCT a.id) AS attendance_count,
                   e.event_date
            FROM events e
            INNER JOIN clubs c ON e.club_id = c.id
            LEFT JOIN attendance a ON e.id = a.event_id AND a.status = 'present'
            WHERE e.event_date <= CURDATE()
            GROUP BY e.id
            ORDER BY e.event_date DESC
            LIMIT 10";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Lấy mức độ tham gia theo vai trò
 * @return array Dữ liệu tham gia theo vai trò
 */
function getRoleEngagement($conn) {
    $sql = "SELECT u.role, 
                   COUNT(DISTINCT cm.id) AS club_memberships,
                   COUNT(DISTINCT a.id) AS event_attendance
            FROM users u
            LEFT JOIN club_members cm ON u.id = cm.user_id AND cm.status = 'approved'
            LEFT JOIN attendance a ON u.id = a.user_id AND a.status = 'present'
            GROUP BY u.role";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Lấy số liệu thống kê nhanh
 * @return array Số liệu tổng quan
 */
function getQuickStats($conn) {
    $sql = "SELECT 
                (SELECT COUNT(*) FROM users) AS total_users,
                (SELECT COUNT(*) FROM clubs) AS total_clubs,
                (SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()) AS upcoming_events,
                (SELECT COUNT(*) FROM club_posts WHERE status = 'approved') AS active_posts";
    return $conn->query($sql)->fetch_assoc();
}

/**
 * Lấy danh sách các câu lạc bộ năng động nhất
 * @return array Top 5 CLB năng động
 */
function getMostActiveClubs($conn) {
    $sql = "SELECT c.name, 
                   COUNT(DISTINCT e.id) + COUNT(DISTINCT cp.id) AS activity_count
            FROM clubs c
            LEFT JOIN events e ON c.id = e.club_id
            LEFT JOIN club_posts cp ON c.id = cp.club_id
            GROUP BY c.id
            ORDER BY activity_count DESC
            LIMIT 5";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Lấy thống kê sự kiện hàng tháng
 * @return array Dữ liệu sự kiện 6 tháng gần nhất
 */
function getMonthlyEventStats($conn) {
    $sql = "SELECT DATE_FORMAT(event_date, '%Y-%m') AS month,
                   COUNT(*) AS event_count,
                   COUNT(DISTINCT club_id) AS participating_clubs
            FROM events
            WHERE event_date <= CURDATE()
            GROUP BY month
            ORDER BY month DESC
            LIMIT 6";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

// Lấy dữ liệu
$user_growth_data = getUserGrowthData($conn);
$club_metrics = getClubMetrics($conn);
$event_participation = getEventParticipation($conn);
$role_engagement = getRoleEngagement($conn);
$quick_stats = getQuickStats($conn);
$active_clubs = getMostActiveClubs($conn);
$monthly_events = getMonthlyEventStats($conn);
?>

<!-- HTML Structure -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-4 text-primary fw-bold">Thống Kê Chi Tiết</h2>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4 g-4">
        <?php 
        $stats = [
            ['title' => 'Tổng Người Dùng', 'value' => $quick_stats['total_users'], 'icon' => 'fa-users', 'color' => 'primary'],
            ['title' => 'Tổng CLB', 'value' => $quick_stats['total_clubs'], 'icon' => 'fa-school', 'color' => 'success'],
            ['title' => 'Sự Kiện Sắp Tới', 'value' => $quick_stats['upcoming_events'], 'icon' => 'fa-calendar', 'color' => 'warning'],
            ['title' => 'Bài Viết Đã Duyệt', 'value' => $quick_stats['active_posts'], 'icon' => 'fa-newspaper', 'color' => 'info']
        ];
        foreach ($stats as $stat): ?>
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= $stat['icon'] ?> fa-2x text-<?= $stat['color'] ?> me-3"></i>
                        <div>
                            <h5 class="card-title text-muted mb-1"><?= $stat['title'] ?></h5>
                            <h2 class="card-text fw-bold text-<?= $stat['color'] ?>"><?= $stat['value'] ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- User Growth Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 fw-semibold">Tăng Trưởng Người Dùng Theo Tháng</h5>
                </div>
                <div class="card-body">
                    <canvas id="userGrowthChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Club Metrics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 fw-semibold">Thống Kê Hoạt Động CLB</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên CLB</th>
                                    <th>Số Thành Viên</th>
                                    <th>Số Sự Kiện</th>
                                    <th>Số Bài Viết</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($club_metrics as $club): ?>
                                <tr>
                                    <td><?= htmlspecialchars($club['name']) ?></td>
                                    <td><?= number_format($club['member_count']) ?></td>
                                    <td><?= number_format($club['event_count']) ?></td>
                                    <td><?= number_format($club['post_count']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Participation and Role Engagement -->
    <div class="row mb-4 g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 fw-semibold">Tỷ Lệ Tham Gia Sự Kiện Gần Đây</h5>
                </div>
                <div class="card-body">
                    <canvas id="eventParticipationChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 fw-semibold">Mức Độ Tham Gia Theo Vai Trò</h5>
                </div>
                <div class="card-body">
                    <canvas id="roleEngagementChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Active Clubs and Monthly Events -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 fw-semibold">CLB Năng Động Nhất</h5>
                </div>
                <div class="card-body">
                    <canvas id="activeClubsChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 fw-semibold">Thống Kê Sự Kiện Theo Tháng</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyEventsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- External Dependencies -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Chart Configurations -->
<script>
'use strict';

// User Growth Chart
const userGrowthData = <?= json_encode($user_growth_data) ?>;
new Chart(document.getElementById('userGrowthChart'), {
    type: 'line',
    data: {
        labels: userGrowthData.map(item => item.month),
        datasets: [{
            label: 'Người Dùng Mới',
            data: userGrowthData.map(item => item.total),
            borderColor: '#4bc0c0',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Số Lượng' } },
            x: { title: { display: true, text: 'Tháng' } }
        }
    }
});

// Event Participation Chart
const eventData = <?= json_encode($event_participation) ?>;
new Chart(document.getElementById('eventParticipationChart'), {
    type: 'bar',
    data: {
        labels: eventData.map(item => item.title),
        datasets: [{
            label: 'Số Người Tham Gia',
            data: eventData.map(item => item.attendance_count),
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: '#36a2eb',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Số Người' } },
            x: { title: { display: true, text: 'Sự Kiện' } }
        }
    }
});

// Role Engagement Chart
const roleData = <?= json_encode($role_engagement) ?>;
new Chart(document.getElementById('roleEngagementChart'), {
    type: 'radar',
    data: {
        labels: roleData.map(item => item.role),
        datasets: [
            {
                label: 'Thành Viên CLB',
                data: roleData.map(item => item.club_memberships),
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: '#ff6384',
                pointBackgroundColor: '#ff6384'
            },
            {
                label: 'Tham Gia Sự Kiện',
                data: roleData.map(item => item.event_attendance),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: '#36a2eb',
                pointBackgroundColor: '#36a2eb'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { r: { beginAtZero: true } }
    }
});

// Most Active Clubs Chart
const activeClubsData = <?= json_encode($active_clubs) ?>;
new Chart(document.getElementById('activeClubsChart'), {
    type: 'doughnut',
    data: {
        labels: activeClubsData.map(item => item.name),
        datasets: [{
            data: activeClubsData.map(item => item.activity_count),
            backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'right' } }
    }
});

// Monthly Events Chart
const monthlyEventsData = <?= json_encode($monthly_events) ?>;
new Chart(document.getElementById('monthlyEventsChart'), {
    type: 'bar',
    data: {
        labels: monthlyEventsData.map(item => item.month),
        datasets: [
            {
                label: 'Số Sự Kiện',
                data: monthlyEventsData.map(item => item.event_count),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: '#36a2eb',
                borderWidth: 1
            },
            {
                label: 'Số CLB Tham Gia',
                data: monthlyEventsData.map(item => item.participating_clubs),
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: '#ff6384',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Số Lượng' } },
            x: { title: { display: true, text: 'Tháng' } }
        }
    }
});
</script>