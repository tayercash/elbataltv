<?php
date_default_timezone_set('UTC');
include_once("../config_db.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');


$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";

$nowtime = date('Y-m-d H:i:s', time());

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

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


    if (isset($_POST["d"]) && $_POST["d"] !== "" && isset($_POST["t"]) && $_POST["t"] !== "") {
        $key = "ajkhsbcjk#&#&*@@ds" . $_POST["t"];
        $post_data = MouDecrypt($_POST["d"], $key);
        $_POST = json_decode($post_data, true);


        if (isset($_POST["action"]) && $_POST["action"] !== "") {

            $page_action = $_POST["action"];

            if ($page_action == "register_account_with_data") {

                $user_name = $_POST["username"];
                $user_email = $_POST["email"];
                $user_pass = $_POST["password"];
                $user_pass_hashed = password_hash($user_pass, PASSWORD_DEFAULT);
                $user_pass_1 = $_POST["retype_password"];
                $avatar_code = isset($_POST["avatar_code"]) ? ($_POST["avatar_code"] == "" ? rand_string(10) : $_POST["avatar_code"]) : rand_string(10);
                $u_client = isset($_POST["u_client"]) ? $_POST["u_client"] : "mouscripts";

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

                if (isset($_POST["u_id"]) && $_POST["u_id"] !== "" && isset($_POST["u_ip"]) && $_POST["u_ip"] !== "" && isset($_POST["u_name"]) && $_POST["u_name"] !== "") {
                    $user_device_id = $_POST["u_id"];
                    $user_device_name = $_POST["u_name"];
                    $user_ip = $_POST["u_ip"];
                } else {
                    addmsg(402, "An Error.");
                    $has_error = true;
                    $stop = true;
                }

                if ($stop === false) {

                    $result1 = $conn->query("SELECT * FROM $users_table_name WHERE email = " . esc($user_email) . "");
                    if ($result1->num_rows > 0) {
                        addmsg(402, "This email already exists");
                        $has_error = true;
                    } else {
                        $activecode = rand_string(8);
                        $activelink = $actual_link . "/login.php?action=activeaccount&username=$user_name&token=$activecode";

                        // $querycreateaccount = "INSERT INTO $users_table_name (username, password, email, user_icon, status, active, created_at, type ,actvcode,last_req_restoken,code_last_active) VALUES ('$user_name', '$user_pass_hashed', '$user_email', '$avatar_code', 'working',0,'$nowtime' , 'user' , '$activecode','$nowtime','$nowtime')";
                        $querycreateaccount = "INSERT INTO $users_table_name (username, password, email, user_icon, status, active, created_at, type ,actvcode,last_req_restoken) VALUES ('$user_name', '$user_pass_hashed', '$user_email', '$avatar_code', 'working',0,'$nowtime' , 'user' , '$activecode','$nowtime')";
                        $result = mysqli_query($conn, $querycreateaccount);
                        if ($result) {
                            $user_id = $conn->insert_id;

                            // addmsg("loged_in_u_id", $user_id);
                            // addmsg(200, "Account successfully created");


                            add_for_logins($user_id, $user_name, $user_email, $avatar_code, $g_icon, $avatar_or_g_icon, $user_device_id, $user_device_name, $user_ip, $u_client);

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


                if (isset($_POST["u_id"]) && $_POST["u_id"] !== "" && isset($_POST["u_ip"]) && $_POST["u_ip"] !== "" && isset($_POST["u_name"]) && $_POST["u_name"] !== "") {
                    $user_device_id = $_POST["u_id"];
                    $user_device_name = $_POST["u_name"];
                    $user_ip = $_POST["u_ip"];
                    $u_client = isset($_POST["u_client"]) ? $_POST["u_client"] : "mouscripts";

                } else {
                    addmsg(402, "An Error.");
                    $has_error = true;
                    return false;
                }

                $loged_in_status = false;
                $select_this_email = $conn->query("SELECT * FROM $users_table_name WHERE google_linked_id = " . esc($g_id) . "");
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


                    $select_g_email = $conn->query("SELECT * FROM $users_table_name WHERE email = " . esc($g_email) . "");
                    if ($select_g_email->num_rows > 0) {
                        $row = $select_g_email->fetch_assoc();
                        $user_id = $row["id"];
                        $user_name = $row["username"];
                        $email = $row["email"];
                        $avatar_code = $row["user_icon"];
                        // $g_icon = $row["g_icon"];
                        $avatar_or_g_icon = $row["avatar_or_g_icon"];

                        if ($row["google_linked_email"] == null) {
                            $conn->query("UPDATE $users_table_name SET google_linked=1,google_linked_email=" . esc($g_email) . ",google_linked_id=" . esc($g_id) . " WHERE email=" . esc($g_email) . "");
                        }

                        $loged_in_status = true;
                    } else {
                        $avatar_or_g_icon = 2;

                        // $result_create_user_by_google = mysqli_query($conn, "INSERT INTO $users_table_name (username, password, email, user_icon,avatar_or_g_icon, google_linked,google_linked_email, google_linked_id, status, active, created_at, type ,actvcode,last_req_restoken,code_last_active) VALUES (" . esc($g_name) . ", " . esc($google_user_pass_hashed) . ", " . esc($g_email) . ", " . esc($avatar_code) . ", $avatar_or_g_icon ,1," . esc($g_email) . "," . esc($g_id) . ", 'working',1," . esc($nowtime) . " , 'user' , " . esc($activecode) . "," . esc($nowtime) . "," . esc($nowtime) . ")");
                        $result_create_user_by_google = mysqli_query($conn, "INSERT INTO $users_table_name (username, password, email, user_icon,avatar_or_g_icon, google_linked,google_linked_email, google_linked_id, status, active, created_at, type ,actvcode,last_req_restoken) VALUES (" . esc($g_name) . ", " . esc($google_user_pass_hashed) . ", " . esc($g_email) . ", " . esc($avatar_code) . ", $avatar_or_g_icon ,1," . esc($g_email) . "," . esc($g_id) . ", 'working',1," . esc($nowtime) . " , 'user' , " . esc($activecode) . "," . esc($nowtime) . ")");

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
                $conn->query("UPDATE $users_table_name SET g_icon=" . esc($g_icon) . " WHERE email=" . esc($g_email) . "");

                if ($loged_in_status == true) {
                    // addmsg(200, "LOGED IN SUCCESSFULLY");
                    // addmsg("loged_in_u_id", $user_id);

                    $u_client = isset($_POST["u_client"]) ? $_POST["u_client"] : "mouscripts";

                    add_for_logins($user_id, $user_name, $email, $avatar_code, $g_icon, $avatar_or_g_icon, $user_device_id, $user_device_name, $user_ip, $u_client);
                }
            } else if ($page_action == "login_account_with_data") {
                $username = $_POST["user_name_email"];
                $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
                $u_client = isset($_POST["u_client"]) ? $_POST["u_client"] : "mouscripts";

                $force_login_with_email = false;
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
                if (isset($_POST["u_id"]) && $_POST["u_id"] !== "" && isset($_POST["u_ip"]) && $_POST["u_ip"] !== "" && isset($_POST["u_name"]) && $_POST["u_name"] !== "") {
                    $user_device_id = $_POST["u_id"];
                    $user_device_name = $_POST["u_name"];
                    $user_ip = $_POST["u_ip"];
                } else {
                    addmsg(402, "An Error.");
                    $stop = true;
                    $has_error = true;
                }

                if (isValidEmail($username)) {
                    $sql = "SELECT * FROM $users_table_name WHERE email = '$username'";
                    $log_msg = "This email does not exist";
                } else if (filter_var($username, FILTER_VALIDATE_INT) !== false) {
                    $sql = "SELECT * FROM $users_table_name WHERE id = '$username'";
                    $log_msg = "This ID does not exist";
                } else {
                    addmsg(402, "Please Insert an Vaild id or valid email");
                    $stop = true;
                    $has_error = true;

                    $sql = "SELECT * FROM $users_table_name WHERE username = '$username'";
                    $log_msg = "This username does not exist";
                }


                if ($stop === false) {

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
                            add_for_logins($user_id, $user_name, $email, $avatar_code, $g_icon, $avatar_or_g_icon, $user_device_id, $user_device_name, $user_ip, $u_client);
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
                    $result = $conn->query("SELECT * FROM $users_table_name WHERE id = " . esc($user_id) . " AND email = " . esc($user_email) . "");
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        // $sqlpassword = $row["password"];
                        // if (password_verify($now_user_password, $sqlpassword)) {

                        $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id=" . esc($user_id) . " and dev_id=" . esc($dev_id) . "");
                        if ($result_dev_id_logins->num_rows > 0) {
                            if ($conn->query("UPDATE $users_table_name SET username=" . esc($new_user_name) . " , user_icon = " . esc($new_user_avatar) . " , avatar_or_g_icon = " . esc($avatar_or_g_icon) . " WHERE id=" . esc($user_id) . " AND email = " . esc($user_email) . "") === TRUE) {
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
                    $user_device_name = $token_data["dev_name"];
                    $u_client = isset($token_data["u_client"]) ? $token_data["u_client"] : "mouscripts";

                    $now_utc = date("Y-m-d H:i:s", time());

                    $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id=" . esc($user_id) . " and dev_id=" . esc($user_device_id) . "");
                    if ($result_dev_id_logins->num_rows > 0) {

                        if ($conn->query("UPDATE `$logins_table` SET last_login_at=" . esc($now_utc) . ", dev_name = " . esc($user_device_name) . " WHERE user_id=" . esc($user_id) . " and dev_id=" . esc($user_device_id) . "") !== false) {

                            $select_user_data = $conn->query("SELECT * FROM $users_table_name WHERE id=" . esc($user_id) . "");
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
                                $active_until = $code_last_active + ($code_active_days * 24 * 60 * 60);
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
                                    //  || $u_client !== "mouscripts"
                                    if (($enable_active_with_code == true && $force_active_with_code == true) || $u_client !== "mouscripts") {
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
            } else if ($page_action == "Get_My_Devices") {
                if (isset($_POST["token"]) && $_POST["token"]) {
                    $token = $_POST["token"];
                    $token_data = json_decode(mou_custom_decode($token), true);
                    $user_id = $token_data["u_id"];
                    $user_device_id = $token_data["dev_id"];
                    $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id=" . esc($user_id) . " and dev_id=" . esc($user_device_id) . "");
                    if ($result_dev_id_logins->num_rows > 0) {

                        $Get_User_Devices_Result = $conn->query("SELECT * FROM $logins_table WHERE user_id=" . esc($user_id) . "");
                        addmsg("num", $Get_User_Devices_Result->num_rows);
                        addmsg("user_id", $user_id);

                        if ($Get_User_Devices_Result->num_rows > 0) {
                            $devices = [];
                            while ($row = $Get_User_Devices_Result->fetch_assoc()) {
                                $dev["id"] = $row["dev_id"];
                                $dev["name"] = $row["dev_name"];
                                $dev["last_login_at"] = $row["last_login_at"];
                                array_push($devices, $dev);
                            }
                            addmsg("Devices", $devices);
                        }
                    }
                }
            } else if ($page_action == "logout_an_device") {
                if (isset($_POST["token"]) && $_POST["token"]) {
                    $token = $_POST["token"];
                    $token_data = json_decode(mou_custom_decode($token), true);
                    $user_id = $token_data["u_id"];
                    $user_device_id = $token_data["dev_id"];
                    $device_id_will_logout = $token_data["device_id_will_logout"];
                    $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id=" . esc($user_id) . " and dev_id=" . esc($user_device_id) . "");
                    if ($result_dev_id_logins->num_rows > 0) {
                        if ($conn->query("DELETE FROM $logins_table WHERE user_id=" . esc($user_id) . " and dev_id=" . esc($device_id_will_logout) . "") === TRUE) {
                            addmsg(200, "device loged out");
                            addmsg(201, $device_id_will_logout);
                        }
                    }
                }
            } else if ($page_action == "ref_log_me") {
                if (isset($_POST["token"]) && $_POST["token"]) {
                    $token = $_POST["token"];
                    $token_data = json_decode(mou_custom_decode($token), true);
                    $user_id = $token_data["u_id"];
                    $user_device_id = $token_data["dev_id"];
                    $now_utc = date("Y-m-d H:i:s", time());
                    $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id=" . esc($user_id) . " and dev_id=" . esc($user_device_id) . "");
                    if ($result_dev_id_logins->num_rows > 0) {
                        if ($conn->query("UPDATE $logins_table SET last_login_at=" . esc($now_utc) . " WHERE user_id=" . esc($user_id) . " and dev_id=" . esc($user_device_id) . "") === TRUE) {
                            exit();
                        } else {
                            addmsg(401, $conn->error);
                        }
                    } else {
                        addmsg(400, "LogOut");
                    }
                }
            } else if ($page_action == "logout") {
                $token = $_POST["token"];
                $token_data = json_decode(mou_custom_decode($token), true);

                $user_id = $token_data["u_id"];
                $user_device_id = $token_data["dev_id"];

                if ($conn->query("DELETE FROM $logins_table WHERE user_id = " . esc($user_id) . " and dev_id = " . esc($user_device_id) . "") === TRUE) {
                    addmsg(200, "loged out Successfully");
                } else {
                    addmsg(400, $conn->error);
                }
            } else if ($page_action == "forget_password") {
                $forgeted_email = &$_POST["forgeted_email"];
                $u_id = &$_POST["u_id"];
                $u_name =& $_POST["u_name"];
                $u_ip = &$_POST["u_ip"];

                $nowutc = date("Y-m-d H:i:s", time());
                $now_utc_datetime = new DateTime($nowutc);
                $now_utc = $now_utc_datetime->format('U');
                $now_utc_date_time = date("Y-m-d H:i:s", $now_utc);

                $stop = false;
                if ($forgeted_email == "") {
                    addmsg(406, "لم يتم التعرف علي الايميل");
                    $stop = true;
                    $has_error = true;
                }
                if ($u_id == "" || $u_name == "" || $u_ip == "") {
                    addmsg(406, "خطأ في بعض البيانات");
                    $stop = true;
                    $has_error = true;
                }

                if ($has_error == false) {
                    $result = $conn->query("SELECT * FROM $users_table_name WHERE email = " . esc($forgeted_email) . "");
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $user_id = $row["id"];
                        $user_name = $row["username"];
                        $last_req_restoken = $row['last_req_restoken'];

                        if (time() > strtotime('+5 minutes', strtotime($last_req_restoken))) {
                            $resettoken = rand_string(8);
                            $sqlupdatecode = "UPDATE $users_table_name SET restoken = '$resettoken' , last_req_restoken = '$nowutc'  WHERE email = '$forgeted_email'";
                            if ($conn->query($sqlupdatecode) === TRUE) {

                                $token["u_id"] = $user_id;
                                $token["token"] = $resettoken;
                                $token["t"] = $now_utc;
                                $resetlink = $actual_link . "/recover-password.php?token=" . mou_custom_encode(json_encode($token, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));


                                $forgeted_email_html = file_get_contents("forget_email_template.html", true);
                                $forgeted_email_html = str_replace("[Product Name]", "Elbatal TV", $forgeted_email_html);
                                $forgeted_email_html = str_replace("{{name}}", $user_name, $forgeted_email_html);
                                $forgeted_email_html = str_replace("{{device_name}}", $u_name, $forgeted_email_html);
                                $forgeted_email_html = str_replace("{{action_url}}", $resetlink, $forgeted_email_html);
                                $forgeted_email_html = str_replace("{{support_url}}", "https://t.me/Elbatal_Tv", $forgeted_email_html);

                                $mail = new PHPMailer;
                                $mail->isSMTP();
                                $mail->Host = "smtp.gmail.com";
                                $mail->SMTPAuth = true;
                                $mail->Username = 'elbatalapptv@gmail.com';
                                $mail->Password = 'snta njit kgjc azam';
                                $mail->SMTPSecure = "tls";
                                $mail->Port = 587;
                                $mail->setFrom('elbatalapptv@gmail.com', 'Elbatal TV');
                                $mail->addAddress($forgeted_email, $user_name);
                                $mail->isHTML(true);
                                $mail->CharSet = 'UTF-8';
                                $mail->Subject = 'اعادة تعين كلمة مرور حسابك علي تطبيق Elbatal TV';
                                $mail->Body = $forgeted_email_html;

                                $mail->AltBody = 'click on this link to activate your account : ';
                                if (!$mail->send()) {
                                    addmsg(405, "ERROR => " . $mail->ErrorInfo);
                                } else {
                                    $conn->query("UPDATE $users_table_name SET last_req_restoken = " . esc($nowutc) . " WHERE email = " . esc($forgeted_email) . "");
                                    addmsg(200, "قم بفحص ايميلك . تم ارسال ايميل بمعلومات اعادة تعين كلمة المرور علي ايميلك");
                                }
                            }
                        } else {
                            $now = new DateTime();
                            $now_time = $now->getTimestamp();
                            $alow_time_stamp = strtotime($last_req_restoken) + (60 * 5);
                            $distance = $alow_time_stamp - $now_time;

                            addmsg(407, $distance);
                            $has_error = true;
                        }


                    } else {
                        addmsg(403, "هذا الإيميل غير مسجل به من قبل");
                        $has_error = true;

                    }
                }

            } else if ($page_action == "reset_password") {
                $new_password = &$_POST["new_password"];
                $logout_from_devices = $_POST["logout_from_devices"] == "1" ? true : false;

                $token = json_decode(mou_custom_decode($_POST["token"]), true);
                $u_id = $token["u_id"];
                $u_token = $token["token"];
                $u_t = (int) $token["t"];
                $u_t_active_until = $u_t + (1 * 24 * 60 * 60);

                $nowutc = date("Y-m-d H:i:s", time());
                $now_utc_datetime = new DateTime($nowutc);
                $now_utc = $now_utc_datetime->format('U');
                $now_utc_date_time = date("Y-m-d H:i:s", $now_utc);

                if ($now_utc <= $u_t_active_until) {
                    $result = $conn->query("SELECT * FROM $users_table_name WHERE id = " . esc($u_id) . "");
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $server_restoken = $row["restoken"];
                        if ($server_restoken == $u_token) {

                            $newpassword = password_hash($new_password, PASSWORD_DEFAULT);
                            if ($conn->query("UPDATE $users_table_name SET password = " . esc($newpassword) . " WHERE id = " . esc($u_id) . "") === TRUE) {
                                addmsg(200, 'new password set successfully');

                                $newresettoken = rand_string(8);
                                if ($conn->query("UPDATE $users_table_name SET restoken = " . esc($newresettoken) . " WHERE id = " . esc($u_id) . "") === TRUE) {
                                    addmsg(201, "Link Stoped Successfully.");
                                }

                                if ($logout_from_devices == true) {
                                    if ($conn->query("DELETE FROM $logins_table WHERE user_id = " . esc($u_id) . "") === TRUE) {
                                        addmsg(201, "Devices Loged OUT.");
                                    }
                                }
                            } else {
                                $has_error = true;
                                addmsg(403, 'db error : ' . $conn->error);
                            }

                        } else {
                            $has_error = true;
                            addmsg(404, "Token Expired.");
                        }
                    }
                } else {
                    $has_error = true;
                    addmsg(405, "Link Time Has Expired.");
                }
            }
            // $timestamp = time();
            // $response["t"] = $timestamp;
            // $key = "ajhsbcjkas@#@!!@sc" . $timestamp;
            // $response["d"] = MouEncrypt(response(), $key);
            // echo json_encode($response);

            echo response();
            // exit();
        }

    } else {

        $has_error = true;
        addmsg(403, "Cant Found Data");
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

function email_validation($email)
{
    global $whitelist_domains;
    return (!preg_match(
        "^([a-zA-Z0-9]+)([\.{1}])?([a-zA-Z0-9]+)\@(" . implode('|', $whitelist_domains) . ")^",
        $email
    )
    )
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
    if (is_null($txt)) {
        $txt = '';
    }
    $encoded = urlencode($txt);
    $encoded = base64_encode($encoded);
    $encoded = strtr($encoded, $custom, $default);
    return $encoded;
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

function add_for_logins($user_id, $user_name, $email, $avatar_code, $g_icon, $avatar_or_g_icon, $user_device_id, $user_device_name, $user_ip, $u_client)
{
    global $conn, $logins_table, $users_table_name, $has_error, $enable_active_with_code, $force_active_with_code, $code_active_days;

    $can_loged_in = false;
    $max_devices = 4;
    $now_utc = date("Y-m-d H:i:s", time());

    $result_dev_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id=" . esc($user_id) . " and dev_id=" . esc($user_device_id) . "");

    if ($result_dev_id_logins->num_rows > 0) {

        if ($conn->query("UPDATE `$logins_table` SET last_login_at=" . esc($now_utc) . ", dev_name=" . esc($user_device_name) . " WHERE user_id=" . esc($user_id) . " and dev_id=" . esc($user_device_id) . "") !== false) {
            $can_loged_in = true;
        }
    } else {
        $result_id_logins = $conn->query("SELECT * FROM $logins_table WHERE user_id = " . esc($user_id) . "");
        if ($result_id_logins->num_rows < $max_devices) {

            $result_add_login = mysqli_query($conn, "INSERT INTO $logins_table (user_id, dev_id, dev_name, dev_ip, last_login_at) VALUES (" . esc($user_id) . ", " . esc($user_device_id) . ", " . esc($user_device_name) . ", " . esc($user_ip) . ", " . esc($now_utc) . ")");
            if ($result_add_login === TRUE) {
                $can_loged_in = true;
            }
        }
    }


    if ($can_loged_in) {


        $result = $conn->query("SELECT * FROM $users_table_name WHERE id = " . esc($user_id) . "");
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

            $active_until = $code_last_active + ($code_active_days * 24 * 60 * 60);
            $now_utc = date("Y-m-d H:i:s", time());
            $now_utc_datetime = new DateTime($now_utc);
            $now_utc = $now_utc_datetime->format('U');

            $pro_until = $row["pro_until"];
            $pro_until = new DateTime($pro_until);
            $pro_until = $pro_until->format('U');

            $code_passed = false;
            // || $u_client !== "mouscripts"
            if (($enable_active_with_code == true && $force_active_with_code == true) || $u_client !== "mouscripts") {
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

function MouDecrypt($encrypted, $key = "")
{
    // Decode the encrypted string
    $encrypted = mb_convert_encoding($encrypted, 'UTF-8', 'auto');
    $encrypted = base64_decode($encrypted);

    $result = "";
    $i = 0;
    foreach (str_split($encrypted) as $letter) {
        $result .= chr(ord($letter) ^ ord($key[$i % strlen($key)]));
        $i++;
    }

    return $result;
}
function MouEncrypt($plain, $key = "")
{
    $result = "";
    $i = 0;
    foreach (str_split($plain) as $letter) {
        $result .= chr(ord($letter) ^ ord($key[$i % strlen($key)]));
        $i++;
    }
    $result = base64_encode($result);
    $result = mb_convert_encoding($result, 'UTF-8', 'auto');
    return $result;
}
function isValidEmail($email)
{
    // Use a regular expression to validate emails without requiring a TLD
    return preg_match('/^[\w\.\-]+@[\w\.\-]+$/', $email);
}