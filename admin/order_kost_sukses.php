<?php
require 'session.php'; // Memastikan pengguna sudah login

$orderDetailsFromSession = null;
if (isset($_SESSION['latestKostOrderDetails'])) {
    $orderDetailsFromSession = $_SESSION['latestKostOrderDetails'];
    // unset($_SESSION['latestKostOrderDetails']); // Pertimbangkan untuk menghapus session setelah digunakan
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


    <?php
        // Hapus blok <style> internal dari sini jika semua gaya dipindahkan ke file CSS eksternal
    ?>
</head>

<body class="order-success-page">

    <header class="header-custom sticky-top">
        <nav class="container navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="indexuser.php"><img src="../image/logo mover.png" alt="MOVER Logo"
                    style="height: 70px;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
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
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="username-display"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="success-card-container-standalone">
        <div class="success-card-standalone">
            <div class="success-icon-standalone"><i class="fas fa-check-circle"></i></div>
            <h2 class="success-title-standalone">Pesanan Kost Anda Telah Diterima!</h2>
            <p class="success-message-standalone">
                Terima kasih telah memilih MOVER. Pesanan Anda sedang kami proses.
                Tim kami akan segera menghubungi Anda untuk konfirmasi lebih lanjut.
            </p>
            <div class="order-details-summary-standalone">
                <h6 class="text-center">Detail Pesanan Anda:</h6>
                <dl class="row mt-3">
                    <dt class="col-sm-5">Nomor Pesanan:</dt>
                    <dd class="col-sm-7"><span id="orderId">Memuat...</span></dd>

                    <dt class="col-sm-5">Nama Kost:</dt>
                    <dd class="col-sm-7"><span id="summaryKostName">Memuat...</span></dd>

                    <dt class="col-sm-5">Alamat Kost:</dt>
                    <dd class="col-sm-7"><span id="summaryKostAddress">Memuat...</span></dd>

                    <dt class="col-sm-5">Tanggal Check-in:</dt>
                    <dd class="col-sm-7"><span id="tanggalSewa">Memuat...</span></dd>

                    <dt class="col-sm-5">Durasi Sewa:</dt>
                    <dd class="col-sm-7"><span id="durasiSewaDisplay">Memuat...</span></dd>

                    <dt class="col-sm-5">Total Pembayaran:</dt>
                    <dd class="col-sm-7"><strong>Rp <span id="totalBiaya">Memuat...</span></strong></dd>

                    <dt class="col-sm-5">Metode Pembayaran:</dt>
                    <dd class="col-sm-7"><span id="metodePembayaran">Memuat...</span></dd>

                    <dt class="col-sm-5">Catatan Tambahan:</dt>
                    <dd class="col-sm-7"><span id="catatanTambahan">Memuat...</span></dd>
                </dl>
            </div>
            <p class="mt-4 text-muted text-center" style="font-size: 0.9rem;">
                Harap simpan detail pesanan Anda. Jika ada pertanyaan, jangan ragu untuk menghubungi layanan pelanggan
                kami.
            </p>
            <div class="mt-4 d-flex justify-content-center flex-wrap gap-2">
                <a href="carikost.php" class="btn btn-primary">Cari Kost Lain</a>
                <a href="indexuser.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                <a href="review_form.php?order_id=<?php echo htmlspecialchars($orderDetailsFromSession['orderId'] ?? 'N/A'); ?>&order_type=kost"
                    class="btn btn-info">Berikan Ulasan</a>
            </div>
        </div>
    </main>

    <footer class="footer-custom text-center py-4">
        <div class="container">
            <p>&copy; <span id="tahunSekarangSuccess"><?php echo date("Y"); ?></span> MOVER. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script>
    const phpOrderDetails = <?php echo json_encode($orderDetailsFromSession); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="../javascript/order_kost_sukses.js"></script>
</body>

</html>