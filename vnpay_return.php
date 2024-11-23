<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>VNPAY RESPONSE</title>
    <!-- Bootstrap 5 core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
</head>

<body>
    <?php include_once 'header.php'; ?>

    <?php
    require_once("./vnpay_config.php");
    $vnp_SecureHash = $_GET['vnp_SecureHash'];
    $inputData = array();
    foreach ($_GET as $key => $value) {
        if (substr($key, 0, 4) == "vnp_") {
            $inputData[$key] = $value;
        }
    }

    unset($inputData['vnp_SecureHash']);
    ksort($inputData);
    $i = 0;
    $hashData = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

    // Kết nối cơ sở dữ liệu
    $conn = new mysqli('localhost', 'root', '123456', 'zatanshop');
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    // Lấy các tham số từ URL trả về
    $vnp_TxnRef = $_GET['vnp_TxnRef'];  // Mã đơn hàng
    $vnp_Amount = $_GET['vnp_Amount'];  // Số tiền thanh toán (VND * 100)
    $vnp_ResponseCode = $_GET['vnp_ResponseCode'];  // Mã phản hồi
    $vnp_TransactionNo = $_GET['vnp_TransactionNo'];  // Mã giao dịch tại VNPAY
    $vnp_BankCode = $_GET['vnp_BankCode'];  // Mã ngân hàng
    $vnp_PayDate = $_GET['vnp_PayDate'];  // Thời gian thanh toán

    if ($secureHash == $vnp_SecureHash) {
        if ($vnp_ResponseCode == '00') {
            // Thanh toán thành công
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', updated_at = CURRENT_TIMESTAMP WHERE order_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $vnp_TxnRef);
                if ($stmt->execute()) {
                    echo "
                    <div class='container response-container'>
                        <div class='response-header'>
                            <h3 class='text-muted'>ZATAN SHOP THÔNG BÁO</h3>
                        </div>
                        <div>
                            <div class='mb-3'>
                                <label class='response-label'>Mã đơn hàng:</label>
                                <span>{$vnp_TxnRef}</span>
                            </div>
                            <div class='mb-3'>
                                <label class='response-label'>Số tiền:</label>
                                <span>" . ($vnp_Amount / 100) . " VND</span>
                            </div>
                            <div class='mb-3'>
                                <label class='response-label'>Kết quả:</label>
                                <span class='response-status success'>GD Thanh công</span>
                            </div>
                        </div>
                    </div>";
                } else {
                    echo "
                    <div class='container response-container'>
                        <div class='response-header'>
                            <h3 class='text-muted'>VNPAY RESPONSE</h3>
                        </div>
                        <div>
                            <div class='mb-3'>
                                <label class='response-label'>Kết quả:</label>
                                <span class='response-status error'>Lỗi khi cập nhật dữ liệu: " . $stmt->error . "</span>
                            </div>
                        </div>
                    </div>";
                }
            }
        } else {
            // Thanh toán không thành công
            echo "
            <div class='container response-container'>
                <div class='response-header'>
                    <h3 class='text-muted'>VNPAY RESPONSE</h3>
                </div>
                <div>
                    <div class='mb-3'>
                        <label class='response-label'>Mã đơn hàng:</label>
                        <span>{$vnp_TxnRef}</span>
                    </div>
                    <div class='mb-3'>
                        <label class='response-label'>Số tiền:</label>
                        <span>" . ($vnp_Amount / 100) . " VND</span>
                    </div>
                    <div class='mb-3'>
                        <label class='response-label'>Kết quả:</label>
                        <span class='response-status error'>GD Không thành công</span>
                    </div>
                </div>
            </div>";
            // Cập nhật trạng thái đơn hàng là thất bại
            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'failed' WHERE order_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $vnp_TxnRef);
                $stmt->execute();
            }
        }
    } else {
        // Chuỗi kiểm tra không hợp lệ
        echo "
        <div class='container response-container'>
            <div class='response-header'>
                <h3 class='text-muted'>VNPAY RESPONSE</h3>
            </div>
            <div>
                <div class='mb-3'>
                    <label class='response-label'>Kết quả:</label>
                    <span class='response-status error'>Chu kỳ không hợp lệ</span>
                </div>
            </div>
        </div>";
    }

    ?>
    <?php include_once 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
