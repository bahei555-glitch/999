<?php
session_start();
if(!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// جلب الموظفين
$employees = $conn->query("SELECT * FROM employees")->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){
    $employee_id = $_POST['employee_id'];
    $amount = $_POST['amount'];
    $advance_date = $_POST['advance_date'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO advances (employee_id, amount, advance_date, status, notes) VALUES (?,?,?,?,?)");
    $stmt->execute([$employee_id, $amount, $advance_date, $status, $notes]);
    $success = "تم إضافة السلفة بنجاح!";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إضافة سلفة</title>
</head>
<body>
<h2>إضافة سلفة</h2>
<a href="dashboard.php">العودة للصفحة المركزية</a>
<?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
<form method="POST">
    <label>الموظف:</label><br>
    <select name="employee_id" required>
        <?php foreach($employees as $e): ?>
            <option value="<?= $e['id'] ?>"><?= $e['name'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>المبلغ:</label><br>
    <input type="number" step="0.01" name="amount" required><br><br>

    <label>الحالة:</label><br>
    <select name="status">
        <option value="مدفوع">مدفوع</option>
        <option value="غير مدفوع">غير مدفوع</option>
    </select><br><br>

    <label>التاريخ:</label><br>
    <input type="date" name="advance_date" required><br><br>

    <label>ملاحظات:</label><br>
    <textarea name="notes"></textarea><br><br>

    <button type="submit" name="submit">إضافة</button>
</form>
</body>
</html>
