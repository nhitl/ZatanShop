<?php
include_once 'dbconnect.php';

if (isset($_GET['subcategories_id']) || isset($first_sub_id)) {
    $subcategory_id = isset($_GET['subcategories_id']) ? $_GET['subcategories_id'] : $first_sub_id;

    // Tránh lỗi SQL Injection
    $subcategory_id = mysqli_real_escape_string($conn, $subcategory_id);

    // Truy vấn lấy 4 sản phẩm mới nhất theo subcategory_id
    $query = "
        SELECT p.*, d.discount_percentage 
        FROM products p 
        LEFT JOIN discounts d ON p.product_id = d.product_id 
        WHERE p.subcategory_id = '$subcategory_id' 
        ORDER BY p.product_id DESC 
        LIMIT 4";

    $products = mysqli_query($conn, $query);

    // Kiểm tra lỗi SQL
    if (!$products) {
        die("Lỗi truy vấn SQL: " . mysqli_error($conn));
    }

    // Nếu không có sản phẩm, hiển thị thông báo
    if (mysqli_num_rows($products) == 0) {
        echo "<p style='text-align: center; font-size: 1.2rem; color: #ce0000;'>Đang trong quá trình cập nhật sản phẩm.</p>";
    } else {
        echo "<section class='box-cate'>";
        echo "<div class='product-row'>";
        while ($product = mysqli_fetch_assoc($products)) {
            $price = number_format($product['price'], 0, ',', '.') . "đ";

            // Tính giá giảm nếu có
            if ($product['discount_percentage']) {
                $discount_price = $product['price'] * (1 - $product['discount_percentage'] / 100);
                $discount_price = number_format($discount_price, 0, ',', '.') . "đ";
            } else {
                $discount_price = null;
            }

            // Kiểm tra số lượng tồn kho
            $stock_status = ($product['stock_quantity'] > 0) ? "<span class='in-stock'>Còn hàng</span>" : "<span class='out-of-stock'>Hết hàng</span>";
            $sales_count = $product['sales_count'];

            // Lấy slug của sản phẩm để tạo link
            $slug = $product['slug']; // Giả sử bạn có trường slug trong bảng products

            echo "
            <div class='product-card'>
                <a href='$slug'>
                    <img src='admin/admin/{$product['background_image']}' alt='{$product['product_name']}' class='product-img'>
                </a>
                <div class='card-body'>
                    <a href='$slug'>
                        <div class='product-name'>{$product['product_name']}</div>
                    </a>
                    <p>Đã bán: <b>$sales_count</b></p>";

                if ($discount_price) {
                    echo "<p class='old-price'><s>$price</s></p>";
                    echo "<p class='discount-price'>$discount_price</p>";
                } else {
                    echo "<p class='price'>$price</p>";
                }

                // Thêm phần footer với stock status và nút thêm vào giỏ hàng
                echo "
                    <div class='product-footer'>
                        <span class='stock-status'>$stock_status</span>
                        <button class='add-to-cart' onclick='addToCart({$product['product_id']})'>
                            <i class='fa-sharp fa-solid fa-cart-plus'></i>
                        </button>
                    </div>
                </div>
            </div>";

        }
        echo "</div>";
        echo "</section>";
        echo "<div class='view-all-container'>";
            echo "<a href='all-products.php' class='view-all-button'>Xem tất cả</a>";
            echo "</div>";
    }
}
?>
