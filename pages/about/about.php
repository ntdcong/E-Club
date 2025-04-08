<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../templates/layout.php';
?>
<div class="about-page">
    <!-- Hero Section with Parallax -->
    <div class="hero-section">
        <div class="parallax-container">
            <div class="container">
                <div class="hero-content text-center">
                    <h1 class="display-3 fw-bold text-white mb-3">Về Chúng Tôi</h1>
                    <div class="divider-custom mx-auto mb-4">
                        <div class="divider-custom-line"></div>
                        <div class="divider-custom-icon"><i class="bi bi-stars"></i></div>
                        <div class="divider-custom-line"></div>
                    </div>
                    <p class="lead text-white">Nền tảng kết nối và phát triển cộng đồng câu lạc bộ</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <!-- Mission Section with Animation -->
        <div class="row align-items-center mb-5 mission-section">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="position-relative mission-image">
                    <div class="image-shape shape-1"></div>
                    <div class="image-shape shape-2"></div>
                    <div class="image-container">
                        <img src="<?php echo SITE_URL; ?>/assets/images/mission.jpg" alt="Sứ mệnh" class="img-fluid rounded-lg shadow-lg">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="content-wrapper">
                    <span class="badge bg-primary mb-2">Sứ Mệnh</span>
                    <h2 class="h2 fw-bold mb-3">Sứ Mệnh Của Chúng Tôi</h2>
                    <p class="lead mb-4">Hệ thống quản lý câu lạc bộ của chúng tôi được thiết kế để tạo ra một môi trường năng động và hiệu quả cho việc tổ chức và tham gia các hoạt động câu lạc bộ.</p>
                    <p>Chúng tôi cam kết mang đến trải nghiệm người dùng tốt nhất, giúp các câu lạc bộ vận hành hiệu quả và thành viên dễ dàng tham gia các hoạt động phù hợp với sở thích của mình.</p>
                    <div class="feature-list mt-4">
                        <div class="feature-item">
                            <i class="bi bi-check-circle-fill text-primary"></i>
                            <span>Quản lý hiệu quả</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-check-circle-fill text-primary"></i>
                            <span>Trải nghiệm người dùng tối ưu</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-check-circle-fill text-primary"></i>
                            <span>Kết nối cộng đồng</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vision Section with Animated Counter -->
        <div class="row align-items-center mb-5 vision-section">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                <div class="position-relative vision-image">
                    <div class="image-shape shape-3"></div>
                    <div class="image-shape shape-4"></div>
                    <div class="image-container">
                        <img src="<?php echo SITE_URL; ?>/assets/images/vision.png" alt="Tầm nhìn" class="img-fluid rounded-lg shadow-lg">
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="content-wrapper">
                    <span class="badge bg-success mb-2">Tầm Nhìn</span>
                    <h2 class="h2 fw-bold mb-3">Tầm Nhìn</h2>
                    <p class="lead mb-4">Chúng tôi hướng đến việc trở thành nền tảng hàng đầu trong việc kết nối và phát triển cộng cộng đồng câu lạc bộ, tạo điều kiện cho các thành viên phát triển kỹ năng và đam mê của mình.</p>
                    <p>Với công nghệ hiện đại và giao diện thân thiện, chúng tôi mong muốn xây dựng một hệ sinh thái toàn diện cho các câu lạc bộ phát triển bền vững.</p>

                    <div class="stats-container mt-4">
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="stat-card">
                                    <div class="stat-number"><span class="counter" data-target="50">0</span>+</div>
                                    <div class="stat-label">Câu lạc bộ</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card">
                                    <div class="stat-number"><span class="counter" data-target="1000">0</span>+</div>
                                    <div class="stat-label">Thành viên</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card">
                                    <div class="stat-number"><span class="counter" data-target="200">0</span>+</div>
                                    <div class="stat-label">Sự kiện</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Core Values Section with 3D Cards -->
        <div class="core-values-section py-5 mb-5">
            <div class="text-center mb-5">
                <span class="badge bg-info mb-2">Giá Trị</span>
                <h2 class="h2 fw-bold">Giá Trị Cốt Lõi</h2>
                <p class="lead mx-auto" style="max-width: 700px;">Những giá trị định hướng mọi hoạt động của chúng tôi</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="core-value-card">
                        <div class="card-inner">
                            <div class="card-front">
                                <div class="icon-circle bg-primary text-white mb-4 mx-auto">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <h3 class="h4 fw-bold">Cộng Đồng</h3>
                            </div>
                            <div class="card-back">
                                <p>Xây dựng môi trường gắn kết và hỗ trợ lẫn nhau, tạo điều kiện cho mọi thành viên phát triển.</p>
                                <a href="#" class="btn btn-sm btn-light mt-2">Tìm hiểu thêm</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="core-value-card">
                        <div class="card-inner">
                            <div class="card-front">
                                <div class="icon-circle bg-primary text-white mb-4 mx-auto">
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <h3 class="h4 fw-bold">Chất Lượng</h3>
                            </div>
                            <div class="card-back">
                                <p>Cam kết mang đến trải nghiệm tốt nhất cho người dùng với giao diện hiện đại và tính năng đa dạng.</p>
                                <a href="#" class="btn btn-sm btn-light mt-2">Tìm hiểu thêm</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="core-value-card">
                        <div class="card-inner">
                            <div class="card-front">
                                <div class="icon-circle bg-primary text-white mb-4 mx-auto">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <h3 class="h4 fw-bold">Tin Cậy</h3>
                            </div>
                            <div class="card-back">
                                <p>Đảm bảo tính minh bạch và an toàn trong mọi hoạt động, bảo vệ thông tin người dùng là ưu tiên hàng đầu.</p>
                                <a href="#" class="btn btn-sm btn-light mt-2">Tìm hiểu thêm</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Section with Hover Effects -->
        <div class="team-section mb-5">
            <div class="text-center mb-5">
                <span class="badge bg-warning mb-2">Đội Ngũ</span>
                <h2 class="h2 fw-bold">Đội Ngũ Phát Triển</h2>
                <p class="lead mx-auto" style="max-width: 700px;">Những người tài năng đứng sau sự thành công của chúng tôi</p>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-4">
                <!-- Mỗi thành viên trong 1 col -->
                <div class="col">
                    <div class="team-card text-center">
                        <div class="team-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/user.png" alt="Hà Huy Chiến Thắng" class="img-fluid">
                            <div class="team-overlay">
                                <div class="team-social">
                                    <a href="#"><i class="bi bi-facebook"></i></a>
                                    <a href="#"><i class="bi bi-twitter"></i></a>
                                    <a href="#"><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3 class="h6 fw-bold">Hà Huy Chiến Thắng</h3>
                            <p class="text-muted mb-0">Sự kiện & Bài viết</p>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="team-card text-center">
                        <div class="team-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/user.png" alt="Nguyễn Thành Tiến" class="img-fluid">
                            <div class="team-overlay">
                                <div class="team-social">
                                    <a href="#"><i class="bi bi-facebook"></i></a>
                                    <a href="#"><i class="bi bi-twitter"></i></a>
                                    <a href="#"><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3 class="h6 fw-bold">Nguyễn Thành Tiến</h3>
                            <p class="text-muted mb-0">Quản lý Câu lạc bộ</p>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="team-card text-center">
                        <div class="team-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/user.png" alt="Nguyễn Quốc Tiến" class="img-fluid">
                            <div class="team-overlay">
                                <div class="team-social">
                                    <a href="#"><i class="bi bi-facebook"></i></a>
                                    <a href="#"><i class="bi bi-twitter"></i></a>
                                    <a href="#"><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3 class="h6 fw-bold">Nguyễn Quốc Tiến</h3>
                            <p class="text-muted mb-0">Thông báo</p>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="team-card text-center">
                        <div class="team-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/user.png" alt="Lê Ngọc Anh" class="img-fluid">
                            <div class="team-overlay">
                                <div class="team-social">
                                    <a href="#"><i class="bi bi-facebook"></i></a>
                                    <a href="#"><i class="bi bi-twitter"></i></a>
                                    <a href="#"><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3 class="h6 fw-bold">Lê Ngọc Anh</h3>
                            <p class="text-muted mb-0">Nhắn tin</p>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="team-card text-center">
                        <div class="team-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/user.png" alt="Phạm Lê Gia Hân" class="img-fluid">
                            <div class="team-overlay">
                                <div class="team-social">
                                    <a href="#"><i class="bi bi-facebook"></i></a>
                                    <a href="#"><i class="bi bi-twitter"></i></a>
                                    <a href="#"><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3 class="h6 fw-bold">Phạm Lê Gia Hân</h3>
                            <p class="text-muted mb-0">Đăng nhập, Đăng ký & Trang cá nhân</p>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="team-card text-center">
                        <div class="team-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/user.png" alt="Nguyễn Thành Duy Công" class="img-fluid">
                            <div class="team-overlay">
                                <div class="team-social">
                                    <a href="#"><i class="bi bi-facebook"></i></a>
                                    <a href="#"><i class="bi bi-twitter"></i></a>
                                    <a href="#"><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3 class="h6 fw-bold">Nguyễn Thành Duy Công</h3>
                            <p class="text-muted mb-0">Đóng góp, Dashboard, Mail & Deploy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Base Styles */
    .about-page {
        --primary-color: #4f46e5;
        --secondary-color: #f9fafb;
        --text-color: #1f2937;
        --text-muted: #6b7280;
        --border-color: #e5e7eb;
        --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Hero Section with Parallax */
    .hero-section {
        position: relative;
        overflow: hidden;
    }

    .parallax-container {
        height: 500px;
        background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('<?php echo SITE_URL; ?>/assets/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-content {
        max-width: 800px;
        margin: 0 auto;
    }

    /* Divider Custom */
    .divider-custom {
        width: 100%;
        max-width: 7rem;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .divider-custom-line {
        width: 100%;
        height: 2px;
        background-color: #fff;
        border-radius: 1rem;
    }

    .divider-custom-icon {
        color: #fff;
        font-size: 1.5rem;
        margin: 0 1rem;
    }

    /* Mission & Vision Sections */
    .mission-section,
    .vision-section {
        padding: 4rem 0;
    }

    .content-wrapper {
        padding: 2rem;
    }

    .image-container {
        position: relative;
        z-index: 2;
        overflow: hidden;
        border-radius: 12px;
    }

    .image-shape {
        position: absolute;
        border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        background-color: rgba(79, 70, 229, 0.1);
    }

    .shape-1 {
        width: 300px;
        height: 300px;
        top: -50px;
        left: -50px;
        z-index: 1;
        animation: morph 8s ease-in-out infinite;
    }

    .shape-2 {
        width: 200px;
        height: 200px;
        bottom: -30px;
        right: -30px;
        z-index: 1;
        animation: morph 8s ease-in-out infinite 2s;
    }

    .shape-3 {
        width: 250px;
        height: 250px;
        top: -40px;
        right: -40px;
        z-index: 1;
        animation: morph 8s ease-in-out infinite 1s;
    }

    .shape-4 {
        width: 180px;
        height: 180px;
        bottom: -20px;
        left: -20px;
        z-index: 1;
        animation: morph 8s ease-in-out infinite 3s;
    }

    @keyframes morph {
        0% {
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        }

        25% {
            border-radius: 58% 42% 75% 25% / 76% 46% 54% 24%;
        }

        50% {
            border-radius: 50% 50% 33% 67% / 55% 27% 73% 45%;
        }

        75% {
            border-radius: 33% 67% 58% 42% / 63% 68% 32% 37%;
        }

        100% {
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        }
    }

    /* Feature List */
    .feature-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    /* Stats Container */
    .stats-container {
        margin-top: 2rem;
    }

    .stat-card {
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: var(--shadow);
        text-align: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    /* Core Values Section with 3D Cards */
    .core-values-section {
        padding: 4rem 0;
    }

    .core-value-card {
        perspective: 1000px;
        height: 300px;
    }

    .card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.8s;
        transform-style: preserve-3d;
    }

    .core-value-card:hover .card-inner {
        transform: rotateY(180deg);
    }

    .card-front,
    .card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        border-radius: 12px;
        box-shadow: var(--shadow);
        padding: 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .card-front {
        background-color: white;
    }

    .card-back {
        background-color: var(--primary-color);
        color: white;
        transform: rotateY(180deg);
    }

    .icon-circle {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    /* Team Section with Hover Effects */
    .team-section {
        padding: 4rem 0;
    }

    .team-card {
        background-color: white;
        width: 200px;
        height: 330px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
    }

    .team-card:hover {
        transform: translateY(-10px);
    }

    .team-image {
        position: relative;
        overflow: hidden;
    }

    .team-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(79, 70, 229, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .team-card:hover .team-overlay {
        opacity: 1;
    }

    .team-social {
        display: flex;
        gap: 1rem;
    }

    .team-social a {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: white;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .team-social a:hover {
        background-color: var(--primary-color);
        color: white;
    }

    .team-info {
        padding: 1.5rem;
        text-align: center;
    }

    /* Contact CTA Section */
    .contact-cta {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: 16px;
        padding: 4rem 2rem;
        margin-top: 4rem;
    }

    /* Counter Animation */
    @keyframes countUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate counters
        const counters = document.querySelectorAll('.counter');
        const speed = 200;

        const animateCounter = () => {
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const increment = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(animateCounter, 1);
                } else {
                    counter.innerText = target;
                }
            });
        };

        // Start animation when elements are in viewport
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter();
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });

        const statsContainer = document.querySelector('.stats-container');
        if (statsContainer) {
            observer.observe(statsContainer);
        }

        // Parallax effect for hero section
        window.addEventListener('scroll', function() {
            const parallax = document.querySelector('.parallax-container');
            if (parallax) {
                let scrollPosition = window.pageYOffset;
                parallax.style.backgroundPositionY = scrollPosition * 0.5 + 'px';
            }
        });
    });
</script>