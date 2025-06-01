<?php
require 'session.php'; //
require '../koneksi.php'; //

// Daftar harga barang (SERVER-SIDE, untuk verifikasi & jika JS mati)
$harga_barang_list_server = [
    'koper' => ['nama' => 'Koper/Tas Pakaian', 'harga' => 20000],
    'kardusSedang' => ['nama' => 'Kardus Sedang', 'harga' => 35000],
    // ... (lengkap seperti di order.js dan order.php sebelumnya)
    'mejaBelajar' => ['nama' => 'Meja Belajar', 'harga' => 60000],
];
$harga_per_km_server = 5000;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_order_js'])) {
    // Ini adalah request dari JavaScript fetch
    header('Content-Type: application/json'); // Set header untuk response JSON
    $response_data = ['success' => false, 'message' => 'Gagal memproses permintaan.'];

    $asal = isset($_POST['asal']) ? trim($_POST['asal']) : '';
    $tujuan = isset($_POST['tujuan']) ? trim($_POST['tujuan']) : '';
    $jarak_input = isset($_POST['jarak']) ? trim($_POST['jarak']) : '0';
    $jarak = filter_var($jarak_input, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

    $tanggalPindah = isset($_POST['tanggalPindah']) ? $_POST['tanggalPindah'] : '';
    $barang_pilihan_client = isset($_POST['barang']) && is_array($_POST['barang']) ? $_POST['barang'] : [];
    $catatanTambahan = isset($_POST['catatanTambahan']) ? trim($_POST['catatanTambahan']) : '';
    $metodePembayaran = isset($_POST['metodePembayaran']) ? $_POST['metodePembayaran'] : 'OVO';

    // Validasi Server-side (PENTING!)
    if (empty($asal) || empty($tujuan) || $jarak === false || empty($tanggalPindah) || empty($barang_pilihan_client)) {
        $response_data['message'] = "Data tidak lengkap. Pastikan semua field wajib diisi.";
        echo json_encode($response_data);
        exit;
    }

    // Kalkulasi ulang total biaya di server untuk verifikasi (PENTING!)
    $calculatedTotalBiayaServer = 0;
    $calculatedBiayaJarakServer = $jarak * $harga_per_km_server;
    $calculatedTotalBiayaServer += $calculatedBiayaJarakServer;
    $barangPindahanDisplayArray = [];

    foreach ($barang_pilihan_client as $item_key) {
        if (isset($harga_barang_list_server[$item_key])) {
            $calculatedTotalBiayaServer += $harga_barang_list_server[$item_key]['harga'];
            $barangPindahanDisplayArray[] = $harga_barang_list_server[$item_key]['nama'];
        } else {
            $response_data['message'] = "Jenis barang tidak valid: " . htmlspecialchars($item_key);
            echo json_encode($response_data);
            exit;
        }
    }
    $barang_string_for_db = implode(", ", $barangPindahanDisplayArray);


    // Simpan ke Database
    $orderId = "MV-" . strtoupper(substr(uniqid(), -6));
    $id_user_session = null;
    if (isset($_SESSION['user_id'])) { // Dari login.php jika diset
        $id_user_session = $_SESSION['user_id'];
    } elseif (isset($_SESSION['username'])) { // Fallback jika hanya username yang diset
        $stmt_get_id = $conn->prepare("SELECT id_user FROM user WHERE username = ?");
        if ($stmt_get_id) {
            $stmt_get_id->bind_param("s", $_SESSION['username']);
            $stmt_get_id->execute();
            $result_id = $stmt_get_id->get_result();
            if ($result_id->num_rows > 0) {
                $id_user_session = $result_id->fetch_assoc()['id_user'];
            }
            $stmt_get_id->close();
        }
    }

    if (!$id_user_session) {
        $response_data['message'] = "Sesi pengguna tidak ditemukan. Silakan login kembali.";
        echo json_encode($response_data);
        exit;
    }

    $status_pesanan = "Menunggu Pembayaran";
    $stmt = $conn->prepare("INSERT INTO pesanan (id_pesanan, id_user, alamat_asal, alamat_tujuan, jarak_km, tanggal_pindah, jenis_barang, total_biaya, metode_pembayaran, catatan_tambahan, status_pesanan, tanggal_pesan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    if ($stmt) {
        $stmt->bind_param("ssssisssdss", $orderId, $id_user_session, $asal, $tujuan, $jarak, $tanggalPindah, $barang_string_for_db, $calculatedTotalBiayaServer, $metodePembayaran, $catatanTambahan, $status_pesanan);
        if ($stmt->execute()) {
            $response_data['success'] = true;
            $response_data['message'] = 'Pesanan berhasil disimpan!';
            $response_data['orderId'] = $orderId;
            $response_data['totalBiaya'] = $calculatedTotalBiayaServer;
            $response_data['metodePembayaran'] = $metodePembayaran;
            $response_data['asal'] = $asal;
            $response_data['tujuan'] = $tujuan;
            $response_data['jarak'] = $jarak;
            $response_data['tanggalPindah'] = $tanggalPindah;
            $response_data['catatanTambahan'] = $catatanTambahan;
            $response_data['barangPindahanDisplay'] = $barang_string_for_db;
            // $response_data['isUserLoggedIn'] = true; // Contoh jika ingin mengirim status login
        } else {
            $response_data['message'] = "Gagal menyimpan pesanan: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        $response_data['message'] = "Database error (prepare statement): " . htmlspecialchars($conn->error);
    }
    echo json_encode($response_data);
    exit; // Penting untuk menghentikan eksekusi HTML di bawah jika ini adalah AJAX/fetch request
}

// ... (Sisa HTML dari order.php sebelumnya untuk tampilan form awal diletakkan di sini)
// Ini akan menjadi fallback jika JavaScript dimatikan atau jika pengguna mengakses order.php secara langsung.
// Jika Anda ingin halaman ini HANYA merespon AJAX, Anda bisa menghapus bagian HTML di bawah.
// Namun, biasanya lebih baik memiliki fallback HTML.

// Untuk fallback HTML (jika JS mati atau direct access), Anda bisa menggunakan logika PHP yang sudah ada di versi `order.php` tanpa JS klien.
// Misalnya:
$asal_php = ''; $tujuan_php = ''; // dan seterusnya untuk nilai default form jika diakses tanpa JS
// Jika mau, bisa copy-paste bagian form HTML dari `order.php` versi "tanpa JS" ke sini.
// Tapi untuk contoh ini, saya akan fokus pada respons AJAX di atas.
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVER - Buat Pesanan Pindahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/indexuser.css">
    <style>
    /* Tambahkan sedikit style untuk pesan error/sukses jika belum ada di indexuser.css */
    .success-section {
        padding: 20px;
        text-align: center;
        display: none;
        /* Default disembunyikan oleh JS */
    }

    .success-card {
        background-color: #fff;
        color: #333;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        display: inline-block;
    }

    .success-icon {
        font-size: 50px;
        color: green;
        margin-bottom: 15px;
    }

    .success-title {
        font-size: 24px;
        margin-bottom: 10px;
    }

    .order-details-summary {
        text-align: left;
        margin-top: 20px;
    }

    .order-details-summary p {
        margin-bottom: 5px;
    }

    .order-details-summary hr {
        margin: 10px 0;
    }

    .btn-back-home {
        margin-top: 20px;
        background-color: #418d99;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
    }

    .btn-back-home:hover {
        background-color: #367a8a;
    }

    .content-section {
        padding-top: 20px;
        padding-bottom: 20px;
    }
    </style>
</head>

<body>
    <header class="navbar">
        <div class="logo">LOGO</div>
        <nav class="nav-menu">
            <div class="profile-icon"><img src="../assets/img/red-truck.png" alt="User Profile" /></div>
            <a href="#">About</a>
            <a href="#">Contact</a>
            <a href="order.php"><button class="order-btn" disabled>Order <span class="arrow">▶</span></button></a>
            <a href="logout.php"><button class="logout-btn">Logout</button></a>
        </nav>
    </header>

    <section class="order-form-section" id="orderFormSection">
        <div class="container my-5 content-section">
            <h2 class="text-center mb-4 title-order">Buat Pesanan Pindahan Kosan Anda</h2>
            <div class="row">
                <div class="col-lg-8 mb-4">
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
                                    <input type="number" class="form-control" id="jarak" name="jarak" min="1" value="1"
                                        required>
                                    <small class="form-text text-muted">Jarak akan mempengaruhi biaya
                                        pengiriman.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggalPindah" class="form-label">Tanggal Pindahan</label>
                                    <input type="date" class="form-control" id="tanggalPindah" name="tanggalPindah"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Barang (Pilih yang sesuai)</label>
                                    <?php
                                    // Ini untuk fallback jika JS mati, atau untuk memastikan ID-nya ada.
                                    // Di order.js, HARGA_BARANG menggunakan key seperti 'koper', 'kardusSedang'. Pastikan ID checkbox sama.
                                    $harga_barang_js_keys = [ // Sesuaikan dengan keys di order.js
                                        'koper' => 'Koper/Tas Pakaian (Rp 20.000)',
                                        'kardusSedang' => 'Kardus Sedang (Rp 35.000)',
                                        'kardusBesar' => 'Kardus Besar (Rp 50.000)',
                                        'lemariKecil' => 'Lemari Kecil (Rp 100.000)',
                                        'kasurSingle' => 'Kasur Single (Rp 75.000)',
                                        'mejaBelajar' => 'Meja Belajar (Rp 60.000)'
                                    ];
                                    foreach ($harga_barang_js_keys as $key => $label_text):
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="<?php echo $key; ?>"
                                            id="<?php echo $key; ?>" name="barang[]">
                                        <label class="form-check-label" for="<?php echo $key; ?>">
                                            <?php echo htmlspecialchars($label_text); ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                    <small class="form-text text-muted">Pilih barang-barang yang akan dipindahkan untuk
                                        estimasi biaya.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="catatanTambahan" class="form-label">Catatan Tambahan (Opsional)</label>
                                    <textarea class="form-control" id="catatanTambahan" name="catatanTambahan" rows="3"
                                        placeholder="Contoh: Barang rapuh, harap hati-hati"></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm summary-card">
                        <div class="card-body">
                            <h5 class="card-title mb-4 summary-title">Ringkasan Biaya</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Biaya Jarak (<span id="summaryJarak">0</span> km)</span>
                                <span id="biayaJarak">Rp 0</span>
                            </div>
                            <div id="summaryBarang">
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold total-price">
                                <span>Total Biaya</span>
                                <span id="totalBiaya">Rp 0</span>
                            </div>

                            <h5 class="card-title mt-4 mb-3 payment-title">Metode Pembayaran</h5>
                            <?php
                                $metode_pembayaran_list_html = ["OVO", "DANA", "Gopay", "Bank Transfer", "Cash"];
                                foreach($metode_pembayaran_list_html as $metode_html):
                                ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="metodePembayaran"
                                    id="<?php echo strtolower(str_replace(' ', '', $metode_html)); ?>"
                                    value="<?php echo $metode_html; ?>"
                                    <?php echo ($metode_html == "OVO") ? 'checked' : ''; ?>>
                                <label class="form-check-label"
                                    for="<?php echo strtolower(str_replace(' ', '', $metode_html)); ?>">
                                    <?php echo $metode_html; echo ($metode_html == "Bank Transfer") ? " (BCA, Mandiri, BRI)" : ""; echo ($metode_html == "Cash") ? " (Pembayaran di tempat)" : "";?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <button type="button" class="btn btn-success-custom w-100 mt-4" id="bayarButton"
                                disabled>Bayar Sekarang</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="success-section" id="successSection">
        <div class="container my-5">
            <div class="success-card">
                <div class="success-icon">&#10004;</div>
                <h2 class="success-title">Pembayaran Berhasil!</h2>
                <p class="success-message">Terima kasih telah menggunakan layanan Mover. Pesanan Anda telah diterima dan
                    akan segera kami proses.</p>
                <div class="order-details-summary">
                    <h6>Ringkasan Pesanan Anda:</h6>
                    <p><span><strong>Nomor Pesanan:</strong></span> <span id="orderId">#MV-XXXXXX</span></p>
                    <p><span><strong>Total Pembayaran:</strong></span> <span id="totalPaid">Rp 0</span></p>
                    <p><span><strong>Metode Pembayaran:</strong></span> <span id="paymentMethod"></span></p>
                    <hr>
                    <p><span><strong>Alamat Asal:</strong></span> <span id="summaryAsal"></span></p>
                    <p><span><strong>Alamat Tujuan:</strong></span> <span id="summaryTujuan"></span></p>
                    <p><span><strong>Jarak:</strong></span> <span id="summaryJarakFinal"></span> km</p>
                    <p><span><strong>Tanggal Pindah:</strong></span> <span id="summaryTanggalPindah"></span></p>
                    <p><span><strong>Barang Pindahan:</strong></span> <span id="summaryBarangFinal"></span></p>
                    <p><span><strong>Catatan Tambahan:</strong></span> <span id="summaryCatatan"></span></p>
                </div>
                <p class="mt-4 text-muted">Tim Mover akan segera menghubungi Anda melalui nomor telepon yang terdaftar
                    untuk konfirmasi lebih lanjut dan penjadwalan.</p>
                <a href="indexuser.php" class="btn btn-back-home" id="backToHomeAfterOrder">Kembali ke Beranda</a>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../javascript/order.js"></script>
    <script>
    // Variabel ini akan diisi oleh PHP berdasarkan status sesi
    const isLoggedIn_php = <?php echo json_encode(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true); ?>;
    const loggedInUsername_php = <?php echo json_encode(isset($_SESSION['username']) ? $_SESSION['username'] : ''); ?>;
    const currentPage_php = <?php echo json_encode(basename($_SERVER['PHP_SELF'])); ?>;
    const currentDir_php = <?php echo json_encode(basename(dirname($_SERVER['PHP_SELF']))); ?>; // Misal: 'admin'
    </script>
    <script src="../javascript/global-auth.js"></script>
</body>

</html>