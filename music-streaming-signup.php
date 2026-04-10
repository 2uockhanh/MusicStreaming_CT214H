<?php
session_start();
require './includes/db-connect.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = "";

if (isset($_POST['btn_signup'])) {
    $username = trim($_POST['input_username']);
    $password = trim($_POST['input_password']);
    $email = trim($_POST['input_email']);

    // Validation
    if (empty($username) || empty($password) || empty($email)) {
        $error = "Please fill in all fields!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password has to be at least 6 characters long!";
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT user_id FROM users WHERE user_name = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $error = "Username or email already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (user_name, email, password, role) VALUES (?, ?, ?, 'user')";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                if ($stmt->execute()) {
                    echo "<script>alert('Sign up successful!'); window.location.href='music-streaming-login.php';</script>";
                    exit();
                } else {
                    $error = "Error occurred while signing up, please try again!";
                }
                $stmt->close();
            } else {
                $error = "Error preparing the SQL statement!";
            }
        }
        $check_stmt->close();
    }
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
			<div class="signup">
				<h3>SIGN UP</h3>
				<?php if (!empty($error)) { echo "<p style='color: red;'>$error</p>"; } ?>
				<form method="post">
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
					<button class="btn" name="btn_signup" type="submit">SIGN UP</button>
				</form>
				<div class="nav_form">
					<table>
						<tr>
							<td>Already have an account?</td>
							<td><a href="music-streaming-login.php">Log In Now.</a></td>
						</tr>
					</table>
				</div>
			</div>
		</section>
	</div>
	<script src="./js/music-streaming-mode-toggle.js"></script>
</body>
</html>