<?php
include 'dbconnect.php'; // Kết nối cơ sở dữ liệu
session_start();

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Chuyển hướng người dùng đến trang đăng nhập nếu chưa đăng nhập
    exit();
}

$user_id = $_SESSION['user_id']; // Lấy ID người dùng từ phiên làm việc

// Lấy thông tin giỏ hàng của người dùng
$sql_cart = "SELECT c.cart_id, c.product_id, c.quantity, p.product_name, p.price, p.background_image, 
                     p.stock_quantity, COALESCE(d.discount_percentage, 0) AS discount_percentage
             FROM cart c
             JOIN products p ON c.product_id = p.product_id
             LEFT JOIN discounts d ON p.product_id = d.product_id
             WHERE c.user_id = ?";

$stmt_cart = $conn->prepare($sql_cart);
if ($stmt_cart === false) {
    die('MySQL prepare statement failed for cart: ' . htmlspecialchars($conn->error));
}

$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();
if ($result_cart === false) {
    die('MySQL execute statement failed for cart: ' . htmlspecialchars($stmt_cart->error));
}

// Kiểm tra nếu có sản phẩm trong giỏ hàng
if ($result_cart->num_rows === 0) {
    die('Giỏ hàng của bạn hiện tại trống.');
}

// Lấy thông tin người dùng và địa chỉ giao hàng
$sql_user_info = "SELECT u.full_name, u.phone_number
                  FROM users u
                  WHERE u.user_id = ?";

$stmt_user_info = $conn->prepare($sql_user_info);
if ($stmt_user_info === false) {
    die('MySQL prepare statement failed for user info: ' . htmlspecialchars($conn->error));
}

$stmt_user_info->bind_param("i", $user_id);
$stmt_user_info->execute();
$result_user_info = $stmt_user_info->get_result();
if ($result_user_info === false) {
    die('MySQL execute statement failed for user info: ' . htmlspecialchars($stmt_user_info->error));
}

$user_info = $result_user_info->fetch_assoc();
if ($user_info === NULL) {
    die('Không thể lấy thông tin người dùng.');
}

// Lấy các voucher đang hoạt động
$sql_vouchers = "SELECT voucher_code, discount_percentage, min_order_value, max_discount_value 
                 FROM voucher 
                 WHERE status = 'active' AND expiry_date >= CURDATE()";
$result_vouchers = $conn->query($sql_vouchers);
if ($result_vouchers === false) {
    die('MySQL query failed for voucher: ' . htmlspecialchars($conn->error));
}

// Xử lý form thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_id = $_POST['address_id'];

    $shipping_method = $_POST['shipping_method'];
    $payment_method = $_POST['payment_method'] ?? 'cash_on_delivery'; // Giá trị mặc định là 'cash_on_delivery'
    $online_payment_method = $_POST['online_payment_method'] ?? null;
    $selected_vouchers = isset($_POST['vouchers']) ? $_POST['vouchers'] : []; // Danh sách voucher được chọn

    $valid_payment_methods = ['online', 'cash_on_delivery'];
    if (!in_array($payment_method, $valid_payment_methods)) {
        die('Phương thức thanh toán không hợp lệ.');
    }

    // Tính toán tổng số tiền đơn hàng
    $total_amount = 0;
    $result_cart->data_seek(0); // Reset pointer
    while ($row = $result_cart->fetch_assoc()) {
        $discount = $row['discount_percentage'];
        $price = $row['price'];
        $discounted_price = $price - ($price * $discount / 100);
        $total_price = $discounted_price * $row['quantity'];
        $total_amount += $total_price;
    }

    // Lấy phí vận chuyển
    $shipping_fee = ($shipping_method === 'express') ? 100000 : 50000;

    // Tính toán giảm giá từ các voucher được chọn
    $voucher_discounts = [];
    foreach ($selected_vouchers as $voucher_code) {
        $voucher_sql = "SELECT discount_percentage, min_order_value, max_discount_value 
                        FROM voucher 
                        WHERE voucher_code = ? AND status = 'active' AND expiry_date >= CURDATE()";
        $voucher_stmt = $conn->prepare($voucher_sql);
        if ($voucher_stmt === false) {
            die('MySQL prepare statement failed for voucher check: ' . htmlspecialchars($conn->error));
        }
        $voucher_stmt->bind_param("s", $voucher_code);
        $voucher_stmt->execute();
        $voucher_result = $voucher_stmt->get_result();
        if ($voucher_result === false) {
            die('MySQL execute statement failed for voucher check: ' . htmlspecialchars($voucher_stmt->error));
        }
        if ($voucher_result->num_rows > 0) {
            $voucher_row = $voucher_result->fetch_assoc();
            if ($total_amount >= $voucher_row['min_order_value']) {
                $voucher_discount = min($voucher_row['max_discount_value'], $total_amount * $voucher_row['discount_percentage'] / 100);
                $voucher_discounts[] = $voucher_discount;
            }
        }
    }

    // Tính tổng giảm giá từ các voucher
    $total_voucher_discount = array_sum($voucher_discounts);

    // Tổng cộng
    $grand_total = $total_amount + $shipping_fee - $total_voucher_discount;

    // Lưu thông tin đơn hàng vào cơ sở dữ liệu
    $conn->begin_transaction();
    try {
        // Thêm đơn hàng
        $order_sql = "INSERT INTO orders (user_id, address_id, shipping_method, payment_method, total_amount, voucher_code, grand_total, shipping_fee) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $order_stmt = $conn->prepare($order_sql);
        if ($order_stmt === false) {
            throw new Exception('MySQL prepare statement failed for order: ' . htmlspecialchars($conn->error));
        }

        // Xử lý voucher_code cho đơn hàng
        $voucher_codes = implode(',', $selected_vouchers);

        $order_stmt->bind_param("iissdsss", $user_id, $address_id, $shipping_method, $payment_method, $total_amount, $voucher_codes, $grand_total, $shipping_fee);
        if (!$order_stmt->execute()) {
            throw new Exception('MySQL execute statement failed for order: ' . htmlspecialchars($order_stmt->error));
        }

        $order_id = $conn->insert_id; // Lấy ID đơn hàng vừa được tạo

        // Thêm chi tiết đơn hàng
        $result_cart->data_seek(0); // Reset pointer
        while ($row = $result_cart->fetch_assoc()) {
            $discount = $row['discount_percentage'];
            $price = $row['price'];
            $discounted_price = $price - ($price * $discount / 100);
            $total_price = $discounted_price * $row['quantity'];

            // Chèn chi tiết đơn hàng
            $order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                   VALUES (?, ?, ?, ?)";
            $order_item_stmt = $conn->prepare($order_item_sql);
            if ($order_item_stmt === false) {
                throw new Exception('MySQL prepare statement failed for order item: ' . htmlspecialchars($conn->error));
            }

            $order_item_stmt->bind_param("iiid", $order_id, $row['product_id'], $row['quantity'], $total_price);
            if (!$order_item_stmt->execute()) {
                throw new Exception('MySQL execute statement failed for order item: ' . htmlspecialchars($order_item_stmt->error));
            }

            // Cập nhật số lượng tồn kho
            $new_stock_quantity = max(0, $row['stock_quantity'] - $row['quantity']); // Đảm bảo tồn kho không âm
            $update_stock_sql = "UPDATE products SET stock_quantity = ? WHERE product_id = ?";
            $update_stock_stmt = $conn->prepare($update_stock_sql);
            if ($update_stock_stmt === false) {
                throw new Exception('MySQL prepare statement failed for updating stock: ' . htmlspecialchars($conn->error));
            }

            $update_stock_stmt->bind_param("ii", $new_stock_quantity, $row['product_id']);
            if (!$update_stock_stmt->execute()) {
                throw new Exception('MySQL execute statement failed for updating stock: ' . htmlspecialchars($update_stock_stmt->error));
            }

            // Cập nhật số lượng sản phẩm đã bán
            $update_sales_sql = "UPDATE products SET sales_count = sales_count + ? WHERE product_id = ?";
            $update_sales_stmt = $conn->prepare($update_sales_sql);
            if ($update_sales_stmt === false) {
                throw new Exception('MySQL prepare statement failed for updating sales_count: ' . htmlspecialchars($conn->error));
            }

            $update_sales_stmt->bind_param("ii", $row['quantity'], $row['product_id']);
            if (!$update_sales_stmt->execute()) {
                throw new Exception('MySQL execute statement failed for updating sales_count: ' . htmlspecialchars($update_sales_stmt->error));
            }
        }

        // Xóa sản phẩm khỏi giỏ hàng
        $delete_cart_sql = "DELETE FROM cart WHERE user_id = ?";
        $delete_cart_stmt = $conn->prepare($delete_cart_sql);
        if ($delete_cart_stmt === false) {
            throw new Exception('MySQL prepare statement failed for delete cart: ' . htmlspecialchars($conn->error));
        }

        $delete_cart_stmt->bind_param("i", $user_id);
        if (!$delete_cart_stmt->execute()) {
            throw new Exception('MySQL execute statement failed for delete cart: ' . htmlspecialchars($delete_cart_stmt->error));
        }

        $conn->commit();
        echo 'Đặt hàng thành công!';

        // Kiểm tra phương thức thanh toán và chuyển hướng
        if ($payment_method === 'online') {
            header('Location: online_payment.php?order_id=' . $order_id);
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        die('Có lỗi xảy ra: ' . htmlspecialchars($e->getMessage()));
    }
} else {
    die('Yêu cầu không hợp lệ.');
}
