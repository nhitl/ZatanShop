<?php
// Kết nối cơ sở dữ liệu
include_once '../dbconnect.php';
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

// Mảng ánh xạ trạng thái đơn hàng
$status_mapping = [
    'pending' => 'Đang chờ',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang vận chuyển',
    'delivered' => 'Đã giao',
    'canceled' => 'Đã hủy'
];

// Xử lý cập nhật trạng thái đơn hàng khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['order_status'];
    $update_sql = "UPDATE orders SET order_status = '$new_status' WHERE order_id = $order_id";
    if ($conn->query($update_sql) === TRUE) {
        $_SESSION['success_message'] = "Cập nhật trạng thái thành công!";
        // Chuyển hướng về chính trang này để tránh việc submit form khi reload trang
        header("Location: " . $_SERVER['PHP_SELF'] . "?order_id=$order_id");
        exit(); // Dừng việc thực thi tiếp sau khi chuyển hướng
    } else {
        echo "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

// Lấy thông tin đơn hàng và voucher
$sql_order = "SELECT orders.*, shipping_addresses.address, shipping_addresses.recipient_name, shipping_addresses.recipient_phone, users.full_name, voucher.description AS voucher_description, voucher.discount_percentage
              FROM orders
              LEFT JOIN shipping_addresses ON orders.address_id = shipping_addresses.address_id
              LEFT JOIN users ON orders.user_id = users.user_id
              LEFT JOIN voucher ON orders.voucher_code = voucher.voucher_code
              WHERE orders.order_id = $order_id";

$order_result = $conn->query($sql_order);

if (!$order_result) {
    die("Lỗi truy vấn SQL: " . $conn->error);
}

$order = $order_result->fetch_assoc();

$total_amount = $order['total_amount'];
$shipping_fee = $order['shipping_fee'];
$grand_total = $order['grand_total'];

// Tính tổng giảm
$discount_total = ($total_amount + $shipping_fee) - $grand_total;

// Định dạng giá trị tổng giảm
$formatted_discount_total = $discount_total == floor($discount_total) ? number_format($discount_total, 0) : number_format($discount_total, 2);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng</title>
    <!-- Cập nhật Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/orders.css">
    <style>
        .container {
            margin-top: 100px;
            margin-bottom: 100px;
        }
    </style>
</head>

<body>
    <?php
    // Include header
    include_once '../header.php';
    include_once '../notification.php';
    ?>

    <div class="container">
        <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left-long"></i></a>

        <h2 class="text-center mb-4">Chi tiết đơn hàng #<?= $order['order_id'] ?></h2>

        <!-- Hiển thị thông báo thành công nếu có -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); // Xóa thông báo sau khi hiển thị 
            ?>
        <?php endif; ?>

        <!-- Nội dung chi tiết đơn hàng -->
        <div class="card mb-3">
            <div class="card-body">
                <h5>Thông tin người nhận</h5>
                <p><strong>Tên:</strong> <?= $order['recipient_name'] ?></p>
                <p><strong>Địa chỉ:</strong> <?= $order['address'] ?></p>
                <p><strong>Điện thoại:</strong> <?= $order['recipient_phone'] ?></p>
            </div>
        </div>

        <h5>Sản phẩm trong đơn hàng</h5>

        <div class="order-items-container">
            <!-- Thêm tiêu đề -->
            <div class="order-item-header d-flex">
                <div class="product-name-header">Tên sản phẩm</div>
                <div class="product-quantity-header">Số lượng</div>
                <div class="product-price-header">Đơn giá</div>
                <div class="product-total-header">Tổng cộng</div>
            </div>

            <?php
            // Lấy thông tin các sản phẩm trong đơn hàng
            $sql_items = "SELECT order_items.*, products.product_name 
          FROM order_items
          LEFT JOIN products ON order_items.product_id = products.product_id
          WHERE order_items.order_id = $order_id";
            $items_result = $conn->query($sql_items);
            while ($item = $items_result->fetch_assoc()):
                // Tính giá sản phẩm và tổng cộng
                $price = $item['price'];
                $total = $price * $item['quantity'];

                // Kiểm tra nếu giá có phần thập phân hay không
                $formatted_price = $price == floor($price) ? number_format($price, 0) : number_format($price, 2);
                $formatted_total = $total == floor($total) ? number_format($total, 0) : number_format($total, 2);
            ?>
                <div class="order-item d-flex">
                    <div class="product-name flex-grow-1" title="<?= htmlspecialchars($item['product_name']) ?>">
                        <?= htmlspecialchars($item['product_name']) ?>
                    </div>
                    <div class="product-quantity"><?= $item['quantity'] ?></div>
                    <div class="product-price"><?= $formatted_price ?>₫</div>
                    <div class="product-total"><?= $formatted_total ?>₫</div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5>Thông tin đơn hàng</h5>
                <?php
                // Định dạng các giá trị
                $formatted_total_amount = $total_amount == floor($total_amount) ? number_format($total_amount, 0) : number_format($total_amount, 2);
                $formatted_shipping_fee = $shipping_fee == floor($shipping_fee) ? number_format($shipping_fee, 0) : number_format($shipping_fee, 2);
                $formatted_grand_total = $grand_total == floor($grand_total) ? number_format($grand_total, 0) : number_format($grand_total, 2);
                ?>
                <p><strong>Tổng tiền hàng:</strong> <?= $formatted_total_amount ?>₫</p>
                <p><strong>Phí vận chuyển:</strong> <?= $formatted_shipping_fee ?>₫</p>
                <p><strong>Voucher đã sử dụng:</strong> <?= htmlspecialchars($order['voucher_code']) ?> ( Tổng Giảm : <?= $formatted_discount_total ?>₫ )</p>
                <p><strong>Tổng thanh toán:</strong> <?= $formatted_grand_total ?>₫</p>
                <p><strong>Phương thức thanh toán:</strong> <?= $order['payment_method'] == 'online' ? 'Online' : 'Thanh toán khi nhận hàng' ?></p>
                <p><strong>Trạng thái đơn hàng:</strong> <?= $status_mapping[$order['order_status']] ?></p>

                <!-- Form cập nhật trạng thái đơn hàng -->
                <form action="" method="POST" class="mt-4">
                    <label for="order_status"><strong>Cập nhật trạng thái đơn hàng:</strong></label>
                    <select name="order_status" id="order_status" class="form-select" required>
                        <?php foreach ($status_mapping as $key => $value): ?>
                            <option value="<?= $key ?>" <?= $order['order_status'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary mt-3">Cập nhật</button>
                </form>
            </div>
        </div>



        <a href="index.php" class="btn btn-secondary">Quay lại</a>
        <a href="delete.php?order_id=<?= $order['order_id'] ?>" class="btn btn-danger float-end" onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này không?');">Xóa đơn hàng</a>

    </div>

    <!-- Cập nhật Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>