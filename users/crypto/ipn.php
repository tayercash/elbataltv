<?php
include_once("../config_db.php");

define("now_payments_log", "now_payments.log");
$log_msg = "";

// CoinPayments IPN Secret
$ipn_secret = 'mouscripts'; // Set this in your CoinPayments account

// Read POST data
$raw_post = file_get_contents('php://input');

$now_utc = date("Y-m-d H:i:s", time());
$now_utc_datetime = new DateTime($now_utc);
$now_utc = $now_utc_datetime->format('U');
// $now_utc = 1724918500;
$now_utc_date_time = date("Y-m-d H:i:s", $now_utc);

$post = $_POST;
// Verify HMAC signature
$hmac = isset($_SERVER['HTTP_HMAC']) ? $_SERVER['HTTP_HMAC'] : '';

if (empty($hmac)) {
    die('No HMAC signature sent.');
}

// Load your private key
$private_key = 'e508f4515D035fdEb4C1c02B2bE4acb05011D7660c8Fc00353fBfd46aFd48BEa'; // Same as in create_payment.php

// Recreate HMAC to verify
$signature = hash_hmac('sha512', $raw_post, $ipn_secret);

error_log(date('[Y-m-d H:i e] ') . "HMAC Recived => \n" . $raw_post . "\n" . $signature . PHP_EOL, 3, now_payments_log);

if (!hash_equals($signature, $hmac)) {
    error_log(date('[Y-m-d H:i e] ') . 'HMAC signature does not match.' . PHP_EOL, 3, now_payments_log);

    die('HMAC signature does not match.');
}

// Check if the IPN is for a completed payment
if ($post['ipn_mode'] != 'hmac') {
    error_log(date('[Y-m-d H:i e] ') . 'IPN Mode is not HMAC.' . PHP_EOL, 3, now_payments_log);
    die('IPN Mode is not HMAC.');
}
if ($post['status'] >= 100 || $post['status'] == 2) {


    // Extract necessary data
    $txn_id = trim($post['txn_id'], "'"); // CoinPayments transaction ID
// $item_name = $post['item_name'];
    $invoice = $post['invoice']; // Your order ID
    $amount = trim($post['amount2'], "'"); // Amount received
// $currency1 = $post['currency1']; // Original currency
// $currency2 = $post['currency2']; // CoinPayments currency
    $status = trim($post['status'], "'");


    error_log(date('[Y-m-d H:i e] ') . "Done Payment complete. \n" . json_encode($post) . PHP_EOL, 3, now_payments_log);

    // TODO: Update your database to mark the order as paid
// Example:
    // Update the order with $order_id as paid.

    $result = $conn->query("SELECT * FROM $Coin_Payments_table WHERE txn_id = '$txn_id'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $transaction_db_id = $row["id"];
        $user_id = $row["from_user"];
        $item_number = $row["item_number"];

        $payment_status = $row['payment_status'];
        $successfull_url_status = $row['successfull_url_status'];
        if ($payment_status == "1" && $successfull_url_status == "1") {
        } else {

            $result_update_transaction = mysqli_query($conn, "UPDATE $Coin_Payments_table SET payment_status='1',txn_id='$txn_id',amount='$amount', done_at='$now_utc_date_time' WHERE id='$transaction_db_id'");
            if ($result_update_transaction === TRUE) {

                $log_msg .= "$user_id, $txn_id";

            }
        }




        $product = $conn->query("SELECT * From `$products_table` WHERE id=$item_number");
        if ($product->num_rows > 0) {
            $row = $product->fetch_assoc();
            $item_image = $row["image"];
            $successfull_url = $row["successfull_url"];
            $redirect_url = $row["redirect_url"];
            $res_url = $successfull_url . '&meth=coinpayments&user_id=' . $user_id . '&txn_id=' . $txn_id;
            $api_res = file_get_contents($res_url);

            $log_msg .= " , " . $api_res;
        } else {
            $successfull_url = false;
            $redirect_url = false;
        }
        error_log(date('[Y-m-d H:i e] ') . $log_msg . PHP_EOL, 3, now_payments_log);


    } else {

    }


    // Optionally, send a confirmation email to the customer

    // Respond with HTTP 200
    http_response_code(200);



} else {
    // Payment not complete or pending
    error_log(date('[Y-m-d H:i e] ') . 'Payment not complete.' . PHP_EOL, 3, now_payments_log);

    die('Payment not complete.');
}
