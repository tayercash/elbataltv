<?php
$chatId = "6368473051";
define("Telegram_log_file", "telegram.log");

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";


$update = json_decode(file_get_contents('php://input'), TRUE);

$botToken = "7019855373:AAFao0nAusx6jChpaQiqd9IQWAgP9OoPYsk";
$botAPI = "https://api.telegram.org/bot" . $botToken;


// Check if callback is set
if (isset($update['callback_query'])) {
    $callback_data = urldecode($update['callback_query']['data']);
    $action = getQueryVariable("a", "?" . $callback_data);
    // error_log(date('[Y-m-d H:i e] ') . "update => " . "\n" . $update . "\n" . PHP_EOL, 3, Telegram_log_file);

    if ($action == "check") {
        // Reply with callback_query data
        $data = http_build_query([
            'text' => '✅ Server Working',
            'chat_id' => $update['callback_query']['from']['id']
        ]);
        // end button from loading

        // send_res
        file_get_contents($botAPI . "/sendMessage?{$data}");

       $delete_buttons_res = file_get_contents($botAPI . "/editMessageReplyMarkup?chat_id=" . $update['callback_query']['from']['id'] . "&message_id=" . $update['callback_query']["message"]["message_id"] . "&inline_message_id=" .$update['callback_query']["id"] . "&reply_markup=");

    } else if ($action == "subs") {
        $type = getQueryVariable("type", "?" . $callback_data);
        $user_id = getQueryVariable("u_id", "?" . $callback_data);
        $pay_id = getQueryVariable("p_id", "?" . $callback_data);


        $res = json_decode(file_get_contents($actual_link . "/api/elbatal_pro_backs.php?action=extend&phone_cash=1&user_id=$user_id&type=$type&pay_id=$pay_id"), true);

        if ($res["status"] == true) {
            $messages = $res["messages"]["200"];

            $delete_buttons_res = file_get_contents($botAPI . "/editMessageReplyMarkup?chat_id=" . $update['callback_query']['from']['id'] . "&message_id=" . $update['callback_query']["message"]["message_id"] . "&inline_message_id=" .$update['callback_query']["id"] . "&reply_markup=");
        }
    }

    file_get_contents($botAPI . "/answerCallbackQuery?callback_query_id=" . $update['callback_query']['id']);
    // error_log(date('[Y-m-d H:i e] ') . "New callback_query" . "\n" . json_encode($update['callback_query']) . "\n" . PHP_EOL, 3, Telegram_log_file);

    // error_log(date('[Y-m-d H:i e] ') . "send_end_button => " . "\n" . $send_end_button . "\n" . PHP_EOL, 3, Telegram_log_file);
}

// Check for normal command
$msg = $update['message']['text'];
if ($msg === "/start") {

    // Create keyboard
    $data = http_build_query([
        'text' => 'Hello World',
        'chat_id' => $update['message']['from']['id']
    ]);
    $keyboard = json_encode([
        "inline_keyboard" => [
            [
                [
                    "text" => "Check Server Working",
                    "callback_data" => urlencode("a=check")
                ]
            ]
        ]
    ]);

    // Send keyboard
    $res = file_get_contents($botAPI . "/sendMessage?{$data}&reply_markup={$keyboard}");
}


function getQueryVariable($variable, $link = "")
{
    if ($link == "") {
        $query = $_SERVER['QUERY_STRING'];
    } else {
        if (strpos($link, "?") !== false) {
            $query = explode("?", $link)[1];
        } else {
            $query = $link;
        }
    }
    $vars = explode("&", $query);
    for ($i = 0; $i < count($vars); $i++) {
        $pair = explode("=", $vars[$i]);
        if ($pair[0] == $variable) {
            return $pair[1];
        }
    }
    return (false);
}
