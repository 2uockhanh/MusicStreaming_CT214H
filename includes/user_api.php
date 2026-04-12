<?php
header('Content-Type: application/json');
require 'db-connect.php'; 

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Load danh sách bài hát và chức năng tìm kiếm tài khoản 
if ($action === 'read') {

    $search = $_POST['search'] ?? ''; 
    try {
        if ($search !== '') {
            $searchParam = "%" . $search . "%";
            $stmt = $conn->prepare("SELECT User_id, User_name, Email, Role FROM Users WHERE User_name LIKE ? OR Email LIKE ? ORDER BY User_id DESC");
            $stmt->bind_param("ss", $searchParam, $searchParam);
        } else {
            $stmt = $conn->prepare("SELECT User_id, User_name, Email, Role FROM Users ORDER BY User_id DESC");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode(['success' => true, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, "message" => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}
if ($action === 'create') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'user';

    try {
        $sql = "INSERT INTO Users (User_name, Email, Role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Lỗi SQL: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("sss", $username, $email, $role);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thêm tài khoản thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi thêm: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống (có thể trùng Email): ' . $e->getMessage()]);
    }
    exit;
}
 // update users

if ($action === 'update') {
    $id = $_POST['user_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'user';

    try {
        $stmt = $conn->prepare("UPDATE Users SET User_name = ?, Email = ?, Role = ? WHERE User_id = ?");
        $stmt->bind_param("sssi", $username, $email, $role, $id); 
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
}
if ($action === 'delete') {
    $id = $_POST['id'] ?? '';

    try {
        $stmt = $conn->prepare("DELETE FROM Users WHERE User_id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Xóa tài khoản thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa user này (có thể do đang vướng khóa ngoại ở bảng Songs/Playlists).']);
    }
}


?>