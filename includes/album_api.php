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
?>