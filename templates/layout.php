<?php
function renderHeader($page)
{
    ?>
    <!DOCTYPE html>
    <html lang="vi" class="h-100">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo APP_NAME; ?></title>
        <!-- Core CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
        <!-- Additional Libraries -->
        <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
        <style>
            :root {
                --primary-color: #2563eb;
                --secondary-color: #3b82f6;
                --accent-color: #60a5fa;
                --success-color: #34d399;
                --warning-color: #fbbf24;
                --danger-color: #f87171;
                --background-color: #f8fafc;
                --text-color: #1e293b;
                --border-radius: 12px;
                --transition-speed: 0.3s;
            }

            body {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                background-color: var(--background-color);
                font-family: 'Be Vietnam Pro', sans-serif;
                color: var(--text-color);
            }

            .navbar {
                background: rgba(255, 255, 255, 0.9) !important;
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                padding: 1rem 0;
            }

            .navbar-brand {
                font-weight: 700;
                font-size: 1.5rem;
                color: var(--primary-color);
                letter-spacing: -0.5px;
            }

            .nav-link {
                font-weight: 500;
                color: var(--text-color) !important;
                padding: 0.8rem 1.2rem !important;
                border-radius: var(--border-radius);
                transition: all var(--transition-speed) ease;
                margin: 0 3px;
            }

            .nav-link:hover {
                color: var(--primary-color) !important;
                background: rgba(37, 99, 235, 0.1);
                transform: translateY(-1px);
            }

            .nav-link.active {
                color: #fff !important;
                background: var(--primary-color);
            }

            .container.content {
                flex: 1 0 auto;
                padding: 2rem 1rem;
                max-width: 1200px;
                margin: 0 auto;
            }

            .card {
                border: none;
                border-radius: var(--border-radius);
                background: #fff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                transition: all var(--transition-speed) ease;
                overflow: hidden;
            }

            .card:hover {
                box-shadow: 0 10px 20px rgba(37, 99, 235, 0.1);
            }

            .btn {
                border-radius: var(--border-radius);
                padding: 0.7rem 1.5rem;
                font-weight: 500;
                transition: all var(--transition-speed) ease;
            }

            .btn-primary {
                background: var(--primary-color);
                border: none;
            }

            .btn-primary:hover {
                background: var(--secondary-color);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            }

            .alert {
                border: none;
                border-radius: var(--border-radius);
                padding: 1rem 1.5rem;
            }

            .badge {
                padding: 0.5em 1em;
                border-radius: 20px;
                font-weight: 500;
            }

            .footer {
                background: #fff;
                border-top: 1px solid rgba(0, 0, 0, 0.1);
                color: var(--text-color);
                padding: 2rem 0;
                margin-top: auto;
            }

            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 10px;
            }

            ::-webkit-scrollbar-track {
                background: var(--background-color);
            }

            ::-webkit-scrollbar-thumb {
                background: var(--accent-color);
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: var(--primary-color);
            }

            /* Animations */
            .fade-in {
                animation: fadeIn 0.5s ease-in;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php"><?php echo APP_NAME; ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'home' ? 'active' : ''; ?>" href="index.php?page=home"><i class="bi bi-house-door me-1"></i>Trang Chính</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'clubs' ? 'active' : ''; ?>" href="index.php?page=clubs"><i class="bi bi-people me-1"></i>CLB</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'events' ? 'active' : ''; ?>" href="index.php?page=events"><i class="bi bi-calendar-event me-1"></i>Sự Kiện</a>
                        </li>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'admin' ? 'active' : ''; ?>" href="index.php?page=admin">Admin</a>
                        </li>
                        <?php endif; ?>
                        <?php if (isClubLeader()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'club_leader' ? 'active' : ''; ?>" href="index.php?page=club_leader">Quản Lý CLB</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav">
                        <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'profile' ? 'active' : ''; ?>" href="index.php?page=profile">Trang Cá Nhân</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=logout">Đăng Xuất</a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'register' ? 'active' : ''; ?>" href="index.php?page=register">Đăng Ký</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'login' ? 'active' : ''; ?>" href="index.php?page=login">Đăng Nhập</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container content">
            <?php
            // Display flash messages
            $flash = getFlashMessage();
            if ($flash) {
                echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show shadow-sm' role='alert'>";
                echo $flash['message'];
                echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
                echo '</div>';
            }
            ?>
    <?php
}

function renderFooter()
{
    ?>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-md-4 text-center text-md-start">
                        <span class="footer-brand"><?php echo APP_NAME; ?></span>
                        <p class="text-muted mt-2 mb-0">
                            <small>Nền tảng quản lý câu lạc bộ hiện đại</small>
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="d-flex justify-content-center gap-3">
                            <a href="index.php?page=about" class="footer-link">Về chúng tôi</a>
                            <a href="index.php?page=support" class="footer-link">Hỗ trợ</a>
                            <a href="index.php?page=contact" class="footer-link">Liên hệ</a>
                        </div>
                    </div>
                    <div class="col-md-4 text-center text-md-end">
                        <div class="mb-2">
                            <a href="#" class="footer-link"><i class="bi bi-facebook me-2"></i></a>
                            <a href="#" class="footer-link"><i class="bi bi-twitter me-2"></i></a>
                            <a href="#" class="footer-link"><i class="bi bi-instagram"></i></a>
                        </div>
                        <small class="text-muted">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></small>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Core Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <!-- Animation Library -->
        <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
        <script>
            // Initialize AOS
            AOS.init({
                duration: 800,
                once: true
            });

            // Enhanced UI Interactions
            $(document).ready(function() {
                // Add fade-in animation to cards
                $('.card').addClass('fade-in');
                
                // Smooth scroll
                $('a[href*="#"]').on('click', function(e) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: $($(this).attr('href')).offset().top - 100
                    }, 500, 'linear');
                });

                // Navbar scroll effect
                $(window).scroll(function() {
                    if ($(window).scrollTop() > 50) {
                        $('.navbar').addClass('shadow-sm');
                    } else {
                        $('.navbar').removeClass('shadow-sm');
                    }
                });
            });
        </script>
    </body>
    </html>
    <?php
}
