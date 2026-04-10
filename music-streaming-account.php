<?php
session_start();
include 'includes/db-connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: music-streaming-home.php");
    exit();
}

$avatarUrl = 'img/avatar.jpg';
$email = '';
$username = '';
$userRole = '';

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT User_name, email, User_avatar_url, Role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $email = $row['email'];
    $userRole = $row['Role'];
    $username = $row['User_name'];
    $_SESSION['user_name'] = $row['User_name'];
    if (!empty($row['User_avatar_url'])) {
        $avatarUrl = $row['User_avatar_url'];
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eMuzik - My Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/account_style.css">
    <link rel="stylesheet" href="css/home-style.css">
</head>

<body class="home">
    <div style="display: flex;">
        <div>
            <nav class="navbar">
                <img class="logo" src="./img/logo.png" alt="eMusik"></img>
                <div class="nav">
                    <table>
                        <tr>
                            <button class="nav_button" onclick="window.location.href='music-streaming-home.php'">
                                <img class="nav_logo" src="./img/home.png"> Home
                            </button>
                        </tr>
                        <tr>
                            <button class="nav_button" onclick="window.location.href='music-streaming-library.php'">
                                <img class="nav_logo" src="./img/library.png"> Library
                            </button>
                        </tr>
                        <tr>
                            <button class="nav_button">
                                <img class="nav_logo" src="./img/favourite.png"> Favourite
                            </button>
                        </tr>
                        <tr>
                            <button class="nav_button">
                                <img class="nav_logo" src="./img/account.png"> Account
                            </button>
                        </tr>
                    </table>
                </div>
            </nav>
        </div>
        <div class="main-content">
            <div class="header">
                <ul style="display: grid; grid-template-columns: 90% 10%;">
                    <li>
                        <!-- <div class="search" style="text-align: center;">
                            <input type="text" id="search_info" name="search_info" placeholder="Search songs, artists..."></input>
                        </div> -->
                    </li>
                    <li>
                        <div class="avatar">
                            <div class="user-icon-wrapper">
                                <img style="width: 40px; height: 40px; border-radius: 30px;" src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar"></img>

                                <div class="dropdown-menu">
                                    <a href="music-streaming-account.php">My Account</a>
                                    <?php if ($userRole === 'admin'): ?>
                                    <a href="music-streaming-admin.php">Admin Dashboard</a>
                                    <?php endif; ?>
                                    <a href="#">My Favourite</a>
                                    <a href="#">Theme Mode</a>
                                    <a href="auth/logout.php" onclick="return confirm('Are you sure you want to log out?')">Log Out</a>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <section class="article_body">
                <h1>MY ACCOUNT</h1>

                <div class="profile-container">
                    <div class="avatar-section">
                        <div class="avatar-circle" id="avatar-preview">
                            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        </div>
                        <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display: none;">
                        <button class="btn-avatar" onclick="document.getElementById('avatar-input').click();">Change Avatar</button>
                    </div>

                    <div class="form-section">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                           <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <div class="form-group">
                            <label>Change<br>Password</label>
                            <input type="password" name="password">
                        </div>
                        <div class="form-group" style="justify-content: flex-end; margin-top: 10px;">
                            <button type="submit" class="btn-confirm">
                                <i class="fas fa-check-circle"></i> Confirm Change
                            </button>
                            <button type="button" class="btn-cancel">
                                <i class="fas fa-cancel"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <!--<button class="btn-action btn-library"><i class="fas fa-music"></i> MY LIBRARY</button>-->
                    <!-- <button class="btn-action btn-upload"><i class="fas fa-upload"></i> UPLOAD SONG</button> -->
                </div>
            </section>


            <footer class="footer">
                <div class="footer-content">
                    <div class="footer-section">
                        <h4>About Us</h4>
                        <ul>
                            <li><a href="#Introduce">Introduce</a></li>
                            <li><a href="#Term">Term</a></li>
                            <li><a href="#Privacy">Privacy</a></li>
                            <li><a href="#PolicySafety">Policy And Safety</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h4>Useful Link</h4>
                        <ul>
                            <li><a href="#Introduce">My Account</a></li>
                            <li><a href="#Term">Library</a></li>
                            <li><a href="#Privacy">Favourite</a></li>
                            <li><a href="#PolicySafety">Import Music</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h4>Social Links</h4>
                        <ul>
                            <li><a href="#Facebook">Facebook</a></li>
                            <li><a href="#TikTok">TikTok</a></li>
                            <li><a href="#Threads">Threads</a></li>
                            <li><a href="#Instagram">Instagram</a></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2026 eMusik. All rights reserved.</p>
                </div>
            </footer>
            </main>
            <script src="js/change-avatar.js"></script>
            <script src="js/change-info.js"></script>
</body>

</html>