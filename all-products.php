<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Male_Fashion Template">
    <meta name="keywords" content="Male_Fashion, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Zatan Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Header Section Begin -->
    <?php 
    include_once 'header.php'; 
    include_once 'contact_button.php';
    ?>
    <!-- Header Section End -->
    <section class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tất cả sản phẩm</li>
                </ol>
            </nav>
        </div>
    </section>
    <!-- Shop Section Begin -->
    <section class="shop-spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-12 siderbar-products">
                    <div class="shop__sidebar">
                        <div class="shop__sidebar__search mb-4">
                            <form action="#" class="search-form">
                                <input type="text" name="search" class="form-control search-input" placeholder="Tìm kiếm...">
                                <button type="submit" class="btn btn-primary search-btn">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </form>
                        </div>

                        <div class="shop__sidebar__accordion">
                            <div class="accordion" id="accordionExample">
                                <!-- Phần danh mục -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                            <div class="card-heading">
                                                <a data-target="#collapseOne">Danh Mục</a>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne">
                                        <div class="accordion-body">
                                            <div class="shop__sidebar__categories">
                                                <ul class="nice-scroll">
                                                    <?php
                                                    include_once 'dbconnect.php';

                                                    // Truy vấn CSDL để lấy danh mục và danh mục con
                                                    $category_query = "SELECT c.category_id, c.category_name, sc.subcategory_id, sc.subcategory_name
                                FROM categories c
                                LEFT JOIN subcategories sc ON c.category_id = sc.category_id";
                                                    $category_result = mysqli_query($conn, $category_query);

                                                    // Tạo một mảng để lưu danh mục và danh mục con
                                                    $categories = [];
                                                    while ($row = mysqli_fetch_assoc($category_result)) {
                                                        $categories[$row['category_id']]['name'] = $row['category_name'];
                                                        if ($row['subcategory_id']) {
                                                            $categories[$row['category_id']]['subcategories'][] = [
                                                                'id' => $row['subcategory_id'],
                                                                'name' => $row['subcategory_name']
                                                            ];
                                                        }
                                                    }

                                                    // Hiển thị danh mục và danh mục con
                                                    foreach ($categories as $category_id => $category) {
                                                        echo '<li class="danhmuc"><a href="?category=' . $category_id . '">' . $category['name'] . '</a>';
                                                        if (!empty($category['subcategories'])) {
                                                            echo '<ul class="subcategories">';
                                                            foreach ($category['subcategories'] as $subcategory) {
                                                                echo '<li class="li-subcategories"><a href="?subcategory=' . $subcategory['id'] . '">' . $subcategory['name'] . '</a></li>';
                                                            }
                                                            echo '</ul>';
                                                        }
                                                        echo '</li>';
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Phần thương hiệu -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            <div class="card-heading">
                                                <a data-target="#collapseOne">Thương Hiệu</a>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo">
                                        <div class="accordion-body">
                                            <div class="shop__sidebar__brand">
                                                <ul>
                                                    <?php
                                                    // Truy vấn CSDL để lấy thương hiệu
                                                    $brand_query = "SELECT brand_id, brand_name FROM brands";
                                                    $brand_result = mysqli_query($conn, $brand_query);

                                                    // Kiểm tra và hiển thị thương hiệu nếu có dữ liệu
                                                    if ($brand_result && mysqli_num_rows($brand_result) > 0) {
                                                        while ($brand = mysqli_fetch_assoc($brand_result)) {
                                                            echo '<li class="brand-item"><a href="?brand=' . $brand['brand_id'] . '">' . $brand['brand_name'] . '</a></li>';
                                                        }
                                                    } else {
                                                        echo '<li class="brand-item">Không có thương hiệu nào.</li>';
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-lg-9 col-md-9 col-12 list-prouduct">
                    <div class="button-sort">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_asc'])); ?>" class="btn btn-outline me-2"><i class="fa-solid fa-arrow-up-wide-short"></i> Giá thấp đến cao</a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'price_desc'])); ?>" class="btn btn-outline me-2"><i class="fa-solid fa-arrow-down-short-wide"></i> Giá cao đến thấp</a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'sales_desc'])); ?>" class="btn btn-outline">Sản phẩm bán chạy <i class="fa-solid fa-fire"></i></a>
                    </div>

                    <?php
                    $filter_message = '';
                    $total_filtered_products = 0;

                    // Lấy các tham số lọc và sắp xếp từ URL
                    $selected_category = isset($_GET['category']) ? $_GET['category'] : '';
                    $selected_subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : '';
                    $selected_brand = isset($_GET['brand']) ? $_GET['brand'] : '';
                    $search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                    $sort_option = isset($_GET['sort']) ? $_GET['sort'] : '';

                    // Lấy tên danh mục từ ID
                    $category_name = '';
                    if ($selected_category) {
                        $category_query = "SELECT category_name FROM categories WHERE category_id = $selected_category";
                        $category_result = mysqli_query($conn, $category_query);
                        if ($category_result) {
                            $category_row = mysqli_fetch_assoc($category_result);
                            $category_name = $category_row['category_name'];
                        }
                    }

                    // Lấy tên danh mục con từ ID
                    $subcategory_name = '';
                    if ($selected_subcategory) {
                        $subcategory_query = "SELECT subcategory_name FROM subcategories WHERE subcategory_id = $selected_subcategory";
                        $subcategory_result = mysqli_query($conn, $subcategory_query);
                        if ($subcategory_result) {
                            $subcategory_row = mysqli_fetch_assoc($subcategory_result);
                            $subcategory_name = $subcategory_row['subcategory_name'];
                        }
                    }

                    // Lấy tên thương hiệu từ ID
                    $brand_name = '';
                    if ($selected_brand) {
                        $brand_query = "SELECT brand_name FROM brands WHERE brand_id = $selected_brand";
                        $brand_result = mysqli_query($conn, $brand_query);
                        if ($brand_result) {
                            $brand_row = mysqli_fetch_assoc($brand_result);
                            $brand_name = $brand_row['brand_name'];
                        }
                    }

                    // Tạo thông báo lọc dựa trên các tham số lọc
                    $filter_message = 'Tất cả sản phẩm';

                    // Thêm thông tin về danh mục nếu có
                    if ($category_name) {
                        $filter_message .= ' trong danh mục: ' . htmlspecialchars($category_name);
                    }

                    // Thêm thông tin về danh mục con nếu có
                    if ($subcategory_name) {
                        $filter_message .= ' trong danh mục: ' . htmlspecialchars($subcategory_name);
                    }

                    // Thêm thông tin về thương hiệu nếu có
                    if ($brand_name) {
                        $filter_message .= ' của thương hiệu: ' . htmlspecialchars($brand_name);
                    }

                    // Thêm thông tin về tìm kiếm nếu có
                    if ($search_term) {
                        $filter_message .= ' tìm kiếm với từ khóa: ' . htmlspecialchars($search_term);
                    }

                    // Truy vấn đếm số lượng sản phẩm đã lọc
                    $count_query = "SELECT COUNT(*) AS total_filtered_products FROM products p WHERE 1=1";

                    // Thêm điều kiện cho danh mục nếu có
                    if ($selected_category) {
                        $count_query .= " AND p.category_id = $selected_category";
                    }

                    // Thêm điều kiện cho danh mục con nếu có
                    if ($selected_subcategory) {
                        $count_query .= " AND p.subcategory_id = $selected_subcategory";
                    }

                    // Thêm điều kiện cho thương hiệu nếu có
                    if ($selected_brand) {
                        $count_query .= " AND p.brand_id = $selected_brand";
                    }

                    // Thêm điều kiện tìm kiếm nếu có
                    if ($search_term) {
                        $count_query .= " AND p.product_name LIKE '%$search_term%'";
                    }

                    // Thực hiện truy vấn đếm số lượng sản phẩm
                    $count_result = mysqli_query($conn, $count_query);
                    if ($count_result) {
                        $total_filtered_products = mysqli_fetch_assoc($count_result)['total_filtered_products'];
                    }

                    // Hiển thị thông báo lọc và số lượng sản phẩm nếu có
                    if ($filter_message != '') {
                        echo '<div class="filter-message">' . $filter_message . ' (' . $total_filtered_products . ' sản phẩm)</div>';
                    }

                    // Số sản phẩm trên mỗi trang
                    $productsPerPage = 12;

                    // Trang hiện tại (mặc định là 1 nếu không có giá trị)
                    $current_page = isset($_GET['page']) ? $_GET['page'] : 1;

                    // Bắt đầu từ vị trí của sản phẩm trong truy vấn
                    $start_from = ($current_page - 1) * $productsPerPage;

                    // Truy vấn CSDL để lấy thông tin sản phẩm, danh mục và thương hiệu và sắp xếp
                    $query = "SELECT p.product_id, p.product_name, p.price, p.background_image, b.brand_name, p.stock_quantity
                        FROM products p 
                        JOIN brands b ON p.brand_id = b.brand_id 
                        WHERE 1=1"; // WHERE 1=1 để thêm điều kiện một cách dễ dàng


                    // Thêm điều kiện cho danh mục nếu có
                    if ($selected_category) {
                        $query .= " AND p.category_id = $selected_category";
                    }

                    // Thêm điều kiện cho danh mục con nếu có
                    if ($selected_subcategory) {
                        $query .= " AND p.subcategory_id = $selected_subcategory";
                    }

                    // Thêm điều kiện cho thương hiệu nếu có
                    if ($selected_brand) {
                        $query .= " AND p.brand_id = $selected_brand";
                    }

                    // Thêm điều kiện tìm kiếm nếu có
                    if ($search_term) {
                        $query .= " AND p.product_name LIKE '%$search_term%'";
                    }

                    // Thêm sắp xếp và giới hạn kết quả
                    if ($sort_option == 'price_asc') {
                        $query .= " ORDER BY p.price ASC";
                    } elseif ($sort_option == 'price_desc') {
                        $query .= " ORDER BY p.price DESC";
                    } elseif ($sort_option == 'sales_desc') {
                        $query .= " ORDER BY p.sales_count DESC";
                    } else {
                        // Mặc định sắp xếp theo ID giảm dần
                        $query .= " ORDER BY p.product_id DESC";
                    }

                    $query .= " LIMIT $start_from, $productsPerPage";

                    $result = mysqli_query($conn, $query);

                    if ($result) {
                        if ($result->num_rows > 0) {
                            echo '<div class="row product__filter">';
                            // Lặp qua từng sản phẩm và hiển thị
                            while ($row = $result->fetch_assoc()) {
                                // Tạo đường dẫn đầy đủ đến hình ảnh
                                $imagePath = 'admin' . $row["background_image"];

                                // Tách tên thương hiệu thành các ký tự riêng lẻ và bao bọc chúng trong thẻ <span>
                                $brand_name_spans = '';
                                foreach (str_split($row['brand_name']) as $char) {
                                    $brand_name_spans .= '<span class="brand-char">' . htmlspecialchars($char) . '</span>';
                                }

                                // Lấy thông tin giảm giá từ bảng discounts
                                $discount_percentage = 0; // Khởi tạo phần trăm giảm giá
                                $current_date = date('Y-m-d'); // Ngày hiện tại
                                $discount_query = "SELECT discount_percentage FROM discounts WHERE product_id = " . $row["product_id"] . " AND start_date <= '$current_date' AND end_date >= '$current_date'";
                                $discount_result = $conn->query($discount_query);

                                if ($discount_result && $discount_result->num_rows > 0) {
                                    $discount_row = $discount_result->fetch_assoc();
                                    $discount_percentage = $discount_row["discount_percentage"];
                                }

                                // Tính giá sau giảm giá
                                $original_price = $row["price"];
                                $discounted_price = $original_price * (1 - ($discount_percentage / 100));

                                echo '<div class="col-lg-4 col-md-6 col-6">';
                                echo '    <div class="card product-all mb-4">';
                                echo '        <div class="img-container promo-container">';
                                echo '            <a href="product-detail.php?id=' . $row["product_id"] . '">';
                                echo '                <img src="' . $imagePath . '" class="card-img-top product-img" alt="' . htmlspecialchars($row["product_name"]) . '"';
                                echo '                     data-name="' . htmlspecialchars($row["product_name"]) . '"';
                                echo '                     data-price="' . number_format($discounted_price) . '"';
                                echo '                     data-bs-toggle="modal" data-bs-target="#productModal">';
                                echo '            </a>';

                                // Kiểm tra và hiển thị biểu tượng khuyến mãi nếu có khuyến mãi
                                $promo_query = "SELECT promotion_description FROM product_promotions WHERE product_id = " . $row["product_id"];
                                $promo_result = mysqli_query($conn, $promo_query);

                                if ($promo_result && mysqli_num_rows($promo_result) > 0) {
                                    // Thêm biểu tượng quà tặng phía trên bên phải
                                    echo '<div class="promo-icon-wrapper position-absolute top-0 end-0 p-2">';
                                    echo '    <div class="promo-icon-circle">';
                                    echo '        <i class="fa-solid fa-gift" title="Quà tặng"></i>'; // Biểu tượng quà tặng

                                    // Tạo một biến để lưu trữ tất cả mô tả khuyến mãi
                                    $promo_descriptions = [];

                                    // Lặp qua tất cả các khuyến mãi
                                    while ($promo_row = mysqli_fetch_assoc($promo_result)) {
                                        $promo_descriptions[] ='- ' . htmlspecialchars($promo_row['promotion_description']); // Lưu mô tả khuyến mãi vào mảng
                                    }

                                    // Nối tất cả mô tả lại thành một chuỗi, phân cách bằng dấu phẩy hoặc ký tự nào đó
                                    echo '        <div class="promo-tooltip">' . implode('</br> ', $promo_descriptions) . '</div>'; // Nội dung khuyến mãi
                                    echo '    </div>';
                                    echo '</div>';
                                }


                                if ($discount_percentage > 0) {
                                    echo '            <span class="badge discount-badge">- ' . $discount_percentage . '%</span>';
                                }

                                echo '        </div>'; // Đóng img-container

                                // Thân card sản phẩm
                                echo '        <div class="card-body product-info">';
                                echo '            <a href="product-detail.php?id=' . $row["product_id"] . '" class="product-link">';
                                echo '                <h5 class="card-title product-name" title="' . htmlspecialchars($row["product_name"]) . '">' . htmlspecialchars($row["product_name"]) . '</h5>';
                                echo '            </a>';
                                echo '            <p class="product-price">';

                                if ($discount_percentage > 0) {
                                    echo '<span class="original-price">' . number_format($original_price) . ' VNĐ</span><br>';
                                    echo '<span class="discounted-price">' . number_format($discounted_price) . ' VNĐ</span>';
                                } else {
                                    echo number_format($original_price) . ' VNĐ';
                                }

                                echo '            </p>';
                                echo '<div class="stock-and-cart d-flex justify-content-between align-items-center">';
                                echo '<p class="stock-quantity mb-0">';

                                if ($row['stock_quantity'] > 0) {
                                    echo '<i class="fa-regular fa-circle-check"></i> Còn hàng';
                                } else {
                                    echo '<span class="text-danger"><i class="fa-regular fa-circle-xmark"></i> Đã hết hàng</span>';
                                }

                                echo '</p>';
                                echo '<a href="#" onclick="addToCart(' . htmlspecialchars($row['product_id']) . ', 1); return false;" class="btn btn-primary"><i class="fa-solid fa-cart-plus"></i></a>';
                                echo '</div>';
                                echo '        </div>'; // Đóng div card-body
                                echo '    </div>'; // Đóng div card
                                echo '</div>'; // Đóng div col

                            }



                            echo '</div>';
                            // Giải phóng bộ nhớ
                            mysqli_free_result($result);

                            // Tính tổng số sản phẩm để phân trang
                            $total_pages = ceil($total_filtered_products / $productsPerPage);

                            echo '<div class="shop__pagination">';
                            echo '<nav aria-label="Page navigation example">';
                            echo '<ul class="pagination">';

                            // Hiển thị các liên kết phân trang
                            for ($i = 1; $i <= $total_pages; $i++) {
                                // Thêm tham số lọc và sắp xếp vào URL phân trang
                                $page_link = "?page=$i";
                                if ($selected_category) $page_link .= "&category=$selected_category";
                                if ($selected_subcategory) $page_link .= "&subcategory=$selected_subcategory";
                                if ($selected_brand) $page_link .= "&brand=$selected_brand";
                                if ($search_term) $page_link .= "&search=$search_term";
                                if ($sort_option) $page_link .= "&sort=$sort_option";

                                echo '<li class="page-item ' . ($current_page == $i ? 'active' : '') . '">';
                                echo '<a class="page-link" href="' . $page_link . '">' . $i . '</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                            echo '</nav>';
                            echo '</div>';
                        } else {
                            echo 'Không có sản phẩm nào.';
                        }
                    } else {
                        echo 'Có lỗi khi truy vấn dữ liệu.';
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="promoModal" tabindex="-1" aria-labelledby="promoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="promoModalLabel">Thông tin khuyến mãi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="promoModalBody">
                        <!-- Nội dung khuyến mãi sẽ được chèn vào đây -->
                    </div>
                </div>
            </div>
        </div>

    </section>

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

    <!-- Footer Section Begin -->
    <?php include_once 'footer.php'; ?>
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

</body>

</html>