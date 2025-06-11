<?php
session_start(); // Mulai session untuk mengaksesnya

// Hapus semua variabel session
$_SESSION = array(); // Atau session_unset();

// Hancurkan session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy(); // Hancurkan data sesi di server

// Redirect ke halaman login (atau halaman lain yang sesuai)
header("Location: login.php");
exit;
?>