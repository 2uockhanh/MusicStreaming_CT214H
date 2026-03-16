
    // 1. Hàm đổi màu và lưu lại
    function changeTheme(colorHex) {
        // Đổi giá trị biến CSS
        document.documentElement.style.setProperty('--primary-color', colorHex);
        // Lưu vào trình duyệt
        localStorage.setItem('userThemeColor', colorHex);
    }

    // 2. Tự động khôi phục màu khi vừa vào trang
    window.onload = function() {
        const savedColor = localStorage.getItem('userThemeColor');
        if (savedColor) {
            document.documentElement.style.setProperty('--primary-color', savedColor);
        }
    };

    // Hàm bật/tắt chế độ Sáng Tối
function toggleDarkMode() {
    const body = document.body;
    body.classList.toggle('dark-mode'); // Bật/tắt class dark-mode
    
    // Lưu trạng thái vào LocalStorage
    if (body.classList.contains('dark-mode')) {
        localStorage.setItem('userThemeMode', 'dark');
    } else {
        localStorage.setItem('userThemeMode', 'light');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Khôi phục màu primary (Code cũ của bạn giữ nguyên)
    const savedColor = localStorage.getItem('userThemeColor');
    if (savedColor) {
        document.documentElement.style.setProperty('--primary-color', savedColor);
    }

    // 2. Khôi phục chế độ Sáng/Tối
    const savedMode = localStorage.getItem('userThemeMode');
    if (savedMode === 'dark') {
        document.body.classList.add('dark-mode');
    }
});