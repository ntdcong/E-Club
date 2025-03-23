<?php
require_once __DIR__ . '/../../config/cloudinary.php';

// Check if user is logged in and is a club leader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'club_leader') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT cl.club_id, c.name as club_name 
                       FROM club_leaders cl 
                       JOIN clubs c ON cl.club_id = c.id 
                       WHERE cl.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$club_data = $result->fetch_assoc();
$club_id = $club_data['club_id'];
$club_name = $club_data['club_name'];
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_url = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_result = uploadToCloudinary($_FILES['image'], 'club_posts');
        if ($upload_result['success']) {
            $image_url = $upload_result['url'];
        } else {
            flashMessage('Tải ảnh lên không thành công: ' . $upload_result['message'], 'danger');
        }
    }

    $stmt = $conn->prepare("INSERT INTO club_posts (club_id, title, content, image_url, status, created_at, created_by) 
                          VALUES (?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP, ?)");
    $stmt->bind_param("isssi", $club_id, $title, $content, $image_url, $user_id);

    if ($stmt->execute()) {
        flashMessage('Bài viết đã được tạo và đang chờ duyệt');
        redirect('/index.php?page=club_leader/posts');
        exit();
    } else {
        flashMessage('Có lỗi xảy ra: ' . $conn->error, 'danger');
    }
    $stmt->close();
}
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="mb-0">Tạo Bài Viết Mới - <?php echo htmlspecialchars($club_name); ?></h5>
            </div>
            <a href="index.php?page=club_leader/posts" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
        <div class="card-body">
            <form action="index.php?page=club_leader/create_post" method="POST" enctype="multipart/form-data" id="postForm">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">Tiêu Đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                placeholder="Nhập tiêu đề bài viết" required>
                        </div>

                        <div class="mb-4">
                            <label for="editor" class="form-label fw-bold">Nội Dung <span class="text-danger">*</span></label>
                            <div id="editor" class="form-control" style="min-height: 400px; border: 1px solid #ced4da; border-radius: 0.25rem;"></div>
                            <textarea name="content" id="content" style="display: none"></textarea>
                            <div class="form-text text-end" id="wordCount">0 từ | 0 ký tự</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Thông Tin Bổ Sung</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label for="image" class="form-label fw-bold">Hình Ảnh Đại Diện</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <div class="form-text">Kích thước khuyến nghị: 1200x630 pixels</div>
                                    <div class="mt-2" id="imagePreview" style="display: none; max-width: 100%;">
                                        <img id="previewImg" class="img-fluid rounded" style="max-height: 200px;" />
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Bài viết sẽ được gửi đến quản trị viên để xét duyệt trước khi xuất bản.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="index.php?page=club_leader/posts" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Hủy
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-paper-plane me-1"></i> Gửi Bài Viết
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include CKEditor and necessary styles -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let editor;
    
    ClassicEditor
        .create(document.getElementById("editor"), {
            toolbar: [
                'heading', '|',
                'bold', 'italic', 'strikethrough', 'underline', '|',
                'bulletedList', 'numberedList', '|',
                'outdent', 'indent', '|',
                'alignment', '|',
                'link', 'blockQuote', 'insertTable', 'mediaEmbed', '|',
                'undo', 'redo'
            ],
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells',
                    'tableProperties',
                    'tableCellProperties'
                ]
            },
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                ]
            },
            placeholder: 'Viết nội dung bài viết chi tiết ở đây...'
        })
        .then(newEditor => {
            editor = newEditor;
            
            // Add word and character counter
            editor.model.document.on('change:data', () => {
                const text = editor.getData();
                const plainText = text.replace(/<[^>]*>/g, '');
                const words = plainText.trim().split(/\s+/).filter(word => word.length > 0);
                document.getElementById('wordCount').textContent = 
                    `${words.length} từ | ${plainText.length} ký tự`;
            });
        })
        .catch(error => {
            console.error(error);
        });

    // Submit form and get editor content
    document.getElementById('postForm').addEventListener('submit', function(e) {
        if (!editor) {
            e.preventDefault();
            alert('Trình soạn thảo chưa khởi tạo. Vui lòng thử lại.');
            return;
        }
        
        const editorContent = editor.getData();
        if (!editorContent.trim()) {
            e.preventDefault();
            alert('Vui lòng nhập nội dung bài viết.');
            return;
        }
        
        document.getElementById('content').value = editorContent;
    });
    
    // Image preview
    document.getElementById('image').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(file);
        }
    });
});
</script>