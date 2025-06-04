<?php
require 'session.php'; // Pastikan session sudah dimulai dan user login jika perlu
require '../koneksi.php'; //

// Daftar harga barang (SERVER-SIDE, untuk verifikasi & jika JS mati)
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
    header('Content-Type: application/json'); //
    $response_data_json = ['success' => false, 'message' => 'Gagal memproses permintaan.']; // Untuk respons JSON

    $alamat_asal = isset($_POST['asal']) ? trim($_POST['asal']) : ''; //
    $alamat_tujuan = isset($_POST['tujuan']) ? trim($_POST['tujuan']) : ''; //
    $jarak_input = isset($_POST['jarak']) ? trim($_POST['jarak']) : '0'; //
    $jarak_km = filter_var($jarak_input, FILTER_VALIDATE_FLOAT, ["options" => ["min_range" => 0.1]]); //

    $tanggal_pindah = isset($_POST['tanggalPindah']) ? $_POST['tanggalPindah'] : ''; //
    $barang_pilihan_client = isset($_POST['barang']) && is_array($_POST['barang']) ? $_POST['barang'] : []; //
    $catatan_tambahan = isset($_POST['catatanTambahan']) ? trim($_POST['catatanTambahan']) : ''; //
    $metode_pembayaran = isset($_POST['metodePembayaran']) ? $_POST['metodePembayaran'] : 'OVO'; //

    // Validasi Server-side
    if (empty($alamat_asal) || empty($alamat_tujuan) || $jarak_km === false || empty($tanggal_pindah) || empty($barang_pilihan_client)) {
        $response_data_json['message'] = "Data tidak lengkap. Pastikan semua field wajib diisi dan jarak valid."; //
        echo json_encode($response_data_json); //
        exit; //
    }

    // Kalkulasi ulang total biaya di server
    $calculatedTotalBiayaServer = 0; //
    $calculatedBiayaJarakServer = $jarak_km * $harga_per_km_server; //
    $calculatedTotalBiayaServer += $calculatedBiayaJarakServer; //
    $barangPindahanDisplayArray = []; //

    foreach ($barang_pilihan_client as $item_key) { //
        if (isset($harga_barang_list_server[$item_key])) { //
            $calculatedTotalBiayaServer += $harga_barang_list_server[$item_key]['harga']; //
            $barangPindahanDisplayArray[] = $harga_barang_list_server[$item_key]['nama']; //
        } else {
            $response_data_json['message'] = "Jenis barang tidak valid: " . htmlspecialchars($item_key); //
            echo json_encode($response_data_json); //
            exit; //
        }
    }
    $jenis_barang_for_db = implode(", ", $barangPindahanDisplayArray); //

    // Dapatkan id_user dari session
    $id_user_session = null; //
    if (isset($_SESSION['user_id'])) { // Diasumsikan 'user_id' adalah kunci session untuk ID pengguna
        $id_user_session = $_SESSION['user_id']; //
    } elseif (isset($_SESSION['username'])) { // Fallback jika 'username' yang ada
        $stmt_get_id = $conn->prepare("SELECT id_user FROM user WHERE username = ?"); //
        if ($stmt_get_id) { //
            $stmt_get_id->bind_param("s", $_SESSION['username']); //
            $stmt_get_id->execute(); //
            $result_id = $stmt_get_id->get_result(); //
            if ($result_id->num_rows > 0) { //
                $id_user_session = $result_id->fetch_assoc()['id_user']; //
            }
            $stmt_get_id->close(); //
        }
    }

    if (!$id_user_session) { //
        $response_data_json['message'] = "Sesi pengguna tidak ditemukan. Silakan login kembali."; //
        echo json_encode($response_data_json); //
        exit; //
    }

    $status_pesanan = "Menunggu Pembayaran"; // Default status

    // ====================================================================
    // BAGIAN PREPARE DAN BIND_PARAM YANG SUDAH DIISI
    // ====================================================================
    $stmt = $conn->prepare( //
        "INSERT INTO order_layanan_pindahan_barang_kos 
        (id_user, alamat_jemput, alamat_tujuan, jarak_km, tanggal_datang_pk, jenis_barang, total_harga_pk, metode_pembayaran_pk, catatan_tambahan, status_pesanan) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if ($stmt) { //
        $stmt->bind_param( //
            "sssdsdssss", 
            $id_user_session,
            $alamat_asal,          // Variabel $alamat_asal akan masuk ke kolom alamat_jemput
            $alamat_tujuan,
            $jarak_km,             // Tipe 'd' untuk decimal/double
            $tanggal_pindah,       // Variabel $tanggal_pindah akan masuk ke kolom tanggal_datang_pk
            $jenis_barang_for_db,
            $calculatedTotalBiayaServer, // Tipe 'd' untuk double
            $metode_pembayaran,    // Variabel $metode_pembayaran akan masuk ke kolom metode_pembayaran_pk
            $catatan_tambahan,
            $status_pesanan
        );
    // ====================================================================

        if ($stmt->execute()) { //
            $newOrderId = mysqli_insert_id($conn); //

            // Data yang akan disimpan di session untuk order_sukses.php
            $orderDataToStoreInSession = [
                'orderId' => $newOrderId,
                'totalBiaya' => $calculatedTotalBiayaServer,
                'metodePembayaran' => $metode_pembayaran,
                'asal' => $alamat_asal,
                'tujuan' => $alamat_tujuan,
                'jarak' => $jarak_km,
                'tanggalPindah' => $tanggal_pindah,
                'barangPindahanDisplay' => $jenis_barang_for_db,
                'catatanTambahan' => $catatan_tambahan
            ];

            $_SESSION['order_details'] = $orderDataToStoreInSession; //

            // Respons JSON ke JavaScript
            $response_data_json = [
                'success' => true,
                'message' => 'Pesanan berhasil diproses! Anda akan dialihkan...',
                'orderId' => $newOrderId 
            ];
            
        } else {
            $response_data_json = ['success' => false, 'message' => "Gagal menyimpan pesanan: " . htmlspecialchars($stmt->error)]; //
        }
        $stmt->close(); //
    } else {
        $response_data_json = ['success' => false, 'message' => "Database error (prepare statement): " . htmlspecialchars($conn->error)]; //
    }
    echo json_encode($response_data_json); //
    exit; // Penting setelah merespons AJAX
}

// Bagian HTML dari order.php tetap sama seperti yang Anda miliki
// ... (kode HTML untuk form pemesanan) ...
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVER - Buat Pesanan Pindahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/carikost.css">
    <style>
    /* Anda bisa menambahkan style spesifik untuk order.php di sini jika perlu */
    /* Style dasar dari respons sebelumnya untuk kartu, dll. sudah ada di order_sukses.php */
    /* Jika ingin konsisten, beberapa style global bisa ditaruh di indexuser.css */
    .form-card,
    .summary-card {
        background-color: #ffffff;
        color: #333;
        border: none;
        border-radius: 15px;
        /* */
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .title-order {
        color: white;
        font-weight: bold;
    }

    .form-title,
    .summary-title,
    .payment-title {
        color: #2f4f4f;
        font-weight: 600;
    }

    .form-label {
        color: #495057;
        font-weight: 500;
    }

    .form-control {
        border-radius: 8px;
        /* */
        border: 1px solid #ced4da;
    }

    .btn-success-custom {
        /* Untuk tombol "Pesan Sekarang" */
        background-color: #ffd700;
        /* */
        border-color: #ffd700;
        /* */
        color: #2f4f4f;
        /* */
        font-weight: 600;
        /* */
        padding: 10px 20px;
    }

    .btn-success-custom:hover {
        background-color: #e0c200;
        /* */
        border-color: #e0c200;
        /* */
    }

    /* Navbar styling (jika belum tercakup oleh indexuser.css atau butuh override) */
    .navbar {
        padding: 20px 40px;
    }

    /* */
    .nav-menu .order-btn {
        /* Tombol Beranda di navbar order.php */
        background-color: white;
        color: #418d99;
        padding: 8px 20px;
        font-weight: 500;
        border: none;
        border-radius: 8px;
        /* */
    }

    .nav-menu .logout-btn {
        background-color: #FFD700;
        color: #2f4f4f;
        padding: 8px 20px;
        font-weight: 500;
        border: none;
        border-radius: 8px;
        /* */
    }

    .profile-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* */
    </style>
</head>

<body style="background-color: #418d99; color:white; padding-top: 90px;">
    <header class="header-custom sticky-top">
        <nav class="container navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="indexuser.php">LOGO MOVER</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto align-items-center">
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
                                    <small class="form-text text-muted" style="color: #6c757d;">Jarak akan mempengaruhi
                                        biaya pengiriman.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggalPindah" class="form-label">Tanggal Pindahan</label>
                                    <input type="date" class="form-control" id="tanggalPindah" name="tanggalPindah"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Barang (Pilih yang sesuai)</label>
                                    <?php
                                    // Loop menggunakan $harga_barang_list_server
                                    if (isset($harga_barang_list_server) && is_array($harga_barang_list_server)) {
                                        foreach ($harga_barang_list_server as $key => $item):
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            value="<?php echo htmlspecialchars($key); ?>"
                                            id="<?php echo htmlspecialchars($key); ?>" name="barang[]">
                                        <label class="form-check-label" for="<?php echo htmlspecialchars($key); ?>">
                                            <?php 
                                            $nama_barang = isset($item['nama']) ? $item['nama'] : 'Nama tidak tersedia';
                                            $harga_barang = isset($item['harga']) ? $item['harga'] : 0;
                                            echo htmlspecialchars($nama_barang . " (Rp " . number_format($harga_barang) . ")"); 
                                            ?>
                                        </label>
                                    </div>
                                    <?php 
                                        endforeach;
                                    } else {
                                        echo "<p class='text-danger'>Daftar barang tidak tersedia.</p>";
                                    }
                                    ?>
                                    <small class="form-text text-muted" style="color: #6c757d;">Pilih barang-barang yang
                                        akan dipindahkan.</small>
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
                <div class="col-lg-5">
                    <div class="card shadow-sm summary-card sticky-top" style="top: 100px;">
                        <div class="card-body">
                            <h5 class="card-title mb-4 summary-title">Ringkasan Biaya</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Biaya Jarak (<span id="summaryJarak">0</span> km)</span>
                                <span id="biayaJarak">Rp 0</span>
                            </div>
                            <div id="summaryBarang">
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold total-price" style="font-size: 1.2em;">
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
                                    <?php echo $metode_html; echo ($metode_html == "Bank Transfer") ? " (Virtual Account)" : ""; echo ($metode_html == "Cash") ? " (Bayar di Tempat)" : "";?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <button type="button" class="btn btn-success-custom w-100 mt-4" id="bayarButton"
                                disabled>Pesan Sekarang</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../javascript/order.js"></script>
    <script>
    // Variabel ini akan diisi oleh PHP berdasarkan status sesi
    const isLoggedIn_php = <?php echo json_encode(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true); ?>;
    const loggedInUsername_php = <?php echo json_encode(isset($_SESSION['username']) ? $_SESSION['username'] : ''); ?>;
    </script>
</body>

</html>