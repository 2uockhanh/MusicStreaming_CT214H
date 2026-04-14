<?php
session_start();
include 'includes/db-connect.php';

// 1. Xử lý Avatar người dùng trên Header
$avatarUrl = 'img/avatar.jpg';
if (!empty($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT User_avatar_url FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['User_avatar_url'])) {
            $avatarUrl = $row['User_avatar_url'];
        }
    }
    $stmt->close();
}

// 2. Lấy thông tin chi tiết Bài hát
$song_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql_song_info = "SELECT s.*, a.Artist_name, a.Artist_id, a.Avatar_url 
                  FROM Songs s 
                  LEFT JOIN Artists a ON s.Artist_id = a.Artist_id 
                  WHERE s.Song_id = ?";
$stmt_song = $conn->prepare($sql_song_info);
$stmt_song->bind_param("i", $song_id);
$stmt_song->execute();
$songResult = $stmt_song->get_result();
$songInfo = $songResult->fetch_assoc();

if (!$songInfo) {
    header("Location: music-streaming-home.php"); // Nếu ko tìm thấy, trả về home
    exit();
}

// 3. Lấy các bài hát khác của cùng nghệ sĩ (trừ bài hiện tại)
$otherSongsResult = null;
if (!empty($songInfo['Artist_id'])) {
    $sql_other_songs = "SELECT s.Song_id, s.Song_title, s.File_url, s.Song_image_url, a.Artist_name 
                        FROM Songs s 
                        LEFT JOIN Artists a ON s.Artist_id = a.Artist_id 
                        WHERE s.Artist_id = ? AND s.Song_id != ? 
                        ORDER BY RAND() LIMIT 5"; // Lấy ngẫu nhiên 5 bài
    $stmt_other_songs = $conn->prepare($sql_other_songs);
    $stmt_other_songs->bind_param("ii", $songInfo['Artist_id'], $song_id);
    $stmt_other_songs->execute();
    $otherSongsResult = $stmt_other_songs->get_result();
}

// 4. Xử lý logic khi người dùng bấm nút "Add" (Thêm bài hát vào Playlist)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_playlist_id'])) {
    $pl_id = intval($_POST['add_to_playlist_id']);
    $s_id = $song_id;
    
    // Kiểm tra xem bài hát đã tồn tại trong Playlist này chưa
    $check_stmt = $conn->prepare("SELECT * FROM playlist_Song WHERE Playlist_id = ? AND Song_id = ?");
    $check_stmt->bind_param("ii", $pl_id, $s_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        $insert_stmt = $conn->prepare("INSERT INTO playlist_Song (Playlist_id, Song_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $pl_id, $s_id);
        $insert_stmt->execute();
        echo "<script>alert('Đã thêm bài hát vào playlist thành công!');</script>";
    } else {
        echo "<script>alert('Bài hát này đã có trong playlist!');</script>";
    }
}

// 5. Lấy danh sách Playlist của User hiện hành để hiển thị lên Popup
$user_playlists = [];
if (!empty($_SESSION['user_id'])) {
    $sql_my_pl = "SELECT Playlist_id, Playlist_name, Playlist_avatar_url FROM Playlists WHERE User_id = ? ORDER BY Playlist_id DESC";
    $stmt_my_pl = $conn->prepare($sql_my_pl);
    $stmt_my_pl->bind_param("i", $_SESSION['user_id']);
    $stmt_my_pl->execute();
    $user_playlists = $stmt_my_pl->get_result()->fetch_all(MYSQLI_ASSOC);
}

// 6. Kiểm tra xem người dùng đã Like bài hát này chưa
$is_liked = false;
if (!empty($_SESSION['user_id'])) {
    $check_like = $conn->prepare("SELECT * FROM Favorites WHERE User_id = ? AND Song_id = ?");
    $check_like->bind_param("ii", $_SESSION['user_id'], $song_id);
    $check_like->execute();
    if ($check_like->get_result()->num_rows > 0) {
        $is_liked = true;
    }
}

$playlistAvatarUrl = './img/default-playlist-avatar.jpg';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eMusik - <?php echo htmlspecialchars($songInfo['Song_title']); ?> - <?php echo htmlspecialchars($songInfo['Artist_name'] ?? 'Unknown Artist'); ?></title> 
    <link rel="stylesheet" href='./css/song-info-style.css'>
    <link rel="stylesheet" href='./css/library-style.css'>
    <link href='https://fonts.googleapis.com/css?family=Passero One' rel='stylesheet'>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css' rel='stylesheet'>
</head>
<body>
    <div style="display: flex;">
        <!-- MENU TRÁI (giữ nguyên) -->
        <div>
            <nav class="navbar">
                <button class="logo_nav" onclick="document.location='./music-streaming-home.php'">
                    <h3 style="font-family: 'Passero One'; font-size: 48px; margin: 0px auto;">eMuzik</h3>
                </button>
                <div class="nav">
                <table>
                        <tr>
                            <button class="nav_button" onclick="document.location='./music-streaming-home.php'">
                                <img class="nav_logo" src="./img/home.png"> Home
                            </button>
                        </tr>
                        <tr>
                            <button class="nav_button" onclick="document.location='./music-streaming-library.php'">
                                <img class="nav_logo" src="./img/library.png"> Library
                            </button>
                        </tr>
                    </table>
                </div>
            </nav>
        </div>
        
        <!-- NỘI DUNG CHÍNH -->
        <div class="home">
            <!-- HEADER (giữ nguyên) -->
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
                                <?php if ($userRole === 'admin'): ?>
                                <button value="Admin Dashboard" class="dropDownBtn" onclick="window.location.href='music-streaming-admin.php'">Admin Dashboard</button>
                                <?php endif; ?>
                                <button value="themeMode" id="themeMode" class="dropDownBtn">🌙 Theme Mode</button>
                                <button type="button" class="dropDownBtn" onclick="window.location.href='music-streaming-login.php'">Log Out</button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div style="justify-content: center;">
                <div class="song-header">
                    <img class="song-image" src="<?php echo htmlspecialchars($songInfo['Song_image_url'] ?? './img/default-song.jpg'); ?>" alt="cover">
                    <div class="song-name">
                        <h4>Song</h4>
                        <h2><?php echo htmlspecialchars($songInfo['Song_title']); ?></h2>
                        <div onclick="document.location='music-streaming-artist-info.php?id=<?php echo $songInfo['Artist_id']; ?>'" style="margin-top: 0px; grid-template-columns: 30px 200px auto; display: grid; align-content: center; cursor: pointer;">
                            <img style="width: 30px; height: 30px; border-radius: 30px;" src="<?php echo htmlspecialchars($songInfo['Avatar_url'] ?? './img/default-playlist-avatar.jpg'); ?>" alt="artist">
                            <h4 style="margin-left: 10px; margin-top: 5px;"><?php echo htmlspecialchars($songInfo['Artist_name'] ?? 'Unknown Artist'); ?></h4>
                        </div>
                    </div>
                    <button id="playBtn" class="playBtn">⏵ PLAY</button>
                </div>
            </div>
            <div style="margin-left: 200px;">
                <!--<button id="likeBtn" class="likeBtn">♥ Like</button>-->
                <button id="likeBtn" class="likeBtn <?php echo $is_liked ? 'liked' : ''; ?>" data-song-id="<?php echo $song_id; ?>"><?php echo $is_liked ? '♥ Liked' : '♥ Like'; ?></button>
                <!--<button class="likeBtn">Share</button>-->
                <button class="likeBtn" id="add_to_playlist_btn">Add Playlist</button>
            </div>
            <div style="margin-bottom: 20px; grid-template-columns: 65% 35%; display: grid; color: white;">
                <div class="lyrics">
                    <h4 style="font-family: Tahoma, sans-serif; font-size: 110%; margin-left: 15%; margin-bottom: 20px;">Lyrics</h4>
                    <div class="lyric-body" style="border-radius: 10px; background-color: grey; color: black; max-width: 70%; padding: 5px; margin-left: 10%;">
                        <pre style="font-family: Roboto, sans-serif; margin: 10px; font-style: italic; font-weight: lighter; color: rgba(1, 1, 1, 1); white-space: pre-wrap; font-size: 16px;"><?php echo !empty($songInfo['Lyric']) ? htmlspecialchars($songInfo['Lyric']) : 'Chưa có lời cho bài hát này.'; ?></pre>
                    </div>
                </div>
                <div class="artist-participate">
                    <h4 style="font-family: Tahoma, sans-serif; font-size: 110%;">Artists</h4>
                    <div class="artist-participate-body">
                        <div onclick="document.location='music-streaming-artist-info.php?id=<?php echo $songInfo['Artist_id']; ?>'" class="artist-participate-nav" style="grid-template-columns: 30px auto; display: grid; gap: 10px; font-family: Tahoma, sans-serif; align-content: center; cursor: pointer;">
                            <img style="width: 30px; height:30px; border-radius: 30px;" src="<?php echo htmlspecialchars($songInfo['Avatar_url'] ?? './img/default-playlist-avatar.jpg'); ?>">
                            <h4 style="margin-left: 10px; margin-top: 5px;"><?php echo htmlspecialchars($songInfo['Artist_name'] ?? 'Unknown Artist'); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>Last Update: <?php echo date('d/m/Y'); ?></p>
                <p>&copy; 2026 LOOPS MUSIC.</p>
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
                            <li><a href="./music-streaming-library.html">Library</a></li>
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

            <!-- KHOẢNG TRỐNG ĐỂ KHÔNG BỊ PLAYER CHE -->
            <div style="height: 100px;"></div>
        </div>
    </div>
    <!-- THÊM NHẠC VÀO PLAYLIST -->
    <div class="add_to_playlist_popup" id="add_to_playlist_popup">
        <div class="popupHead" id="popupHead">
            <div class="popupHeader" id="popupHeader">Add To Playlists</div>
            <button class="popupClose" id="popupClose">&#128936;</button>
        </div>
        <div class="popup_search" style="text-align: center;">
            <input type="text" id="search_info" name="search_info" placeholder="Search playlists..."></input>
        </div>
        <div>
            <button id="create_new_playlist" style="grid-template-columns: 40px auto; display: grid; gap: 10px; align-items: center; background-color: rgba(0,0,0,0); border: 0px solid black; margin: 10px 0px 10px 20px;">
                <img src="./img/add.png" style="width: 40px; height: 40px;"></img>
                <p style="font-family: Roboto, sans-serif;">Create New Playlist</p>
            </button>
        </div>
        <div>
            <div style="margin: 20px 0px 20px 20px; font-family: Roboto, sans-serif; font-weight: bold;">Your Playlists</div>
            <div style="max-height: 250px; overflow-y: auto; overflow-x: hidden;">
                <?php foreach ($user_playlists as $pl): ?>
                    <div style="grid-template-columns: 40px 60% 60px; display: grid; gap: 10px; align-items: center; justify-content: center; margin-bottom: 10px;">
                        <img src="<?php echo htmlspecialchars($pl['Playlist_avatar_url'] ?? './img/default-playlist-avatar.jpg'); ?>" style="width: 40px; height: 40px; border: 1px solid black; border-radius: 10px; object-fit: cover;"></img>
                        <p style="font-family: Roboto, sans-serif; margin: 0; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?php echo htmlspecialchars($pl['Playlist_name']); ?></p>
                        <form method="POST" action="" style="margin: 0;">
                            <input type="hidden" name="add_to_playlist_id" value="<?php echo $pl['Playlist_id']; ?>">
                            <button type="submit" style="background-color: grey; color: black; padding: 5px 10px; font-weight: bold; border-radius: 20px; cursor: pointer; border: none;">Add</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (count($user_playlists) === 0): ?>
                    <p style="font-family: Roboto, sans-serif; font-size: 14px; text-align: center; padding: 20px 0;">Bạn chưa có playlist nào. Hãy tạo mới nhé!</p>
                <?php endif; ?>
            </div>
            <!--
            <div style="grid-template-columns: 40px 60% 60px; display: grid; gap: 10px; align-items: center; justify-content: center;">
                <img src="./img/default-playlist-avatar.jpg" style="width: 40px; height: 40px; border: 1px solid black; border-radius: 10px;"></img>
                <p style="font-family: Roboto, sans-serif;">Say Hi</p>
                <button style="background-color: grey; color: black; padding: 5px 10px; font-weight: bold; border-radius: 20px;">Add</button>
            </div>
            -->
        </div>
    </div>
    <!-- TẠO PLAYLIST -->
    <div class="add_playlist_popup" id="add_playlist_popup">
        <div class="popup_head" id="popup_head">
            <div class="popup_header" id="popup_header">Create Playlist</div>
            <button class="popup_close" id="popup_close">&#128936;</button>
        </div>
        <div style="grid-template-columns: 240px 330px; gap: 30px; display: grid;">
            <div style="margin-left: 30px;">
                <div style="width: 200px; height: 200px; border: 3px solid black; justify-content: center; align-items: center; overflow: hidden; display: flex;" id="avatar-preview">
                    <img src="<?php echo htmlspecialchars($playlistAvatarUrl ?? './img/default-playlist-avatar.jpg'); ?>" style="width: 100%; height: 100%; object-fit:cover;" alt="Avatar">
                </div>
                <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display: none;">
                <button onclick="document.getElementById('avatar-input').click();" style="font-family: Roboto, sans-serif; width: fit-content; height: fit-content; background-color: rgba(217, 217, 217, 1); color: black; font-size: medium; padding: 10px; font-weight: bold; border-radius: 20px; justify-content: center; text-align: center; margin-top: 10px; margin-left: 40px;">Change Avatar</button>
            </div>
            <div style="font-family: Roboto, sans-serif; align-content: center;">
                <h4 style="margin-bottom: 10px; margin-top: 0px;">Playlist's Name</h4>
                <input type="text" name="playlistName" style="width: 300px; height: 30px; border-radius: 30px; background-color: rgba(217, 217, 217, 1); color: black; padding-left: 10px;">
                <!--
                <h4 style="margin-bottom: 10px;">Description</h4>
                <input type="text" style="width: 300px; height: 120px; border-radius: 30px; background-color: rgba(217, 217, 217, 1); color: black; padding-left: 10px;">
                -->
            </div>
        </div>
        <div style="grid-template-columns: 240px 150px 150px; gap: 30px; display: grid; margin-top: 10px;">
            <button type="button" name="changeSeenMode" class="changeSeenMode" id="changeSeenMode">🔒 Private</button>
            <button type="button" class="createPlaylist" id="createPlaylist" style="width: 80%; height: fit-content; padding: 10px 20px; border-radius: 20px; font-family: Roboto, sans-serif; background-color: rgba(1, 1, 1, 1); color: white;">Create</button>
            <button type="button" class="cancelPlaylist" id="cancelPlaylist" style="width: 80%; height: fit-content; padding: 10px 20px; border-radius: 20px; font-family: Roboto, sans-serif; background-color: red; color: white;">Cancel</button>
        </div>
    </div>
    
    <!-- TRÌNH PHÁT NHẠC (MUSIC PLAYER) - giữ nguyên -->
    <div id="music-player" style="position: fixed; bottom: 0px; left: 0px; width: 100%; background: rgba(217, 217, 217, 1); color: black; display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; z-index: 1000; box-sizing: border-box; border-top: 1px solid #282828;">
        <div style="display: flex; align-items: center; width: 30%;">
            <img id="player-img" src="./img/default-song.jpg" style="width: 55px; height: 55px; border-radius: 5px; margin-right: 15px; object-fit: cover;"> <!-- Ảnh của bài hát được phát -->
            <div style="overflow: hidden; white-space: nowrap; font-family: Roboto, sans-serif;">
                <h4 id="player-title" style="margin: 0; font-size: 15px; text-overflow: ellipsis; overflow: hidden;"><?php echo htmlspecialchars($songInfo['song_title'] ?? 'Chưa chọn bài hát') ?></h4> <!-- Tên bài hát -->
                <p id="player-artist" style="margin: 5px 0 0 0; font-size: 13px; text-overflow: ellipsis; overflow: hidden;">...</p> <!-- Tên nghệ sĩ -->
            </div>
        </div>
        
        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; max-width: 40%;">
            <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 8px;">
                <button id="btn-prev" style="background: none; border: none; color: #b3b3b3; cursor: pointer; font-size: 20px; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b3b3b3'"><img id="prevImg" src="./img/left-next.png" style="width: 20px; height: 20px; margin-left: 10px; margin-right: 10px;"></img></button> <!--⏮-->
                <button id="btn-play" style="background: black; border: none; color: white; cursor: pointer; width: 40px; height: 40px; border-radius: 50%; font-size: 18px; display: flex; align-items: center; justify-content: center; transition: transform 0.1s;" onmousedown="this.style.transform='scale(0.95)'" onmouseup="this.style.transform='scale(1)'">▶</button> <!--▶-->
                <button id="btn-next" style="background: none; border: none; color: #b3b3b3; cursor: pointer; font-size: 20px; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b3b3b3'"><img id="nextImg" src="./img/right-next.png" style="width: 20px; height: 20px; margin-left: 10px; margin-right: 10px;"></img></button> <!--⏭-->
            </div>
            <div style="display: flex; align-items: center; width: 100%; gap: 10px; font-size: 12px; font-family: Roboto, sans-serif;">
                <span id="current-time" style="min-width: 40px; text-align: right;">0:00</span>
                <input type="range" id="progress-bar" value="0" min="0" max="100" style="flex: 1; cursor: pointer; height: 4px; border-radius: 2px; appearance: none; outline: none; accent-color: black;">
                <span id="total-time" style="min-width: 40px;">0:00</span>
            </div>
        </div>

        <div style="width: 30%; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
            <span style="font-size: 18px; color: #b3b3b3;"><img id="volume-img" style="width: 20px; height: 20px;" src="./img/volume.png"></img></span>
            <input type="range" id="volume-bar" value="1" min="0" max="1" step="0.01" style="width: 100px; cursor: pointer; height: 4px; border-radius: 2px; appearance: none; accent-color: black; outline: none;">
        </div>
        <audio id="audio-player" style="display: none;">
            <source src="<?php echo htmlspecialchars($songInfo['file_url'] ?? ''); ?>" type="audio/mpeg"></source>
        </audio>
    </div>

    <!-- Bằng việc include file JS này, thanh tìm kiếm và player vẫn sẽ hoạt động trơn tru! -->
    <script src="js/music-streaming-home.js?v=<?php echo time(); ?>"></script>
    <script>
        document.getElementById("add_to_playlist_btn").addEventListener("click", openAddPlaylistPopUp);
        function openAddPlaylistPopUp() {
            document.getElementById("add_to_playlist_popup").style.display = "block";
        }
        document.getElementById("popupClose").addEventListener("click", closeAddPlaylistPopUp);
        function closeAddPlaylistPopUp(){
            document.getElementById("add_to_playlist_popup").style.display = "none";
        }
        document.getElementById("create_new_playlist").addEventListener("click", openPlaylistPopUp);
        function openPlaylistPopUp() {
            document.getElementById("add_to_playlist_popup").style.display = "none";
            document.getElementById("changeSeenMode").textContent = "🔒 Private";
            document.getElementById("add_playlist_popup").style.display = "block";
        }
        document.getElementById("popup_close").addEventListener("click", closePlaylistPopup);
        function closePlaylistPopup() {
            document.getElementById("add_to_playlist_popup").style.display = "block";
            document.getElementById("add_playlist_popup").style.display = "none";
        }
        document.getElementById("changeSeenMode").addEventListener("click", changeSeenMode);
        function changeSeenMode() {
            if (document.getElementById("changeSeenMode").textContent === "🔒 Private") {
                document.getElementById("changeSeenMode").textContent = "🔓 Public";
            } else {
                document.getElementById("changeSeenMode").textContent = "🔒 Private";
            }
        };
    </script>
    <script src="./js/music-streaming-library.js"></script>
    <script src="js/playlist.js"></script>
    <script src="js/music-streaming-song.js"></script>
    <script>
        // Thêm JS để nút play trên trang này hoạt động
        document.getElementById('playBtn').addEventListener('click', () => {
            const songData = {
                url: '<?php echo htmlspecialchars($songInfo['File_url'] ?? ''); ?>',
                title: '<?php echo htmlspecialchars($songInfo['Song_title'] ?? ''); ?>',
                artist: '<?php echo htmlspecialchars($songInfo['Artist_name'] ?? 'Unknown Artist'); ?>',
                img: '<?php echo htmlspecialchars($songInfo['Song_image_url'] ?? './img/default-song.jpg'); ?>'
            };
            
            // Lấy thông tin bài hát đang phát trong Player toàn cục
            const currentAudio = document.getElementById('audio-player');
            const originalUrl = currentAudio.getAttribute('data-original-url');
            
            // Nếu đang phát đúng bài này, thì nút đóng vai trò là Play/Pause
            if (originalUrl === songData.url) {
                if (currentAudio.paused) {
                    currentAudio.play();
                } else {
                    currentAudio.pause();
                }
                return;
            }

            // Hàm playSong đã được định nghĩa trong music-streaming-home.js
            // Ta chỉ cần gọi lại nó với dữ liệu của bài hát hiện tại
            playSong(songData.url, songData.title, songData.artist, songData.img);

            // Cập nhật lại playlist hiện tại để nút next/prev hoạt động đúng
            // Tìm bài hát hiện tại trong playlist đã load
            let songIndex = currentPlaylist.findIndex(song => song.url === songData.url);
            if (songIndex !== -1) {
                currentIndex = songIndex;
            } else {
                // Nếu không có trong playlist (ví dụ: vào thẳng trang info), thêm nó vào đầu
                currentPlaylist.unshift(songData);
                currentIndex = 0;
            }
        });

        // Đồng bộ biểu tượng cho Nút Play bự trên trang
        const systemAudio = document.getElementById('audio-player');
        const bigPlayBtn = document.getElementById('playBtn');
        
        systemAudio.addEventListener('play', () => {
            if (systemAudio.getAttribute('data-original-url') === '<?php echo htmlspecialchars($songInfo['File_url'] ?? ''); ?>') {
                bigPlayBtn.innerHTML = '⏸ PAUSE';
            }
        });
        
        systemAudio.addEventListener('pause', () => {
            bigPlayBtn.innerHTML = '⏵ PLAY';
        });
        
        // Kiểm tra ngay lúc load trang nếu bài này đang được phát
        if (systemAudio.getAttribute('data-original-url') === '<?php echo htmlspecialchars($songInfo['File_url'] ?? ''); ?>' && !systemAudio.paused) {
            bigPlayBtn.innerHTML = '⏸ PAUSE';
        }
        // --- XỬ LÝ JS CHO NÚT LIKE BÀI HÁT ---
        const likeBtn = document.getElementById('likeBtn');
        if (likeBtn) {
            likeBtn.addEventListener('click', function() {
                const songId = this.getAttribute('data-song-id');
                const isLiked = this.classList.contains('liked');
                
                // Cập nhật UI ngay lập tức cho trải nghiệm mượt (Optimistic UI)
                this.classList.toggle('liked');
                if (!isLiked) {
                    this.textContent = '♥ Liked';
                } else {
                    this.textContent = '♥ Like';
                }

                // Gửi request ngầm lưu vào CSDL
                const formData = new FormData();
                formData.append('song_id', songId);
                fetch('includes/song_api.php?action=toggle_like', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message || 'Có lỗi xảy ra!');
                        // Hoàn tác UI nếu bị lỗi (chưa đăng nhập)
                        this.classList.toggle('liked');
                        this.textContent = isLiked ? '♥ Liked' : '♥ Like';
                    }
                }).catch(err => console.error('Lỗi Like bài hát:', err));
            });
        }
    </script>
</body>
</html>