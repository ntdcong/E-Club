<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../templates/layout.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">FAQ</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold mb-4" data-aos="fade-up">Câu hỏi thường gặp</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="accordion shadow-sm rounded-lg" id="faqAccordion" data-aos="fade-up" data-aos-delay="100">
                <!-- Câu hỏi 1 -->
                <div class="accordion-item border-0 mb-3 rounded-lg">
                    <h2 class="accordion-header" id="heading1">
                        <button class="accordion-button rounded-lg" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                            <i class="bi bi-question-circle me-2 text-primary"></i> Làm thế nào để tham gia câu lạc bộ?
                        </button>
                    </h2>
                    <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="heading1" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>Để tham gia câu lạc bộ, bạn cần thực hiện các bước sau:</p>
                            <ol>
                                <li>Đăng nhập vào tài khoản của bạn</li>
                                <li>Truy cập trang <a href="index.php?page=clubs">Câu lạc bộ</a></li>
                                <li>Chọn câu lạc bộ bạn muốn tham gia</li>
                                <li>Nhấn nút "Tham gia" và chờ phê duyệt từ quản trị viên</li>
                            </ol>
                            <p>Sau khi yêu cầu của bạn được phê duyệt, bạn sẽ nhận được thông báo và trở thành thành viên chính thức của câu lạc bộ.</p>
                        </div>
                    </div>
                </div>

                <!-- Câu hỏi 2 -->
                <div class="accordion-item border-0 mb-3 rounded-lg">
                    <h2 class="accordion-header" id="heading2">
                        <button class="accordion-button collapsed rounded-lg" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                            <i class="bi bi-question-circle me-2 text-primary"></i> Làm thế nào để tạo một câu lạc bộ mới?
                        </button>
                    </h2>
                    <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>Việc tạo câu lạc bộ mới chỉ có thể được thực hiện bởi quản trị viên. Nếu bạn muốn đề xuất một câu lạc bộ mới, vui lòng liên hệ với quản trị viên hoặc gửi đề xuất qua trang <a href="index.php?page=contact">Liên hệ</a>.</p>
                            <p>Quản trị viên sẽ xem xét đề xuất của bạn và có thể tạo câu lạc bộ mới nếu đề xuất được chấp thuận.</p>
                        </div>
                    </div>
                </div>

                <!-- Câu hỏi 3 -->
                <div class="accordion-item border-0 mb-3 rounded-lg">
                    <h2 class="accordion-header" id="heading3">
                        <button class="accordion-button collapsed rounded-lg" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                            <i class="bi bi-question-circle me-2 text-primary"></i> Làm thế nào để đăng ký tham gia sự kiện?
                        </button>
                    </h2>
                    <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>Để đăng ký tham gia sự kiện, bạn cần:</p>
                            <ol>
                                <li>Đăng nhập vào tài khoản của bạn</li>
                                <li>Truy cập trang <a href="index.php?page=events">Sự kiện</a></li>
                                <li>Chọn sự kiện bạn muốn tham gia</li>
                                <li>Nhấn nút "Đăng ký tham gia" và làm theo hướng dẫn</li>
                            </ol>
                            <p>Sau khi đăng ký thành công, bạn sẽ nhận được thông báo xác nhận và thông tin chi tiết về sự kiện.</p>
                        </div>
                    </div>
                </div>

                <!-- Câu hỏi 4 -->
                <div class="accordion-item border-0 mb-3 rounded-lg">
                    <h2 class="accordion-header" id="heading4">
                        <button class="accordion-button collapsed rounded-lg" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                            <i class="bi bi-question-circle me-2 text-primary"></i> Làm thế nào để trở thành trưởng câu lạc bộ?
                        </button>
                    </h2>
                    <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="heading4" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>Trưởng câu lạc bộ được chỉ định bởi quản trị viên. Để trở thành trưởng câu lạc bộ, bạn cần:</p>
                            <ol>
                                <li>Là thành viên tích cực của câu lạc bộ</li>
                                <li>Thể hiện khả năng lãnh đạo và tổ chức</li>
                                <li>Liên hệ với quản trị viên để bày tỏ nguyện vọng</li>
                            </ol>
                            <p>Quản trị viên sẽ xem xét và có thể chỉ định bạn làm trưởng câu lạc bộ nếu bạn đáp ứng các yêu cầu.</p>
                        </div>
                    </div>
                </div>

                <!-- Câu hỏi 5 -->
                <div class="accordion-item border-0 mb-3 rounded-lg">
                    <h2 class="accordion-header" id="heading5">
                        <button class="accordion-button collapsed rounded-lg" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                            <i class="bi bi-question-circle me-2 text-primary"></i> Tôi quên mật khẩu, phải làm sao?
                        </button>
                    </h2>
                    <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="heading5" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <p>Nếu bạn quên mật khẩu, bạn có thể thực hiện các bước sau để đặt lại mật khẩu:</p>
                            <ol>
                                <li>Truy cập trang <a href="index.php?page=login">Đăng nhập</a></li>
                                <li>Nhấn vào liên kết "Quên mật khẩu"</li>
                                <li>Nhập địa chỉ email đã đăng ký</li>
                                <li>Làm theo hướng dẫn được gửi đến email của bạn</li>
                            </ol>
                            <p>Nếu bạn vẫn gặp vấn đề, vui lòng liên hệ với quản trị viên qua trang <a href="index.php?page=contact">Liên hệ</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm rounded-lg border-0 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-headset me-2"></i> Cần thêm hỗ trợ?</h5>
                </div>
                <div class="card-body">
                    <p>Nếu bạn không tìm thấy câu trả lời cho câu hỏi của mình, vui lòng liên hệ với chúng tôi qua:</p>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-envelope-fill text-primary me-2"></i> Email: support@clubmanagement.com</li>
                        <li class="mb-2"><i class="bi bi-telephone-fill text-primary me-2"></i> Điện thoại: (84) 123-456-789</li>
                        <li><i class="bi bi-chat-dots-fill text-primary me-2"></i> <a href="index.php?page=contact">Form liên hệ</a></li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm rounded-lg border-0" data-aos="fade-up" data-aos-delay="300">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i> Liên kết hữu ích</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item border-0 ps-0"><i class="bi bi-chevron-right text-primary me-2"></i> <a href="index.php?page=about&action=privacy">Chính sách quyền riêng tư</a></li>
                        <li class="list-group-item border-0 ps-0"><i class="bi bi-chevron-right text-primary me-2"></i> <a href="index.php?page=about&action=terms">Điều khoản sử dụng</a></li>
                        <li class="list-group-item border-0 ps-0"><i class="bi bi-chevron-right text-primary me-2"></i> <a href="index.php?page=about&action=about">Về chúng tôi</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
?>