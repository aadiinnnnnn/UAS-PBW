<?php
session_start();
require '../koneksi.php'; //

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama_user = isset($_POST['nama_user']) ? trim($_POST['nama_user']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $no_hp = isset($_POST['no_hp']) ? trim($_POST['no_hp']) : '';
    $username = isset($_POST['regUsername']) ? trim($_POST['regUsername']) : '';
    $password = isset($_POST['regPassword']) ? trim($_POST['regPassword']) : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';
    // Ambil peran dari form, default ke 'user' jika tidak ada atau tidak valid
    $role_input = isset($_POST['role']) ? trim($_POST['role']) : 'user';

    // Validasi peran
    $allowed_roles = ['user', 'owner']; // Definisikan peran yang diizinkan
    if (!in_array($role_input, $allowed_roles)) {
        $error_message = "Peran yang dipilih tidak valid.";
    } else {
        $role = $role_input; // Gunakan peran yang sudah divalidasi
    }


    // Validasi dasar (lanjutan)
    if (empty($error_message)) { // Hanya lanjutkan jika tidak ada error peran sebelumnya
        if (empty($nama_user) || empty($email) || empty($no_hp) || empty($username) || empty($password) || empty($confirmPassword)) {
            $error_message = "Semua field harus diisi (Nama Lengkap, Email, No. HP, Username, Password, Konfirmasi Password, Peran).";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Format email tidak valid.";
        } elseif (!preg_match('/^[0-9]{10,15}$/', $no_hp)) {
            $error_message = "Format No. HP tidak valid. Harap masukkan 10-15 digit angka.";
        } elseif (strlen($password) < 6) {
            $error_message = "Password minimal harus 6 karakter.";
        } elseif ($password !== $confirmPassword) {
            $error_message = "Password dan konfirmasi password tidak cocok.";
        } else {
            // Cek apakah username atau email sudah ada di database
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

                    while(!$is_id_unique && $generation_attempts < 10) {
                        $id_user_candidate = strtoupper(bin2hex(random_bytes(5)));
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
                        $error_message = "Gagal menghasilkan ID pengguna unik setelah beberapa percobaan. Silakan coba lagi.";
                    }

                    if ($is_id_unique && empty($error_message)) { // Pastikan tidak ada error sebelum insert
                        // Insert data pengguna baru termasuk peran (role)
                        $stmt_insert = $conn->prepare("INSERT INTO user (id_user, nama_user, email, no_hp, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        if ($stmt_insert === false) {
                            $error_message = "Database error (prepare insert): " . htmlspecialchars($conn->error);
                        } else {
                            // Parameter ketujuh (s) adalah untuk $role
                            $stmt_insert->bind_param("sssssss", $id_user, $nama_user, $email, $no_hp, $username, $hashed_password, $role);

                            if ($stmt_insert->execute()) {
                                $success_message = "Registrasi berhasil! Akun Anda (". htmlspecialchars($role) .") telah berhasil dibuat.";
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
    <link rel="stylesheet" href="../css/common.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;700&family=Poppins:wght@400;600;700&display=swap"
        rel="stylesheet">
</head>

<body class="register-page-body">
    <div class="register-card">
        <h2 class="register-title">Daftar Akun Baru</h2> <?php if (!empty($error_message)): ?>
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

        <?php if (empty($success_message)): // Hanya tampilkan form jika belum sukses ?>
        <form id="registerForm" class="register-form" method="POST"
            action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
                <label for="nama_user" class="form-label">Nama Lengkap</label> <input type="text" class="form-control"
                    id="nama_user" name="nama_user" placeholder="Masukkan nama lengkap Anda" required
                    value="<?php echo isset($_POST['nama_user']) ? htmlspecialchars($_POST['nama_user']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label> <input type="email" class="form-control" id="email"
                    name="email" placeholder="Masukkan email Anda" required
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="no_hp" class="form-label">No. HP</label> <input type="tel" class="form-control" id="no_hp"
                    name="no_hp" placeholder="Masukkan No. HP Anda (cth: 081234567890)" required pattern="[0-9]{10,15}"
                    title="Masukkan 10-15 digit angka"
                    value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="regUsername" class="form-label">Username</label> <input type="text" class="form-control"
                    id="regUsername" name="regUsername" placeholder="Buat username" required
                    value="<?php echo isset($_POST['regUsername']) ? htmlspecialchars($_POST['regUsername']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="regPassword" class="form-label">Password</label> <input type="password" class="form-control"
                    id="regPassword" name="regPassword" placeholder="Buat password (minimal 6 karakter)" required>
            </div>
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Konfirmasi Password</label> <input type="password"
                    class="form-control" id="confirmPassword" name="confirmPassword"
                    placeholder="Konfirmasi password Anda" required>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Daftar sebagai:</label>
                <select class="form-select form-control" id="role" name="role" required>
                    <option value="user"
                        <?php echo (isset($_POST['role']) && $_POST['role'] == 'user') ? 'selected' : ''; ?>>Pengguna
                        (Pencari Kos/Jasa)</option>
                    <option value="owner"
                        <?php echo (isset($_POST['role']) && $_POST['role'] == 'owner') ? 'selected' : ''; ?>>Pemilik
                        Kost</option>
                </select>
            </div>

            <button type="submit" class="btn btn-register-custom">Daftar</button>
        </form>
        <?php endif; ?>

        <div class="login-link-register"> Sudah punya akun? <a href="login.php">Login di sini</a> </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>