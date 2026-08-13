<?php
mysqli_report(MYSQLI_REPORT_STRICT);
//Include Configuration file
include_once("../../config_db.php");
include '../config.php';


if (isset($_GET["u_id"]) && $_GET["u_id"] !== "" && isset($_GET["p_id"]) && $_GET["p_id"] !== "") {
    $user_id = $_GET["u_id"];
    $product_id = $_GET["p_id"];

    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl" class="dark">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Elbata Tv - Pay With PayPal</title>
        <link rel="stylesheet" href="../../files/fonts/noto-sans-arabic/font.css">
        <link rel="stylesheet" href="../../files/fonts/font-awesome/css/all.min.css">
        <link rel="stylesheet" href="buy.css">
    </head>

    <body>
        <div class="mou_bg"></div>

        <div class="container">
            <?php
            $result = $conn->query("SELECT * FROM $users_table_name WHERE id = " . esc($user_id));
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user_name = $row["username"];
                $user_email = $row["email"];
            } else {
                die("not found");
            }

            $result = $conn->query("SELECT * FROM `$products_table` WHERE id = " . esc($product_id));
            if ($result->num_rows > 0) {

                $row = $result->fetch_assoc();
                $productPrice = $row['price'];
                // Define PayPal fees
                $paypalFeeRate = 0.029; // 2.9%
                $fixedFee = 0.30; // $0.30
        
                // Calculate total amount including PayPal fees
                $totalAmount = ($productPrice + $fixedFee) / (1 - $paypalFeeRate);

                // Round the total amount to two decimal places
                $totalAmount = round($totalAmount, 2);
                // Calculate the fee amount
                $paypalFee = $totalAmount - $productPrice;
                ?>

                <div class="card">
                    <div class="profile_img_container" data-avatar_or_google="avatar">
                        <div class="img_container">
                            <img src="<?php echo $row['image']; ?>">
                        </div>
                    </div>

                    <div class="body">
                        <div class="info_container">
                            <p><b>اسم المستخدم</b><span id="user_name"><?php echo $user_name ?></span></p>
                            <p><b>الإشتراك</b><span id="product"><?php echo $row['name'] ?></span></p>
                            <p><b>السعر</b><?php echo '$' . $productPrice . ' ' . PAYPAL_CURRENCY; ?></p>
                            <p><b>الرسوم</b><?php echo '$' . $paypalFee . ' ' . PAYPAL_CURRENCY; ?></p>
                            <p><b>اجمالي المبلغ</b><?php echo '$' . $totalAmount . ' ' . PAYPAL_CURRENCY; ?></p>
                        </div>
                        <!-- Paypal payment form for displaying the buy button -->
                        <form action="<?php echo PAYPAL_URL; ?>" method="POST">
                            <!-- Identify your bussiness so that you can collect the payment -->
                            <input type="hidden" name="business" value="<?php echo PAYPAL_ID; ?>">
                            <input TYPE="hidden" name="custom" value="<?php echo "a=batalPAY&user_id=$user_id" ?>">
                            <!-- Specify a buy now button -->
                            <input type="hidden" name="cmd" value="_xclick">
                            <!-- Specify details about the item that buyers will purchase -->
                            <input type="hidden" name="item_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="item_number" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="amount" value="<?php echo $totalAmount ?>">
                            <input type="hidden" name="currency_code" value="<?php echo PAYPAL_CURRENCY; ?>">

                            <!-- Specify URLs -->
                            <input type="hidden" name="return" value="<?php echo PAYPAL_RETURN_URL; ?>">
                            <input type="hidden" name="cancel_return" value="<?php echo PAYPAL_CANCEL_URL; ?>">

                            <!-- Display the payment button -->
                            <div id="buy_now">
                                <input type="image" name="submit" style="border:0;" src="../images/buynow.png">
                            </div>
                        </form>
                    </div>
                </div>
                <?php

            } else {
                die("not found");
            }
            ?>
        </div>
    </body>

    </html>

    <?php
} else {
    exit();
}
?>
