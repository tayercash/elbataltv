<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

$pdo = requireDB();

// Capture current timezone offset before switching to UTC
try {
    $offsetStmt = $pdo->query("SELECT TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), NOW()) AS tz_offset");
    $offsetRow = $offsetStmt->fetch(PDO::FETCH_ASSOC);
    $tzOffset = intval($offsetRow['tz_offset'] ?? 0);
} catch (PDOException $e) {
    $tzOffset = 0;
}

$pdo->exec("SET time_zone = '+00:00'");

// One-time migration: convert existing timestamps from local time to UTC
if ($tzOffset != 0) {
    try {
        $migrated = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'tz_migrated'")->fetch();
        if (!$migrated) {
            $tables = [
                "UPDATE users SET created_at = DATE_ADD(created_at, INTERVAL -{$tzOffset} SECOND) WHERE created_at IS NOT NULL",
                "UPDATE users SET last_activity = DATE_ADD(last_activity, INTERVAL -{$tzOffset} SECOND) WHERE last_activity IS NOT NULL",
                "UPDATE users SET email_verification_expires = DATE_ADD(email_verification_expires, INTERVAL -{$tzOffset} SECOND) WHERE email_verification_expires IS NOT NULL",
                "UPDATE user_devices SET last_login = DATE_ADD(last_login, INTERVAL -{$tzOffset} SECOND) WHERE last_login IS NOT NULL",
                "UPDATE user_devices SET created_at = DATE_ADD(created_at, INTERVAL -{$tzOffset} SECOND) WHERE created_at IS NOT NULL",
                "UPDATE password_resets SET expires_at = DATE_ADD(expires_at, INTERVAL -{$tzOffset} SECOND) WHERE expires_at IS NOT NULL",
                "UPDATE password_resets SET created_at = DATE_ADD(created_at, INTERVAL -{$tzOffset} SECOND) WHERE created_at IS NOT NULL",
                "UPDATE login_logs SET created_at = DATE_ADD(created_at, INTERVAL -{$tzOffset} SECOND) WHERE created_at IS NOT NULL",
            ];
            foreach ($tables as $sql) {
                try { $pdo->exec($sql); } catch (PDOException $e) {}
            }
            $pdo->exec("INSERT IGNORE INTO settings (`key`, `value`) VALUES ('tz_migrated', '1')");
        }
    } catch (PDOException $e) {}
}

function jsonInput() {
    return json_decode(file_get_contents('php://input'), true) ?: [];
}

function verifyDevice($pdo, $userId, $hwid) {
    if (!$userId || !$hwid) return false;
    $stmt = $pdo->prepare("SELECT id FROM user_devices WHERE user_id = ? AND hwid = ?");
    $stmt->execute([$userId, $hwid]);
    return (bool)$stmt->fetch();
}

function verifyToken($pdo, $userId, $hwid, $token) {
    if (!$userId || !$hwid || !$token) return false;
    $stmt = $pdo->prepare("SELECT id FROM user_devices WHERE user_id = ? AND hwid = ? AND token = ?");
    $stmt->execute([$userId, $hwid, $token]);
    return (bool)$stmt->fetch();
}

function jsonError($msg) {
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

function requireDevice($pdo, $userId, $hwid) {
    if (!verifyDevice($pdo, $userId, $hwid)) {
        jsonError('جهاز غير مصرح');
    }
}

function requireToken($pdo, $data) {
    $userId = intval($data['requesterId'] ?? ($data['userId'] ?? 0));
    $hwid = $data['requesterHwid'] ?? ($data['hwid'] ?? '');
    $token = $data['requesterToken'] ?? ($data['token'] ?? '');
    if (!verifyToken($pdo, $userId, $hwid, $token)) {
        jsonError('جلسة غير صالحة');
    }
}

function requireActiveStatus($pdo, $userId) {
    if (!$userId) jsonError('يجب تسجيل الدخول');
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['status'] == 0) {
        jsonError('الحساب معطل');
    }
}

function mou_custom_encode($txt, $num = 1) {
    $default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    $custom = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";
    if (is_null($txt)) $txt = '';
    $encoded = urlencode($txt);
    $encoded = base64_encode($encoded);
    $encoded = strtr($encoded, $custom, $default);
    return $encoded;
}

function mou_custom_decode($txt, $num = 1) {
    $default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    $custom = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";
    $decoded = $txt;
    for ($i = 1; $i <= $num; $i++) {
        $decoded = base64_decode(strtr($decoded, $custom, $default));
    }
    return urldecode($decoded);
}

function syncToElbatal($pdo, $userId, $data) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, avatar, password, role, google_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return;

        $username = $user['username'];
        $email = $user['email'] ?: '';
        if ($email === '') $email = 'user' . $userId . '@elbatal.local';
        $hash = $user['password'];
        $avatar = $user['avatar'];
        $role = $user['role'] ?: 'user';
        $googleId = $user['google_id'];

        $userIcon = $username;
        $gIcon = null;
        $avatarOrGIcon = 1;
        if (!empty($avatar)) {
            if (preg_match('#^https?://#i', $avatar)) {
                $gIcon = $avatar;
                $avatarOrGIcon = 2;
            } elseif (preg_match('/seed=([^&]+)/', $avatar, $m)) {
                $userIcon = rawurldecode($m[1]);
            } else {
                $path = parse_url($avatar, PHP_URL_PATH);
                if ($path && ($base = basename($path)) && $base !== '') {
                    $userIcon = $base;
                }
            }
        }

        $stmt = $pdo->prepare("SELECT id FROM Elbatal_Users WHERE id = ?");
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            $actvcode = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
            $stmt = $pdo->prepare("INSERT INTO Elbatal_Users (id, username, email, password, user_icon, g_icon, avatar_or_g_icon, google_linked, google_linked_email, google_linked_id, role, active, status, created_at, type, actvcode, has_pro, pro_until) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'working', NOW(), 'user', ?, '0', NOW())");
            $stmt->execute([$userId, $username, $email, $hash, $userIcon, $gIcon, $avatarOrGIcon, $googleId ? 1 : 0, $googleId ? $email : null, $googleId, $role, $actvcode]);
        } else {
            $stmt = $pdo->prepare("UPDATE Elbatal_Users SET username = ?, email = ?, user_icon = ?, g_icon = ?, avatar_or_g_icon = ?, google_linked = ?, google_linked_email = ?, google_linked_id = ? WHERE id = ?");
            $stmt->execute([$username, $email, $userIcon, $gIcon, $avatarOrGIcon, $googleId ? 1 : 0, $googleId ? $email : null, $googleId, $userId]);
        }

        $hwid = $data['device_info']['hwid'] ?? ($data['device_info']['androidId'] ?? ($data['device_info']['platform'] ?? ''));
        if ($hwid !== '') {
            $devName = $data['device_info']['device_name'] ?? ($data['device_info']['dev_name'] ?? '');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            }
            $stmt = $pdo->prepare("SELECT id FROM Elbatal_logins WHERE user_id = ? AND dev_id = ?");
            $stmt->execute([$userId, $hwid]);
            if ($stmt->fetch()) {
                $stmt = $pdo->prepare("UPDATE Elbatal_logins SET dev_ip = ?, dev_name = ?, last_login_at = NOW() WHERE user_id = ? AND dev_id = ?");
                $stmt->execute([$ip, $devName, $userId, $hwid]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO Elbatal_logins (user_id, dev_id, dev_name, dev_ip, last_login_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$userId, $hwid, $devName, $ip]);
            }
        }
    } catch (PDOException $e) {
        error_log('syncToElbatal: ' . $e->getMessage());
    }
}

function createPngFromImage($sourcePath, $targetPath, $size) {
    if (!function_exists('imagecreatetruecolor')) {
        return copy($sourcePath, $targetPath);
    }
    $info = @getimagesize($sourcePath);
    if (!$info) return false;
    switch ($info[2]) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($sourcePath); break;
        case IMAGETYPE_PNG: $src = @imagecreatefrompng($sourcePath); break;
        case IMAGETYPE_GIF: $src = @imagecreatefromgif($sourcePath); break;
        case IMAGETYPE_WEBP: $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false; break;
        default: $src = false;
    }
    if (!$src) return false;
    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $size, $size, $transparent);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $size, $size, imagesx($src), imagesy($src));
    $ok = imagepng($dst, $targetPath);
    imagedestroy($src);
    imagedestroy($dst);
    return $ok;
}

function createFaviconFromImage($sourcePath, $targetPath) {
    if (!function_exists('imagecreatetruecolor')) {
        return createFaviconFromPngFile($sourcePath, $targetPath) || createDefaultFavicon($targetPath);
    }
    $tmpPng = tempnam(sys_get_temp_dir(), 'mou_favicon_');
    if (!$tmpPng || !createPngFromImage($sourcePath, $tmpPng, 32)) {
        return createFaviconFromPngFile($sourcePath, $targetPath) || createDefaultFavicon($targetPath);
    }
    $img = @imagecreatefrompng($tmpPng);
    @unlink($tmpPng);
    if (!$img) return false;

    $xor = '';
    $and = '';
    for ($y = 31; $y >= 0; $y--) {
        $maskRow = '';
        $maskByte = 0;
        $bit = 7;
        for ($x = 0; $x < 32; $x++) {
            $rgba = imagecolorat($img, $x, $y);
            $alpha = ($rgba & 0x7F000000) >> 24;
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;
            $a = 255 - (int)round($alpha * 255 / 127);
            $xor .= chr($b) . chr($g) . chr($r) . chr($a);
            if ($alpha >= 64) $maskByte |= (1 << $bit);
            $bit--;
            if ($bit < 0) {
                $maskRow .= chr($maskByte);
                $maskByte = 0;
                $bit = 7;
            }
        }
        $and .= str_pad($maskRow, 4, "\0", STR_PAD_RIGHT);
    }
    imagedestroy($img);

    $bitmapInfoHeader = pack('V3v2V6', 40, 32, 64, 1, 32, 0, strlen($xor . $and), 0, 0, 0, 0);
    $imageData = $bitmapInfoHeader . $xor . $and;
    $header = pack('vvv', 0, 1, 1);
    $entry = pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($imageData), 22);
    return file_put_contents($targetPath, $header . $entry . $imageData) !== false;
}

function createFaviconFromPngFile($sourcePath, $targetPath) {
    $info = @getimagesize($sourcePath);
    if (!$info || $info[2] !== IMAGETYPE_PNG) return false;
    $pngData = file_get_contents($sourcePath);
    if ($pngData === false) return false;
    $width = max(1, min(256, intval($info[0])));
    $height = max(1, min(256, intval($info[1])));
    $header = pack('vvv', 0, 1, 1);
    $entry = pack('CCCCvvVV', $width === 256 ? 0 : $width, $height === 256 ? 0 : $height, 0, 0, 1, 32, strlen($pngData), 22);
    return file_put_contents($targetPath, $header . $entry . $pngData) !== false;
}

function createDefaultFavicon($targetPath) {
    $xor = '';
    $and = '';
    for ($y = 31; $y >= 0; $y--) {
        for ($x = 0; $x < 32; $x++) {
            $r = 255;
            $g = 204 - (int)($y * 2.2);
            $b = (int)($x * 2.5);
            $xor .= chr($b) . chr(max(120, $g)) . chr($r) . chr(255);
        }
        $and .= "\0\0\0\0";
    }
    $bitmapInfoHeader = pack('V3v2V6', 40, 32, 64, 1, 32, 0, strlen($xor . $and), 0, 0, 0, 0);
    $imageData = $bitmapInfoHeader . $xor . $and;
    $header = pack('vvv', 0, 1, 1);
    $entry = pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($imageData), 22);
    return file_put_contents($targetPath, $header . $entry . $imageData) !== false;
}

function saveSiteIcons($uploadedFile) {
    if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) return true;
    $ext = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) return false;
    $imgDir = __DIR__ . '/assets/img';
    if (!is_dir($imgDir)) mkdir($imgDir, 0755, true);
    $logoPath = $imgDir . '/logo_192.png';
    $sourcePath = $uploadedFile['tmp_name'];
    if (!createPngFromImage($sourcePath, $logoPath, 192)) {
        if (!move_uploaded_file($sourcePath, $logoPath)) return false;
        $sourcePath = $logoPath;
    }
    return createFaviconFromImage($sourcePath, __DIR__ . '/favicon.ico');
}

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN `last_activity` datetime DEFAULT NULL AFTER `created_at`");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN `email` varchar(255) DEFAULT NULL AFTER `username`");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN `email_verified` tinyint(1) NOT NULL DEFAULT 0 AFTER `email`");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN `email_verification_code` varchar(6) DEFAULT NULL AFTER `email_verified`");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN `email_verification_expires` datetime DEFAULT NULL AFTER `email_verification_code`");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN `google_id` varchar(255) DEFAULT NULL AFTER `email`");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN `avatar` varchar(500) DEFAULT NULL AFTER `google_id`");
} catch (PDOException $e) {}
try {
    $pdo->exec("UPDATE users SET email_verified = 1 WHERE status = 1");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN `gender` enum('male','female') DEFAULT NULL AFTER `avatar`");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN `country` varchar(100) DEFAULT NULL AFTER `gender`");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN `max_devices` int(11) DEFAULT NULL AFTER `country`");
} catch (PDOException $e) {}
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `user_devices` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `hwid` varchar(255) NOT NULL,
        `device_info` longtext DEFAULT NULL,
        `token` varchar(64) DEFAULT NULL,
        `last_login` datetime NOT NULL DEFAULT current_timestamp(),
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_device` (`user_id`, `hwid`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE user_devices ADD COLUMN `token` varchar(64) DEFAULT NULL AFTER `device_info`");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE user_devices ADD KEY `token` (`token`)");
} catch (PDOException $e) {}
try {
    $pdo->exec("ALTER TABLE users DROP COLUMN `hwid_list`");
} catch (PDOException $e) {}
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `token` varchar(255) NOT NULL,
        `expires_at` datetime NOT NULL,
        `used` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `token` (`token`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `login_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `email` varchar(255) DEFAULT NULL,
        `method` varchar(50) NOT NULL DEFAULT 'password',
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `location_country` varchar(100) DEFAULT NULL,
        `location_city` varchar(100) DEFAULT NULL,
        `location_isp` varchar(255) DEFAULT NULL,
        `location_ip` varchar(45) DEFAULT NULL,
        `success` tinyint(1) NOT NULL DEFAULT 0,
        `failure_reason` varchar(255) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

function getLocationData() {
    static $cached = null;
    if ($cached !== null) return $cached;
    $cached = ['country' => null, 'city' => null, 'isp' => null, 'ip' => null];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://ipv4-check-perf.radar.cloudflare.com/api/info');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode === 200) {
        $data = json_decode($resp, true);
        if ($data) {
            $cached = [
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'isp' => $data['asOrganization'] ?? $data['isp'] ?? null,
                'ip' => $data['ip'] ?? null,
            ];
        }
    }
    return $cached;
}

// Seed default settings
$defaultSettings = [
    'google_client_id' => '',
    'google_client_secret' => '',
    'google_login_enabled' => '0',
    'site_name' => 'Mou Default',
    'site_short_desc' => '',
    'site_full_desc' => '',
    'app_name' => 'Mou Default',
    'smtp_host' => '',
    'smtp_port' => '587',
    'smtp_email' => '',
    'smtp_password' => '',
    'smtp_encryption' => 'tls',
    'smtp_ready' => '0',
    'email_verification_enabled' => '0',
    'max_devices' => '2'
];
foreach ($defaultSettings as $key => $value) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO settings (`key`, `value`) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    } catch (PDOException $e) {}
}

function sendMail($pdo, $to, $subject, $htmlBody) {
    $host = '';
    $port = '587';
    $email = '';
    $password = '';
    $encryption = 'tls';
    $appName = 'Mou Default';
    $stmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` IN ('smtp_host','smtp_port','smtp_email','smtp_password','smtp_encryption','app_name')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        switch ($row['key']) {
            case 'smtp_host': $host = $row['value']; break;
            case 'smtp_port': $port = $row['value']; break;
            case 'smtp_email': $email = $row['value']; break;
            case 'smtp_password': $password = $row['value']; break;
            case 'smtp_encryption': $encryption = $row['value']; break;
            case 'app_name': $appName = $row['value']; break;
        }
    }
    if (!$host || !$email || !$password) { error_log('SMTP not configured'); return false; }

    require_once __DIR__ . '/assets/lib/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/assets/lib/PHPMailer/SMTP.php';
    require_once __DIR__ . '/assets/lib/PHPMailer/Exception.php';

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = intval($port);
        $mail->SMTPAuth = true;
        $mail->Username = $email;
        $mail->Password = $password;
        $mail->CharSet = 'UTF-8';

        if ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom($email, $appName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $e->getMessage());
        return false;
    }
}

function resolveAvatarUrl($avatar, $username) {
    if (!empty($avatar)) {
        if (preg_match('#^https?://#i', $avatar)) {
            $parts = parse_url($avatar);
            $host = $parts['host'] ?? '';
            $currentHost = $_SERVER['HTTP_HOST'] ?? '';
            if ($host === $currentHost) {
                $avatar = ($parts['path'] ?? '') . (isset($parts['query']) ? '?' . $parts['query'] : '');
            } else {
                return $avatar;
            }
        }
        $first = $avatar[0];
        if ($first === '/' || $first === '.' || $first === '\\') {
            $cleaned = $avatar;
            if (defined('BASE_PATH') && BASE_PATH && strpos($cleaned, BASE_PATH) === 0) {
                $cleaned = substr($cleaned, strlen(BASE_PATH));
            }
            return BASE_PATH . '/' . ltrim($cleaned, '/');
        }
        return BASE_PATH . '/avatar.php?seed=' . urlencode($avatar);
    }
    return BASE_PATH . '/avatar.php?seed=' . urlencode($username);
}

switch ($action) {
    // ==================== REGISTER ====================
    case 'register':
        $data = jsonInput();
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $deviceInfo = $data['device_info'] ?? [];
        $gender = $data['gender'] ?? null;
        $country = trim($data['country'] ?? '');

        if (!$username || !$password) {
            echo json_encode(['success' => false, 'error' => 'Username and password required']);
            break;
        }
        if (strlen($username) < 3) {
            echo json_encode(['success' => false, 'error' => 'اسم المستخدم قصير جداً']);
            break;
        }
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'error' => 'كلمة المرور قصيرة جداً']);
            break;
        }
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'البريد الإلكتروني غير صالح']);
            break;
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'اسم المستخدم موجود بالفعل']);
            break;
        }

        if ($email) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'البريد الإلكتروني مستخدم بالفعل']);
                break;
            }
        }

        // Check if email verification is enabled
        $verificationEnabled = false;
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'email_verification_enabled'");
        $stmt->execute();
        $vRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($vRow && $vRow['value'] === '1' && $email) {
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'smtp_ready'");
            $stmt->execute();
            $smtpRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($smtpRow && $smtpRow['value'] === '1') {
                $verificationEnabled = true;
            }
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        if ($verificationEnabled) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status, email_verified, email_verification_code, email_verification_expires, gender, country) VALUES (?, ?, ?, 'user', 0, 0, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), ?, ?)");
            $stmt->execute([$username, $email, $hash, $otp, $gender, $country ?: null]);
            $userId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'app_name'");
            $stmt->execute();
            $appName = ($row = $stmt->fetch()) ? $row['value'] : 'Mou Default';

            $htmlBody = "
            <div style='font-family:sans-serif;max-width:600px;margin:0 auto;padding:30px;background:#f9f9f9;border-radius:12px;'>
                <div style='text-align:center;padding:20px 0;'>
                    <h2 style='color:#333;margin:0;'>$appName</h2>
                </div>
                <div style='background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.06);'>
                    <h1 style='font-size:20px;color:#333;margin:0 0 16px;'>مرحباً $username</h1>
                    <p style='color:#666;line-height:1.6;margin:0 0 20px;'>شكراً لتسجيلك في $appName. استخدم الكود التالي لتفعيل حسابك:</p>
                    <div style='text-align:center;padding:20px 0;'>
                        <span style='display:inline-block;font-size:32px;font-weight:bold;letter-spacing:8px;color:#ffcc00;background:#1a1a1a;padding:16px 32px;border-radius:10px;'>$otp</span>
                    </div>
                    <p style='color:#999;font-size:13px;margin:20px 0 0;'>الكود صالح لمدة 15 دقيقة. إذا لم تطلب هذا، تجاهل هذه الرسالة.</p>
                </div>
            </div>";

            $sent = sendMail($pdo, $email, 'تفعيل حسابك - ' . $appName, $htmlBody);

            if (!$sent) {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
                echo json_encode(['success' => false, 'error' => 'فشل إرسال كود التفعيل. تحقق من إعدادات البريد']);
                break;
            }

            echo json_encode([
                'success' => true,
                'requires_verification' => true,
                'user_id' => $userId,
                'email' => $email,
                'message' => 'تم إرسال كود التفعيل إلى بريدك الإلكتروني'
            ]);
            break;
        }

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status, email_verified, gender, country) VALUES (?, ?, ?, 'user', 1, 1, ?, ?)");
        $stmt->execute([$username, $email ?: null, $hash, $gender, $country ?: null]);
        $userId = $pdo->lastInsertId();

        // Auto-register device
        $hwid = $deviceInfo['hwid'] ?? ($deviceInfo['platform'] ?? 'web');
        $infoJson = json_encode($deviceInfo, JSON_UNESCAPED_UNICODE);
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("INSERT INTO user_devices (user_id, hwid, device_info, token, last_login) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE device_info = VALUES(device_info), token = VALUES(token), last_login = NOW()");
        $stmt->execute([$userId, $hwid, $infoJson, $token]);

        syncToElbatal($pdo, $userId, $data);

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $userId,
                'username' => $username,
                'role' => 'user',
                'avatar' => resolveAvatarUrl(null, $username),
                'token' => $token,
            ]
        ]);
        break;

    // ==================== LOGIN ====================
    case 'login':
        $data = jsonInput();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $deviceInfo = $data['device_info'] ?? [];

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($forwarded[0]);
        }
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $loc = getLocationData();

        if (!$email || !$password) {
            if ($email) {
                $pdo->prepare("INSERT INTO login_logs (user_id, email, method, ip_address, user_agent, location_country, location_city, location_isp, location_ip, success, failure_reason) VALUES (NULL, ?, 'password', ?, ?, ?, ?, ?, ?, 0, 'Email/password required')")->execute([$email, $ip, $ua, $loc['country'], $loc['city'], $loc['isp'], $loc['ip']]);
            }
            echo json_encode(['success' => false, 'error' => 'Email and password required']);
            break;
        }

        $stmt = $pdo->prepare("SELECT id, username, email, avatar, password, role, status, email_verified FROM users WHERE email = ? OR CAST(id AS CHAR) = ?");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $pdo->prepare("INSERT INTO login_logs (user_id, email, method, ip_address, user_agent, location_country, location_city, location_isp, location_ip, success, failure_reason) VALUES (NULL, ?, 'password', ?, ?, ?, ?, ?, ?, 0, 'Invalid email/ID or password')")->execute([$email, $ip, $ua, $loc['country'], $loc['city'], $loc['isp'], $loc['ip']]);
            echo json_encode(['success' => false, 'error' => 'Invalid email/ID or password']);
            break;
        }

        if ($user['status'] == 0) {
            $reason = '';
            if (isset($user['email_verified']) && $user['email_verified'] == 0 && !empty($user['email'])) {
                $reason = 'Email not verified';
            } else {
                $reason = 'Account disabled';
            }
            $pdo->prepare("INSERT INTO login_logs (user_id, email, method, ip_address, user_agent, location_country, location_city, location_isp, location_ip, success, failure_reason) VALUES (?, ?, 'password', ?, ?, ?, ?, ?, ?, 0, ?)")->execute([$user['id'], $user['email'], $ip, $ua, $loc['country'], $loc['city'], $loc['isp'], $loc['ip'], $reason]);
            if ($reason === 'Email not verified') {
                echo json_encode(['success' => false, 'error' => 'لم يتم تفعيل الحساب بعد. يرجى تأكيد البريد الإلكتروني أولاً', 'needs_verification' => true, 'user_id' => $user['id'], 'email' => $user['email']]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Account is disabled']);
            }
            break;
        }

        $stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        $hwid = $deviceInfo['hwid'] ?? ($deviceInfo['androidId'] ?? ($deviceInfo['platform'] ?? 'web'));
        $infoJson = json_encode($deviceInfo, JSON_UNESCAPED_UNICODE);
        $token = bin2hex(random_bytes(32));

        // Device authorization: check limit & auto-register
        $stmt = $pdo->prepare("SELECT max_devices FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $userMaxDevices = $userRow ? $userRow['max_devices'] : null;

        if ($userMaxDevices !== null) {
            $maxDevices = intval($userMaxDevices);
        } else {
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'max_devices'");
            $stmt->execute();
            $maxDevices = intval(($row = $stmt->fetch()) ? $row['value'] : 2);
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_devices WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $deviceCount = intval($stmt->fetch(PDO::FETCH_ASSOC)['cnt']);

        // Check if this device is already registered
        $stmt = $pdo->prepare("SELECT id FROM user_devices WHERE user_id = ? AND hwid = ?");
        $stmt->execute([$user['id'], $hwid]);
        $existingDevice = $stmt->fetch();

        if ($existingDevice) {
            $stmt = $pdo->prepare("UPDATE user_devices SET device_info = ?, token = ?, last_login = NOW() WHERE user_id = ? AND hwid = ?");
            $stmt->execute([$infoJson, $token, $user['id'], $hwid]);
        } elseif ($maxDevices === 0 || $deviceCount < $maxDevices) {
            $stmt = $pdo->prepare("INSERT INTO user_devices (user_id, hwid, device_info, token, last_login) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE device_info = VALUES(device_info), token = VALUES(token), last_login = NOW()");
            $stmt->execute([$user['id'], $hwid, $infoJson, $token]);
        } else {
            $pdo->prepare("INSERT INTO login_logs (user_id, email, method, ip_address, user_agent, location_country, location_city, location_isp, location_ip, success, failure_reason) VALUES (?, ?, 'password', ?, ?, ?, ?, ?, ?, 0, 'Device limit exceeded')")->execute([$user['id'], $user['email'], $ip, $ua, $loc['country'], $loc['city'], $loc['isp'], $loc['ip']]);
            jsonError('لقد تجاوزت الحد الأقصى للأجهزة المسموح بها (' . $maxDevices . '). يرجى التواصل مع المشرف');
        }

        // Automatic eviction: keep only the most recent maxDevices devices
        if ($maxDevices > 0) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_devices WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $currentCount = intval($stmt->fetch(PDO::FETCH_ASSOC)['cnt']);
            if ($currentCount > $maxDevices) {
                $toDelete = $currentCount - $maxDevices;
                $stmt = $pdo->prepare("DELETE FROM user_devices WHERE user_id = ? AND hwid != ? ORDER BY last_login ASC LIMIT ?");
                $stmt->execute([$user['id'], $hwid, $toDelete]);
            }
        }

        $pdo->prepare("INSERT INTO login_logs (user_id, email, method, ip_address, user_agent, location_country, location_city, location_isp, location_ip, success, failure_reason) VALUES (?, ?, 'password', ?, ?, ?, ?, ?, ?, 1, NULL)")->execute([$user['id'], $user['email'], $ip, $ua, $loc['country'], $loc['city'], $loc['isp'], $loc['ip']]);

        syncToElbatal($pdo, $user['id'], $data);

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'avatar' => resolveAvatarUrl($user['avatar'], $user['username']),
                'token' => $token,
            ]
        ]);
        break;

    // ==================== GOOGLE LOGIN ====================
    case 'google_login':
        $data = jsonInput();
        $credential = $data['credential'] ?? '';

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($forwarded[0]);
        }
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $loc = getLocationData();

        if (!$credential) {
            // Profile-based sign-in (native app / electron): OS already verified the Google account
            $profileId = trim(mou_custom_decode($data['gid'] ?? ''));
            $profileEmail = trim(mou_custom_decode($data['email'] ?? ''));
            if (!$profileId || !$profileEmail) {
                echo json_encode(['success' => false, 'error' => 'Missing credential']);
                break;
            }
            $payload = [
                'sub' => $profileId,
                'email' => $profileEmail,
                'name' => trim($data['username'] ?? ''),
                'picture' => $data['g_icon'] ?? ($data['picture'] ?? ''),
            ];
        } else {
            // Verify token with Google
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($credential));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                echo json_encode(['success' => false, 'error' => 'Google token verification failed']);
                break;
            }

            $payload = json_decode($response, true);
        }
        $googleId = $payload['sub'] ?? '';
        $email = $payload['email'] ?? '';
        $name = $payload['name'] ?? $email;
        $picture = $payload['picture'] ?? '';

        if (!$googleId || !$email) {
            echo json_encode(['success' => false, 'error' => 'Invalid Google token payload']);
            break;
        }

        // Check if Google login is enabled
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'google_login_enabled'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['value'] !== '1') {
            $pdo->prepare("INSERT INTO login_logs (user_id, email, method, ip_address, user_agent, location_country, location_city, location_isp, location_ip, success, failure_reason) VALUES (NULL, ?, 'google', ?, ?, ?, ?, ?, ?, 0, 'Google login disabled')")->execute([$email, $ip, $ua, $loc['country'], $loc['city'], $loc['isp'], $loc['ip']]);
            echo json_encode(['success' => false, 'error' => 'Google login is disabled']);
            break;
        }

        // Find user by google_id or email, or create new
        $stmt = $pdo->prepare("SELECT id, username, email, google_id, avatar, role, status FROM users WHERE google_id = ?");
        $stmt->execute([$googleId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $stmt = $pdo->prepare("SELECT id, username, email, google_id, avatar, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $stmt = $pdo->prepare("UPDATE users SET google_id = ?, avatar = ? WHERE id = ?");
                $stmt->execute([$googleId, $picture, $user['id']]);
                $user['avatar'] = $picture;
            }
        }

        if (!$user) {
            $username = explode('@', $email)[0];
            $baseUsername = $username;
            $suffix = 1;
            while (true) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if (!$stmt->fetch()) break;
                $username = $baseUsername . $suffix;
                $suffix++;
            }
            $randomPass = bin2hex(random_bytes(16));
            $hash = password_hash($randomPass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, google_id, avatar, password, role, status, email_verified) VALUES (?, ?, ?, ?, ?, 'user', 1, 1)");
            $stmt->execute([$username, $email, $googleId, $picture, $hash]);
            $userId = $pdo->lastInsertId();
            $user = ['id' => $userId, 'username' => $username, 'role' => 'user', 'status' => 1, 'avatar' => $picture, 'email' => $email];
        }

        // Update avatar if Google picture changed
        if (!empty($picture) && $user['avatar'] !== $picture) {
            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$picture, $user['id']]);
            $user['avatar'] = $picture;
        }

        if ($user['status'] == 0) {
            $pdo->prepare("INSERT INTO login_logs (user_id, email, method, ip_address, user_agent, location_country, location_city, location_isp, location_ip, success, failure_reason) VALUES (?, ?, 'google', ?, ?, ?, ?, ?, ?, 0, 'Account disabled')")->execute([$user['id'], $user['email'], $ip, $ua, $loc['country'], $loc['city'], $loc['isp'], $loc['ip']]);
            echo json_encode(['success' => false, 'error' => 'Account is disabled']);
            break;
        }

        $stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        $deviceInfo = $data['device_info'] ?? [];
        $hwid = $deviceInfo['hwid'] ?? ($deviceInfo['androidId'] ?? $googleId);
        $infoJson = json_encode($deviceInfo, JSON_UNESCAPED_UNICODE);
        $token = bin2hex(random_bytes(32));

        // Device authorization: check limit & auto-register
        $stmt = $pdo->prepare("SELECT max_devices FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $userMaxDevices = $userRow ? $userRow['max_devices'] : null;

        if ($userMaxDevices !== null) {
            $maxDevices = intval($userMaxDevices);
        } else {
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'max_devices'");
            $stmt->execute();
            $maxDevices = intval(($row = $stmt->fetch()) ? $row['value'] : 2);
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_devices WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $deviceCount = intval($stmt->fetch(PDO::FETCH_ASSOC)['cnt']);

        $stmt = $pdo->prepare("SELECT id FROM user_devices WHERE user_id = ? AND hwid = ?");
        $stmt->execute([$user['id'], $hwid]);
        $existingDevice = $stmt->fetch();

        if ($existingDevice) {
            $stmt = $pdo->prepare("UPDATE user_devices SET device_info = ?, token = ?, last_login = NOW() WHERE user_id = ? AND hwid = ?");
            $stmt->execute([$infoJson, $token, $user['id'], $hwid]);
        } elseif ($maxDevices === 0 || $deviceCount < $maxDevices) {
            $stmt = $pdo->prepare("INSERT INTO user_devices (user_id, hwid, device_info, token, last_login) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE device_info = VALUES(device_info), token = VALUES(token), last_login = NOW()");
            $stmt->execute([$user['id'], $hwid, $infoJson, $token]);
        } else {
            $pdo->prepare("INSERT INTO login_logs (user_id, email, method, ip_address, user_agent, location_country, location_city, location_isp, location_ip, success, failure_reason) VALUES (?, ?, 'google', ?, ?, ?, ?, ?, ?, 0, 'Device limit exceeded')")->execute([$user['id'], $user['email'], $ip, $ua, $loc['country'], $loc['city'], $loc['isp'], $loc['ip']]);
            jsonError('لقد تجاوزت الحد الأقصى للأجهزة المسموح بها (' . $maxDevices . '). يرجى التواصل مع المشرف');
        }

        // Automatic eviction: keep only the most recent maxDevices devices
        if ($maxDevices > 0) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM user_devices WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $currentCount = intval($stmt->fetch(PDO::FETCH_ASSOC)['cnt']);
            if ($currentCount > $maxDevices) {
                $toDelete = $currentCount - $maxDevices;
                $stmt = $pdo->prepare("DELETE FROM user_devices WHERE user_id = ? AND hwid != ? ORDER BY last_login ASC LIMIT ?");
                $stmt->execute([$user['id'], $hwid, $toDelete]);
            }
        }

        $pdo->prepare("INSERT INTO login_logs (user_id, email, method, ip_address, user_agent, location_country, location_city, location_isp, location_ip, success, failure_reason) VALUES (?, ?, 'google', ?, ?, ?, ?, ?, ?, 1, NULL)")->execute([$user['id'], $user['email'], $ip, $ua, $loc['country'], $loc['city'], $loc['isp'], $loc['ip']]);

        syncToElbatal($pdo, $user['id'], $data);

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'avatar' => resolveAvatarUrl($user['avatar'], $user['username']),
                'token' => $token,
            ]
        ]);
        break;

    // ==================== HEARTBEAT ====================
    case 'heartbeat':
        $data = jsonInput();
        $userId = intval($data['userId'] ?? 0);
        $hwid = $data['hwid'] ?? '';
        if ($userId > 0) {
            requireActiveStatus($pdo, $userId);
            requireToken($pdo, $data);
            $stmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
            $stmt = $pdo->prepare("UPDATE user_devices SET last_login = NOW() WHERE user_id = ? AND hwid = ?");
            $stmt->execute([$userId, $hwid]);
        }
        echo json_encode(['success' => true]);
        break;

    // ==================== PROFILE ====================
    case 'get_profile':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $userId = intval($data['requesterId'] ?? 0);
        $stmt = $pdo->prepare("SELECT id, username, email, role, avatar, google_id, gender, country FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'User not found']);
            break;
        }
        $displayAvatar = resolveAvatarUrl($user['avatar'], $user['username']);
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar' => $displayAvatar,
                'has_google' => !empty($user['google_id']),
                'gender' => $user['gender'] ?? null,
                'country' => $user['country'] ?? null,
            ]
        ]);
        break;

    case 'update_profile':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $userId = intval($data['requesterId'] ?? 0);
        $username = trim($data['username'] ?? '');
        $avatar = $data['avatar'] ?? null;
        $gender = $data['gender'] ?? null;
        $country = trim($data['country'] ?? '');
        $password = $data['password'] ?? null;

        if (!$userId || !$username) {
            echo json_encode(['success' => false, 'error' => 'Username is required']);
            break;
        }
        if (mb_strlen($username) < 3) {
            echo json_encode(['success' => false, 'error' => 'Username must be at least 3 characters']);
            break;
        }

        if ($password !== null && $password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, avatar = ?, gender = ?, country = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $avatar ?: null, $gender, $country ?: null, $hash, $userId]);
        } elseif (array_key_exists('avatar', $data)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, avatar = ?, gender = ?, country = ? WHERE id = ?");
            $stmt->execute([$username, $avatar ?: null, $gender, $country ?: null, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, gender = ?, country = ? WHERE id = ?");
            $stmt->execute([$username, $gender, $country ?: null, $userId]);
        }

        // Fetch the current avatar for response (might have been updated or kept)
        $stmt = $pdo->prepare("SELECT avatar, gender, country FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentAvatar = $row['avatar'];
        $displayAvatar = resolveAvatarUrl($currentAvatar, $username);

        echo json_encode([
            'success' => true,
            'username' => $username,
            'avatar' => $displayAvatar,
            'gender' => $row['gender'] ?? null,
            'country' => $row['country'] ?? null,
        ]);
        break;

    // ==================== USERS ====================
    case 'get_users':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $stmt = $pdo->query("SELECT id, username, role, status, avatar FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as &$u) {
            $u['avatar'] = resolveAvatarUrl($u['avatar'], $u['username']);
        }
        echo json_encode($users);
        break;

    case 'get_online_users':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $ids = $data['ids'] ?? [];
        if (!empty($ids) && is_array($ids)) {
            $ids = array_map('intval', $ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT DISTINCT u.id, u.username FROM users u JOIN user_devices d ON d.user_id = u.id WHERE d.last_login >= NOW() - INTERVAL 5 SECOND AND u.status = 1 AND u.id IN ($placeholders)");
            $stmt->execute($ids);
        } else {
            $stmt = $pdo->query("SELECT DISTINCT u.id, u.username FROM users u JOIN user_devices d ON d.user_id = u.id WHERE d.last_login >= NOW() - INTERVAL 5 SECOND AND u.status = 1");
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'add_user':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'user';
        $gender = $data['gender'] ?? null;
        $country = trim($data['country'] ?? '');
        if (!$username || !$password) {
            echo json_encode(['success' => false, 'error' => 'Username and password required']);
            break;
        }
        if ($email !== '') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Email already exists']);
                break;
            }
        }
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Username already exists']);
            break;
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status, email_verified, gender, country) VALUES (?, ?, ?, ?, 1, 1, ?, ?)");
        $stmt->execute([$username, $email ?: null, $hash, $role, $gender, $country ?: null]);
        echo json_encode(['success' => true]);
        break;

    case 'update_user':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $id = $data['id'] ?? 0;
        $username = trim($data['username'] ?? '');
        $role = $data['role'] ?? 'user';
        if (!$id || !$username) {
            echo json_encode(['success' => false, 'error' => 'ID and username required']);
            break;
        }
        if (!empty($data['password'])) {
            $hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $role, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $role, $id]);
        }
        echo json_encode(['success' => true]);
        break;

    case 'toggle_user_status':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $id = $data['id'] ?? 0;
        $currentStatus = $data['currentStatus'] ?? 1;
        $newStatus = $currentStatus == 1 ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        echo json_encode(['success' => true]);
        break;

    case 'delete_user':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $id = $data['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM user_devices WHERE user_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND username != 'admin'");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    case 'reset_hwid':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $userId = $data['userId'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM user_devices WHERE user_id = ?");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true]);
        break;

    case 'get_users_paginated':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $page = max(1, intval($data['page'] ?? 1));
        $perPage = max(10, min(100, intval($data['per_page'] ?? 20)));
        $search = trim($data['search'] ?? '');
        $sort = in_array($data['sort'] ?? '', ['id', 'username', 'email', 'role', 'status', 'gender', 'country', 'created_at', 'last_activity', 'max_devices']) ? $data['sort'] : 'id';
        $sortDir = strtoupper($data['sort_dir'] ?? '') === 'DESC' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];
        if ($search !== '') {
            $where = "WHERE (username LIKE ? OR email LIKE ? OR id LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users {$where}");
        $countStmt->execute($params);
        $total = intval($countStmt->fetchColumn());
        $totalPages = max(1, ceil($total / $perPage));

        $activeCount = intval($pdo->query("SELECT COUNT(*) FROM users WHERE status = 1")->fetchColumn());
        $disabledCount = intval($pdo->query("SELECT COUNT(*) FROM users WHERE status = 0")->fetchColumn());
        $siteTotal = $activeCount + $disabledCount;
        $onlineCount = intval($pdo->query("SELECT COUNT(DISTINCT u.id) FROM users u JOIN user_devices d ON d.user_id = u.id WHERE d.last_login >= NOW() - INTERVAL 5 SECOND AND u.status = 1")->fetchColumn());

        $stmt = $pdo->prepare("SELECT id, username, email, role, status, avatar, gender, country, created_at, last_activity, max_devices FROM users {$where} ORDER BY {$sort} {$sortDir} LIMIT ? OFFSET ?");
        $i = 1;
        foreach ($params as $p) {
            $stmt->bindValue($i++, $p, PDO::PARAM_STR);
        }
        $stmt->bindValue($i++, (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue($i++, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as &$u) {
            $u['avatar'] = resolveAvatarUrl($u['avatar'], $u['username']);
            $u['created_at'] = $u['created_at'] ? date('Y-m-d\TH:i:s\Z', strtotime($u['created_at'])) : '';
            $u['last_activity'] = $u['last_activity'] ? date('Y-m-d\TH:i:s\Z', strtotime($u['last_activity'])) : '';
        }
        echo json_encode(['success' => true, 'users' => $users, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'total_pages' => $totalPages, 'site_total' => $siteTotal, 'online_count' => $onlineCount, 'active_count' => $activeCount, 'disabled_count' => $disabledCount]);
        break;

    case 'update_user_max_devices':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $userId = intval($data['user_id'] ?? 0);
        $maxDevices = $data['max_devices'] ?? '';
        if (!$userId) {
            echo json_encode(['success' => false, 'error' => 'معرف المستخدم مطلوب']);
            break;
        }
        if ($maxDevices === '' || $maxDevices === null) {
            $stmt = $pdo->prepare("UPDATE users SET max_devices = NULL WHERE id = ?");
            $stmt->execute([$userId]);
        } else {
            $val = max(0, intval($maxDevices));
            $stmt = $pdo->prepare("UPDATE users SET max_devices = ? WHERE id = ?");
            $stmt->execute([$val, $userId]);
        }
        echo json_encode(['success' => true]);
        break;

    case 'get_user_logs':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $userId = intval($data['user_id'] ?? 0);
        $page = max(1, intval($data['page'] ?? 1));
        $perPage = max(10, min(100, intval($data['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];
        if ($userId > 0) {
            $where = "WHERE user_id = ?";
            $params = [$userId];
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM login_logs {$where}");
        $countStmt->execute($params);
        $total = intval($countStmt->fetchColumn());
        $totalPages = max(1, ceil($total / $perPage));

        $stmt = $pdo->prepare("SELECT id, user_id, email, method, ip_address, user_agent, location_country, location_city, location_isp, location_ip, success, failure_reason, created_at FROM login_logs {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $i = 1;
        foreach ($params as $p) {
            $stmt->bindValue($i++, $p, PDO::PARAM_INT);
        }
        $stmt->bindValue($i++, (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue($i++, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($logs as &$log) {
            $log['created_at'] = $log['created_at'] ? date('Y-m-d\TH:i:s\Z', strtotime($log['created_at'])) : '';
        }
        echo json_encode(['success' => true, 'logs' => $logs, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'total_pages' => $totalPages]);
        break;

    // ==================== SETTINGS ====================
    case 'get_setting':
        $data = jsonInput();
        $key = $_GET['key'] ?? ($data['key'] ?? '');
        $publicSettings = ['google_client_id', 'google_login_enabled'];
        if (!in_array($key, $publicSettings, true)) {
            requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
            requireToken($pdo, $data);
        }
        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['value' => $row ? $row['value'] : null]);
        break;

    case 'save_setting':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $key = $data['key'] ?? '';
        $value = $data['value'] ?? '';
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->execute([$key, $value, $value]);
        echo json_encode(['success' => true]);
        break;

    case 'save_site_profile':
        $data = $_POST;
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $settings = [
            'site_name' => trim($data['site_name'] ?? 'Mou Default') ?: 'Mou Default',
            'site_short_desc' => trim($data['site_short_desc'] ?? ''),
            'site_full_desc' => trim($data['site_full_desc'] ?? ''),
        ];
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value, $value]);
        }
        if (!saveSiteIcons($_FILES['site_logo'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'فشل حفظ صورة الموقع']);
            break;
        }
        echo json_encode(['success' => true]);
        break;

    // ==================== USER DEVICES ====================
    case 'get_user_devices':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $userId = intval($data['requesterId'] ?? 0);
        $stmt = $pdo->prepare("SELECT id, hwid, device_info, last_login, created_at FROM user_devices WHERE user_id = ? ORDER BY last_login DESC");
        $stmt->execute([$userId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'remove_user_device':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $userId = intval($data['requesterId'] ?? 0);
        $deviceId = intval($data['device_id'] ?? 0);
        $currentHwid = $data['requesterHwid'] ?? '';
        if (!$deviceId) {
            echo json_encode(['success' => false, 'error' => 'معرف الجهاز مطلوب']);
            break;
        }
        $stmt = $pdo->prepare("SELECT id, hwid FROM user_devices WHERE id = ? AND user_id = ?");
        $stmt->execute([$deviceId, $userId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$device) {
            echo json_encode(['success' => false, 'error' => 'الجهاز غير موجود']);
            break;
        }
        if ($device['hwid'] === $currentHwid) {
            echo json_encode(['success' => false, 'error' => 'لا يمكن حذف الجهاز الحالي']);
            break;
        }
        $pdo->prepare("DELETE FROM user_devices WHERE id = ? AND user_id = ?")->execute([$deviceId, $userId]);
        echo json_encode(['success' => true]);
        break;

    // ==================== SESSION ====================
    case 'verify_session':
        $data = jsonInput();
        $userId = intval($data['userId'] ?? 0);
        $hwid = $data['hwid'] ?? '';
        $token = $data['token'] ?? '';
        $deviceAuthorized = verifyDevice($pdo, $userId, $hwid);
        $tokenAuthorized = verifyToken($pdo, $userId, $hwid, $token);
        $active = false;
        if ($userId > 0) {
            $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $active = $user && $user['status'] == 1;
        }
        echo json_encode(['success' => true, 'authorized' => $deviceAuthorized && $tokenAuthorized, 'active' => $active]);
        break;

    // ==================== FORGOT PASSWORD ====================
    case 'forgot_password':
        $data = jsonInput();
        $email = trim($data['email'] ?? '');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'البريد الإلكتروني غير صالح']);
            break;
        }

        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Always return success to prevent email enumeration
        if (!$user) {
            echo json_encode(['success' => true, 'message' => 'إذا كان البريد الإلكتروني مسجلاً، ستتلقى رابط إعادة تعيين كلمة المرور']);
            break;
        }

        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))");
        $stmt->execute([$user['id'], $token]);

        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'app_name'");
        $stmt->execute();
        $appName = ($row = $stmt->fetch()) ? $row['value'] : 'Mou Default';

        $resetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}/reset-password.php?token=" . urlencode($token);

        $template = file_get_contents(__DIR__ . '/forget_email_template.html');
        $template = str_replace(
            ['{{name}}', '{{action_url}}', '{{device_name}}', '{{support_url}}', '[Product Name]'],
            [$user['username'], $resetUrl, $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', $email, $appName],
            $template
        );

        $sent = sendMail($pdo, $email, 'إعادة تعيين كلمة المرور', $template);

        echo json_encode(['success' => true, 'message' => 'إذا كان البريد الإلكتروني مسجلاً، ستتلقى رابط إعادة تعيين كلمة المرور']);
        break;

    // ==================== VERIFY RESET CODE ====================
    case 'verify_reset_token':
        $data = jsonInput();
        $token = trim($data['token'] ?? '');
        $password = $data['password'] ?? '';

        if (!$token || !$password) {
            echo json_encode(['success' => false, 'error' => 'البيانات غير مكتملة']);
            break;
        }
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'error' => 'كلمة المرور قصيرة جداً']);
            break;
        }

        $stmt = $pdo->prepare("SELECT pr.user_id, u.username FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0 LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'الرابط غير صالح أو منتهي الصلاحية']);
            break;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $row['user_id']]);
        $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")->execute([$token]);

        if (!empty($data['reset_devices'])) {
            $pdo->prepare("DELETE FROM user_devices WHERE user_id = ?")->execute([$row['user_id']]);
        }

        echo json_encode(['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح']);
        break;

    // ==================== TEST SMTP ====================
    case 'test_smtp':
        $data = jsonInput();
        requireActiveStatus($pdo, intval($data['requesterId'] ?? 0));
        requireToken($pdo, $data);
        $testEmail = trim($data['test_email'] ?? '');
        if (!$testEmail || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'البريد الإلكتروني للاختبار غير صالح']);
            break;
        }

        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'app_name'");
        $stmt->execute();
        $appName = ($row = $stmt->fetch()) ? $row['value'] : 'Mou Default';

        $sent = sendMail($pdo, $testEmail, 'اختبار إعدادات البريد', "<h2>اختبار ناجح</h2><p>تهانينا! إعدادات البريد الإلكتروني تعمل بشكل صحيح.</p>");

        echo json_encode(['success' => $sent, 'error' => $sent ? null : 'فشل إرسال البريد. تحقق من الإعدادات']);
        break;

    // ==================== VERIFY EMAIL ====================
    case 'verify_email':
        $data = jsonInput();
        $userId = intval($data['user_id'] ?? 0);
        $code = trim($data['code'] ?? '');
        $deviceInfo = $data['device_info'] ?? [];

        if (!$userId || !$code) {
            echo json_encode(['success' => false, 'error' => 'البيانات غير مكتملة']);
            break;
        }

        $stmt = $pdo->prepare("SELECT id, username, email, email_verification_code, email_verification_expires FROM users WHERE id = ? AND email_verified = 0");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'الحساب غير موجود أو تم تفعيله مسبقاً']);
            break;
        }

        if (!$user['email_verification_code'] || $user['email_verification_code'] !== $code) {
            echo json_encode(['success' => false, 'error' => 'كود التفعيل غير صحيح']);
            break;
        }

        if ($user['email_verification_expires'] && strtotime($user['email_verification_expires']) < time()) {
            echo json_encode(['success' => false, 'error' => 'انتهت صلاحية الكود. اطلب كود جديد']);
            break;
        }

        $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, status = 1, email_verification_code = NULL, email_verification_expires = NULL, last_activity = NOW() WHERE id = ?");
        $stmt->execute([$userId]);

        // Auto-register device
        $hwid = $deviceInfo['hwid'] ?? 'web';
        $infoJson = json_encode($deviceInfo, JSON_UNESCAPED_UNICODE);
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("INSERT INTO user_devices (user_id, hwid, device_info, token, last_login) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE device_info = VALUES(device_info), token = VALUES(token), last_login = NOW()");
        $stmt->execute([$userId, $hwid, $infoJson, $token]);

        syncToElbatal($pdo, $userId, $data);

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $userId,
                'username' => $user['username'],
                'role' => 'user',
                'avatar' => resolveAvatarUrl(null, $user['username']),
                'token' => $token,
            ]
        ]);
        break;

    // ==================== RESEND VERIFICATION ====================
    case 'resend_verification':
        $data = jsonInput();
        $userId = intval($data['user_id'] ?? 0);

        if (!$userId) {
            echo json_encode(['success' => false, 'error' => 'البيانات غير مكتملة']);
            break;
        }

        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ? AND email_verified = 0 AND status = 0");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'الحساب غير موجود أو تم تفعيله مسبقاً']);
            break;
        }

        if (!$user['email']) {
            echo json_encode(['success' => false, 'error' => 'لا يوجد بريد إلكتروني مسجل لهذا الحساب']);
            break;
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("UPDATE users SET email_verification_code = ?, email_verification_expires = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?");
        $stmt->execute([$otp, $userId]);

        $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'app_name'");
        $stmt->execute();
        $appName = ($row = $stmt->fetch()) ? $row['value'] : 'Mou Default';

        $htmlBody = "
        <div style='font-family:sans-serif;max-width:600px;margin:0 auto;padding:30px;background:#f9f9f9;border-radius:12px;'>
            <div style='text-align:center;padding:20px 0;'>
                <h2 style='color:#333;margin:0;'>$appName</h2>
            </div>
            <div style='background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.06);'>
                <h1 style='font-size:20px;color:#333;margin:0 0 16px;'>مرحباً {$user['username']}</h1>
                <p style='color:#666;line-height:1.6;margin:0 0 20px;'>إليك كود التفعيل الجديد لحسابك في $appName:</p>
                <div style='text-align:center;padding:20px 0;'>
                    <span style='display:inline-block;font-size:32px;font-weight:bold;letter-spacing:8px;color:#ffcc00;background:#1a1a1a;padding:16px 32px;border-radius:10px;'>$otp</span>
                </div>
                <p style='color:#999;font-size:13px;margin:20px 0 0;'>الكود صالح لمدة 15 دقيقة.</p>
            </div>
        </div>";

        $sent = sendMail($pdo, $user['email'], 'إعادة إرسال كود التفعيل - ' . $appName, $htmlBody);

        if (!$sent) {
            echo json_encode(['success' => false, 'error' => 'فشل إرسال البريد. تحقق من إعدادات البريد']);
            break;
        }

        echo json_encode(['success' => true, 'message' => 'تم إرسال كود التفعيل الجديد']);
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
