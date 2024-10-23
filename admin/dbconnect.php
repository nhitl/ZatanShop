<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Nếu chưa đăng nhập, chuyển hướng đến trang login
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] != 1) {
    header("Location: ../no_access.php"); // Nếu không phải role = 2, chuyển hướng đến trang lỗi hoặc trang khác
    exit();
}

$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "zatanshop";

// Tạo kết nối
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
