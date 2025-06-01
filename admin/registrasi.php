<?php
session_start();
// PASTIKAN PATH INI BENAR menuju file koneksi.php Anda
// Contoh: Jika register.php di root, dan koneksi.php di folder 'admin', maka 'admin/koneksi.php'
// Contoh: Jika register.php dan koneksi.php keduanya di folder 'admin', maka 'koneksi.php'
require '../koneksi.php'; // Asumsi register.php ada di folder admin, dan koneksi.php di root. SESUAIKAN!

$error_message = '';
$success_message = '';

/**
 * Fungsi untuk generate id_user unik char(10) berdasarkan Nama Lengkap.
 * Format: usr + InisialNamaDepan + InisialNamaBelakang + 5 karakter acak.
 *
 * @param string $fullname Nama lengkap pengguna.
 * @param int $length Panjang total ID yang diinginkan (default 10).
 * @return string ID pengguna yang digenerate.
 */
function generateUserIdFromName($fullname, $length = 10) {
    $prefix = 'usr';
    $prefixLength = strlen($prefix);
    $namePart = '';

    $fullname = trim($fullname);
    if (empty($fullname)) {
        $namePart = 'XX'; // Default jika nama kosong
    } else {
        $parts = explode(' ', $fullname, 2);
        $firstNameInitial = !empty($parts[0]) ? strtoupper(substr($parts[0], 0, 1)) : 'X';
        $lastNameInitial = !empty($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : 'X';
        $namePart = $firstNameInitial . $lastNameInitial;
    }

    $randomLength = $length - $prefixLength - strlen($namePart);
    if ($randomLength < 0) $randomLength = 0; // Pastikan tidak negatif

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomChars = '';
    for ($i = 0; $i < $randomLength; $i++) {
        $randomChars .= $characters[rand(0, $charactersLength - 1)];
    }

    $generatedId = $prefix . $namePart . $randomChars;
    return substr($generatedId, 0, $length); // Pastikan panjangnya tepat
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $fullname_input = isset($_POST['fullname_display_only']) ? trim($_POST['fullname_display_only']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['regUsername']) ? trim($_POST['regUsername']) : '';
    $password = isset($_POST['regPassword']) ? trim($_POST['regPassword']) : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';
    $level = 'user'; // Default level untuk pengguna baru

    // Validasi dasar
    if (empty($fullname_input) || empty($email) || empty($username) || empty($password) || empty($confirmPassword)) {
        $error_message = "Semua field harus diisi (Nama Lengkap, Email, Username, Password, Konfirmasi Password).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal harus 6 karakter.";
    } elseif ($password !== $confirmPassword) {
        $error_message = "Password dan konfirmasi password tidak cocok.";
    } else {
        // Cek apakah username atau email sudah ada di database
        // Menggunakan nama tabel 'users' (plural) sesuai mover.sql
        $stmt_check = $conn->prepare("SELECT username, email FROM user WHERE username = ? OR email = ?");
        if ($stmt_check === false) {
            $error_message = "Database error (prepare check): " . htmlspecialchars($conn->error);
        } else {
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $existing_user = $result_check->fetch_assoc();
                if ($existing_user['username'] === $username) {
                    $error_message = "Username sudah digunakan. Silakan pilih username lain.";
                } elseif ($existing_user['email'] === $email) {
                    $error_message = "Email sudah terdaftar. Silakan gunakan email lain.";
                }
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $id_user = '';
                $is_id_unique = false;
                $generation_attempts = 0;

                // Loop untuk memastikan id_user unik
                while(!$is_id_unique && $generation_attempts < 10) { // Batasi percobaan untuk menghindari infinite loop
                    // Generate ID dari nama lengkap
                    $id_user_candidate = generateUserIdFromName($fullname_input, 10);

                    // Menggunakan nama tabel 'users' (plural)
                    $stmt_id_check = $conn->prepare("SELECT id_user FROM user WHERE id_user = ?");
                    if ($stmt_id_check === false) {
                        $error_message = "Database error (prepare id_check): " . htmlspecialchars($conn->error);
                        break;
                    }
                    $stmt_id_check->bind_param("s", $id_user_candidate);
                    $stmt_id_check->execute();
                    $result_id_check = $stmt_id_check->get_result();
                    if ($result_id_check->num_rows == 0) {
                        $id_user = $id_user_candidate;
                        $is_id_unique = true;
                    }
                    $stmt_id_check->close();
                    $generation_attempts++;
                }

                if (!$is_id_unique && empty($error_message)) {
                    $error_message = "Gagal menghasilkan ID pengguna unik setelah beberapa percobaan. Nama mungkin terlalu umum atau coba lagi.";
                }

                if ($is_id_unique) {
                    // Insert data pengguna baru
                    // Menggunakan nama tabel 'users' (plural) dan menyertakan kolom 'level'
                    $stmt_insert = $conn->prepare("INSERT INTO user (id_user, username, email, password) VALUES ( ?, ?, ?, ?)");
                    if ($stmt_insert === false) {
                        $error_message = "Database error (prepare insert): " . htmlspecialchars($conn->error);
                    } else {
                        // Menyesuaikan bind_param menjadi 'sssss' untuk 5 parameter
                        $stmt_insert->bind_param("ssss", $id_user, $username, $email, $hashed_password);

                        if ($stmt_insert->execute()) {
                            $success_message = "Registrasi berhasil! Akun Anda telah dibuat dengan ID: " . htmlspecialchars($id_user);
                            $_POST = array(); // Kosongkan variabel POST
                        } else {
                            $error_message = "Registrasi gagal. Silakan coba lagi. Error: " . htmlspecialchars($stmt_insert->error);
                        }
                        $stmt_insert->close();
                    }
                }
            }
            $stmt_check->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVER - Daftar Akun Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/registrasi.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;700&family=Poppins:wght@400;600;700&display=swap"
        rel="stylesheet">
</head>

<body class="register-page-body">
    <header class="navbar">
        <div class="logo">MOVER</div>
        <nav class="nav-menu">
            <div class="profile-icon">
                <img src="" alt="Mover Icon" />
            </div>
            <a href="#">About</a>
            <a href="#">Contact</a>
            <a href="../admin/index.php"> <button class="order-btn">HOME <span class="arrow"></span></button>
            </a>
        </nav>
    </header>
    <div class="register-card">
        <h2 class="register-title">Daftar Akun Baru</h2>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <p>Silakan <a href="login.php">login di sini</a>.</p>
        </div>
        <?php endif; ?>

        <?php if (empty($success_message)): ?>
        <form id="registerForm" class="register-form" method="POST"
            action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
                <label for="fullname" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="fullname" name="fullname_display_only"
                    placeholder="Masukkan nama lengkap Anda" required
                    value="<?php echo isset($_POST['fullname_display_only']) ? htmlspecialchars($_POST['fullname_display_only']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda"
                    required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="regUsername" class="form-label">Username</label>
                <input type="text" class="form-control" id="regUsername" name="regUsername" placeholder="Buat username"
                    required
                    value="<?php echo isset($_POST['regUsername']) ? htmlspecialchars($_POST['regUsername']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="regPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="regPassword" name="regPassword"
                    placeholder="Buat password (minimal 6 karakter)" required>
            </div>
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                    placeholder="Konfirmasi password Anda" required>
            </div>
            <button type="submit" class="btn btn-register-custom">Daftar</button>
        </form>
        <?php endif; ?>

        <div class="login-link-register"> Sudah punya akun? <a href="login.php">Login di sini</a> </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>