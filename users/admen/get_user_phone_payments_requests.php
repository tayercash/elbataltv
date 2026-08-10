<?php
header("Content-Type:application/json");
include("../config_db.php");

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";
$msgs_obj = array();

$dollar_to_pound = "30";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["action"]) && $_POST["action"] !== "") {

        $page_action = $_POST["action"];

        if ($page_action == "get_user_rquests") {

            $user_id = $_POST["user_id"];

            $requests = array();
            $result = $conn->query("SELECT * FROM $phone_payments_table WHERE from_user_id = '$user_id'");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $request["req_id"] = $row["id"];
                    $request["phone_cash_name"] = $row["phone_cash_name"];
                    $request["sender_phone"] = $row["sender_phone"];
                    $request["from_user_id"] = $row["from_user_id"];
                    $request["product_id"] = $row["product_id"];
                    $request["value"] = $row["value"];
                    $request["datetime"] = $row["datetime"];
                    $request["status"] = $row["status"];
                    array_push($requests, $request);
                }
            }

            addmsg("200", $requests);
        }
    }
}


echo response();
exit();

function rand_string($length)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return substr(str_shuffle($chars), 0, $length);
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
    $json_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return $json_response;
}
