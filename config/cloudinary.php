<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Admin\AdminApi;

// Cấu hình Cloudinary
Configuration::instance([
    'cloud' => [
        'cloud_name' => 'dsxpjcve6', // Thay bằng cloud_name thực tế
        'api_key'    => '812194634798677',    // Thay bằng API key thực tế
        'api_secret' => '9Xo7p-v8SSV_N7WzAQznhkIF-oA'  // Thay bằng API secret thực tế
    ],
    'url' => [
        'secure' => true
    ]
]);

// Hàm upload ảnh lên Cloudinary
function uploadToCloudinary($file, $folder = 'club_management') {
    try {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new Exception('No file uploaded');
        }

        $upload = (new UploadApi())->upload($file['tmp_name'], [
            'folder' => $folder,
            'resource_type' => 'image',
            'transformation' => [
                'quality' => 'auto',
                'fetch_format' => 'auto'
            ]
        ]);

        return [
            'success' => true,
            'url' => $upload['secure_url'],
            'public_id' => $upload['public_id']
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Hàm xóa ảnh khỏi Cloudinary
function deleteFromCloudinary($public_id) {
    try {
        $admin = new AdminApi();
        $result = $admin->deleteAssets([$public_id]);
        
        return [
            'success' => true,
            'result' => $result
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Kiểm tra upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $result = uploadToCloudinary($_FILES['image']);
    echo json_encode($result);
}

// Kiểm tra xóa ảnh
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    $result = deleteFromCloudinary($_POST['delete_image']);
    echo json_encode($result);
}
?>
