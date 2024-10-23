<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/cart.css">
    <script>
        function toggleOtherAddresses() {
            const otherAddresses = document.getElementById('other-addresses');
            if (otherAddresses.style.display === 'none' || otherAddresses.style.display === '') {
                otherAddresses.style.display = 'block';
            } else {
                otherAddresses.style.display = 'none';
            }
        }
    </script>
</head>

<body>
    <?php include_once 'header.php'; ?>

    <div class="container mt-4">
        <h1>Thanh toán</h1>
        <?php if ($is_cart_empty): ?>
            <div class="alert alert-warning" role="alert">
                Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm vào giỏ hàng trước khi thanh toán.
            </div>
        <?php else: ?>
            <!-- Thông tin nhận hàng -->
            <form id="checkout-form" method="POST" action="process_checkout.php">
                <h2>Thông tin nhận hàng</h2>
                
                <!-- Địa chỉ mặc định -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Địa chỉ mặc định</h5>
                        <p class="card-text">
                            <strong>Họ và tên:</strong> <?php echo htmlspecialchars($shipping_info['recipient_name']); ?><br>
                            <strong>Số điện thoại:</strong> <?php echo htmlspecialchars($shipping_info['recipient_phone']); ?><br>
                            <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($shipping_info['address']); ?>
                        </p>
                    </div>
                </div>

                <!-- Hiển thị nút chọn địa chỉ khác -->
                <div class="mb-3">
                    <button type="button" class="btn btn-link" onclick="toggleOtherAddresses()">Chọn địa chỉ khác</button>
                </div>

                <!-- Các địa chỉ khác (ẩn mặc định) -->
                <div id="other-addresses" style="display: none;">
                    <?php while ($other_address = $result_other_addresses->fetch_assoc()): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Địa chỉ khác</h5>
                                <p class="card-text">
                                    <strong>Họ và tên:</strong> <?php echo htmlspecialchars($other_address['recipient_name']); ?><br>
                                    <strong>Số điện thoại:</strong> <?php echo htmlspecialchars($other_address['recipient_phone']); ?><br>
                                    <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($other_address['address']); ?>
                                </p>
                                <button type="button" class="btn btn-primary">Chọn địa chỉ này</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>
