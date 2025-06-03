<?php
session_start();
require '../koneksi.php'; // Pastikan path ini benar

// Aktifkan pelaporan error untuk debugging (hapus atau beri komentar di lingkungan produksi)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Pengecekan Sesi Pengguna
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// 2. Pengecekan Peran (Role)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Akses Ditolak</title>";
    echo "<link href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<style>body { display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f8f9fa; }</style>";
    echo "</head><body><div class='container text-center'>";
    echo "<div class='alert alert-danger' role='alert'><h1>Akses Ditolak!</h1><p>Anda tidak memiliki izin untuk mengakses halaman ini sebagai pemilik.</p>";
    echo "<a href='indexuser.php' class='btn btn-primary mt-3'>Kembali ke Dasbor Pengguna</a>";
    echo "&nbsp;<a href='logout.php' class='btn btn-secondary mt-3'>Logout</a>";
    echo "</div></div></body></html>";
    exit;
}

$idPemilik = $_SESSION['user_id'];
$namaPemilik = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Pemilik';

// --- Pengambilan Data untuk Kartu Ringkasan ---
$totalKostAktif = 0;
// PASTIKAN tabel pengelolaan_kost memiliki kolom id_user dan terisi dengan benar
$queryKostAktif = "SELECT COUNT(*) AS total_aktif FROM pengelolaan_kost WHERE tersedia = 1 AND id_user = '$idPemilik'";
$resultKostAktif = mysqli_query($conn, $queryKostAktif);
if ($resultKostAktif) {
    $dataKostAktif = mysqli_fetch_assoc($resultKostAktif);
    $totalKostAktif = $dataKostAktif['total_aktif'] ?? 0;
} else {
    if (ini_get('display_errors')) { echo "<p class='text-danger'>Error query totalKostAktif: " . mysqli_error($conn) . "</p>"; }
}

$totalKamarTersedia = 0;
// PASTIKAN tabel pengelolaan_kost memiliki kolom id_user dan terisi dengan benar
$queryKamarTersedia = "SELECT SUM(jumlah) AS total_kamar FROM pengelolaan_kost WHERE tersedia = 1 AND id_user = '$idPemilik'";
$resultKamarTersedia = mysqli_query($conn, $queryKamarTersedia);
if ($resultKamarTersedia) {
    $dataKamarTersedia = mysqli_fetch_assoc($resultKamarTersedia);
    $totalKamarTersedia = $dataKamarTersedia['total_kamar'] ?? 0;
} else {
    if (ini_get('display_errors')) { echo "<p class='text-danger'>Error query totalKamarTersedia: " . mysqli_error($conn) . "</p>"; }
}

$pesanBaru = 0; // Placeholder, sesuaikan dengan logika pesan Anda

// --- Pengambilan Data untuk Properti Milik Anda ---
// Kueri ini mengambil semua kolom yang relevan, termasuk yang baru Anda tambahkan.
// PASTIKAN tabel pengelolaan_kost memiliki kolom id_user dan terisi dengan benar.
$queryProperti = "SELECT id_kos_plk, nama, tipe, tersedia, jumlah, lokasi,
                         gambar_url, gambar_dalam_kamar_url, gambar_kamar_mandi_url,
                         harga_sewa, periode_sewa
                  FROM pengelolaan_kost
                  WHERE id_user = '$idPemilik'
                  ORDER BY id_kos_plk DESC";
$resultProperti = mysqli_query($conn, $queryProperti);
if (!$resultProperti) {
     if (ini_get('display_errors')) { echo "<p class='text-danger'>Error query resultProperti: " . mysqli_error($conn) . "</p>"; }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Pemilik - MOVER</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/owner.css">
    <link rel="stylesheet" href="../css/pemilik.css">
</head>

<body data-spy="scroll" data-target="#navbarNavOwnerDashboard" data-offset="85">

    <header class="header-custom sticky-top">
        <nav class="container navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="owner.php">LOGO MOVER</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavOwnerDashboard"
                aria-controls="navbarNavOwnerDashboard" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavOwnerDashboard">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#db-ringkasan">Ringkasan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#db-properti-saya">Properti Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tambah.php">Tambah Kost</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownOwner" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo $namaPemilik; ?> (Owner)
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

    <main class="container mt-4 mb-5">
        <section id="db-ringkasan" class="scrollspy-section">
            <div class="dashboard-welcome">
                <h3 id="welcomeMessageDashboard">Selamat Datang Kembali, <?php echo $namaPemilik; ?>!</h3>
                <p>Ini adalah dasbor pengelolaan properti kost Anda.</p>
            </div>
            <h4 class="section-title mt-4">Ringkasan Aktivitas Properti Anda</h4>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="summary-card">
                        <div class="icon"><i class="fas fa-home"></i></div>
                        <div class="number" id="totalKostAktifDashboard"><?php echo $totalKostAktif; ?></div>
                        <div class="title">Total Kost Aktif</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="summary-card">
                        <div class="icon"><i class="fas fa-door-open"></i></div>
                        <div class="number" id="totalKamarTersediaDashboard"><?php echo $totalKamarTersedia; ?></div>
                        <div class="title">Total Kamar Tersedia</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="summary-card">
                        <div class="icon"><i class="fas fa-envelope"></i></div>
                        <div class="number" id="pesanBaruDashboard"><?php echo $pesanBaru; ?></div>
                        <div class="title">Pesan/Notifikasi Baru</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="db-properti-saya" class="scrollspy-section kost-list-preview pt-4">
            <h4 class="section-title">Properti Kost Milik Anda</h4>
            <div class="row" id="daftarKostTerbaruContainerDashboard">
                <?php
                if ($resultProperti && mysqli_num_rows($resultProperti) > 0) {
                    while ($properti = mysqli_fetch_assoc($resultProperti)) {
                        $id_kos_plk = htmlspecialchars($properti['id_kos_plk']);
                        $nama_kost = htmlspecialchars($properti['nama']);
                        $tipe_kost = htmlspecialchars($properti['tipe']);
                        $lokasi_kost = htmlspecialchars($properti['lokasi']);
                        $status_tersedia = (int)$properti['tersedia'];
                        $jumlah_kamar = (int)$properti['jumlah'];

                        // --- PENYESUAIAN PATH GAMBAR UNTUK DITAMPILKAN ---
                        $placeholder_img = "https://via.placeholder.com/400x300.png?text=" . urlencode($nama_kost);
                        $url_gambar_utama = $placeholder_img; // Default ke placeholder
                      // Cek dan buat path untuk gambar_url (Foto Utama)
                         if (!empty($properti['gambar_url'])) { // Corrected: Access 'gambar_url' from DB result
                             $path_gambar_utama_db = $properti['gambar_url']; // Corrected: Use 'gambar_url' value
                             // The image path stored in the DB (from tambah.php) is relative to the web root, e.g., "uploads/kost_images/..."
                             // Since owner.php is in 'admin/', we need to go up one directory (../) to reach 'uploads/'.
                             $url_gambar_utama = "../image/fotoutama_683f343bb603b_budi_dejek.jpeg" . htmlspecialchars($path_gambar_utama_db);

                             // Optional: Further check if the physical file exists
                             // This assumes 'uploads' directory is directly in the project root (e.g., C:\xampp\htdocs\UAS\uploads\)
                             if (!file_exists(dirname(dirname(__FILE__)) . 'C:\xampp\htdocs\UAS\image\budi dejek.jpeg' . $path_gambar_utama_db)) {
                                 $url_gambar_utama = "https://via.placeholder.com/400x300.png?text=File+Not+Found"; // Placeholder if file does not exist
                             }
                         } else {
                             // If gambar_url is empty or not set, use the default placeholder
                             $url_gambar_utama = $placeholder_img;
                         }

                        $harga_sewa_kost = isset($properti['harga_sewa']) && $properti['harga_sewa'] !== null ? "Rp " . number_format((float)$properti['harga_sewa'], 0, ',', '.') : "Harga belum diatur";
                        $periode_sewa_kost = !empty($properti['periode_sewa']) ? "/ " . htmlspecialchars($properti['periode_sewa']) : "";

                        echo "<div class=\"col-md-6 col-lg-4\">";
                        echo "    <div class=\"card kost-card-sm mb-4\">";
                        echo "        <img src=\"{$url_gambar_utama}\" alt=\"".htmlspecialchars($nama_kost)."\" class=\"card-img-top\" style=\"height: 180px; object-fit: cover;\" onerror=\"this.onerror=null; this.src='https://via.placeholder.com/400x300.png?text=Gagal+Muat';\">";
                        echo "        <div class=\"card-body\">";
                        echo "            <h5 class=\"kost-name\">{$nama_kost}</h5>";
                        echo "            <p class=\"kost-location font-italic mb-1\" style=\"font-size: 0.85em; color: #666;\"><i class=\"fas fa-map-marker-alt mr-1\"></i>{$lokasi_kost}</p>";
                        echo "            <p class=\"kost-price\">{$harga_sewa_kost} <span class=\"period\">{$periode_sewa_kost}</span></p>";
                        echo "            <div class=\"mb-2\">";
                        echo "                <span class=\"badge badge-primary mr-1\">".ucfirst($tipe_kost)."</span> ";
                        if ($status_tersedia == 1) {
                            echo "            <span class=\"badge badge-success mr-1\">Aktif</span> ";
                        } else {
                            echo "            <span class=\"badge badge-secondary mr-1\">Non-Aktif</span> ";
                        }
                        echo "                <span class=\"badge badge-light\"><i class=\"fas fa-bed mr-1\"></i>{$jumlah_kamar} Kamar</span>";
                        echo "            </div>";
                        echo "            <a href=\"kelola_kost.php?id={$id_kos_plk}\" class=\"btn btn-sm btn-outline-primary btn-block\">Kelola</a>";
                        echo "        </div>";
                        echo "    </div>";
                        echo "</div>";
                    }
                } else {
                    if ($resultProperti && mysqli_num_rows($resultProperti) == 0) {
                         echo "<div class=\"col-12\"><p class=\"text-center text-muted\">Anda belum memiliki properti kost yang terdaftar, atau tidak ada properti yang cocok. Silakan <a href='tambah.php'>tambahkan properti baru</a>.</p></div>";
                    }
                }
                ?>
            </div>
        </section>
    </main>

    <footer class="footer-custom text-center py-4">
        <div class="container">
            <p>&copy; <span id="tahunSekarangDashboard"><?php echo date("Y"); ?></span> MOVER. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../javascript/pemilik.js"></script>
</body>

</html>