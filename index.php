<?php

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";

$page_title = "Elbatal TV - تحميل تطبيق البطل";
$page_description = "تطبيق البطل. Elbatal TV. افضل تطبيق لمشاهدة المباريات والافلام والمسلسلات مجانا. انشاء كود تفعيل لحسابك.";
$page_image = "https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgfO0U_DyEwxYMyTMzuYAkJQLyFTP2M17J45CDHfbcFnD6RztG2xNpAnrbEGz-OZ34XrnBfM4OJsMJjAWZ19D-7JReUGs2lyhz-UHb2KjZVteCr1oqyVRBHQHJ50reo86lrXT0rN9OkCs396KXol4FcM3Pc2mZpxDlipOjlV93MILk_xx-vCJV64DMs_jU/s1024/logo-1024-back.png";
$fav_icon_url = $domainlink . "/favicon.ico";

$is_dev = isset($_GET["is_dev"]) && $_GET["is_dev"] == "1" ? true : false;

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>" />
    <meta name="keywords"
        content='download elbatal tv, telecharger elbatal tv, elbatal tv apk, application elbatal tv, installer elbatal tv, elbatal pc, elbatal tv, elbatal tv, elbatal app, elbatal app, elbatal app live tv, bein sports, beoutq, osn, mbc, alkass, best app, koora, yalla shot, live plus, Embratoria, Redbox tv, Show Sport tv, Kora liveالبطل, البطل بث مباشر, تطبيق البطل, تحميل البطل , مباريات اليوم, قنوات مشفرة, قنوات عربية, بث مباشر, بي ان سبورت, بي اوت كيو, يلا شوت, كورة لايف' />
    <meta property="og:title" content="<?php echo $page_title; ?>" />
    <meta property="og:description" content="<?php echo $page_description; ?>" />
    <meta property="og:image" content="<?php echo $page_image; ?>" />
    <meta property="og:url" content="<?php echo $domainlink; ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo $page_title; ?>" />
    <meta name="twitter:description" content="<?php echo $page_description; ?>" />
    <meta name="twitter:image" content="<?php echo $page_image; ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="canonical" href="<?php echo $domainlink; ?>" />
    <link href='<?php echo $fav_icon_url; ?>' rel='apple-touch-icon' sizes='120x120' />
    <link href='<?php echo $fav_icon_url; ?>' rel='apple-touch-icon' sizes='152x152' />
    <link href='<?php echo $fav_icon_url; ?>' rel='icon' type='image/x-icon' />
    <link href='<?php echo $fav_icon_url; ?>' rel='shortcut icon' type='image/x-icon' />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* arabic */
        @font-face {
            font-family: 'Noto Sans Arabic';
            font-style: normal;
            font-weight: 300;
            font-stretch: 100%;
            src: url(https://fonts.gstatic.com/s/notosansarabic/v18/nwpCtLGrOAZMl5nJ_wfgRg3DrWFZWsnVBJ_sS6tlqHHFlj4wv4o.woff2) format('woff2');
            unicode-range: U+0600-06FF, U+200C-200E, U+2010-2011, U+204F, U+2E41, U+FB50-FDFF, U+FE80-FEFC, U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        /* arabic */
        @font-face {
            font-family: 'Noto Sans Arabic';
            font-style: normal;
            font-weight: 400;
            font-stretch: 100%;
            src: url(https://fonts.gstatic.com/s/notosansarabic/v18/nwpCtLGrOAZMl5nJ_wfgRg3DrWFZWsnVBJ_sS6tlqHHFlj4wv4o.woff2) format('woff2');
            unicode-range: U+0600-06FF, U+200C-200E, U+2010-2011, U+204F, U+2E41, U+FB50-FDFF, U+FE80-FEFC, U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        /* arabic */
        @font-face {
            font-family: 'Noto Sans Arabic';
            font-style: normal;
            font-weight: 500;
            font-stretch: 100%;
            src: url(https://fonts.gstatic.com/s/notosansarabic/v18/nwpCtLGrOAZMl5nJ_wfgRg3DrWFZWsnVBJ_sS6tlqHHFlj4wv4o.woff2) format('woff2');
            unicode-range: U+0600-06FF, U+200C-200E, U+2010-2011, U+204F, U+2E41, U+FB50-FDFF, U+FE80-FEFC, U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        /* arabic */
        @font-face {
            font-family: 'Noto Sans Arabic';
            font-style: normal;
            font-weight: 600;
            font-stretch: 100%;
            src: url(https://fonts.gstatic.com/s/notosansarabic/v18/nwpCtLGrOAZMl5nJ_wfgRg3DrWFZWsnVBJ_sS6tlqHHFlj4wv4o.woff2) format('woff2');
            unicode-range: U+0600-06FF, U+200C-200E, U+2010-2011, U+204F, U+2E41, U+FB50-FDFF, U+FE80-FEFC, U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        /* arabic */
        @font-face {
            font-family: 'Noto Sans Arabic';
            font-style: normal;
            font-weight: 700;
            font-stretch: 100%;
            src: url(https://fonts.gstatic.com/s/notosansarabic/v18/nwpCtLGrOAZMl5nJ_wfgRg3DrWFZWsnVBJ_sS6tlqHHFlj4wv4o.woff2) format('woff2');
            unicode-range: U+0600-06FF, U+200C-200E, U+2010-2011, U+204F, U+2E41, U+FB50-FDFF, U+FE80-FEFC, U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        /* arabic */
        @font-face {
            font-family: 'Noto Sans Arabic';
            font-style: normal;
            font-weight: 800;
            font-stretch: 100%;
            src: url(https://fonts.gstatic.com/s/notosansarabic/v18/nwpCtLGrOAZMl5nJ_wfgRg3DrWFZWsnVBJ_sS6tlqHHFlj4wv4o.woff2) format('woff2');
            unicode-range: U+0600-06FF, U+200C-200E, U+2010-2011, U+204F, U+2E41, U+FB50-FDFF, U+FE80-FEFC, U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        /* arabic */
        @font-face {
            font-family: 'Noto Sans Arabic';
            font-style: normal;
            font-weight: 900;
            font-stretch: 100%;
            src: url(https://fonts.gstatic.com/s/notosansarabic/v18/nwpCtLGrOAZMl5nJ_wfgRg3DrWFZWsnVBJ_sS6tlqHHFlj4wv4o.woff2) format('woff2');
            unicode-range: U+0600-06FF, U+200C-200E, U+2010-2011, U+204F, U+2E41, U+FB50-FDFF, U+FE80-FEFC, U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Noto Sans Arabic', sans-serif;
            overflow: hidden;
            overflow-y: auto;
        }


        .background-container {
            position: relative;
            height: 300px;
            overflow: hidden;
            background: linear-gradient(186deg, var(--first_background_color) 0%, var(--second_background_color) 49%, var(--second_background_color) 74%, var(--first_background_color) 100%);
            background-attachment: fixed;
            background-size: cover;
            background-position: bottom center;
        }

        .header_box {
            max-width: 940px;
            margin: 0 auto;
            background: #fff;
            margin-top: -144px;
            padding-top: 132px;
            position: relative;
            z-index: 9;
            box-shadow: 0 1px 6px rgb(0 0 0 / 12%), 0 1px 4px rgb(0 0 0 / 24%);
            border-radius: 30px;
        }

        .app_logo {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            overflow: hidden;
            border-radius: 30px;
            border: solid 10px #fff;
        }

        .app_logo .img_container {
            display: block;
            width: max-content;
            height: max-content;
            overflow: hidden;
            box-shadow: 0 1px 2px 0 rgb(60 64 67 / 30%), 0 1px 3px 1px rgb(60 64 67 / 15%);
            background: #121212;
        }

        .app_logo .img_container img {
            display: block;
            width: 250px;
            height: 250px;
        }

        .app_name h1 {
            margin: 0;
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
            line-height: 1.5;
            direction: rtl;
        }

        .app_description p {
            margin: 0;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .mou-info-table {
            border-collapse: collapse;
            width: 100%;
        }

        .mou-info-table tr:not(:last-child) {
            border-bottom: 1px solid #ddd;
        }

        .mou-info-table tr:first-child {
            border-top: 1px solid #ddd;
        }

        .mou-info-table tr td {
            background: #0000;
            padding: 0.5rem;
        }

        .mou-info-table tr td:first-child {
            border-left: 1px solid #ddd;
        }

        .contact_container {
            text-align: center;
            background: linear-gradient(186deg, var(--first_background_color) 0%, var(--second_background_color) 49%, var(--second_background_color) 74%, var(--first_background_color) 100%);
            padding: 10px;
        }

        .contact_container a {
            display: inline-block;
            width: 40px;
            height: 40px;
            color: #fff;
            text-decoration: none;
            border-radius: 2px;
            transition: all 0.3s linear;
            -moz-transition: all 0.3s linear;
            -webkit-transition: all 0.3s linear;
            -o-transition: all 0.3s linear;
        }

        .contact_container a:hover {
            border-radius: 10px;
        }

        .contact_container i {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        a#whats_app {
            background: #0dc143;
        }

        a#gmail {
            background: #ea4335;
        }

        a#telegram {
            background: #29a9ea;
        }

        a#messanger {
            background: #0080f7;
        }

        .download_btn {
            display: block;
            background: var(--first_background_color);
            color: #fff;
            text-decoration: none;
            max-width: max-content;
            margin: 0.5rem auto;
            padding: 6px 16px;
            border-radius: 10px;
            text-align: center;
            max-width: 300px;
            width: 100%;
        }

        .download_btn img {
            width: 25px;
            height: 25px;
            align-items: center;
            display: flex;
            margin-right: 4px;
        }

        .code_container {
            display: none;
            position: relative;
            background: var(--first_background_color);
            border-radius: 20px;
            overflow: hidden;
            max-width: 300px;
            color: #fff;
            direction: ltr;
            padding: 1rem;
            margin: 1rem auto;
        }

        .code_container .code {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
            grid-template-rows: auto;
            grid-gap: 10px;
            padding: 0.5rem 0;
            justify-content: center;
        }

        .code_container .code .num {
            border-radius: 10px;
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            background-color: var(--elmnt_back);
        }

        .code_text,
        .code_btn {
            text-align: center;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            background-color: var(--elmnt_back);
        }

        .code_btn {
            cursor: pointer;
        }

        #create_active_code {
            display: none;
            background: #eb2f24;
            cursor: pointer;
        }

        #create_active_code.user_activated {
            background: #198754;
        }

        .mouscripts {
            margin: 0 auto;
            width: 100%;
            overflow: hidden;
            text-align: center;
        }

        .mouscripts iframe {
            display: block;
            margin: 0 auto;
        }

        .downloads_table {
            direction: ltr;
            width: 100%;
            border-collapse: collapse;
        }

        .downloads_table th,
        .downloads_table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .downloads_table th {
            background-color: #f4f4f4;
        }

        .downloads_table tr:hover {
            background-color: #f1f1f1;
        }

        .downloads_table .download_btn {
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .downloads_table .download_btn:not(:last-child) {
            margin-bottom: 0.5rem;
        }

        /* Responsive Styling */
        @media (max-width: 600px) {

            .app_logo .img_container img {
                width: 200px;
                height: 200px;
            }

            .header_box {
                padding-top: 100px;
                margin-top: -170px;
            }

            .downloads_table {
                border: 0;
            }

            .downloads_table thead {
                display: none;
            }

            .downloads_table tr {
                display: block;
                padding-top: 0.5rem;
            }

            .downloads_table tr:not(:last-child) {
                border-bottom: 2px solid #ddd;
            }

            .mou-info-table tr:last-child {
                border-bottom: 2px solid #ddd;
            }

            .downloads_table td {
                display: block;
                text-align: center;
                padding: 10px;
                border: none;
                position: relative;
            }

            .downloads_table td:not(:last-child) {
                border-bottom: 1px solid #ddd;
            }

            .downloads_table td:before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                font-weight: bold;
                text-align: left;
            }
        }

        .d-none {
            display: none !important;
        }

        .smart_tv_code {}

        .smart_tv_code .tv_code {
            padding: 2px 4px;
            margin: 0 6px;
            background: #f37623;
            border-radius: 4px;
        }

        :root {
            --bg-success-color: #198754;
            --mou_border_color2: #ffffffb0;
            --first_background_color: #121212;
            --second_background_color: #121212;
            --elmnt_back: #2b2c2c;
        }

        *,
        ::after,
        ::before {
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <div class="background-container">
    </div>

    <div class="header_box">
        <div class="app_logo">
            <div class="img_container">
                <img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEhcdm1nbCwa96TWKIaBVqOx6EleT7JaND9pWo1PWm8zGKS8txTHYUoYlsOe9TFuLERSVi0oFntgF23NOAUipFmY8aX5xZOHrsgZy0EteWOz40Y9X7N_udjfmg45k1NFKwEjJByVUCYAeI2KWalVg4xudpx-Z5JSAFk-KLHlQMPWmcYkxI92lAABfqxLmRo/s500/logo-500.png"
                    alt="تطبيق البطل" />
            </div>
        </div>

        <div class="app_name">
            <h1>البطل TV</h1>
        </div>
        <div class="app_description">
            <p>
                افضل تطبيق لمشاهدة المباريات والافلام والمسلسلات مجانا
            </p>
        </div>

        <a class="download_btn" id="create_active_code"><i class="fas fa-key"></i> انشاء كود تفعيل لحسابك</a>
        <div class="code_container">
            <div class="code_text">كود التفعيل</div>
            <div class="code" id="user_code"></div>
            <div class="code_btn" id="copy_code">نسخ</div>
        </div>

        
        <div id="container-6a1f1683757155a00898e6dc9912b407"></div>
        <table class="mou-info-table">
            <tbody>
                <tr>
                    <td>الفئة</td>
                    <td>تليفزيون , افلام , مسلسلات , مباريات</td>
                </tr>
                <tr>
                    <td>اصدار تطبيق الاندرويد</td>
                    <td>3.1
                    </td>
                </tr>
                <tr>
                    <td>اصدار تطبيق الويندوز</td>
                    <td>1.8.0</td>
                </tr>
                <tr>
                    <td>المتطلبات</td>
                    <td style="direction: ltr;text-align: right;">Android +5.0 , Windows7+</td>
                </tr>
                <tr>
                    <td>عدد التحميلات</td>
                    <td id="num_of_downs"></td>
                </tr>

            </tbody>
        </table>
        <table class="downloads_table">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Size</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Elbatal-TV.v3.1.apk</td>
                    <td>27.6 MB</td>
                    <td>
                        <a class="download_btn" href="https://www.elbatal-app.com/d/Elbatal-TV.v3.1.apk"
                            download><img src="download.png"> Direct Download</a>
                        <a class="download_btn"
                            href="https://www.mediafire.com/file/vgi8lx9zdg792br/Elbatal-TV.v3.1.apk/file"
                            target="_blank"><img src="mediafire.svg"> MediaFire</a>
                        <a class="download_btn d-none"
                            href="https://mega4upload.net/y67s9thxz7km"
                            target="_blank"><img src="mediafire.svg"> Mega4up</a>

                        <a class="download_btn smart_tv_code"><span>Smart Tv Code</span><span
                                class="tv_code">4696701</span></a>

                    </td>
                </tr>
                <tr class="d-none">
                    <td>ELPlayer.v1.4.apk</td>
                    <td>19.75 MB</td>
                    <td>
                        <?php if ($is_dev) { ?>

                        <?php } ?>
                        <a class="download_btn d-none"
                            href="https://play.google.com/store/apps/details?id=com.mouscripts.bplayer"
                            target="_blank"><img src="playstore.png"> PlayStore</a>

                        <a class="download_btn" href="https://cdn.jsdelivr.net/gh/Mouscripts/dl/ELPlayer.v1.4.apk"
                            download="https://cdn.jsdelivr.net/gh/Mouscripts/dl/ELPlayer.v1.4.apk"><img src="download.png"> Direct
                            Download</a>

                        <a class="download_btn"
                            href="https://www.mediafire.com/file/uawbo8imzj7xdph/ELPlayer.v1.4.apk/file"
                            target="_blank"><img src="mediafire.svg"> Mediafire</a>

                        <a class="download_btn smart_tv_code"><span>Smart Tv Code</span><span
                                class="tv_code">1039103</span></a>
                    </td>
                </tr>
                <tr>
                    <td>Elbatal-TV-Setup-1.8.0.exe</td>
                    <td>144.53 MB</td>
                    <td>
                        <a class="download_btn" href="https://www.elbatal-app.com/d/Elbatal-TV-Setup-1.8.0.exe"
                            download><img src="download.png"> Direct Download</a>
                        <a class="download_btn"
                            href="https://www.mediafire.com/file/lapwcj4ylpzge7x/Elbatal-TV-Setup-1.8.0.exe/file"
                            target="_blank"><img src="mediafire.svg"> Mediafire</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="contact_container">
            <a target="_blank" href="https://t.me/Elbatal_Tv" id="telegram" aria-label="telegram"><i
                    class="fab fa-telegram-plane fa-lg"></i></a>
            <a target="_blank" href="mail:mouscripts@gmail.com" id="gmail" aria-label="gmail"><i
                    class="far fa-envelope fa-lg"></i></a>
        </div>
    </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="pop-under.js"></script>
    <?php if (!$is_dev) { ?>
        <script>
            (function(global, factory) {
                typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
                    typeof define === 'function' && define.amd ? define(['exports'], factory) :
                    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.ConsoleBan = {}));
            }(this, (function(exports) {
                'use strict';

                /*! *****************************************************************************
                Copyright (c) Microsoft Corporation.
    
                Permission to use, copy, modify, and/or distribute this software for any
                purpose with or without fee is hereby granted.
    
                THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
                REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
                AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
                INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
                LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
                OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
                PERFORMANCE OF THIS SOFTWARE.
                ***************************************************************************** */

                var __assign = function() {
                    __assign = Object.assign || function __assign(t) {
                        for (var s, i = 1, n = arguments.length; i < n; i++) {
                            s = arguments[i];
                            for (var p in s)
                                if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
                        }
                        return t;
                    };
                    return __assign.apply(this, arguments);
                };

                var defaultOptions = {
                    clear: true,
                    debug: true,
                    debugTime: 3000
                };

                /**
                 * 处理 URL 补全
                 * @example '' -> /
                 * @example path -> /path
                 * @example /path -> /path
                 * @param url
                 */
                function completion(url) {
                    if (!url) return '/';
                    return url[0] !== '/' ? "/" + url : url;
                }

                var ConsoleBan =
                    /** @class */
                    function() {
                        function ConsoleBan(option) {
                            var _a = __assign(__assign({}, defaultOptions), option),
                                clear = _a.clear,
                                debug = _a.debug,
                                debugTime = _a.debugTime,
                                callback = _a.callback,
                                redirect = _a.redirect,
                                write = _a.write;

                            this._debug = debug;
                            this._debugTime = debugTime;
                            this._clear = clear;
                            this._callback = callback;
                            this._redirect = redirect;
                            this._write = write;
                        }

                        ConsoleBan.prototype.clear = function() {
                            if (this._clear) {
                                console.clear = function() {};
                            }
                        };

                        ConsoleBan.prototype.debug = function() {
                            if (this._debug) {
                                var db = new Function('debugger');
                                setInterval(db, this._debugTime);
                            }
                        };

                        ConsoleBan.prototype.redirect = function() {
                            if (!this._redirect) {
                                return;
                            } // 绝对地址


                            if (!!~this._redirect.indexOf('http')) {
                                location.href !== this._redirect ? location.href = this._redirect : null;
                                return;
                            } // 相对地址


                            var path = location.pathname + location.search;

                            if (completion(this._redirect) === path) {
                                return;
                            }

                            location.href = this._redirect;
                        };

                        ConsoleBan.prototype.callback = function() {
                            var _this = this;

                            if (!this._callback && !this._redirect && !this._write) {
                                return;
                            }

                            var img = new Image();
                            Object.defineProperty(img, 'id', {
                                get: function get() {
                                    // callback
                                    if (_this._callback) {
                                        _this._callback.call(null);

                                        return;
                                    } // redirect


                                    _this.redirect();

                                    if (_this._redirect) {
                                        return;
                                    } // write


                                    _this.write();
                                }
                            });
                            console.log(img);
                        };

                        ConsoleBan.prototype.write = function() {
                            if (this._write) {
                                document.body.innerHTML = typeof this._write === 'string' ? this._write : this._write.innerHTML;
                            }
                        };

                        ConsoleBan.prototype.ban = function() {
                            // callback
                            this.callback(); // clear console.clear

                            this.clear(); // debug init

                            this.debug();
                        };

                        return ConsoleBan;
                    }();

                function init(option) {
                    var instance = new ConsoleBan(option);
                    instance.ban();
                }

                exports.default = init;
                exports.init = init;

                Object.defineProperty(exports, '__esModule', {
                    value: true
                });

            })));
            ConsoleBan.init({
                redirect: '/404'
            })
        </script>
    <?php } ?>
    <script>
        // var popURL = "https://www.profitablecpmrate.com/hx9q3r3n?key=ce764b0d8e77587e570f5e861a47f5f5";
        // function onPopUnderLoaded() {
        //     this.useTabUnder(true).pop(popURL).frequency(1, 6);
        // }
        $(document).ready(function() {
            downloads_num = beauty_num(192456);
            $("#num_of_downs").text(downloads_num);

            if (getQueryVariable("code") !== false) {
                code = getQueryVariable("code");
                if (code.length == 6) {
                    $(".code_container").show();
                    for (i = 0; i < code.length; i++) {
                        num = code[i];
                        $("#user_code").append(`<span class="num">${num}</span>`);
                    }
                    if (typeof window.electron !== "undefined") {
                        ipcRenderer.send("activation_code", code);
                        window.close();
                    }
                }
            } 
            if (getQueryVariable("getCode") !== false) {
                $("#create_active_code").css("display", "block");
                user_id = getQueryVariable("getCode");
            }
            $("#copy_code").click(function() {
                code_will_copy = $("#user_code").text();
                copyTextToClipboard(code_will_copy, function(status) {
                    if (status == true) {
                        alert("تم نسخ الكود");
                    }
                });
            });
            can_req_code = true;
            $("#create_active_code").click(function() {
                if (user_id == "ID") {
                    user_id_input = prompt("ادخل المعرف الخاص بحسابك .");
                    if (user_id_input !== null && user_id_input !== "") {
                        user_id = user_id_input;
                    } else {
                        alert("يرجي التحقق من المعرف الخاص بك");
                        return false;;
                    }
                }

                if (can_req_code == true && !$("#create_active_code").hasClass("user_activated")) {
                    can_req_code = false;
                    $("#create_active_code").html(`<i class="fas fa-circle-notch fa-spin"></i> جاري انشاء رابط التفعيل`);
                    // var windowReference = window.open();
                    $.ajax({
                        url: "https://new.elbatal-app.com/users/accounts/code_1.php",
                        type: 'POST',
                        timeout: 30 * 1000,
                        data: {
                            action: "get_code",
                            uid: user_id
                        },
                        success: function(data) {
                            can_req_code = true;
                            $("#create_active_code").html(`<i class="fas fa-key"></i> انشاء كود تفعيل لحسابك`);
                            // console.log(JSON.stringify(data));

                            for (var i = 0; i < Object.keys(data.messages).length; i++) {
                                message_code = Object.keys(data.messages)[i];
                                message = data.messages[message_code];
                                if (data.status == true) {
                                    if (message_code == 200) {
                                        short_url = data.messages.short_link;
                                        // window.open(short_url);
                                        // windowReference.location = short_url;
                                        setTimeout(() => {
                                            // window.open(short_url, "_blank");
                                        });
                                        location.href = short_url;
                                    } else if (message_code == "201") {
                                        $("#create_active_code").addClass("user_activated").html(`<i class="fas fa-check"></i> تم تفعيل حسابك . قم بالرجوع الي التطبيق`);
                                    }
                                } else {
                                    alert(message);
                                }

                            }

                        },
                        error: function(jqXHR, error, errorThrown) {
                            can_req_code = true;

                            // alert(jqXHR.responseText);

                        }
                    });
                }

            });
        })

        function copyTextToClipboard(text, callback) {
            text_area = $(`<textarea style="position:fixed;"></textarea>`);
            $(text_area).text(text);
            $(text_area).appendTo("body");
            $(text_area).focus();
            $(text_area).select();
            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                console.log('Copying text command was ' + msg);
                $(text_area).remove();
                if (successful) {
                    callback(true);
                } else {
                    callback(false);
                }
            } catch (err) {
                $(text_area).remove();
                console.log('Oops, unable to copy');
                callback(false);
            }
        }

        function beauty_num(num, digits) {
            const lookup = [{
                    value: 1,
                    symbol: ""
                },
                {
                    value: 1e3,
                    symbol: "k"
                },
                {
                    value: 1e6,
                    symbol: "M"
                },
                {
                    value: 1e9,
                    symbol: "G"
                },
                {
                    value: 1e12,
                    symbol: "T"
                },
                {
                    value: 1e15,
                    symbol: "P"
                },
                {
                    value: 1e18,
                    symbol: "E"
                }
            ];
            const rx = /\.0+$|(\.[0-9]*[1-9])0+$/;
            var item = lookup.slice().reverse().find(function(item) {
                return num >= item.value;
            });
            return item ? (num / item.value).toFixed(digits).replace(rx, "$1") + item.symbol : "0";
        }

        function getQueryVariable(variable, meth = 1, link = "") {
            if (meth == 1) {
                var query = window.location.search.substring(1);
            } else {
                var query = link.split("?")[1];
            }
            var vars = query.split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (pair[0] == variable) {
                    return pair[1];
                }
            }
            return (false);
        }
    </script>

</body>

</html>