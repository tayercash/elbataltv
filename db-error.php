<!DOCTYPE html>
<html class="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Database Error</title>
<link rel="stylesheet" href="assets/fonts/font-awesome/css/all.min.css">
<link rel="stylesheet" href="assets/fonts/noto-sans-arabic/font.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Noto Sans Arabic',sans-serif; }
html { background:#0d0d0e; width:100%; height:100%; }
body {
  min-height:100vh; display:flex; align-items:center; justify-content:center;
  background:radial-gradient(circle at center,#1c1808 0%,#0d0d0e 80%);
  padding:20px; position:relative;
}
.bg-glow {
  position:fixed; width:350px; height:350px;
  background:radial-gradient(circle,rgba(239,68,68,0.12) 0%,transparent 70%);
  top:50%; left:50%; transform:translate(-50%,-50%);
  filter:blur(60px); z-index:0;
}
.error-card {
  background:rgba(18,18,20,0.75);
  backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px);
  border:1px solid rgba(239,68,68,0.2); border-radius:24px;
  padding:40px; max-width:480px; width:100%; text-align:center;
  position:relative; z-index:1;
  box-shadow:0 20px 50px rgba(0,0,0,0.6);
}
.error-card .icon {
  width:72px; height:72px; border-radius:50%;
  background:rgba(239,68,68,0.1);
  display:flex; align-items:center; justify-content:center;
  margin:0 auto 20px; font-size:2rem; color:#ef4444;
  border:2px solid rgba(239,68,68,0.2);
}
.error-card h1 { color:#fff; font-size:1.4rem; font-weight:700; margin-bottom:8px; }
.error-card p { color:#9e9e9e; font-size:0.9rem; line-height:1.7; margin-bottom:24px; }
.error-card .error-detail {
  background:rgba(239,68,68,0.05);
  border:1px solid rgba(239,68,68,0.1);
  border-radius:12px; padding:12px 16px;
  font-size:0.8rem; color:#ef4444; direction:ltr;
  word-break:break-all; margin-bottom:24px;
  text-align:left; font-family:monospace;
}
.retry-btn {
  display:inline-flex; align-items:center; gap:8px;
  padding:12px 28px; border-radius:12px; border:none;
  background:linear-gradient(135deg,#ffcc00,#ff9900); color:#0b0a02;
  font-size:0.95rem; font-weight:700; cursor:pointer;
  text-decoration:none; transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
  box-shadow:0 4px 15px rgba(255,204,0,0.2);
}
.retry-btn:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(255,204,0,0.35); }
.error-detail.hidden { display:none; }
.toggle-detail {
  background:none; border:none; color:#666; font-size:0.8rem;
  cursor:pointer; text-decoration:underline; padding:0; margin-bottom:16px;
  display:inline-block;
}
.toggle-detail:hover { color:#ffcc00; }
</style>
</head>
<body>
<div class="bg-glow"></div>
<div class="error-card">
  <div class="icon"><i class="fas fa-database"></i></div>
  <h1>Database Connection Error</h1>
  <p>تعذر الاتصال بقاعدة البيانات. الرجاء التحقق من إعدادات الاتصال والمحاولة مرة أخرى.</p>
  <?php if (isset($_GET['msg'])): ?>
  <div class="error-detail" id="detailBox"><?php echo htmlspecialchars($_GET['msg']); ?></div>
  <?php endif; ?>
  <a href="<?php echo htmlspecialchars($_GET['ref'] ?? '/'); ?>" class="retry-btn">
    <i class="fas fa-redo"></i> إعادة المحاولة
  </a>
</div>
</body>
</html>
