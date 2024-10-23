<?php
include 'dbconnect.php'; // Kết nối đến cơ sở dữ liệu

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

// Kiểm tra phương thức thanh toán
if ($payment_method == 'momo') {
    // Lấy tổng số tiền cần thanh toán
    $order_sql = "SELECT grand_total FROM orders WHERE order_id = ?";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order_stmt->bind_result($grand_total);
    $order_stmt->fetch();
    $order_stmt->close();

    // Kiểm tra nếu đơn hàng tồn tại
    if (!$grand_total) {
        die("Đơn hàng không tồn tại.");
    }

    // Tạo URL thanh toán MoMo
    generatePaymentUrl($order_id, $grand_total); // Truyền grand_total vào hàm
} else {
    // Xử lý các phương thức thanh toán khác nếu cần
}

function generatePaymentUrl($order_id, $grand_total) {
    global $conn;

    $momoAppId = 'YOUR_APP_ID';
    $momoSecretKey = 'YOUR_SECRET_KEY';
    $partnerCode = 'YOUR_PARTNER_CODE';
    $orderInfo = 'Thanh toán đơn hàng #' . $order_id;
    $amount = $grand_total; // Sử dụng biến grand_total được truyền vào
    $requestId = time();
    $redirectUrl = 'YOUR_REDIRECT_URL'; // URL quay lại sau thanh toán
    $ipnUrl = 'YOUR_IPN_URL'; // URL thông báo

    $requestData = array(
        'partnerCode' => $partnerCode,
        'partnerName' => 'MoMo Partner',
        'storeId' => 'storeId',
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $order_id,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'lang' => 'vi',
    );

    // Tạo chữ ký cho yêu cầu
    $rawHash = json_encode($requestData);
    $signature = hash_hmac('sha256', $rawHash, $momoSecretKey);
    
    $requestData['signature'] = $signature;

    // Gửi yêu cầu đến MoMo
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://test-payment.momo.vn/v2/gateway/api/create');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));

    $response = curl_exec($ch);
    curl_close($ch);
    
    $responseData = json_decode($response, true);
    
    if (isset($responseData['payUrl'])) {
        header('Location: ' . $responseData['payUrl']);
        exit;
    } else {
        echo 'Lỗi: ' . $responseData['message'];
    }
}
?>
