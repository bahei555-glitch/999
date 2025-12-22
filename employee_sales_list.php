<?php
session_start();

// التأكد من تسجيل دخول الموظف
if(!isset($_SESSION['employeeLogged']) || !$_SESSION['employeeLogged']){
    header("Location: index.php");
    exit;
}

$employeeName = $_SESSION['employeeName'] ?? 'الموظف';
$salesFile = 'sales.json';
$salfFile = 'salf.json';

// جلب البيانات
$salesData = file_exists($salesFile) ? json_decode(file_get_contents($salesFile), true) : [];
$salfData = file_exists($salfFile) ? json_decode(file_get_contents($salfFile), true) : [];

// ** إضافة سلفة **
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salfAmount'])){
    $amount = floatval($_POST['salfAmount']);
    if($amount > 0){
        $now = new DateTime();
        $entry = [
            'employee'=>$employeeName,
            'amount'=>$amount,
            'date'=>$now->format('Y-m-d'),
            'time'=>$now->format('H:i:s')
        ];
        $salfData[] = $entry;
        file_put_contents($salfFile, json_encode($salfData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// ** فلترة حسب التاريخ **
$filterStart = $_GET['startDate'] ?? '';
$filterEnd = $_GET['endDate'] ?? '';

function filterByEmployeeAndDate($data, $employee, $start='', $end=''){
    return array_filter($data, function($row) use ($employee,$start,$end){
        $rowEmp = strtolower($row['employee'] ?? '');
        $date = $row['date'] ?? '';
        if(strtolower($employee) !== $rowEmp) return false;
        if($start && $date < $start) return false;
        if($end && $date > $end) return false;
        return true;
    });
}

$employeeSales = filterByEmployeeAndDate($salesData, $employeeName, $filterStart, $filterEnd);
$employeeSalf = filterByEmployeeAndDate($salfData, $employeeName, $filterStart, $filterEnd);

// حساب الملخص اليومي والشهري
$today = date('Y-m-d');
$month = date('Y-m');

$summary = [
    'cashToday'=>0, 'cardToday'=>0, 'totalToday'=>0,
    'cashMonth'=>0, 'cardMonth'=>0, 'totalMonth'=>0
];

foreach($employeeSales as $s){
    $amt = floatval($s['amount'] ?? 0);
    $method = strtolower($s['method'] ?? '');
    $date = $s['date'] ?? '';
    $monthOfSale = substr($date,0,7);

    // اليومي
    if($date === $today){
        $summary['totalToday'] += $amt;
        if($method==='cash') $summary['cashToday'] += $amt;
        elseif($method==='card') $summary['cardToday'] += $amt;
    }

    // الشهري
    if($monthOfSale === $month){
        $summary['totalMonth'] += $amt;
        if($method==='cash') $summary['cashMonth'] += $amt;
        elseif($method==='card') $summary['cardMonth'] += $amt;
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لوحة تحكم الموظف - صالون لمسة إبداعية</title>
<style>
/* احتفظنا بنفس CSS الأصلي */ 
:root{
    --gold:#FFD700;
    --daily-bg:#2E2B2B;
    --monthly-bg:#1E3A5F;
    --card-text:#fff;
    --panel-bg:#111;
}
body { font-family:'Cairo',sans-serif; background: linear-gradient(180deg,#000,#111); color:#fff; margin:0; padding:20px;}
header { text-align:center; margin-bottom:20px; }
header .logo-container { display:inline-block; border:4px solid var(--gold); border-radius:50%; padding:5px; box-shadow:0 0 20px var(--gold); margin-bottom:10px; }
header .logo-container img { width:80px; height:80px; border-radius:50%; object-fit:cover; }
header h1 { font-size:1.8rem; color:var(--gold); margin:0; text-shadow:0 0 10px var(--gold); }
.cards-row{ display:flex; flex-wrap:wrap; gap:12px; justify-content:center; margin-bottom:15px; }
.cards-row.daily { flex-direction: row-reverse; }
.cards-row.monthly { flex-direction: row-reverse; }
.card{ background:var(--daily-bg); padding:15px 10px; border-radius:10px; box-shadow:0 0 12px rgba(255,215,0,0.5); text-align:center; min-width:100px; flex:1 1 120px; display:flex; flex-direction:column; justify-content:center; align-items:center; color:var(--card-text); }
.cards-row.monthly .card{ background:var(--monthly-bg); }
.card h3{margin:0 0 5px;font-size:1rem;text-shadow:0 0 5px var(--gold);}
.card p{margin:0;font-size:1rem;}
.filter-row { display:grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap:10px; justify-items:center; align-items:center; margin-bottom:15px; }
.filter-row input { padding:6px 8px; border-radius:6px; border:none; background:var(--panel-bg); color:#fff; text-align:right; width:100%; }
.filter-buttons { display:flex; gap:8px; flex-wrap:wrap; justify-content:center; align-items:center; }
.gold-btn { padding:6px 12px; border-radius:6px; border:none; background:var(--gold); color:#111; font-weight:bold; cursor:pointer; transition:0.3s; }
.gold-btn:hover { transform:translateY(-2px); box-shadow:0 0 15px var(--gold); }
.back-btn { padding:8px 12px; background:var(--gold); color:#111; font-weight:bold; border:none; border-radius:8px; cursor:pointer; transition:0.3s; }
.back-btn:hover { transform:translateY(-2px); box-shadow:0 0 20px var(--gold); }
.salf-section { display:flex; justify-content:center; flex-wrap:wrap; gap:8px; margin-bottom:15px; }
.salf-section input { padding:8px; border-radius:6px; border:none; background:#222; color:#fff; text-align:right; min-width:120px; flex:1 1 140px; }
.salf-section button { padding:8px 12px; border-radius:6px; border:none; background:var(--gold); color:#111; font-weight:bold; cursor:pointer; }
.table-section { width:100%; }
table { width:100%; border-collapse:collapse; table-layout: fixed; background:#222; border-radius:8px; overflow:hidden; margin-bottom:20px;}
table th, table td { padding:8px; text-align:center; border-bottom:1px solid #444; word-wrap: break-word; }
table th { background:#333; color:var(--gold); }
</style>
</head>
<body>
<div class="sales-list-box">
<header>
    <div class="logo-container"><img src="logo.jpg" alt="Logo"></div>
    <h1>لوحة تحكم الموظف</h1>
    <p style="margin-top:8px;color:#fff;font-size:0.95rem;">الموظف الحالي: <?= htmlspecialchars($employeeName) ?></p>
</header>

<h2 style="text-align:center;">إجمالي المبيعات اليومية</h2>
<div class="cards-row daily">
    <div class="card"><h3>كاش اليومي</h3><p><?= number_format($summary['cashToday'],2) ?></p></div>
    <div class="card"><h3>شبكة اليومي</h3><p><?= number_format($summary['cardToday'],2) ?></p></div>
    <div class="card"><h3>توتل اليومي</h3><p><?= number_format($summary['totalToday'],2) ?></p></div>
</div>

<h2 style="text-align:center;">إجمالي المبيعات الشهرية</h2>
<div class="cards-row monthly">
    <div class="card"><h3>كاش الشهري</h3><p><?= number_format($summary['cashMonth'],2) ?></p></div>
    <div class="card"><h3>شبكة الشهري</h3><p><?= number_format($summary['cardMonth'],2) ?></p></div>
    <div class="card"><h3>توتل الشهري</h3><p><?= number_format($summary['totalMonth'],2) ?></p></div>
</div>

<!-- فلترة التاريخ -->
<form method="get" class="filter-row">
    <input type="date" name="startDate" value="<?= htmlspecialchars($filterStart) ?>" placeholder="من">
    <input type="date" name="endDate" value="<?= htmlspecialchars($filterEnd) ?>" placeholder="إلى">
    <div class="filter-buttons">
        <button type="submit" class="gold-btn">تطبيق الفلتر</button>
        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="gold-btn" style="text-decoration:none;">إعادة الضبط</a>
        <a href="employee_sales.php" class="back-btn" style="text-decoration:none;">رجوع</a>
    </div>
</form>

<!-- إضافة السلفة -->
<form method="post" class="salf-section">
    <input type="number" name="salfAmount" placeholder="المبلغ" required>
    <button type="submit">تسجيل السلفة</button>
</form>

<div class="table-section">
<h2 style="text-align:center;">جدول المبيعات</h2>
<table>
    <thead>
        <tr>
            <th>الموظف</th>
            <th>المبلغ</th>
            <th>طريقة الدفع</th>
            <th>التاريخ</th>
            <th>الوقت</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($employeeSales as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['employee'] ?? '') ?></td>
            <td><?= number_format(floatval($s['amount'] ?? 0),2) ?></td>
            <td><?= htmlspecialchars($s['method'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['time'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2 style="text-align:center;">جدول السلف</h2>
<table>
    <thead>
        <tr>
            <th>الموظف</th>
            <th>المبلغ</th>
            <th>التاريخ</th>
            <th>الوقت</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($employeeSalf as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['employee'] ?? '') ?></td>
            <td><?= number_format(floatval($s['amount'] ?? 0),2) ?></td>
            <td><?= htmlspecialchars($s['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['time'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
</body>
</html>
