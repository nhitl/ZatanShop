<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trả góp qua thẻ tín dụng MPOS</title>
    <style>
        /* Căn chỉnh nội dung bên trong cột */
        .tra-gop .container {
            padding-top: 60px;
        }

        .container .row.bg-warning {
            padding: 33px;
            border-radius: 8px;
        }

        .container .row.bg-warning .col-12 .col-md-6 {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        /* Tạo viền cho phần HƯỚNG DẪN TRẢ GÓP */
        .container .row.bg-warning .border {
            padding: 20px;
            border: none;
            /* Sử dụng màu vàng nhạt tương tự lớp bg-warning */
            border-radius: 8px;
        }

        /* Tiêu đề */
        .container .row.bg-warning h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #343a40;
            /* Màu chữ đậm hơn */
        }

        /* Nội dung mô tả */
        .container .row.bg-warning h4 {
            font-size: 1.5rem;
            font-weight: 400;
            color: #343a40;
        }

        /* Căn chỉnh hình ảnh */
        .container .row.bg-warning img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            object-fit: contain;
            /* Đảm bảo hình ảnh không bị méo */
        }

        .row .col-12 h2 {
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <?php include_once 'header.php';
    include_once 'contact_button.php'; ?>
    <section class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Hỗ trợ trả góp</li>
                </ol>
            </nav>
        </div>
    </section>
    <section class="tra-gop">
        <div class="container">
            <div class="row bg-warning">
                <div class="col-12 col-md-8">
                    <div class="row">
                        <h2>HƯỚNG DẪN TRẢ GÓP</h2>

                    </div>
                    <div class="row">
                        <h4>Trả góp không cần thế chấp tài sản và trả góp qua thẻ tín dụng , thủ tục “SIÊU NHANH - SIÊU ĐƠN GIẢN” tại Zatan Shop</h4>
                    </div>

                </div>
                <div class="col-4 d-none d-md-block">
                    <img src="assets\img\logo.png" alt="logo">
                </div>

            </div>
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center text-uppercase mb-4">Trả góp qua thẻ tín dụng MPOS</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h4>1. Áp dụng</h4>
                    <p>Khách hàng có nhu cầu mua sản phẩm tại Zatan Shop và trả góp qua thẻ tín dụng với giá trị đơn hàng từ 3.000.000 vnđ trở lên (sau khi trừ các khuyến mại). Khách hàng vẫn nhận đủ các chương trình khuyến mãi mà Zatan Shop đang áp dụng đối với từng sản phẩm.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h4>2. Điều kiện</h4>
                    <ul>
                        <li>Khách hàng cần chuẩn bị CMND/CCCD + thẻ tín dụng (cả 2 đều phải chính chủ).</li>
                        <li>Hạn mức thẻ tín dụng cần phải lớn hơn tổng số tiền của đơn hàng và phí chuyển đổi trả góp (tùy theo kỳ hạn và ngân hàng phát hành thẻ theo bảng bên dưới).</li>
                        <li>Khách hàng có thể chọn trả góp toàn bộ giá trị đơn hàng hoặc trả góp một phần giá trị đơn hàng.</li>
                        <li>Đối với một số sản phẩm, khách hàng phải tiến hành đặt cọc theo quy định của Zatan Shop.</li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h4>3. Hình Thức Đăng Ký</h4>
                    <p><strong>Cách 1: Đăng ký tại Showroom Zatan Shop</strong><br>
                        Khách hàng tới Showroom Zatan Shop để được tư vấn sản phẩm, thực hiện thanh toán trả góp tại Showroom và nhận sản phẩm.</p>

                    <p><strong>Cách 2: Đăng ký thông qua Hotline</strong><br>
                        Khách hàng liên hệ Zatan Shop qua Hotline để được tư vấn sản phẩm, đặt hàng, nhận sản phẩm và thực hiện thanh toán trả góp tại nhà.</p>
                </div>
            </div>
        </div>


        <div class="row mx-5">
            <div class="col-md-6 col-12">
                <h2 class="text-center text-uppercase mb-4">Phí giao dịch</h2>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Loại thẻ</th>
                            <th>Phí</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Thẻ nội địa ATM/ NAPAS</td>
                            <td>Liên Hệ</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Thẻ Visa/Master/Unionpay phát hành tại Việt Nam</td>
                            <td>Liên Hệ</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Thẻ Visa, Master, JCB, CUP phát hành tại nước ngoài</td>
                            <td>Liên Hệ</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>QRCode mVisa, MasterPass, JCBQR, UPIQR</td>
                            <td>Liên Hệ</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>QRCode QR-Pay, ViettelPay, VinID</td>
                            <td>Liên Hệ</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Thanh toán thẻ từ xa</td>
                            <td>Liên Hệ</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6 col-12">
                <h2 class="text-center text-uppercase mb-4">Chính sách phí giao dịch</h2>
                <table class="table table-bordered table-custom">
                    <tbody>
                        <tr>
                            <td>
                                <strong>Chính sách phí trả góp</strong><br><br>
                                <em>Chú ý:</em> Vào thời điểm thanh toán, số dư hạn mức tiêu dùng trong thẻ tín dụng của chủ thẻ phải lớn hơn hoặc bằng tổng giá trị giao dịch.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Danh sách ngân hàng hỗ trợ trả góp và biểu phí chuyển đổi trả góp</strong><br><br>
                                Zatan Shop ghi nhận trả góp qua MPOS, áp dụng cho khách hàng CÁ NHÂN có thẻ tín dụng (Credit Card) của 1 trong 22 ngân hàng sau: SacomBank, VIB, HSBC, SCB, VPBank, Shinhan - ANZ, Maritime Bank, Eximbank, Techcombank, Citibank, SeaBank, Standard Chartered, Kiên Long, Citibank, OCB, Fecredit, TPBank, BIDV, VCB, ACB, Nam Á Bank, MB….
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php include_once 'footer.php'; ?>
</body>

</html>