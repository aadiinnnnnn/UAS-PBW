<?php
require 'session.php'; // Memastikan pengguna sudah login

$orderDetailsFromSession = null;
// Coba ambil detail pesanan dari session PHP
if (isset($_SESSION['latestKostOrderDetails'])) { //
    $orderDetailsFromSession = $_SESSION['latestKostOrderDetails']; //
    // Idealnya, unset session di sini jika sudah tidak diperlukan lagi dan
    // Anda tidak bergantung pada JS untuk membersihkannya dari localStorage sebagai sumber utama.
    // Jika backend (proses_order_kost.php) sudah mengisi session ini,
    // dan halaman sukses ini satu-satunya yang membacanya, maka aman untuk di-unset.
    // unset($_SESSION['latestKostOrderDetails']); // Aktifkan jika yakin
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVER - Pesanan Kost Berhasil</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
    body.order-success-page {
        background-color: #f0f2f5;
        /* */
        display: flex;
        /* */
        flex-direction: column;
        /* */
        min-height: 100vh;
        /* */
    }

    .order-success-header .navbar-brand,
    .order-success-header .nav-link {
        color: white !important;
        /* */
    }

    .order-success-header .btn-logout-custom {
        background-color: #FFD700;
        /* */
        color: #2f4f4f !important;
        /* */
        border: none;
        /* */
        padding: 8px 15px;
        /* */
        border-radius: 5px;
        /* */
        font-weight: 500;
        /* */
    }

    .header-custom .profile-icon-link .profile-icon-sm {
        /* */
        display: flex;
        /* */
        align-items: center;
        /* */
        justify-content: center;
        /* */
        width: 35px;
        /* */
        height: 35px;
        /* */
        border-radius: 50%;
        /* */
        overflow: hidden;

        border: 2px solid white;
    }

    .header-custom .profile-icon-link .profile-icon-sm img {
        /* */
        width: 100%;
        /* */
        height: 100%;
        /* */
        object-fit: cover;
        /* */
    }

    .success-card-container {
        flex-grow: 1;
        /* */
        display: flex;
        /* */
        align-items: center;
        /* */
        justify-content: center;
        /* */
        padding: 20px;
        /* */
        margin-top: 70px;
        /* */
        margin-bottom: 20px;
        /* */
    }

    .success-card-standalone {
        background-color: #fff;
        /* */
        color: #333;
        /* */
        border-radius: 15px;
        /* */
        padding: 35px 40px;
        /* */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        /* */
        max-width: 650px;
        /* */
        width: 100%;
        /* */
        text-align: center;
        /* */
    }

    .success-icon-standalone {
        font-size: 4.5rem;
        /* */
        color: #28a745;
        /* */
        margin-bottom: 20px;
        /* */
        line-height: 1;
        /* */
    }

    .success-title-standalone {
        font-size: 2rem;
        /* */
        font-weight: 700;
        /* */
        color: #367A83;
        /* */
        margin-bottom: 15px;
        /* */
    }

    .success-message-standalone {
        font-size: 1.1rem;
        /* */
        color: #495057;
        /* */
        margin-bottom: 25px;
        /* */
    }

    .order-details-summary-standalone {
        text-align: left;
        /* */
        margin-top: 20px;
        /* */
        border-top: 1px solid #dee2e6;
        /* */
        padding-top: 20px;
        /* */
    }

    .order-details-summary-standalone h6 {
        font-size: 1.2rem;
        /* */
        color: #367A83;
        /* */
        margin-bottom: 15px;
        /* */
        font-weight: 600;
        /* */
    }

    .order-details-summary-standalone p {
        margin-bottom: 10px;
        /* */
        font-size: 1rem;
        /* */
        color: #333;
        /* */
    }

    .order-details-summary-standalone p strong {
        color: #495057;
        /* */
        min-width: 170px;
        /* */
        display: inline-block;
        /* */
        font-weight: 600;
        /* */
    }

    .btn-back-home-standalone {
        margin-top: 25px;
        /* */
        background-color: #F5A623;
        /* */
        color: white;
        /* */
        padding: 12px 30px;
        /* */
        font-size: 1.05rem;
        /* */
        border-radius: 8px;
        /* */
        text-decoration: none;
        /* */
        font-weight: 600;
        /* */
        border: none;
        /* */
        transition: background-color 0.2s ease;
        /* */
    }

    .btn-back-home-standalone:hover {
        background-color: #db8e1e;
        /* */
    }

    .footer-custom {
        margin-top: auto;
        /* */
    }
    </style>
</head>

<body class="order-success-page">

    <header class="header-custom sticky-top order-success-header">
        <nav class="container navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="indexuser.php">LOGO MOVER</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavSuccess"
                aria-controls="navbarNavSuccess" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavSuccess">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle profile-icon-link" href="#" id="navbarDropdownUser"
                            role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="profile-icon-sm">
                                <img src="../assets/img/default-profile.png" alt="User Profile" />
                            </div>
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownUser">
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="indexuser.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="pilihan_order.php">Order Lagi</a></li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link btn btn-logout-custom">Logout</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="success-card-container">
        <div class="success-card-standalone">
            <div class="success-icon-standalone"><i class="fas fa-check-circle"></i></div> {/* */}
            <h2 class="success-title-standalone">Pesanan Kost Anda Telah Diterima!</h2> {/* */}
            <p class="success-message-standalone">
                Terima kasih telah memilih MOVER. Pesanan Anda sedang kami proses. {/* */}
                Tim kami akan segera menghubungi Anda untuk konfirmasi lebih lanjut. {/* */}
            </p>
            <div class="order-details-summary-standalone">
                <h6>Detail Pesanan Anda:</h6> {/* */}
                <p><strong>Nomor Pesanan:</strong> <span id="orderId">Memuat...</span></p> {/* */}
                <p><strong>Nama Kost:</strong> <span id="summaryKostName">Memuat...</span></p> {/* */}
                <p><strong>Alamat Kost:</strong> <span id="summaryKostAddress">Memuat...</span></p> {/* */}
                <p><strong>Tanggal Check-in:</strong> <span id="tanggalSewa">Memuat...</span></p> {/* */}
                <p><strong>Durasi Sewa:</strong> <span id="durasiSewaDisplay">Memuat...</span></p> {/* */}
                <p><strong>Total Pembayaran:</strong> Rp <span id="totalBiaya">Memuat...</span></p> {/* */}
                <p><strong>Metode Pembayaran:</strong> <span id="metodePembayaran">Memuat...</span></p> {/* */}
                <p><strong>Catatan Tambahan:</strong> <span id="catatanTambahan">Memuat...</span></p> {/* */}
            </div>
            <p class="mt-4 text-muted" style="font-size: 0.9rem;">
                Harap simpan detail pesanan Anda. Jika ada pertanyaan, jangan ragu untuk menghubungi layanan pelanggan
                {/* */}
                kami. {/* */}
            </p>
            <a href="bersih.php" class="btn btn-back-home-standalone">Cari Kost Lain</a>
            <a href="indexuser.php" class="btn btn-secondary mt-3 ml-2">Kembali ke Dashboard</a>
            <a href="review_form.php?order_id=<?php echo htmlspecialchars($orderDetailsFromSession['orderId']); ?>&order_type=kost"
                class="btn btn-info mt-3 ml-2">Berikan Ulasan</a>
        </div>
    </main>

    <footer class="footer-custom text-center py-4">
        <div class="container">
            <p>&copy; <span id="tahunSekarangSuccess"><?php echo date("Y"); ?></span> MOVER. Hak Cipta Dilindungi.</p>
            {/* */}
        </div>
    </footer>

    <script>
    // Menyisipkan detail pesanan dari PHP Session ke variabel JavaScript global
    // Variabel ini akan dibaca oleh order-kost-sukses.js
    const phpOrderDetails = <?php echo json_encode($orderDetailsFromSession); ?>; /* */
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../javascript/order_kost_sukses.js"></script>
</body>

</html>