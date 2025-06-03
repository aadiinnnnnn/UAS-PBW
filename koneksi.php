<?php
$host = 'localhost';  // Hostname dari server database
$user = 'root';       // Username untuk akses database
$pass = '';           // Password untuk akses database (sesuaikan)
$dbname = 'mover';  // Nama database

// Membuat koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Periksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>