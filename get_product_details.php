<?php
include_once 'dbconnect.php';

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);

    // Truy vấn lấy thông tin sản phẩm cùng với thông tin từ các bảng khác
    $sql = "
        SELECT 
            p.product_id, 
            p.product_name, 
            p.price, 
            p.background_image, 
            p.configuration, 
            p.product_info, 
            p.sales_count, 
            c.category_name, 
            b.brand_name, 
            d.discount_percentage, 
            pp.promotion_description 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN discounts d ON p.product_id = d.product_id
        LEFT JOIN product_promotions pp ON p.product_id = pp.product_id
        WHERE p.product_id = $productId
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        // Tính giá sau khi giảm giá
        $discountedPrice = $product['price'] * (1 - ($product['discount_percentage'] / 100));
        $product['discounted_price'] = $discountedPrice; // Thêm giá sau giảm giá vào mảng
        echo json_encode($product);
    } else {
        echo json_encode([]);
    }
}
?>
