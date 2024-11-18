<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pop-up Ads</title>
    <style>
        /* Lớp phủ toàn màn hình */
        #overlay {
            display: block;
            /* Đảm bảo lớp phủ luôn hiển thị */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            /* Phủ đen mờ */
            z-index: 1050;
            /* Cả lớp phủ và pop-up đều có z-index cao */
        }

        /* Định dạng pop-up */
        #popupAd {
            display: block;
            /* Đảm bảo pop-up luôn hiển thị */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1060;
            /* Đảm bảo pop-up luôn trên lớp phủ */
            width: 80%;
            /* Kích thước hình ảnh */
            max-width: 450px;
        }


        #popupAd img {
            width: 100%;
            border-radius: 10px;
        }

        #popupAd .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #fff;
            border: none;
            color: #000;
            font-size: 18px;
            cursor: pointer;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <!-- Lớp phủ -->
    <div id="overlay"></div>

    <!-- Nội dung pop-up -->
    <div id="popupAd">
        <button class="close-btn" onclick="closePopup()">×</button>
        <a href="deal.php">
            <img src="assets/img/popup2.png" alt="Quảng cáo">
        </a>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const popupAd = document.getElementById('popupAd');
            const overlay = document.getElementById('overlay');

            console.log(popupAd); // Kiểm tra nếu popupAd có tồn tại
            console.log(overlay); // Kiểm tra nếu overlay có tồn tại

            if (popupAd && overlay) {
                popupAd.style.display = 'block';
                overlay.style.display = 'block';
            }
        });


        function closePopup() {
            const popupAd = document.getElementById('popupAd');
            const overlay = document.getElementById('overlay');

            if (popupAd && overlay) {
                popupAd.style.display = 'none';
                overlay.style.display = 'none';
            }
        }
    </script>
</body>

</html>