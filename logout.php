<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        // Hapus data dari LocalStorage
        localStorage.removeItem('userSewaIn');
        
        // SweetAlert Feedback
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Keluar',
            text: 'Terima kasih telah menggunakan SewaIn!',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            window.location.href = 'user/dashboardUser.php';
        });
    </script>
</body>
</html>