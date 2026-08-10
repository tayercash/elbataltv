<?php
// Replace with your AdMob server-side verification secret
$secretKey = '';

// Get all query parameters
$params = $_GET;

// Save and remove the signature from the parameters
$providedSignature = $params['signature'] ?? '';
unset($params['signature']);

// Step 1: Sort the parameters alphabetically by key
ksort($params);

// Step 2: Create the query string (key=value&key2=value2...)
$dataString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

// Step 3: Generate HMAC SHA256 signature
$expectedSignature = hash_hmac('sha256', $dataString, $secretKey);

// Step 4: Verify the signature
if (hash_equals($expectedSignature, $providedSignature)) {
    // ✅ Valid request - reward the user
    // You can now use:
    // $params['user_id'], $params['reward_amount'], $params['reward_type'], etc.

    // Example: Log the reward
    file_put_contents('rewards.log', json_encode([
        'timestamp' => time(),
        'user_id' => $params['user_id'] ?? '',
        'reward' => $params['reward_amount'] ?? '',
        'type' => $params['reward_type'] ?? '',
    ]) . PHP_EOL, FILE_APPEND);

    // Send 200 OK
    http_response_code(200);
    echo 'OK';
} else {
    // ❌ Invalid signature
    // http_response_code(400);
    // echo 'Invalid signature';

    // Get all query parameters
    $params = $_GET;

    // Log the reward info (optional)
    file_put_contents('rewards.log', json_encode([
        'timestamp' => time(),
        'user_id' => $params['user_id'] ?? '',
        'reward_amount' => $params['reward_amount'] ?? '',
        'reward_type' => $params['reward_type'] ?? '',
        'custom_data' => $params['custom_data'] ?? '',
    ]) . PHP_EOL, FILE_APPEND);

    // Always respond with 200 OK (reward the user)
    http_response_code(200);
    echo 'OK';


}



?>