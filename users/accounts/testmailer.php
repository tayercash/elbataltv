<?php
date_default_timezone_set('UTC');

$user_email = "promahmoudnabil@gmail.com";
$user_name = "Mahmoud Nabil";

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = "smtp.gmail.com";
$mail->SMTPAuth = true;
$mail->Username = 'elbatalapptv@gmail.com';
$mail->Password = 'snta njit kgjc azam';
$mail->SMTPSecure = "tls";
$mail->Port = 587;
$mail->setFrom('elbatalapptv@gmail.com', 'Elbatal TV');
$mail->addAddress($user_email, $user_name);
$mail->isHTML(true);
$mail->CharSet = 'UTF-8';
$mail->Subject = 'اعادة تعين كلمة مرور حسابك علي تطبيق Elbatal TV';
$mail->Body = '<a>Wellcome <b>' . $user_name . '</b></a>';

$mail->AltBody = 'click on this link to activate your account : ';
if (!$mail->send()) {

    echo "ERROR => " . $mail->ErrorInfo;

} else {

    echo "done";
    
}