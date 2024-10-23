<?php
// Kết nối đến cơ sở dữ liệu
include_once '../dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['configuration_id'])) {
    $configurationId = $_POST['configuration_id'];

    // Chuẩn bị truy vấn để xóa cấu hình
    $deleteQuery = "DELETE FROM product_configurations WHERE configuration_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    
    if (!$stmt) {
        // Trả về lỗi nếu chuẩn bị truy vấn không thành công
        echo json_encode(['success' => false, 'message' => 'Không thể chuẩn bị truy vấn: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $configurationId);

    if ($stmt->execute()) {
        // Nếu xóa thành công
        echo json_encode(['success' => true]);
    } else {
        // Nếu có lỗi xảy ra
        echo json_encode(['success' => false, 'message' => 'Không thể xóa cấu hình: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
}
?>
