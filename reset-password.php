<?php
$lockFile = __DIR__ . '/installed.lock';
if (!file_exists($lockFile)) {
    header('Location: install.php');
    exit;
}
$token = isset($_GET['token']) ? $_GET['token'] : '';
require_once __DIR__ . '/config.php';
$pdo = requireDB();
$siteName = 'Mou Default';
try {
    $stmt = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'site_name'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['value'] !== '') $siteName = $row['value'];
} catch (PDOException $e) {}
$siteName = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html class="dark" id="reset-html">
<head>
<script>
  (function(){
    var l=localStorage.getItem('lang')||'ar';
    document.documentElement.lang=l;
    document.documentElement.dir=l==='ar'?'rtl':'ltr';
  })();
</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title data-i18n="common.reset_password">إعادة تعيين كلمة المرور</title>
<link rel="icon" href="favicon.ico">
<link rel="stylesheet" href="assets/fonts/font-awesome/css/all.min.css">
<link rel="stylesheet" href="assets/fonts/noto-sans-arabic/font.css">
<link rel="stylesheet" href="assets/css/root.css">
<link rel="stylesheet" href="assets/css/light-theme.css">
<script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.className=document.documentElement.className.replace(/\b(dark|light)\b/g,'').trim();document.documentElement.classList.add(t)})();</script>
<style>
html { background:#0d0d0e !important; width:100%; height:100%; margin:0; padding:0; }
body {
  font-family:'Noto Sans Arabic',sans-serif;
  background:radial-gradient(circle at center,#1c1808 0%,#0d0d0e 80%) !important;
  color:#f5f5f7 !important; display:flex; justify-content:center; align-items:center;
  margin:0; padding:20px; width:100%; min-height:100vh; overflow:hidden; position:relative;
}
.bg-glow-1 {
  position:absolute; width:250px; height:250px;
  background:radial-gradient(circle,rgba(255,204,0,0.15) 0%,transparent 70%) !important;
  top:20%; left:15%; z-index:1; filter:blur(40px);
  animation:floatGlow 8s infinite alternate ease-in-out;
}
.bg-glow-2 {
  position:absolute; width:300px; height:300px;
  background:radial-gradient(circle,rgba(255,153,0,0.12) 0%,transparent 70%) !important;
  bottom:15%; right:10%; z-index:1; filter:blur(50px);
  animation:floatGlow 10s infinite alternate-reverse ease-in-out;
}
@keyframes floatGlow {
  0% { transform:translateY(0) scale(1); }
  100% { transform:translateY(-20px) scale(1.1); }
}
.reset-container {
  background:rgba(18,18,20,0.75) !important;
  backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px);
  padding:35px 30px; border-radius:24px;
  border:1px solid rgba(255,204,0,0.15) !important;
  box-shadow:0 20px 50px rgba(0,0,0,0.6), inset 0 1px 2px rgba(255,255,255,0.05);
  width:100%; max-width:380px; text-align:center; z-index:10;
  transition:all 0.3s ease;
}
.logo-section { margin-bottom:30px; display:flex; flex-direction:column; align-items:center; gap:12px; }
.logo-img {
  width:72px; height:72px; border-radius:18px; object-fit:cover;
  border:2px solid rgba(255,204,0,0.6);
  box-shadow:0 8px 24px rgba(255,204,0,0.3);
  animation:logoPulse 4s infinite alternate;
}
@keyframes logoPulse {
  0% { transform:scale(1); box-shadow:0 8px 24px rgba(255,204,0,0.25); }
  100% { transform:scale(1.05); box-shadow:0 12px 30px rgba(255,204,0,0.45); }
}
.app-name {
  font-size:1.6rem; font-weight:800; margin:0; letter-spacing:0.5px;
  background:linear-gradient(135deg,#ffffff 30%,#e0e0e0 70%,#ffcc00 100%);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.app-tagline { font-size:0.8rem; color:#9e9e9e !important; margin:-5px 0 0 0; letter-spacing:0.5px; }
.input-group { margin-bottom:22px; text-align:right; }
.input-group label { display:block; margin-bottom:8px; font-size:13px; font-weight:500; color:#d1d1d6 !important; padding-right:4px; }
.input-wrapper { position:relative; display:flex; align-items:center; }
.input-wrapper i { position:absolute; right:14px; color:#777; font-size:1.1rem; transition:color 0.3s; z-index:2; }
.input-wrapper input {
  width:100%; padding:13px 45px 13px 15px;
  border-radius:12px; border:1px solid rgba(255,255,255,0.08) !important;
  background:rgba(255,255,255,0.04) !important; color:white !important;
  box-sizing:border-box; outline:none; font-size:0.95rem;
  transition:all 0.3s ease; font-family:inherit;
}
.input-wrapper input:focus {
  border-color:rgba(255,204,0,0.6) !important;
  background:rgba(255,255,255,0.07) !important;
  box-shadow:0 0 12px rgba(255,204,0,0.15) !important;
}
.input-wrapper input:focus + i { color:#ffcc00 !important; }
#submit-btn {
  width:100%; padding:14px; border-radius:12px; border:none;
  background:linear-gradient(135deg,#ffcc00,#ff9900) !important;
  color:#0b0a02 !important; font-size:1rem; font-weight:700;
  cursor:pointer; transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
  box-shadow:0 4px 15px rgba(255,204,0,0.2); font-family:inherit; margin-top:10px;
}
#submit-btn:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(255,204,0,0.4); }
#submit-btn:active { transform:translateY(1px); }
#submit-btn:disabled { opacity:0.5; cursor:not-allowed; transform:none; }
#error-msg {
  background:rgba(239,68,68,0.1) !important; border:1px solid rgba(239,68,68,0.2) !important;
  color:#ef4444 !important; font-size:13px; font-weight:500;
  padding:10px 12px; border-radius:10px; margin-top:20px;
  display:none; align-items:center; justify-content:center; gap:8px;
  animation:shakeError 0.4s ease;
}
#success-msg {
  background:rgba(16,185,129,0.1) !important; border:1px solid rgba(16,185,129,0.2) !important;
  color:#10b981 !important; font-size:13px; font-weight:500;
  padding:10px 12px; border-radius:10px; margin-top:20px;
  display:none; align-items:center; justify-content:center; gap:8px;
}
@keyframes shakeError {
  0%,100% { transform:translateX(0); }
  25% { transform:translateX(-6px); }
  75% { transform:translateX(6px); }
}
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-box-shadow:0 0 0px 1000px #1c1c1e inset !important;
  -webkit-text-fill-color:white !important;
  transition:background-color 50000s ease-in-out 0s;
}
.back-link {
  margin-top: 20px; font-size: 0.85rem; color: #9e9e9e;
}
.back-link a {
  color: #ffcc00; text-decoration: none; font-weight: 600; transition: color 0.3s;
}
.back-link a:hover { color: #ff9900; text-decoration: underline; }
.toggle-wrap {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:18px; padding:10px 14px;
  background:rgba(255,255,255,0.04); border-radius:10px;
  border:1px solid rgba(255,255,255,0.06);
}
.toggle-wrap label { font-size:0.85rem; color:#d1d1d6; cursor:pointer; font-weight:500; }
.toggle-switch {
  position:relative; width:44px; height:24px; flex-shrink:0;
  background:rgba(255,255,255,0.1); border-radius:12px;
  cursor:pointer; transition:background 0.3s;
}
.toggle-switch.active { background:rgba(255,204,0,0.6); }
.toggle-switch::after {
  content:''; position:absolute; top:2px; width:20px; height:20px;
  border-radius:50%; background:#fff; transition:all 0.3s;
  box-shadow:0 2px 4px rgba(0,0,0,0.3);
}
.toggle-switch.active::after { background:#ffcc00; }
.toggle-switch[dir="rtl"]::after { right:2px; }
.toggle-switch[dir="rtl"].active::after { right:22px; }
.toggle-switch[dir="ltr"]::after { left:2px; }
.toggle-switch[dir="ltr"].active::after { left:22px; }
</style>
</head>
<body>
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="reset-container">
  <div class="logo-section">
    <img class="logo-img" src="assets/img/logo_192.png" alt="Logo">
    <h1 class="app-name"><?php echo $siteName; ?></h1>
    <p class="app-tagline" data-i18n="app.tagline">نظام الإدارة المتكامل</p>
  </div>

  <form id="reset-form" autocomplete="off" <?php if (!$token) echo 'style="display:none"'; ?>>
    <p style="color:#9e9e9e;font-size:0.85rem;margin-bottom:24px;line-height:1.6;" data-i18n="common.reset_desc">أدخل كلمة المرور الجديدة</p>

    <div class="input-group">
      <label for="password" data-i18n="common.new_password">كلمة المرور الجديدة</label>
      <div class="input-wrapper">
        <input type="password" id="password" data-i18n-placeholder="placeholder.password_mask" placeholder="••••••••" required autocomplete="new-password">
        <i class="fas fa-lock"></i>
      </div>
    </div>

    <div class="input-group">
      <label for="confirm-password" data-i18n="common.confirm_password">تأكيد كلمة المرور</label>
      <div class="input-wrapper">
        <input type="password" id="confirm-password" data-i18n-placeholder="placeholder.password_mask" placeholder="••••••••" required autocomplete="new-password">
        <i class="fas fa-lock"></i>
      </div>
    </div>

    <div class="toggle-wrap">
      <label for="reset-devices-toggle" data-i18n="common.reset_devices">تسجيل الخروج من جميع الأجهزة</label>
      <div id="reset-devices-toggle" class="toggle-switch" role="button" tabindex="0" dir="rtl"></div>
    </div>

    <button type="submit" id="submit-btn" data-i18n="common.reset_password_btn">تغيير كلمة المرور</button>

    <div class="back-link" style="margin-top:20px;">
      <a href="login.php" data-i18n="common.back_to_login">العودة إلى تسجيل الدخول</a>
    </div>
  </form>

  <div id="error-msg"><i class="fas fa-exclamation-circle"></i> <span></span></div>
  <div id="success-msg"><i class="fas fa-check-circle"></i> <span></span></div>

  <div id="no-token-msg" style="<?php echo $token ? 'display:none' : ''; ?>">
    <p style="color:#ef4444;font-size:0.9rem;" data-i18n="common.invalid_reset_link">رابط إعادة التعيين غير صالح</p>
    <div class="back-link">
      <a href="login.php" data-i18n="common.back_to_login">العودة إلى تسجيل الدخول</a>
    </div>
  </div>
</div>

<script src="assets/js/i18n.js"></script>
<script src="assets/js/jquery.min.js"></script>
<script>
$(document).ready(function () {
  if (typeof i18n !== 'undefined') i18n.translateDOM();
  if (typeof i18n !== 'undefined') i18n.initTheme();
  var dir = document.documentElement.dir || 'rtl';
  $('#reset-devices-toggle').attr('dir', dir);
});

var resetToken = '<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>';
var resetDevices = false;

$('#reset-devices-toggle').on('click', function() {
  $(this).toggleClass('active');
  resetDevices = $(this).hasClass('active');
});

$('#reset-form').on('submit', function(e) {
  e.preventDefault();
  var password = $('#password').val().trim();
  var confirmPassword = $('#confirm-password').val().trim();
  var errorEl = document.getElementById('error-msg');
  var successEl = document.getElementById('success-msg');
  var btn = $('#submit-btn');

  errorEl.style.display = 'none';
  successEl.style.display = 'none';

  if (!password || !confirmPassword) {
    errorEl.querySelector('span').textContent = 'يرجى ملء جميع الحقول';
    errorEl.style.display = 'flex';
    return false;
  }

  if (password !== confirmPassword) {
    errorEl.querySelector('span').textContent = 'كلمة المرور غير متطابقة';
    errorEl.style.display = 'flex';
    return false;
  }

  if (password.length < 6) {
    errorEl.querySelector('span').textContent = 'كلمة المرور قصيرة جداً (6 أحرف على الأقل)';
    errorEl.style.display = 'flex';
    return false;
  }

  btn.prop('disabled', true).text('جاري التغيير...');

  $.ajax({
    url: 'api.php?action=verify_reset_token',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ token: resetToken, password: password, reset_devices: resetDevices }),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        $('#reset-form').hide();
        successEl.querySelector('span').textContent = res.message || 'تم تغيير كلمة المرور';
        successEl.style.display = 'flex';
      } else {
        errorEl.querySelector('span').textContent = res.error || 'حدث خطأ';
        errorEl.style.display = 'flex';
      }
    },
    error: function() {
      errorEl.querySelector('span').textContent = 'خطأ في الاتصال بالخادم';
      errorEl.style.display = 'flex';
    },
    complete: function() {
      btn.prop('disabled', false).text('تغيير كلمة المرور');
    }
  });
  return false;
});
</script>
</body>
</html>
