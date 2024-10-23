<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <title>Document</title>
</head>

<body>
    <!-- Navbar -->
    <div class="main-header">
        <div class="container">
            <div class="box-header">
                <div class="row align-items-center">
                    <div class="col-6 col-lg-3 col-md-4 header-logo">
                        <a class="navbar-brand me-auto" href="./index.php">
                            <img src="admin/assets/img/loo.png" alt="Logo">
                        </a>

                    </div>
                    <div class="col-12 col-md-12 col-lg-6 header-mid">

                    </div>
                    <div class="col-6 col-lg-3 col-md-8 header-right">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header">
        <div class="text-box">
            <h3 class="heading-primary">
                <span class="heading-primary__main">Zatan Shop</span>
                <span class="heading-primary__sub">Cửa hàng công nghệ uy tín số 1 tại Việt Nam</span>
            </h3>
        </div>
        <nav class="navbar navbar-expand-lg fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand me-auto" href="./index.php">
                    <img src="admin/assets/img/loo.png" alt="Logo">
                </a>
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <a href="index.php" class="logo-link">
                            <img src="admin/assets/img/logo.png" alt="Logo" class="offcanvas-logo">
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>


                    <div class="offcanvas-body">
                        <form class="d-flex search-form" action="all-products.php" method="GET" role="search">
                            <div class="input-group">
                                <input class="form-control search-input" type="search" name="search" placeholder="Tìm kiếm" aria-label="Search">
                                <button class="btn search-button" type="submit">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </form>
                        <ul class="navbar-nav justify-content-center flex-grow-1 py-3">
                            <li class="nav-item">
                                <a class="nav-link mx-lg-2 my-lg-2 <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="./index.php">TRANG CHỦ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mx-lg-2 my-lg-2 <?php echo ($current_page == 'tra-gop.php') ? 'active' : ''; ?>" href="./tra-gop.php">HỖ TRỢ TRẢ GÓP</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mx-lg-2 my-lg-2 <?php echo ($current_page == 'chinh-sach-kinh-doanh.php') ? 'active' : ''; ?>" href="./chinh-sach-kinh-doanh.php">GIÁ ƯU ĐÃI NHẤT</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mx-lg-2 my-lg-2 <?php echo ($current_page == 'news.php') ? 'active' : ''; ?>" href="./news.php">TIN CÔNG NGHỆ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mx-lg-2 my-lg-2 <?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>" href="#" id="cart-link">GIỎ HÀNG</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <a href="./info.php" class="login-button">Tài Khoản</a>
                <button class="navbar-toggler pe-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>
    </div>

    <!-- Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bạn cần đăng nhập để xem giỏ hàng.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnLogin">Đăng nhập</button>
                <button type="button" class="btn btn-secondary" id="btnHome">Trang chủ</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script type="text/javascript">
    $(document).ready(function() {
        $('#cart-link').click(function(e) {
            e.preventDefault(); // Ngăn chặn liên kết mặc định

            // Kiểm tra trạng thái đăng nhập bằng PHP
            <?php if (!isset($_SESSION['user_id'])): ?>
                $('#loginModal').modal('show');
            <?php else: ?>
                window.location.href = "./cart.php";
            <?php endif; ?>
        });

        $("#btnLogin").click(function() {
            window.location.href = "login.php";
        });

        $("#btnHome").click(function() {
            window.location.href = "index.php";
        });
    });
</script>
</body>

</html>