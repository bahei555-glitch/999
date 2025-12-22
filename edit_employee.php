<?php
// edit_employee.php
session_start();
if(!isset($_SESSION['adminLogged']) || $_SESSION['adminLogged'] !== true){
    header('Location: login.php');
    exit;
}

$host = "localhost";
$db = "barbershop_db";
$user = "root";
$pass = "";
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// التحقق من وجود ID للموظف
if(!isset($_GET['id'])){
    header('Location: employee_management.php');
    exit;
}

$id = intval($_GET['id']);

// جلب بيانات الموظف
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id=?");
$stmt->execute([$id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$employee){
    die("الموظف غير موجود");
}

// حفظ التعديلات
if(isset($_POST['save'])){
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $notes = $_POST['notes'] ?? '';

    $stmt = $pdo->prepare("UPDATE employees SET name=?, role=?, phone=?, notes=? WHERE id=?");
    $stmt->execute([$name, $role, $phone, $notes, $id]);
    header('Location: employee_management.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تعديل الموظف - صالون لمسة إبداعية</title>
<style>
body { font-family:'Cairo',sans-serif; background: linear-gradient(180deg,#000,#111); color:#fff; display:flex; justify-content:center; align-items:flex-start; min-height:100vh; margin:0; padding:40px 0; }
header { text-align:center; margin-bottom:30px; }
header .logo-container { display:inline-block; border:4px solid gold; border-radius:50%; padding:5px; box-shadow:0 0 20px gold; margin-bottom:10px; }
header .logo-container img { width:100px; height:100px; border-radius:50%; object-fit:cover; }
header h1 { font-size:2rem; color:gold; margin:0; text-shadow:0 0 10px gold; }

.edit-box { background:#111; padding:20px; border-radius:15px; box-shadow:0 0 35px rgba(255,215,0,0.5); width:90%; max-width:500px; text-align:center; }
.edit-box:hover { box-shadow:0 0 50px rgba(255,215,0,0.8); }

input { width:90%; padding:10px; margin:8px 0; border-radius:8px; border:none; background:#222; color:#fff; font-size:1rem; text-align:right; }
button { padding:10px 15px; border-radius:8px; border:none; cursor:pointer; font-weight:bold; margin:5px; }
.save-btn { background:gold; color:#111; }
.back-btn { background:#2a5298; color:#fff; }
</style>
</head>
<body>
<div style="display:flex; flex-direction:column; align-items:center; width:100%;">
<header>
    <div class="logo-container"><img src="logo.jpg" alt="Logo"></div>
    <h1>تعديل بيانات الموظف</h1>
</header>

<div class="edit-box">
    <form method="POST">
        <input type="text" name="name" placeholder="اسم الموظف" value="<?= htmlspecialchars($employee['name']) ?>" required>
        <input type="text" name="role" placeholder="الدور" value="<?= htmlspecialchars($employee['role']) ?>">
        <input type="text" name="phone" placeholder="الهاتف" value="<?= htmlspecialchars($employee['phone']) ?>">
        <input type="text" name="notes" placeholder="ملاحظات" value="<?= htmlspecialchars($employee['notes']) ?>">
        <div>
            <button type="submit" name="save" class="save-btn">حفظ التعديلات</button>
            <button type="button" class="back-btn" onclick="window.location.href='employee_management.php'">رجوع</button>
        </div>
    </form>
</div>
</div>
</body>
</html>
