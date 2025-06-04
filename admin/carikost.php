<?php
require 'session.php'; // Memastikan pengguna sudah login
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Kost - MOVER</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/carikost.css">
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
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a href="order.php" class="nav-link order-btn-nav">Order Pindahan</a>
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

    <section class="hero-section-search">
        <div class="container">
            <h1>Temukan Kost Impianmu</h1>
            <p>Cari dan temukan kost idaman dengan mudah di berbagai kota.</p>
        </div>
    </section>

    <div class="container search-filter-container">
        <div class="search-bar-wrapper">
            <form id="searchForm" class="row align-items-end">
                <div class="form-group col-lg-4 col-md-12">
                    <label for="lokasiKost">Lokasi</label>
                    <input type="text" class="form-control form-control-lg" name="lokasi" id="lokasiKost"
                        placeholder="Masukkan nama lokasi/area/alamat">
                </div>
                <div class="form-group col-lg-3 col-md-6">
                    <label for="tipeKost">Tipe Kost</label>
                    <select id="tipeKost" name="tipe" class="custom-select custom-select-lg">
                        <option selected value="">Semua Tipe</option>
                        <option value="Putra">Putra</option>
                        <option value="Putri">Putri</option>
                        <option value="Campur">Campur</option>
                    </select>
                </div>
                <div class="form-group col-lg-3 col-md-6">
                    <label for="hargaKost">Harga Maks (Rp)</label>
                    <input type="number" class="form-control form-control-lg" name="harga_maks" id="hargaKost"
                        placeholder="Contoh: 1500000">
                </div>
                <div class="form-group col-lg-2 col-md-12">
                    <button type="submit" class="btn btn-search-kost btn-lg btn-block"><i class="fas fa-search"></i>
                        Cari</button>
                </div>
            </form>
        </div>

        <div class="advanced-filters mt-3 text-center">
            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseFilters"
                aria-expanded="false" aria-controls="collapseFilters">
                <i class="fas fa-sliders-h"></i> Filter Lainnya
            </button>
            <div class="collapse" id="collapseFilters">
                <div class="card card-body mt-2">
                    <div class="row">
                        <div class="col-md-4">
                            <h5>Fasilitas Populer</h5>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="fasilitas[]"
                                    value="AC" id="fasilitasAC"><label class="form-check-label"
                                    for="fasilitasAC">AC</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="fasilitas[]"
                                    value="WiFi" id="fasilitasWiFi"><label class="form-check-label"
                                    for="fasilitasWiFi">WiFi</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="fasilitas[]"
                                    value="Kamar Mandi Dalam" id="fasilitasKM"><label class="form-check-label"
                                    for="fasilitasKM">Kamar Mandi Dalam</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="fasilitas[]"
                                    value="Parkir Motor" id="fasilitasParkirMotor"><label class="form-check-label"
                                    for="fasilitasParkirMotor">Parkir Motor</label></div>
                        </div>
                        <div class="col-md-4">
                            <h5>Durasi Sewa</h5>
                            <select id="durasiSewa" name="durasi_sewa" class="custom-select">
                                <option selected value="">Semua Durasi</option>
                                <option value="Bulan">Bulanan</option>
                                <option value="Tahun">Tahunan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <h5>Urutkan Berdasarkan</h5>
                            <select id="urutkan" name="urutkan" class="custom-select">
                                <option selected value="relevansi">Relevansi</option>
                                <option value="harga_terendah">Harga Terendah</option>
                                <option value="harga_tertinggi">Harga Tertinggi</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <main class="container mt-4 mb-5">
        <div class="row" id="hasilPencarianKost">
            <div class="col-12 text-center" id="loadingIndicator">
            </div>
        </div>
    </main>

    <footer class="footer-custom text-center py-4">
        <div class="container">
            <p>&copy; <span id="tahunSekarang"><?php echo date("Y"); ?></span> MOVER. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../javascript/carikost.js"></script>
</body>

</html>