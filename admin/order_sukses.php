<?php
require 'session.php';

$orderDetailsFromSession = $_SESSION['order_details'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVER - Pesanan Pindahan Berhasil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <style>
    /* CSS dari file order_sukses.php yang sudah ada bisa dipertahankan */
    body.order-success-page {
        background-color: #f0f2f5;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .success-card-container-standalone {
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        margin-top: 70px;
        margin-bottom: 20px;
    }

    .success-card-standalone {
        background-color: #fff;
        color: #333;
        border-radius: 15px;
        padding: 35px 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        max-width: 650px;
        width: 100%;
        text-align: center;
    }

    .success-icon-standalone {
        font-size: 4.5rem;
        color: #28a745;
        margin-bottom: 20px;
        line-height: 1;
    }

    .success-title-standalone {
        font-size: 2rem;
        font-weight: 700;
        color: #367A83;
        margin-bottom: 15px;
    }

    .success-message-standalone {
        font-size: 1.1rem;
        color: #495057;
        margin-bottom: 25px;
    }

    .order-details-summary-standalone {
        text-align: left;
        margin-top: 20px;
        border-top: 1px solid #dee2e6;
        padding-top: 20px;
    }

    .order-details-summary-standalone h6 {
        font-size: 1.2rem;
        color: #367A83;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .order-details-summary-standalone p {
        margin-bottom: 10px;
        font-size: 1rem;
        color: #333;
        display: flex;
        justify-content: space-between;
    }

    .order-details-summary-standalone p strong {
        font-weight: 600;
        color: #495057;
    }

    .btn-back-home-standalone {
        margin-top: 25px;
        background-color: #F5A623;
        color: white;
        padding: 12px 30px;
        font-size: 1.05rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        border: none;
        transition: background-color 0.2s ease;
    }

    .btn-back-home-standalone:hover {
        background-color: #db8e1e;
    }
    </style>
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

    <main class="success-card-container-standalone">
        <div class="success-card-standalone">
            <div class="success-icon-standalone"><i class="fas fa-check-circle"></i></div>
            <h2 class="success-title-standalone">Pesanan Pindahan Anda Telah Diterima!</h2>
            <p class="success-message-standalone">
                Terima kasih telah memilih MOVER. Pesanan pindahan Anda sedang kami proses.
                Tim kami akan segera menghubungi Anda untuk konfirmasi lebih lanjut.
            </p>
            <div class="order-details-summary-standalone">
                <h6>Detail Pesanan Anda:</h6>
                <p><strong>Nomor Pesanan:</strong> <span id="orderId">Memuat...</span></p>
                <p><strong>Alamat Jemput:</strong> <span id="alamatJemput">Memuat...</span></p>
                <p><strong>Alamat Tujuan:</strong> <span id="alamatTujuan">Memuat...</span></p>
                <p><strong>Tanggal Pindahan:</strong> <span id="tanggalPindahan">Memuat...</span></p>
                <p><strong>Barang Pindahan:</strong> <span id="barangPindahan" class="text-end">Memuat...</span></p>
                <hr>
                <p><strong>Subtotal:</strong> <span id="subtotalDisplay">Memuat...</span></p>
                <p class="text-danger" id="diskonDisplayWrapper" style="display:none;">
                    <strong>Diskon:</strong> <span id="diskonDisplay">Memuat...</span>
                </p>
                <hr>
                <p class="fw-bold"><strong>Total Pembayaran:</strong> <span id="totalBiaya"
                        class="fw-bold">Memuat...</span></p>
                <p><strong>Metode Pembayaran:</strong> <span id="metodePembayaran">Memuat...</span></p>
            </div>
            <a href="order.php" class="btn btn-primary mt-3 ml-2">Pesan Pindahan Lain</a>
            <a href="indexuser.php" class="btn btn-secondary mt-3 ml-2">Kembali ke Dashboard</a>
            <a href="review_form.php?order_id=<?php echo htmlspecialchars($orderDetailsFromSession['orderId'] ?? 'N/A'); ?>&order_type=pindahan"
                class="btn btn-info mt-3 ml-2">Berikan Ulasan</a>
        </div>
    </main>

    <footer class="footer-custom text-center py-4">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> MOVER. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script>
    // Menyisipkan detail pesanan dari PHP Session ke variabel JavaScript
    const phpOrderDetails = <?php echo json_encode($orderDetailsFromSession); ?>;

    document.addEventListener("DOMContentLoaded", function() {
        const formatRupiah = (angka) => "Rp " + parseFloat(angka).toLocaleString("id-ID");

        const orderDetails = phpOrderDetails;
        if (orderDetails) {
            document.getElementById("orderId").textContent = orderDetails.orderId || "N/A";
            document.getElementById("alamatJemput").textContent = orderDetails.asal || "N/A";
            document.getElementById("alamatTujuan").textContent = orderDetails.tujuan || "N/A";
            document.getElementById("tanggalPindahan").textContent = orderDetails.tanggalPindah ? new Date(
                orderDetails.tanggalPindah).toLocaleDateString("id-ID", {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }) : "N/A";
            document.getElementById("barangPindahan").textContent = orderDetails.barangPindahanDisplay || "N/A";

            // BARU: Tampilkan detail biaya
            document.getElementById("subtotalDisplay").textContent = formatRupiah(orderDetails.subtotal || 0);
            if (orderDetails.diskonPersen > 0) {
                document.getElementById("diskonDisplay").textContent =
                    `- ${formatRupiah(orderDetails.nilaiDiskon || 0)} (${orderDetails.diskonPersen}%)`;
                document.getElementById("diskonDisplayWrapper").style.display = 'flex';
            }

            document.getElementById("totalBiaya").textContent = formatRupiah(orderDetails.totalBiaya || 0);
            document.getElementById("metodePembayaran").textContent = orderDetails.metodePembayaran || "N/A";
        } else {
            document.querySelector(".order-details-summary-standalone").innerHTML =
                '<p class="text-danger text-center">Gagal memuat detail pesanan.</p>';
        }
    });
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>