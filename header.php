<?php
include_once('dbconnect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Khởi tạo biến tránh lỗi Undefined variable
$user_id = null; 
$user_logged_in_header = isset($_SESSION['user_id']);
$user_full_name_header = '';

if ($user_logged_in_header) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT full_name FROM users WHERE user_id = ?";
    $stmt_header = $conn->prepare($query);
    $stmt_header->bind_param("i", $user_id);
    $stmt_header->execute();
    $result_header = $stmt_header->get_result();

    if ($row_header = $result_header->fetch_assoc()) {
        $user_full_name_header = $row_header['full_name'];
    }
    $stmt_header->close();
}

$unread_count = 0;

// Kiểm tra nếu $user_id có giá trị thì mới truy vấn
if (!is_null($user_id)) {
    $sql_notifi = "SELECT COUNT(*) AS unread_count FROM notification_orders WHERE user_id = ? AND status = 'unread'";
    $stmt_notifi = $conn->prepare($sql_notifi);
    $stmt_notifi->bind_param("i", $user_id);
    $stmt_notifi->execute();
    $result_notifi = $stmt_notifi->get_result();
    
    if ($row = $result_notifi->fetch_assoc()) {
        $unread_count = intval($row['unread_count']);
    }
    $stmt_notifi->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" href="admin/assets/img/favicon.ico.png" type="image/png">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <title>Header</title>
</head>

<body>
    <?php
    include_once 'loading_bar.php';
    ?>
    <header class="header">
        <div class="topbar">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9 header-promo">
                        <div class="notification">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <ul class="notification-list">
                            <?php
                            // Truy vấn để lấy thông báo
                            $sql_notifications = "SELECT title, message FROM notifications WHERE status = 'active' ORDER BY created_at DESC";
                            $notificationsResult = $conn->query($sql_notifications);

                            // Hiển thị thông báo
                            if ($notificationsResult->num_rows > 0) {
                                while ($row = $notificationsResult->fetch_assoc()) {
                                    echo '<li class="notification-item">';
                                    echo '<strong class="notification-title">' . htmlspecialchars($row['title']) . '</strong>: ' . htmlspecialchars($row['message']);
                                    echo '</li>';
                                }
                            } else {
                                echo '<li class="notification-item">Không có thông báo nào.</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="col-lg-3 header-hotline sm-hidden">
                        <i class="fa-solid fa-phone-volume"></i>
                        <a title="Điện thoại: 0364 313 062" href="tel:0364313062">
                            <div class="text-box">
                                <span class="acc-text-small">Tư vấn mua hàng</span>
                                <span class="acc-text">0364 313 062</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-header">
            <div class="container">
                <div class="box-header">
                    <div class="row align-items-center">
                        <!-- Logo -->
                        <div class="col-6 col-lg-3 col-md-4 order-md-1 header-logo">
                            <a href="index.php" class="logo-wrapper" title="Zatan Shop">
                                <img src="admin/assets/img/logoshop1.png" alt="Logo">
                            </a>
                        </div>
                        <!-- Search Form -->
                        <div class="col-12 col-md-12 col-lg-6 order-3 order-md-3 order-lg-2 header-mid">
                            <form class="d-flex search-form" action="all-products.php" method="GET" role="search">
                                <div class="input-group">
                                    <input class="form-control search-input" type="search" name="search" placeholder="Tìm kiếm" aria-label="Search">
                                    <button class="btn search-button" type="submit">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- Right Section -->
                        <div class="col-6 col-lg-3 col-md-8 order-md-2 order-lg-3 header-right">
                            <div class="location-stores d-flex justify-content-end">
                                <a href="vi-tri-cua-hang" class="text-center" title="Cửa hàng">
                                    <span class="box-icon">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </span>
                                    <span class="item-title">Cửa hàng</span>
                                </a>
                                <a id="cart-icon" href="./gio-hang" class="text-center" title="Giỏ hàng">
                                    <span class="box-icon position-relative">
                                        <i class="fa-solid fa-shopping-cart"></i>
                                        <span class="count item position-absolute top-0 start-100 translate-middle badge">
                                            0
                                        </span>
                                    </span>
                                    <span class="item-title">Giỏ hàng</span>
                                </a>
                                <a href="<?php echo $user_logged_in_header ? 'tai-khoan' : 'login.php'; ?>" class="text-center" title="Tài khoản">
                                    <span class="box-icon">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <span class="item-title">
                                        <?php echo $user_logged_in_header ? htmlspecialchars($user_full_name_header) : 'Đăng nhập'; ?>
                                    </span>
                                </a>
                                <a id="notifi-icon" href="thong-bao" class="position-relative text-center">
                                    <span class="box-icon">
                                        <i class="fa-regular fa-message"></i>
                                    </span>
                                    <span class="item-title">Thông báo</span>
                                    <?php if ($unread_count > 0): ?>
                                        <span class="notification-badge"></span>
                                    <?php endif; ?>
                                </a>

                                <button class="navbar-toggler pe-0 " type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"><i class="fa-solid fa-bars"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-menu">
            <nav class="navbar navbar-expand-lg ">
                <div class="container-fluid">
                    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                        <div class="offcanvas-header">
                            <a href="index.php" class="logo-link">
                                <img width="120px" src="admin/assets/img/logoshop2.png" alt="Logo" class="offcanvas-logo">
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            <ul class="navbar-nav justify-content-center flex-grow-1">
                                <li class="nav-item">
                                    <a class="nav-link mx-lg-2<?php echo ($current_page == 'index.php') ? ' active' : ''; ?>" href="./index.php"><i class="fa-solid fa-diamond"></i> Trang chủ</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mx-lg-2<?php echo ($current_page == 'all-products.php') ? ' active' : ''; ?>" href="./san-pham"><i class="fa-solid fa-diamond"></i> Sản phẩm</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mx-lg-2 <?php echo ($current_page == 'tra-gop.php') ? ' active' : ''; ?>" href="./tra-gop"><i class="fa-solid fa-diamond"></i> Hỗ trợ trả góp</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mx-lg-2<?php echo ($current_page == 'news.php') ? ' active' : ''; ?>" href="./tin-tuc-moi-nhat"><i class="fa-solid fa-diamond"></i> Tin công nghệ</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mx-lg-2<?php echo ($current_page == 'chinh-sach-kinh-doanh.php') ? ' active' : ''; ?>" href="chinh-sach"><i class="fa-solid fa-diamond"></i> Chính sách</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    <!-- Modal -->
    <div class="modal fade" id="universalModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modalhead" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thông báo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Nội dung của modal sẽ thay đổi tại đây -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnLoginUniversal">Đăng nhập</button>
                    <button type="button" class="btn btn-secondary" id="btnHomeUniversal">Trang chủ</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Khi người dùng nhấn vào biểu tượng giỏ hàng
            $('#cart-icon').click(function(e) {
                e.preventDefault(); // Ngăn chặn liên kết mặc định

                <?php if (!isset($_SESSION['user_id'])): ?>
                    // Nếu chưa đăng nhập, hiển thị modal và điều chỉnh nội dung cho giỏ hàng
                    $('#modalTitle').text('Thông báo');
                    $('#modalBody').text('Bạn cần đăng nhập để xem giỏ hàng.');
                    $('#universalModal').modal('show');
                <?php else: ?>
                    window.location.href = "./gio-hang"; // Nếu đã đăng nhập, điều hướng đến giỏ hàng
                <?php endif; ?>
            });

            // Khi người dùng nhấn vào liên kết "Tài khoản"
            $('a[title="Tài khoản"]').click(function(e) {
                e.preventDefault(); // Ngăn chặn liên kết mặc định

                <?php if (!isset($_SESSION['user_id'])): ?>
                    // Nếu chưa đăng nhập, hiển thị modal và điều chỉnh nội dung cho tài khoản
                    $('#modalTitle').text('Thông báo');
                    $('#modalBody').text('Bạn cần đăng nhập để xem thông tin tài khoản.');
                    $('#universalModal').modal('show');
                <?php else: ?>
                    window.location.href = "tai-khoan"; // Nếu đã đăng nhập, điều hướng đến thông tin tài khoản
                <?php endif; ?>
            });

            $('#notifi-icon').click(function(e) {
                e.preventDefault(); // Ngăn chặn liên kết mặc định

                <?php if (!isset($_SESSION['user_id'])): ?>
                    // Nếu chưa đăng nhập, hiển thị modal và điều chỉnh nội dung cho giỏ hàng
                    $('#modalTitle').text('Thông báo');
                    $('#modalBody').text('Bạn chưa đăng nhập');
                    $('#universalModal').modal('show');
                <?php else: ?>
                    window.location.href = "./thong-bao"; // Nếu đã đăng nhập, điều hướng đến giỏ hàng
                <?php endif; ?>
            });

            // Điều hướng đến trang đăng nhập
            $('#btnLoginUniversal').click(function() {
                window.location.href = "login.php";
            });

            // Điều hướng đến trang đăng ký (chỉ hiện khi cần thiết)
            $('#btnRegisterUniversal').click(function() {
                window.location.href = "register.php";
            });

            // Điều hướng về trang chủ
            $('#btnHomeUniversal').click(function() {
                window.location.href = "index.php";
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            let $notifications = $('.notification-item');
            let index = 0;

            function showNotification() {
                // Ẩn tất cả các thông báo trước
                $notifications.each(function() {
                    $(this).removeClass('active');
                });

                // Hiển thị thông báo hiện tại
                $notifications.eq(index).addClass('active');
                index = (index + 1) % $notifications.length;
            }

            showNotification(); // Hiển thị thông báo đầu tiên
            setInterval(showNotification, 5000);
        });

        // Hàm cập nhật số lượng sản phẩm trong giỏ hàng
        function updateCartCount() {
            $.ajax({
                url: 'get_cart_count.php', // Tạo một file PHP để lấy số lượng giỏ hàng
                type: 'GET',
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.status === 'success') {
                        $('#cart-icon .count').text(result.count);
                    }
                },
                error: function() {
                    console.error('Không thể lấy số lượng giỏ hàng.');
                }
            });
        }
        $(document).ready(function() {
            updateCartCount(); // Cập nhật số lượng giỏ hàng khi tải trang
        });
    </script>
    <script>
        // Script cho main-header
        var mainHeader = document.querySelector('.main-header');
        var stickyMainHeader = mainHeader.offsetTop;

        function stickyMainHeaderFunction() {
            if (window.innerWidth < 992) {
                if (window.pageYOffset > stickyMainHeader) {
                    mainHeader.style.position = 'fixed';
                    mainHeader.style.top = '0';
                    mainHeader.style.width = '100%';
                    mainHeader.style.zIndex = '1000';
                } else {
                    mainHeader.style.position = 'relative';
                    mainHeader.style.boxShadow = 'none';
                }
            } else {
                mainHeader.style.position = 'relative';
                mainHeader.style.boxShadow = 'none';
            }
        }

        // Script cho header-menu
        var headerMenu = document.querySelector('.header-menu');
        var sticky = headerMenu.offsetTop;

        function stickyHeader() {
            if (window.pageYOffset > sticky) {
                headerMenu.style.position = 'fixed';
                headerMenu.style.top = '0';
                headerMenu.style.width = '100%';
                headerMenu.style.zIndex = '1000';
            } else {
                headerMenu.style.position = 'relative';
                headerMenu.style.boxShadow = 'none';
            }
        }

        // Thêm sự kiện cuộn cho cả hai phần header-menu và main-header
        window.addEventListener('scroll', function() {
            stickyMainHeaderFunction();
            stickyHeader();
        });

        // Kiểm tra lại khi thay đổi kích thước màn hình
        window.addEventListener('resize', function() {
            stickyMainHeaderFunction();
        });
    </script>


</body>

</html>