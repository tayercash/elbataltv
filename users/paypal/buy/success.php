<?php
//Include Configuration File
include_once("../../config_db.php");
include '../config.php';
//If Transaction Data is Available in the URL
// header("Content-Type: Application/json");

// PayPal PDT Identity Token
$pdt_identity_token = 'ZVRU2vWInK_3K8rHnBWdW4OMOiNi-sFIarNGLPmdTjZzMt3E55vCJswv9TC';

// Retrieve the transaction ID from the URL
$tx = $_GET['tx'] ?? null;

if ($tx) {
    // Prepare the request for PayPal PDT
    $req = "cmd=_notify-synch&tx=$tx&at=$pdt_identity_token";

    // Send the request to PayPal for verification
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: Close']);

    $res = curl_exec($ch);
    curl_close($ch);

    if ($res && strpos($res, "SUCCESS") === 0) {
        // Parse response and display transaction details

        $lines = explode("\n", trim($res));
        $data = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line && str_contains($line, '=')) {
                list($key, $value) = explode("=", $line);
                $data[urldecode($key)] = urldecode($value);
            }
        }


        $payment_status = "SUCCESS";
        $txn_id = $data['txn_id'];
        $payment_gross = $data['payment_gross'];
        $currency_code = $data['mc_currency'];
        $item_number = $data['item_number'];



        $payment_custom = isset($data['custom']) ? $data['custom'] : "";
        $raw_custom_data = explode('&', $payment_custom);
        $custom_data = new stdClass();

        foreach ($raw_custom_data as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                $custom_data->{$keyval[0]} = $keyval[1];
            }
        }
        $custom_action = $custom_data->{"a"};
        $user_id = $custom_data->{"user_id"};

        if ($custom_action == "batalPAY") {


            //Check if transaction data exists with the same TXN ID
            $prevPaymentResult = $conn->query("SELECT * FROM `$paypal_payments_table` WHERE txn_id = " . esc($txn_id));
            if ($prevPaymentResult->num_rows > 0) {
                $paymentRow = $prevPaymentResult->fetch_assoc();
                $payment_id = $paymentRow['id'];
                $payment_gross = $paymentRow['payment_gross'];
                $payment_status = $paymentRow['payment_status'];
            } else {
                //Insert transaction data into the database
                $insert = $conn->query("INSERT INTO `$paypal_payments_table` (item_number,txn_id,from_user,payment_gross,currency_code,payment_status) VALUES(" . esc($item_number) . "," . esc($txn_id) . "," . esc($user_id) . " , " . esc($payment_gross) . "," . esc($currency_code) . "," . esc($payment_status) . ")");
                $payment_id = $conn->insert_id;
            }

            //Get Product infomation from the database
            $productResult = $conn->query("SELECT * FROM `$products_table` WHERE id = " . esc($item_number));
            $productRow = $productResult->fetch_assoc();


            $successfull_url = $productRow["successfull_url"];
            $redirect_url = $productRow["redirect_url"];
            $res_url = $successfull_url . '&paypal=1&user_id=' . $user_id . '&txn_id=' . $txn_id;
            $api_res = file_get_contents($res_url);

            $payment_success = true;

        }



    }
}


if (!empty($payment_success)) {
    $img_url = "../images/check.png";
} else {
    $img_url = "../images/wrong.png";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Payment Status</title>
    <link rel="stylesheet" href="../../files/fonts/noto-sans-arabic/font.css">
    <link rel="stylesheet" href="../../files/fonts/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="buy.css">
</head>

<body>
    <div class="mou_bg"></div>

    <div class="container">

        <div class="card">
            <div class="profile_img_container" data-avatar_or_google="avatar">
                <div class="img_container">
                    <img src="<?php echo $img_url; ?>">
                </div>
            </div>

            <div class="body">

                <?php if (!empty($payment_success)) { ?>
                    <h1 class="success">عملية دفع ناجحة</h1>
                    <div class="info_container">
                        <p><b>الرقم المرجعي</b><span><?php echo $payment_id; ?></span></p>
                        <p><b>معرف العملية</b><span><?php echo $txn_id; ?></span></p>
                        <p><b>القيمة المدفوعه</b><span><?php echo $payment_gross; ?></span></p>
                        <p><b>حالة الدفع</b><span><?php echo $payment_status; ?></span></p>
                        <p><b>الاشتراك</b><span><?php echo $productRow['name']; ?></span></p>
                        <p><b>قيمة الاشتراك</b><span><?php echo $productRow['price']; ?>$</span></p>
                    </div>
                <?php } else { ?>
                    <h1 class="error">فشل التحقق من عملية الدفع</h1>
                <?php } ?>
            </div>
        </div>
    </div>

</body>

</html>