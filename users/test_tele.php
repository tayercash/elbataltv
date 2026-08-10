<?php
$telegram_token = "6507187286:AAFRvpLXDl-fn7eT1PPEqOJZLLg81b9bkIE";
$chat_id = "2140799570";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //     $telegram_msg = urlencode("
    // عملية دفع جديدة بقيمة $payment_gross $currency_code
    // <b>user_id</b> => <strong>$user_id</strong> 
    // txn_id  => <strong>$txn_id</strong>
    // res_url => $res_url
    // result  => $api_res
    //     ");

    //     $telegram_token = "6507187286:AAFRvpLXDl-fn7eT1PPEqOJZLLg81b9bkIE";
    //     $chat_id = "2140799570";
    //     $telegram_msg_link = "https://api.telegram.org/bot$telegram_token/sendMessage?chat_id=$chat_id&parse_mode=HTML&text=$telegram_msg";
    //     $ret = file_get_contents($telegram_msg_link);


}

$post_fields = array(
    'chat_id'   => $chat_id,
    'photo'     => new CURLFile(realpath("paypal/images/pro.png")),
    "caption" => "<b>Welcome</b>",
    "parse_mode" => "HTML" 
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type:multipart/form-data"
));
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot$telegram_token/sendPhoto?chat_id=$chat_id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
$output = curl_exec($ch);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Telegram PNG</title>
</head>

<body>
    <input id="image" type="image" width="100" height="30" alt="Login" src="https://raw.githubusercontent.com/mdn/learning-area/master/html/forms/image-type-example/login.png" />
</body>

</html>