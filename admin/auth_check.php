<?php
session_start();

// cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] !== true || $_SESSION['role'] !== 'admin') {
    // jika tidak, arahkan kembali ke halaman login di root
    header("Location: ../login.php");
    exit();
}
?>
