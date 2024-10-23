<?php
include 'dbconnect.php'; // Kết nối cơ sở dữ liệu

session_start(); // Khởi tạo phiên làm việc

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    echo "Bạn cần đăng nhập để cập nhật giỏ hàng.";
    exit();
}

$user_id = $_SESSION['user_id']; // Lấy ID người dùng từ phiên làm việc

if (isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    $sql = "UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    $stmt->execute();

    // Tính tổng số tiền
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

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $discount = $row['discount_percentage'];
        $price = $row['price'];
        $discounted_price = $price - ($price * $discount / 100);
        $total_price = $discounted_price * $row['quantity'];
        $total_amount += $total_price;

        // Lưu thông tin từng sản phẩm
        $items[] = [
            'cart_id' => $row['cart_id'],
            'total_price' => number_format($total_price, 0, ',', '.')
        ];
    }

    $response = [
        'total_amount' => number_format($total_amount, 0, ',', '.'),
        'items' => $items
    ];

    echo json_encode($response); // Trả về tổng số tiền và thông tin các mặt hàng
    $stmt->close();
    $conn->close();
}
?>
