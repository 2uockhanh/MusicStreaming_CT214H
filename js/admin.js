document.addEventListener('DOMContentLoaded', () => {
    loadUsers(); 
    loadSongs(); 
    loadArtists();
    loadAlbums();
    // loadArtistsForDropdown() sẽ được gọi khi mở album modal
});

// ==================== QUẢN LÝ USER ====================



// async function  loadUsers(searchQuery = '') {
//     try{
//         const reponse = await fetch ('includes/user_api.php?action=read&search=${encodeURIComponent(searchQuery)}');
//         const result = await reponse.json();
//         if(result.success){
//             const tbody = document.querySelector('#User-list-body');
//             tbody.innerHTML='';
//             result.data.forEach(user => {
//                 let badgeClass = user.Role === 'admin' ? 'badge-admin' :'badge-user';
//                 let tr = document.createElement('tr');
//                 const userData = encodeURIComponent(JSON.stringify(user));

//                 tr.innerHTML = `
//                     <td>${user.User_id}</td>
//                     <td>${user.User_name}</td>
//                     <td>${user.Email}</td>
//                     <td><span class="badge ${badgeClass}">${user.Role}</span></td>
//                     <td>
//                         <button class="action-btn btn-edit" onclick="openModal('edit', '${userData}')"><i class="fas fa-edit"></i></button>
//                         <button class="action-btn btn-delete" onclick="deleteUser(${user.User_id})"><i class="fas fa-trash"></i></button>
//                     </td>
//                 `;
               
//             });
//              tbody.appendChild(tr);
//         }
//     } catch (error){
//         console.error('Can not connect to User:',error);
//     };
// }
async function loadUsers(searchQuery = '') {
    try {

        const formData = new FormData();
        formData.append('action', 'read');
        if (searchQuery !== '') {
            formData.append('search', searchQuery);
        }


        const response = await fetch('includes/user_api.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();

        if (result.success) {
            const tbody = document.querySelector('#user-list-body');
            tbody.innerHTML = ''; 

            result.data.forEach(user => {
                let badgeClass = user.Role === 'admin' ? 'badge-admin' : 'badge-user';
                let tr = document.createElement('tr');
                
                const userData = encodeURIComponent(JSON.stringify(user));

                tr.innerHTML = `
                    <td>${user.User_id}</td>
                    <td>${user.User_name}</td>
                    <td>${user.Email}</td>
                    <td><span class="badge ${badgeClass}">${user.Role}</span></td>
                    <td>
                        <button class="action-btn btn-edit" onclick="openModal('edit', '${userData}')"><i class="fas fa-edit"></i></button>
                        <button class="action-btn btn-delete" onclick="deleteUser(${user.User_id})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (error) { console.error('Lỗi kết nối User:', error); }
}

function searchUser(){
    const keyword = document.getElementById('search_info').value;
    loadUsers(keyword);
}
function openModal(mode, dataString = null) {
    const modal = document.getElementById('crud-modal');
    const form = document.getElementById('crud-form');
    form.reset(); 
    
    if(mode === 'add') {
        document.getElementById('modal-title').innerText = 'Add User';
        document.getElementById('user_id').value = ''; 
    } else if (mode === 'edit') {
        const user = JSON.parse(decodeURIComponent(dataString));
        document.getElementById('modal-title').innerText = 'Edit User';
        document.getElementById('user_id').value = user.User_id;
        document.getElementById('username').value = user.User_name;
        document.getElementById('email').value = user.Email;
        document.getElementById('role').value = user.Role;
    }
    modal.style.display = 'flex';
}

function closeModal() { document.getElementById('crud-modal').style.display = 'none'; }

document.getElementById('crud-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', formData.get('user_id') ? 'update' : 'create');

    try {
        const response = await fetch('includes/user_api.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            closeModal(); 
            loadUsers();  
        } else alert('Lỗi: ' + result.message);
    } catch (error) { console.error('Lỗi API User:', error); }
});

async function deleteUser(id) {
    if (confirm('Xóa tài khoản này?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        try {
            const response = await fetch('includes/user_api.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) loadUsers();
            else alert('Lỗi: ' + result.message);
        } catch (error) { console.error('Lỗi API:', error); }
    }
}

// ==================== QUẢN LÝ ARTIST ====================

async function loadArtists(searchQuery = '') {
    try {
        const formData = new FormData();
        formData.append('action', 'read');
        if (searchQuery !== '') {
            formData.append('search', searchQuery);
        }

        const response = await fetch('includes/artist_api.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            const tbody = document.getElementById('artist-list-body');
            tbody.innerHTML = '';

            result.data.forEach(artist => {
                const artistData = encodeURIComponent(JSON.stringify(artist));
                let biography = artist.Biography || '';
                if (biography.length > 80) biography = biography.slice(0, 80) + '...';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${artist.Artist_id}</td>
                    <td>${artist.Artist_name}</td>
                    <td>${biography}</td>
                    <td>
                        <button class="action-btn btn-edit" onclick="openArtistModal('edit', '${artistData}')"><i class="fas fa-edit"></i></button>
                        <button class="action-btn btn-delete" onclick="deleteArtist(${artist.Artist_id})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (error) { console.error('Lỗi tải artist:', error); }
}

function searchArtists() {
    const keyword = document.getElementById('search_artist').value;
    loadArtists(keyword);
}

function openArtistModal(mode, dataString = null) {
    const modal = document.getElementById('artist-crud-modal');
    const form = document.getElementById('artist-crud-form');
    form.reset();
    document.getElementById('current-avatar-name').innerText = '';

    if (mode === 'add') {
        document.getElementById('artist-modal-title').innerText = 'Add Artist';
        document.getElementById('artist_id').value = '';
        document.getElementById('artist_name').value = '';
        document.getElementById('biography').value = '';
        document.getElementById('avatar_url').value = '';
    } else if (mode === 'edit') {
        const artist = JSON.parse(decodeURIComponent(dataString));
        document.getElementById('artist-modal-title').innerText = 'Edit Artist';
        document.getElementById('artist_id').value = artist.Artist_id;
        document.getElementById('artist_name').value = artist.Artist_name;
        document.getElementById('biography').value = artist.Biography || '';
        document.getElementById('avatar_url').value = artist.Avatar_url || '';
        document.getElementById('current-avatar-name').innerText = artist.Avatar_url ? 'Current avatar: ' + artist.Avatar_url : '';
    }

    modal.style.display = 'flex';
}

function closeArtistModal() { document.getElementById('artist-crud-modal').style.display = 'none'; }

async function submitArtist(e) {
    e.preventDefault();
    const form = document.getElementById('artist-crud-form');
    const formData = new FormData(form);
    const action = formData.get('artist_id') ? 'update' : 'create';
    formData.append('action', action);

    try {
        const response = await fetch('includes/artist_api.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            closeArtistModal();
            loadArtists();
        } else {
            alert('Lỗi: ' + result.message);
        }
    } catch (error) {
        console.error('Lỗi API Artist:', error);
        alert('Lỗi hệ thống khi gửi dữ liệu artist!');
    }
}

async function deleteArtist(id) {
    if (confirm('Xóa artist này?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        try {
            const response = await fetch('includes/artist_api.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) loadArtists();
            else alert('Lỗi: ' + result.message);
        } catch (error) { console.error(error); }
    }
}

// ==================== QUẢN LÝ ALBUM ====================

// Load danh sách artist cho dropdown
async function loadArtistsForDropdown() {
    try {
        const formData = new FormData();
        formData.append('action', 'get_artists');
        
        const response = await fetch('includes/album_api.php', { 
            method: 'POST', 
            body: formData 
        });
        
        const result = await response.json();
        console.log('Artist dropdown result:', result);
        
        if (result.success && result.data) {
            const select = document.getElementById('artist_id');
            select.innerHTML = '<option value="">-- Select Artist --</option>';
            result.data.forEach(artist => {
                const option = document.createElement('option');
                option.value = artist.Artist_id;
                option.textContent = artist.Artist_name;
                select.appendChild(option);
            });
            console.log('Loaded ' + result.data.length + ' artists');
        } else {
            console.error('API error:', result.message);
        }
    } catch (error) { 
        console.error('Lỗi tải artist dropdown:', error); 
    }
}

// Load danh sách album
async function loadAlbums(searchQuery = '') {
    try {
        const formData = new FormData();
        formData.append('action', 'read');
        if (searchQuery !== '') {
            formData.append('search', searchQuery);
        }

        const response = await fetch('includes/album_api.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            const tbody = document.getElementById('album-list-body');
            tbody.innerHTML = '';

            result.data.forEach(album => {
                const albumData = encodeURIComponent(JSON.stringify(album));
                const releaseDate = album.Release_date ? album.Release_date : 'N/A';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${album.Album_id}</td>
                    <td>${album.Album_title}</td>
                    <td>${album.Artist_name || 'Unknown'}</td>
                    <td>${releaseDate}</td>
                    <td>
                        <button class="action-btn btn-edit" onclick="openAlbumModal('edit', '${albumData}')"><i class="fas fa-edit"></i></button>
                        <button class="action-btn btn-delete" onclick="deleteAlbum(${album.Album_id})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (error) { console.error('Lỗi tải album:', error); }
}

function searchAlbums() {
    const keyword = document.getElementById('search_album').value;
    loadAlbums(keyword);
}

function openAlbumModal(mode, dataString = null) {
    const modal = document.getElementById('album-crud-modal');
    const form = document.getElementById('album-crud-form');
    form.reset();
    document.getElementById('current-cover-name').innerText = '';

    // Reload artist dropdown mỗi lần mở modal
    loadArtistsForDropdown();

    if (mode === 'add') {
        document.getElementById('album-modal-title').innerText = 'Add Album';
        document.getElementById('album_id').value = '';
        document.getElementById('album_title').value = '';
        document.getElementById('artist_id').value = '';
        document.getElementById('release_date').value = '';
        document.getElementById('cover_image_url').value = '';
    } else if (mode === 'edit') {
        const album = JSON.parse(decodeURIComponent(dataString));
        document.getElementById('album-modal-title').innerText = 'Edit Album';
        document.getElementById('album_id').value = album.Album_id;
        document.getElementById('album_title').value = album.Album_title;
        document.getElementById('artist_id').value = album.Artist_id || '';
        document.getElementById('release_date').value = album.Release_date || '';
        document.getElementById('cover_image_url').value = album.Cover_image_url || '';
        document.getElementById('current-cover-name').innerText = album.Cover_image_url ? 'Current cover: ' + album.Cover_image_url : '';
    }

    modal.style.display = 'flex';
}

function closeAlbumModal() { document.getElementById('album-crud-modal').style.display = 'none'; }

async function submitAlbum(e) {
    e.preventDefault();
    const form = document.getElementById('album-crud-form');
    const formData = new FormData(form);
    const action = formData.get('album_id') ? 'update' : 'create';
    formData.append('action', action);

    try {
        const response = await fetch('includes/album_api.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            closeAlbumModal();
            loadAlbums();
        } else {
            alert('Lỗi: ' + result.message);
        }
    } catch (error) {
        console.error('Lỗi API Album:', error);
        alert('Lỗi hệ thống khi gửi dữ liệu album!');
    }
}

async function deleteAlbum(id) {
    if (confirm('Xóa album này?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        try {
            const response = await fetch('includes/album_api.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) loadAlbums();
            else alert('Lỗi: ' + result.message);
        } catch (error) { console.error(error); }
    }
}

// ==================== QUẢN LÝ BÀI HÁT (SONGS) ====================
// Thêm tham số searchQuery mặc định là rỗng
async function loadSongs(searchQuery = '') {
    try {
        // Gắn thêm search vào đường dẫn API
        const response = await fetch(`includes/song_api.php?action=read&search=${encodeURIComponent(searchQuery)}`);
        const result = await response.json();

        if (result.success) {
            const tbody = document.getElementById('song-list-body');
            tbody.innerHTML = ''; 

            result.data.forEach(song => {
                let tr = document.createElement('tr');
                let album = song.Album_id ? song.Album_id : 'Single'; 
                
                const songData = encodeURIComponent(JSON.stringify(song));

                tr.innerHTML = `
                    <td>${song.Song_id}</td>
                    <td>${song.Song_title}</td>
                    <td>${album}</td>
                    <td>${song.View_count}</td>
                    <td>
                        <button class="action-btn btn-edit" onclick="openSongModal('edit', '${songData}')"><i class="fas fa-edit"></i></button>
                        <button class="action-btn btn-delete" onclick="deleteSong(${song.Song_id})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    } catch (error) { console.error('Lỗi tải bài hát:', error); }
}

// Hàm tìm kiếm bài hát
function searchSongs() {
    const keyword = document.getElementById('search_song').value;
    loadSongs(keyword);
}

// Load danh sách album cho dropdown Song
async function loadAlbumsForSongDropdown() {
    try {
        const formData = new FormData();
        formData.append('action', 'get_albums');
        
        const response = await fetch('includes/album_api.php', { 
            method: 'POST', 
            body: formData 
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            const select = document.getElementById('album_id');
            select.innerHTML = '<option value="">-- Select Album (Optional) --</option>';
            result.data.forEach(album => {
                const option = document.createElement('option');
                option.value = album.Album_id;
                option.textContent = album.Album_title;
                select.appendChild(option);
            });
        }
    } catch (error) { 
        console.error('Lỗi tải album dropdown:', error); 
    }
}

function openSongModal(mode, dataString = null) {
    const modal = document.getElementById('song-crud-modal');
    const form = document.getElementById('song-crud-form');
    form.reset();
    
    // Reset thông báo file cũ
    const fileNameDisplay = document.getElementById('current-file-name');
    if(fileNameDisplay) fileNameDisplay.innerText = '';

    // Reload album dropdown mỗi lần mở modal
    loadAlbumsForSongDropdown();

    if(mode === 'add') {
        document.getElementById('song-modal-title').innerText = 'Add New Song';
        document.getElementById('song_id').value = '';
        document.getElementById('song_title').value = '';
        document.getElementById('lyric').value = '';
        document.getElementById('album_id').value = '';
    } else if (mode === 'edit') {
        const data = JSON.parse(decodeURIComponent(dataString));
        document.getElementById('song-modal-title').innerText = 'Edit Song';
        document.getElementById('song_id').value = data.Song_id;
        document.getElementById('song_title').value = data.Song_title;
        document.getElementById('lyric').value = data.Lyric || '';
        document.getElementById('album_id').value = data.Album_id || '';
        
        if(fileNameDisplay) {
            fileNameDisplay.innerText = "File hiện tại: " + (data.File_url || 'Chưa có');
        }
    }
    modal.style.display = 'flex';
}

function closeSongModal() { document.getElementById('song-crud-modal').style.display = 'none'; }

// Hàm submit bài hát riêng biệt để xử lý upload file an toàn
async function submitSong(e) {
    e.preventDefault(); 
    const form = document.getElementById('song-crud-form');
    const formData = new FormData(form);
    const action = formData.get('song_id') ? 'update' : 'create';
    formData.append('action', action);

    try {
        const response = await fetch('includes/song_api.php', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            closeSongModal();
            loadSongs(); 
        } else {
            alert('Lỗi: ' + result.message);
        }
    } catch (error) { 
        console.error('Lỗi API Song:', error); 
        alert('Lỗi hệ thống khi gửi dữ liệu!');
    }
}

async function deleteSong(id) {
    if (confirm('Xóa bài hát này?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        try {
            const response = await fetch('includes/song_api.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) loadSongs();
            else alert('Lỗi: ' + result.message);
        } catch (error) { console.error(error); }
    }
}