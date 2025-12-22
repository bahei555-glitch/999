<?php
session_start();

// حماية الدخول
if(!isset($_SESSION['adminLogged']) || $_SESSION['adminLogged'] !== true){
    header("Location: login.php");
    exit;
}

// ملفات البيانات
$salesFile       = 'sales.json';
$withdrawalsFile = 'manager_withdrawals.json';
$salfFile        = 'salf.json';
$expensesFile    = 'expenses.json';

// قراءة البيانات
$salesData   = file_exists($salesFile) ? json_decode(file_get_contents($salesFile), true) : [];
$withdrawals = file_exists($withdrawalsFile) ? json_decode(file_get_contents($withdrawalsFile), true) : [];
$salfData    = file_exists($salfFile) ? json_decode(file_get_contents($salfFile), true) : [];
$expenses    = file_exists($expensesFile) ? json_decode(file_get_contents($expensesFile), true) : [];

/* ===============================
   مبيعات كاش / شبكة (حل نهائي)
   =============================== */
$salesCash = 0;
$salesNetwork = 0;

foreach($salesData as $s){
    $amount = floatval($s['amount'] ?? 0);
    $method = strtolower(trim($s['method'] ?? ''));

    if($method === 'cash' || $method === 'كاش'){
        $salesCash += $amount;
    }

    if($method === 'network' || $method === 'شبكة' || $method === 'card'){
        $salesNetwork += $amount;
    }
}

/* ===============================
   سحوبات المدير
   =============================== */
$withdrawCash = 0;
$withdrawNetwork = 0;

foreach($withdrawals as $w){
    $amount = floatval($w['amount'] ?? 0);
    $type   = strtolower(trim($w['type'] ?? ''));

    if($type === 'cash')    $withdrawCash += $amount;
    if($type === 'network') $withdrawNetwork += $amount;
}

/* ===============================
   السلف والمصروفات
   =============================== */
$totalSalf = array_sum(array_map(fn($s)=>floatval($s['amount'] ?? 0), $salfData));
$totalExpenses = array_sum(array_map(fn($e)=>floatval($e['amount'] ?? 0), $expenses));

/* ===============================
   المتبقي
   =============================== */
$remainCash    = $salesCash - $withdrawCash - $totalExpenses - $totalSalf;
$remainNetwork = $salesNetwork - $withdrawNetwork;
$totalRemain   = $remainCash + $remainNetwork;

/* ===============================
   مقارنة شهرية
   =============================== */
$now = new DateTime();
$currentMonth = $now->format('Y-m');
$prevMonth = (new DateTime('first day of last month'))->format('Y-m');

$currentSales = 0;
$prevSales = 0;

foreach($salesData as $s){
    $amount = floatval($s['amount'] ?? 0);
    $date   = $s['date'] ?? '';

    if(substr($date,0,7) === $currentMonth) $currentSales += $amount;
    if(substr($date,0,7) === $prevMonth)    $prevSales += $amount;
}

/* ===============================
   تقرير اليوم
   =============================== */
$today = date('Y-m-d');
$dailyReport = 0;

foreach($salesData as $s){
    if(substr(($s['date'] ?? ''),0,10) === $today){
        $dailyReport += floatval($s['amount'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تحليل الحسابات - صالون لمسة إبداعية</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&display=swap" rel="stylesheet">
<style>
:root{
  --cash:#2ecc71;
  --network:#9b59b6;
  --withdraw:#f39c12;
  --expense:#e74c3c;
  --advance:#f39c12;
  --total:#16a085;
}
body{margin:0;font-family:'Cairo',sans-serif;background:#f4f7fb;}
header{background:#1e3c72;color:#fff;padding:25px;text-align:center;position:relative;}
.back-btn{position:absolute;top:20px;right:20px;padding:10px 15px;background:#16a085;color:#fff;border:none;border-radius:10px;cursor:pointer;}
.container{max-width:1200px;margin:auto;padding:20px;}
.dashboard{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;}
.card{background:#fff;border-radius:16px;padding:18px;box-shadow:0 10px 25px rgba(0,0,0,.08);text-align:center;}
.card h3{margin:0;color:#666;}
.value{font-size:28px;font-weight:800;margin-top:10px;}
.cash{color:var(--cash)}
.network{color:var(--network)}
.withdraw{color:var(--withdraw)}
.expense{color:var(--expense)}
.advance{color:var(--advance)}
.total{color:var(--total);font-size:32px;}
</style>
</head>
<body>

<header>
  <h1>📊 تحليل الحسابات</h1>
  <p>صالون لمسة إبداعية – نظرة مالية شاملة</p>
  <button class="back-btn" onclick="location.href='dashboard.php'">العودة</button>
</header>

<div class="container">
  <div class="dashboard">

    <div class="card"><h3>💵 مبيعات كاش</h3><div class="value cash"><?=number_format($salesCash,2)?></div></div>
    <div class="card"><h3>💳 مبيعات شبكة</h3><div class="value network"><?=number_format($salesNetwork,2)?></div></div>

    <div class="card"><h3>💰 سحوبات كاش</h3><div class="value withdraw"><?=number_format($withdrawCash,2)?></div></div>
    <div class="card"><h3>🏦 سحوبات شبكة</h3><div class="value withdraw"><?=number_format($withdrawNetwork,2)?></div></div>

    <div class="card"><h3>💸 المصروفات</h3><div class="value expense"><?=number_format($totalExpenses,2)?></div></div>
    <div class="card"><h3>📝 السلف</h3><div class="value advance"><?=number_format($totalSalf,2)?></div></div>

    <div class="card"><h3>🪙 المتبقي كاش</h3><div class="value cash"><?=number_format($remainCash,2)?></div></div>
    <div class="card"><h3>💰 المتبقي شبكة</h3><div class="value network"><?=number_format($remainNetwork,2)?></div></div>

    <div class="card"><h3>📊 إجمالي المتبقي</h3><div class="value total"><?=number_format($totalRemain,2)?></div></div>

    <div class="card"><h3>📅 مقارنة شهرية</h3>
      <div class="value"><?=number_format($currentSales,2)?> | <?=number_format($prevSales,2)?></div>
    </div>

    <div class="card"><h3>📆 كشف حساب اليوم</h3><div class="value"><?=number_format($dailyReport,2)?></div></div>

  </div>
</div>

</body>
</html>
