<?php
header("Content-Type:application/json");
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";

$json["name"] = "Elbatal TV";
$json["short_name"] = "Elbatal TV";

$icons = array();
$icon["src"] = "$domainlink/users/icon.png";
$icon["sizes"] = "128x128";
$icon["type"] = "image/png";
array_push($icons, $icon);
$icon["src"] = "$domainlink/users/icon.png";
$icon["sizes"] = "144x144";
$icon["type"] = "image/png";
array_push($icons, $icon);
$icon["src"] = "$domainlink/users/icon.png";
$icon["sizes"] = "152x152";
$icon["type"] = "image/png";
array_push($icons, $icon);
$icon["src"] = "$domainlink/users/icon.png";
$icon["sizes"] = "192x192";
$icon["type"] = "image/png";
array_push($icons, $icon);
$icon["src"] = "$domainlink/users/icon.png";
$icon["sizes"] = "256x256";
$icon["type"] = "image/png";
array_push($icons, $icon);
$icon["src"] = "$domainlink/users/icon.png";
$icon["sizes"] = "512x512";
$icon["type"] = "image/png";
array_push($icons, $icon);
$json["icons"] = $icons;

$json["scope"] = "/";
// $json["display"] = "/index.html";
$json["start_url"] = "standalone";
$json["background_color"] = "#e8eaf6";
$json["theme_color"] = "#3f51b5";

$related_applications = array();
$related_application["platform"] = "play";
$related_application["id"] = "com.mouscripts.elbatal";
$related_application["url"] = $domainlink;
array_push($related_applications, $related_application);
$json["related_applications"] = $related_applications;

echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
