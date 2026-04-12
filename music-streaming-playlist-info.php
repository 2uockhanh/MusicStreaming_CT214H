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

// 2. Lấy thông tin chi tiết Playlist
$playlist_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql_playlist_info = "SELECT p.*, u.User_name, u.User_avatar_url 
                      FROM Playlists p 
                      LEFT JOIN Users u ON p.User_id = u.User_id 
                      WHERE p.Playlist_id = ?";
$stmt_playlist = $conn->prepare($sql_playlist_info);
$stmt_playlist->bind_param("i", $playlist_id);
$stmt_playlist->execute();
$playlistResult = $stmt_playlist->get_result();
$playlistInfo = $playlistResult->fetch_assoc();

if (!$playlistInfo) {
    header("Location: music-streaming-home.php"); // Nếu ko tìm thấy, trả về home
    exit();
}

// 3. Lấy các bài hát trong Playlist
$sql_songs = "SELECT s.Song_id, s.Song_title, s.File_url, s.Song_image_url, s.Duration, a.Artist_name 
              FROM playlist_Song ps
              INNER JOIN Songs s ON ps.Song_id = s.Song_id
              LEFT JOIN Artists a ON s.Artist_id = a.Artist_id 
              WHERE ps.Playlist_id = ? 
              ORDER BY s.Song_id DESC";
$stmt_songs = $conn->prepare($sql_songs);
$stmt_songs->bind_param("i", $playlist_id);
$stmt_songs->execute();
$songsResult = $stmt_songs->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eMusik - <?php echo htmlspecialchars($playlistInfo['Playlist_name']); ?></title> 
    <link rel="stylesheet" href='./css/playlist-info-style.css'>
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
                            <button class="nav_button" onclick="document.location='./music-streaming-library.html'">
                                <img class="nav_logo" src="./img/library.png"> Library
                            </button>
                        </tr>
                        <tr>
                            <button class="nav_button">
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

            <div style="margin-bottom: 20px; cursor: pointer;">
                <div class="playlist-header">
                    <img class="playlist-image" src="<?php echo htmlspecialchars($playlistInfo['Playlist_avatar_url'] ?? './img/default-playlist.jpg'); ?>" alt="cover">
                    <div class="playlist-name">
                        <h4>Playlist</h4>
                        <h2><?php echo htmlspecialchars($playlistInfo['Playlist_name']); ?></h2>
                        <div style="margin-top: 0px; grid-template-columns: 30px 100px auto; display: grid; align-content: center;">
                            <img style="width: 30px; height: 30px; border-radius: 30px;" src="<?php echo htmlspecialchars($playlistInfo['User_avatar_url'] ?? './img/default-playlist-avatar.jpg'); ?>" alt="user">
                            <h4 style="margin-left: 10px; margin-top: 5px;"><?php echo htmlspecialchars($playlistInfo['User_name'] ?? 'Unknown'); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-left: 200px; margin-top: 20px; margin-bottom: 20px;">
                <button id="playPlaylistBtn" class="likeBtn" style="background-color: #1DB954; color: white; font-weight: bold; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer;">⏵ PLAY ALL</button>
                <button class="likeBtn" id="likeBtn">♥ Save</button>
                <button class="likeBtn" id="addSongsBtn">Add Songs</button>
                <button class="likeBtn" id="editPlaylistBtn">Edit Playlist</button>
            </div>
            
            <div>
                <table class="playlist-table">
                    <tr style="cursor: unset;">
                        <th>#</th>
                        <th>Title</th>
                        <th>Duration</th>
                    </tr>
                    <?php if ($songsResult && $songsResult->num_rows > 0): ?>
                        <?php 
                        $index = 1;
                        $hidden_song_data = "";
                        while($song = $songsResult->fetch_assoc()): 
                            $duration = $song['Duration'] ?? 0;
                            $min = floor($duration / 60);
                            $sec = $duration % 60;
                            $timeStr = $min . ':' . str_pad($sec, 2, '0', STR_PAD_LEFT);
                            
                            $hidden_song_data .= sprintf(
                                '<div class="song_nav" data-id="%s" data-url="%s" data-title="%s" data-artist="%s" data-img="%s" style="display:none;"></div>',
                                $song['Song_id'], htmlspecialchars($song['File_url']), htmlspecialchars($song['Song_title']), htmlspecialchars($song['Artist_name'] ?? 'Unknown Artist'), htmlspecialchars($song['Song_image_url'] ?? './img/default-song.jpg')
                            );
                        ?>
                        <tr onclick="document.location='music-streaming-song-info.php?id=<?php echo $song['Song_id']; ?>'">
                            <td><?php echo $index++; ?></td>
                            <td>
                                <div> <h4><?php echo htmlspecialchars($song['Song_title']); ?></h4> <h4 style="font-weight: lighter;"><?php echo htmlspecialchars($song['Artist_name'] ?? 'Unknown Artist'); ?></h4> </div>
                            </td>
                            <td><?php echo $timeStr; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px; color: #b3b3b3;">Playlist này chưa có bài hát nào.</td>
                        </tr>
                    <?php endif; ?>
                </table>
                <?php echo $hidden_song_data ?? ''; ?>
            </div>
            
            <div class="copyright">
                <p style="color: grey; margin: 0px;">Last Update: <?php echo date('d/m/Y'); ?></p>
                <p style="color: grey; margin: 0px;">&copy; 2026 LOOPS MUSIC.</p>
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

    <div class="add_songs_popup" id="add_songs_popup">
        <div class="popupHead" id="popupHead">
            <div class="popupHeader" id="popupHeader">Add Songs</div>
            <button class="popupClose" id="popupClose">&#128936;</button>
        </div>
        <div class="popup_search" style="text-align: center;">
            <input type="text" id="search_info" name="search_info" placeholder="Search playlists..."></input>
        </div>
        <div style="margin: 20px auto;">
            <div style="grid-template-columns: 40px 60% 60px; display: grid; gap: 10px; align-items: center; justify-content: center; border-top: 1px solid black; padding-top: 10px; margin-top: 10px;">
                <img src="./img/default-song.jpg" style="width: 40px; height: 40px; border: 1px solid black; border-radius: 10px;"></img>
                <p style="font-family: Roboto, sans-serif;">Bài hát bạn muốn thêm...</p>
                <button style="background-color: grey; color: black; padding: 5px 10px; font-weight: bold; border-radius: 20px;">Add</button>
            </div>
        </div>
    </div>
    
    <div class="edit_playlist_popup" id="edit_playlist_popup">
        <div class="popup_head" id="popup_head">
            <div class="popup_header" id="popup_header">Edit Playlist</div>
            <button class="popup_close" id="popup_close">&#128936;</button>
        </div>
        <div style="grid-template-columns: 240px 330px; gap: 30px; display: grid;">
            <div style="margin-left: 30px;">
                <img src="<?php echo htmlspecialchars($playlistInfo['Playlist_avatar_url'] ?? './img/default-playlist.jpg'); ?>" style="width: 200px; height: 200px; border: 3px solid black; object-fit: cover;">
                <button style="font-family: Roboto, sans-serif; width: fit-content; height: fit-content; background-color: rgba(217, 217, 217, 1); color: black; font-size: medium; padding: 10px; font-weight: bold; border-radius: 20px; justify-content: center; text-align: center; margin-top: 10px; margin-left: 40px;">Change Avatar</button>
            </div>
            <div style="font-family: Roboto, sans-serif;">
                <h4 style="margin-bottom: 10px; margin-top: 0px;">Playlist's Name</h4>
                <input type="text" value="<?php echo htmlspecialchars($playlistInfo['Playlist_name']); ?>" style="width: 300px; height: 30px; border-radius: 30px; background-color: rgba(217, 217, 217, 1); color: black; padding-left: 10px;">
                <h4 style="margin-bottom: 10px;">Description</h4>
                <input type="text" value="" style="width: 300px; height: 120px; border-radius: 30px; background-color: rgba(217, 217, 217, 1); color: black; padding-left: 10px;">
            </div>
        </div>
        <div style="grid-template-columns: 240px 150px 150px; gap: 30px; display: grid; margin-top: 10px;">
            <button class="changeSeenMode" id="changeSeenMode"><?php echo (isset($playlistInfo['is_public']) && $playlistInfo['is_public']) ? '🔓 Public' : '🔒 Private'; ?></button>
            <button style="width: 80%; height: fit-content; padding: 10px 20px; border-radius: 20px; font-family: Roboto, sans-serif; background-color: rgba(1, 1, 1, 1); color: white;">Save</button>
            <button style="width: 80%; height: fit-content; padding: 10px 20px; border-radius: 20px; font-family: Roboto, sans-serif; background-color: red; color: white;" onclick="document.getElementById('edit_playlist_popup').style.display='none'">Cancel</button>
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

    <script src="js/music-streaming-home.js"></script>
    <script src="js/music-streaming-playlist-info.js"></script>
    <script>
        // JS cho nút Play All
        document.getElementById('playPlaylistBtn')?.addEventListener('click', () => {
            if (typeof currentPlaylist !== 'undefined' && currentPlaylist.length > 0) {
                const firstSong = currentPlaylist[0];
                playSong(firstSong.url, firstSong.title, firstSong.artist, firstSong.img);
                currentIndex = 0;
            } else {
                alert('Playlist này hiện chưa có bài hát nào để phát!');
            }
        });
        
        // Đổi trạng thái nút Save
        document.getElementById('likeBtn')?.addEventListener('click', function() {
            this.classList.toggle('liked');
            if (this.classList.contains('liked')) this.textContent = '♥ Saved';
            else this.textContent = '♥ Save';
        });
    </script>
</body>
</html>