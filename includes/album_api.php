<?php
header('Content-Type: application/json');
require 'db-connect.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Lấy danh sách Album
if ($action === 'read') {
    try {
        $stmt = $conn->prepare("SELECT Album_id, Album_title, Release_date, Cover_image_url, Artist_id FROM Albums ORDER BY Album_id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

// Thêm Album mới
if ($action === 'create') {
    $title = $_POST['album_title'] ?? '';
    $release_date = $_POST['release_date'] ?? date('Y-m-d');
    $artist_id = $_POST['artist_id'] ?? null;
    
    // Nếu hệ thống có Form Upload ảnh Cover cho Album, có thể lấy URL ảnh tại đây:
    $cover_image_url = $_POST['cover_image_url'] ?? 'https://res.cloudinary.com/dmmfauvvu/image/upload/v1775810472/default_album.jpg'; 

    try {
        $stmt = $conn->prepare("INSERT INTO Albums (Album_title, Release_date, Cover_image_url, Artist_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $release_date, $cover_image_url, $artist_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thêm Album thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Cập nhật Album
if ($action === 'update') {
    $id = $_POST['album_id'] ?? '';
    $title = $_POST['album_title'] ?? '';
    $release_date = $_POST['release_date'] ?? date('Y-m-d');
    $artist_id = $_POST['artist_id'] ?? null;
    
    // Lấy URL ảnh cover cũ
    $cover_image_url = $_POST['cover_image_url'] ?? ''; 

    try {
        // Xử lý upload file ảnh mới (nếu admin có chọn file)
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($ext, $allowed)) {
                $uploadDir = '../uploads/albums/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $newName = uniqid('album_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['cover']['tmp_name'], $uploadDir . $newName)) {
                    $cover_image_url = 'uploads/albums/' . $newName;
                }
            }
        }

        $stmt = $conn->prepare("UPDATE Albums SET Album_title = ?, Release_date = ?, Cover_image_url = ?, Artist_id = ? WHERE Album_id = ?");
        $stmt->bind_param("sssii", $title, $release_date, $cover_image_url, $artist_id, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật Album thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Xóa Album
if ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    try {
        $stmt = $conn->prepare("DELETE FROM Albums WHERE Album_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Xóa Album thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa (có thể Album này đang chứa bài hát).']);
    }
    exit;
}
?>