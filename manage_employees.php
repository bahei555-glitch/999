<?php
session_start();

// حماية: إذا لم يتم تسجيل دخول المدير
if(!isset($_SESSION['adminLogged']) || $_SESSION['adminLogged'] !== true){
    header("Location: login.php");
    exit;
}

// ملف حفظ الموظفين
$file = 'workers.json';

// قراءة البيانات
if(!file_exists($file)){
    file_put_contents($file, json_encode([]));
}
$workers = json_decode(file_get_contents($file), true);

// معالجة POST للعمليات
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $action = $_POST['action'] ?? '';

    if($action === 'add'){
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        if($name && $username && $password){
            $workers[] = ['name'=>$name,'username'=>$username,'password'=>$password];
            file_put_contents($file, json_encode($workers));
        }
    }

    if($action === 'edit'){
        $index = intval($_POST['index'] ?? -1);
        if(isset($workers[$index])){
            $name = trim($_POST['name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            if($name && $username && $password){
                $workers[$index] = ['name'=>$name,'username'=>$username,'password'=>$password];
                file_put_contents($file, json_encode($workers));
            }
        }
    }

    if($action === 'delete'){
        $index = intval($_POST['index'] ?? -1);
        if(isset($workers[$index])){
            array_splice($workers, $index, 1);
            file_put_contents($file, json_encode($workers));
        }
    }

    header("Location: manage_employees.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إدارة الموظفين - صالون لمسة إبداعية</title>
<style>
body { font-family:'Cairo',sans-serif; background: linear-gradient(180deg,#000,#111); color:#fff; display:flex; justify-content:center; align-items:flex-start; min-height:100vh; margin:0; padding:40px 0; }
header { text-align:center; margin-bottom:30px; }
header .logo-container { display:inline-block; border:4px solid gold; border-radius:50%; padding:5px; box-shadow:0 0 20px gold; margin-bottom:10px; }
header .logo-container img { width:100px; height:100px; border-radius:50%; object-fit:cover; }
header h1 { font-size:2rem; color:gold; margin:0; text-shadow:0 0 10px gold; }

.employee-box { background:#111; padding:20px; border-radius:15px; box-shadow:0 0 35px rgba(255,215,0,0.5); width:90%; max-width:600px; text-align:center; }
.employee-box:hover { box-shadow:0 0 50px rgba(255,215,0,0.8); }

.top-buttons { display:flex; justify-content:flex-end; gap:10px; margin-bottom:15px; flex-direction: row-reverse; }
button { padding:8px 12px; border-radius:6px; border:none; cursor:pointer; margin:2px; }
.add-btn { background:gold; color:#111; font-weight:bold; }
.back-btn { background:gold; color:#111; font-weight:bold; }
.edit-btn { background:#2a5298; color:#fff; }
.delete-btn { background:red; color:#fff; }

table { width:100%; border-collapse:collapse; background:#222; border-radius:10px; overflow:hidden; }
table th, table td { padding:10px; text-align:center; border-bottom:1px solid #444; }
table th { background:#333; color:gold; }

@media(max-width:480px){ 
    table th, table td { font-size:0.8rem; padding:6px; } 
    button { font-size:0.8rem; padding:6px; } 
    .top-buttons { flex-direction: column; align-items:center; }
}
</style>
</head>
<body>
<div style="display:flex; flex-direction:column; align-items:center; width:100%;">
<header>
    <div class="logo-container"><img src="logo.jpg" alt="Logo"></div>
    <h1>إدارة الموظفين</h1>
</header>

<div class="employee-box">
    <div class="top-buttons">
        <button class="back-btn" onclick="window.location.href='dashboard.php'">رجوع للوحة التحكم</button>
        <button class="add-btn" onclick="showAddForm()">إضافة موظف جديد</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>اسم المستخدم</th>
                <th>كلمة المرور</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($workers as $i=>$w): ?>
            <tr>
                <td><?= htmlspecialchars($w['name']) ?></td>
                <td><?= htmlspecialchars($w['username']) ?></td>
                <td><?= htmlspecialchars($w['password']) ?></td>
                <td>
                    <button class="edit-btn" onclick="showEditForm(<?= $i ?>)">تعديل</button>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="index" value="<?= $i ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('هل تريد حذف الموظف؟')">حذف</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>

<!-- Forms modals -->
<div id="employeeForm" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); justify-content:center; align-items:center;">
    <form method="post" style="background:#222; padding:20px; border-radius:12px; display:flex; flex-direction:column; gap:10px; min-width:300px;">
        <h2 id="formTitle" style="color:gold; text-align:center; margin:0;">إضافة موظف</h2>
        <input type="text" name="name" id="empName" placeholder="الاسم" required>
        <input type="text" name="username" id="empUsername" placeholder="اسم المستخدم" required>
        <input type="text" name="password" id="empPassword" placeholder="كلمة المرور" required>
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="index" id="formIndex" value="">
        <div style="display:flex; gap:10px; justify-content:center;">
            <button type="submit" style="background:gold; color:#111;">حفظ</button>
            <button type="button" style="background:red; color:#fff;" onclick="hideForm()">إلغاء</button>
        </div>
    </form>
</div>

<script>
function showAddForm(){
    document.getElementById('employeeForm').style.display='flex';
    document.getElementById('formTitle').textContent='إضافة موظف';
    document.getElementById('formAction').value='add';
    document.getElementById('empName').value='';
    document.getElementById('empUsername').value='';
    document.getElementById('empPassword').value='';
}

function showEditForm(index){
    const row = document.querySelectorAll('table tbody tr')[index];
    const cells = row.children;
    document.getElementById('employeeForm').style.display='flex';
    document.getElementById('formTitle').textContent='تعديل موظف';
    document.getElementById('formAction').value='edit';
    document.getElementById('formIndex').value=index;
    document.getElementById('empName').value=cells[0].textContent;
    document.getElementById('empUsername').value=cells[1].textContent;
    document.getElementById('empPassword').value=cells[2].textContent;
}

function hideForm(){
    document.getElementById('employeeForm').style.display='none';
}
</script>
</body>
</html>
