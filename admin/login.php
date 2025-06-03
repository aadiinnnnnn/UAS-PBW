<?php
session_start(); // Mulai session di awal file
require '../koneksi.php'; // Hubungkan ke database

$error_message = ''; // Variabel untuk menyimpan pesan error

// Jika pengguna sudah login, redirect ke halaman indexuser.php
if (isset($_SESSION['username'])) {
    header("Location: indexuser.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Lindungi dari SQL Injection
        $username = mysqli_real_escape_string($conn, $username); // Gunakan $conn atau variabel koneksi Anda

        // Query untuk mengambil data user
        // user table has columns: id_user, nama_user, email, no_hp, username, password, role
        $sql = "SELECT * FROM user WHERE username = '$username'"; //
        $result = mysqli_query($conn, $sql); // Gunakan $conn atau variabel koneksi Anda

        if ($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);

            // Verifikasi password
            // Jika password di database di-hash menggunakan password_hash()
            if (password_verify($password, $row['password'])) { //
                // Password benar, buat session
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $row['id_user']; // Changed 'id' to 'id_user' to match DB
                $_SESSION['username'] = $row['username']; //
                // You might also want to store other user details in the session if needed, e.g., nama_user or role
                // $_SESSION['nama_user'] = $row['nama_user'];
                // $_SESSION['role'] = $row['role'];

                // Redirect ke halaman indexuser.php
                header("Location: indexuser.php"); //
                exit;
            } else {
                // Password salah (jika menggunakan password_verify)
                $error_message = "password salah."; //
            }
            
        } else {
            // Username tidak ditemukan
            $error_message = "Username salah."; //
        }
    } else {
        $error_message = "Username dan password harus diisi."; //
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
    }

    .login-container {
        background-color: #fff;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        width: 100%;
        max-width: 400px;
    }

    .login-container h2 {
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .form-floating label {
        padding-left: 0.5rem;
        /* Adjust if label is misaligned */
    }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>MOVER INC</h2>
        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username Anda"
                    required> <label for="username">Username</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password Anda"
                    required> <label for="password">Password</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
            <p class="mt-3 text-center">
                Belum punya akun? <a href="registrasi.php">Daftar di sini</a> </p>
            <p class="mt-2 text-center">
                <a href="../admin/index.php">Kembali ke Halaman Utama</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>