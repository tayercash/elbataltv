<?php
$secretKey = "YOUR_GAME_SECRET_KEY";
$params = $_GET;
$signature = $params['signature'];
unset($params['signature']); // remove signature from calculation

// Sort params by key
ksort($params);
$queryString = http_build_query($params);

// Generate expected signature
$expected = hash_hmac('sha256', $queryString, $secretKey);

if (hash_equals($expected, $signature)) {
    // ✅ Valid request from Unity
    $userId = $params['userId'];
    $rewardId = $params['rewardId'];

    // Grant reward in your database
    echo "OK";
} else {
    // ❌ Invalid request, possible spoof
    http_response_code(403);
}