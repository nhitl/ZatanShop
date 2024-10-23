<?php
include 'dbconnect.php'; // Kết nối cơ sở dữ liệu

session_start(); // Khởi tạo phiên làm việc

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    echo "Bạn cần đăng nhập để xem giỏ hàng.";
    exit();
}

$user_id = $_SESSION['user_id']; // Lấy ID người dùng từ phiên làm việc

// Xử lý yêu cầu xóa sản phẩm
// Trong cart.php
// Xóa sản phẩm khỏi giỏ hàng
if (isset($_POST['remove_id'])) {
    $cart_id = $_POST['remove_id'];

    // Xóa sản phẩm khỏi giỏ hàng
    $sql = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Tính tổng số tiền sau khi xóa sản phẩm
        $total_amount = 0;

        $sql = "SELECT c.cart_id, c.product_id, c.quantity, p.price, 
                    COALESCE(d.discount_percentage, 0) AS discount_percentage
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            LEFT JOIN discounts d ON p.product_id = d.product_id
            WHERE c.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $discount = $row['discount_percentage'];
            $price = $row['price'];
            $discounted_price = $price - ($price * $discount / 100);
            $total_price = $discounted_price * $row['quantity'];
            $total_amount += $total_price;
        }

        echo number_format($total_amount, 0, ',', '.'); // Trả về tổng số tiền với định dạng VNĐ
    }
    $stmt->close();
    $conn->close();
    exit();
}



// Truy vấn giỏ hàng của người dùng cùng với thông tin giảm giá
$sql = "SELECT c.cart_id, c.product_id, c.quantity, p.product_name, p.price, p.background_image, 
               COALESCE(d.discount_percentage, 0) AS discount_percentage
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        LEFT JOIN discounts d ON p.product_id = d.product_id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/cart.css">

<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng</title>
</head>

<body>

    <?php
    include_once 'header.php';
    ?>
    <div class="container">
        <h1>Giỏ hàng của bạn</h1>
        <div class="row" id="cart-items">
            <?php if ($result->num_rows > 0): ?>
                <?php
                $total_amount = 0;
                while ($row = $result->fetch_assoc()):
                    $discount = $row['discount_percentage'];
                    $price = $row['price'];
                    $discounted_price = $price - ($price * $discount / 100);
                    $total_price = $discounted_price * $row['quantity'];
                    $total_amount += $total_price;
                ?>
                    <div class="col-12 cart-item" data-cart-id="<?php echo $row['cart_id']; ?>">
                        <div class="row">
                            <div class="col-10 my-3">
                                <div class="row">
                                    <div class="col-md-2">
                                        <img src="<?php echo 'admin' . $row['background_image']; ?>" alt="<?php echo $row['product_name']; ?>">
                                    </div>
                                    <div class="col-md-10">
                                        <h4><?php echo $row['product_name']; ?></h4>
                                    </div>
                                </div>

                            </div>
                            <div class="col-2">
                                <div class="item-actions">
                                    <a href="#" onclick="removeFromCart(<?php echo $row['cart_id']; ?>); return false;" class="btn btn-danger">Xóa</a>
                                </div>
                            </div>

                        </div>

                        <p class="price">
                            <?php if ($discount > 0): ?>
                                <span class="original-price"><?php echo number_format($price, 0, ',', '.'); ?> VNĐ</span>
                                <span class="discounted-price"><?php echo number_format($discounted_price, 0, ',', '.'); ?> VNĐ</span>
                                <span class="discount">(Giảm <?php echo $discount; ?>%)</span>
                            <?php else: ?>
                                <span class="no-discount-price"><?php echo number_format($price, 0, ',', '.'); ?> VNĐ</span>
                            <?php endif; ?>
                        </p>



                        <p>
                            Số lượng:
                            <button class="btn btn-secondary btn-sm" onclick="updateQuantity(<?php echo $row['cart_id']; ?>, -1)">-</button>
                            <input type="text" value="<?php echo $row['quantity']; ?>" class="quantity-input" id="quantity-<?php echo $row['cart_id']; ?>" readonly>
                            <button class="btn btn-secondary btn-sm" onclick="updateQuantity(<?php echo $row['cart_id']; ?>, 1)">+</button>
                        </p>
                        <p class="total-price" id="total-price-<?php echo $row['cart_id']; ?>">Tổng giá: <?php echo number_format($total_price, 0); ?> VNĐ</p>
                    </div>
                <?php endwhile; ?>
                <div class="col-12">
                    <h3 class="text-right" id="total-amount">Tổng số tiền: <?php echo number_format($total_amount, 0); ?> VNĐ</h3>
                    <a href="checkout.php" class="btn btn-success">Thanh toán</a>
                </div>

            <?php else: ?>
                <div class="col-12">
                    <p>Giỏ hàng của bạn đang trống.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    include_once 'footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        function removeFromCart(cartId) {
            $.ajax({
                url: 'cart.php',
                type: 'POST',
                data: {
                    remove_id: cartId
                },
                success: function(response) {
                    // Xóa phần tử khỏi DOM khi xóa thành công
                    $('.cart-item[data-cart-id="' + cartId + '"]').remove();

                    // Cập nhật tổng số tiền
                    $('#total-amount').text('Tổng số tiền: ' + response + ' VNĐ');

                    // Nếu giỏ hàng trống, hiển thị thông báo
                    if ($('#cart-items').children().length === 0) {
                        $('#cart-items').html('<p>Giỏ hàng của bạn đang trống.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    // Xử lý lỗi
                    console.error('Có lỗi xảy ra: ' + error);
                }
            });
        }
    </script>
    <script>
        function updateQuantity(cartId, change) {
            var quantityInput = document.getElementById('quantity-' + cartId);
            var currentQuantity = parseInt(quantityInput.value);
            var newQuantity = currentQuantity + change;

            if (newQuantity < 1) newQuantity = 1;

            quantityInput.value = newQuantity;

            $.ajax({
                url: 'update_cart.php',
                type: 'POST',
                data: {
                    cart_id: cartId,
                    quantity: newQuantity
                },
                dataType: 'json', // Chỉ định kiểu dữ liệu trả về là JSON
                success: function(response) {
                    // Cập nhật tổng giá trị của mặt hàng cụ thể
                    var priceText = $('.cart-item[data-cart-id="' + cartId + '"] .discounted-price').text() || $('.cart-item[data-cart-id="' + cartId + '"] .original-price').text();
                    var price = parseFloat(priceText.replace(/[^0-9]/g, '')) || 0;
                    var totalPrice = newQuantity * price;
                    $('#total-price-' + cartId).text('Tổng giá: ' + response.items.find(item => item.cart_id == cartId).total_price + ' VNĐ');

                    // Cập nhật tổng số tiền
                    $('#total-amount').text('Tổng số tiền: ' + response.total_amount + ' VNĐ');
                },
                error: function(xhr, status, error) {
                    console.error('Có lỗi xảy ra: ' + error);
                }
            });
        }



        function updateCartTotal() {
            var totalAmount = 0;

            $('.cart-item').each(function() {
                var quantity = parseInt($(this).find('.quantity-input').val());
                var priceText = $(this).find('.discounted-price').text() || $(this).find('.original-price').text();
                var price = parseFloat(priceText.replace(/[^0-9]/g, '')) || 0; // Sử dụng giá gốc nếu không có giá giảm
                var totalPrice = quantity * price;
                $(this).find('.total-price').text('Tổng giá: ' + number_format(totalPrice, 0) + ' VNĐ');
                totalAmount += totalPrice;
            });

            $('#total-amount').text('Tổng số tiền: ' + number_format(totalAmount, 0) + ' VNĐ');
        }

        function number_format(number, decimals) {
            var n = number;
            var c = isNaN(decimals) ? 0 : decimals;
            var d = '.';
            var t = ',';
            var s = '';

            var i = parseInt(n = (+n || 0).toFixed(c)) + '';
            var j = (j = i.length) > 3 ? j % 3 : 0;

            s = (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t);
            s = c ? s + d + Math.abs(n - i).toFixed(c).slice(2) : s;

            return s;
        }


        function updateTotalAmount() {
            $.ajax({
                url: 'cart.php',
                type: 'POST',
                data: {
                    remove_id: 0
                }, // Chỉ là yêu cầu để lấy tổng số tiền hiện tại
                success: function(response) {
                    // Giả sử response là số tiền tổng cộng tính bằng VNĐ
                    $('#total-amount').text('Tổng số tiền: ' + number_format(parseFloat(response), 0) + ' VNĐ');
                },
                error: function(xhr, status, error) {
                    console.error('Có lỗi xảy ra: ' + error);
                }
            });
        }
    </script>


</body>

</html>