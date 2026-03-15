<?php
$servername = "localhost";
$username = "root"; // User mặc định của XAMPP
$password = "";     // Mật khẩu mặc định để trống
$dbname = "music_web"; // Tên database bạn tạo trong phpMyAdmin

// Khởi tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối DB thất bại: " . $conn->connect_error);
}

// Cài đặt charset để lưu tiếng Việt có dấu
$conn->set_charset("utf8mb4");
?>