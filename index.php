<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Zatan Shop - Cung cấp các sản phẩm công nghệ như mainboard, CPU, VGA, RAM, và nhiều sản phẩm khác với giá ưu đãi và dịch vụ chất lượng." />
    <meta name="keywords" content="Zatan Shop, mainboard, CPU, VGA, RAM, sản phẩm công nghệ, mua sắm trực tuyến" />
    <link rel="icon" href="admin/assets/img/favicon.ico.png" type="image/png">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <title>Mua Sắm Công Nghệ Tại Zatan Shop - Mainboard, CPU, VGA, RAM</title>

</head>

<body>
    <?php include_once 'header.php';
    include_once 'banner.php';
    include_once 'danhmuc.php';
    include_once 'voucher_code.php';
    include_once 'new-products.php';
    include_once 'loading_bar.php';
    include_once 'contact_button.php';
    include_once 'box_categories.php';
    ?>
    

    <section class="news">
        <a href="tin-tuc-moi-nhat">
            <h2 class="text-center mb-4">TIN TỨC CÔNG NGHỆ 2024</h2>
        </a>

        <div class="container">
            <?php
            include_once 'dbconnect.php';
            $query = "SELECT news_id, news_name, news_image FROM news ORDER BY news_id DESC LIMIT 6";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                echo '<div class="row">';
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="col-lg-4 col-md-6 col-sm-6">';
                    echo '<div class="blog__item">';
                    echo '<a href="chi-tiet-tin-tuc?news_id=' . $row['news_id'] . '">';
                    echo '<img src="admin/admin/' . $row["news_image"] . '" alt="' . $row["news_name"] . '">';
                    echo '</a>';
                    echo '<div class="blog__item__text">';
                    echo '<a href="chi-tiet-tin-tuc?news_id=' . $row['news_id'] . '">';
                    echo '<h5>' . $row['news_name'] . '</h5>';
                    echo '</a>';
                    echo '<a class="view" href="chi-tiet-tin-tuc?news_id=' . $row['news_id'] . '">Xem Ngay</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }

                echo '</div>';
            }
            ?>
        </div>
    </section>

    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-main animate__animated">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Thông tin sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="product-details" class="product-details">
                        <img id="product-img" src="" alt="Product Image" class="product-img img-fluid mb-3">
                        <h3 id="product-name" class="product-name"></h3>
                        <p id="product-price" class="product-price"></p>
                        <p id="product-category" class="product-category"></p>
                        <p id="product-brand" class="product-brand"></p>
                        <p id="product-sales" class="product-sales"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>


    <?php include_once 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        const productModal = document.getElementById('productModal');

        // Thêm hiệu ứng zoomIn khi modal được mở
        productModal.addEventListener('show.bs.modal', function() {
            const modalContent = productModal.querySelector('.modal-content');
            modalContent.classList.remove('animate__zoomOut'); // Gỡ bỏ hiệu ứng zoomOut nếu có
            modalContent.classList.add('animate__zoomIn'); // Thêm hiệu ứng zoomIn
        });

        // Thêm hiệu ứng zoomOut khi modal bị đóng
        productModal.addEventListener('hide.bs.modal', function() {
            const modalContent = productModal.querySelector('.modal-content');
            modalContent.classList.remove('animate__zoomIn'); // Gỡ bỏ hiệu ứng zoomIn
            modalContent.classList.add('animate__zoomOut'); // Thêm hiệu ứng zoomOut
        });

        // Reset lại hiệu ứng khi modal hoàn tất ẩn
        productModal.addEventListener('hidden.bs.modal', function() {
            const modalContent = productModal.querySelector('.modal-content');
            modalContent.classList.remove('animate__zoomOut'); // Gỡ bỏ hiệu ứng zoomOut
        });
    </script>
    <script>
        $(document).ready(function() {
    $('.open-modal-btn').click(function() {
        var productSlug = $(this).closest('.card').find('a').attr('href').split('/').pop(); // Lấy slug từ đường dẫn

        $.ajax({
            url: 'get_product_details.php',
            type: 'GET',
            data: {
                slug: productSlug // Truyền slug
            },
            dataType: 'json',
            success: function(data) {
                $('#productModal #product-img').attr('src', 'admin/admin/' + data.background_image);
                $('#productModal #product-name').text(data.product_name);

                // Hiển thị giá
                var discountedPrice = parseFloat(data.price * (1 - (data.discount_percentage / 100))).toFixed(0);
                var originalPrice = parseFloat(data.price).toFixed(0);
                var savingsPercentage = data.discount_percentage;

                $('#productModal #product-price').html(
                    '<strong class="title">Giá:</strong> <span class="discounted-price">' + new Intl.NumberFormat().format(discountedPrice) + ' VNĐ</span> ' +
                    '<span class="original-price">' + new Intl.NumberFormat().format(originalPrice) + ' VNĐ</span> ' +
                    '<strong class="title1">(Tiết kiệm: ' + savingsPercentage + '%)</strong>'
                );

                $('#productModal #product-category').html('<strong class="title">Loại sản phẩm:</strong> ' + data.category_name);
                $('#productModal #product-brand').html('<strong class="title">Thương hiệu:</strong> ' + data.brand_name);
                $('#productModal #product-sales').html('<strong class="title">Số lượng đã bán:</strong> ' + data.sales_count);

                // Hiển thị modal
                $('#productModal').modal('show');
            },
            error: function() {
                alert('Không thể lấy thông tin sản phẩm.');
            }
        });
    });
});
    </script>

</body>

</html>