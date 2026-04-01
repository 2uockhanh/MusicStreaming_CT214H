<?php
session_start(); // Bắt buộc để lưu trạng thái đăng nhập
require_once '../includes/db-connect.php'; // Nhúng file kết nối DB

$error = "";

// Kiểm tra xem người dùng có bấm nút Đăng nhập chưa
if (isset($_POST['btn_login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 1. Tìm user trong database bằng Prepared Statement (Chống SQL Injection)
    $sql = "SELECT user_id, user_name, password, role FROM users WHERE user_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // 2. Nếu tìm thấy user, tiếp tục kiểm tra mật khẩu
    if ($row = $result->fetch_assoc()) {
        // Hàm password_verify sẽ đối chiếu mật khẩu nhập vào với mã băm trong DB
        if (password_verify($password, $row['password'])) {
            // 3. Đăng nhập thành công -> Lưu thông tin vào Session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['role'] = $row['role'];

            if($row['role'] === 'admin') {
                header("Location: ../admin/index.php");
                exit();
            }
            
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

<?php include '../includes/header.php'; ?>

<body>
    <div class="auth-container">
    <h2>Log in</h2>
    <?php if (!empty($error)) echo "<div class='error-msg'>$error</div>"; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" required>
        </div>
        
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <button type="submit" name="btn_login" class="btn-submit">Đăng nhập</button>
    </form>
    
    <div class="auth-links">
        <p>Not have an account? <a href="signup.php">Sign up now</a></p>
    </div>
</div>
<script src="../js/login.js" defer></script>

</body>
