<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết tin tức</title>
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="icon" href="admin/assets/img/favicon.ico.png" type="image/png">
    

</head>

<body>
    <?php
    include_once 'dbconnect.php';
    include_once 'header.php';
    include_once 'contact_button.php';

    if (isset($_GET['news_id'])) {
        $news_id = $_GET['news_id'];
        $query = "SELECT * FROM news WHERE news_id = $news_id";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
    ?>
            <section class="news-details">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 col-md-10">
                            <h2 class="text-center mb-4"><?= $row['news_name'] ?></h2>
                            <img src="admin/admin/<?= $row['news_image'] ?>" 
                                 alt="<?= $row['news_name'] ?>" 
                                 class="news-image img-fluid mx-auto d-block mb-4">
                            <div class="news-content">
                                <p><?= nl2br($row['content']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
    <?php
        } else {
            echo '<p class="text-center text-danger">Không tìm thấy tin tức.</p>';
        }
    } else {
        echo '<p class="text-center text-warning">ID tin tức không hợp lệ.</p>';
    }
    ?>

    <!-- Footer -->
    <?php include_once 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
