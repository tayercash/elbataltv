<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$get_magic_quotes_exists = false;
include_once("../config_db.php");
include_once("config.php");

define("IPN_LOG_FILE", "ipn.log");
error_log(date('[Y-m-d H:i e] ') . "Start New Payment Check" . PHP_EOL, 3, IPN_LOG_FILE);


$raw_post_data = file_get_contents('php://input');

$log_msg = "";

$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}
$req = 'cmd=_notify-validate';
if (function_exists('get_magic_quotes_gpc')) {
    $get_magic_quotes_exists = true;
}
foreach ($myPost as $key => $value) {
    if ($get_magic_quotes_exists == true) {
        $value = urlencode(stripslashes($value));
    } else {
        $value = urlencode($value);
    }
    $req .= "&$key=$value";
}


$paypalURL = PAYPAL_URL;
$ch = curl_init($paypalURL);
if ($ch == FALSE) {
    return FALSE;
}

curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSLVERSION, 6);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: Mouscripts'));
$res = curl_exec($ch);
curl_close($ch);
$tokens = explode("\r\n\r\n", trim($res));
$res = trim(end($tokens));
if (strcmp($res, "VERIFIED") == 0 || strcasecmp($res, "VERIFIED") == 0) {


    error_log(date('[Y-m-d H:i e] ') . "New Payment" . PHP_EOL, 3, IPN_LOG_FILE);


    $payment_custom = isset($myPost['custom']) ? urldecode($myPost['custom']) : "";
    echo $payment_custom;
    $raw_custom_data = explode('&', urldecode($payment_custom));
    $custom_data = new stdClass();
    $custom_msgs = "";
    foreach ($raw_custom_data as $keyval) {
        $keyval = explode('=', $keyval);
        if (count($keyval) == 2) {
            $custom_data->{$keyval[0]} = $keyval[1];
        }
    }
    if (isset($custom_data->{"a"})) {
        $user_id = $custom_data->{"user_id"};
        $page_action = $custom_data->{"a"};

        echo $user_id;

        $item_number = $myPost['item_number'];
        $txn_id = $myPost['txn_id'];
        $payment_gross = $myPost['mc_gross'];
        $currency_code = $myPost['mc_currency'];
        $payment_status = $myPost['payment_status'];


        //Check if transation data exists with the same TXN ID
        $prevPayment = $conn->query("SELECT id FROM $paypal_payments_table WHERE txn_id= '" . $txn_id . "'");
        if ($prevPayment->num_rows == 0) {

            $insert = $conn->query("INSERT INTO $paypal_payments_table (item_number,txn_id,from_user,payment_gross,currency_code,payment_status) VALUES('$item_number','$txn_id','$user_id','$payment_gross','$currency_code','$payment_status')");
        }

        $log_msg .= "$user_id, $txn_id";


        $product = $conn->query("SELECT * From `$products_table` WHERE id=$item_number");
        if ($product->num_rows > 0) {
            $row = $product->fetch_assoc();
            $item_image = $row["image"];
            $successfull_url = $row["successfull_url"];
            $redirect_url = $row["redirect_url"];
            $res_url = $successfull_url . '&paypal=1&user_id=' . $user_id . '&txn_id=' . $txn_id;
            $api_res = file_get_contents($res_url);

            $log_msg .= " , " . $api_res;
        } else {
            $successfull_url = false;
            $redirect_url = false;
        }
        error_log(date('[Y-m-d H:i e] ') . $log_msg . PHP_EOL, 3, IPN_LOG_FILE);

    }



    // $telegram_msg_link = "https://api.telegram.org/bot$telegram_token/sendMessage?chat_id=$chat_id&parse_mode=HTML&text=$telegram_msg";
    // $ret = file_get_contents($telegram_msg_link);


    //     $telegram_msg = "عملية دفع جديدة بقيمة $payment_gross $currency_code
    // <b>user_id</b> : <strong>$user_id</strong> 
    // txn_id  : <strong>$txn_id</strong>
    // result  : $api_res";

    // $telegram_token = "6507187286:AAFRvpLXDl-fn7eT1PPEqOJZLLg81b9bkIE";
    // $chat_id = "2140799570";

    // $post_fields = array(
    //     'chat_id'   => $chat_id,
    //     'photo'     => new CURLFile(realpath($item_image)),
    //     "caption" => $telegram_msg,
    //     "parse_mode" => "HTML"
    // );
    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //     "Content-Type:multipart/form-data"
    // ));
    // curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot$telegram_token/sendPhoto?chat_id=$chat_id");
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    // $output = curl_exec($ch);


}
