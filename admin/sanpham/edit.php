<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Xử lý yêu cầu xóa cấu hình
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_configuration'])) {
    $configId = $_POST['delete_configuration'];

    // Xóa cấu hình khỏi cơ sở dữ liệu
    $deleteQuery = "DELETE FROM product_configurations WHERE configuration_id = $configId"; // Chỉnh sửa thành configuration_id
    if (mysqli_query($conn, $deleteQuery)) {
        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa cấu hình']);
        exit();
    }
}


// Lấy thông tin sản phẩm theo product_id
if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];

    // Truy vấn thông tin sản phẩm từ CSDL
    $productQuery = "SELECT * FROM products WHERE product_id = $productId";
    $productResult = mysqli_query($conn, $productQuery);
    $product = mysqli_fetch_assoc($productResult);

    // Lấy thông tin cấu hình sản phẩm
    $configQuery = "SELECT * FROM product_configurations WHERE product_id = $productId";
    $configResult = mysqli_query($conn, $configQuery);
    $configurations = mysqli_fetch_all($configResult, MYSQLI_ASSOC);

    // Lấy thông tin giảm giá (nếu có)
    $discountQuery = "SELECT * FROM discounts WHERE product_id = $productId";
    $discountResult = mysqli_query($conn, $discountQuery);
    $discount = mysqli_fetch_assoc($discountResult);

    // Lấy danh sách danh mục
    $categoryQuery = "SELECT * FROM categories";
    $categoryResult = mysqli_query($conn, $categoryQuery);

    // Lấy danh sách thương hiệu
    $brandQuery = "SELECT * FROM brands";
    $brandResult = mysqli_query($conn, $brandQuery);

    // Lấy danh sách ảnh từ bảng product_images
    $imageQuery = "SELECT * FROM product_images WHERE product_id = $productId";
    $imageResult = mysqli_query($conn, $imageQuery);

    // Lấy dữ liệu các quà tặng hiện có
    $selectPromotionQuery = "SELECT promotion_id, promotion_description FROM product_promotions WHERE product_id = '$productId'";
    $promotionResults = mysqli_query($conn, $selectPromotionQuery);
}


// Kiểm tra xem form đã được submit chưa
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['delete_configuration'])) {
    // Lấy giá trị từ form
    $productName = $_POST['product_name'];
    $price = str_replace(',', '', $_POST['price']);  // Loại bỏ dấu phẩy trong giá

    // Xử lý cập nhật hoặc thêm mới cấu hình
    foreach ($_POST['configurations'] as $config) {
        if (empty($config['configuration_id'])) {
            $insertConfigQuery = "INSERT INTO product_configurations (config_name, config_value, product_id) 
                                  VALUES ('{$config['name']}', '{$config['value']}', '$productId')";
            mysqli_query($conn, $insertConfigQuery);
        } else {
            $updateConfigQuery = "UPDATE product_configurations SET 
                config_name = '{$config['name']}', 
                config_value = '{$config['value']}' 
                WHERE configuration_id = {$config['configuration_id']}";
            mysqli_query($conn, $updateConfigQuery);
        }
    }
    // Xử lý các quà tặng đã có
    if (!empty($_POST['promotion_description'])) {
        foreach ($_POST['promotion_description'] as $promotionId => $promotionDescription) {
            $promotionDescription = mysqli_real_escape_string($conn, $promotionDescription); // Bảo vệ SQL Injection

            // Nếu promotionId là số, tức là quà tặng đã có và cần cập nhật
            if (is_numeric($promotionId)) {
                if (!empty($promotionDescription)) {
                    // Cập nhật quà tặng
                    $updatePromotionQuery = "UPDATE product_promotions 
                                             SET promotion_description = '$promotionDescription' 
                                             WHERE promotion_id = '$promotionId'";
                    if (!mysqli_query($conn, $updatePromotionQuery)) {
                        echo "Lỗi khi cập nhật thông tin khuyến mãi: " . mysqli_error($conn);
                    }
                } else {
                    // Nếu quà tặng không còn mô tả, xóa quà tặng
                    $deletePromotionQuery = "DELETE FROM product_promotions WHERE promotion_id = '$promotionId'";
                    if (!mysqli_query($conn, $deletePromotionQuery)) {
                        echo "Lỗi khi xóa khuyến mãi: " . mysqli_error($conn);
                    }
                }
            }
        }
    }

    // Xử lý thêm quà tặng mới
    if (!empty($_POST['new_promotion_description'])) {

        foreach ($_POST['new_promotion_description'] as $newPromotionDescription) {
            $newPromotionDescription = mysqli_real_escape_string($conn, $newPromotionDescription); // Bảo vệ SQL Injection

            // Kiểm tra và thêm các quà tặng không trống
            if (!empty($newPromotionDescription)) {
                $insertPromotionQuery = "INSERT INTO product_promotions (product_id, promotion_description) 
                                     VALUES ('$productId', '$newPromotionDescription')";
                if (!mysqli_query($conn, $insertPromotionQuery)) {
                    echo "Lỗi khi thêm thông tin khuyến mãi mới: " . mysqli_error($conn);
                } else {
                    echo "Thêm quà tặng mới thành công: $newPromotionDescription <br>"; // Thông báo để kiểm tra
                }
            }
        }
    }
    $productInfo = $_POST['product_info'];
    $categoryId = $_POST['category_id'];
    $subcategoryId = $_POST['subcategory_id'];
    $brandId = $_POST['brand_id'];
    $stockQuantity = $_POST['stock_quantity'];
    $promotionDescription = $_POST['promotion_description']; // Thông tin quà tặng

    // Xử lý cập nhật ảnh mô tả sản phẩm (nếu có thay đổi)
    $targetDir = "../assets/img/imgproducts/";
    $currentDate = date("YmdHis");
    if (!empty($_FILES["background_image"]["name"])) {
        $targetFile = $targetDir . $currentDate . "_" . basename($_FILES["background_image"]["name"]);
        move_uploaded_file($_FILES["background_image"]["tmp_name"], $targetFile);
        $updateImageQuery = "UPDATE products SET background_image='$targetFile' WHERE product_id=$productId";
        mysqli_query($conn, $updateImageQuery);
    }

    // Xử lý cập nhật danh sách ảnh mô tả sản phẩm (nếu có thay đổi)
    if (isset($_FILES['new_image'])) {
        $totalImages = count($_FILES['new_image']['name']); // Số lượng ảnh được tải lên
        $targetDir = "../assets/img/imgproducts/"; // Thư mục lưu trữ ảnh

        for ($i = 0; $i < $totalImages; $i++) {
            if (!empty($_FILES['new_image']['name'][$i])) {
                // Tạo tên file duy nhất bằng cách kết hợp uniqid() và tên file gốc
                $imageExtension = pathinfo($_FILES['new_image']['name'][$i], PATHINFO_EXTENSION);
                $imageName = uniqid("img_", true) . '.' . $imageExtension; // Tên file duy nhất
                $targetFile = $targetDir . $imageName;

                // Di chuyển file ảnh tới thư mục đích
                if (move_uploaded_file($_FILES['new_image']['tmp_name'][$i], $targetFile)) {
                    // Lưu thông tin ảnh vào cơ sở dữ liệu
                    $insertImageQuery = "INSERT INTO product_images (product_id, image_url) 
                                     VALUES ('$productId', '$targetFile')";
                    if (!mysqli_query($conn, $insertImageQuery)) {
                        echo "Lỗi khi lưu ảnh vào cơ sở dữ liệu: " . mysqli_error($conn);
                    }
                } else {
                    echo "Lỗi khi tải ảnh lên.";
                }
            }
        }
    }


    // Cập nhật thông tin sản phẩm
    $updateProductQuery = "UPDATE products SET 
    product_name='$productName', 
    price='$price', 
    product_info='$productInfo', 
    category_id='$categoryId', 
    subcategory_id='$subcategoryId', 
    brand_id='$brandId', 
    stock_quantity='$stockQuantity'  
    WHERE product_id=$productId";



    if (mysqli_query($conn, $updateProductQuery)) {
        // Lấy lại thông tin sản phẩm mới
        $productResult = mysqli_query($conn, $productQuery);
        $product = mysqli_fetch_assoc($productResult);
    } else {
        echo "Lỗi cập nhật sản phẩm: " . mysqli_error($conn);
    }





    // Cập nhật thông tin giảm giá
    if (!empty($_POST['discount_percentage']) && $_POST['discount_percentage'] > 0) {
        $discountPercentage = $_POST['discount_percentage'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];

        // Kiểm tra xem đã có thông tin giảm giá hay chưa
        $discountQuery = "SELECT * FROM discounts WHERE product_id=$productId"; // Thêm truy vấn kiểm tra
        $discountResult = mysqli_query($conn, $discountQuery);
        $discount = mysqli_fetch_assoc($discountResult);

        if ($discount) {
            $updateDiscountQuery = "UPDATE discounts SET 
                discount_percentage='$discountPercentage', 
                start_date='$startDate', 
                end_date='$endDate' 
                WHERE product_id=$productId";
            mysqli_query($conn, $updateDiscountQuery);
        } else {
            $insertDiscountQuery = "INSERT INTO discounts (product_id, discount_percentage, start_date, end_date) 
                VALUES ('$productId', '$discountPercentage', '$startDate', '$endDate')";
            mysqli_query($conn, $insertDiscountQuery);
        }
    }



    // Thiết lập thông báo thành công vào biến session
    $_SESSION['success_message'] = 'Cập nhật sản phẩm thành công!';

    // Chuyển hướng về trang danh sách sản phẩm sau khi chỉnh sửa xong
    header("Location: index.php");
    exit();
}

?>






<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <title>Chỉnh sửa sản phẩm</title>
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>

    <style>
        .container {
            margin-top: 80px;
        }

        .product-image {
            width: 100px;
            height: 100px;
        }

        @media (min-width: 768px) {
            .container {
                width: 80% !important;
            }
        }

        .form-label {
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php
    // Include header
    include_once '../header.php';
    ?>
    <div class="container">
        <div class="row">
            <main role="main" class="col ms-sm-auto px-4">
                <!-- Nút quay lại -->
                <a href="index.php" class="btn btn-secondary mb-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="my-4">Chỉnh sửa sản phẩm</h2>

                <!-- Form chỉnh sửa sản phẩm -->
                <form action="edit.php?product_id=<?php echo $productId; ?>" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Tên sản phẩm:</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo $product['product_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Danh mục:</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Chọn danh mục</option>
                            <?php while ($category = mysqli_fetch_assoc($categoryResult)): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php echo $category['category_id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo $category['category_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="subcategory_id" class="form-label">Danh mục con:</label>
                        <select class="form-select" id="subcategory_id" name="subcategory_id" required>
                            <option value="">Chọn danh mục con</option>
                            <!-- Các danh mục con sẽ được thêm vào đây qua AJAX -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Thương hiệu:</label>
                        <select class="form-select" id="brand_id" name="brand_id" required>
                            <option value="">Chọn thương hiệu</option>
                            <?php while ($brand = mysqli_fetch_assoc($brandResult)): ?>
                                <option value="<?php echo $brand['brand_id']; ?>" <?php echo $brand['brand_id'] == $product['brand_id'] ? 'selected' : ''; ?>>
                                    <?php echo $brand['brand_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Giá:</label>
                        <input type="text" class="form-control" id="price" name="price" value="<?php echo number_format($product['price'], 0, '.', ','); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="discount_percentage" class="form-label">Phần trăm giảm giá:</label>
                        <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" value="<?php echo $discount['discount_percentage'] ?? ''; ?>" min="0" max="100">
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Ngày bắt đầu:</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $discount['start_date'] ?? ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">Ngày kết thúc:</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $discount['end_date'] ?? ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="stock_quantity" class="form-label">Số lượng trong kho:</label>
                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label for="promotion_description" class="form-label">Quà tặng kèm:</label>
                        <div id="promotion-container">
                            <?php
                            while ($promotion = mysqli_fetch_assoc($promotionResults)) {
                                echo '<textarea class="form-control promotion-description mt-2" name="promotion_description[' . $promotion['promotion_id'] . ']" placeholder="Mô tả quà tặng kèm">' . $promotion['promotion_description'] . '</textarea>';
                            }
                            ?>
                            <!-- Thêm trường cho quà tặng mới -->
                            <textarea class="form-control promotion-description mt-2" name="new_promotion_description[]" placeholder="Mô tả quà tặng kèm mới (nếu có)"></textarea>
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" id="add-promotion">+</button>
                    </div>




                    <div class="mb-3">
                        <label for="background_image" class="form-label">Ảnh đại diện chính của sản phẩm:</label>
                        <input type="file" class="form-control" id="background_image" name="background_image">
                        <img src="<?php echo $product['background_image']; ?>" alt="Product Image" style="width: 150px;">
                    </div>

                    <div class="mb-3">
                        <label for="product_images" class="form-label">Danh sách ảnh mô tả sản phẩm:</label>
                        <div id="product_images_list">
                            <?php
                            // Lấy danh sách ảnh từ bảng product_images dựa trên product_id
                            $productId = $product['product_id'];
                            $imageQuery = "SELECT * FROM product_images WHERE product_id = $productId";
                            $imageResult = mysqli_query($conn, $imageQuery);

                            if (mysqli_num_rows($imageResult) > 0) {
                                while ($row = mysqli_fetch_assoc($imageResult)) {
                                    $imageId = $row['image_id'];
                                    $imageUrl = $row['image_url'];
                                    echo '<div class="image-item" id="image_' . $imageId . '">';
                                    echo '<img src="' . $imageUrl . '" alt="Product Image" style="width: 150px; margin-bottom: 10px;">';
                                    echo '<button type="button" class="btn btn-danger delete-image" data-image-id="' . $imageId . '">Xóa</button>';
                                    echo '</div>';
                                }
                            } else {
                                echo 'Không có ảnh mô tả nào.';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="new_image" class="form-label">Thêm ảnh mới:</label>
                        <div id="image_inputs_container">
                            <input type="file" class="form-control mb-2" id="new_image_1" name="new_image[]">
                        </div>
                        <button type="button" class="btn btn-secondary" id="add_new_image">+</button>
                    </div>


                    <h3>Thông số kỹ thuật:</h3>
                    <div id="configurations-container">
                        <?php foreach ($configurations as $config): ?>
                            <div class="row mb-3 align-items-end" data-id="<?php echo $config['configuration_id']; ?>">
                                <div class="col-md-4 mb-3">
                                    <label for="config_name_<?php echo $config['configuration_id']; ?>" class="form-label">Tên cấu hình:</label>
                                    <input type="text" id="config_name_<?php echo $config['configuration_id']; ?>"
                                        name="configurations[<?php echo $config['configuration_id']; ?>][name]"
                                        value="<?php echo htmlspecialchars($config['config_name']); ?>"
                                        class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="config_value_<?php echo $config['configuration_id']; ?>" class="form-label">Giá trị:</label>
                                    <textarea id="config_value_<?php echo $config['configuration_id']; ?>"
                                        name="configurations[<?php echo $config['configuration_id']; ?>][value]"
                                        class="form-control" required><?php echo htmlspecialchars($config['config_value']); ?></textarea>
                                </div>
                                <input type="hidden" name="configurations[<?php echo $config['configuration_id']; ?>][configuration_id]" value="<?php echo $config['configuration_id']; ?>">
                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button type="button" onclick="deleteConfiguration(<?php echo $config['configuration_id']; ?>)" class="btn btn-danger">Xóa</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addConfiguration()" class="btn btn-secondary">Thêm cấu hình</button>

                    <div class="mb-3">
                        <label for="product_info" class="form-label">Thông tin sản phẩm:</label>
                        <textarea class="form-control" id="product_info" name="product_info" rows="4"><?php echo $product['product_info']; ?></textarea>
                        <script>
                            CKEDITOR.replace('product_info');
                        </script>
                    </div>

                    <button type="submit" class="btn btn-success">Cập nhật</button>
                </form>

                <!-- Modal Xác Nhận Xóa -->
                <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmDeleteModalLabel">Xác Nhận Xóa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Bạn có chắc chắn muốn xóa cấu hình này không?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Xóa</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            var initialCategoryId = "<?php echo $product['category_id']; ?>";
            if (initialCategoryId) {
                $.ajax({
                    url: 'fetch_subcategories.php',
                    type: 'POST',
                    data: {
                        category_id: initialCategoryId
                    },
                    success: function(response) {
                        $('#subcategory_id').html(response);
                        // Chọn danh mục con hiện tại của sản phẩm
                        var selectedSubcategoryId = "<?php echo $product['subcategory_id']; ?>";
                        $('#subcategory_id').val(selectedSubcategoryId);
                    }
                });
            }

            // Thay đổi danh mục cha để lấy danh mục con mới
            $('#category_id').on('change', function() {
                var categoryId = $(this).val(); // Lấy ID danh mục được chọn

                if (categoryId) {
                    $.ajax({
                        url: 'fetch_subcategories.php', // File xử lý yêu cầu lấy danh mục con
                        type: 'POST',
                        data: {
                            category_id: categoryId
                        },
                        success: function(response) {
                            $('#subcategory_id').html(response);
                        }
                    });
                } else {
                    $('#subcategory_id').html('<option value="">Chọn danh mục con</option>');
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '.delete-image', function() {
            var imageId = $(this).data('image-id');

            if (confirm("Bạn có chắc chắn muốn xóa ảnh này không?")) {
                $.ajax({
                    url: 'delete_image.php', // File PHP xử lý xóa ảnh
                    type: 'POST',
                    data: {
                        image_id: imageId
                    },
                    success: function(response) {
                        if (response == "success") {
                            $('#image_' + imageId).remove(); // Xóa ảnh khỏi giao diện
                        } else {
                            alert("Lỗi khi xóa ảnh.");
                        }
                    }
                });
            }
        });
    </script>

    <script>
        function addConfiguration() {
            var container = document.getElementById('configurations-container');
            var newConfig = `
        <div class="row mb-3 align-items-end"> 
            <div class="col-md-4 form-group">
                <label for="config_name_new">Tên cấu hình:</label>
                <input type="text" name="configurations[new_${Date.now()}][name]" class="form-control" required>
            </div>
            <div class="col-md-4 form-group">
                <label for="config_value_new">Giá trị:</label>
                <textarea name="configurations[new_${Date.now()}][value]" class="form-control" required></textarea>
            </div>
            <div class="col-md-2 form-group d-flex align-items-end">
                <button type="button" onclick="deleteNewConfiguration(this)" class="btn btn-danger">Xóa</button>
            </div>
        </div>`;
            container.insertAdjacentHTML('beforeend', newConfig);
        }



        // Hàm để xóa cấu hình mới
        function deleteNewConfiguration(button) {
            button.closest('.row').remove();
        }

        let configurationIdToDelete;

        function deleteConfiguration(configurationId) {
            configurationIdToDelete = configurationId;
            const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            deleteModal.show();
        }

        document.getElementById('confirmDeleteButton').addEventListener('click', function() {
            if (configurationIdToDelete) {
                // Gửi yêu cầu AJAX để xóa cấu hình
                $.ajax({
                    url: 'delete_configuration.php', // Đường dẫn tới file xử lý xóa
                    method: 'POST',
                    data: {
                        configuration_id: configurationIdToDelete
                    },
                    success: function(response) {
                        console.log(response); // Ghi lại phản hồi để kiểm tra
                        // Phân tích phản hồi JSON
                        const data = JSON.parse(response);
                        if (data.success) {
                            // Xóa cấu hình khỏi UI
                            $(`#config_name_${configurationIdToDelete}`).closest('.row').remove();
                        } else {
                            alert('Có lỗi xảy ra khi xóa cấu hình: ' + data.message);
                        }
                        // Đóng modal
                        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
                        deleteModal.hide();
                    },
                    error: function(xhr, status, error) {
                        alert('Có lỗi xảy ra khi gửi yêu cầu xóa: ' + error);
                        console.log(xhr.responseText); // Ghi lại thông tin lỗi từ phản hồi
                    }
                });
            }
        });
    </script>

    <script>
        let imageCounter = 1; // Đếm số lượng input file

        document.getElementById('add_new_image').addEventListener('click', function() {
            imageCounter++;

            // Tạo một input file mới
            const newInput = document.createElement('input');
            newInput.type = 'file';
            newInput.classList.add('form-control', 'mb-2');
            newInput.name = 'new_image[]'; // Để sử dụng nhiều file cùng một lúc
            newInput.id = 'new_image_' + imageCounter;

            // Thêm input mới vào container
            document.getElementById('image_inputs_container').appendChild(newInput);
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#add-promotion').click(function() {
                // Thêm một textarea mới cho quà tặng
                $('#promotion-container').append(
                    '<textarea class="form-control promotion-description mt-2" name="new_promotion_description[]" placeholder="Mô tả quà tặng kèm mới (nếu có)"></textarea>'
                );
            });
        });
    </script>








</body>

</html>