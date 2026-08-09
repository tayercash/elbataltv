<?php
session_start();
$lockFile = __DIR__ . '/installed.lock';
if (file_exists($lockFile)) {
    header('Location: login.php');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'save_progress') {
    header('Content-Type: application/json');
    $_SESSION['install'] = $_POST['data'] ?? [];
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'restore_progress') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $_SESSION['install'] ?? []]);
    exit;
}

if ($action === 'check_requirements') {
    header('Content-Type: application/json');
    $required = [
        'pdo_mysql' => 'PDO MySQL',
        'mysqli' => 'MySQLi',
        'curl' => 'cURL',
        'fileinfo' => 'Fileinfo',
        'mbstring' => 'Mbstring',
        'openssl' => 'OpenSSL',
        'gd' => 'GD',
        'exif' => 'Exif',
        'json' => 'JSON',
        'session' => 'Session',
    ];
    $items = [];
    $ok = true;
    foreach ($required as $extension => $label) {
        $loaded = extension_loaded($extension);
        $items[] = ['extension' => $extension, 'label' => $label, 'loaded' => $loaded];
        if (!$loaded) $ok = false;
    }
    echo json_encode(['success' => true, 'ok' => $ok, 'items' => $items]);
    exit;
}

if ($action === 'test_connection') {
    header('Content-Type: application/json');
    $host = $_POST['host'] ?? '';
    $name = $_POST['name'] ?? '';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo json_encode(['success' => true, 'message' => 'Connection successful!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'run_sql') {
    header('Content-Type: application/json');
    $host = $_POST['host'] ?? '';
    $name = $_POST['name'] ?? '';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");

        $sqlPath = __DIR__ . '/database.sql';
        if (!file_exists($sqlPath)) {
            echo json_encode(['success' => false, 'message' => 'database.sql file not found!']);
            exit;
        }
        $sql = file_get_contents($sqlPath);
        $sql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $sql);
        $sql = preg_replace('/USE `[^`]+`;/i', '', $sql);
        $sql = preg_replace('/^--[^\n]*\n?/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        $grouped = ['create_table' => [], 'create_view' => [], 'other' => []];
        foreach ($statements as $stmt) {
            if (preg_match('/^CREATE\s+TABLE/i', $stmt)) {
                $grouped['create_table'][] = $stmt;
            } elseif (preg_match('/^CREATE\s+(OR\s+REPLACE\s+)?VIEW/i', $stmt)) {
                $grouped['create_view'][] = $stmt;
            } else {
                $grouped['other'][] = $stmt;
            }
        }
        $executed = 0;
        foreach (array_merge($grouped['create_table'], $grouped['create_view'], $grouped['other']) as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
                $executed++;
            }
        }
        echo json_encode(['success' => true, 'message' => "Database ready! $executed queries executed."]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'SQL execution error: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'create_admin') {
    header('Content-Type: application/json');
    $host = $_POST['host'] ?? '';
    $name = $_POST['name'] ?? '';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';

    if (!$adminUser || !$adminPass) {
        echo json_encode(['success' => false, 'message' => 'Admin username and password are required']);
        exit;
    }
    if (strlen($adminPass) < 4) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 4 characters']);
        exit;
    }
    if ($adminEmail && !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10
        ]);
        $pdo->exec("USE `$name`");

        $hash = password_hash($adminPass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id > 0");
        $stmt->execute();
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'admin', 1)");
        $stmt->execute([$adminUser, $adminEmail ?: null, $hash]);

        echo json_encode(['success' => true, 'message' => 'Admin account created!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'run_installation') {
    header('Content-Type: application/json');
    $host = $_POST['host'] ?? '';
    $name = $_POST['name'] ?? '';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $siteName = $_POST['site_name'] ?? 'Mou Default';
    $shortDesc = $_POST['short_desc'] ?? '';
    $fullDesc = $_POST['full_desc'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30
        ]);
        $pdo->exec("USE `$name`");

        $settings = [
            'site_name' => $siteName,
            'site_short_desc' => $shortDesc,
            'site_full_desc' => $fullDesc,
        ];
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value, $value]);
        }

        $escHost = str_replace("'", "\\'", $host);
        $escName = str_replace("'", "\\'", $name);
        $escUser = str_replace("'", "\\'", $user);
        $escPass = str_replace("'", "\\'", $pass);
        $localConfig = "<?php
define('DB_HOST', '$escHost');
define('DB_NAME', '$escName');
define('DB_USER', '$escUser');
define('DB_PASS', '$escPass');
";
        file_put_contents(__DIR__ . '/config.local.php', $localConfig);

        ensureSiteIcons($_FILES['logo'] ?? null);

        file_put_contents($lockFile, date('Y-m-d H:i:s'));
        $_SESSION['install'] = [];

        echo json_encode(['success' => true, 'message' => 'Installation completed successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

function createPngFromImage($sourcePath, $targetPath, $size) {
    if (!function_exists('imagecreatetruecolor')) {
        return copy($sourcePath, $targetPath);
    }
    $info = @getimagesize($sourcePath);
    if (!$info) return false;
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $src = @imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $src = @imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $src = @imagecreatefromgif($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false;
            break;
        default:
            $src = false;
    }
    if (!$src) return false;
    $dst = imagecreatetruecolor($size, $size);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $size, $size, $transparent);
    $srcWidth = imagesx($src);
    $srcHeight = imagesy($src);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $size, $size, $srcWidth, $srcHeight);
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

function ensureSiteIcons($uploadedFile) {
    $imgDir = __DIR__ . '/assets/img';
    if (!is_dir($imgDir)) {
        mkdir($imgDir, 0755, true);
    }
    $logoPath = $imgDir . '/logo_192.png';
    $faviconPath = __DIR__ . '/favicon.ico';
    $sourcePath = null;
    if ($uploadedFile && $uploadedFile['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed, true)) {
            $sourcePath = $uploadedFile['tmp_name'];
            if (!createPngFromImage($sourcePath, $logoPath, 192)) {
                move_uploaded_file($sourcePath, $logoPath);
                $sourcePath = $logoPath;
            }
        }
    }
    if (!$sourcePath) {
        if (!file_exists($logoPath)) {
            createPngFromImage(__DIR__ . '/assets/img/logo_192.png', $logoPath, 192);
        }
        $sourcePath = $logoPath;
    }
    if (file_exists($sourcePath)) {
        createFaviconFromImage($sourcePath, $faviconPath);
    }
}
?>
<!DOCTYPE html>
<html class="dark">
<head>
<script>
  (function(){
    var l=localStorage.getItem('lang')||'ar';
    document.documentElement.lang=l;
    document.documentElement.dir=l==='ar'?'rtl':'ltr';
  })();
</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup</title>
<link rel="icon" href="favicon.ico">
<link rel="stylesheet" href="assets/fonts/font-awesome/css/all.min.css">
<link rel="stylesheet" href="assets/fonts/noto-sans-arabic/font.css">
<script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.className=document.documentElement.className.replace(/\b(dark|light)\b/g,'').trim();document.documentElement.classList.add(t)})();</script>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Noto Sans Arabic',sans-serif; }
:root {
  --bg:#080809; --card:#121214; --primary:#ffcc00; --text:#fff; --dim:#9e9e9e;
  --input-bg:#19191d; --border:rgba(255,204,0,0.15); --danger:#ef4444; --success:#10b981;
}
html { background:#0d0d0e; width:100%; height:100%; }
body {
  min-height:100vh; display:flex; align-items:center; justify-content:center;
  background:radial-gradient(circle at center,#1c1808 0%,#0d0d0e 80%);
  padding:20px; position:relative;
}
.bg-glow-1 {
  position:fixed; width:300px; height:300px;
  background:radial-gradient(circle,rgba(255,204,0,0.12) 0%,transparent 70%);
  top:15%; left:10%; filter:blur(50px); z-index:0;
  animation:floatGlow 8s infinite alternate ease-in-out;
}
.bg-glow-2 {
  position:fixed; width:350px; height:350px;
  background:radial-gradient(circle,rgba(255,153,0,0.1) 0%,transparent 70%);
  bottom:10%; right:5%; filter:blur(60px); z-index:0;
  animation:floatGlow 10s infinite alternate-reverse ease-in-out;
}
@keyframes floatGlow {
  0% { transform:translateY(0) scale(1); }
  100% { transform:translateY(-20px) scale(1.1); }
}
.install-wrapper { width:100%; max-width:560px; position:relative; z-index:1; }
.install-header { text-align:center; margin-bottom:30px; display:flex; flex-direction:column; align-items:center; gap:10px; }
.install-header .logo-icon {
  width:64px; height:64px; border-radius:16px;
  border:2px solid rgba(255,204,0,0.5); box-shadow:0 8px 24px rgba(255,204,0,0.2);
  animation:logoPulse 4s infinite alternate;
}
@keyframes logoPulse {
  0% { transform:scale(1); box-shadow:0 8px 24px rgba(255,204,0,0.15); }
  100% { transform:scale(1.05); box-shadow:0 12px 30px rgba(255,204,0,0.35); }
}
.install-header h1 {
  font-size:1.5rem; font-weight:800;
  background:linear-gradient(135deg,#ffffff 30%,#e0e0e0 70%,#ffcc00 100%);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.install-header p { color:#9e9e9e; font-size:0.85rem; }
.steps-bar { display:flex; justify-content:center; gap:8px; margin-bottom:30px; }
.step-dot { display:flex; align-items:center; gap:8px; }
.step-dot .circle {
  width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center;
  font-size:0.8rem; font-weight:700; background:rgba(255,255,255,0.04);
  border:1.5px solid rgba(255,255,255,0.08); color:#777; transition:all 0.3s;
}
.step-dot.active .circle {
  background:linear-gradient(135deg,#ffcc00,#ff9900); color:#0b0a02;
  border-color:#ffcc00; box-shadow:0 4px 15px rgba(255,204,0,0.3);
}
.step-dot.done .circle { background:var(--success); color:#fff; border-color:var(--success); box-shadow:0 4px 12px rgba(16,185,129,0.3); }
.step-dot .label { font-size:0.75rem; color:#777; font-weight:500; }
.step-dot.active .label { color:#ffcc00; font-weight:600; }
.step-dot.done .label { color:var(--success); }
.step-line { width:36px; height:1.5px; background:rgba(255,255,255,0.08); align-self:center; }

.card {
  background:rgba(18,18,20,0.75) !important;
  backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px);
  border:1px solid rgba(255,204,0,0.12); border-radius:24px;
  padding:30px; box-shadow:0 20px 50px rgba(0,0,0,0.6), inset 0 1px 2px rgba(255,255,255,0.05);
  transition:all 0.3s;
}
.card:hover { border-color:rgba(255,204,0,0.25); }
.card h2 {
  color:#fff; font-size:1.15rem; margin-bottom:4px;
  display:flex; align-items:center; gap:10px; font-weight:700;
}
.card h2 i { color:#ffcc00; font-size:1.05rem; }
.card .subtitle { color:#9e9e9e; font-size:0.8rem; margin-bottom:22px; }

.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:0.8rem; color:#d1d1d6; margin-bottom:6px; font-weight:500; }
.input-icon-wrap { position:relative; display:flex; align-items:center; }
.input-icon-wrap i { position:absolute; right:13px; color:#777; font-size:1rem; transition:color 0.3s; z-index:2; }
.input-icon-wrap input, .input-icon-wrap textarea {
  width:100%; padding:12px 40px 12px 14px;
  border-radius:12px; border:1px solid rgba(255,255,255,0.08);
  background:rgba(255,255,255,0.04); color:#fff;
  font-size:0.9rem; font-family:inherit; outline:none;
  transition:all 0.3s; box-sizing:border-box;
}
.input-icon-wrap textarea { padding:12px 14px; min-height:90px; resize:vertical; }
.input-icon-wrap input:focus, .input-icon-wrap textarea:focus {
  border-color:rgba(255,204,0,0.6);
  background:rgba(255,255,255,0.07);
  box-shadow:0 0 12px rgba(255,204,0,0.12);
}
.input-icon-wrap input:focus + i { color:#ffcc00; }
.form-row { display:flex; gap:10px; }
.form-row .form-group { flex:1; }

.file-upload {
  position:relative; display:flex; align-items:center; gap:12px; padding:12px;
  background:rgba(255,255,255,0.03); border:1.5px dashed rgba(255,255,255,0.1);
  border-radius:12px; cursor:pointer; transition:all 0.3s;
}
.file-upload:hover { border-color:rgba(255,204,0,0.4); background:rgba(255,204,0,0.03); }
.file-upload input { position:absolute; inset:0; opacity:0; cursor:pointer; }
.file-upload .upload-icon {
  width:44px; height:44px; border-radius:12px;
  background:linear-gradient(135deg,rgba(255,204,0,0.12),rgba(255,153,0,0.08));
  display:flex; align-items:center; justify-content:center; color:#ffcc00; font-size:1.2rem; flex-shrink:0;
}
.file-upload .upload-info { flex:1; }
.file-upload .upload-info .main-text { color:#e0e0e0; font-size:0.85rem; font-weight:500; }
.file-upload .upload-info .sub-text { color:#888; font-size:0.75rem; margin-top:2px; }
.logo-preview { width:54px; height:54px; border-radius:10px; object-fit:cover; border:2px solid rgba(255,204,0,0.4); display:none; }

.btn {
  width:100%; padding:13px; border:none; border-radius:12px;
  font-size:0.95rem; font-weight:700; cursor:pointer;
  font-family:inherit; transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
}
.btn-primary {
  background:linear-gradient(135deg,#ffcc00,#ff9900); color:#0b0a02;
  box-shadow:0 4px 15px rgba(255,204,0,0.2);
}
.btn-primary:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 8px 25px rgba(255,204,0,0.35); }
.btn-primary:disabled { opacity:0.4; cursor:not-allowed; transform:none; box-shadow:none; }
.btn-outline {
  background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); color:#ccc;
}
.btn-outline:hover { border-color:rgba(255,204,0,0.4); color:#ffcc00; background:rgba(255,204,0,0.04); }
.btn-success {
  background:linear-gradient(135deg,#10b981,#059669); color:#fff;
  box-shadow:0 4px 15px rgba(16,185,129,0.25);
}
.btn-success:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(16,185,129,0.35); }

.status-msg { text-align:center; font-size:0.8rem; margin-top:10px; min-height:20px; border-radius:8px; padding:6px; }
.status-msg.success { color:var(--success); background:rgba(16,185,129,0.08); margin-bottom:12px; }
.status-msg.error { color:var(--danger); background:rgba(239,68,68,0.08); }
.status-msg.loading { color:#ffcc00; background:rgba(255,204,0,0.06); }
.requirements-box {
  background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);
  border-radius:14px; padding:14px; margin-bottom:20px;
}
.requirements-title { display:flex; align-items:center; gap:8px; color:#e0e0e0; font-size:0.9rem; font-weight:700; margin-bottom:10px; }
.requirements-title i { color:#ffcc00; }
.requirements-list { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.requirement-item {
  display:flex; align-items:center; justify-content:space-between; gap:8px;
  padding:8px 10px; border-radius:10px; background:rgba(0,0,0,0.18); color:#ccc; font-size:0.78rem;
}
.requirement-item.ok i { color:var(--success); }
.requirement-item.missing i { color:var(--danger); }
#recheck_requirements_btn { margin-bottom:12px; }
@media (max-width:520px) { .requirements-list { grid-template-columns:1fr; } }

.step { display:none; }
.step.active { display:block; }

.loading-spinner { display:inline-block; width:14px; height:14px; border:2px solid rgba(255,204,0,0.3); border-top-color:#ffcc00; border-radius:50%; animation:spin 0.6s linear infinite; vertical-align:middle; margin-left:5px; }
@keyframes spin { to { transform:rotate(360deg); } }

.result-box { text-align:center; padding:30px 10px; }
.result-box .result-icon { font-size:3.2rem; margin-bottom:14px; }
.result-box .result-icon.success { color:var(--success); }
.result-box .result-icon.error { color:var(--danger); }
.result-box h3 { color:#fff; font-size:1.15rem; margin-bottom:6px; font-weight:700; }
.result-box p { color:#9e9e9e; font-size:0.85rem; margin-bottom:22px; }

.sql-info {
  background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);
  border-radius:14px; padding:18px; margin-bottom:20px; text-align:center;
}
.sql-info i { color:#ffcc00; font-size:1.8rem; margin-bottom:10px; display:block; }
.sql-info h3 { color:#e0e0e0; font-size:0.95rem; font-weight:600; margin-bottom:4px; }
.sql-info p { color:#888; font-size:0.8rem; }
.progress-track {
  background:rgba(255,255,255,0.06); border-radius:8px; height:6px; overflow:hidden; margin-bottom:8px;
}
.progress-track .progress-fill {
  height:100%; border-radius:8px;
  background:linear-gradient(90deg,#ffcc00,#ff9900);
  transition:width 0.5s ease;
}

.btn-row { display:flex; gap:10px; margin-top:8px; }
.btn-row .btn { margin-top:0; }

input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-box-shadow:0 0 0px 1000px #1c1c1e inset !important;
  -webkit-text-fill-color:white !important;
  transition:background-color 50000s ease-in-out 0s;
}
</style>
</head>
<body>
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="install-wrapper">
  <div class="install-header">
    <img src="assets/img/logo_192.png" alt="" class="logo-icon" onerror="this.style.display='none'">
    <h1>Site Setup</h1>
    <p data-i18n-install="setup.subtitle">Configure your database and site information</p>
  </div>

    <div class="steps-bar">
      <div class="step-dot active" id="step-dot-1">
        <span class="circle">1</span>
        <span class="label" data-i18n-install="requirements.short_title">Requirements</span>
      </div>
      <div class="step-line"></div>
      <div class="step-dot" id="step-dot-2">
        <span class="circle">2</span>
        <span class="label" data-i18n-install="setup.database">Database</span>
      </div>
      <div class="step-line"></div>
      <div class="step-dot" id="step-dot-3">
        <span class="circle">3</span>
        <span class="label" data-i18n-install="setup.setup_db">Setup DB</span>
      </div>
      <div class="step-line"></div>
      <div class="step-dot" id="step-dot-4">
        <span class="circle">4</span>
        <span class="label" data-i18n-install="setup.admin_account">Admin</span>
      </div>
      <div class="step-line"></div>
      <div class="step-dot" id="step-dot-5">
        <span class="circle">5</span>
        <span class="label" data-i18n-install="setup.site_info">Site Info</span>
      </div>
    </div>

  <div class="card">
    <!-- Step 1: Requirements -->
    <div class="step active" id="step-1">
      <h2><i class="fas fa-puzzle-piece"></i> <span data-i18n-install="requirements.title">PHP Extensions</span></h2>
      <div class="subtitle" data-i18n-install="requirements.subtitle">Check required PHP extensions before installation</div>

      <div class="requirements-box">
        <div class="requirements-title"><i class="fas fa-puzzle-piece"></i> <span data-i18n-install="requirements.title">PHP Extensions</span></div>
        <div id="requirements_list" class="requirements-list"></div>
        <div class="status-msg loading" id="requirements_status" data-i18n-install="requirements.checking">Checking requirements...</div>
      </div>

      <button class="btn btn-outline" id="recheck_requirements_btn" onclick="checkRequirements()">
        <i class="fas fa-sync-alt"></i> <span data-i18n-install="requirements.recheck">Re-check Requirements</span>
      </button>
      <button class="btn btn-primary" id="step1_next" disabled onclick="goToStep(2)">
        <span data-i18n-install="common.next">Next Step</span> <i class="fas fa-arrow-right"></i>
      </button>
    </div>

    <!-- Step 2: Database Connection -->
    <div class="step" id="step-2">
      <h2><i class="fas fa-database"></i> <span data-i18n-install="db.title">Database Connection</span></h2>
      <div class="subtitle" data-i18n-install="db.subtitle">Enter your MySQL database credentials</div>

      <div class="form-group">
        <label data-i18n-install="db.host">Database Host</label>
        <div class="input-icon-wrap">
          <input type="text" id="db_host" value="127.0.0.1" data-i18n-install-placeholder="placeholder.db_host" placeholder="e.g. 127.0.0.1 or localhost">
          <i class="fas fa-server"></i>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label data-i18n-install="db.name">Database Name</label>
          <div class="input-icon-wrap">
            <input type="text" id="db_name" data-i18n-install-placeholder="placeholder.db_name" placeholder="Database name">
            <i class="fas fa-database"></i>
          </div>
        </div>
        <div class="form-group">
          <label data-i18n-install="db.user">Username</label>
          <div class="input-icon-wrap">
            <input type="text" id="db_user" value="root" data-i18n-install-placeholder="placeholder.db_user" placeholder="MySQL username">
            <i class="fas fa-user"></i>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label data-i18n-install="db.pass">Password</label>
        <div class="input-icon-wrap">
          <input type="password" id="db_pass" data-i18n-install-placeholder="placeholder.db_pass" placeholder="MySQL password">
          <i class="fas fa-lock"></i>
        </div>
      </div>

      <button class="btn btn-outline" id="test_conn_btn" onclick="testConnection()">
        <i class="fas fa-plug"></i> <span data-i18n-install="db.test">Test Connection</span>
      </button>
      <div class="status-msg" id="conn_status"></div>

      <button class="btn btn-primary" id="step2_next" disabled onclick="goToStep(3)">
        <span data-i18n-install="common.next">Next Step</span> <i class="fas fa-arrow-right"></i>
      </button>
    </div>

    <!-- Step 3: Run Database SQL -->
    <div class="step" id="step-3">
      <h2><i class="fas fa-table"></i> <span data-i18n-install="sql.title">Setup Database Tables</span></h2>
      <div class="subtitle" data-i18n-install="sql.subtitle">Run database.sql to create all tables</div>

      <div class="sql-info">
        <i class="fas fa-layer-group"></i>
        <h3 data-i18n-install="sql.info_title">Database Tables</h3>
        <p data-i18n-install="sql.info_desc">Creates tables: users, settings, device logs & more.</p>
      </div>

      <div id="sql_progress" style="display:none;">
        <div class="progress-track"><div class="progress-fill" id="sql_progress_bar" style="width:0%;"></div></div>
        <div class="status-msg loading" id="sql_progress_text" data-i18n-install="sql.running">Running SQL queries...</div>
      </div>

      <button class="btn btn-primary" id="run_sql_btn" onclick="runSql()">
        <i class="fas fa-play"></i> <span data-i18n-install="sql.run">Run SQL Now</span>
      </button>
      <div class="status-msg" id="sql_status"></div>

      <div class="btn-row">
        <button class="btn btn-outline" onclick="goToStep(2)" style="flex:1;">
          <i class="fas fa-arrow-left"></i> <span data-i18n-install="common.back">Back</span>
        </button>
        <button class="btn btn-primary" id="step3_next" disabled onclick="goToStep(4)" style="flex:1;">
          <span data-i18n-install="common.next">Next Step</span> <i class="fas fa-arrow-right"></i>
        </button>
      </div>
    </div>

    <!-- Step 4: Create Admin Account -->
    <div class="step" id="step-4">
      <h2><i class="fas fa-shield-alt"></i> <span data-i18n-install="admin.title">Admin Account</span></h2>
      <div class="subtitle" data-i18n-install="admin.subtitle">Create the administrator account</div>

      <div class="form-group">
        <label data-i18n-install="admin.username">Admin Username</label>
        <div class="input-icon-wrap">
          <input type="text" id="admin_user" value="admin" data-i18n-install-placeholder="placeholder.admin_user" placeholder="e.g. admin">
          <i class="fas fa-user-shield"></i>
        </div>
      </div>
      <div class="form-group">
        <label data-i18n-install="admin.email">Admin Email</label>
        <div class="input-icon-wrap">
          <input type="email" id="admin_email" data-i18n-install-placeholder="placeholder.admin_email" placeholder="admin@example.com">
          <i class="fas fa-envelope"></i>
        </div>
      </div>
      <div class="form-group">
        <label data-i18n-install="admin.password">Admin Password</label>
        <div class="input-icon-wrap">
          <input type="password" id="admin_pass" data-i18n-install-placeholder="placeholder.admin_pass" placeholder="Enter a strong password" autocomplete="new-password">
          <i class="fas fa-lock"></i>
        </div>
      </div>
      <div class="form-group">
        <label data-i18n-install="admin.confirm_password">Confirm Password</label>
        <div class="input-icon-wrap">
          <input type="password" id="admin_pass_confirm" data-i18n-install-placeholder="placeholder.admin_pass_confirm" placeholder="Repeat the password" autocomplete="new-password">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>

      <button class="btn btn-primary" id="create_admin_btn" onclick="createAdmin()">
        <i class="fas fa-user-plus"></i> <span data-i18n-install="admin.create">Create Admin</span>
      </button>
      <div class="status-msg" id="admin_status"></div>

      <div class="btn-row">
        <button class="btn btn-outline" onclick="goToStep(3)" style="flex:1;">
          <i class="fas fa-arrow-left"></i> <span data-i18n-install="common.back">Back</span>
        </button>
        <button class="btn btn-primary" id="step4_next" disabled onclick="goToStep(5)" style="flex:1;">
          <span data-i18n-install="common.next">Next Step</span> <i class="fas fa-arrow-right"></i>
        </button>
      </div>
    </div>

    <!-- Step 5: Site Info -->
    <div class="step" id="step-5">
      <h2><i class="fas fa-info-circle"></i> <span data-i18n-install="site.title">Site Information</span></h2>
      <div class="subtitle" data-i18n-install="site.subtitle">Tell us about your website</div>

      <div class="form-group">
        <label data-i18n-install="site.name">Site Name</label>
        <div class="input-icon-wrap">
          <input type="text" id="site_name" value="Mou Default" data-i18n-install-placeholder="placeholder.site_name" placeholder="Your site name">
          <i class="fas fa-globe"></i>
        </div>
      </div>
      <div class="form-group">
        <label data-i18n-install="site.logo">Site Logo</label>
        <div class="file-upload" id="logo_upload_area">
          <input type="file" id="logo_input" accept="image/png,image/jpeg,image/gif,image/webp">
          <img id="logo_preview" class="logo-preview" alt="">
          <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
          <div class="upload-info">
            <div class="main-text" data-i18n-install="site.logo_text">Click to upload logo</div>
            <div class="sub-text" data-i18n-install="site.logo_hint">Recommended: 192x192 PNG</div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label data-i18n-install="site.short_desc">Short Description</label>
        <div class="input-icon-wrap">
          <input type="text" id="short_desc" value="" data-i18n-install-placeholder="placeholder.short_desc" placeholder="e.g. Integrated Management System">
          <i class="fas fa-quote-right"></i>
        </div>
      </div>
      <div class="form-group">
        <label data-i18n-install="site.full_desc">Full Description</label>
        <div class="input-icon-wrap">
          <textarea id="full_desc" data-i18n-install-placeholder="placeholder.full_desc" placeholder="Describe your website in detail..."></textarea>
        </div>
      </div>

      <button class="btn btn-primary" id="install_btn" onclick="runInstallation()">
        <i class="fas fa-rocket"></i> <span data-i18n-install="site.install">Install Now</span>
      </button>
      <div class="status-msg" id="install_status"></div>

      <button class="btn btn-outline" onclick="goToStep(4)">
        <i class="fas fa-arrow-left"></i> <span data-i18n-install="common.back">Back</span>
      </button>
    </div>

    <!-- Step 6: Result -->
    <div class="step" id="step-6">
      <div class="result-box" id="result_box">
        <div class="result-icon" id="result_icon"><i class="fas fa-circle-notch fa-spin" style="font-size:2.5rem;color:#ffcc00;"></i></div>
        <h3 id="result_title" data-i18n-install="setup.installing">Installing...</h3>
        <p id="result_msg"></p>
        <button class="btn btn-success" id="go_login_btn" style="display:none;" onclick="window.location.href='login.php'">
          <i class="fas fa-sign-in-alt"></i> <span data-i18n-install="setup.go_login">Go to Login</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script>
let dbVerified = false;
let sqlDone = false;
let adminCreated = false;
let stepData = {};
let savedLang = localStorage.getItem('lang') || 'ar';
let requirementsOk = false;

function saveProgress() {
  var data = {
    step: $('.step.active').attr('id').replace('step-', ''),
    dbHost: stepData.host || '',
    dbName: stepData.name || '',
    dbUser: stepData.user || '',
    dbPass: stepData.pass || '',
    dbVerified: dbVerified ? '1' : '0',
    sqlDone: sqlDone ? '1' : '0',
    adminCreated: adminCreated ? '1' : '0',
    requirementsOk: requirementsOk ? '1' : '0',
    adminUser: $('#admin_user').val(),
    adminPass: $('#admin_pass').val(),
    siteName: $('#site_name').val(),
    shortDesc: $('#short_desc').val(),
    fullDesc: $('#full_desc').val()
  };
  $.post('install.php', { action:'save_progress', data:data });
}

function restoreProgress() {
  $.post('install.php', { action:'restore_progress' }, function(res) {
    if (!res.success || !res.data) return;
    var d = res.data;
    if (d.step && d.step >= 1 && d.step <= 6) goToStep(parseInt(d.step));
    if (d.requirementsOk === '1') {
      requirementsOk = true;
      $('#step1_next').prop('disabled', false);
    }
    if (d.dbHost) {
      $('#db_host').val(d.dbHost);
      $('#db_name').val(d.dbName || '');
      $('#db_user').val(d.dbUser || '');
      $('#db_pass').val(d.dbPass || '');
      if (d.dbVerified === '1') {
        dbVerified = true;
        stepData = { host: d.dbHost, name: d.dbName, user: d.dbUser, pass: d.dbPass };
        $('#step2_next').prop('disabled', false);
      }
    }
    if (d.sqlDone === '1') {
      sqlDone = true;
      $('#step3_next').prop('disabled', false);
    }
    if (d.adminCreated === '1') {
      adminCreated = true;
      $('#step4_next').prop('disabled', false);
    }
    if (d.adminUser) $('#admin_user').val(d.adminUser);
    if (d.adminPass) $('#admin_pass').val(d.adminPass);
    if (d.siteName) $('#site_name').val(d.siteName);
    if (d.shortDesc) $('#short_desc').val(d.shortDesc);
    if (d.fullDesc) $('#full_desc').val(d.fullDesc);
  }, 'json');
}

function t(key) {
  const dict = {
    'setup.subtitle': ['Configure your database and site information','تكوين قاعدة البيانات ومعلومات الموقع'],
    'setup.database': ['Database','قاعدة البيانات'],
    'setup.setup_db': ['Setup DB','تهيئة DB'],
    'setup.admin_account': ['Admin','المدير'],
    'setup.site_info': ['Site Info','معلومات الموقع'],
    'setup.installing': ['Installing...','جاري التثبيت...'],
    'setup.go_login': ['Go to Login','تسجيل الدخول'],
    'db.title': ['Database Connection','اتصال قاعدة البيانات'],
    'db.subtitle': ['Enter your MySQL database credentials','أدخل بيانات اتصال MySQL'],
    'db.host': ['Database Host','خادم قاعدة البيانات'],
    'db.name': ['Database Name','اسم قاعدة البيانات'],
    'db.user': ['Username','اسم المستخدم'],
    'db.pass': ['Password','كلمة المرور'],
    'db.test': ['Test Connection','اختبار الاتصال'],
    'db.testing': ['Testing...','جاري الاختبار...'],
    'db.success': ['Connection successful!','تم الاتصال بنجاح!'],
    'db.error_prefix': ['Connection failed: ','فشل الاتصال: '],
    'requirements.title': ['PHP Extensions','امتدادات PHP'],
    'requirements.short_title': ['Requirements','المتطلبات'],
    'requirements.subtitle': ['Check required PHP extensions before installation','فحص امتدادات PHP المطلوبة قبل التثبيت'],
    'requirements.recheck': ['Re-check Requirements','إعادة فحص المتطلبات'],
    'requirements.checking': ['Checking requirements...','جاري فحص المتطلبات...'],
    'requirements.ok': ['All required extensions are enabled','كل الامتدادات المطلوبة مفعلة'],
    'requirements.missing': ['Enable missing extensions before continuing','فعّل الامتدادات الناقصة قبل المتابعة'],
    'sql.title': ['Setup Database Tables','إعداد جداول قاعدة البيانات'],
    'sql.subtitle': ['Run database.sql to create all tables','تشغيل database.sql لإنشاء الجداول'],
    'sql.info_title': ['Database Tables','جداول قاعدة البيانات'],
    'sql.info_desc': ['Creates tables: users, settings, devices & more.','إنشاء الجداول: المستخدمين، الإعدادات، الأجهزة والمزيد.'],
    'sql.run': ['Run SQL Now','تشغيل SQL الآن'],
    'sql.running': ['Running SQL queries...','جاري تشغيل استعلامات SQL...'],
    'sql.success': ['Database tables created successfully!','تم إنشاء جداول قاعدة البيانات بنجاح!'],
    'sql.error_prefix': ['SQL execution failed: ','فشل تنفيذ SQL: '],
    'site.title': ['Site Information','معلومات الموقع'],
    'site.subtitle': ['Tell us about your website','أدخل معلومات موقعك'],
    'site.name': ['Site Name','اسم الموقع'],
    'site.logo': ['Site Logo','شعار الموقع'],
    'site.logo_text': ['Click to upload logo','اضغط لرفع الشعار'],
    'site.logo_hint': ['Recommended: 192x192 PNG','مستحسن: 192x192 PNG'],
    'site.short_desc': ['Short Description','الوصف القصير'],
    'site.full_desc': ['Full Description','الوصف الكامل'],
    'site.install': ['Install Now','تثبيت الآن'],
    'site.installing': ['Installing... Please wait','جاري التثبيت... الرجاء الانتظار'],
    'site.success': ['Installation completed successfully!','تم التثبيت بنجاح!'],
    'site.success_msg': ['Your site is ready. Click below to login.','موقعك جاهز. اضغط أدناه لتسجيل الدخول.'],
    'common.next': ['Next Step','الخطوة التالية'],
    'common.back': ['Back','رجوع'],
    'install.error': ['Installation failed: ','فشل التثبيت: '],
    'install.wait': ['Please wait...','الرجاء الانتظار...'],
    'admin.title': ['Admin Account','حساب المدير'],
    'admin.subtitle': ['Create the administrator account','إنشاء حساب المدير'],
    'admin.username': ['Admin Username','اسم المستخدم للمدير'],
    'admin.email': ['Admin Email','البريد الإلكتروني للمدير'],
    'admin.password': ['Admin Password','كلمة مرور المدير'],
    'admin.confirm_password': ['Confirm Password','تأكيد كلمة المرور'],
    'admin.create': ['Create Admin','إنشاء المدير'],
    'admin.creating': ['Creating...','جاري الإنشاء...'],
    'admin.success': ['Admin account created successfully!','تم إنشاء حساب المدير بنجاح!'],
    'admin.error_required': ['Username and password are required','اسم المستخدم وكلمة المرور مطلوبان'],
    'admin.error_password_short': ['Password must be at least 4 characters','كلمة المرور يجب أن تكون 4 أحرف على الأقل'],
    'admin.error_password_mismatch': ['Passwords do not match','كلمات المرور غير متطابقة'],
    'admin.error_prefix': ['Admin creation failed: ','فشل إنشاء المدير: '],
    'placeholder.db_host': ['e.g. 127.0.0.1 or localhost','مثال: 127.0.0.1 أو localhost'],
    'placeholder.db_name': ['Database name','اسم قاعدة البيانات'],
    'placeholder.db_user': ['MySQL username','اسم مستخدم MySQL'],
    'placeholder.db_pass': ['MySQL password','كلمة مرور MySQL'],
    'placeholder.admin_user': ['e.g. admin','مثال: admin'],
    'placeholder.admin_email': ['admin@example.com','admin@example.com'],
    'placeholder.admin_pass': ['Enter a strong password','أدخل كلمة مرور قوية'],
    'placeholder.admin_pass_confirm': ['Repeat the password','أعد إدخال كلمة المرور'],
    'placeholder.site_name': ['Your site name','اسم موقعك'],
    'placeholder.short_desc': ['e.g. Integrated Management System','مثال: نظام الإدارة المتكامل'],
    'placeholder.full_desc': ['Describe your website in detail...','اكتب وصف موقعك بالتفصيل...'],
  };
  return (dict[key]||['', ''])[localStorage.getItem('lang') === 'en' ? 0 : 1] || key;
}

function translate() {
  document.querySelectorAll('[data-i18n-install]').forEach(function(el) {
    el.textContent = t(el.getAttribute('data-i18n-install'));
  });
  document.querySelectorAll('[data-i18n-install-placeholder]').forEach(function(el) {
    el.placeholder = t(el.getAttribute('data-i18n-install-placeholder'));
  });
}

$(document).ready(function() {
  translate();
  checkRequirements();
  restoreProgress();
  $('#logo_input').on('change', function(e) {
    var file = e.target.files[0];
    if (file) {
      var reader = new FileReader();
      reader.onload = function(ev) {
        $('#logo_preview').attr('src', ev.target.result).show();
        $('.file-upload .upload-icon, .file-upload .upload-info').hide();
      };
      reader.readAsDataURL(file);
    }
  });
  $('#admin_user, #admin_pass, #admin_pass_confirm').on('input', function() {
    adminCreated = false;
    $('#step4_next').prop('disabled', true);
    $('#admin_status').removeClass('success error loading').text('');
    saveProgress();
  });
  $('#site_name').on('input', saveProgress);
  $('#short_desc').on('input', saveProgress);
  $('#full_desc').on('input', saveProgress);
});

function checkRequirements() {
  $('#step1_next').prop('disabled', true);
  $('#recheck_requirements_btn').prop('disabled', true).html('<span class="loading-spinner"></span> ' + t('requirements.checking'));
  $('#requirements_status').removeClass('success error').addClass('loading').text(t('requirements.checking')).show();
  $.post('install.php', { action:'check_requirements' }, function(res) {
    var html = '';
    (res.items || []).forEach(function(item) {
      html += '<div class="requirement-item ' + (item.loaded ? 'ok' : 'missing') + '">'
        + '<span>' + item.label + '</span>'
        + '<i class="fas ' + (item.loaded ? 'fa-check-circle' : 'fa-times-circle') + '"></i>'
        + '</div>';
    });
    $('#requirements_list').html(html);
    requirementsOk = !!res.ok;
    if (requirementsOk) {
      $('#requirements_status').removeClass('loading error').addClass('success').text(t('requirements.ok'));
      $('#step1_next').prop('disabled', false);
      saveProgress();
    } else {
      $('#requirements_status').removeClass('loading success').addClass('error').text(t('requirements.missing'));
      $('#step1_next').prop('disabled', true);
      if (!$('.step.active').is('#step-1')) goToStep(1);
    }
    $('#recheck_requirements_btn').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> ' + t('requirements.recheck'));
  }, 'json').fail(function() {
    requirementsOk = false;
    $('#requirements_status').removeClass('loading success').addClass('error').text('Server error');
    $('#step1_next').prop('disabled', true);
    if (!$('.step.active').is('#step-1')) goToStep(1);
    $('#recheck_requirements_btn').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> ' + t('requirements.recheck'));
  });
}

function testConnection() {
  if (!requirementsOk) {
    $('#conn_status').removeClass('success loading').addClass('error').text(t('requirements.missing'));
    return;
  }
  var host = $('#db_host').val().trim();
  var name = $('#db_name').val().trim();
  var user = $('#db_user').val().trim();
  var pass = $('#db_pass').val();
  if (!host || !name || !user) {
    $('#conn_status').removeClass('success loading').addClass('error').text(t('db.error_prefix') + 'Fill all fields');
    return;
  }
  var btn = $('#test_conn_btn');
  btn.prop('disabled', true).html('<span class="loading-spinner"></span> ' + t('db.testing'));
  $('#conn_status').removeClass('success error').addClass('loading').text(t('install.wait'));
  $.post('install.php', { action:'test_connection', host:host, name:name, user:user, pass:pass }, function(res) {
    if (res.success) {
      $('#conn_status').removeClass('error loading').addClass('success').text(t('db.success'));
      dbVerified = true;
      stepData = { host:host, name:name, user:user, pass:pass };
      $('#step2_next').prop('disabled', false);
      saveProgress();
    } else {
      $('#conn_status').removeClass('success loading').addClass('error').text(t('db.error_prefix') + res.message);
      dbVerified = false; $('#step2_next').prop('disabled', true);
    }
  }).fail(function() {
    $('#conn_status').removeClass('success loading').addClass('error').text(t('db.error_prefix') + 'Server error');
  }).always(function() {
    btn.prop('disabled', false).html('<i class="fas fa-plug"></i> ' + t('db.test'));
  });
}

function runSql() {
  sqlDone = false;
  $('#sql_progress').show();
  $('#sql_progress_bar').css('width','30%');
  $('#run_sql_btn').prop('disabled', true);
  $('#sql_status').removeClass('success error').addClass('loading').text(t('install.wait'));
  $('#sql_progress_bar').css('width','60%');
  $.post('install.php', { action:'run_sql', host:stepData.host, name:stepData.name, user:stepData.user, pass:stepData.pass }, function(res) {
    if (res.success) {
      $('#sql_progress_bar').css('width','100%');
      $('#sql_status').removeClass('error loading').addClass('success').text(t('sql.success'));
      sqlDone = true;
      $('#step3_next').prop('disabled', false);
      saveProgress();
    } else {
      $('#sql_progress_bar').css('width','100%').css('background','var(--danger)');
      $('#sql_status').removeClass('success loading').addClass('error').text(t('sql.error_prefix') + res.message);
      sqlDone = false; $('#step3_next').prop('disabled', true);
    }
  }).fail(function() {
    $('#sql_progress_bar').css('width','100%').css('background','var(--danger)');
    $('#sql_status').removeClass('success loading').addClass('error').text(t('sql.error_prefix') + 'Server error');
  }).always(function() {
    $('#run_sql_btn').prop('disabled', false);
    setTimeout(function() { $('#run_sql_btn').html('<i class="fas fa-redo"></i> ' + (sqlDone ? 'Re-run SQL' : 'Try Again')); }, 500);
  });
}

function createAdmin() {
  var adminUser = $('#admin_user').val().trim();
  var adminEmail = $('#admin_email').val().trim();
  var adminPass = $('#admin_pass').val();
  var adminConfirm = $('#admin_pass_confirm').val();
  if (!adminUser || !adminPass) {
    $('#admin_status').removeClass('success').addClass('error').text(t('admin.error_required'));
    return;
  }
  if (adminEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(adminEmail)) {
    $('#admin_status').removeClass('success').addClass('error').text('Invalid email format');
    return;
  }
  if (adminPass.length < 4) {
    $('#admin_status').removeClass('success').addClass('error').text(t('admin.error_password_short'));
    return;
  }
  if (adminPass !== adminConfirm) {
    $('#admin_status').removeClass('success').addClass('error').text(t('admin.error_password_mismatch'));
    return;
  }
  var btn = $('#create_admin_btn');
  btn.prop('disabled', true).html('<span class="loading-spinner"></span> ' + t('admin.creating'));
  $('#admin_status').removeClass('success error').addClass('loading').text(t('install.wait'));
  $.post('install.php', {
    action:'create_admin',
    host:stepData.host, name:stepData.name, user:stepData.user, pass:stepData.pass,
    admin_user:adminUser, admin_email:adminEmail, admin_pass:adminPass
  }, function(res) {
    if (res.success) {
      $('#admin_status').removeClass('error loading').addClass('success').text(t('admin.success'));
      adminCreated = true;
      $('#step4_next').prop('disabled', false);
      saveProgress();
    } else {
      $('#admin_status').removeClass('success loading').addClass('error').text(res.message);
      adminCreated = false;
      $('#step4_next').prop('disabled', true);
    }
  }).fail(function() {
    $('#admin_status').removeClass('success loading').addClass('error').text(t('admin.error_prefix') + 'Server error');
  }).always(function() {
    btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> ' + t('admin.create'));
  });
}

function goToStep(step) {
  $('.step').removeClass('active');
  $('#step-'+step).addClass('active');
  $('.step-dot').removeClass('active');
  $('.step-dot').removeClass('done');
  $('#step-dot-'+step).addClass('active');
  if (step === 2) $('#step-dot-1').addClass('done');
  if (step === 3) $('#step-dot-1, #step-dot-2').addClass('done');
  if (step === 4) $('#step-dot-1, #step-dot-2, #step-dot-3').addClass('done');
  if (step === 5) $('#step-dot-1, #step-dot-2, #step-dot-3, #step-dot-4').addClass('done');
  if (step === 6) $('#step-dot-1, #step-dot-2, #step-dot-3, #step-dot-4, #step-dot-5').addClass('done');
  saveProgress();
}

function runInstallation() {
  var siteName = $('#site_name').val().trim() || 'Mou Default';
  var shortDesc = $('#short_desc').val().trim();
  var fullDesc = $('#full_desc').val().trim();
  if (!siteName) {
    $('#install_status').removeClass('success').addClass('error').text('Site name is required');
    return;
  }
  var fd = new FormData();
  fd.append('action','run_installation');
  fd.append('host', stepData.host);
  fd.append('name', stepData.name);
  fd.append('user', stepData.user);
  fd.append('pass', stepData.pass);
  fd.append('site_name', siteName);
  fd.append('short_desc', shortDesc);
  fd.append('full_desc', fullDesc);

  var logoFile = $('#logo_input')[0].files[0];
  if (logoFile) fd.append('logo', logoFile);

  goToStep(6);
  $('#result_icon').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2.5rem;color:#ffcc00;"></i>');
  $('#result_title').text(t('setup.installing'));
  $('#result_msg').text(t('install.wait'));
  $('#go_login_btn').hide();

  $.ajax({
    url:'install.php', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success: function(res) {
      if (res.success) {
        $('#result_icon').html('<i class="fas fa-check-circle" style="font-size:3.2rem;color:var(--success);"></i>');
        $('#result_title').text(t('site.success'));
        $('#result_msg').text(t('site.success_msg'));
        $('#go_login_btn').show();
      } else {
        $('#result_icon').html('<i class="fas fa-times-circle" style="font-size:3.2rem;color:var(--danger);"></i>');
        $('#result_title').text('Error');
        $('#result_msg').text(t('install.error') + res.message);
      }
    },
    error: function() {
      $('#result_icon').html('<i class="fas fa-times-circle" style="font-size:3.2rem;color:var(--danger);"></i>');
      $('#result_title').text('Error');
      $('#result_msg').text(t('install.error') + 'Server error');
    }
  });
}
</script>
</body>
</html>
