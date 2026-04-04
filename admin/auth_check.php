<?php
session_start();

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] !== true || $_SESSION['role'] !== 'admin') {
    // Jika tidak, arahkan kembali ke halaman login di root
    header("Location: ../login.php");
    exit();
}
?>
