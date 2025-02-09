<?php
// Kết nối tới database
include_once '../dbconnect.php';

// Truy vấn để lấy thông tin đơn hàng và sắp xếp theo thời gian tạo mới nhất trước
$sql = "SELECT orders.*, users.full_name, shipping_addresses.recipient_phone, shipping_addresses.address 
        FROM orders
        JOIN users ON orders.user_id = users.user_id
        JOIN shipping_addresses ON orders.address_id = shipping_addresses.address_id
        ORDER BY orders.created_at DESC";


$result = $conn->query($sql);

// Mảng ánh xạ trạng thái đơn hàng từ tiếng Anh sang tiếng Việt
$status_mapping = [
    'pending' => 'Đang chờ',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang vận chuyển',
    'delivered' => 'Đã giao',
    'canceled' => 'Đã hủy'
];

// Mảng ánh xạ trạng thái thanh toán
$payment_status_mapping = [
    'pending' => 'Chờ thanh toán',
    'paid' => 'Đã thanh toán',
    'failed' => 'Thanh toán thất bại',
    'Pending Refund' => 'Chờ hoàn tiền',
    'Refund Successful' => 'Hoàn tiền thành công',
    'Refund Failed' => 'Hoàn tiền thất bại'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/orders.css">
</head>

<body>
    <div class="container">
        <?php
        // Include header
        include_once '../header.php';
        include_once '../notification.php';
        ?>
        <h2 class="text-center mb-4">Quản lý đơn hàng </h2>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()) {
                $status = strtolower($row['order_status']);
                $status_text = isset($status_mapping[$status]) ? $status_mapping[$status] : $status;
                $payment_status = $row['payment_status'];  
                $payment_status_text = isset($payment_status_mapping[$payment_status]) ? $payment_status_mapping[$payment_status] : $payment_status;
            ?>
                <div class="col-md-4 mb-4">
                    <div class="order-card">
                        <div class="order-details">
                            <h5 class="order-id text-center">#<?php echo $row['order_id']; ?></h5>
                            <div class="order-info">
                                <div class="order-info-item">
                                    <h6 class="order-info-title">Tên khách hàng:</h6>
                                    <p class="order-info-content"><?php echo $row['full_name']; ?></p>
                                </div>
                                <div class="order-info-item">
                                    <h6 class="order-info-title">Số điện thoại:</h6>
                                    <p class="order-info-content"><?php echo $row['recipient_phone']; ?></p>
                                </div>

                                <div class="order-info-item">
                                    <h6 class="order-info-title">Địa chỉ:</h6>
                                    <div class="order-address">
                                        <p class="order-info-content"><?php echo $row['address']; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="order-meta">
                                <div class="order-meta-item">
                                    <h6 class="order-info-title">Tổng tiền:</h6>
                                    <p class="order-info-content"><?php echo number_format($row['grand_total'], 2) . " VND"; ?></p>
                                </div>
                                <div class="order-meta-item">
                                    <h6 class="order-info-title">Ngày tạo:</h6>
                                    <p class="order-info-content"><?php echo date("d-m-Y H:i:s", strtotime($row['created_at'])); ?></p>
                                </div>
                                <span class="order-status <?php echo $status; ?>">
                                    Trạng thái đơn hàng: <?php echo $status_text; ?>
                                </span>
                                <div class="order-payment-status">
                                    <h6 class="order-info-title">Trạng thái thanh toán:</h6>
                                    <p class="order-info-content <?php echo $payment_status; ?>">
                                        <?php echo $payment_status_text; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <a href="order_details.php?order_id=<?php echo $row['order_id']; ?>" class="details-button">Chi tiết</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
    include_once '../footer.php';
    ?>
    <!-- Link Bootstrap 5 JS và Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>