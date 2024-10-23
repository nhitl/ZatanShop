<?php
// Include file kết nối CSDL
include_once 'dbconnect.php';

// Lấy user_id từ session hoặc từ request
$user_id = 28; // Hoặc lấy từ session: $_SESSION['user_id']

// Xử lý yêu cầu xóa sản phẩm khỏi giỏ hàng
if (isset($_POST['delete_cart'])) {
    $product_id = $_POST['product_id'];

    // Xóa sản phẩm khỏi giỏ hàng
    $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    if ($delete_stmt === false) {
        die('Prepare failed: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($delete_stmt, "ii", $user_id, $product_id);
    $success = mysqli_stmt_execute($delete_stmt);
    if (!$success) {
        die('Execute failed: ' . mysqli_stmt_error($delete_stmt));
    }
    mysqli_stmt_close($delete_stmt);

    // Trả về thông báo thành công
    echo json_encode(['success' => true]);
    exit();
}

// Nếu không phải là yêu cầu xóa, chuyển hướng về giỏ hàng
header("Location: cart.php");
exit();
?>
