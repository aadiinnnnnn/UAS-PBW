<?php
session_start(); // Mulai session di awal file
require '../koneksi.php'; // Hubungkan ke database

$error_message = ''; // Variabel untuk menyimpan pesan error

// Jika pengguna sudah login, coba arahkan berdasarkan peran yang sudah ada di session
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'owner') {
            header("Location: owner.php"); // Path ke dasbor owner
            exit;
        } elseif ($_SESSION['role'] === 'user') {
            header("Location: indexuser.php"); // Path ke dasbor user
            exit;
        }
    }
    // Jika peran tidak jelas tapi sudah login, mungkin arahkan ke default atau logout untuk login ulang
    header("Location: index.php"); // Fallback jika peran tidak ada di session tapi sudah login
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password_input = $_POST['password']; // Ganti nama variabel agar tidak bentrok dengan kolom 'password'

        // Lindungi dari SQL Injection
        $username = mysqli_real_escape_string($conn, $username);

        // Query untuk mengambil data user termasuk peran (role)
        $sql = "SELECT id_user, username, password, role FROM user WHERE username = '$username'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);

            // Verifikasi password
            if (password_verify($password_input, $row['password'])) {
                // Password benar, buat session
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $row['id_user'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role']; // Simpan peran ke session

                // Arahkan berdasarkan peran
                if ($row['role'] === 'owner') {
                    header("Location: owner.php"); // Path ke dasbor owner
                    exit;
                } elseif ($row['role'] === 'user') {
                    header("Location: indexuser.php"); // Path ke dasbor user
                    exit;
                } else {
                    // Peran tidak terdefinisi atau tidak dikenal, arahkan ke halaman default
                    $error_message = "Peran pengguna tidak dikenali. Silakan hubungi administrator.";
                    // Atau arahkan ke halaman umum:
                    // header("Location: index.php");
                    // exit;
                }
            } else {
                $error_message = "Username atau password salah.";
            }
            
        } else {
            $error_message = "Username atau password salah.";
        }
    } else {
        $error_message = "Username dan password harus diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pengguna - MOVER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        font-family: 'Poppins', sans-serif;
        /* Menggunakan Poppins */
    }

    .login-container {
        background-color: #fff;
        padding: 2.5rem;
        /* Sedikit lebih besar padding */
        border-radius: 0.75rem;
        /* Border radius lebih besar */
        box-shadow: 0 0.8rem 1.5rem rgba(0, 0, 0, 0.1);
        /* Shadow lebih halus */
        width: 100%;
        max-width: 420px;
        /* Max width sedikit lebih besar */
    }

    .login-container h2 {
        margin-bottom: 1rem;
        text-align: center;
        color: #367A83;
        /* Warna dari common.css */
        font-weight: 700;
        /* Lebih tebal */
        font-size: 2rem;
        /* Lebih besar */
    }

    .login-container .subtitle {
        text-align: center;
        color: #6c757d;
        margin-bottom: 2rem;
        font-size: 0.95rem;
    }

    .form-floating label {
        padding-left: 0.75rem;
    }

    .form-control:focus {
        /* Gaya dari common.css */
        border-color: #F5A623 !important;
        box-shadow: 0 0 0 0.2rem rgba(245, 166, 35, 0.25) !important;
    }

    .btn-primary {
        /* Tombol login utama */
        background-color: #F5A623;
        /* Warna aksen oranye */
        border-color: #F5A623;
        font-weight: 600;
        padding: 0.75rem;
        /* Padding tombol */
        font-size: 1.05rem;
    }

    .btn-primary:hover {
        background-color: #db8e1e;
        /* Warna hover */
        border-color: #db8e1e;
    }

    .text-center a {
        color: #367A83;
        /* Warna link */
        text-decoration: none;
    }

    .text-center a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>MOVER</h2>
        <p class="subtitle">Silakan login ke akun Anda</p>
        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username Anda"
                    required
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <label for="username">Username</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password Anda"
                    required>
                <label for="password">Password</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
            <p class="mt-3 text-center">
                Belum punya akun? <a href="registrasi.php">Daftar di sini</a> </p>
            <p class="mt-2 text-center">
                <a href="../index.php">Kembali ke Halaman Utama</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>