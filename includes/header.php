<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Web Nghe Nhạc</title>
    <link rel="stylesheet" href="../css/style.css?v=2">
    
    <script src="../js/login.js" defer></script>
</head>
<body>
    <button class="theme-toggle-btn" onclick="toggleDarkMode()">🌓 Sáng / Tối</button>
    <div class="theme-switcher" style=" position: fixed; top: 80px; right: 20px; z-index: 1000; ">
    <span>Chọn màu giao diện: </span>
    <button onclick="changeTheme('#2b3d94')" style="background: #2b3d94; width: 25px; height: 25px; border-radius: 50%; cursor: pointer; border: none;"></button>
    <button onclick="changeTheme('#e9aa19')" style="background: #e9aa19; width: 25px; height: 25px; border-radius: 50%; cursor: pointer; border: none;"></button>
    <button onclick="changeTheme('#165a17')" style="background: #165a17; width: 25px; height: 25px; border-radius: 50%; cursor: pointer; border: none;"></button>
    <button onclick="changeTheme('#9c27b0')" style="background: #9c27b0; width: 25px; height: 25px; border-radius: 50%; cursor: pointer; border: none;"></button>
</div>