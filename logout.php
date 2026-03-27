<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .cartoon-border { border: 3px solid #000; }
        .cartoon-shadow { box-shadow: 8px 8px 0px 0px #000; }
        .cartoon-shadow-sm { box-shadow: 4px 4px 0px 0px #000; }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">
    <script>
        // Hapus data dari LocalStorage
        localStorage.removeItem('userSewaIn');
    </script>

    <div id="logoutToast" class="bg-white cartoon-border cartoon-shadow p-12 flex flex-col items-center gap-8 text-center w-full max-w-[420px] animate-bounce">
        <div class="w-28 h-28 bg-emerald-400 cartoon-border rounded-full flex items-center justify-center text-white cartoon-shadow-sm">
            <i data-lucide="check-circle-2" class="w-14 h-14"></i>
        </div>
        <div class="space-y-4">
            <h4 class="font-black text-3xl uppercase italic text-slate-900 leading-tight">Sampai Jumpa!</h4>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest leading-relaxed">Berhasil Keluar Dari Akun SewaIn</p>
        </div>
        <div class="w-full">
            <div class="bg-yellow-300 cartoon-border px-6 py-4 font-black text-[10px] uppercase italic cartoon-shadow-sm flex items-center justify-center gap-3">
                 Mengalihkan...
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        setTimeout(() => {
            window.location.href = 'user/dashboardUser.php';
        }, 1500);
    </script>
</body>
</html>