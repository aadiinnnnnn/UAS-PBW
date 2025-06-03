<?php
session_start();
// Periksa apakah pengguna sudah login menggunakan variabel session yang benar
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { // Menggunakan $_SESSION['loggedin']
    header('Location: login.php'); // Pastikan path ini benar jika session.php ada di dalam folder admin
    exit;
}
?>