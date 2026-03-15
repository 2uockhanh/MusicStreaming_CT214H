<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Nghe Nhạc</title>
</head>
<body>

    <header>
        <button id="themeToggleBtn">Đổi giao diện Sáng/Tối</button>
    </header>

    <main>
        <h1>Danh sách bài hát</h1>
        <ul>
            <li>Bài hát 1 - Ca sĩ A</li>
            <li>Bài hát 2 - Ca sĩ B</li>
        </ul>
    </main>

    <div class="player-container">
        <audio id="mainAudio" src=""></audio>
        
        <span id="currentSongInfo">Chưa chọn bài hát</span><br><br>
        <button id="playPauseBtn">Play</button>
    </div>

    <script>
        // 1. Xử lý logic Sáng/Tối
        const themeBtn = document.getElementById('themeToggleBtn');
        const htmlElement = document.documentElement;

        themeBtn.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            htmlElement.setAttribute('data-theme', newTheme);
            
            // Có thể lưu vào localStorage để giữ theme khi reload trang
            localStorage.setItem('theme', newTheme);
        });

        // Load theme từ localStorage khi vào trang
        if(localStorage.getItem('theme')) {
            htmlElement.setAttribute('data-theme', localStorage.getItem('theme'));
        }

        // 2. Xử lý logic Play/Pause cơ bản
        const audio = document.getElementById('mainAudio');
        const playPauseBtn = document.getElementById('playPauseBtn');

        playPauseBtn.addEventListener('click', () => {
            // Check nếu chưa có source nhạc thì không làm gì
            if (!audio.src || audio.src === window.location.href) return;

            if (audio.paused) {
                audio.play();
                playPauseBtn.innerText = 'Pause';
            } else {
                audio.pause();
                playPauseBtn.innerText = 'Play';
            }
        });
    </script>
</body>
</html>