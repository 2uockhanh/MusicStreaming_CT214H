document.addEventListener('DOMContentLoaded', () => {
    loadUsers(); 
    loadSongs(); 
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

function openSongModal(mode, dataString = null) {
    const modal = document.getElementById('song-crud-modal');
    const form = document.getElementById('song-crud-form');
    form.reset();
    
    // Reset thông báo file cũ
    const fileNameDisplay = document.getElementById('current-file-name');
    if(fileNameDisplay) fileNameDisplay.innerText = '';

    if(mode === 'add') {
        document.getElementById('song-modal-title').innerText = 'Add New Song';
        document.getElementById('song_id').value = '';
    } else if (mode === 'edit') {
        const data = JSON.parse(decodeURIComponent(dataString));
        document.getElementById('song-modal-title').innerText = 'Edit Song';
        document.getElementById('song_id').value = data.Song_id;
        document.getElementById('song_title').value = data.Song_title;
        document.getElementById('lyric').value = data.Lyric || '';
        document.getElementById('album_id').value = data.Album_id || '';
        document.getElementById('view_count').value = data.View_count;
        
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