<?php
include 'session.php'; // Memastikan pengguna sudah login

$status = $_GET['status'] ?? 'error'; // 'success' atau 'duplicate' atau 'error'
$order_id_ref = $_GET['order_id'] ?? 'N/A'; //
$order_type = $_GET['order_type'] ?? 'layanan'; //

$page_title = "Ulasan Gagal Terkirim!"; //
$confirmation_message = "Maaf, terjadi kesalahan saat mengirim ulasan Anda. Silakan coba lagi."; //
$icon_class = "fas fa-times-circle"; //
$icon_color = "#dc3545"; // Red

if ($status == 'success') { //
    $page_title = "Review Terkirim!"; //
    $confirmation_message = "Terima kasih telah meluangkan waktu untuk memberikan ulasan Anda. Masukan Anda sangat membantu kami untuk meningkatkan layanan!"; //
    $icon_class = "fas fa-check-circle"; //
    $icon_color = "#28a745"; // Green
} elseif ($status == 'duplicate') { //
    $page_title = "Ulasan Sudah Ada!"; //
    $confirmation_message = "Anda sudah pernah memberikan ulasan untuk order ini. Terima kasih atas feedback Anda sebelumnya!"; //
    $icon_class = "fas fa-info-circle"; //
    $icon_color = "#ffc107"; // Yellow/Orange
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $page_title; ?> | MOVER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../css/rating.css" />
    <link rel="stylesheet" href="../css/common.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <style>
    .confirmation-icon {
        font-size: 4.5rem;
        margin-bottom: 20px;
        line-height: 1;
    }

    .confirmation-container h2 {
        font-size: 2rem;
        color: #367A83;
        font-weight: 700;
    }

    .confirmation-container p {
        font-size: 1.1rem;
        color: #555;
        line-height: 1.6;
        margin-bottom: 25px;
    }

    .confirmation-container .btn {
        background-color: var(--primary-bg-color);
        color: var(--white-text);
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }

    .confirmation-container .btn:hover {
        background-color: var(--button-hover);
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
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

    <main class="main-content d-flex justify-content-center align-items-center flex-grow-1">
        <div class="confirmation-container">
            <div class="confirmation-icon" style="color: <?php echo $icon_color; ?>;">
                <i class="<?php echo $icon_class; ?>"></i>
            </div>
            <h2><?php echo $page_title; ?></h2>
            <p><?php echo $confirmation_message; ?></p>
            <p>Order ID: <strong><?php echo htmlspecialchars($order_id_ref); ?></strong> (Tipe:
                <?php echo htmlspecialchars($order_type); ?>)</p>

            <a href="indexuser.php" class="btn">Kembali ke Beranda</a>
        </div>
    </main>

    <footer class="footer">&copy; <?php echo date("Y"); ?> MOVER. All rights reserved.</footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>