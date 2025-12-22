<?php
session_start();

// التأكد من تسجيل دخول المدير
if(!isset($_SESSION['adminLogged']) || !$_SESSION['adminLogged']){
    header("Location: index.php");
    exit;
}

$adminFile = "admin.json";

// إنشاء ملف الباسورد إذا لم يكن موجود
if(!file_exists($adminFile)){
    file_put_contents($adminFile, json_encode(['password'=>'1234'], JSON_PRETTY_PRINT));
}

// جلب الباسورد الحالي من ملف JSON
$adminData = json_decode(file_get_contents($adminFile), true);
$currentPass = $adminData['password'] ?? '1234';

$msg = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $oldPass = $_POST['currentPass'] ?? '';
    $newPass = $_POST['newPass'] ?? '';
    $confirmPass = $_POST['confirmPass'] ?? '';

    if(!$oldPass || !$newPass || !$confirmPass){
        $msg = "املأ جميع الحقول";
    } elseif($oldPass !== $currentPass){
        $msg = "الباسورد الحالي غير صحيح";
    } elseif($newPass !== $confirmPass){
        $msg = "الباسورد الجديد غير متطابق";
    } else {
        // تحديث الباسورد في admin.json
        $adminData['password'] = $newPass;
        file_put_contents($adminFile, json_encode($adminData, JSON_PRETTY_PRINT));

        // تحديث الباسورد المتغير المستخدم في تسجيل الدخول فورًا
        $currentPass = $newPass;

        $msg = "تم تغيير الباسورد بنجاح";
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تغيير باسورد المدير - صالون لمسة إبداعية</title>
<style>
body { font-family:'Cairo',sans-serif; background: linear-gradient(180deg,#000,#111); color:#fff; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; padding:0; }
header { text-align:center; margin-bottom:30px; }
header .logo-container { display:inline-block; border:4px solid gold; border-radius:50%; padding:5px; box-shadow:0 0 20px gold; margin-bottom:10px; }
header .logo-container img { width:100px; height:100px; border-radius:50%; object-fit:cover; }
header h1 { font-size:2rem; color:gold; margin:0; text-shadow:0 0 10px gold; }
.form-box { background:#111; padding:30px 25px; border-radius:15px; box-shadow:0 0 35px rgba(255,215,0,0.5); width:350px; max-width:90%; text-align:center; display:flex; flex-direction:column; align-items:center; transition:0.3s; }
.form-box:hover { box-shadow:0 0 50px rgba(255,215,0,0.8); }
h2 { color:gold; margin-bottom:20px; text-shadow:0 0 10px gold; font-size:1.5rem; }
.input-group { position:relative; margin:15px 0; width:100%; }
.input-group label { display:block; margin-bottom:5px; color:#ffd700; text-align:right; }
.input-group input { width:100%; padding:12px; border:none; border-radius:8px; background:#222; color:#fff; font-size:1rem; box-sizing:border-box; text-align:right; }
button { width:100%; padding:12px; margin-top:15px; background:gold; color:#111; font-weight:bold; border:none; border-radius:8px; cursor:pointer; transition:0.3s; font-size:1rem; }
button:hover { transform:translateY(-2px); box-shadow:0 0 20px gold; }
.back-btn { background:#2a5298; color:#fff; margin-top:10px; }
.msg { color:lime; font-weight:bold; margin-top:10px; }
.error { color:red; font-weight:bold; margin-top:10px; }
@media(max-width:480px){ .form-box { padding:20px 15px; } h2 { font-size:1.3rem; } .input-group input { font-size:0.95rem; padding:10px; } button { font-size:0.95rem; padding:10px; } }
</style>
</head>
<body>
<div style="display:flex; flex-direction:column; align-items:center; width:100%;">
<header>
    <div class="logo-container"><img src="logo.jpg" alt="Logo"></div>
    <h1>تغيير باسورد المدير</h1>
</header>
<div class="form-box">
    <h2>تغيير كلمة المرور</h2>
    <?php if($msg): ?>
        <div class="<?= strpos($msg,'نجاح')!==false ? 'msg' : 'error' ?>"><?= $msg ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="input-group">
            <label for="current">الباسورد الحالي:</label>
            <input type="password" id="current" name="currentPass" placeholder="الباسورد الحالي" required>
        </div>
        <div class="input-group">
            <label for="newPass">الباسورد الجديد:</label>
            <input type="password" id="newPass" name="newPass" placeholder="الباسورد الجديد" required>
        </div>
        <div class="input-group">
            <label for="confirmPass">تأكيد الباسورد الجديد:</label>
            <input type="password" id="confirmPass" name="confirmPass" placeholder="تأكيد الباسورد" required>
        </div>
        <button type="submit">تغيير الباسورد</button>
    </form>
    <button class="back-btn" onclick="window.location.href='dashboard.php'">رجوع للوحة التحكم</button>
</div>
</div>
</body>
</html>
