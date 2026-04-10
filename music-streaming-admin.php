<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eMuzik - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">eMuzik Admin</div>
        <button class="nav-btn" onclick="window.location.href='music-streaming-home.php'"><i class="fas fa-home"></i> Return to Home</button>
        <button class="nav-btn active" onclick="switchTab('users', this)"><i class="fas fa-users"></i> Users Management</button>
        <button class="nav-btn" onclick="switchTab('songs', this)"><i class="fas fa-music"></i> Songs Management</button>
        <button class="nav-btn" onclick="switchTab('albums', this)"><i class="fas fa-list"></i> Albums Management</button>
        <button class="nav-btn" onclick="switchTab('artists', this)"><i class="fas fa-users"></i> Artists Management</button>
    </aside>

    <main class="main-content">
        <div id="users" class="table-container active">
             <div class="search" style="text-align: center; margin-bottom: 15px;">
                <input type="text" id="search_info" placeholder="Search for username" onkeyup="searchUser()" style="padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc;">
            </div>
            <div class="header">
                <h1>Users List</h1>
                <button class="btn-add" onclick="openModal('add')"><i class="fas fa-plus"></i> Add Admin</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>User_id</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="user-list-body"></tbody>
            </table>
        </div>

        <div id="songs" class="table-container">
            <div class="search" style="text-align: center; margin-bottom: 15px;">
                <input type="text" id="search_song" placeholder="Search for ID or song's name..." onkeyup="searchSongs()" style="padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc;">
            </div>
            <div class="header">
                <h1>Songs List</h1>
                <button class="btn-add" onclick="openSongModal('add')"><i class="fas fa-plus"></i> Add Song</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Song_id</th><th>Song_title</th><th>Album_id</th><th>View_count</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="song-list-body"></tbody>
            </table>
        </div>

        <div id="albums" class="table-container">
            <div class="search" style="text-align: center; margin-bottom: 15px;">
                <input type="text" id="search_album" placeholder="Search for album name or artist..." onkeyup="searchAlbums()" style="padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc;">
            </div>
            <div class="header">
                <h1>Albums List</h1>
                <button class="btn-add" onclick="openAlbumModal('add')"><i class="fas fa-plus"></i> Add Album</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Album_id</th><th>Album_title</th><th>Artist</th><th>Release Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="album-list-body"></tbody>
            </table>
        </div>

        <div id="artists" class="table-container">
            <div class="search" style="text-align: center; margin-bottom: 15px;">
                <input type="text" id="search_artist" placeholder="Search for artist name" onkeyup="searchArtists()" style="padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc;">
            </div>
            <div class="header">
                <h1>Artists List</h1>
                <button class="btn-add" onclick="openArtistModal('add')"><i class="fas fa-plus"></i> Add Artist</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Artist_id</th><th>Artist_name</th><th>Biography</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="artist-list-body"></tbody>
            </table>
        </div>

        <div id="crud-modal" class="admin-modal-overlay" style="display: none;">
            <div class="admin-modal-content" style="height: 450px;">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                <h2 id="modal-title">Add/Edit User</h2>
                <form id="crud-form">
                    <input type="hidden" id="user_id" name="user_id">
                    <div class="form-group-modern">
                        <label>Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group-modern">
                        <label>Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group-modern">
                        <label>Role</label>
                        <select id="role" name="role">
                            <option value="user">User</option>
                            <option value="artist">Artist</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn-save-modern">Save User</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="song-crud-modal" class="admin-modal-overlay" style="display: none;">
            <div class="admin-modal-content" style="width: 600px; height:600px">
                <span class="close-modal" onclick="closeSongModal()">&times;</span>
                <h2 id="song-modal-title">Add New Song</h2>
                <form id="song-crud-form" enctype="multipart/form-data" onsubmit="submitSong(event)">
                    <input type="hidden" id="song_id" name="song_id">
                    <div class="form-group-modern">
                        <label>Song Title</label>
                        <input type="text" id="song_title" name="song_title" required>
                    </div>
                    <div class="form-group-modern">
                        <label>Music File (.mp3, .wav)</label>
                        <input type="file" id="music_file" name="music_file" accept="audio/*">
                        <small id="current-file-name" style="color: #666; display: block; margin-top: 5px;"></small>
                    </div>


                    <div class="form-group-modern">
                        <label>Lyrics</label>
                        <textarea id="lyric" name="lyric" rows="4"></textarea>
                    </div>
                    <div class="form-group-modern">
                        <label>Album</label>
                        <select id="album_id" name="album_id">
                            <option value="">-- Select Album (Optional) --</option>
                        </select>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeSongModal()">Cancel</button>
                        <button type="submit" class="btn-save-modern">Save Song</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="album-crud-modal" class="admin-modal-overlay" style="display: none;">
            <div class="admin-modal-content" style="width: 550px; height: 550px;">
                <span class="close-modal" onclick="closeAlbumModal()">&times;</span>
                <h2 id="album-modal-title">Add Album</h2>
                <form id="album-crud-form" enctype="multipart/form-data" onsubmit="submitAlbum(event)">
                    <input type="hidden" id="album_id" name="album_id">
                    <div class="form-group-modern">
                        <label>Album Title</label>
                        <input type="text" id="album_title" name="album_title" required>
                    </div>
                    <div class="form-group-modern">
                        <label>Artist</label>
                        <select id="artist_id" name="artist_id" required>
                            <option value="">-- Select Artist --</option>
                        </select>
                    </div>
                    <div class="form-group-modern">
                        <label>Release Date</label>
                        <input type="date" id="release_date" name="release_date">
                    </div>
                    <div class="form-group-modern">
                        <label>Album Cover (JPG/PNG)</label>
                        <input type="file" id="cover" name="cover" accept="image/png, image/jpeg">
                        <small id="current-cover-name" style="color: #666; display: block; margin-top: 5px;"></small>
                    </div>
                    <div class="form-group-modern">
                        <label>Cover URL (fallback)</label>
                        <input type="text" id="cover_image_url" name="cover_image_url" placeholder="uploads/avatars/filename.jpg">
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeAlbumModal()">Cancel</button>
                        <button type="submit" class="btn-save-modern">Save Album</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="artist-crud-modal" class="admin-modal-overlay" style="display: none;">
            <div class="admin-modal-content" style="width: 500px; height: 520px;">
                <span class="close-modal" onclick="closeArtistModal()">&times;</span>
                <h2 id="artist-modal-title">Add/Edit Artist</h2>
                <form id="artist-crud-form" enctype="multipart/form-data" onsubmit="submitArtist(event)">
                    <input type="hidden" id="artist_id" name="artist_id">
                    <div class="form-group-modern">
                        <label>Artist Name</label>
                        <input type="text" id="artist_name" name="artist_name" required>
                    </div>
                    <div class="form-group-modern">
                        <label>Biography</label>
                        <textarea id="biography" name="biography" rows="4"></textarea>
                    </div>
                    <div class="form-group-modern">
                        <label>Avatar (JPG/PNG)</label>
                        <input type="file" id="avatar" name="avatar" accept="image/png, image/jpeg">
                        <small id="current-avatar-name" style="color: #666; display: block; margin-top: 5px;"></small>
                    </div>
                    <div class="form-group-modern">
                        <label>Avatar URL (fallback)</label>
                        <input type="text" id="avatar_url" name="avatar_url" placeholder="uploads/avatars/default.jpg">
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeArtistModal()">Cancel</button>
                        <button type="submit" class="btn-save-modern">Save Artist</button>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <script>
        function switchTab(tabId, btn) {
            document.querySelectorAll('.table-container').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
        }
    </script>
    <script src="js/admin.js"></script>
</body>
</html>