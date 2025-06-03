<?php
require 'session.php'; // Memastikan pengguna sudah login
require '../koneksi.php'; // Koneksi ke database

$id_kost_dipesan = isset($_GET['id_kost']) ? $_GET['id_kost'] : null;
$kost_details = null;
$error_message = '';

if ($id_kost_dipesan) {
    $stmt = $conn->prepare("SELECT nama, lokasi, harga_sewa, periode_sewa, gambar_url FROM pengelolaan_kost WHERE id_kos_plk = ? AND tersedia = 1");
    if ($stmt) {
        $stmt->bind_param("s", $id_kost_dipesan);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $kost_details = $result->fetch_assoc();
        } else {
            $error_message = "Detail kost tidak ditemukan atau kost tidak tersedia.";
        }
        $stmt->close();
    } else {
        $error_message = "Gagal menyiapkan query database: " . $conn->error;
    }
} else {
    $error_message = "ID Kost tidak diberikan.";
}

// Jika ada error, bisa redirect atau tampilkan pesan
if ($error_message && !$kost_details) {
    // Opsi: redirect kembali ke carikost.php dengan pesan error
    // header('Location: carikost.php?error=' . urlencode($error_message));
    // exit;
    // Atau tampilkan pesan error di halaman ini:
    // echo "<div class='alert alert-danger'>".$error_message." <a href='carikost.php'>Kembali mencari kost</a>.</div>";
    // Untuk sekarang, kita biarkan halaman render dengan nilai default atau kosong agar JS bisa menghandle.
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kost - MOVER</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css"> {/* Pastikan common.css ada dan sesuai */}
    <link rel="stylesheet" href="../css/owner.css"> {/* Mungkin tidak semua gaya owner.css relevan */}

    <style>
    .kost-summary-card {
        background-color: #f8f9fa;
        border: 1px solid #e0e0e0;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
    }

    .kost-summary-card h5 {
        color: #367A83;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .price-summary strong {
        font-size: 1.3em;
        color: #F5A623;
    }

    #totalPriceDisplay {
        font-weight: bold;
    }

    .btn-submit-kost {
        /* Penyesuaian dari CSS yang Anda berikan */
        font-weight: bold;
        border-radius: 8px !important;
        padding: 10px 22px;
        font-size: 1.05rem;
        margin-top: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        outline: none;
        transition: background-color 0.2s ease-out, border-color 0.2s ease-out, transform 0.15s ease-out, box-shadow 0.2s ease-out;
        background-color: #F5A623;
        /* Warna utama tombol submit */
        color: white !important;
    }

    .btn-submit-kost:hover {
        background-color: #db8e1e;
        /* Warna hover */
    }

    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
        /* Di atas modal */
        width: auto;
        max-width: 350px;
    }

    .toast-message {
        background-color: #333;
        color: white;
        padding: 15px 20px;
        margin-bottom: 10px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        opacity: 0;
        transform: translateY(-20px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .toast-message.show {
        opacity: 1;
        transform: translateY(0);
    }

    .toast-message.hide {
        opacity: 0;
        transform: translateY(-20px);
    }

    .toast-icon {
        margin-right: 10px;
        font-size: 1.2em;
    }

    .toast-message.success {
        background-color: #28a745;
    }

    .toast-message.error {
        background-color: #dc3545;
    }

    .toast-message.warning {
        background-color: #ffc107;
        color: #333;
    }

    .toast-message.info {
        background-color: #17a2b8;
    }

    .toast-close-button {
        background: none;
        border: none;
        color: inherit;
        font-size: 1.2em;
        margin-left: auto;
        padding: 0 5px;
        cursor: pointer;
        line-height: 1;
    }
    </style>
</head>

<body>

    <header class="header-custom sticky-top">
        <nav class="container navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="indexuser.php">LOGO MOVER</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavOrder"
                aria-controls="navbarNavOrder" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavOrder">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="carikost.php">Cari Kost Lain</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="../assets/img/default-profile.png" class="profile-icon-sm" alt="Profil"
                                style="width: 25px; height: 25px; border-radius: 50%; margin-right: 5px;">
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

    <main class="container mt-4 mb-5">
        <h2 class="owner-page-main-title"><b>Formulir Pemesanan Kost</b></h2>

        <?php if ($error_message && !$kost_details): ?>
        <div class="alert alert-danger text-center">
            <?php echo htmlspecialchars($error_message); ?> <br>
            <a href="carikost.php" class="btn btn-primary mt-2">Kembali Mencari Kost</a>
        </div>
        <?php else: ?>
        <div class="row">
            <div class="col-lg-5">
                <div class="kost-summary-card">
                    <h5>Detail Kost Dipesan</h5>
                    <div id="kostDetailsSummary">
                        <p><strong>Nama Kost:</strong> <span
                                id="summaryKostName"><?php echo htmlspecialchars($kost_details['nama'] ?? 'Memuat...'); ?></span>
                        </p>
                        <p><strong>Alamat:</strong> <span
                                id="summaryKostAddress"><?php echo htmlspecialchars($kost_details['lokasi'] ?? 'Memuat...'); ?></span>
                        </p>
                        <p><strong>Harga Dasar:</strong> Rp <span
                                id="summaryBasePrice"><?php echo number_format($kost_details['harga_sewa'] ?? 0, 0, ',', '.'); ?></span>
                            / <?php echo htmlspecialchars($kost_details['periode_sewa'] ?? 'Bulan'); ?></p>
                        <input type="hidden" id="hiddenBasePrice"
                            value="<?php echo floatval($kost_details['harga_sewa'] ?? 0); ?>">
                        <input type="hidden" id="hiddenIdKost"
                            value="<?php echo htmlspecialchars($id_kost_dipesan); ?>">
                    </div>
                </div>

                <div class="kost-summary-card price-summary">
                    <h5>Ringkasan Pembayaran</h5>
                    <p>Harga per Durasi: Rp <span id="pricePerDurationDisplay">-</span></p>
                    <hr>
                    <p><strong>Total Pembayaran: Rp <span id="totalPriceDisplay">-</span></strong></p>
                </div>
            </div>

            <div class="col-lg-7">
                <form id="formOrderKost">
                    <div class="form-section-card">
                        <h4 class="section-title"><i class="fas fa-user-circle mr-2"></i>Data Diri Pemesan</h4>
                        <div class="form-group">
                            <label for="fullName">Nama Lengkap</label>
                            <input type="text" class="form-control" id="fullName" name="fullName" required
                                placeholder="Masukkan nama lengkap Anda">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    placeholder="cth: nama@email.com">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="phoneNumber">Nomor Telepon/WA</label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required
                                    placeholder="cth: 08123456789">
                            </div>
                        </div>
                    </div>

                    <div class="form-section-card">
                        <h4 class="section-title"><i class="fas fa-calendar-alt mr-2"></i>Detail Sewa</h4>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="checkInDate">Tanggal Mulai Sewa (Check-in)</label>
                                <input type="date" class="form-control" id="checkInDate" name="checkInDate" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="duration">Durasi Sewa</label>
                                <select class="custom-select" id="duration" name="duration" required>
                                    <option value="" selected disabled>Pilih durasi...</option>
                                    <option value="1" data-price-multiplier="1">1 Bulan</option>
                                    <option value="3" data-price-multiplier="2.9">3 Bulan (Diskon)</option>
                                    <option value="6" data-price-multiplier="5.7">6 Bulan (Diskon)</option>
                                    <option value="12" data-price-multiplier="11">1 Tahun (Diskon Besar)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notes">Catatan Tambahan (Opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="Misal: Perkiraan jam kedatangan, permintaan khusus (jika ada)"></textarea>
                        </div>
                    </div>

                    <div class="form-section-card">
                        <h4 class="section-title"><i class="fas fa-credit-card mr-2"></i>Metode Pembayaran</h4>
                        <p class="text-muted">Pilih metode pembayaran yang Anda inginkan. Instruksi pembayaran akan
                            diberikan setelah pesanan diproses.</p>
                        <div class="form-group">
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="paymentOVO" name="paymentMethod" value="OVO"
                                    class="custom-control-input" required>
                                <label class="custom-control-label" for="paymentOVO">OVO</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="paymentDANA" name="paymentMethod" value="DANA"
                                    class="custom-control-input" required>
                                <label class="custom-control-label" for="paymentDANA">DANA</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="paymentGoPay" name="paymentMethod" value="Go-Pay"
                                    class="custom-control-input" required>
                                <label class="custom-control-label" for="paymentGoPay">Go-Pay</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="paymentBankTransfer" name="paymentMethod" value="Bank Transfer"
                                    class="custom-control-input" checked required>
                                <label class="custom-control-label" for="paymentBankTransfer">Bank Transfer (BCA,
                                    Mandiri, BRI)</label>
                            </div>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="paymentCash" name="paymentMethod" value="Cash"
                                    class="custom-control-input" required>
                                <label class="custom-control-label" for="paymentCash">Cash (Bayar di Tempat)</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="termsAgreement"
                                name="termsAgreement" required>
                            <label class="custom-control-label" for="termsAgreement">Saya telah membaca dan menyetujui
                                <a href="#termsModal" data-toggle="modal">Syarat & Ketentuan Pemesanan</a> yang
                                berlaku.</label>
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <a href="order_kost_success.php" id="submitOrderLink" class="btn btn-submit-kost">
                            Pesan Kost
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Syarat & Ketentuan Pemesanan Kost</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Harap baca dengan seksama syarat dan ketentuan berikut sebelum melanjutkan pemesanan:</p>
                    <ol>
                        <li>Pemesanan dianggap sah setelah pembayaran dikonfirmasi.</li>
                        <li>Pembatalan pemesanan akan dikenakan biaya sesuai kebijakan yang berlaku.</li>
                        <li>Dilarang membawa hewan peliharaan (kecuali diizinkan secara eksplisit oleh pemilik kost).
                        </li>
                        <li>Menjaga kebersihan dan ketertiban lingkungan kost.</li>
                        <li>Segala kerusakan yang disebabkan oleh penyewa menjadi tanggung jawab penyewa.</li>
                    </ol>
                    <p>Dengan mencentang kotak persetujuan, Anda dianggap telah memahami dan menerima seluruh syarat dan
                        ketentuan ini.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer-custom text-center py-4">
        <div class="container">
            <p>© <span id="tahunSekarangOrder"><?php echo date("Y"); ?></span> MOVER. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    const phpOrderDetails = <?php echo json_encode($orderDetailsFromSession); ?>;
    </script>
    <script src="../javascript/order_kost.js"></script>
    <div id="toast-container"></div>
</body>

</html>