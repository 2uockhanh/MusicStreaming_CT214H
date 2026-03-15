<?php
session_start(); // Bắt buộc để lưu trạng thái đăng nhập
require_once '../includes/db-connect.php'; // Nhúng file kết nối DB

$error = "";

// Kiểm tra xem người dùng có bấm nút Đăng nhập chưa
if (isset($_POST['btn_login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 1. Tìm user trong database bằng Prepared Statement (Chống SQL Injection)
    $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // 2. Nếu tìm thấy user, tiếp tục kiểm tra mật khẩu
    if ($row = $result->fetch_assoc()) {
        // Hàm password_verify sẽ đối chiếu mật khẩu nhập vào với mã băm trong DB
        if (password_verify($password, $row['password'])) {
            // 3. Đăng nhập thành công -> Lưu thông tin vào Session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            // 4. Chuyển hướng về trang chủ
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Sai mật khẩu!";
        }
    } else {
        $error = "Tài khoản không tồn tại!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
   
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; margin-top: 50px; }
        .login-form { border: 1px solid #ccc; padding: 20px; border-radius: 5px; width: 300px; }
        .login-form input { width: 90%; padding: 8px; margin: 10px 0; }
        .login-form button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        .error { color: red; font-size: 14px; }
    </style>
</head>
<body>

<div class="login-form">
    <h2>Đăng nhập</h2>
    
    <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>

    <form method="POST" action="login.php">
        <label>Tên đăng nhập:</label>
        <input type="text" name="username" required>
        
        <label>Mật khẩu:</label>
        <input type="password" name="password" required>
        
        <button type="submit" name="btn_login">Đăng nhập</button>
    </form>
    
    <p style="text-align: center; font-size: 14px;">Chưa có tài khoản? <a href="signup.php">Đăng ký</a></p>
</div>

</body>
</html>