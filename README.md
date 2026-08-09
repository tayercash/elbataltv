# Mou Default

نظام إدارة متكامل متعدد اللغات مع دعم تسجيل الدخول عبر Google وإدارة الأجهزة.

## المميزات

- تسجيل الدخول وإنشاء حساب (بريد إلكتروني + Google OAuth)
- لوحة تحكم المستخدم والأدمن
- تحميل الصور الرمزية (Avatar) تلقائيًا
- نظام الأجهزة المسموح بها (حد أقصى للأجهزة لكل مستخدم)
- إعدادات SMTP للبريد الإلكتروني
- تفعيل الحساب عبر البريد (Email Verification)
- واجهة متعددة اللغات (عربي / English)
- ثيم dark / light
- إدارة الموقع كاملة من لوحة الأدمن

## المتطلبات

- PHP 8.0+
- MySQL 5.7+
- Apache (mod_rewrite مفعل)
- XAMPP / WAMP / أي بيئة ويب

## التثبيت

1. انسخ المشروع إلى `htdocs/moudefault`
2. شغّل Apache + MySQL
3. افتح `http://localhost/moudefault/install.php`
4. اتبع خطوات التثبيت
5. بعد التثبيت، سيتم إنشاء ملف `installed.lock`

### Virtual Host (اختياري)

```
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/moudefault"
    ServerName moudef.org
</VirtualHost>
```

ثم أضف `127.0.0.1 moudef.org` إلى ملف `hosts`.

## هيكل المشروع

```
moudefault/
├── admin/              # لوحة تحكم الأدمن
├── assets/             # ملفات CSS / JS / Fonts
│   ├── css/
│   ├── js/
│   ├── fonts/
│   └── pages/          # صفحات محملة عبر AJAX
├── dashboard/          # لوحة تحكم المستخدم
├── Back/               # نسخة احتياطية (مُستبعد من git)
├── api.php             # API المركزي
├── avatar.php          # توليد الصور الرمزية
├── config.php          # الإعدادات + BASE_PATH التلقائي
├── login.php
├── register.php
├── index.php
├── install.php
└── database.sql
```

## BASE_PATH

`BASE_PATH` يتم اكتشافه تلقائيًا في `config.php` بمقارنة `__DIR__` مع `DOCUMENT_ROOT`:
- subfolder (`localhost/moudefault`): `BASE_PATH = "/moudefault"`
- virtual host (`moudef.org`): `BASE_PATH = ""`

كل المسارات (API, Avatars, Assets) تُبنى ديناميكيًا باستخدام `BASE_PATH`.

## API

`api.php` يستقبل طلبات POST مع `action` في query string.

### المصادقة
- `register` - إنشاء حساب
- `login` - تسجيل الدخول
- `google_login` - تسجيل الدخول عبر Google
- `forgot_password` - نسيت كلمة المرور
- `reset_password` - إعادة تعيين كلمة المرور

### الملف الشخصي
- `get_profile` - جلب بيانات الملف الشخصي
- `update_profile` - تحديث الملف الشخصي (الاسم، الصورة، الجنس، الدولة، كلمة المرور)

### الإدارة
- `get_users` - جلب قائمة المستخدمين
- `update_user_status` - تفعيل/تعطيل حساب
- `update_user_role` - تغيير صلاحية مستخدم
- `update_user_max_devices` - تعديل حد الأجهزة
- `get_setting` / `save_setting` - إعدادات الموقع
- `save_site_profile` - رفع صورة الموقع وتحديث البيانات

### الجلسات
- `verify_session` - التحقق من صحة الجلسة
- `heartbeat` - نبض heartbeat (كل 5 ثوان)

## الصور الرمزية (Avatars)

الصور الرمزية تُخزّن كـ **seed** فقط في قاعدة البيانات (مثل `m-1`).  
عند العرض، تُبنى تلقائيًا باستخدام `BASE_PATH + '/avatar.php?seed=' + seed`.

### أنواع الصور المخزنة

| النوع | مثال المخزون | كيف يُعرض |
|-------|--------------|-----------|
| Avatar مولد | `m-94` | `/moudefault/avatar.php?seed=m-94` |
| Google Photo | `https://lh3.googleusercontent.com/...` | كما هو |
| قديم (transition) | `http://moudef.org/avatar.php?seed=m-1` | يُكشف ويُعالج |

## التطوير

### إضافة صفحة جديدة

1. أنشئ الملف في `assets/pages/`
2. أضف الرابط في `navigation.js`
3. استخدم `BASE_PATH` لكل المسارات

### قواعد التعديل

- لا تكتب دومين أو مسار ثابت أبدًا
- استخدم `BASE_PATH` لكل الروابط
- ممنوع تخزين رابط كامل في قاعدة البيانات للصور المحلية

## الترخيص

MIT
