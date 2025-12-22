<?php
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit;
}

// جلب البيانات
$salesFile = 'sales.json';
$employeesFile = 'workers.json';
$expensesFile = 'expenses.json';

$salesData = file_exists($salesFile) ? json_decode(file_get_contents($salesFile), true) : [];
$employees = file_exists($employeesFile) ? json_decode(file_get_contents($employeesFile), true) : [];
$expensesData = file_exists($expensesFile) ? json_decode(file_get_contents($expensesFile), true) : [];

// حساب الملخص
$totalRevenue = array_sum(array_map(fn($s)=>floatval($s['amount']??0), $salesData));
$storeRevenue = $totalRevenue/2;
$totalExpenses = array_sum(array_map(fn($e)=>floatval($e['amount']??0), $expensesData));
$netProfit = $storeRevenue - $totalExpenses;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ملخص المبيعات - صالون لمسة إبداعية</title>
<style>
body{margin:0;font-family:'Cairo',sans-serif;background:#111;color:#fff;padding:20px;}
.container{max-width:1200px;margin:auto;}
header{text-align:center;margin-bottom:20px;}
header h1{color:#FFD700;font-size:2rem;text-shadow:0 0 10px #FFD700;}
.cards-row{display:flex;flex-wrap:wrap;gap:16px;justify-content:center;margin-bottom:20px;}
.card{background:#222;flex:1 1 180px;padding:20px;border-radius:12px;box-shadow:0 0 15px rgba(255,215,0,0.3);text-align:center;min-width:150px;transition:0.3s;border:2px solid #FFD700;}
.card:hover{transform:translateY(-5px);box-shadow:0 0 25px #FFD700;}
.card h3{margin:0 0 10px;font-size:1rem;text-shadow:0 0 6px #FFD700;}
.card p{margin:6px 0;font-size:1.1rem;font-weight:bold;padding:4px 6px;border-radius:6px;}
.filter-row{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;margin-bottom:20px;align-items:center;}
.filter-row select{padding:6px;border-radius:6px;border:none;background:#222;color:#fff;text-align:center;}
.gold-btn{padding:8px 14px;border-radius:6px;border:none;background:#FFD700;color:#111;font-weight:bold;cursor:pointer;transition:0.3s;}
.gold-btn:hover{transform:translateY(-2px);box-shadow:0 0 15px #FFD700;}
@media(max-width:768px){
.cards-row{flex-direction:column !important;align-items:center;}
.card{min-width:80%;margin-bottom:10px;}
}
</style>
</head>
<body>
<div class="container">
<header>
    <h1>ملخص المبيعات للشهر الحالي</h1>
</header>

<!-- بطاقات الملخص -->
<div class="cards-row" id="summaryCards">
    <div class="card"><h3>إجمالي الإيرادات</h3><p id="totalRevenue"><?= number_format($totalRevenue,2) ?></p></div>
    <div class="card"><h3>إيرادات المحل</h3><p id="storeRevenue"><?= number_format($storeRevenue,2) ?></p></div>
    <div class="card"><h3>المصروفات</h3><p id="totalExpenses"><?= number_format($totalExpenses,2) ?></p></div>
    <div class="card"><h3>صافي الربح</h3><p id="netProfit"><?= number_format($netProfit,2) ?></p></div>
</div>

<!-- فلتر الموظفين -->
<div class="filter-row">
    <label>اختر الموظف:</label>
    <select id="employeeSelect" onchange="filterEmployeeCards()">
        <option value="all">عرض الجميع</option>
        <?php foreach($employees as $emp): ?>
            <option value="<?= htmlspecialchars($emp['name'] ?? $emp) ?>"><?= htmlspecialchars($emp['name'] ?? $emp) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="gold-btn" onclick="window.location.href='dashboard.php'">رجوع للوحة التحكم</button>
</div>

<h2 style="text-align:center;">بطاقات الموظفين</h2>
<div class="cards-row" id="employeeCards"></div>
</div>

<script>
const salesData = <?= json_encode($salesData, JSON_UNESCAPED_UNICODE) ?>;
const employees = <?= json_encode($employees, JSON_UNESCAPED_UNICODE) ?>;

// توليد بطاقات الموظفين
function generateEmployeeCards(filtered=null){
    const container = document.getElementById('employeeCards');
    container.innerHTML = '';
    const data = filtered || salesData;

    const today = new Date().toISOString().split('T')[0];
    const month = today.slice(0,7);

    employees.forEach(emp=>{
        const empName = emp.name ?? emp;
        const empSales = data.filter(s=>s.employee === empName);
        if(empSales.length === 0) return;

        const dailyTotal = empSales.filter(s=>s.date === today).reduce((a,b)=>a+parseFloat(b.amount||0),0);
        const monthlyTotal = empSales.filter(s=>s.date.startsWith(month)).reduce((a,b)=>a+parseFloat(b.amount||0),0);

        const div = document.createElement('div');
        div.className = 'card';
        div.innerHTML = `<h3>${empName}</h3>
            <p>اليومي: ${dailyTotal.toFixed(2)}</p>
            <p>الشهري: ${monthlyTotal.toFixed(2)}</p>`;
        container.appendChild(div);
    });
}

// فلترة حسب الموظف
function filterEmployeeCards(){
    const emp = document.getElementById('employeeSelect').value;
    if(emp === 'all'){
        generateEmployeeCards();
    } else {
        const filtered = salesData.filter(s=>s.employee === emp);
        generateEmployeeCards(filtered);
    }
}

window.onload = ()=>generateEmployeeCards();
</script>
</body>
</html>
