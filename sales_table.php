<?php
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit;
}

$salesFile = 'sales.json';
$salesData = file_exists($salesFile) ? json_decode(file_get_contents($salesFile), true) : [];

// عملية الحذف
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $index = intval($_GET['delete']);
    if(isset($salesData[$index])){
        array_splice($salesData, $index, 1);
        file_put_contents($salesFile, json_encode($salesData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

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
<title>جدول المبيعات لكل الموظفين - صالون لمسة إبداعية</title>
<style>
body { font-family:'Cairo',sans-serif; background: #111; color:#fff; margin:0; padding:20px; direction: rtl; }
header { text-align:center; margin-bottom:20px; }
header .logo-container { display:inline-block; border:4px solid gold; border-radius:50%; padding:5px; box-shadow:0 0 20px gold; margin-bottom:10px; }
header .logo-container img { width:100px; height:100px; border-radius:50%; object-fit:cover; }
header h1 { font-size:2rem; color:gold; margin:0; text-shadow:0 0 10px gold; }
.filter-row { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; align-items:center; margin-bottom:15px; }
.filter-row input, .filter-row button { padding:8px 12px; border-radius:6px; border:none; }
.filter-row input { background:#222; color:#fff; }
.filter-row button { background:gold; color:#111; font-weight:bold; cursor:pointer; transition:0.3s; }
.filter-row button:hover { transform:translateY(-2px); box-shadow:0 0 20px gold; }
.back-btn { margin-left:10px; }
table { width:100%; border-collapse:collapse; max-width:1000px; margin-bottom:20px; }
th, td { padding:10px; border:1px solid #333; text-align:center; }
th { background:gold; color:#111; }
td { background:#222; }
.delete-btn { padding:5px 8px; background:red; color:#fff; border:none; border-radius:5px; cursor:pointer; }
.delete-btn:hover { background:#ff4d4d; }
@media(max-width:480px){ table, th, td { font-size:0.8rem; } .filter-row input, .filter-row button { font-size:0.8rem; padding:6px; } }
</style>
</head>
<body>

<header>
    <div class="logo-container"><img src="logo.jpg" alt="Logo"></div>
    <h1>جدول المبيعات لكل الموظفين</h1>
</header>

<form method="get" class="filter-row">
    <label>من:</label>
    <input type="date" name="startDate" value="<?= htmlspecialchars($filterStart) ?>">
    <label>إلى:</label>
    <input type="date" name="endDate" value="<?= htmlspecialchars($filterEnd) ?>">
    <button type="submit">تطبيق الفلتر</button>
    <button type="button" onclick="window.location.href='dashboard.php'">رجوع للوحة التحكم</button>
</form>

<h2>جدول المبيعات</h2>
<table>
    <thead>
        <tr>
            <th>الموظف</th>
            <th>طريقة الدفع</th>
            <th>المبلغ</th>
            <th>التاريخ</th>
            <th>الوقت</th>
            <th>حذف</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($filteredSales as $index => $sale): ?>
        <tr>
            <td><?= htmlspecialchars($sale['employee'] ?? '') ?></td>
            <td><?= htmlspecialchars($sale['method'] ?? '-') ?></td>
            <td><?= number_format(floatval($sale['amount'] ?? 0),2) ?></td>
            <td><?= htmlspecialchars($sale['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($sale['time'] ?? '') ?></td>
            <td><a href="?delete=<?= $index ?>" onclick="return confirm('هل أنت متأكد من حذف هذه العملية؟')" class="delete-btn">حذف</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
