<?php
require './includes/db-connect.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = "";

if (isset($_POST['btn_signup'])) {
    $username = trim($_POST['input_username']);
    $password = trim($_POST['input_password']);
    $email = trim($_POST['input_email']);
    
    // 1. KIỂM TRA EMAIL ĐÃ TỒN TẠI HAY CHƯA
    $stmt_check = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        $error = "This email address is already in use. Please try a different one!";
    } else {
        // 2. NẾU CHƯA TỒN TẠI, TIẾN HÀNH ĐĂNG KÝ
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu
        // Dùng prepared statement để lưu vào CSDL
        $sql = "INSERT INTO users (user_name, email, password, role) VALUES (?, ?, ?, 'user')";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
                $stmt->bind_param("sss", $username,$email, $hashed_password);
                if ($stmt->execute()) {
                    echo "<script>alert('Registration successful!'); window.location.href='music-streaming-login.php';</script>";
                } else {
                    $error = "Error: The username or email address already exists!";
                }
                $stmt->close();
            }
    }
    $stmt_check->close();
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>eMusik - Sign Up</title>
	<link rel="stylesheet" href='./css/welcome-style.css'>
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
			<form method="POST" action="">
			<div class="signup">
				<h3>SIGN UP</h3>
				<div class="signup_form">
					<table>
						<tr>
							<th>Username </th>
							<td><input type="text" id="input_username" name="input_username" required></td>
						</tr>
						<tr>
							<th>Password </th>
							<td><input type="password" id="input_password" name="input_password" required></td>
						</tr>
						<tr>
							<th>Email </th>
							<td><input type="email" id="input_email" name="input_email" required></td>
						</tr>
					</table>
				</div>
				<button type="submit" class="btn" name="btn_signup">SIGN UP</button>
				<?php if (!empty($error)) echo "<p style='color:red; text-align:center; font-family: Roboto, sans-serif; margin-top: 10px;'>$error</p>"; ?>
				<div class="nav_form">
					<table>
						<tr>
							<td>Already have an account?</td>
							<td><a href="music-streaming-login.php">Log In Now.</a></td>
						</tr>
					</table>
				</div>
			</div>
			</form>
		</section>
	</div>
	<script src="./js/music-streaming-mode-toggle.js"></script>
</body>
</html>
