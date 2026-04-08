<?php
require_once '../includes/check-admin.php';
include '../includes/db-connect.php';


$userCount = $conn->query("SELECT COUNT(*) as total FROM Users")->fetch_assoc()['total'];
$songCount = $conn->query("SELECT COUNT(*) as total FROM Songs")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emuzik - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard-admin.css">
</head>
<body>

    <div class="dashboard-container">
        <h1><i class="fas fa-chart-line"></i> Emuzik Admin Panel</h1>
        
        <div class="nav-buttons">
            <a href="admin/manage_users.php" class="btn btn-user"><i class="fas fa-users"></i> USERS</a>
            <a href="admin/manage_songs.php" class="btn btn-music"><i class="fas fa-music"></i> MUSIC</a>
            <button id="refresh-stats" class="btn btn-refresh"><i class="fas fa-sync"></i> REFRESH</button>
            <a href="../auth/login.php" class="btn btn-logout">LOGOUT</a>
        </div>
        <br>
        <div class="grid">
            <div class="card user-card">
                <h3>TOTAL USERS</h3>
                <p id="user-count"><?php echo $userCount; ?></p>
            </div>
            <div class="card music-card">
                <h3>TOTAL SONGS</h3>
                <p id="song-count"><?php echo $songCount; ?></p>
            </div>
        </div>

        
    </div>

    <script src="js/dashboard-admin.js"></script>
</body>
</html>