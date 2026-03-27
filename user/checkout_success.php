<?php
session_start();
include '../config.php';

if (!isset($_GET['id'])) {
    header("Location: dashboardUser.php");
    exit();
}

$id_sewa = (int)$_GET['id'];
$id_user = $_SESSION['id_user'];

// --- FETCH ORDER DATA ---
$query_sewa = mysqli_query($conn, "
    SELECT s.*, u.nama, u.notelp 
    FROM penyewaan s 
    JOIN user u ON s.iduser = u.id_user 
    WHERE s.idsewa = $id_sewa AND s.iduser = $id_user
");
$data_sewa = mysqli_fetch_assoc($query_sewa);

if (!$data_sewa) {
    header("Location: dashboardUser.php");
    exit();
}

$query_detail = mysqli_query($conn, "
    SELECT d.*, a.nama_alat 
    FROM penyewaan_detail d 
    JOIN alat a ON d.idalat = a.idalat 
    WHERE d.idsewa = $id_sewa
");

// --- CONSTRUCT WHATSAPP MESSAGE ---
$items_list = "";
mysqli_data_seek($query_detail, 0); // Reset result pointer
while($item = mysqli_fetch_assoc($query_detail)) {
    $items_list .= "- " . $item['nama_alat'] . " (" . $item['jumlah'] . " Unit)\n";
}

$wa_number = "6287776600292";
$msg_template = "*HALO ADMIN SEWAIN!*\n\n"
              . "SAYA TELAH MELAKUKAN PEMESANAN BARU:\n"
              . "*ID PESANAN:* #%s\n\n"
              . "*DETAIL ALAT:*\n%s\n"
              . "*DATA PENYEWA:*\n"
              . "- Nama: %s\n"
              . "- WA: %s\n"
              . "- Periode: %s s/d %s (%s Hari)\n"
              . "- Metode: %s\n"
              . "- Total: Rp%s\n\n"
              . "Mohon segera diproses ya admin, terima kasih!";

$msg_full = sprintf(
    $msg_template,
    $id_sewa,
    $items_list,
    $data_sewa['nama'],
    $data_sewa['notelp'],
    $data_sewa['tanggal_mulai'],
    $data_sewa['tanggal_selesai'],
    $data_sewa['durasi'],
    $data_sewa['metode_pengiriman'],
    number_format($data_sewa['total_biaya'], 0, ',', '.')
);

$wa_url = "https://wa.me/{$wa_number}?text=" . urlencode($msg_full);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - SewaIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
        .cartoon-border { border: 3px solid #000; }
        .cartoon-shadow { box-shadow: 8px 8px 0px 0px #000; }
        .cartoon-shadow-sm { box-shadow: 4px 4px 0px 0px #000; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">
    <div class="bg-white cartoon-border cartoon-shadow p-12 flex flex-col items-center gap-8 text-center w-full max-w-[480px]">
        <div class="w-28 h-28 bg-emerald-400 cartoon-border rounded-full flex items-center justify-center text-white cartoon-shadow-sm">
            <i data-lucide="check-circle" class="w-14 h-14"></i>
        </div>
        
        <div class="space-y-4">
            <h1 class="font-black text-3xl uppercase italic text-slate-900 leading-tight">PESANAN BERHASIL!</h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Terima kasih telah menyewa di SewaIn. Silakan konfirmasi ke admin via WhatsApp untuk mempercepat proses verifikasi.</p>
        </div>

        <div class="w-full space-y-4">
            <a href="<?= $wa_url ?>" target="_blank" class="w-full bg-emerald-500 text-white py-5 rounded-2xl font-black text-xs tracking-widest uppercase italic block shadow-[4px_4px_0px_0px_#064e3b] hover:translate-y-1 transition-all flex items-center justify-center gap-3">
                <i data-lucide="message-circle" class="w-5 h-5"></i>
                KONFIRMASI VIA WHATSAPP
            </a>
            
            <a href="dashboardUser.php" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-xs tracking-widest uppercase italic block shadow-[4px_4px_0px_0px_#000] hover:translate-y-1 transition-all">
                KEMBALI KE BERANDA
            </a>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
