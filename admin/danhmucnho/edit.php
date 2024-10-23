<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Kiểm tra xem có tham số subcategory_id được truyền không
if (isset($_GET['subcategory_id'])) {
    $subcategoryId = $_GET['subcategory_id'];

    // Truy vấn CSDL để lấy thông tin chi tiết danh mục con
    $query = "SELECT * FROM subcategories WHERE subcategory_id = $subcategoryId";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        // Hiển thị thông báo nếu không tìm thấy danh mục con
        echo "Không tìm thấy danh mục con";
        exit();
    }
} else {
    // Hiển thị thông báo nếu không có subcategory_id
    echo "Không có danh mục con để hiển thị";
    exit();
}

// Xử lý khi form được submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $subcategoryName = $_POST['subcategory_name'];
    $categoryId = $_POST['category_id'];

    // Cập nhật thông tin danh mục con trong bảng subcategories
    $updateSubcategoryQuery = "UPDATE subcategories SET 
                               subcategory_name = '$subcategoryName', 
                               category_id = '$categoryId'
                               WHERE subcategory_id = $subcategoryId";

    if (mysqli_query($conn, $updateSubcategoryQuery)) {
        // Lưu thông báo thành công vào session
        session_start();
        $_SESSION['success_message'] = "Cập nhật danh mục con thành công!";
    }

    // Chuyển hướng về trang danh sách danh mục con sau khi cập nhật
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <title>Chỉnh sửa danh mục con</title>
    <link rel="stylesheet" href="../assets/css/modal.css">
    <style>
        .container-fluid{
            margin-top: 20px;
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
            <a href="index.php" class="btn btn-secondary mt-5"><i class="fa-solid fa-left-long"></i></a>
                <h2 class="my-4">Chỉnh sửa danh mục con</h2>

                <!-- Form chỉnh sửa danh mục con -->
                <form action="edit.php?subcategory_id=<?php echo $subcategoryId; ?>" method="post">
                    <!-- Trường chỉnh sửa tên danh mục con -->
                    <div class="form-group">
                        <label for="subcategory_name">Tên danh mục con:</label>
                        <input type="text" class="form-control" id="subcategory_name" name="subcategory_name" value="<?php echo $row['subcategory_name']; ?>" required>
                    </div>

                    <!-- Trường chọn danh mục cha -->
                    <div class="form-group">
                        <label for="category_id">Danh mục cha:</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <?php
                            // Truy vấn để lấy danh sách danh mục cha
                            $categoryQuery = "SELECT * FROM categories";
                            $categoryResult = mysqli_query($conn, $categoryQuery);
                            
                            while ($categoryRow = mysqli_fetch_assoc($categoryResult)) {
                                $selected = $row['category_id'] == $categoryRow['category_id'] ? 'selected' : '';
                                echo "<option value='" . $categoryRow['category_id'] . "' $selected>" . $categoryRow['category_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Nút cập nhật -->
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </form>

            </main>
        </div>
    </div>
</body>
</html>

<?php
// Giải phóng bộ nhớ
mysqli_free_result($result);

// Đóng kết nối CSDL
mysqli_close($conn);
?>
