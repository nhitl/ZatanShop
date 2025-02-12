<?php
include_once 'dbconnect.php'; // Kết nối MySQL

$user_session_id = session_id();
$inactive_threshold = 300; // 5 phút (300 giây)

// Kiểm tra xem session_id đã tồn tại trong database chưa
$check_query = "SELECT COUNT(*) as count FROM online_users WHERE session_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $user_session_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$row = $result->fetch_assoc();
$check_stmt->close();

// Nếu chưa có session_id này trong database thì thêm mới, nếu có thì chỉ cập nhật thời gian
if ($row['count'] == 0) {
    $insert_query = "INSERT INTO online_users (session_id, last_activity) VALUES (?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("s", $user_session_id);
    $insert_stmt->execute();
    $insert_stmt->close();
} else {
    $update_query = "UPDATE online_users SET last_activity = NOW() WHERE session_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("s", $user_session_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Xóa những người dùng đã offline (không hoạt động sau 5 phút)
$cleanup_query = "DELETE FROM online_users WHERE last_activity < (NOW() - INTERVAL ? SECOND)";
$cleanup_stmt = $conn->prepare($cleanup_query);
$cleanup_stmt->bind_param("i", $inactive_threshold);
$cleanup_stmt->execute();
$cleanup_stmt->close();
?>
