<?php
    include 'auth_check.php';
    include '../config.php';

    // 1. Menunggu Diambil (Pending)
    $qPending = mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE status='pending'");
    $pending = mysqli_fetch_assoc($qPending)['total'] ?? 0;

    // 2. Transaksi Sedang Disewa
    $qDisewa = mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE status='disewa'");
    $disewa = mysqli_fetch_assoc($qDisewa)['total'] ?? 0;

    // 3. Terlambat (Disewa tapi melewati tanggal selesai)
    // Assume if status='disewa' and tanggal_selesai is less than today it is overdue
    $qTerlambat = mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE status='disewa' AND tanggal_selesai < CURDATE()");
    $terlambat = mysqli_fetch_assoc($qTerlambat)['total'] ?? 0;

    // 4. Total UMKM
    // Check if column role exists. If not just count user
    $qUser = mysqli_query($conn, "SELECT COUNT(*) as total FROM user WHERE role='user'");
    if($qUser) {
        $userTotal = mysqli_fetch_assoc($qUser)['total'] ?? 0;
    } else {
        $qUserFallback = mysqli_query($conn, "SELECT COUNT(*) as total FROM user");
        $userTotal = mysqli_fetch_assoc($qUserFallback)['total'] ?? 0;
    }

    // 5. Status Alat Produksi
    $qStok = mysqli_query($conn, "SELECT SUM(stok) as total FROM alat");
    $total_tersedia = mysqli_fetch_assoc($qStok)['total'] ?? 0;

    $qStokSewa = mysqli_query($conn, "SELECT SUM(pd.jumlah) as total FROM penyewaan_detail pd JOIN penyewaan p ON pd.idsewa = p.idsewa WHERE p.status='disewa'");
    $total_disewa = 0;
    if($qStokSewa) {
         $total_disewa = mysqli_fetch_assoc($qStokSewa)['total'] ?? 0;
    }
    
    $total_alat = $total_tersedia + $total_disewa;
    $persen_tersedia = $total_alat > 0 ? round(($total_tersedia / $total_alat) * 100) : 0;
    $persen_disewa = $total_alat > 0 ? round(($total_disewa / $total_alat) * 100) : 0;

    // 6. Recent Transactions
    $qRecent = mysqli_query($conn, "
        SELECT s.*, u.nama as nama_pelanggan 
        FROM penyewaan s 
        JOIN user u ON s.iduser = u.id_user 
        ORDER BY s.idsewa DESC 
        LIMIT 4
    ");

    // 7. Chart Data (Penyewaan per bulan tahun ini)
    $qChart = mysqli_query($conn, "
        SELECT MONTH(tanggal_mulai) as bulan_num, COUNT(idsewa) as total
        FROM penyewaan
        WHERE YEAR(tanggal_mulai) = YEAR(CURDATE())
        GROUP BY MONTH(tanggal_mulai)
    ");
    $chartData = array_fill(1, 12, 0);
    if($qChart) {
        while($row = mysqli_fetch_assoc($qChart)) {
            $chartData[(int)$row['bulan_num']] = (int)$row['total'];
        }
    }

    $chartDataJs = json_encode(array_values($chartData));

    $dataLabel = [];
    $dataValue = [];

    $query = mysqli_query($conn, "
        SELECT k.kategori, COUNT(a.idalat) as total
        FROM kategori k
        LEFT JOIN alat a ON k.idkategori = a.idkategori
        GROUP BY k.idkategori
    ");

    while ($row = mysqli_fetch_assoc($query)) {
        $dataLabel[] = $row['kategori'];
        $dataValue[] = $row['total'];
    }

    $labelJson = json_encode($dataLabel);
    $valueJson = json_encode($dataValue);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Admin Dashboard</title>

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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#f8f9fa] font-sans text-gray-800 antialiased" x-data="{ sidebarOpen: true }">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside 
            class="fixed inset-y-0 left-0 z-50 flex flex-col bg-brand-500 text-white transition-all duration-300 ease-in-out md:static md:block shrink-0 shadow-xl"
            :class="sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full md:translate-x-0 md:w-20'">
            
            <!-- Sidebar Header -->
            <div class="flex h-[72px] items-center justify-center px-4 shrink-0 font-bold tracking-wider">
                <h1 x-show="sidebarOpen" class="text-2xl w-full text-center" x-transition.opacity>Admin</h1>
                <h1 x-show="!sidebarOpen" class="text-2xl hidden md:block" x-cloak>A</h1>
            </div>

            <!-- Sidebar Nav -->
            <nav class="flex-1 space-y-2 overflow-y-auto px-3 py-4 scrollbar-hide">
                <a href="dashboardAdmin.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none bg-white text-brand-700 shadow-sm" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-grid-alt text-xl shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Dashboard</span>
                    <div x-show="sidebarOpen" class="ml-auto flex h-2 w-2 shrink-0 rounded-full bg-brand-500"></div>
                </a>

                <a href="kategori.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-layer text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Kategori</span>
                </a>

                <a href="alat.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-wrench text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Alat Produksi</span>
                </a>

                <a href="transaksi.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-shopping-bag text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Daftar Transaksi</span>
                </a>

                <a href="pengembalianAdmin.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-archive-in text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Pengembalian</span>
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
                    <span class="text-lg font-medium text-white hidden sm:block capitalize">Dashboard</span>
                </div>

                <div class="flex items-center gap-3 sm:gap-5 text-white">
                    <div class="flex items-center gap-3 pl-3 sm:pl-5 relative before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-8 before:w-px before:bg-brand-400">
                        <div class="hidden sm:flex flex-col text-right justify-center">
                            <span class="text-sm font-semibold leading-tight"><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></span>
                            <span class="text-[10px] text-brand-400 font-medium mt-0.5 uppercase tracking-wider">ADMIN</span>
                        </div>
                        <button class="h-9 w-9 overflow-hidden rounded-full bg-white/10 hover:bg-white/20 border border-white/20 transition flex items-center justify-center focus:outline-none">
                            <i class='bx bx-user text-xl text-white'></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto w-full p-6 bg-[#f8f9fa]">
                <div class="mx-auto w-full">

                    <!-- Dashboard Stats Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Stat Card 1 -->
                        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-shadow hover:shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Menunggu Diambil</p>
                                    <h3 class="mt-2 text-3xl font-bold text-gray-800"><?= $pending ?></h3>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-2xl text-orange-500 shadow-inner">
                                    <i class='bx bx-time-five'></i>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-2 text-sm">
                                <span class="flex items-center text-orange-500 font-medium bg-orange-50 px-2 py-0.5 rounded-md text-xs">Pesanan Pending</span>
                            </div>
                        </div>

                        <!-- Stat Card 2 -->
                        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-shadow hover:shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Alat Sedang Disewa</p>
                                    <h3 class="mt-2 text-3xl font-bold text-gray-800"><?= $disewa ?></h3>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-2xl text-brand-600 shadow-inner">
                                    <i class='bx bx-shopping-bag'></i>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-2 text-sm">
                                <span class="flex items-center text-brand-600 font-medium bg-brand-50 px-2 py-0.5 rounded-md text-xs">Penyewaan Aktif</span>
                            </div>
                        </div>

                        <!-- Stat Card 3 -->
                        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-shadow hover:shadow-md <?php if($terlambat > 0) echo 'border-b-4 border-red-500'; ?>">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Belum Dikembalikan</p>
                                    <h3 class="mt-2 text-3xl font-bold <?= $terlambat > 0 ? 'text-red-600' : 'text-gray-800' ?>"><?= $terlambat ?></h3>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl <?= $terlambat > 0 ? 'bg-red-50 text-red-500 shadow-md' : 'bg-gray-50 text-gray-400 shadow-inner' ?>">
                                    <i class='bx bx-error-circle'></i>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-2 text-sm">
                                <?php if($terlambat > 0): ?>
                                <span class="flex items-center text-red-500 font-medium bg-red-50 px-2 py-0.5 rounded-md text-[11px]">Terlambat</span>
                                <span class="text-gray-400 text-[11px]">Segera tindak lanjuti</span>
                                <?php else: ?>
                                <span class="text-emerald-500 font-medium bg-emerald-50 px-2 py-0.5 rounded-md text-[11px]">Aman</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Stat Card 4 -->
                        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-shadow hover:shadow-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Total UMKM</p>
                                    <h3 class="mt-2 text-3xl font-bold text-gray-800"><?= $userTotal ?></h3>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-500 shadow-inner">
                                    <i class='bx bx-group'></i>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-2 text-sm">
                                <span class="text-gray-400 text-xs">Pelanggan Terdaftar</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

                        <!-- Line Chart (lebih lebar) -->
                        <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm ring-1 ring-gray-100">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">
                                Statistik Penyewaan (<?= date('Y') ?>)
                            </h2>
                            <div class="relative h-80 w-full">
                                <canvas id="rentalsChart"></canvas>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="bg-white rounded-3xl p-6 shadow-sm ring-1 ring-gray-100">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">
                                Statistik Kategori
                            </h2>
                            <div class="relative h-80 w-full">
                                <canvas id="myPieChart"></canvas>
                            </div>
                        </div>

                    </div>

                    <!-- Analytics & Recent Transactions Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        <!-- Recent Transactions Table (Takes 2 columns) -->
                        <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm ring-1 ring-gray-100">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold text-gray-800">Penyewaan Terbaru</h2>
                                <a href="transaksi.php" class="text-sm font-medium text-brand-500 hover:text-brand-600 focus:outline-none">Lihat Semua</a>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm text-gray-500">
                                    <thead class="bg-gray-50/50 text-xs uppercase text-gray-400 border-b border-gray-100">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 font-semibold tracking-wider">ID / Penyewa</th>
                                            <th scope="col" class="px-4 py-3 font-semibold tracking-wider">Tanggal Mulai</th>
                                            <th scope="col" class="px-4 py-3 font-semibold tracking-wider">Metode</th>
                                            <th scope="col" class="px-4 py-3 font-semibold tracking-wider text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <?php if(mysqli_num_rows($qRecent) > 0): ?>
                                            <?php while($row = mysqli_fetch_assoc($qRecent)): ?>
                                                <?php
                                                    $statusColor = "gray";
                                                    if($row['status'] == 'pending') $statusColor = "orange";
                                                    elseif($row['status'] == 'disewa') $statusColor = "blue";
                                                    elseif($row['status'] == 'selesai') $statusColor = "green";
                                                    elseif($row['status'] == 'dibatalkan') $statusColor = "red";
                                                ?>
                                                <tr class="hover:bg-gray-50/50 transition-colors">
                                                    <td class="px-4 py-3">
                                                        <div class="font-medium text-gray-900 border-b border-gray-100 pb-0.5 mb-0.5 inline-block">
                                                            INV-<?= str_pad($row['idsewa'], 4, '0', STR_PAD_LEFT) ?>
                                                        </div>
                                                        <div class="text-xs"><?= htmlspecialchars($row['nama_pelanggan']) ?></div>
                                                    </td>
                                                    <td class="px-4 py-3 text-xs">
                                                        <i class='bx bx-calendar mr-1 text-gray-400'></i> <?= date('d M Y', strtotime($row['tanggal_mulai'])) ?>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-1 text-[10px] font-semibold text-gray-600 uppercase">
                                                            <i class='bx <?= $row['metode_pengiriman'] == "Ambil Sendiri" ? 'bx-store' : 'bx-truck' ?>'></i> <?= $row['metode_pengiriman'] ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <span class="inline-block rounded-full bg-<?= $statusColor ?>-100 px-2.5 py-0.5 text-[10px] font-semibold uppercase text-<?= $statusColor ?>-600">
                                                            <?= $row['status'] ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Stock Analytics (Takes 1 column) -->
                        <div class="bg-brand-500 rounded-3xl p-6 shadow-sm ring-1 ring-brand-600 text-white flex flex-col justify-between relative overflow-hidden">
                            <!-- Background element -->
                            <i class='bx bx-archive-in absolute -right-6 -bottom-6 text-[150px] opacity-10'></i>

                            <div class="relative z-10">
                                <h2 class="text-xl font-bold mb-1">Status Alat Produksi</h2>
                                <p class="text-brand-200 text-sm mb-6">Ringkasan kondisi stok di gudang</p>

                                <div class="space-y-4">
                                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-sm font-medium">Tersedia Digudang</span>
                                            <span class="text-lg font-bold"><?= $total_tersedia ?> <span class="text-xs font-normal">unit</span></span>
                                        </div>
                                        <div class="w-full bg-brand-600 rounded-full h-1.5 mt-2">
                                            <div class="bg-white h-1.5 rounded-full" style="width: <?= $persen_tersedia ?>%"></div>
                                        </div>
                                    </div>

                                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-sm font-medium">Sedang Disewa</span>
                                            <span class="text-lg font-bold"><?= $total_disewa ?> <span class="text-xs font-normal">unit</span></span>
                                        </div>
                                        <div class="w-full bg-brand-600 rounded-full h-1.5 mt-2">
                                            <div class="bg-[#f9db72] h-1.5 rounded-full" style="width: <?= $persen_disewa ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <a href="alat.php" class="relative z-10 w-full mt-6 bg-white text-center text-brand-600 rounded-xl py-3 font-semibold text-sm hover:bg-brand-50 transition-colors focus:outline-none shadow-sm inline-block">
                                Lihat Detail Alat
                            </a>
                        </div>
                    </div>

                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-[#f8f9fa] px-6 py-4 text-center sm:text-right text-[13px] text-gray-400 font-medium shrink-0">
                &copy; 2026 Admin Dashboard. All rights reserved.
            </footer>
        </div>

        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-brand-700/50 backdrop-blur-sm transition-opacity md:hidden" x-transition.opacity></div>
    </div>

    <!-- Chart Configuration -->
    <script>
        const ctx = document.getElementById('rentalsChart').getContext('2d');
        const data = <?= $chartDataJs ?>;
        
        let gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)'); // brand-500 equivalent approx with opacity
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Total Penyewaan',
                    data: data,
                    borderColor: '#3b82f6', // Tailwind brand/blue color
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 12,
                        titleFont: { size: 13, family: "'Poppins', sans-serif" },
                        bodyFont: { size: 13, family: "'Poppins', sans-serif" },
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.parsed.y + ' Transaksi';
                            }
                        }
                    }
                },
                
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: Math.max(...data) + 2,
                        ticks: {
                            precision: 0,
                            font: { family: "'Poppins', sans-serif", size: 11 },
                            color: '#9ca3af',
                            stepSize: 1
                        },
                        grid: {
                            color: '#f3f4f6',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: { family: "'Poppins', sans-serif", size: 11 },
                            color: '#9ca3af'
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                layout: {
                padding: {
                    top: 10,
                    bottom: 0
                }
            }
            }
        });

        const labels = <?= $labelJson ?>;
        const dataPie = <?= $valueJson ?>;

        const ctxPie = document.getElementById('myPieChart').getContext('2d');

        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: dataPie,
                    backgroundColor: labels.map(() => 
                        '#' + Math.floor(Math.random()*16777215).toString(16)
                    )
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
