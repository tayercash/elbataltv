<?php
$lockFile = __DIR__ . '/installed.lock';
if (!file_exists($lockFile)) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $isApi = isset($_GET['action']);
    if ($isAjax || $isApi) {
        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not installed. Please run setup first.']);
        exit;
    }
    header('Location: install.php');
    exit;
}
$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
} else {
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', '');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

$projectDir = str_replace('\\', '/', __DIR__);
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
if ($docRoot && strpos($projectDir, $docRoot) === 0) {
    $basePath = substr($projectDir, strlen($docRoot));
} else {
    $basePath = '';
}
define('BASE_PATH', $basePath);

function requireDB() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $isApi = isset($_GET['action']);
        if ($isAjax || $isApi) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Database connection failed']);
            exit;
        }
        $ref = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        header('Location: db-error.php?ref=' . urlencode($ref) . '&msg=' . urlencode($e->getMessage()));
        exit;
    }
}
