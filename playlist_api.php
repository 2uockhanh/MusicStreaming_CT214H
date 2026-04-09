<?php
session_start();
header('Content-Type: application/json');
require 'db-connect.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để sử dụng tính năng này.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// ==================== 1. TẠO PLAYLIST ====================
if ($action === 'create') {
    $name = trim($_POST['playlist_name'] ?? '');
    $is_public = isset($_POST['is_public']) ? (int)$_POST['is_public'] : 1;

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Tên playlist không được để trống!']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO Playlists (User_id, Playlist_name, is_public) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $name, $is_public);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Tạo playlist thành công!', 'playlist_id' => $stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}

// ==================== 2. LẤY DANH SÁCH PLAYLIST CỦA USER ====================
if ($action === 'read') {
    try {
        $stmt = $conn->prepare("SELECT Playlist_id, Playlist_name, is_public FROM Playlists WHERE User_id = ? ORDER BY Playlist_id DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}

// ==================== 3. THÊM BÀI HÁT VÀO PLAYLIST ====================
if ($action === 'add_song') {
    $playlist_id = $_POST['playlist_id'] ?? '';
    $song_id = $_POST['song_id'] ?? '';

    if (!$playlist_id || !$song_id) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin playlist hoặc bài hát.']);
        exit;
    }

    try {
        // Thêm vào playlist_Song (Dùng INSERT IGNORE hoặc kiểm tra trước để tránh lỗi nếu bài hát đã tồn tại)
        $stmt = $conn->prepare("INSERT IGNORE INTO playlist_Song (Playlist_id, Song_id, Added_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $playlist_id, $song_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Đã thêm bài hát vào playlist!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi (Có thể bài hát đã nằm trong Playlist): ' . $e->getMessage()]);
    }
    exit;
}
?>