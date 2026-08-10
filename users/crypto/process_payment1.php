<?php
include_once "../config_db.php";
require "../vendor/coinpaymentsnet/coinpayments-php/src/CoinpaymentsAPI.php";


$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";


// Payment details
$amount = 9.9; // Amount in USD
// CoinPayments fees
$coinpayments_fee_percent = 0.5; // Example: 0.5% CoinPayments merchant fee
// Calculate total amount to charge (product price + CoinPayments fees)
$amount_to_charge = $amount * (1 + $coinpayments_fee_percent / 100);
$currency = 'USD'; // Currency for the purchase
$coin = 'USDT.TRC20'; // Cryptocurrency to accept
$order_id = 'ORDER_' . uniqid(); // Unique order ID
$item_name = 'Mou CoinRyze +1 Month'; // Description of the product
// User and callback URLs
$success_url = "$actual_link/success.php";
$cancel_url = "$actual_link/cancel.php";
$ipn_url = "$actual_link/ipn.php";
// $uid = $_POST["uid"];
$uid = "2615574";


// CoinPayments API configuration
$public_key = '67390e8e994c2845f1a6b4583ac496687564127cc3e7cdc93be22dfbc3a0ef79'; // From CoinPayments
$private_key = 'e508f4515D035fdEb4C1c02B2bE4acb05011D7660c8Fc00353fBfd46aFd48BEa'; // From CoinPayments


// Initialize CoinPayments API client
$cps = new CoinPaymentsAPI($private_key, $public_key, "json");

// Create a new transaction
$req = array(
    'amount' => $amount, // USD amount
    'currency1' => 'USD', // Your currency
    'currency2' => 'USDT.TRC20', // The currency to accept
    'buyer_email' => 'customer@example.com', // Buyer’s email
    'item_name' => $item_name, // Name of the item
    'custom' => $order_id, // Custom field, e.g., order ID
    'ipn_url' => $ipn_url, // IPN URL for transaction updates
);

try {
    $result = $cps->CreateCustomTransaction($req);
    echo "Transaction created:\n";
    print_r($result);
    // Redirect the user to the checkout URL
    $add_new_transaction = mysqli_query($conn, "INSERT INTO $coinryze_users_Transactions_name (uid,status,transaction_id) VALUES ('$uid', 0, '$order_id')");
    if ($add_new_transaction === TRUE) {

    }
    header('Location: ' . $result['result']['checkout_url']);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
