<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true){
    header("Location: login.php");
    exit;
}

$salfFile = 'salf.json';
$workersFile = 'workers.json';

// جلب البيانات
$withdrawals = file_exists($salfFile) ? json_decode(file_get_contents($salfFile), true) : [];
$employees = file_exists($workersFile) ? json_decode(file_get_contents($workersFile), true) : [];

// التأكد من وجود id لكل سلفة
$changed = false;
foreach($withdrawals as &$w){
    if(!isset($w['id']) || !$w['id']){
        $w['id'] = '_'.bin2hex(random_bytes(4));
        $changed = true;
    }
}
unset($w);
if($changed){
    file_put_contents($salfFile,json_encode($withdrawals,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}

// إضافة موظف جديد
if(isset($_POST['new_employee_name'])){
    $newEmp = trim($_POST['new_employee_name']);
    if($newEmp && !in_array(['name'=>$newEmp], $employees)){
        $employees[] = ['name'=>$newEmp];
        file_put_contents($workersFile,json_encode($employees,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// تعديل اسم موظف
if(isset($_POST['edit_employee_name'], $_POST['old_employee_name'])){
    $oldName = trim($_POST['old_employee_name']);
    $newName = trim($_POST['edit_employee_name']);
    if($oldName && $newName){
        foreach($employees as &$emp){
            if(($emp['name']??'') === $oldName){
                $emp['name'] = $newName;
            }
        }
        unset($emp);
        foreach($withdrawals as &$w){
            if(($w['employee']??'') === $oldName){
                $w['employee'] = $newName;
            }
        }
        unset($w);
        file_put_contents($workersFile,json_encode($employees,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        file_put_contents($salfFile,json_encode($withdrawals,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// إضافة سلفة جديدة
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['employee'],$_POST['amount'],$_POST['date'])){
    $employee = $_POST['employee'];
    $amount = floatval($_POST['amount']);
    $date = $_POST['date'];
    if($employee && $amount>0 && $date){
        $withdrawals[] = [
            'id'=>'_'.bin2hex(random_bytes(4)),
            'employee'=>$employee,
            'amount'=>$amount,
            'date'=>$date,
            'sentByEmployee'=>false
        ];
        file_put_contents($salfFile,json_encode($withdrawals,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// حذف سلفة
if(isset($_GET['delete_id'])){
    $id = $_GET['delete_id'];
    $withdrawals = array_filter($withdrawals, fn($w)=>($w['id']??'')!==$id);
    file_put_contents($salfFile,json_encode(array_values($withdrawals),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// تعديل سلفة موجودة
if(isset($_POST['edit_withdrawal_id'], $_POST['edit_amount'], $_POST['edit_date'])){
    $id = $_POST['edit_withdrawal_id'];
    $amount = floatval($_POST['edit_amount']);
    $date = $_POST['edit_date'];
    foreach($withdrawals as &$w){
        if(($w['id']??'') === $id){
            $w['amount'] = $amount;
            $w['date'] = $date;
        }
    }
    unset($w);
    file_put_contents($salfFile,json_encode($withdrawals,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// حذف كل السلف لموظف
if(isset($_GET['delete_emp'])){
    $empName = $_GET['delete_emp'];
    $withdrawals = array_filter($withdrawals, fn($w)=>($w['employee']??'')!==$empName);
    file_put_contents($salfFile,json_encode(array_values($withdrawals),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// فلترة حسب التاريخ
$filterStart = $_GET['startDate'] ?? '';
$filterEnd = $_GET['endDate'] ?? '';
function filterByDate($data, $start='', $end=''){
    return array_filter($data, function($row) use ($start, $end){
        $date = $row['date'] ?? '';
        if($start && $date < $start) return false;
        if($end && $date > $end) return false;
        return true;
    });
}
$filteredWithdrawals = filterByDate($withdrawals, $filterStart, $filterEnd);

// حساب إجمالي السلف لكل موظف
$totals = [];
foreach($employees as $emp){
    $empName = $emp['name'] ?? '';
    $empSalf = array_filter($filteredWithdrawals, fn($w)=>($w['employee'] ?? '') === $empName);
    $totals[$empName] = array_sum(array_map(fn($w)=>floatval($w['amount'] ?? 0), $empSalf));
}
$totalAll = array_sum(array_map(fn($w)=>floatval($w['amount'] ?? 0), $filteredWithdrawals));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>السلف - صالون لمسة إبداعية</title>
<style>
body{font-family:'Cairo',sans-serif;background:#f5f6fa;margin:0;padding:20px}
.container{max-width:1100px;margin:auto;background:#fff;border-radius:12px;padding:20px;box-shadow:0 5px 15px rgba(0,0,0,.1)}
h1{text-align:center;margin-bottom:20px}
.form-row{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:15px;align-items:center}
input,select,button{padding:10px;border-radius:8px;border:1px solid #ccc}
button{cursor:pointer}
.employee-cards{display:flex;flex-wrap:wrap;gap:15px;margin-bottom:20px}
.employee-card{background:#4cd137;color:#fff;border-radius:12px;padding:15px;flex:1 1 180px;text-align:center;position:relative}
.employee-card span{display:block;font-size:22px;margin-top:8px}
.delete-btn,.remove-emp{position:absolute;top:6px;border:none;color:#fff;border-radius:50%;width:26px;height:26px;font-weight:bold;cursor:pointer}
.delete-btn{right:6px;background:#e84118}
.remove-emp{left:6px;background:#2f3640}
table{width:100%;border-collapse:collapse}
th,td{text-align:center;padding:10px;border-bottom:1px solid #ddd}
th{background:#f1f2f6}
.btn-delete{background:#e84118;color:#fff;border:none;padding:6px 12px;border-radius:6px}
.total-card{background:#ff6b81;color:#fff;border-radius:12px;padding:20px;text-align:center;font-size:20px;margin-bottom:20px}
.total-card span{font-size:32px;display:block}
.employee-sent{background:#fff3cd}
.employee-manual{background:#d4edda}
input.edit-input{width:70px;text-align:center;padding:4px;border-radius:4px;border:1px solid #999}
</style>
</head>
<body>
<div class="container">
<h1>سلف الموظفين</h1>

<!-- إضافة موظف جديد -->
<form method="post" class="form-row">
<input type="text" name="new_employee_name" placeholder="اسم موظف جديد" required>
<button type="submit">إضافة موظف</button>
</form>

<!-- تعديل اسم موظف -->
<form method="post" class="form-row">
<select name="old_employee_name" required>
<option value="">اختر الموظف لتعديله</option>
<?php foreach($employees as $emp): ?>
<option value="<?= htmlspecialchars($emp['name'] ?? '') ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
<?php endforeach; ?>
</select>
<input type="text" name="edit_employee_name" placeholder="الاسم الجديد" required>
<button type="submit">تعديل الاسم</button>
</form>

<!-- إضافة سلفة -->
<form method="post" class="form-row">
<select name="employee" required>
<option value="">اختر الموظف</option>
<?php foreach($employees as $emp): ?>
<option value="<?= htmlspecialchars($emp['name'] ?? '') ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
<?php endforeach; ?>
</select>
<input type="number" step="0.01" name="amount" placeholder="المبلغ" required>
<input type="date" name="date" required>
<button type="submit">إضافة سلفة</button>
</form>

<!-- فلترة -->
<form method="get" class="form-row">
<label>من:</label>
<input type="date" name="startDate" value="<?= htmlspecialchars($filterStart) ?>">
<label>إلى:</label>
<input type="date" name="endDate" value="<?= htmlspecialchars($filterEnd) ?>">
<button type="submit">تطبيق الفلتر</button>
<button type="button" onclick="window.location.href='dashboard.php'">رجوع للقائمة</button>
</form>

<div class="total-card">
إجمالي السلف
<span><?= number_format($totalAll,2) ?></span> ريال
</div>

<div class="employee-cards">
<?php foreach($employees as $emp):
$empName = $emp['name'] ?? '';
$sum = $totals[$empName] ?? 0;
?>
<div class="employee-card">
<?= htmlspecialchars($empName) ?>
<span><?= number_format($sum,2) ?> ريال</span>
<?php if($sum>0): ?>
<a href="?delete_emp=<?= urlencode($empName) ?>" class="remove-emp" title="حذف كل السلف" onclick="return confirm('هل تريد حذف كل السلف لهذا الموظف؟')">−</a>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<table>
<thead>
<tr>
<th>الموظف</th>
<th>المبلغ</th>
<th>التاريخ</th>
<th>المصدر</th>
<th>حذف</th>
<th>تعديل</th>
</tr>
</thead>
<tbody>
<?php foreach($filteredWithdrawals as $w): ?>
<tr class="<?= ($w['sentByEmployee'] ?? false)?'employee-sent':'employee-manual' ?>">
<td><?= htmlspecialchars($w['employee'] ?? '') ?></td>
<td>
<form method="post" style="display:inline-block">
<input type="hidden" name="edit_withdrawal_id" value="<?= htmlspecialchars($w['id'] ?? '') ?>">
<input type="number" step="0.01" class="edit-input" name="edit_amount" value="<?= number_format(floatval($w['amount'] ?? 0),2) ?>" required>
</td>
<td>
<input type="date" class="edit-input" name="edit_date" value="<?= htmlspecialchars($w['date'] ?? '') ?>" required>
</td>
<td><?= ($w['sentByEmployee'] ?? false)?'موظف':'مدير' ?></td>
<td><a href="?delete_id=<?= htmlspecialchars($w['id'] ?? '') ?>" class="btn-delete" onclick="return confirm('هل تريد حذف هذه السلفة؟')">حذف</a></td>
<td><button type="submit" class="btn-delete">حفظ</button></form></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</body>
</html>
