<?php
include 'dbconnect.php'; // Kết nối CSDL

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $voucher_code = $_POST['voucher_code'];
    $total_amount = $_POST['total_amount'];

    // Kiểm tra voucher có hợp lệ không
    $sql = "SELECT discount_percentage, min_order_value, max_discount_value 
            FROM voucher 
            WHERE voucher_code = ? AND status = 'active' AND expiry_date >= CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $voucher_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $voucher = $result->fetch_assoc();

        // Kiểm tra tổng tiền có đủ điều kiện để áp dụng voucher không
        if ($total_amount >= $voucher['min_order_value']) {
            // Tính giảm giá
            $discount_amount = $total_amount * ($voucher['discount_percentage'] / 100);
            if ($discount_amount > $voucher['max_discount_value']) {
                $discount_amount = $voucher['max_discount_value'];
            }

            // Tính tổng tiền sau giảm giá
            $final_total = $total_amount - $discount_amount;

            // Trả về kết quả cho AJAX
            echo json_encode([
                'voucher_discount' => number_format($discount_amount, 0, ',', '.'),
                'final_total' => number_format($final_total, 0, ',', '.')
            ]);
        } else {
            echo json_encode([
                'error' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng voucher.'
            ]);
        }
    } else {
        echo json_encode(['error' => 'Voucher không hợp lệ.']);
    }
}
?>
