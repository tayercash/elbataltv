<?php
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-Type: text/plain; charset=utf-8");
$timestamp = time(); // Current timestamp in seconds since Unix Epoch

header("T: $timestamp");
$json_file = 'app_config_data.json';

if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $existing_data = json_decode($json_data, true);

    // Check if the existing data is a valid array
    if (!is_array($existing_data)) {
        $existing_data = array(); // Initialize as an empty array if invalid
    }
}
$Mou_Key = "c!xZj+N9saASFF&G@Ev@vw" . $timestamp;

$json_enc = MouEncrypt(json_encode($existing_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $Mou_Key);
// $json_dec = MouDecrypt($json_enc, $Mou_Key);
echo $json_enc;
function MouDecrypt($encrypted, $key = "")
{
    // Decode the encrypted string
    // $encrypted =  mb_convert_encoding($encrypted, 'UTF-8', 'ASCII');
    $encrypted =  iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $encrypted);
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
    $result = mb_convert_encoding($result, 'ASCII', 'UTF-8');
    return $result;
}