document.getElementById('avatar-input').onchange = function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Avatar">`;
        }
        reader.readAsDataURL(file);
    }

    const formData = new FormData();
    formData.append('avatar', file);

    fetch('../includes/handle-update-playlist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Avatar updated successfully!');
            // Cập nhật tất cả các avatar trên giao diện theo ảnh mới
            document.querySelectorAll('img[alt="Avatar"]').forEach(img => {
                img.src = data.url;
            });
        } else {
            alert('Failed to update avatar: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the avatar.');
    });
}
document.addEventListener('DOMContentLoaded', function() {
    const playlistnameInput = document.querySelector('input[name="playlistName"]');
    const changeSeenMode = document.getElementById("changeSeenMode").textContent === '🔒 Private' ? 0 : 1;
    //const emailInput = document.querySelector('input[name="email"]');
    //const passwordInput = document.querySelector('input[name="password"]');
    const btnCreate = document.getElementById('createPlaylist');
    const btnCancel = document.getElementById('cancelPlaylist');
    
    let originalDataUser = {
        playlistname: playlistnameInput.value,
        changeSeenMode: changeSeenMode
    };
    
    btnCancel.addEventListener('click', function() {
        playlistnameInput.value = originalDataUser.playlistname;
        document.getElementById("changeSeenMode").textContent = '🔒 Private';
        //emailInput.value = originalDataUser.email;
        //passwordInput.value = '';
        alert('Changes have been discarded.');
    });

    btnCreate.addEventListener('click', function() {
        const newDataPlaylist = {
            playlistname: playlistnameInput.value.trim(),
            changeSeenMode: changeSeenMode
            //email: emailInput.value.trim(),
            //password: passwordInput.value
        };

        if (!newDataPlaylist.playlistname) { //|| !newDataUser.email
            alert('Please fill in all required fields.');
            return;
        }

        fetch('../includes/handle-update-playlist.php', { 
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(newDataPlaylist)
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                alert('Playlist created successfully!');
                document.getElementById('add_playlist_popup').style.display = "none";
                //originalDataUser.username = '';
                //originalDataUser.email = newDataUser.email;
                //passwordInput.value = '';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Connection Error:', err);
            alert('Could not connect to server.');
        });
    });
});