<?php
$host = "localhost";
$db_name = "barbershop_db";
$username = "root"; // اسم المستخدم لديك
$password = "";     // كلمة المرور لديك

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "فشل الاتصال بقاعدة البيانات: " . $e->getMessage();
    exit;
}
?>
