<?php
// employee_management.php
session_start();
if(!isset($_SESSION['adminLogged']) || $_SESSION['adminLogged'] !== true){
    header('Location: login.php');
    exit;
}

// اتصال بقاعدة البيانات
$host = "localhost";
$db = "barbershop_db";
$user = "root";  // غيره حسب الإعداد
$pass = "";      // كلمة المرور حسب إعداد XAMPP
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// إضافة موظف جديد
if(isset($_POST['add'])){
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? 'موظف';
    $phone = $_POST['phone'] ?? '';
    $notes = $_POST['notes'] ?? '';
    if($name){
        $stmt = $pdo->prepare("INSERT INTO employees (name, role, phone, notes) VALUES (?,?,?,?)");
        $stmt->execute([$name, $role, $phone, $notes]);
    }
}

// حذف موظف
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM employees WHERE id=?");
    $stmt->execute([$id]);
}

// جلب جميع الموظفين
$stmt = $pdo->query("SELECT * FROM employees ORDER BY id DESC");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إدارة الموظفين - صالون لمسة إبداعية</title>
<style>
/* نفس التصميم السابق */
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
@media(max-width:480px){ table th, table td { font-size:0.8rem; padding:6px; } button { font-size:0.8rem; padding:6px; } .top-buttons { flex-direction: column; align-items:center; } }
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
        <button class="add-btn" onclick="document.getElementById('addForm').style.display='block'">إضافة موظف جديد</button>
    </div>

    <!-- نموذج إضافة موظف -->
    <form id="addForm" style="display:none; margin-bottom:15px;" method="POST">
        <input type="text" name="name" placeholder="اسم الموظف" required>
        <input type="text" name="role" placeholder="الدور" value="موظف">
        <input type="text" name="phone" placeholder="الهاتف">
        <input type="text" name="notes" placeholder="ملاحظات">
        <button type="submit" name="add" class="add-btn">حفظ الموظف</button>
        <button type="button" onclick="this.parentElement.style.display='none'" class="back-btn">إلغاء</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>الدور</th>
                <th>الهاتف</th>
                <th>ملاحظات</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($employees as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['name']) ?></td>
                <td><?= htmlspecialchars($e['role']) ?></td>
                <td><?= htmlspecialchars($e['phone']) ?></td>
                <td><?= htmlspecialchars($e['notes']) ?></td>
                <td>
                    <a href="edit_employee.php?id=<?= $e['id'] ?>" class="edit-btn">تعديل</a>
                    <a href="?delete=<?= $e['id'] ?>" class="delete-btn" onclick="return confirm('هل تريد حذف الموظف؟')">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
</body>
</html>
