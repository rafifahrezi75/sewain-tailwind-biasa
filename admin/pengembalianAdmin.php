<?php
    include 'auth_check.php';
    include '../config.php';

    // Handler Create Pengembalian
    if (isset($_POST['simpan_pengembalian'])) {
        $id_sewa          = mysqli_real_escape_string($conn, $_POST['id_sewa']);
        $tanggal_kembali  = date('Y-m-d H:i:s');
        
        $kondisi_arr      = isset($_POST['kondisi']) ? $_POST['kondisi'] : [];
        if (empty($kondisi_arr)) {
            $kondisi = "Aman";
        } else {
            $kondisi = mysqli_real_escape_string($conn, implode(", ", $kondisi_arr));
        }
        
        $denda_kerusakan  = isset($_POST['denda_kerusakan']) ? (int)$_POST['denda_kerusakan'] : 0;
        
        $q_sewa = mysqli_query($conn, "SELECT tanggal_selesai FROM penyewaan WHERE idsewa = '$id_sewa'");
        $d_sewa = mysqli_fetch_assoc($q_sewa);
        $tgl_selesai = new DateTime($d_sewa['tanggal_selesai']);
        $tgl_skrg    = new DateTime(date('Y-m-d'));
        
        $keterlambatan_hari = 0;
        if ($tgl_skrg > $tgl_selesai) {
            $keterlambatan_hari = $tgl_skrg->diff($tgl_selesai)->days;
        }
        
        $denda_per_hari = 50000;
        $total_denda    = ($keterlambatan_hari * $denda_per_hari) + $denda_kerusakan;
        $catatan_admin  = "Telah divalidasi oleh sistem";
        $metode_kembali = "Dikembalikan langsung";
        $status         = 'selesai';

        $insert_query = "INSERT INTO pengembalian 
            (id_sewa, tanggal_kembali, kondisi, keterlambatan_hari, denda_per_hari, denda_kerusakan, total_denda, catatan_admin, status, metode_kembali) 
            VALUES 
            ('$id_sewa', '$tanggal_kembali', '$kondisi', '$keterlambatan_hari', '$denda_per_hari', '$denda_kerusakan', '$total_denda', '$catatan_admin', '$status', '$metode_kembali')
            ON DUPLICATE KEY UPDATE
            tanggal_kembali = '$tanggal_kembali',
            kondisi = '$kondisi',
            keterlambatan_hari = '$keterlambatan_hari',
            denda_per_hari = '$denda_per_hari',
            denda_kerusakan = '$denda_kerusakan',
            total_denda = '$total_denda',
            catatan_admin = '$catatan_admin',
            status = '$status',
            metode_kembali = '$metode_kembali'";

        if (mysqli_query($conn, $insert_query)) {
            mysqli_query($conn, "UPDATE penyewaan SET status = 'selesai' WHERE idsewa = '$id_sewa'");
            
            // Ambil data user untuk notif WA
            $q_user_notif = mysqli_query($conn, "SELECT u.nama, u.notelp FROM penyewaan s JOIN user u ON s.iduser = u.id_user WHERE s.idsewa = '$id_sewa'");
            $d_user_notif = mysqli_fetch_assoc($q_user_notif);
            $wa_num = $d_user_notif['notelp'];
            $nama_u = urlencode($d_user_notif['nama']);
            $inv_l = str_pad($id_sewa, 4, '0', STR_PAD_LEFT);

            header("Location: pengembalianAdmin.php?pesan=berhasil&wa=$wa_num&nama=$nama_u&total=$total_denda&inv=$inv_l");
            exit;
        } else {
            echo "Error inserting pengembalian: " . mysqli_error($conn);
            exit;
        }
    }

    // Data Antrean (Hanya yang statusnya 'disewa')
    $query_antrean = mysqli_query($conn, "
        SELECT s.*, u.nama as nama_umkm, u.notelp as wa_pelanggan, 
               DATEDIFF(CURDATE(), s.tanggal_selesai) as hari_telat 
        FROM penyewaan s 
        JOIN user u ON s.iduser = u.id_user 
        WHERE s.status = 'disewa' 
        ORDER BY s.tanggal_selesai ASC
    ");

    // Data Riwayat (Hanya dari tabel pengembalian)
    $query_riwayat = mysqli_query($conn, "
        SELECT p.*, s.tanggal_mulai, s.tanggal_selesai, u.nama as nama_umkm 
        FROM pengembalian p 
        JOIN penyewaan s ON p.id_sewa = s.idsewa 
        JOIN user u ON s.iduser = u.id_user 
        ORDER BY p.id_kembali DESC 
        LIMIT 20
    ");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengembalian | Admin Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <link href="../src/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-[#f8f9fa] font-sans text-gray-800 antialiased"
    x-data="{ 
        sidebarOpen: true, 
        qcModalOpen: false, 
        denda_kerusakan: 0,
        selectedData: { id_sewa: '', id_sewa_label: '', dendaTelat: '', telat: '', wa: '', user: '' }
    }">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 flex flex-col bg-brand-500 text-white transition-all duration-300 ease-in-out md:static md:block shrink-0 shadow-xl"
            :class="sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full md:translate-x-0 md:w-20'">

            <div class="flex h-[72px] items-center justify-center px-4 shrink-0 font-bold tracking-wider">
                <h1 x-show="sidebarOpen" class="text-2xl w-full text-center" x-transition.opacity>Admin</h1>
                <h1 x-show="!sidebarOpen" class="text-2xl hidden md:block" x-cloak>A</h1>
            </div>

            <nav class="flex-1 space-y-2 overflow-y-auto px-3 py-4 scrollbar-hide">

                <a href="dashboardAdmin.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-grid-alt text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Dashboard</span>
                </a>

                <a href="kategori.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-layer text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Kategori</span>
                </a>
                <a href="alat.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-wrench text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Alat Produksi</span>
                </a>
                <a href="transaksi.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all text-white hover:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-shopping-bag text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Daftar Transaksi</span>
                </a>
                <a href="pengembalianAdmin.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none bg-white text-brand-700 shadow-sm" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-archive-in text-xl shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Pengembalian</span>
                    <div x-show="sidebarOpen" class="ml-auto flex h-2 w-2 shrink-0 rounded-full bg-brand-500"></div>
                </a>

            </nav>

            <div class="p-4" :class="sidebarOpen ? '' : 'md:px-2'">
                <a href="../logout.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-white transition-all hover:bg-brand-600 focus:outline-none" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class='bx bx-log-out text-xl shrink-0 opacity-80'></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="flex flex-1 flex-col overflow-hidden transition-all duration-300">
            
            <!-- Navbar -->
            <header class="flex h-[72px] items-center justify-between bg-brand-500 px-6 shrink-0 shadow-sm border-b border-brand-600/30">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-white hover:bg-brand-600 rounded-lg p-2 transition focus:outline-none">
                        <i class='bx bx-menu text-2xl'></i>
                    </button>
                    <span class="text-lg font-medium text-white">Pengembalian Alat</span>
                </div>

                <div class="flex items-center gap-3 sm:gap-5 text-white">
                    <div class="flex items-center gap-3 pl-3 sm:pl-5 relative before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-8 before:w-px before:bg-brand-400">
                        <div class="hidden sm:flex flex-col text-right justify-center">
                            <span class="text-sm font-semibold leading-tight"><?= htmlspecialchars($_SESSION['nama']) ?></span>
                            <span class="text-[10px] text-brand-400 font-medium mt-0.5 uppercase tracking-wider">SEWAIN</span>
                        </div>
                        <button class="h-9 w-9 overflow-hidden rounded-full bg-white/10 hover:bg-white/20 border border-white/20 transition flex items-center justify-center focus:outline-none">
                            <i class='bx bx-user text-xl text-white'></i>
                        </button>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 scrollbar-hide">
                
                <!-- SECTION 1: ANTREAN -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                    <h2 class="text-2xl font-bold text-gray-800">Antrean Validasi</h2>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i class='bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg'></i>
                            <input type="text" placeholder="Cari invoice..." class="pl-10 pr-4 py-2 w-full sm:w-64 rounded-xl border border-gray-200 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden mb-10">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-500">
                            <thead class="bg-gray-50/50 text-xs uppercase text-gray-400 border-b border-gray-100 font-semibold tracking-wider">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Invoice & UMKM</th>
                                    <th scope="col" class="px-6 py-4">Tenggat Waktu</th>
                                    <th scope="col" class="px-6 py-4">Status Waktu</th>
                                    <th scope="col" class="px-6 py-4">Progres</th>
                                    <th scope="col" class="px-6 py-4 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (mysqli_num_rows($query_antrean) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($query_antrean)): ?>
                                        <?php 
                                            // Kalkulasi Telat
                                            $telatHari = max(0, $row['hari_telat']);
                                            $dendaTelatValue = $telatHari * 50000;
                                            
                                            // Format Rupiah string untuk di oper ke modal
                                            $dendaRupiahFormat = number_format($dendaTelatValue, 0, ',', '.');
                                        ?>
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <span class="block font-medium text-gray-900 border-b border-gray-100 pb-1 mb-1">#<?= str_pad($row['idsewa'], 4, '0', STR_PAD_LEFT) ?></span>
                                                <span class="text-xs text-gray-500"><?= htmlspecialchars($row['nama_umkm']) ?></span>
                                            </td>
                                            <td class="px-6 py-4 text-xs font-medium text-gray-700">
                                                <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php $sisa = $row['hari_telat'] * -1; // Sisa hari ?>
                                                <?php if($telatHari > 0): ?>
                                                    <span class="inline-flex items-center gap-1 rounded bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600 ring-1 ring-inset ring-red-500/10">Telat <?= $telatHari ?> Hari</span>
                                                <?php elseif($sisa == 0): ?>
                                                    <span class="inline-flex items-center gap-1 rounded bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-600 ring-1 ring-inset ring-orange-500/10">Hari Ini</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1 rounded bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">H-<?= $sisa ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center gap-1 rounded bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                    Sedang Disewa
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <button @click="selectedData = {
                                                        id_sewa: '<?= $row['idsewa'] ?>',
                                                        id_sewa_label: '#<?= str_pad($row['idsewa'], 4, '0', STR_PAD_LEFT) ?>',
                                                        dendaTelat: '<?= $dendaRupiahFormat ?>',
                                                        dendaTelatRaw: <?= $dendaTelatValue ?>,
                                                        telat: '<?= $telatHari ?> Hari',
                                                        user: `<?= htmlspecialchars($row['nama_umkm'], ENT_QUOTES) ?>`,
                                                        wa: '<?= $row['wa_pelanggan'] ?>'
                                                    }; denda_kerusakan = 0; qcModalOpen = true"
                                                    class="bg-brand-500 text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-brand-600 transition-colors shadow-sm focus:outline-none">
                                                    Validasi Pengembalian
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium text-sm">Belum ada penyewaan yang masih disewa.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SECTION 2: RIWAYAT SEWA -->
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Riwayat Pengembalian Selesai</h2>
                </div>

                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-500">
                            <thead class="bg-gray-50/50 text-xs uppercase text-gray-400 border-b border-gray-100 font-semibold tracking-wider">
                                <tr>
                                    <th scope="col" class="px-6 py-4">Invoice</th>
                                    <th scope="col" class="px-6 py-4">Kondisi Alat</th>
                                    <th scope="col" class="px-6 py-4">Keterlambatan</th>
                                    <th scope="col" class="px-6 py-4">Total Denda</th>
                                    <th scope="col" class="px-6 py-4 text-right">Tanggal Selesai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if(mysqli_num_rows($query_riwayat) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($query_riwayat)): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="block font-medium text-gray-900 border-b border-gray-100 pb-1 mb-1">#<?= str_pad($row['id_sewa'], 4, '0', STR_PAD_LEFT) ?></span>
                                            <span class="text-xs text-gray-500 block"><?= htmlspecialchars($row['nama_umkm']) ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-700">
                                            <?= htmlspecialchars($row['kondisi']) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if($row['keterlambatan_hari'] > 0): ?>
                                                <span class="inline-flex items-center gap-1.5 rounded bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600 ring-1 ring-inset ring-red-500/10">Telat <?= $row['keterlambatan_hari'] ?> Hari</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1.5 rounded bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-600 ring-1 ring-inset ring-emerald-500/10">Tepat Waktu</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if($row['total_denda'] > 0): ?>
                                                <span class="font-medium text-red-600 block">Rp <?= number_format($row['total_denda'], 0, ',', '.') ?></span>
                                            <?php else: ?>
                                                <span class="text-xs text-emerald-600 font-medium bg-emerald-50 px-2 py-1 rounded inline-block">Selesai / Tanpa Denda</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-xs text-gray-700 block"><?= date('d M Y', strtotime($row['tanggal_kembali'])) ?></span>
                                            <span class="text-xs text-gray-400 block mt-1"><i class='bx bx-check'></i> QC Divalidasi</span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium text-sm">Belum ada riwayat pengembalian.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL VALIDASI PENGEMBALIAN (DESAIN USER LANGSUNG CREATE PENGEMBALIAN)  -->
    <!-- ========================================================================= -->
    <div x-show="qcModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm" x-cloak x-transition.opacity>
        <div @click.outside="qcModalOpen = false" class="w-full max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden" x-show="qcModalOpen" x-transition.scale.95>
            
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5 bg-gray-50/50">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Validasi Pengembalian</h3>
                    <p class="text-xs text-gray-500 mt-1" x-text="'Invoice ' + selectedData.id_sewa_label"></p>
                </div>
                <button type="button" @click="qcModalOpen = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <!-- FORM UNTUK MENGIRIM DATA KE PHP UNTUK MEMBUAT BARIS DI TABEL PENGEMBALIAN -->
            <form action="pengembalianAdmin.php" method="POST" class="p-6">
                <!-- Data ini diambil dari database saat tombol diklik melalui Alpine.js -->
                <input type="hidden" name="id_sewa" :value="selectedData.id_sewa">

                <!-- Alert Denda Keterlambatan -->
                <div class="mb-6 rounded-xl border border-red-100 bg-red-50 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h4 class="text-sm font-semibold text-red-600 mb-0.5">Denda Keterlambatan</h4>
                        <p class="text-xs text-red-500" x-text="'Sistem: Terlambat ' + selectedData.telat"></p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-xl font-bold text-red-600 tracking-tight" x-text="'Rp ' + selectedData.dendaTelat"></p>
                    </div>
                </div>

                <div class="space-y-6 mb-8">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800 mb-3 block">Kondisi Alat (Hasil Inspeksi Gudang)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors shadow-sm bg-white">
                                <input type="checkbox" name="kondisi[]" value="Unit Kotor/Bau" class="w-4 h-4 text-brand-500 border-gray-300 rounded focus:ring-brand-500">
                                <span class="text-sm font-medium text-gray-700">Unit Kotor / Bau</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors shadow-sm bg-white">
                                <input type="checkbox" name="kondisi[]" value="Kerusakan Fisik" class="w-4 h-4 text-brand-500 border-gray-300 rounded focus:ring-brand-500">
                                <span class="text-sm font-medium text-gray-700">Kerusakan Fisik</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-2">Nominal Denda Tambahan Jika Ada</label>
                        <div class="flex items-stretch rounded-xl shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-brand-500 overflow-hidden bg-white">
                            <span class="flex items-center justify-center px-4 bg-gray-50 text-gray-500 text-sm font-medium border-r border-gray-200">Rp</span>
                            <input type="number" name="denda_kerusakan" x-model.number="denda_kerusakan" placeholder="0" class="flex-1 w-full border-0 py-2.5 pl-4 px-3 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm font-medium outline-none">
                        </div>
                        <p class="mt-2 text-xs text-gray-500 block">Kosongkan (atau isi 0) jika alat utuh dan bersih.</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row-reverse gap-3 pt-2">
                    <button type="submit" name="simpan_pengembalian"
                        class="flex-1 bg-brand-500 text-white px-5 py-2.5 rounded-xl font-medium text-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all flex items-center justify-center gap-2 shadow-sm">
                        <i class='bx bx-check-circle text-lg'></i>
                        Validasi & Selesaikan
                    </button>
                    <button type="button" @click="qcModalOpen = false" class="flex-none px-6 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-200 focus:ring-offset-2">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
        Swal.fire({ 
            icon: 'success', 
            title: 'Berhasil!', 
            text: 'Pengembalian berhasil divalidasi dan tersimpan.', 
            showConfirmButton: false, 
            timer: 2000 
        });

        // Hapus query params dari URL agar tidak trigger berulang saat refresh
        const url_clean = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path:url_clean}, '', url_clean);

        // Notif WA Otomatis jika parameter ada
        <?php if (isset($_GET['wa'])): ?>
            (function() {
                const wa = "<?= $_GET['wa'] ?>";
                const nama = "<?= htmlspecialchars($_GET['nama']) ?>";
                const total = parseInt("<?= $_GET['total'] ?>") || 0;
                const inv = "<?= $_GET['inv'] ?>";
                
                let pesan = `*KONFIRMASI PENGEMBALIAN UNIT*\n\nHalo *${nama}*,\nLaporan pengembalian alat Anda telah kami terima dan divalidasi.\n\n*Nomor Invoice:* #${inv}\n`;
                
                if (total > 0) {
                    pesan += `*Total Denda:* Rp ${total.toLocaleString('id-ID')}\n_(Termasuk denda keterlambatan dan biaya perbaikan fisik/kebersihan jika ada)_\n\nMohon selesaikan pelunasan secara langsung di kasir atau via transfer bank.\n\n`;
                } else {
                    pesan += `*Status:* Alat Kembali dalam Kondisi Aman dan Lengkap (Tanpa Denda Tambahan)\n\n`;
                }
                pesan += `Terima kasih!`;

                // Format nomor WA agar valid (mengganti 0 di depan dengan 62)
                let finalWa = wa.replace(/^0/, '62').replace(/[^\d]/g, '');
                
                setTimeout(() => {
                    window.open(`https://wa.me/${finalWa}?text=${encodeURIComponent(pesan)}`, '_blank');
                }, 1000); // Tunggu sebentar agar Swal terlihat
            })();
        <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>