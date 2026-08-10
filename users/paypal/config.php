<?php
include_once '../../config_db.php';

/* 
PayPal Setting and Database configuration
*/
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";

//Paypal Settings and Configuration
// define('PAYPAL_ID', 'sb-475n43s31387602@business.example.com');
// define('PAYPAL_SANDBOX', TRUE); //TRUE OR FALSE

define('PAYPAL_ID', 'mouscripts@gmail.com');
define('PAYPAL_SANDBOX', FALSE); //TRUE OR FALSE

define('PAYPAL_RETURN_URL', $actual_link . '/success.php');
define('PAYPAL_CANCEL_URL', $actual_link . '/cancel.php');
define('PAYPAL_NOTIFY_URL', $actual_link . '/ipn.php');
define('PAYPAL_CURRENCY', 'USD');

//Database Configuration
define('DB_HOST', $servername);
define('DB_USERNAME', $db_username);
define('DB_PASSWORD', $db_password);
define('DB_NAME', $db_name);

//Change Not Required
define('PAYPAL_URL', (PAYPAL_SANDBOX == true) ? "https://ipnpb.sandbox.paypal.com/cgi-bin/webscr" : "https://ipnpb.paypal.com/cgi-bin/webscr");