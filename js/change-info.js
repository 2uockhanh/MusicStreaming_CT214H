document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.querySelector('input[name="username"]');
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const btnConfirm = document.querySelector('.btn-confirm');
    const btnCancel = document.querySelector('.btn-cancel');

    let originalDataUser = {
        username: usernameInput.value,
        email: emailInput.value,
    };

    btnCancel.addEventListener('click', function() {
        usernameInput.value = originalDataUser.username;
        emailInput.value = originalDataUser.email;
        passwordInput.value = '';
        alert('Changes have been discarded.');
    });

    btnConfirm.addEventListener('click', function() {
        const newDataUser = {
            username: usernameInput.value.trim(),
            email: emailInput.value.trim(),
            password: passwordInput.value
        };

        if (!newDataUser.username || !newDataUser.email) {
            alert('Please fill in all required fields.');
            return;
        }

        fetch('./includes/profile-handler.php', { 
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(newDataUser)
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                alert('Profile updated successfully!');
                originalDataUser.username = newDataUser.username;
                originalDataUser.email = newDataUser.email;
                passwordInput.value = '';
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