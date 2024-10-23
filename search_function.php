<?php
include_once 'dbconnect.php';

$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

if ($search_term) {
    $query = "SELECT p.product_id, p.product_name, p.price, p.background_image, b.brand_name 
              FROM products p 
              JOIN brands b ON p.brand_id = b.brand_id 
              WHERE p.product_name LIKE '%$search_term%'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        echo '<div class="row product__filter">';
        while ($product = mysqli_fetch_assoc($result)) {
            // Hiển thị sản phẩm
            echo '<div class="col-lg-4 col-md-6 col-sm-6">';
            echo '<div class="product__item">';
            echo '<div class="product__item__pic set-bg" data-setbg="' . $product['background_image'] . '"></div>';
            echo '<div class="product__item__text">';
            echo '<h6><a href="#">' . htmlspecialchars($product['product_name']) . '</a></h6>';
            echo '<h5>' . htmlspecialchars($product['price']) . ' VND</h5>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="no-results">Không tìm thấy sản phẩm nào.</div>';
    }
} else {
    echo '<div class="no-search-term">Vui lòng nhập từ khóa tìm kiếm.</div>';
}
?>
