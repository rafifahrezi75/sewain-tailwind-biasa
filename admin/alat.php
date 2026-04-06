<?php
    include 'auth_check.php';
    include '../config.php';

    //pagination
    $limit = 5;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $start = ($page - 1) * $limit;

    $countQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM alat");
    $countRow = mysqli_fetch_assoc($countQuery);
    $total_alat = $countRow['total'];
    $total_page = ceil($total_alat / $limit);

    // get data alat join dengan kategori
    $query = mysqli_query($conn, "SELECT alat.*, kategori.kategori as nama_kategori, kategori.icon FROM alat LEFT JOIN kategori ON alat.idkategori = kategori.idkategori ORDER BY alat.idalat DESC LIMIT $start, $limit");

    // get kategori untuk dropdown
    $query_kat = mysqli_query($conn, "SELECT * FROM kategori ORDER BY kategori ASC");

    // tambah alat
    if (isset($_POST['simpan_alat'])) {
        $nama_alat = mysqli_real_escape_string($conn, $_POST['nama_alat']);
        $idkategori = mysqli_real_escape_string($conn, $_POST['idkategori']);
        $stok = (int)$_POST['stok'];
        $harga_sewa = (int)$_POST['harga_sewa'];
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        
        $status = ($stok > 0) ? 'tersedia' : 'kosong';

        $nama_file = "";
        if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != '') {
            $ekstensi_diperbolehkan = array('png','jpg','jpeg');
            $nama_file_asli = $_FILES['gambar']['name'];
            $x = explode('.', $nama_file_asli);
            $ekstensi = strtolower(end($x));
            $ukuran = $_FILES['gambar']['size'];
            $file_tmp = $_FILES['gambar']['tmp_name'];

            if(in_array($ekstensi, $ekstensi_diperbolehkan) === true){
                if($ukuran < 2044070){
                    $random_name = time() . '-' . uniqid() . '.' . $ekstensi;
                    move_uploaded_file($file_tmp, '../uploads/'.$random_name);
                    $nama_file = $random_name;
                }
            }
        }

        $insert = mysqli_query($conn, "INSERT INTO alat (idkategori, nama_alat, harga_sewa, stok, status, deskripsi, gambar) VALUES ('$idkategori', '$nama_alat', '$harga_sewa', '$stok', '$status', '$deskripsi', '$nama_file')");
        if ($insert) {
            header("Location: alat.php?pesan=berhasil_tambah");
            exit;
        } else {
            header("Location: alat.php?pesan=gagal_tambah");
            exit;
        }
    }

    // update alat
    if (isset($_POST['update_alat'])) {
        $id_edit = mysqli_real_escape_string($conn, $_POST['idalat_edit']);
        $nama_alat = mysqli_real_escape_string($conn, $_POST['nama_alat']);
        $idkategori = mysqli_real_escape_string($conn, $_POST['idkategori']);
        $stok = (int)$_POST['stok'];
        $harga_sewa = (int)$_POST['harga_sewa'];
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        
        $status = ($stok > 0) ? 'tersedia' : 'kosong';

        if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != '') {
            $ekstensi_diperbolehkan = array('png','jpg','jpeg');
            $nama_file_asli = $_FILES['gambar']['name'];
            $x = explode('.', $nama_file_asli);
            $ekstensi = strtolower(end($x));
            $ukuran = $_FILES['gambar']['size'];
            $file_tmp = $_FILES['gambar']['tmp_name'];

            if(in_array($ekstensi, $ekstensi_diperbolehkan) === true){
                if($ukuran < 2044070){
                    $random_name = time() . '-' . uniqid() . '.' . $ekstensi;
                    move_uploaded_file($file_tmp, '../uploads/'.$random_name);

                    // hapus gambar lama
                    $get_gambar = mysqli_query($conn, "SELECT gambar FROM alat WHERE idalat = '$id_edit'");
                    $data_gambar = mysqli_fetch_array($get_gambar);
                    if ($data_gambar && !empty($data_gambar['gambar'])) {
                        $path_file = '../uploads/' . $data_gambar['gambar'];
                        if(file_exists($path_file)){
                            unlink($path_file);
                        }
                    }
                    
                    $update = mysqli_query($conn, "UPDATE alat SET idkategori='$idkategori', nama_alat='$nama_alat', harga_sewa='$harga_sewa', stok='$stok', status='$status', deskripsi='$deskripsi', gambar='$random_name' WHERE idalat='$id_edit'");
                }
            }
        } else {
            // jika tidak ada upload gambar baru
            $update = mysqli_query($conn, "UPDATE alat SET idkategori='$idkategori', nama_alat='$nama_alat', harga_sewa='$harga_sewa', stok='$stok', status='$status', deskripsi='$deskripsi' WHERE idalat='$id_edit'");
        }
        
        if ($update ?? false) {
            header("Location: alat.php?pesan=berhasil_update");
            exit;
        } else {
            header("Location: alat.php?pesan=gagal_update");
            exit;
        }
    }

    // hapus alat
    if (isset($_GET['hapus_id'])) {
        $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus_id']);
        
        // hapus file gambar di uploads jika ada
        $get_gambar = mysqli_query($conn, "SELECT gambar FROM alat WHERE idalat = '$id_hapus'");
        $data_gambar = mysqli_fetch_array($get_gambar);
        if ($data_gambar && !empty($data_gambar['gambar'])) {
            $path_file = '../uploads/' . $data_gambar['gambar'];
            if(file_exists($path_file)){
                unlink($path_file);
            }
        }

        $delete = mysqli_query($conn, "DELETE FROM alat WHERE idalat = '$id_hapus'");
        if ($delete) {
            header("Location: alat.php?pesan=berhasil_hapus");
            exit;
        } else {
            header("Location: alat.php?pesan=gagal_hapus");
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alat Produksi | Admin Dashboard</title>

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

                <a href="kategori.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-layer text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Kategori</span>
                </a>

                <a href="alat.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none bg-white text-brand-700 shadow-sm" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-wrench text-xl shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Alat Produksi</span>
                    <div x-show="sidebarOpen" class="ml-auto flex h-2 w-2 shrink-0 rounded-full bg-brand-500"></div>
                </a>

                <a href="transaksi.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-shopping-bag text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Daftar Transaksi</span>
                </a>

                <a href="pengembalianAdmin.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all text-white hover:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-archive-in text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Pengembalian</span>
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
                        Alat Produksi
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
                    
                <div x-data="{ modalOpen: false, editModalOpen: false, editData: { id: '', nama: '', kategori: '', stok: '', harga: '', deskripsi: '' } }">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <h2 class="text-2xl font-bold text-gray-800">Daftar Alat Produksi</h2>
                        <div class="flex items-center gap-3">
                            <!-- Search bar -->
                            <div class="relative">
                                <i class='bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg'></i>
                                <input type="text" placeholder="Cari alat..." class="pl-10 pr-4 py-2 w-full sm:w-64 rounded-xl border border-gray-200 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                            </div>
                            
                            <button @click="modalOpen = true" class="flex shrink-0 items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 shadow-sm">
                                <i class='bx bx-plus text-lg'></i>
                                <span class="hidden sm:inline">Tambah Alat</span>
                            </button>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left text-sm text-gray-500">
                                <thead class="bg-gray-50/50 text-xs uppercase text-gray-400 border-b border-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Alat Produksi</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Kategori</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Total Stok</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Harga/Hari</th>
                                        <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-center w-36">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <?php
                                    $no = $start + 1;
                                    if (mysqli_num_rows($query) > 0) {
                                        while ($row = mysqli_fetch_array($query)) {
                                            $colorClass = "bg-indigo-50 text-indigo-600";
                                            if ($row['idkategori'] % 3 == 0) {
                                                $colorClass = "bg-emerald-50 text-emerald-600";
                                            } elseif ($row['idkategori'] % 2 == 0) {
                                                $colorClass = "bg-orange-50 text-orange-600";
                                            }
                                    ?>
                                    <tr class="hover:bg-brand-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <div class="h-10 w-10 shrink-0 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 overflow-hidden">
                                                    <?php if(!empty($row['gambar'])): ?>
                                                        <img src="../uploads/<?php echo htmlspecialchars($row['gambar']); ?>" alt="" class="h-full w-full object-cover">
                                                    <?php else: ?>
                                                        <i class='bx bx-image text-xl'></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <span class="block font-semibold text-gray-800 text-[15px]"><?php echo htmlspecialchars($row['nama_alat']); ?></span>
                                                    <span class="text-xs font-medium text-gray-400 max-w-[12rem] truncate block" title="<?php echo htmlspecialchars($row['deskripsi']); ?>"><?php echo htmlspecialchars($row['deskripsi']); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold <?php echo $colorClass; ?>">
                                                <i class='bx <?php echo empty($row['icon']) ? 'bx-layer' : htmlspecialchars($row['icon']); ?> text-sm'></i>
                                                <?php echo htmlspecialchars($row['nama_kategori'] ?? 'Tidak ada Kategori'); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if($row['status'] == 'tersedia' && $row['stok'] > 0): ?>
                                                <div class="flex flex-col">
                                                    <span class="font-semibold text-gray-700"><?php echo $row['stok']; ?> Unit</span>
                                                    <span class="mt-1 w-max rounded-md bg-emerald-100/80 px-2 py-0.5 text-[10px] font-bold tracking-wider text-emerald-700 uppercase">Tersedia</span>
                                                </div>
                                            <?php else: ?>
                                                <div class="flex flex-col">
                                                    <span class="font-semibold text-gray-400"><?php echo $row['stok']; ?> Unit</span>
                                                    <span class="mt-1 w-max rounded-md bg-red-100/80 px-2 py-0.5 text-[10px] font-bold tracking-wider text-red-700 uppercase">Kosong</span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-brand-600 font-bold whitespace-nowrap">Rp <?php echo number_format($row['harga_sewa'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="detail_alat.php?id=<?php echo htmlspecialchars($row['idalat'] ?? '', ENT_QUOTES); ?>" class="rounded-lg p-2 text-emerald-500 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-600 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1" title="Detail Alat">
                                                    <i class='bx bx-info-circle text-xl'></i>
                                                </a>
                                                <button type="button" @click="editData = { id: '<?php echo htmlspecialchars($row['idalat'] ?? '', ENT_QUOTES); ?>', nama: '<?php echo htmlspecialchars($row['nama_alat'] ?? '', ENT_QUOTES); ?>', kategori: '<?php echo htmlspecialchars($row['idkategori'] ?? '', ENT_QUOTES); ?>', stok: '<?php echo htmlspecialchars($row['stok'] ?? '', ENT_QUOTES); ?>', harga: '<?php echo htmlspecialchars($row['harga_sewa'] ?? '', ENT_QUOTES); ?>', deskripsi: '<?php echo htmlspecialchars(str_replace(array('\r', '\n'), array(' ', ' '), $row['deskripsi'] ?? ''), ENT_QUOTES); ?>' }; editModalOpen = true" class="rounded-lg p-2 text-blue-500 bg-blue-50 hover:bg-blue-100 hover:text-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1" title="Edit">
                                                    <i class='bx bx-edit text-xl'></i>
                                                </button>
                                                <button onclick="konfirmasiHapus('<?php echo htmlspecialchars($row['idalat'] ?? '', ENT_QUOTES); ?>')" type="button" class="rounded-lg p-2 text-red-500 bg-red-50 hover:bg-red-100 hover:text-red-600 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1" title="Hapus">
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
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class='bx bx-folder-open text-4xl text-gray-300 mb-2'></i>
                                                <span class="text-sm">Belum ada alat produksi yang ditambahkan.</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="bg-gray-50/50 p-4 sm:flex sm:items-center sm:justify-between border-t border-gray-100 rounded-b-2xl">
                            <p class="text-sm text-gray-500">
                                Menampilkan <span class="font-medium text-gray-700"><?php echo min($start + 1, $total_alat ?: 0); ?></span> 
                                sampai <span class="font-medium text-gray-700"><?php echo min($start + $limit, $total_alat); ?></span> 
                                dari <span class="font-medium text-gray-700"><?php echo $total_alat; ?></span> alat
                            </p>
                            <div class="mt-4 sm:mt-0 flex gap-2">
                                <?php if($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-colors">Sebelumnya</a>
                                <?php else: ?>
                                    <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed" disabled>Sebelumnya</button>
                                <?php endif; ?>

                                <?php if($page < $total_page): ?>
                                    <a href="?page=<?php echo $page + 1; ?>" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-colors">Selanjutnya</a>
                                <?php else: ?>
                                    <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed" disabled>Selanjutnya</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Form Tambah Alat -->
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
                            class="w-full max-w-2xl rounded-2xl bg-white shadow-xl ring-1 ring-gray-200/50 max-h-[90vh] flex flex-col"
                            x-show="modalOpen" 
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 shrink-0">
                                <h3 class="text-lg font-bold text-gray-800">Tambah Alat Produksi Baru</h3>
                                <button @click="modalOpen = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition focus:outline-none">
                                    <i class='bx bx-x text-2xl'></i>
                                </button>
                            </div>

                                <form id="formTambahAlat" action="alat.php" method="POST" enctype="multipart/form-data" class="flex flex-col h-full" x-data="{ fileName: '' }">
                                    <div class="overflow-y-auto p-6 space-y-4 flex-1">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-700">Nama Alat</label>
                                                <input type="text" name="nama_alat" required class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" placeholder="Misal: Mesin Press Cup">
                                            </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
                                            <select name="idkategori" required class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                                <option disabled selected value="">Pilih Kategori...</option>
                                                <?php 
                                                if(mysqli_num_rows($query_kat) > 0){
                                                    while($k = mysqli_fetch_array($query_kat)){
                                                        echo '<option value="' . $k['idkategori'] . '">' . htmlspecialchars($k['kategori']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Total Stok</label>
                                        <input type="number" name="stok" min="0" required class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" placeholder="0">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Harga Sewa / Hari (Rp)</label>
                                        <input type="number" name="harga_sewa" min="0" step="1000" required class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" placeholder="50000">
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                                    <textarea rows="3" name="deskripsi" required class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" placeholder="Spesifikasi atau catatan khusus terkait alat ini..."></textarea>
                                </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Foto Alat</label>
                                        <div class="mt-1 flex justify-center rounded-xl border border-dashed border-gray-300 px-6 py-6 transition-colors hover:bg-gray-50 relative overflow-hidden group">
                                            
                                            <!-- File Name Display (Tampil saat fileName ADA isinya) -->
                                            <template x-if="fileName">
                                                <div class="absolute inset-0 z-10 bg-white flex flex-col items-center justify-center p-4">
                                                    <div class="flex items-center gap-3 bg-brand-50 border border-brand-100 rounded-lg px-4 py-3 w-full max-w-sm">
                                                        <i class='bx bxs-file-image text-3xl text-brand-500'></i>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="fileName"></p>
                                                            <p class="text-[11px] text-gray-500 font-medium">Image selected</p>
                                                        </div>
                                                        <button type="button" @click="fileName = ''; document.getElementById('file-upload').value = '';" class="text-gray-400 hover:text-red-500 transition-colors focus:outline-none" title="Hapus foto">
                                                            <i class='bx bx-trash text-xl'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Form Upload Asli (Tampil saat KOSONG) -->
                                            <div class="text-center" x-show="!fileName">
                                                <i class='bx bx-image-add text-4xl text-gray-400'></i>
                                                <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                                    <label for="file-upload" class="relative cursor-pointer rounded-md bg-transparent font-semibold text-brand-500 hover:text-brand-600 focus-within:outline-none">
                                                        <span>Upload file</span>
                                                        <input id="file-upload" name="gambar" type="file" required accept="image/png, image/jpeg, image/jpg" class="sr-only" @change="fileName = $event.target.files.length ? $event.target.files[0].name : ''">
                                                    </label>
                                                    <p class="pl-1">atau drag and drop</p>
                                                </div>
                                                <p class="text-xs leading-5 text-gray-500">PNG, JPG up to 2MB</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 border-t border-gray-100 px-6 py-4 justify-end bg-gray-50/50 rounded-b-2xl shrink-0">
                                    <button type="button" @click="modalOpen = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                                        Batal
                                    </button>
                                    <button type="submit" name="simpan_alat" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 shadow-sm">
                                        Simpan Alat
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal Form Edit Alat -->
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
                            class="w-full max-w-2xl rounded-2xl bg-white shadow-xl ring-1 ring-gray-200/50 max-h-[90vh] flex flex-col"
                            x-show="editModalOpen" 
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 shrink-0">
                                <h3 class="text-lg font-bold text-gray-800">Edit Alat Produksi</h3>
                                <button type="button" @click="editModalOpen = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition focus:outline-none">
                                    <i class='bx bx-x text-2xl'></i>
                                </button>
                            </div>

                            <form action="alat.php" method="POST" enctype="multipart/form-data" class="flex flex-col h-full" x-data="{ fileNameEdit: '' }">
                                <input type="hidden" name="idalat_edit" :value="editData.id">
                                <div class="overflow-y-auto p-6 space-y-4 flex-1">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700">Nama Alat</label>
                                            <input type="text" name="nama_alat" :value="editData.nama" required class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" placeholder="Misal: Mesin Press Cup">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
                                            <select name="idkategori" :value="editData.kategori" required class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                                <option disabled value="">Pilih Kategori...</option>
                                                <?php 
                                                if(mysqli_num_rows($query_kat) > 0){
                                                    mysqli_data_seek($query_kat, 0);
                                                    while($k = mysqli_fetch_array($query_kat)){
                                                        echo '<option value="' . $k['idkategori'] . '">' . htmlspecialchars($k['kategori']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700">Total Stok</label>
                                            <input type="number" name="stok" :value="editData.stok" min="0" required class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" placeholder="0">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700">Harga Sewa / Hari (Rp)</label>
                                            <input type="number" name="harga_sewa" :value="editData.harga" min="0" step="1000" required class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" placeholder="50000">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                                        <textarea rows="3" name="deskripsi" :value="editData.deskripsi" required class="block w-full rounded-xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" placeholder="Spesifikasi..."></textarea>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Ganti Foto Alat (Kosongkan jika tidak diganti)</label>
                                        <div class="mt-1 flex justify-center rounded-xl border border-dashed border-gray-300 px-6 py-6 transition-colors hover:bg-gray-50 relative overflow-hidden group">
                                            
                                            <!-- File Name Display -->
                                            <template x-if="fileNameEdit">
                                                <div class="absolute inset-0 z-10 bg-white flex flex-col items-center justify-center p-4">
                                                    <div class="flex items-center gap-3 bg-brand-50 border border-brand-100 rounded-lg px-4 py-3 w-full max-w-sm">
                                                        <i class='bx bxs-file-image text-3xl text-brand-500'></i>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="fileNameEdit"></p>
                                                        </div>
                                                        <button type="button" @click="fileNameEdit = ''; document.getElementById('file-upload-edit').value = '';" class="text-gray-400 hover:text-red-500 transition-colors focus:outline-none">
                                                            <i class='bx bx-trash text-xl'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Base Upload Area -->
                                            <div class="text-center" x-show="!fileNameEdit">
                                                <i class='bx bx-image-add text-4xl text-gray-400'></i>
                                                <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                                    <label for="file-upload-edit" class="relative cursor-pointer rounded-md bg-transparent font-semibold text-brand-500 hover:text-brand-600 focus-within:outline-none">
                                                        <span>Upload file baru</span>
                                                        <input id="file-upload-edit" name="gambar" type="file" accept="image/png, image/jpeg, image/jpg" class="sr-only" @change="fileNameEdit = $event.target.files.length ? $event.target.files[0].name : ''">
                                                    </label>
                                                </div>
                                                <p class="text-[11px] mt-2 font-medium text-orange-500 bg-orange-50 px-2 py-0.5 rounded">*Abaikan jika foto tidak diubah</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 border-t border-gray-100 px-6 py-4 justify-end bg-gray-50/50 rounded-b-2xl shrink-0">
                                    <button type="button" @click="editModalOpen = false" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                                        Batal
                                    </button>
                                    <button type="submit" name="update_alat" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 shadow-sm">
                                        Perbarui Data
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
        text: 'Alat berhasil ditambahkan.',
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
        text: 'Gagal menambahkan alat.',
        confirmButtonColor: '#ef4444'
    }).then(() => {
        window.history.replaceState(null, null, window.location.pathname);
    });

    //notifikasi update
    <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil_update'): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data Alat berhasil diperbarui!',
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
        text: 'Gagal memperbarui alat.',
        confirmButtonColor: '#ef4444'
    }).then(() => {
        window.history.replaceState(null, null, window.location.pathname);
    });

    //notifikasi hapus
    <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil_hapus'): ?>
    Swal.fire({
        icon: 'success',
        title: 'Terhapus!',
        text: 'Alat berhasil dihapus.',
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
        text: 'Gagal menghapus alat.',
        confirmButtonColor: '#ef4444'
    }).then(() => {
        window.history.replaceState(null, null, window.location.pathname);
    });
    <?php endif; ?>

    //konfirmasi hapus
    function konfirmasiHapus(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data alat akan dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'alat.php?hapus_id=' + id;
            }
        })
    }
</script>

</body>
</html>