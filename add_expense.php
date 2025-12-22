<?php
session_start();
if(!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

if(isset($_POST['submit'])){
    $item = $_POST['item'];
    $amount = $_POST['amount'];
    $expense_date = $_POST['expense_date'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO expenses (item, amount, expense_date, notes) VALUES (?,?,?,?)");
    $stmt->execute([$item, $amount, $expense_date, $notes]);
    $success = "تم إضافة المصروف بنجاح!";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إضافة مصروف</title>
</head>
<body>
<h2>إضافة مصروف</h2>
<a href="dashboard.php">العودة للصفحة المركزية</a>
<?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
<form method="POST">
    <label>البند:</label><br>
    <input type="text" name="item" required><br><br>

    <label>المبلغ:</label><br>
    <input type="number" step="0.01" name="amount" required><br><br>

    <label>التاريخ:</label><br>
    <input type="date" name="expense_date" required><br><br>

    <label>ملاحظات:</label><br>
    <textarea name="notes"></textarea><br><br>

    <button type="submit" name="submit">إضافة</button>
</form>
</body>
</html>
