<?php
session_start();

// التأكد من تسجيل دخول الموظف
if(!isset($_SESSION['employeeLogged']) || !$_SESSION['employeeLogged']){
    header("Location: index.php");
    exit;
}

$employeeName = $_SESSION['employeeName'] ?? 'الموظف';
$salesFile = "sales.json";

// معالجة تسجيل البيع
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $amount = floatval($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? '';
    if($amount > 0 && in_array($method, ['cash','card'])){
        $sale = [
            'employee' => $employeeName,
            'amount' => $amount,
            'method' => $method,
            'date' => date('Y-m-d'),
            'time' => date('h:i A')
        ];

        $sales = file_exists($salesFile) ? json_decode(file_get_contents($salesFile), true) : [];
        $sales[] = $sale;
        file_put_contents($salesFile, json_encode($sales, JSON_PRETTY_PRINT));

        $successMsg = "تم تسجيل عملية $method بقيمة $amount";
    } else {
        $errorMsg = "يرجى إدخال مبلغ صحيح واختيار طريقة الدفع";
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تسجيل المبيعات - صالون لمسة إبداعية</title>
<style>
body {
    font-family:'Cairo',sans-serif;
    background: linear-gradient(180deg,#000,#111);
    color:#fff;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    min-height:100vh;
    margin:0;
    padding:40px 0;
}
header { text-align:center; margin-bottom:20px; }
header .logo-container { display:inline-block; border:4px solid gold; border-radius:50%; padding:5px; box-shadow:0 0 20px gold; margin-bottom:10px; }
header .logo-container img { width:100px; height:100px; border-radius:50%; object-fit:cover; }
header h1 { font-size:2rem; color:gold; margin:0; text-shadow:0 0 10px gold; }
.button-top { display:flex; justify-content:center; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
.button-top button { width:160px; height:50px; border:none; border-radius:8px; font-weight:bold; font-size:1rem; cursor:pointer; transition: 0.3s; }
.button-top button:hover { transform: translateY(-2px); box-shadow: 0 0 15px gold; }
.view-btn { background-color:#44bd32; color:white; }
.logout-btn { background-color:red; color:white; }
.sales-box { background:#111; padding:30px 25px; border-radius:15px; box-shadow:0 0 35px rgba(255,215,0,0.5); width:400px; max-width:90%; text-align:center; display:flex; flex-direction:column; align-items:center; }
.sales-box:hover { box-shadow:0 0 50px rgba(255,215,0,0.8); }
h2 { color:gold; margin-bottom:20px; text-shadow:0 0 10px gold; font-size:1.5rem; }
.input-row { display: flex; gap:10px; width:100%; margin:15px 0; }
.input-row input, .input-row button { flex:1; height:60px; border-radius:8px; border:none; font-size:1rem; font-weight:bold; text-align:center; display:flex; align-items:center; justify-content:center; cursor:pointer; position: relative; transition: all 0.2s ease; }
.input-row input { background:#222; color:#fff; }
.cash-btn { background:#2ecc71; color:#fff; }
.card-btn { background:#3498db; color:#fff; }
.input-row button:hover { transform: translateY(-3px); box-shadow: 0 8px 20px gold; }
.input-row button:active { transform: translateY(1px); box-shadow: 0 3px 6px #00000066; }
.input-row button .icon-fly { position: absolute; top:50%; left:50%; transform: translate(-50%, -50%); opacity:0; font-size:20px; pointer-events:none; animation: fly 0.6s ease forwards; }
@keyframes fly { 0% { transform: translate(-50%, -50%) scale(1); opacity:1; } 50% { transform: translate(-50%, -100%) scale(1.5); opacity:1; } 100% { transform: translate(-50%, -150%) scale(0.5); opacity:0; } }
@media(max-width:480px){ .input-row input, .input-row button { height:50px; font-size:0.95rem; } }
.success-msg { color:lime; margin-top:10px; font-weight:bold; }
.error-msg { color:red; margin-top:10px; font-weight:bold; }
</style>
</head>
<body>
<div style="display:flex; flex-direction:column; align-items:center; width:100%;">
<header>
    <div class="logo-container"><img src="logo.jpg" alt="Logo"></div>
    <h1>تسجيل المبيعات</h1>
</header>

<div class="button-top">
    <button class="view-btn" onclick="location.href='employee_sales_list.php'">عرض المبيعات</button>
    <button class="logout-btn" onclick="location.href='logout.php'">خروج</button>
</div>

<div class="sales-box">
    <h2>مرحبًا، <?= htmlspecialchars($employeeName) ?></h2>

    <?php if(isset($successMsg)): ?>
        <div class="success-msg"><?= $successMsg ?></div>
    <?php endif; ?>
    <?php if(isset($errorMsg)): ?>
        <div class="error-msg"><?= $errorMsg ?></div>
    <?php endif; ?>

    <form method="POST" class="input-row">
        <input type="number" name="amount" placeholder="المبلغ" required step="0.01">
        <button type="submit" name="method" value="cash" class="cash-btn">💵 كاش</button>
        <button type="submit" name="method" value="card" class="card-btn">💳 شبكة</button>
    </form>
</div>
</div>
</body>
</html>
