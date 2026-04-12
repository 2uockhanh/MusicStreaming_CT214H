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

    fetch('includes/upload-avatar-handler.php', {
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