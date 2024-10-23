<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Xử lý logic lọc sản phẩm
$filter = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];

    // Nếu người dùng nhập số, lọc theo product_id, nếu không, lọc theo product_name
    if (is_numeric($search)) {
        $filter = "WHERE product_id = '$search'";
    } else {
        $filter = "WHERE product_name LIKE '%$search%'";
    }
}

// Truy vấn CSDL
$query = "SELECT product_id, product_name, price, background_image, configuration, product_info FROM products $filter";

// Thực hiện truy vấn
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Sản phẩm</title>
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link rel="stylesheet" href="assets/css/modal.css">
</head>

<body>

    <?php
    // Include header
    include_once '../header.php';
    include_once '../notification.php';
    ?>
    <section class="page-products">
        <div class="container-fluid">
            <h2 class="my-3 text-center">Danh sách sản phẩm</h2>

            <!-- Nút Quay lại chỉ hiển thị khi có lọc -->
            <?php if (isset($_GET['search']) && $_GET['search'] != ''): ?>
                <div class="mb-3">
                    <a href="index.php" class="btn btn-secondary">Quay lại</a>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Tạo SP mới</a>

                <!-- Form lọc sản phẩm -->
                <form class="d-flex" method="GET" action="">
                    <input class="form-control me-2" type="text" name="search" placeholder="Nhập tên SP hoặc ID" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>



            <!-- Products list -->
            <div class="row">
                <?php
                // Hiển thị dữ liệu từ CSDL
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='col-12'>";
                    echo "<div class='product-item'>";
                    echo "<div class='row'>";
                    echo "<div class='col-md-3 col-12'>";
                    echo "<strong>ID:</strong> {$row['product_id']}<br>";
                    echo "<strong>Tên sản phẩm:</strong> {$row['product_name']}";
                    echo "</div>";
                    echo "<div class='col-md-3 col-12'>";
                    echo "<strong>Giá:</strong> " . number_format($row['price'], 0, ',', '.') . " VNĐ";
                    echo "</div>";
                    echo "<div class='col-md-3 col-12'>";
                    echo "<strong>Ảnh mô tả:</strong><br><img src='{$row['background_image']}' alt='Ảnh sản phẩm' width='150'>";
                    echo "</div>";
                    echo "<div class='col-md-3 col-12'>";
                    echo "<strong>Thao tác:</strong> <br>";
                    echo "<a href='#' class='btn btn-info btn-sm view-product' data-bs-toggle='modal' data-bs-target='#productDetailModal' data-product-id='{$row['product_id']}'><i class='fas fa-eye'></i> Xem chi tiết</a>";
                    echo "<a href='edit.php?product_id={$row['product_id']}' class='btn btn-primary btn-sm'><i class='fas fa-edit'></i> Chỉnh sửa</a>";
                    echo "<a href='delete.php?product_id={$row['product_id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')\"><i class='fas fa-trash-alt'></i> Xóa</a>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                ?>
            </div>

            <!-- Modal xem chi tiết -->
            <div class='modal fade' id='productDetailModal' tabindex='-1' aria-labelledby='productDetailModalLabel' aria-hidden='true'>
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title' id='productDetailModalLabel'>Chi tiết Sản phẩm</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                        <div class='modal-body'>
                            <!-- Nội dung chi tiết sản phẩm sẽ được hiển thị ở đây -->
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Nút cuộn lên đầu trang -->
        <div class="scroll-to-top">
            <i class="fas fa-chevron-up"></i>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            // Hiển thị nút cuộn lên đầu trang khi người dùng cuộn xuống
            $(window).scroll(function() {
                if ($(this).scrollTop() > 200) {
                    $('.scroll-to-top').fadeIn();
                } else {
                    $('.scroll-to-top').fadeOut();
                }
            });

            // Xử lý sự kiện khi nút cuộn lên đầu trang được nhấn
            $('.scroll-to-top').click(function() {
                $('html, body').animate({
                    scrollTop: 0
                }, 800);
                return false;
            });

            // JavaScript để xử lý sự kiện khi nút xem chi tiết được click
            $('.view-product').click(function() {
                var productId = $(this).data('product-id');

                // Gửi yêu cầu AJAX để lấy dữ liệu chi tiết sản phẩm
                $.ajax({
                    type: 'GET',
                    url: 'get_product_detail.php',
                    data: {
                        'product_id': productId
                    },
                    success: function(response) {
                        // Hiển thị dữ liệu chi tiết sản phẩm trong modal
                        $('#productDetailModal .modal-body').html(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Lỗi AJAX:', textStatus, errorThrown);
                    }
                });
            });
        });
    </script>

</body>

</html>