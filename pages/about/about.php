<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../templates/layout.php';
?>
<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-3">Về Chúng Tôi</h1>
            <div class="divider-custom mx-auto mb-4">
                <div class="divider-custom-line"></div>
                <div class="divider-custom-icon"><i class="bi bi-stars"></i></div>
                <div class="divider-custom-line"></div>
            </div>
            <p class="lead text-muted">Nền tảng kết nối và phát triển cộng đồng câu lạc bộ</p>
        </div>
    </div>

    <!-- Mission Section -->
    <div class="row align-items-center mb-5" data-aos="fade-up">
        <div class="col-lg-6 mb-4 mb-lg-0"></div>
        <div class="col-lg-6">
            <h2 class="h2 fw-bold mb-3">Sứ Mệnh Của Chúng Tôi</h2>
            <p class="lead mb-4">Hệ thống quản lý câu lạc bộ của chúng tôi được thiết kế để tạo ra một môi trường năng động và hiệu quả cho việc tổ chức và tham gia các hoạt động câu lạc bộ.</p>
            <p>Chúng tôi cam kết mang đến trải nghiệm người dùng tốt nhất, giúp các câu lạc bộ vận hành hiệu quả và thành viên dễ dàng tham gia các hoạt động phù hợp với sở thích của mình.</p>
        </div>
    </div>

    <!-- Vision Section -->
    <div class="row align-items-center mb-5" data-aos="fade-up" data-aos-delay="100">
        <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0"></div>
        <div class="col-lg-6 order-lg-1">
            <h2 class="h2 fw-bold mb-3">Tầm Nhìn</h2>
            <p class="lead mb-4">Chúng tôi hướng đến việc trở thành nền tảng hàng đầu trong việc kết nối và phát triển cộng đồng câu lạc bộ, tạo điều kiện cho các thành viên phát triển kỹ năng và đam mê của mình.</p>
            <p>Với công nghệ hiện đại và giao diện thân thiện, chúng tôi mong muốn xây dựng một hệ sinh thái toàn diện cho các câu lạc bộ phát triển bền vững.</p>
        </div>
    </div>

    <!-- Core Values Section -->
    <div class="py-5 bg-light rounded-3 mb-5" data-aos="fade-up" data-aos-delay="200">
        <div class="container">
            <h2 class="h2 fw-bold text-center mb-5">Giá Trị Cốt Lõi</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-4 bg-white rounded-3 shadow-sm h-100 core-value-card">
                        <div class="icon-circle bg-primary text-white mb-4 mx-auto">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3 class="h4 fw-bold">Cộng Đồng</h3>
                        <p class="mb-0">Xây dựng môi trường gắn kết và hỗ trợ lẫn nhau, tạo điều kiện cho mọi thành viên phát triển.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4 bg-white rounded-3 shadow-sm h-100 core-value-card">
                        <div class="icon-circle bg-primary text-white mb-4 mx-auto">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <h3 class="h4 fw-bold">Chất Lượng</h3>
                        <p class="mb-0">Cam kết mang đến trải nghiệm tốt nhất cho người dùng với giao diện hiện đại và tính năng đa dạng.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4 bg-white rounded-3 shadow-sm h-100 core-value-card">
                        <div class="icon-circle bg-primary text-white mb-4 mx-auto">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="h4 fw-bold">Tin Cậy</h3>
                        <p class="mb-0">Đảm bảo tính minh bạch và an toàn trong mọi hoạt động, bảo vệ thông tin người dùng là ưu tiên hàng đầu.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="row mb-5" data-aos="fade-up" data-aos-delay="300">
        <div class="col-lg-10 mx-auto text-center">
            <h2 class="h2 fw-bold mb-5">Đội Ngũ Phát Triển</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="team-member">
                        <h3 class="h5 fw-bold">Nguyễn Văn A</h3>
                        <p class="text-muted mb-0">Founder & CEO</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-member">
                        <h3 class="h5 fw-bold">Trần Thị B</h3>
                        <p class="text-muted mb-0">Lead Developer</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-member">
                        <h3 class="h5 fw-bold">Lê Văn C</h3>
                        <p class="text-muted mb-0">UX/UI Designer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
        background-color: var(--primary-color);
        border-radius: 1rem;
    }
    
    .divider-custom-icon {
        color: var(--primary-color);
        font-size: 1.5rem;
        margin: 0 1rem;
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
    
    .core-value-card {
        transition: all 0.3s ease;
    }
    
    .core-value-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .team-member {
        transition: all 0.3s ease;
    }
    
    .team-member:hover {
        transform: translateY(-5px);
    }
</style>