<?php
die();
date_default_timezone_set('UTC');
include_once("../config_db.php");
header("Content-Type:application/json");

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$nowtime = date('Y-m-d H:i:s', time());

$msgs_obj = array();

$empty_username_msg = "Do not leave the Username field blank";
$length_username_msg = "Your Username must be 6 characters long or more";
$empty_user_email_msg = "Do not leave the Email field blank";
$validate_user_email_msg = "Domain of this email is not supported yet";
$empty_user_password_msg = "Do not leave the Password field blank";
$length_user_password_msg = "Your password must be 6 characters long or more";
$empty_user_password1_msg = "Do not leave the Confirm Password field blank";
$validate_user_password1_msg = "The password confirmation should be exactly the same as the password";

$whitelist_domains = ['gmail.com'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $has_error = false;

    if (isset($_POST["action"]) && $_POST["action"] !== "") {

        $page_action = $_POST["action"];

        if ($page_action == "register_account_with_data") {

            $user_name = $_POST["username"];
            $user_email = $_POST["email"];
            $user_pass = $_POST["password"];
            $user_pass_hashed = password_hash($user_pass, PASSWORD_DEFAULT);
            $user_pass_1 = $_POST["retype_password"];
            $avatar_code = isset($_POST["avatar_code"]) ? ($_POST["avatar_code"] == "" ? rand_string(10) : $_POST["avatar_code"]) : rand_string(10);
            $g_icon = null;
            $avatar_or_g_icon = 1;

            $stop = false;

            if ($user_name == "") {
                addmsg(501, $empty_username_msg);
                $stop = true;
                $has_error = true;
            } else if (strlen($user_name) < 6) {
                addmsg(501, $length_username_msg);
                $stop = true;
                $has_error = true;
            }
            if ($user_email == "") {
                addmsg(502, $empty_user_email_msg);
                $stop = true;
                $has_error = true;
            }
            if ($user_pass == "") {
                addmsg(503, $empty_user_password_msg);
                $stop = true;
                $has_error = true;
            } else if (strlen($user_pass) < 6) {
                addmsg(503, $length_user_password_msg);
                $stop = true;
                $has_error = true;
            }
            if ($user_pass_1 == "") {
                addmsg(504, $empty_user_password1_msg);
                $stop = true;
                $has_error = true;
            } else if ($user_pass_1 !== $user_pass) {
                addmsg(504, $validate_user_password1_msg);
                $stop = true;
                $has_error = true;
            }

            if (isset($_POST["u_id"]) && $_POST["u_id"] !== "" && isset($_POST["u_ip"]) && $_POST["u_ip"] !== "") {
                $user_device_id = $_POST["u_id"];
                $user_ip = $_POST["u_ip"];
            } else {
                addmsg(402, "An Error.");
                $has_error = true;
                $stop = true;
            }

            if ($stop === false) {

                $result1 =  $conn->query("SELECT * FROM $users_table_name WHERE email = '$user_email'");
                if ($result1->num_rows > 0) {
                    addmsg(402, "This email already exists");
                    $has_error = true;
                } else {
                    $activecode = rand_string(8);
                    $activelink = $actual_link . "/login.php?action=activeaccount&username=$user_name&token=$activecode";

                    $querycreateaccount = "INSERT INTO $users_table_name (username, password, email, user_icon, status, active, created_at, type ,actvcode,last_req_restoken,code_last_active) VALUES ('$user_name', '$user_pass_hashed', '$user_email', '$avatar_code', 'working',0,'$nowtime' , 'user' , '$activecode','$nowtime','$now_time')";
                    $result = mysqli_query($conn, $querycreateaccount);
                    if ($result) {
                        $user_id = $conn->insert_id;

                        // addmsg("loged_in_u_id", $user_id);
                        // addmsg(200, "Account successfully created");


                        add_for_logins($user_id, $user_name, $user_email, $avatar_code, $g_icon, $avatar_or_g_icon, $user_device_id, $user_ip);

                        // $mail = new PHPMailer;
                        // $mail->isSMTP();
                        // $mail->Host = "smtp.gmail.com";
                        // $mail->SMTPAuth = true;
                        // $mail->Username = 'promahmoudnabil@gmail.com';
                        // $mail->Password = 'Mido@mido1';
                        // $mail->SMTPSecure = "tls";
                        // $mail->Port = 587;
                        // $mail->setFrom('promahmoudnabil@gmail.com', 'KOORA IPTV');
                        // $mail->addAddress($user_email, $user_name);
                        // $mail->isHTML(true);
                        // $mail->Subject = 'activeate your account on ' . $website_name;
                        // $mail->Body    = '<a>Wellcome <b>' . $user_name . '</b></a><br><a>click on this code to activate your account :- </a><a href="' . $activelink . '">' . $activecode . '<a>';
                        // $mail->AltBody = 'click on this link to activate your account : ' . $activelink;
                        // if (!$mail->send()) {
                        //     addmsg(406, 'Mailer Error: ' . $mail->ErrorInfo);
                        // } else {
                        //     addmsg(201, "The activation email has been sent to your email");
                        // }
                    } else {
                        addmsg(403, $conn->error);
                    }
                }
            }
        } else if ($page_action == "login_account_with_google") {
            $g_name = $_POST["username"];
            $g_email = mou_custom_decode($_POST["email"]);
            $g_id = mou_custom_decode($_POST["gid"]);
            $g_icon = $_POST["g_icon"];
            $avatar_code = $_POST["avatar_code"];
            $google_user_pass_hashed = password_hash(rand_string(12), PASSWORD_DEFAULT);
            $activecode = rand_string(8);


            if (isset($_POST["u_id"]) && $_POST["u_id"] !== "" && isset($_POST["u_ip"]) && $_POST["u_ip"] !== "") {
                $user_device_id = $_POST["u_id"];
                $user_ip = $_POST["u_ip"];
            } else {
                addmsg(402, "An Error.");
                $has_error = true;
                return false;
            }




            $loged_in_status = false;
            $select_this_email =  $conn->query("SELECT * FROM $users_table_name WHERE google_linked_id = '$g_id'");
            if ($select_this_email->num_rows > 0) {
                $loged_in_status = true;
                $row = $select_this_email->fetch_assoc();
                $user_id = $row["id"];
                $user_name = $row["username"];
                $email = $row["email"];
                $avatar_code = $row["user_icon"];
                // $g_icon = $row["g_icon"];
                $avatar_or_g_icon = $row["avatar_or_g_icon"];
            } else {


                $select_g_email =  $conn->query("SELECT * FROM $users_table_name WHERE email = '$g_email'");
                if ($select_g_email->num_rows > 0) {
                    $row = $select_g_email->fetch_assoc();
                    $user_id = $row["id"];
                    $user_name = $row["username"];
                    $email = $row["email"];
                    $avatar_code = $row["user_icon"];
                    // $g_icon = $row["g_icon"];
                    $avatar_or_g_icon = $row["avatar_or_g_icon"];

                    if ($row["google_linked_email"] == null) {
                        $conn->query("UPDATE $users_table_name SET google_linked=1,google_linked_email='$g_email',google_linked_id='$g_id' WHERE email='$g_email'");
                    }

                    $loged_in_status = true;
                } else {
                    $avatar_or_g_icon = 2;

                    $result_create_user_by_google = mysqli_query($conn, "INSERT INTO $users_table_name (username, password, email, user_icon,avatar_or_g_icon, google_linked,google_linked_email, google_linked_id, status, active, created_at, type ,actvcode,last_req_restoken,code_last_active) VALUES ('$g_name', '$google_user_pass_hashed', '$g_email', '$avatar_code', $avatar_or_g_icon ,1,'$g_email','$g_id', 'working',1,'$nowtime' , 'user' , '$activecode','$nowtime','$nowtime')");

                    if ($result_create_user_by_google === TRUE) {
                        $user_id = $conn->insert_id;
                        $user_name = $g_name;
                        $email = $g_email;
                    }

                    $loged_in_status = true;
                }


                // if ($result_create_user_by_google === true) {
                //     $_SESSION['login_with'] = 'google_oauth';
                //     $_SESSION['google_user_id'] = $google_user_id;
                //     $g_log_msg = "signedupsuccessfully";
                // } else {
                //     if (mysqli_errno($conn) == 1062) {
                //         $g_log_msg = "duplicate";
                //     }
                // }
            }
            $conn->query("UPDATE $users_table_name SET g_icon='$g_icon' WHERE email='$g_email'");

            if ($loged_in_status == true) {
                // addmsg(200, "LOGED IN SUCCESSFULLY");
                // addmsg("loged_in_u_id", $user_id);

                add_for_logins($user_id, $user_name, $email, $avatar_code, $g_icon, $avatar_or_g_icon, $user_device_id, $user_ip);
            }
        } else if ($page_action == "login_account_with_data") {

            $username = $_POST["user_name_email"];
            $password =  password_hash($_POST["password"], PASSWORD_DEFAULT);

            $force_login_with_email = true;
            $stop = false;

            if ($username == "") {
                addmsg(401, $empty_username_msg);
                $stop = true;
                $has_error = true;
            }
            if ($_POST["password"] == "") {
                addmsg(402, $empty_user_password_msg);
                $stop = true;
                $has_error = true;
            }
            if (isset($_POST["u_id"]) && $_POST["u_id"] !== "" && isset($_POST["u_ip"]) && $_POST["u_ip"] !== "") {
                $user_device_id = $_POST["u_id"];
                $user_ip = $_POST["u_ip"];
            } else {
                addmsg(402, "An Error.");
                $stop = true;
                $has_error = true;
            }


            if ($stop === false) {
                if ($force_login_with_email == true || filter_var($username, FILTER_VALIDATE_EMAIL)) {
                    $sql = "SELECT * FROM $users_table_name WHERE email = '$username'";
                    $log_msg = "This email does not exist";
                } else {
                    $sql = "SELECT * FROM $users_table_name WHERE username = '$username'";
                    $log_msg = "This username does not exist";
                }
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $sqlpassword = $row["password"];
                    if (password_verify($_POST["password"], $sqlpassword)) {
                        $user_id = $row["id"];
                        $user_name = $row["username"];
                        $email = $row["email"];
                        $avatar_code = $row["user_icon"];
                        $g_icon = $row["g_icon"];
                        $avatar_or_g_icon = $row["avatar_or_g_icon"];
                        add_for_logins($user_id, $user_name, $email, $avatar_code, $g_icon, $avatar_or_g_icon, $user_device_id, $user_ip);
                    } else {
                        addmsg(402, "Please check the password");
                        $has_error = true;
                    }
                } else {
                    addmsg(401, $log_msg);
                    $has_error = true;
                }
            }
        } else if ($page_action == "update_user_data") {
            $new_user_name = @$_POST["new_user_name"];
            $user_id = @$_POST["new_user_id"];
            $user_email = @$_POST["new_user_email"];
            $new_user_avatar = @$_POST["new_user_avatar"];
            // $now_user_password =  @$_POST["now_user_password"];
            $avatar_or_g_icon = $_POST["avatar_or_g_icon"];
            $dev_id = $_POST["dev_id"];

            $stop = false;

            if ($new_user_name == "" || $user_email == "" || $user_id == "" || $dev_id == "") {
                addmsg(401, "an error.");
                $stop = true;
                $has_error = true;
            }

            if ($stop === false) {
                if ($new_user_avatar == "") {
                    $new_user_avatar = "10b15ef17da8534081";
                }

                // echo "SELECT * FROM $users_table_name WHERE id = '$user_id' AND email = '$user_email'" . "\n";
                $result = $conn->query("SELECT * FROM $users_table_name WHERE id = '$user_id' AND email = '$user_email'");
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    // $sqlpassword = $row["password"];
                    // if (password_verify($now_user_password, $sqlpassword)) {

                    $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id='$user_id' and dev_id='$dev_id'");
                    if ($result_dev_id_logins->num_rows > 0) {
                        if ($conn->query("UPDATE $users_table_name SET username='$new_user_name' , user_icon = '$new_user_avatar' , avatar_or_g_icon = '$avatar_or_g_icon' WHERE id='$user_id' AND email = '$user_email'") === TRUE) {
                            addmsg("user_name", $new_user_name);
                            addmsg("avatar", $new_user_avatar);
                            addmsg("avatar_or_g_icon", $avatar_or_g_icon);
                            addmsg(200, "Profile Updated Successfully.");
                        } else {
                            echo "Error updating record: " . $conn->error;
                        }
                    } else {
                        addmsg(401, "An Error !");
                        $has_error = true;
                    }

                    // } else {
                    //     addmsg(402, "Please check the password");
                    //     $has_error = true;
                    // }
                } else {
                    addmsg(401, "doesn match");
                    $has_error = true;
                }
            }
        } else if ($page_action == "get_user_data") {

            if (isset($_POST["token"]) && $_POST["token"]) {
                $token = $_POST["token"];
                $token_data = json_decode(mou_custom_decode($token), true);

                $user_id = $token_data["u_id"];
                $user_device_id = $token_data["dev_id"];


                $now_utc = date("Y-m-d H:i:s", time());

                $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id='$user_id' and dev_id='$user_device_id'");
                if ($result_dev_id_logins->num_rows > 0) {

                    if ($conn->query("UPDATE `$logins_table` SET last_login_at='$now_utc' WHERE user_id='$user_id' and dev_id='$user_device_id'") !== false) {

                        $select_user_data = $conn->query("SELECT * FROM $users_table_name WHERE id='$user_id'");
                        if ($select_user_data->num_rows > 0) {
                            $row = $select_user_data->fetch_assoc();

                            $user_name = $row["username"];
                            $email = $row["email"];
                            $avatar_code = $row["user_icon"];
                            $g_icon = $row["g_icon"];
                            $avatar_or_g_icon = $row["avatar_or_g_icon"];
                            $role = $row["role"];

                            $now_utc_datetime = new DateTime($now_utc);
                            $now_utc = $now_utc_datetime->format('U');
                            $code_last_active = $row["code_last_active"];
                            $code_last_active = $code_last_active == null ? "2000-00-00 00:00:00" : $code_last_active;
                            $code_last_active = new DateTime($code_last_active);
                            $code_last_active = $code_last_active->format('U');
                            // $code_last_active = $code_last_active + (4 * 60 * 60);
                            $active_until = $code_last_active + ($code_active_days *  24 * 60 * 60);
                            addmsg(200, mou_custom_encode($row["has_pro"]));
                            addmsg(201, mou_custom_encode($user_name));
                            addmsg("username", $user_name);
                            addmsg(mou_custom_encode("email"), mou_custom_encode($email));
                            addmsg("avatar", $avatar_code);
                            addmsg("g_icon", $g_icon);
                            addmsg("avatar_or_g_icon", $avatar_or_g_icon);
                            addmsg(mou_custom_encode("role"), mou_custom_encode($role));
                            addmsg(mou_custom_encode("now_utc"), mou_custom_encode($now_utc));
                            addmsg(mou_custom_encode("dev_id"), mou_custom_encode($user_device_id));
                            addmsg(mou_custom_encode("code_active_until"), mou_custom_encode($active_until));
                            

                            $pro_until = $row["pro_until"];
                            $pro_until = new DateTime($pro_until);
                            $pro_until = $pro_until->format('U');
                            if ($row["has_pro"] == 1 && $pro_until > $now_utc) {
                                addmsg(mou_custom_encode("pro_until"), mou_custom_encode($pro_until));
                            } else {

                                $code_passed = false;
                                if ($enable_active_with_code == true && $force_active_with_code == true) {
                                    if ($active_until > $now_utc) {
                                        $code_passed = true;
                                    }
                                } else {
                                    $code_passed = true;
                                }

                                if ($code_passed == false) {
                                    addmsg(401, "loged out");
                                }
                            }
                        }
                    }
                } else {
                    addmsg(400, "loged out");
                }
            }
        } else if ($page_action == "logout") {
            $token = $_POST["token"];
            $token_data = json_decode(mou_custom_decode($token), true);

            $user_id = $token_data["u_id"];
            $user_device_id = $token_data["dev_id"];

            if ($conn->query("DELETE FROM $logins_table WHERE user_id = '$user_id' and dev_id = '$user_device_id'") === TRUE) {
                addmsg(200, "loged out Successfully");
            } else {
                addmsg(400,  $conn->error);
            }
        }
        echo response();
        exit();
    }
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
    $encoded = base64_encode($encoded);
    $encoded = strtr($encoded, $custom, $default);
    return $encoded;
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

function add_for_logins($user_id, $user_name, $email, $avatar_code, $g_icon, $avatar_or_g_icon, $user_device_id, $user_ip)
{
    global $conn, $logins_table, $users_table_name, $has_error, $enable_active_with_code, $force_active_with_code, $code_active_days;
    $can_loged_in = false;
    $max_devices = 4;
    $now_utc = date("Y-m-d H:i:s", time());


    $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id='$user_id' and dev_id='$user_device_id'");

    if ($result_dev_id_logins->num_rows > 0) {

        if ($conn->query("UPDATE `$logins_table` SET last_login_at='$now_utc' WHERE user_id='$user_id' and dev_id='$user_device_id'") !== false) {
            $can_loged_in = true;
        }
    } else {
        $result_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id = '$user_id'");
        if ($result_id_logins->num_rows < $max_devices) {

            $result_add_login = mysqli_query($conn, "INSERT INTO $logins_table (user_id, dev_id, dev_ip, last_login_at) VALUES ('$user_id', '$user_device_id', '$user_ip', '$now_utc')");
            if ($result_add_login === TRUE) {
                $can_loged_in = true;
            }
        }
    }


    if ($can_loged_in) {


        $result = $conn->query("SELECT * FROM $users_table_name WHERE id = '$user_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row["id"];
            $user_name = $row["username"];
            $email = $row["email"];
            $avatar_code = $row["user_icon"];
            $g_icon = $row["g_icon"];
            $avatar_or_g_icon = $row["avatar_or_g_icon"];
            $role = $row["role"];

            $code_last_active = $row["code_last_active"];
            $code_last_active = $code_last_active == null ? "2000-00-00 00:00:00" : $code_last_active;
            $code_last_active = new DateTime($code_last_active);
            $code_last_active = $code_last_active->format('U');
            // $code_last_active = $code_last_active + (4 * 60 * 60);

            $active_until = $code_last_active + ($code_active_days *  24 * 60 * 60);
            $now_utc = date("Y-m-d H:i:s", time());
            $now_utc_datetime = new DateTime($now_utc);
            $now_utc = $now_utc_datetime->format('U');

            $pro_until = $row["pro_until"];
            $pro_until = new DateTime($pro_until);
            $pro_until = $pro_until->format('U');

            $code_passed = false;
            if ($enable_active_with_code == true && $force_active_with_code == true) {
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
                addmsg("loged_in_u_id", $user_id);
                addmsg("user_name", $user_name);
                addmsg("email", $email);
                addmsg("avatar", $avatar_code);
                addmsg("g_icon", $g_icon);
                addmsg("avatar_or_g_icon", $avatar_or_g_icon);
                addmsg("role", $role);
                addmsg("active_until", $active_until);
                addmsg(200, "You have signed in successfully");
            } else {
                addmsg(405, "يجب عليك ادخال كود التفعيل");
                addmsg("u_id", $user_id);
                $has_error = true;
            }
        }
    } else {
        addmsg(409, "تم تسجيل الدخول بالحساب علي اقصي عدد اجهزه ممكن");
        $has_error = true;
    }
}
