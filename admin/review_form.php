<?php
include 'session.php'; // Memastikan pengguna sudah login
include '../koneksi.php'; // Koneksi ke database

$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'User';

$order_id_ref = $_GET['order_id'] ?? null;
$order_type = $_GET['order_type'] ?? null; // 'kost', 'pindahan', 'bersih'

$message = '';
$error = '';
$order_info_display = 'Order tidak ditemukan atau tidak valid.';

// Validasi dasar parameter URL
if (empty($order_id_ref) || empty($order_type)) {
    $error = "Parameter order_id atau order_type tidak lengkap.";
} else {
    // Ambil informasi order untuk ditampilkan kepada user
    $table = '';
    $id_col = ''; // Nama kolom ID primer di tabel order
    $order_date_col = '';
    $total_price_col = '';
    $item_desc_col = ''; // Kolom untuk deskripsi item/layanan

    switch ($order_type) {
        case 'kost':
            $table = 'order_sewa_kost';
            $id_col = 'id_order_sewa'; // <<--- PERBAIKAN: Berdasarkan mover (4).sql
            $order_date_col = 'tanggal_check_in';
            $total_price_col = 'total_harga';
            $item_desc_col = 'durasi_sewa_pilihan'; // atau 'nama_kost'
            break;
        case 'pindahan':
            $table = 'order_layanan_pindahan_barang_kos';
            $id_col = 'id_order_pk';
            $order_date_col = 'tanggal_datang_pk';
            $total_price_col = 'total_harga_pk';
            $item_desc_col = 'jenis_barang';
            break;
        case 'bersih':
            $table = 'order_layanan_bersih_kos';
            $id_col = 'id_order_bk';
            $order_date_col = 'tanggal_datang_bk';
            $total_price_col = 'total_harga_bk';
            $item_desc_col = 'jenis_paket_bk'; // Menggunakan jenis_paket_bk untuk nama paket
            break;
        default:
            $error = "Tipe order tidak valid.";
    }

    if (empty($error) && !empty($table)) {
        // Ambil detail order
        $stmt_order_check = $conn->prepare("SELECT * FROM {$table} WHERE {$id_col} = ? AND id_user = ?");
        if ($stmt_order_check) {
            $stmt_order_check->bind_param("ss", $order_id_ref, $user_id);
            $stmt_order_check->execute();
            $result_order_check = $stmt_order_check->get_result();
            if ($result_order_check->num_rows > 0) {
                $order_data = $result_order_check->fetch_assoc();
                
                $tanggal_order_format = '';
                try {
                    $date_obj = new DateTime($order_data[$order_date_col]);
                    $tanggal_order_format = $date_obj->format('d M Y');
                } catch (Exception $e) {
                    $tanggal_order_format = htmlspecialchars($order_data[$order_date_col]);
                }

                $order_info_display = "Order ID: <span class='order-id-display'>" . htmlspecialchars($order_data[$id_col]) . "</span> | Tipe: " . htmlspecialchars($order_type) . " | Tanggal: " . $tanggal_order_format . " | Total: Rp " . number_format($order_data[$total_price_col], 0, ',', '.');
                if (isset($order_data[$item_desc_col])) {
                    $order_info_display .= " | Layanan: " . htmlspecialchars($order_data[$item_desc_col]);
                }

                // Cek apakah user sudah memberikan ulasan untuk order ini
                // Baris 77: PERBAIKAN KOLOM 'order_id_ref' -> 'id_order' dan 'order_type' -> 'jenis_layanan'
                $stmt_check_ulasan = $conn->prepare("SELECT id_ulasan FROM ulasan_layanan WHERE id_user = ? AND id_order = ? AND jenis_layanan = ?");
                if ($stmt_check_ulasan) {
                    $stmt_check_ulasan->bind_param("sss", $user_id, $order_id_ref, $order_type);
                    $stmt_check_ulasan->execute();
                    if ($stmt_check_ulasan->get_result()->num_rows > 0) {
                        header("Location: submit_review.php?status=duplicate&order_id=" . urlencode($order_id_ref) . "&order_type=" . urlencode($order_type));
                        exit();
                    }
                    $stmt_check_ulasan->close();
                } else {
                    $error = "Gagal menyiapkan cek ulasan: " . $conn->error;
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

// Proses form submission (ketika user mengisi ulasan)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $rating = $_POST['rating'] ?? 0;
    $comment = $_POST['comment'] ?? '';

    $order_id_ref_post = $_POST['order_id_ref'] ?? null;
    $order_type_post = $_POST['order_type'] ?? null;

    $final_order_id = $order_id_ref_post ?: $order_id_ref;
    $final_order_type = $order_type_post ?: $order_type;

    if ($rating < 1 || $rating > 5) {
        $error = "Rating tidak valid.";
    } elseif (empty($final_order_id) || empty($final_order_type)) {
        $error = "Informasi order tidak lengkap untuk menyimpan ulasan.";
    } else {
        // Cek kembali duplikasi sebelum insert untuk mencegah submit ganda yang sangat cepat
        // PERBAIKAN KOLOM 'order_id_ref' -> 'id_order' dan 'order_type' -> 'jenis_layanan'
        $stmt_check_ulasan_pre_insert = $conn->prepare("SELECT id_ulasan FROM ulasan_layanan WHERE id_user = ? AND id_order = ? AND jenis_layanan = ?");
        if ($stmt_check_ulasan_pre_insert) {
            $stmt_check_ulasan_pre_insert->bind_param("sss", $user_id, $final_order_id, $final_order_type);
            $stmt_check_ulasan_pre_insert->execute();
            if ($stmt_check_ulasan_pre_insert->get_result()->num_rows > 0) {
                header("Location: submit_review.php?status=duplicate&order_id=" . urlencode($final_order_id) . "&order_type=" . urlencode($final_order_type));
                exit();
            }
            $stmt_check_ulasan_pre_insert->close();
        } else {
            $error = "Gagal menyiapkan pre-cek ulasan: " . $conn->error;
        }

        if (empty($error)) {
            // Masukkan ulasan ke database
            // PERBAIKAN KOLOM 'order_id_ref' -> 'id_order' dan 'order_type' -> 'jenis_layanan'
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
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Berikan Ulasanmu | MOVER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../css/rating.css" />
    <link rel="stylesheet" href="../css/common.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

    <main class="main-content">
        <div class="review-container">
            <h2>Bagaimana Pengalamanmu di Mover?</h2>
            <p class="order-info">Kami Menghargai Feedback dari kamu!</p>

            <?php if (!empty($error)): ?>
            <div class="message-box error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <p class="mt-3"><a href="indexuser.php" class="btn btn-secondary">Kembali ke Beranda</a></p>
            <?php elseif (empty($order_id_ref) || empty($order_type) || empty($order_data)): ?>
            <div class="message-box error-message">
                Order tidak ditemukan atau tidak valid untuk diulas. Pastikan Anda mengakses halaman ini dari link
                ulasan di riwayat pesanan.
            </div>
            <p class="mt-3"><a href="indexuser.php" class="btn btn-secondary">Kembali ke Beranda</a></p>
            <?php else: ?>
            <p class="order-info">Detail Order: <?php echo $order_info_display; ?></p>
            <form action="" method="POST">
                <input type="hidden" name="submit_review" value="1" />
                <input type="hidden" name="order_id_ref" value="<?php echo htmlspecialchars($order_id_ref); ?>" />
                <input type="hidden" name="order_type" value="<?php echo htmlspecialchars($order_type); ?>" />

                <div class="form-group">
                    <label for="rating">Rating mu:</label>
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
                    <label for="comment">Beritahu Kami tentang Pengalamanmu:</label>
                    <textarea id="comment" name="comment" rows="5"
                        placeholder="Bagikan pendapatmu tentang layanan, pengiriman, atau hal lainnya..."
                        maxlength="500"></textarea>
                </div>

                <button type="submit">Kirim Review</button>
            </form>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">&copy; <?php echo date("Y"); ?> MOVER. All rights reserved.</footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>