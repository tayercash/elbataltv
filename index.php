<?php
$lockFile = __DIR__ . '/installed.lock';
if (!file_exists($lockFile)) {
    header('Location: install.php');
    exit;
}
require_once __DIR__ . '/config.php';
$pdo = requireDB();

$settings = [
    'site_name' => 'Mou Default',
    'site_short_desc' => 'Integrated Management System',
    'site_full_desc' => 'A focused workspace for managing users, secure device access, authentication, and daily operations from one streamlined dashboard.',
];
try {
    $stmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` IN ('site_name','site_short_desc','site_full_desc')");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['value'] !== null && $row['value'] !== '') {
            $settings[$row['key']] = $row['value'];
        }
    }
} catch (PDOException $e) {}

$siteName = htmlspecialchars($settings['site_name'], ENT_QUOTES, 'UTF-8');
$shortDesc = htmlspecialchars($settings['site_short_desc'], ENT_QUOTES, 'UTF-8');
$fullDesc = htmlspecialchars($settings['site_full_desc'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html class="dark">
<head>
<script>
  (function(){
    var l=localStorage.getItem('lang')||'ar';
    document.documentElement.lang=l;
    document.documentElement.dir=l==='ar'?'rtl':'ltr';
    if (localStorage.getItem('userId') && localStorage.getItem('userToken')) document.documentElement.classList.add('has-session');
  })();
</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<script>var BASE_PATH = '<?php echo BASE_PATH; ?>';</script>
<title><?php echo $siteName; ?></title>
<link rel="icon" href="favicon.ico">
<link rel="stylesheet" href="assets/fonts/font-awesome/css/all.min.css">
<link rel="stylesheet" href="assets/fonts/noto-sans-arabic/font.css">
<link rel="stylesheet" href="assets/css/light-theme.css">
<script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.className=document.documentElement.className.replace(/\b(dark|light)\b/g,'').trim();document.documentElement.classList.add(t)})();</script>
<link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
<div class="page-glow"></div>
<header class="site-header">
  <nav class="nav">
    <a class="brand" href="index.php">
      <img src="assets/img/logo_192.png" alt="">
      <span><?php echo $siteName; ?></span>
    </a>
    <div class="actions">
      <div class="lang-toggle">
        <button class="lang-btn" data-lang="ar" onclick="i18n.setLang('ar')">AR</button>
        <button class="lang-btn" data-lang="en" onclick="i18n.setLang('en')">EN</button>
      </div>
      <div class="auth-links" id="guest-actions">
        <a class="btn btn-ghost" href="login.php" data-i18n="common.login">Login</a>
        <a class="btn btn-primary" href="register.php" data-i18n="common.register">Register</a>
      </div>
      <div class="user-menu" id="user-menu">
        <button class="user-menu-toggle" type="button" aria-haspopup="true" aria-expanded="false">
          <span class="user-avatar"><img id="user-avatar-img" src="" alt=""><span id="user-avatar-initial">U</span></span>
          <span class="user-name" id="user-menu-name">User</span>
          <i class="fas fa-chevron-down"></i>
        </button>
        <div class="user-dropdown">
          <a id="user-dashboard-link" href="dashboard/"><i class="fas fa-gauge-high"></i> <span data-i18n="landing.open_dashboard">Open Dashboard</span></a>
          <a class="admin-only" href="admin/"><i class="fas fa-user-shield"></i> <span id="admin-dashboard-label">Admin Dashboard</span></a>
        </div>
      </div>
    </div>
  </nav>
</header>

<main>
  <section class="hero">
    <div>
      <div class="eyebrow"><i class="fas fa-shield-alt"></i> <span data-i18n="landing.eyebrow">Secure operations workspace</span></div>
      <h1><span><?php echo $siteName; ?></span><br><span data-i18n="landing.hero_suffix">built for controlled access.</span></h1>
      <p class="lead"><?php echo $fullDesc; ?></p>
      <div class="hero-actions">
        <a class="btn btn-primary" id="dashboard-link" href="dashboard/"><i class="fas fa-gauge-high"></i> <span data-i18n="landing.open_dashboard">Open Dashboard</span></a>
        <a class="btn btn-ghost guest-hero-link" href="login.php"><i class="fas fa-right-to-bracket"></i> <span data-i18n="common.login">Login</span></a>
        <a class="btn btn-ghost guest-hero-link" href="register.php"><i class="fas fa-user-plus"></i> <span data-i18n="common.register">Register</span></a>
      </div>
      <div class="stats">
        <div class="stat"><strong>Token</strong><span data-i18n="landing.stat_sessions">device-bound sessions</span></div>
        <div class="stat"><strong>5s</strong><span data-i18n="landing.stat_online">live online checks</span></div>
        <div class="stat"><strong>Admin</strong><span data-i18n="landing.stat_control">central control panel</span></div>
      </div>
    </div>

    <div class="showcase">
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title"><img src="assets/img/logo_192.png" alt=""><span><?php echo $shortDesc; ?></span></div>
          <div class="status-pill" data-i18n="common.connected">Connected</div>
        </div>
        <div class="feature-list">
          <div class="feature-item"><i class="fas fa-fingerprint"></i><div><strong data-i18n="landing.feature_devices_title">Device-aware sessions</strong><p data-i18n="landing.feature_devices_desc">Tie each session to a verified device token.</p></div></div>
          <div class="feature-item"><i class="fas fa-users-cog"></i><div><strong data-i18n="landing.feature_users_title">User administration</strong><p data-i18n="landing.feature_users_desc">Manage accounts, roles, status, and device resets.</p></div></div>
          <div class="feature-item"><i class="fas fa-gear"></i><div><strong data-i18n="landing.feature_settings_title">Project settings</strong><p data-i18n="landing.feature_settings_desc">Control Google login, mail settings, site profile, and branding.</p></div></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="cards">
      <div class="info-card"><i class="fas fa-lock"></i><h3 data-i18n="landing.card_security_title">Security first</h3><p data-i18n="landing.card_security_desc">Session tokens are tied to the device, so clearing browser data requires logging in again.</p></div>
      <div class="info-card"><i class="fas fa-bolt"></i><h3 data-i18n="landing.card_ops_title">Unified operations</h3><p data-i18n="landing.card_ops_desc">A simple dashboard entry point for daily controlled operations.</p></div>
      <div class="info-card"><i class="fas fa-palette"></i><h3 data-i18n="landing.card_brand_title">Custom branding</h3><p data-i18n="landing.card_brand_desc">Update site information, logo, and favicon directly from settings.</p></div>
    </div>
  </section>
</main>

<script src="assets/js/i18n.js"></script>
<script src="assets/js/index.js"></script>
</body>
</html>
