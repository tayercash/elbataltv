<?php if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
  header('Location: ../../login.php');
  exit;
} ?>
<style>
  .settings-container {
    max-width: 1440px;
    margin: 0 auto;
  }

  .settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }

  @media (max-width:768px) {
    .settings-grid {
      grid-template-columns: 1fr;
    }
  }

  .settings-card {
    background: rgba(30, 30, 30, 0.4);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  }

  .settings-card h3 {
    color: #ffcc00;
    font-size: 1.1rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255, 204, 0, 0.1);
  }

  .settings-card-wide {
    grid-column: 1 / -1;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    display: block;
    color: #d1d1d6;
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 8px;
  }

  .form-group .hint {
    font-size: 0.75rem;
    color: #888;
    margin-top: 6px;
    line-height: 1.5;
  }

  .form-group input[type="text"],
  .form-group input[type="password"],
  .form-group textarea {
    width: 100%;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.04);
    color: #fff;
    font-size: 0.95rem;
    font-family: inherit;
    transition: border-color 0.3s, box-shadow 0.3s;
    box-sizing: border-box;
  }

  .form-group textarea {
    min-height: 110px;
    resize: vertical;
    line-height: 1.6;
  }

  .form-group input:focus {
    outline: none;
    border-color: rgba(255, 204, 0, 0.6);
    box-shadow: 0 0 12px rgba(255, 204, 0, 0.15);
  }

  .toggle-group {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.06);
  }

  .toggle-group .toggle-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .toggle-group .toggle-info span {
    color: #fff;
    font-weight: 500;
    font-size: 0.95rem;
  }

  .toggle-group .toggle-info small {
    color: #888;
    font-size: 0.8rem;
  }

  .toggle-switch {
    position: relative;
    width: 50px;
    height: 28px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 14px;
    cursor: pointer;
    transition: background 0.3s;
    flex-shrink: 0;
  }

  .toggle-switch.active {
    background: #ffcc00;
  }

  .toggle-switch .knob {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 22px;
    height: 22px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  }

  [dir="rtl"] .toggle-switch .knob {
    left: auto;
    right: 3px;
  }

  .toggle-switch.active .knob {
    transform: translateX(22px);
  }

  [dir="rtl"] .toggle-switch.active .knob {
    transform: translateX(-22px);
  }

  .save-btn {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #ffcc00, #ff9900);
    color: #0b0a02;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(255, 204, 0, 0.2);
    font-family: inherit;
    margin-top: 8px;
  }

  .save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 204, 0, 0.4);
  }

  .save-btn:active {
    transform: translateY(1px);
  }

  .save-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
  }

  .status-msg {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 500;
    display: none;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
  }

  .status-msg.success {
    display: flex;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.2);
  }

  .status-msg.error {
    display: flex;
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
  }

  .not-admin-msg {
    text-align: center;
    padding: 60px 20px;
    color: #888;
  }

  .not-admin-msg i {
    font-size: 3rem;
    display: block;
    margin-bottom: 15px;
    color: #ef4444;
    opacity: 0.6;
  }

  .not-admin-msg h3 {
    color: #fff;
    margin-bottom: 8px;
  }

  .site-profile-layout {
    display: grid;
    grid-template-columns: minmax(220px, 0.75fr) minmax(320px, 1.25fr);
    gap: 24px;
    align-items: stretch;
  }

  .site-logo-panel {
    border: 1px solid rgba(255, 204, 0, 0.12);
    background: radial-gradient(circle at top, rgba(255, 204, 0, 0.12), rgba(255, 255, 255, 0.03));
    border-radius: 18px;
    padding: 22px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    text-align: center;
  }

  .site-logo-preview {
    width: 120px;
    height: 120px;
    border-radius: 26px;
    object-fit: cover;
    border: 2px solid rgba(255, 204, 0, 0.45);
    box-shadow: 0 14px 32px rgba(0, 0, 0, 0.35), 0 0 24px rgba(255, 204, 0, 0.12);
    background: rgba(0, 0, 0, 0.25);
  }

  .site-favicon-preview {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(0, 0, 0, 0.25);
  }

  .site-upload-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 12px;
    background: rgba(255, 204, 0, 0.1);
    color: #ffcc00;
    border: 1px solid rgba(255, 204, 0, 0.22);
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 700;
    transition: 0.2s;
  }

  .site-upload-label:hover {
    background: rgba(255, 204, 0, 0.16);
    transform: translateY(-1px);
  }

  .site-upload-label input {
    display: none;
  }

  @media (max-width: 576px) {
    .settings-card {
      padding: 20px;
    }

    .site-profile-layout {
      grid-template-columns: 1fr;
    }
  }
</style>

<div id="settings-status" class="status-msg"></div>

<div id="settings-content">
  <div class="settings-container">
    <div class="settings-grid">
      <div class="settings-card settings-card-wide">
        <h3><i class="fas fa-globe"></i> <span data-i18n="settings.site_profile_title">بيانات الموقع</span></h3>
        <div id="site-profile-status" class="status-msg"></div>

        <div class="site-profile-layout">
          <div class="site-logo-panel">
            <img id="site_logo_preview" class="site-logo-preview" src="../assets/img/logo_192.png" alt="Logo">
            <div style="display:flex;align-items:center;gap:10px;color:#888;font-size:0.8rem;">
              <img id="site_favicon_preview" class="site-favicon-preview" src="../favicon.ico" alt="Favicon">
              <span data-i18n="settings.favicon_hint">سيتم توليد favicon.ico تلقائياً من الصورة</span>
            </div>
            <label class="site-upload-label">
              <i class="fas fa-image"></i>
              <span data-i18n="settings.change_site_logo">تغيير صورة الموقع</span>
              <input type="file" id="site_logo_input" accept="image/png,image/jpeg,image/gif,image/webp">
            </label>
          </div>

          <div>
            <div class="form-group">
              <label data-i18n="settings.site_name">اسم الموقع</label>
              <input type="text" id="site_name" data-i18n-placeholder="settings.site_name_placeholder"
                placeholder="Mou Default" autocomplete="off">
            </div>
            <div class="form-group">
              <label data-i18n="settings.site_short_desc">الوصف القصير</label>
              <input type="text" id="site_short_desc" data-i18n-placeholder="settings.site_short_desc_placeholder"
                placeholder="نظام الإدارة المتكامل" autocomplete="off">
            </div>
            <div class="form-group">
              <label data-i18n="settings.site_full_desc">الوصف الكامل</label>
              <textarea id="site_full_desc" data-i18n-placeholder="settings.site_full_desc_placeholder"
                placeholder="اكتب وصف الموقع..."></textarea>
            </div>
            <button class="save-btn" id="save-site-profile-btn">
              <i class="fas fa-save"></i> <span data-i18n="settings.save_site_profile">حفظ بيانات الموقع</span>
            </button>
          </div>
        </div>
      </div>

      <div class="settings-card">
        <h3><i class="fab fa-google"></i> Google OAuth</h3>

        <div class="form-group">
          <label data-i18n="settings.client_id">Google Client ID</label>
          <input type="text" id="google_client_id" data-i18n-placeholder="settings.client_id"
            placeholder="Google Client ID" autocomplete="off">
          <div class="hint" data-i18n="settings.client_id_hint">Client ID من مشروع Google Cloud Console (OAuth 2.0)
          </div>
        </div>

        <div class="form-group">
          <label data-i18n="settings.client_secret">Google Client Secret</label>
          <input type="password" id="google_client_secret" data-i18n-placeholder="settings.client_secret"
            placeholder="Google Client Secret" autocomplete="off">
          <div class="hint" data-i18n="settings.client_secret_hint">Client Secret من مشروع Google Cloud Console</div>
        </div>

        <div style="margin-top:24px;">
          <button class="save-btn" id="save-settings-btn">
            <i class="fas fa-save"></i> <span data-i18n="settings.save_btn">حفظ الإعدادات</span>
          </button>
        </div>

        <div style="margin-top:28px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.06);">
          <h3 style="color:#ffcc00;font-size:1.1rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;"><i
              class="fas fa-question-circle"></i> <span data-i18n="settings.guide_title">كيفية الحصول على بيانات Google
              OAuth</span></h3>

          <div id="guide-steps" style="display:flex;flex-direction:column;gap:14px;"></div>
        </div>
      </div>

      <div class="settings-card">
        <h3><i class="fas fa-envelope"></i> <span data-i18n="settings.email_title">إعدادات البريد الإلكتروني</span></h3>

        <div class="form-group">
          <label data-i18n="settings.app_name">اسم التطبيق</label>
          <input type="text" id="app_name" data-i18n-placeholder="settings.app_name_placeholder"
            placeholder="Mou Default" autocomplete="off">
          <div class="hint" data-i18n="settings.app_name_hint">الاسم الذي سيظهر في رسائل البريد الإلكتروني</div>
        </div>

        <div class="form-group">
          <label data-i18n="settings.smtp_host">SMTP Host</label>
          <input type="text" id="smtp_host" data-i18n-placeholder="settings.smtp_host_placeholder"
            placeholder="smtp.gmail.com" autocomplete="off">
        </div>

        <div class="form-group">
          <label data-i18n="settings.smtp_port">SMTP Port</label>
          <input type="text" id="smtp_port" data-i18n-placeholder="settings.smtp_port_placeholder" placeholder="587"
            autocomplete="off">
        </div>

        <div class="form-group">
          <label data-i18n="settings.smtp_email">SMTP Email</label>
          <input type="text" id="smtp_email" data-i18n-placeholder="settings.smtp_email_placeholder"
            placeholder="your@email.com" autocomplete="off">
        </div>

        <div class="form-group">
          <label data-i18n="settings.smtp_password">SMTP Password</label>
          <input type="password" id="smtp_password" data-i18n-placeholder="placeholder.password_mask"
            placeholder="••••••••" autocomplete="off">
        </div>

        <div class="form-group">
          <label data-i18n="settings.smtp_encryption">نوع التشفير</label>
          <select id="smtp_encryption"
            style="width:100%;padding:12px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);color:#fff;font-size:0.95rem;font-family:inherit;transition:border-color 0.3s;box-sizing:border-box;">
            <option value="tls">TLS</option>
            <option value="ssl">SSL</option>
            <option value="none">None</option>
          </select>
        </div>

        <div style="margin-top:24px;">
          <button class="save-btn" id="save-email-btn">
            <i class="fas fa-save"></i> <span data-i18n="settings.save_email_btn">حفظ إعدادات البريد</span>
          </button>
        </div>

        <div style="margin-top:16px;">
          <button class="save-btn" id="test-email-btn"
            style="background:linear-gradient(135deg,#10b981,#059669) !important;color:#fff !important;">
            <i class="fas fa-paper-plane"></i> <span data-i18n="settings.test_email_btn">اختبار الإرسال</span>
          </button>
        </div>

        <div id="email-status" class="status-msg" style="margin-top:16px;margin-bottom:0;"></div>

        <div style="margin-top:28px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.06);">
          <h3 style="color:#ffcc00;font-size:1.1rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;"><i
              class="fas fa-shield-alt"></i> <span data-i18n="settings.verification_title">تأكيد الحسابات الجديدة</span>
          </h3>
          <div class="form-group">
            <div class="toggle-group">
              <div class="toggle-info">
                <span data-i18n="settings.verification_toggle_label">تفعيل إرسال كود تفعيل للحسابات الجديدة</span>
                <small data-i18n="settings.verification_toggle_hint">عند التفعيل، سيتم إرسال كود OTP إلى البريد
                  الإلكتروني للمستخدم الجديد لتفعيل حسابه قبل أن يتمكن من تسجيل الدخول</small>
              </div>
              <div class="toggle-switch" id="email_verification_toggle">
                <div class="knob"></div>
              </div>
            </div>
          </div>
          <div style="margin-top:16px;">
            <button class="save-btn" id="save-verification-btn">
              <i class="fas fa-save"></i> <span data-i18n="settings.save_verification_btn">حفظ إعدادات التفعيل</span>
            </button>
          </div>
          <div id="verification-status" class="status-msg" style="margin-top:12px;"></div>
        </div>
      </div>

      <div class="settings-card">
        <h3><i class="fas fa-shield-alt"></i> <span data-i18n="settings.login_card_title">إعدادات تسجيل الدخول</span>
        </h3>

        <div class="form-group">
          <label data-i18n="settings.max_devices_label">عدد الأجهزة المسموح بها لكل مستخدم</label>
          <input type="number" id="max_devices" min="0" value="2"
            style="width:100%;padding:12px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);color:#fff;font-size:0.95rem;font-family:inherit;transition:border-color 0.3s,box-shadow 0.3s;box-sizing:border-box;"
            autocomplete="off">
          <div class="hint" data-i18n="settings.max_devices_hint">0 = بدون حد أقصى (غير محدود). الجهاز الحالي لا يُحتسب
            ضمن الحد المسموح به عند التحديث</div>
        </div>

        <div class="form-group">
          <div class="toggle-group">
            <div class="toggle-info">
              <span data-i18n="settings.toggle_label">تفعيل تسجيل الدخول بجوجل</span>
              <small data-i18n="settings.toggle_hint">عند التفعيل، سيظهر زر "تسجيل الدخول باستخدام Google" في صفحة
                الدخول</small>
            </div>
            <div class="toggle-switch" id="google_login_toggle">
              <div class="knob"></div>
            </div>
          </div>
        </div>

        <div style="margin-top:24px;">
          <button class="save-btn" id="save-login-btn">
            <i class="fas fa-save"></i> <span data-i18n="settings.save_login_btn">حفظ إعدادات تسجيل الدخول</span>
          </button>
        </div>
        <div id="login-status" class="status-msg" style="margin-top:12px;"></div>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    function requesterData() {
      var hwid = btoa(navigator.userAgent + screen.width + screen.height + navigator.language);
      return { requesterId: parseInt(localStorage.getItem('userId') || 0), requesterHwid: hwid, requesterToken: localStorage.getItem('userToken') || '' };
    }
    var userRole = localStorage.getItem('userRole');
    if (userRole !== 'admin') {
      $('#settings-content').html(
        '<div class="not-admin-msg">' +
        '<i class="fas fa-shield-alt"></i>' +
        '<h3 data-i18n="settings.not_admin_title">صلاحية محدودة</h3>' +
        '<p style="color:#888;" data-i18n="settings.not_admin_desc">هذه الصفحة مخصصة للمدير فقط</p>' +
        '</div>'
      );
      if (typeof i18n !== 'undefined') i18n.translateDOM();
      return;
    }

    var statusEl = $('#settings-status');
    var emailStatusEl = $('#email-status');
    var siteProfileStatusEl = $('#site-profile-status');

    function loadSettings() {
      var keys = ['google_client_id', 'google_client_secret', 'google_login_enabled', 'site_name', 'site_short_desc', 'site_full_desc', 'app_name', 'smtp_host', 'smtp_port', 'smtp_email', 'smtp_password', 'smtp_encryption', 'email_verification_enabled', 'max_devices'];
      var loaded = 0;
      keys.forEach(function (key) {
        $.ajax({
          url: '../api.php?action=get_setting',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(Object.assign({ key: key }, requesterData())),
          dataType: 'json',
          success: function (res) {
            if (res.value !== null && res.value !== undefined) {
              if (key === 'google_login_enabled') {
                if (res.value === '1') {
                  $('#google_login_toggle').addClass('active');
                } else {
                  $('#google_login_toggle').removeClass('active');
                }
              } else if (key === 'email_verification_enabled') {
                if (res.value === '1') {
                  $('#email_verification_toggle').addClass('active');
                } else {
                  $('#email_verification_toggle').removeClass('active');
                }
              } else if (key === 'max_devices') {
                $('#' + key).val(res.value || '2');
              } else if (key === 'smtp_encryption') {
                $('#' + key).val(res.value);
              } else if (key === 'site_name' || key === 'site_short_desc' || key === 'site_full_desc') {
                $('#' + key).val(res.value);
              } else if (key === 'google_client_secret' || key === 'smtp_password') {
                $('#' + key).val(res.value);
              } else {
                $('#' + key).val(res.value);
              }
            }
            loaded++;
            if (loaded === keys.length) {
              statusEl.removeClass('success error').hide();
            }
          },
          error: function () {
            loaded++;
          }
        });
      });
    }

    $('#site_logo_input').on('change', function () {
      var file = this.files && this.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#site_logo_preview').attr('src', e.target.result);
        $('#site_favicon_preview').attr('src', e.target.result);
      };
      reader.readAsDataURL(file);
    });

    $('#save-site-profile-btn').on('click', function () {
      var btn = $(this);
      btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + t('settings.saving'));
      siteProfileStatusEl.removeClass('success error').hide();

      var fd = new FormData();
      var auth = requesterData();
      fd.append('requesterId', auth.requesterId);
      fd.append('requesterHwid', auth.requesterHwid);
      fd.append('requesterToken', auth.requesterToken);
      fd.append('site_name', $('#site_name').val().trim());
      fd.append('site_short_desc', $('#site_short_desc').val().trim());
      fd.append('site_full_desc', $('#site_full_desc').val().trim());
      var logoFile = $('#site_logo_input')[0].files[0];
      if (logoFile) fd.append('site_logo', logoFile);

      $.ajax({
        url: '../api.php?action=save_site_profile',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
          if (res.success) {
            var stamp = Date.now();
            $('#site_logo_preview').attr('src', '../assets/img/logo_192.png?v=' + stamp);
            $('#site_favicon_preview').attr('src', '../favicon.ico?v=' + stamp);
            var siteName = $('#site_name').val().trim() || 'Mou Default';
            $('#app-sidebar .app-logo span').text(siteName);
            var logoImg = $('#app-sidebar .app-logo img');
            logoImg.attr('src', logoImg.attr('src').split('?')[0] + '?v=' + stamp);
            $('[data-i18n="app.name"]').text(siteName);
            document.title = siteName;
            siteProfileStatusEl.removeClass('error').addClass('success').html('<i class="fas fa-check-circle"></i> ' + t('settings.saved')).show();
          } else {
            siteProfileStatusEl.removeClass('success').addClass('error').html('<i class="fas fa-exclamation-circle"></i> ' + (res.error || t('settings.error'))).show();
          }
        },
        error: function () {
          siteProfileStatusEl.removeClass('success').addClass('error').html('<i class="fas fa-exclamation-circle"></i> ' + t('settings.error')).show();
        },
        complete: function () {
          btn.prop('disabled', false).html('<i class="fas fa-save"></i> ' + t('settings.save_site_profile'));
          setTimeout(function () { siteProfileStatusEl.fadeOut(300); }, 4000);
        }
      });
    });

    $('#google_login_toggle').on('click', function () {
      $(this).toggleClass('active');
    });

    $('#email_verification_toggle').on('click', function () {
      $(this).toggleClass('active');
    });

    $('#save-settings-btn').on('click', function () {
      var btn = $(this);
      btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + t('settings.saving'));

      var settings = {
        google_client_id: $('#google_client_id').val().trim(),
        google_client_secret: $('#google_client_secret').val().trim()
      };

      var keys = Object.keys(settings);
      var saved = 0;
      var hasError = false;

      keys.forEach(function (key) {
        $.ajax({
          url: '../api.php?action=save_setting',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(Object.assign({ key: key, value: settings[key] }, requesterData())),
          dataType: 'json',
          success: function (res) {
            saved++;
            if (saved === keys.length && !hasError) {
              statusEl.removeClass('error').addClass('success').html('<i class="fas fa-check-circle"></i> ' + t('settings.saved')).show();
              btn.prop('disabled', false).html('<i class="fas fa-save"></i> ' + t('settings.save_btn'));
              setTimeout(function () { statusEl.fadeOut(300); }, 3000);
            }
          },
          error: function () {
            hasError = true;
            statusEl.removeClass('success').addClass('error').html('<i class="fas fa-exclamation-circle"></i> ' + t('settings.error')).show();
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> ' + t('settings.save_btn'));
          }
        });
      });
    });

    // ==================== Email Settings ====================
    $('#save-email-btn').on('click', function () {
      var btn = $(this);
      btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + t('settings.saving'));

      var settings = {
        app_name: $('#app_name').val().trim(),
        smtp_host: $('#smtp_host').val().trim(),
        smtp_port: $('#smtp_port').val().trim(),
        smtp_email: $('#smtp_email').val().trim(),
        smtp_password: $('#smtp_password').val().trim(),
        smtp_encryption: $('#smtp_encryption').val(),
        smtp_ready: ($('#smtp_host').val().trim() && $('#smtp_email').val().trim() && $('#smtp_password').val().trim()) ? '1' : '0',
        email_verification_enabled: $('#email_verification_toggle').hasClass('active') ? '1' : '0'
      };

      var keys = Object.keys(settings);
      var saved = 0;
      var hasError = false;

      keys.forEach(function (key) {
        $.ajax({
          url: '../api.php?action=save_setting',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(Object.assign({ key: key, value: settings[key] }, requesterData())),
          dataType: 'json',
          success: function (res) {
            saved++;
            if (saved === keys.length && !hasError) {
              emailStatusEl.removeClass('error').addClass('success').html('<i class="fas fa-check-circle"></i> ' + t('settings.saved')).show();
              btn.prop('disabled', false).html('<i class="fas fa-save"></i> ' + t('settings.save_email_btn'));
              setTimeout(function () { emailStatusEl.fadeOut(300); }, 3000);
            }
          },
          error: function () {
            hasError = true;
            emailStatusEl.removeClass('success').addClass('error').html('<i class="fas fa-exclamation-circle"></i> ' + t('settings.error')).show();
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> ' + t('settings.save_email_btn'));
          }
        });
      });
    });

    $('#test-email-btn').on('click', function () {
      var btn = $(this);
      btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الاختبار...');

      var testEmail = $('#smtp_email').val().trim();
      if (!testEmail) {
        emailStatusEl.removeClass('success').addClass('error').html('<i class="fas fa-exclamation-circle"></i> يرجى حفظ إعدادات البريد أولاً').show();
        btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> ' + t('settings.test_email_btn'));
        return;
      }

      $.ajax({
        url: '../api.php?action=test_smtp',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(Object.assign({ test_email: testEmail }, requesterData())),
        dataType: 'json',
        success: function (res) {
          if (res.success) {
            emailStatusEl.removeClass('error').addClass('success').html('<i class="fas fa-check-circle"></i> تم الإرسال بنجاح! تحقق من بريدك').show();
          } else {
            emailStatusEl.removeClass('success').addClass('error').html('<i class="fas fa-exclamation-circle"></i> ' + (res.error || 'فشل الإرسال')).show();
          }
        },
        error: function () {
          emailStatusEl.removeClass('success').addClass('error').html('<i class="fas fa-exclamation-circle"></i> خطأ في الاتصال بالخادم').show();
        },
        complete: function () {
          btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> ' + t('settings.test_email_btn'));
          setTimeout(function () { emailStatusEl.fadeOut(300); }, 5000);
        }
      });
    });

    // ==================== Verification Settings ====================
    $('#save-verification-btn').on('click', function () {
      var btn = $(this);
      var statusEl = $('#verification-status');
      btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + t('settings.saving'));

      var value = $('#email_verification_toggle').hasClass('active') ? '1' : '0';

      $.ajax({
        url: '../api.php?action=save_setting',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(Object.assign({ key: 'email_verification_enabled', value: value }, requesterData())),
        dataType: 'json',
        success: function (res) {
          if (res.success) {
            statusEl.removeClass('error').addClass('success').html('<i class="fas fa-check-circle"></i> ' + t('settings.saved')).show();
          } else {
            statusEl.removeClass('success').addClass('error').html('<i class="fas fa-exclamation-circle"></i> ' + (res.error || t('settings.error'))).show();
          }
        },
        error: function () {
          statusEl.removeClass('success').addClass('error').html('<i class="fas fa-exclamation-circle"></i> ' + t('settings.error')).show();
        },
        complete: function () {
          btn.prop('disabled', false).html('<i class="fas fa-save"></i> ' + t('settings.save_verification_btn'));
          setTimeout(function () { statusEl.fadeOut(300); }, 3000);
        }
      });
    });

    // ==================== Login Settings ====================
    $('#save-login-btn').on('click', function () {
      var btn = $(this);
      var statusEl = $('#login-status');
      btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + t('settings.saving'));

      var settings = {
        max_devices: $('#max_devices').val().trim() || '2',
        google_login_enabled: $('#google_login_toggle').hasClass('active') ? '1' : '0'
      };

      var keys = Object.keys(settings);
      var saved = 0;
      var hasError = false;

      keys.forEach(function (key) {
        $.ajax({
          url: '../api.php?action=save_setting',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(Object.assign({ key: key, value: settings[key] }, requesterData())),
          dataType: 'json',
          success: function (res) {
            saved++;
            if (saved === keys.length && !hasError) {
              statusEl.removeClass('error').addClass('success').html('<i class="fas fa-check-circle"></i> ' + t('settings.saved')).show();
              btn.prop('disabled', false).html('<i class="fas fa-save"></i> ' + t('settings.save_login_btn'));
              setTimeout(function () { statusEl.fadeOut(300); }, 3000);
            }
          },
          error: function () {
            hasError = true;
            statusEl.removeClass('success').addClass('error').html('<i class="fas fa-exclamation-circle"></i> ' + t('settings.error')).show();
            btn.prop('disabled', false).html('<i class="fas fa-save"></i> ' + t('settings.save_login_btn'));
          }
        });
      });
    });

    loadSettings();
    renderGuideSteps();
    if (typeof i18n !== 'undefined') i18n.translateDOM();
  });

  function renderGuideSteps() {
    var container = document.getElementById('guide-steps');
    if (!container || typeof t === 'undefined') return;
    var steps = [
      { num: 1, titleKey: 'settings.guide_step1_title', descKey: 'settings.guide_step1_desc' },
      { num: 2, titleKey: 'settings.guide_step2_title', descKey: 'settings.guide_step2_desc' },
      { num: 3, titleKey: 'settings.guide_step3_title', descKey: 'settings.guide_step3_desc' },
      { num: 4, titleKey: 'settings.guide_step4_title', descKey: 'settings.guide_step4_desc' },
      { num: 5, titleKey: 'settings.guide_step5_title', descKey: 'settings.guide_step5_desc', isStep5: true },
      { num: 6, titleKey: 'settings.guide_step6_title', descKey: 'settings.guide_step6_desc' }
    ];
    var origin = window.location.origin;
    var uris = [
      { label: t('settings.guide_step5_origin_js'), items: [origin] },
      { label: t('settings.guide_step5_redirect_uri'), items: [origin + (typeof BASE_PATH !== 'undefined' ? BASE_PATH : '') + '/login.php'] }
    ];
    var html = '';
    steps.forEach(function (step) {
      html += '<div style="display:flex;gap:14px;align-items:flex-start;">'
        + '<div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#ffcc00,#ff9900);color:#0b0a02;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;flex-shrink:0;margin-top:2px;">' + step.num + '</div>'
        + '<div>'
        + '<div style="color:#fff;font-weight:600;font-size:0.9rem;margin-bottom:4px;">' + t(step.titleKey) + '</div>'
        + '<div style="color:#888;font-size:0.8rem;line-height:1.6;">';
      if (step.isStep5) {
        html += t(step.descKey) + '<br>';
        uris.forEach(function (group) {
          html += '<div style="margin-top:6px;font-size:0.8rem;color:#aaa;">' + group.label + ':</div>';
          group.items.forEach(function (uri) {
            html += '<span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.06);padding:3px 8px;border-radius:4px;margin:2px 0;margin-' + (document.dir === 'rtl' ? 'right' : 'left') + ':8px;"><code style="font-size:0.75rem;color:#ffcc00;background:none;padding:0;">' + uri + '</code><button onclick="copyText(\'' + uri + '\',this)" style="background:none;border:none;color:#888;cursor:pointer;font-size:0.7rem;padding:2px 4px;border-radius:3px;transition:color 0.2s;" title="' + t('settings.copy_btn') + '"><i class="fas fa-copy"></i></button></span>';
          });
        });
      } else {
        html += t(step.descKey);
      }
      html += '</div></div></div>';
    });
    container.innerHTML = html;
  };

  function copyText(text, btn) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function () {
        var icon = btn.querySelector('i');
        icon.className = 'fas fa-check';
        btn.style.color = '#10b981';
        setTimeout(function () {
          icon.className = 'fas fa-copy';
          btn.style.color = '#888';
        }, 1500);
      }).catch(function () { fallbackCopy(text, btn); });
    } else {
      fallbackCopy(text, btn);
    }
  }
  function fallbackCopy(text, btn) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed'; ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand('copy');
      var icon = btn.querySelector('i');
      icon.className = 'fas fa-check';
      btn.style.color = '#10b981';
      setTimeout(function () { icon.className = 'fas fa-copy'; btn.style.color = '#888'; }, 1500);
    } catch (e) { }
    document.body.removeChild(ta);
  }
</script>