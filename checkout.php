<?php
include 'dbconnect.php'; // Kết nối cơ sở dữ liệu
session_start();

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Chuyển hướng người dùng đến trang đăng nhập nếu chưa đăng nhập
    exit();
}

$user_id = $_SESSION['user_id']; // Lấy ID người dùng từ phiên làm việc

// Function to handle query preparation and execution
function executeQuery($conn, $sql, $params)
{
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log('MySQL prepare statement failed: ' . htmlspecialchars($conn->error));
        die('Database error. Please try again later.');
    }
    $stmt->bind_param(...$params);
    if (!$stmt->execute()) {
        error_log('MySQL execute statement failed: ' . htmlspecialchars($stmt->error));
        die('Database error. Please try again later.');
    }
    return $stmt->get_result();
}

// Lấy thông tin giỏ hàng của người dùng
$sql_cart = "SELECT c.cart_id, c.product_id, c.quantity, p.product_name, p.price, p.background_image, 
                    COALESCE(d.discount_percentage, 0) AS discount_percentage
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            LEFT JOIN discounts d ON p.product_id = d.product_id
            WHERE c.user_id = ?";
$result_cart = executeQuery($conn, $sql_cart, ['i', $user_id]);

$is_cart_empty = $result_cart->num_rows == 0;

// Lấy thông tin người dùng và địa chỉ giao hàng
$sql_user_info = "SELECT u.full_name, u.phone_number FROM users u WHERE u.user_id = ?";
$result_user_info = executeQuery($conn, $sql_user_info, ['i', $user_id]);
$user_info = $result_user_info->fetch_assoc();

// Lấy địa chỉ mặc định (is_default = 1)
$sql_shipping_info = "SELECT recipient_name, recipient_phone, address_id, address
                      FROM shipping_addresses 
                      WHERE user_id = ? AND is_default = 1";
$result_shipping_info = executeQuery($conn, $sql_shipping_info, ['i', $user_id]);
$shipping_info = $result_shipping_info->fetch_assoc();

// Lấy các địa chỉ khác (không phải mặc định)
$sql_other_addresses = "SELECT address_id, recipient_name, recipient_phone, address 
                        FROM shipping_addresses 
                        WHERE user_id = ? AND is_default = 0";
$result_other_addresses = executeQuery($conn, $sql_other_addresses, ['i', $user_id]);


// Lấy các voucher đang hoạt động
$sql_voucher = "SELECT voucher_code, discount_percentage, min_order_value, max_discount_value, expiry_date 
                FROM voucher 
                WHERE status = 'active' AND expiry_date >= CURDATE()";
$result_vouchers = $conn->query($sql_voucher);

// Lấy giá trị giảm giá tối đa
$sql_max_discount = "SELECT MAX(max_discount_value) AS max_discount_value FROM voucher WHERE status = 'active' AND expiry_date >= CURDATE()";
$max_discount_result = $conn->query($sql_max_discount);
$max_discount_row = $max_discount_result->fetch_assoc();
$max_discount_value = $max_discount_row['max_discount_value'];

// Kiểm tra xem người dùng có ít nhất một địa chỉ không
$has_address = !empty($shipping_info['recipient_name']) && !empty($shipping_info['recipient_phone']) && !empty($shipping_info['address']);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets\css\checkout.css">

</head>

<body>
    <?php include_once 'header.php'; ?>
    <section class="checkout">
        <div class="container mt-4">
            <h1>Thanh toán</h1>
            <?php if ($is_cart_empty || !$has_address): ?>
                <?php if ($is_cart_empty): ?>
                    <div class="alert alert-warning" role="alert">
                        Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm vào giỏ hàng trước khi thanh toán.
                    </div>
                <?php endif; ?>

                <?php if (!$has_address): ?>
                    <!-- Nếu không có địa chỉ, hiển thị liên kết để thêm địa chỉ mới -->
                    <p class="card-text ">
                        Bạn chưa có địa chỉ nhận hàng. <a class="text-danger" href="setting-address.php">Nhấp vào đây để thêm địa chỉ mới.</a>
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <!-- Thông tin nhận hàng -->
                <form id="checkout-form" method="POST" action="process_checkout.php">
                    <h2>Thông tin nhận hàng</h2>
                    <!-- Địa chỉ mặc định -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Họ và tên:</strong> <?php echo htmlspecialchars($shipping_info['recipient_name']); ?><br>
                                <strong>Số điện thoại:</strong> <?php echo htmlspecialchars($shipping_info['recipient_phone']); ?><br>
                                <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($shipping_info['address']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Hiển thị nút chọn địa chỉ khác -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-custom" onclick="toggleOtherAddresses()">
                            <i class="fa-solid fa-plus"></i> Chọn địa chỉ khác
                        </button>
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
                                    <button type="button" class="btn btn-select"
                                        onclick="selectAddress(
                        '<?php echo htmlspecialchars($other_address['recipient_name']); ?>', 
                        '<?php echo htmlspecialchars($other_address['recipient_phone']); ?>', 
                        '<?php echo htmlspecialchars($other_address['address']); ?>',
                        '<?php echo htmlspecialchars($other_address['address_id']); ?>')">
                                        Chọn địa chỉ này
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <a href="setting-address.php">Bạn muốn thêm địa chỉ mới?</a>
                    </div>

                    <!-- Trường ẩn để lưu ID địa chỉ đã chọn -->
                    <input type="hidden" id="selected_address_id" name="address_id" value="<?php echo htmlspecialchars($shipping_info['address_id']); ?>">


                    <h2>Các sản phẩm thanh toán</h2>
                    <ul class="list-group mb-4" id="cart-items">
                        <?php
                        $total_amount = 0;
                        while ($row = $result_cart->fetch_assoc()):
                            $discount = $row['discount_percentage'];
                            $price = $row['price'];
                            $discounted_price = $price - ($price * $discount / 100);
                            $total_price = $discounted_price * $row['quantity'];
                            $total_amount += $total_price;
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center" data-price="<?php echo $total_price; ?>">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo 'admin' . $row['background_image']; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="img-thumbnail me-3" style="width: 100px;">
                                    <div>
                                        <h5><?php echo htmlspecialchars($row['product_name']); ?></h5>
                                        <p><?php echo htmlspecialchars($row['quantity']); ?> x <?php echo number_format($discounted_price, 0, ',', '.'); ?> ₫</p>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?php echo number_format($total_price, 0, ',', '.'); ?> ₫</span>
                            </li>
                        <?php endwhile; ?>
                    </ul>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="shipping-method mb-3">
                                <label for="shipping_method" class="form-label">Phương thức giao hàng</label>
                                <select class="form-select" id="shipping_method" name="shipping_method">
                                    <option value="standard" selected>Giao hàng tiêu chuẩn (50,000 VNĐ)</option>
                                    <option value="express">Giao hàng nhanh (100,000 VNĐ)</option>
                                </select>
                            </div>
                            <div class="payment-method mb-3">
                                <label for="payment_method" class="form-label">Phương thức thanh toán</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="cash_on_delivery">Thanh toán khi nhận hàng (COD)</option>
                                    <option value="online">Thanh toán trực tuyến</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Voucher Selection -->
                            <div class="voucher-code mb-3">
                                <label for="vouchers" class="form-label">Voucher khuyến mãi</label>
                                <div id="voucher_error_message" style="color: red; display: none;"></div>
                                <?php
                                if ($result_vouchers->num_rows > 0) {
                                    while ($voucher = $result_vouchers->fetch_assoc()) {
                                        $voucher_code = htmlspecialchars($voucher['voucher_code']);
                                        $discount_percentage = htmlspecialchars($voucher['discount_percentage']);
                                        $min_order_value = htmlspecialchars($voucher['min_order_value']);
                                        $max_discount_value = htmlspecialchars($voucher['max_discount_value']);
                                        echo '<div class="form-check">
                            <input class="form-check-input" type="checkbox" id="voucher_' . $voucher_code . '" name="vouchers[]" value="' . $voucher_code . '"
                                   data-discount-percentage="' . $discount_percentage . '"
                                   data-min-order-value="' . $min_order_value . '"
                                   data-max-discount-value="' . $max_discount_value . '">
                            <label class="form-check-label" for="voucher_' . $voucher_code . '">
                                Mã: ' . $voucher_code . ' - Giảm ' . $discount_percentage . '%, tối đa ' . number_format($max_discount_value, 0, ',', '.') . ' VNĐ (Đơn tối thiểu: ' . number_format($min_order_value, 0, ',', '.') . ' VNĐ)
                            </label>
                        </div>';
                                    }
                                } else {
                                    echo '<p>Hiện không có voucher nào khả dụng.</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>



                    <!-- Thông tin chi tiết về tiền hàng, phí vận chuyển, voucher, và tổng tiền -->
                    <div class="payment-details mb-3">
                        <label class="form-label">Chi tiết thanh toán</label>
                        <p>Tiền hàng: <span id="product_amount"><?php echo number_format($total_amount, 0, ',', '.'); ?> ₫</span></p>
                        <p>Phí vận chuyển: <span id="shipping_fee">50,000 VNĐ</span></p>
                        <p>Voucher: <span id="voucher_discount">- 0 ₫</span></p>
                        <p><strong>Tổng thanh toán: <span id="total_amount"><?php echo number_format($total_amount + 50000, 0, ',', '.'); ?> ₫</span></strong></p>
                    </div>

                    <!-- Button thanh toán -->
                    <button type="submit" class="btn btn-primary">Xác nhận đặt hàng</button>
                </form>
            <?php endif; ?>
        </div>
    </section>


    <?php include_once 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleOtherAddresses() {
            var otherAddressesDiv = document.getElementById('other-addresses');
            if (otherAddressesDiv.style.display === 'none') {
                otherAddressesDiv.style.display = 'block';
            } else {
                otherAddressesDiv.style.display = 'none';
            }
        }

        function selectAddress(name, phone, address, addressId) {
            // Cập nhật thông tin địa chỉ trên giao diện
            document.querySelector('.card-title').innerText = 'Địa chỉ nhận hàng';
            const addressSection = document.querySelector('.card-text');
            addressSection.innerHTML = `
            <strong>Họ và tên:</strong> ${name}<br>
            <strong>Số điện thoại:</strong> ${phone}<br>
            <strong>Địa chỉ:</strong> ${address}
        `;

            // Cập nhật ID địa chỉ đã chọn vào trường ẩn
            document.getElementById('selected_address_id').value = addressId;

            // Ẩn phần địa chỉ khác
            document.getElementById('other-addresses').style.display = 'none';
        }
    </script>

    <script>
        // Giá trị phí giao hàng
        const shippingFees = {
            standard: 50000,
            express: 100000
        };

        // Lấy phần tử hiển thị thông tin
        const totalAmountElement = document.getElementById('total_amount');
        const shippingFeeElement = document.getElementById('shipping_fee');
        const voucherDiscountElement = document.getElementById('voucher_discount');
        const productAmountElement = document.getElementById('product_amount');
        const errorMessageElement = document.getElementById('voucher_error_message');

        let totalAmount = <?php echo $total_amount; ?>; // Tiền hàng (chưa bao gồm phí vận chuyển)
        let shippingFee = shippingFees.standard; // Phí vận chuyển mặc định là 50,000 VNĐ
        let discountAmount = 0; // Giá trị giảm giá từ voucher

        // Hiển thị tiền hàng mặc định khi tải trang
        productAmountElement.textContent = new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(totalAmount);

        // Hiển thị phí vận chuyển mặc định khi tải trang
        shippingFeeElement.textContent = new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(shippingFee);

        // Hàm cập nhật tổng tiền bao gồm phí giao hàng và giảm giá từ voucher
        function updateTotalAmount() {
            // Tổng tiền = Tiền hàng - Giảm giá + Phí vận chuyển
            const finalTotal = totalAmount - discountAmount + shippingFee;

            // Cập nhật hiển thị tổng tiền
            totalAmountElement.textContent = new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(finalTotal);

            // Cập nhật hiển thị phí vận chuyển
            shippingFeeElement.textContent = new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(shippingFee);

            // Cập nhật hiển thị giá trị giảm giá từ voucher
            voucherDiscountElement.textContent = '- ' + new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(discountAmount);
        }

        // Cập nhật tổng tiền khi thay đổi phương thức giao hàng
        document.getElementById('shipping_method').addEventListener('change', function() {
            const selectedMethod = this.value;
            shippingFee = shippingFees[selectedMethod];

            // Cập nhật tổng tiền
            updateTotalAmount();
        });

        // Xử lý thay đổi voucher và cập nhật tổng tiền
        const voucherCheckboxes = document.querySelectorAll('input[name="vouchers[]"]');
        const voucherErrorMessage = document.getElementById('voucher_error_message');

        voucherCheckboxes.forEach(function(voucherCheckbox) {
            voucherCheckbox.addEventListener('change', function() {
                discountAmount = 0; // Đặt lại giá trị giảm giá
                let hasValidVoucher = false;
                let allInvalid = true;

                // Xóa thông báo lỗi
                voucherErrorMessage.style.display = 'none';
                voucherErrorMessage.textContent = '';

                // Tính toán tổng giá trị giảm giá từ voucher
                voucherCheckboxes.forEach(function(voucher) {
                    if (voucher.checked) {
                        const discountPercentage = parseFloat(voucher.getAttribute('data-discount-percentage'));
                        const minOrderValue = parseFloat(voucher.getAttribute('data-min-order-value'));
                        const maxDiscount = parseFloat(voucher.getAttribute('data-max-discount-value'));

                        // Kiểm tra điều kiện đơn hàng
                        if (totalAmount >= minOrderValue) {
                            const discountValue = Math.min((totalAmount * discountPercentage / 100), maxDiscount);
                            discountAmount += discountValue;
                            hasValidVoucher = true;
                        } else {
                            // Nếu không đủ điều kiện, đánh dấu tất cả voucher không hợp lệ
                            allInvalid = allInvalid && !voucher.checked; // Nếu tất cả các voucher không được chọn
                        }
                    }
                });

                // Hiển thị thông báo lỗi nếu không có voucher hợp lệ nào được chọn
                if (!hasValidVoucher && !allInvalid) {
                    voucherErrorMessage.textContent = 'Đơn hàng chưa đủ điều kiện áp dụng voucher này.';
                    voucherErrorMessage.style.display = 'block';
                }

                // Cập nhật tổng tiền sau khi áp dụng voucher và phí giao hàng
                updateTotalAmount();
            });
        });
    </script>
    



</body>

</html>