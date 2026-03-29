<?php
session_start();
require_once 'includes/db-connect.php';

// 1. Kiểm tra đăng nhập (Chống vào lậu)
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 2. Truy xuất thông tin user từ Database
// Giả sử bảng users của bạn có cột: username, email, avatar
$sql = "SELECT username, email, avatar FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Lỗi: Không tìm thấy người dùng!";
    exit();
}
?>
<form method="POST" action="" enctype="multipart/form-data">
    <div class="form-fields">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Nhập địa chỉ email">
        </div>
        
        <div class="form-group">
            <label for="password">Change Password</label>
            <input type="password" name="new_password" id="password" placeholder="Nhập mật khẩu mới (bỏ trống nếu không đổi)">
        </div>

        <div style="text-align: right; margin-top: 10px;">
            <button type="submit" name="btn_update_profile" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Lưu Thay Đổi
            </button>
        </div>
    </div>
</form>