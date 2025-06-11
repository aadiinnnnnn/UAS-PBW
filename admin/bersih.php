<?php
include 'session.php'; // Memastikan pengguna sudah login
include '..\koneksi.php'; // Koneksi ke database

$message = '';
$error = '';
$paket_layanan = [];
// BARU: Definisikan persentase diskon. Urutan penting: [murah, menengah, mahal]
$discounts = [10, 30, 50]; 

// Ambil data paket dari database untuk mengisi radio button
// Diurutkan dari harga termurah ke termahal
$query_paket = "SELECT id_bk, jenis_paket_bk, deskripsi_layanan_bk, durasi_layanan_bk, harga_bk FROM layanan_bersih_kos ORDER BY harga_bk ASC";
$result_paket = mysqli_query($conn, $query_paket);
if ($result_paket) {
    if (mysqli_num_rows($result_paket) > 0) {
        while ($row = mysqli_fetch_assoc($result_paket)) {
            $paket_layanan[] = $row;
        }
    } else {
        $error = "Tidak ada data paket layanan yang ditemukan di database.";
    }
} else {
    $error = "Gagal memuat daftar paket layanan: " . mysqli_error($conn);
}

// Logic untuk memproses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_SESSION['user_id']; // ID user dari session
    $id_bk = $_POST['id_bk'] ?? ''; // ID paket dari form
    $jenis_paket_bk = $_POST['nama_paket'] ?? ''; // Nama paket dari form
    $metode_pembayaran_bk = $_POST['metode_pembayaran'] ?? ''; // Metode pembayaran
    $total_harga_bk_client = (float) ($_POST['total_biaya_input'] ?? 0); // Total harga dari klien
    $tanggal_datang_bk = $_POST['tanggal_datang'] ?? ''; // Tanggal datang/pelaksanaan

    // Validasi input
    if (empty($id_bk) || empty($jenis_paket_bk) || empty($metode_pembayaran_bk) || $total_harga_bk_client <= 0 || empty($tanggal_datang_bk)) {
        $error = "Semua field wajib diisi.";
    } else {
        // Mendapatkan tanggal order saat ini
        $tanggal_order_bk = date('Y-m-d');

        // MODIFIKASI: Validasi harga dengan memperhitungkan diskon di server
        $stmt_check_paket = $conn->prepare("SELECT harga_bk, deskripsi_layanan_bk FROM layanan_bersih_kos WHERE id_bk = ?");
        if ($stmt_check_paket) {
            $stmt_check_paket->bind_param("s", $id_bk);
            $stmt_check_paket->execute();
            $result_check_paket = $stmt_check_paket->get_result();

            if ($result_check_paket->num_rows > 0) {
                $data_paket_db = $result_check_paket->fetch_assoc();
                $harga_asli_db = (float) $data_paket_db['harga_bk'];
                $deskripsi_paket_db = $data_paket_db['deskripsi_layanan_bk'];

                // BARU: Tentukan diskon yang seharusnya berlaku di server
                $diskon_server = 0;
                // Kita cari tahu index paket yang dipesan untuk menentukan diskonnya
                $paket_index = -1;
                foreach ($paket_layanan as $index => $paket) {
                    if ($paket['id_bk'] == $id_bk) {
                        $paket_index = $index;
                        break;
                    }
                }

                if ($paket_index !== -1 && isset($discounts[$paket_index])) {
                    $diskon_server = $discounts[$paket_index];
                }

                // BARU: Hitung harga final yang seharusnya di sisi server
                $nilai_diskon_server = $harga_asli_db * ($diskon_server / 100);
                $harga_final_server = $harga_asli_db - $nilai_diskon_server;
                
                // BARU: Verifikasi harga dari client dengan harga yang dihitung server (beri toleransi kecil)
                if (abs($total_harga_bk_client - $harga_final_server) > 0.01) {
                    $error = "Validasi harga gagal. Harga yang diterima: Rp " . number_format($total_harga_bk_client) . ", seharusnya: Rp " . number_format($harga_final_server) . ".";
                } else {
                    // Masukkan data ke tabel order_layanan_bersih_kos
                    $stmt_insert = $conn->prepare("INSERT INTO order_layanan_bersih_kos (id_bk, id_user, jenis_paket_bk, tanggal_order_bk, tanggal_datang_bk, total_harga_bk, metode_pembayaran_bk) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt_insert) {
                        $stmt_insert->bind_param("sssssds", $id_bk, $id_user, $jenis_paket_bk, $tanggal_order_bk, $tanggal_datang_bk, $harga_final_server, $metode_pembayaran_bk);

                        if ($stmt_insert->execute()) {
                            $_SESSION['latestBersihOrderDetails'] = [
                                'orderId' => $stmt_insert->insert_id,
                                'id_bk' => $id_bk,
                                'jenis_paket_bk' => $jenis_paket_bk,
                                'tanggal_order_bk' => $tanggal_order_bk,
                                'tanggal_datang_bk' => $tanggal_datang_bk,
                                'total_harga_bk' => $harga_final_server,
                                'metode_pembayaran_bk' => $metode_pembayaran_bk,
                                'deskripsi_paket' => $deskripsi_paket_db
                            ];
                            header("Location: order_bersih_sukses.php");
                            exit();
                        } else {
                            $error = "Gagal menyimpan pesanan: " . $stmt_insert->error;
                        }
                        $stmt_insert->close();
                    } else {
                        $error = "Gagal menyiapkan statement INSERT: " . $conn->error;
                    }
                }
            } else {
                $error = "ID Paket tidak ditemukan atau tidak valid.";
            }
            $stmt_check_paket->close();
        } else {
            $error = "Gagal menyiapkan statement cek paket: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Jasa Bersih Kos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/bersih.css">
</head>

<body>
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

    <div class="container py-5">
        <h2 class="mb-4">Form Pemesanan Jasa Bersih Kos</h2>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="form-label">Pilih Paket <span class="text-danger">*</span></label>
                <?php if (!empty($paket_layanan)): ?>
                <div class="row">
                    <?php foreach ($paket_layanan as $index => $paket): ?>
                    <?php
                            // BARU: Tentukan diskon berdasarkan index
                            $diskon_persen = isset($discounts[$index]) ? $discounts[$index] : 0;
                        ?>
                    <div class="col-md-4 mb-3">
                        <div class="form-check paket-card p-3">
                            <input class="form-check-input paket-radio visually-hidden" type="radio" name="paket_kos"
                                id="paket_<?php echo htmlspecialchars($paket['id_bk']); ?>"
                                value="<?php echo htmlspecialchars($paket['harga_bk']); ?>"
                                data-idbk="<?php echo htmlspecialchars($paket['id_bk']); ?>"
                                data-nama="<?php echo htmlspecialchars($paket['jenis_paket_bk']); ?>"
                                data-deskripsi="<?php echo htmlspecialchars($paket['deskripsi_layanan_bk']); ?>"
                                data-diskon="<?php echo $diskon_persen; ?>" required>
                            <label class="form-check-label w-100"
                                for="paket_<?php echo htmlspecialchars($paket['id_bk']); ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="paket-nama mb-0">
                                        <?php echo htmlspecialchars($paket['jenis_paket_bk']); ?></h5>
                                    <?php if ($diskon_persen > 0): ?>
                                    <span class="badge badge-diskon"><?php echo $diskon_persen; ?>% OFF</span>
                                    <?php endif; ?>
                                </div>
                                <p class="paket-deskripsi text-muted">
                                    <?php echo htmlspecialchars($paket['deskripsi_layanan_bk']); ?></p>
                                <p class="paket-harga text-end">Rp
                                    <?php echo number_format($paket['harga_bk'], 0, ',', '.'); ?></p>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">Tidak ada paket layanan yang tersedia saat ini.</p>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="tanggal_datang" class="form-label">Tanggal Pelaksanaan <span
                        class="text-danger">*</span></label>
                <input type="date" class="form-control" name="tanggal_datang" id="tanggal_datang" required
                    min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="mb-3">
                <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span
                        class="text-danger">*</span></label>
                <select class="form-select" name="metode_pembayaran" id="metode_pembayaran" required>
                    <option value="">- Pilih Metode Pembayaran -</option>
                    <option value="Transfer Bank">Transfer Bank</option>
                    <option value="E-Wallet">E-Wallet (OVO, DANA, dll)</option>
                    <option value="Cash">Cash / Bayar Langsung</option>
                </select>
            </div>

            <div class="mb-4">
                <h5>Ringkasan Pesanan</h5>
                <div id="ringkasanDetail" class="p-3 border rounded bg-light">
                    <p class="text-muted text-center mb-0">Pilih paket untuk melihat ringkasan.</p>
                </div>
                <div class="d-flex justify-content-between fw-bold mt-3" style="font-size: 1.2em;">
                    <span>Total Akhir:</span>
                    <span id="totalBiayaText">Rp 0</span>
                </div>
            </div>

            <input type="hidden" name="id_bk" id="id_bk_input">
            <input type="hidden" name="nama_paket" id="nama_paket_input">
            <input type="hidden" name="total_biaya_input" id="total_biaya_input">

            <button type="submit" class="btn btn-pesan w-100">Pesan Sekarang</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../javascript/bersih.js" defer></script>
</body>

</html>