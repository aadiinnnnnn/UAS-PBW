<?php 
require 'session.php'; 
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MOVER - Pengguna</title>
    <link rel="stylesheet" href="../css/indexuser.css" />

</head>

<body>
    <header class="navbar">
        <div class="logo">LOGO</div>
        <nav class="nav-menu">
            <div class="profile-icon">
                <img src="/assets/img/red-truck.png" alt="User Profile" />
            </div>
            <a href="#">About</a>
            <a href="#">Contact</a>
            <a href="order.php">
                <button class="order-btn">Order <span class="arrow">▶</span></button>
            </a>
            <a href="logout.php">
                <button class="logout-btn">Logout</button>
            </a>
        </nav>

    </header>

    <main class="hero">
        <div class="hero-text">
            <h1>Pindah berkala<br>kosan ke kosan</h1>
            <p>Pake MOVER aja!</p>
            <button class="cta-btn">Order Sekarang</button>
        </div>
        <div class="hero-image">
            <div class="circle-bg"></div>
            <img src="/—Pngtree—realistic 3d model of a_20092787.png" alt="Truk Mover" />
        </div>
    </main>
</body>

</html>