<?php
//Include Configuration File
include_once 'config.php';

//If Transaction Data is Available in the URL
if (!empty($_GET['item_number']) && !empty($_GET['tx']) && !empty($_GET['amt']) && !empty($_GET['cc']) && !empty($_GET['st'])) {
    //Get Transaction Information from URL
    // $from_user = $_GET['item_name'];

    $item_number = $_GET['item_number'];
    $txn_id = $_GET['tx'];
    $payment_gross = $_GET['amt'];
    $currency_code = $_GET['cc'];
    $payment_status = $_GET['st'];
    //Get Product infomation from the database
    $productResult = $conn->query("SELECT * FROM `$products_table` WHERE id = " . $item_number);
    $productRow = $productResult->fetch_assoc();

    $payment_custom = isset($_GET['cm']) ? $_GET['cm'] : "";
    $raw_custom_data = explode('&', $payment_custom);

    $custom_data = new stdClass();
    foreach ($raw_custom_data as $keyval) {
        $keyval = explode('=', $keyval);
        if (count($keyval) == 2) {
            $custom_data->{$keyval[0]} = $keyval[1];
        }
    }
    $page_action = $custom_data->{"a"};
    $user_id = $custom_data->{"user_id"};

    // Check if transaction data exists with the same TXN ID
    $prevPaymentResult = $conn->query("SELECT * FROM `$paypal_payments_table` WHERE txn_id = '" . $txn_id . "'");
    if ($prevPaymentResult->num_rows > 0) {
        $paymentRow = $prevPaymentResult->fetch_assoc();
        $payment_id = $paymentRow['id'];
        $payment_gross = $paymentRow['payment_gross'];
        $payment_status = $paymentRow['payment_status'];
    } else {
        //Insert transaction data into the database
        $insert = $conn->query("INSERT INTO `$paypal_payments_table` (item_number,txn_id,from_user,payment_gross,currency_code,payment_status) VALUES ('$item_number','$txn_id','$user_id' , '$payment_gross','$currency_code','$payment_status')");
        $payment_id = $conn->insert_id;
    }

    $product = $conn->query("SELECT * From `$products_table` WHERE id=$item_number");
    if ($product->num_rows > 0) {
        $row = $product->fetch_assoc();
        $successfull_url = $row["successfull_url"];
        $redirect_url = $row["redirect_url"];
        $res_url = $successfull_url . '&paypal=1&user_id=' . $user_id . '&txn_id=' . $txn_id;
        $api_res = file_get_contents($res_url);
    } else {
        $successfull_url = false;
        $redirect_url = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Payment Status</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <div class="main">
            <div class="status">
                <?php if (!empty($payment_id)) { ?>
                    <h1 class="success">Your Payment has been successful</h1>

                    <h4>Payment Information</h4>
                    <p><b>Reference Number:</b> <?php echo $payment_id; ?></p>
                    <p><b>Transaction ID:</b> <?php echo $txn_id; ?></p>
                    <p><b>Paid Amount:</b> <?php echo $payment_gross; ?></p>
                    <p><b>Payment Status:</b> <?php echo $payment_status; ?></p>

                    <h4>Product Information</h4>
                    <p><b>Name:</b> <?php echo $productRow['name']; ?></p>
                    <p><b>Price:</b> <?php echo $productRow['price']; ?></p>
                <?php } else { ?>
                    <h1 class="error">Your Payment has failed</h1>
                <?php } ?>
            </div>
            <a data-href_redrict="<?php echo $redirect_url; ?>" class="btn-link">Back to Products</a>
            <script src="/assests/dist/js/jquery.min.js"></script>
            <script>
                $("[data-href_redrict]").click(function() {
                    href_redrict = $(this).attr("data-href_redrict");
                    if (href_redrict !== false) {
                        window.opener.location.reload();
                        window.close();
                    }
                })
            </script>
        </div>
    </div>
</body>

</html>