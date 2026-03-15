<?php
require_once '../includes/db-connect.php';
if (isset($_POST['btn_signup'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu
    // Dùng prepared statement để lưu vào CSDL
    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
            $stmt->bind_param("sss", $username,$email, $hashed_password);
            if ($stmt->execute()) {
                echo "<script>alert('Đăng ký thành công!'); window.location.href='login.php';</script>";
            } else {
                $error = "Lỗi: Tên đăng nhập đã tồn tại!";
            }
            $stmt->close();
        }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="POST" action="signup.php">
        <label >Username</label><br>
        <input type="text" name="username" required><br>
        <label>Password</label><br>
        <input type="text" name="password" required><br>
        <label>Email</label><br>
        <input type="text" name="email" required><br>
        <button type="submit" name="btn_signup">Sign up</button>
    </form>
</body>
</html>