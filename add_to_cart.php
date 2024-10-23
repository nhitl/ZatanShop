<?php
// add_to_cart.php
include 'dbconnect.php'; // Kết nối cơ sở dữ liệu

session_start(); // Khởi tạo phiên làm việc

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.']);
    exit();
}

$user_id = $_SESSION['user_id']; // Lấy ID người dùng từ phiên làm việc

// Lấy dữ liệu từ yêu cầu AJAX
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Kiểm tra tồn kho sản phẩm
$sql_stock = "SELECT stock_quantity FROM products WHERE product_id = ?";
$stmt_stock = $conn->prepare($sql_stock);
$stmt_stock->bind_param("i", $product_id);
$stmt_stock->execute();
$result_stock = $stmt_stock->get_result();

if ($result_stock->num_rows > 0) {
    $product = $result_stock->fetch_assoc();
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['status' => 'error', 'message' => 'Sản phẩm này đã hết hàng!']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại.']);
    exit();
}

// Kiểm tra nếu sản phẩm đã có trong giỏ hàng
$sql_check = "SELECT cart_id FROM cart WHERE user_id = ? AND product_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $product_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Cập nhật số lượng sản phẩm nếu đã có trong giỏ hàng
    $sql_update = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("iii", $quantity, $user_id, $product_id);
    $stmt_update->execute();
    echo json_encode(['status' => 'success', 'message' => 'Sản phẩm đã được cập nhật trong giỏ hàng.']);
} else {
    // Thêm sản phẩm mới vào giỏ hàng
    $sql_insert = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iii", $user_id, $product_id, $quantity);
    $stmt_insert->execute();
    echo json_encode(['status' => 'success', 'message' => 'Thêm sản phẩm vào giỏ hàng thành công.']);
}
?>
