<?php
require 'session.php';

// Ambil detail pesanan dari session
$orderDetails = $_SESSION['latestKostOrderDetails'] ?? null;

// Jika tidak ada detail, arahkan ke halaman utama untuk mencegah error
if (!$orderDetails) {
    header("Location: indexuser.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Kost Berhasil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/order_sukses.css">
</head>

<body>
    <header class="header-custom sticky-top">
        <nav class="container navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="indexuser.php"><img src="../image/logo mover.png" alt="MOVER Logo"
                    style="height: 70px;"></a>
        </nav>
    </header>

    <div class="container my-5">
        <div class="card success-card shadow">
            <div class="card-body text-center">
                <div class="success-icon mb-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="card-title">Pemesanan Berhasil!</h1>
                <p class="lead">Terima kasih telah melakukan pemesanan. Pesanan Anda dengan nomor
                    <strong>#<?php echo htmlspecialchars($orderDetails['orderId']); ?></strong> sedang menunggu
                    konfirmasi dari pemilik kost.</p>

                <div class="order-details-summary text-start mt-4">
                    <h5 class="mb-3">Rincian Pesanan</h5>
                    <p><strong>Nama Kost:</strong>
                        <span><?php echo htmlspecialchars($orderDetails['kostName']); ?></span></p>
                    <p><strong>Tanggal Masuk:</strong>
                        <span><?php echo date('d F Y', strtotime(htmlspecialchars($orderDetails['checkInDate']))); ?></span>
                    </p>
                    <p><strong>Durasi:</strong>
                        <span><?php echo htmlspecialchars($orderDetails['durationText']); ?></span></p>

                    <hr>
                    <p><strong>Subtotal:</strong> <span>Rp
                            <?php echo number_format($orderDetails['subtotal'], 0, ',', '.'); ?></span></p>
                    <?php if ($orderDetails['diskonPersen'] > 0): ?>
                    <p class="text-danger"><strong>Diskon
                            (<?php echo htmlspecialchars($orderDetails['diskonPersen']); ?>%):</strong> <span>- Rp
                            <?php echo number_format($orderDetails['nilaiDiskon'], 0, ',', '.'); ?></span></p>
                    <?php endif; ?>
                    <hr>
                    <p class="fw-bold fs-5"><strong>Total Pembayaran:</strong> <span class="fw-bold fs-5">Rp
                            <?php echo number_format($orderDetails['totalPrice'], 0, ',', '.'); ?></span></p>
                    <p><strong>Metode Pembayaran:</strong>
                        <span><?php echo htmlspecialchars($orderDetails['paymentMethod']); ?></span></p>
                </div>

                <p class="mt-4">Anda akan dihubungi oleh pemilik kost untuk proses selanjutnya. Harap simpan detail
                    pesanan Anda.</p>

                <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
                    <a href="indexuser.php" class="btn btn-primary">Kembali ke Beranda</a>
                    <a href="review_form.php?order_id=<?php echo htmlspecialchars($orderDetails['orderId']); ?>&order_type=kost"
                        class="btn btn-info text-white">
                        <i class="fas fa-star me-1"></i> Berikan Ulasan
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
// Hapus detail pesanan dari session setelah ditampilkan
unset($_SESSION['latestKostOrderDetails']);
?>