<?php
session_start();

// جلب بيانات المدير من ملف JSON
$adminFile = "admin.json";
if(!file_exists($adminFile)){
    file_put_contents($adminFile, json_encode(['password'=>'1234'], JSON_PRETTY_PRINT));
}
$adminData = json_decode(file_get_contents($adminFile), true);
$adminPassword = $adminData['password'] ?? '1234';
$adminUsername = "admin";

// جلب بيانات الموظفين من ملف JSON
$workersFile = "workers.json";
if(!file_exists($workersFile)){
    file_put_contents($workersFile, json_encode([]));
}
$workers = json_decode(file_get_contents($workersFile), true);

$loginError = "";
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $role = $_POST['role'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if(empty($username) || empty($password)){
        $loginError = "يرجى إدخال اسم المستخدم وكلمة المرور";
    } else {
        if($role === "admin"){
            if($username === $adminUsername && $password === $adminPassword){
                $_SESSION['adminLogged'] = true;
                header("Location: dashboard.php");
                exit;
            } else {
                $loginError = "اسم المستخدم أو كلمة المرور غير صحيحة للمدير";
            }
        } else { // موظف
            $found = false;
            foreach($workers as $emp){
                if(($emp['username'] ?? '') === $username && ($emp['password'] ?? '') === $password){
                    $_SESSION['employeeLogged'] = true;
                    $_SESSION['employeeName'] = $emp['name'];
                    header("Location: employee_sales.php");
                    exit;
                }
            }
            $loginError = "اسم المستخدم أو كلمة المرور غير صحيحة للموظف";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تسجيل الدخول - صالون لمسة إبداعية</title>
<style>
body { font-family:'Cairo',sans-serif; background: linear-gradient(180deg, #000000, #111111); color:#fff; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; padding:0; }
header { text-align:center; margin-bottom:30px; }
header .logo-container { display:inline-block; border:4px solid gold; border-radius:50%; padding:5px; box-shadow:0 0 20px gold; margin-bottom:10px; }
header .logo-container img { width:100px; height:100px; border-radius:50%; object-fit:cover; }
header h1 { font-size:2rem; color:gold; margin:0; text-shadow:0 0 10px gold; }
.login-box { background:#111; padding:30px 25px; border-radius:15px; box-shadow:0 0 35px rgba(255,215,0,0.5); width:350px; max-width:90%; text-align:center; display:flex; flex-direction:column; align-items:center; transition:0.3s; }
.login-box:hover { box-shadow:0 0 50px rgba(255,215,0,0.8); }
h2 { color:gold; margin-bottom:20px; text-shadow:0 0 10px gold; font-size:1.5rem; }
.input-group { position:relative; margin:15px 0; width:100%; }
.input-group label { display:block; margin-bottom:5px; color:#ffd700; text-align:right; }
.input-group input, .input-group select { width:100%; padding:12px; border:none; border-radius:8px; background:#222; color:#fff; font-size:1rem; box-sizing:border-box; text-align:right; }
button { width:100%; padding:12px; margin-top:15px; background:gold; color:#111; font-weight:bold; border:none; border-radius:8px; cursor:pointer; transition:0.3s; font-size:1rem; }
button:hover { transform:translateY(-2px); box-shadow:0 0 20px gold; }
.error-msg { color:red; margin-top:10px; font-weight:bold; }
.add-user-btn { background:#2a5298; color:#fff; }
@media(max-width:480px){ .login-box { padding:20px 15px; } h2 { font-size:1.3rem; } .input-group input, .input-group select { font-size:0.95rem; padding:10px; } button { font-size:0.95rem; padding:10px; } }
</style>
</head>
<body>
<div style="display:flex; flex-direction:column; align-items:center; width:100%;">
<header>
    <div class="logo-container"><img src="logo.jpg" alt="Logo"></div>
    <h1>تسجيل الدخول</h1>
</header>

<div class="login-box">
    <h2>تسجيل الدخول</h2>
    <?php if($loginError): ?>
        <div class="error-msg"><?= $loginError ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="input-group">
            <label for="role">اختر النوع:</label>
            <select id="role" name="role">
                <option value="employee">موظف</option>
                <option value="admin">مدير</option>
            </select>
        </div>
        <div class="input-group">
            <label for="username">اسم المستخدم:</label>
            <input type="text" id="username" name="username" placeholder="اسم المستخدم">
        </div>
        <div class="input-group">
            <label for="password">كلمة المرور:</label>
            <input type="password" id="password" name="password" placeholder="كلمة المرور">
        </div>
        <button type="submit">دخول</button>
    </form>
</div>
</div>
</body>
</html>
