<?php
include_once 'dbconnect.php';

// Thực thi câu lệnh SQL chỉ để lấy danh mục
$sql = "SELECT category_id, category_name, category_image FROM categories";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Slider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="style.css">

</head>

<body>
<?php include_once 'header.php';
    ?>
    <section class="category-swiper">
    <div class="container mt-4">
        <h1 class="slogen">
            <p>Danh Mục Sản Phẩm Tại Zatan Shop</p>
        </h1>
        <div class="swiper-container-category">
            <div class="swiper-wrapper">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $categoryId = $row['category_id'];
                        $categoryName = $row['category_name'];
                        $categoryImage = $row['category_image'];

                        echo '<div class="swiper-slide swiper-slide-active">';
                        echo '<a href="all-products.php?category=' . $categoryId . '">';
                        echo '<img src="admin/' . $categoryImage . '" alt="' . $categoryName . '">';
                        echo '<div class="category-name">' . $categoryName . '</div>';
                        echo '</a>';
                        echo '</div>';
                    }
                } else {
                    echo "Không có dữ liệu";
                }
                ?>
            </div>

            <!-- Nút điều hướng -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script>
        const swiper1 = new Swiper('.swiper-container-category', {
            slidesPerView: 4,
            spaceBetween: 30,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                100: {
                    slidesPerView: 2,
                    spaceBetween: 0,
                },
                640: {
                    slidesPerView: 2,
                    spaceBetween: 0,
                },
                768: {
                    slidesPerView: 4,
                    spaceBetween: 0,
                },

                1024: {
                    slidesPerView: 6,
                    spaceBetween: 0,
                },
            },
        });

        document.addEventListener('DOMContentLoaded', function() {
            function getRandomColor() {
                const letters = '0123456789ABCDEF';
                let color = '#';
                for (let i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }

            function changeTextColor() {
                const elements = document.querySelectorAll('.slogen p');
                elements.forEach(element => {
                    element.style.color = getRandomColor();
                });
            }

            setInterval(changeTextColor, 500); // Thay đổi màu sắc mỗi 500ms
        });
    </script>

</body>

</html>