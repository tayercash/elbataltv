<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (empty($_SESSION["can_join"]) || $_SESSION["can_join"] !== true) {
    header("Location: ../admen/login.php");
    die();
}

include_once __DIR__ . '/../config_db.php';

$page_title = "إدارة التحميلات";
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../files/fonts/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="../files/fonts/Poppins/font.css">
    <link rel="stylesheet" href="../files/fonts/noto-sans-arabic/font.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <div class="topbar-right">
            <i class="fas fa-download"></i>
            <h1><?php echo $page_title; ?></h1>
        </div>
        <div class="topbar-left">
            <a href="../admen/index.php" class="btn btn-light"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a>
            <a href="../admen/logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
        </div>
    </header>

    <main class="container">
        <div class="refresh-note">
            <span class="live-dot"></span>
            يتم التحديث تلقائيًا كل <b id="refresh_interval_label">3</b> ثواني
            <button class="btn btn-ghost" onclick="refreshNow()"><i class="fas fa-sync-alt"></i> تحديث الآن</button>
        </div>

        <section class="stats" id="stats_cards">
            <div class="stat-card" data-status="downloading">
                <div class="stat-icon blue"><i class="fas fa-spinner fa-spin"></i></div>
                <div class="stat-info">
                    <span class="stat-value" id="stat_downloading">0</span>
                    <span class="stat-label">قيد التحميل</span>
                </div>
            </div>
            <div class="stat-card" data-status="queued">
                <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <span class="stat-value" id="stat_queued">0</span>
                    <span class="stat-label">في الانتظار</span>
                </div>
            </div>
            <div class="stat-card" data-status="completed">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <span class="stat-value" id="stat_completed">0</span>
                    <span class="stat-label">مكتملة</span>
                </div>
            </div>
            <div class="stat-card" data-status="error">
                <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <span class="stat-value" id="stat_error">0</span>
                    <span class="stat-label">أخطاء</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-gauge-high"></i></div>
                <div class="stat-info">
                    <span class="stat-value" id="stat_speed">0 B/s</span>
                    <span class="stat-label">السرعة الحالية</span>
                </div>
            </div>
        </section>

        <section class="card" id="settings_card">
            <div class="card-header">
                <i class="fas fa-cog"></i>
                <h2>إعدادات التحميل</h2>
            </div>
            <div class="card-body settings-grid">
                <div class="field">
                    <label for="max_concurrent">عدد التحميلات المتزامنة</label>
                    <input type="number" id="max_concurrent" min="1" max="50" value="3">
                    <small>أقصى عدد من الملفات يتم تحميلها في نفس الوقت</small>
                </div>
                <div class="field">
                    <label class="switch-label">
                        <input type="checkbox" id="downloads_enabled" class="switch">
                        <span class="slider"></span>
                        <span>تفعيل نظام التحميل</span>
                    </label>
                    <small>عند إيقافه، تتوقف كل الأجهزة عن بدء تحميلات جديدة</small>
                </div>
                <div class="field">
                    <label class="switch-label">
                        <input type="checkbox" id="pause_all" class="switch">
                        <span class="slider"></span>
                        <span>إيقاف مؤقت للكل</span>
                    </label>
                    <small>يجبر كل الأجهزة على إيقاف التحميل الحالي فورًا</small>
                </div>
                <div class="field">
                    <label for="max_retries">أقصى عدد محاولات</label>
                    <input type="number" id="max_retries" min="0" max="10" value="3">
                    <small>عدد مرات إعادة محاولة التحميل عند الفشل</small>
                </div>
                <div class="field full">
                    <button class="btn btn-primary" onclick="saveSettings()"><i class="fas fa-save"></i> حفظ الإعدادات</button>
                    <span id="settings_status" class="save-status"></span>
                </div>
            </div>
        </section>

        <section class="card" id="add_card">
            <div class="card-header">
                <i class="fas fa-plus-circle"></i>
                <h2>إضافة تحميل جديد</h2>
            </div>
            <div class="card-body add-grid">
                <div class="field">
                    <label for="add_title">اسم الملف</label>
                    <input type="text" id="add_title" placeholder="مثال: فيلم البطل - جودة عالية">
                </div>
                <div class="field">
                    <label for="add_link">رابط الملف</label>
                    <input type="text" id="add_link" placeholder="https://.../video.mp4" dir="ltr">
                </div>
                <div class="field">
                    <label for="add_ext">الامتداد (اختياري)</label>
                    <input type="text" id="add_ext" placeholder="mp4" dir="ltr">
                </div>
                <div class="field">
                    <label for="add_headers">هيدرز مخصصة (JSON اختياري)</label>
                    <input type="text" id="add_headers" placeholder='{"Referer":"https://..."}' dir="ltr">
                </div>
                <div class="field">
                    <label for="add_device">device_id للجهاز (اختياري)</label>
                    <input type="text" id="add_device" placeholder="dev-xxxx (يُترك فارغًا للإدارة)" dir="ltr">
                </div>
                <div class="field full">
                    <button class="btn btn-primary" onclick="addJob()"><i class="fas fa-plus"></i> إضافة</button>
                    <span id="add_status" class="save-status"></span>
                </div>
            </div>
        </section>

        <section class="card" id="table_card">
            <div class="card-header">
                <i class="fas fa-list"></i>
                <h2>قائمة التحميلات</h2>
                <div class="table-filters">
                    <select id="filter_status" onchange="loadDownloads()">
                        <option value="">كل الحالات</option>
                        <option value="downloading">قيد التحميل</option>
                        <option value="queued">في الانتظار</option>
                        <option value="paused">متوقف مؤقتًا</option>
                        <option value="completed">مكتملة</option>
                        <option value="error">خطأ</option>
                        <option value="cancelled">ملغاة</option>
                    </select>
                    <input type="text" id="filter_search" placeholder="بحث..." oninput="debouncedLoad()">
                </div>
            </div>
            <div class="card-body table-wrap">
                <table class="downloads-table">
                    <thead>
                        <tr>
                            <th>الملف</th>
                            <th>الحجم</th>
                            <th>التقدم</th>
                            <th>السرعة</th>
                            <th>الجهاز</th>
                            <th>الحالة</th>
                            <th>الوقت</th>
                            <th>تحكم</th>
                        </tr>
                    </thead>
                    <tbody id="downloads_tbody">
                        <tr><td colspan="8" class="empty">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="../files/js/jquery.min.js"></script>
    <script src="js/panel.js"></script>
</body>
</html>
