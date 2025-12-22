<?php
session_start();

// الباسورد الافتراضي للمدير
$adminPassword = '1234';

// معالجة تسجيل الدخول
$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $inputPass = $_POST['password'] ?? '';
    if($inputPass === $adminPassword){
        $_SESSION['admin_logged'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = 'كلمة المرور غير صحيحة!';
    }
}

// إذا المدير مسجل دخول سابقًا، يتم توجيهه مباشرة
if(isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true){
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>دخول المدير - صالون لمسة إبداعية</title>
<style>
body {
    font-family:'Cairo',sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
    background: linear-gradient(180deg, #000, #111);
    color:#fff;
}
.login-box {
    background:#222;
    padding:40px 30px;
    border-radius:15px;
    width:350px;
    text-align:center;
    box-shadow:0 0 30px rgba(255,215,0,0.5);
}
.login-box:hover { box-shadow:0 0 50px rgba(255,215,0,0.8); }
.login-box img {
    width:100px;
    height:100px;
    border-radius:50%;
    object-fit:cover;
    margin-bottom:15px;
    border:3px solid gold;
    box-shadow:0 0 15px gold;
}
h2 { color:gold; margin-bottom:25px; font-size:1.5rem; text-shadow:0 0 10px gold; }
.input-group { display:flex; flex-direction:column; align-items:flex-end; margin-bottom:15px; text-align:right; }
.input-group input {
    width:100%;
    box-sizing:border-box;
    text-align:right;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#333;
    color:#fff;
    font-size:1rem;
}
button {
    width:100%;
    padding:12px;
    margin-top:10px;
    background:gold;
    color:#111;
    font-weight:bold;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:1rem;
    transition:0.3s;
}
button:hover { transform:translateY(-2px); box-shadow:0 0 20px gold; }
.error-msg { color:red; margin-bottom:10px; }
@media(max-width:480px){
    .login-box { width:90%; padding:25px; }
    h2 { font-size:1.3rem; }
    .input-group input { font-size:0.95rem; padding:10px; }
    button { font-size:0.95rem; padding:10px; }
}
</style>
</head>
<body>

<div class="login-box">
    <img src="logo.jpg" alt="Logo">
    <h2>دخول المدير</h2>

    <?php if($error): ?>
    <div class="error-msg"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <input type="password" name="password" placeholder="كلمة المرور" required>
        </div>
        <button type="submit">دخول</button>
    </form>
</div>

</body>
</html>
