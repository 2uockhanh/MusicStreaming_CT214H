<?php
header('Content-Type: application/json');
require 'db-connect.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
define('MUSIC_UPLOAD_DIR', '../uploads/music/');

// --- Hàm xử lý Upload File Nhạc ---
function handleMusicUpload($file_input) {
    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$file_input];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['mp3', 'wav', 'ogg', 'm4a'];

    if (!in_array($ext, $allowed)) {
        throw new Exception("Chỉ chấp nhận file định dạng nhạc (mp3, wav, ogg, m4a).");
    }

    // Tạo tên file ngẫu nhiên để không trùng
    $newName = uniqid('music_', true) . '.' . $ext;
    
    if (move_uploaded_file($file_tmp = $file['tmp_name'], MUSIC_UPLOAD_DIR . $newName)) {
        return 'uploads/music/' . $newName; 
    }
    return null;
}

// hiển thị danh sách các bài hát có trong csdl => đặt ID giảm dần để biết được bài nào được thêm vào gần đây nhất

if ($action === 'read') {
    $search = $_GET['search'] ?? ''; // Lấy từ khóa tìm kiếm
    try {
        if ($search !== '') {
            // Nếu có từ khóa -> Tìm theo tên bài hát
            $searchParam = "%" . $search . "%";
            $stmt = $conn->prepare("SELECT Song_id, Song_title, File_url, Lyric, Album_id, View_count FROM Songs WHERE Song_title LIKE ? ORDER BY Song_id DESC");
            $stmt->bind_param("s", $searchParam);
        } else {
            // Nếu không có từ khóa -> Lấy tất cả
            $stmt = $conn->prepare("SELECT Song_id, Song_title, File_url, Lyric, Album_id, View_count FROM Songs ORDER BY Song_id DESC");
        }
        
        $stmt->execute();
        $result = $stmt->get_result(); 
        echo json_encode(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

// Thêm bài hát vào CSDL
if ($action === 'create') {
    $title = $_POST['song_title'] ?? '';
    $lyric = $_POST['lyric'] ?? '';
    $views = $_POST['view_count'] ?? 0;
    $album_id = !empty($_POST['album_id']) ? $_POST['album_id'] : null;

    try {
        $file_url = handleMusicUpload('music_file');

        
        if (!$file_url) {
            echo json_encode(['success' => false, 'message' => 'Bạn phải upload file nhạc!']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO Songs (Song_title, File_url, Lyric, View_count, Album_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $title, $file_url, $lyric, $views, $album_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thêm bài hát thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Cập nhật lại thông tin bài hát
if ($action === 'update') {
    $id = $_POST['song_id'] ?? '';
    $title = $_POST['song_title'] ?? '';
    $lyric = $_POST['lyric'] ?? '';
    $views = $_POST['view_count'] ?? 0;
    $album_id = !empty($_POST['album_id']) ? $_POST['album_id'] : null;

    try {
        $new_file_url = handleMusicUpload('music_file');

        if ($new_file_url) {
            $stmt = $conn->prepare("UPDATE Songs SET Song_title=?, File_url=?, Lyric=?, View_count=?, Album_id=? WHERE Song_id=?");
            $stmt->bind_param("sssiii", $title, $new_file_url, $lyric, $views, $album_id, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Songs SET Song_title=?, Lyric=?, View_count=?, Album_id=? WHERE Song_id=?");
            $stmt->bind_param("ssiii", $title, $lyric, $views, $album_id, $id);
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

// Xóa bài hát khỏi CSDL
if ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    try {
        $stmt = $conn->prepare("DELETE FROM Songs WHERE Song_id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Xóa bài hát thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa (bài hát đang nằm trong Playlist).']);
    }
    exit;
}

?>


