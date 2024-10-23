<?php
include 'dbconnect.php'; // Kết nối cơ sở dữ liệu

session_start(); // Khởi tạo phiên làm việc

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    echo "Bạn cần đăng nhập để xem giỏ hàng.";
    exit();
}

$user_id = $_SESSION['user_id']; // Lấy ID người dùng từ phiên làm việc

// Xóa sản phẩm khỏi giỏ hàng
if (isset($_POST['remove_id'])) {
    $cart_id = $_POST['remove_id'];

    // Xóa sản phẩm khỏi giỏ hàng
    $sql = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();

    // Kiểm tra nếu xóa thành công
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

        // Truy vấn để lấy tổng số lượng sản phẩm còn lại
        $sql = "SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $total_items = $row['total_items'] ?? 0; // Số lượng sản phẩm còn lại

        // Trả về tổng số tiền và số lượng sản phẩm còn lại
        echo json_encode([
            'total_amount' => number_format($total_amount, 0, ',', '.'),
            'total_items' => $total_items
        ]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

if (isset($_POST['checkout'])) {
    // Lấy thông tin sản phẩm trong giỏ hàng
    $sql = "SELECT c.product_id, c.quantity, p.stock_quantity, p.background_image 
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $exceeded = false;
    $message = "";

    // Kiểm tra từng sản phẩm trong giỏ hàng
    while ($row = $result->fetch_assoc()) {
        // Lấy ID và tên sản phẩm
        $product_id = $row['product_id'];
        $product_name_query = "SELECT product_name FROM products WHERE product_id = ?";
        $product_stmt = $conn->prepare($product_name_query);
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $product_stmt->bind_result($product_name);
        $product_stmt->fetch();
        $product_stmt->close();

        // Lấy đường dẫn ảnh sản phẩm
        $product_image = 'admin' . htmlspecialchars($row['background_image']); // Đảm bảo đường dẫn đúng

        if ($row['quantity'] > $row['stock_quantity']) {
            $exceeded = true;

            // Định dạng thông báo
            $message .= "
            <div class='d-flex align-items-center mb-2'>
                <img src='$product_image' alt='" . htmlspecialchars($product_name) . "' class='img-thumbnail me-2' style='width: 60px; height: 60px;'>
                <div>
                    Sản phẩm <strong>" . htmlspecialchars($product_name) . "</strong> vượt quá số lượng trong kho, vui lòng chọn lại. </br>
                    Số lượng đặt mua: <strong>" . htmlspecialchars($row['quantity']) . "</strong>, 
                    Số lượng còn lại: <strong>" . htmlspecialchars($row['stock_quantity']) . "</strong>
                </div>
            </div>";
        }
    }

    // Nếu số lượng vượt quá, thông báo cho người dùng
    if ($exceeded) {
        echo json_encode(['success' => false, 'message' => $message]);
    } else {
        // Nếu không có lỗi, trả về thành công
        echo json_encode(['success' => true]);
        exit(); // Dừng script sau khi trả về
    }

    $stmt->close();
    $conn->close();
    exit();
}




// Truy vấn giỏ hàng của người dùng cùng với thông tin giảm giá
$sql = "SELECT c.cart_id, c.product_id, c.quantity, p.product_name, p.price, 
               p.stock_quantity, p.background_image, 
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/cart.css">
    <title>Document</title>
    <style>
        .gio-hang .container{
            width: 80%;
            min-height:400px;
        }
    </style>
</head>

<body>

    <?php
    include_once 'header.php';
    include_once 'contact_button.php';
    ?>
    <section class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Thông tin giỏ hàng</li>
                </ol>
            </nav>
        </div>
    </section>
    <section class="gio-hang">
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
                        $stock_quantity = $row['stock_quantity'];
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

                            <!-- Hiển thị số lượng còn lại trong kho -->
                            <p class="stock-info text-end" style="font-weight: bold;">
                                Số lượng còn lại trong kho: <span><?php echo $row['stock_quantity']; ?></span>
                            </p>
                        </div>

                    <?php endwhile; ?>
                    <div class="col-12">
                        <h3 class="text-right" id="total-amount">Tổng số tiền: <?php echo number_format($total_amount, 0); ?> VNĐ</h3>
                        <a href="#" id="checkout-button" class="btn btn-success">Thanh toán</a>
                    </div>

                <?php else: ?>
                    <div class="col-12">
                        <p>Giỏ hàng của bạn đang trống.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notificationModalLabel">Thông báo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-message">
                        <!-- Nội dung thông báo sẽ được thêm vào đây -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php
    include_once 'footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function removeFromCart(cartId) {
            $.ajax({
                url: 'cart.php',
                type: 'POST',
                data: {
                    remove_id: cartId
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    // Xóa phần tử khỏi DOM khi xóa thành công
                    $('.cart-item[data-cart-id="' + cartId + '"]').remove();

                    // Cập nhật tổng số tiền
                    $('#total-amount').text('Tổng số tiền: ' + result.total_amount + ' VNĐ');

                    // Cập nhật số lượng sản phẩm trong giỏ hàng
                    $('#cart-icon .count').text(result.total_items);

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
    <script>
        $(document).ready(function() {
            $('#checkout-button').click(function(e) {
                e.preventDefault(); // Ngăn chặn hành động mặc định của liên kết
                $.post('cart.php', {
                    checkout: true
                }, function(response) {
                    // Xử lý phản hồi từ máy chủ ở đây
                    if (response.success) {
                        window.location.href = 'checkout.php'; // Chuyển hướng nếu thành công
                    } else {
                        // Hiển thị thông báo trong modal
                        $('#modal-message').html(response.message); // Đặt nội dung thông báo
                        var modal = new bootstrap.Modal(document.getElementById('notificationModal'));
                        modal.show(); // Hiển thị modal
                    }
                }, 'json');
            });
        });
    </script>


</body>

</html>