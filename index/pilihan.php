<?php
require 'session.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVER - Pilih Kebutuhanmu</title>
    <link rel="stylesheet" href="../css/pilih.css">
    <link rel="stylesheet" href="../css/common.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <header class="header-custom sticky-top ">
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

    <main class="container">
        <h1>Pilih Kebutuhanmu!</h1>
        <p class="subtitle">MOVER siap membantu berbagai kebutuhan kosanmu.</p>
        <div class="options-container">
            <a href="order.php" class="option-card pindahan">
                <div class="option-icon">
                    <img src="https://img.icons8.com/plasticine/100/000000/truck.png" alt="Pindahan Barang Icon">
                </div>
                <h2>Pindahan Barang</h2>
                <p class="option-description">Pindahkan barang kosan dengan aman dan cepat bersama kami.</p>
            </a>
            <a href="bersih.php" class="option-card bersih">
                <div class="option-icon">
                    <img src="https://img.icons8.com/plasticine/100/000000/vacuum-cleaner.png" alt="Bersih-bersih Icon">
                </div>
                <h2>Bersih-bersih</h2>
                <p class="option-description">Layanan kebersihan profesional untuk kamar kos yang nyaman.</p>
            </a>
            <a href="carikost.php" class="option-card carikost">
                <div class="option-icon">
                    <img src="https://img.icons8.com/plasticine/100/000000/home-page.png" alt="Cari Kost Icon">
                </div>
                <h2>Cari Kost</h2>
                <p class="option-description">Temukan kost impianmu dengan mudah melalui platform kami.</p>
            </a>
        </div>
    </main>
</body>

</html>