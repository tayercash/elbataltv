<?php
header("Content-Type:application/json");
include("../../config_db.php");

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";
$msgs_obj = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["action"]) && $_POST["action"] !== "") {

        $page_action = $_POST["action"];

        if ($page_action == "get_sub_packs") {

            $products = array();
            $result_query = $conn->query("SELECT * FROM $products_table WHERE category='batal_pro'");
            if ($result_query->num_rows > 0) {
                while ($row = $result_query->fetch_assoc()) {
                    $product["id"] = $row["id"];
                    $product["name"] = $row["name"];
                    $product["price"] = $row["price"];
                    $product["image"] = $row["image"];
                    $product["offer"] = $row["offer"];
                    if (filter_var($product["image"], FILTER_VALIDATE_URL) === FALSE) {
                        $product["image"] = $product["image"];
                    }
                    $product_status = $row["status"];
                    if ($product_status == 1) {
                        array_push($products, $product);
                    }
                }
            }
            addmsg("200", $products);
            addmsg("dollar_to_pound", $dollar_to_pound);
            addmsg("vodafone_num_for_send", $vodafone_num_for_send);
            addmsg("etislat_num_for_send", $etislat_num_for_send);
            addmsg("paypal_email_for_send", $paypal_email_for_send);
            addmsg("enable_paypal", $enable_paypal);
        }
    }
}


echo response();
exit();

function send_message_to_whatsapp()
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
    $template_component_parameter["text"] = "1";
    array_push($template_component_parameters, $template_component_parameter);

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = "2";
    array_push($template_component_parameters, $template_component_parameter);

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = "3";
    array_push($template_component_parameters, $template_component_parameter);

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = "4";
    array_push($template_component_parameters, $template_component_parameter);

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = "5";
    array_push($template_component_parameters, $template_component_parameter);

    $template_component_parameter["type"] = "text";
    $template_component_parameter["text"] = "6";
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
    if ($response === FALSE) {
        die('Whatsapp Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
}

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
