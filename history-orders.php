<?php
session_start(); // Khởi động phiên
include_once 'dbconnect.php';

// Mảng ánh xạ trạng thái đơn hàng từ tiếng Anh sang tiếng Việt
$status_mapping = [
    'pending' => 'Đang chờ',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang vận chuyển',
    'delivered' => 'Giao hàng thành công',
    'canceled' => 'Đã hủy'
];

$payment_status_mapping = [
    'pending' => 'Chờ thanh toán',
    'paid' => 'Đã thanh toán',
    'failed' => 'Thanh toán thất bại',
    'Pending Refund' => 'Chờ hoàn tiền',
    'Refund Successful' => 'Hoàn tiền thành công',
    'Refund Failed' => 'Hoàn tiền thất bại'
];


// Mảng ánh xạ phương thức thanh toán từ tiếng Anh sang tiếng Việt
$payment_method_mapping = [
    'online' => 'Thanh toán online',
    'cash_on_delivery' => 'Thanh toán khi nhận hàng'
];

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo "Bạn cần đăng nhập để xem thông tin.";
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];


    // Lấy chi tiết đơn hàng
    $sql = "
SELECT order_items.*, products.product_name, products.background_image, orders.voucher_code, orders.total_amount, orders.shipping_fee, orders.grand_total
FROM order_items
JOIN products ON order_items.product_id = products.product_id
JOIN orders ON order_items.order_id = orders.order_id
WHERE order_items.order_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();



    if ($result->num_rows > 0) {
        $order_details = [];
        while ($row = $result->fetch_assoc()) {
            $order_details[] = $row;
        }

        // Hiển thị chi tiết đơn hàng
        if (!empty($order_details)) {
            echo "<div class='container'>";

            // Lấy giá trị từ order_details
            $total_amount = $order_details[0]['total_amount'];
            $shipping_fee = $order_details[0]['shipping_fee'];
            $grand_total = $order_details[0]['grand_total']; // Lấy grand_total

            // Tính tổng giảm giá
            $total_discount = ($total_amount + $shipping_fee) - $grand_total;
            foreach ($order_details as $detail) {
                echo "
                <div class='row list-p-orders align-items-center m-2'>
                    <div class='col-5 col-md-6 col-lg-2 m-1'>
                        <img src='admin{$detail['background_image']}' alt='{$detail['product_name']}' class='img-fluid' style='max-width: 100px;'>
                    </div>
                    <div class='col-6 col-md-6 col-lg-6 m-1 list-p-text'>
                        <h5 class='mb-0'>{$detail['product_name']}</h5>
                    </div>
                    <div class='col-12 col-md-6 col-lg-3 m-1'>
                        <p class='mb-1'>Giá: " . number_format($detail['price'], 0) . " VND</p>
                        <p class='mb-0'>Số lượng: {$detail['quantity']}</p>
                    </div>
                </div>";
            }
            // Hiển thị thông tin tổng tiền hàng, phí vận chuyển và tổng thanh toán
            echo "<div class='order-summary-container'>"; // Bọc tất cả trong một div

            echo "<p class='order-summary total-amount'>Tổng tiền hàng: " . number_format($total_amount, 0) . "₫</p>";
            echo "<p class='order-summary shipping-fee'>Phí vận chuyển: " . number_format($shipping_fee, 0) . "₫</p>";

            // Hiển thị voucher đã sử dụng
            if (!empty($order_details[0]['voucher_code'])) {
                $voucher_codes = $order_details[0]['voucher_code']; // Lấy voucher_code
                echo "<p class='voucher-code'>Voucher đã sử dụng: <strong>{$voucher_codes}</strong> (Tổng Giảm: " . number_format($total_discount, 0) . "₫)</p>";
            }

            echo "<p class='grand-total'>Tổng thanh toán: " . number_format($grand_total, 0) . "₫</p>"; // Hiển thị grand_total

            echo "</div>"; // Đóng div
            echo "</div>";
        }
    } else {
        echo "Không có chi tiết đơn hàng.";
    }
    exit(); // Dừng thực thi tiếp PHP để trả về phản hồi AJAX
}

// Truy vấn đơn hàng của người dùng
$sql = "
SELECT o.*, v.voucher_code, v.description 
FROM orders o 
LEFT JOIN vouchers v ON o.voucher_code = v.voucher_code 
WHERE o.user_id = ? 
ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử mua hàng</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/history-orders.css">
</head>

<body>
    <?php include 'header.php';
    include_once 'contact_button.php'; ?>
    <section class="history-orders">
        <div class="container">
            <h1 class="text-center mb-4 pt-3">Lịch sử mua hàng</h1>
            <?php if (empty($orders)): ?>
                <div class="alert alert-info">Bạn chưa có đơn hàng nào.</div>
            <?php else: ?>
                <!-- Tabs -->
                <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-orders" type="button" role="tab"><i class="fa-solid fa-diamond"></i> Tất cả</button>
                    </li>
                    <?php foreach ($status_mapping as $key => $status_name): ?>
                        <li class="nav-item">
                            <button class="nav-link" id="<?php echo $key; ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo $key; ?>-orders" type="button" role="tab"><?php echo '<i class="fa-solid fa-diamond"></i> ' . $status_name; ?></button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Tab Contents -->
                <div class="tab-content mt-4" id="orderTabsContent">
                    <div class="tab-pane fade show active" id="all-orders" role="tabpanel">
                        <div class="row g-3">
                            <?php foreach ($orders as $order): ?>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <?php include 'order_card.php'; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php foreach ($status_mapping as $key => $status_name): ?>
                        <div class="tab-pane fade" id="<?php echo $key; ?>-orders" role="tabpanel">
                            <div class="row g-3">
                                <?php foreach ($orders as $order): ?>
                                    <?php if ($order['order_status'] === $key): ?>
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <?php include 'order_card.php'; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>


        <!-- Modal để hiển thị chi tiết đơn hàng -->
        <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="orderDetailModalLabel">Chi tiết đơn hàng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Nội dung chi tiết đơn hàng sẽ được load tại đây -->
                        <div id="orderDetailsContent"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="cancelOrderBtn" style="display: none;">Hủy đơn hàng</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal xác nhận và hiển thị thông báo hủy đơn hàng -->
        <div class="modal fade modalcancel-notify" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelOrderModalLabel">Xác nhận hủy đơn hàng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmationMessage">Bạn có chắc chắn muốn hủy đơn hàng này không?</p>
                        <div id="resultMessage" class="d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="confirmCancelOrderBtn">Hủy đơn hàng</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer.php'; ?>
    </section>
    <script>
        $(document).ready(function() {
            var currentOrderId = null; // Biến để lưu trữ order_id hiện tại

            // Khi nhấn nút "Xem", lưu order_id
            $('.view-order').on('click', function() {
                currentOrderId = $(this).data('order-id'); // Lưu order_id
                $.ajax({
                    url: '',
                    type: 'GET',
                    data: {
                        order_id: currentOrderId
                    },
                    success: function(response) {
                        $('#orderDetailsContent').html(response);
                        $('#cancelOrderBtn').show();
                        $('#orderDetailModal').modal('show');
                    }
                });
            });

            // Xử lý sự kiện khi người dùng nhấn nút hủy đơn hàng
            $('#cancelOrderBtn').on('click', function() {
                // Mở modal xác nhận
                $('#cancelOrderModal').modal('show');
            });

            // Khi nhấn nút "Hủy đơn hàng", sử dụng currentOrderId
            $('#confirmCancelOrderBtn').on('click', function() {
                $.ajax({
                    url: 'cancel_order.php',
                    type: 'POST',
                    data: {
                        order_id: currentOrderId
                    },
                    success: function(response) {
                        var responseParts = response.split('|');
                        var status = responseParts[0];
                        var message = responseParts[1];

                        $('#confirmationMessage').addClass('d-none');
                        $('#resultMessage').removeClass('d-none')
                            .removeClass('alert-success alert-danger')
                            .addClass(status === 'success' ? 'alert alert-success' : 'alert alert-danger')
                            .text(message);

                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                });
            });


        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

</body>

</html>