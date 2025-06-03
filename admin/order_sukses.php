<?php
session_start(); // Mulai session di paling atas

// Periksa apakah detail order ada di session.
if (!isset($_SESSION['order_details']) || empty($_SESSION['order_details'])) {
    // Jika tidak ada detail order, redirect ke halaman lain (misalnya, halaman utama pengguna)
    // untuk mencegah akses langsung atau tampilan data yang salah.
    header('Location: indexuser.php'); // Anda bisa mengganti ini dengan halaman login atau order
    exit; // Selalu exit setelah header redirect
}

// Ambil detail order dari session
$orderDetails = $_SESSION['order_details'];

// HAPUS detail order dari session setelah diambil dan akan ditampilkan.
// Ini penting agar data tidak muncul lagi jika halaman di-refresh atau diakses kembali.
unset($_SESSION['order_details']);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVER - Pesanan Berhasil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/indexuser.css">
    <style>
    body {
        background-color: #418d99;
        /* */
        color: white;
        /* */
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100vh;
        padding-top: 20px;
        padding-bottom: 20px;
        font-family: "Helvetica Neue", "Poppins", sans-serif;
        /* */
    }

    .navbar {
        display: flex;
        /* */
        justify-content: space-between;
        /* */
        align-items: center;
        /* */
        padding: 25px 50px;
        /* */
        width: 100%;
        position: fixed;
        /* */
        top: 0;
        /* */
        left: 0;
        /* */
        z-index: 1030;
        /* */
        background-color: #418d99;
        /* Samakan dengan background body jika ingin menyatu */
    }

    .logo {
        font-size: 1.4rem;
        /* */
        font-weight: 600;
        /* */
        color: white;
    }

    .nav-menu {
        display: flex;
        /* */
        align-items: center;
        /* */
        gap: 20px;
    }

    .nav-menu a,
    .nav-menu .nav-button {
        text-decoration: none;
        /* */
        color: white;
        /* */
        font-size: 1rem;
        /* */
        padding: 8px 15px;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }

    .nav-menu a:hover,
    .nav-menu .nav-button:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .nav-button.logout {
        background-color: #FFD700;
        /* */
        color: #2f4f4f;
        /* */
        border: none;
        cursor: pointer;
        font-weight: 500;
        /* */
    }

    .nav-button.logout:hover {
        background-color: #e0c200;
        /* */
    }

    .profile-icon {
        width: 40px;
        /* */
        height: 40px;
        /* */
        border-radius: 50%;
        /* */
        overflow: hidden;
        /* */
        border: 2px solid white;
        /* */
    }

    .profile-icon img {
        width: 100%;
        /* */
        height: 100%;
        /* */
        object-fit: cover;
        /* */
    }

    .success-card-container {
        margin-top: 120px;
        width: 100%;
        display: flex;
        justify-content: center;
        padding: 20px;
    }

    .success-card-standalone {
        background-color: #ffffff;
        color: #333;
        border-radius: 15px;
        padding: 30px 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        max-width: 650px;
        width: 100%;
        text-align: center;
    }

    .success-icon-standalone {
        font-size: 5rem;
        color: #28a745;
        margin-bottom: 15px;
        line-height: 1;
    }

    .success-title-standalone {
        font-size: 2.2rem;
        font-weight: 700;
        color: #2f4f4f;
        margin-bottom: 10px;
    }

    .success-message-standalone {
        font-size: 1.15rem;
        color: #495057;
        margin-bottom: 20px;
    }

    .order-details-summary-standalone {
        text-align: left;
        margin-top: 20px;
        border-top: 1px solid #dee2e6;
        padding-top: 20px;
    }

    .order-details-summary-standalone h6 {
        font-size: 1.3rem;
        color: #2f4f4f;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .order-details-summary-standalone p {
        margin-bottom: 10px;
        font-size: 1rem;
        color: #333;
        word-wrap: break-word;
    }

    .order-details-summary-standalone p strong {
        color: #495057;
        min-width: 160px;
        display: inline-block;
        font-weight: 600;
    }

    .btn-back-home-standalone {
        margin-top: 25px;
        background-color: #FFD700;
        /* */
        color: #2f4f4f;
        /* */
        padding: 12px 30px;
        font-size: 1.1rem;
        border-radius: 8px;
        /* */
        text-decoration: none;
        /* */
        font-weight: 600;
        /* */
        border: none;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .btn-back-home-standalone:hover {
        background-color: #e0c200;
        /* */
        transform: translateY(-2px);
    }
    </style>
</head>

<body>
    <header class="navbar">
        <div class="logo">MOVER</div>
        <nav class="nav-menu">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['username'])): ?>
            <div class="profile-icon">
                <img src="../assets/img/default-profile.png"
                    alt="<?php echo htmlspecialchars($_SESSION['username']); ?> Profile" />
            </div>
            <a href="indexuser.php">Beranda</a>
            <a href="order.php">Order Lagi</a>
            <form action="logout.php" method="post" style="display: inline; margin: 0; padding: 0;">
                <button type="submit" class="nav-button logout">Logout</button>
            </form>
            <?php else: ?>
            <a href="login.php" class="nav-button login" style="background-color: white; color: #418d99;">Login</a>
            <a href="../index.php">Halaman Utama</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="success-card-container">
        <div class="success-card-standalone">
            <div class="success-icon-standalone">&#10004;</div>
            <h2 class="success-title-standalone">Pesanan Anda Telah Diterima!</h2>
            <p class="success-message-standalone">
                Terima kasih telah memilih MOVER. Pesanan Anda sedang kami proses.
                Tim kami akan segera menghubungi Anda untuk konfirmasi lebih lanjut.
            </p>
            <div class="order-details-summary-standalone">
                <h6>Detail Pesanan Anda:</h6>
                <p><strong>Nomor Pesanan:</strong>
                    #<?php echo isset($orderDetails['orderId']) ? htmlspecialchars($orderDetails['orderId']) : 'Tidak Tersedia'; ?>
                </p>
                <p><strong>Total Pembayaran:</strong> Rp
                    <?php echo isset($orderDetails['totalBiaya']) ? number_format($orderDetails['totalBiaya'], 0, ',', '.') : 'Tidak Tersedia'; ?>
                </p>
                <p><strong>Metode Pembayaran:</strong>
                    <?php echo isset($orderDetails['metodePembayaran']) ? htmlspecialchars($orderDetails['metodePembayaran']) : 'Tidak Tersedia'; ?>
                </p>
                <hr style="margin: 15px 0;">
                <p><strong>Alamat Asal:</strong>
                    <?php echo isset($orderDetails['asal']) ? htmlspecialchars($orderDetails['asal']) : 'Tidak Tersedia'; ?>
                </p>
                <p><strong>Alamat Tujuan:</strong>
                    <?php echo isset($orderDetails['tujuan']) ? htmlspecialchars($orderDetails['tujuan']) : 'Tidak Tersedia'; ?>
                </p>
                <p><strong>Perkiraan Jarak:</strong>
                    <?php echo isset($orderDetails['jarak']) ? htmlspecialchars($orderDetails['jarak']) : 'N/A'; ?> km
                </p>
                <p><strong>Tanggal Pindahan:</strong>
                    <?php echo isset($orderDetails['tanggalPindah']) ? htmlspecialchars(date("d F Y", strtotime($orderDetails['tanggalPindah']))) : 'Tidak Tersedia'; ?>
                </p>
                <p><strong>Barang Pindahan:</strong>
                    <?php echo isset($orderDetails['barangPindahanDisplay']) ? htmlspecialchars($orderDetails['barangPindahanDisplay']) : 'Tidak Tersedia'; ?>
                </p>
                <?php if (isset($orderDetails['catatanTambahan']) && !empty($orderDetails['catatanTambahan'])): ?>
                <p><strong>Catatan Tambahan:</strong> <?php echo htmlspecialchars($orderDetails['catatanTambahan']); ?>
                </p>
                <?php endif; ?>
            </div>
            <p class="mt-4 text-muted" style="font-size: 0.9rem;">
                Harap simpan detail pesanan Anda. Jika ada pertanyaan, jangan ragu untuk menghubungi layanan pelanggan
                kami.
            </p>
            <a href="indexuser.php" class="btn btn-back-home-standalone">Kembali ke Beranda</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>