<?php
session_start();
require_once './includes/db-connect.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: music-streaming-login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// --- XỬ LÝ THÊM BÀI HÁT VÀO PLAYLIST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_song_id'])) {
    $add_song_id = intval($_POST['add_song_id']);
    $pl_id = isset($_POST['target_pl_id']) ? intval($_POST['target_pl_id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
    
    // Kiểm tra quyền (chỉ chủ sở hữu mới được thêm)
    $check_owner = $conn->prepare("SELECT User_id FROM Playlists WHERE Playlist_id = ?");
    $check_owner->bind_param("i", $pl_id);
    $check_owner->execute();
    $owner_res = $check_owner->get_result()->fetch_assoc();
    
    if ($owner_res && $owner_res['User_id'] == $user_id) {
        $check_exist = $conn->prepare("SELECT * FROM playlist_Song WHERE Playlist_id = ? AND Song_id = ?");
        $check_exist->bind_param("ii", $pl_id, $add_song_id);
        $check_exist->execute();
        if ($check_exist->get_result()->num_rows === 0) {
            $ins = $conn->prepare("INSERT INTO playlist_Song (Playlist_id, Song_id) VALUES (?, ?)");
            $ins->bind_param("ii", $pl_id, $add_song_id);
            $ins->execute();
            echo "<script>alert('Đã thêm bài hát vào Playlist!'); window.location.href='music-streaming-playlist-info.php?id=$pl_id';</script>";
            exit();
        } else {
            echo "<script>alert('Bài hát đã có trong Playlist!'); window.location.href='music-streaming-playlist-info.php?id=$pl_id';</script>";
            exit();
        }
    }
}

// --- XỬ LÝ CẬP NHẬT THÔNG TIN PLAYLIST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_playlist_id']) && isset($_GET['id'])) {
    $edit_pl_id = intval($_POST['edit_playlist_id']);
    $pl_id = intval($_GET['id']);
    $new_name = trim($_POST['playlist_name']);
    $is_public = intval($_POST['is_public']);

    // Kiểm tra có phải chủ sở hữu cập nhật Playlist không
    if ($edit_pl_id === $pl_id) {
        $check_owner = $conn->prepare("SELECT User_id, Playlist_avatar_url FROM Playlists WHERE Playlist_id = ?");
        $check_owner->bind_param("i", $pl_id);
        $check_owner->execute();
        $owner_res = $check_owner->get_result()->fetch_assoc();

        if ($owner_res && $owner_res['User_id'] == $user_id) {
            $avatar_url = $owner_res['Playlist_avatar_url']; // Giữ nguyên ảnh cũ nếu không update

            // Kiểm tra có tải ảnh mới lên không
            if (isset($_FILES['playlist_avatar']) && $_FILES['playlist_avatar']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['playlist_avatar']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $uploadDir = 'uploads/playlists/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    $newName = uniqid('pl_', true) . '.' . $ext;
                    if (move_uploaded_file($_FILES['playlist_avatar']['tmp_name'], $uploadDir . $newName)) {
                        $avatar_url = $uploadDir . $newName; // Cập nhật đường dẫn mới
                    }
                }
            }

            $stmt_update = $conn->prepare("UPDATE Playlists SET Playlist_name = ?, Playlist_avatar_url = ?, is_public = ? WHERE Playlist_id = ?");
            $stmt_update->bind_param("ssii", $new_name, $avatar_url, $is_public, $pl_id);
            $stmt_update->execute();

            echo "<script>alert('Cập nhật Playlist thành công!'); window.location.href='music-streaming-playlist-info.php?id=$pl_id';</script>";
            exit();
        }
    }
}

// 2. Lấy Avatar người dùng cho phần Header Dropdown
$avatarUrl = './img/avatar.jpg';
$stmt_avatar = $conn->prepare("SELECT User_avatar_url FROM users WHERE user_id = ?");
$stmt_avatar->bind_param("i", $user_id);
$stmt_avatar->execute();
$res_avatar = $stmt_avatar->get_result();
if ($row = $res_avatar->fetch_assoc()) {
    if (!empty($row['User_avatar_url'])) {
        $avatarUrl = $row['User_avatar_url'];
    }
}
$stmt_avatar->close();

// 3. Xác định dữ liệu là Album hay Playlist
$type_label = '';
$title = '';
$cover_img = '';
$owner_name = '';
$owner_img = '';
$year = '';
$songs = [];
$is_owner = false;
$artist_link = '#';

if (isset($_GET['album_id'])) {
    // --- NẾU LÀ ALBUM CỦA NGHỆ SĨ ---
    $album_id = intval($_GET['album_id']);
    $sql = "SELECT a.*, ar.Artist_name, ar.Avatar_url FROM Albums a JOIN Artists ar ON a.Artist_id = ar.Artist_id WHERE a.Album_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $album_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if ($data) {
        $type_label = 'Album';
        $title = $data['Album_title'];
        $cover_img = $data['Cover_image_url'] ?: './img/default-playlist.jpg';
        $owner_name = $data['Artist_name'];
        $owner_img = $data['Avatar_url'] ?: './img/default-artist.jpg';
        $year = $data['Release_date'] ? date('Y', strtotime($data['Release_date'])) : '';
        $artist_link = "music-streaming-artist-info.php?id=" . $data['Artist_id'];
        
        $sql_songs = "SELECT s.*, ar.Artist_name FROM Songs s LEFT JOIN Artists ar ON s.Artist_id = ar.Artist_id WHERE s.Album_id = ?";
        $stmt_s = $conn->prepare($sql_songs);
        $stmt_s->bind_param("i", $album_id);
        $stmt_s->execute();
        $songs = $stmt_s->get_result()->fetch_all(MYSQLI_ASSOC);
    }
} elseif (isset($_GET['id'])) {
    // --- NẾU LÀ PLAYLIST CÁ NHÂN TẠO ---
    $playlist_id = intval($_GET['id']);
    $sql = "SELECT p.*, u.User_name, u.User_avatar_url FROM Playlists p JOIN Users u ON p.User_id = u.User_id WHERE p.Playlist_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $playlist_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if ($data) {
        $type_label = 'Playlist';
        $title = $data['Playlist_name'];
        $cover_img = $data['Playlist_avatar_url'] ?: './img/default-playlist-avatar.jpg';
        $owner_name = $data['User_name'];
        $owner_img = $data['User_avatar_url'] ?: './img/avatar.jpg';
        $year = date('Y'); 
        $is_owner = ($data['User_id'] == $_SESSION['user_id']);
        $artist_link = "music-streaming-account.php";

        $sql_songs = "SELECT s.*, ar.Artist_name FROM playlist_Song ps JOIN Songs s ON ps.Song_id = s.Song_id LEFT JOIN Artists ar ON s.Artist_id = ar.Artist_id WHERE ps.Playlist_id = ?";
        $stmt_s = $conn->prepare($sql_songs);
        $stmt_s->bind_param("i", $playlist_id);
        $stmt_s->execute();
        $songs = $stmt_s->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

if (!$title) {
    echo "<script>alert('Không tìm thấy dữ liệu Album/Playlist!'); window.location.href='music-streaming-home.php';</script>";
    exit();
}

// Hàm format thời gian giây -> phút:giây
function formatDuration($seconds) {
    if (!$seconds) return "0:00";
    $m = floor($seconds / 60);
    $s = $seconds % 60;
    return $m . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eMuzik - <?php echo htmlspecialchars($title); ?></title> 
    <link rel="stylesheet" href='./css/playlist-info-style.css'>
    <link rel="stylesheet" href='./css/home-style.css'>
    <link href='https://fonts.googleapis.com/css?family=Passero One' rel='stylesheet'>
</head>
<body>
    <div style="display: flex;">
        <!-- CỘT NAVBAR -->
        <div>
            <nav class="navbar">
                <button class="logo_nav" onclick="document.location='./music-streaming-home.php'">
                    <h3 style="font-family: 'Passero One'; font-size: 48px; margin: 0px auto;">eMuzik</h3>
                </button>
                <div class="nav">
                    <table>
                        <tr>
                            <button class="nav_button" onclick="document.location='./music-streaming-home.php'">
                                <img class="nav_logo" src="./img/home.png"> Home</img>
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

            <!-- PHẦN THÔNG TIN PLAYLIST -->
            <div style="margin-bottom: 20px; cursor: pointer;">
                <div class="playlist-header" onclick="document.location='<?php echo $artist_link; ?>'">
                    <img class="playlist-image" src="<?php echo htmlspecialchars($cover_img); ?>" alt="cover"></img>
                    <div class="playlist-name">
                        <h4><?php echo $type_label; ?></h4>
                        <h2><?php echo htmlspecialchars($title); ?></h2>
                        <div style="margin-top: 0px; grid-template-columns: 30px 100px auto; display: grid; align-content: center;">
                            <img style="width: 30px; height: 30px; border-radius: 30px; object-fit: cover;" src="<?php echo htmlspecialchars($owner_img); ?>" alt="avatar"></img>
                            <h4 style="margin-left: 10px; margin-top: 5px; white-space: nowrap;"><?php echo htmlspecialchars($owner_name); ?></h4>
                            <h4 style="margin-left: 10px; margin-top: 5px;"> - <?php echo $year; ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($is_owner): ?>
            <div style="margin-left: 200px; margin-top: 20px; margin-bottom: 20px;">
                <button class="likeBtn" id="addSongsBtn" onclick="document.getElementById('add_songs_popup').style.display='block'; document.getElementById('popup_overlay').style.display='block';">Add Songs</button>
                <button class="likeBtn" id="editPlaylistBtn" onclick="document.getElementById('edit_playlist_popup').style.display='block'; document.getElementById('popup_overlay').style.display='block';">Edit Playlist</button>
            </div>
            <?php endif; ?>

            <!-- BẢNG BÀI HÁT THEO HTML CỦA BẠN -->
            <div>
                <table class="playlist-table">
                    <tr style="cursor: unset;">
                        <th>#</th>
                        <th>Title</th>
                        <th>Duration</th>
                    </tr>
                    <?php if (count($songs) > 0): ?>
                        <?php foreach ($songs as $index => $song): ?>
                            <!-- Nhấn vào 1 bài hát thì chuyển đến song-info tương ứng -->
                            <tr onclick="document.location='./music-streaming-song-info.php?id=<?php echo $song['Song_id']; ?>'">
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <div> 
                                        <h4><?php echo htmlspecialchars($song['Song_title']); ?></h4> 
                                        <h4 style="font-weight: lighter;"><?php echo htmlspecialchars($song['Artist_name'] ?? 'Unknown'); ?></h4> 
                                    </div>
                                </td>
                                <td><?php echo formatDuration($song['Duration']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px;">Không có bài hát nào.</td>
                        </tr>
                    <?php endif; ?>
                </table>
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
            
            <div style="height: 100px;"></div> <!-- Tránh bị player che -->
        </div>
    </div>

    <!-- LỚP PHỦ OVERLAY -->
    <div id="popup_overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.7); z-index: 1999;" onclick="this.style.display='none'; document.getElementById('add_songs_popup').style.display='none'; document.getElementById('edit_playlist_popup').style.display='none';"></div>

    <!-- POPUP TỪ HTML CỦA BẠN (ẨN THEO MẶC ĐỊNH) -->
    <div class="add_songs_popup" id="add_songs_popup" style="display: none; z-index: 2000;">
        <div class="popupHead" id="popupHead">
            <div class="popupHeader" id="popupHeader">Add Songs</div>
            <button class="popupClose" id="popupClose" onclick="document.getElementById('add_songs_popup').style.display='none'; document.getElementById('popup_overlay').style.display='none';">&#128936;</button>
        </div>
        <div class="popup_search" style="text-align: center;">
            <input type="text" id="search_add_song" placeholder="Search songs..."></input>
        </div>
        <div id="add_song_results" style="margin: 20px auto; overflow-y: auto; max-height: 300px;">
            <!-- Dữ liệu mẫu như HTML -->
            <p style="text-align: center; color: black; font-family: Roboto, sans-serif;">Gõ để tìm kiếm bài hát...</p>
        </div>
    </div>
    
    <div class="edit_playlist_popup" id="edit_playlist_popup" style="display: none; z-index: 2000;">
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="edit_playlist_id" value="<?php echo isset($_GET['id']) ? intval($_GET['id']) : 0; ?>">
            <div class="popup_head" id="popup_head">
                <div class="popup_header" id="popup_header">Edit Playlist</div>
                <button type="button" class="popup_close" id="popup_close" onclick="document.getElementById('edit_playlist_popup').style.display='none'; document.getElementById('popup_overlay').style.display='none';">&#128936;</button>
            </div>
            <div style="grid-template-columns: 240px 330px; gap: 30px; display: grid;">
                <div style="margin-left: 30px; text-align: center;">
                    <img id="edit_pl_preview" src="<?php echo htmlspecialchars($cover_img); ?>" style="width: 200px; height: 200px; border: 3px solid black; object-fit: cover;">
                    <input type="file" id="playlist_avatar" name="playlist_avatar" accept="image/*" style="display: none;" onchange="previewImage(event)">
                    <button type="button" onclick="document.getElementById('playlist_avatar').click()" style="font-family: Roboto, sans-serif; width: fit-content; height: fit-content; background-color: rgba(217, 217, 217, 1); color: black; font-size: medium; padding: 10px; font-weight: bold; border-radius: 20px; justify-content: center; text-align: center; margin-top: 10px;">Change Avatar</button>
                </div>
                <div style="font-family: Roboto, sans-serif;">
                    <h4 style="margin-bottom: 10px; margin-top: 0px;">Playlist's Name</h4>
                    <input type="text" name="playlist_name" value="<?php echo htmlspecialchars($title); ?>" style="width: 300px; height: 30px; border-radius: 30px; background-color: rgba(217, 217, 217, 1); color: black; padding-left: 10px;" required>
                    <h4 style="margin-bottom: 10px; margin-top: 10px;">Description</h4>
                    <input type="text" name="playlist_desc" value="" style="width: 300px; height: 120px; border-radius: 30px; background-color: rgba(217, 217, 217, 1); color: black; padding-left: 10px;">
                </div>
            </div>
            <div style="grid-template-columns: 240px 150px 150px; gap: 30px; display: grid; margin-top: 10px;">
                <?php $pl_public = isset($data['is_public']) ? $data['is_public'] : 0; ?>
                <input type="hidden" name="is_public" id="edit_is_public" value="<?php echo $pl_public; ?>">
                <button type="button" class="changeSeenMode" id="editChangeSeenMode"><?php echo $pl_public ? '🔓 Public' : '🔒 Private'; ?></button>
                <button type="submit" style="width: 80%; height: fit-content; padding: 10px 20px; border-radius: 20px; font-family: Roboto, sans-serif; background-color: rgba(1, 1, 1, 1); color: white; cursor: pointer;">Save</button>
                <button type="button" style="width: 80%; height: fit-content; padding: 10px 20px; border-radius: 20px; font-family: Roboto, sans-serif; background-color: red; color: white; cursor: pointer;" onclick="document.getElementById('edit_playlist_popup').style.display='none'; document.getElementById('popup_overlay').style.display='none';">Cancel</button>
            </div>
        </form>
    </div>

    <!-- TRÌNH PHÁT NHẠC (Yêu cầu có id chính xác để js cập nhật) -->
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

    <script src="./js/music-streaming-home.js"></script>
    <!-- Script xử lý Popup -->
    <script>
        // Preview Image cho Edit Playlist
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit_pl_preview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        // Chuyển đổi trạng thái Public / Private
        const editChangeSeenMode = document.getElementById('editChangeSeenMode');
        const editIsPublic = document.getElementById('edit_is_public');
        if (editChangeSeenMode && editIsPublic) {
            editChangeSeenMode.addEventListener('click', function() {
                if (this.textContent.includes('Private')) {
                    this.textContent = '🔓 Public';
                    editIsPublic.value = 1;
                } else {
                    this.textContent = '🔒 Private';
                    editIsPublic.value = 0;
                }
            });
        }

        // API Tìm kiếm bài hát và Add vào playlist
        const searchAddSong = document.getElementById('search_add_song');
        const addSongResults = document.getElementById('add_song_results');

        if (searchAddSong && addSongResults) {
            let addSearchTimeout;
            searchAddSong.addEventListener('input', (e) => {
                clearTimeout(addSearchTimeout);
                const query = e.target.value.trim();
                if (query.length === 0) {
                    addSongResults.innerHTML = '<p style="text-align: center; color: black; font-family: Roboto, sans-serif;">Gõ để tìm kiếm bài hát...</p>';
                    return;
                }

                addSearchTimeout = setTimeout(() => {
                    fetch(`includes/song_api.php?action=read&search=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            addSongResults.innerHTML = '';
                            if (data.success && data.data.length > 0) {
                                data.data.forEach(song => {
                                    addSongResults.innerHTML += `
                                        <div style="grid-template-columns: 40px 60% 60px; display: grid; gap: 10px; align-items: center; justify-content: center; border-top: 1px solid #ccc; padding: 10px 0;">
                                            <img src="${song.Song_image_url ? song.Song_image_url : './img/default-song.jpg'}" style="width: 40px; height: 40px; border: 1px solid black; border-radius: 10px; object-fit: cover;"></img>
                                            <p style="font-family: Roboto, sans-serif; color: black; margin: 0; text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${song.Song_title}</p>
                                            <form method="POST" action="" style="margin: 0;">
                                                <input type="hidden" name="add_song_id" value="${song.Song_id}">
                                                <input type="hidden" name="target_pl_id" value="<?php echo isset($_GET['id']) ? intval($_GET['id']) : 0; ?>">
                                                <button type="submit" style="background-color: grey; color: black; padding: 5px 10px; font-weight: bold; border-radius: 20px; border: none; cursor: pointer;">Add</button>
                                            </form>
                                        </div>
                                    `;
                                });
                            } else {
                                addSongResults.innerHTML = '<p style="text-align: center; color: black; font-family: Roboto, sans-serif;">Không tìm thấy bài hát nào.</p>';
                            }
                        }).catch(err => console.error(err));
                }, 300);
            });
        }
    </script>
</body>
</html>