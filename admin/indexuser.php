<?php 
require 'session.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MOVER - Dasbor Pengguna</title>
    <link rel="stylesheet" href="../css/indexuser.css" />
</head>

<body>
    <header class="navbar">
        <div class="logo">LOGO MOVER</div>
        <nav class="nav-menu">
            <div class="profile-icon">
                <img src="../assets/img/default-profile.png" alt="User Profile" />
            </div>
            <a href="#">About</a>
            <a href="#">Contact</a>
            <a href="logout.php">
                <button class="logout-btn"
                    style="background-color: #dc3545; color: white; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer;">Logout</button>
            </a>
        </nav>
    </header>

    <main class="hero">
        <div class="hero-text">
            <h1>Pindah atau Cari Kost?</h1>
            <p>Pake MOVER aja! Semua jadi lebih mudah.</p>

            <a href="pilihan.php" class="cta-btn" style="text-decoration: none;">Pesan Layanan Sekarang</a>
        </div>
        <div class="hero-image">
            <div class="circle-bg"></div>
            <img src="../image/—Pngtree—realistic 3d model of a_20092787.png" alt="Truk Mover" />
        </div>
    </main>
</body>

</html>