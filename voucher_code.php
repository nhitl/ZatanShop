<?php
// Include file kết nối CSDL
include_once 'dbconnect.php';

// Truy vấn CSDL để lấy voucher còn hiệu lực
$query = "SELECT voucher_code, description, discount_percentage, expiry_date FROM vouchers WHERE status = 'active' AND expiry_date >= CURDATE()";
$result = mysqli_query($conn, $query);

// Kiểm tra xem truy vấn có thành công hay không
if (!$result) {
    die("Truy vấn thất bại: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã Giảm Giá Ngày Hôm Nay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <section class="voucher-page py-4">
        <div class="container">
            <h2 class="text-center mb-4">
                <svg height="40" width="200" xmlns="http://www.w3.org/2000/svg">
                    <text x="5" y="30" fill="none" stroke="red" font-size="35">Zatan Shop</text>
                </svg>
                <br>
                <span class="text-center title2 mb-4">Mã giảm giá ngày hôm nay</span>
            </h2>

            <!-- Swiper -->
            <div class="swiper-container-voucher">
                <div class="swiper-wrapper">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="swiper-slide">
                            <div class="voucher-badge mb-1">
                                <div class="voucher-image">
                                    <img src="assets/img/voucher_img.png" alt="Hộp quà" />
                                </div>
                                <div class="voucher-info">
                                    <h5 class="text-code"><?= htmlspecialchars($row['voucher_code']) ?></h5>
                                    <p class="descript" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars($row['description']) ?></p>
                                    <small class="text-date">Hết hạn: <?= htmlspecialchars(date('d/m/Y', strtotime($row['expiry_date']))) ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="swiper-button-next-3">
                    <svg width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2.13003" y="29" width="38" height="38" transform="rotate(-45 2.13003 29)" stroke="black" fill="#fff" stroke-width="2"></rect>
                        <rect class="rect-hover" x="8" y="29.2133" width="30" height="30" transform="rotate(-45 8 29.2133)" fill="black"></rect>
                        <path d="M18.5 29H39.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M29 18.5L39.5 29L29 39.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
                <div class="swiper-button-prev-3">
                    <svg width="58" height="58" viewBox="0 0 58 58" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2.13003" y="29" width="38" height="38" transform="rotate(-45 2.13003 29)" stroke="black" fill="#fff" stroke-width="2"></rect>
                        <rect class="rect-hover" x="8" y="29.2133" width="30" height="30" transform="rotate(-45 8 29.2133)" fill="black"></rect>
                        <path d="M39.5 29H18.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M29 39.5L18.5 29L29 18.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
            </div>
            <!-- Kết thúc Swiper -->
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script>
        // Khởi tạo Swiper
        const swiper = new Swiper('.swiper-container-voucher', {
            slidesPerView: 3, // Mặc định hiển thị 3 slide
            spaceBetween: 10, // Khoảng cách giữa các slide
            navigation: {
                nextEl: '.swiper-button-next-3',
                prevEl: '.swiper-button-prev-3',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            loop: true, // Vòng lặp cho Swiper
            breakpoints: {
                // Điều chỉnh số lượng slide tùy theo kích thước màn hình
                100: {
                    slidesPerView: 1, // Màn hình nhỏ: hiển thị 1 slide
                },
                693: {
                    slidesPerView: 2, // Màn hình trung bình: hiển thị 2 slide
                },
                992: {
                    slidesPerView: 3, // Màn hình lớn: hiển thị 3 slide
                },
            },
        });
    </script>
</body>

</html>
