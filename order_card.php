<?php
// Kiểm tra nếu không có biến $order, dừng thực thi.
if (!isset($order)) {
    echo "Dữ liệu đơn hàng không hợp lệ.";
    exit();
}

// Mảng ánh xạ (đảm bảo các mảng đã được định nghĩa hoặc truyền vào)
$status_mapping = $status_mapping ?? [];
$payment_method_mapping = $payment_method_mapping ?? [];
?>

<div class="col-12">
    <div class="card position-relative">
        <!-- Icon chuông với Tooltip -->
        <?php if ($order['payment_status'] === 'paid' && $order['order_status'] === 'canceled'): ?>
            <button
                class="btn btn-sm notification-icon position-absolute"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                title="Vui lòng nhắn tin cho quản trị viên để yêu cầu hoàn tiền."
                style="top: 10px; right: 10px;">
                <i class="fa-solid fa-bell"></i>
            </button>

        <?php endif; ?>

        <div class="card-body">
            <h5 class="card-title">Mã đơn hàng: <?php echo htmlspecialchars($order['order_id']); ?></h5>
            <p class="card-text"><strong>Tổng tiền:</strong> <?php echo number_format($order['grand_total'], 0); ?> VND</p>
            <p class="card-text"><strong>Phương thức thanh toán:</strong><br>
                <?php echo $payment_method_mapping[htmlspecialchars($order['payment_method'])] ?? htmlspecialchars($order['payment_method']); ?>
            </p>
            <p class="card-text"><strong>Trạng thái đơn hàng:</strong><br>
                <span class="order-status <?php echo htmlspecialchars($order['order_status']); ?>">
                    <?php echo $status_mapping[htmlspecialchars($order['order_status'])] ?? htmlspecialchars($order['order_status']); ?>
                </span>
            </p>
            <p class="card-text"><strong>Trạng thái thanh toán:</strong><br>
                <span class="payment-status <?php echo htmlspecialchars($order['payment_status']); ?>">
                    <?php echo $payment_status_mapping[htmlspecialchars($order['payment_status'])] ?? htmlspecialchars($order['payment_status']); ?>
                </span>
            </p>
            <p class="card-text"><strong>Ngày đặt hàng:</strong><br> <?php echo htmlspecialchars($order['created_at']); ?></p>

            <a href="#" class="btn btn-info btn-sm view-order" data-order-id="<?php echo $order['order_id']; ?>">Xem chi tiết</a>
        </div>
    </div>
</div>