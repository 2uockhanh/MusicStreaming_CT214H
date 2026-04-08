document.getElementById('avatar-input').onchange = function() {
    const file = this.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('Please select an image file.');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const size = 150; 
            const canvas = document.createElement('canvas');
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');

            let drawWidth, drawHeight, offsetX, offsetY;
            const imgRatio = img.width / img.height;

            if (imgRatio > 1) {
                drawHeight = size;
                drawWidth = img.width * (size / img.height);
                offsetX = (size - drawWidth) / 2;
                offsetY = 0;
            } else {
                drawWidth = size;
                drawHeight = img.height * (size / img.width);
                offsetX = 0;
                offsetY = (size - drawHeight) / 2;
            }

            ctx.clearRect(0, 0, size, size);
            ctx.drawImage(img, offsetX, offsetY, drawWidth, drawHeight);

            const preview = document.getElementById('avatar-preview');
            const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
            preview.innerHTML = `<img src="${dataUrl}" alt="Avatar">`;

            canvas.toBlob(blob => {
                if (!blob) {
                    alert('Unable to process avatar image.');
                    return;
                }

                const formData = new FormData();
                formData.append('avatar', blob, 'avatar.jpg');

                fetch('../includes/upload-avatar-handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Avatar updated successfully!');
                        const headerIcon = document.querySelector('.user-icon img');
                        if(headerIcon) headerIcon.src = dataUrl;
                    } else {
                        alert('Failed to update: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while uploading.');
                });
            }, 'image/jpeg', 0.95);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
};