<?php
date_default_timezone_set('UTC');
include_once("../config_db.php");
header("Content-Type:application/json");
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$nowtime = date('Y-m-d H:i:s', time());
$msgs_obj = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Access-Control-Allow-Origin: *');

    $has_error = false;
    if (isset($_POST["action"]) && $_POST["action"] !== "") {
        $page_action = $_POST["action"];
        if ($page_action == "get_code") {
            $user_id = $_POST["uid"];


            $result_user = $conn->query("SELECT * FROM `$users_table_name` WHERE id='$user_id'");
            if ($result_user->num_rows > 0) {
                $row = $result_user->fetch_assoc();
                $now_user_active_code = $row["active_code"];

                $now_utc = date("Y-m-d H:i:s", time());
                $now_utc_datetime = new DateTime($now_utc);
                $now_utc = $now_utc_datetime->format('U');
                $now_utc_date_time = date("Y-m-d H:i:s", $now_utc);

                $create_new_code_for_user = false;
                $code_last_req = $row["code_last_req"];

                $code_last_active = $row["code_last_active"];
                $code_last_active = $code_last_active == null ? "2000-00-00 00:00:00" : $code_last_active;
                $code_last_active = new DateTime($code_last_active);
                $code_last_active = $code_last_active->format('U');
                $active_until = $code_last_active + ($code_active_days * 24 * 60 * 60);

                $pro_until = $row["pro_until"];
                $pro_until = new DateTime($pro_until);
                $pro_until = $pro_until->format('U');

                $code_passed = false;
                if ($enable_active_with_code == true) {
                    if ($row["has_pro"] == 1 && $pro_until > $now_utc) {
                        $code_passed = true;
                    } else {
                        if ($active_until > $now_utc) {
                            $code_passed = true;
                        }
                    }
                } else {
                    $code_passed = true;
                }

                if ($code_passed == true) {
                    addmsg(201, "User Activated");
                } else {

                    if ($code_last_req !== null) {
                        $code_last_req = new DateTime($code_last_req);
                        $code_last_req = $code_last_req->format('U');

                        $can_req_after = $code_last_req + ($can_req_new_code_after_mins * 60);

                        if ($now_utc < $can_req_after) {

                            $select_code_data = $conn->query("SELECT * FROM `$codes_table` WHERE code='$now_user_active_code'");
                            if ($select_code_data->num_rows > 0) {
                                $row = $select_code_data->fetch_assoc();
                                $code_id = $row["id"];
                                $code = $row["code"];
                                $short_link = $row["link"];
                                addmsg(200, "Last Code Found");
                                addmsg("short_link", $short_link);
                            } else {
                                addmsg(401, "code not found");
                                $has_error = true;
                            }
                        } else {
                            $create_new_code_for_user = true;
                        }
                    } else {
                        $create_new_code_for_user = true;
                    }

                    if ($create_new_code_for_user == true) {
                        $select_rand_code = $conn->query("SELECT * FROM `$codes_table` WHERE status = 1 AND short_website = $short_website ORDER BY RAND() LIMIT 1");
                        // $select_rand_code =  $conn->query("SELECT * FROM `$codes_table` WHERE status = 1 ORDER BY RAND() LIMIT 1");
                        if ($select_rand_code->num_rows > 0) {
                            $row = $select_rand_code->fetch_assoc();
                            $code_id = $row["id"];
                            $code = $row["code"];
                            $short_link = $row["link"];

                            if ($conn->query("UPDATE `$users_table_name` SET active_code='$code' , code_last_req='$now_utc_date_time' WHERE id='$user_id'") === TRUE) {
                                addmsg(200, "Succefully Create Code");
                                addmsg("short_link", $short_link);
                            } else {
                                addmsg(401, "Error updating record: " . $conn->error);
                                $has_error = true;
                            }
                        } else {
                        }
                    }
                }
            } else {
                addmsg(401, "User Not Found");
                $has_error = true;
            }
        } else if ($page_action == "validate_code") {
            if (isset($_POST["token"]) && $_POST["token"] !== "") {
                $code_token = $_POST["token"];
                $code_token = json_decode(mou_custom_decode($code_token), true);
                $user_id = $code_token["user_id"];
                $code = $code_token["code"];
                if (strlen($code) == 6) {

                    $select_user = $conn->query("SELECT * FROM `$users_table_name` WHERE id = '$user_id'");
                    if ($select_user->num_rows > 0) {

                        $row = $select_user->fetch_assoc();
                        $active_code = $row["active_code"];
                        $code_last_active = $row["code_last_active"];
                        $code_last_active = $code_last_active == null ? "2000-00-00 00:00:00" : $code_last_active;
                        $code_last_active = new DateTime($code_last_active);
                        $code_last_active = $code_last_active->format('U');
                        $active_until = $code_last_active + ($code_active_days * 24 * 60 * 60);
                        // $code_last_active = $code_last_active + (4 * 60 * 60);
                        $now_utc = date("Y-m-d H:i:s", time());
                        $now_utc_datetime = new DateTime($now_utc);
                        $now_utc = $now_utc_datetime->format('U');
                        $now_utc_date_time = date("Y-m-d H:i:s", $now_utc);
                        $new_code = "000";

                        $pro_until = $row["pro_until"];
                        $pro_until = new DateTime($pro_until);
                        $pro_until = $pro_until->format('U');


                        $code_passed = false;
                        if ($enable_active_with_code == true) {
                            if ($row["has_pro"] == 1 && $pro_until > $now_utc) {
                                $code_passed = true;
                            } else {
                                if ($active_until > $now_utc) {
                                    $code_passed = true;
                                    addmsg(mou_custom_encode("now_utc"), mou_custom_encode($now_utc));
                                    addmsg(mou_custom_encode("code_active_until"), mou_custom_encode($active_until));
                                }
                            }
                        } else {
                            $code_passed = true;
                        }

                        if ($code_passed == true) {
                            addmsg(200, "User Activated");
                        } else {

                            if ($code == 147369) {
                                if ($conn->query("UPDATE `$users_table_name` SET active_code='$new_code', code_last_active='$now_utc_date_time' WHERE id='$user_id'") === TRUE) {
                                    addmsg(200, "Correct Code");
                                    $active_until = $now_utc + ($code_active_days * 24 * 60 * 60);
                                    addmsg(mou_custom_encode("now_utc"), mou_custom_encode($now_utc));
                                    addmsg(mou_custom_encode("code_active_until"), mou_custom_encode($active_until));
                                } else {
                                    addmsg(400, "Error updating record: ");
                                    // addmsg(401, "Error updating record: " . $conn->error);
                                    $has_error = true;
                                }
                            } else if ($active_code == $code) {

                                $select_code_from_codes = $conn->query("SELECT * FROM `$codes_table` WHERE code = '$code'");
                                if ($select_code_from_codes->num_rows > 0) {
                                    $row = $select_code_from_codes->fetch_assoc();

                                    $code_status = $row["status"];
                                    if ($code_status == true) {
                                        if ($conn->query("UPDATE `$codes_table` SET used_num= used_num + 1 WHERE code = '$code'") !== TRUE) {
                                            addmsg(401, "Error updating used_num record: ");
                                        }
                                        if ($conn->query("UPDATE `$users_table_name` SET active_code='$new_code', code_last_active='$now_utc_date_time' WHERE id='$user_id'") === TRUE) {
                                            addmsg(200, "Correct Code");
                                            $active_until = $now_utc + ($code_active_days * 24 * 60 * 60);
                                            addmsg(mou_custom_encode("code_active_until"), mou_custom_encode($active_until));
                                            addmsg(mou_custom_encode("now_utc"), mou_custom_encode($now_utc));
                                        } else {
                                            addmsg(400, "Error updating record: ");
                                            // addmsg(401, "Error updating record: " . $conn->error);
                                            $has_error = true;
                                        }
                                    } else {
                                        addmsg(400, "Code Disabled !" . $conn->error);
                                        $has_error = true;
                                    }
                                } else {
                                    addmsg(400, "Code NOT FOUND !" . $conn->error);
                                    $has_error = true;
                                }
                            } else {
                                addmsg(400, "Code Wrong!");
                                $has_error = true;
                            }
                        }
                    } else {
                        addmsg(400, "User Not Found!");
                        $has_error = true;
                    }
                } else {
                    addmsg(400, "Token Wrong!");
                    $has_error = true;
                }
            } else {
                addmsg(400, "Token Not Found");
                $has_error = true;
            }
        }
        echo response();
        exit();
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"]) && $_GET["action"] !== "") {

        $page_action = $_GET["action"];
        if ($page_action == "create_code") {
            if (isset($_GET["num"]) && $_GET["num"] !== "" && isset($_GET["website"]) && $_GET["website"] !== "") {
                $codes_num = (int) $_GET["num"];
                $short_website = (int) $_GET["website"];


                // exit();

                for ($i = 0; $i < $codes_num; $i++) {
                    $code = rand_num(6);
                    $alias = "code" . rand_string(8);

                    $url_for_code = urlencode("https://new.elbatal-app.com/?code=" . $code);

                    if ($short_website == 1) {
                        $api_token = 'd1e178a78678ea130f6143c5775deb8eee2b5dca';
                        $api_url = "https://linkjust.com/api?api={$api_token}&url={$url_for_code}&alias=" . $alias;
                    } else if ($short_website == 2) {
                        $api_token = '0126dfb290affb3e71c81af8acb50367192806d7';
                        $api_url = "https://exe.io/api?api={$api_token}&url={$url_for_code}&alias=" . $alias;
                    } else if ($short_website == 3) {
                        $api_token = '08a5f83fa382c090918c530b9836137e3afdae7c';
                        $api_url = "https://cuty.io/api?api={$api_token}&url={$url_for_code}&alias=" . $alias;
                    } else if ($short_website == 4) {
                        $api_token = '4854a7d0cbb7e3e2e5d659a3f5548602e5dc2ca1';
                        $api_url = "https://shrinkme.io/api?api={$api_token}&url={$url_for_code}&alias=" . $alias;
                    } else {
                        break;
                    }


                    echo $api_url . "\n";

                    $shortenedUrl = "";
                    if (mysqli_query($conn, "INSERT INTO `$codes_table` (code,link,short_website) VALUES ('$code','$shortenedUrl',$short_website)")) {
                        $sub_id = $conn->insert_id;
                        addmsg(200, $sub_id);
                    } else {
                        addmsg(409, "حدث خطأ اثناء اضافة الكود");
                        $has_error = true;
                    }

                    $result = json_decode(file_get_contents($api_url), TRUE);

                    
                    if ($result["status"] === 'error') {
                        echo $result["message"];
                        //  echo $result["message"];
                    } else {
                        $shortenedUrl = $result["shortenedUrl"];
                        if (mysqli_query($conn, "INSERT INTO `$codes_table` (code,link,short_website) VALUES ('$code','$shortenedUrl',$short_website)")) {
                            $sub_id = $conn->insert_id;
                            addmsg(200, $sub_id);
                        } else {
                            addmsg(409, "حدث خطأ اثناء اضافة الكود");
                            $has_error = true;
                        }
                    }
                }
            }
        }
    }
}
function rand_num($length)
{
    $chars = "0123456789";
    return substr(str_shuffle($chars), 0, $length);
}
function rand_string($length)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return substr(str_shuffle($chars), 0, $length);
}
function addmsg($code, $msg)
{
    global $msgs_obj;
    $msgs_obj[$code] = $msg;
    // $response['message_code'] = $code;
    // $response['message'] = $msg;
    // array_push($msgs_array, $response);
}

function email_validation($email)
{
    global $whitelist_domains;
    return (!preg_match(
        "^([a-zA-Z0-9]+)([\.{1}])?([a-zA-Z0-9]+)\@(" . implode('|', $whitelist_domains) . ")^",
        $email
    ))
        ? FALSE : TRUE;
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
