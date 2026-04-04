<?php
session_start();
include '../config.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Query untuk tab aktif
$q_aktif = mysqli_query($conn, "
    SELECT p.*, a.nama_alat, a.gambar, pd.idalat,
           (SELECT COUNT(*) FROM penyewaan_detail pd2 WHERE pd2.idsewa = p.idsewa) as total_item
    FROM penyewaan p 
    JOIN penyewaan_detail pd ON p.idsewa = pd.idsewa 
    JOIN alat a ON pd.idalat = a.idalat 
    WHERE p.iduser = $id_user AND p.status NOT IN ('selesai', 'dibatalkan', 'ditolak')
    GROUP BY p.idsewa
    ORDER BY p.tanggal_mulai DESC
");
$aktif_count = mysqli_num_rows($q_aktif);

// Query untuk tab selesai
$q_selesai = mysqli_query($conn, "
    SELECT p.*, a.nama_alat, a.gambar, pd.idalat, pp.tanggal_kembali,
           (SELECT COUNT(*) FROM penyewaan_detail pd2 WHERE pd2.idsewa = p.idsewa) as total_item
    FROM penyewaan p 
    JOIN penyewaan_detail pd ON p.idsewa = pd.idsewa 
    JOIN alat a ON pd.idalat = a.idalat 
    LEFT JOIN pengembalian pp ON p.idsewa = pp.id_sewa
    WHERE p.iduser = $id_user AND p.status = 'selesai'
    GROUP BY p.idsewa
    ORDER BY p.tanggal_selesai DESC
");
$selesai_count = mysqli_num_rows($q_selesai);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivitas Sewa - SewaIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F1F5F9;
        }

        .cartoon-border {
            border: 3px solid #000;
        }

        .cartoon-shadow {
            box-shadow: 6px 6px 0px 0px rgba(0, 0, 0, 1);
        }

        .cartoon-shadow-sm {
            box-shadow: 4px 4px 0px 0px rgba(0, 0, 0, 1);
        }

        .tab-active {
            background-color: #FACC15 !important; /* Kuning Terang */
            transform: translate(-2px, -2px);
            box-shadow: 4px 4px 0px 0px #000;
        }

        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="p-4 md:p-8" x-data="{ tab: 'aktif', modalOpen: false, returnInv: '', returnName: '' }">

    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-10">
            <a href="dashboardUser.php"
                class="w-12 h-12 bg-white cartoon-border rounded-2xl flex items-center justify-center cartoon-shadow-sm hover:bg-yellow-50 transition-all">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </a>
            <div>
                <h1 class="text-3xl font-black uppercase italic tracking-tighter text-slate-900">Aktivitas Sewa</h1>
            </div>
        </div>

        <div class="flex gap-4 mb-8">
            <button @click="tab = 'aktif'" 
                :class="tab === 'aktif' ? 'tab-active' : ''"
                class="px-6 py-3 bg-white cartoon-border rounded-xl font-black text-xs uppercase italic transition-all">
                Sewa Aktif (<?= $aktif_count ?>)
            </button>
            <button @click="tab = 'selesai'" 
                :class="tab === 'selesai' ? 'tab-active' : ''"
                class="px-6 py-3 bg-white cartoon-border rounded-xl font-black text-xs uppercase italic hover:bg-slate-50 transition-all">
                Riwayat Selesai (<?= $selesai_count ?>)
            </button>
        </div>

        <div x-show="tab === 'aktif'" class="space-y-6" x-transition.opacity>
            <?php if ($aktif_count > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($q_aktif)): ?>
                    <?php 
                        $inv = "INV-" . str_pad($row['idsewa'], 4, '0', STR_PAD_LEFT);
                        $tampil_nama = $row['nama_alat'];
                        if ($row['total_item'] > 1) {
                            $tampil_nama .= " (+ " . ($row['total_item'] - 1) . " item lainnya)";
                        }
                        
                        $is_pending = ($row['status'] == 'pending');
                        $is_disewa = ($row['status'] == 'disewa');
                        $is_qc = in_array(strtolower($row['status']), ['kembali', 'menunggu qc']);
                    ?>

                    <?php if ($is_disewa): ?>
                        <div class="bg-white cartoon-border cartoon-shadow rounded-[2.5rem] p-6 flex flex-col md:flex-row items-center gap-6 border-b-[8px]">
                            <div class="w-24 h-24 bg-blue-100 cartoon-border rounded-[1.5rem] flex items-center justify-center shrink-0 overflow-hidden">
                                <?php if($row['gambar']): ?>
                                    <img src="../uploads/<?= $row['gambar'] ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i data-lucide="package" class="w-12 h-12 text-blue-600"></i>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1 text-center md:text-left">
                                <div class="flex flex-wrap justify-center md:justify-start items-center gap-2 mb-2">
                                    <span class="bg-blue-100 text-blue-600 text-[9px] font-black px-3 py-1 rounded-full cartoon-border uppercase tracking-widest">Sedang Digunakan</span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase italic">#<?= $inv ?></span>
                                </div>
                                <h3 class="text-xl font-black uppercase italic text-slate-900"><?= $tampil_nama ?></h3>
                                <div class="flex items-center justify-center md:justify-start gap-4 mt-2">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase">Deadline: <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full md:w-auto">
                                <button @click="modalOpen = true; returnInv = '<?= $inv ?>'; returnName = '<?= addslashes($row['nama_alat']) ?>'"
                                    class="w-full bg-red-700 text-white px-8 py-4 rounded-2xl cartoon-border cartoon-shadow-sm font-black text-xs uppercase italic hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all active:scale-95">
                                    Kembalikan Alat
                                </button>
                            </div>
                        </div>

                    <?php elseif ($is_pending): ?>
                        <div class="bg-slate-50 cartoon-border rounded-[2.5rem] p-6 flex flex-col md:flex-row items-center gap-6 border-dashed">
                            <div class="w-24 h-24 bg-slate-200 cartoon-border rounded-[1.5rem] flex items-center justify-center shrink-0 overflow-hidden">
                                <?php if($row['gambar']): ?>
                                    <img src="../uploads/<?= $row['gambar'] ?>" class="w-full h-full object-cover grayscale opacity-70">
                                <?php else: ?>
                                    <i data-lucide="hourglass" class="w-12 h-12 text-slate-500"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 text-center md:text-left">
                                <div class="flex flex-wrap justify-center md:justify-start items-center gap-2 mb-2">
                                    <span class="bg-orange-100 text-orange-600 text-[9px] font-black px-3 py-1 rounded-full cartoon-border uppercase animate-pulse italic">Menunggu Konfirmasi</span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase italic">#<?= $inv ?></span>
                                </div>
                                <h3 class="text-xl font-black uppercase italic text-slate-600 tracking-tighter"><?= $tampil_nama ?></h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-1 italic leading-tight">Admin sedang memverifikasi pesanan Anda.</p>
                            </div>
                            <div class="w-full md:w-auto">
                                <div class="px-6 py-4 bg-slate-200 cartoon-border rounded-2xl font-black text-[10px] text-slate-500 uppercase italic text-center">
                                    PENDING
                                </div>
                            </div>
                        </div>

                    <?php elseif ($is_qc): ?>
                        <div class="bg-slate-50 opacity-80 cartoon-border rounded-[2.5rem] p-6 flex flex-col md:flex-row items-center gap-6 border-dashed border-slate-300">
                            <div class="w-24 h-24 bg-white cartoon-border rounded-[1.5rem] flex items-center justify-center shrink-0 grayscale overflow-hidden">
                                <?php if($row['gambar']): ?>
                                    <img src="../uploads/<?= $row['gambar'] ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i data-lucide="search" class="w-12 h-12 text-slate-400"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 text-center md:text-left">
                                <div class="flex flex-wrap justify-center md:justify-start items-center gap-2 mb-2">
                                    <span class="bg-yellow-100 text-yellow-600 text-[9px] font-black px-3 py-1 rounded-full cartoon-border uppercase animate-pulse italic">Menunggu Validasi Admin</span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase italic">#<?= $inv ?></span>
                                </div>
                                <h3 class="text-xl font-black uppercase italic text-slate-400 tracking-tighter"><?= $tampil_nama ?></h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-1 italic leading-tight">Unit sedang dalam pengecekan QC di gudang.</p>
                            </div>
                            <div class="w-full md:w-auto">
                                <div class="px-6 py-4 bg-slate-200 cartoon-border rounded-2xl font-black text-[10px] text-slate-400 uppercase italic text-center">
                                    PROSES QC
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-12 px-6 bg-white cartoon-border rounded-[2.5rem]">
                    <i data-lucide="inbox" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-black uppercase italic text-slate-400">Belum ada aktivitas sewa</h3>
                    <a href="dashboardUser.php" class="inline-block mt-4 bg-yellow-400 cartoon-border cartoon-shadow-sm px-6 py-3 rounded-xl font-black text-xs uppercase italic hover:bg-yellow-300 transition-all">Mulai Sewa Alat</a>
                </div>
            <?php endif; ?>
        </div>

        <div x-show="tab === 'selesai'" class="space-y-6" x-transition.opacity x-cloak>
            <?php if ($selesai_count > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($q_selesai)): ?>
                    <?php 
                        $inv = "INV-" . str_pad($row['idsewa'], 4, '0', STR_PAD_LEFT);
                        $tampil_nama = $row['nama_alat'];
                        if ($row['total_item'] > 1) {
                            $tampil_nama .= " (+ " . ($row['total_item'] - 1) . " item lainnya)";
                        }
                    ?>
                    <div class="bg-white cartoon-border rounded-[2.5rem] p-6 flex flex-col md:flex-row items-center gap-6 opacity-70 grayscale">
                        <div class="w-20 h-20 bg-slate-100 cartoon-border rounded-2xl flex items-center justify-center overflow-hidden">
                            <?php if($row['gambar']): ?>
                                <img src="../uploads/<?= $row['gambar'] ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i data-lucide="check-circle" class="w-10 h-10 text-slate-400"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <span class="text-[9px] font-black text-slate-400 uppercase italic">#<?= $inv ?></span>
                            <h3 class="text-lg font-black uppercase italic text-slate-700 leading-none mb-1 mt-1"><?= $tampil_nama ?></h3>
                            <p class="text-[10px] font-bold text-emerald-600 uppercase italic">Dikembalikan: <?= $row['tanggal_kembali'] ? date('d M Y', strtotime($row['tanggal_kembali'])) : date('d M Y', strtotime($row['tanggal_selesai'])) ?></p>
                        </div>
                        <a href="detailAlat.php?id=<?= $row['idalat'] ?>" class="w-full text-center md:w-auto bg-white cartoon-border cartoon-shadow-sm px-6 py-3 rounded-xl font-black text-[10px] uppercase italic hover:bg-yellow-300 transition-all">
                            Sewa Lagi
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-12 px-6 bg-white cartoon-border rounded-3xl">
                    <i data-lucide="archive" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-black uppercase italic text-slate-400">Riwayat kosong</h3>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-12 bg-white cartoon-border border-dashed p-6 rounded-[2rem] flex items-start gap-4">
            <i data-lucide="help-circle" class="text-blue-500 w-8 h-8 shrink-0"></i>
            <div>
                <h5 class="font-black uppercase italic text-sm text-slate-900">Butuh bantuan pengembalian?</h5>
                <p class="text-[11px] font-bold text-slate-600 mt-1 italic leading-relaxed">
                    Silakan antar unit ke Gudang SewaIn. Jika alat terlalu berat untuk diantar sendiri (seperti Oven besar), 
                    silakan hubungi admin via WhatsApp untuk penjemputan unit ke lokasi Anda.
                </p>
            </div>
        </div>
    </div>

    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" x-cloak x-transition.opacity>
    <div @click.outside="modalOpen = false" class="bg-white cartoon-border cartoon-shadow rounded-[3rem] w-full max-w-sm p-8 relative overflow-hidden" x-transition.scale.90>
        <div class="text-center">
            <div class="w-20 h-20 bg-yellow-400 cartoon-border rounded-full flex items-center justify-center mx-auto mb-6 shadow-[4px_4px_0px_0px_#000]">
                <i data-lucide="map-pin" class="w-10 h-10 text-black"></i>
            </div>
            <h3 class="text-2xl font-black uppercase italic text-slate-900 mb-2 leading-none tracking-tighter">Cara Kembalikan</h3>
            <p class="text-[11px] font-bold text-slate-500 mb-8 uppercase italic leading-tight px-4">
                Antar alat langsung ke **Gudang SewaIn (Blok B-12)**. <br>Admin akan cek fisik & validasi di tempat.
            </p>
            
            <div class="space-y-4">
                <button @click="modalOpen = false" class="w-full bg-blue-500 text-white py-4 rounded-2xl cartoon-border shadow-[4px_4px_0px_0px_#000] font-black text-xs uppercase italic hover:bg-blue-600 transition-all active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                    OKE, SIAP!
                </button>

                <a :href="'https://wa.me/6287776600292?text=' + encodeURIComponent('*Halo Admin SewaIn, saya butuh bantuan penjemputan alat untuk unit ' + returnName + ' dengan nomor Invoice #' + returnInv + '*. Terima kasih.')" 
                   target="_blank"
                   class="w-full bg-white border-2 border-black py-4 rounded-2xl flex items-center justify-center gap-2 font-black text-xs uppercase italic hover:bg-slate-50 transition-all shadow-[4px_4px_0px_0px_#000] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                    BUTUH JEMPUTAN
                </a>
            </div>
        </div>
    </div>
</div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>