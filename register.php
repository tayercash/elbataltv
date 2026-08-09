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
<html class="dark" id="register-html">
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
<title data-i18n="common.register">إنشاء حساب</title>
<link rel="icon" href="favicon.ico">
<link rel="stylesheet" href="assets/fonts/font-awesome/css/all.min.css">
<link rel="stylesheet" href="assets/fonts/noto-sans-arabic/font.css">
<link rel="stylesheet" href="assets/css/root.css">
<link rel="stylesheet" href="assets/css/custom-select.css">
<link rel="stylesheet" href="assets/css/light-theme.css">
<script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.className=document.documentElement.className.replace(/\b(dark|light)\b/g,'').trim();document.documentElement.classList.add(t)})();</script>
<style>
html { background:#0d0d0e !important; width:100%; height:100%; margin:0; padding:0; }
body {
  font-family:'Noto Sans Arabic',sans-serif;
  background:radial-gradient(circle at center,#1c1808 0%,#0d0d0e 80%) !important;
  color:#f5f5f7 !important; display:flex; justify-content:center; align-items:center;
  margin:0; padding:20px; width:100%; min-height:100vh; overflow-y:auto; position:relative;
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
.register-container {
  background:rgba(18,18,20,0.78) !important;
  backdrop-filter:blur(18px); -webkit-backdrop-filter:blur(18px);
  padding:0; border-radius:28px;
  border:1px solid rgba(255,204,0,0.16) !important;
  box-shadow:0 24px 70px rgba(0,0,0,0.68), inset 0 1px 2px rgba(255,255,255,0.05);
  width:100%; max-width:900px; text-align:center; z-index:10;
  transition:all 0.3s ease;
  display:grid; grid-template-columns:minmax(280px,0.9fr) minmax(340px,1.1fr);
  overflow:hidden;
}
.register-container:hover {
  border-color:rgba(255,204,0,0.3) !important;
  box-shadow:0 25px 60px rgba(255,204,0,0.05),0 20px 50px rgba(0,0,0,0.7);
}
.form-row {
  display:flex; flex-direction:column; gap:0;
}
.form-row .input-group {
  flex:1;
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
.register-container form { padding:42px 38px; text-align:right; }
.register-container .login-link,
.register-container #google-login-section,
.register-container #error-msg { text-align:center; }
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
.login-link {
  margin-top: 20px; font-size: 0.85rem; color: #9e9e9e;
}
.login-link a {
  color: #ffcc00; text-decoration: none; font-weight: 600; transition: color 0.3s;
}
.login-link a:hover { color: #ff9900; text-decoration: underline; }
.lang-btn.active { background:rgba(255,204,0,0.15) !important; color:#ffcc00 !important; border-color:rgba(255,204,0,0.3) !important; }
.lang-btn:hover { background:rgba(255,255,255,0.05); color:#ccc; }
@media (max-width:760px) {
  body { align-items:flex-start; }
  .register-container { max-width:420px; grid-template-columns:1fr; }
  .logo-section { padding:30px 22px 24px; border-inline-end:none; border-bottom:1px solid rgba(255,204,0,0.12); }
  .register-container form { padding:28px 22px 30px; }
}
</style>
</head>
<body>
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="register-container">
  <div class="logo-section">
    <img class="logo-img" src="assets/img/logo_192.png" alt="Logo">
    <h1 class="app-name"><?php echo $siteName; ?></h1>
    <p class="app-tagline" data-i18n="app.tagline">نظام الإدارة المتكامل</p>
    <div class="lang-toggle" style="display:flex;gap:6px;margin-top:5px;    direction: ltr;">
      <button class="lang-btn" data-lang="ar" onclick="i18n.setLang('ar')" style="padding:4px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);background:transparent;color:#888;cursor:pointer;font-size:0.75rem;font-weight:600;transition:all 0.3s;font-family:inherit;">AR</button>
      <button class="lang-btn" data-lang="en" onclick="i18n.setLang('en')" style="padding:4px 12px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);background:transparent;color:#888;cursor:pointer;font-size:0.75rem;font-weight:600;transition:all 0.3s;font-family:inherit;">EN</button>
    </div>
  </div>

  <form id="register-form" autocomplete="off">
    <input type="text" name="fake_user" style="display:none" tabindex="-1">
    <input type="password" name="fake_pass" style="display:none" tabindex="-1">

    <div class="form-row">
      <div class="input-group">
        <label for="username" data-i18n="common.username">اسم المستخدم</label>
        <div class="input-wrapper">
          <input type="text" id="username" data-i18n-placeholder="placeholder.username" placeholder="أدخل اسم المستخدم" required autocomplete="one-time-code">
          <i class="fas fa-user"></i>
        </div>
      </div>

      <div class="input-group">
        <label for="email" data-i18n="common.email">البريد الإلكتروني</label>
        <div class="input-wrapper">
          <input type="email" id="email" data-i18n-placeholder="placeholder.email" placeholder="أدخل البريد الإلكتروني" required autocomplete="email">
          <i class="fas fa-envelope"></i>
        </div>
      </div>
    </div>

    <div class="form-row">
      <div class="input-group">
        <label for="password" data-i18n="common.password">كلمة المرور</label>
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
    </div>

    <div class="form-row">
      <div class="input-group">
        <label for="reg-gender" data-i18n="profile.gender">الجنس</label>
        <div class="input-wrapper">
          <select id="reg-gender" data-custom-select>
            <option value="" data-i18n="profile.gender_not_set">غير محدد</option>
            <option value="male" data-i18n="profile.male">ذكر</option>
            <option value="female" data-i18n="profile.female">أنثى</option>
          </select>
          <i class="fas fa-venus-mars"></i>
        </div>
      </div>

      <div class="input-group">
        <label for="reg-country" data-i18n="profile.country">الدولة</label>
        <div class="input-wrapper">
          <select id="reg-country" data-custom-select>
          </select>
          <i class="fas fa-globe"></i>
        </div>
      </div>
    </div>

    <button type="submit" id="submit-btn" data-i18n="common.register">إنشاء حساب</button>

    <div id="google-login-section" style="display:none;margin-top:20px;">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <hr style="flex:1;border:none;border-top:1px solid rgba(255,255,255,0.08);">
        <span style="font-size:0.8rem;color:#666;white-space:nowrap;" data-i18n="common.or">أو</span>
        <hr style="flex:1;border:none;border-top:1px solid rgba(255,255,255,0.08);">
      </div>
      <div id="google-button-container" style="display:flex;justify-content:center;"></div>
    </div>

    <div class="login-link">
      <span data-i18n="common.have_account">لديك حساب بالفعل؟</span>
      <a href="login.php" data-i18n="common.login">تسجيل الدخول</a>
    </div>

    <div id="error-msg"><i class="fas fa-exclamation-circle"></i> <span></span></div>
  </form>

  <!-- OTP Verification -->
  <div id="otp-section" style="display:none;padding:42px 38px;text-align:center;">
    <i class="fas fa-envelope" style="font-size:3rem;color:#ffcc00;margin-bottom:16px;opacity:0.8;"></i>
    <h3 style="color:#fff;margin:0 0 8px;font-size:1.1rem;" data-i18n="register.verify_title">تأكيد البريد الإلكتروني</h3>
    <p id="otp-message" style="color:#888;font-size:0.85rem;margin:0 0 24px;line-height:1.6;">تم إرسال كود التفعيل إلى بريدك الإلكتروني</p>
    <div class="input-group" style="max-width:280px;margin:0 auto;">
      <label for="otp-code" style="display:block;margin-bottom:8px;font-size:13px;font-weight:500;color:#d1d1d6;text-align:center;" data-i18n="register.otp_label">أدخل الكود المكون من 6 أرقام</label>
      <div class="input-wrapper" style="justify-content:center;">
        <input type="text" id="otp-code" maxlength="6" style="text-align:center;font-size:1.5rem;letter-spacing:10px;padding:13px 15px;direction:ltr;font-family:monospace;" placeholder="000000" autocomplete="one-time-code">
      </div>
    </div>
    <button type="button" id="verify-otp-btn" class="submit-btn" style="width:100%;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,#ffcc00,#ff9900);color:#0b0a02;font-size:1rem;font-weight:700;cursor:pointer;transition:all 0.3s;box-shadow:0 4px 15px rgba(255,204,0,0.2);font-family:inherit;margin-top:10px;max-width:280px;" data-i18n="register.verify_btn">تأكيد الحساب</button>
    <div id="otp-error" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#ef4444;font-size:13px;padding:10px 12px;border-radius:10px;margin-top:16px;display:none;"><i class="fas fa-exclamation-circle"></i> <span></span></div>
    <div style="margin-top:16px;font-size:0.85rem;color:#888;">
      <span data-i18n="register.no_code">لم يصلك الكود؟</span>
      <a href="#" id="resend-otp-link" style="color:#ffcc00;text-decoration:none;font-weight:600;" data-i18n="register.resend">إعادة إرسال</a>
    </div>
  </div>
</div>

<script src="assets/js/i18n.js"></script>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/custom-select.js"></script>
<script>
function collectDeviceInfo() {
  return {
    platform: navigator.platform,
    userAgent: navigator.userAgent,
    language: navigator.language,
    screenWidth: screen.width,
    screenHeight: screen.height,
    hardwareConcurrency: navigator.hardwareConcurrency || '',
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    hwid: btoa(navigator.userAgent + screen.width + screen.height + navigator.language)
  };
}

function populateCountrySelect(sel) {
  var list = typeof i18n !== 'undefined' ? i18n.countries : [];
  var lang = localStorage.getItem('lang') || 'ar';
  var key = lang === 'ar' ? 'ar' : 'en';
  sel.empty().append('<option value="">' + (lang === 'ar' ? '— اختر الدولة —' : '— Select Country —') + '</option>');
  list.forEach(function(c) { sel.append('<option value="' + c.code + '" data-flag="' + i18n.getFlagImgUrl(c.code) + '">' + c[key] + '</option>'); });
  window.updateCustomSelect ? updateCustomSelect(sel) : null;
}

$(document).ready(function () {
  if (typeof i18n !== 'undefined') i18n.translateDOM();
  if (typeof i18n !== 'undefined') i18n.initTheme();
  var lang = localStorage.getItem('lang') || 'ar';
  $('.lang-btn').filter('[data-lang="' + lang + '"]').addClass('active');
  initCustomSelects();
  populateCountrySelect($('#reg-country'));
  if (typeof i18n !== 'undefined') i18n.detectCountry(function(code) {
    if (code) {
      $('#reg-country').val(code).trigger('change');
    }
  });

  // Handle redirect from login when email not verified
  var urlParams = new URLSearchParams(window.location.search);
  var verifyEmail = urlParams.get('verify');
  var pendingUserId = localStorage.getItem('pendingVerificationUserId');
  if (verifyEmail && pendingUserId) {
    $('#register-form').hide();
    $('#otp-message').text('تم إرسال كود التفعيل إلى ' + verifyEmail + '. يرجى إدخال الكود أدناه.');
    $('#otp-section').data('userId', parseInt(pendingUserId)).show();
    localStorage.removeItem('pendingVerificationUserId');
    if (typeof i18n !== 'undefined') i18n.translateDOM();
  }
});

$('#register-form').on('submit', function(e) {
  e.preventDefault();
  var username = $('#username').val().trim();
  var email = $('#email').val().trim();
  var password = $('#password').val().trim();
  var confirmPassword = $('#confirm-password').val().trim();
  var errorEl = document.getElementById('error-msg');
  var btn = $('#submit-btn');

  if (!username || !email || !password || !confirmPassword) {
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

  if (username.length < 3) {
    errorEl.querySelector('span').textContent = 'اسم المستخدم قصير جداً (3 أحرف على الأقل)';
    errorEl.style.display = 'flex';
    return false;
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    errorEl.querySelector('span').textContent = 'البريد الإلكتروني غير صالح';
    errorEl.style.display = 'flex';
    return false;
  }

  errorEl.style.display = 'none';
  btn.prop('disabled', true).text('جاري إنشاء الحساب...');

  var deviceInfo = collectDeviceInfo();

  $.ajax({
    url: 'api.php?action=register',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ username: username, email: email, password: password, gender: $('#reg-gender').val() || null, country: $('#reg-country').val() || '', device_info: deviceInfo }),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        if (res.requires_verification) {
          $('#register-form').hide();
          $('#otp-message').text(res.message || 'تم إرسال كود التفعيل إلى بريدك الإلكتروني');
          $('#otp-section').data('userId', res.user_id).show();
          if (typeof i18n !== 'undefined') i18n.translateDOM();
        } else {
          localStorage.setItem('username', res.user.username);
          localStorage.setItem('userId', res.user.id);
          localStorage.setItem('userRole', res.user.role);
          localStorage.setItem('userAvatar', res.user.avatar || '');
          localStorage.setItem('userToken', res.user.token || '');
          window.location.href = 'dashboard/';
        }
      } else {
        errorEl.querySelector('span').textContent = res.error || 'فشل إنشاء الحساب';
        errorEl.style.display = 'flex';
      }
    },
    error: function() {
      errorEl.querySelector('span').textContent = 'خطأ في الاتصال بالخادم';
      errorEl.style.display = 'flex';
    },
    complete: function() {
      btn.prop('disabled', false).text('إنشاء حساب');
    }
  });
  return false;
});

// ==================== OTP Verification ====================
$('#verify-otp-btn').on('click', function() {
  var code = $('#otp-code').val().trim();
  var userId = $('#otp-section').data('userId');
  var otpErrorEl = document.getElementById('otp-error');
  var btn = $(this);

  if (!code || code.length !== 6) {
    otpErrorEl.querySelector('span').textContent = 'يرجى إدخال الكود المكون من 6 أرقام';
    otpErrorEl.style.display = 'flex';
    return;
  }

  otpErrorEl.style.display = 'none';
  btn.prop('disabled', true).text('جاري التفعيل...');
  var deviceInfo = collectDeviceInfo();

  $.ajax({
    url: 'api.php?action=verify_email',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ user_id: userId, code: code, device_info: deviceInfo }),
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
        otpErrorEl.querySelector('span').textContent = res.error || 'فشل التفعيل';
        otpErrorEl.style.display = 'flex';
      }
    },
    error: function() {
      otpErrorEl.querySelector('span').textContent = 'خطأ في الاتصال بالخادم';
      otpErrorEl.style.display = 'flex';
    },
    complete: function() {
      btn.prop('disabled', false).text('تأكيد الحساب');
    }
  });
});

$('#resend-otp-link').on('click', function(e) {
  e.preventDefault();
  var userId = $('#otp-section').data('userId');
  var link = $(this);
  var otpErrorEl = document.getElementById('otp-error');

  link.css('pointer-events', 'none').text('جاري الإرسال...');
  otpErrorEl.style.display = 'none';

  $.ajax({
    url: 'api.php?action=resend_verification',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ user_id: userId }),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        otpErrorEl.className = '';
        otpErrorEl.style.cssText = 'background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);color:#10b981;font-size:13px;padding:10px 12px;border-radius:10px;margin-top:16px;display:flex;align-items:center;justify-content:center;gap:8px;';
        otpErrorEl.innerHTML = '<i class="fas fa-check-circle"></i> <span>' + (res.message || 'تم إرسال الكود الجديد') + '</span>';
        otpErrorEl.style.display = 'flex';
        setTimeout(function() { otpErrorEl.style.display = 'none'; }, 4000);
      } else {
        otpErrorEl.className = '';
        otpErrorEl.style.cssText = 'background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#ef4444;font-size:13px;padding:10px 12px;border-radius:10px;margin-top:16px;display:flex;align-items:center;justify-content:center;gap:8px;';
        otpErrorEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> <span>' + (res.error || 'فشل الإرسال') + '</span>';
        otpErrorEl.style.display = 'flex';
      }
    },
    error: function() {
      otpErrorEl.className = '';
      otpErrorEl.style.cssText = 'background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#ef4444;font-size:13px;padding:10px 12px;border-radius:10px;margin-top:16px;display:flex;align-items:center;justify-content:center;gap:8px;';
      otpErrorEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> <span>خطأ في الاتصال بالخادم</span>';
      otpErrorEl.style.display = 'flex';
    },
    complete: function() {
      link.css('pointer-events', '').text('إعادة إرسال');
    }
  });
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
        btn.prop('disabled', false).text('إنشاء حساب');
      }
    });
  };

  initGoogleLogin();
})();
</script>
</body>
</html>
