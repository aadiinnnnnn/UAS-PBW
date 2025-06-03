<?php
include 'session.php'; // Memastikan pengguna sudah login
include '..\koneksi.php'; // Koneksi ke database

$message = '';
$error = '';
$paket_layanan = [];

// Ambil data paket dari database untuk mengisi radio button
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
    $error = "Gagal memuat daftar paket layanan: " . mysqli_error($conn) . " (Query: " . $query_paket . ")";
}

// Logic untuk memproses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_SESSION['user_id']; // ID user dari session
    $id_bk = $_POST['id_bk'] ?? ''; // ID paket dari form
    $jenis_paket_bk = $_POST['nama_paket'] ?? ''; // Nama paket dari form
    $metode_pembayaran_bk = $_POST['metode_pembayaran'] ?? ''; // Metode pembayaran
    $total_harga_bk = $_POST['total_biaya_input'] ?? 0; // Total harga
    $tanggal_datang_bk = $_POST['tanggal_datang'] ?? ''; // Tanggal datang/pelaksanaan

    // Validasi input
    if (empty($id_bk) || empty($jenis_paket_bk) || empty($metode_pembayaran_bk) || $total_harga_bk <= 0 || empty($tanggal_datang_bk)) {
        $error = "Semua field wajib diisi.";
    } elseif ($total_harga_bk <= 0) {
        $error = "Total biaya harus lebih dari Rp 0.";
    } else {
        // Mendapatkan tanggal order saat ini
        $tanggal_order_bk = date('Y-m-d');

        // Pastikan id_bk ada di database dan harga sesuai
        $stmt_check_paket = $conn->prepare("SELECT harga_bk, deskripsi_layanan_bk FROM layanan_bersih_kos WHERE id_bk = ?");
        if ($stmt_check_paket) {
            $stmt_check_paket->bind_param("s", $id_bk);
            $stmt_check_paket->execute();
            $result_check_paket = $stmt_check_paket->get_result();
            if ($result_check_paket->num_rows > 0) {
                $data_paket_db = $result_check_paket->fetch_assoc();
                $harga_valid_db = $data_paket_db['harga_bk'];
                $deskripsi_paket_db = $data_paket_db['deskripsi_layanan_bk'];

                // Verifikasi harga dari client dengan harga dari database
                if ($total_harga_bk != $harga_valid_db) {
                    $error = "Harga paket tidak sesuai dengan data. Ada masalah validasi.";
                } else {
                    // Masukkan data ke tabel order_layanan_bersih_kos
                    // *** PERHATIKAN: id_order_bk tidak disertakan di sini ***
                    $stmt_insert = $conn->prepare("INSERT INTO order_layanan_bersih_kos (id_bk, id_user, jenis_paket_bk, tanggal_order_bk, tanggal_datang_bk, total_harga_bk, metode_pembayaran_bk) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt_insert) {
                        // *** PERHATIKAN: Tidak ada 'i' untuk id_order_bk di sini ***
                        $stmt_insert->bind_param("sssssds", $id_bk, $id_user, $jenis_paket_bk, $tanggal_order_bk, $tanggal_datang_bk, $total_harga_bk, $metode_pembayaran_bk);

                        if ($stmt_insert->execute()) { // Baris ini adalah baris 61
                            // Simpan detail pesanan ke session untuk halaman sukses
                            $_SESSION['latestBersihOrderDetails'] = [
                                'orderId' => $stmt_insert->insert_id, // Gunakan ID yang di-generate otomatis oleh DB
                                'id_bk' => $id_bk,
                                'jenis_paket_bk' => $jenis_paket_bk,
                                'tanggal_order_bk' => $tanggal_order_bk,
                                'tanggal_datang_bk' => $tanggal_datang_bk,
                                'total_harga_bk' => $total_harga_bk,
                                'metode_pembayaran_bk' => $metode_pembayaran_bk,
                                'deskripsi_paket' => $deskripsi_paket_db // Sertakan deskripsi dari DB
                            ];
                            // Redirect ke halaman sukses
                            header("Location: order_bersih_sukses.php");
                            exit();
                        } else {
                            $error = "Gagal menyimpan pesanan ke database: " . $stmt_insert->error;
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/bersih.css">
</head>

<body>
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
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a href="pilihan.php" class="nav-link order-btn-nav">Order Layanan Lain</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="../assets/img/default-profile.png" class="profile-icon-sm" alt="Profil">
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

    <div class="container py-5">
        <h2 class="mb-4">Form Pemesanan Jasa Bersih Kos</h2>

        <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Pilih Paket <span class="text-danger">*</span></label>
                <?php if (!empty($paket_layanan)): ?>
                <?php foreach ($paket_layanan as $paket): ?>
                <div class="form-check">
                    <input class="form-check-input paket-radio" type="radio" name="paket_kos"
                        id="paket_<?php echo htmlspecialchars($paket['id_bk']); ?>"
                        value="<?php echo htmlspecialchars($paket['harga_bk']); ?>"
                        data-idbk="<?php echo htmlspecialchars($paket['id_bk']); ?>"
                        data-nama="<?php echo htmlspecialchars($paket['jenis_paket_bk'] . ' - ' . $paket['deskripsi_layanan_bk']); ?>"
                        required>
                    <label class="form-check-label" for="paket_<?php echo htmlspecialchars($paket['id_bk']); ?>">
                        <?php echo htmlspecialchars($paket['jenis_paket_bk']); ?> -
                        <?php echo htmlspecialchars($paket['deskripsi_layanan_bk']); ?> (Rp
                        <?php echo number_format($paket['harga_bk'], 0, ',', '.'); ?>)
                    </label>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p class="text-muted">Tidak ada paket layanan yang tersedia saat ini.</p>
                <p class="text-muted">Harap tambahkan data ke tabel `layanan_bersih_kos` di database Anda.</p>
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

            <div class="mb-3">
                <h5>Ringkasan Pesanan</h5>
                <div id="ringkasanDetail" class="p-3 border rounded text-muted">Pilih paket untuk melihat ringkasan.
                </div>
                <p class="mt-2"><strong>Total: </strong><span id="totalBiayaText">Rp 0</span></p>
            </div>

            <input type="hidden" name="id_bk" id="id_bk_input">
            <input type="hidden" name="nama_paket" id="nama_paket_input">
            <input type="hidden" name="total_biaya_input" id="total_biaya_input">

            <button type="submit" class="btn btn-pesan">Pesan Sekarang</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../javascript/bersih.js" defer></script>
</body>

</html>