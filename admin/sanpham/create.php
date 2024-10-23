<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Lấy danh sách danh mục từ CSDL
$categoryQuery = "SELECT * FROM categories";
$categoryResult = mysqli_query($conn, $categoryQuery);

// Lấy danh sách thương hiệu từ CSDL
$brandQuery = "SELECT brand_id, brand_name, brand_image FROM brands";
$brandResult = mysqli_query($conn, $brandQuery);

// Kiểm tra xem form đã được submit chưa
// Kiểm tra xem form đã được submit chưa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Các giá trị khác
    $productName = $_POST['product_name'];
    $price = $_POST['price'];
    $productInfo = $_POST['product_info'];
    $categoryId = $_POST['category_id'];
    $subcategoryId = $_POST['subcategory_id'];
    $brandId = $_POST['brand_id'];
    $stockQuantity = $_POST['stock_quantity']; // Lấy số lượng sản phẩm
    $promotionDescription = $_POST['promotion_description']; // Lấy mô tả quà tặng kèm

    // Kiểm tra biến trống
    if (empty($productName) || empty($price) || empty($categoryId) || empty($subcategoryId) || empty($brandId) || empty($stockQuantity)) {
        echo "Vui lòng điền đầy đủ thông tin sản phẩm.";
        exit();
    }

    // Xử lý ảnh chính 
    $targetDir = "../assets/img/imgproducts/";

    // Upload ảnh chính 
    $imageExtension = pathinfo($_FILES["background_image"]["name"], PATHINFO_EXTENSION);
    $imageName = uniqid("bg_", true) . '.' . $imageExtension; // Tên file duy nhất cho ảnh chính
    $targetFile = $targetDir . $imageName;

    if (move_uploaded_file($_FILES["background_image"]["tmp_name"], $targetFile)) {
        // Có thể thêm logic lưu trữ thông tin ảnh chính vào cơ sở dữ liệu nếu cần
    } else {
        echo "Lỗi khi tải ảnh chính lên.";
        exit();
    }

    // Insert dữ liệu vào bảng products
    $insertProductQuery = "INSERT INTO products (product_name, price, background_image, product_info, category_id, subcategory_id, brand_id, stock_quantity) 
                           VALUES ('$productName', '$price', '$targetFile', '$productInfo', '$categoryId', '$subcategoryId', '$brandId', '$stockQuantity')";
    
    if (mysqli_query($conn, $insertProductQuery)) {
        // Lấy product_id của sản phẩm vừa thêm
        $productId = mysqli_insert_id($conn);

        // Insert cấu hình sản phẩm vào bảng product_configurations
        foreach ($_POST['config_name'] as $key => $configName) {
            $configValue = $_POST['config_value'][$key]; // Giá trị tương ứng với tên

            // Lưu vào cơ sở dữ liệu
            if (!empty($configName) && !empty($configValue)) {
                $stmt = $conn->prepare("INSERT INTO product_configurations (product_id, config_name, config_value) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $productId, $configName, $configValue);
                $stmt->execute();
            }
        }

        // Insert quà tặng kèm nếu có
        if (!empty($_POST['promotion_description'])) {
            foreach ($_POST['promotion_description'] as $promotionDescription) {
                $promotionDescription = mysqli_real_escape_string($conn, $promotionDescription); // Bảo vệ SQL Injection
                if (!empty($promotionDescription)) {
                    $insertPromotionQuery = "INSERT INTO product_promotions (product_id, promotion_description) 
                                    VALUES ('$productId', '$promotionDescription')";
                    if (!mysqli_query($conn, $insertPromotionQuery)) {
                        echo "Lỗi khi thêm thông tin khuyến mãi: " . mysqli_error($conn);
                    }
                }
            }
        }

        // Upload và lưu trữ ảnh mô tả sản phẩm vào bảng product_images
        if (!empty($_FILES["description_images"]["name"])) {
            foreach ($_FILES["description_images"]["name"] as $key => $image) {
                $imageExtension = pathinfo($_FILES["description_images"]["name"][$key], PATHINFO_EXTENSION);
                $imageName = uniqid("desc_", true) . '.' . $imageExtension; // Tên file duy nhất cho ảnh mô tả
                $targetFile = $targetDir . $imageName;

                if (move_uploaded_file($_FILES["description_images"]["tmp_name"][$key], $targetFile)) {
                    // Insert dữ liệu vào bảng product_images
                    $insertImageQuery = "INSERT INTO product_images (product_id, image_url) 
                                VALUES ('$productId', '$targetFile')";
                    if (!mysqli_query($conn, $insertImageQuery)) {
                        echo "Lỗi khi thêm ảnh mô tả: " . mysqli_error($conn);
                    }
                } else {
                    echo "Lỗi khi tải ảnh mô tả lên.";
                }
            }
        }

        // Kiểm tra và lưu thông tin giảm giá nếu có
        if (!empty($_POST['discount_percentage']) && $_POST['discount_percentage'] > 0) {
            $discountPercentage = $_POST['discount_percentage'];
            $startDate = $_POST['start_date']; // Ngày bắt đầu giảm giá
            $endDate = $_POST['end_date']; // Ngày kết thúc giảm giá

            // Insert dữ liệu vào bảng discounts
            $insertDiscountQuery = "INSERT INTO discounts (product_id, discount_percentage, start_date, end_date) 
                                    VALUES ('$productId', '$discountPercentage', '$startDate', '$endDate')";
            if (!mysqli_query($conn, $insertDiscountQuery)) {
                echo "Lỗi khi thêm thông tin giảm giá: " . mysqli_error($conn);
            }
        }

        // Thiết lập thông báo thành công vào session
        $_SESSION['success_message'] = "Sản phẩm đã được tạo thành công.";

        // Chuyển hướng về trang danh sách sản phẩm sau khi thêm mới
        header("Location: index.php");
        exit();
    } else {
        echo "Lỗi khi thêm sản phẩm: " . mysqli_error($conn);
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <title>Tạo mới sản phẩm</title>
    <link rel="stylesheet" href="../assets/css/modal.css">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    <style>
        .container-fluid {
            margin-top: 80px;
        }
    </style>
</head>

<body>

    <?php
    // Include header
    include_once '../header.php';
    ?>
    <div class="container-fluid">
        <div class="row">
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-left-long"></i></a>
                <h2 class="my-4">Tạo mới sản phẩm</h2>

                <!-- Form tạo mới sản phẩm -->
                <form action="create.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="product_name">Tên sản phẩm:</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Danh mục:</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Chọn danh mục</option>
                            <?php
                            // Hiển thị danh sách danh mục trong dropdown
                            while ($category = mysqli_fetch_assoc($categoryResult)) {
                                echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subcategory_id">Danh mục con:</label>
                        <select class="form-control" id="subcategory_id" name="subcategory_id" required>
                            <option value="">Chọn danh mục con</option>
                            <!-- Các danh mục con sẽ được tải động qua AJAX -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="brand_id">Thương hiệu:</label>
                        <select class="form-control" id="brand_id" name="brand_id" required>
                            <option value="">Chọn thương hiệu</option>
                            <?php
                            // Hiển thị danh sách thương hiệu trong dropdown
                            while ($brand = mysqli_fetch_assoc($brandResult)) {
                                echo "<option value='{$brand['brand_id']}'>{$brand['brand_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Giá:</label>
                        <input type="text" class="form-control" id="price" name="price" required>
                    </div>
                    <!-- Thêm các trường giảm giá -->
                    <div class="form-group">
                        <label for="discount_percentage">Phần trăm giảm giá:</label>
                        <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" min="0" max="100">
                    </div>

                    <div class="form-group">
                        <label for="start_date">Ngày bắt đầu:</label>
                        <input type="date" class="form-control" id="start_date" name="start_date">
                    </div>

                    <div class="form-group">
                        <label for="end_date">Ngày kết thúc:</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                    <div class="form-group">
                        <label for="stock_quantity">Số lượng sản phẩm trong kho:</label>
                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="promotion_description">Quà tặng kèm:</label>
                        <div id="promotion-container">
                            <textarea class="form-control promotion-description" name="promotion_description[]" placeholder="Mô tả quà tặng kèm (nếu có)"></textarea>
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" id="add-promotion">+</button>
                    </div>



                    <div class="form-group">
                        <label for="background_image">Ảnh đại diện chính của sản phẩm:</label>
                        <input type="file" class="form-control-file" id="background_image" name="background_image" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="description_images">Ảnh mô tả sản phẩm (nhiều):</label>
                        <div id="description_images_container">
                            <input type="file" class="form-control-file" name="description_images[]" accept="image/*">
                        </div>
                        <button type="button" id="add_image_button" class="btn btn-success mt-2"><i class="fas fa-plus"></i> Thêm ảnh</button>
                    </div>


                    <div class="container">
                        <div id="configurations-container" class="row">
                            <div class="col-md-6 form-group">
                                <label>Tên cấu hình:</label>
                                <input type="text" name="config_name[]" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Giá trị cấu hình:</label>
                                <textarea name="config_value[]" class="form-control" required></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addConfiguration()" class="btn btn-primary">Thêm cấu hình</button>


                    <div class="form-group">
                        <label for="product_info">Thông tin sản phẩm:</label>
                        <textarea class="form-control" id="product_info" name="product_info"></textarea>
                    </div>


                    <button type="submit" class="btn btn-primary">Tạo mới</button>
                </form>

            </main>
        </div>
    </div>

    <script>
        // Thêm sự kiện lắng nghe khi người dùng thay đổi danh mục
        document.getElementById('category_id').addEventListener('change', function() {
            var categoryId = this.value;

            if (categoryId) {
                // Tạo một yêu cầu AJAX để lấy danh sách subcategories
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'fetch_subcategories.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        // Cập nhật dropdown subcategory với dữ liệu nhận được từ fetch_subcategories.php
                        document.getElementById('subcategory_id').innerHTML = this.responseText;
                    }
                };
                // Gửi yêu cầu cùng với category_id
                xhr.send('category_id=' + categoryId);
            } else {
                // Nếu không chọn danh mục nào, trả về mặc định
                document.getElementById('subcategory_id').innerHTML = '<option value="">Chọn danh mục con</option>';
            }
        });

        document.getElementById('add_image_button').addEventListener('click', function() {
            // Tạo một thẻ input mới
            var newInput = document.createElement('input');
            newInput.type = 'file';
            newInput.name = 'description_images[]';
            newInput.accept = 'image/*';
            newInput.className = 'form-control-file mt-2'; // Thêm class cho thẻ input

            // Thêm thẻ input vào container
            document.getElementById('description_images_container').appendChild(newInput);
        });

        // Kích hoạt CKEditor cho các textarea
        CKEDITOR.replace('product_info');
    </script>
    <script>
        // Hàm để thêm trường cấu hình mới
        function addConfiguration() {
            var container = document.getElementById('configurations-container');
            var newConfig = `
        <div class="col-md-6 form-group">
            <label>Tên cấu hình:</label>
            <input type="text" name="config_name[]" class="form-control" required>
        </div>
        <div class="col-md-6 form-group">
            <label>Giá trị cấu hình:</label>
            <textarea name="config_value[]" class="form-control" required></textarea>
        </div>`;
            container.insertAdjacentHTML('beforeend', newConfig);
        }

        $(document).ready(function() {
            $('#add-promotion').click(function() {
                $('#promotion-container').append(
                    '<textarea class="form-control promotion-description mt-2" name="promotion_description[]" placeholder="Mô tả quà tặng kèm (nếu có)"></textarea>'
                );
            });
        });
    </script>


</body>

</html>

<?php
// Đóng kết nối CSDL
mysqli_close($conn);
?>