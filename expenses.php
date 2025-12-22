<?php
session_start();

// حماية الدخول: لو المدير مش مسجل دخول يرجع لصفحة الدخول
if(!isset($_SESSION['adminLogged']) || $_SESSION['adminLogged'] !== true){
    header("Location: login.php");
    exit;
}

// ملفات البيانات
$itemsFile = 'expenseItems.json';
$expensesFile = 'expenses.json';

// إذا الملفات غير موجودة، أنشئها تلقائيًا
if(!file_exists($itemsFile)){
    file_put_contents($itemsFile, json_encode(["إيجار","كهرباء","مياه"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
if(!file_exists($expensesFile)){
    file_put_contents($expensesFile, json_encode([], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}

// قراءة البيانات بعد التأكد من وجود الملفات
$items = json_decode(file_get_contents($itemsFile), true);
$expenses = json_decode(file_get_contents($expensesFile), true);

// إضافة مصروف جديد إذا تم الإرسال
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
    $action = $_POST['action'];

    if($action === 'addItem' && !empty($_POST['itemName'])){
        $newItem = trim($_POST['itemName']);
        if(!in_array($newItem, $items)){
            $items[] = $newItem;
            file_put_contents($itemsFile, json_encode($items, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        }
        exit; // AJAX response لا حاجة لإعادة تحميل الصفحة
    }

    if($action === 'addExpense' && !empty($_POST['expenseItem']) && !empty($_POST['expenseAmount']) && !empty($_POST['expenseDate'])){
        $expenses[] = [
            "item" => $_POST['expenseItem'],
            "amount" => floatval($_POST['expenseAmount']),
            "date" => $_POST['expenseDate']
        ];
        file_put_contents($expensesFile, json_encode($expenses, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        exit;
    }

    if($action === 'deleteExpense' && isset($_POST['index'])){
        $index = intval($_POST['index']);
        if(isset($expenses[$index])){
            array_splice($expenses, $index, 1);
            file_put_contents($expensesFile, json_encode($expenses, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        }
        exit;
    }

    if($action === 'deleteItem' && !empty($_POST['itemName'])){
        $itemName = $_POST['itemName'];
        $items = array_filter($items, fn($i)=> $i !== $itemName);
        $expenses = array_filter($expenses, fn($e)=> $e['item'] !== $itemName);
        file_put_contents($itemsFile, json_encode(array_values($items), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        file_put_contents($expensesFile, json_encode(array_values($expenses), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>المصروفات - صالون لمسة إبداعية</title>
<style>
body { font-family:'Cairo',sans-serif; background:#f5f6fa; margin:0; padding:20px; }
.container { max-width:1000px; margin:auto; background:#fff; border-radius:12px; padding:20px; box-shadow:0 5px 15px rgba(0,0,0,.1); }
h1 { text-align:center; color:#2f3640; margin-bottom:20px; }
.form-row, .filter-row { display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:15px; }
input, select, button { padding:10px; border-radius:8px; border:1px solid #dcdde1; font-size:16px; }
select { min-width:150px; }
input[type="number"], input[type="date"] { width:150px; }
button { cursor:pointer; border:none; transition:0.3s; }
.btn-add-item { background:#00a8ff; color:white; font-weight:bold; width:40px; height:40px; font-size:20px; }
.btn-add-item:hover { background:#0097e6; }
.btn-add-expense { background:#44bd32; color:white; font-weight:bold; padding:10px 15px; }
.btn-add-expense:hover { background:#4cd137; }
.total-card { background:#ff6b81; color:white; border-radius:12px; padding:20px; text-align:center; font-size:20px; font-weight:bold; box-shadow:0 5px 15px rgba(0,0,0,.1); margin-bottom:20px; }
.total-card span { display:block; font-size:32px; margin-top:5px; }
.item-cards { display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px; }
.item-card { background:#4cd137; color:white; border-radius:12px; padding:15px; flex:1 1 150px; text-align:center; font-weight:bold; box-shadow:0 5px 15px rgba(0,0,0,.1); position:relative; }
.item-card span { display:block; font-size:24px; margin-top:10px; }
.item-card .delete-btn { position:absolute; top:5px; right:5px; background:#e84118; border:none; border-radius:50%; width:25px; height:25px; color:white; font-weight:bold; cursor:pointer; line-height:22px; }
.item-card .delete-btn:hover { background:#c23616; }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { text-align:center; padding:12px; border-bottom:1px solid #dcdde1; }
th { background:#f1f2f6; color:#2f3640; }
tr:nth-child(even) { background:#f9f9f9; }
.btn-delete { background:#e84118; color:white; border-radius:6px; padding:6px 12px; }
.btn-delete:hover { background:#c23616; }
.btn-back { margin-bottom:20px; background:#353b48; color:white; padding:10px 20px; border-radius:8px; display:inline-block; }
.btn-back:hover { background:#2f3640; }
</style>
</head>
<body>

<div class="container">
<button class="btn-back" onclick="window.location.href='dashboard.php'">العودة لصفحة التحكم</button>
<h1>المصروفات</h1>

<div class="form-row">
<select id="expenseItem">
<option value="">اختر البند</option>
<?php foreach($items as $it): ?>
    <option value="<?= htmlspecialchars($it) ?>"><?= htmlspecialchars($it) ?></option>
<?php endforeach; ?>
</select>
<button class="btn-add-item" onclick="addItem()">+</button>
<input type="number" id="expenseAmount" placeholder="المبلغ">
<input type="date" id="expenseDate">
<button class="btn-add-expense" onclick="addExpense()">إضافة مصروف</button>
</div>

<div class="filter-row">
<label>إلى</label>
<input type="date" id="filterFrom">
<label>من</label>
<input type="date" id="filterTo">
<button onclick="applyFilter()">عرض</button>
<button onclick="clearFilter()">مسح الفلتر</button>
</div>

<div class="total-card">
إجمالي المصروفات
<span id="totalExpenses">0</span> ريال
</div>

<div class="item-cards" id="itemCards"></div>

<table id="expensesTable">
<thead>
<tr>
<th>البند</th>
<th>المبلغ</th>
<th>التاريخ</th>
<th>حذف</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>

<script>
let expenses = <?= json_encode($expenses, JSON_UNESCAPED_UNICODE) ?>;
let items = <?= json_encode($items, JSON_UNESCAPED_UNICODE) ?>;
let filteredExpenses = null;

// إضافة بند جديد
function addItem(){
    let name = prompt('أدخل اسم البند الجديد:');
    if(!name) return;
    fetch('', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=addItem&itemName='+encodeURIComponent(name)
    }).then(()=> location.reload());
}

// إضافة مصروف
function addExpense(){
    let item = document.getElementById('expenseItem').value;
    let amount = document.getElementById('expenseAmount').value;
    let date = document.getElementById('expenseDate').value;
    if(!item || !amount || !date){ alert('أكمل البيانات'); return; }

    fetch('',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=addExpense&expenseItem='+encodeURIComponent(item)+'&expenseAmount='+encodeURIComponent(amount)+'&expenseDate='+encodeURIComponent(date)
    }).then(()=> location.reload());
}

// حذف مصروف
function deleteExpense(index){
    if(!confirm('هل أنت متأكد من الحذف؟')) return;
    fetch('',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=deleteExpense&index='+index
    }).then(()=> location.reload());
}

// حذف بند والمصروفات الخاصة به
function deleteItem(name){
    if(!confirm('هل أنت متأكد من حذف البند وكل المصروفات الخاصة به؟')) return;
    fetch('',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=deleteItem&itemName='+encodeURIComponent(name)
    }).then(()=> location.reload());
}

// عرض البيانات
function displayExpenses(list=null){
    const tbody = document.querySelector('#expensesTable tbody');
    tbody.innerHTML='';
    let data = list || expenses;
    let total = 0;

    data.forEach((e,i)=>{
        total += parseFloat(e.amount);
        let row = document.createElement('tr');
        row.innerHTML = `
            <td>${e.item}</td>
            <td>${e.amount}</td>
            <td>${e.date}</td>
            <td><button class="btn-delete" onclick="deleteExpense(${i})">حذف</button></td>
        `;
        tbody.appendChild(row);
    });
    document.getElementById('totalExpenses').textContent = total.toFixed(2);
    displayItemCards(data);
}

// عرض بطاقات البنود
function displayItemCards(list=null){
    const container = document.getElementById('itemCards');
    container.innerHTML='';
    let data = list || expenses;

    items.forEach(item=>{
        let total = data.filter(e=>e.item===item).reduce((sum,e)=>sum+parseFloat(e.amount),0);
        let card = document.createElement('div');
        card.className='item-card';
        card.innerHTML=`<button class="delete-btn" onclick="deleteItem('${item}')">×</button>${item}<span>${total.toFixed(2)} ريال</span>`;
        container.appendChild(card);
    });
}

// فلتر حسب التاريخ
function applyFilter(){
    let from = document.getElementById('filterFrom').value;
    let to = document.getElementById('filterTo').value;
    filteredExpenses = expenses.filter(e=>{
        if(from && to) return e.date>=from && e.date<=to;
        else if(from) return e.date>=from;
        else if(to) return e.date<=to;
        else return true;
    });
    displayExpenses(filteredExpenses);
}

function clearFilter(){
    document.getElementById('filterFrom').value='';
    document.getElementById('filterTo').value='';
    filteredExpenses=null;
    displayExpenses();
}

displayExpenses();
</script>

</body>
</html>
