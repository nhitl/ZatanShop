<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Kiểm tra xem có tham số product_id được truyền không
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $productId = intval($_GET['product_id']);

    // Truy vấn CSDL để lấy thông tin chi tiết sản phẩm
    $query = "SELECT p.product_id, p.product_name, p.price, p.background_image, p.product_info, 
                     c.category_name, s.subcategory_name, b.brand_name, p.stock_quantity
              FROM products p
              INNER JOIN categories c ON p.category_id = c.category_id
              INNER JOIN subcategories s ON p.subcategory_id = s.subcategory_id
              INNER JOIN brands b ON p.brand_id = b.brand_id
              WHERE p.product_id = $productId";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Hiển thị thông tin chi tiết sản phẩm
        echo "<p><strong>ID:</strong> {$row['product_id']}</p>";
        echo "<p><strong>Tên sản phẩm:</strong> {$row['product_name']}</p>";
        echo "<p><strong>Danh mục:</strong> {$row['category_name']}</p>";
        echo "<p><strong>Danh mục con:</strong> {$row['subcategory_name']}</p>"; // Thêm danh mục con
        echo "<p><strong>Thương hiệu:</strong> {$row['brand_name']}</p>";
        echo "<p><strong>Giá gốc:</strong> " . number_format($row['price'], 0, ',', '.') . " VNĐ</p>";
        echo "<p><strong>Số lượng trong kho:</strong> {$row['stock_quantity']}</p>"; // Hiển thị số lượng trong kho
        echo "<p><strong>Ảnh chính của sản phẩm:</strong></p>";
        echo "<img src='{$row['background_image']}' alt='Ảnh sản phẩm' width='100'>";

        // Truy vấn để lấy ảnh mô tả sản phẩm
        $imageQuery = "SELECT image_url FROM product_images WHERE product_id = $productId";
        $imageResult = mysqli_query($conn, $imageQuery);

        if ($imageResult && mysqli_num_rows($imageResult) > 0) {
            echo "<p><strong>Ảnh chi tiết:</strong></p>";
            while ($imageRow = mysqli_fetch_assoc($imageResult)) {
                echo "<img src='{$imageRow['image_url']}' alt='Ảnh mô tả' width='100'>";
            }
        }

        // Truy vấn bảng product_configurations để lấy thông số kỹ thuật
        $configQuery = "SELECT config_name, config_value FROM product_configurations WHERE product_id = $productId";
        $configResult = mysqli_query($conn, $configQuery);

        // Hiển thị thông số kỹ thuật dưới dạng bảng với Bootstrap
        if ($configResult && mysqli_num_rows($configResult) > 0) {
            echo "<p><strong>Thông số kỹ thuật:</strong></p>";
            echo "<table class='table table-bordered'>";
            echo "<thead class='table-light'><tr><th>Tên thông số</th><th>Giá trị</th></tr></thead>";
            echo "<tbody>";

            while ($configRow = mysqli_fetch_assoc($configResult)) {
                echo "<tr>";
                echo "<td>{$configRow['config_name']}</td>";
                echo "<td>{$configRow['config_value']}</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";
        } else {
            echo "<p><strong>Thông số kỹ thuật:</strong> Không có dữ liệu</p>";
        }

        echo "<p><strong>Thông tin sản phẩm:</strong> {$row['product_info']}</p>";

        // Truy vấn để lấy thông tin khuyến mãi nếu có
        $promotionQuery = "SELECT promotion_description FROM product_promotions WHERE product_id = $productId";
        $promotionResult = mysqli_query($conn, $promotionQuery);

        if ($promotionResult && mysqli_num_rows($promotionResult) > 0) {
            echo "<p><strong>Quà tặng kèm:</strong></p>";
            echo "<ul>"; // Mở danh sách không có thứ tự
            while ($promotionRow = mysqli_fetch_assoc($promotionResult)) {
                echo "<li>{$promotionRow['promotion_description']}</li>"; // Hiển thị từng quà tặng
            }
            echo "</ul>"; // Đóng danh sách
        } else {
            echo "<p><strong>Quà tặng kèm:</strong> Không có dữ liệu</p>"; // Nếu không có quà tặng
        }

        // Truy vấn để lấy thông tin giảm giá nếu có
        $discountQuery = "SELECT discount_percentage, start_date, end_date FROM discounts WHERE product_id = $productId";
        $discountResult = mysqli_query($conn, $discountQuery);

        if ($discountResult && mysqli_num_rows($discountResult) > 0) {
            $discountRow = mysqli_fetch_assoc($discountResult);
            echo "<p><strong>Thông tin giảm giá:</strong> {$discountRow['discount_percentage']}%</p>";
            echo "<p><strong>Ngày bắt đầu:</strong> {$discountRow['start_date']}</p>";
            echo "<p><strong>Ngày kết thúc:</strong> {$discountRow['end_date']}</p>";
        }

    } else {
        // Hiển thị thông báo nếu không tìm thấy sản phẩm
        echo "Không tìm thấy sản phẩm";
    }

    // Giải phóng bộ nhớ
    mysqli_free_result($result);
    mysqli_free_result($imageResult);
    mysqli_free_result($configResult);
    mysqli_free_result($promotionResult);
    mysqli_free_result($discountResult);
} else {
    // Hiển thị thông báo nếu không có product_id hoặc không hợp lệ
    echo "Không có sản phẩm để hiển thị";
}

// Đóng kết nối CSDL
mysqli_close($conn);
?>
