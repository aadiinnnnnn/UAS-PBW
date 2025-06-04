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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/order.css">
</head>

<body class="order-success-page">

    <header class="header-custom sticky-top">
        <nav class="container navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" class="" href="indexuser.php"><img src="../image/logo mover.png" alt=""
                    style="height: 70px;"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="indexuser.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a href="pilihan.php" class="nav-link order-btn-nav">Layanan</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle profile-icon-link" href="#" id="navbarDropdownUser"
                            role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="../assets/img/default-profile.png" class="profile-icon-sm" alt="User Profile" />
                            <span class="username-display"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownUser">
                            <a class="dropdown-item" href="profil.php">Profil</a>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="success-card-container">
        <div class="success-card-standalone">
            <div class="success-icon-standalone"><i class="fas fa-check-circle"></i></div>
            <h2 class="success-title-standalone">Pesanan Kost Anda Telah Diterima!</h2>
            <p class="success-message-standalone">
                Terima kasih telah memilih MOVER. Pesanan Anda sedang kami proses
                Tim kami akan segera menghubungi Anda untuk konfirmasi lebih lanjut.
            </p>
            <div class="order-details-summary-standalone">
                <h6>Detail Pesanan Anda:</h6>
                <p><strong>Nomor Pesanan:</strong> <span id="orderId">Memuat...</span></p>
                <p><strong>Nama Kost:</strong> <span id="summaryKostName">Memuat...</span></p>
                <p><strong>Alamat Kost:</strong> <span id="summaryKostAddress">Memuat...</span></p>
                <p><strong>Tanggal Check-in:</strong> <span id="tanggalSewa">Memuat...</span></p>
                <p><strong>Durasi Sewa:</strong> <span id="durasiSewaDisplay">Memuat...</span></p>
                <p><strong>Total Pembayaran:</strong> Rp <span id="totalBiaya">Memuat...</span></p>
                <p><strong>Metode Pembayaran:</strong> <span id="metodePembayaran">Memuat...</span></p>
                <p><strong>Catatan Tambahan:</strong> <span id="catatanTambahan">Memuat...</span></p>
            </div>
            <p class="mt-4 text-muted" style="font-size: 0.9rem;">
                Harap simpan detail pesanan Anda. Jika ada pertanyaan, jangan ragu untuk menghubungi layanan pelanggan
                kami.
            </p>
            <a href="carikost.php" class="btn btn-back-home-standalone">Cari Kost Lain</a>
            <a href="indexuser.php" class="btn btn-secondary mt-3 ml-2">Kembali ke Dashboard</a>
            <a href="review_form.php?order_id=<?php echo htmlspecialchars($orderDetailsFromSession['orderId'] ?? 'N/A'); ?>&order_type=kost"
                class="btn btn-info mt-3 ml-2">Berikan Ulasan</a>
        </div>
    </main>

    <footer class="footer-custom text-center py-4">
        <div class="container">
            <p>&copy; <span id="tahunSekarangSuccess"><?php echo date("Y"); ?></span> MOVER. Hak Cipta Dilindungi.</p>

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