<?php
//Include Configuration File
include_once("../../config_db.php");
//If Transaction Data is Available in the URL
// header("Content-Type: Application/json");

$now_utc = date("Y-m-d H:i:s", time());
$now_utc_datetime = new DateTime($now_utc);
$now_utc = $now_utc_datetime->format('U');
// $now_utc = 1724918500;
$now_utc_date_time = date("Y-m-d H:i:s", $now_utc);

$invoice = $_GET['invoice'] ?? null;
$txn_id = $_GET['txn_id'] ?? null;

function getTransactionStatus($txn_id)
{
    $private_key = 'e508f4515D035fdEb4C1c02B2bE4acb05011D7660c8Fc00353fBfd46aFd48BEa';
    $public_key = '67390e8e994c2845f1a6b4583ac496687564127cc3e7cdc93be22dfbc3a0ef79';

    $req = [
        'version' => 1,  // Specify the API version
        'cmd' => 'get_tx_info',
        'txid' => $txn_id,
        'key' => $public_key,
        'format' => 'json',
    ];

    $post_data = http_build_query($req, '', '&');
    $hmac = hash_hmac('sha512', $post_data, $private_key);

    $ch = curl_init('https://www.coinpayments.net/api.php');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['HMAC: ' . $hmac]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

$result = getTransactionStatus($txn_id);
if ($result && isset($result['result']['status'])) {
    $status = $result['result']['status'];
    // $status = 100;
    if ($status == 100) {
        $amount = $result['result']['amountf'];


        $result = $conn->query("SELECT * FROM $Coin_Payments_table WHERE txn_id = " . esc($txn_id));
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $payment_id = $row['id'];
            $user_id = $row["from_user"];
            $item_number = $row["item_number"];

            $payment_gross = $row['payment_gross'];
            $payment_status = $row['payment_status'];
            $successfull_url_status = $row['successfull_url_status'];
            if ($payment_status == "1" && $successfull_url_status == "1") {
                $payment_success = true;


            } else {
                $result_update_transaction = mysqli_query($conn, "UPDATE $Coin_Payments_table SET payment_status=" . esc("1") . ",txn_id=" . esc($txn_id) . ",amount=" . esc($amount) . ", done_at=" . esc($now_utc_date_time) . " WHERE id=" . esc($payment_id));
                if ($result_update_transaction === TRUE) {
                    $payment_status = '1';
                    $payment_success = true;
                }
            }


            //Get Product infomation from the database
            $productResult = $conn->query("SELECT * FROM `$products_table` WHERE id = " . esc($item_number));
            $productRow = $productResult->fetch_assoc();

            $successfull_url = $productRow["successfull_url"];
            $redirect_url = $productRow["redirect_url"];
            $res_url = $successfull_url . '&meth=coinpayments&user_id=' . $user_id . '&txn_id=' . $txn_id;
            $api_res = json_decode(file_get_contents($res_url), true);
            $payment_status = $payment_status == "1" ? "نجحت العملية" : "حدث خطأ";

        }




    }
} else {
    // echo "Failed to fetch transaction details.";
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
    <link rel="stylesheet" href="buy.css?ch_killer=<?php echo time(); ?>">
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
                        <p><b>القيمة المدفوعه</b><span><?php echo $payment_gross; ?>$</span></p>
                        <p><b>الاشتراك</b><span><?php echo $productRow['name']; ?></span></p>
                        <p><b>قيمة الاشتراك</b><span><?php echo $productRow['price']; ?>$</span></p>
                        <p><b>حالة الدفع</b><span><?php echo $payment_status; ?></span></p>

                        <?php
                        foreach ($api_res["messages"] as $msg_key => $msg_val) {
                            ?>
                            <!-- <a><b><?php echo $msg_val; ?></b></a> -->
                            <?php
                        }
                        ?>

                    </div>
                <?php } else { ?>
                    <h1 class="error">فشل التحقق من عملية الدفع</h1>
                <?php } ?>
            </div>
        </div>
    </div>

</body>

</html>
