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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <header class="navbar">
        <div class="logo">MOVER</div>
        <nav class="nav-menu">
            <a href="indexuser.php">Beranda</a>
            <a href="#">About</a>
            <a href="#">Contact</a>
            <div class="profile-dropdown">
                <button class="profile-btn">
                    <img src="../assets/img/default-profile.png" alt="Profil">
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <i class="fas fa-caret-down"></i>
                </button>
                <div class="profile-dropdown-content">
                    <a href="logout.php">Logout</a>
                </div>
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