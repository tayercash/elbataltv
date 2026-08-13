<?php
header("Content-Type:application/json");
include_once('../config_db.php');


if (isset($_GET["action"]) && $_GET["action"] !== "") {

    $action = $_GET["action"];
    if ($action == "extend") {
        $data_error = false;
        if (isset($_GET["user_id"]) && isset($_GET["type"])) {
            $user_id = $_GET["user_id"];
            $type_of_extend = in_array($_GET["type"], ["1", "2", "3", "4"]) ? $_GET["type"] : "1";

            if (isset($_GET["meth"]) && $_GET["meth"] == "coinpayments") {
                $method = "coinpayments";
            } else if (isset($_GET["paypal"])) {
                $method = "paypal";
            } else if (isset($_GET["phone_cash"])) {
                $method = "phone_cash";
            }
        } else {
            $data_error = true;
        }

        if ($data_error == false) {


            if ($type_of_extend == "1") {
                $add_what = '+1 Month';
            } else if ($type_of_extend == "2") {
                $add_what = '+3 Month';
            } else if ($type_of_extend == "3") {
                $add_what = '+6 Month';
            } else if ($type_of_extend == "4") {
                $add_what = '+1 Year';
            }

            $user_data = $conn->query("SELECT * From $users_table_name WHERE id=" . esc($user_id));
            if ($user_data->num_rows > 0) {
                $row = $user_data->fetch_assoc();

                $now_utc = date("Y-m-d H:i:s", time());
                $now_utc_datetime = new DateTime($now_utc);
                $now_utc = $now_utc_datetime->format('U');
                $pro_until = $row["pro_until"];
                $pro_until = new DateTime($pro_until);
                $pro_until = $pro_until->format('U');

                if ($pro_until > $now_utc) {
                    $updated_pro_until = strtotime($add_what, $pro_until);
                } else {
                    $updated_pro_until = strtotime($add_what, $now_utc);
                }
                $updated_pro_until_string = date("Y-m-d H:i:s", $updated_pro_until);
            }

            if ($method == "paypal") {

                if (isset($_GET["txn_id"])) {
                    $txn_id = $_GET["txn_id"];
                } else {
                    exit();
                }

                $payment_info = $conn->query("SELECT * From `$paypal_payments_table` WHERE txn_id=" . esc($txn_id));
                if ($payment_info->num_rows > 0) {
                    $row = $payment_info->fetch_assoc();
                    $successfull_url_status = $row["successfull_url_status"];

                    if ($successfull_url_status == 0 && (string)$row["from_user"] === (string)$user_id) {

                        if ($conn->query("UPDATE `$users_table_name` SET has_pro=1 , pro_until=" . esc($updated_pro_until_string) . " WHERE id=" . esc($user_id)) !== false) {
                            addmsg(200, "تم تجديد اشتراك للحساب - $user_id" . "\n" . "تاريخ التجديد اصبح : " . $updated_pro_until_string);
                        } else {
                            addmsg(401, "حدث خطأ اثناء التجديد");
                        }

                        // echo "now_utc => " . $now_utc . "\n";
                        // echo "pro_until => " . $pro_until . "\n";
                        // echo "updated_pro_until_string => " . $updated_pro_until_string . "\n\n";
                        // echo "difs => " . $updated_pro_until - $pro_until;

                        if ($conn->query("UPDATE `$paypal_payments_table` SET successfull_url_status=1 WHERE txn_id=" . esc($txn_id)) === false) {
                            addmsg(402, "An error occurred while modifying the successful status of the payment");
                        }
                    } else if ($successfull_url_status == 1) {
                        addmsg(403, "This payment has already been successfully completed");
                    }
                } else {
                    addmsg(404, "An error occurred during the payment process");
                }
            } else if ($method == "coinpayments") {


                if (isset($_GET["txn_id"])) {
                    $txn_id = $_GET["txn_id"];
                } else {
                    exit();
                }

                $payment_info = $conn->query("SELECT * From `$Coin_Payments_table` WHERE txn_id=" . esc($txn_id));
                if ($payment_info->num_rows > 0) {
                    $row = $payment_info->fetch_assoc();
                    $successfull_url_status = $row["successfull_url_status"];

                    if ($successfull_url_status == 0 && (string)$row["from_user"] === (string)$user_id) {

                        if ($conn->query("UPDATE `$users_table_name` SET has_pro=1 , pro_until=" . esc($updated_pro_until_string) . " WHERE id=" . esc($user_id)) !== false) {
                            addmsg(200, "تم تجديد اشتراك للحساب - $user_id" . "\n" . "تاريخ التجديد اصبح : " . $updated_pro_until_string);
                        } else {
                            addmsg(401, "حدث خطأ اثناء التجديد");
                        }

                        // echo "now_utc => " . $now_utc . "\n";
                        // echo "pro_until => " . $pro_until . "\n";
                        // echo "updated_pro_until_string => " . $updated_pro_until_string . "\n\n";
                        // echo "difs => " . $updated_pro_until - $pro_until;

                        if ($conn->query("UPDATE `$Coin_Payments_table` SET successfull_url_status=1 WHERE txn_id=" . esc($txn_id)) === false) {
                            addmsg(402, "An error occurred while modifying the successful status of the payment");
                        }
                    } else if ($successfull_url_status == 1) {
                        addmsg(403, "This payment has already been successfully completed");
                    }
                } else {
                    addmsg(404, "An error occurred during the payment process");
                }

            } else if ($method == "phone_cash") {
                if (isset($_GET["pay_id"])) {
                    $pay_id = $_GET["pay_id"];
                } else {
                    die("pay_id not found");
                }
                $payment_info = $conn->query("SELECT * From `$phone_payments_table` WHERE id=" . esc($pay_id) . " and from_user_id=" . esc($user_id));
                if ($payment_info->num_rows > 0) {
                    $row_payment = $payment_info->fetch_assoc();
                    $product_id = $row_payment["product_id"];
                    $payment_status = $row_payment["status"];
                    if ($payment_status == 0) {

                        if ($conn->query("UPDATE `$users_table_name` SET has_pro = 1 , pro_until=" . esc($updated_pro_until_string) . " WHERE id=" . esc($user_id)) !== false) {
                            addmsg(200, "Update Subscription Successfully.");
                            addmsg("updated_pro_until", $updated_pro_until);
                        } else {
                            addmsg(401, "حدث خطأ اثناء التجديد");
                        }

                        if ($conn->query("UPDATE `$phone_payments_table` SET status=1 WHERE id='" . esc($pay_id) . "' and from_user_id='" . esc($user_id) . "'") === false) {
                            addmsg(402, "An error occurred while modifying the successful status of the payment");
                        }
                    } else {
                        addmsg(201, "This payment has already been successfully completed");
                    }
                } else {
                    addmsg(401, "Wrong Pay");
                }
            }
        }
    }
}


echo response();
exit();

function rand_string($length)
{
    if ($length < 1) $length = 1;
    $bytes = random_bytes(ceil($length / 2));
    return substr(bin2hex($bytes), 0, $length);
}
function mou_custom_encode($txt, $num = 1)
{
    $default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    $custom = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";
    $encoded = urlencode($txt);
    for ($i = 1; $i <= $num; $i++) {
        $encoded = strtr(base64_encode($encoded), $custom, $default);
    }
    return htmlspecialchars($encoded);
}

function mou_custom_decode($txt, $num = 1)
{
    $default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    $custom = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";
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
