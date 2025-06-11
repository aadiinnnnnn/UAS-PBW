<?php
require 'session.php';
require '../koneksi.php';

$harga_barang_list_server = [
    'koper' => ['nama' => 'Koper/Tas Pakaian', 'harga' => 20000],
    'kardusSedang' => ['nama' => 'Kardus Sedang', 'harga' => 35000],
    'kardusBesar' => ['nama' => 'Kardus Besar', 'harga' => 50000],
    'lemariKecil' => ['nama' => 'Lemari Kecil', 'harga' => 100000],
    'kasurSingle' => ['nama' => 'Kasur Single', 'harga' => 75000],
    'mejaBelajar' => ['nama' => 'Meja Belajar', 'harga' => 60000],
];
$harga_per_km_server = 5000;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_order_js'])) {
    header('Content-Type: application/json');
    $response_data_json = ['success' => false, 'message' => 'Gagal memproses permintaan.'];

    $alamat_asal = trim($_POST['asal'] ?? '');
    $alamat_tujuan = trim($_POST['tujuan'] ?? '');
    $jarak_km = filter_var(trim($_POST['jarak'] ?? '0'), FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0.1]]);
    $tanggal_pindah = $_POST['tanggalPindah'] ?? '';
    $barang_pilihan_client = isset($_POST['barang']) && is_array($_POST['barang']) ? $_POST['barang'] : [];
    $catatan_tambahan = trim($_POST['catatanTambahan'] ?? '');
    $metode_pembayaran = $_POST['metodePembayaran'] ?? 'OVO';

    if (empty($alamat_asal) || empty($alamat_tujuan) || $jarak_km === false || empty($tanggal_pindah) || empty($barang_pilihan_client)) {
        $response_data_json['message'] = "Data tidak lengkap. Pastikan semua field wajib diisi.";
        echo json_encode($response_data_json);
        exit;
    }

    $subtotalServer = 0;
    $biayaJarakServer = $jarak_km * $harga_per_km_server;
    $subtotalServer += $biayaJarakServer;
    $barangPindahanDisplayArray = [];

    foreach ($barang_pilihan_client as $item_key) {
        if (isset($harga_barang_list_server[$item_key])) {
            $subtotalServer += $harga_barang_list_server[$item_key]['harga'];
            $barangPindahanDisplayArray[] = $harga_barang_list_server[$item_key]['nama'];
        } else {
            $response_data_json['message'] = "Jenis barang tidak valid: " . htmlspecialchars($item_key);
            echo json_encode($response_data_json);
            exit;
        }
    }
    $jenis_barang_for_db = implode(", ", $barangPindahanDisplayArray);

    $diskonPersenServer = 0;
    if ($subtotalServer > 200000) {
        $diskonPersenServer = 15;
    } elseif ($subtotalServer > 100000) {
        $diskonPersenServer = 10;
    }
    
    $nilaiDiskonServer = $subtotalServer * ($diskonPersenServer / 100);
    $hargaFinalServer = $subtotalServer - $nilaiDiskonServer;

    $id_user_session = $_SESSION['user_id'] ?? null;
    if (!$id_user_session) {
        $response_data_json['message'] = "Sesi pengguna tidak ditemukan. Silakan login kembali.";
        echo json_encode($response_data_json);
        exit;
    }

    $status_pesanan = "Menunggu Pembayaran";
    
    $stmt = $conn->prepare(
        "INSERT INTO order_layanan_pindahan_barang_kos 
        (id_user, alamat_jemput, alamat_tujuan, jarak_km, tanggal_datang_pk, jenis_barang, total_harga_pk, metode_pembayaran_pk, catatan_tambahan, status_pesanan) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($stmt) {
        $stmt->bind_param(
            "sssdsdssss", 
            $id_user_session, $alamat_asal, $alamat_tujuan, $jarak_km, $tanggal_pindah, 
            $jenis_barang_for_db, $hargaFinalServer, $metode_pembayaran, $catatan_tambahan, $status_pesanan
        );

        if ($stmt->execute()) {
            $newOrderId = mysqli_insert_id($conn);

            $_SESSION['order_details'] = [
                'orderId' => $newOrderId,
                'layanan' => 'Jasa Pindahan Barang',
                'subtotal' => $subtotalServer,
                'diskonPersen' => $diskonPersenServer,
                'nilaiDiskon' => $nilaiDiskonServer,
                'totalBiaya' => $hargaFinalServer,
                'metodePembayaran' => $metode_pembayaran,
                'asal' => $alamat_asal,
                'tujuan' => $alamat_tujuan,
                'jarak' => $jarak_km,
                'tanggalPindah' => $tanggal_pindah,
                'barangPindahanDisplay' => $jenis_barang_for_db,
                'catatanTambahan' => $catatan_tambahan
            ];

            $response_data_json['success'] = true;
            $response_data_json['message'] = 'Pesanan berhasil diproses! Anda akan dialihkan...';
            $response_data_json['orderId'] = $newOrderId;
            
        } else {
            $response_data_json['message'] = "Gagal menyimpan pesanan: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        $response_data_json['message'] = "Database error: " . htmlspecialchars($conn->error);
    }
    echo json_encode($response_data_json);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVER - Buat Pesanan Pindahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/order.css">
</head>

<body style="background-color: #418d99; color:white;">
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownOwner" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownOwner">
                            <a class="dropdown-item" href="profil.php">Profil</a>
                            <a class="dropdown-item" href="#">Pengaturan</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <section class="order-form-section" id="orderFormSection">
        <div class="container my-4">
            <h2 class="text-center mb-4 title-order">Buat Pesanan Pindahan Kosan Anda</h2>
            <div class="row justify-content-center">
                <div class="col-lg-7 mb-4">
                    <div class="card shadow-sm form-card">
                        <div class="card-body">
                            <h5 class="card-title mb-4 form-title">Detail Pindahan</h5>
                            <form id="orderForm">
                                <div class="mb-3">
                                    <label for="asal" class="form-label">Alamat Asal</label>
                                    <input type="text" class="form-control" id="asal" name="asal"
                                        placeholder="Contoh: Jl. Merdeka No. 10, Jakarta Pusat" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tujuan" class="form-label">Alamat Tujuan</label>
                                    <input type="text" class="form-control" id="tujuan" name="tujuan"
                                        placeholder="Contoh: Jl. Perjuangan No. 5, Bekasi Barat" required>
                                </div>
                                <div class="mb-3">
                                    <label for="jarak" class="form-label">Perkiraan Jarak (km)</label>
                                    <input type="number" class="form-control" id="jarak" name="jarak" min="0.1"
                                        step="0.1" value="1" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggalPindah" class="form-label">Tanggal Pindahan</label>
                                    <input type="date" class="form-control" id="tanggalPindah" name="tanggalPindah"
                                        required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Barang (Pilih yang sesuai)</label>
                                    <?php
                                    foreach ($harga_barang_list_server as $key => $item):
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            value="<?php echo htmlspecialchars($key); ?>"
                                            id="<?php echo htmlspecialchars($key); ?>" name="barang[]">
                                        <label class="form-check-label" for="<?php echo htmlspecialchars($key); ?>">
                                            <?php echo htmlspecialchars($item['nama'] . " (Rp " . number_format($item['harga']) . ")"); ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="catatanTambahan" class="form-label">Catatan Tambahan (Opsional)</label>
                                    <textarea class="form-control" id="catatanTambahan" name="catatanTambahan" rows="3"
                                        placeholder="Contoh: Barang rapuh, harap hati-hati"></textarea>
                                </div>
                                <div id="payment-method-container" class="d-none">
                                    <?php
                                $metode_pembayaran_list_html = ["OVO", "DANA", "Gopay", "Bank Transfer", "Cash"];
                                foreach($metode_pembayaran_list_html as $metode_html):
                                ?>
                                    <input class="form-check-input" type="radio" name="metodePembayaran"
                                        id="hidden_<?php echo strtolower(str_replace(' ', '', $metode_html)); ?>"
                                        value="<?php echo $metode_html; ?>"
                                        <?php echo ($metode_html == "OVO") ? 'checked' : ''; ?>>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card shadow-sm summary-card sticky-top" style="top: 100px;">
                        <div class="card-body">
                            <h5 class="card-title mb-4 summary-title">Ringkasan Biaya</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Biaya Jarak (<span id="summaryJarak">0</span> km)</span>
                                <span id="biayaJarak">Rp 0</span>
                            </div>
                            <div id="summaryBarang" class="mb-2"></div>
                            <hr>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Subtotal</span>
                                <span id="subtotalBiaya">Rp 0</span>
                            </div>
                            <div id="summaryDiskon" class="d-flex justify-content-between mb-1 text-danger"
                                style="display: none;">
                                <span>Diskon</span>
                                <span id="nilaiDiskon">- Rp 0</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold total-price" style="font-size: 1.2em;">
                                <span>Total</span>
                                <span id="totalBiaya">Rp 0</span>
                            </div>

                            <h5 class="card-title mt-4 mb-3 payment-title">Metode Pembayaran</h5>
                            <?php foreach($metode_pembayaran_list_html as $metode_html): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="metodePembayaran"
                                    id="<?php echo strtolower(str_replace(' ', '', $metode_html)); ?>"
                                    value="<?php echo $metode_html; ?>" form="orderForm"
                                    <?php echo ($metode_html == "OVO") ? 'checked' : ''; ?>>
                                <label class="form-check-label"
                                    for="<?php echo strtolower(str_replace(' ', '', $metode_html)); ?>">
                                    <?php echo $metode_html; echo ($metode_html == "Bank Transfer") ? " (VA)" : ""; echo ($metode_html == "Cash") ? " (di Tempat)" : "";?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <button type="button" class="btn btn-primary w-100 mt-4" id="bayarButton" disabled>Pesan
                                Sekarang</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../javascript/order.js"></script>
</body>

</html>