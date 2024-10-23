<?php
include('dbconnect.php');
session_start(); // Khởi tạo phiên làm việc

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để xem giỏ hàng.']);
    exit();
}

$user_id = $_SESSION['user_id']; // Lấy ID người dùng từ session

// Truy vấn để lấy tổng số lượng sản phẩm trong giỏ hàng
$sql = "SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// In số lượng sản phẩm hoặc 0 nếu không có
$total_items = $row['total_items'] ?? 0;
echo json_encode(['status' => 'success', 'count' => $total_items]); // Trả về số lượng sản phẩm
?>
