<?php
session_start();

// التحقق من تسجيل دخول المدير
if(!isset($_SESSION['adminLogged']) || $_SESSION['adminLogged'] !== true){
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لوحة تحكم المدير - صالون لمسة إبداعية</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
body {
    font-family:'Cairo',sans-serif;
    background: linear-gradient(180deg,#000,#111);
    color:#fff;
    margin:0;
    padding:40px;
    display:flex;
    flex-direction:column;
    align-items:center;
}
header { text-align:center; margin-bottom:30px; }
header .logo-container {
    display:inline-block;
    border:4px solid gold;
    border-radius:50%;
    padding:5px;
    box-shadow:0 0 20px gold;
    margin-bottom:10px;
}
header .logo-container img {
    width:100px;
    height:100px;
    border-radius:50%;
    object-fit:cover;
}
header h1 {
    font-size:2rem;
    color:gold;
    margin:0;
    text-shadow:0 0 10px gold;
}

.button-row {
    display:flex;
    gap:20px;
    flex-wrap:wrap;
    justify-content:center;
    margin-bottom:20px;
}
.dashboard-btn {
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:10px;
    padding:25px;
    background:#222;
    color:#fff;
    border:none;
    border-radius:20px;
    cursor:pointer;
    transition:0.3s;
    width:160px;
    height:160px;
    text-align:center;
    font-size:1.1rem;
}
.dashboard-btn i { font-size:3rem; color:gold; }
.dashboard-btn:hover {
    background:#333;
    box-shadow:0 0 25px gold;
    transform:translateY(-3px);
}

.control-btn {
    padding:12px 20px;
    background:gold;
    color:#111;
    font-weight:bold;
    border:none;
    border-radius:10px;
    cursor:pointer;
    transition:0.3s;
    margin-top:10px;
    font-size:1rem;
}
.control-btn:hover {
    transform:translateY(-2px);
    box-shadow:0 0 20px gold;
}

/* Toast */
#toast {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: gold;
    color: #111;
    padding: 12px 25px;
    border-radius: 10px;
    box-shadow: 0 0 20px gold;
    font-weight: bold;
    display: none;
    z-index: 1000;
    animation: fadeinout 3s forwards;
}
@keyframes fadeinout {
    0% {opacity: 0; transform: translateX(-50%) translateY(-10px);}
    10%, 90% {opacity: 1; transform: translateX(-50%) translateY(0);}
    100% {opacity: 0; transform: translateX(-50%) translateY(-10px);}
}

@media(max-width:480px){
    .dashboard-btn {
        width:120px;
        height:120px;
        padding:15px;
        font-size:0.95rem;
    }
    .dashboard-btn i { font-size:2rem; }
}
</style>
</head>
<body>

<header>
    <div class="logo-container">
        <img src="logo.jpg" alt="Logo">
    </div>
    <h1>لوحة تحكم المدير</h1>
</header>

<div class="button-row">
    <button class="dashboard-btn" onclick="location.href='sales_table.php'">
        <i class="fas fa-table"></i>
        <span>جدول المبيعات</span>
    </button>
    <button class="dashboard-btn" onclick="location.href='sales_cards.php'">
        <i class="fas fa-chart-pie"></i>
        <span>بطاقات المبيعات</span>
    </button>
    <button class="dashboard-btn" onclick="location.href='manage_employees.php'">
        <i class="fas fa-user-cog"></i>
        <span>إدارة الموظفين</span>
    </button>
    <button class="dashboard-btn" onclick="location.href='expenses.php'">
        <i class="fas fa-money-bill-wave"></i>
        <span>المصروفات</span>
    </button>
    <button class="dashboard-btn" onclick="location.href='withdrawals.php'">
        <i class="fas fa-hand-holding-dollar"></i>
        <span>سحوبات الموظفين</span>
    </button>
    <button class="dashboard-btn" onclick="location.href='monthly_summary.php'">
        <i class="fas fa-calendar-alt"></i>
        <span>ملخص المبيعات الشهرية</span>
    </button>
    <button class="dashboard-btn" onclick="location.href='change_admin_password.php'">
        <i class="fas fa-key"></i>
        <span>تغيير كلمة المرور</span>
    </button>
    <button class="dashboard-btn" onclick="location.href='analysis.php'">
        <i class="fas fa-chart-line"></i>
        <span>تحليل الحسابات</span>
    </button>
    <button class="dashboard-btn" onclick="location.href='manager_withdrawals.php'">
        <i class="fas fa-hand-holding-usd"></i>
        <span>سحوبات المدير</span>
    </button>
    <!-- زر التصفير -->
    <button class="dashboard-btn" id="resetButton">
        <i class="fas fa-broom"></i>
        <span>تصفير البرنامج</span>
    </button>
</div>

<button class="control-btn" id="editOrderBtn">تعديل الترتيب</button>
<button class="control-btn" id="saveOrderBtn" style="display:none;">حفظ الترتيب</button>
<button class="control-btn" id="resetOrderBtn">إعادة الترتيب الافتراضي</button>
<button class="control-btn" onclick="logout()">تسجيل الخروج</button>

<div id="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// تسجيل الخروج
function logout(){
    location.href = "index.php";
}

// حفظ الترتيب الافتراضي عند أول تحميل
const buttonRow = document.querySelector('.button-row');
const editBtn = document.getElementById('editOrderBtn');
const saveBtn = document.getElementById('saveOrderBtn');
const resetOrderBtn = document.getElementById('resetOrderBtn');
const toast = document.getElementById('toast');
const resetButton = document.getElementById("resetButton");

if(!localStorage.getItem('defaultOrder')) {
    const defaultOrder = [...buttonRow.children].map(c => c.getAttribute('onclick'));
    localStorage.setItem('defaultOrder', JSON.stringify(defaultOrder));
}

let sortable = new Sortable(buttonRow, {
    animation: 150,
    disabled: true,
    ghostClass: 'dragging',
    onStart: function (evt) {
        evt.item.style.boxShadow = "0 0 25px gold";
        evt.item.style.transform = "scale(1.05)";
    },
    onEnd: function (evt) {
        evt.item.style.boxShadow = "";
        evt.item.style.transform = "";
        saveOrder();
    }
});

editBtn.addEventListener('click', () => {
    sortable.option("disabled", false);
    editBtn.style.display = "none";
    saveBtn.style.display = "inline-block";
});

saveBtn.addEventListener('click', () => {
    saveOrder();
    sortable.option("disabled", true);
    saveBtn.style.display = "none";
    editBtn.style.display = "inline-block";
    showToast("تم حفظ الترتيب بنجاح!");
});

resetOrderBtn.addEventListener('click', () => {
    const defaultOrder = JSON.parse(localStorage.getItem('defaultOrder'));
    defaultOrder.forEach(clickAttr => {
        let el = [...buttonRow.children].find(c => c.getAttribute('onclick') === clickAttr);
        if (el) buttonRow.appendChild(el);
    });
    saveOrder();
    showToast("تم إعادة الترتيب الافتراضي!");
});

function saveOrder() {
    let order = [...buttonRow.children].map(c => c.getAttribute('onclick'));
    localStorage.setItem('dashboardOrder', JSON.stringify(order));
}

(function loadOrder() {
    let saved = JSON.parse(localStorage.getItem('dashboardOrder'));
    if (!saved) return;
    saved.forEach(clickAttr => {
        let el = [...buttonRow.children].find(c => c.getAttribute('onclick') === clickAttr);
        if (el) buttonRow.appendChild(el);
    });
})();

function showToast(msg){
    toast.textContent = msg;
    toast.style.display = "block";
    setTimeout(() => {
        toast.style.display = "none";
    }, 3000);
}

// زر التصفير
resetButton.addEventListener("click", function () {
    if(confirm("تحذير: سيتم مسح جميع بيانات البرنامج! هل تريد المتابعة؟")) {
        window.location.href = "reset_program.php";
    }
});
</script>

</body>
</html>
