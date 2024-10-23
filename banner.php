<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<body>
    <?php
    include_once 'header.php';
    ?>
    <section class="banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-9 col-md-9 col-12">
                    <?php
                    include_once 'dbconnect.php'; // Include your database connection

                    // Query the database to get the banners
                    $query = "SELECT * FROM banners"; // Change 'banners' to your actual table name
                    $result = mysqli_query($conn, $query);

                    // Initialize the $banners array if the query was successful
                    $banners = [];
                    if ($result && mysqli_num_rows($result) > 0) {
                        $banners = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    } else {
                        echo "No banners found.";
                    }
                    ?>
                    <div id="carouselExample" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                        <div class="carousel-inner">
                            <?php
                            $isActive = true;
                            foreach ($banners as $banner) {
                                // Đường dẫn đầy đủ tới ảnh banner
                                $imagePath = 'admin/assets/img/imgbanners/' . basename($banner['banner_image']);

                                echo '<div class="carousel-item' . ($isActive ? ' active' : '') . '">';
                                echo '<img src="' . $imagePath . '" class="d-block w-100" alt="' . $banner['banner_name'] . '">';
                                echo '<div class="carousel-caption d-none d-md-block">';
                                echo '</div>';
                                echo '</div>';
                                $isActive = false;
                            }
                            ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-12">
                    <?php
                    include_once 'dbconnect.php'; // Kết nối cơ sở dữ liệu

                    // Truy vấn lấy 3 subbanner mới nhất
                    $subbanner_query = "SELECT * FROM subbanners ORDER BY created_at DESC LIMIT 4"; // Thay 'created_at' bằng cột thời gian tương ứng

                    $subbanner_result = mysqli_query($conn, $subbanner_query);

                    // Kiểm tra xem có dữ liệu không
                    if ($subbanner_result && mysqli_num_rows($subbanner_result) > 0) {
                        echo '<div class="row row-banner">';

                        // Duyệt qua từng bản ghi banner phụ
                        while ($subbanner = mysqli_fetch_assoc($subbanner_result)) {
                            // Đường dẫn đầy đủ tới ảnh banner phụ
                            $subbannerImagePath = 'admin/assets/img/imgsubbanners/' . basename($subbanner['subbanner_image']);

                            echo '<div class="col-lg-12 col-md-12 col-6 py-2">';
                            echo '<a class="banner-right" href="' . $subbanner['link'] . '">';
                            echo '<div class="image-wrapper">'; // Thêm lớp wrapper
                            echo '<img src="' . $subbannerImagePath . '" alt="' . $subbanner['subbanner_name'] . '" class="img-fluid">';
                            echo '<div class="overlay-subbanner"></div>'; // Lớp phủ cho subbanner
                            echo '</div>'; // Đóng div.wrapper
                            echo '</a>';
                            echo '</div>';
                        }

                        echo '</div>';
                    } else {
                        echo "No sub-banners found.";
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

</body>
</body>
</html>