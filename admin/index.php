<?php
$lockFile = __DIR__ . '/../installed.lock';
if (!file_exists($lockFile)) {
  header('Location: ../install.php');
  exit;
}
require_once __DIR__ . '/../config.php';
$pdo = requireDB();
$siteName = 'Mou Default';
try {
    $stmt = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'site_name'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['value'] !== '') $siteName = $row['value'];
} catch (PDOException $e) {}
$siteName = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html class="dark">

<head>
  <script>
    (function () {
      var l = localStorage.getItem('lang') || 'ar';
      document.documentElement.lang = l;
      document.documentElement.dir = l === 'ar' ? 'rtl' : 'ltr';
    })();
  </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?php echo $siteName; ?></title>
  <link rel="icon" href="../favicon.ico">
  <script>var BASE_PATH = '<?php echo BASE_PATH; ?>';if (!localStorage.getItem('userId') || !localStorage.getItem('userToken')) { location.href = BASE_PATH + '/login.php' }</script>
  <link rel="stylesheet" href="../assets/fonts/font-awesome/css/all.min.css">
  <link rel="stylesheet" href="../assets/fonts/noto-sans-arabic/font.css">
  <link rel="stylesheet" href="../assets/css/root.css">
  <link rel="stylesheet" href="../assets/css/popup.css">
  <link rel="stylesheet" href="../assets/css/main.css">
  <link rel="stylesheet" href="../assets/css/navigation.css">
  <link rel="stylesheet" href="../assets/css/app.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/toastify.css">
  <link rel="stylesheet" href="../assets/css/custom-select.css">
  <link rel="stylesheet" href="../assets/css/camera.css">
  <link rel="stylesheet" href="../assets/css/calculator.css">
  <link rel="stylesheet" href="../assets/css/ltr-override.css">
  <link rel="stylesheet" href="../assets/css/light-theme.css">
  <script>
    (function () {
      var t = localStorage.getItem('theme') || 'dark';
      document.documentElement.className = document.documentElement.className.replace(/\b(dark|light)\b/g, '').trim();
      document.documentElement.classList.add(t);
    })();
  </script>
</head>

<body>
  <div class="mou_background"></div>
  <div class="full_app_loader"></div>

  <div class="window_popups">
    <div class="mou_popup" id="receipt_preview_popup">
      <div class="mou_popup_container" style="max-width:450px;">
        <div class="mou_popup_header">
          <span data-i18n="receipt.title">مرفق العملية</span>
          <span class="close_popup" data-closepopup="receipt_preview_popup"></span>
        </div>
        <div class="mou_popup_body"
          style="text-align:center;padding:20px;min-height:150px;display:flex;align-items:center;justify-content:center;flex-direction:column;">
          <div id="receipt_loader" style="display:none;padding:20px;color:#ffcc00;">
            <i class="fas fa-circle-notch fa-spin" style="font-size:2.5rem;display:block;margin:0 auto 12px;"></i>
            <span style="font-size:13px;color:#aaa;" data-i18n="receipt.loading">جاري تحميل الإيصال...</span>
          </div>
          <img id="receipt_preview_image" src="" data-i18n-alt="receipt.alt" alt="Receipt"
            style="max-width:100%;border-radius:12px;display:none;">
          <div id="no_receipt_msg" style="color:#888;display:none;padding:20px;">
            <i class="fas fa-file-invoice" style="font-size:3rem;display:block;margin-bottom:10px;"></i>
            <span data-i18n="receipt.none">لا يوجد إيصال مرفق</span>
          </div>
        </div>
      </div>
    </div>

    <div class="mou_popup" id="confirm_action_popup" data-require-choice>
      <div class="mou_popup_container" style="max-width:390px;">
        <div class="confirm-popup-body">
          <div class="confirm-popup-icon"><i class="fas fa-question"></i></div>
          <h3 data-confirm-title style="margin:0;color:var(--text-main);font-size:1.05rem;"></h3>
          <p data-confirm-message class="confirm-popup-message"></p>
        </div>
        <div class="confirm-popup-actions">
          <button type="button" class="confirm-popup-cancel" data-confirm-no></button>
          <button type="button" class="confirm-popup-yes" data-confirm-yes></button>
        </div>
      </div>
    </div>

    <div class="mou_popup" id="profile_popup">
      <div class="mou_popup_container" style="max-width:520px;">
        <div class="mou_popup_header">
          <span data-i18n="profile.title">تعديل الملف الشخصي</span>
          <span class="close_popup" data-closepopup="profile_popup"></span>
        </div>
        <div class="mou_popup_body" style="padding:20px;">
          <div style="text-align:center;margin-bottom:18px;">
            <div id="profile-avatar-preview" style="width:82px;height:82px;border-radius:50%;margin:0 auto 8px;overflow:hidden;border:3px solid rgba(255,204,0,0.5);box-shadow:0 0 20px rgba(255,204,0,0.2);">
              <img id="profile-avatar-img" src="" style="width:100%;height:100%;object-fit:cover;display:none;">
              <span id="profile-avatar-initial" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#ffcc00,#ff9900);color:#000;font-weight:bold;font-size:2rem;"></span>
            </div>
          </div>
          <div style="margin-bottom:16px;">
            <label data-i18n="common.username" style="display:block;margin-bottom:5px;font-size:0.85rem;color:var(--text-secondary);">اسم المستخدم</label>
            <input type="text" id="profile-username-input" class="custom-input" style="width:100%;padding:10px 14px;border-radius:8px;font-size:0.95rem;" data-i18n-placeholder="placeholder.username">
          </div>
          <div style="margin-bottom:12px;">
            <label data-i18n="profile.gender" style="display:block;margin-bottom:5px;font-size:0.85rem;color:var(--text-secondary);">الجنس</label>
            <select id="profile-gender-input" data-custom-select>
              <option value="" data-i18n="profile.gender_not_set">غير محدد</option>
              <option value="male" data-i18n="profile.male">ذكر</option>
              <option value="female" data-i18n="profile.female">أنثى</option>
            </select>
          </div>
          <div style="margin-bottom:16px;">
            <label data-i18n="profile.country" style="display:block;margin-bottom:5px;font-size:0.85rem;color:var(--text-secondary);">الدولة</label>
            <select id="profile-country-input" data-custom-select>
            </select>
          </div>
          <div style="margin-bottom:12px;">
            <button type="button" id="profile-password-btn" style="width:100%;padding:10px 16px;border:none;border-radius:8px;cursor:pointer;font-size:0.9rem;display:flex;align-items:center;justify-content:center;gap:8px;background:rgba(255,204,0,0.1);color:#ffcc00;border:1.5px solid rgba(255,204,0,0.2);font-weight:600;transition:all 0.3s;">
              <i class="fas fa-lock"></i> <span data-i18n="profile.change_password">تغيير كلمة المرور</span>
            </button>
          </div>
          <div id="profile-google-section" style="margin-bottom:12px;display:none;">
            <button type="button" id="profile-use-google-btn" style="width:100%;padding:10px 16px;border:none;border-radius:8px;cursor:pointer;font-size:0.9rem;display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(135deg,#4285F4,#34A853);color:#fff;font-weight:600;">
              <i class="fab fa-google"></i> <span data-i18n="profile.use_google">استخدام صورة جوجل</span>
            </button>
          </div>
          <div style="margin-bottom:12px;">
            <label style="display:block;margin-bottom:6px;font-size:0.85rem;color:var(--text-secondary);" data-i18n="profile.choose_avatar">اختر الصورة الرمزية</label>
            <div id="avatar-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(54px,1fr));gap:6px;max-height:220px;overflow-y:auto;padding:8px;background:rgba(0,0,0,0.12);border-radius:10px;border:1px solid rgba(255,255,255,0.04);"></div>
          </div>
          <div style="text-align:center;">
            <button type="button" id="profile-save-btn" style="padding:11px 40px;border:none;border-radius:8px;cursor:pointer;font-size:1rem;background:linear-gradient(135deg,#ffcc00,#ff9900);color:#000;font-weight:bold;transition:opacity 0.2s;" disabled>
              <span data-i18n="common.save">حفظ</span>
            </button>
            <div id="profile-save-spinner" style="display:none;padding:11px;color:#ffcc00;"><i class="fas fa-circle-notch fa-spin"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mou_popup" id="profile_password_popup">
    <div class="mou_popup_container" style="max-width:420px;">
      <div class="mou_popup_header">
        <span data-i18n="profile.change_password">تغيير كلمة المرور</span>
        <span class="close_popup" data-closepopup="profile_password_popup"></span>
      </div>
      <div class="mou_popup_body" style="padding:20px;">
        <div style="margin-bottom:16px;">
          <label style="display:block;margin-bottom:5px;font-size:0.85rem;color:var(--text-secondary);" data-i18n="profile.new_password">كلمة المرور الجديدة</label>
          <input type="password" id="profile-password-input" class="custom-input" style="width:100%;padding:10px 14px;border-radius:8px;font-size:0.95rem;" placeholder="أدخل كلمة المرور الجديدة" data-i18n-placeholder="profile.password_placeholder">
        </div>
        <div style="margin-bottom:20px;">
          <label style="display:block;margin-bottom:5px;font-size:0.85rem;color:var(--text-secondary);" data-i18n="profile.confirm_password">تأكيد كلمة المرور</label>
          <input type="password" id="profile-confirm-password-input" class="custom-input" style="width:100%;padding:10px 14px;border-radius:8px;font-size:0.95rem;" placeholder="أعد كتابة كلمة المرور" data-i18n-placeholder="profile.confirm_password_placeholder">
        </div>
        <div style="text-align:center;">
          <button type="button" id="profile-password-save-btn" style="padding:11px 40px;border:none;border-radius:8px;cursor:pointer;font-size:1rem;background:linear-gradient(135deg,#ffcc00,#ff9900);color:#000;font-weight:bold;transition:opacity 0.2s;">
            <span data-i18n="common.save">حفظ</span>
          </button>
          <div id="profile-password-spinner" style="display:none;padding:11px;color:#ffcc00;"><i class="fas fa-circle-notch fa-spin"></i></div>
        </div>
      </div>
    </div>
  </div>

  <header class="app-header">
    <div class="header-left">
      <button id="sidebar-toggle-btn" class="icon-btn">
        <i class="fas fa-bars"></i>
      </button>
      <div id="connection-status" class="status-badge">
        <span class="status-dot"></span>
        <span class="status-text" data-i18n="common.connected">متصل</span>
      </div>
    </div>

    <div class="header-right">
      <button id="theme-toggle-btn" class="icon-btn">
        <i class="fas fa-sun"></i>
      </button>
      <button id="user-view-btn" class="icon-btn"
        onclick="sessionStorage.setItem('view_as_user','1');window.location.href=BASE_PATH+'/dashboard/?view_as_user=1'"
        title="User View">
        <i class="fas fa-user"></i>
      </button>
      <div class="lang-toggle">
        <button class="lang-btn" data-lang="ar" onclick="i18n.setLang('ar')">AR</button>
        <button class="lang-btn" data-lang="en" onclick="i18n.setLang('en')">EN</button>
      </div>
      <div class="user-info">
        <span id="display-username">Admin</span>
        <div class="user-avatar" id="header-avatar"><img class="avatar-img" src="" alt=""
            style="display:none;width:100%;height:100%;border-radius:50%;object-fit:cover;"><span
            class="avatar-initial">A</span></div>
      </div>
    </div>
  </header>

  <div class="full_view">
    <div class="full_view_container" id="content"></div>
  </div>

  <script src="../assets/js/i18n.js"></script>
  <script src="../assets/js/jquery.min.js"></script>
  <script src="../assets/js/custom-select.js"></script>
  <script src="../assets/js/popup.js"></script>
  <script src="../assets/js/navigation.js"></script>
  <script src="../assets/js/app.js"></script>
  <script src="../assets/js/toastify.js"></script>

  <script>
    $(document).ready(function () {
      var role = localStorage.getItem('userRole');
      if (role !== 'admin') {
        window.location.href = BASE_PATH + '/dashboard/';
        return;
      }

      const lang = localStorage.getItem('lang') || 'ar';
      document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
      document.documentElement.lang = lang;

      (function () {
        var uid = localStorage.getItem('userId');
        var token = localStorage.getItem('userToken') || '';
        var hwid = btoa(navigator.userAgent + screen.width + screen.height + navigator.language);
        if (uid && token) {
          $.ajax({
            url: BASE_PATH + '/api.php?action=verify_session',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ userId: parseInt(uid), hwid: hwid, token: token }),
            dataType: 'json',
            success: function (res) {
              if (!res.authorized || !res.active) {
                localStorage.removeItem('username');
                localStorage.removeItem('userId');
                localStorage.removeItem('userRole');
                localStorage.removeItem('userAvatar');
                localStorage.removeItem('userToken');
                window.location.href = BASE_PATH + '/login.php';
              }
            }
          });
        } else {
          window.location.href = BASE_PATH + '/login.php';
        }
      })();
      $('.lang-btn').filter('[data-lang="' + lang + '"]').addClass('active');
      if (typeof PopupManager !== 'undefined') PopupManager.init();
      if (typeof i18n !== 'undefined') i18n.translateDOM();
      if (typeof i18n !== 'undefined') i18n.initTheme();
      initCustomSelects();

      const storedName = localStorage.getItem('username') || 'User';
      const storedRole = localStorage.getItem('userRole') || 'user';
      const storedAvatar = localStorage.getItem('userAvatar') || '';
      $('#display-username').text(storedName);
      $('.sidebar-user-section .username').text(storedName);
      var roleBadge = $('#sidebar-role-badge');
      roleBadge.attr('data-i18n', 'common.admin').text(i18n ? i18n.t('common.admin') : 'Admin');

      function resolveAvatarUrl(url) {
        if (url && /^https?:\/\//i.test(url)) {
          try {
            var u = new URL(url);
            if (u.hostname === window.location.hostname) {
              return u.pathname + u.search;
            }
          } catch(e) {}
        }
        return url;
      }
      function setAvatar(url, name) {
        var initial = name.charAt(0).toUpperCase();
        url = resolveAvatarUrl(url);
        $('.user-avatar, .sidebar-avatar').each(function () {
          var el = $(this);
          var img = el.find('.avatar-img');
          var span = el.find('.avatar-initial');
          if (url) {
            img.attr('src', url).show();
            span.hide();
          } else {
            img.hide();
            span.text(initial).show();
          }
        });
      }
      setAvatar(storedAvatar, storedName);

      $('#theme-toggle-btn').on('click', function () {
        if (typeof i18n !== 'undefined') i18n.toggleTheme();
      });
    });
  </script>

  <div id="app-sidebar" class="sidebar-drawer">
    <div class="sidebar-backdrop"></div>

    <div class="sidebar-content">
      <div class="sidebar-header">
        <div class="app-logo" style="display:flex;align-items:center;gap:10px;">
          <img src="../assets/img/logo_192.png" alt=""
            style="width:36px;height:36px;border-radius:8px;box-shadow:0 2px 10px rgba(255,204,0,0.4);border:1.5px solid rgba(255,204,0,0.6);">
          <span style="font-weight:bold;font-size:1.2rem;"><?php echo $siteName; ?></span>
        </div>
        <button id="sidebar-close-btn" class="close-btn"
          style="background:none;border:none;color:#888;font-size:1.2rem;cursor:pointer;transition:color 0.3s;flex:0;"><i
            class="fas fa-times"></i></button>
      </div>

      <div class="sidebar-user-section"
        style="padding:10px 20px;display:flex;align-items:center;gap:15px;background:rgba(255,204,0,0.03);border-bottom:1px solid rgba(255,255,255,0.05);">
        <div class="sidebar-avatar"
          style="width:45px;height:45px;background:linear-gradient(135deg,#ffcc00,#ff9900);color:#000;font-weight:bold;font-size:1.2rem;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(255,204,0,0.3);overflow:hidden;">
          <img class="avatar-img" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover;"><span
            class="avatar-initial">A</span>
        </div>
        <div style="flex:1;">
          <div class="username" style="font-weight:bold;color:#fff;font-size:1rem;line-height: 1.2;"></div>
          <div style="display:flex;align-items:center;gap:8px;margin-top:2px;">
            <span id="sidebar-role-badge"
              style="font-size:0.75rem;color:#ffcc00;background:rgba(255,204,0,0.1);padding:2px 8px;border-radius:10px;width:fit-content;font-weight:500;"></span>
            <button onclick="openProfilePopup()" title="Edit Profile" style="background:none;border:none;color:#888;cursor:pointer;font-size:0.75rem;padding:2px;transition:color 0.2s;line-height:1;" onmouseover="this.style.color='#ffcc00'" onmouseout="this.style.color='#888'">
              <i class="fas fa-pen"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="sidebar-menu" style="flex:1;overflow-y:auto;padding:15px 10px;display:flex;flex-direction:column;">

        <a href="#" class="menu-item submenu-item active" data-target="assets/pages/settings.php"><i
            class="fas fa-cog"></i>
          <span data-i18n="sidebar.project_settings">إعدادات المشروع</span></a>

        <div class="sidebar-dropdown-wrapper" style="display:flex;flex-direction:column;">
          <a href="#" class="menu-item dropdown-toggle">
            <i class="fas fa-user-shield"></i>
            <span data-i18n="sidebar.admin_panel">المستخدمين</span>
            <i class="fas fa-chevron-down dropdown-arrow"></i>
          </a>
          <div class="sidebar-submenu">
            <a href="#" class="menu-item submenu-item" data-target="assets/pages/admin.php"><i
                class="fas fa-users-cog"></i> <span data-i18n="sidebar.user_management">إدارة المستخدمين</span></a>
          </div>
        </div>
      </div>

      <div class="sidebar-footer"
        style="padding:15px 20px;border-top:1px solid rgba(255,255,255,0.05);display:flex;flex-direction:column;gap:8px;">
        <button onclick="logout()" class="sidebar-logout-btn">
          <i class="fas fa-sign-out-alt"></i>
          <span data-i18n="common.logout">تسجيل الخروج</span>
        </button>
      </div>
    </div>
  </div>

  <script>
    async function logout() {
      var ok = await confirmPopup({
        title: typeof i18n !== 'undefined' ? i18n.t('common.logout') : 'Logout',
        message: typeof i18n !== 'undefined' ? i18n.t('common.logout_confirm') : 'Are you sure you want to logout?',
        confirmText: typeof i18n !== 'undefined' ? i18n.t('common.logout') : 'Logout',
        cancelText: typeof i18n !== 'undefined' ? i18n.t('common.cancel') : 'Cancel'
      });
      if (ok) {
        localStorage.removeItem('username');
        localStorage.removeItem('userId');
        localStorage.removeItem('userRole');
        localStorage.removeItem('userAvatar');
        localStorage.removeItem('userToken');
        window.location.href = BASE_PATH + '/login.php';
      }
    }

    var userId = localStorage.getItem('userId');
    var userToken = localStorage.getItem('userToken') || '';
    if (userId && userToken) {
      function getHwid() {
        var info = navigator.userAgent + screen.width + screen.height + navigator.language;
        return btoa(info);
      }
      function sendHeartbeat() {
        navigator.sendBeacon(BASE_PATH + '/api.php?action=heartbeat', JSON.stringify({ userId: parseInt(userId), hwid: getHwid(), token: userToken }));
      }
      sendHeartbeat();
      setInterval(sendHeartbeat, 5000);
    }

    // ==================== PROFILE POPUP ====================
    var _profileSelectedAvatar = null;
    var _profileOriginalUsername = '';
    var _profileHasGoogle = false;

    function openProfilePopup() {
      PopupManager.openPopup('profile_popup');
      loadProfileData();
    }

    function loadProfileData() {
      var uid = parseInt(localStorage.getItem('userId'));
      var tok = localStorage.getItem('userToken') || '';
      var hwid = getHwid();
      $('#profile-save-btn').prop('disabled', true);
      $.ajax({
        url: BASE_PATH + '/api.php?action=get_profile',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ requesterId: uid, requesterHwid: hwid, requesterToken: tok }),
        dataType: 'json',
        success: function (res) {
          if (!res.success) { showToast(res.error || 'Error', 'error'); return; }
          var u = res.user;
          _profileOriginalUsername = u.username;
          _profileHasGoogle = u.has_google;
          _profileSelectedAvatar = null;
          $('#profile-username-input').val(u.username);
          $('#profile-gender-input').val(u.gender || '').trigger('change');
          var savedCountry = u.country || '';
          populateProfileCountrySelect($('#profile-country-input'), savedCountry);
          if (!savedCountry && typeof i18n !== 'undefined') {
            i18n.detectCountry(function(code) {
              if (code) {
                $('#profile-country-input').val(code);
                var sel = document.getElementById('profile-country-input');
                if (sel) sel.dispatchEvent(new Event('change', {bubbles: true}));
              }
            });
          }
          if (u.avatar) {
            $('#profile-avatar-img').attr('src', u.avatar).show();
            $('#profile-avatar-initial').hide();
          } else {
            $('#profile-avatar-img').hide();
            $('#profile-avatar-initial').text(u.username.charAt(0).toUpperCase()).show();
          }
          if (u.has_google) { $('#profile-google-section').show(); } else { $('#profile-google-section').hide(); }
          buildAvatarGrid();
          $('#profile-save-btn').prop('disabled', false);
        },
        error: function () {
          showToast(i18n ? i18n.t('toast.server_error') : 'Server error', 'error');
        }
      });
    }

    function getAvatarSeed(i, gender) {
      if (gender === 'male') return 'm-' + i;
      if (gender === 'female') return 'f-' + i;
      return 'av-' + i;
    }

    function buildAvatarGrid() {
      var grid = document.getElementById('avatar-grid');
      if (!grid) return;
      var gender = $('#profile-gender-input').val();
      grid.innerHTML = '';
      for (var i = 1; i <= 100; i++) {
        var img = document.createElement('img');
        img.src = BASE_PATH + '/avatar.php?seed=' + getAvatarSeed(i, gender);
        img.className = 'profile-avatar-option';
        img.dataset.index = i;
        img.dataset.gender = gender;
        img.loading = 'lazy';
        img.style.cssText = 'width:100%;aspect-ratio:1;border-radius:8px;cursor:pointer;object-fit:cover;border:2px solid transparent;transition:border-color 0.2s,transform 0.15s;box-sizing:border-box;';
        img.onmouseover = function () { if (!this.classList.contains('selected')) this.style.transform = 'scale(1.08)'; };
        img.onmouseout = function () { this.style.transform = ''; };
        img.onclick = selectAvatarOption;
        grid.appendChild(img);
      }
    }

    function selectAvatarOption() {
      document.querySelectorAll('#avatar-grid .profile-avatar-option').forEach(function (el) {
        el.classList.remove('selected');
        el.style.borderColor = 'transparent';
      });
      this.classList.add('selected');
      this.style.borderColor = '#ffcc00';
      var u = new URL(this.src);
      var m = u.search.match(/[?&]seed=([^&]+)/);
      _profileSelectedAvatar = m ? decodeURIComponent(m[1]) : (u.pathname + u.search);
    }

    function populateProfileCountrySelect(sel, selectedVal) {
      var list = i18n ? i18n.countries : [];
      var lang = localStorage.getItem('lang') || 'ar';
      var key = lang === 'ar' ? 'ar' : 'en';
      sel.empty().append('<option value="">' + (lang === 'ar' ? '— اختر الدولة —' : '— Select Country —') + '</option>');
      list.forEach(function(c) {
        var opt = $('<option>').val(c.code).text(c[key]).attr('data-flag', i18n.getFlagImgUrl(c.code));
        if (c.code === selectedVal) opt.prop('selected', true);
        sel.append(opt);
      });
      updateCustomSelect(sel);
    }

    $(document).on('change', '#profile-gender-input', function() {
      buildAvatarGrid();
    });

    $(document).on('click', '#profile-use-google-btn', function () {
      document.querySelectorAll('#avatar-grid .profile-avatar-option').forEach(function (el) {
        el.classList.remove('selected');
        el.style.borderColor = 'transparent';
      });
      var uid = parseInt(localStorage.getItem('userId'));
      var tok = localStorage.getItem('userToken') || '';
      var hwid = getHwid();
      $.ajax({
        url: BASE_PATH + '/api.php?action=get_profile',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ requesterId: uid, requesterHwid: hwid, requesterToken: tok }),
        dataType: 'json',
        success: function (res) {
          if (res.success && res.user.avatar) {
            _profileSelectedAvatar = res.user.avatar;
            $('#profile-avatar-img').attr('src', res.user.avatar).show();
            $('#profile-avatar-initial').hide();
          }
        }
      });
    });

    $(document).on('click', '#profile-save-btn', function () {
      var btn = $(this);
      var spinner = $('#profile-save-spinner');
      var uid = parseInt(localStorage.getItem('userId'));
      var tok = localStorage.getItem('userToken') || '';
      var hwid = getHwid();
      var username = $('#profile-username-input').val().trim();
      var avatar = _profileSelectedAvatar;
      if (!username) { showToast(i18n ? i18n.t('common.validation_required') : 'Username required', 'error'); return; }
      if (username.length < 3) { showToast(i18n ? i18n.t('profile.username_short') : 'Username too short', 'error'); return; }
      var gender = $('#profile-gender-input').val() || null;
      var country = $('#profile-country-input').val() || '';
      btn.hide();
      spinner.show();
      var payload = { requesterId: uid, requesterHwid: hwid, requesterToken: tok, username: username, gender: gender, country: country };
      if (avatar !== null) payload.avatar = avatar;
      $.ajax({
        url: BASE_PATH + '/api.php?action=update_profile',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        dataType: 'json',
        success: function (res) {
          spinner.hide();
          btn.show();
          if (!res.success) { showToast(res.error || 'Error', 'error'); return; }
          localStorage.setItem('username', res.username);
          localStorage.setItem('userAvatar', res.avatar);
          $('#display-username').text(res.username);
          $('.sidebar-user-section .username').text(res.username);
          if (res.avatar) {
            $('.avatar-img').attr('src', res.avatar).show();
            $('.avatar-initial').hide();
          } else {
            $('.avatar-img').hide();
            $('.avatar-initial').text(res.username.charAt(0).toUpperCase()).show();
          }
          PopupManager.closePopup('profile_popup');
          showToast(i18n ? i18n.t('profile.saved') : 'Profile updated', 'success');
        },
        error: function () {
          spinner.hide();
          btn.show();
          showToast(i18n ? i18n.t('toast.server_error') : 'Server error', 'error');
        }
      });
    });

    $(document).on('click', '#profile-password-btn', function() {
      $('#profile-password-input').val('');
      $('#profile-confirm-password-input').val('');
      $('#profile_password_popup').openpopup();
    });

    $(document).on('click', '#profile-password-save-btn', function() {
      var btn = $(this);
      var spinner = $('#profile-password-spinner');
      var uid = parseInt(localStorage.getItem('userId'));
      var tok = localStorage.getItem('userToken') || '';
      var hwid = getHwid();
      var password = $('#profile-password-input').val().trim();
      var confirmPassword = $('#profile-confirm-password-input').val().trim();
      if (!password) { showToast(i18n ? i18n.t('profile.password_required') : 'Password is required', 'error'); return; }
      if (password !== confirmPassword) { showToast(i18n ? i18n.t('profile.password_mismatch') : 'Passwords do not match', 'error'); return; }
      btn.hide();
      spinner.show();
      $.ajax({
        url: BASE_PATH + '/api.php?action=update_profile',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({requesterId: uid, requesterHwid: hwid, requesterToken: tok, username: localStorage.getItem('username'), password: password}),
        dataType: 'json',
        success: function(res) {
          spinner.hide(); btn.show();
          if (!res.success) { showToast(res.error || 'Error', 'error'); return; }
          PopupManager.closePopup('profile_password_popup');
          showToast(i18n ? i18n.t('profile.password_changed') : 'Password changed', 'success');
        },
        error: function() {
          spinner.hide(); btn.show();
          showToast(i18n ? i18n.t('toast.server_error') : 'Server error', 'error');
        }
      });
    });
  </script>
</body>

</html>
