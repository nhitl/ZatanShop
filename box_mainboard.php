<?php
include_once 'dbconnect.php';

// Kiểm tra nếu có yêu cầu AJAX
if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == 1) {
    $subcategory_id = isset($_POST['subcategory_id']) ? $_POST['subcategory_id'] : null;

    if ($subcategory_id) {
        // Truy vấn sản phẩm kết hợp với bảng discounts
        $product_query = "
        SELECT p.*, d.discount_percentage 
        FROM products p
        LEFT JOIN discounts d ON p.product_id = d.product_id 
        WHERE p.subcategory_id = $subcategory_id 
        LIMIT 4";

        $product_result = $conn->query($product_query);

        // Bắt đầu xây dựng HTML trả về
        $html = '';

        if ($product_result->num_rows > 0) {
            while ($product = $product_result->fetch_assoc()) {
                $price = $product['price'];
                $discounted_price = null;
                if ($product['discount_percentage']) {
                    $discounted_price = $price - ($price * $product['discount_percentage'] / 100);
                }

                // Xây dựng HTML cho sản phẩm
                $html .= '
<div class="col-md-6 col-lg-3 col-12 mb-4 animate__animated animate__fadeIn">
    <div class="card ">
    <div class="img-wrapper">
        <a href="product_detail.php?id=' . $product['product_id'] . '">
            <img src="admin' . $product['background_image'] . '" class="card-img-top" alt="' . $product['product_name'] . '">
        </a>
        </div>
        <div class="card-body">
            <a href="product_detail.php?id=' . $product['product_id'] . '" title="' . htmlspecialchars($product['product_name']) . '">
                <h5 class="card-title">' . $product['product_name'] . '</h5>
            </a>';

                // Tính toán giá đã giảm
                $price = $product['price'];
                $discounted_price = null;
                if ($product['discount_percentage']) {
                    $discounted_price = $price - ($price * $product['discount_percentage'] / 100);
                }

                // Hiển thị giá gốc và giá đã giảm
                if ($discounted_price) {
                    $html .= '<p class="card-text">
                            <span class="original-price" style="text-decoration: line-through;">' . number_format($price, 0, ',', '.') . ' ₫</span><br>
                            <strong class="discounted-price">' . number_format($discounted_price, 0, ',', '.') . ' ₫</strong>
                          </p>';
                } else {
                    $html .= '<p class="card-text-price">' . number_format($price, 0, ',', '.') . ' ₫</p>';
                }

                // Gộp trạng thái hàng tồn kho và nút thêm vào giỏ hàng
                $html .= '<div class="d-flex justify-content-between align-items-center">';
                if ($product['stock_quantity'] > 0) {
                    $html .= '<span class="text-success"><i class="fa-regular fa-circle-check"></i> Còn hàng</span>';
                } else {
                    $html .= '<span class="text-danger"><i class="fa-regular fa-circle-xmark"></i> Hết hàng</span>';
                }

                $html .= '<a href="#" onclick="addToCart(' . htmlspecialchars($product['product_id']) . ', 1); return false;" class="btn btn-primary">
                        <i class="fa-sharp fa-solid fa-cart-plus"></i> 
                      </a>';
                $html .= '</div>'; // Kết thúc div d-flex

                $html .= '
        </div>
    </div>
</div>';
            }
        } else {
            $html = '<p class="text-center">Hiện tại chưa có sản phẩm nào trong danh mục này!...</p>';
        }

        // Trả về HTML trực tiếp
        echo $html;
    }
    exit;
}


// Lấy tên của category_id = 1 (ví dụ là Mainboard)
$category_id = 1;
$category_query = "SELECT * FROM categories WHERE category_id = $category_id";
$category_result = $conn->query($category_query);
$category = $category_result->fetch_assoc();

// Lấy danh sách các subcategories liên quan
$subcategory_query = "SELECT * FROM subcategories WHERE category_id = $category_id";
$subcategory_result = $conn->query($subcategory_query);

// Kiểm tra nếu có subcategory_id được bấm vào
$subcategory_id = isset($_GET['subcategory_id']) ? $_GET['subcategory_id'] : null;

// Nếu không có subcategory_id nào được chọn, lấy subcategory_id đầu tiên
if (!$subcategory_id) {
    $first_subcategory = $subcategory_result->fetch_assoc();
    $subcategory_id = $first_subcategory['subcategory_id'];
}


// Truy vấn sản phẩm kết hợp với bảng discounts
$product_query = "
SELECT p.*, d.discount_percentage 
FROM products p
LEFT JOIN discounts d ON p.product_id = d.product_id 
WHERE p.subcategory_id = $subcategory_id 
LIMIT 4";

$product_result = $conn->query($product_query);


?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="assets/css/main-board.css">
    <title>Danh mục sản phẩm - Mainboard</title>
</head>

<body>

    <?php include_once 'header.php'; ?>
    <section class="box-mainboard">
        <div class="container my-5">
            <h1 class="title1">Zatan Shop</h1>
            <h2 class="title2 mb-4"><?= $category['category_name'] ?></h2>

            <div class="text-center mb-4">
                <?php
                // Reset lại kết quả của $subcategory_result để dùng tiếp
                $subcategory_result->data_seek(0);
                $first_subcategory_active = true; // Biến để xác định nút đầu tiên có được active không
                while ($subcategory = $subcategory_result->fetch_assoc()) : ?>
                    <button class="btn btn-outline-primary mx-2 load-products <?= $first_subcategory_active ? 'active-mainboard' : '' ?>" data-id="<?= $subcategory['subcategory_id'] ?>">
                        <?= $subcategory['subcategory_name'] ?>
                    </button>
                    <?php $first_subcategory_active = false; // Đặt biến thành false sau khi đã thêm active cho nút đầu tiên 
                    ?>
                <?php endwhile; ?>
            </div>


            <!-- Vùng hiển thị sản phẩm -->
            <div class="row" id="product-list">
                <?php if ($product_result->num_rows > 0) : ?>
                    <?php while ($product = $product_result->fetch_assoc()) : ?>
                        <div class="col-md-6 col-lg-3 col-12 mb-4 animate__animated animate__fadeIn">
                            <div class="card">
                                <div class="img-wrapper">
                                    <a href="product-detail.php?id=<?= $product['product_id'] ?>">
                                        <img src="admin<?= $product['background_image'] ?>" class="card-img-top" alt="<?= $product['product_name'] ?>">
                                    </a>
                                </div>

                                <div class="card-body">
                                    <a href="product-detail.php?id=<?= $product['product_id'] ?>" title="<?= htmlspecialchars($product['product_name']) ?>">
                                        <h5 class="card-title"><?= $product['product_name'] ?></h5>
                                    </a>

                                    <?php
                                    // Tính toán giá đã giảm
                                    $price = $product['price'];
                                    $discounted_price = null;
                                    if ($product['discount_percentage']) {
                                        $discounted_price = $price - ($price * $product['discount_percentage'] / 100);
                                    }
                                    ?>

                                    <!-- Hiển thị giá gốc và giá đã giảm -->
                                    <?php if ($discounted_price) : ?>
                                        <p class="card-text">
                                            <span class="original-price" style="text-decoration: line-through;"><?= number_format($price, 0, ',', '.') ?> ₫</span>
                                            <br>
                                            <strong class="discounted-price"><?= number_format($discounted_price, 0, ',', '.') ?> ₫</strong>
                                        </p>
                                    <?php else : ?>
                                        <p class="card-text-price"><?= number_format($price, 0, ',', '.') ?> ₫</p>
                                    <?php endif; ?>


                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- Trạng thái hàng tồn kho -->
                                        <?php if ($product['stock_quantity'] > 0) : ?>
                                            <span class="text-success"><i class="fa-regular fa-circle-check"></i> Còn hàng</span>
                                        <?php else : ?>
                                            <span class="text-danger"><i class="fa-regular fa-circle-xmark"></i> Hết hàng</span>
                                        <?php endif; ?>

                                        <!-- Nút thêm vào giỏ hàng -->
                                        <a href="#" onclick="addToCart(<?= htmlspecialchars($product['product_id']) ?>, 1); return false;" class="btn btn-primary">
                                            <i class="fa-sharp fa-solid fa-cart-plus"></i>
                                        </a>
                                    </div>

                                </div>
                            </div>

                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p class="text-center">Hiện tại chưa có sản phẩm nào trong danh mục này!...</p>
                <?php endif; ?>
            </div>


            <!-- Nút xem tất cả -->
            <div class="text-center">
                <a href="all-products.php?category=1" class="btn btn-outline-primary"><i class="fa-solid fa-right-to-bracket"></i> Xem tất cả MainBoard</a>
            </div>
        </div>
    </section>

    <script>
        $(document).ready(function() {
            // Khi người dùng bấm vào nút subcategory
            $('.load-products').on('click', function() {
                var subcategory_id = $(this).data('id');

                // Gửi yêu cầu AJAX
                $.ajax({
                    url: 'box_mainboard.php', // URL đến trang xử lý
                    type: 'POST',
                    data: {
                        subcategory_id: subcategory_id,
                        is_ajax: 1
                    },
                    success: function(response) {
                        // Cập nhật phần tử #product-list với HTML mới
                        $('#product-list').html(response);

                        // Thêm class animate vào từng sản phẩm sau khi nội dung được tải
                        $('#product-list .col-md-6').addClass('animate__animated animate__fadeIn');
                    },
                    error: function(xhr, status, error) {
                        console.error("Lỗi AJAX: " + error);
                    }
                });
            });
        });

        $(document).ready(function() {
            // Khi người dùng bấm vào nút subcategory
            $('.load-products').on('click', function() {
                // Xóa lớp active khỏi tất cả các nút
                $('.load-products').removeClass('active-mainboard');

                // Thêm lớp active vào nút được nhấn
                $(this).addClass('active-mainboard');

                var subcategory_id = $(this).data('id');

                // Gửi yêu cầu AJAX đến chính trang này
                $.ajax({
                    url: 'box_mainboard.php', // Để trống để gửi đến chính trang hiện tại
                    type: 'POST',
                    data: {
                        subcategory_id: subcategory_id,
                        is_ajax: 1
                    },
                    success: function(response) {
                        // Cập nhật HTML trả về trực tiếp vào phần product-list
                        $('#product-list').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Lỗi AJAX: " + error);
                    }
                });
            });
        });
    </script>

</body>

</html>