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
    <title>Chỉnh sửa danh mục con</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/modal.css">

    <style>
        .edit-subcate .container {
            margin-top: 50px;
            min-height: 600px;
        }
    </style>
</head>

<body>
    <?php include_once '../header.php'; ?>
    <section class="edit-subcate">
        <div class="container">
            <div class="row">
                <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-4">
                    <a href="index.php" class="btn btn-secondary mt-5"><i class="fa-solid fa-arrow-left"></i></a>
                    <h2 class="my-4">Chỉnh sửa danh mục con</h2>

                    <!-- Form chỉnh sửa danh mục con -->
                    <form action="edit.php?subcategory_id=<?php echo $subcategoryId; ?>" method="post">
                        <div class="mb-3">
                            <label for="subcategory_name" class="form-label">Tên danh mục con:</label>
                            <input type="text" class="form-control" id="subcategory_name" name="subcategory_name"
                                value="<?php echo $row['subcategory_name']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Danh mục cha:</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <?php
                                $categoryQuery = "SELECT * FROM categories";
                                $categoryResult = mysqli_query($conn, $categoryQuery);

                                while ($categoryRow = mysqli_fetch_assoc($categoryResult)) {
                                    $selected = $row['category_id'] == $categoryRow['category_id'] ? 'selected' : '';
                                    echo "<option value='" . $categoryRow['category_id'] . "' $selected>" . $categoryRow['category_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </form>
                </main>
            </div>
        </div>
    </section>


    <?php include_once '../footer.php'; ?>

    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>


<?php
// Giải phóng bộ nhớ
mysqli_free_result($result);

// Đóng kết nối CSDL
mysqli_close($conn);
?>