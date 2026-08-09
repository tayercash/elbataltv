<?php
$seed = isset($_GET['seed']) ? $_GET['seed'] : 'default';

header('Content-Type: image/svg+xml');
header('Cache-Control: no-cache, must-revalidate');

require_once __DIR__ . '/assets/lib/Multiavatar.php';
$avatar = new Multiavatar();
echo $avatar($seed, true, null);
