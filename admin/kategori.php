<?php
    include 'auth_check.php';
    include '../config.php';

    //pagination
    $limit = 5;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $start = ($page - 1) * $limit;

    $countQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM kategori");
    $countRow = mysqli_fetch_assoc($countQuery);
    $total_kategori = $countRow['total'];
    $total_page = ceil($total_kategori / $limit);

    //get data
    $query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY idkategori DESC LIMIT $start, $limit");
    
    $no = $start + 1;

    //insert
    if (isset($_POST['tambah_kategori'])) {
        $nama_kategori = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
        $icon = mysqli_real_escape_string($conn, $_POST['icon']);
        
        $insert = mysqli_query($conn, "INSERT INTO kategori (kategori, icon) VALUES ('$nama_kategori', '$icon')");
        if ($insert) {
            header("Location: kategori.php?pesan=berhasil_tambah");
            exit;
        } else {
            header("Location: kategori.php?pesan=gagal_tambah");
            exit;
        }
    }

    //update
    if (isset($_POST['update_kategori'])) {
        $idkategori = mysqli_real_escape_string($conn, $_POST['idkategori']);
        $nama_kategori = mysqli_real_escape_string($conn, $_POST['edit_nama_kategori']);
        $icon = mysqli_real_escape_string($conn, $_POST['edit_icon']);
        
        $update = mysqli_query($conn, "UPDATE kategori SET kategori='$nama_kategori', icon='$icon' WHERE idkategori='$idkategori'");
        if ($update) {
            header("Location: kategori.php?pesan=berhasil_update");
            exit;
        } else {
            header("Location: kategori.php?pesan=gagal_update");
            exit;
        }
    }

    //delete
    if (isset($_GET['hapus_id'])) {
        $idkategori = mysqli_real_escape_string($conn, $_GET['hapus_id']);
        
        $delete = mysqli_query($conn, "DELETE FROM kategori WHERE idkategori='$idkategori'");
        if ($delete) {
            header("Location: kategori.php?pesan=berhasil_hapus");
            exit;
        } else {
            header("Location: kategori.php?pesan=gagal_hapus");
            exit;
        }
    }

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kategori | Admin Dashboard</title>

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
<body class="bg-[#f8f9fa] font-sans text-gray-800 antialiased" x-data="{ sidebarOpen: true }">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside 
            class="fixed inset-y-0 left-0 z-50 flex flex-col bg-brand-500 text-white transition-all duration-300 ease-in-out md:static md:block shrink-0 shadow-xl"
            :class="sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full md:translate-x-0 md:w-20'">
            
            <!-- Sidebar Header -->
            <div class="flex h-[72px] items-center justify-center px-4 shrink-0 font-bold tracking-wider">
                <!-- Full Logo -->
                <h1 x-show="sidebarOpen" class="text-2xl w-full text-center" x-transition.opacity>Admin</h1>
                <!-- Mini Logo -->
                <h1 x-show="!sidebarOpen" class="text-2xl hidden md:block" x-cloak>A</h1>
            </div>

            <!-- Sidebar Nav -->
            <nav class="flex-1 space-y-2 overflow-y-auto px-3 py-4 scrollbar-hide">

                <a href="dashboardAdmin.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-grid-alt text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Dashboard</span>
                </a>

                <a href="kategori.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none bg-white text-brand-700 shadow-sm" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-layer text-xl shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Kategori</span>
                    <div x-show="sidebarOpen" class="ml-auto flex h-2 w-2 shrink-0 rounded-full bg-brand-500"></div>
                </a>

                <a href="alat.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-wrench text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Alat Produksi</span>
                </a>

                <a href="transaksi.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-shopping-bag text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Daftar Transaksi</span>
                </a>

                <a href="pengembalianAdmin.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all text-white hover:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-archive-in text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Pengembalian</span>
                </a>

                <a href="cetak_laporan.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-archive-in text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Cetak Laporan</span>
                </a>

            </nav>

            <!-- Sidebar Footer -->
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
                    <!-- Hamburger Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" class="text-white hover:bg-brand-600 rounded-lg p-2 transition focus:outline-none">
                        <i class='bx bx-menu text-2xl'></i>
                    </button>
                    <!-- Current Page Title -->
                    <span class="text-lg font-medium text-white hidden sm:block capitalize">
                        Kategori
                    </span>
                </div>

                <!-- Right Side Topbar -->
                <div class="flex items-center gap-3 sm:gap-5 text-white">
                    <button class="relative rounded-lg p-2 hover:bg-brand-600 transition focus:outline-none">
                        <i class='bx bx-bell text-xl'></i>
                        <span class="absolute right-1.5 top-1.5 flex h-2.5 w-2.5 items-center justify-center rounded-full bg-[#f9db72] border-2 border-brand-500"></span>
                    </button>
                    
                    <div class="flex items-center gap-3 pl-3 sm:pl-5 relative before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-8 before:w-px before:bg-brand-400">
                        <div class="hidden sm:flex flex-col text-right justify-center">
                            <span class="text-sm font-semibold leading-tight"><?= htmlspecialchars($_SESSION['nama']) ?></span>
                            <span class="text-[10px] text-brand-400 font-medium mt-0.5 uppercase tracking-wider">ADMIN</span>
                        </div>
                        <button class="h-9 w-9 overflow-hidden rounded-full bg-white/10 hover:bg-white/20 border border-white/20 transition flex items-center justify-center focus:outline-none">
                            <!-- Could be image -->
                            <i class='bx bx-user text-xl text-white'></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto w-full">
                <!-- Inner padding wrapper -->
                <div class="mx-auto w-full p-6">
                    
                <div x-data="{ modalOpen: false, editModalOpen: false, editData: { id: '', nama: '', icon: '' } }">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Daftar Kategori</h2>
                        <button @click="modalOpen = true" class="flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 shadow-sm">
                            <i class='bx bx-plus text-lg'></i>
                            <span>Tambah Kategori</span>
                        </button>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left text-sm text-gray-500 whitespace-nowrap">
                                <thead class="bg-gray-50/50 text-xs uppercase text-gray-400 border-b border-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-center w-16">No</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Kategori</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-center w-28">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-gray-700 border-b border-gray-100">
                                    <?php
                                    if (mysqli_num_rows($query) > 0) {
                                        while ($row = mysqli_fetch_array($query)) {
                                            $colorClass = "bg-indigo-50 text-indigo-600";
                                            if ($row['idkategori'] % 3 == 0) {
                                                $colorClass = "bg-emerald-50 text-emerald-600";
                                            } elseif ($row['idkategori'] % 2 == 0) {
                                                $colorClass = "bg-orange-50 text-orange-600";
                                            }
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-900 text-center"><?php echo $no++; ?></td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg shadow-sm <?php echo $colorClass; ?>">
                                                    <i class='bx <?php echo empty($row['icon']) ? 'bx-layer' : htmlspecialchars($row['icon']); ?> text-xl'></i>
                                                </div>
                                                <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['kategori']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" @click="editData = { id: '<?php echo htmlspecialchars($row['idkategori'] ?? '', ENT_QUOTES); ?>', nama: '<?php echo htmlspecialchars($row['kategori'] ?? '', ENT_QUOTES); ?>', icon: '<?php echo htmlspecialchars($row['icon'] ?? '', ENT_QUOTES); ?>' }; editModalOpen = true" class="rounded-lg p-2 text-blue-500 bg-blue-50 hover:bg-blue-100 hover:text-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1" title="Edit">
                                                    <i class='bx bx-edit text-xl'></i>
                                                </button>
                                                <button onclick="konfirmasiHapus('<?php echo htmlspecialchars($row['idkategori'] ?? '', ENT_QUOTES); ?>')" type="button" class="rounded-lg p-2 text-red-500 bg-red-50 hover:bg-red-100 hover:text-red-600 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1" title="Hapus">
                                                    <i class='bx bx-trash text-xl'></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-10 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class='bx bx-folder-open text-4xl text-gray-300 mb-2'></i>
                                                <span class="text-sm">Belum ada kategori yang ditambahkan.</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php 
                                    } 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="bg-gray-50/50 p-4 sm:flex sm:items-center sm:justify-between border-t border-gray-100 rounded-b-2xl">
                            <p class="text-sm text-gray-500">
                                Menampilkan <span class="font-medium text-gray-700"><?php echo mysqli_num_rows($query) > 0 ? $start + 1 : 0; ?></span> 
                                sampai <span class="font-medium text-gray-700"><?php echo min($start + mysqli_num_rows($query), $total_kategori); ?></span> 
                                dari total <span class="font-medium text-gray-700"><?php echo $total_kategori; ?></span> kategori
                            </p>
                            <div class="mt-4 sm:mt-0 flex gap-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">Sebelumnya</a>
                                <?php else: ?>
                                    <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed focus:outline-none" disabled>Sebelumnya</button>
                                <?php endif; ?>

                                <?php if ($page < $total_page): ?>
                                    <a href="?page=<?php echo $page + 1; ?>" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">Selanjutnya</a>
                                <?php else: ?>
                                    <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed focus:outline-none" disabled>Selanjutnya</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Form Kategori -->
                    <div 
                        x-show="modalOpen" 
                        class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        style="display: none;"
                    >
                        <div 
                            @click.outside="modalOpen = false"
                            class="w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-gray-200/50"
                            x-show="modalOpen" 
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                                <h3 class="text-lg font-bold text-gray-800">Tambah Kategori</h3>
                                <button @click="modalOpen = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition focus:outline-none">
                                    <i class='bx bx-x text-2xl'></i>
                                </button>
                            </div>
                            
                            <form action="" method="POST">
                                <div class="space-y-4 px-6 py-4">
                                    
                                    <div>
                                        <label for="nama_kategori" class="mb-1 block text-sm font-medium text-gray-700">Nama Kategori</label>
                                        <input type="text" id="nama_kategori" name="nama_kategori" class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Masukkan nama kategori" required>
                                    </div>

                                    <div>
                                        <label for="icon" class="mb-1 block text-sm font-medium text-gray-700">Icon Class (Boxicons)</label>
                                        <input type="text" id="icon" name="icon" class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Contoh: bx-laptop">
                                        <p class="mt-1 text-xs text-gray-500">Gunakan class icon dari <a href="https://boxicons.com/icons?free=true" target="_blank" class="text-brand-500 hover:underline">Boxicons</a>.</p>
                                    </div>

                                </div>
                                
                                <div class="flex items-center gap-2 border-t border-gray-100 px-6 py-4 justify-end bg-gray-50/50 rounded-b-2xl">
                                    <button type="button" @click="modalOpen = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                                        Batal
                                    </button>
                                    <button type="submit" name="tambah_kategori" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all shadow-sm">
                                        Simpan Kategori
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal Form Update Kategori -->
                    <div 
                        x-show="editModalOpen" 
                        class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        style="display: none;"
                    >
                        <div 
                            @click.outside="editModalOpen = false"
                            class="w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-gray-200/50"
                            x-show="editModalOpen" 
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                                <h3 class="text-lg font-bold text-gray-800">Update Kategori</h3>
                                <button type="button" @click="editModalOpen = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition focus:outline-none">
                                    <i class='bx bx-x text-2xl'></i>
                                </button>
                            </div>

                            <form action="" method="POST">
                                <input type="hidden" name="idkategori" x-model="editData.id">
                                <div class="space-y-4 px-6 py-4">

                                    <div>
                                        <label for="edit_nama_kategori" class="mb-1 block text-sm font-medium text-gray-700">Nama Kategori</label>
                                        <input type="text" id="edit_nama_kategori" name="edit_nama_kategori" x-model="editData.nama" class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Masukkan nama kategori" required>
                                    </div>

                                    <div>
                                        <label for="edit_icon" class="mb-1 block text-sm font-medium text-gray-700">Icon Class (Boxicons)</label>
                                        <input type="text" id="edit_icon" name="edit_icon" x-model="editData.icon" class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Contoh: bx-laptop">
                                    </div>

                                </div>

                                <div class="flex items-center gap-2 border-t border-gray-100 px-6 py-4 justify-end bg-gray-50/50 rounded-b-2xl">
                                    <button type="button" @click="editModalOpen = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                                        Batal
                                    </button>
                                    <button type="submit" name="update_kategori" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all shadow-sm">
                                        Update Kategori
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-[#f8f9fa] px-6 py-4 text-center sm:text-right text-[13px] text-gray-400 font-medium shrink-0">
                &copy; 2026 Admin Dashboard. All rights reserved.
            </footer>
        </div>
        
        <!-- Overlay on mobile to close sidebar -->
        <div 
            x-show="sidebarOpen" 
            @click="sidebarOpen = false" 
            class="fixed inset-0 z-40 bg-brand-700/50 backdrop-blur-sm transition-opacity md:hidden" 
            x-transition.opacity>
        </div>

    </div>

    <script>
        //notifikasi insert
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil_tambah'): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Kategori berhasil ditambahkan.',
            confirmButtonColor: '#3b82f6',
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            window.history.replaceState(null, null, window.location.pathname);
        });
        <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'gagal_tambah'): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Gagal menambahkan kategori baru.',
            confirmButtonColor: '#ef4444'
        }).then(() => {
            window.history.replaceState(null, null, window.location.pathname);
        });

        //notifikasi update
        <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil_update'): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Kategori berhasil diupdate.',
            confirmButtonColor: '#3b82f6',
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            window.history.replaceState(null, null, window.location.pathname);
        });

        <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'gagal_update'): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Gagal mengupdate kategori.',
            confirmButtonColor: '#ef4444'
        }).then(() => {
            window.history.replaceState(null, null, window.location.pathname);
        });

        //notifikasi hapus
        <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil_hapus'): ?>
        Swal.fire({
            icon: 'success',
            title: 'Terhapus!',
            text: 'Kategori berhasil dihapus.',
            confirmButtonColor: '#3b82f6',
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            window.history.replaceState(null, null, window.location.pathname);
        });
        <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'gagal_hapus'): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Gagal menghapus kategori.',
            confirmButtonColor: '#ef4444'
        }).then(() => {
            window.history.replaceState(null, null, window.location.pathname);
        });
        <?php endif; ?>

        //konfirmasi hapus
        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Kategori yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'kategori.php?hapus_id=' + id;
                }
            })
        }
    </script>

</body>
</html>
