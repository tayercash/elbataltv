<?php
header("Content-Type:application/json");
include("../config_db.php");

require 'vendor/autoload.php';
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";

$serverKey = 'AAAAz4LPjhA:APA91bHqxOTkHq1bXr7rWlPmzvgKrs1LnS7mDi61ot--VO-CwFjaJCGKNtTUJlYfAVWxcnooVUr6Aj7jv4EhrddCTsSgA6M3XyJwyLdl8oMGryoLeiHVS0B5UiAFFULU2lwrT_yKA-rl';
// $user_token = "dPj_FJYTRqSS8yoHtJ8hCC:APA91bGAOj-j1GceNwf9j66NJVyB9jn029ffzIzn12iCvCoI1mT9mkiiHUbS0txvY5vosEra_cp_nucRd9bKLfrKgD2ERtxxyRUqCQTbHhBaoFwCipI9oo-Xw_LwX0hDmqrqwQR1F8U2";


$msgs_obj = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["action"]) && $_POST["action"] !== "") {

        $page_action = $_POST["action"];

        if ($page_action == "sent_to_tobic") {

            $notify_title = $_POST["notify_title"];
            $notify_body = $_POST["notify_body"];
            $send_notify_with_img = $_POST["send_notify_with_img"] == "" ? false : $_POST["send_notify_with_img"];
            $to_topics = json_decode($_POST["to_topics"]);
            $qurey_data = $_POST["qurey_data"];
            $verify_notify = $_POST["verify_notify"] == "true" ? true : false;


            $data_error = false;
            if (isset($_POST["token"]) && $_POST["token"] !== "") {
                $token = $_POST["token"];
                $token = mou_custom_decode($token);
                $token_data = explode("#", $token);
            } else {
                $data_error = true;
                addmsg(400, "token not found");
            }

            if (count($to_topics) == 0) {
                $data_error = true;
                addmsg(400, "Please Select Topic.");
            }

            if ($data_error == false) {

                $user_id = $token_data[0];
                $dev_id = $token_data[1];
                $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id='$user_id' and dev_id='$dev_id'");
                if ($result_dev_id_logins->num_rows > 0) {
                    $can_send_notification = false;

                    $result_query = $conn->query("SELECT * FROM $share_queries_table WHERE query=\"$qurey_data\"");
                    if ($result_query->num_rows > 0) {
                        $row = $result_query->fetch_assoc();

                        $share_id = $row["id"];
                        if (file_exists("imgs/" . $row["img_url"])) {
                            $img_url = $actual_link . "/imgs/" . $row["img_url"];
                        }
                        if ($row["notified"] == 0) {
                            $can_send_notification = true;
                        } else {
                            if ($verify_notify == true) {
                                $can_send_notification = true;
                            } else {
                                addmsg(402, "Verify Notify.");
                                $has_error = true;
                            }
                        }
                    } else {
                        $can_send_notification = true;


                        $img_base64 = $_POST["img_base64"];
                        $img = str_replace('data:image/png;base64,', '', $img_base64);
                        $img = str_replace(' ', '+', $img);
                        $data = base64_decode($img);
                        $img_url = rand_string(5) . "_" . time() . ".png";
                        file_put_contents('imgs/' . $img_url, $data);

                        if ($conn->query("INSERT INTO $share_queries_table (`title`, `body`, `query`, `img_url`,`notified`) VALUES (\"$notify_title\", \"$notify_body\", \"$qurey_data\", \"$img_url\", 1)") === TRUE) {
                            $share_id = $conn->insert_id;
                            if (file_exists("imgs/" . $img_url)) {
                                $img_url = $actual_link . "/imgs/" . $img_url;
                            }
                        } else {
                            addmsg(409, "Error: " . $sql . "<br>" . $conn->error);
                            $has_error = true;
                        }
                    }
                    if ($can_send_notification == true) {
                        // try to send notify
                        $arrayToSend = [];
                        $customData = [];
                        $notification = [];
                        $android = [];
                        $customData["title"] = $notify_title;
                        $customData["body"] = $notify_body;
                        $customData["sound"] = "default";

                        $fixed_to_topics = array();
                        foreach ($to_topics as $topic) {
                            array_push($fixed_to_topics, "'$topic' in topics");
                        }
                        $condition = implode(" || ", $fixed_to_topics);



                        $serviceAccountKey = 'service-account-key.json';
                        $projectID = 'elbatal-tv-global'; // Replace with your Firebase Project ID
                        $deviceToken = 'docYmlnBQyCCrje8BsUpd0:APA91bEeub1iGP8YAqjOvGxp2IqbqdDAYyg3fZ3PDgYa95DaqbeyGfRG9djf4AZtxf3y0S9zVHBpQNXlYFt4OhpQYFJpiHsKhGZR6G63V9IOFo4pL0ue6NA';
                        $credentials = new ServiceAccountCredentials(
                            'https://www.googleapis.com/auth/firebase.messaging',
                            $serviceAccountKey
                        );
                        $accessToken = $credentials->fetchAuthToken()['access_token'];
                        $data = [
                            "message" => [
                                "condition" => $condition,
                                "data" => [
                                    "title" => $notify_title,
                                    "body" => $notify_body,
                                    "notification_id" => $share_id,
                                    "qurey_data" => $qurey_data
                                ]
                            ],
                        ];

                        // Send the request
                        $client = new Client();
                        try {
                            $response = $client->post(
                                "https://fcm.googleapis.com/v1/projects/{$projectID}/messages:send",
                                [
                                    'headers' => [
                                        'Authorization' => 'Bearer ' . $accessToken,
                                        'Content-Type' => 'application/json',
                                    ],
                                    'json' => $data,
                                ]
                            );

                            if ($conn->query("UPDATE $share_queries_table SET notified = 1 WHERE id=$share_id") === TRUE) {
                                addmsg(200, "Notify Successfully.");
                            } else {
                                addmsg(411, "An Error On Update Notified Status");
                            }
                        } catch (RequestException $e) {
                            // Handle any errors
                            addmsg(410, "An Error When Send data : " . $response->error);
                        }


                        // else {


                        //     $message_id = $response->message_id;

                        //     $result_get_share_info = $conn->query("SELECT * FROM $share_queries_table WHERE id='$share_id'");
                        //     if ($result_dev_id_logins->num_rows > 0) {
                        //         $row = $result_get_share_info->fetch_assoc();

                        //         $notify_ids = $row["notify_ids"];
                        //         if ($notify_ids == "") {
                        //             $new_notifies_id = $message_id;
                        //         } else {
                        //             $new_notifies_id = $notify_ids . "," . $notify_ids;
                        //         }
                        //     } else {
                        //         $new_notifies_id = $message_id;
                        //     }
                        //     if ($conn->query("UPDATE $share_queries_table SET notify_ids = '$new_notifies_id' WHERE id=$share_id") === TRUE) {
                        //     } else {
                        //         addmsg(411, "An Error On DB");
                        //     }
                        //     addmsg(200, "Notify Successfully.");
                        // }

                    }
                } else {
                    addmsg(401, "An Error !");
                    $has_error = true;
                }
            }
        } else if ($page_action == "share") {

            $share_title = $_POST["share_title"];
            $share_body = $_POST["share_body"];
            $qurey_data = $_POST["qurey_data"];
            $img_base64 = $_POST["img_base64"];

            $data_error = false;
            if (isset($_POST["token"]) && $_POST["token"] !== "") {
                $token = $_POST["token"];
                $token = mou_custom_decode($token);
                $token_data = explode("#", $token);
            } else {
                $data_error = true;
                addmsg(400, "token not found");
            }
            if ($data_error == false) {



                $user_id = $token_data[0];
                $dev_id = $token_data[1];
                $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id='$user_id' and dev_id='$dev_id'");
                if ($result_dev_id_logins->num_rows > 0) {


                    $result_query = $conn->query("SELECT * FROM $share_queries_table WHERE query=\"$qurey_data\"");
                    if ($result_query->num_rows > 0) {
                        $row = $result_query->fetch_assoc();
                        $share_id = $row["id"];
                        if (file_exists("imgs/" . $row["img_url"])) {
                            $img_url = $actual_link . "/imgs/" . $row["img_url"];
                        }
                    } else {

                        $img = str_replace('data:image/png;base64,', '', $img_base64);
                        $img = str_replace(' ', '+', $img);
                        $data = base64_decode($img);
                        $img_url = rand_string(5) . "_" . time() . ".png";
                        file_put_contents('imgs/' . $img_url, $data);

                        if ($conn->query("INSERT INTO $share_queries_table (`title`, `body`, `query`, `img_url`) VALUES (\"$share_title\", \"$share_body\", \"$qurey_data\", \"$img_url\")") === TRUE) {
                            $share_id = $conn->insert_id;
                            if (file_exists("imgs/" . $img_url)) {
                                $img_url = $actual_link . "/imgs/" . $img_url;
                            }
                        } else {
                            addmsg(409, "Error: " . $sql . "<br>" . $conn->error);
                            $has_error = true;
                        }
                    }

                    $share_link = $actual_link . "/share.php?share_id=" . $share_id;
                    // $share_link = $domainlink . "/share/" . $share_id;
                    addmsg(200, $share_link);
                } else {
                    addmsg(401, "An Error !");
                    $has_error = true;
                }
            }
        } else if ($page_action == "notified_clicked") {
            if (isset($_POST["notification_id"]) && $_POST["notification_id"] !== "") {
                $notification_id = $_POST["notification_id"];
                if ($conn->query("UPDATE $share_queries_table SET opend_from_notify_num = opend_from_notify_num + 1 WHERE id = $notification_id") === TRUE) {
                    // $share_id = $conn->insert_id;
                    addmsg(200, "Thanks");
                } else {
                    addmsg(409, "Error: " . $sql . "<br>" . $conn->error);
                    $has_error = true;
                }
            }
        } else if ($page_action == "share_opend") {

            if (isset($_POST["share_id"]) && $_POST["share_id"] !== "") {
                $share_id = $_POST["share_id"];
                if ($conn->query("UPDATE $share_queries_table SET opend_from_web_num = opend_from_web_num + 1 WHERE id = $share_id") === TRUE) {
                    // $share_id = $conn->insert_id;
                    addmsg(200, "Thanks");
                } else {
                    addmsg(409, "Error: " . $sql . "<br>" . $conn->error);
                    $has_error = true;
                }
            }
        } else if ($page_action == "sync") {

            $share_title = $_POST["title"];
            $share_body = $_POST["share_body"];
            $qurey_data = $_POST["qurey_data"];
            $img_base64 = $_POST["img_base64"];
            $server_name = $_POST["server_name"];

            $img_search_query = $share_title . " - " . $server_name;



            $data_error = false;
            if (isset($_POST["token"]) && $_POST["token"] !== "") {
                $token = $_POST["token"];
                $token = mou_custom_decode($token);
                $token_data = explode("#", $token);
            } else {
                $data_error = true;
                addmsg(400, "token not found");
            }
            if ($data_error == false) {



                $user_id = $token_data[0];
                $dev_id = $token_data[1];
                $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id='$user_id' and dev_id='$dev_id'");
                if ($result_dev_id_logins->num_rows > 0) {


                    $result_query = $conn->query("SELECT * FROM $query_img_search_table WHERE query=\"$img_search_query\"");
                    if ($result_query->num_rows > 0) {
                        $row = $result_query->fetch_assoc();
                        if (file_exists("imgs/" . $row["img_url"])) {
                            $img_url = $actual_link . "/imgs/" . $row["img_url"];
                        }
                    } else {
                        $img = str_replace('data:image/png;base64,', '', $img_base64);
                        $img = str_replace(' ', '+', $img);
                        $data = base64_decode($img);
                        $img_url = rand_string(5) . "_" . time() . ".png";
                        file_put_contents('imgs/' . $img_url, $data);

                        if ($conn->query("INSERT INTO $query_img_search_table (`query`, `img_url`) VALUES (\"$img_search_query\", \"$img_url\")") === TRUE) {
                            if (file_exists("imgs/" . $img_url)) {
                                $img_url = $actual_link . "/imgs/" . $img_url;
                            }
                        } else {
                            addmsg(409, "Error: " . $sql . "<br>" . $conn->error);
                            $has_error = true;
                        }
                    }



                    addmsg(200, $img_url);
                } else {
                    addmsg(401, "An Error !");
                    $has_error = true;
                }
            }

        }
    }
}

echo response();
exit();

function send_message($json)
{
    global $url, $serverKey;
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key=' . $serverKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //Send the request
    $response = curl_exec($ch);
    //Close request
    if ($response === FALSE) {
        die('FCM Send Error: ' . curl_error($ch));
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
