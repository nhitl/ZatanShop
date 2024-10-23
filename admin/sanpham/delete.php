<?php 

include_once '../dbconnect.php';

// Kiểm tra nếu có product_id được truyền từ URL
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $productId = intval($_GET['product_id']);

    // Truy vấn CSDL để lấy thông tin chi tiết sản phẩm
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Xóa ảnh mô tả sản phẩm từ hệ thống file
        $backgroundImage = $row['background_image'];
        if (file_exists($backgroundImage)) {
            unlink($backgroundImage);
        }

        // Xóa các bản ghi liên quan trong bảng order_items
        $deleteOrderItemsQuery = "DELETE FROM order_items WHERE product_id = ?";
        $deleteOrderItemsStmt = mysqli_prepare($conn, $deleteOrderItemsQuery);
        mysqli_stmt_bind_param($deleteOrderItemsStmt, 'i', $productId);
        mysqli_stmt_execute($deleteOrderItemsStmt);

        // Xóa các bản ghi liên quan trong bảng product_configurations
        $deleteConfigurationsQuery = "DELETE FROM product_configurations WHERE product_id = ?";
        $deleteConfigurationsStmt = mysqli_prepare($conn, $deleteConfigurationsQuery);
        mysqli_stmt_bind_param($deleteConfigurationsStmt, 'i', $productId);
        mysqli_stmt_execute($deleteConfigurationsStmt);

        // Xóa ảnh mô tả sản phẩm từ bảng product_images
        $deleteImagesQuery = "DELETE FROM product_images WHERE product_id = ?";
        $deleteImagesStmt = mysqli_prepare($conn, $deleteImagesQuery);
        mysqli_stmt_bind_param($deleteImagesStmt, 'i', $productId);
        mysqli_stmt_execute($deleteImagesStmt);

        // Xóa các bản ghi liên quan trong bảng cart
        $deleteCartQuery = "DELETE FROM cart WHERE product_id = ?";
        $deleteCartStmt = mysqli_prepare($conn, $deleteCartQuery);
        mysqli_stmt_bind_param($deleteCartStmt, 'i', $productId);
        mysqli_stmt_execute($deleteCartStmt);

        // Sau đó, xóa thông tin sản phẩm từ bảng products
        $deleteProductQuery = "DELETE FROM products WHERE product_id = ?";
        $deleteProductStmt = mysqli_prepare($conn, $deleteProductQuery);
        mysqli_stmt_bind_param($deleteProductStmt, 'i', $productId);
        
        if (mysqli_stmt_execute($deleteProductStmt)) {
            // Thiết lập thông báo thành công vào session
            $_SESSION['success_message'] = "Sản phẩm đã được xóa thành công.";
            // Chuyển hướng về trang danh sách sản phẩm sau khi xóa
            header("Location: index.php");
            exit();
        } else {
            echo "Lỗi xóa sản phẩm: " . mysqli_error($conn);
        }
    } else {
        // Hiển thị thông báo nếu không tìm thấy sản phẩm
        echo "Không tìm thấy sản phẩm";
        exit();
    }
} else {
    // Hiển thị thông báo nếu không có product_id
    echo "Không có sản phẩm để xóa";
    exit();
}

// Đóng kết nối CSDL
mysqli_close($conn);
?>
