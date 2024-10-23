<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Zatan Shop</title>
</head>

<body>
    <?php include_once 'header.php';
    include_once 'banner.php';
    include_once 'testdanhmuc.php';
    include_once 'new-products.php';
    include_once 'loading_bar.php';
    include_once 'contact_button.php';
    include_once 'box_mainboard.php';
    include_once 'box_cpu.php';
    ?>
    

    <section class="news">
        <a href="news.php">
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
                    echo '<a href="blog-details.php?news_id=' . $row['news_id'] . '">';
                    echo '<img src="admin/admin/' . $row["news_image"] . '" alt="' . $row["news_name"] . '">';
                    echo '</a>';
                    echo '<div class="blog__item__text">';
                    echo '<h5>' . $row['news_name'] . '</h5>';
                    echo '<a href="blog-details.php?news_id=' . $row['news_id'] . '">Xem Ngay</a>';
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
                var productId = $(this).closest('.card, .product-card').find('a').attr('href').split('id=')[1];

                $.ajax({
                    url: 'get_product_details.php',
                    type: 'GET',
                    data: {
                        id: productId
                    },
                    dataType: 'json',
                    success: function(data) {
                        $('#productModal #product-img').attr('src', 'admin' + data.background_image);
                        $('#productModal #product-name').text(data.product_name);

                        // Lấy giá gốc và giá sau giảm
                        var discountedPrice = parseFloat(data.price * (1 - (data.discount_percentage / 100))).toFixed(0); // Giá sau giảm
                        var originalPrice = parseFloat(data.price).toFixed(0); // Giá gốc
                        var savingsPercentage = data.discount_percentage; // Tỷ lệ tiết kiệm

                        // Hiển thị giá theo định dạng yêu cầu
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