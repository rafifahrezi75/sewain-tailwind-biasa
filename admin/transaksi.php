<?php
    include '../config.php';

    // Pagination
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $start = ($page - 1) * $limit;

    // Search logic
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    $where_clause = "";
    if ($search != '') {
        $where_clause = " WHERE (s.idsewa LIKE '%$search%' OR u.nama LIKE '%$search%') ";
    }

    // Count total for pagination
    $countQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan s JOIN user u ON s.iduser = u.id_user $where_clause");
    $countRow = mysqli_fetch_assoc($countQuery);
    $total_transaksi = $countRow['total'] ?? 0;
    $total_page = ceil($total_transaksi / $limit);

    // --- Update Status Handler ---
    if (isset($_POST['update_status'])) {
        $id_sewa = mysqli_real_escape_string($conn, $_POST['id_sewa']);
        $status_baru = mysqli_real_escape_string($conn, $_POST['status_baru']);
        
        $update = mysqli_query($conn, "UPDATE penyewaan SET status = '$status_baru' WHERE idsewa = '$id_sewa'");
        if ($update) {
            header("Location: transaksi.php?pesan=berhasil_update");
            exit;
        } else {
            header("Location: transaksi.php?pesan=gagal_update");
            exit;
        }
    }

    // Get Data Transaksi
    $query = mysqli_query($conn, "
        SELECT s.*, u.nama as nama_pelanggan 
        FROM penyewaan s 
        JOIN user u ON s.iduser = u.id_user 
        $where_clause 
        ORDER BY s.idsewa DESC 
        LIMIT $start, $limit
    ");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Transaksi | Admin Dashboard</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <link href="../src/output.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-[#f8f9fa] font-sans text-gray-800 antialiased" x-data="{ sidebarOpen: true, statusModalOpen: false, statusData: { id: '', current: '' } }">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside 
            class="fixed inset-y-0 left-0 z-50 flex flex-col bg-brand-500 text-white transition-all duration-300 ease-in-out md:static md:block shrink-0 shadow-xl"
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

                <a href="transaksi.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none bg-white text-brand-700 shadow-sm" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-shopping-bag text-xl shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Daftar Transaksi</span>
                    <div x-show="sidebarOpen" class="ml-auto flex h-2 w-2 shrink-0 rounded-full bg-brand-500"></div>
                </a>

                <a href="pengembalian.html" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-archive-in text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Pengembalian</span>
                </a>

                <a href="pelanggan.html" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-group text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Pelanggan UMKM</span>
                </a>

                <a href="pengaturan.html" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-cog text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Pengaturan Sistem</span>
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
                    <span class="text-lg font-medium text-white hidden sm:block capitalize">Daftar Transaksi</span>
                </div>

                <div class="flex items-center gap-3 sm:gap-5 text-white">
                    <div class="flex items-center gap-3 pl-3 sm:pl-5 relative before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-8 before:w-px before:bg-brand-400">
                        <div class="hidden sm:flex flex-col text-right justify-center">
                            <span class="text-sm font-semibold leading-tight">Admin</span>
                            <span class="text-[10px] text-brand-400 font-medium mt-0.5 uppercase tracking-wider">SEWAIN</span>
                        </div>
                        <button class="h-9 w-9 overflow-hidden rounded-full bg-white/10 hover:bg-white/20 border border-white/20 transition flex items-center justify-center focus:outline-none">
                            <i class='bx bx-user text-xl text-white'></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto w-full p-6">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <h2 class="text-2xl font-bold text-gray-800">Daftar Transaksi</h2>
                        
                        <form method="GET" action="transaksi.php" class="flex items-center gap-3">
                            <div class="relative">
                                <i class='bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg'></i>
                                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari invoice atau UMKM..." class="pl-10 pr-4 py-2 w-full sm:w-64 rounded-xl border border-gray-200 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                            </div>
                            <button type="submit" class="bg-brand-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-brand-600 transition-colors">Cari</button>
                        </form>
                    </div>

                    <!-- Data Table -->
                    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left text-sm text-gray-500">
                                <thead class="bg-gray-50/50 text-xs uppercase text-gray-400 border-b border-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Invoice & UMKM</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Item Disewa</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Periode</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Metode</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Total</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php if (mysqli_num_rows($query) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($query)): ?>
                                            <?php
                                                $id_sewa = $row['idsewa'];
                                                $itemQuery = mysqli_query($conn, "SELECT pd.*, a.nama_alat FROM penyewaan_detail pd JOIN alat a ON pd.idalat = a.idalat WHERE pd.idsewa = $id_sewa");
                                                $items = [];
                                                while($item = mysqli_fetch_assoc($itemQuery)) {
                                                    $items[] = "{$item['jumlah']}x {$item['nama_alat']}";
                                                }
                                                $firstItem = array_shift($items);
                                                $otherCount = count($items);
                                                
                                                $statusColor = "gray";
                                                if($row['status'] == 'pending') $statusColor = "orange";
                                                elseif($row['status'] == 'disewa') $statusColor = "blue";
                                                elseif($row['status'] == 'selesai') $statusColor = "green";
                                                elseif($row['status'] == 'batal') $statusColor = "red";
                                            ?>
                                            <tr class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <span class="block font-medium text-gray-900 border-b border-gray-100 pb-1 mb-1">#<?= str_pad($row['idsewa'], 4, '0', STR_PAD_LEFT) ?></span>
                                                    <span class="text-xs text-gray-500"><?= htmlspecialchars($row['nama_pelanggan']) ?></span>
                                                </td>
                                                <td class="px-6 py-4 text-xs">
                                                    <div class="font-medium text-gray-700"><?= htmlspecialchars($firstItem) ?></div>
                                                    <?php if($otherCount > 0): ?>
                                                        <div class="text-gray-400">+<?= $otherCount ?> Item lainnya</div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 text-xs">
                                                    <span class="block whitespace-nowrap"><i class='bx bx-calendar mr-1'></i><?= date('d M y', strtotime($row['tanggal_mulai'])) ?> - <?= date('d M y', strtotime($row['tanggal_selesai'])) ?></span>
                                                    <span class="block text-gray-400 mt-1">(<?= $row['durasi'] ?> Hari)</span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-[10px] font-semibold text-gray-600 uppercase">
                                                        <i class='bx <?= $row['metode_pengiriman'] == "Ambil Sendiri" ? 'bx-store' : 'bx-truck' ?>'></i> <?= $row['metode_pengiriman'] ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 font-medium text-gray-900">
                                                    Rp <?= number_format($row['total_biaya'], 0, ',', '.') ?>
                                                    <?php if($row['ongkir'] > 0): ?>
                                                        <span class="text-[10px] items-center text-gray-500 block font-normal">(inc. Ongkir)</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-<?= $statusColor ?>-100 px-2.5 py-1 text-xs font-semibold text-<?= $statusColor ?>-600 capitalize">
                                                        <?= $row['status'] ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button @click="statusData = { id: '<?= $row['idsewa'] ?>', current: '<?= $row['status'] ?>' }; statusModalOpen = true" 
                                                            class="rounded-lg bg-brand-50 text-brand-600 px-3 py-1.5 text-xs font-semibold hover:bg-brand-100 transition-colors focus:outline-none border border-brand-100">
                                                            Ubah Status
                                                        </button>
                                                        <a href="detail_transaksi.php?id=<?= $row['idsewa'] ?>" class="rounded-lg bg-gray-50 text-gray-600 px-3 py-1.5 text-xs font-medium hover:bg-gray-100 transition-colors focus:outline-none border border-gray-200" title="Detail">
                                                            <i class='bx bx-show'></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-10 text-center text-gray-400 italic">Belum ada transaksi ditemukan.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="bg-gray-50/50 p-4 sm:flex sm:items-center sm:justify-between border-t border-gray-100 rounded-b-2xl">
                            <p class="text-sm text-gray-500">Menampilkan <span class="font-medium text-gray-700"><?= min($start + 1, $total_transaksi) ?></span> sampai <span class="font-medium text-gray-700"><?= min($start + $limit, $total_transaksi) ?></span> dari <span class="font-medium text-gray-700"><?= $total_transaksi ?></span> transaksi</p>
                            <div class="mt-4 sm:mt-0 flex gap-2">
                                <?php if($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">Prev</a>
                                <?php else: ?>
                                    <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-300 cursor-not-allowed" disabled>Prev</button>
                                <?php endif; ?>

                                <?php if($page < $total_page): ?>
                                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">Next</a>
                                <?php else: ?>
                                    <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-300 cursor-not-allowed" disabled>Next</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="bg-[#f8f9fa] px-6 py-4 text-center sm:text-right text-[13px] text-gray-400 font-medium shrink-0">
                &copy; 2026 Admin Dashboard. All rights reserved.
            </footer>
        </div>
        
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-brand-700/50 backdrop-blur-sm transition-opacity md:hidden" x-transition.opacity></div>
    </div>

    <!-- Modal Ubah Status -->
    <div 
        x-show="statusModalOpen" 
        class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
    >
        <div 
            @click.outside="statusModalOpen = false"
            class="w-full max-w-sm rounded-2xl bg-white shadow-xl ring-1 ring-gray-200/50 overflow-hidden"
            x-show="statusModalOpen" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        >
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-800">Update Status</h3>
                <button @click="statusModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <i class='bx bx-x text-2xl'></i>
                </button>
            </div>

            <form action="transaksi.php" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id_sewa" :value="statusData.id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Pesanan</label>
                    <div class="grid grid-cols-1 gap-2">
                        <label class="relative flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 transition hover:bg-gray-50 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                            <input type="radio" name="status_baru" value="pending" :checked="statusData.current == 'pending'" class="accent-orange-500">
                            <span class="text-sm font-semibold text-orange-600">Pending</span>
                        </label>
                        <label class="relative flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 transition hover:bg-gray-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="status_baru" value="disewa" :checked="statusData.current == 'disewa'" class="accent-blue-500">
                            <span class="text-sm font-semibold text-blue-600">Sedang Disewa</span>
                        </label>
                        <label class="relative flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 transition hover:bg-gray-50 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                            <input type="radio" name="status_baru" value="selesai" :checked="statusData.current == 'selesai'" class="accent-green-500">
                            <span class="text-sm font-semibold text-green-600">Selesai</span>
                        </label>
                        <label class="relative flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 transition hover:bg-gray-50 has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                            <input type="radio" name="status_baru" value="batal" :checked="statusData.current == 'batal'" class="accent-red-500">
                            <span class="text-sm font-semibold text-red-600">Dibatalkan</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="statusModalOpen = false" class="flex-1 rounded-xl border border-gray-300 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" name="update_status" class="flex-1 rounded-xl bg-brand-500 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Notifikasi Update
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil_update'): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Diperbarui!',
            text: 'Status transaksi telah berhasil diubah.',
            confirmButtonColor: '#3b82f6',
            timer: 2000,
            timerProgressBar: true
        }).then(() => {
            window.history.replaceState(null, null, window.location.pathname);
        });
        <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'gagal_update'): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat mengubah status.',
            confirmButtonColor: '#ef4444'
        }).then(() => {
            window.history.replaceState(null, null, window.location.pathname);
        });
        <?php endif; ?>
    </script>
</body>
</html>
