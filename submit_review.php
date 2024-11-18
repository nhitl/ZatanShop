<?php
// Kết nối đến cơ sở dữ liệu
include_once 'dbconnect.php';
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Thay đổi múi giờ về Việt Nam
session_start(); // Khởi tạo session

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập để gửi đánh giá."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $image_path = null;

    // Kiểm tra nếu có file ảnh được tải lên
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/reviews/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Kiểm tra loại file ảnh hợp lệ
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            // Đảm bảo thư mục upload tồn tại
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Lưu ảnh vào thư mục uploads
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            }
        }
    }

    // Chuẩn bị và thực thi câu lệnh SQL để thêm đánh giá
    $stmt = $conn->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $product_id, $user_id, $rating, $comment, $image_path);

    if ($stmt->execute()) {
        // Lấy thông tin bình luận vừa thêm
        $review_id = $stmt->insert_id;
        $stmt->close();

        // Lấy thông tin người dùng
        $stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $full_name = $user['full_name'];
        $stmt->close();

        // Tạo phản hồi JSON
        $response = [
            "success" => true,
            "review" => [
                "full_name" => htmlspecialchars($full_name),
                "rating" => $rating,
                "comment" => htmlspecialchars($comment),
                "image" => $image_path ? $image_path : null,
                "created_at" => date("Y-m-d H:i:s")
            ]
        ];

        echo json_encode($response); // Trả về kết quả thành công
    } else {
        echo json_encode(["success" => false, "message" => "Có lỗi xảy ra. Vui lòng thử lại."]);
    }

    $conn->close();
}
?>
