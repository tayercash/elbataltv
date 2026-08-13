# Google Login Security Update (ID Token Verification)

## ما تم إنجازه (في هذا المستودع)

1. **السيرفر** — `users/accounts/accounts.php`:
   - `login_account_with_google` أصبح **يرفض أي طلب بلا `id_token` صالح** (رمز خطأ 403).
   - دالة جديدة `verify_google_id_token()` تتحقق من التوكن عبر `https://oauth2.googleapis.com/tokeninfo` وتفحص:
     - `iss` = accounts.google.com
     - `exp` (غير منتهي)
     - `email_verified` = true
     - `aud` (اختياري): يتحقق من المطابقة مع `$google_oauth_client_id` في `config_db.php` عند ملئه
     - تُستخرج منها `sub` و `email` كمرجع موثوق
   - يتحقق أن الـ `gid` و `email` المُرسلَين من التطبيق مطابقان لما في التوكن (403 عند عدم التطابق).

2. **الواجهة** — `app/files/js/homepagedl.js`:
   - `on_google_signed_success(personID, personName, personEmail, personImg, idToken)`.
   - عند الإرسال: `gid` يُشفَّر بـ `mou_custom_encode()` و `email` يُشفَّر بـ `mou_custom_encode()`، و`username` يُرسل عاديًا، و`id_token` يُرسل كما هو.
   - معالج Electron (`g_profile`) يمرر `profile.idToken`.
   - `Start_Login` يستخدم التوكين المحفوظ أولًا (لا يطلب إعادة مصادقة جوجل عند وجود token).

## بروتوكول الطلب (موحّد لكل العملاء)

POST إلى `accounts/accounts.php`:

| الحقل | القيمة |
|---|---|
| `action` | `login_account_with_google` |
| `id_token` | ID token الخام من Google (غير مشفر) |
| `gid` | `mou_custom_encode(sub)` — **مشفر** |
| `email` | `mou_custom_encode(email)` — **مشفر** |
| `username` | الاسم عادي (غير مشفر) |
| `avatar_code` | كود الصورة الرمزية |
| `g_icon` | رابط صورة Google (قد يكون null) |
| `u_id` | dev_id عادي |
| `u_name` | dev_name عادي |
| `u_client` | عادي |

## المطلوب تعديله خارج هذا المجلد

### 1. أندرويد — `MainActivity.java`
- فعّل `requestIdToken`:
```java
GoogleSignInOptions gso = new GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
    .requestEmail()
    .requestIdToken("CLIENT_ID هنا من مشروع Google Cloud")
    .build();
```
- بعد النجاح، مرر `getIdToken()` إلى JS (المعامل الخامس):
```java
GoogleSignInAccount acct = result.getSignInAccount();
mWebView.evaluateJavascript("on_google_signed_success('" + acct.getId() + "','" + name + "','" + email + "','" + photo + "','" + acct.getIdToken() + "');", null);
```
- إذا كان أندرويد يرسل الطلب من جافا مباشرة (لا عبر `on_google_signed_success`): يجب أن يشفّر `gid` و`email` بـ `mou_custom_encode` (Base64 مبدّل بترتيب `ZYX...` إلى `ABC...` — انظر `mou_custom_encode` في `homepagedl.js:1011`).

### 2. إلكترون — `main.js` (مستودع الإلكترون)
- كود `signInWithGoogle` يجب أن يجلب `id_token` ويمرره في حدث `g_profile`:
```js
win.webContents.send('g_profile', { id: user.id, name: user.name, email: user.email, picture: user.picture, idToken: token });
```
- التشفير يحدث في `on_google_signed_success` تلقائيًا — مرر القيم عادية.

### 3. تحسين أمني اختياري — `config_db.php`
- املأ `$google_oauth_client_id = "....apps.googleusercontent.com";` لتفعيل التحقق من `aud` في التوكن.

## ملاحظة
- بعد تحديث التطبيق (Android + Electron)، سيتحقق السيرفر من كل تسجيل دخول Google.
- أي إصدار قديم من التطبيق يرسل بلا `id_token` سيُرفض (403) — تُحدَّث التطبيقات أولًا ثم يُرفع هذا الكود للحماية الكاملة.
- أهم سبب لرسالة "Google verification mismatch / 403" بعد الترقية: إرسال `gid` أو `email` غير مشفّرين، أو غياب `id_token`.
