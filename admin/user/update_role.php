<?php
include_once '../dbconnect.php';

if (isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = intval($_POST['role']);

    $query = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $new_role, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Cập nhật thành công.";
    } else {
        $_SESSION['error_message'] = "Lỗi.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
