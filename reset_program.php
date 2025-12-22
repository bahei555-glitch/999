<?php
session_start();

// التأكد من تسجيل دخول المدير
if(!isset($_SESSION['adminLogged']) || !$_SESSION['adminLogged']){
    header("Location: index.php");
    exit;
}

$adminFile = "admin.json";

// جلب الباسورد الحالي
if(!file_exists($adminFile)){
    file_put_contents($adminFile, json_encode(['password'=>'1234'], JSON_PRETTY_PRINT));
}
$adminData = json_decode(file_get_contents($adminFile), true);
$currentPass = $adminData['password'] ?? '1234';

$message = "";

if(isset($_POST['resetProgram'])){
    $enteredPass = $_POST['adminPass'] ?? '';
    if($enteredPass === $currentPass){
        // قائمة ملفات JSON التي سيتم تصفيرها
        $dataFiles = ['sales.json','expenses.json','salf.json','manager_withdrawals.json','employee_withdrawals.json','advances.json'];

        foreach($dataFiles as $file){
            if(file_exists($file)){
                file_put_contents($file, json_encode([], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            }
        }
        $message = "تم تصفير البرنامج بنجاح!";
    } else {
        $message = "كلمة المرور غير صحيحة!";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>تصفير البرنامج - صالون لمسة إبداعية</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family:'Cairo',sans-serif; background:#111; color:#fff; display:flex; flex-direction:column; align-items:center; padding:40px; }
h1 { color:gold; margin-bottom:20px; }
form { display:flex; flex-direction:column; gap:15px; background:#222; padding:30px; border-radius:15px; width:350px; max-width:90%; text-align:center; }
input { padding:10px; border-radius:10px; border:none; font-size:1rem; }
button { padding:12px; border-radius:10px; border:none; background:gold; color:#111; font-weight:bold; cursor:pointer; transition:0.3s; }
button:hover { transform:translateY(-2px); box-shadow:0 0 15px gold; }
.message { margin-top:20px; font-weight:bold; color:lightgreen; text-align:center; }
.error { margin-top:20px; font-weight:bold; color:red; text-align:center; }
.back-btn { margin-top:30px; padding:10px 20px; background:#2a5298; color:#fff; border-radius:10px; text-decoration:none; }
@media(max-width:480px){ form { padding:20px 15px; } input, button { font-size:0.95rem; padding:10px; } }
</style>
</head>
<body>

<h1>تصفير البرنامج</h1>

<form method="post">
    <input type="password" name="adminPass" placeholder="أدخل كلمة مرور المدير" required>
    <button type="submit" name="resetProgram">تصفير البرنامج</button>
</form>

<?php if($message): ?>
    <div class="<?= $message === 'تم تصفير البرنامج بنجاح!' ? 'message' : 'error' ?>"><?= $message ?></div>
<?php endif; ?>

<a href="dashboard.php" class="back-btn">العودة للوحة التحكم</a>

</body>
</html>
