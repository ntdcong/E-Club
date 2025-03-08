<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../templates/layout.php';
?>
<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-3">Liên Hệ Với Chúng Tôi</h1>
            <div class="divider-custom mx-auto mb-4">
                <div class="divider-custom-line"></div>
                <div class="divider-custom-icon"><i class="bi bi-chat-dots"></i></div>
                <div class="divider-custom-line"></div>
            </div>
            <p class="lead text-muted">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn</p>
        </div>
    </div>

    <!-- Contact Info & Form Section -->
    <div class="row g-5 mb-5">
        <!-- Contact Information -->
        <div class="col-lg-5" data-aos="fade-right">
            <div class="bg-white p-4 p-lg-5 rounded-4 shadow-sm h-100">
                <h2 class="h3 fw-bold mb-4 border-start border-4 border-primary ps-3">Thông Tin Liên Hệ</h2>
                
                <div class="contact-item d-flex mb-4">
                    <div class="contact-icon bg-primary text-white me-3">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div>
                        <h3 class="h6 fw-bold mb-1">Địa Chỉ</h3>
                        <p class="mb-0 text-muted">123 Đường ABC, Quận XYZ<br>Thành phố HCM, Việt Nam</p>
                    </div>
                </div>
                
                <div class="contact-item d-flex mb-4">
                    <div class="contact-icon bg-primary text-white me-3">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <div>
                        <h3 class="h6 fw-bold mb-1">Điện Thoại</h3>
                        <p class="mb-0 text-muted">+84 123 456 789</p>
                    </div>
                </div>
                
                <div class="contact-item d-flex mb-4">
                    <div class="contact-icon bg-primary text-white me-3">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div>
                        <h3 class="h6 fw-bold mb-1">Email</h3>
                        <p class="mb-0 text-muted">support@eclubmanagement.com</p>
                    </div>
                </div>
                
                <div class="mt-5">
                    <h3 class="h6 fw-bold mb-3">Kết Nối Với Chúng Tôi</h3>
                    <div class="d-flex gap-2">
                        <a href="#" class="social-link">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="col-lg-7" data-aos="fade-left">
            <div class="bg-white p-4 p-lg-5 rounded-4 shadow-sm">
                <h2 class="h3 fw-bold mb-4 border-start border-4 border-primary ps-3">Gửi Tin Nhắn</h2>
                <form id="contactForm" class="contact-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Họ và tên" required>
                                <label for="name">Họ và tên</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                <label for="email">Email</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Chủ đề" required>
                        <label for="subject">Chủ đề</label>
                    </div>
                    
                    <div class="form-floating mb-4">
                        <textarea class="form-control" id="message" name="message" style="height: 150px" placeholder="Nội dung" required></textarea>
                        <label for="message">Nội dung</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send me-2"></i>Gửi Tin Nhắn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Map Section -->
    <div class="row mb-5" data-aos="fade-up">
        <div class="col-12">
            <div class="bg-white p-4 rounded-4 shadow-sm">
                <h2 class="h3 fw-bold mb-4 border-start border-4 border-primary ps-3">Vị Trí Của Chúng Tôi</h2>
                <div class="ratio ratio-21x9 rounded-3 overflow-hidden">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4946681007846!2d106.69908361471815!3d10.771913992323586!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f4670702e31%3A0xa5777fb3a5bb9e35!2sHo%20Chi%20Minh%20City%20University%20of%20Technology!5e0!3m2!1sen!2s!4v1650010000000!5m2!1sen!2s" 
                            style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
    
    .contact-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .social-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #f8f9fa;
        color: var(--primary-color);
        font-size: 1.25rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .social-link:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-3px);
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.25);
    }
    
    .contact-form .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Simulate form submission
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang gửi...';
        
        setTimeout(function() {
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Đã gửi thành công!';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');
            
            form.reset();
            
            setTimeout(function() {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-primary');
            }, 3000);
        }, 1500);
    });
});
</script>
<?php
?>