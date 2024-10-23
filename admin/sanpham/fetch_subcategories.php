<?php
include_once '../dbconnect.php';

if (isset($_POST['category_id'])) {
    $categoryId = $_POST['category_id'];

    // Truy vấn để lấy danh sách subcategories dựa trên category_id
    $query = "SELECT * FROM subcategories WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Tạo danh sách option cho subcategories
    if ($result->num_rows > 0) {
        while ($subcategory = $result->fetch_assoc()) {
            echo "<option value='{$subcategory['subcategory_id']}'>{$subcategory['subcategory_name']}</option>";
        }
    } else {
        echo "<option value=''>Không có danh mục con nào</option>";
    }
    $stmt->close();
}
$conn->close();
?>



