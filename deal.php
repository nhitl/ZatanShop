<?php
include_once 'dbconnect.php';
$sqlFeatured = "SELECT p.product_id, p.product_name, p.price, p.background_image, d.discount_percentage, d.end_date 
                FROM products p 
                JOIN discounts d ON p.product_id = d.product_id 
                WHERE d.end_date >= CURDATE() 
                ORDER BY d.discount_percentage DESC 
                LIMIT 3";
$resultFeatured = $conn->query($sqlFeatured);

$limit = 12; // Số sản phẩm trên mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sqlDeals = "SELECT p.product_id, p.product_name, p.price, p.background_image, d.discount_percentage, d.end_date 
             FROM products p 
             JOIN discounts d ON p.product_id = d.product_id 
             WHERE d.end_date >= CURDATE() 
             ORDER BY d.end_date DESC 
             LIMIT $limit OFFSET $offset";
$resultDeals = $conn->query($sqlDeals);

// Tính tổng số trang
$sqlCount = "SELECT COUNT(*) AS total 
             FROM products p 
             JOIN discounts d ON p.product_id = d.product_id 
             WHERE d.end_date >= CURDATE()";
$resultCount = $conn->query($sqlCount);
$totalRows = $resultCount->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Flash Sale Mỗi Ngày</title>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        function startCountdown(productId, endDate) {
            var countdownDate = new Date(endDate).getTime();
            var x = setInterval(function() {
                var now = new Date().getTime();
                var distance = countdownDate - now;

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Cập nhật hiển thị cho từng phần tử
                document.getElementById("days-" + productId).innerHTML = days < 10 ? '0' + days : days;
                document.getElementById("hours-" + productId).innerHTML = hours < 10 ? '0' + hours : hours;
                document.getElementById("minutes-" + productId).innerHTML = minutes < 10 ? '0' + minutes : minutes;
                document.getElementById("seconds-" + productId).innerHTML = seconds < 10 ? '0' + seconds : seconds;

                // Kiểm tra thời gian
                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("days-" + productId).innerHTML = "00";
                    document.getElementById("hours-" + productId).innerHTML = "00";
                    document.getElementById("minutes-" + productId).innerHTML = "00";
                    document.getElementById("seconds-" + productId).innerHTML = "00";
                    document.querySelector(".countdown-wrapper").innerHTML += "<p>Đã kết thúc</p>"; // Hoặc xử lý khác
                }
            }, 1000);
        }
    </script>

</head>

<body>
    <?php include_once 'header.php'; 
    include_once 'contact_button.php';?>
    <section class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Trang giảm giá</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="deal">
        <div class="container">
            <div class="deal-featured">
                <div class="deal-featured-slider swiper-container">
                    <div class="swiper-wrapper">
                        <?php if ($resultFeatured->num_rows > 0): ?>
                            <?php while ($row = $resultFeatured->fetch_assoc()):
                                // Tính giá sau khi giảm
                                $originalPrice = $row['price'];
                                $discountPercentage = $row['discount_percentage'];
                                $discountedPrice = $originalPrice - ($originalPrice * $discountPercentage / 100);
                            ?>
                                <div class="swiper-slide">
                                    <div class="deal-card">
                                        <a href="product-detail.php?id=<?php echo $row['product_id']; ?>" class="deal-card-image position-relative">
                                            <img src="<?php echo 'admin' . $row['background_image']; ?>" alt="<?php echo $row['product_name']; ?>" class="img-deal-featured">
                                            <span class="badge hot-deal"><i class="fa-solid fa-bolt"></i> Hot Deal <i class="fa-regular fa-clock"></i></span> <!-- Mác Hot Deal -->
                                        </a>

                                        <div class="deal-card-info">
                                            <h5 class="product-title" title="<?php echo htmlspecialchars($row['product_name']); ?>">
                                                <a href="product-detail.php?id=<?php echo $row['product_id']; ?>" class="product-link">
                                                    <?php echo htmlspecialchars($row['product_name']); ?>
                                                </a>
                                            </h5>

                                            <p class="product-price-discounted"><span class="text-danger"><?php echo number_format($discountedPrice); ?> VNĐ</span></p>
                                            <p class="product-price"><span class="text-decoration-line-through text-muted"><?php echo number_format($originalPrice); ?> VNĐ</span><span class="text-danger">(Tiết kiệm: <?php echo $discountPercentage; ?>%)</span></p>
                                            <div class="button-wrapper">
                                                <a href="#" onclick="addToCart(<?php echo htmlspecialchars($row['product_id']); ?>, 1); return false;" class="btn btn-danger">MUA GIÁ SỐC</a>
                                            </div>

                                            <div class="countdown-wrapper">
                                                <p class="countdown-text">Thời gian còn lại:</p>
                                                <div class="countdown">
                                                    <div class="countdown-item">
                                                        <span class="countdown-value" id="days-<?php echo $row['product_id']; ?>">00</span>
                                                        <span class="countdown-label">Ngày</span>
                                                    </div>
                                                    <div class="countdown-item">
                                                        <span class="countdown-value" id="hours-<?php echo $row['product_id']; ?>">00</span>
                                                        <span class="countdown-label">Giờ</span>
                                                    </div>
                                                    <div class="countdown-item">
                                                        <span class="countdown-value" id="minutes-<?php echo $row['product_id']; ?>">00</span>
                                                        <span class="countdown-label">Phút</span>
                                                    </div>
                                                    <div class="countdown-item">
                                                        <span class="countdown-value" id="seconds-<?php echo $row['product_id']; ?>">00</span>
                                                        <span class="countdown-label">Giây</span>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <script>
                                    // Gọi hàm countdown cho từng sản phẩm
                                    if ("<?php echo $row['end_date']; ?>" !== "") {
                                        startCountdown(<?php echo $row['product_id']; ?>, "<?php echo $row['end_date']; ?>");
                                    }
                                </script>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Không có sản phẩm giảm giá.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="deal-list row">
                <?php if ($resultDeals->num_rows > 0): ?>
                    <?php while ($row = $resultDeals->fetch_assoc()): ?>
                        <div class="col-md-3 col-6">
                            <div class="card mb-4">
                                <a class="img-deal" href="product-detail.php?id=<?php echo $row['product_id']; ?>">
                                    <!-- Badge hiển thị % giảm giá -->
                                    <div class="discount-badge">
                                        - <?php echo $row['discount_percentage']; ?>%
                                    </div>
                                    <img src="<?php echo 'admin' . $row['background_image']; ?>" alt="<?php echo $row['product_name']; ?>" class="card-img-top">
                                </a>
                                <div class="card-body">
                                    <a href="product-detail.php?id=<?php echo $row['product_id']; ?>">
                                        <h5 class="card-title" title="<?php echo htmlspecialchars($row['product_name']); ?>">
                                            <?php echo htmlspecialchars($row['product_name']); ?>
                                        </h5>
                                    </a>
                                    <p class="card-text"><span class="text-decoration-line-through"><?php echo number_format($row['price']); ?> VNĐ</span></p>
                                    <p class="card-text"><span class="text-danger"><?php echo number_format($row['price'] * (1 - $row['discount_percentage'] / 100)); ?> VNĐ</span></p>

                                    <!-- Thêm khuyến mãi nếu có -->
                                    <?php
                                    // Truy vấn khuyến mãi cho sản phẩm
                                    $promo_query = "SELECT promotion_description FROM product_promotions WHERE product_id = " . $row['product_id'];
                                    $promo_result = mysqli_query($conn, $promo_query);

                                    if ($promo_result && mysqli_num_rows($promo_result) > 0): ?>
                                        <div class="promo-icon-wrapper position-absolute top-0 end-0 p-2">
                                            <div class="promo-icon-circle">
                                                <i class="fa-solid fa-gift" title="Quà tặng"></i>
                                                <div class="promo-tooltip-wrapper">
                                                    <div class="promo-tooltip">
                                                        <?php
                                                        // Lưu trữ tất cả mô tả khuyến mãi
                                                        $promo_descriptions = [];
                                                        while ($promo_row = mysqli_fetch_assoc($promo_result)) {
                                                            // Thêm ký tự '-' trước mô tả khuyến mãi
                                                            $promo_descriptions[] = '- ' . htmlspecialchars($promo_row['promotion_description']);
                                                        }
                                                        // Hiển thị các mô tả khuyến mãi với dấu '-' và mỗi mô tả nằm trên một dòng mới
                                                        echo implode('<br>', $promo_descriptions);
                                                        ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="btn-wrapper">
                                        <a href="#" onclick="addToCart(<?php echo htmlspecialchars($row['product_id']); ?>, 1); return false;" class="btn btn-primary"><i class="fa-solid fa-cart-plus"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Không có sản phẩm giảm giá.</p>
                <?php endif; ?>
            </div>



            <div class="paging">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Nút về Trang đầu -->
                        <li class="page-item <?php if ($page == 1) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=1"><i class="fa-solid fa-angles-left"></i></a>
                        </li>

                        <!-- Các trang ở giữa -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Nút đến Trang cuối -->
                        <li class="page-item <?php if ($page == $totalPages) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $totalPages; ?>"><i class="fa-solid fa-angles-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>

        </div>
    </section>
    <?php include_once 'footer.php'; ?>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        var swiper7 = new Swiper('.swiper-container', {
            slidesPerView: 1, // Hiển thị một slide mỗi lần
            spaceBetween: 10, // Không có khoảng cách giữa các slide
            loop: false, // Cho phép lặp lại các slide
            speed: 1000, // Tốc độ chuyển động khi kéo (ms)
            autoplay: {
                delay: 3000, // Tự động chuyển sau 3 giây
                disableOnInteraction: false, // Không tắt autoplay khi người dùng tương tác
            },
            touchRatio: 1, // Tăng khả năng nhận diện cảm ứng
            effect: 'slide', // Hiệu ứng chuyển slide cơ bản
        });

        // Tạm ngừng autoplay khi di chuột vào
        $('.swiper-container').on('mouseenter', function() {
            swiper7.autoplay.stop();
        });

        // Khôi phục autoplay khi di chuột ra
        $('.swiper-container').on('mouseleave', function() {
            swiper7.autoplay.start();
        });


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
                    // Hiển thị thông báo dựa trên trạng thái
                    if (result.status === 'error') {
                        alert(result.message); // Hiển thị thông báo lỗi
                    } else {
                        alert(result.message); // Hiển thị thông báo thành công
                        // Cập nhật số lượng sản phẩm trong giỏ (nếu cần)
                        updateCartCount(); // Hàm này cần được định nghĩa nếu bạn muốn cập nhật số lượng giỏ hàng
                    }
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>