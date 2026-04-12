document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
    loadSongs();
});

// ==================== QUẢN LÝ NGƯỜI DÙNG ====================
function loadUsers(searchQuery = '') {
    const formData = new FormData();
    formData.append('action', 'read');
    if (searchQuery) formData.append('search', searchQuery);

    fetch('includes/user_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            const tbody = document.getElementById('user-list-body');
            tbody.innerHTML = '';
            response.data.forEach(user => {
                const roleBadge = user.Role === 'admin' ? 'badge-admin' : 'badge-user';
                tbody.innerHTML += `
                    <tr>
                        <td>${user.User_id}</td>
                        <td>${user.User_name}</td>
                        <td>${user.Email}</td>
                        <td><span class="badge ${roleBadge}">${user.Role}</span></td>
                        <td>
                            <button class="action-btn btn-delete" onclick="deleteUser(${user.User_id})"><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                `;
            });
        }
    })
    .catch(err => console.error('Lỗi load user:', err));
}

function searchUser() {
    const query = document.getElementById('search_info').value;
    loadUsers(query);
}

function deleteUser(id) {
    if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('includes/user_api.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            if (res.success) loadUsers();
        });
    }
}

// ==================== QUẢN LÝ BÀI HÁT ====================
function loadSongs(searchQuery = '') {
    let url = 'includes/song_api.php?action=read';
    if (searchQuery) url += '&search=' + encodeURIComponent(searchQuery);

    fetch(url)
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            const tbody = document.getElementById('song-list-body');
            tbody.innerHTML = '';
            response.data.forEach(song => {
                tbody.innerHTML += `
                    <tr>
                        <td>${song.Song_id}</td>
                        <td>${song.Song_title}</td>
                        <td>${song.Album_id || 'N/A'}</td>
                        <td>${song.View_count || 0}</td>
                        <td>
                            <button class="action-btn btn-delete" onclick="deleteSong(${song.Song_id})"><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                `;
            });
        }
    })
    .catch(err => console.error('Lỗi load song:', err));
}

function searchSongs() {
    const query = document.getElementById('search_song').value;
    loadSongs(query);
}

function deleteSong(id) {
    if (confirm('Bạn có chắc chắn muốn xóa bài hát này?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('includes/song_api.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            alert(res.message);
            if (res.success) loadSongs();
        });
    }
}

// ==================== ĐÓNG/MỞ MODAL ====================
function openModal(type) { document.getElementById('crud-modal').style.display = 'flex'; }
function closeModal() { document.getElementById('crud-modal').style.display = 'none'; }
function openSongModal(type) { document.getElementById('song-crud-modal').style.display = 'flex'; }
function closeSongModal() { document.getElementById('song-crud-modal').style.display = 'none'; }