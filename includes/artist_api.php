<?php
header('Content-Type: application/json');
require 'db-connect.php';

define('AVATAR_UPLOAD_DIR', '../uploads/avatars/');

function handleAvatarUpload($file_input) {
    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$file_input];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        throw new Exception('Chỉ chấp nhận file ảnh dạng JPG, JPEG hoặc PNG.');
    }

    $newName = uniqid('artist_', true) . '.' . $ext;
    $targetPath = AVATAR_UPLOAD_DIR . $newName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/avatars/' . $newName;
    }

    throw new Exception('Upload avatar thất bại.');
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'read') {
    $search = $_POST['search'] ?? '';
    try {
        if ($search !== '') {
            $searchParam = '%' . $search . '%';
            $stmt = $conn->prepare("SELECT Artist_id, Artist_name, Biography, Avatar_url FROM Artists WHERE Artist_name LIKE ? OR Biography LIKE ? ORDER BY Artist_id DESC");
            $stmt->bind_param('ss', $searchParam, $searchParam);
        } else {
            $stmt = $conn->prepare("SELECT Artist_id, Artist_name, Biography, Avatar_url FROM Artists ORDER BY Artist_id DESC");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'create') {
    $name = trim($_POST['artist_name'] ?? '');
    $biography = trim($_POST['biography'] ?? '');
    $avatar_url = trim($_POST['avatar_url'] ?? '');

    try {
        $uploadedAvatar = handleAvatarUpload('avatar');
        if ($uploadedAvatar) {
            $avatar_url = $uploadedAvatar;
        }

        $stmt = $conn->prepare("SELECT COALESCE(MAX(Artist_id), 0) + 1 AS next_id FROM Artists");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $nextId = $result['next_id'] ?? 1;

        $insert = $conn->prepare("INSERT INTO Artists (Artist_id, Artist_name, Biography, Avatar_url) VALUES (?, ?, ?, ?)");
        $insert->bind_param('isss', $nextId, $name, $biography, $avatar_url);
        if ($insert->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thêm artist thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'update') {
    $id = $_POST['artist_id'] ?? '';
    $name = trim($_POST['artist_name'] ?? '');
    $biography = trim($_POST['biography'] ?? '');
    $avatar_url = trim($_POST['avatar_url'] ?? '');

    try {
        $uploadedAvatar = handleAvatarUpload('avatar');
        if ($uploadedAvatar) {
            $avatar_url = $uploadedAvatar;
        }

        if ($avatar_url !== '') {
            $stmt = $conn->prepare("UPDATE Artists SET Artist_name = ?, Biography = ?, Avatar_url = ? WHERE Artist_id = ?");
            $stmt->bind_param('sssi', $name, $biography, $avatar_url, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Artists SET Artist_name = ?, Biography = ? WHERE Artist_id = ?");
            $stmt->bind_param('ssi', $name, $biography, $id);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật artist thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    try {
        $stmt = $conn->prepare("DELETE FROM Artists WHERE Artist_id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Xóa artist thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa artist này.']);
    }
    exit;
}

?>