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
<?php include '../includes/header.php'; ?>
    <div class="auth-container">
        <h2> Sign up</h2>
    <form method="POST" action="signup.php">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="text" name="email" required>
            </div>
            <button type="submit" name="btn_signup" class="btn-submit">Đăng ký</button>
        </form>
        </form>
         <div class="auth-links">
        <p>Already have an account? <a href="login.php"><u>Log in</u></a></p>
    </div>
    </div>
