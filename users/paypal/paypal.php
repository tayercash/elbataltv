<?php
header("Access-Control-Allow-Origin: *");
$base = "https://api-m.sandbox.paypal.com";
$CLIENT_ID = "AWDkwLH0glmK-CvmzDY25O-yhys8QPzQOWkBSb1JmU-Rtg7uPOUbfoXat3gDiiR5vYMm0n8sIt7CbBpb";
$APP_SECRET = "EM-q-0vMuFnnrXs11Wsk6fK25ZfnvaPKdVndx_fHlf_jjgISDAM2Xzb_qkRLbQ7-YnAm2N6kgq4Aj_4B";
// $hex_ary = array();
// foreach (str_split($in_str) as $chr) {
//     $hex_ary[] = sprintf("%02X", ord($chr));
// }
// echo base64_encode(implode(' ', $hex_ary));
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header("Content-Type: Application/json");
    $access_token = generateAccessToken();
    $orderID = $_POST["orderID"];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $base . "/v2/checkout/orders/" . $orderID . "/capture");
    curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $headers = array(
        "Content-Type: application/json",
        "Authorization: Bearer $access_token"
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $res = curl_exec($ch);

    echo $res;

    exit();
}

function generateAccessToken()
{
    global $base, $CLIENT_ID, $APP_SECRET;
    $auth = base64_encode($CLIENT_ID . ":" . $APP_SECRET);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $base . "/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $headers = array(
        "accept: application/json",
        "accept-language: en_US",
        "Authorization: Basic $auth",
        "content-type: application/x-www-form-urlencoded"
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $res = json_decode(curl_exec($ch));
    return $res->access_token;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <!-- Replace "test" with your own sandbox Business account app client ID -->
    <script src="https://www.paypal.com/sdk/js?client-id=AWDkwLH0glmK-CvmzDY25O-yhys8QPzQOWkBSb1JmU-Rtg7uPOUbfoXat3gDiiR5vYMm0n8sIt7CbBpb&currency=USD"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Set up a container element for the button -->
    <div id="paypal-button-container"></div>
    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        "amount": {
                            "currency_code": "USD",
                            "value": 5
                        }
                    }]
                });
            },
            // Finalize the transaction on the server after payer approval
            onApprove(data) {
                console.log(data);

                $.ajax({
                    method: "POST",
                    url: "",
                    data: {
                        orderID: data.orderID
                    },
                    success: function(res) {
                        console.log(res);
                    }
                })

            }
        }).render('#paypal-button-container');
    </script>
</body>

</html>