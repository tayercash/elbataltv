<?php
date_default_timezone_set('UTC');
include_once("../config_db.php");
header("Content-Type:application/json");

define("Phone_payments_file", "Phone_Payments.log");

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$nowtime = date('Y-m-d H:i:s', time());

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $has_error = false;

    if (isset($_POST["action"]) && $_POST["action"] !== "") {

        $page_action = $_POST["action"];

        if ($page_action == "donation_with_phone_cash") {
            $can_save_data = false;
            $user_id = $_POST["user_id"];
            $donation_value = $_POST["donation_value"];
            $sender_number = $_POST["sender_number"];
            $phone_cash_name = $_POST["phone_cash_name"];
            $product_id = $_POST["product_id"];


            $last_payment_from_user_id = $conn->query("SELECT * FROM `$phone_payments_table` WHERE from_user_id = " . esc($user_id) . " ORDER BY ID DESC LIMIT 1");
            if ($last_payment_from_user_id->num_rows > 0) {
                $last_paymentRow = $last_payment_from_user_id->fetch_assoc();
                $last_payment_from_id = $last_paymentRow['id'];
                $last_payment_datetime = $last_paymentRow['datetime'];

                $now = new DateTime();
                $now_time = $now->getTimestamp();
                $alow_time_stamp = strtotime($last_payment_datetime) + (60 * 5);
                $distance =  $alow_time_stamp - $now_time;

                if ($now_time >= $alow_time_stamp) {
                    $can_save_data = true;
                } else {
                    addmsg(409, $distance);
                    $can_save_data = false;
                }
            } else {
                //Insert transaction data into the database
                $can_save_data = true;
            }

            if ($can_save_data == true) {
                $result = $conn->query("SELECT * FROM $products_table WHERE id = " . esc($product_id));
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $sub_type = $row["name"];
                }
                $result = $conn->query("SELECT * FROM $users_table_name WHERE id = " . esc($user_id));
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $user_name = $row["username"];
                    $user_email = $row["email"];
                }

                if (mysqli_query($conn, "INSERT INTO `$phone_payments_table` (from_user_id,sender_phone,value,phone_cash_name,product_id,status,datetime) VALUES (" . esc($user_id) . "," . esc($sender_number) . "," . esc($donation_value) . "," . esc($phone_cash_name) . "," . esc($product_id) . ",'0'," . esc($nowtime) . ")")) {
                    $sub_id = $conn->insert_id;

                    addmsg(200, "Your Payment Successfull.");
                    send_sub_data_to_telegram($user_id, $user_name, $user_email, $sub_type, $product_id, $sub_id, $phone_cash_name, $sender_number, $donation_value);
                    // send_sub_data_to_whatsapp($user_id, $user_name, $user_email, $sub_type, $product_id, $sub_id);
                } else {
                    addmsg(409, "حدث خطأ اثناء ارسال بيانات الدفع");
                    $has_error = true;
                }
            }
        }
        echo response();
        exit();
    }
}
function rand_string($length)
{
    if ($length < 1) $length = 1;
    $bytes = random_bytes(ceil($length / 2));
    return substr(bin2hex($bytes), 0, $length);
}
function addmsg($code, $msg)
{
    global $msgs_obj;
    $msgs_obj[$code] = $msg;
    // $response['message_code'] = $code;
    // $response['message'] = $msg;
    // array_push($msgs_array, $response);
}

function response()
{
    global $msgs_obj, $has_error;
    if ($has_error == false) {
        $response['status'] = true;
    } else {
        $response['status'] = false;
    }
    $response['messages'] = $msgs_obj;
    $json_response = json_encode($response);
    return $json_response;
}
function mou_custom_encode($txt, $num = 1)
{
    $default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    $custom  = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";
    $encoded = urlencode($txt);
    for ($i = 1; $i <= $num; $i++) {
        $encoded = strtr(base64_encode($encoded), $custom, $default);
    }
    return htmlspecialchars($encoded);
}

function mou_custom_decode($txt, $num = 1)
{
    $default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    $custom  = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";
    $decoded = $txt;
    for ($i = 1; $i <= $num; $i++) {
        $decoded = base64_decode(strtr($decoded, $custom, $default));
    }
    return urldecode(htmlspecialchars($decoded));
}

function send_sub_data_to_telegram($user_id, $user_name, $user_email, $sub_type, $product_id, $sub_id, $phone_cash_name, $sender_number, $donation_value)
{
    global $dollar_to_pound;
    $botToken = "7019855373:AAFao0nAusx6jChpaQiqd9IQWAgP9OoPYsk";
    $botAPI = "https://api.telegram.org/bot" . $botToken;

    if ($phone_cash_name == "PayPal") {
        $donation_value = $donation_value . "$";
    } else {
        $donation_value = ($donation_value * $dollar_to_pound) . " جنية";
    }

    $body_msg = "بيانات المستخدم : " . "\n";
    $body_msg .= "معرف المستخدم : " . $user_id . "\n";
    $body_msg .= "اسم المستخدم : " . $user_name . "\n";
    $body_msg .= "حساب المستخدم : " . $user_email . "\n\n";
    $body_msg .= "بيانات الطلب : " . "\n";
    $body_msg .= "الطلب : " . $sub_type . "\n";
    $body_msg .= "معرف المنتج : " . $product_id . "\n";
    $body_msg .= "معرف الطلب : " . $sub_id . "\n\n";
    $body_msg .= "بيانات التحويل : " . "\n";
    $body_msg .= "ميثود التحويل : " . $phone_cash_name . "\n";
    $body_msg .= "تحويل من : " . $sender_number . "\n";
    $body_msg .= "قيمة التحويل : " . $donation_value . "\n\n";


    $data = http_build_query([
        'text' => $body_msg,
        'chat_id' => "6368473051"
    ]);
    $keyboard = json_encode([
        "inline_keyboard" => [
            [
                [
                    "text" => "تأكيد طلب المستخدم",
                    "callback_data" => urlencode("a=subs&u_id=$user_id&type=$product_id&p_id=$sub_id")
                ]
            ]
        ]
    ]);
    $send_msg_res = file_get_contents($botAPI . "/sendMessage?{$data}&reply_markup={$keyboard}");
    error_log(date('[Y-m-d H:i e] ') . "send_msg_url => " . "\n" . $botAPI . "/sendMessage?{$data}&reply_markup={$keyboard}" . PHP_EOL, 3, Phone_payments_file);

    error_log(date('[Y-m-d H:i e] ') . "send_msg_res => " . "\n" . json_encode($send_msg_res) . "\n" . PHP_EOL, 3, Phone_payments_file);
}
function send_sub_data_to_whatsapp($user_id, $user_name, $user_email, $sub_type, $product_id, $sub_id)
{
    $serverKey  = "EAAK9NnWPNcsBOwerWvR0IRbLGtx6qHf0RHKXKzHPxUkfji35EQv9ojRu0NIPQ7UixbdlmGqkPeitOTlNOBfQhPlgWBUZBx1L9XOdEQaFKPllA0QwGLxZBVq4mZCJY0MrEsf5YYzqjMtZBXpn7ZAOtYCIEe7Lsijuqe8DsLmwXejLZAgK2IBuEBwFKSAHIFDf0LDoOVmKLlOMtWYWJ5SAUZD";

    $msg_data["messaging_product"] = "whatsapp";
    $msg_data["recipient_type"] = "individual";
    $msg_data["to"] = "201067480965";
    $msg_data["type"] = "template";
    $template["name"] = "user_subscriptions";

    $template_language["code"] = "ar";
    $template["language"] = $template_language;

    $template_components = array();

    // body parameters

    $template_component["type"] = "body";
    $template_component_parameters = array();

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = $user_id;
    array_push($template_component_parameters, $template_component_parameter);

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = $user_name;
    array_push($template_component_parameters, $template_component_parameter);

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = $user_email;
    array_push($template_component_parameters, $template_component_parameter);

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = $sub_type;
    array_push($template_component_parameters, $template_component_parameter);

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = $product_id;
    array_push($template_component_parameters, $template_component_parameter);

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = $sub_id;
    array_push($template_component_parameters, $template_component_parameter);

    $template_component["parameters"] = $template_component_parameters;
    array_push($template_components, $template_component);
    // buttons parameters
    $template_component["type"] = "button";
    $template_component["sub_type"] = "url";
    $template_component["index"] = "0";
    $template_component_parameters = array();

    $template_component_parameter["type"] = "payload";
    $template_component_parameter["payload"] = "?test_key=test_val&2nd_key=444";
    array_push($template_component_parameters, $template_component_parameter);

    $template_component["parameters"] = $template_component_parameters;
    array_push($template_components, $template_component);

    $template["components"] = $template_components;
    $msg_data["template"] = $template;


    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: Bearer ' . $serverKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v18.0/196120636927842/messages");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($msg_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //Send the request
    $response = curl_exec($ch);
    //Close request
    // if ($response === FALSE) {
    //     addmsg(409, "حدث خطأ اثناء ارسال بيانات الدفع");
    // }
    curl_close($ch);
}
