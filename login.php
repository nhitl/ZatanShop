<?php
// Báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection file
include_once 'dbconnect.php';

// Hàm để làm sạch dữ liệu nhập
function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Tạo biến để lưu thông báo lỗi và thông báo thành công
$login_error = "";
$register_error = "";
$register_success = "";
$login_success = ""; // Thông báo đăng nhập thành công
$show_registration_form = false; // Biến để kiểm soát trạng thái của form đăng ký
$redirect = false; // Biến để kiểm soát việc chuyển hướng
$redirect_url = ''; // URL để chuyển hướng

// Kiểm tra nếu biểu mẫu được gửi để đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $email = sanitizeInput($_POST["email"]);
    $phone = sanitizeInput($_POST["phone"]);
    $password = sanitizeInput($_POST["password"]);
    $confirm_password = sanitizeInput($_POST["confirm_password"]);

    // Kiểm tra trùng lặp email
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $register_error = "Email đã tồn tại!";
        $show_registration_form = true;
    } else {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (email, phone_number, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                $register_success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
                $show_registration_form = true; // Hiển thị form đăng ký
                $redirect = true; // Đánh dấu rằng cần chuyển hướng sau một khoảng thời gian
            } else {
                $register_error = "Lỗi: " . $stmt->error;
                $show_registration_form = true; // Hiển thị form đăng ký khi có lỗi
            }
        } else {
            $register_error = "Mật khẩu không khớp!";
            $show_registration_form = true; // Hiển thị form đăng ký khi có lỗi
        }
    }
    $stmt->close(); // Đóng đối tượng stmt sau khi không cần thiết
}

// Kiểm tra nếu biểu mẫu được gửi để đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = sanitizeInput($_POST["email"]);
    $password = sanitizeInput($_POST["password"]);

    $stmt = $conn->prepare("SELECT user_id, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row["password"])) {
            session_start();
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role']; // Lưu role vào session

            // Xác định session_id
            $session_id = session_id(); 

            // Cập nhật giỏ hàng với user_id
            $update_cart_sql = "UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL";
            $stmt = $conn->prepare($update_cart_sql);
            $stmt->bind_param("is", $_SESSION['user_id'], $session_id);
            $stmt->execute();

            $login_success = "Đăng nhập thành công!";
            $show_registration_form = false;
            $redirect = true; // Đánh dấu cần chuyển hướng sau 3 giây

            // Xác định URL để chuyển hướng dựa trên role
            if ($row['role'] == 1) {
                $redirect_url = './admin/index.php'; // URL cho admin
            } else {
                $redirect_url = './index.php'; // URL cho người dùng thường
            }
        } else {
            $login_error = "Mật khẩu không chính xác!";
        }
    } else {
        $login_error = "Người dùng không tồn tại!";
    }
    $stmt->close(); // Đóng đối tượng stmt sau khi không cần thiết
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login & Registration Form</title>
    <link rel="stylesheet" href="assets/css/stylelogin.css">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Nếu có thông báo đăng nhập thành công và cần chuyển hướng
            <?php if ($login_success && $redirect): ?>
                document.getElementById('login-success').style.display = 'block'; // Hiển thị thông báo đăng nhập thành công
                setTimeout(function () {
                    window.location.href = '<?php echo $redirect_url; ?>'; // Chuyển hướng sau 3 giây
                }, 1500);
            <?php endif; ?>

            // Nếu có thông báo đăng ký thành công và cần chuyển hướng
            <?php if ($register_success && $redirect): ?>
                setTimeout(function () {
                    document.getElementById('check').checked = false; // Đổi về trạng thái đăng nhập
                    alert('<?php echo $register_success; ?>'); // Hiển thị thông báo đăng ký thành công
                }, 1500); // Đợi 2 giây trước khi chuyển trạng thái form
            <?php endif; ?>
        });
    </script>
</head>

<body>
    <?php 
    include_once 'loading_bar.php';
    ?>
    <div class="container">
        <input type="checkbox" id="check" <?php echo $show_registration_form ? 'checked' : ''; ?> style="display: none;">
        
        <!-- Form Đăng Nhập -->
        <div class="form login">
            <header>Đăng Nhập</header>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="text" name="email" placeholder="Nhập email của bạn" required>
                <input type="password" name="password" placeholder="Nhập mật khẩu của bạn" required>
                <a href="#">Quên mật khẩu?</a>
                <input type="submit" class="button" name="login" value="Đăng nhập">
                <!-- Đặt thông báo lỗi bên dưới trường nhập liệu cuối cùng -->
                <?php if (!empty($login_error) && isset($_POST["login"])): ?>
                    <div class="error-message">
                        <?php echo $login_error; ?>
                    </div>
                <?php endif; ?>
                <!-- Đặt thông báo đăng nhập thành công -->
                <?php if (!empty($login_success) && isset($_POST["login"])): ?>
                    <div class="success-message" id="login-success" style="display: block;">
                        <?php echo $login_success; ?>
                    </div>
                <?php endif; ?>
            </form>
            <div class="signup">
                <span class="signup">Bạn chưa có tài khoản?
                    <label for="check">Đăng ký ngay</label>
                </span>
            </div>
        </div>

        <!-- Form Đăng Ký -->
        <div class="form registration">
            <header>Đăng Ký</header>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="text" name="email" placeholder="Nhập email của bạn" required>
                <input type="text" name="phone" placeholder="Nhập số điện thoại của bạn" required>
                <input type="password" name="password" placeholder="Nhập mật khẩu" required>
                <input type="password" name="confirm_password" placeholder="Xác nhận lại mật khẩu" required>
                <input type="submit" class="button" name="register" value="Đăng ký">
                <!-- Đặt thông báo lỗi và thông báo thành công bên dưới trường nhập liệu cuối cùng -->
                <?php if (!empty($register_error) && isset($_POST["register"])): ?>
                    <div class="error-message">
                        <?php echo $register_error; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($register_success) && isset($_POST["register"])): ?>
                    <div class="success-message">
                        <?php echo $register_success; ?>
                    </div>
                <?php endif; ?>
            </form>
            <div class="signup">
                <span class="signup">Bạn đã có tài khoản?
                    <label for="check">Đăng nhập</label>
                </span>
            </div>
        </div>
    </div>
</body>

</html>
