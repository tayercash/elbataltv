<?php
error_reporting(0);
ini_set('display_errors', 'Off');
include "../config_db.php";
$messages = [];

// إعدادات الدخول: بتحمل من ملف خارج الـ web root (أنشئه اول مرة)
// فلا تبقى كلمة السر مكتوبة في ملف عام يقدر أي حد يشوفه
$credentials_file = dirname(__DIR__, 2) . '/credentials/admen_credentials.php';
if (!file_exists($credentials_file)) {
  @mkdir(dirname($credentials_file), 0770, true);
  // قيمة أولية مشفرة - غيّرها فوراً من ملف credentials
  file_put_contents($credentials_file, "<?php\n\$admen_username = 'mouscripts@gmail.com';\n\$admen_password_hash = '" . password_hash('Mozo@mozo1', PASSWORD_DEFAULT) . "';\n");
}
include $credentials_file;

$admen_username = isset($admen_username) ? $admen_username : '';
$admen_password_hash = isset($admen_password_hash) ? $admen_password_hash : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $user_name = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
  $user_pass = !empty($_POST['user_pass']) ? $_POST['user_pass'] : '';

  $stop = false;

  if ($user_name == "") {
    $stop = true;
    addmsgs("من فضلك تحقق من بيانات تسجيل الدخول !");
  }
  if ($user_pass == "") {
    $stop = true;
    addmsgs("من فضلك تحقق من بيانات تسجيل الدخول !");
  }

  if ($stop == false) {
    // حد محاولات الدخول لمنع الـ Brute Force
    if (session_status() === PHP_SESSION_NONE) {
      if (session_name() !== 'ELBATAL_ADMIN') {
        session_name('ELBATAL_ADMIN');
      }
      session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
      ]);
      session_start();
    }
    $now = time();
    $max_attempts = 5;
    $lock_seconds = 15 * 60; // 15 دقيقة

    $attempts = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0;
    $first_attempt = isset($_SESSION['login_first_attempt']) ? $_SESSION['login_first_attempt'] : $now;

    if ($attempts >= $max_attempts && ($now - $first_attempt) < $lock_seconds) {
      $wait = $lock_seconds - ($now - $first_attempt);
      addmsgs("تم تجاوز حد المحاولات، حاول بعد " . ceil($wait / 60) . " دقيقة");
    } else {
      // مقارنة آمنة: username بالـ hash_equals + كلمة المرور بـ password_verify
      if (hash_equals($admen_username, $user_name) && password_verify($user_pass, $admen_password_hash)) {
        $_SESSION["login_attempts"] = 0;
        unset($_SESSION["login_first_attempt"]);
        session_regenerate_id(true); // منع الـ session fixation
        $_SESSION["can_join"] = true;

        header("Location: index.php");
        exit;
      } else {
        $_SESSION["login_attempts"] = ($attempts + 1);
        if (!isset($_SESSION["login_first_attempt"])) {
          $_SESSION["login_first_attempt"] = $now;
        }
        addmsgs("من فضلك تحقق من بيانات تسجيل الدخول !");
      }
    }
  }

}

function addmsgs(...$msgs)
{
  global $messages;
  foreach ($msgs as $value) {
    array_push($messages, $value);
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zero store | Login</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <!-- Google Fonts Roboto -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" />
  <!-- MDB -->
  <link rel="stylesheet" href="files/css/mdb.rtl.min.css" />

  <link rel="stylesheet" type="text/css" href="files/css/login.css">
</head>

<body>


  <section class="vh-100 gradient-custom">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
          <div class="card bg-dark text-white" style="border-radius: 1rem;">
            <div class="card-body p-5 text-center">

              <div class="mb-md-2 mt-md-4 pb-5">
                <form id="login" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

                  <h2 class="fw-bold mb-2 text-uppercase">تسجيل الدخول</h2>
                  <p class="text-white-50 mb-5">من فضلك ادخل اسم المستخدم والباسورد !</p>

                  <div class="form-outline form-white mb-4">

                    <input type="text" id="user_name" name="user_name" class="form-control form-control-lg" />
                    <label class="form-label" for="user_name">اسم المستخدم</label>
                  </div>

                  <div class="form-outline form-white mb-4">
                    <input type="password" id="user_pass" name="user_pass" class="form-control form-control-lg" />
                    <label class="form-label" for="user_pass">الباسورد</label>
                  </div>

                  <button class="btn btn-outline-light btn-lg px-5" type="submit">الدخول</button>
                </form>
              </div>
              <?php
              if (count($messages) > 0) {
                for ($i = 0; $i < count($messages); $i++) {
                  echo '<div class="alert alert-danger mt-2" role="alert" data-mdb-color="danger">
              <i class="fas fa-times-circle me-3"></i>' . $messages[$i] . '</div>';
                }
              }
              ?>
            </div>
          </div>

        </div>
      </div>
  </section>
  <!-- MDB -->
  <script type="text/javascript" src="files/js/mdb.min.js"></script>
</body>

</html>