<?php
session_start(); // Bắt buộc để lưu trạng thái đăng nhập
require_once './includes/db-connect.php'; // Nhúng file kết nối DB
$error = "";


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
                header("Location: music-streaming-admin.php");
                exit();
            }
            
            // 4. Chuyển hướng về trang chủ
            header("Location: music-streaming-home.php");
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
<html lang="en" theme="dark">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>eMuzik - Log In</title>
	<link rel="stylesheet" href='./css/welcome-style.css'>
</head>
<body>
	<form method="POST" action="">
		
	</form>
	<div id="welcome" style="background-color: rgba(1,1,1,0.6);">
		<div style="text-align: right; margin-right: 10px; margin-top: 10px; ">
			<button id="darkModeToggle" class="darkModeToggle">🌙 DARK</button>
		</div>
		<section>
			<div class="lg">
				<img class="logo" src="./img/logo.png">
			</div>
			<form method="POST" action="">
				<div class="login" style="height: 350px;">
				<h3>LOG IN</h3>
				<div class="login_form">
					<table>
						<tr>
							<th>Username </th>
							<td><input type="text" id="input_username" name="username"></td>
						</tr>
						<tr>
							<th style="margin-right: 20px;">Password </th>
							<td><input type="password" id="input_password" name="password"></td>
						</tr>
					</table>
				</div>
				<div>
					<button type="submit" name="btn_login" class="btn">LOG IN</button>
				</div>
			
			</form>
				<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
				<div class="nav_form">
					<table>
						<tr>
							<td>Not have an account? </td>
							<td>
								<a href="music-streaming-signup.php">Sign up now</a>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</section>
	</div>
	

	<!-- <script src="/js/music-streaming.js"></script> -->
	 <script>
	var bd = document.body;
	var btn = document.getElementById("darkModeToggle");
	btn.addEventListener("click", changeModeToggle);
	function changeModeToggle() {	
		bd.classList.toggle("dark-body");
		btn.classList.toggle("dark-button");
		if (btn.classList.contains("dark-button")) {
			btn.textContent = "☀️ LIGHT";
			btn.classList.add("light-button");
			bd.classList.add("light-body");
		} else {
			btn.textContent = "🌙 DARK";
			btn.classList.remove("light-button");
			bd.classList.remove("light-body");
	}
};

	 </script>
</body>
</html>