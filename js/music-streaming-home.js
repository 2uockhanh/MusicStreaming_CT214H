let currentPlaylist = [];
let currentIndex = -1;

// Hàm lưu trạng thái Trình phát nhạc vào LocalStorage
function savePlayerState() {
    const audio = document.getElementById('audio-player');
    if (!audio || !audio.src) return;
    const state = {
        title: document.getElementById('player-title').innerText,
        artist: document.getElementById('player-artist').innerText,
        img: document.getElementById('player-img').src,
        originalUrl: audio.getAttribute('data-original-url') || '',
        currentTime: audio.currentTime,
        isPlaying: !audio.paused
    };
    localStorage.setItem('emuzik_player_state', JSON.stringify(state));
}

// Hàm này được đưa ra global scope để các trang khác (như song-info) có thể gọi
function playSong(url, title, artist, img) {
    const audio = document.getElementById('audio-player');
    const playBtn = document.getElementById('btn-play');

    document.getElementById('player-title').innerText = title;
    document.getElementById('player-artist').innerText = artist;
    document.getElementById('player-img').src = img;
    
    // Đảm bảo URL nhạc trỏ đúng thư mục gốc (xử lý đường dẫn với project có tên emuzik)
    let audioUrl = url;
    if (audioUrl && !audioUrl.startsWith('http')) {
        // Loại bỏ các ký tự thừa ở đầu (./ hoặc /)
        if (audioUrl.startsWith('./')) audioUrl = audioUrl.substring(2);
        if (audioUrl.startsWith('/')) audioUrl = audioUrl.substring(1);
        
        // Gắn thêm thư mục gốc /emuzik/ vào trước đường dẫn
        if (!audioUrl.startsWith('emuzik/')) {
            audioUrl = '/emuzik/' + audioUrl; 
        } else {
            audioUrl = '/' + audioUrl;
        }
    }

    if (audioUrl) {
        console.log("🎵 Trình duyệt đang tìm file nhạc tại: ", audioUrl);
        console.log("🔗 Đường dẫn gốc (từ DB) là: ", url);
        
        audio.src = audioUrl;
        audio.setAttribute('data-original-url', url);
        audio.play().catch(e => {
            console.error("❌ Chi tiết lỗi phát nhạc:", e);
            alert("Không thể phát nhạc! Hãy nhấn F12, mở tab Console để xem đường dẫn đang bị sai ở đâu.");
        });
    } else {
        alert("Bài hát này chưa được cập nhật file âm thanh trong hệ thống!");
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. MUSIC PLAYER LOGIC ---
    const audio = document.getElementById('audio-player');
    const playBtn = document.getElementById('btn-play');
    const prevBtn = document.getElementById('btn-prev');
    const nextBtn = document.getElementById('btn-next');
    const progressBar = document.getElementById('progress-bar');
    const volumeBar = document.getElementById('volume-bar');
    const currentTimeEl = document.getElementById('current-time');
    const totalTimeEl = document.getElementById('total-time');

    // 1.1 KHÔI PHỤC TRẠNG THÁI NHẠC TỪ LOCAL STORAGE (Trải nghiệm nghe nhạc xuyên suốt)
    function loadPlayerState() {
        const saved = localStorage.getItem('emuzik_player_state');
        if (saved) {
            try {
                const state = JSON.parse(saved);
                if (state.originalUrl) {
                    document.getElementById('player-title').innerText = state.title;
                    document.getElementById('player-artist').innerText = state.artist;
                    document.getElementById('player-img').src = state.img;
                    
                    let audioUrl = state.originalUrl;
                    if (audioUrl && !audioUrl.startsWith('http')) {
                        if (audioUrl.startsWith('./')) audioUrl = audioUrl.substring(2);
                        if (audioUrl.startsWith('/')) audioUrl = audioUrl.substring(1);
                        
                        if (!audioUrl.startsWith('emuzik/')) {
                            audioUrl = '/emuzik/' + audioUrl;
                        } else {
                            audioUrl = '/' + audioUrl;
                        }
                    }
                    
                    audio.src = audioUrl;
                    audio.setAttribute('data-original-url', state.originalUrl);
                    audio.currentTime = state.currentTime || 0;
                    
                    // Tự động phát tiếp nếu trước đó đang phát (Trình duyệt có thể chặn nếu user chưa click)
                    if (state.isPlaying) {
                        audio.play().then(() => playBtn.innerHTML = '⏸').catch(() => playBtn.innerHTML = '▶');
                    }
                }
            } catch(e) {}
        }
    }
    loadPlayerState();

    // Thu thập danh sách bài hát đang hiển thị ở trang chủ để làm Playlist
    const songElements = document.querySelectorAll('.song_nav');
    songElements.forEach((el, index) => {
        currentPlaylist.push({
            id: el.getAttribute('data-id'),
            url: el.getAttribute('data-url'),
            title: el.getAttribute('data-title'),
            artist: el.getAttribute('data-artist'),
            img: el.getAttribute('data-img')
        });
    });

    function playSongIndex(index) {
        if (index < 0 || index >= currentPlaylist.length) return;
        currentIndex = index;
        const song = currentPlaylist[currentIndex];
        playSong(song.url, song.title, song.artist, song.img);
    }

    // Phát / Tạm dừng
    playBtn.addEventListener('click', () => {
        if (!audio.src || audio.src === window.location.href) {
            // Nếu chưa chọn bài nào, hãy phát bài đầu tiên trong danh sách
            if (currentPlaylist.length > 0) {
                playSongIndex(0);
            } else {
                alert("Không có bài hát nào trong danh sách để phát!");
            }
            return;
        }
        if (audio.paused) {
            audio.play().catch(e => console.error("Lỗi Resume nhạc:", e));
        } else {
            audio.pause();
        }
    });

    // Lắng nghe sự kiện play/pause để lưu trạng thái
    audio.addEventListener('play', () => {
        playBtn.innerHTML = '⏸';
        savePlayerState();
    });
    audio.addEventListener('pause', () => {
        playBtn.innerHTML = '▶';
        savePlayerState();
    });
    audio.addEventListener('error', () => {
        document.getElementById('player-title').innerText = "Lỗi file nhạc";
        playBtn.innerHTML = '▶';
    });
    
    // Hàm lấy index bài hát ngẫu nhiên (đảm bảo không lặp lại bài vừa phát)
    function getRandomIndex() {
        if (currentPlaylist.length <= 1) return 0;
        let randomIdx;
        do {
            randomIdx = Math.floor(Math.random() * currentPlaylist.length);
        } while (randomIdx === currentIndex);
        return randomIdx;
    }

    // Chuyển bài trước / sau (Phát ngẫu nhiên theo danh sách thu thập được trên trang)
    prevBtn.addEventListener('click', () => {
        if (currentPlaylist.length > 0) {
            playSongIndex(getRandomIndex());
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentPlaylist.length > 0) {
            playSongIndex(getRandomIndex());
        }
    });

    // Tự động chuyển bài khi kết thúc
    audio.addEventListener('ended', () => nextBtn.click());

    // Xử lý kéo thả (seek) mượt mà, không bị giật lùi
    let isDragging = false;
    progressBar.addEventListener('mousedown', () => isDragging = true);
    progressBar.addEventListener('mouseup', () => isDragging = false);
    progressBar.addEventListener('touchstart', () => isDragging = true);
    progressBar.addEventListener('touchend', () => isDragging = false);

    // Lấy tổng thời lượng khi file đã load xong meta data
    audio.addEventListener('loadedmetadata', () => {
        if (audio.duration && !isNaN(audio.duration)) {
            totalTimeEl.innerText = formatTime(audio.duration);
        }
    });

    // Cập nhật thanh thời lượng (Progress bar)
    audio.addEventListener('timeupdate', () => {
        if (audio.duration && !isNaN(audio.duration) && !isDragging) {
            const progressPercent = (audio.currentTime / audio.duration) * 100;
            progressBar.value = progressPercent;
            currentTimeEl.innerText = formatTime(audio.currentTime);
            totalTimeEl.innerText = formatTime(audio.duration);
        }
        // Lưu trạng thái định kỳ 3 giây/lần
        if (Math.floor(audio.currentTime) % 3 === 0) savePlayerState();
    });

    // Cho phép tua (seek) nhạc
    progressBar.addEventListener('input', () => { // Khi đang kéo thanh trượt
        if (audio.duration && !isNaN(audio.duration)) {
            currentTimeEl.innerText = formatTime((progressBar.value / 100) * audio.duration);
        }
    });
    progressBar.addEventListener('change', () => { // Khi thả chuột ra
        if (audio.duration && !isNaN(audio.duration)) {
            audio.currentTime = (progressBar.value / 100) * audio.duration;
        }
    });

    // Chỉnh âm lượng (Volume)
    if (volumeBar) {
        volumeBar.addEventListener('input', () => {
            audio.volume = volumeBar.value / 100;
        });
    }

    function formatTime(seconds) {
        if (isNaN(seconds)) return "0:00";
        const min = Math.floor(seconds / 60);
        const sec = Math.floor(seconds % 60);
        return `${min}:${sec < 10 ? '0' : ''}${sec}`;
    }

    // --- 2. AJAX SEARCH LOGIC ---
    const searchInput = document.getElementById('search_info');
    const searchResults = document.getElementById('search-results');
    let searchTimeout;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length === 0) {
            searchResults.style.display = 'none';
            return;
        }

        // Sử dụng debounce để tránh gọi API liên tục khi gõ nhanh
        searchTimeout = setTimeout(() => {
            fetch(`includes/song_api.php?action=read&search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        searchResults.innerHTML = ''; // Clear mảng kết quả
                        if (res.data.length === 0) {
                            searchResults.innerHTML = '<div style="padding: 15px; color: #b3b3b3;">Không tìm thấy bài hát.</div>';
                        } else {
                            res.data.forEach(song => {
                                const div = document.createElement('div');
                                div.style.padding = '10px 15px';
                                div.style.display = 'flex';
                                div.style.alignItems = 'center';
                                div.style.cursor = 'pointer';
                                div.style.borderBottom = '1px solid #333';
                                div.style.transition = 'background 0.2s';
                                div.addEventListener('mouseenter', () => div.style.backgroundColor = '#2a2a2a');
                                div.addEventListener('mouseleave', () => div.style.backgroundColor = 'transparent');
                                
                                div.innerHTML = `
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0; font-size: 14px; color: white;">${song.Song_title}</h4>
                                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #b3b3b3;">Lượt xem: ${song.View_count}</p>
                                    </div>
                                    <div style="background: #1ed760; color: black; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">▶</div>
                                `;
                                
                                div.addEventListener('click', () => {
                                    // Chuyển hướng đến trang song info thay vì phát nhạc tĩnh
                                    window.location.href = `music-streaming-song-info.php?id=${song.Song_id}`;
                                });
                                
                                searchResults.appendChild(div);
                            });
                        }
                        searchResults.style.display = 'flex';
                    }
                }).catch(err => console.error("Search API Error: ", err));
        }, 300);
    });

    // Đóng popup tìm kiếm khi click ra ngoài thanh search
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search')) {
            searchResults.style.display = 'none';
        }
    });

    // --- 3. DROPDOWN AVATAR & THEME MODE LOGIC ---
    const dropbtn = document.getElementById('dropbtn');
    const dropDown = document.getElementById('dropDown');
    const themeModeBtn = document.getElementById('themeMode');
    
    // Toggle Dropdown Menu
    if (dropbtn && dropDown) {
        dropbtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Ngăn sự kiện click lan ra ngoài
            dropDown.classList.toggle('showDropDown');
        });
    }

    // Đóng dropdown khi click ra ngoài vùng avatar
    document.addEventListener('click', (e) => {
        if (dropDown && dropDown.classList.contains('showDropDown') && !e.target.closest('.avatar_dropdown')) {
            dropDown.classList.remove('showDropDown');
        }
    });

    // Khôi phục giao diện sáng/tối từ LocalStorage (Giữ màu khi chuyển trang)
    if (localStorage.getItem('emuzik_theme') === 'light') {
        document.body.classList.add('light-body');
        if (themeModeBtn) themeModeBtn.innerHTML = '☀️ Theme Mode';
    }
    
    // Đổi Theme Mode
    if (themeModeBtn) {
        themeModeBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Giữ dropdown không bị đóng ngay lập tức
            document.body.classList.toggle('light-body');
            
            if (document.body.classList.contains('light-body')) {
                themeModeBtn.innerHTML = '☀️ Theme Mode';
                localStorage.setItem('emuzik_theme', 'light');
            } else {
                themeModeBtn.innerHTML = '🌙 Theme Mode';
                localStorage.setItem('emuzik_theme', 'dark');
            }
        });
    }
});