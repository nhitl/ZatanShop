<?php
include_once 'dbconnect.php'; // Kết nối cơ sở dữ liệu
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Lấy trạng thái hiện tại của đơn hàng
    $sql_check_status = "SELECT order_status FROM orders WHERE order_id = ?";
    $stmt_check_status = $conn->prepare($sql_check_status);
    $stmt_check_status->bind_param('i', $order_id);
    $stmt_check_status->execute();
    $result_check_status = $stmt_check_status->get_result();

    if ($result_check_status->num_rows > 0) {
        $order = $result_check_status->fetch_assoc();
        $current_status = $order['order_status'];

        // Kiểm tra trạng thái đơn hàng
        if ($current_status === 'canceled') {
            echo 'error|Đơn hàng đã được hủy. Không thể thực hiện hành động hủy.';
            exit; // Dừng script
        } elseif ($current_status === 'delivered') {
            echo 'error|Đơn hàng đã được giao. Không thể thực hiện hành động hủy.';
            exit; // Dừng script
        }
    } else {
        echo 'error|Không tìm thấy đơn hàng.';
        exit; // Dừng script
    }

    // Lấy thông tin các sản phẩm trong đơn hàng
    $sql_items = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param('i', $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();

    // Cập nhật lại số lượng tồn kho và số lượng bán
    while ($item = $result_items->fetch_assoc()) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        // Cập nhật lại stock_quantity và sales_count
        $sql_update = "UPDATE products SET stock_quantity = stock_quantity + ?, sales_count = sales_count - ? WHERE product_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('iii', $quantity, $quantity, $product_id);
        $stmt_update->execute();
    }

    // Cập nhật trạng thái đơn hàng thành 'canceled'
    $sql_cancel = "UPDATE orders SET order_status = 'canceled' WHERE order_id = ?";
    $stmt_cancel = $conn->prepare($sql_cancel);
    $stmt_cancel->bind_param('i', $order_id);
    if ($stmt_cancel->execute()) {
        // Thông báo hủy đơn hàng thành công
        echo 'success|Đơn hàng đã được hủy thành công.';
    } else {
        echo 'error|Không thể hủy đơn hàng.';
    }
}
