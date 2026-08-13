<?php
date_default_timezone_set("UTC");
$servername = "localhost";
$db_username = "elbataltv";
$db_password = "Z2etHrLTbas8daAA";
$db_name = "elbataltv";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ============================================================
// حماية عامة ضد SQL Injection و XSS
// ============================================================
// أي قيمة بتدخل في استعلام SQL لازم تمر من esc()
function esc($value)
{
  global $conn;
  if (is_null($value)) return 'NULL';
  if (is_numeric($value) && !is_string($value)) return $value;
  return "'" . $conn->real_escape_string((string) $value) . "'";
}

// تطهير مدخلات النصوص من أي وسوم أو حقن HTML/JS
function clean_input($value, $max_len = 0)
{
  if (is_array($value)) return $value;
  $value = trim((string) $value);
  $value = strip_tags($value);
  if ($max_len > 0) {
    $value = mb_substr($value, 0, $max_len);
  }
  return $value;
}

$dollar_to_pound = "50";
$paypal_email_for_send = "promahmoudnabil@gmail.com";
$vodafone_num_for_send = "01067480965";
$etislat_num_for_send = "01154622602";
$enable_paypal = true;
$add_to_time_zone = 4 * 60 * 60;

$enable_active_with_code = true;
$force_active_with_code = false;
$code_active_days = 1;
$can_req_new_code_after_mins = 15;
$short_website = 3; // 1 = linkjust.com, 2 = exe.io, 3 = cuty.io, 4 = shrinkme.io

$users_table_name = 'Elbatal_Users';
$products_table = "Elbatal_Products";
$paypal_payments_table = "Elbatal_paypal_Payments";
$Coin_Payments_table = "Elbatal_Coin_Payments";
$logins_table = "Elbatal_logins";
$share_queries_table = "Elbatal_share_queries";
$share_imgs_table = "Elbatal_share_imgs";
$query_img_search_table = "Elbatal_query_img";
$phone_payments_table = "Elbatal_phone_Payments";
$codes_table = "Elbatal_code";
$user_favs_table = "Elbatal_UsersFavs";
$user_continue_watching_table = "Elbatal_continue_watching";
$downloads_table = "Elbatal_downloads";
$downloads_settings_table = "Elbatal_downloads_settings";
$auth_tokens_table = "Elbatal_auth_tokens";

// Google OAuth Web Client ID (aud) — املأه من مشروع Google Cloud لتفعيل التحقق من aud
$google_oauth_client_id = "";


// $conn->query("SET GLOBAL time_zone = '+00:00';");
if (php_sapi_name() === 'cli' && isset($_GET["install_database"]) && $_GET["install_database"] == "true") {

  $conn->query("CREATE TABLE IF NOT EXISTS `$users_table_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_icon` varchar(255) NOT NULL,
  `g_icon` varchar(255) DEFAULT NULL,
  `avatar_or_g_icon` int(2) NOT NULL DEFAULT 1,
  `google_linked` TINYINT(1) DEFAULT 0,
  `google_linked_email` varchar(255),
  `google_linked_id` varchar(255),
  `role` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 0,
  `status` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(50) DEFAULT NULL,
  `actvcode` varchar(40) NOT NULL,
  `restoken` varchar(20),
  `last_req_restoken` datetime,
  `has_pro` text NOT NULL,
  `pro_until` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
  $conn->query("CREATE TABLE IF NOT EXISTS `$products_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` text NOT NULL,
  `category` text NOT NULL,
  `image` text NOT NULL,
  `status` boolean NOT NULL,
  `successfull_url` text NOT NULL,
  `redirect_url` text NOT NULL,
  `offer` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
  $conn->query("CREATE TABLE IF NOT EXISTS `$paypal_payments_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_number` varchar(255) NOT NULL,
  `payment_status` varchar(255) NOT NULL,
  `from_user` varchar(255) NOT NULL,
  `payment_gross` double(10,2) NOT NULL,
  `currency_code` varchar(255) NOT NULL,
  `txn_id` varchar(255) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `successfull_url_status` tinyint(1) DEFAULT 0,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

  $conn->query("CREATE TABLE IF NOT EXISTS `$Coin_Payments_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) NOT NULL,
  `item_number` varchar(255) NOT NULL,
  `payment_status` varchar(255) NOT NULL,
  `from_user` varchar(255) NOT NULL,
  `payment_gross` double(10,2) NOT NULL,
  `amount` varchar(255) DEFAULT NULL,
  `currency_code` varchar(255) NOT NULL,
  `txn_id` varchar(255) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `done_at` timestamp,
  `successfull_url_status` tinyint(1) DEFAULT 0,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

  $conn->query("CREATE TABLE IF NOT EXISTS `$logins_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `dev_id` varchar(255) NOT NULL,
  `dev_ip` varchar(255) NOT NULL,
  `last_login_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

  $conn->query("CREATE TABLE IF NOT EXISTS `$auth_tokens_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dev_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT 0,
   PRIMARY KEY (`id`),
   UNIQUE KEY `token` (`token`),
   KEY `user_id` (`user_id`),
   KEY `dev_id` (`dev_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

  $conn->query("CREATE TABLE IF NOT EXISTS `$share_queries_table` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` text NOT NULL,
    `body` text NOT NULL,
    `query` text NOT NULL,
    `img_url` text DEFAULT NULL,
    `notified` tinyint(1) DEFAULT 0,
    `shared` tinyint(1) DEFAULT 0,
    `web_clicked_num` int(11) NOT NULL DEFAULT 0,
    `opend_from_notify_num` int(11) NOT NULL DEFAULT 0,
    `opend_from_web_num` int(11) NOT NULL DEFAULT 0,
    `shared_in` timestamp NOT NULL DEFAULT current_timestamp(),
    `shared_from_u_id` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

  $conn->query("CREATE TABLE IF NOT EXISTS `$phone_payments_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` text NOT NULL,
  `sender_phone` text NOT NULL,
  `value` text NOT NULL,
  `phone_cash_name` text NOT NULL,
  `product_id` varchar(100) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

  $conn->query("CREATE TABLE IF NOT EXISTS `$codes_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `used_num` int(11) DEFAULT 0,
  `status` int(1) NOT NULL DEFAULT 1,
  `link` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

  $conn->query("CREATE TABLE IF NOT EXISTS `$query_img_search_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query` varchar(255) NOT NULL,
  `img_url` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `img_url` (`img_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");


  $conn->query("CREATE TABLE IF NOT EXISTS `$user_favs_table` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `vid_id` INT NOT NULL,
    `favorited_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`,`user_id`,`vid_id`),
    FOREIGN KEY (user_id) REFERENCES Elbatal_Users(id) ON DELETE CASCADE,
    FOREIGN KEY (vid_id) REFERENCES Elbatal_share_queries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

  $conn->query("CREATE TABLE IF NOT EXISTS `$user_continue_watching_table` (
    `user_id` INT NOT NULL,
    `video_id` VARCHAR(255) NOT NULL,
    `time` DOUBLE NOT NULL,
    `duration` DOUBLE DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `video_id`),
    FOREIGN KEY (`user_id`) REFERENCES `Elbatal_Users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

  $conn->query("CREATE TABLE IF NOT EXISTS `$downloads_table` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `job_token` varchar(64) NOT NULL,
    `file_title` varchar(255) NOT NULL,
    `file_ext` varchar(20) DEFAULT NULL,
    `file_link` text NOT NULL,
    `custom_headers` text DEFAULT NULL,
    `platform` varchar(20) NOT NULL DEFAULT 'web',
    `device_id` varchar(255) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'queued',
    `total_size` bigint(20) NOT NULL DEFAULT 0,
    `downloaded_size` bigint(20) NOT NULL DEFAULT 0,
    `progress` double NOT NULL DEFAULT 0,
    `speed` double NOT NULL DEFAULT 0,
    `error_msg` text DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `started_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    `completed_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `job_token` (`job_token`),
    KEY `status` (`status`),
    KEY `device_id` (`device_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

  $conn->query("CREATE TABLE IF NOT EXISTS `$downloads_settings_table` (
    `settings_key` varchar(50) NOT NULL,
    `settings_value` text DEFAULT NULL,
    PRIMARY KEY (`settings_key`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

  $conn->query("INSERT IGNORE INTO `$downloads_settings_table` (`settings_key`, `settings_value`) VALUES
    ('max_concurrent', '3'),
    ('downloads_enabled', '1'),
    ('pause_all', '0'),
    ('max_retries', '3')");

}

function ensure_auth_tokens_table() {

    global $conn, $auth_tokens_table;

    static $checked = false;

    if ($checked === true) {
        return true;
    }

    $result = $conn->query("SHOW TABLES LIKE '$auth_tokens_table'");

    if ($result !== false && $result->num_rows > 0) {
        $checked = true;
        return true;
    }

    $conn->query("CREATE TABLE IF NOT EXISTS `$auth_tokens_table` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `token` varchar(128) NOT NULL,
    `user_id` int(11) NOT NULL,
    `dev_id` varchar(255) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `expires_at` datetime DEFAULT NULL,
    `revoked` tinyint(1) NOT NULL DEFAULT 0,
     PRIMARY KEY (`id`),
     UNIQUE KEY `token` (`token`),
     KEY `user_id` (`user_id`),
     KEY `dev_id` (`dev_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

    $checked = true;

    return true;
}
