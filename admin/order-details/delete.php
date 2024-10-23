<?php
session_start(); // Bắt đầu phiên

// Kết nối cơ sở dữ liệu
include_once '../dbconnect.php';

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

// Kiểm tra xem đơn hàng có tồn tại không
$check_order_sql = "SELECT * FROM orders WHERE order_id = $order_id";
$check_order_result = $conn->query($check_order_sql);

if ($check_order_result->num_rows > 0) {
    // Xóa các mục trong order_items liên quan đến đơn hàng
    $delete_items_sql = "DELETE FROM order_items WHERE order_id = $order_id";
    
    if ($conn->query($delete_items_sql) === TRUE) {
        // Sau khi xóa các mục, xóa đơn hàng
        $delete_order_sql = "DELETE FROM orders WHERE order_id = $order_id";
        
        if ($conn->query($delete_order_sql) === TRUE) {
            $_SESSION['success_message'] = "Đơn hàng đã được xóa thành công!";
            header("Location: index.php"); // Chuyển hướng về trang danh sách đơn hàng
            exit();
        } else {
            echo "<div class='alert alert-danger'>Lỗi khi xóa đơn hàng: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Lỗi khi xóa mục đơn hàng: " . $conn->error . "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Đơn hàng không tồn tại!</div>";
}
?>
