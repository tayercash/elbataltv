<?php
$lockFile = __DIR__ . '/installed.lock';
if (!file_exists($lockFile)) {
    header('Location: install.php');
    exit;
}
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
<html class="dark" id="login-html">
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
<script>var BASE_PATH = '<?php echo BASE_PATH; ?>';</script>
<title data-i18n="common.login">تسجيل الدخول</title>
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
.login-container {
  background:rgba(18,18,20,0.78) !important;
  backdrop-filter:blur(18px); -webkit-backdrop-filter:blur(18px);
  padding:0; border-radius:28px;
  border:1px solid rgba(255,204,0,0.16) !important;
  box-shadow:0 24px 70px rgba(0,0,0,0.68), inset 0 1px 2px rgba(255,255,255,0.05);
  width:100%; max-width:860px; text-align:center; z-index:10;
  transition:all 0.3s ease;
  display:grid; grid-template-columns:minmax(280px,0.9fr) minmax(340px,1.1fr);
  overflow:hidden;
}
.login-container:hover {
  border-color:rgba(255,204,0,0.3) !important;
  box-shadow:0 25px 60px rgba(255,204,0,0.05),0 20px 50px rgba(0,0,0,0.7);
}
.logo-section {
  margin:0; padding:42px 34px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:14px;
  min-height:100%; position:relative; overflow:hidden;
  background:radial-gradient(circle at top,#3a2c03 0%,rgba(255,204,0,0.08) 34%,rgba(255,255,255,0.02) 100%);
  border-inline-end:1px solid rgba(255,204,0,0.12);
}
.logo-section::before {
  content:""; position:absolute; width:220px; height:220px; border-radius:50%;
  background:radial-gradient(circle,rgba(255,204,0,0.18),transparent 68%);
  top:-55px; inset-inline-start:-55px; filter:blur(4px);
}
.logo-section::after {
  content:""; position:absolute; width:160px; height:160px; border-radius:42px;
  border:1px solid rgba(255,204,0,0.12); transform:rotate(18deg);
  bottom:-60px; inset-inline-end:-35px;
}
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
.login-container form { padding:42px 38px; text-align:right; }
.login-container .register-link,
.login-container #google-login-section,
.login-container #error-msg { text-align:center; }
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
.register-link {
  margin-top: 20px; font-size: 0.85rem; color: #9e9e9e;
}
.register-link a {
  color: #ffcc00; text-decoration: none; font-weight: 600; transition: color 0.3s;
}
.register-link a:hover { color: #ff9900; text-decoration: underline; }
.lang-btn.active { background:rgba(255,204,0,0.15) !important; color:#ffcc00 !important; border-color:rgba(255,204,0,0.3) !important; }
.lang-btn:hover { background:rgba(255,255,255,0.05); color:#ccc; }
@media (max-width:760px) {
  body { align-items:flex-start; overflow-y:auto; }
  .login-container { max-width:420px; grid-template-columns:1fr; }
  .logo-section { padding:30px 22px 24px; border-inline-end:none; border-bottom:1px solid rgba(255,204,0,0.12); }
  .login-container form { padding:28px 22px 30px; }
}
</style>
</head>
<body>
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="login-container">
  <div class="logo-section">
    <img class="logo-img" src="assets/img/logo_192.png" alt="Logo">
    <h1 class="app-name"><?php echo $siteName; ?></h1>
    <p class="app-tagline" data-i18n="app.tagline">نظام الإدارة المتكامل</p>
    <div class="lang-toggle" style="display:flex;gap:6px;margin-top:5px;direction: ltr;">
      <button class="lang-btn" data-lang="ar" onclick="i18n.setLang('ar')" style="padding:4px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);background:transparent;color:#888;cursor:pointer;font-size:0.75rem;font-weight:600;transition:all 0.3s;font-family:inherit;">AR</button>
      <button class="lang-btn" data-lang="en" onclick="i18n.setLang('en')" style="padding:4px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);background:transparent;color:#888;cursor:pointer;font-size:0.75rem;font-weight:600;transition:all 0.3s;font-family:inherit;">EN</button>
    </div>
  </div>

  <form id="login-form" autocomplete="off">
    <input type="text" name="fake_user" style="display:none" tabindex="-1">
    <input type="password" name="fake_pass" style="display:none" tabindex="-1">

    <div class="input-group">
      <label for="email" data-i18n="common.email">البريد الإلكتروني أو رقم المستخدم</label>
      <div class="input-wrapper">
        <input type="text" id="email" data-i18n-placeholder="placeholder.email" placeholder="أدخل البريد الإلكتروني أو رقم المستخدم" required>
        <i class="fas fa-envelope"></i>
      </div>
    </div>

    <div class="input-group">
      <label for="password" data-i18n="common.password">كلمة المرور</label>
      <div class="input-wrapper">
        <input type="password" id="password" data-i18n-placeholder="placeholder.password_mask" placeholder="••••••••" required autocomplete="new-password">
        <i class="fas fa-lock"></i>
      </div>
    </div>

    <button type="submit" id="submit-btn" data-i18n="common.login">تسجيل الدخول</button>

    <div id="google-login-section" style="display:none;margin-top:20px;">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <hr style="flex:1;border:none;border-top:1px solid rgba(255,255,255,0.08);">
        <span style="font-size:0.8rem;color:#666;white-space:nowrap;" data-i18n="common.or">أو</span>
        <hr style="flex:1;border:none;border-top:1px solid rgba(255,255,255,0.08);">
      </div>
      <div id="google-button-container" style="display:flex;justify-content:center;"></div>
    </div>

    <div class="register-link">
      <span data-i18n="common.no_account">ليس لديك حساب؟</span>
      <a href="register.php" data-i18n="common.register">إنشاء حساب</a>
    </div>

    <div style="margin-top:12px;font-size:0.8rem;text-align:center;">
      <a href="forgot-password.php" data-i18n="common.forgot_password" style="color:#888;text-decoration:none;transition:color 0.3s;" onmouseover="this.style.color='#ffcc00'" onmouseout="this.style.color='#888'">نسيت كلمة المرور؟</a>
    </div>

    <div id="error-msg"><i class="fas fa-exclamation-circle"></i> <span data-i18n="common.login_error">خطأ في البريد الإلكتروني أو كلمة المرور</span></div>
  </form>
</div>

<script src="assets/js/i18n.js"></script>
<script src="assets/js/jquery.min.js"></script>
<script>
function collectDeviceInfo() {
  var info = {
    platform: navigator.platform,
    userAgent: navigator.userAgent,
    language: navigator.language,
    screenWidth: screen.width,
    screenHeight: screen.height,
    hardwareConcurrency: navigator.hardwareConcurrency || '',
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    hwid: btoa(navigator.userAgent + screen.width + screen.height + navigator.language)
  };
  return info;
}

$(document).ready(function () {
  if (typeof i18n !== 'undefined') i18n.translateDOM();
  if (typeof i18n !== 'undefined') i18n.initTheme();
  var lang = localStorage.getItem('lang') || 'ar';
  $('.lang-btn').filter('[data-lang="' + lang + '"]').addClass('active');
});

$('#login-form').on('submit', function(e) {
  e.preventDefault();
  var email = $('#email').val().trim();
  var password = $('#password').val().trim();
  var errorEl = document.getElementById('error-msg');
  var btn = $('#submit-btn');

  if (!email || !password) {
    errorEl.style.display = 'flex';
    return false;
  }

  errorEl.style.display = 'none';
  btn.prop('disabled', true).text('جاري تسجيل الدخول...');

  var deviceInfo = collectDeviceInfo();

  $.ajax({
    url: 'api.php?action=login',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ email: email, password: password, device_info: deviceInfo }),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        localStorage.setItem('username', res.user.username);
        localStorage.setItem('userId', res.user.id);
        localStorage.setItem('userRole', res.user.role);
        localStorage.setItem('userAvatar', res.user.avatar || '');
        localStorage.setItem('userToken', res.user.token || '');
        localStorage.setItem('deviceHwid', deviceInfo.hwid || '');
        window.location.href = 'dashboard/';
      } else if (res.needs_verification) {
        localStorage.setItem('pendingVerificationUserId', res.user_id);
        window.location.href = 'register.php?verify=' + encodeURIComponent(res.email);
      } else {
        errorEl.querySelector('span').textContent = res.error || 'خطأ في البريد الإلكتروني أو كلمة المرور';
        errorEl.style.display = 'flex';
      }
    },
    error: function() {
      errorEl.querySelector('span').textContent = 'خطأ في الاتصال بالخادم';
      errorEl.style.display = 'flex';
    },
    complete: function() {
      btn.prop('disabled', false).text('تسجيل الدخول');
    }
  });
  return false;
});

// ==================== Google Login ====================
(function() {
  function getGoogleLocale() {
    return (localStorage.getItem('lang') || 'ar') === 'en' ? 'en' : 'ar';
  }

  function initGoogleLogin() {
    $.get('api.php?action=get_setting&key=google_client_id', function(res) {
      var clientId = res.value || '';
      if (!clientId) return;

      $.get('api.php?action=get_setting&key=google_login_enabled', function(enabledRes) {
        if (enabledRes.value !== '1') return;

        $('#google-login-section').show();

        var googleLocale = getGoogleLocale();
        var script = document.createElement('script');
        script.src = 'https://accounts.google.com/gsi/client?hl=' + googleLocale;
        script.async = true;
        script.defer = true;
        script.onload = function() {
          google.accounts.id.initialize({
            client_id: clientId,
            callback: handleGoogleCredential
          });
          google.accounts.id.renderButton(
            document.getElementById('google-button-container'),
            { theme: 'outline', size: 'large', type: 'standard', shape: 'pill', text: 'signin_with', locale: googleLocale }
          );
        };
        document.head.appendChild(script);
      });
    });
  }

  window.handleGoogleCredential = function(response) {
    var errorEl = document.getElementById('error-msg');
    var btn = $('#submit-btn');
    errorEl.style.display = 'none';
    btn.prop('disabled', true).text('جاري تسجيل الدخول...');

    var deviceInfo = {
      platform: navigator.platform,
      userAgent: navigator.userAgent,
      language: navigator.language,
      screenWidth: screen.width,
      screenHeight: screen.height,
      hardwareConcurrency: navigator.hardwareConcurrency || '',
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
      hwid: btoa(navigator.userAgent + screen.width + screen.height + navigator.language)
    };

    $.ajax({
      url: 'api.php?action=google_login',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ credential: response.credential, device_info: deviceInfo }),
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          localStorage.setItem('username', res.user.username);
          localStorage.setItem('userId', res.user.id);
          localStorage.setItem('userRole', res.user.role);
          localStorage.setItem('userAvatar', res.user.avatar || '');
          localStorage.setItem('userToken', res.user.token || '');
          localStorage.setItem('deviceHwid', deviceInfo.hwid || '');
          window.location.href = 'dashboard/';
        } else {
          errorEl.querySelector('span').textContent = res.error || 'فشل تسجيل الدخول';
          errorEl.style.display = 'flex';
        }
      },
      error: function() {
        errorEl.querySelector('span').textContent = 'خطأ في الاتصال بالخادم';
        errorEl.style.display = 'flex';
      },
      complete: function() {
        btn.prop('disabled', false).text('تسجيل الدخول');
      }
    });
  };

  initGoogleLogin();
})();
</script>
</body>
</html>
