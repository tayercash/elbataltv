<?php
include_once('../config_db.php');

$dsn = "mysql:host=localhost;dbname=$db_name;charset=utf8mb4";
$user = $db_username;
$pass = $db_password;

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $video_id = $_POST['video_id'] ?? null;
    $time = $_POST['time'] ?? null;
    $duration = $_POST['duration'] ?? null;

    if (!$user_id || !$video_id || $time === null || $duration === null) {
        http_response_code(400);
        echo json_encode(["error" => "Missing parameters"]);
        exit;
    }

    try {
        // لو مفيش record بيعمل insert, لو فيه بيعمل update
        $stmt = $pdo->prepare("
        INSERT INTO $user_continue_watching_table (user_id, video_id, time, duration, updated_at)
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE time = VALUES(time), duration = VALUES(duration), updated_at = NOW()
    ");
        $stmt->execute([$user_id, $video_id, $time, $duration]);

        echo json_encode([
            "status" => "saved",
            "user_id" => $user_id,
            "video_id" => $video_id,
            "time" => $time,
            "duration" => $duration
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }

} elseif ($method === 'GET') {
    $user_id = $_GET['user_id'] ?? null;
    $video_id = $_GET['video_id'] ?? null;

    if (!$user_id || !$video_id) {
        http_response_code(400);
        echo json_encode(["error" => "Missing parameters"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT time FROM $user_continue_watching_table WHERE user_id = ? AND video_id = ?");
    $stmt->execute([$user_id, $video_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($row ?: ["time" => 0]);
}