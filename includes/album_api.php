<?php
header('Content-Type: application/json');
require 'db-connect.php';

define('COVER_UPLOAD_DIR', '../uploads/avatars/');

function handleCoverUpload($file_input) {
    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$file_input];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        throw new Exception('Chỉ chấp nhận file ảnh dạng JPG, JPEG hoặc PNG.');
    }

    $newName = uniqid('album_cover_', true) . '.' . $ext;
    $targetPath = COVER_UPLOAD_DIR . $newName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/avatars/' . $newName;
    }

    throw new Exception('Upload album cover thất bại.');
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Lấy danh sách album
if ($action === 'read') {
    $search = $_POST['search'] ?? '';
    try {
        if ($search !== '') {
            $searchParam = '%' . $search . '%';
            $stmt = $conn->prepare("
                SELECT a.Album_id, a.Album_title, a.Release_date, a.Cover_image_url, a.Artist_id, ar.Artist_name
                FROM Albums a
                LEFT JOIN Artists ar ON a.Artist_id = ar.Artist_id
                WHERE a.Album_title LIKE ? OR ar.Artist_name LIKE ?
                ORDER BY a.Album_id DESC
            ");
            $stmt->bind_param('ss', $searchParam, $searchParam);
        } else {
            $stmt = $conn->prepare("
                SELECT a.Album_id, a.Album_title, a.Release_date, a.Cover_image_url, a.Artist_id, ar.Artist_name
                FROM Albums a
                LEFT JOIN Artists ar ON a.Artist_id = ar.Artist_id
                ORDER BY a.Album_id DESC
            ");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

// Lấy danh sách artist cho dropdown
if ($action === 'get_artists') {
    try {
        $stmt = $conn->prepare("SELECT Artist_id, Artist_name FROM Artists ORDER BY Artist_name ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

// Lấy danh sách album cho dropdown
if ($action === 'get_albums') {
    try {
        $stmt = $conn->prepare("SELECT Album_id, Album_title, Artist_id FROM Albums ORDER BY Album_title ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

// Thêm album mới
if ($action === 'create') {
    $title = trim($_POST['album_title'] ?? '');
    $release_date = trim($_POST['release_date'] ?? '');
    $artist_id = intval($_POST['artist_id'] ?? 0);
    $cover_url = trim($_POST['cover_image_url'] ?? '');

    try {
        if (!$title || !$artist_id) {
            echo json_encode(['success' => false, 'message' => 'Album title và Artist phải được chọn!']);
            exit;
        }

        // Mặc định release_date là ngày hôm nay
        if (empty($release_date)) {
            $release_date = date('Y-m-d');
        }

        // Kiểm tra Artist tồn tại
        $check = $conn->prepare("SELECT Artist_id FROM Artists WHERE Artist_id = ?");
        $check->bind_param('i', $artist_id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Artist không tồn tại!']);
            exit;
        }

        // Upload cover nếu có
        $uploadedCover = handleCoverUpload('cover');
        if ($uploadedCover) {
            $cover_url = $uploadedCover;
        }

        // Lấy ID tiếp theo
        $stmt = $conn->prepare("SELECT COALESCE(MAX(Album_id), 0) + 1 AS next_id FROM Albums");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $nextId = $result['next_id'] ?? 1;

        // Insert album
        $insert = $conn->prepare("INSERT INTO Albums (Album_id, Album_title, Release_date, Cover_image_url, Artist_id) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param('isssi', $nextId, $title, $release_date, $cover_url, $artist_id);
        
        if ($insert->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thêm album thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Cập nhật album
if ($action === 'update') {
    $id = intval($_POST['album_id'] ?? 0);
    $title = trim($_POST['album_title'] ?? '');
    $release_date = trim($_POST['release_date'] ?? '');
    $artist_id = intval($_POST['artist_id'] ?? 0);
    $cover_url = trim($_POST['cover_image_url'] ?? '');

    try {
        if (!$title || !$artist_id) {
            echo json_encode(['success' => false, 'message' => 'Album title và Artist phải được chọn!']);
            exit;
        }

        // Mặc định release_date là ngày hôm nay nếu không nhập
        if (empty($release_date)) {
            $release_date = date('Y-m-d');
        }

        // Kiểm tra Artist tồn tại
        $check = $conn->prepare("SELECT Artist_id FROM Artists WHERE Artist_id = ?");
        $check->bind_param('i', $artist_id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Artist không tồn tại!']);
            exit;
        }

        // Upload cover nếu có
        $uploadedCover = handleCoverUpload('cover');
        if ($uploadedCover) {
            $cover_url = $uploadedCover;
        }

        // Update album
        if ($cover_url !== '') {
            $stmt = $conn->prepare("UPDATE Albums SET Album_title = ?, Release_date = ?, Cover_image_url = ?, Artist_id = ? WHERE Album_id = ?");
            $stmt->bind_param('sssii', $title, $release_date, $cover_url, $artist_id, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Albums SET Album_title = ?, Release_date = ?, Artist_id = ? WHERE Album_id = ?");
            $stmt->bind_param('ssii', $title, $release_date, $artist_id, $id);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật album thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Xóa album
if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    try {
        $stmt = $conn->prepare("DELETE FROM Albums WHERE Album_id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Xóa album thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa album này.']);
    }
    exit;
}

?>