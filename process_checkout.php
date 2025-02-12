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
                 FROM vouchers 
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
                        FROM vouchers 
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

        $conn->commit();  // Xác nhận giao dịch và lưu đơn hàng

        if ($payment_method === 'online') {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
            date_default_timezone_set('Asia/Ho_Chi_Minh');

            // Kết nối cơ sở dữ liệu
            $conn = new mysqli('localhost', 'root', '123456', 'zatanshop');

            // Kiểm tra kết nối
            if ($conn->connect_error) {
                die("Kết nối thất bại: " . $conn->connect_error);
            }

            // Truy vấn đơn hàng mới nhất
            $stmt = $conn->prepare("SELECT * FROM orders ORDER BY order_id DESC LIMIT 1");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $order = $result->fetch_assoc();
                // Lấy thông tin đơn hàng
                $order_id = $order['order_id'];
                $grand_total = $order['grand_total'];  // Tổng tiền thanh toán
            } else {
                die("Không tìm thấy đơn hàng.");
            }

            // Kiểm tra dữ liệu đơn hàng
            if (!isset($order_id) || !isset($grand_total)) {
                die("Dữ liệu đơn hàng không hợp lệ.");
            }

            // Cập nhật trạng thái thanh toán ban đầu
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'pending' WHERE order_id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            // Các thông số cấu hình VNPAY
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // Địa chỉ thanh toán
            $vnp_Returnurl = "http://localhost:8081/DOAN/vnpay_return.php"; // URL trả về sau khi thanh toán
            $vnp_TmnCode = "A477Z1E7"; // Mã website tại VNPAY
            $vnp_HashSecret = "LWIO1UKE8JMYVPP72VQL74UZEPMI3HHK"; // Chuỗi bí mật từ VNPAY

            $vnp_TxnRef = $order_id;  // Mã đơn hàng
            $vnp_Amount = round($grand_total);  // Làm tròn số tiền sau khi nhân với 100
            $vnp_Locale = 'vn';  // Ngôn ngữ hiển thị (ví dụ: 'vn' cho Tiếng Việt)
            $vnp_BankCode = '';  // Mã ngân hàng (nếu có, nếu không có thì để trống)
            $vnp_IpAddr = '192.168.0.102';  // Địa chỉ IP của người dùng
            $expire = date('YmdHis', strtotime('+1 day')); // Thời gian hết hạn đơn hàng (thêm 1 ngày)


            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount * 100,  // Lưu ý: nhân với 100 để có số tiền đúng
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef, // Thêm thông tin đơn hàng
                "vnp_OrderType" => "other",  // Đảm bảo có tham số này
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
                "vnp_ExpireDate" => $expire
            );


            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }
            


            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }
            header('Location: ' . $vnp_Url);
            die();



            
        } else {
            // Trường hợp thanh toán khi nhận hàng
            // Hiển thị modal thông báo đặt hàng thành công
            echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet' />";
            echo "<script src='https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js'></script>";
            echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>";

            echo "<div class='modal fade' id='orderSuccessModal' tabindex='-1' aria-labelledby='orderSuccessModalLabel' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='orderSuccessModalLabel'>Thông báo</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                </div>
                <div class='modal-body'>
                    <p>Đặt hàng thành công! Cảm ơn bạn đã mua hàng.</p>
                </div>
                <div class='modal-footer'>
                </div>
            </div>
        </div>
    </div>";

            echo "<script>
        $(document).ready(function() {
            $('#orderSuccessModal').modal('show');

            // Chuyển hướng sau 2.5 giây
            setTimeout(function() {
                window.location.href = 'cam-on';
            }, 2500);
        });
    </script>";
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        die('Có lỗi xảy ra: ' . htmlspecialchars($e->getMessage()));
    }
} else {
    die('Yêu cầu không hợp lệ.');
}
