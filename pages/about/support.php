<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../templates/layout.php';
?>
<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-3">Trung Tâm Hỗ Trợ</h1>
            <div class="divider-custom mx-auto mb-4">
                <div class="divider-custom-line"></div>
                <div class="divider-custom-icon"><i class="bi bi-question-circle"></i></div>
                <div class="divider-custom-line"></div>
            </div>
            <p class="lead text-muted">Chúng tôi luôn sẵn sàng giúp đỡ bạn giải quyết mọi vấn đề</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- FAQ Section -->
        <div class="col-lg-8" data-aos="fade-up">
            <div class="bg-white rounded-4 shadow-sm p-4 p-lg-5 mb-4">
                <h2 class="h3 fw-bold mb-4 border-start border-4 border-primary ps-3">Câu Hỏi Thường Gặp</h2>
                
                <div class="accordion accordion-flush" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header">
                            <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="bi bi-person-plus-fill me-2 text-primary"></i>
                                Làm thế nào để tham gia câu lạc bộ?
                            </button>
                        </h3>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Để tham gia câu lạc bộ, bạn cần thực hiện các bước sau:</p>
                                <ol class="mb-0">
                                    <li>Đăng nhập vào tài khoản của bạn</li>
                                    <li>Truy cập trang <strong>Câu lạc bộ</strong> từ menu chính</li>
                                    <li>Tìm và chọn câu lạc bộ mà bạn muốn tham gia</li>
                                    <li>Nhấn vào nút <span class="badge bg-primary">Tham gia</span> và điền thông tin yêu cầu</li>
                                    <li>Chờ phê duyệt từ trưởng câu lạc bộ</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="bi bi-calendar-plus-fill me-2 text-primary"></i>
                                Làm thế nào để tạo sự kiện mới?
                            </button>
                        </h3>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Chỉ trưởng câu lạc bộ mới có quyền tạo sự kiện mới. Nếu bạn là trưởng câu lạc bộ:</p>
                                <ol class="mb-0">
                                    <li>Đăng nhập vào tài khoản của bạn</li>
                                    <li>Truy cập trang <strong>Quản lý CLB</strong> từ menu chính</li>
                                    <li>Chọn mục <strong>Sự kiện</strong> từ menu bên trái</li>
                                    <li>Nhấn vào nút <span class="badge bg-success">Tạo sự kiện mới</span></li>
                                    <li>Điền đầy đủ thông tin sự kiện và gửi để phê duyệt</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3 rounded-3 shadow-sm">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="bi bi-pencil-square me-2 text-primary"></i>
                                Làm thế nào để cập nhật thông tin cá nhân?
                            </button>
                        </h3>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Để cập nhật thông tin cá nhân của bạn:</p>
                                <ol class="mb-0">
                                    <li>Đăng nhập vào tài khoản của bạn</li>
                                    <li>Nhấp vào tên người dùng ở góc trên bên phải</li>
                                    <li>Chọn <strong>Trang Cá Nhân</strong> từ menu thả xuống</li>
                                    <li>Nhấn vào nút <span class="badge bg-info">Chỉnh sửa thông tin</span></li>
                                    <li>Cập nhật thông tin của bạn và nhấn <strong>Lưu thay đổi</strong></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-4 shadow-sm p-4 p-lg-5" data-aos="fade-up" data-aos-delay="100">
                <h2 class="h3 fw-bold mb-4 border-start border-4 border-primary ps-3">Hướng Dẫn Sử Dụng</h2>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="guide-card p-4 rounded-3 border border-light h-100">
                            <div class="guide-icon bg-primary-subtle text-primary rounded-circle mb-3">
                                <i class="bi bi-book"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-3">Tài Liệu Hướng Dẫn</h3>
                            <p class="text-muted mb-4">Xem hướng dẫn chi tiết về cách sử dụng các tính năng của hệ thống quản lý câu lạc bộ.</p>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-text me-2"></i>Xem tài liệu
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="guide-card p-4 rounded-3 border border-light h-100">
                            <div class="guide-icon bg-primary-subtle text-primary rounded-circle mb-3">
                                <i class="bi bi-play-circle"></i>
                            </div>
                            <h3 class="h5 fw-bold mb-3">Video Hướng Dẫn</h3>
                            <p class="text-muted mb-4">Xem các video hướng dẫn trực quan về cách sử dụng hệ thống quản lý câu lạc bộ.</p>
                            <a href="#" class="btn btn-outline-primary">
                                <i class="bi bi-camera-video me-2"></i>Xem video
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Support Sidebar -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                <h3 class="h4 fw-bold mb-3">Liên Hệ Hỗ Trợ</h3>
                <p class="text-muted mb-4">Bạn không tìm thấy câu trả lời cho câu hỏi của mình? Hãy liên hệ với đội ngũ hỗ trợ của chúng tôi.</p>
                
                <div class="d-grid gap-2">
                    <a href="index.php?page=about&subpage=contact" class="btn btn-primary">
                        <i class="bi bi-headset me-2"></i>Liên hệ hỗ trợ
                    </a>
                </div>
            </div>
            
            <div class="bg-primary bg-gradient text-white rounded-4 shadow-sm p-4 mb-4">
                <h3 class="h4 fw-bold mb-3">Hỗ Trợ Trực Tuyến</h3>
                <p class="mb-4">Đội ngũ hỗ trợ của chúng tôi luôn sẵn sàng giúp đỡ bạn trong giờ làm việc.</p>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="support-status me-2"></div>
                    <span>Trực tuyến (8:00 - 17:00)</span>
                </div>
                
                <div class="d-grid">
                    <button class="btn btn-light">
                        <i class="bi bi-chat-dots-fill me-2"></i>Bắt đầu trò chuyện
                    </button>
                </div>
            </div>
            
            <div class="bg-white rounded-4 shadow-sm p-4">
                <h3 class="h4 fw-bold mb-3">Tài Nguyên</h3>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item border-0 ps-0">
                        <a href="#" class="text-decoration-none d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>
                            Hướng dẫn sử dụng (PDF)
                        </a>
                    </li>
                    <li class="list-group-item border-0 ps-0">
                        <a href="#" class="text-decoration-none d-flex align-items-center">
                            <i class="bi bi-question-circle me-2 text-primary"></i>
                            Câu hỏi thường gặp mở rộng
                        </a>
                    </li>
                    <li class="list-group-item border-0 ps-0">
                        <a href="#" class="text-decoration-none d-flex align-items-center">
                            <i class="bi bi-journal-text me-2 text-success"></i>
                            Blog hỗ trợ
                        </a>
                    </li>
                    <li class="list-group-item border-0 ps-0">
                        <a href="#" class="text-decoration-none d-flex align-items-center">
                            <i class="bi bi-people me-2 text-info"></i>
                            Cộng đồng hỗ trợ
                        </a>
                    </li>
                </ul>
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
    
    .accordion-button:not(.collapsed) {
        background-color: rgba(37, 99, 235, 0.1);
        color: var(--primary-color);
        box-shadow: none;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(37, 99, 235, 0.1);
    }
    
    .guide-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .guide-card {
        transition: all 0.3s ease;
    }
    
    .guide-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }
    
    .support-status {
        width: 12px;
        height: 12px;
        background-color: #10b981;
        border-radius: 50%;
        position: relative;
    }
    
    .support-status::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: #10b981;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(2.5);
            opacity: 0;
        }
    }
    
    .list-group-item a {
        transition: all 0.3s ease;
    }
    
    .list-group-item a:hover {
        transform: translateX(5px);
        color: var(--primary-color) !important;
    }
</style>