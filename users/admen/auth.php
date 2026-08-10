<?php
session_start();
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