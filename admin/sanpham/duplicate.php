<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Kiểm tra xem có product_id được truyền vào không
if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];

    // Sao chép thông tin sản phẩm từ bảng products
    $productQuery = "SELECT * FROM products WHERE product_id = $productId";
    $productResult = mysqli_query($conn, $productQuery);
    if ($productResult && mysqli_num_rows($productResult) > 0) {
        $product = mysqli_fetch_assoc($productResult);

        // Thư mục lưu trữ ảnh
        $targetDir = "../assets/img/imgproducts/";

        // Tạo bản sao cho ảnh chính
        $newBackgroundImage = uniqid("bg_copy_", true) . '.' . pathinfo($product['background_image'], PATHINFO_EXTENSION);
        copy($product['background_image'], $targetDir . $newBackgroundImage);

        // Tạo slug mới
        $originalSlug = $product['slug'];
        $newSlug = $originalSlug . '-' . uniqid(); // Thêm hậu tố duy nhất

        // Kiểm tra slug để đảm bảo duy nhất
        while (true) {
            $checkSlugQuery = "SELECT * FROM products WHERE slug = '$newSlug'";
            $checkSlugResult = mysqli_query($conn, $checkSlugQuery);
            if (mysqli_num_rows($checkSlugResult) == 0) {
                break; // Nếu slug là duy nhất, thoát vòng lặp
            }
            $newSlug = $originalSlug . '-' . uniqid(); // Tạo slug mới nếu bị trùng
        }

        // Tạo sản phẩm mới với slug độc nhất
        $newProductQuery = "INSERT INTO products (product_name, slug, price, background_image, product_info, category_id, subcategory_id, brand_id, stock_quantity) 
                            VALUES ('" . $product['product_name'] . "', '$newSlug', '" . $product['price'] . "', '" . $targetDir . $newBackgroundImage . "', '" . $product['product_info'] . "', '" . $product['category_id'] . "', '" . $product['subcategory_id'] . "', '" . $product['brand_id'] . "', '" . $product['stock_quantity'] . "')";
        
        if (mysqli_query($conn, $newProductQuery)) {
            $newProductId = mysqli_insert_id($conn);

            // Sao chép cấu hình từ bảng product_configurations
            $configQuery = "SELECT * FROM product_configurations WHERE product_id = $productId";
            $configResult = mysqli_query($conn, $configQuery);
            while ($config = mysqli_fetch_assoc($configResult)) {
                $newConfigQuery = "INSERT INTO product_configurations (product_id, config_name, config_value) 
                                   VALUES ($newProductId, '" . $config['config_name'] . "', '" . $config['config_value'] . "')";
                mysqli_query($conn, $newConfigQuery);
            }

            // Sao chép thông tin quà tặng từ bảng product_promotions
            $promoQuery = "SELECT * FROM product_promotions WHERE product_id = $productId";
            $promoResult = mysqli_query($conn, $promoQuery);
            while ($promo = mysqli_fetch_assoc($promoResult)) {
                $newPromoQuery = "INSERT INTO product_promotions (product_id, promotion_description) 
                                  VALUES ($newProductId, '" . mysqli_real_escape_string($conn, $promo['promotion_description']) . "')";
                mysqli_query($conn, $newPromoQuery);
            }

            // Sao chép và tạo bản sao cho ảnh mô tả trong bảng product_images
            $imageQuery = "SELECT * FROM product_images WHERE product_id = $productId";
            $imageResult = mysqli_query($conn, $imageQuery);
            while ($image = mysqli_fetch_assoc($imageResult)) {
                $newImageName = uniqid("desc_copy_", true) . '.' . pathinfo($image['image_url'], PATHINFO_EXTENSION);
                copy($image['image_url'], $targetDir . $newImageName);
                $newImageQuery = "INSERT INTO product_images (product_id, image_url) 
                                  VALUES ($newProductId, '" . $targetDir . $newImageName . "')";
                mysqli_query($conn, $newImageQuery);
            }

            // Sao chép thông tin giảm giá từ bảng discounts
            $discountQuery = "SELECT * FROM discounts WHERE product_id = $productId";
            $discountResult = mysqli_query($conn, $discountQuery);
            while ($discount = mysqli_fetch_assoc($discountResult)) {
                $newDiscountQuery = "INSERT INTO discounts (product_id, discount_percentage, start_date, end_date) 
                                     VALUES ($newProductId, '" . $discount['discount_percentage'] . "', '" . $discount['start_date'] . "', '" . $discount['end_date'] . "')";
                mysqli_query($conn, $newDiscountQuery);
            }

            // Thiết lập thông báo thành công vào session
            $_SESSION['success_message'] = "Sản phẩm đã được sao chép thành công.";

            // Chuyển hướng về trang danh sách sản phẩm sau khi sao chép
            header("Location: index.php");
            exit();
        } else {
            echo "Lỗi khi sao chép sản phẩm: " . mysqli_error($conn);
        }
    } else {
        echo "Sản phẩm không tồn tại.";
    }
} else {
    echo "Không có ID sản phẩm.";
}
?>
