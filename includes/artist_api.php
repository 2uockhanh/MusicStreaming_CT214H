<?php
header('Content-Type: application/json');
require 'db-connect.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
define('AVATAR_UPLOAD_DIR', '../uploads/avatars/');

// --- Hàm xử lý Upload File Ảnh Đại Diện ---
function handleAvatarUpload($file_input) {
    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$file_input];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($ext, $allowed)) {
        throw new Exception("Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif).");
    }

    if (!is_dir(AVATAR_UPLOAD_DIR)) {
        mkdir(AVATAR_UPLOAD_DIR, 0777, true);
    }

    // Tạo tên file ngẫu nhiên
    $newName = uniqid('artist_', true) . '.' . $ext;
    
    if (move_uploaded_file($file['tmp_name'], AVATAR_UPLOAD_DIR . $newName)) {
        return 'uploads/avatars/' . $newName; 
    }
    return null;
}

// Lấy danh sách nghệ sĩ
if ($action === 'read') {
    $search = $_POST['search'] ?? ''; 
    try {
        if ($search !== '') {
            $searchParam = "%" . $search . "%";
            $stmt = $conn->prepare("SELECT Artist_id, Artist_name, Biography, Avatar_url FROM Artists WHERE Artist_name LIKE ? ORDER BY Artist_id DESC");
            $stmt->bind_param("s", $searchParam);
        } else {
            $stmt = $conn->prepare("SELECT Artist_id, Artist_name, Biography, Avatar_url FROM Artists ORDER BY Artist_id DESC");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, "message" => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

// Thêm nghệ sĩ mới
if ($action === 'create') {
    $name = $_POST['artist_name'] ?? '';
    $bio = $_POST['biography'] ?? '';
    $avatar_url = $_POST['avatar_url'] ?? ''; // Url fallback nếu ko có file upload

    try {
        $uploaded_file = handleAvatarUpload('avatar');
        if ($uploaded_file) {
            $avatar_url = $uploaded_file;
        }

        $stmt = $conn->prepare("INSERT INTO Artists (Artist_name, Biography, Avatar_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $bio, $avatar_url);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thêm nghệ sĩ thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi thêm: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// Cập nhật nghệ sĩ
if ($action === 'update') {
    $id = $_POST['artist_id'] ?? '';
    $name = $_POST['artist_name'] ?? '';
    $bio = $_POST['biography'] ?? '';
    $avatar_url = $_POST['avatar_url'] ?? ''; 

    try {
        $uploaded_file = handleAvatarUpload('avatar');
        if ($uploaded_file) {
            $avatar_url = $uploaded_file; // Ưu tiên file vừa được upload
        }

        $stmt = $conn->prepare("UPDATE Artists SET Artist_name = ?, Biography = ?, Avatar_url = ? WHERE Artist_id = ?");
        $stmt->bind_param("sssi", $name, $bio, $avatar_url, $id); 
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// Xóa nghệ sĩ
if ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    try {
        $stmt = $conn->prepare("DELETE FROM Artists WHERE Artist_id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Xóa nghệ sĩ thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa nghệ sĩ này (đang vướng khóa ngoại do nghệ sĩ này có bài hát trong hệ thống).']);
    }
    exit;
}
?>