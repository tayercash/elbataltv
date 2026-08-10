<?php
// mysqli_report(MYSQLI_REPORT_STRICT);
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";
$redirect_link = $this_file_link;
require_once(dirname(__FILE__) . "/../../dashboard/auth.php");
require_once(dirname(__FILE__) . "/../../paypal/config.php");

$user_hints = 0;

$conn->query("CREATE TABLE IF NOT EXISTS `mou_thimbles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `acc_id` varchar(100) NOT NULL,
    `num_of_hints` text NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
$conn->query("CREATE TABLE IF NOT EXISTS `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `price` text NOT NULL,
    `image` text NOT NULL,
    `status` boolean NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

$thimbles_user_data = $conn->query("SELECT * From mou_thimbles WHERE acc_id=$user_id");
if ($thimbles_user_data->num_rows > 0) {
    $row = $thimbles_user_data->fetch_assoc();
    $user_hints = $row["num_of_hints"];
} else {
    $user_hints = 5;
    $insert = $conn->query("INSERT INTO mou_thimbles(acc_id,num_of_hints) VALUES ($user_id,\"$user_hints\")");
}

?>
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MouScripts - Thimbles</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="mou_background"></div>
    <div class="full_container">

        <div class="header">
            <div class="U_name mou_box_shadow">
                <div class="u_img_container mou_box_shadow">
                    <img src="../../assests/dist/img/user.png" alt="">
                </div>
                <span class="text" id="user_name"><?php echo $user_name; ?></span>
            </div>
            <div class="U_hint mou_box_shadow">
                <div class="u_img_container mou_box_shadow">
                    <img src="../../assests/dist/img/coin.png" alt="">
                </div>
                <span class="text"><span class="text_content"><span id="user_hints"><?php echo $user_hints; ?></span> Hints</span></span>
            </div>
        </div>

        <div class="mou_box_shadow mou_container">
            <div class="mou_blocks">
                <?php
                $results = $conn->query("SELECT * FROM products WHERE status = 1 AND id in (1,2,3,4)");
                if ($results) {
                    while ($row = $results->fetch_assoc()) {
                ?>
                        <div class="mou_block mou_box_shadow">
                            <span class="square">
                                <div class="block_img">
                                    <img src="<?php echo $domainlink . $row['image']; ?>" alt="">
                                </div>
                            </span>

                            <div class="body">

                                <h5><?php echo $row['name'] ?></h5>
                                <h6>Price: <?php echo '$' . $row['price'] . ' ' . PAYPAL_CURRENCY; ?></h6>
                                <form target="_blank" action="<?php echo PAYPAL_URL; ?>" method="POST">
                                    <input type="hidden" name="business" value="<?php echo PAYPAL_ID; ?>">
                                    <input type="hidden" name="cmd" value="_xclick">
                                    <input type="hidden" name="item_name" value="<?php echo $row['name']; ?>">
                                    <input type="hidden" name="item_number" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="amount" value="<?php echo $row['price']; ?>">
                                    <input type="hidden" name="currency_code" value="<?php echo PAYPAL_CURRENCY; ?>">
                                    <input type="hidden" name="custom" value="<?php echo urlencode("user_id=$user_id"); ?>">
                                    <input type="hidden" name="return" value="<?php echo PAYPAL_RETURN_URL; ?>">
                                    <input type="hidden" name="cancel_return" value="<?php echo PAYPAL_CANCEL_URL; ?>">
                                    <input type="image" name="submit" style="border:0;" src="<?php echo $domainlink . "/assests/dist/img/btn_buynow_LG.gif"; ?>">
                                </form>
                            </div>
                        </div>
                <?php
                    }
                }
                ?>


            </div>

        </div>
    </div>
    <script src="/assests/dist/js/jquery.min.js"></script>
    <script></script>
</body>

</html>