<?php
    session_start();
    include 'db-connect.php';

    if(isset($_SESSION['user_id']) && isset($_FILES['avatar'])) {
        $userId = $_SESSION['user_id'];
        $target_dir = "../uploads/avatars/";

        $fileName = $userId . "_" . time() . "_" . basename($_FILES["avatar"]["name"]);
        $target_file_path = $target_dir . $fileName;

        $fileType = pathinfo($target_file_path, PATHINFO_EXTENSION);
        $allowedTypes = ['jpg', 'jpeg', 'png'];

        if(in_array(strtolower($fileType), $allowedTypes)) {
            if(move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file_path)) {
                $dbPath = "uploads/avatars/" . $fileName;
                $sql = "UPDATE users SET User_avatar_url = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $dbPath, $userId);
                if($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Avatar updated successfully!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database update failed!']);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'File upload failed!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file type!']);
        }
    }
?>