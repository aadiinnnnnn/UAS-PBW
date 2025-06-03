<?php
session_start();
require '../koneksi.php'; // Sesuaikan path jika perlu

// Aktifkan pelaporan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Pengecekan Sesi dan Peran
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php'); 
    exit;
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Akses Ditolak</title>";
    echo "<link href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<style>body { display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f8f9fa; }</style>";
    echo "</head><body><div class='container text-center'>";
    echo "<div class='alert alert-danger' role='alert'><h1>Akses Ditolak!</h1><p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>";
    echo "<a href='indexuser.php' class='btn btn-primary mt-3'>Kembali ke Dasbor Pengguna</a>";
    echo " <a href='logout.php' class='btn btn-secondary mt-3'>Logout</a>";
    echo "</div></div></body></html>";
    exit;
}

$idOwner = $_SESSION['user_id']; 
$namaOwner = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Pemilik';
$message = '';
$error_message = '';

// Variabel untuk menyimpan nilai form jika terjadi error validasi (sticky form)
$form_values = [
    'namaKost' => '', 'tipeKost' => '', 'deskripsiKost' => '', 
    'alamatKost' => '', 'kotaKost' => '', 'provinsiKost' => '', 'linkGoogleMaps' => '', 
    'hargaBulanan' => '', 'jumlahKamar' => '', 'infoTambahanHarga' => '', 
    'fasilitasUmum' => [], 'fasilitasKamar' => [], 
    'namaKontak' => '', 
    'nomorTeleponKontak' => '',
    // Path gambar lama (berguna jika ini form edit, untuk tambah baru akan null)
    'gambar_url_lama' => null, 
    'gambar_dalam_kamar_url_lama' => null, 
    'gambar_kamar_mandi_url_lama' => null
];


// 2. Pemrosesan Formulir Penambahan Kost
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitKost'])) { 
    
    // Isi $form_values dengan data POST untuk sticky form
    foreach ($form_values as $key => $value) {
        if (isset($_POST[$key])) {
            if (is_array($_POST[$key])) {
                $form_values[$key] = $_POST[$key]; 
            } else {
                $form_values[$key] = trim($_POST[$key]);
            }
        }
    }

    $namaKost = $form_values['namaKost'];
    $tipeKost = $form_values['tipeKost'];
    $deskripsiKost = $form_values['deskripsiKost']; 
    
    $alamatLengkap = $form_values['alamatKost'];
    $kotaKost = $form_values['kotaKost']; 
    $provinsiKost = $form_values['provinsiKost']; 
    $lokasiKostDb = $alamatLengkap; 
    if (!empty($kotaKost)) $lokasiKostDb .= ", " . htmlspecialchars($kotaKost);
    if (!empty($provinsiKost)) $lokasiKostDb .= ", " . htmlspecialchars($provinsiKost);
    
    // $linkGoogleMaps = $form_values['linkGoogleMaps']; // Anda perlu kolom `link_gmaps` di DB

    $hargaBulananInput = $form_values['hargaBulanan'];
    $hargaSewaDb = null;
    if ($hargaBulananInput !== '' && is_numeric($hargaBulananInput) && $hargaBulananInput >= 0) {
        $hargaSewaDb = (float)$hargaBulananInput;
    }
    
    $jumlahKamarInput = $form_values['jumlahKamar'];
    $jumlahKamar = null;
    if ( $jumlahKamarInput !== '' && is_numeric($jumlahKamarInput) && $jumlahKamarInput >= 0) {
         $jumlahKamar = (int)$jumlahKamarInput;
    }

    $periodeSewaDb = "Bulan"; 
    // $infoTambahanHarga = $form_values['infoTambahanHarga']; // Bisa masuk ke deskripsi atau kolom baru

    $fasilitasUmum = $form_values['fasilitasUmum'];
    $fasilitasKamar = $form_values['fasilitasKamar'];
    $semuaFasilitas = array_merge($fasilitasUmum, $fasilitasKamar);
    $fasilitasDb = !empty($semuaFasilitas) ? implode(', ', array_map('htmlspecialchars', $semuaFasilitas)) : NULL;

    $nomorTeleponKontak = $form_values['nomorTeleponKontak']; 
    $namaKontakPerson = $form_values['namaKontak']; 

    $tersedia = 1; 

    // Validasi Sederhana di Sisi Server
    if (empty($namaKost) || empty($tipeKost) || empty($alamatLengkap) || $hargaSewaDb === null || $jumlahKamar === null || empty($nomorTeleponKontak) || empty($namaKontakPerson)) {
        $error_message = "Nama Kost, Tipe, Alamat, Harga per Bulan, Jumlah Kamar, Nama Kontak, dan Nomor Telepon Kontak wajib diisi.";
    }
    if ($hargaSewaDb !== null && $hargaSewaDb < 0) {
         $error_message = "Harga tidak boleh negatif.";
    }
    if ($jumlahKamar !== null && $jumlahKamar < 0) {
        $error_message = "Jumlah kamar tidak boleh negatif.";
    }
    if (!empty($nomorTeleponKontak) && !preg_match('/^[0-9]{10,15}$/', $nomorTeleponKontak)) {
        $error_message = "Format Nomor Telepon Kontak tidak valid (10-15 digit angka).";
    }

    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    $uploadDir = "../uploads/kost_images/"; 

    // Fungsi helper untuk unggah file
    function unggahFile($fileKey, $prefix, &$errorMessage, $existingPath = null) {
        global $uploadDir, $allowTypes;
        $fileUrlDb = $existingPath; 

        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK && !empty($_FILES[$fileKey]['name'])) {
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0775, true)) {
                    $errorMessage .= " Gagal membuat direktori unggahan untuk {$fileKey}. Pastikan server memiliki izin tulis ke '{$uploadDir}'.";
                    return $existingPath; 
                }
            }
            if (!empty($errorMessage) && strpos($errorMessage, "Gagal membuat direktori unggahan untuk {$fileKey}") !== false) return $existingPath;


            $fileOriginalName = basename($_FILES[$fileKey]["name"]);
            $fileExtension = strtolower(pathinfo($fileOriginalName, PATHINFO_EXTENSION));
            $fileName = uniqid($prefix) . "_" . preg_replace("/[^a-zA-Z0-9_.-]/", "_", pathinfo($fileOriginalName, PATHINFO_FILENAME)) . "." . $fileExtension;
            $targetFilePath = $uploadDir . $fileName;

            if (in_array($fileExtension, $allowTypes)) {
                if ($_FILES[$fileKey]["size"] <= 2097152) { // Maks 2MB
                    if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $targetFilePath)) {
                        $fileUrlDb = "uploads/kost_images/" . $fileName; // Path relatif dari root web Anda
                    } else {
                        $errorMessage .= " Gagal mengunggah {$fileKey}. Kode Error PHP: " . $_FILES[$fileKey]['error'];
                    }
                } else {
                    $errorMessage .= " Ukuran {$fileKey} maks 2MB.";
                }
            } else {
                $errorMessage .= " Format {$fileKey} hanya JPG, JPEG, PNG, GIF.";
            }
        } elseif (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] != UPLOAD_ERR_NO_FILE && $_FILES[$fileKey]['error'] != UPLOAD_ERR_OK) {
            $errorMessage .= " Terjadi kesalahan saat mengunggah {$fileKey}. Kode Error PHP: " . $_FILES[$fileKey]['error'];
        }
        return $fileUrlDb;
    }

    // Ambil path gambar lama dari $form_values (berguna untuk form edit, untuk tambah baru akan NULL)
    $gambarUrlDb = $form_values['gambar_url_lama']; 
    $gambarDalamKamarUrlDb = $form_values['gambar_dalam_kamar_url_lama'];
    $gambarKamarMandiUrlDb = $form_values['gambar_kamar_mandi_url_lama'];

    if (empty($error_message)) { 
        $gambarUrlDb = unggahFile('fotoUtama', 'fotoutama_', $error_message, $form_values['gambar_url_lama']);
        $gambarDalamKamarUrlDb = unggahFile('fotoDalamKamar', 'dalamkamar_', $error_message, $form_values['gambar_dalam_kamar_url_lama']);
        $gambarKamarMandiUrlDb = unggahFile('fotoKamarMandi', 'kamarmandi_', $error_message, $form_values['gambar_kamar_mandi_url_lama']);
    }

    if (empty($error_message)) {
        $idKosPlk = "KOS" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 12));

        // Kolom di tabel pengelolaan_kost: id_kos_plk, id_user, nama, lokasi, no_telp, nama_kontak_person, fasilitas, 
        // gambar_url, gambar_dalam_kamar_url, gambar_kamar_mandi_url, 
        // deskripsi, tipe, rating (di-set NULL oleh DB), jumlah, harga_sewa, periode_sewa, tersedia
        //
        $sql = "INSERT INTO pengelolaan_kost 
                    (id_kos_plk, id_user, nama, lokasi, no_telp, nama_kontak_person, fasilitas, 
                     gambar_url, gambar_dalam_kamar_url, gambar_kamar_mandi_url, 
                     deskripsi, tipe, jumlah, harga_sewa, periode_sewa, tersedia, rating) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)"; 
                // Rating diisi NULL secara eksplisit (16 '?' sebelumnya)

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // bind_param sekarang memiliki 16 tipe data untuk 16 placeholder '?'
            $stmt->bind_param(
                "ssssssssssssidsi", // 12 's', 1 'i' (jumlah), 1 'd' (harga_sewa), 1 's' (periode_sewa), 1 'i' (tersedia)
                $idKosPlk,
                $idOwner,
                $namaKost,
                $lokasiKostDb,
                $nomorTeleponKontak,
                $namaKontakPerson,
                $fasilitasDb,
                $gambarUrlDb,
                $gambarDalamKamarUrlDb,
                $gambarKamarMandiUrlDb,
                $deskripsiKost,
                $tipeKost,
                $jumlahKamar,       
                $hargaSewaDb,       
                $periodeSewaDb,     
                $tersedia           
                // Rating tidak di-bind karena di SQL sudah diisi NULL secara eksplisit
            );

            if ($stmt->execute()) {
                $message = "Properti kost baru '" . htmlspecialchars($namaKost) . "' berhasil ditambahkan! ID Kost: " . $idKosPlk;
                // Kosongkan form_values setelah sukses
                foreach ($form_values as $key => $value) {
                    if (is_array($form_values[$key])) { $form_values[$key] = []; } else { $form_values[$key] = '';}
                }
            } else {
                $error_message = "Gagal menambahkan properti: " . $stmt->error . " (Query: Gagal Eksekusi)";
            }
            $stmt->close();
        } else {
            $error_message = "Gagal menyiapkan statement database: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kost Baru - MOVER</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/owner.css">
    <link rel="stylesheet" href="../css/pemilik.css">
</head>

<body>

    <header class="header-custom sticky-top">
        <nav class="container navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="owner.php">LOGO MOVER</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavOwnerTambahKost"
                aria-controls="navbarNavOwnerTambahKost" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavOwnerTambahKost">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="owner.php">Dashboard</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="pemilik.php">Tambah Kost <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="owner.php#db-properti-saya">Properti Saya</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownOwner" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo $namaOwner; ?>
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
        <h2 class="owner-page-title text-center">Tambahkan Properti Kost Baru Anda</h2>

        <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
        <?php endif; ?>

        <form id="formTambahKostPage" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
            enctype="multipart/form-data">
            {/* Informasi Umum */}
            <div class="form-section-card">
                <h4><i class="fas fa-info-circle mr-2"></i>Informasi Umum Kost</h4>
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="namaKost">Nama Kost <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="namaKost" name="namaKost"
                            placeholder="Contoh: Kost Sejahtera Sentosa" required
                            value="<?php echo htmlspecialchars($form_values['namaKost']); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="tipeKost">Tipe Kost <span class="text-danger">*</span></label>
                        <select id="tipeKost" name="tipeKost" class="custom-select" required>
                            <option value="" disabled <?php echo empty($form_values['tipeKost']) ? 'selected' : '';?>>
                                Pilih tipe...</option>
                            <option value="Putra"
                                <?php echo ($form_values['tipeKost'] == 'Putra') ? 'selected' : ''; ?>>Putra</option>
                            <option value="Putri"
                                <?php echo ($form_values['tipeKost'] == 'Putri') ? 'selected' : ''; ?>>Putri</option>
                            <option value="Campur"
                                <?php echo ($form_values['tipeKost'] == 'Campur') ? 'selected' : ''; ?>>Campur</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="deskripsiKost">Deskripsi Kost</label>
                    <textarea class="form-control" id="deskripsiKost" name="deskripsiKost" rows="4"
                        placeholder="Jelaskan tentang kost Anda, keunggulan, suasana, dll."><?php echo htmlspecialchars($form_values['deskripsiKost']); ?></textarea>
                </div>
            </div>

            {/* Detail Lokasi */}
            <div class="form-section-card">
                <h4><i class="fas fa-map-marked-alt mr-2"></i>Detail Lokasi</h4>
                <div class="form-group">
                    <label for="alamatKost">Alamat Lengkap Kost <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="alamatKost" name="alamatKost"
                        placeholder="Jl. Kebenaran No. 1, RT 01/RW 02..." required
                        value="<?php echo htmlspecialchars($form_values['alamatKost']); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="kotaKost">Kota/Kabupaten</label>
                        <input type="text" class="form-control" id="kotaKost" name="kotaKost"
                            placeholder="Contoh: Jakarta Selatan"
                            value="<?php echo htmlspecialchars($form_values['kotaKost']); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="provinsiKost">Provinsi</label>
                        <input type="text" class="form-control" id="provinsiKost" name="provinsiKost"
                            placeholder="Contoh: DKI Jakarta"
                            value="<?php echo htmlspecialchars($form_values['provinsiKost']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="linkGoogleMaps">Link Google Maps (Opsional)</label>
                    <input type="url" class="form-control" id="linkGoogleMaps" name="linkGoogleMaps"
                        placeholder="https://maps.app.goo.gl/contoh"
                        value="<?php echo htmlspecialchars($form_values['linkGoogleMaps']); ?>">
                </div>
            </div>

            {/* Harga & Ketersediaan */}
            <div class="form-section-card">
                <h4><i class="fas fa-dollar-sign mr-2"></i>Harga & Ketersediaan</h4>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="hargaBulanan">Harga per Bulan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="hargaBulanan" name="hargaBulanan"
                            placeholder="1500000" min="0" step="10000" required
                            value="<?php echo htmlspecialchars($form_values['hargaBulanan']); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="jumlahKamar">Jumlah Total Kamar <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="jumlahKamar" name="jumlahKamar" placeholder="5"
                            min="0" step="1" required
                            value="<?php echo htmlspecialchars($form_values['jumlahKamar']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="infoTambahanHarga">Informasi Tambahan Harga (Opsional)</label>
                    <input type="text" class="form-control" id="infoTambahanHarga" name="infoTambahanHarga"
                        placeholder="Contoh: Deposit 500rb, sudah termasuk listrik & air"
                        value="<?php echo htmlspecialchars($form_values['infoTambahanHarga']); ?>">
                </div>
            </div>

            <div class="form-section-card">
                <h4><i class="fas fa-concierge-bell mr-2"></i>Fasilitas</h4>
                <label>Fasilitas Umum (Pilih yang sesuai):</label>
                <div class="row checkbox-group">
                    <?php 
                        $fasilitasUmumList = ["WiFi", "Dapur Bersama", "Parkir Motor", "Parkir Mobil", "Ruang Tamu", "CCTV", "Penjaga Keamanan", "Akses 24 Jam", "Mesin Cuci Bersama", "Area Jemur"];
                        $selectedFasilitasUmum = $form_values['fasilitasUmum'];
                        foreach ($fasilitasUmumList as $fu) {
                            $fuId = preg_replace('/\s+/', '', $fu);
                            $checked = in_array($fu, $selectedFasilitasUmum) ? 'checked' : '';
                            echo "<div class=\"col-lg-3 col-md-4 col-sm-6 col-6\"><div class=\"form-check\"><input class=\"form-check-input\" type=\"checkbox\" value=\"".htmlspecialchars($fu)."\" id=\"fasUmum{$fuId}\" name=\"fasilitasUmum[]\" {$checked}><label class=\"form-check-label\" for=\"fasUmum{$fuId}\">".htmlspecialchars($fu)."</label></div></div>";
                        }
                    ?>
                </div>
                <hr>
                <label class="mt-3">Fasilitas Kamar (Pilih yang sesuai):</label>
                <div class="row checkbox-group">
                    <?php 
                        $fasilitasKamarList = ["AC", "Kamar Mandi Dalam", "Lemari Pakaian", "Meja Belajar", "Jendela", "Kasur", "TV Kabel", "Kulkas Mini", "Air Panas", "Ventilasi"];
                        $selectedFasilitasKamar = $form_values['fasilitasKamar'];
                        foreach ($fasilitasKamarList as $fk) {
                            $fkId = preg_replace('/\s+/', '', $fk);
                            $checked = in_array($fk, $selectedFasilitasKamar) ? 'checked' : '';
                            echo "<div class=\"col-lg-3 col-md-4 col-sm-6 col-6\"><div class=\"form-check\"><input class=\"form-check-input\" type=\"checkbox\" value=\"".htmlspecialchars($fk)."\" id=\"fasKamar{$fkId}\" name=\"fasilitasKamar[]\" {$checked}><label class=\"form-check-label\" for=\"fasKamar{$fkId}\">".htmlspecialchars($fk)."</label></div></div>";
                        }
                    ?>
                </div>
            </div>

            <div class="form-section-card">
                <h4><i class="fas fa-images mr-2"></i>Unggah Foto Kost</h4>
                <div class="form-group">
                    <label for="fotoUtama">Foto Utama (Tampak Depan)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="fotoUtama" name="fotoUtama"
                            accept="image/jpeg, image/png, image/gif">
                        <label class="custom-file-label" for="fotoUtama" data-browse="Pilih File">Pilih foto...</label>
                    </div>
                    <small class="form-text text-muted">Ukuran maksimal 2MB. Format: JPG, PNG, GIF.</small>
                    <div id="previewFotoUtama" class="mt-2" style="max-height: 200px; overflow: hidden;"></div>
                </div>
                <hr>
                <div class="form-group">
                    <label for="fotoDalamKamar">Foto Dalam Kamar (Opsional)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="fotoDalamKamar" name="fotoDalamKamar"
                            accept="image/jpeg, image/png, image/gif">
                        <label class="custom-file-label" for="fotoDalamKamar" data-browse="Pilih File">Pilih
                            foto...</label>
                    </div>
                    <small class="form-text text-muted">Ukuran maksimal 2MB.</small>
                    <div id="previewFotoDalamKamar" class="mt-2" style="max-height: 200px; overflow: hidden;"></div>
                </div>
                <hr>
                <div class="form-group">
                    <label for="fotoKamarMandi">Foto Kamar Mandi (Opsional)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="fotoKamarMandi" name="fotoKamarMandi"
                            accept="image/jpeg, image/png, image/gif">
                        <label class="custom-file-label" for="fotoKamarMandi" data-browse="Pilih File">Pilih
                            foto...</label>
                    </div>
                    <small class="form-text text-muted">Ukuran maksimal 2MB.</small>
                    <div id="previewFotoKamarMandi" class="mt-2" style="max-height: 200px; overflow: hidden;"></div>
                </div>
            </div>

            <div class="form-section-card">
                <h4><i class="fas fa-phone-alt mr-2"></i>Kontak yang Dapat Dihubungi</h4>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="namaKontak">Nama Pemilik/Penjaga <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="namaKontak" name="namaKontak"
                            placeholder="Nama yang akan dihubungi" required
                            value="<?php echo htmlspecialchars($form_values['namaKontak'] ?: $namaOwner); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="nomorTeleponKontak">Nomor Telepon/WA Aktif <span
                                class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="nomorTeleponKontak" name="nomorTeleponKontak"
                            placeholder="08xxxxxxxxxx" required pattern="[0-9]{10,15}"
                            title="Masukkan 10-15 digit angka"
                            value="<?php echo htmlspecialchars($form_values['nomorTeleponKontak']); ?>">
                    </div>
                </div>
            </div>

            <div class="text-right mt-4 mb-5">
                <button type="submit" name="submitKost" class="btn btn-submit-kost"><i
                        class="fas fa-paper-plane mr-2"></i>Publikasikan Kost</button>
            </div>
        </form>
    </main>

    <footer class="footer-custom text-center py-4">
        <div class="container">
            <p>© <span id="tahunSekarangTambahKost"><?php echo date("Y"); ?></span> MOVER. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../javascript/addkost.js"></script>
</body>

</html>