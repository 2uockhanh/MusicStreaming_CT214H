<?php
session_start();
header('Content-Type: application/json');

$pdo = new PDO("mysql:host=localhost;dbname=Emuzik_db;charset=utf8", "root", "");

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id']) || !$data) {
    echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ']);
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $data['username'];
$email = $data['email'];
$password = $data['password'];

try {
    $sql = "UPDATE Users SET User_name = ?, Email = ?";
    $params = [$username, $email];

    if (!empty($password)) {
        $sql .= ", Password = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE User_id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'message' => 'This username or email is already taken']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating profile: ' . $e->getMessage()]);
    }
}