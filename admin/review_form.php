<?php
include 'session.php'; // Memastikan pengguna sudah login
include '../koneksi.php'; // Koneksi ke database

$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'User';

$order_id_ref = $_GET['order_id'] ?? null;
$order_type = $_GET['order_type'] ?? null; 

$message = '';
$error = '';
$order_info_display = 'Order tidak ditemukan atau tidak valid.';
$order_data = null; 

if (empty($order_id_ref) || empty($order_type)) {
    $error = "Parameter order_id atau order_type tidak lengkap.";
} else {
    // Inisialisasi variabel query
    $table = '';
    $id_col = '';
    $item_desc_col = '';
    $additional_join = '';
    $select_cols = '*';

    // Switch-case untuk menangani SETIAP jenis layanan secara terpisah
    switch ($order_type) {
        case 'kost':
            $table = 'order_sewa_kost';
            $id_col = 'id_order_sewa';
            $additional_join = ' JOIN pengelolaan_kost pk ON id_kos_plk = pk.id_kos_plk';
            $select_cols = '*, pk.nama as nama_item';
            $item_desc_col = 'nama_item'; // Sudah benar, karena menggunakan alias
            break;
        
        case 'pindahan':
            $table = 'order_layanan_pindahan_barang_kos';
            $id_col = 'id_order_pk';
            // PERBAIKAN: Menghapus prefix 'o.'
            $item_desc_col = 'jenis_barang';
            break;

        case 'bersih':
            $table = 'order_layanan_bersih_kos';
            $id_col = 'id_order_bk';
            // PERBAIKAN: Menghapus prefix 'o.'
            $item_desc_col = 'jenis_paket_bk';
            break;

        default:
            $error = "Tipe order tidak valid.";
    }

    if (empty($error) && !empty($table)) {
        $stmt_order_check = $conn->prepare("SELECT * FROM {$table} {$additional_join} WHERE {$id_col} = ? AND id_user = ?");
        
        if ($stmt_order_check) {
            $stmt_order_check->bind_param("ss", $order_id_ref, $user_id);
            $stmt_order_check->execute();
            $result_order_check = $stmt_order_check->get_result();

            if ($result_order_check->num_rows > 0) {
                $order_data = $result_order_check->fetch_assoc();
                
                // PERBAIKAN: Mengakses kolom deskripsi menggunakan variabel yang sudah diperbaiki
                if (isset($order_data[$item_desc_col])) {
                    $order_info_display = "Order ID: #" . htmlspecialchars($order_id_ref) . " | Layanan: " . htmlspecialchars($order_data[$item_desc_col]);
                } else {
                    $order_info_display = "Order ID: #" . htmlspecialchars($order_id_ref);
                }

                // Cek ulasan duplikat (logika ini berlaku untuk semua layanan)
                $stmt_check_ulasan = $conn->prepare("SELECT id_ulasan FROM ulasan_layanan WHERE id_user = ? AND id_order = ? AND jenis_layanan = ?");
                if ($stmt_check_ulasan) {
                    $stmt_check_ulasan->bind_param("sss", $user_id, $order_id_ref, $order_type);
                    $stmt_check_ulasan->execute();
                    if ($stmt_check_ulasan->get_result()->num_rows > 0) {
                        header("Location: submit_review.php?status=duplicate&order_id=" . urlencode($order_id_ref) . "&order_type=" . urlencode($order_type));
                        exit();
                    }
                    $stmt_check_ulasan->close();
                }

            } else {
                $error = "Order tidak ditemukan atau bukan milik Anda.";
            }
            $stmt_order_check->close();
        } else {
            $error = "Gagal menyiapkan query order: " . $conn->error;
        }
    }
}

// Proses form submission (logika ini umum dan tidak perlu diubah)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $rating = $_POST['rating'] ?? 0;
    $comment = $_POST['comment'] ?? '';
    $final_order_id = $_POST['order_id_ref'] ?? null;
    $final_order_type = $_POST['order_type'] ?? null;

    if ($rating < 1 || $rating > 5 || empty($final_order_id) || empty($final_order_type)) {
        $error = "Rating atau informasi order tidak valid.";
    } else {
        $stmt_insert_ulasan = $conn->prepare("INSERT INTO ulasan_layanan (id_user, id_order, jenis_layanan, rating, komentar) VALUES (?, ?, ?, ?, ?)");
        if ($stmt_insert_ulasan) {
            $stmt_insert_ulasan->bind_param("sssis", $user_id, $final_order_id, $final_order_type, $rating, $comment);
            if ($stmt_insert_ulasan->execute()) {
                header("Location: submit_review.php?status=success&order_id=" . urlencode($final_order_id) . "&order_type=" . urlencode($final_order_type));
                exit();
            } else {
                $error = "Gagal menyimpan ulasan: " . $stmt_insert_ulasan->error;
            }
            $stmt_insert_ulasan->close();
        } else {
            $error = "Gagal menyiapkan statement ulasan: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Berikan Ulasanmu | MOVER</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css" />
    <link rel="stylesheet" href="../css/rating.css" />
</head>

<body>
    <header class="header-custom sticky-top">
        <nav class="container navbar navbar-expand-lg navbar-dark">
            <a class="navbar-brand" href="indexuser.php"><img src="../image/logo mover.png" alt="MOVER Logo"
                    style="height: 70px;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span
                    class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="indexuser.php">Beranda</a></li>
                    <li class="nav-item"><a href="pilihan.php" class="nav-link order-btn-nav">Layanan</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="username-display"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="review-container">
            <h2>Bagaimana Pengalamanmu di Mover?</h2>
            <p class="order-info">Kami Menghargai Feedback dari kamu!</p>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (empty($order_data)): ?>
            <div class="alert alert-warning">Order tidak valid atau tidak ditemukan. <a href="indexuser.php">Kembali ke
                    beranda</a>.</div>
            <?php else: ?>
            <p class="order-info"><strong>Detail Order:</strong> <?php echo $order_info_display; ?></p>
            <form
                action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>?order_id=<?php echo urlencode($order_id_ref);?>&order_type=<?php echo urlencode($order_type);?>"
                method="POST">
                <input type="hidden" name="submit_review" value="1" />
                <input type="hidden" name="order_id_ref" value="<?php echo htmlspecialchars($order_id_ref); ?>" />
                <input type="hidden" name="order_type" value="<?php echo htmlspecialchars($order_type); ?>" />

                <div class="form-group">
                    <label for="rating">Rating Anda:</label>
                    <div class="rating-stars">
                        <input type="radio" id="star5" name="rating" value="5" required /><label for="star5"
                            title="5 stars">&#9733;</label>
                        <input type="radio" id="star4" name="rating" value="4" /><label for="star4"
                            title="4 stars">&#9733;</label>
                        <input type="radio" id="star3" name="rating" value="3" /><label for="star3"
                            title="3 stars">&#9733;</label>
                        <input type="radio" id="star2" name="rating" value="2" /><label for="star2"
                            title="2 stars">&#9733;</label>
                        <input type="radio" id="star1" name="rating" value="1" /><label for="star1"
                            title="1 star">&#9733;</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comment">Komentar Anda:</label>
                    <textarea id="comment" name="comment" rows="5" placeholder="Bagikan pendapatmu tentang layanan..."
                        maxlength="500"></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">Kirim Ulasan</button>
            </form>
            <?php endif; ?>
        </div>
    </main>
    <footer class="footer">&copy; <?php echo date("Y"); ?> MOVER. All rights reserved.</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>