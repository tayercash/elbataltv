<?php

if (session_status() === PHP_SESSION_NONE) {
    if (session_name() !== 'ELBATAL_ADMIN') {
        session_name('ELBATAL_ADMIN');
    }
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

// مهلة انتهاء الجلسة: 30 دقيقة عدم نشاط = خروج تلقائي
$session_timeout = 30 * 60;
if (!empty($_SESSION["elbatal_admin_last_activity"]) && (time() - $_SESSION["elbatal_admin_last_activity"]) > $session_timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    die();
}
$_SESSION["elbatal_admin_last_activity"] = time();

if (!empty($_SESSION["can_join"])) {
    if ($_SESSION["can_join"] !== true) {
        header("Location: login.php");
        die();
    }
} else {
    header("Location: login.php");
    die();
}

?>