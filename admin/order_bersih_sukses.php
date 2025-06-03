<?php
require 'session.php'; // Memastikan pengguna sudah login

$orderDetailsFromSession = null;
// Coba ambil detail pesanan dari session PHP
if (isset($_SESSION['latestBersihOrderDetails'])) {
    $orderDetailsFromSession = $_SESSION['latestBersihOrderDetails'];
    // Opsional: Unset session di sini jika sudah tidak diperlukan lagi
    // unset($_SESSION['latestBersihOrderDetails']);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVER - Pesanan Jasa Bersih Berhasil</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/bersih.css">
    <style>
    /* Gaya khusus untuk halaman sukses ini */
    body.order-success-page {
        background-color: #f0f2f5;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .success-card-container {
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        margin-top: 70px;
        /* Offset untuk sticky header */
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
        /* Warna hijau sukses */
        margin-bottom: 20px;
        line-height: 1;
    }

    .success-title-standalone {
        font-size: 2rem;
        font-weight: 700;
        color: #367A83;
        /* Warna MOVER primary */
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
    }

    .order-details-summary-standalone p strong {
        color: #495057;
        min-width: 170px;
        /* Lebar minimum untuk label strong */
        display: inline-block;
        font-weight: 600;
    }

    .btn-back-home-standalone {
        margin-top: 25px;
        background-color: #F5A623;
        /* Warna aksen oranye */
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
            <a class="navbar-brand" href="indexuser.php">LOGO MOVER</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavSuccess"
                aria-controls="navbarNavSuccess" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavSuccess">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="indexuser.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pilihan.php">Order Layanan Lain</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="../assets/img/default-profile.png" class="profile-icon-sm" alt="User Profile" />
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownUser">
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
            <h2 class="success-title-standalone">Pesanan Jasa Bersih Anda Telah Diterima!</h2>
            <p class="success-message-standalone">
                Terima kasih telah memilih MOVER. Pesanan jasa bersih Anda sedang kami proses.
                Tim kami akan segera menghubungi Anda untuk konfirmasi lebih lanjut.
            </p>
            <div class="order-details-summary-standalone">
                <h6>Detail Pesanan Anda:</h6>
                <p><strong>Nomor Pesanan:</strong> <span id="orderId">Memuat...</span></p>
                <p><strong>Paket Jasa:</strong> <span id="paketJasa">Memuat...</span></p>
                <p><strong>Deskripsi Paket:</strong> <span id="deskripsiPaket">Memuat...</span></p>
                <p><strong>Tanggal Pelaksanaan:</strong> <span id="tanggalPelaksanaan">Memuat...</span></p>
                <p><strong>Total Pembayaran:</strong> Rp <span id="totalBiaya">Memuat...</span></p>
                <p><strong>Metode Pembayaran:</strong> <span id="metodePembayaran">Memuat...</span></p>
            </div>
            <p class="mt-4 text-muted" style="font-size: 0.9rem;">
                Harap simpan detail pesanan Anda. Jika ada pertanyaan, jangan ragu untuk menghubungi layanan pelanggan
                kami.
            </p>
            <a href="bersih.php" class="btn btn-back-home-standalone">Pesan Jasa Bersih Lain</a>
            <a href="indexuser.php" class="btn btn-secondary mt-3 ml-2">Kembali ke Dashboard</a>
            <a href="review_form.php?order_id=<?php echo htmlspecialchars($orderDetailsFromSession['orderId']); ?>&order_type=bersih"
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
    const phpOrderDetails = <?php echo json_encode($orderDetailsFromSession); ?>;
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Set tahun sekarang di footer (jika belum ada di common.js/footer global)
        const tahunElement = document.getElementById("tahunSekarangSuccess");
        if (tahunElement) {
            tahunElement.textContent = new Date().getFullYear();
        }

        const orderIdEl = document.getElementById("orderId");
        const paketJasaEl = document.getElementById("paketJasa");
        const deskripsiPaketEl = document.getElementById("deskripsiPaket");
        const tanggalPelaksanaanEl = document.getElementById("tanggalPelaksanaan");
        const totalBiayaEl = document.getElementById("totalBiaya");
        const metodePembayaranEl = document.getElementById("metodePembayaran");

        // Ambil data dari variabel global phpOrderDetails (dari PHP session)
        const orderDetails = phpOrderDetails;

        if (orderDetails) {
            if (orderIdEl) orderIdEl.textContent = orderDetails.orderId || "Tidak Tersedia";
            if (paketJasaEl) paketJasaEl.textContent = orderDetails.jenis_paket_bk || "Tidak Diketahui";
            if (deskripsiPaketEl) deskripsiPaketEl.textContent = orderDetails.deskripsi_paket || "-";

            if (tanggalPelaksanaanEl && orderDetails.tanggal_datang_bk) {
                try {
                    const date = new Date(orderDetails.tanggal_datang_bk + "T00:00:00");
                    if (!isNaN(date.getTime())) {
                        const options = {
                            day: "numeric",
                            month: "long",
                            year: "numeric",
                            timeZone: "UTC"
                        };
                        tanggalPelaksanaanEl.textContent = date.toLocaleDateString("id-ID", options);
                    } else {
                        tanggalPelaksanaanEl.textContent = orderDetails.tanggal_datang_bk;
                    }
                } catch (e) {
                    console.error("Error parsing tanggal pelaksanaan:", e);
                    tanggalPelaksanaanEl.textContent = "Tidak Tersedia";
                }
            } else if (tanggalPelaksanaanEl) {
                tanggalPelaksanaanEl.textContent = "Tidak Tersedia";
            }

            if (totalBiayaEl) {
                totalBiayaEl.textContent = orderDetails.total_harga_bk !== undefined ? parseFloat(orderDetails
                    .total_harga_bk).toLocaleString("id-ID") : "Tidak Diketahui";
            }
            if (metodePembayaranEl) metodePembayaranEl.textContent = orderDetails.metode_pembayaran_bk ||
                "Tidak Diketahui";

        } else {
            // Jika tidak ada detail pesanan (misal diakses langsung)
            const defaultText = "Tidak Dapat Dimuat";
            if (orderIdEl) orderIdEl.textContent = defaultText;
            if (paketJasaEl) paketJasaEl.textContent = defaultText;
            if (deskripsiPaketEl) deskripsiPaketEl.textContent = defaultText;
            if (tanggalPelaksanaanEl) tanggalPelaksanaanEl.textContent = defaultText;
            if (totalBiayaEl) totalBiayaEl.textContent = defaultText;
            if (metodePembayaranEl) metodePembayaranEl.textContent = defaultText;

            console.warn("Tidak ada detail pesanan ditemukan dari session untuk ditampilkan.");
            const successCard = document.querySelector(".success-card-standalone");
            if (successCard) {
                const detailsDiv = successCard.querySelector(".order-details-summary-standalone");
                const pError = document.createElement("p");
                pError.innerHTML =
                    '<strong style="color:red; margin-top:15px; display:block;">Gagal memuat detail pesanan. Silakan cek riwayat pesanan Anda atau hubungi customer service.</strong>';
                if (detailsDiv) {
                    detailsDiv.style.display = "none"; // Sembunyikan detail jika error
                    detailsDiv.insertAdjacentElement("afterend", pError);
                } else {
                    successCard.appendChild(pError);
                }
            }
        }
    });
    </script>
</body>

</html>