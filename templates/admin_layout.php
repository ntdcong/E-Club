<?php
function renderAdminHeader()
{
    if (!isAdmin()) {
        flashMessage('Bạn không có quyền truy cập trang này', 'danger');
        redirect('/index.php?page=home');
    }
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo APP_NAME; ?> - Quản trị</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
        <link href="<?php echo SITE_URL; ?>/assets/css/post-content.css" rel="stylesheet">
        <style>
            :root {
                --primary-color: #4361ee;
                --secondary-color: #3f37c9;
                --accent-color: #4895ef;
                --success-color: #4cc9f0;
                --light-bg: #f8f9fa;
                --dark-bg: #212529;
            }
            
            body {
                font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
                background-color: var(--light-bg);
            }
            
            .admin-sidebar {
                min-height: 100vh;
                background: linear-gradient(180deg, var(--dark-bg) 0%, #2c3e50 100%);
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
                transition: all 0.3s;
                z-index: 1000;
            }
            
            .admin-sidebar .nav-link {
                color: rgba(255,255,255,.8);
                padding: 1rem 1.2rem;
                border-radius: 8px;
                margin-bottom: 5px;
                transition: all 0.3s;
            }
            
            .admin-sidebar .nav-link:hover {
                color: #fff;
                background: rgba(255,255,255,.1);
                transform: translateX(5px);
            }
            
            .admin-sidebar .nav-link.active {
                background: var(--primary-color);
                color: #fff;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            }
            
            .admin-header {
                background: #fff;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                border-bottom: 1px solid #eee;
            }
            
            .content-wrapper {
                background: var(--light-bg);
                min-height: calc(100vh - 60px);
            }
            
            .admin-logo {
                font-weight: 700;
                font-size: 1.5rem;
                background: linear-gradient(45deg, #fff, rgba(255,255,255,0.7));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                letter-spacing: 1px;
            }
            
            .card {
                border: none;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                transition: transform 0.3s;
            }
            
            .btn-admin {
                border-radius: 8px;
                padding: 0.5rem 1rem;
                font-weight: 500;
                transition: all 0.3s;
            }
            
            .btn-admin:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            
            .user-welcome {
                font-weight: 500;
                background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
        </style>
    </head>
    <body>
        <div class="d-flex">
            <!-- Sidebar -->
            <div class="admin-sidebar p-3" style="width: 280px;">
                <div class="d-flex align-items-center mb-4 ps-2">
                    <i class="bi bi-shield-lock-fill text-white me-2 fs-4"></i>
                    <h5 class="admin-logo mb-0"><?php echo APP_NAME; ?></h5>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link <?php echo !isset($_GET['action']) ? 'active' : ''; ?>" href="index.php?page=admin">
                        <i class="bi bi-speedometer2 me-2"></i> Bảng điều khiển
                    </a>
                    <a class="nav-link <?php echo isset($_GET['action']) && $_GET['action'] === 'manage_users' ? 'active' : ''; ?>" 
                       href="index.php?page=admin&action=manage_users">
                        <i class="bi bi-people-fill me-2"></i> Quản lý người dùng
                    </a>
                    <a class="nav-link <?php echo isset($_GET['action']) && $_GET['action'] === 'pending_events' ? 'active' : ''; ?>" 
                       href="index.php?page=admin&action=pending_events">
                        <i class="bi bi-calendar-check-fill me-2"></i> Duyệt sự kiện
                    </a>
                    <a class="nav-link <?php echo isset($_GET['action']) && $_GET['action'] === 'manage_donations' ? 'active' : ''; ?>" 
                       href="index.php?page=admin/donations">
                        <i class="bi bi-heart-fill me-2"></i> Quản lý đóng góp
                    </a>
                    <a class="nav-link <?php echo isset($_GET['action']) && $_GET['action'] === 'manage_leaders' ? 'active' : ''; ?>" 
                       href="index.php?page=admin&action=manage_leaders">
                        <i class="bi bi-person-badge-fill me-2"></i> Quản lý trưởng CLB
                    </a>        
                    <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'admin/email_broadcast') ? 'active' : ''; ?>" href="index.php?page=admin/email_broadcast">
                        <i class="bi bi-megaphone"></i> Gửi Email Hệ Thống
                    </a>                  
                    <div class="border-top my-4"></div>
                    
                    <a class="nav-link text-warning" href="index.php?page=home">
                        <i class="bi bi-house-door-fill me-2"></i> Về trang chủ
                    </a>
                    <a class="nav-link text-danger" href="index.php?page=logout">
                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-grow-1">
                <!-- Header -->
                <header class="admin-header p-3 d-flex justify-content-between align-items-center sticky-top">
                    <div>
                        <h4 class="m-0 fw-bold text-primary">
                            <?php
                            if (!isset($_GET['action'])) {
                                echo 'Bảng điều khiển';
                            } elseif ($_GET['action'] === 'manage_users') {
                                echo 'Quản lý người dùng';
                            } elseif ($_GET['action'] === 'manage_clubs') {
                                echo 'Quản lý câu lạc bộ';
                            } elseif ($_GET['action'] === 'pending_events') {
                                echo 'Duyệt sự kiện';
                            } elseif ($_GET['action'] === 'manage_donations') {
                                echo 'Quản lý đóng góp';
                            } elseif ($_GET['action'] === 'manage_leaders') {
                                echo 'Quản lý trưởng CLB';
                            } else {
                                echo 'Quản trị hệ thống';
                            }
                            ?>
                        </h4>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="user-welcome me-3">Xin chào, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    </div>
                </header>

                <!-- Content Area -->
                <main class="content-wrapper p-4">
                    <?php
                    if (isset($_SESSION['flash_message'])) {
                        $message = $_SESSION['flash_message'];
                        $type = $_SESSION['flash_type'] ?? 'success';
                        echo "<div class='alert alert-{$type} alert-dismissible fade show shadow-sm' role='alert'>";
                        echo "<i class='bi bi-info-circle me-2'></i>" . $message;
                        echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
                        echo '</div>';
                        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
                    }
                    ?>
    <?php
}

function renderAdminFooter()
{
    ?>
                </main>
                
                <!-- Footer -->
                <footer class="bg-white text-center p-3 border-top">
                    <small class="text-muted">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - Hệ thống quản lý câu lạc bộ</small>
                </footer>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Thêm hiệu ứng cho các card
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.05)';
                });
            });
        });
        </script>
    </body>
    </html>
    <?php
}
