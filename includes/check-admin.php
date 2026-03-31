<?php
    session_start();

    if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        
    echo "<script>
        alert('You should not be here!');
        window.location.href='../auth/login.php';
    </script>";
        exit();
    }
?>