<?php
include_once "../config_db.php";

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";

define("now_payments_log", "now_payments.log");
// CoinPayments API configuration
$public_key = '67390e8e994c2845f1a6b4583ac496687564127cc3e7cdc93be22dfbc3a0ef79'; // From CoinPayments
$private_key = 'e508f4515D035fdEb4C1c02B2bE4acb05011D7660c8Fc00353fBfd46aFd48BEa'; // From CoinPayments

$uid = $_POST["uid"];
$item_name = $_POST["item_name"];
$item_number = $_POST["item_number"];

$result = $conn->query("SELECT * FROM `$products_table` WHERE id = $item_number");
if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();
    $amount = $row['price'] - ($row['price'] * 1 / 100);


    // Payment details
    // $amount = 9.9; // Amount in USD
// CoinPayments fees
    $coinpayments_fee_percent = 0.5; // Example: 0.5% CoinPayments merchant fee

    // Calculate total amount to charge (product price + CoinPayments fees)
    $amount_to_charge = $amount * (1 + $coinpayments_fee_percent / 100);

    $currency = 'USD'; // Currency for the purchase
    $coin = 'USDT.TRC20'; // Cryptocurrency to accept
    $order_id = 'ORDER_' . uniqid(); // Unique order ID
    $item_name = $row['name']; // Description of the product

    // User and callback URLs
    $success_url = "$actual_link/success.php";
    $cancel_url = "$actual_link/cancel.php";
    $ipn_url = "$actual_link/ipn.php";



    // Prepare data for CoinPayments API
    $params = array(
        'version' => 1,
        'cmd' => 'create_transaction',
        'key' => $public_key,
        'amount' => $amount,
        'currency1' => $currency,
        'currency2' => $coin,
        'buyer_email' => 'customer@example.com', // Optional
        'item_name' => $item_name,
        'invoice' => $order_id,
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'ipn_url' => $ipn_url,
    );

    // Sort the array by key
    ksort($params);

    // Build the query string
    $query_string = http_build_query($params, '', '&');

    // Create the HMAC signature
    $hmac = hash_hmac('sha512', $query_string, $private_key);

    error_log(date('[Y-m-d H:i e] ') . "HMAC Sent => \n" . $query_string . "\n" . $hmac . PHP_EOL, 3, now_payments_log);


    // Initialize cURL
    $ch = curl_init('https://www.coinpayments.net/api.php');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: ' . $hmac));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Set to 1 in production

    // Execute the request
    $response = curl_exec($ch);
    if ($response === false) {
        die('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);

    // Decode the response
    $result = json_decode($response, true);

    if ($result['error'] == 'ok') {
        // Redirect to the payment URL

        $coinpayments_txn_id = $result["result"]["txn_id"];

        $add_new_transaction = mysqli_query($conn, "INSERT INTO $Coin_Payments_table (transaction_id, item_number, payment_status, from_user, payment_gross, currency_code,txn_id  ) VALUES ('$order_id', '$item_number' , 0, '$uid', $amount_to_charge, '$currency', '$coinpayments_txn_id' )");
        if ($add_new_transaction === TRUE) {
            header('Location: ' . $result['result']['checkout_url']);
            exit;
        }


    } else {
        // Handle error
        echo 'Error: ' . $result['error'];
    }


}
