<?php
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
$uploadDir = '../uploads/playlist_avatar/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Tự động tạo thư mục nếu chưa có
}
$newName = 'avatar_user_' . $userId . '_' . time() . '.' . $ext;
$destination = $uploadDir . $newName;
if (move_uploaded_file($file['tmp_name'], $destination)) {
    $avatarUrl = './uploads/playlist_avatar/' . $newName; // Đường dẫn tương đối lưu vào DB
    /*
    $stmt = $conn->prepare("UPDATE Users SET User_avatar_url = ? WHERE User_id = ?");
    $stmt->bind_param("si", $avatarUrl, $userId);
    $stmt->execute();
    */  
    echo json_encode(['success' => true, 'message' => 'Cập nhật ảnh đại diện thành công!', 'url' => $avatarUrl]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, không thể lưu file ảnh.']);
}

/*
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}
    */
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request data.']);
    exit;
}
$userId = $_SESSION['user_id'];
$playlistId = $_SESSION['playlist_id'];
$playlistname = trim($input['playlistname']);
$changSeenMode = trim($input['changeSeenMode']);
try {
    /*
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET user_name = ?, email = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $newUsername, $newEmail, $hashedPassword, $userId);
    } else {
        $sql = "UPDATE users SET user_name = ?, email = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $newUsername, $newEmail, $userId);
    }
    */
    $sql = "UPDATE playlists SET playlist_name = ?, changeSeenMode = ? WHERE playlist_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $playlistname, $changSeenMode, $playlist_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        if ($conn->errno == 1062) {
            echo json_encode(['status' => 'error', 'message' => 'Username or Email already exists.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        }
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}
$conn->close();
?>