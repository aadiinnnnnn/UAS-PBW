<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MOVER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/indexuser.css">

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
                        <a href="pilihan.php" class="nav-link">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link order-btn-nav">login</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="hero">
        <div class="hero-text">
            <h1>Pindah berkala<br>kosan ke kosan</h1>
            <p>Pake MOVER aja!</p>
            <a href="login.php"><button class="cta-btn">Order Sekarang</button></a>
        </div>
        <div class="hero-image">
            <div class="circle-bg"></div>
            <img src="../image/—Pngtree—realistic 3d model of a_20092787.png" alt="Truk Mover" />
        </div>
    </main>
</body>

</html>