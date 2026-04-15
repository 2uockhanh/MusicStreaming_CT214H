<?php
session_start();
require_once './includes/db-connect.php';

// 1. Yêu cầu đăng nhập để xem thư viện
if (!isset($_SESSION['user_id'])) {
    header("Location: music-streaming-login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Lấy Avatar người dùng hiển thị trên header
$avatarUrl = './img/avatar.jpg';
$stmt = $conn->prepare("SELECT User_avatar_url FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    if (!empty($row['User_avatar_url'])) {
        $avatarUrl = $row['User_avatar_url'];
    }
}
$stmt->close();

// 3. Xử lý chức năng Tạo Playlist mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_create_playlist'])) {
    $playlist_name = trim($_POST['playlist_name']);
    
    // Nhận giá trị is_public từ input ẩn được set bởi JS (0: Private, 1: Public)
    $is_public = (isset($_POST['is_public']) && $_POST['is_public'] === '1') ? 1 : 0;

    if (!empty($playlist_name)) {
        $stmt = $conn->prepare("INSERT INTO Playlists (User_id, Playlist_name, is_public) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $playlist_name, $is_public);
        $stmt->execute();
        
        // Tải lại trang để cập nhật danh sách ngay lập tức
        header("Location: music-streaming-library.php");
        exit();
    }
}

// 4. Truy vấn danh sách Playlist do User này tạo (Mới nhất xếp trên)
$sql_playlists = "SELECT * FROM Playlists WHERE User_id = ? ORDER BY Playlist_id DESC";
$stmt_playlists = $conn->prepare($sql_playlists);
$stmt_playlists->bind_param("i", $user_id);
$stmt_playlists->execute();
$playlists = $stmt_playlists->get_result()->fetch_all(MYSQLI_ASSOC);

// 5. Truy vấn danh sách Bài hát yêu thích (Favorites)
$sql_favorites = "SELECT s.*, a.Artist_name 
                  FROM Favorites f 
                  JOIN Songs s ON f.Song_id = s.Song_id 
                  LEFT JOIN Artists a ON s.Artist_id = a.Artist_id 
                  WHERE f.User_id = ? 
                  ORDER BY f.Song_id DESC";
$stmt_favorites = $conn->prepare($sql_favorites);
$stmt_favorites->bind_param("i", $user_id);
$stmt_favorites->execute();
$favorites = $stmt_favorites->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eMusik - Library</title> 
    <!-- <link rel="stylesheet" href='./css/library-style.css'> -->
    <link rel="stylesheet" href='./css/library-style.css?v=<?php echo time(); ?>'>
    <!-- <link rel="stylesheet" href='./css/home-style.css'> Để load đúng giao diện dropdown và nav -->
    <link rel="stylesheet" href='./css/home-style.css?v=<?php echo time(); ?>'>
    <link href='https://fonts.googleapis.com/css?family=Passero One' rel='stylesheet'>
</head>
<body>
    <div class="main-wrapper" style="display: flex;">
        <nav class="navbar">
            <button class="logo_nav" onclick="document.location='./music-streaming-home.php'">
                <h3 style="font-family: 'Passero One'; font-size: 48px; margin: 0px auto;">eMuzik</h3>
            </button>
            <div class="nav">
                <button class="nav_button" onclick="document.location='./music-streaming-home.php'">
                    <img class="nav_logo" src="./img/home.png"> Home
                </button>
                <button class="nav_button" onclick="document.location='./music-streaming-library.php'">
                    <img class="nav_logo" src="./img/library.png"> Library
                </button>
                <button class="nav_button" onclick="document.location='./music-streaming-favourite.php'">
                    <img class="nav_logo" src="./img/favourite.png"> Favourite
                </button>
            </div>
        </nav>
        <div class="home">
            <div class="header"> 
                <ul style="display: grid; grid-template-columns: 90% 10%; list-style-type: none;">
                    <li>
                        <div class="search" style="text-align: center; position: relative;">
                            <input type="text" id="search_info" name="search_info" placeholder="Search songs, artists..."></input>
                            <div id="search-results" style="position: absolute; top: 100%; left: 50%; transform: translateX(-50%); width: 100%; max-width: 400px; background: #181818; border-radius: 10px; margin-top: 5px; display: none; flex-direction: column; max-height: 350px; overflow-y: auto; z-index: 1001; box-shadow: 0 8px 16px rgba(0,0,0,0.8); text-align: left; border: 1px solid #333;">
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="avatar_dropdown" style="text-align: right; margin-right: 20px; margin-top: 20px; margin-bottom: 20px; width: 10%;">
                            <button class="dropbtn" id="dropbtn">
                                <img style="width: 40px; height: 40px; border-radius: 30px;" src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar"></img>
                            </button>
                            <div class="dropDownList" name="avatar" id="dropDown">
                                <button type="button" class="dropDownBtn" onclick="window.location.href='music-streaming-account.php'">My Account</button>
                                <button value="themeMode" id="themeMode" class="dropDownBtn">🌙 Theme Mode</button>
                                <button type="button" class="dropDownBtn" onclick="window.location.href='music-streaming-login.php'">Log Out</button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            
            <!-- PHẦN PLAYLIST CỦA TÔI -->
            <div class="show_your_playlist">
                <div class="show_your_playlist_head">
                    <h3>YOUR PLAYLIST</h3>
                    <a class="add_playlist_btn" id="add_playlist_btn" style="cursor: pointer;">
                        <img src="./img/add.png" style="width: 20px; height: 20px;"></img>
                    </a>
                </div>
                <div class="show_your_favourite_body">
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/left-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                    <div class="show_body_grid" style="color: white; font-family: Tahoma, sans-serif; justify-content: flex-start; gap: 20px;">
                        <?php if (count($playlists) > 0): ?>
                            <?php foreach ($playlists as $pl): ?>
                                <?php
                                    // Đếm số lượng bài hát trong playlist này
                                    $pl_id = $pl['Playlist_id'];
                                    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM playlist_Song WHERE Playlist_id = ?");
                                    $count_stmt->bind_param("i", $pl_id);
                                    $count_stmt->execute();
                                    $count_res = $count_stmt->get_result()->fetch_assoc();
                                    $song_count = $count_res['total'];
                                ?>
                                <button class="your_favourite_nav" onclick="document.location='music-streaming-playlist-info.php?id=<?php echo $pl['Playlist_id']; ?>'">
                                    <div class="your_favourite_nav_grid">
                                        <img src="<?php echo htmlspecialchars($pl['Playlist_avatar_url'] ?? './img/default-playlist-avatar.jpg'); ?>" alt="eMusik"></img>
                                        <div class="article_body">
                                            <h4><?php echo htmlspecialchars($pl['Playlist_name']); ?></h4> 
                                            <h4 style="font-weight: lighter;"><?php echo $song_count; ?> songs</h4>
                                        </div>
                                    </div>
                                </button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #b3b3b3; margin-left: 20px;">You don't have any playlist.</p>
                        <?php endif; ?>
                    </div>
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/right-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- PHẦN BÀI HÁT YÊU THÍCH -->
            <div class="show_your_favourite">
                <div class="show_your_favourite_head">
                    <h3>YOUR FAVOURITE SONG</h3>
                </div>
                <div class="show_your_favourite_body">
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/left-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                    <div class="show_body_grid" style="color: white; font-family: Roboto, sans-serif; justify-content: flex-start; gap: 20px;">
                        <?php if (count($favorites) > 0): ?>
                            <?php foreach ($favorites as $song): ?>
                                <button class="your_favourite_nav" onclick="document.location='music-streaming-song-info.php?id=<?php echo $song['Song_id']; ?>'">
                                    <div class="your_favourite_nav_grid">
                                        <img src="<?php echo htmlspecialchars($song['Song_image_url'] ?? './img/default-song.jpg'); ?>" alt="eMusik"></img>
                                        <div class="article_body">
                                            <h4><?php echo htmlspecialchars($song['Song_title']); ?></h4> 
                                            <h4 style="font-weight: lighter;"><?php echo htmlspecialchars($song['Artist_name'] ?? 'Unknown'); ?></h4>
                                        </div>
                                    </div>
                                </button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #b3b3b3; margin-left: 20px;">No favourite songs yet.</p>
                        <?php endif; ?>
                    </div>
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/right-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- FOOTER -->
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
                            <li><a href="./music-streaming-account.php">My Account</a></li>
                            <li><a href="./music-streaming-library.php">Library</a></li>
                            <li><a href="./music-streaming-favourite.html">Favourite</a></li>
                            <li><a href="./music-streaming-import-music.html">Import Music</a></li>
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
            
            <div style="height: 100px;"></div> <!-- Tránh bị player che -->
        </div>
    </div>

    <!-- POPUP TẠO PLAYLIST ĐÃ ĐƯỢC BỌC TRONG THẺ FORM ĐỂ GỬI POST LÊN PHP -->
    <div class="add_playlist_popup" id="add_playlist_popup" style="display: none;">
            <form method="POST" action="">
                <div class="popup_head" id="popup_head">
                    <div class="popup_header" id="popup_header">Create Playlist</div>
                    <button type="button" class="popup_close" id="popup_close">&#128936;</button>
                </div>
                <div style="grid-template-columns: 240px 330px; gap: 30px; display: grid;">
                    <div style="margin-left: 30px;">
                        <img src="./img/default-playlist-avatar.jpg" style="width: 200px; height: 200px; border: 3px solid black;">
                        <button type="button" style="font-family: Roboto, sans-serif; width: fit-content; height: fit-content; background-color: rgba(217, 217, 217, 1); color: black; font-size: medium; padding: 10px; font-weight: bold; border-radius: 20px; justify-content: center; text-align: center; margin-top: 10px; margin-left: 40px;">Change Avatar</button>
                    </div>
                    <div style="font-family: Roboto, sans-serif;">
                        <h4 style="margin-bottom: 10px; margin-top: 0px;">Playlist's Name</h4>
                        <!-- Input Name -->
                        <input type="text" name="playlist_name" required style="width: 300px; height: 30px; border-radius: 30px; background-color: rgba(217, 217, 217, 1); color: black; padding-left: 10px;">
                        <h4 style="margin-bottom: 10px; margin-top: 10px;">Description</h4>
                        <input type="text" style="width: 300px; height: 120px; border-radius: 30px; background-color: rgba(217, 217, 217, 1); color: black; padding-left: 10px;">
                    </div>
                </div>
                <div style="grid-template-columns: 240px 150px 150px; gap: 30px; display: grid; margin-top: 10px;">
                    <button type="button" class="changeSeenMode" id="changeSeenMode">🔒 Private</button>
                    <input type="hidden" name="is_public" id="input_is_public" value="0">
                    <button type="submit" name="btn_create_playlist" style="width: 80%; height: fit-content; padding: 10px 20px; border-radius: 20px; font-family: Roboto, sans-serif; background-color: rgba(1, 1, 1, 1); color: white; cursor: pointer;">Create</button>
                    <button type="button" style="width: 80%; height: fit-content; padding: 10px 20px; border-radius: 20px; font-family: Roboto, sans-serif; background-color: red; color: white; cursor: pointer;" onclick="document.getElementById('add_playlist_popup').style.display='none'">Cancel</button>
                </div>
            </form>
    </div>

    <!-- TRÌNH PHÁT NHẠC (Bắt buộc phải có để script JS không bị lỗi) -->
    <div id="music-player" style="position: fixed; bottom: 0; left: 0; width: 100%; background: #121212; color: white; display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; z-index: 1000; box-sizing: border-box; border-top: 1px solid #282828;">
        <div style="display: flex; align-items: center; width: 30%;">
            <img id="player-img" src="./img/default-song.jpg" style="width: 55px; height: 55px; border-radius: 5px; margin-right: 15px; object-fit: cover;">
            <div style="overflow: hidden; white-space: nowrap;">
                <h4 id="player-title" style="margin: 0; font-size: 15px; text-overflow: ellipsis; overflow: hidden;">Chưa chọn bài hát</h4>
                <p id="player-artist" style="margin: 5px 0 0 0; font-size: 13px; color: #b3b3b3; text-overflow: ellipsis; overflow: hidden;">...</p>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; max-width: 40%;">
            <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 8px;">
                <button id="btn-prev" style="background: none; border: none; color: #b3b3b3; cursor: pointer; font-size: 20px; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b3b3b3'">⏮</button>
                <button id="btn-play" style="background: white; border: none; color: black; cursor: pointer; width: 40px; height: 40px; border-radius: 50%; font-size: 18px; display: flex; align-items: center; justify-content: center; transition: transform 0.1s;" onmousedown="this.style.transform='scale(0.95)'" onmouseup="this.style.transform='scale(1)'">▶</button>
                <button id="btn-next" style="background: none; border: none; color: #b3b3b3; cursor: pointer; font-size: 20px; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b3b3b3'">⏭</button>
            </div>
            <div style="display: flex; align-items: center; width: 100%; gap: 10px; font-size: 12px; color: #b3b3b3;">
                <span id="current-time" style="min-width: 40px; text-align: right;">0:00</span>
                <input type="range" id="progress-bar" value="0" min="0" max="100" style="flex: 1; cursor: pointer; height: 4px; border-radius: 2px; appearance: none; background: #535353; outline: none;">
                <span id="total-time" style="min-width: 40px;">0:00</span>
            </div>
        </div>
        <div style="width: 30%; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
            <span style="font-size: 18px; color: #b3b3b3;">🔊</span>
            <input type="range" id="volume-bar" value="100" min="0" max="100" style="width: 100px; cursor: pointer; height: 4px; border-radius: 2px; appearance: none; background: #535353; outline: none;">
        </div>
        <audio id="audio-player" style="display: none;"></audio>
    </div>

    <script src="./js/music-streaming-library.js"></script>
    <script src="./js/music-streaming-home.js"></script>
    <script>
        // Đồng bộ dữ liệu trạng thái Private / Public vào thẻ hidden để đẩy lên PHP
        document.getElementById("changeSeenMode")?.addEventListener("click", function() {
            let hiddenInput = document.getElementById("input_is_public");
            if (this.textContent === "🔓 Public") {
                hiddenInput.value = "1";
            } else {
                hiddenInput.value = "0";
            }
        });
    </script>
</body>
</html>