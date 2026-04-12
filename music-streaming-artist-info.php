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

// 2. Lấy thông tin chi tiết Nghệ sĩ
$artist_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt_artist = $conn->prepare("SELECT * FROM Artists WHERE Artist_id = ?");
$stmt_artist->bind_param("i", $artist_id);
$stmt_artist->execute();
$artistResult = $stmt_artist->get_result();
$artistInfo = $artistResult->fetch_assoc();

if (!$artistInfo) {
    header("Location: music-streaming-home.php"); // Nếu ko tìm thấy, trả về home
    exit();
}

// 3. Lấy các bài hát của Nghệ sĩ đó
$sql_songs = "SELECT s.Song_id, s.Song_title, s.File_url, s.Song_image_url, a.Artist_name 
              FROM Songs s 
              LEFT JOIN Artists a ON s.Artist_id = a.Artist_id 
              WHERE s.Artist_id = ? 
              ORDER BY s.Song_id DESC";
$stmt_songs = $conn->prepare($sql_songs);
$stmt_songs->bind_param("i", $artist_id);
$stmt_songs->execute();
$songsResult = $stmt_songs->get_result();

// 4. Lấy các Album của Nghệ sĩ đó (dùng cho phần Playlists)
$sql_albums = "SELECT Album_id, Album_title, Cover_image_url, Release_date 
               FROM Albums 
               WHERE Artist_id = ? 
               ORDER BY Release_date DESC";
$stmt_albums = $conn->prepare($sql_albums);
$stmt_albums->bind_param("i", $artist_id);
$stmt_albums->execute();
$albumsResult = $stmt_albums->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eMusik - <?php echo htmlspecialchars($artistInfo['Artist_name']); ?></title> 
    <link rel="stylesheet" href='./css/home-style.css'>
    <link rel="stylesheet" href='./css/artist-info-style.css'>
    <link href='https://fonts.googleapis.com/css?family=Passero One' rel='stylesheet'>
</head>
<body>
    <div style="display: flex;">
        <!-- MENU TRÁI -->
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
                        <tr>
                            <button class="nav_button" onclick="document.location='./music-streaming-favourite.php'">
                                <img class="nav_logo" src="./img/favourite.png"> Favourite
                            </button>                        
                        </tr>
                    </table>
                </div>
            </nav>
        </div>
        
        <!-- NỘI DUNG CHÍNH -->
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

            <div style="margin-bottom: 20px;">
                <div class="artist-header">
                    <img class="artist-image" src="<?php echo htmlspecialchars($artistInfo['Avatar_url'] ?? './img/default-artist.jpg'); ?>" alt="<?php echo htmlspecialchars($artistInfo['Artist_name']); ?>"></img>
                    <div class="artist-name">
                        <h3><?php echo htmlspecialchars($artistInfo['Artist_name']); ?></h3>
                        <h4 style="font-weight: lighter; color: #b3b3b3; font-size: 14px; margin-top: 5px; max-width: 600px; line-height: 1.5;">
                            <?php echo htmlspecialchars($artistInfo['Biography']); ?>
                        </h4>
                    </div>
                    <div style="display: flex; gap: 15px; align-items: center; margin-left: 30px;">
                        <button id="playArtistBtn" class="follow-button" style="cursor: pointer; padding: 10px 25px; border-radius: 30px; font-weight: bold; background: #1DB954; color: white; border: none; white-space: nowrap; width: fit-content; height: fit-content; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; min-width: 120px;">⏵ PLAY ALL</button>
                        <button id="likeBtn" class="follow-button" style="cursor: pointer; padding: 10px 25px; border-radius: 30px; font-weight: bold; background: transparent; color: white; border: 1px solid white; white-space: nowrap; width: fit-content; height: fit-content; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; min-width: 120px;">♥ Follow</button>
                    </div>
                </div>
            </div>

            <div class="uploaded_song">
                <div class="uploaded_song_head">
                    <h3>UPLOADED SONG</h3>
                </div>
                <div class="uploaded_song_body">
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/left-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                    <div class="show_body_grid">
                        <?php if ($songsResult && $songsResult->num_rows > 0): ?>
                            <?php while($song = $songsResult->fetch_assoc()): ?>
                            <button class="song_nav" data-id="<?php echo $song['Song_id']; ?>" onclick="window.location.href='music-streaming-song-info.php?id=<?php echo $song['Song_id']; ?>'"
                                data-url="<?php echo htmlspecialchars($song['File_url']); ?>" 
                                data-title="<?php echo htmlspecialchars($song['Song_title']); ?>" 
                                data-artist="<?php echo htmlspecialchars($song['Artist_name'] ?? 'Unknown Artist'); ?>" 
                                data-img="<?php echo htmlspecialchars($song['Song_image_url'] ?? './img/default-song.jpg'); ?>">
                                <div class="song_nav_grid">
                                    <img src="<?php echo htmlspecialchars($song['Song_image_url'] ?? './img/default-song.jpg'); ?>" alt="eMusik"></img>
                                    <div class="article_body">
                                        <h4><?php echo htmlspecialchars($song['Song_title']); ?></h4> 
                                        <h4 style="font-weight: lighter;"><?php echo htmlspecialchars($song['Artist_name'] ?? 'Unknown Artist'); ?></h4>
                                    </div>
                                </div>
                            </button>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: #b3b3b3; padding: 20px;">Nghệ sĩ này chưa có bài hát nào.</p>
                        <?php endif; ?>
                    </div>
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/right-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="playlists">
                <div class="playlists_head">
                    <h3>PLAYLISTS</h3>
                </div>
                <div class="playlists_body">
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/left-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                    <div class="show_body_grid">
                        <?php if (isset($albumsResult) && $albumsResult->num_rows > 0): ?>
                            <?php while($album = $albumsResult->fetch_assoc()): ?>
                            <button class="playlist_nav" onclick="window.location.href='music-streaming-playlist-info.php?id=<?php echo $album['Album_id']; ?>'" style="cursor: pointer;">
                                <div class="playlist_nav_grid">
                                    <img src="<?php echo htmlspecialchars($album['Cover_image_url'] ?? './img/default-playlist.jpg'); ?>" alt="eMusik"></img>
                                    <div class="article_body">
                                        <h4><?php echo htmlspecialchars($album['Album_title']); ?></h4> 
                                    </div>
                                </div> 
                            </button>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: #b3b3b3; padding: 20px;">Nghệ sĩ này chưa có album/playlist nào.</p>
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
                            <li><a href="./music-streaming-favourite.php">Favourite</a></li>
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
    
    <!-- TRÌNH PHÁT NHẠC (MUSIC PLAYER) -->
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

    <!-- Bằng việc include file JS này, thanh tìm kiếm và player vẫn sẽ hoạt động trơn tru! -->
    <script src="js/music-streaming-home.js"></script>
    <script>
        // JS cho nút Play All của nghệ sĩ
        document.getElementById('playArtistBtn')?.addEventListener('click', () => {
            if (typeof currentPlaylist !== 'undefined' && currentPlaylist.length > 0) {
                const firstSong = currentPlaylist[0];
                playSong(firstSong.url, firstSong.title, firstSong.artist, firstSong.img);
                currentIndex = 0;
            } else {
                alert('Nghệ sĩ này hiện chưa có bài hát nào để phát!');
            }
        });
        
        // Đổi trạng thái nút Follow
        document.getElementById('likeBtn')?.addEventListener('click', function() {
            this.classList.toggle('liked');
            if (this.classList.contains('liked')) this.textContent = '♥ Followed';
            else this.textContent = '♥ Follow';
        });
    </script>
</body>
</html>