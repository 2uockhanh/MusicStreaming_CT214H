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
        <button class="nav-btn active" onclick="switchTab('users', this)"><i class="fas fa-users"></i> Users Management</button>
        <button class="nav-btn" onclick="switchTab('songs', this)"><i class="fas fa-music"></i> Songs Management</button>
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
                    <div class="form-row-multi" style="display: flex; gap: 15px;">
                        <div class="form-group-modern" style="flex: 1;">
                            <label>Album ID</label>
                            <input type="number" id="album_id" name="album_id">
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeSongModal()">Cancel</button>
                        <button type="submit" class="btn-save-modern">Save Song</button>
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