<?php
    include '../config.php';

    if (!isset($_GET['id'])) {
        header("Location: alat.php");
        exit;
    }

    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // TAMBAH SPEK
    if (isset($_POST['simpan_spek'])) {
        $spek     = mysqli_real_escape_string($conn, $_POST['spek']);
        $iconspek = mysqli_real_escape_string($conn, $_POST['iconspek']);
        $satuan   = mysqli_real_escape_string($conn, $_POST['satuan']);
        $ins = mysqli_query($conn, "INSERT INTO spesifikasi (idalat, spek, iconspek, satuan) VALUES ('$id', '$spek', '$iconspek', '$satuan')");
        header("Location: detail_alat.php?id=$id&pesan=" . ($ins ? 'berhasil_tambah_spek' : 'gagal_tambah_spek'));
        exit;
    }

    // UPDATE SPEK
    if (isset($_POST['update_spek'])) {
        $idspek   = mysqli_real_escape_string($conn, $_POST['idspek']);
        $spek     = mysqli_real_escape_string($conn, $_POST['spek']);
        $iconspek = mysqli_real_escape_string($conn, $_POST['iconspek']);
        $satuan   = mysqli_real_escape_string($conn, $_POST['satuan']);
        $upd = mysqli_query($conn, "UPDATE spesifikasi SET spek='$spek', iconspek='$iconspek', satuan='$satuan' WHERE idspek='$idspek' AND idalat='$id'");
        header("Location: detail_alat.php?id=$id&pesan=" . ($upd ? 'berhasil_update_spek' : 'gagal_update_spek'));
        exit;
    }

    // HAPUS SPEK
    if (isset($_GET['hapus_spek'])) {
        $idspek = mysqli_real_escape_string($conn, $_GET['hapus_spek']);
        $del = mysqli_query($conn, "DELETE FROM spesifikasi WHERE idspek='$idspek' AND idalat='$id'");
        header("Location: detail_alat.php?id=$id&pesan=" . ($del ? 'berhasil_hapus_spek' : 'gagal_hapus_spek'));
        exit;
    }

    // TAMBAH FOTO DETAIL
    if (isset($_POST['simpan_foto'])) {
        $file     = $_FILES['fotodetail'];
        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ekstensi, $allowed) && $file['size'] < 3145728) {
            $nama_file = time() . '-' . uniqid() . '.' . $ekstensi;
            move_uploaded_file($file['tmp_name'], '../uploads/' . $nama_file);
            $ins = mysqli_query($conn, "INSERT INTO fotodetail (idalat, fotodetail) VALUES ('$id', '$nama_file')");
            header("Location: detail_alat.php?id=$id&pesan=" . ($ins ? 'berhasil_tambah_foto' : 'gagal_tambah_foto'));
        } else {
            header("Location: detail_alat.php?id=$id&pesan=gagal_tambah_foto");
        }
        exit;
    }

    // HAPUS FOTO DETAIL
    if (isset($_GET['hapus_foto'])) {
        $idfoto = mysqli_real_escape_string($conn, $_GET['hapus_foto']);
        $get_f  = mysqli_query($conn, "SELECT fotodetail FROM fotodetail WHERE idfotodetail='$idfoto' AND idalat='$id'");
        $row_f  = mysqli_fetch_assoc($get_f);
        if ($row_f && !empty($row_f['fotodetail'])) {
            $path = '../uploads/' . $row_f['fotodetail'];
            if (file_exists($path)) unlink($path);
        }
        $del = mysqli_query($conn, "DELETE FROM fotodetail WHERE idfotodetail='$idfoto' AND idalat='$id'");
        header("Location: detail_alat.php?id=$id&pesan=" . ($del ? 'berhasil_hapus_foto' : 'gagal_hapus_foto'));
        exit;
    }

    // GET DATA ALAT
    $query = mysqli_query($conn, "SELECT alat.*, kategori.kategori 
                                  FROM alat 
                                  LEFT JOIN kategori ON alat.idkategori = kategori.idkategori 
                                  WHERE alat.idalat = '$id'");
    if (mysqli_num_rows($query) == 0) {
        header("Location: alat.php");
        exit;
    }
    $data = mysqli_fetch_assoc($query);

    // GET DATA SPESIFIKASI
    $query_spek = mysqli_query($conn, "SELECT * FROM spesifikasi WHERE idalat = '$id' ORDER BY idspek ASC");

    // GET DATA FOTO DETAIL
    $query_foto = mysqli_query($conn, "SELECT * FROM fotodetail WHERE idalat = '$id' ORDER BY idfotodetail ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Alat | Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../src/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-[#f8f9fa] font-sans text-gray-800 antialiased" x-data="{ sidebarOpen: true }">

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
                <a href="index.html" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-grid-alt text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Dashboard</span>
                </a>
                <a href="kategori.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-layer text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Kategori</span>
                </a>
                <a href="alat.php" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none bg-white text-brand-700 shadow-sm" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-wrench text-xl shrink-0"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Alat Produksi</span>
                    <div x-show="sidebarOpen" class="ml-auto flex h-2 w-2 shrink-0 rounded-full bg-brand-500"></div>
                </a>
                <a href="transaksi.html" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium transition-all focus:outline-none text-white hover:bg-brand-600 focus:bg-brand-600" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
                    <i class="bx bx-shopping-bag text-xl shrink-0 opacity-80"></i>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">Daftar Transaksi</span>
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
                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 font-medium text-white transition-all hover:bg-brand-600 focus:outline-none" :class="sidebarOpen ? 'justify-start' : 'md:justify-center px-0'">
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
                    <span class="text-lg font-medium text-white hidden sm:block capitalize">
                        Detail — <?php echo htmlspecialchars($data['nama_alat']); ?>
                    </span>
                </div>
                <div class="flex items-center gap-3 sm:gap-5 text-white">
                    <button class="relative rounded-lg p-2 hover:bg-brand-600 transition focus:outline-none">
                        <i class='bx bx-bell text-xl'></i>
                        <span class="absolute right-1.5 top-1.5 flex h-2.5 w-2.5 items-center justify-center rounded-full bg-[#f9db72] border-2 border-brand-500"></span>
                    </button>
                    <div class="flex items-center gap-3 pl-3 sm:pl-5 relative before:absolute before:left-0 before:top-1/2 before:-translate-y-1/2 before:h-8 before:w-px before:bg-brand-400">
                        <div class="hidden sm:flex flex-col text-right justify-center">
                            <span class="text-sm font-semibold leading-tight">Eriko</span>
                            <span class="text-[10px] text-brand-400 font-medium mt-0.5 uppercase tracking-wider">ADMIN</span>
                        </div>
                        <button class="h-9 w-9 overflow-hidden rounded-full bg-white/10 hover:bg-white/20 border border-white/20 transition flex items-center justify-center focus:outline-none">
                            <i class='bx bx-user text-xl text-white'></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto w-full bg-[#f8f9fa]">
                <div class="mx-auto w-full max-w-5xl p-6 space-y-6">
                    
                    <!-- Back Button -->
                    <div class="flex items-center justify-between">
                        <a href="alat.php" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-brand-600 transition-colors bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-100">
                            <i class="bx bx-left-arrow-alt text-xl"></i>
                            Kembali ke Daftar Alat
                        </a>
                        <!-- Breadcrumb -->
                        <nav class="text-xs text-gray-400 hidden sm:flex items-center gap-1.5">
                            <span>Admin</span><i class='bx bx-chevron-right'></i>
                            <a href="alat.php" class="hover:text-brand-500 transition-colors">Alat Produksi</a><i class='bx bx-chevron-right'></i>
                            <span class="text-gray-700 font-semibold"><?php echo htmlspecialchars($data['nama_alat']); ?></span>
                        </nav>
                    </div>

                    <!-- ===== HERO DETAIL CARD ===== -->
                    <div class="bg-white rounded-3xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                        
                        <!-- Top gradient strip -->
                        <div class="h-1.5 w-full bg-gradient-to-r from-brand-400 via-brand-500 to-brand-600"></div>

                        <div class="flex flex-col md:flex-row">
                            <!-- Image Section -->
                            <div class="md:w-5/12 relative flex items-center justify-center min-h-[320px] bg-gradient-to-br from-gray-50 to-gray-100 border-r border-gray-100 overflow-hidden p-8">
                                <!-- Decorative circle behind image -->
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <div class="h-64 w-64 rounded-full bg-brand-100/30"></div>
                                </div>
                                <?php if ($data['gambar']): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($data['gambar']); ?>" 
                                         alt="<?php echo htmlspecialchars($data['nama_alat']); ?>" 
                                         class="relative z-10 w-full h-auto object-contain max-h-[360px] drop-shadow-xl transition-transform duration-500 hover:scale-105">
                                <?php else: ?>
                                    <div class="relative z-10 flex flex-col items-center text-gray-300">
                                        <i class="bx bx-image text-8xl"></i>
                                        <span class="text-sm font-medium mt-2 text-gray-400">Tidak ada gambar</span>
                                    </div>
                                <?php endif; ?>
                                <!-- Status floating badge -->
                                <?php if($data['status'] == 'tersedia'): ?>
                                    <span class="absolute top-4 left-4 z-20 inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-3 py-1.5 text-[11px] font-bold text-white shadow-md tracking-wider uppercase">
                                        <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Tersedia
                                    </span>
                                <?php else: ?>
                                    <span class="absolute top-4 left-4 z-20 inline-flex items-center gap-1.5 rounded-xl bg-red-500 px-3 py-1.5 text-[11px] font-bold text-white shadow-md tracking-wider uppercase">
                                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span> Kosong
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Info Section -->
                            <div class="md:w-7/12 p-8 lg:p-12 flex flex-col gap-8 bg-white">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-4 py-1.5 text-[10px] font-bold text-brand-600 tracking-[0.15em] uppercase border border-brand-100 shadow-sm">
                                        <i class='bx bx-layer mr-2 text-sm'></i>
                                        <?php echo htmlspecialchars($data['kategori']); ?>
                                    </span>
                                    
                                </div>

                                <div class="space-y-4">
                                    <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight leading-[1.1]">
                                        <?php echo htmlspecialchars($data['nama_alat']); ?>
                                    </h2>
                                    <div class="inline-flex flex-col">
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-sm font-bold text-brand-400 uppercase tracking-tighter">Rp</span>
                                            <span class="text-4xl font-black text-gray-900 tracking-tight">
                                                <?php echo number_format($data['harga_sewa'], 0, ',', '.'); ?>
                                            </span>
                                            <span class="text-gray-400 font-medium ml-1">/ hari</span>
                                        </div>
                                        <div class="h-1.5 w-1/3 bg-brand-100 rounded-full mt-2 overflow-hidden">
                                            <div class="h-full bg-brand-500 w-1/2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="group relative overflow-hidden rounded-2xl bg-gray-50 border border-gray-100 p-5 transition-all hover:border-brand-200 hover:bg-white hover:shadow-xl hover:shadow-brand-500/5">
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="p-2 rounded-lg bg-white shadow-sm group-hover:bg-brand-500 group-hover:text-white transition-colors">
                                                <i class='bx bx-package text-xl'></i>
                                            </div>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.1em]">Total Stok</span>
                                        </div>
                                        <p class="text-3xl font-black text-gray-800">
                                            <?php echo $data['stok']; ?> <span class="text-xs font-bold text-gray-400 -ml-1">Unit</span>
                                        </p>
                                    </div>

                                    <div class="group relative overflow-hidden rounded-2xl <?php echo $data['status'] == 'tersedia' ? 'bg-emerald-50/50 border-emerald-100' : 'bg-red-50/50 border-red-100'; ?> border p-5 transition-all hover:shadow-lg">
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="p-2 rounded-lg bg-white shadow-sm">
                                                <i class='bx <?php echo $data['status'] == 'tersedia' ? 'bx-check-double text-emerald-500' : 'bx-time-five text-red-500'; ?> text-xl'></i>
                                            </div>
                                            <span class="text-[10px] font-bold <?php echo $data['status'] == 'tersedia' ? 'text-emerald-500' : 'text-red-400'; ?> uppercase tracking-[0.1em]">Ketersediaan</span>
                                        </div>
                                        <p class="text-2xl font-black <?php echo $data['status'] == 'tersedia' ? 'text-emerald-700' : 'text-red-700'; ?> uppercase tracking-tighter">
                                            <?php echo $data['status'] == 'tersedia' ? 'Tersedia' : 'Kosong'; ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="relative group">
                                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-100 to-transparent rounded-3xl blur opacity-20 transition duration-1000"></div>
                                    <div class="relative rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                                        <div class="flex items-center gap-3 mb-4">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-brand-500 text-white shadow-lg shadow-brand-200">
                                                <i class='bx bx-align-left text-sm'></i>
                                            </span>
                                            <h3 class="font-bold text-gray-800 tracking-tight">Deskripsi Detail</h3>
                                        </div>
                                        <div class="prose prose-sm text-gray-600 max-w-none">
                                            <p class="leading-[1.8] text-[14px] text-gray-500">
                                                <?php echo nl2br(htmlspecialchars($data['deskripsi'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-data="{ modalTambah: false, modalEdit: false, editData: { id: '', spek: '', iconspek: '', satuan: '' } }">

                        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                            <!-- Table Header -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <div>
                                    <h3 class="text-base font-bold text-gray-800">Spesifikasi Teknis</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">Detail spesifikasi <?php echo htmlspecialchars($data['nama_alat']); ?></p>
                                </div>
                                <button @click="modalTambah = true" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-all shadow-sm focus:outline-none">
                                    <i class='bx bx-plus text-lg'></i>
                                    <span>Tambah Spek</span>
                                </button>
                            </div>

                            <!-- Spek Table -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm text-gray-500">
                                    <thead class="bg-gray-50/50 text-xs uppercase text-gray-400 border-b border-gray-100">
                                        <tr>
                                            <th class="px-6 py-3 font-semibold tracking-wider w-10">#</th>
                                            <th class="px-6 py-3 font-semibold tracking-wider">Spesifikasi</th>
                                            <th class="px-6 py-3 font-semibold tracking-wider">Satuan / Nilai</th>
                                            <th class="px-6 py-3 font-semibold tracking-wider text-center w-32">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <?php
                                        if (mysqli_num_rows($query_spek) > 0) {
                                            $no = 1;
                                            while ($spek = mysqli_fetch_assoc($query_spek)) {
                                        ?>
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-6 py-3 text-gray-400 font-medium"><?php echo $no++; ?></td>
                                            <td class="px-6 py-3">
                                                <div class="flex items-center gap-3">
                                                    <?php if(!empty($spek['iconspek'])): ?>
                                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-500">
                                                            <i class="bx <?php echo htmlspecialchars($spek['iconspek']); ?> text-lg"></i>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-400">
                                                            <i class="bx bx-file text-lg"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($spek['spek']); ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-3">
                                                <span class="inline-block rounded-lg bg-brand-50 border border-brand-100 px-3 py-1 text-xs font-bold text-brand-600">
                                                    <?php echo htmlspecialchars($spek['satuan']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-3">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button type="button" @click="editData = { id: '<?php echo $spek['idspek']; ?>', spek: '<?php echo htmlspecialchars($spek['spek'], ENT_QUOTES); ?>', iconspek: '<?php echo htmlspecialchars($spek['iconspek'] ?? '', ENT_QUOTES); ?>', satuan: '<?php echo htmlspecialchars($spek['satuan'], ENT_QUOTES); ?>' }; modalEdit = true" class="rounded-lg p-2 text-blue-500 bg-blue-50 hover:bg-blue-100 transition-colors focus:outline-none" title="Edit">
                                                        <i class='bx bx-edit text-lg'></i>
                                                    </button>
                                                    <button type="button" onclick="hapusSpek('<?php echo $spek['idspek']; ?>')" class="rounded-lg p-2 text-red-500 bg-red-50 hover:bg-red-100 transition-colors focus:outline-none" title="Hapus">
                                                        <i class='bx bx-trash text-lg'></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                        ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                                                <div class="flex flex-col items-center gap-2">
                                                    <i class='bx bx-list-ul text-4xl text-gray-300'></i>
                                                    <span class="text-sm">Belum ada spesifikasi. Klik <strong class="text-brand-500">+ Tambah Spek</strong> untuk memulai.</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ==================== -->
                        <!-- MODAL TAMBAH SPEK    -->
                        <!-- ==================== -->
                        <div x-show="modalTambah"
                             class="fixed inset-0 z-60 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             style="display:none;">
                            <div @click.outside="modalTambah = false"
                                 class="w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-gray-200/50"
                                 x-show="modalTambah"
                                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                                    <h3 class="text-base font-bold text-gray-800">Tambah Spesifikasi</h3>
                                    <button @click="modalTambah = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 transition focus:outline-none">
                                        <i class='bx bx-x text-2xl'></i>
                                    </button>
                                </div>
                                <form action="detail_alat.php?id=<?php echo $id; ?>" method="POST" class="p-6 space-y-4">
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Spesifikasi</label>
                                        <input type="text" name="spek" required placeholder="Contoh: Daya Listrik, Berat, Dimensi..." class="block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Icon Boxicons <span class="text-gray-400 font-normal">(opsional)</span></label>
                                        <input type="text" name="iconspek" placeholder="Contoh: bx-bolt-circle, bx-chip, bx-cog" class="block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                        <p class="mt-1 text-[11px] text-gray-400">Cari nama icon di <a href="https://boxicons.com" target="_blank" class="text-brand-500 underline">boxicons.com</a></p>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Satuan / Nilai</label>
                                        <input type="text" name="satuan" required placeholder="Contoh: 220V, 5 kg, 30x40 cm..." class="block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                    </div>
                                    <div class="flex gap-3 justify-end pt-2">
                                        <button type="button" @click="modalTambah = false" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">Batal</button>
                                        <button type="submit" name="simpan_spek" class="rounded-xl bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600 shadow-sm focus:outline-none">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- ==================== -->
                        <!-- MODAL EDIT SPEK      -->
                        <!-- ==================== -->
                        <div x-show="modalEdit"
                             class="fixed inset-0 z-60 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             style="display:none;">
                            <div @click.outside="modalEdit = false"
                                 class="w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-gray-200/50"
                                 x-show="modalEdit"
                                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                                    <h3 class="text-base font-bold text-gray-800">Edit Spesifikasi</h3>
                                    <button @click="modalEdit = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 transition focus:outline-none">
                                        <i class='bx bx-x text-2xl'></i>
                                    </button>
                                </div>
                                <form action="detail_alat.php?id=<?php echo $id; ?>" method="POST" class="p-6 space-y-4">
                                    <input type="hidden" name="idspek" :value="editData.id">
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Spesifikasi</label>
                                        <input type="text" name="spek" :value="editData.spek" required placeholder="Contoh: Daya Listrik..." class="block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Icon Boxicons <span class="text-gray-400 font-normal">(opsional)</span></label>
                                        <input type="text" name="iconspek" :value="editData.iconspek" placeholder="Contoh: bx-bolt-circle, bx-chip, bx-cog" class="block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                        <p class="mt-1 text-[11px] text-gray-400">Cari nama icon di <a href="https://boxicons.com" target="_blank" class="text-brand-500 underline">boxicons.com</a></p>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Satuan / Nilai</label>
                                        <input type="text" name="satuan" :value="editData.satuan" required placeholder="Contoh: 220V..." class="block w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                    </div>
                                    <div class="flex gap-3 justify-end pt-2">
                                        <button type="button" @click="modalEdit = false" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">Batal</button>
                                        <button type="submit" name="update_spek" class="rounded-xl bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600 shadow-sm focus:outline-none">Perbarui</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div><!-- end x-data spek -->

                    <!-- ======================== -->
                    <!-- FOTO DETAIL SECTION      -->
                    <!-- ======================== -->
                    <div x-data="{ modalFoto: false, fotoPreview: '', fotoName: '' }">

                        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
                            <!-- Header -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <div>
                                    <h3 class="text-base font-bold text-gray-800">Foto Tambahan</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">Galeri foto detail <?php echo htmlspecialchars($data['nama_alat']); ?></p>
                                </div>
                                <button @click="modalFoto = true" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-all shadow-sm focus:outline-none">
                                    <i class='bx bx-image-add text-lg'></i>
                                    <span>Tambah Foto</span>
                                </button>
                            </div>

                            <!-- Photo Grid -->
                            <div class="p-6">
                                <?php if (mysqli_num_rows($query_foto) > 0): ?>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                    <?php while ($foto = mysqli_fetch_assoc($query_foto)): ?>
                                    <div class="group relative overflow-hidden rounded-2xl bg-gray-100 aspect-square shadow-sm ring-1 ring-gray-200/50">
                                        <img src="../uploads/<?php echo htmlspecialchars($foto['fotodetail']); ?>" 
                                             alt="Foto Detail" 
                                             class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                        <!-- Overlay on hover -->
                                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-between p-3">
                                            <a href="../uploads/<?php echo htmlspecialchars($foto['fotodetail']); ?>" target="_blank" 
                                               class="rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 p-2 text-white hover:bg-white/30 transition-colors" title="Lihat Asli">
                                                <i class='bx bx-expand-alt text-base'></i>
                                            </a>
                                            <button type="button" onclick="hapusFoto('<?php echo $foto['idfotodetail']; ?>')" 
                                                    class="rounded-lg bg-red-500/80 backdrop-blur-sm border border-red-400/30 p-2 text-white hover:bg-red-600 transition-colors" title="Hapus">
                                                <i class='bx bx-trash text-base'></i>
                                            </button>
                                        </div>
                                        <!-- Photo number badge -->
                                        <span class="absolute top-2 right-2 h-6 w-6 flex items-center justify-center rounded-lg bg-black/30 backdrop-blur-sm text-white text-[10px] font-bold">
                                            <?php echo $foto['idfotodetail']; ?>
                                        </span>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <?php else: ?>
                                <div class="flex flex-col items-center justify-center py-14 text-gray-300">
                                    <i class='bx bx-image text-6xl mb-3'></i>
                                    <p class="text-sm font-medium text-gray-400">Belum ada foto tambahan.</p>
                                    <p class="text-xs text-gray-300 mt-1">Klik <strong class="text-brand-500">+ Tambah Foto</strong> untuk mengunggah.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- ==================== -->
                        <!-- MODAL TAMBAH FOTO    -->
                        <!-- ==================== -->
                        <div x-show="modalFoto"
                             class="fixed inset-0 z-60 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             style="display:none;">
                            <div @click.outside="modalFoto = false; fotoPreview = '';"
                                 class="w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-gray-200/50"
                                 x-show="modalFoto"
                                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                                    <h3 class="text-base font-bold text-gray-800">Tambah Foto Detail</h3>
                                    <button @click="modalFoto = false; fotoPreview = '';" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 transition focus:outline-none">
                                        <i class='bx bx-x text-2xl'></i>
                                    </button>
                                </div>
                                <form action="detail_alat.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">File Foto</label>
                                        <!-- Upload Area -->
                                        <div class="relative mt-1 flex justify-center rounded-2xl border-2 border-dashed border-gray-200 px-6 py-8 transition-colors hover:bg-gray-50 overflow-hidden">
                                            <!-- Preview -->
                                            <template x-if="fotoPreview">
                                                <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white p-3">
                                                    <img :src="fotoPreview" class="max-h-40 max-w-full rounded-xl object-contain shadow-sm mb-2">
                                                    <p class="text-xs font-semibold text-gray-600 truncate max-w-full" x-text="fotoName"></p>
                                                    <button type="button" @click="fotoPreview = ''; fotoName = ''; document.getElementById('input-foto').value = '';" 
                                                            class="mt-2 text-xs text-red-500 hover:underline focus:outline-none">Ganti foto</button>
                                                </div>
                                            </template>
                                            <!-- Default state -->
                                            <div class="text-center" x-show="!fotoPreview">
                                                <i class='bx bx-cloud-upload text-4xl text-gray-300'></i>
                                                <div class="mt-3 flex text-sm text-gray-500 justify-center">
                                                    <label for="input-foto" class="cursor-pointer font-semibold text-brand-500 hover:text-brand-600 focus-within:outline-none">
                                                        <span>Pilih file</span>
                                                        <input id="input-foto" name="fotodetail" type="file" accept="image/*" required class="sr-only"
                                                               @change="
                                                                   if ($event.target.files.length) {
                                                                       fotoName = $event.target.files[0].name;
                                                                       const reader = new FileReader();
                                                                       reader.onload = e => fotoPreview = e.target.result;
                                                                       reader.readAsDataURL($event.target.files[0]);
                                                                   }
                                                               ">
                                                    </label>
                                                    <span class="ml-1">atau drag & drop</span>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-400">PNG, JPG, WEBP — maks. 3MB</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-3 justify-end pt-1">
                                        <button type="button" @click="modalFoto = false; fotoPreview = '';" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">Batal</button>
                                        <button type="submit" name="simpan_foto" class="rounded-xl bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600 shadow-sm focus:outline-none">Upload Foto</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div><!-- end x-data foto -->

                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-[#f8f9fa] px-6 py-4 text-center sm:text-right text-[13px] text-gray-400 font-medium shrink-0">
                &copy; 2026 Admin Dashboard. All rights reserved.
            </footer>
        </div>
        
        <!-- Overlay on mobile -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-brand-700/50 backdrop-blur-sm transition-opacity md:hidden" x-transition.opacity></div>
    </div>

    <script>
        // Notifikasi
        <?php if (isset($_GET['pesan'])): ?>
        <?php $pesan = $_GET['pesan']; ?>
        <?php if ($pesan == 'berhasil_tambah_spek'): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Spesifikasi berhasil ditambahkan.', confirmButtonColor: '#3b82f6', timer: 2500, timerProgressBar: true })
            .then(() => window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $id; ?>'));
        <?php elseif ($pesan == 'gagal_tambah_spek'): ?>
        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Gagal menambahkan spesifikasi.', confirmButtonColor: '#ef4444' })
            .then(() => window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $id; ?>'));
        <?php elseif ($pesan == 'berhasil_update_spek'): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Spesifikasi berhasil diperbarui.', confirmButtonColor: '#3b82f6', timer: 2500, timerProgressBar: true })
            .then(() => window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $id; ?>'));
        <?php elseif ($pesan == 'gagal_update_spek'): ?>
        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Gagal memperbarui spesifikasi.', confirmButtonColor: '#ef4444' })
            .then(() => window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $id; ?>'));
        <?php elseif ($pesan == 'berhasil_hapus_spek'): ?>
        Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'Spesifikasi berhasil dihapus.', confirmButtonColor: '#3b82f6', timer: 2500, timerProgressBar: true })
            .then(() => window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $id; ?>'));
        <?php elseif ($pesan == 'gagal_hapus_spek'): ?>
        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Gagal menghapus spesifikasi.', confirmButtonColor: '#ef4444' })
            .then(() => window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $id; ?>'));
        <?php elseif ($pesan == 'berhasil_tambah_foto'): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Foto berhasil diunggah.', confirmButtonColor: '#3b82f6', timer: 2500, timerProgressBar: true })
            .then(() => window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $id; ?>'));
        <?php elseif ($pesan == 'gagal_tambah_foto'): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Foto gagal diunggah. Pastikan format dan ukuran file sesuai (maks 3MB).', confirmButtonColor: '#ef4444' })
            .then(() => window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $id; ?>'));
        <?php elseif ($pesan == 'berhasil_hapus_foto'): ?>
        Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'Foto berhasil dihapus.', confirmButtonColor: '#3b82f6', timer: 2500, timerProgressBar: true })
            .then(() => window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $id; ?>'));
        <?php elseif ($pesan == 'gagal_hapus_foto'): ?>
        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Gagal menghapus foto.', confirmButtonColor: '#ef4444' })
            .then(() => window.history.replaceState(null, null, window.location.pathname + '?id=<?php echo $id; ?>'));
        <?php endif; ?>
        <?php endif; ?>

        // Konfirmasi Hapus Spek
        function hapusSpek(idspek) {
            Swal.fire({
                title: 'Hapus Spesifikasi?',
                text: 'Data spesifikasi ini akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'detail_alat.php?id=<?php echo $id; ?>&hapus_spek=' + idspek;
                }
            });
        }

        // Konfirmasi Hapus Foto
        function hapusFoto(idfoto) {
            Swal.fire({
                title: 'Hapus Foto?',
                text: 'Foto ini akan dihapus permanen dari server.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'detail_alat.php?id=<?php echo $id; ?>&hapus_foto=' + idfoto;
                }
            });
        }
    </script>
</body>
</html>
