<?php
session_start();

// حماية الدخول
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true){
    header("Location: login.php");
    exit;
}

// ملف البيانات للسحوبات
$withdrawalsFile = 'manager_withdrawals.json';
if(!file_exists($withdrawalsFile)){
    file_put_contents($withdrawalsFile, json_encode([], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
$withdrawals = json_decode(file_get_contents($withdrawalsFile), true);

// إضافة سحب جديد
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])){
    $action = $_POST['action'];
    if($action==='add' && isset($_POST['amount'], $_POST['type'])){
        $withdrawals[] = [
            'amount'=>floatval($_POST['amount']),
            'type'=>$_POST['type'],
            'date'=>date("Y-m-d H:i:s")
        ];
        file_put_contents($withdrawalsFile, json_encode($withdrawals, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }
    if($action==='delete' && isset($_POST['index'])){
        $index = intval($_POST['index']);
        if(isset($withdrawals[$index])){
            array_splice($withdrawals,$index,1);
            file_put_contents($withdrawalsFile, json_encode($withdrawals, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        }
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// حساب الملخصات
$cashTotal = 0;
$networkTotal = 0;
foreach($withdrawals as $w){
    if($w['type']=='cash') $cashTotal += $w['amount'];
    if($w['type']=='network') $networkTotal += $w['amount'];
}
$allTotal = $cashTotal + $networkTotal;

?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>سحوبات المدير - صالون لمسة إبداعية</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body { font-family:'Cairo',sans-serif; background:#f4f7fb; margin:0; padding:20px; color:#111; display:flex; flex-direction:column; align-items:center; }
.form-container { max-width:400px;width:100%;background:#fff;padding:25px;border-radius:16px;box-shadow:0 15px 25px rgba(0,0,0,0.1); display:flex; flex-direction:column; align-items:center;}
#amount { width:80%; padding:12px 15px; border-radius:12px; border:1px solid #ccc; font-size:16px; text-align:center; margin-bottom:15px;}
.button-row { display:flex; gap:15px; justify-content:center; width:100%; margin-bottom:15px;}
button { flex:1; padding:12px; border:none; border-radius:12px; color:#fff; font-size:14px; cursor:pointer;}
.cash-btn { background:#2ecc71;}
.network-btn { background:#3498db;}
.back-btn { background:#3498db; margin-top:10px;}
.withdrawals-list { max-width:600px; margin:30px auto; padding:20px; background:#fff; border-radius:16px; box-shadow:0 15px 25px rgba(0,0,0,0.1);}
.withdrawals-list table { width:100%; border-collapse:collapse; margin-top:10px;}
.withdrawals-list th, .withdrawals-list td { border:1px solid #ddd; padding:10px; text-align:center;}
.withdrawals-list th { background:#f0f0f0;}
.summary-cards{ display:flex; gap:15px; margin:20px 0; flex-wrap:wrap; justify-content:center;}
.summary-cards .card{ background:#fff; padding:15px 25px; border-radius:14px; min-width:180px; text-align:center; box-shadow:0 10px 20px rgba(0,0,0,.1);}
.summary-cards .card span{ font-size:22px; font-weight:bold; display:block; margin-top:8px;}
.summary-cards .cash{ border-bottom:4px solid #2ecc71;}
.summary-cards .network{ border-bottom:4px solid #3498db;}
.summary-cards .total{ border-bottom:4px solid #f39c12;}
</style>
</head>
<body>

<h1>سحوبات المدير</h1>
<button class="back-btn" onclick="window.location.href='dashboard.php'">العودة للوحة التحكم</button>

<div class="summary-cards">
  <div class="card cash"><h4>سحوبات الكاش</h4><span><?= number_format($cashTotal,2) ?></span></div>
  <div class="card network"><h4>سحوبات الشبكة</h4><span><?= number_format($networkTotal,2) ?></span></div>
  <div class="card total"><h4>الإجمالي</h4><span><?= number_format($allTotal,2) ?></span></div>
</div>

<div class="form-container">
  <form method="post" style="width:100%; display:flex; flex-direction:column; align-items:center;">
    <input type="number" name="amount" id="amount" placeholder="أدخل المبلغ" min="1" required>
    <input type="hidden" name="type" id="typeInput" value="cash">
    <div class="button-row">
      <button type="submit" name="action" value="add" onclick="document.getElementById('typeInput').value='cash'" class="cash-btn">💵 كاش</button>
      <button type="submit" name="action" value="add" onclick="document.getElementById('typeInput').value='network'" class="network-btn">💳 شبكة</button>
    </div>
  </form>
</div>

<div class="withdrawals-list">
  <h3>آخر السحوبات</h3>
  <table>
    <thead>
      <tr><th>التاريخ</th><th>المبلغ</th><th>النوع</th><th>حذف</th></tr>
    </thead>
    <tbody>
      <?php foreach(array_reverse($withdrawals) as $index=>$w): ?>
      <tr>
        <td><?= $w['date'] ?></td>
        <td><?= number_format($w['amount'],2) ?></td>
        <td><?= $w['type']=='cash'?'💵 كاش':'💳 شبكة' ?></td>
        <td>
          <form method="post" style="display:inline;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="index" value="<?= count($withdrawals)-1-$index ?>">
            <button type="submit" style="background:red;color:white;border:none;border-radius:6px;padding:5px 10px;cursor:pointer;">حذف</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</body>
</html>
