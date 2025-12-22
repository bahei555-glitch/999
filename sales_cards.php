<?php
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit;
}

// جلب بيانات المبيعات والموظفين من ملفات JSON
$salesFile = 'sales.json';
$workersFile = 'workers.json';

$salesData = file_exists($salesFile) ? json_decode(file_get_contents($salesFile), true) : [];
$employees = file_exists($workersFile) ? json_decode(file_get_contents($workersFile), true) : [];

// فلترة حسب التاريخ إذا تم الإرسال
$filterStart = $_GET['startDate'] ?? '';
$filterEnd = $_GET['endDate'] ?? '';

function filterByDate($data, $start='', $end='') {
    return array_filter($data, function($row) use ($start, $end){
        $date = $row['date'] ?? '';
        if($start && $date < $start) return false;
        if($end && $date > $end) return false;
        return true;
    });
}

$filteredSales = filterByDate($salesData, $filterStart, $filterEnd);
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>بطاقات الشغل - صالون لمسة إبداعية</title>
<style>
body { font-family:'Cairo',sans-serif; background: linear-gradient(180deg,#000,#111); color:#fff; display:flex; justify-content:center; flex-direction:column; align-items:center; min-height:100vh; margin:0; padding:40px 0; direction: rtl; }
header { text-align:center; margin-bottom:30px; }
header .logo-container { display:inline-block; border:4px solid gold; border-radius:50%; padding:5px; box-shadow:0 0 20px gold; margin-bottom:10px; }
header .logo-container img { width:100px; height:100px; border-radius:50%; object-fit:cover; }
header h1 { font-size:2rem; color:gold; margin:0; text-shadow:0 0 10px gold; }

.filter-row { display:flex; flex-wrap:wrap; gap:10px; justify-content:flex-end; align-items:center; margin-bottom:15px; flex-direction: row-reverse; }
.filter-row input { padding:8px; border-radius:6px; border:none; background:#222; color:#fff; }
.filter-row button { padding:8px 12px; border-radius:6px; border:none; background:gold; color:#111; font-weight:bold; cursor:pointer; transition:0.3s; }
.filter-row button:hover { transform:translateY(-2px); box-shadow:0 0 20px gold; }
.back-btn { margin-left:10px; }

.cards-container { display:flex; flex-wrap:wrap; gap:15px; justify-content:center; width:100%; }
.row { display:flex; flex-wrap:nowrap; gap:15px; justify-content:center; margin-bottom:15px; }
.card { padding:15px; border-radius:15px; width:250px; text-align:center; transition:0.3s; color:#fff; }
.card:hover { box-shadow:0 0 40px rgba(255,215,0,0.8); }
.card h3 { margin-bottom:10px; font-size:1.2rem; text-shadow:0 0 10px gold; }
.card p { margin:5px 0; }
.daily-card { background:#1a3a5a; box-shadow:0 0 25px rgba(0,123,255,0.7); }
.monthly-card { background:#5a1a3a; box-shadow:0 0 25px rgba(255,0,123,0.7); }
.emp-card { background:#222; box-shadow:0 0 25px rgba(255,215,0,0.5); }
.divider { width:100%; height:2px; background:gold; margin:20px 0; border-radius:2px; }

@media(max-width:480px){ 
    .card { width:90%; } 
    .filter-row input, .filter-row button { font-size:0.8rem; padding:6px; } 
    .row { flex-direction:column; } 
}
</style>
</head>
<body>

<div style="display:flex; flex-direction:column; align-items:center; width:100%;">
<header>
    <div class="logo-container"><img src="logo.jpg" alt="Logo"></div>
    <h1>بطاقات الشغل لكل الموظفين</h1>
</header>

<!-- فلترة حسب التاريخ -->
<form method="get" class="filter-row">
    <label>من:</label>
    <input type="date" name="startDate" value="<?= htmlspecialchars($filterStart) ?>">
    <label>إلى:</label>
    <input type="date" name="endDate" value="<?= htmlspecialchars($filterEnd) ?>">
    <button type="submit">تطبيق الفلتر</button>
    <button type="button" class="back-btn" onclick="window.location.href='dashboard.php'">رجوع للوحة التحكم</button>
</form>

<div class="cards-container">
<?php
$today = date('Y-m-d');
$month = date('Y-m');

// بطاقات اليومي للمحل
$dailySales = array_filter($filteredSales, fn($s) => ($s['date'] ?? '') === $today);
$dailyTotal = array_sum(array_map(fn($s)=>floatval($s['amount'] ?? 0), $dailySales));
$dailyCash = array_sum(array_map(fn($s)=>($s['method']==='cash'?floatval($s['amount']):0), $dailySales));
$dailyCard = array_sum(array_map(fn($s)=>($s['method']==='card'?floatval($s['amount']):0), $dailySales));
?>
<div class="row">
    <div class="card daily-card"><h3>شبكة اليومي للمحل</h3><p><?= $dailyCard ?></p></div>
    <div class="card daily-card"><h3>كاش اليومي للمحل</h3><p><?= $dailyCash ?></p></div>
    <div class="card daily-card"><h3>توتل اليومي للمحل</h3><p><?= $dailyTotal ?></p></div>
</div>

<?php
// بطاقات الشهري للمحل
$monthlySales = array_filter($filteredSales, fn($s)=>substr($s['date'] ?? '',0,7)===$month);
$monthlyTotal = array_sum(array_map(fn($s)=>floatval($s['amount'] ?? 0), $monthlySales));
$monthlyCash = array_sum(array_map(fn($s)=>($s['method']==='cash'?floatval($s['amount']):0), $monthlySales));
$monthlyCard = array_sum(array_map(fn($s)=>($s['method']==='card'?floatval($s['amount']):0), $monthlySales));
?>
<div class="row">
    <div class="card monthly-card"><h3>شبكة الشهري للمحل</h3><p><?= $monthlyCard ?></p></div>
    <div class="card monthly-card"><h3>كاش الشهري للمحل</h3><p><?= $monthlyCash ?></p></div>
    <div class="card monthly-card"><h3>توتل الشهري للمحل</h3><p><?= $monthlyTotal ?></p></div>
</div>

<div class="divider"></div>

<?php
// بطاقات كل موظف
foreach($employees as $emp){
    $empSales = array_filter($filteredSales, function($s) use ($emp){
        $empName = strtolower(trim($emp['name'] ?? ''));
        $empUsername = strtolower(trim($emp['username'] ?? ''));
        $saleEmp = strtolower(trim($s['employee'] ?? ''));
        return $saleEmp === $empName || $saleEmp === $empUsername;
    });
    $empDaily = array_sum(array_map(fn($s)=>floatval($s['amount'] ?? 0), array_filter($empSales, fn($s)=>($s['date'] ?? '')===$today)));
    $empTotal = array_sum(array_map(fn($s)=>floatval($s['amount'] ?? 0), $empSales));
?>
<div class="row">
    <div class="card emp-card">
        <h3><?= htmlspecialchars($emp['name'] ?? '') ?></h3>
        <p>اليومي: <?= $empDaily ?> | الكلي: <?= $empTotal ?></p>
    </div>
</div>
<?php } ?>
</div>
</div>

</body>
</html>
