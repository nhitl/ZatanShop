<?php
include_once 'dbconnect.php';

// Lấy danh sách danh mục chính
$categories = mysqli_query($conn, "SELECT * FROM categories");

while ($category = mysqli_fetch_assoc($categories)) {
    echo "<div class='category-container'>";
        $category_id = $category['category_id'];
        $category_name = $category['category_name'];

        // Hiển thị logo trước tiêu đề danh mục
        echo "<div class='logo-container text-center'>
                <img src='admin/assets/img/logoshop1.png' alt='Logo' class='category-logo'>
              </div>";

        echo "<h2 class='cate-title text-center'>$category_name</h2>";

        // Lấy danh mục con, sắp xếp theo subcategory_id tăng dần
        $subcategories = mysqli_query($conn, "SELECT * FROM subcategories WHERE category_id = $category_id ORDER BY subcategory_id ASC");

        // Tìm danh mục con đầu tiên
        $first_subcategory = mysqli_fetch_assoc($subcategories);
        $first_sub_id = $first_subcategory ? $first_subcategory['subcategory_id'] : null;

        // Hiển thị danh sách danh mục con
        echo "<div class='subcategory-tabs'>";
        mysqli_data_seek($subcategories, 0); // Reset lại kết quả query để lặp lại
        while ($sub = mysqli_fetch_assoc($subcategories)) {
            $sub_id = $sub['subcategory_id'];
            $sub_name = $sub['subcategory_name'];
            $activeClass = ($sub_id == $first_sub_id) ? "active" : "";
            echo "<button class='sub-tab $activeClass' onclick='loadProducts($sub_id, $category_id)'>$sub_name</button>";
        }
        echo "</div>";

        // Khu vực hiển thị sản phẩm của danh mục con đầu tiên
        echo "<div class='product-container' id='products_$category_id'>";
        if ($first_sub_id) {
            include 'fetch_products.php';
        }
        echo "</div>";
    echo "</div>";
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Document</title>
    <link rel="stylesheet" href="assets/css/box_cate.css">
</head>

<body>

</body>

<script>
    function loadProducts(subcategoryId, categoryId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "fetch_products.php?subcategories_id=" + subcategoryId, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById("products_" + categoryId).innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    function addToCart(productId) {
        // Gửi yêu cầu Ajax để thêm sản phẩm vào giỏ
        $.ajax({
            url: 'add_to_cart.php', // Tệp PHP xử lý thêm sản phẩm vào giỏ
            type: 'POST',
            data: {
                product_id: productId,
                quantity: 1
            },
            success: function(response) {
                var result = JSON.parse(response);
                // Hiển thị thông báo trong modal
                $('#cartModalMessage').text(result.message);
                $('#cartModal').modal('show'); // Hiện modal
                updateCartCount(); // Cập nhật số lượng giỏ hàng
            },
            error: function() {
                $('#cartModalMessage').text('Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.'); // Thông báo lỗi
                $('#cartModal').modal('show'); // Hiện modal
            }
        });
    }
    // Lắng nghe sự kiện click trên các tab danh mục con
    document.querySelectorAll('.sub-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Loại bỏ lớp active của tất cả các tab
            document.querySelectorAll('.sub-tab').forEach(tab => tab.classList.remove('active'));

            // Thêm lớp active cho tab hiện tại
            this.classList.add('active');

            // Bạn có thể thêm mã để tải sản phẩm của danh mục con tương ứng ở đây nếu cần
        });
    });
</script>


</html>