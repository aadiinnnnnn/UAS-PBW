<?php
require 'session.php'; //
require '../koneksi.php'; //

$id_kost_dipesan = isset($_GET['id_kost']) ? $_GET['id_kost'] : null; //
$kost_details = null;
$error_message = '';

if ($id_kost_dipesan) {
    // MODIFIKASI: Menggunakan 'pengelolaan_kost' sesuai file carikost_data.php dan owner.php
    $stmt = $conn->prepare("SELECT id_kos_plk, nama, lokasi, harga_sewa FROM pengelolaan_kost WHERE id_kos_plk = ? AND tersedia = 1"); //
    if ($stmt) {
        $stmt->bind_param("s", $id_kost_dipesan);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $kost_details = $result->fetch_assoc();
        } else {
            $error_message = "Detail kost tidak ditemukan atau kost tidak tersedia."; //
        }
        $stmt->close();
    } else {
        $error_message = "Gagal menyiapkan query database: " . $conn->error; //
    }
} else {
    $error_message = "ID Kost tidak diberikan."; //
}

// BARU: Definisikan durasi sewa beserta diskonnya
$durations = [
    ['bulan' => 1, 'diskon' => 0, 'label' => '1 Bulan'],
    ['bulan' => 3, 'diskon' => 10, 'label' => '3 Bulan (Diskon 10%)'],
    ['bulan' => 6, 'diskon' => 30, 'label' => '6 Bulan (Diskon 30%)'],
    ['bulan' => 12, 'diskon' => 50, 'label' => '1 Tahun (Diskon 50%)']
];

// MODIFIKASI: Logika ini dari file orderkost_data.php yang Anda berikan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    $id_user = $_SESSION['user_id'];
    $id_kos_plk = $input['idKost'] ?? '';
    $nama_pemesan = $input['fullName'] ?? '';
    $email_pemesan = $input['email'] ?? '';
    $telepon_pemesan = $input['phoneNumber'] ?? '';
    $tanggal_check_in = $input['checkInDate'] ?? '';
    $duration_value = (int)($input['duration'] ?? 0);
    $metode_pembayaran = $input['paymentMethod'] ?? '';
    $catatan_pemesan = $input['notes'] ?? null;
    $status_pemesanan = 'Menunggu Konfirmasi';
    
    // Server-side validation
    if (empty($id_kos_plk) || empty($id_user) || empty($nama_pemesan) || empty($tanggal_check_in) || empty($duration_value) || empty($metode_pembayaran)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap atau tidak valid.']);
        exit;
    }

    // Kalkulasi ulang harga & diskon di server untuk keamanan
    $stmt_price = $conn->prepare("SELECT harga_sewa FROM pengelolaan_kost WHERE id_kos_plk = ?");
    $stmt_price->bind_param("s", $id_kos_plk);
    $stmt_price->execute();
    $result_price = $stmt_price->get_result();
    $kost_price_data = $result_price->fetch_assoc();
    $harga_per_bulan = (float)$kost_price_data['harga_sewa'];
    $stmt_price->close();

    $subtotal = $duration_value * $harga_per_bulan;
    $diskon_persen_server = 0;
    $durasi_sewa_pilihan_text = $duration_value . " Bulan";

    foreach ($durations as $d) {
        if ($d['bulan'] == $duration_value) {
            $diskon_persen_server = $d['diskon'];
            $durasi_sewa_pilihan_text = $d['label'];
            break;
        }
    }
    
    $nilai_diskon_server = $subtotal * ($diskon_persen_server / 100);
    $harga_final_server = $subtotal - $nilai_diskon_server;

    // Cek apakah harga dari client cocok dengan perhitungan server
    if (abs((float)$input['totalPrice'] - $harga_final_server) > 0.01) {
        echo json_encode(['success' => false, 'message' => 'Validasi harga gagal. Mohon refresh halaman.']);
        exit;
    }

    $stmt_insert = $conn->prepare("INSERT INTO order_sewa_kost (id_kos_plk, id_user, nama_pemesan, email_pemesan, telepon_pemesan, tanggal_check_in, durasi_sewa_pilihan, total_harga, metode_pembayaran, catatan_pemesan, status_pemesanan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt_insert) {
        $stmt_insert->bind_param("ssssssdssss", $id_kos_plk, $id_user, $nama_pemesan, $email_pemesan, $telepon_pemesan, $tanggal_check_in, $durasi_sewa_pilihan_text, $harga_final_server, $metode_pembayaran, $catatan_pemesan, $status_pemesanan);

        if ($stmt_insert->execute()) {
            $_SESSION['latestKostOrderDetails'] = [
                'orderId' => $stmt_insert->insert_id,
                'kostName' => $input['kostName'],
                'kostAddress' => $input['kostAddress'],
                'subtotal' => $subtotal,
                'diskonPersen' => $diskon_persen_server,
                'nilaiDiskon' => $nilai_diskon_server,
                'totalPrice' => $harga_final_server,
                'paymentMethod' => $metode_pembayaran,
                'checkInDate' => $tanggal_check_in,
                'durationText' => $durasi_sewa_pilihan_text,
                'notes' => $catatan_pemesan
            ];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesanan: ' . $stmt_insert->error]);
        }
        $stmt_insert->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan statement database: ' . $conn->error]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kost - MOVER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
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

    .btn-submit-kost {
        font-weight: bold;
        border-radius: 8px !important;
        padding: 10px 22px;
        font-size: 1.05rem;
        margin-top: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        background-color: #F5A623;
        color: white !important;
    }

    .btn-submit-kost:hover {
        background-color: #db8e1e;
    }

    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
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
    </style>
</head>

<body>
    <header class="header-custom sticky-top">
        <nav class="container navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="indexuser.php"><img src="../image/logo mover.png" alt="MOVER Logo"
                    style="height: 70px;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"><span
                    class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="indexuser.php">Beranda</a></li>
                    <li class="nav-item"><a href="pilihan.php" class="nav-link order-btn-nav">Layanan</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
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

    <main class="container mt-4 mb-5">
        <h2 class="text-center fw-bold mb-4" style="color: #367A83;">Formulir Pemesanan Kost</h2>
        <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?> <a href="carikost.php">Kembali mencari</a>.</div>
        <?php else: ?>
        <div class="row">
            <div class="col-lg-5">
                <div class="kost-summary-card">
                    <h5>Detail Kost Dipesan</h5>
                    <p><strong>Nama Kost:</strong> <span
                            id="summaryKostName"><?php echo htmlspecialchars($kost_details['nama']); ?></span></p>
                    <p><strong>Alamat:</strong> <span
                            id="summaryKostAddress"><?php echo htmlspecialchars($kost_details['lokasi']); ?></span></p>
                    <p><strong>Harga Dasar:</strong> Rp <span
                            id="summaryBasePrice"><?php echo number_format($kost_details['harga_sewa'], 0, ',', '.'); ?></span>
                        / Bulan</p>
                    <input type="hidden" id="hiddenBasePrice"
                        value="<?php echo floatval($kost_details['harga_sewa']); ?>">
                    <input type="hidden" id="hiddenIdKost"
                        value="<?php echo htmlspecialchars($kost_details['id_kos_plk']); ?>">
                </div>
                <div class="kost-summary-card price-summary sticky-top" style="top: 120px;">
                    <h5>Ringkasan Pembayaran</h5>
                    <div class="d-flex justify-content-between"><span>Subtotal</span> <span id="summarySubtotal">Rp
                            0</span></div>
                    <div id="summaryDiskonWrapper" class="d-flex justify-content-between text-danger"
                        style="display: none;">
                        <span id="summaryDiskonLabel">Diskon</span> <span id="summaryDiskonNilai">- Rp 0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span> <span
                            id="totalPriceDisplay">Rp 0</span></div>
                </div>
            </div>
            <div class="col-lg-7">
                <form id="formOrderKost" class="needs-validation" novalidate>
                    <div class="form-section-card mb-4">
                        <h4 class="section-title"><i class="fas fa-user-circle me-2"></i>Data Diri Pemesan</h4>
                        <div class="mb-3"><label for="fullName" class="form-label">Nama Lengkap</label><input
                                type="text" class="form-control" id="fullName" name="fullName" required></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="email" class="form-label">Email</label><input
                                    type="email" class="form-control" id="email" name="email" required></div>
                            <div class="col-md-6 mb-3"><label for="phoneNumber" class="form-label">Nomor
                                    Telepon/WA</label><input type="tel" class="form-control" id="phoneNumber"
                                    name="phoneNumber" required></div>
                        </div>
                    </div>
                    <div class="form-section-card mb-4">
                        <h4 class="section-title"><i class="fas fa-calendar-alt me-2"></i>Detail Sewa</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Durasi Sewa</label>
                                <select class="form-select" id="duration" name="duration" required>
                                    <option value="" selected disabled>Pilih durasi...</option>
                                    <?php foreach($durations as $d): ?>
                                    <option value="<?php echo $d['bulan']; ?>"
                                        data-diskon="<?php echo $d['diskon']; ?>"><?php echo $d['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3"><label for="checkInDate" class="form-label">Tanggal Mulai
                                    Sewa</label><input type="date" class="form-control" id="checkInDate"
                                    name="checkInDate" required min="<?php echo date('Y-m-d'); ?>"></div>
                        </div>
                        <div class="mb-3"><label for="notes" class="form-label">Catatan Tambahan
                                (Opsional)</label><textarea class="form-control" id="notes" name="notes"
                                rows="3"></textarea></div>
                    </div>
                    <div class="form-section-card mb-4">
                        <h4 class="section-title"><i class="fas fa-credit-card me-2"></i>Metode Pembayaran</h4>
                        <div class="form-check"><input type="radio" id="paymentBankTransfer" name="paymentMethod"
                                value="Bank Transfer" class="form-check-input" checked required><label
                                class="form-check-label" for="paymentBankTransfer">Bank Transfer (BCA, Mandiri,
                                BRI)</label></div>
                        <div class="form-check"><input type="radio" id="paymentOVO" name="paymentMethod" value="OVO"
                                class="form-check-input" required><label class="form-check-label"
                                for="paymentOVO">OVO</label></div>
                        <div class="form-check"><input type="radio" id="paymentGoPay" name="paymentMethod"
                                value="Go-Pay" class="form-check-input" required><label class="form-check-label"
                                for="paymentGoPay">Go-Pay</label></div>
                    </div>
                    <div class="d-grid"><button type="submit" id="submitOrderLink"
                            class="btn btn-submit-kost btn-lg">Pesan Kost</button></div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </main>
    <div id="toast-container"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../javascript/order_kost.js"></script>
</body>

</html>