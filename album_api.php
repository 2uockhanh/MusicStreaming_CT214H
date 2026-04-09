<?php
header('Content-Type: application/json');
require 'db-connect.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
define('IMAGE_UPLOAD_DIR', '../uploads/images/');

// --- Hàm xử lý Upload Hình Ảnh Bìa ---
function handleImageUpload($file_input) {
    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$file_input];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        throw new Exception("Chỉ chấp nhận file định dạng ảnh (jpg, jpeg, png, gif, webp).");
    }

    $newName = uniqid('album_', true) . '.' . $ext;
    
    if (move_uploaded_file($file_tmp = $file['tmp_name'], IMAGE_UPLOAD_DIR . $newName)) {
        return 'uploads/images/' . $newName; 
    }
    return null;
}

// ==================== 1. READ ====================
if ($action === 'read') {
    try {
        $stmt = $conn->prepare("SELECT a.Album_id, a.Album_title, a.Release_date, a.Cover_image_url, a.Artist_id, ar.Artist_name FROM Albums a LEFT JOIN Artists ar ON a.Artist_id = ar.Artist_id ORDER BY a.Album_id DESC");
        $stmt->execute();
        $result = $stmt->get_result(); 
        echo json_encode(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

// ==================== 2. CREATE ====================
if ($action === 'create') {
    $title = $_POST['album_title'] ?? '';
    $release_date = !empty($_POST['release_date']) ? $_POST['release_date'] : null;
    $artist_id = !empty($_POST['artist_id']) ? $_POST['artist_id'] : null;

    try {
        $cover_url = handleImageUpload('cover_image');

        $stmt = $conn->prepare("INSERT INTO Albums (Album_title, Release_date, Cover_image_url, Artist_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $release_date, $cover_url, $artist_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thêm album thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ==================== 3. UPDATE ====================
if ($action === 'update') {
    $id = $_POST['album_id'] ?? '';
    $title = $_POST['album_title'] ?? '';
    $release_date = !empty($_POST['release_date']) ? $_POST['release_date'] : null;
    $artist_id = !empty($_POST['artist_id']) ? $_POST['artist_id'] : null;

    try {
        $new_cover_url = handleImageUpload('cover_image');

        if ($new_cover_url) {
            $stmt = $conn->prepare("UPDATE Albums SET Album_title=?, Release_date=?, Cover_image_url=?, Artist_id=? WHERE Album_id=?");
            $stmt->bind_param("sssii", $title, $release_date, $new_cover_url, $artist_id, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Albums SET Album_title=?, Release_date=?, Artist_id=? WHERE Album_id=?");
            $stmt->bind_param("ssii", $title, $release_date, $artist_id, $id);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ==================== 4. DELETE ====================
if ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    try {
        $stmt = $conn->prepare("DELETE FROM Albums WHERE Album_id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Xóa album thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa (album đang chứa bài hát).']);
    }
    exit;
}
?>