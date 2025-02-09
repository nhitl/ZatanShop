<?php
// Kết nối đến cơ sở dữ liệu
include('dbconnect.php');

if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}
// Lấy slug sản phẩm từ tham số GET
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Khởi tạo biến để lưu trữ dữ liệu sản phẩm và hình ảnh
$product = null;
$images = [];


// Truy vấn thông tin sản phẩm theo slug
$productQuery = "
    SELECT 
        product_id,
        product_name,
        price,
        configuration,
        product_info,
        category_id,
        brand_id,
        subcategory_id,
        sales_count,
        background_image,
        stock_quantity -- Thêm số lượng tồn kho
    FROM products 
    WHERE slug = ?";
$stmt = $conn->prepare($productQuery);
$stmt->bind_param("s", $slug); // Sử dụng 's' vì slug là chuỗi
$stmt->execute();
$productResult = $stmt->get_result();
if ($productResult->num_rows > 0) {
    $product = $productResult->fetch_assoc();
} else {
    echo "Sản phẩm không tồn tại.";
    exit();
}


// Truy vấn hình ảnh sản phẩm
$imageQuery = "SELECT image_url FROM product_images WHERE product_id = ?";
$stmt = $conn->prepare($imageQuery);
$stmt->bind_param("i", $product['product_id']); // Sử dụng product_id đã lấy được
$stmt->execute();
$imagesResult = $stmt->get_result();
while ($image = $imagesResult->fetch_assoc()) {
    $images[] = $image['image_url'];
}


// Truy vấn giảm giá nếu có
$discountQuery = "
    SELECT 
        discount_percentage 
    FROM discounts 
    WHERE product_id = ? 
      AND NOW() BETWEEN start_date AND end_date";
$stmt = $conn->prepare($discountQuery);
$stmt->bind_param("i", $product['product_id']);
$stmt->execute();
$discountResult = $stmt->get_result();
$discount = $discountResult->fetch_assoc();

// Tính toán giá khuyến mãi và tỷ lệ giảm giá
if ($discount) {
    $originalPrice = $product['price'];
    $discountPercentage = $discount['discount_percentage'];
    $discountedPrice = $originalPrice * (1 - $discountPercentage / 100);
} else {
    $originalPrice = $product['price'];
    $discountedPrice = $originalPrice;
    $discountPercentage = 0;
}

// Truy vấn thông tin danh mục
$categoryQuery = "SELECT category_name FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param("i", $product['category_id']);
$stmt->execute();
$categoryResult = $stmt->get_result();
$category = $categoryResult->fetch_assoc();

// Truy vấn thông tin thương hiệu
$brandQuery = "SELECT brand_name FROM brands WHERE brand_id = ?";
$stmt = $conn->prepare($brandQuery);
$stmt->bind_param("i", $product['brand_id']);
$stmt->execute();
$brandResult = $stmt->get_result();
$brand = $brandResult->fetch_assoc();

// Truy vấn thông tin danh mục phụ
$subcategoryQuery = "SELECT subcategory_name FROM subcategories WHERE subcategory_id = ?";
$stmt = $conn->prepare($subcategoryQuery);
$stmt->bind_param("i", $product['subcategory_id']);
$stmt->execute();
$subcategoryResult = $stmt->get_result();
$subcategory = $subcategoryResult->fetch_assoc();

// Giá sản phẩm hiện tại
$currentPrice = $product['price'];

// Tính toán giới hạn giá cho sản phẩm tương tự
$minPrice = $currentPrice * 0.8; // 80% giá hiện tại
$maxPrice = $currentPrice * 1.2; // 120% giá hiện tại

// Truy vấn sản phẩm tương tự
$similarProductsQuery = "
    SELECT 
        product_id,
        product_name,
        price,
        background_image,
        slug 
    FROM products 
    WHERE category_id = ? 
      AND product_id != ? 
      AND price BETWEEN ? AND ? 
    LIMIT 10"; // Giới hạn số sản phẩm lấy về


$stmt = $conn->prepare($similarProductsQuery);
$stmt->bind_param("iiii", $product['category_id'], $product['product_id'], $minPrice, $maxPrice);
$stmt->execute();
$similarProductsResult = $stmt->get_result();

$similarProducts = [];
while ($row = $similarProductsResult->fetch_assoc()) {
    $similarProducts[] = $row;
}

// Truy vấn lấy các đánh giá cho sản phẩm theo product_id
$reviewQuery = "
    SELECT 
        r.rating, r.comment, r.image_path, r.created_at, u.full_name 
    FROM product_reviews r 
    LEFT JOIN users u ON r.user_id = u.user_id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC"; // Sắp xếp theo thời gian tạo, từ cũ đến mới
$stmt = $conn->prepare($reviewQuery);
$stmt->bind_param("i", $product['product_id']);
$stmt->execute();
$reviewsResult = $stmt->get_result(); // Lấy kết quả đánh giá


// Truy vấn lấy số lượng đánh giá theo từng mức sao
$ratingCountQuery = "
    SELECT rating, COUNT(*) AS rating_count
    FROM product_reviews 
    WHERE product_id = ? 
    GROUP BY rating
    ORDER BY rating DESC"; // Sắp xếp theo số sao từ cao đến thấp
$stmt = $conn->prepare($ratingCountQuery);
$stmt->bind_param("i", $product['product_id']);
$stmt->execute();
$ratingCountsResult = $stmt->get_result();
$ratingCounts = [];
while ($row = $ratingCountsResult->fetch_assoc()) {
    $ratingCounts[$row['rating']] = $row['rating_count'];
}

// Nếu không có giá trị cho một mức sao, đặt mặc định là 0
for ($i = 1; $i <= 5; $i++) {
    if (!isset($ratingCounts[$i])) {
        $ratingCounts[$i] = 0;
    }
}

// Truy vấn lấy trung bình số sao của sản phẩm
$averageRatingQuery = "
    SELECT AVG(rating) AS average_rating
    FROM product_reviews
    WHERE product_id = ?";
$stmt = $conn->prepare($averageRatingQuery);
$stmt->bind_param("i", $product['product_id']);
$stmt->execute();
$averageRatingResult = $stmt->get_result();
$averageRatingRow = $averageRatingResult->fetch_assoc();
$averageRating = round($averageRatingRow['average_rating'], 1); // Làm tròn đến 1 chữ số thập phân



?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Chi tiết sản phẩm</title>
    <!-- Thêm Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Thêm Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
    <link rel="stylesheet" href="assets/css/product-detail.css?v=1.0">
    <link rel="icon" href="admin/assets/img/favicon.ico.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</head>

<body>
    <?php
    include_once 'header.php';
    include_once 'contact_button.php';
    ?>
    <section class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi tiết sản phẩm</li>
                </ol>
            </nav>
        </div>
    </section>
    <section class="product layout-product">
        <div class="container mt-2">
            <div class="row">
                <!-- Phần hình ảnh phụ -->
                <div class="col-12 col-xl-12">
                    <div class="detail-product">
                        <div class="row">
                            <div class="product-detail-left product-images col-12 col-md-12 col-lg-6 col-xl-4">
                                <div class="product-image-block">
                                    <div class="swiper-container gallery-top">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($images as $image): ?>
                                                <a class="swiper-slide" href="<?php echo 'admin/admin/' . htmlspecialchars($image); ?>" data-fancybox="gallery" title="Click để xem">
                                                    <img src="<?php echo 'admin/admin/' . htmlspecialchars($image); ?>" alt="Hình ảnh sản phẩm" class="img-responsive mx-auto d-block">
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="swiper-container gallery-thumbs">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($images as $image): ?>
                                                <div class="swiper-slide">
                                                    <div class="p-100">
                                                        <img src="<?php echo 'admin/admin/' . htmlspecialchars($image); ?>" alt="Hình ảnh sản phẩm" class="swiper-lazy">
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev swiper-button-disabled"></div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-12 col-md-7 col-lg-6 col-xl-5">
                                <div class="product-info">
                                    <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
                                    <p class="price">
                                        <?php if ($discountPercentage > 0): ?>
                                            <span class="original-price"><?php echo number_format($originalPrice, 0, ',', '.'); ?> VNĐ</span>
                                            <span class="discounted-price"><?php echo number_format($discountedPrice, 0, ',', '.'); ?> VNĐ</span>
                                            <span class="discount-info">(Giảm <?php echo $discountPercentage; ?>%)</span>
                                        <?php else: ?>
                                            <?php echo number_format($originalPrice, 0, ',', '.'); ?> VNĐ
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Sản phẩm là:</strong> <?php echo html_entity_decode($category['category_name']); ?></p>
                                    <p><strong>Thương hiệu:</strong> <?php echo html_entity_decode($brand['brand_name']); ?></p>
                                    <p><strong>Thể loại:</strong> <?php echo html_entity_decode($subcategory['subcategory_name']); ?></p>
                                    <p><strong>Số lượng đã bán:</strong> <?php echo html_entity_decode($product['sales_count']); ?></p>
                                    <p><strong>Trong kho còn:</strong> <?php echo html_entity_decode($product['stock_quantity']); ?></p>

                                    <div class="quantity">
                                        <label for="quantity">Số lượng:</label>
                                        <div class="quantity-controls">
                                            <button type="button" id="quantity-decrease" class="quantity-btn">-</button>
                                            <input type="number" id="quantity" name="quantity" value="1" min="1" step="1">
                                            <button type="button" id="quantity-increase" class="quantity-btn">+</button>
                                        </div>
                                    </div>


                                    <a href="#" onclick="addToCart(<?php echo htmlspecialchars($product['product_id']); ?>, 1); return false;" class="btn btn-primary"><i class="fa-solid fa-cart-plus"></i>Thêm vào giỏ hàng</a>

                                    <?php
                                    // Giả sử bạn có biến $product_id chứa ID của sản phẩm hiện tại
                                    $product_id = $product['product_id']; // hoặc lấy product_id theo cách bạn sử dụng

                                    // Truy vấn các khuyến mãi từ bảng product_promotions liên quan đến sản phẩm hiện tại
                                    $promo_query = "SELECT promotion_description FROM product_promotions WHERE product_id = $product_id";
                                    $promo_result = mysqli_query($conn, $promo_query);

                                    ?>

                                    <div class="khuyen-mai">
                                        <div class="title">
                                            <img width="64" height="64" src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/giftbox.png?1720274480928" alt="vouver">
                                            <span>Khuyến mãi đặc biệt !!!</span>
                                        </div>
                                        <div class="content">
                                            <ul>
                                                <?php
                                                // Kiểm tra nếu có kết quả từ truy vấn
                                                if ($promo_result && mysqli_num_rows($promo_result) > 0) {
                                                    // Lặp qua tất cả các khuyến mãi và hiển thị chúng
                                                    while ($promo_row = mysqli_fetch_assoc($promo_result)) {
                                                        echo '<li><img width="20" height="20" src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/km_product1.png?1720274480928" alt="Khuyến mãi">';
                                                        echo htmlspecialchars($promo_row['promotion_description']);
                                                        echo '</li>';
                                                    }
                                                } else {
                                                    // Nếu không có khuyến mãi nào, hiển thị thông báo mặc định
                                                    echo '<li>Sản phẩm này hiện tại không nằm trong chương trình khuyến mãi nào.</li>';
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="camket">
                                        <div class="title">
                                            Cam kết của chúng tôi
                                        </div>
                                        <ul>

                                            <li>
                                                <img src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/camket_1.png?1720274480928" alt="cam kết">
                                                <span>Cam kết 100% chính hãng</span>
                                            </li>
                                            <li>
                                                <img src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/camket_2.png?1720274480928" alt="cam kết">
                                                <span>Hoàn tiền 111% nếu hàng giả</span>
                                            </li>
                                            <li>
                                                <img src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/camket_3.png?1720274480928" alt="cam kết">
                                                <span>Giao tận tay khách hàng</span>
                                            </li>
                                            <li>
                                                <img src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/camket_4.png?1720274480928" alt="cam kết">
                                                <span>Mở hộp kiểm tra nhận hàng</span>
                                            </li>
                                            <li>
                                                <img src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/camket_5.png?1720274480928" alt="cam kết">
                                                <span>Hỗ trợ 24/7</span>
                                            </li>
                                            <li>
                                                <img src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/camket_6.png?1720274480928" alt="cam kết">
                                                <span>Đổi trả trong 7 ngày</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Modal -->
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

                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-12 col-md-5 col-12 content-pro">
                                <div class="row">
                                    <div class="col-12 col-md-12 col-lg-6 col-xl-12">
                                        <div class="support-product">
                                            <div class="title">
                                                CHÚNG TÔI LUÔN SẴN SÀNG<br>
                                                ĐỂ GIÚP ĐỠ BẠN
                                            </div>
                                            <div class="image">
                                                <img src="//bizweb.dktcdn.net/100/425/892/themes/819335/assets/evo_product_support.jpg?1678155331448" data-src="//bizweb.dktcdn.net/100/425/892/themes/819335/assets/evo_product_support.jpg?1678155331448" alt="Hỗ trợ trực tuyến" class="lazy mx-auto d-block img-responsive loaded" data-was-processed="true">
                                            </div>
                                            <div class="title2">
                                                Để được hỗ trợ tốt nhất. Hãy gọi
                                            </div>
                                            <div class="phone">
                                                <a href="tel:0364313062" title="0364 313 062">0364 313 062</a>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-6 col-xl-12">
                                        <div class="chinhsach-pro">
                                            <div class="item">
                                                <img width="40" height="40" src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/chinhsach_1.png?1720274480928" alt="Miễn phí vẫn chuyển">
                                                <div class="text">
                                                    <span class="title">Miễn phí vẫn chuyển</span>
                                                    <span class="des">Cho tất cả đơn hàng trong nội thành Hà Nội</span>
                                                </div>
                                            </div>
                                            <div class="item">
                                                <img width="40" height="40" src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/chinhsach_2.png?1720274480928" alt="Miễn phí đổi - trả">
                                                <div class="text">
                                                    <span class="title">Miễn phí đổi - trả</span>
                                                    <span class="des">Đối với sản phẩm lỗi sản xuất hoặc vận chuyển</span>
                                                </div>
                                            </div>
                                            <div class="item">
                                                <img width="40" height="40" src="//bizweb.dktcdn.net/100/491/197/themes/917410/assets/chinhsach_3.png?1720274480928" alt="Hỗ trợ nhanh chóng">
                                                <div class="text">
                                                    <span class="title">Hỗ trợ nhanh chóng</span>
                                                    <span class="des">Gọi Hotline: 0364 313 062 để được hỗ trợ ngay lập tức</span>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <div class="col-12 col-md-8 col-lg-8 col-xl-8 ">
                                <div class="product-tab e-tabs not-dqtab" id="tab-product">
                                    <ul class="tabs tabs-title clearfix">
                                        <li class="tab-link active" data-tab="#tab-1">
                                            <h3><a href="javascript:void(0)">Mô tả sản phẩm</a></h3>
                                        </li>
                                        <li class="tab-link" data-tab="#tab-2">
                                            <h3><a href="javascript:void(0)">Hướng dẫn mua hàng</a></h3>
                                        </li>
                                    </ul>
                                    <div class="tab-float">
                                        <div id="tab-1" class="tab-content active content_extab">
                                            <div class="product_getcontent-wrapper">
                                                <div class="rte product_getcontent">
                                                    <div class="ba-text-fpt has-height">
                                                        <figure><img src="<?php echo 'admin/admin/' . htmlspecialchars($product['background_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>"></figure>
                                                        <h3>Thông tin sản phẩm</h3>
                                                        <p class="pro-info"> <?php echo html_entity_decode($product['product_info']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="show-more">
                                                    <a href="javascript:void(0)" class="btn btn-default more-text see-more">Xem thêm <i class="fas fa-chevron-down"></i></a>
                                                    <a href="javascript:void(0)" class="btn btn-default less-text see-more" style="display: none;">Thu gọn <i class="fas fa-chevron-up"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="tab-2" class="tab-content content_extab">
                                            <div class="rte">
                                                <p><strong>Bước 1:</strong>&nbsp;Truy cập website và lựa chọn sản phẩm&nbsp;cần mua</p>
                                                <p><strong>Bước 2:</strong>&nbsp;Click và sản phẩm muốn mua, màn hình hiển thị ra pop up với các lựa chọn sau</p>
                                                <p>Nếu bạn muốn tiếp tục mua hàng: Bấm vào phần tiếp tục mua hàng để lựa chọn thêm sản phẩm vào giỏ hàng</p>
                                                <p>Nếu bạn muốn xem giỏ hàng để cập nhật sản phẩm: Bấm vào xem giỏ hàng</p>
                                                <p>Nếu bạn muốn đặt hàng và thanh toán cho sản phẩm này vui lòng bấm vào: Đặt hàng và thanh toán</p>
                                                <p><strong>Bước 3:</strong>&nbsp;Lựa chọn thông tin tài khoản thanh toán</p>
                                                <p>Nếu bạn đã có tài khoản vui lòng nhập thông tin tên đăng nhập là email và mật khẩu vào mục đã có tài khoản trên hệ thống</p>
                                                <p>Nếu bạn chưa có tài khoản và muốn đăng ký tài khoản vui lòng điền các thông tin cá nhân để tiếp tục đăng ký tài khoản. Khi có tài khoản bạn sẽ dễ dàng theo dõi được đơn hàng của mình</p>
                                                <p>Nếu bạn muốn mua hàng mà không cần tài khoản vui lòng nhấp chuột vào mục đặt hàng không cần tài khoản</p>
                                                <p><strong>Bước 4:</strong>&nbsp;Điền các thông tin của bạn để nhận đơn hàng, lựa chọn hình thức thanh toán và vận chuyển cho đơn hàng của mình</p>
                                                <p><strong>Bước 5:</strong>&nbsp;Xem lại thông tin đặt hàng, điền chú thích và gửi đơn hàng</p>
                                                <p>Sau khi nhận được đơn hàng bạn gửi chúng tôi sẽ liên hệ bằng cách gọi điện lại để xác nhận lại đơn hàng và địa chỉ của bạn.</p>
                                                <p>Trân trọng cảm ơn.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-4 col-xl-4 config-tb-container">
                                <div class="config-tb">
                                    <div class="container">
                                        <h3 class="title">Thông số kỹ thuật</h3>
                                        <?php
                                        // Truy vấn bảng product_configurations để lấy thông số kỹ thuật
                                        $configQuery = "SELECT config_name, config_value FROM product_configurations WHERE product_id = $product_id";
                                        $configResult = mysqli_query($conn, $configQuery);

                                        // Hiển thị thông số kỹ thuật dưới dạng bảng với Bootstrap
                                        if ($configResult && mysqli_num_rows($configResult) > 0) {
                                            echo "<table class='table table-bordered table-striped shadow-sm'>";
                                            echo "<thead class='table-light'></thead>";
                                            echo "<tbody>";

                                            while ($configRow = mysqli_fetch_assoc($configResult)) {
                                                echo "<tr>";
                                                echo "<td>{$configRow['config_name']}</td>";
                                                echo "<td>{$configRow['config_value']}</td>";
                                                echo "</tr>";
                                            }

                                            echo "</tbody></table>";
                                        } else {
                                            echo "<p><strong></strong> Không có dữ liệu</p>";
                                        }
                                        ?>
                                    </div>
                                    <div class="fade-effect"></div> <!-- Thêm lớp phủ mờ -->
                                    <button class="show-more2" id="showMoreBtn2">Xem toàn bộ thông số <i class="fa-solid fa-angles-down"></i></button>
                                </div>
                            </div>
                        </div>



                        <div class="comment-box">
                            <div class="container mt-5">
                                <!-- Hiển thị tóm tắt số sao -->
                                <div class="header-comment">
                                    <h5 class="mb-4 reviews-title">ĐÁNH GIÁ SẢN PHẨM</h5>
                                    <div class="rating-summary mb-4">
                                        <strong><?php echo $averageRating; ?> trên 5</strong>

                                        <div class="stars3">
                                            <?php
                                            // Số sao đầy đủ
                                            $fullStars = floor($averageRating);
                                            // Phần sao không đầy đủ (làm tròn)
                                            $halfStar = ($averageRating - $fullStars) >= 0.5 ? 1 : 0;
                                            // Tính số sao trống
                                            $emptyStars = 5 - ($fullStars + $halfStar);

                                            // Vẽ ngôi sao đầy đủ
                                            for ($i = 0; $i < $fullStars; $i++) {
                                                echo '<span class="star3 full"></span>';
                                            }

                                            // Vẽ ngôi sao một phần
                                            if ($halfStar) {
                                                echo '<span class="star3 half"></span>';
                                            }

                                            // Vẽ ngôi sao trống
                                            for ($i = 0; $i < $emptyStars; $i++) {
                                                echo '<span class="star3 empty"></span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <!-- Bộ lọc các đánh giá theo số sao -->
                                    <div class="rating-filters mb-4">
                                        <button class="btn btn-outline-primary active" onclick="filterReviews(0)">Tất cả (<?php echo array_sum($ratingCounts); ?>)</button>
                                        <button class="btn btn-outline-primary" onclick="filterReviews(5)">5 sao (<?php echo $ratingCounts[5]; ?>)</button>
                                        <button class="btn btn-outline-primary" onclick="filterReviews(4)">4 sao (<?php echo $ratingCounts[4]; ?>)</button>
                                        <button class="btn btn-outline-primary" onclick="filterReviews(3)">3 sao (<?php echo $ratingCounts[3]; ?>)</button>
                                        <button class="btn btn-outline-primary" onclick="filterReviews(2)">2 sao (<?php echo $ratingCounts[2]; ?>)</button>
                                        <button class="btn btn-outline-primary" onclick="filterReviews(1)">1 sao (<?php echo $ratingCounts[1]; ?>)</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 reviews" id="reviewsList">
                                        <div class="comment" id="reviewsContainer">
                                            <?php if ($reviewsResult->num_rows > 0) : ?>
                                                <?php while ($row = $reviewsResult->fetch_assoc()) : ?>
                                                    <div class="card mb-3 review-card" data-rating="<?php echo $row['rating']; ?>">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-2 col-2">
                                                                    <img width="45px" src="assets/img/imgusers.png" alt="">
                                                                </div>
                                                                <div class="col">
                                                                    <!-- Hiển thị tên và ngày giờ -->
                                                                    <p class="review-header">
                                                                        <strong><?php echo htmlspecialchars($row['full_name']); ?></strong> |
                                                                        <span class="text-muted small">
                                                                            <?php echo date('d-m-Y, H:i:s', strtotime($row['created_at'])); ?>
                                                                        </span>
                                                                    </p>

                                                                    <!-- Hiển thị đánh giá sao -->
                                                                    <div class="star2-rating mb-1">
                                                                        <?php
                                                                        $rating = (int)$row['rating'];
                                                                        for ($i = 1; $i <= 5; $i++) {
                                                                            if ($i <= $rating) {
                                                                                echo '<span class="star2 full"></span>'; // Sao đầy
                                                                            } else {
                                                                                echo '<span class="star2 empty"></span>'; // Sao trống
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </div>

                                                                    <!-- Hiển thị bình luận -->
                                                                    <p class="card-text"><?php echo htmlspecialchars($row['comment']); ?></p>

                                                                    <!-- Hiển thị ảnh nếu có -->
                                                                    <?php if ($row['image_path']) : ?>
                                                                        <img src="<?php echo htmlspecialchars($row['image_path']); ?>"
                                                                            alt="Ảnh sản phẩm" class="img-fluid mt-3" style="max-width: 150px;">
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            <?php else : ?>
                                                <p>Chưa có đánh giá cho sản phẩm này.</p>
                                            <?php endif; ?>
                                        </div>

                                    </div>

                                    <!-- Bình luận và đánh giá -->
                                    <div class="col-lg-6 review-form">
                                        <h5 class="mb-4">Bình luận và đánh giá</h5>
                                        <form id="reviewForm" enctype="multipart/form-data">
                                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">

                                            <!-- Đánh giá sao -->
                                            <div class="mb-3">
                                                <label for="rating" class="form-label">Số sao:</label>
                                                <div id="ratingStars" class="rating">
                                                    <!-- Ngôi sao với data-value từ 1 đến 5 -->
                                                    <span class="star empty" data-value="1"></span>
                                                    <span class="star empty" data-value="2"></span>
                                                    <span class="star empty" data-value="3"></span>
                                                    <span class="star empty" data-value="4"></span>
                                                    <span class="star empty" data-value="5"></span>
                                                </div>
                                                <input type="hidden" name="rating" id="rating" value="5" required>
                                            </div>

                                            <!-- Bình luận và ảnh -->
                                            <div class="mb-3">
                                                <label for="comment" class="form-label">Bình luận:</label>
                                                <textarea name="comment" id="comment" class="form-control" rows="4" required placeholder="Mời bạn nhập bình luận..."></textarea>
                                            </div>


                                            <div class="col-md-9 mb-3">
                                                <label for="image" class="form-label">Đính kèm ảnh:</label>
                                                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                            </div>

                                            <button type="submit" class="btn btn-primary">Gửi bình luận</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>









                        <div class="row mt-5">
                            <div class="similar-products">
                                <div class="container">
                                    <p class="title">Sản phẩm tương tự</p>
                                    <div class="swiper-container-similar-products">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($similarProducts as $similarProduct): ?>
                                                <div class="swiper-slide">
                                                    <div class="product-card">
                                                            <!-- Thẻ hình ảnh -->
                                                            <a href="/<?php echo $similarProduct['slug']; ?>">
                                                                <img src="<?php echo 'admin/admin/' . $similarProduct['background_image']; ?>" alt="<?php echo $similarProduct['product_name']; ?>">
                                                            </a>
                                                            <!-- Thẻ tên sản phẩm -->
                                                            <a href="/<?php echo $similarProduct['slug']; ?>">
                                                                <h5 class="product-link"><?php echo $similarProduct['product_name']; ?></h5>
                                                            </a>

                                                            <!-- Thẻ nút "Xem chi tiết" -->
                                                            <a href="/<?php echo $similarProduct['slug']; ?>" class="btn btn-primary">Xem chi tiết</a>
                                                        </div>

                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="swiper-button-next-2"><i class="fa fa-chevron-right"></i></div>
                                        <div class="swiper-button-prev-2"><i class="fa fa-chevron-left"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- Modal thông báo riêng cho phần bình luận -->
            <div class="modal fade custom-comment-modal" id="commentNotificationModal" tabindex="-1" aria-labelledby="commentNotificationModalLabel" aria-hidden="true">
                <div class="modal-dialog custom-comment-dialog">
                    <div class="modal-content custom-comment-content">
                        <div class="modal-header custom-comment-header">
                            <h5 class="modal-title custom-comment-title" id="commentNotificationModalLabel">Thông báo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body custom-comment-body" id="commentNotificationMessage">
                            <!-- Nội dung thông báo sẽ được chèn vào đây -->
                        </div>
                        <div class="modal-footer custom-comment-footer">
                            <a href="login.php" class="btn custom-comment-login-btn"><i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập</a>
                            <button type="button" class="btn custom-comment-close-btn" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
    </section>
    <?php
    include_once 'footer.php';
    ?>
    <!-- Thêm Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <!-- Thêm Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>

    <script>
        var galleryThumbs = new Swiper('.gallery-thumbs', {
            spaceBetween: 10,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesVisibility: true,
            watchSlidesProgress: true,
        });
        var galleryTop = new Swiper('.gallery-top', {
            spaceBetween: 10,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            thumbs: {
                swiper: galleryThumbs,
            },
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy tất cả các tab
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            // Lặp qua các tab và thêm sự kiện click
            tabLinks.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    // Bỏ class active khỏi tất cả các tab và nội dung
                    tabLinks.forEach(function(tab) {
                        tab.classList.remove('active');
                    });
                    tabContents.forEach(function(content) {
                        content.classList.remove('active');
                    });

                    // Thêm class active vào tab hiện tại
                    this.classList.add('active');

                    // Hiển thị nội dung của tab tương ứng
                    const contentId = this.getAttribute('data-tab');
                    document.querySelector(contentId).classList.add('active');
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const showMoreButtons = document.querySelectorAll('.see-more');

            showMoreButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const content = this.closest('.show-more').previousElementSibling;
                    const moreText = this.parentElement.querySelector('.more-text');
                    const lessText = this.parentElement.querySelector('.less-text');

                    // Toggle nội dung ẩn
                    if (content.style.display === 'block' || content.style.maxHeight) {
                        content.style.display = 'none'; // Thu gọn nội dung
                        moreText.style.display = 'inline'; // Hiển thị nút "Xem thêm"
                        lessText.style.display = 'none'; // Ẩn nút "Thu gọn"
                    } else {
                        content.style.display = 'block'; // Mở rộng nội dung
                        moreText.style.display = 'none'; // Ẩn nút "Xem thêm"
                        lessText.style.display = 'inline'; // Hiển thị nút "Thu gọn"
                    }
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const moreText = document.querySelector('.more-text');
            const lessText = document.querySelector('.less-text');
            const productContent = document.querySelector('.product_getcontent');

            moreText.addEventListener('click', function() {
                productContent.classList.add('expanded');
                moreText.style.display = 'none'; // Ẩn nút "Xem thêm"
                lessText.style.display = 'inline-block'; // Hiển thị nút "Thu gọn"
            });

            lessText.addEventListener('click', function() {
                productContent.classList.remove('expanded');
                moreText.style.display = 'inline-block'; // Hiển thị nút "Xem thêm"
                lessText.style.display = 'none'; // Ẩn nút "Thu gọn"
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Hàm kiểm tra tính hợp lệ cho số lượng
            function validateQuantity(quantity) {
                if (isNaN(quantity) || quantity < 1) {
                    return false;
                }
                return true;
            }

            $('#quantity-increase').click(function() {
                var $quantityInput = $('#quantity');
                var currentValue = parseInt($quantityInput.val(), 10);
                $quantityInput.val(currentValue + 1);
            });

            $('#quantity-decrease').click(function() {
                var $quantityInput = $('#quantity');
                var currentValue = parseInt($quantityInput.val(), 10);
                if (currentValue > 1) {
                    $quantityInput.val(currentValue - 1);
                }
            });

            $('#quantity').on('input', function() {
                var $quantityInput = $(this);
                var value = parseInt($quantityInput.val(), 10);

                // Nếu không hợp lệ (như giá trị âm hoặc không phải số), đặt về giá trị mặc định là 1
                if (!validateQuantity(value)) {
                    $quantityInput.val(1);
                }
            });
        });



        function showModal() {
            // Hiển thị modal
            $('#successModal').modal('show');

            // Đặt hẹn giờ để tự động đóng modal sau 2 giây
            setTimeout(function() {
                $('#successModal').modal('hide');
            }, 2000); // 2 giây
        }

        function addToCart(productId) {
            var quantity = parseInt($('#quantity').val(), 10); // Lấy số lượng từ ô nhập liệu
            // Gửi yêu cầu Ajax để thêm sản phẩm vào giỏ
            $.ajax({
                url: 'add_to_cart.php', // Tệp PHP xử lý thêm sản phẩm vào giỏ
                type: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    var modalBody = $('#cartModalMessage').parent(); // Lấy phần thân của modal
                    $('#cartModalMessage').text(result.message); // Hiển thị thông báo

                    // Thay đổi màu sắc modal dựa trên trạng thái
                    if (result.status === 'success') {
                        modalBody.removeClass('error').addClass('success');
                    } else {
                        modalBody.removeClass('success').addClass('error');
                    }

                    $('#cartModal').modal('show'); // Hiển thị modal
                    updateCartCount(); // Cập nhật số lượng giỏ hàng
                },
                error: function() {
                    var modalBody = $('#cartModalMessage').parent();
                    $('#cartModalMessage').text('Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.'); // Thông báo lỗi
                    modalBody.removeClass('success').addClass('error'); // Đặt màu của lỗi
                    $('#cartModal').modal('show'); // Hiển thị modal
                }
            });
        }
    </script>
    <script>
        const swiper10 = new Swiper('.swiper-container-similar-products', {
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next-2',
                prevEl: '.swiper-button-prev-2',
            },

            breakpoints: {
                // Khi màn hình có kích thước nhỏ hơn hoặc bằng 640px
                640: {
                    slidesPerView: 1, // Hiển thị 1 slide
                },
                // Khi màn hình có kích thước nhỏ hơn hoặc bằng 768px
                768: {
                    slidesPerView: 2, // Hiển thị 2 slide
                    spaceBetween: 15, // Khoảng cách giữa các slide
                },
                // Khi màn hình có kích thước lớn hơn hoặc bằng 768px
                1024: {
                    slidesPerView: 3, // Hiển thị 3 slide
                    spaceBetween: 15, // Khoảng cách giữa các slide
                },
                // Khi màn hình lớn hơn hoặc bằng 1200px
                1200: {
                    slidesPerView: 3, // Hiển thị 4 slide
                    spaceBetween: 15, // Khoảng cách giữa các slide
                }
            }
        });
    </script>
    <script>
        document.getElementById('showMoreBtn2').addEventListener('click', function() {
            var container = document.querySelector('.config-tb-container');
            container.classList.toggle('expanded');

            var btn = document.getElementById('showMoreBtn2');
            if (container.classList.contains('expanded')) {
                btn.innerHTML = 'Thu gọn <i class="fa-solid fa-angles-up"></i>'; // Đổi sang tam giác bình thường
            } else {
                btn.innerHTML = 'Xem toàn bộ thông số <i class="fa-solid fa-angles-down"></i>'; // Đổi sang tam giác ngược
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating');

            // Đặt giá trị mặc định cho rating nếu chưa có giá trị
            if (!ratingInput.value) {
                ratingInput.value = 5; // Đặt giá trị mặc định là 5 sao nếu chưa chọn sao
            }

            // Cập nhật sao hiển thị theo giá trị rating ban đầu
            let currentRating = ratingInput.value;
            updateStars(currentRating);

            // Cập nhật sao khi người dùng hover qua các sao
            stars.forEach(star => {
                star.addEventListener('mouseenter', function() {
                    const value = this.getAttribute('data-value');
                    updateStars(value); // Tạm thời tô sao khi hover
                });

                star.addEventListener('mouseleave', function() {
                    updateStars(currentRating); // Quay lại sao hiện tại khi di chuột ra
                });

                star.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    currentRating = value; // Cập nhật giá trị sao khi click
                    ratingInput.value = value; // Lưu giá trị vào input ẩn
                    updateStars(value); // Cập nhật sao khi click
                });
            });

            // Cập nhật sao khi hover hoặc click
            function updateStars(value) {
                stars.forEach(star => {
                    if (star.getAttribute('data-value') <= value) {
                        star.classList.add('full');
                        star.classList.remove('empty');
                    } else {
                        star.classList.remove('full');
                        star.classList.add('empty');
                    }
                });
            }
        });





        $(document).ready(function() {
            // Xử lý khi người dùng chọn sao
            $("#ratingStars .star").on("click", function() {
                var rating = $(this).data("value"); // Lấy giá trị rating từ data-value
                $("#rating").val(rating); // Cập nhật giá trị vào trường input ẩn
                updateStars(rating); // Cập nhật giao diện sao
            });

            // Cập nhật giao diện sao khi người dùng chọn
            function updateStars(rating) {
                $("#ratingStars .star").each(function() {
                    var starValue = $(this).data("value");
                    if (starValue <= rating) {
                        $(this).removeClass("empty").addClass("full");
                    } else {
                        $(this).removeClass("full").addClass("empty");
                    }
                });
            }

            // Khi form được submit
            $("#reviewForm").on("submit", function(e) {
                e.preventDefault(); // Ngăn việc reload lại trang

                var formData = new FormData(this); // Thu thập dữ liệu từ form

                $.ajax({
                    url: 'submit_review.php', // Tới file xử lý
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        var data = JSON.parse(response);

                        if (data.success) {
                            var newReview = `
        <div class="card mb-3 review-card">
            <div class="card-body">
                <h6 class="card-title">
                    <img width="45px" src="assets/img/imgusers.png" alt="User" class="me-2">
                    ${data.review.full_name} | Ngày ${data.review.created_at}
                </h6>
                <div class="star2-rating mb-1">`;

                            for (var i = 1; i <= 5; i++) {
                                newReview += `<span class="star2 ${i <= data.review.rating ? 'full' : 'empty'}"></span>`;
                            }

                            newReview += `
                </div>
                <p class="card-text">${data.review.comment}</p>
                ${data.review.image ? '<img src="' + data.review.image + '" alt="Ảnh sản phẩm" class="img-fluid mt-3" style="max-width: 150px;">' : ''}
            </div>
        </div>`;

                            $("#reviewsList").prepend(newReview);
                            $("#reviewForm")[0].reset(); // Reset form
                            updateStars(5); // Reset lại ngôi sao (trả về 5 sao mặc định)


                        } else {
                            // Hiển thị thông báo lỗi bằng modal
                            $("#commentNotificationMessage").text("Bạn cần đăng nhập để được bình luận.");
                            var commentModal = new bootstrap.Modal(document.getElementById('commentNotificationModal'));
                            commentModal.show();
                        }
                    },
                    error: function() {
                        // Hiển thị thông báo lỗi bằng modal
                        $("#commentNotificationMessage").text("Có lỗi xảy ra trong quá trình gửi dữ liệu.");
                        var commentModal = new bootstrap.Modal(document.getElementById('commentNotificationModal'));
                        commentModal.show();
                    }
                });
            });
        });



        function filterReviews(rating) {
            // Hiển thị tất cả đánh giá nếu chọn "Tất cả"
            if (rating === 0) {
                $('.review-card').show();
            } else {
                // Ẩn tất cả các đánh giá
                $('.review-card').hide();
                // Chỉ hiển thị những đánh giá có rating tương ứng
                $('.review-card[data-rating="' + rating + '"]').show();
            }

            // Xóa lớp active khỏi tất cả các nút lọc
            $(".rating-filters .btn").removeClass("active");
            // Thêm lớp active cho nút hiện tại
            $(".rating-filters .btn").each(function() {
                if ($(this).attr("onclick") === `filterReviews(${rating})`) {
                    $(this).addClass("active");
                }
            });
        }
    </script>

    <script>
    Fancybox.bind("[data-fancybox='gallery']", {
        Toolbar: {
        display: ["zoom", "close"], // Hiển thị nút phóng to và thoát
        },
        Zoom: {
        maxScale: 2, // Tỷ lệ phóng to tối đa (có thể tăng lên 3 hoặc hơn nếu ảnh lớn)
        },
        Thumbs: {
        autoStart: true, // Hiển thị thumbnail
        },
        Image: {
        zoom: true, // Bật chế độ zoom ảnh
        },
    });
    </script>




</body>

</html>