<?php
require './includes/db-connect.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (isset($_POST['btn_signup'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu
    // Dùng prepared statement để lưu vào CSDL
    $sql = "INSERT INTO users (user_name, email, password, role) VALUES (?, ?, ?, 'user')";
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
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>eMusik - Sign Up</title>
	<link rel="stylesheet" href='/css/welcome-style.css'>
</head>
<body>
	<div id="welcome" style="background-color: rgba(1,1,1,0.6);">
		<div style="text-align: right; margin-right: 10px; margin-top: 10px;">
			<button id="darkModeToggle" class="darkModeToggle">🌙 DARK</button>
		</div>
		<section>
			<div class="lg">
				<img class="logo" src="./img/logo.png">
			</div>
			<div class="signup">
				<h3>SIGN UP</h3>
				<div class="signup_form">
					<table>
						<tr>
							<th>Username </th>
							<td><input type="text" id="input_username" name="input_username"></td>
						</tr>
						<tr>
							<th>Password </th>
							<td><input type="text" id="input_password" name="input_password"></td>
						</tr>
						<tr>
							<th>Email </th>
							<td><input type="text" id="input_password" name="input_password"></td>
						</tr>
					</table>
				</div>
				<button class="btn">SIGN UP</button>
				<div class="nav_form">
					<table>
						<tr>
							<td>Already have an account?</td>
							<td><a href="music-streaming-login.html">Log In Now.</a></td>
						</tr>
					</table>
				</div>
			</div>
		</section>
	</div>
	<script src="music-streaming.js"></script>
</body>
</html>