<?php
function renderHeader($page)
{
    ?>
    <!DOCTYPE html>
    <html lang="en" class="h-100">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo APP_NAME; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            :root {
                --primary-color: #4f46e5;
                --secondary-color: #818cf8;
                --gradient-start: #4f46e5;
                --gradient-end: #818cf8;
            }

            body {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                background-color: #f8f9fa;
                font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            }

            .navbar {
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
                box-shadow: 0 4px 15px rgba(0,0,0,.03);
                padding: 0.8rem 0;
            }

            .navbar-brand {
                font-weight: 600;
                font-size: 1.4rem;
                background: linear-gradient(to right, #fff, rgba(255,255,255,0.8));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .nav-link {
                font-weight: 500;
                padding: 0.8rem 1.2rem !important;
                transition: all 0.4s ease;
                position: relative;
                opacity: 0.9;
            }

            .nav-link:hover {
                opacity: 1;
                box-shadow: 0 0 8px rgba(255, 255, 255, 0.5);
            }

            .nav-link.active::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 20px;
                height: 3px;
                background-color: #fff;
                border-radius: 10px;
            }

            .container.content {
                flex: 1 0 auto;
                padding: 2.5rem 1rem;
            }

            .card {
                border: none;
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0,0,0,.05);
                transition: all 0.4s ease;
                overflow: hidden;
            }

            .card:hover {
                box-shadow: 0 0 20px rgba(129, 140, 248, 0.5);
            }

            .card-header {
                background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(129, 140, 248, 0.1));
                border-bottom: none;
                padding: 1.2rem 1.5rem;
            }

            .footer {
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) !important;
                color: rgba(255,255,255,0.9);
                padding: 1.5rem 0;
                margin-top: auto;
                font-weight: 300;
            }

            .btn {
                border-radius: 50px;
                padding: 0.6rem 1.8rem;
                font-weight: 500;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .btn-primary {
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
                border: none;
            }

            .btn-primary:hover {
                box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            }

            .alert {
                border-radius: 12px;
                border: none;
                padding: 1rem 1.5rem;
                box-shadow: 0 4px 15px rgba(0,0,0,.05);
            }

            .badge {
                padding: 0.5em 1em;
                border-radius: 50px;
                font-weight: 500;
            }

            ::-webkit-scrollbar {
                width: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            ::-webkit-scrollbar-thumb {
                background: var(--secondary-color);
                border-radius: 10px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: var(--primary-color);
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
            <div class="container text-center">
                <span>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</span>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="assets/js/main.js"></script>
    </body>
    </html>
    <?php
}
