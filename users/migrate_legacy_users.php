<?php
// ============================================================
// Migrate legacy users (Elbatal_Users) -> new auth system (users)
// ============================================================
// How to use:
//   1. Import the old database dump (elbarymo_elbataltv.sql) into
//      the "elbataltv" database first (phpMyAdmin Import).
//   2. Open in browser: https://new.elbatal-app.com/users/migrate_legacy_users.php?run=1
// Safe to re-run (idempotent): existing ids are updated, missing ones inserted.
// ============================================================

require_once __DIR__ . '/config_db.php'; // provides $conn (mysqli) + table name vars

header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!isset($_GET['run']) || $_GET['run'] !== '1') {
    echo "Dry-run mode: append ?run=1 to actually migrate.\n\n";
}

$execute = (isset($_GET['run']) && $_GET['run'] === '1');
$conn->set_charset("utf8mb4");

// ------------------------------------------------------------
// 1) Make sure the new-schema tables exist
// ------------------------------------------------------------
$conn->query("CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_code` varchar(6) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT NULL,
  `max_devices` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `user_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hwid` varchar(255) NOT NULL,
  `device_info` longtext DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `last_login` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_device` (`user_id`, `hwid`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$defaultSettings = [
    'upload_mode' => 'drive',
    'google_client_id' => '',
    'google_client_secret' => '',
    'google_login_enabled' => '0',
    'email_verification_enabled' => '0',
    'max_devices' => '2',
    'site_name' => 'Elbatal TV',
    'app_name' => 'البطل',
    'site_short_desc' => 'متعة وسهولة المشاهدة',
    'site_full_desc' => 'البطل - افضل تطبيق لمشاهدة المباريات والافلام والمسلسلات مجانا',
    'smtp_host' => '',
    'smtp_port' => '587',
    'smtp_email' => '',
    'smtp_password' => '',
    'smtp_encryption' => 'tls',
    'tz_migrated' => '1',
];
foreach ($defaultSettings as $k => $v) {
    $stmt = $conn->prepare("INSERT IGNORE INTO `settings` (`key`, `value`) VALUES (?, ?)");
    $stmt->bind_param('ss', $k, $v);
    $stmt->execute();
}

// ------------------------------------------------------------
// 2) Check the legacy table exists
// ------------------------------------------------------------
$check = $conn->query("SHOW TABLES LIKE 'Elbatal_Users'");
if ($check->num_rows === 0) {
    die("ERROR: table 'Elbatal_Users' not found. Import the old database dump first.");
}

// ------------------------------------------------------------
// 3) Track usernames already used (to avoid UNIQUE collisions)
//    Keys are normalized because the DB username column uses a
//    case-insensitive PAD SPACE collation ('Mohammed' == 'Mohammed ').
// ------------------------------------------------------------
function unameKey($name) {
    return mb_strtolower(rtrim((string)$name));
}

$usedUsernames = [];
$q = $conn->query("SELECT username FROM `users`");
if ($q) {
    while ($r = $q->fetch_assoc()) {
        $usedUsernames[unameKey($r['username'])] = true;
    }
}

$selectStmt = $conn->prepare("SELECT id FROM `users` WHERE id = ?");
$insertStmt = $conn->prepare("INSERT INTO `users` (id, username, email, email_verified, google_id, avatar, password, role, status, created_at, last_activity, max_devices) VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, NULL)");
$updateStmt = $conn->prepare("UPDATE `users` SET username = ?, email = ?, email_verified = 1, google_id = ?, avatar = ?, password = ?, role = ?, status = ?, created_at = ?, last_activity = ? WHERE id = ?");

$total = 0;
$inserted = 0;
$updated = 0;
$skipped = 0;

// Process in chunks: fully consume each SELECT before running inserts,
// which avoids connection-state errors during buffered iteration.
$chunkSize = 1000;
$offset = 0;
$fetchStmt = $conn->prepare("SELECT id, username, email, password, user_icon, g_icon, avatar_or_g_icon, google_linked_id, role, active, created_at FROM `Elbatal_Users` ORDER BY `id` ASC LIMIT ? OFFSET ?");

while (true) {
    $fetchStmt->bind_param('ii', $chunkSize, $offset);
    $fetchStmt->execute();
    $rows = $fetchStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (empty($rows)) break;

    foreach ($rows as $row) {
    $total++;

    $id = (int)$row['id'];

    // ---- username (dedupe + length limit) ----
    $baseName = (isset($row['username']) && trim($row['username']) !== '')
        ? mb_substr($row['username'], 0, 100)
        : ('user' . $id);
    $username = $baseName;
    $suffix = 2;
    while (isset($usedUsernames[unameKey($username)])) {
        $candidate = mb_substr($baseName, 0, 100 - strlen('_' . $suffix)) . '_' . $suffix;
        $username = $candidate;
        $suffix++;
    }
    $usedUsernames[unameKey($username)] = true;

    // ---- email ----
    $email = (isset($row['email']) && trim($row['email']) !== '') ? $row['email'] : null;

    // ---- google id ----
    $googleId = (!empty($row['google_linked_id'])) ? $row['google_linked_id'] : null;

    // ---- avatar ----
    $gIcon = !empty($row['g_icon']) ? $row['g_icon'] : null;
    $userIcon = !empty($row['user_icon']) ? $row['user_icon'] : null;
    if (!empty($gIcon) && (int)$row['avatar_or_g_icon'] == 2) {
        $avatar = $gIcon;
    } elseif (!empty($userIcon)) {
        $avatar = '/avatar.php?seed=' . rawurlencode($userIcon);
    } else {
        $avatar = '/avatar.php?seed=' . rawurlencode($username);
    }

    // ---- password (bcrypt; google-only accounts get a random unusable hash) ----
    $password = (!empty($row['password'])) ? $row['password'] : password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);

    // ---- role / status ----
    $role = (isset($row['role']) && strtolower(trim($row['role'])) === 'admin') ? 'admin' : 'user';
    $status = ((int)$row['active'] === 1) ? 1 : 0;
    $createdAt = (!empty($row['created_at'])) ? $row['created_at'] : date('Y-m-d H:i:s');
    $lastActivity = $createdAt;

    // ---- insert or update ----
    $selectStmt->bind_param('i', $id);
    $selectStmt->execute();
    $exists = $selectStmt->get_result()->fetch_assoc();

    if ($execute) {
        try {
            if (!$exists) {
                $insertStmt->bind_param('issssssiss', $id, $username, $email, $googleId, $avatar, $password, $role, $status, $createdAt, $lastActivity);
                $insertStmt->execute();
                $inserted++;
            } else {
                $updateStmt->bind_param('sssssssssi', $username, $email, $googleId, $avatar, $password, $role, $status, $createdAt, $lastActivity, $id);
                $updateStmt->execute();
                $updated++;
            }
        } catch (mysqli_sql_exception $e) {
            // Fallback: some legacy usernames exist in mixed byte encodings
            // (e.g. double-encoded Arabic) that defeat string-based dedupe.
            // Retry once with a guaranteed-unique name to preserve the account.
            $fallbackName = 'user_' . $id;
            try {
                if (!$exists) {
                    $insertStmt->bind_param('issssssiss', $id, $fallbackName, $email, $googleId, $avatar, $password, $role, $status, $createdAt, $lastActivity);
                    $insertStmt->execute();
                    $inserted++;
                } else {
                    $updateStmt->bind_param('sssssssssi', $fallbackName, $email, $googleId, $avatar, $password, $role, $status, $createdAt, $lastActivity, $id);
                    $updateStmt->execute();
                    $updated++;
                }
            } catch (mysqli_sql_exception $e2) {
                $skipped++;
                error_log("migrate_legacy_users: id={$id} skipped: " . $e2->getMessage());
            }
        }
    }
    }

    $offset += $chunkSize;
}

if (!$execute) {
    echo "Found {$total} legacy users. They will be migrated when you run with ?run=1.\n";
} else {
    echo "Migration finished.\n";
    echo "  Total legacy users scanned : {$total}\n";
    echo "  Inserted (new)             : {$inserted}\n";
    echo "  Updated (existing)         : {$updated}\n";
    if ($skipped > 0) {
        echo "  Skipped (errors)           : {$skipped} (see PHP error log)\n";
    }
    echo "\nYou should now delete this file from the server.\n";
}
