<?php
session_start();
include 'includes/db-connect.php';

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

// 1. LẤY DANH SÁCH BÀI HÁT (Kèm tên nghệ sĩ)
$sql_songs = "SELECT s.Song_id, s.Song_title, s.File_url, s.Song_image_url, a.Artist_name 
              FROM Songs s 
              LEFT JOIN Artists a ON s.Artist_id = a.Artist_id 
              ORDER BY s.Song_id DESC LIMIT 15";
$songsResult = $conn->query($sql_songs);

// 2. LẤY DANH SÁCH NGHỆ SĨ
$sql_artists = "SELECT Artist_id, Artist_name, Avatar_url FROM Artists ORDER BY Artist_id DESC LIMIT 15";
$artistsResult = $conn->query($sql_artists);

// 3. LẤY DANH SÁCH PLAYLIST CÔNG KHAI
$sql_playlists = "SELECT p.Playlist_id, p.Playlist_name, p.Playlist_avatar_url, u.User_name 
                  FROM Playlists p 
                  LEFT JOIN Users u ON p.User_id = u.User_id 
                  WHERE p.is_public = 1 ORDER BY p.Playlist_id DESC LIMIT 15";
$playlistsResult = $conn->query($sql_playlists);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eMusik - Home</title> 
    <!-- <link rel="stylesheet" href='./css/home-style.css'> -->
     <link rel="stylesheet" href='./css/home-style.css?v=<?php echo time(); ?>'>
    <link href='https://fonts.googleapis.com/css?family=Passero One' rel='stylesheet'>
</head>
<body>
    <div style="display: flex;">
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
                        <!-- <tr>
                            <button class="nav_button" onclick="document.location='./music-streaming-favourite.php'">
                                <img class="nav_logo" src="./img/favourite.png"> Favourite
                            </button>                        
                        </tr> -->
                    </table>
                </div>
            </nav>
        </div>
        <div class="home">
            <div class="header"> 
                <ul style="display: grid; grid-template-columns: 90% 10%; list-style-type: none;">
                    <li>
                        <div class="search" style="text-align: center; position: relative;">
                            <input type="text" id="search_info" name="search_info" placeholder="Search songs, artists..."></input>
                            <div id="search-results" style="position: absolute; top: 100%; left: 50%; transform: translateX(-50%); width: 100%; max-width: 400px; background: #181818; border-radius: 10px; margin-top: 5px; display: none; flex-direction: column; max-height: 350px; overflow-y: auto; z-index: 1001; box-shadow: 0 8px 16px rgba(0,0,0,0.8); text-align: left; border: 1px solid #333;">
                                <!-- Kết quả tìm kiếm hiển thị ở đây -->
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
            <div class="show_song">
                <div class="show_song_head">
                    <h3>BEST SONG</h3>
                </div>
                <div class="show_song_body">
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
                            <p style="color: white; padding: 20px;">Chưa có bài hát nào.</p>
                        <?php endif; ?>
                    </div>
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/right-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                </div>
            </div>
            <div class="show_artist">
                <div class="show_artist_head">
                    <h3>NOTABLE ARTIST</h3>
                </div>
                <div class="show_artist_body">
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/left-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                    <div class="show_body_grid">
                        <?php if ($artistsResult && $artistsResult->num_rows > 0): ?>
                            <?php while($artist = $artistsResult->fetch_assoc()): ?>
                            <button class="artist_nav" onclick="window.location.href='music-streaming-artist-info.php?id=<?php echo $artist['Artist_id']; ?>'">
                                <img src="<?php echo htmlspecialchars($artist['Avatar_url'] ?? './img/default-artist.jpg'); ?>" alt="eMusik"></img>
                                <div class="article_body">
                                    <h4><?php echo htmlspecialchars($artist['Artist_name']); ?></h4>
                                </div>
                            </button>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: white; padding: 20px;">Chưa có nghệ sĩ nào.</p>
                        <?php endif; ?>
                    </div>
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/right-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                </div>
            </div>
            <div class="show_playlist">
                <div class="show_playlist_head">
                    <h3>BEST PLAYLIST</h3>
                </div>
                <div class="show_playlist_body">
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/left-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                    <div class="show_body_grid">
                        <?php if ($playlistsResult && $playlistsResult->num_rows > 0): ?>
                            <?php while($playlist = $playlistsResult->fetch_assoc()): ?>
                            <button class="playlist_nav" onclick="window.location.href='music-streaming-playlist-info.php?id=<?php echo $playlist['Playlist_id']; ?>'" style="cursor: pointer;">
                                <div class="playlist_nav_grid">
                                    <img src="<?php echo htmlspecialchars($playlist['Playlist_avatar_url'] ?? './img/default-playlist.jpg'); ?>" alt="eMusik"></img>
                                    <div class="article_body">
                                        <h4><?php echo htmlspecialchars($playlist['Playlist_name']); ?></h4> 
                                        <h4 style="font-weight: lighter;"><?php echo htmlspecialchars($playlist['User_name'] ?? 'Unknown'); ?></h4>
                                    </div>
                                </div>
                            </button>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: white; padding: 20px;">Chưa có playlist nào.</p>
                        <?php endif; ?>
                    </div>
                    <div class="show_body_grid">
                        <button class="arrow-btn">
                            <img src="./img/right-arrow.png" style="height: 30px;"></img>
                        </button>
                    </div>
                </div>
            </div>
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

    <script src="js/music-streaming-home.js?v=<?php echo time(); ?>"></script>
</body>
</html>