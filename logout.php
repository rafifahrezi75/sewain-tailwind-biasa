<?php
session_start();
// Menghapus semua data sesi
session_destroy();

// Mengarahkan kembali ke halaman beranda (sesuaikan nama filenya)
header("Location: user/dashboardUser.php"); 
exit();
?>