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
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
        <link href="/assets/css/post-content.css" rel="stylesheet">
        <style>
            :root {
                --primary-color: #6366f1;
                --primary-light: #818cf8;
                --primary-dark: #4f46e5;
                --secondary-color: #ec4899;
                --secondary-light: #f472b6;
                --accent-color: #0ea5e9;
                --success-color: #10b981;
                --warning-color: #f59e0b;
                --danger-color: #ef4444;
                --background-color: #f8fafc;
                --text-color: #1e293b;
                --border-radius: 12px;
                --card-border-radius: 16px;
                --transition-speed: 0.3s;
            }

            body {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                background-color: var(--background-color);
                font-family: 'Nunito', sans-serif;
                color: var(--text-color);
                overflow-x: hidden;
            }

            /* Anime-inspired Navbar */
            .navbar {
                background: rgba(255, 255, 255, 0.9) !important;
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                box-shadow: 0 4px 20px -1px rgba(0, 0, 0, 0.1);
                padding: 0.8rem 0;
                border-radius: 0 0 var(--border-radius) var(--border-radius);
                margin: 0 10px 10px 10px;
                transition: all var(--transition-speed) ease;
            }

            .navbar-brand {
                font-weight: 800;
                font-size: 1.6rem;
                background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
                background-clip: text;
                -webkit-background-clip: text;
                color: transparent;
                position: relative;
                letter-spacing: -0.5px;
            }

            .navbar-brand::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 3px;
                background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
                border-radius: 3px;
                transform: scaleX(0);
                transform-origin: right;
                transition: transform var(--transition-speed) ease;
            }

            .navbar-brand:hover::after {
                transform: scaleX(1);
                transform-origin: left;
            }

            .nav-link {
                font-weight: 600;
                color: var(--text-color) !important;
                padding: 0.8rem 1.2rem !important;
                border-radius: var(--border-radius);
                transition: all var(--transition-speed) ease;
                margin: 0 3px;
                position: relative;
                z-index: 1;
                overflow: hidden;
            }

            .nav-link::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(45deg, var(--primary-light), var(--secondary-light));
                z-index: -1;
                transform: translateY(100%);
                transition: transform var(--transition-speed) ease;
                border-radius: var(--border-radius);
            }

            .nav-link:hover {
                color: white !important;
                transform: translateY(-3px);
                box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            }

            .nav-link:hover::before {
                transform: translateY(0);
            }

            .nav-link.active {
                color: #fff !important;
                background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
                box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
            }

            .nav-link i {
                display: inline-block;
                transition: transform 0.3s ease;
            }

            .nav-link:hover i {
                transform: translateY(-2px);
            }

            .container.content {
                flex: 1 0 auto;
                padding: 2rem 1rem;
                max-width: 1200px;
                margin: 0 auto;
            }

            /* Card styling */
            .card {
                border: none;
                border-radius: var(--card-border-radius);
                background: #fff;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
                transition: all var(--transition-speed) ease;
                overflow: hidden;
                position: relative;
                z-index: 1;
            }

            .card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 4px;
                height: 100%;
                z-index: 2;
                opacity: 0;
                transition: opacity var(--transition-speed) ease;
            }

            .card:hover {
                box-shadow: 30px 20px 30px rgba(215, 99, 241, 0.15);
            }

            .card:hover::before {
                opacity: 1;
            }

            /* Button styling */
            .btn {
                border-radius: var(--border-radius);
                padding: 0.7rem 1.5rem;
                font-weight: 600;
                transition: all var(--transition-speed) ease;
                position: relative;
                overflow: hidden;
                z-index: 1;
            }

            .btn::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.1);
                z-index: -1;
                transform: translateX(-100%) rotate(45deg);
                transition: transform 0.6s ease;
            }

            .btn:hover::before {
                transform: translateX(100%) rotate(45deg);
            }

            /* Notification Styles */
            .notification-content {
                position: absolute;
                top: 50px;
                right: 10px;
                background: white;
                border: 1px solid #ccc;
                padding: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                z-index: 9999;
                display: none;
            }

            .notification-content.active {
                display: block;
            }

            .dropdown-menu {
                border: none;
                border-radius: var(--card-border-radius);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                padding: 0.5rem;
                z-index: 9999;
                /* Tăng z-index để hiển thị trên các phần tử khác */
            }

            /* Cải thiện dropdown thông báo */
            #notificationsDropdown+.dropdown-menu {
                z-index: 10000;
                /* Z-index cao hơn các phần tử khác */
                max-height: 400px;
                overflow-y: auto;
                width: 350px;
                padding: 0;
                box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
                border-radius: var(--card-border-radius);
            }

            #notificationsDropdown+.dropdown-menu .dropdown-item {
                padding: 1rem;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                white-space: normal;
            }

            #notificationsDropdown+.dropdown-menu .dropdown-item:last-child {
                border-bottom: none;
            }

            #notificationsDropdown+.dropdown-menu .dropdown-item.bg-light {
                background-color: rgba(99, 102, 241, 0.08) !important;
            }

            #notificationsDropdown+.dropdown-menu .dropdown-item:hover {
                background-color: rgba(99, 102, 241, 0.05);
            }

            .dropdown-item {
                padding: 0.75rem 1rem;
                border-radius: var(--border-radius);
                transition: all var(--transition-speed) ease;
                margin-bottom: 0.25rem;
            }

            .dropdown-item:last-child {
                margin-bottom: 0;
            }

            .dropdown-item:hover {
                background-color: var(--background-color);
                transform: translateY(-1px);
            }

            .dropdown-item.bg-light {
                background-color: rgba(99, 102, 241, 0.1) !important;
            }

            .badge {
                font-size: 0.65rem;
                padding: 0.35em 0.65em;
                margin-left: -0.5em;
                margin-top: -0.5em;
            }

            .btn-primary {
                background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
                border: none;
            }

            .btn-primary:hover {
                background: linear-gradient(45deg, var(--primary-dark), var(--primary-color));
                transform: translateY(-3px);
                box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
            }

            .btn-secondary {
                background: linear-gradient(45deg, var(--secondary-color), var(--secondary-light));
                border: none;
            }

            .btn-secondary:hover {
                background: linear-gradient(45deg, var(--secondary-color), var(--secondary-light));
                transform: translateY(-3px);
                box-shadow: 0 10px 20px rgba(236, 72, 153, 0.3);
            }

            /* Alert styling */
            .alert {
                border: none;
                border-radius: var(--border-radius);
                padding: 1rem 1.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                position: relative;
                overflow: hidden;
            }

            .alert::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 4px;
                height: 100%;
            }

            .alert-primary::before {
                background: var(--primary-color);
            }

            .alert-success::before {
                background: var(--success-color);
            }

            .alert-warning::before {
                background: var(--warning-color);
            }

            .alert-danger::before {
                background: var(--danger-color);
            }

            /* Badge styling */
            .badge {
                padding: 0.5em 1em;
                border-radius: 20px;
                font-weight: 600;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            /* Form controls */
            .form-control,
            .form-select {
                border-radius: var(--border-radius);
                padding: 0.75rem 1rem;
                border: 1px solid #e2e8f0;
                transition: all var(--transition-speed) ease;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: var(--primary-light);
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            }

            /* Anime-Inspired Footer Styles */
            .footer {
                position: relative;
                background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
                color: #f7fafc;
                padding: 0;
                margin-top: auto;
                border-top: none;
            }

            .footer-wave {
                overflow: hidden;
                position: relative;
                top: -1px;
                width: 100%;
                height: 50px;
            }

            .footer-wave svg {
                position: absolute;
                width: 100%;
                height: 50px;
            }

            .wave-fill {
                fill: #2d3748;
            }

            .footer-content {
                padding: 3rem 0 2rem;
            }

            .footer-brand-container {
                position: relative;
                display: inline-block;
            }

            .footer-brand {
                font-weight: 800;
                font-size: 2rem;
                background: linear-gradient(45deg, var(--primary-light), var(--secondary-light));
                background-clip: text;
                -webkit-background-clip: text;
                color: transparent;
                position: relative;
                letter-spacing: -0.5px;
            }

            .footer-brand-dot {
                position: absolute;
                width: 8px;
                height: 8px;
                background: var(--secondary-light);
                border-radius: 50%;
                right: -12px;
                top: 5px;
                transition: all 0.3s ease;
            }

            .footer-brand-dot.pulse {
                animation: dotPulse 0.6s infinite alternate;
                box-shadow: 0 0 8px var(--secondary-light);
            }

            @keyframes dotPulse {
                0% {
                    transform: scale(1);
                }

                100% {
                    transform: scale(1.5);
                }
            }

            .footer-tagline {
                margin-top: 0.5rem;
                font-size: 1rem;
                font-weight: 400;
                opacity: 0.8;
                max-width: 250px;
            }

            .footer-social {
                display: flex;
                gap: 1rem;
                margin-top: 1.5rem;
            }

            .social-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 38px;
                height: 38px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
                font-size: 1.1rem;
                transition: all 0.3s ease;
                text-decoration: none;
            }

            .social-icon:hover,
            .social-hover {
                transform: translateY(-3px);
                background: linear-gradient(45deg, var(--primary-light), var(--secondary-light));
                box-shadow: 0 5px 15px rgba(244, 114, 182, 0.3);
                color: #fff;
            }

            .footer-heading {
                position: relative;
                font-size: 1.1rem;
                font-weight: 600;
                margin-bottom: 1.25rem;
                padding-bottom: 0.75rem;
                color: #f7fafc;
            }

            .footer-heading:after {
                content: '';
                position: absolute;
                left: 0;
                bottom: 0;
                height: 3px;
                width: 40px;
                background: linear-gradient(90deg, var(--primary-light), var(--secondary-light));
                border-radius: 3px;
            }

            .footer-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .footer-list li {
                margin-bottom: 0.75rem;
            }

            .footer-list a {
                color: rgba(255, 255, 255, 0.75);
                text-decoration: none;
                transition: all 0.3s ease;
                display: inline-block;
                position: relative;
            }

            .footer-list a:hover {
                color: #fff;
                transform: translateX(5px);
            }

            .footer-list a:before {
                content: '›';
                margin-right: 0.5rem;
                color: var(--secondary-light);
                font-weight: bold;
                opacity: 0;
                transform: translateX(-5px);
                display: inline-block;
                transition: all 0.3s ease;
            }

            .footer-list a:hover:before {
                opacity: 1;
                transform: translateX(0);
            }

            .footer-copyright {
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                padding: 1.5rem 0;
                font-size: 0.875rem;
                color: rgba(255, 255, 255, 0.6);
            }

            .footer-links {
                margin-bottom: 1.5rem;
            }

            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 10px;
            }

            ::-webkit-scrollbar-track {
                background: var(--background-color);
            }

            ::-webkit-scrollbar-thumb {
                background: linear-gradient(to bottom, var(--primary-light), var(--secondary-light));
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
            }

            /* Animations */
            .fade-in {
                animation: fadeIn 0.5s ease-in;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes floatUpDown {
                0% {
                    transform: translateY(0);
                }

                50% {
                    transform: translateY(-10px);
                }

                100% {
                    transform: translateY(0);
                }
            }

            /* Tablet & Mobile Adjustments */
            @media (max-width: 991.98px) {
                .navbar {
                    margin: 0 0 10px 0;
                    border-radius: 0;
                }

                .nav-link {
                    margin: 5px 0;
                }
            }

            @media (max-width: 767.98px) {
                .footer-heading {
                    margin-top: 1.5rem;
                }

                .footer-copyright p {
                    text-align: center !important;
                    margin-bottom: 0.5rem;
                }
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
                                <a class="nav-link <?php echo $page === 'admin' ? 'active' : ''; ?>" href="index.php?page=admin"><i class="bi bi-gear me-1"></i>Admin</a>
                            </li>
                        <?php endif; ?>
                        <?php if (isClubLeader()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'club_leader' ? 'active' : ''; ?>" href="index.php?page=club_leader"><i class="bi bi-star me-1"></i>Quản Lý CLB</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav">
                        <?php if (isLoggedIn()): ?>
                            <a class="nav-link position-relative" href="index.php?page=donate">
                                <i class="bi bi-heart me-1"> Đóng Góp</i>
                            </a>
                            <a class="nav-link position-relative" href="index.php?page=notifications">
                                <i class="bi bi-bell me-1">Thông báo</i>
                            </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'profile' ? 'active' : ''; ?>" href="index.php?page=profile"><i class="bi bi-person me-1"></i>Trang Cá Nhân</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=logout"><i class="bi bi-box-arrow-right me-1"></i>Đăng Xuất</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'register' ? 'active' : ''; ?>" href="index.php?page=register"><i class="bi bi-person-plus me-1"></i>Đăng Ký</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'login' ? 'active' : ''; ?>" href="index.php?page=login"><i class="bi bi-box-arrow-in-right me-1"></i>Đăng Nhập</a>
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
                <!-- Wave Divider -->
                <div class="footer-wave">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                        <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="wave-fill"></path>
                    </svg>
                </div>

                <!-- Main Footer Content -->
                <div class="footer-content">
                    <div class="row g-4 align-items-start">
                        <!-- Logo & Description -->
                        <div class="col-md-4 text-center text-md-start">
                            <div class="footer-brand-container">
                                <span class="footer-brand"><?php echo APP_NAME; ?></span>
                                <span class="footer-brand-dot"></span>
                            </div>
                            <p class="footer-tagline">
                                Nền tảng quản lý câu lạc bộ hiện đại
                            </p>
                            <div class="footer-social">
                                <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                                <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="social-icon"><i class="bi bi-discord"></i></a>
                            </div>
                        </div>

                        <!-- Navigation Links -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-6 col-md-4 footer-links">
                                    <h5 class="footer-heading">Điều Hướng</h5>
                                    <ul class="footer-list">
                                        <li><a href="index.php?page=home">Trang Chính</a></li>
                                        <li><a href="index.php?page=clubs">Câu Lạc Bộ</a></li>
                                        <li><a href="index.php?page=events">Sự Kiện</a></li>
                                    </ul>
                                </div>
                                <div class="col-6 col-md-4 footer-links">
                                    <h5 class="footer-heading">Hỗ Trợ</h5>
                                    <ul class="footer-list">
                                        <li><a href="index.php?page=faq">FAQ</a></li>
                                        <li><a href="index.php?page=support">Trợ Giúp</a></li>
                                        <li><a href="index.php?page=contact">Liên Hệ</a></li>
                                    </ul>
                                </div>
                                <div class="col-6 col-md-4 footer-links">
                                    <h5 class="footer-heading">Pháp Lý</h5>
                                    <ul class="footer-list">
                                        <li><a href="index.php?page=privacy">Quyền Riêng Tư</a></li>
                                        <li><a href="index.php?page=terms">Điều Khoản</a></li>
                                        <li><a href="index.php?page=about">Về Chúng Tôi</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copyright Bar -->
                <div class="footer-copyright">
                    <div class="row">
                        <div class="col-md-6 text-center text-md-start">
                            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                        </div>
                        <div class="col-md-6 text-center text-md-end">
                            <p>Made with <i class="bi bi-heart-fill text-danger"></i> Nhóm ABC</p>
                        </div>
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

                // Add icons animation
                $('.nav-link i').parent().hover(
                    function() {
                        $(this).find('i').addClass('animate__animated animate__tada');
                    },
                    function() {
                        $(this).find('i').removeClass('animate__animated animate__tada');
                    }
                );

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

                // Footer animation
                $('.footer-brand-container').hover(function() {
                    $('.footer-brand-dot').toggleClass('pulse');
                });

                // Social icons hover effect
                $('.social-icon').hover(function() {
                    $(this).addClass('social-hover');
                }, function() {
                    $(this).removeClass('social-hover');
                });
            });
        </script>
    </body>

    </html>
<?php
    }
