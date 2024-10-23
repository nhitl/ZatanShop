<?php
// Kết nối đến cơ sở dữ liệu
include 'dbconnect.php'; // Đảm bảo bạn đã kết nối tới cơ sở dữ liệu

// Lấy order_id từ query string
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Truy vấn thông tin đơn hàng
$order_sql = "SELECT grand_total, payment_method FROM orders WHERE order_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_stmt->bind_result($grand_total, $payment_method);
$order_stmt->fetch();
$order_stmt->close();

// Kiểm tra nếu đơn hàng tồn tại
if (!$grand_total) {
    die("Đơn hàng không tồn tại.");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán online</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Thanh toán online</h2>
        <div class="card">
            <div class="card-body">
                <h5>Đơn hàng: <span class="badge bg-secondary"><?php echo $order_id; ?></span></h5>
                <p>Tổng số tiền cần thanh toán: <strong><?php echo number_format($grand_total, 0, ',', '.') . 'đ'; ?></strong></p>

                <form action="process_payment.php" method="post">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <div class="form-group">
                        <label for="payment_method">Chọn phương thức thanh toán:</label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="" disabled selected>-- Chọn phương thức --</option>
                            <option value="credit_card">Thẻ tín dụng</option>
                            <option value="momo">MoMo</option>
                            <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Thanh toán</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
