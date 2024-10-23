<?php
include_once 'dbconnect.php';

// Truy xuất sản phẩm mới nhất và thông tin giảm giá
$sql = "SELECT p.product_id, p.product_name, p.price, p.background_image, p.stock_quantity, 
               COALESCE(d.discount_percentage, 0) AS discount_percentage, 
               COALESCE(p.price * (1 - d.discount_percentage / 100), p.price) AS discounted_price,
               p.configuration, p.sales_count
        FROM products p
        LEFT JOIN discounts d ON p.product_id = d.product_id 
        AND d.start_date <= CURDATE() AND d.end_date >= CURDATE()
        ORDER BY p.product_id DESC LIMIT 10";
$result = $conn->query($sql);

// Truy xuất sản phẩm bán chạy và thông tin giảm giá
$sqlBestSelling = "SELECT p.product_id, p.product_name, p.price, p.background_image,p.stock_quantity, 
                           COALESCE(d.discount_percentage, 0) AS discount_percentage, 
                           COALESCE(p.price * (1 - d.discount_percentage / 100), p.price) AS discounted_price
                    FROM products p
                    LEFT JOIN discounts d ON p.product_id = d.product_id 
                    AND d.start_date <= CURDATE() AND d.end_date >= CURDATE()
                    WHERE p.sales_count > 99 
                    ORDER BY p.sales_count DESC LIMIT 10";
$resultBestSelling = $conn->query($sqlBestSelling);

$sqlDiscountedProducts = "SELECT p.product_id, p.product_name, p.price, p.background_image, 
                                  p.stock_quantity, d.discount_percentage, 
                                  (p.price * (1 - d.discount_percentage / 100)) AS discounted_price
                          FROM products p
                          JOIN discounts d ON p.product_id = d.product_id
                          WHERE d.start_date <= CURDATE() AND d.end_date >= CURDATE()";

$resultDiscountedProducts = $conn->query($sqlDiscountedProducts);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
    <title>Sản phẩm mới</title>

</head>

<body>
    <?php
    include_once 'header.php';
    ?>

    <section class="discounted-products">
        <div class="container mt-4">
            <div class="group-title row align-items-center">
                <div class="col-md-6 col-12">
                    <div class="best-selling-title d-flex align-items-center">
                        <img width="50" height="60" src="assets/img/flicon.png" alt="Flash Sale Icon">
                        <a class="title ms-2" href="deal.php">FLASH SALES MỖI NGÀY</a>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="count-down">
                        <div class="timer-view d-flex align-items-center">
                            <div class="block-timer" id="days">
                                <div class="time">23</div>
                                <div class="label">Ngày</div>
                            </div>
                            <span class="separator">:</span>
                            <div class="block-timer" id="hours">
                                <div class="time">12</div>
                                <div class="label">Giờ</div>
                            </div>
                            <span class="separator">:</span>
                            <div class="block-timer" id="minutes">
                                <div class="time">45</div>
                                <div class="label">Phút</div>
                            </div>
                            <span class="separator">:</span>
                            <div class="block-timer" id="seconds">
                                <div class="time">30</div>
                                <div class="label">Giây</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper-container-discounted-products">
                <div class="swiper-wrapper">
                    <?php
                    if ($resultDiscountedProducts === false) {
                        echo "Lỗi truy vấn: " . $conn->error;
                    } else {
                        if ($resultDiscountedProducts->num_rows > 0) {
                            while ($row = $resultDiscountedProducts->fetch_assoc()) {
                                $imagePath = 'admin' . $row["background_image"];

                                // Truy vấn để lấy thông tin quà tặng từ bảng `product_promotions`
                                $promoQuery = "SELECT promotion_description FROM product_promotions WHERE product_id = " . intval($row['product_id']);
                                $promoResult = $conn->query($promoQuery);

                                $promotionDescriptions = []; // Mảng để lưu tất cả các mô tả quà tặng

                                if ($promoResult && $promoResult->num_rows > 0) {
                                    while ($promoRow = $promoResult->fetch_assoc()) {
                                        // Lưu từng mô tả vào mảng với dấu '-' trước mỗi mô tả
                                        $promotionDescriptions[] = '- ' . htmlspecialchars($promoRow['promotion_description']);
                                    }
                                }

                                // Chuyển mảng mô tả thành một chuỗi để hiển thị trong tooltip, với <br> để xuống dòng
                                $promotionDescription = implode("<br>", $promotionDescriptions); // Xuống dòng với mỗi mô tả khác nhau

                                echo '<div class="swiper-slide col-md-4 d-flex justify-content-center">';
                                echo '    <div class="card position-relative">';

                                // Thêm biểu tượng quà tặng vào góc trên bên phải ảnh
                                if (!empty($promotionDescription)) {
                                    echo '<div class="tag-promo" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" data-bs-offset="-35, 0" title="' . $promotionDescription . '">';
                                    echo '    <i class="fa-solid fa-gift"></i>';
                                    echo '</div>';
                                }

                                echo '        <div class="discount-percentage">- ' . htmlspecialchars($row["discount_percentage"]) . '% </div>';
                                echo '        <div class="card-img-wrapper">';
                                echo '            <a href="product-detail.php?id=' . htmlspecialchars($row["product_id"]) . '">';
                                echo '                <img src="' . htmlspecialchars($imagePath) . '" class="card-img-top product-img" alt="' . htmlspecialchars($row["product_name"]) . '">';
                                echo '            </a>';
                                echo '        </div>';
                                echo '        <button type="button" class="btn open-modal-btn" title="Xem chi tiết" data-bs-toggle="modal" data-bs-target="#productModal">';
                                echo '            <i class="fa-regular fa-eye"></i>';
                                echo '        </button>';
                                echo '        <div class="card-body">';
                                echo '            <h5 class="card-title">';
                                echo '                <a href="product-detail.php?id=' . htmlspecialchars($row["product_id"]) . '" class="product-link" title="' . htmlspecialchars($row["product_name"]) . '">';
                                echo '                    ' . htmlspecialchars($row["product_name"]) . '';
                                echo '                </a>';
                                echo '            </h5>';
                                echo '            <p class="card-text"><span class="text-decoration-line-through">' . htmlspecialchars(number_format($row["price"])) . '₫</span></p>';
                                echo '            <p class="card-text-price">' . htmlspecialchars(number_format($row["discounted_price"])) . ' ₫</p>';
                                echo '            <div class="d-flex justify-content-between align-items-center">';
                                echo '                <p class="stock-quantity mb-0">';
                                if ($row['stock_quantity'] > 0) {
                                    echo '<span class="text-success"><i class="fa-regular fa-circle-check"></i> Còn hàng</span>';
                                } else {
                                    echo '<span class="text-danger"><i class="fa-regular fa-circle-xmark"></i> Đã hết hàng</span>';
                                }
                                echo '                </p>';
                                echo '                <a href="#" onclick="addToCart(' . htmlspecialchars($row['product_id']) . ', 1); return false;" class="btn btn-primary"><i class="fa-sharp fa-solid fa-cart-plus"></i></a>';
                                echo '            </div>'; // Kết thúc thẻ div d-flex
                                echo '        </div>'; // Kết thúc thẻ div card-body
                                echo '    </div>'; // Kết thúc thẻ div card
                                echo '</div>'; // Kết thúc thẻ div swiper-slide
                            }
                        } else {
                            echo "<p>Không có sản phẩm giảm giá.</p>";
                        }
                    }
                    ?>

                </div>
                <!-- Các nút điều hướng -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
            <div class="discounted-products-footer">
                <a href="deal.php" class="btn btn-view-all"><i class="fa-solid fa-right-to-bracket"></i> Xem tất cả</a>
            </div>

        </div>
    </section>

    <section class="best-selling-products-swiper">
        <div class="container mt-4">
            <div class="group-title">
                <div class="best-selling-title">
                    <img width="50" height="50" src="assets/img/flash-icon-1.png">
                    <a class="title" href="#">SẢN PHẨM HOT </a>
                </div>
            </div>
            <div class="swiper-container-best-selling-products">
                <div class="swiper-wrapper">
                    <?php
                    if ($resultBestSelling->num_rows > 0) {
                        while ($row = $resultBestSelling->fetch_assoc()) {
                            $imagePath = 'admin' . $row["background_image"];
                            // Truy vấn để lấy thông tin quà tặng từ bảng `product_promotions`
                            $promoQuery = "SELECT promotion_description FROM product_promotions WHERE product_id = " . intval($row['product_id']);
                            $promoResult = $conn->query($promoQuery);

                            $promotionDescriptions = []; // Mảng để lưu tất cả các mô tả quà tặng

                            if ($promoResult && $promoResult->num_rows > 0) {
                                while ($promoRow = $promoResult->fetch_assoc()) {
                                    // Lưu từng mô tả vào mảng với dấu '-' trước mỗi mô tả
                                    $promotionDescriptions[] = '- ' . htmlspecialchars($promoRow['promotion_description']);
                                }
                            }

                            // Chuyển mảng mô tả thành một chuỗi để hiển thị trong tooltip, với <br> để xuống dòng
                            $promotionDescription = implode("<br>", $promotionDescriptions); // Xuống dòng với mỗi mô tả khác nhau
                            echo '<div class="swiper-slide col-md-4 d-flex justify-content-center">';
                            echo '    <div class="card position-relative" data-product-id="' . htmlspecialchars($row["product_id"]) . '" data-product-name="' . htmlspecialchars($row["product_name"]) . '" data-product-price="' . htmlspecialchars(number_format($row["price"])) . '" data-product-config="' . (isset($row["configuration"]) ? htmlspecialchars($row["configuration"]) : 'Chưa có thông tin') . '" data-product-sales="' . (isset($row["sales_count"]) ? htmlspecialchars($row["sales_count"]) : 'Chưa có thông tin') . '">';
                            // Thêm biểu tượng quà tặng vào góc trên bên phải ảnh
                            if (!empty($promotionDescription)) {
                                echo '<div class="tag-promo" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" data-bs-offset="-35, 0" title="' . $promotionDescription . '">';
                                echo '    <i class="fa-solid fa-gift"></i>';
                                echo '</div>';
                            }
                            echo '        <div class="card-img-wrapper">';
                            echo '            <a href="product-detail.php?id=' . htmlspecialchars($row["product_id"]) . '">';
                            echo '                <img src="' . htmlspecialchars($imagePath) . '" class="card-img-top product-img" alt="' . htmlspecialchars($row["product_name"]) . '">';
                            echo '            </a>';
                            echo '        </div>';
                            echo '        <button type="button" class="btn open-modal-btn" data-bs-toggle="modal" data-bs-target="#productModal" data-product-id="' . htmlspecialchars($row["product_id"]) . '">';
                            echo '            <i class="fa-regular fa-eye"></i>';
                            echo '        </button>';
                            echo '        <div class="card-body">';
                            echo '            <h5 class="card-title">';
                            echo '                <a href="product-detail.php?id=' . htmlspecialchars($row["product_id"]) . '" class="product-link" title="' . htmlspecialchars($row["product_name"]) . '">';
                            echo '                    ' . htmlspecialchars($row["product_name"]) . '';
                            echo '                </a>';
                            echo '            </h5>';
                            echo '            <p class="card-text">';
                            if ($row["discount_percentage"] > 0) {
                                echo '<span class="price-discount">' . htmlspecialchars(number_format($row["price"])) . ' ₫</span>';
                                echo '<span class="card-text-price">' . htmlspecialchars(number_format($row["discounted_price"])) . ' ₫</span>';
                            } else {
                                echo '<span class="card-text-price">' . htmlspecialchars(number_format($row["price"])) . ' ₫</span>';
                            }
                            echo '            </p>';
                            echo '            <div class="d-flex justify-content-between align-items-center">';
                            echo '                <p class="stock-quantity mb-0">';
                            if ($row['stock_quantity'] > 0) {
                                echo '<span class="text-success"><i class="fa-regular fa-circle-check"></i> Còn hàng</span>';
                            } else {
                                echo '<span class="text-danger"><i class="fa-regular fa-circle-xmark"></i> Đã hết hàng</span>';
                            }
                            echo '                </p>';
                            echo '                <a href="#" onclick="addToCart(' . htmlspecialchars($row['product_id']) . ', 1); return false;" class="btn btn-primary"><i class="fa-sharp fa-solid fa-cart-plus"></i></a>';
                            echo '            </div>'; // Kết thúc thẻ div d-flex
                            echo '        </div>';
                            echo '    </div>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>Không có sản phẩm bán chạy.</p>";
                    }
                    ?>
                </div>
                <!-- Các nút điều hướng -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
            <div class="best-selling-footer">
                <a href="all-products.php?sort=sales_desc" class="btn btn-view-all"><i class="fa-solid fa-right-to-bracket"></i> Xem tất cả</a>
            </div>
        </div>
    </section>

    <section class="new-products-swiper">
        <div class="container mt-4">
            <div class="group-title">
                <div class="new-products-title">
                    <img width="50" height="50" src="https://zeevector.com/wp-content/uploads/New-Logo-Design-png@zeevector.png" alt="New Products Icon">
                    <a class="title" href="#">SẢN PHẨM MỚI</a>
                </div>
            </div>
            <div class="swiper-container-new-products">
                <div class="swiper-wrapper">
                    <?php
                    if ($result === false) {
                        echo "Lỗi truy vấn: " . $conn->error;
                    } else {
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $imagePath = 'admin' . $row["background_image"];
                                // Truy vấn để lấy thông tin quà tặng từ bảng `product_promotions`
                                $promoQuery = "SELECT promotion_description FROM product_promotions WHERE product_id = " . intval($row['product_id']);
                                $promoResult = $conn->query($promoQuery);

                                $promotionDescriptions = []; // Mảng để lưu tất cả các mô tả quà tặng

                                if ($promoResult && $promoResult->num_rows > 0) {
                                    while ($promoRow = $promoResult->fetch_assoc()) {
                                        // Lưu từng mô tả vào mảng với dấu '-' trước mỗi mô tả
                                        $promotionDescriptions[] = '- ' . htmlspecialchars($promoRow['promotion_description']);
                                    }
                                }

                                // Chuyển mảng mô tả thành một chuỗi để hiển thị trong tooltip, với <br> để xuống dòng
                                $promotionDescription = implode("<br>", $promotionDescriptions); // Xuống dòng với mỗi mô tả khác nhau

                                echo '<div class="swiper-slide col-md-4 d-flex justify-content-center">';
                                echo '    <div class="card position-relative" data-product-id="' . htmlspecialchars($row["product_id"]) . '" data-product-name="' . htmlspecialchars($row["product_name"]) . '" data-product-price="' . htmlspecialchars(number_format($row["price"])) . '" data-product-config="' . (isset($row["configuration"]) ? htmlspecialchars($row["configuration"]) : 'Chưa có thông tin') . '" data-product-sales="' . (isset($row["sales_count"]) ? htmlspecialchars($row["sales_count"]) : 'Chưa có thông tin') . '">';
                                // Thêm biểu tượng quà tặng vào góc trên bên phải ảnh
                                if (!empty($promotionDescription)) {
                                    echo '<div class="tag-promo" data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" data-bs-offset="-35, 0" title="' . $promotionDescription . '">';
                                    echo '    <i class="fa-solid fa-gift"></i>';
                                    echo '</div>';
                                }
                                echo '        <div class="card-img-wrapper">';
                                echo '            <a href="product-detail.php?id=' . htmlspecialchars($row["product_id"]) . '">';
                                echo '                <img src="' . htmlspecialchars($imagePath) . '" class="card-img-top product-img" alt="' . htmlspecialchars($row["product_name"]) . '">';
                                echo '            </a>';
                                echo '        </div>';
                                echo '        <button type="button" class="btn open-modal-btn" data-bs-toggle="modal" data-bs-target="#productModal" data-product-id="' . htmlspecialchars($row["product_id"]) . '">';
                                echo '            <i class="fa-regular fa-eye"></i>';
                                echo '        </button>';
                                echo '        <div class="card-body">';
                                echo '            <h5 class="card-title">';
                                echo '                <a href="product-detail.php?id=' . htmlspecialchars($row["product_id"]) . '" class="product-link" title="' . htmlspecialchars($row["product_name"]) . '">';
                                echo '                    ' . htmlspecialchars($row["product_name"]) . '';
                                echo '                </a>';
                                echo '            </h5>';
                                echo '            <p class="card-text">';
                                if ($row["discount_percentage"] > 0) {
                                    echo '<span class="price-discount">' . htmlspecialchars(number_format($row["price"])) . ' ₫</span>';
                                    echo '<span class="card-text-price">' . htmlspecialchars(number_format($row["discounted_price"])) . ' ₫</span>';
                                } else {
                                    echo '<span class="card-text-price">' . htmlspecialchars(number_format($row["price"])) . ' ₫</span>';
                                }
                                echo '            </p>';
                                echo '            <div class="d-flex justify-content-between align-items-center">';
                                echo '                <p class="stock-quantity mb-0">';
                                if ($row['stock_quantity'] > 0) {
                                    echo '<span class="text-success"><i class="fa-regular fa-circle-check"></i> Còn hàng</span>';
                                } else {
                                    echo '<span class="text-danger"><i class="fa-regular fa-circle-xmark"></i> Đã hết hàng</span>';
                                }
                                echo '                </p>';
                                echo '            <a href="#" onclick="addToCart(' . htmlspecialchars($row['product_id']) . '); return false;" class="btn btn-primary"><i class="fa-sharp fa-solid fa-cart-plus"></i></a>';
                                echo '            </div>'; // Kết thúc thẻ div d-flex
                                echo '        </div>';
                                echo '    </div>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>Không có sản phẩm mới.</p>";
                        }
                    }
                    ?>
                </div>

                <!-- Các nút điều hướng -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
            <div class="new-products-footer">
                <a href="all-products.php" class="btn btn-view-all"><i class="fa-solid fa-right-to-bracket"></i> Xem tất cả</a>
            </div>
        </div>
    </section>
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modaladdtocard">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Thông báo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="cartModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var swiper2 = new Swiper('.swiper-container-new-products', {
                loop: true, // Cho phép lặp lại các slide
                autoplay: {
                    delay: 2000, // Thời gian giữa mỗi lần trượt (tính bằng ms)
                    disableOnInteraction: false, // Tạm dừng autoplay khi có tương tác, nhưng sẽ tiếp tục sau đó
                    pauseOnMouseEnter: true, // Tạm dừng autoplay khi di chuột vào
                },
                speed: 1000,
                spaceBetween: 10,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    100: {
                        slidesPerView: 1, // Hiển thị 1 slide trên màn hình nhỏ hơn 100px
                    },
                    640: {
                        slidesPerView: 2, // Hiển thị 2 slides trên màn hình lớn hơn 640px
                    },
                    768: {
                        slidesPerView: 3, // Hiển thị 3 slides trên màn hình lớn hơn 768px
                    },
                    1024: {
                        slidesPerView: 4, // Hiển thị 4 slides trên màn hình lớn hơn 1024px
                    },
                },
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var swiper3 = new Swiper('.swiper-container-best-selling-products', {
                loop: true, // Cho phép lặp lại các slide
                autoplay: {
                    delay: 2000, // Thời gian giữa mỗi lần trượt (tính bằng ms)
                    disableOnInteraction: false, // Tạm dừng autoplay khi có tương tác, nhưng sẽ tiếp tục sau đó
                    pauseOnMouseEnter: true, // Tạm dừng autoplay khi di chuột vào
                },
                speed: 1000,
                spaceBetween: 10,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    100: {
                        slidesPerView: 1, // Hiển thị 1 slide trên màn hình nhỏ hơn 100px
                    },
                    640: {
                        slidesPerView: 2, // Hiển thị 2 slides trên màn hình lớn hơn 640px
                    },
                    768: {
                        slidesPerView: 3, // Hiển thị 3 slides trên màn hình lớn hơn 768px
                    },
                    1024: {
                        slidesPerView: 4, // Hiển thị 4 slides trên màn hình lớn hơn 1024px
                    },
                },
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var swiper4 = new Swiper('.swiper-container-discounted-products', {
                loop: true, // Cho phép lặp lại các slide
                autoplay: {
                    delay: 2500, // Thời gian giữa mỗi lần trượt (tính bằng ms)
                    disableOnInteraction: false, // Tạm dừng autoplay khi có tương tác, nhưng sẽ tiếp tục sau đó
                    pauseOnMouseEnter: true, // Tạm dừng autoplay khi di chuột vào
                },
                speed: 1500,
                spaceBetween: 10,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    100: {
                        slidesPerView: 1, // Hiển thị 1 slide trên màn hình nhỏ hơn 100px
                    },
                    640: {
                        slidesPerView: 2, // Hiển thị 2 slides trên màn hình lớn hơn 640px
                    },
                    768: {
                        slidesPerView: 3, // Hiển thị 3 slides trên màn hình lớn hơn 768px
                    },
                    1024: {
                        slidesPerView: 4, // Hiển thị 4 slides trên màn hình lớn hơn 1024px
                    },
                },
            });
        });
    </script>

    <script>
        // Thiết lập thời gian đếm ngược
        const targetTime = new Date("Feb 30, 2025 00:00:00").getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetTime - now;

            // Tính toán ngày, giờ, phút, giây
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Hiển thị thời gian
            document.getElementById("days").querySelector(".time").textContent = days < 10 ? "0" + days : days;
            document.getElementById("hours").querySelector(".time").textContent = hours < 10 ? "0" + hours : hours;
            document.getElementById("minutes").querySelector(".time").textContent = minutes < 10 ? "0" + minutes : minutes;
            document.getElementById("seconds").querySelector(".time").textContent = seconds < 10 ? "0" + seconds : seconds;

            // Nếu hết thời gian
            if (distance < 0) {
                clearInterval(countdownInterval);
                document.querySelector(".count-down").textContent = "Hết thời gian!";
            }
        }

        // Cập nhật mỗi giây
        const countdownInterval = setInterval(updateCountdown, 1000);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
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
        // Kích hoạt tất cả tooltips khi trang đã tải xong
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        // Khởi tạo tooltip với hỗ trợ HTML
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                html: true // Cho phép HTML trong tooltip
            });
        });
    </script>


</body>

</html>