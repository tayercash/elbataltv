<?php
include_once ("../config_db.php");

$show_not_auth = false;
if (isset($_GET["token"]) && $_GET["token"] !== "") {
    if (mou_custom_decode($_GET["token"]) !== "") {
        $token = json_decode(mou_custom_decode($_GET["token"]), true);
        $u_id = $token["u_id"];
        $u_token = $token["token"];
        $u_t = (int) $token["t"];
        $nowutc = date("Y-m-d H:i:s", time());
        $now_utc_datetime = new DateTime($nowutc);
        $now_utc = $now_utc_datetime->format('U');
        $now_utc_date_time = date("Y-m-d H:i:s", $now_utc);
        $u_t_active_until = $u_t + (1 * 24 * 60 * 60);

        if ($now_utc <= $u_t_active_until) {
            $result = $conn->query("SELECT * FROM $users_table_name WHERE id = '$u_id'");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $server_restoken = $row["restoken"];
                if ($server_restoken == $u_token) {
                    ?>
                    <!DOCTYPE html>
                    <html lang="en" dir="ltr" class="dark">

                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Elbatal Tv - Reset Password</title>
                        <link rel="stylesheet" href="../files/fonts/noto-sans-arabic/font.css">
                        <link rel="stylesheet" href="../files/fonts/font-awesome/css/all.min.css">
                        <link rel="stylesheet" href="css/recover-password.css">
                    </head>

                    <body>
                        <div class="mou_bg"></div>

                        <div class="container">
                            <div class="card_container">
                                <div class="card">
                                    <h1 class="title">
                                        Reset Your Password
                                    </h1>
                                    <form class="reset_password" id="reset_password" autocomplete="off" novalidate="novalidate">

                                        <div id="reset_password_elmnts" style="">

                                            <div class="mou_input_container prepend mou_box_shadow">
                                                <div class="prepend_input"><span>New Passowrd</span></div>
                                                <input class="input valid" type="text" id="new_password" name="new_password"
                                                    autocomplete="off" placeholder="Enter New Passord">
                                            </div>

                                            <div class="mou_input_container prepend mou_box_shadow">
                                                <div class="prepend_input"><span>Re Passowrd</span></div>
                                                <input class="input valid" type="text" id="re_password" name="re_password"
                                                    autocomplete="off" placeholder="Re Enter Your New Passord">
                                            </div>

                                            <label class="checkbox_container">LogOut From My All Devices
                                                <input type="checkbox" id="log_out_from_devices">
                                                <span class="checkmark"></span>
                                            </label>

                                            <button type="submit" class="mou_watch_btn" id="send_confirm_forget"><i
                                                    class="far fa-check"></i>Reset Password Now</button>
                                        </div>
                                    </form>

                                </div>
                                <div class="mou_alert mou_success password_changed_alert forget_password_alert d-none">
                                    <div class="mou_alert-icon"><i class="far fa-check"></i></div>
                                    <div class="mou_alert-message">Password has been reset successfully.<br>Now go to Elbatal TV app and log
                                        in.</div>
                                </div>

                                <div class="mou_alert mou_danger can_send_forget_timer_alert forget_password_alert d-none">
                                    <div class="mou_alert-icon"><i class="far fa-exclamation-circle"></i></div>
                                    <div class="mou_alert-message"><a>قمت بارسال ايميل لاستعادة كلمة المرور بالفعل<br>يمكنك الارسال مره
                                            اخري بعد <span class="can_send_forget_timer_after"></span></a></div>
                                </div>
                            </div>


                        </div>
                        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
                        <script src="js/jquery.validate.min.js"></script>
                        <script>
                            can_use_forget_password_form = true;
                            $("#reset_password").submit(function (e) {
                                e.preventDefault();
                                if ($(this).valid() && can_use_forget_password_form) {
                                    can_use_forget_password_form = false;
                                    new_password = $("#new_password").val();
                                    token = "<?php echo $_GET["token"] ?>";
                                    logout_from_devices = $('#log_out_from_devices').is(':checked') == true ? "1" : "0";
                                    $.ajax({
                                        url: "accounts_4.php",
                                        type: 'POST',
                                        timeout: 30 * 1000,
                                        data: {
                                            action: "reset_password",
                                            new_password: new_password,
                                            token: token,
                                            logout_from_devices: logout_from_devices
                                        },
                                        success: function (data) {
                                            can_use_forget_password_form = true;
                                            if (data.status == false) {
                                                for (var i = 0; i < Object.keys(data.messages).length; i++) {
                                                    message_code = Object.keys(data.messages)[i];
                                                    message = data.messages[message_code];

                                                    showToast("حدث خطأ");
                                                    location.reload();


                                                }
                                            } else {

                                                for (var i = 0; i < Object.keys(data.messages).length; i++) {
                                                    message_code = Object.keys(data.messages)[i];
                                                    message = data.messages[message_code];
                                                    if (message_code == 200) {
                                                        $(".card").addClass("d-none");
                                                        $(".password_changed_alert").removeClass("d-none");

                                                    }
                                                }
                                            }

                                        }, error: function (jqXHR, error, errorThrown) {
                                            can_use_forget_password_form = true;
                                            showToast("Error !");
                                        }
                                    })

                                }
                            });
                            $(document).ready(function () {
                                $("#reset_password").validate({
                                    rules: {
                                        new_password: {
                                            required: true,
                                            minlength: 6
                                        },
                                        re_password: {
                                            required: true,
                                            minlength: 6,
                                            equalTo: "#new_password"
                                        }
                                    },
                                    messages: {

                                        new_password: {
                                            required: "Please enter the password",
                                            minlength: "Your password must be at least 6 characters long."
                                        },
                                        re_password: {
                                            required: "Please retype your password.",
                                            minlength: "Your password must be at least 6 characters long.",
                                            equalTo: "Confirm password is different from password"
                                        }

                                    },
                                    errorElement: "span",
                                    errorPlacement: function (error, element) {
                                        error.addClass("invalid-feedback mou_danger");
                                        error.insertAfter(element.parents(".mou_input_container"));
                                    }
                                });
                            });

                            function showToast(msg) {
                                alert(msg);
                            }
                        </script>
                    </body>

                    </html>
                    <?php
                } else {
                    $show_not_auth = true;
                }
            }
        } else {
            $show_not_auth = true;
        }
    } else {
        $show_not_auth = true;
    }
} else {
    $show_not_auth = true;
}

if ($show_not_auth == true) {
    ?>
    <!DOCTYPE html>
    <html lang="en" dir="ltr" class="dark">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Elbatal Tv - Reset Password</title>
        <link rel="stylesheet" href="../files/fonts/noto-sans-arabic/font.css">
        <link rel="stylesheet" href="../files/fonts/font-awesome/css/all.min.css">
        <link rel="stylesheet" href="css/recover-password.css">
    </head>

    <body>
        <div class="mou_bg"></div>

        <div class="container">
            <div class="card_container">

                <div class="mou_alert mou_danger can_send_forget_timer_alert forget_password_alert">
                    <div class="mou_alert-icon"><i class="far fa-exclamation-circle"></i></div>
                    <div class="mou_alert-message"><a>Link has expired !</a></div>
                </div>
            </div>


        </div>
    </body>

    </html>
    <?php
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
    $custom = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";
    $decoded = $txt;

    for ($i = 1; $i <= $num; $i++) {
        $decoded = base64_decode(strtr($decoded, $custom, $default));
    }
    return urldecode(htmlspecialchars($decoded));
}

?>