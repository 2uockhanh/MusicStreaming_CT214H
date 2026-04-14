<?php
<<<<<<< HEAD
session_start();
header('Content-Type: application/json');
require 'db-connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập!']);
    exit;
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Chưa chọn file hoặc có lỗi tải lên!']);
    exit;
}

$userId = $_SESSION['user_id'];
$file = $_FILES['avatar'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (jpg, png, gif, webp).']);
    exit;
}

$uploadDir = '../uploads/user_avatar/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Tự động tạo thư mục nếu chưa có
}

$newName = 'avatar_user_' . $userId . '_' . time() . '.' . $ext;
$destination = $uploadDir . $newName;

if (move_uploaded_file($file['tmp_name'], $destination)) {
    $avatarUrl = 'uploads/user_avatar/' . $newName; // Đường dẫn tương đối lưu vào DB
    $stmt = $conn->prepare("UPDATE Users SET User_avatar_url = ? WHERE User_id = ?");
    $stmt->bind_param("si", $avatarUrl, $userId);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Cập nhật ảnh đại diện thành công!', 'url' => $avatarUrl]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, không thể lưu file ảnh.']);
}
=======
    session_start();
    include '../includes/db-connect.php';

    if(isset($_SESSION['user_id']) && isset($_FILES['avatar'])) {
        $userId = $_SESSION['user_id'];
        $target_dir = "../uploads/avatars/";

        $fileName = $userId . "_" . time() . "_" . basename($_FILES["avatar"]["name"]);
        $target_file_path = $target_dir . $fileName;

        $fileType = pathinfo($target_file_path, PATHINFO_EXTENSION);
        $allowedTypes = ['jpg', 'jpeg', 'png'];

        if(in_array(strtolower($fileType), $allowedTypes)) {
            if(move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file_path)) {
                $sql = "UPDATE users SET User_avatar_url = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $target_file_path, $userId);
                if($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Avatar updated successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database update failed!']);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'File upload failed!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file type!']);
        }
    }
>>>>>>> a6f9757347f91081d963074a00db226ffab80926
?>