# Google Login Security Update (ID Token Verification)

## ما تم إنجازه (في هذا المستودع)

1. **السيرفر** — `users/accounts/accounts.php`:
   - `login_account_with_google` أصبح **يرفض أي طلب بلا `id_token` صالح** (رمز خطأ 403).
   - دالة جديدة `verify_google_id_token()` تتحقق من التوكن عبر `https://oauth2.googleapis.com/tokeninfo` وتفحص:
     - `iss` = accounts.google.com
     - `exp` (غير منتهي)
     - `email_verified` = true
     - تُستخرج منها `sub` و `email` كمرجع موثوق
   - تطابق اختياري: إن أرسل التطبيق `gid` أو `email` غير مطابقين لما في التوكن → رفض (403).

2. **الواجهة** — `app/files/js/homepagedl.js`:
   - `on_google_signed_success(personID, personName, personEmail, personImg, idToken)` يرسل الآن `id_token` في الطلب.
   - معالج Electron (`g_profile`) يمرر `profile.idToken`.

## المطلوب تعديله خارج هذا المجلد

### 1. أندرويد — `MainActivity.java`
الكود الحالي يمرر `acct.getId()` (الـ sub) فقط إلى `on_google_signed_success`. مطلوب جلب ID token وإضافته:

- عند إنشاء `GoogleSignInOptions` اجعل الـ `requestIdToken` مفعّلاً:
```java
GoogleSignInOptions gso = new GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
    .requestEmail()
    .requestIdToken("CLIENT_ID هنا من مشروع Google Cloud")
    .build();
```

- بعد نجاح الدخول، مرر التوكن إلى JS:
```java
GoogleSignInAccount acct = result.getSignInAccount();
String idToken = acct.getIdToken();   // مطلوب
String personID = acct.getId();       // الـ sub (كما الآن)
mWebView.evaluateJavascript("on_google_signed_success('" + personID + "','" + name + "','" + email + "','" + photo + "','" + idToken + "');", null);
```

### 2. إلكترون — `main.js` (مستودع الإلكترون)
كود `signInWithGoogle` في ملف main.js الخاص بالإلكترون:
- استخدم `google.auth.getToken()` أو مشروع OAuth يجلب `id_token`.
- مرره في حدث `g_profile`:
```js
win.webContents.send('g_profile', { id: user.id, name: user.name, email: user.email, picture: user.picture, idToken: token });
```

## ملاحظة
- بعد تحديث التطبيق (Android + Electron)، سيتحقق السيرفر من كل تسجيل دخول Google.
- أي إصدار قديم من التطبيق يرسل بلا `id_token` سيُرفض (403) — تُحدَّث التطبيقات أولاً ثم يُرفع هذا الكود للحماية الكاملة.
