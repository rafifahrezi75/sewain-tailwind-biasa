<?php
include '../config.php';

$kat_filter = isset($_GET['kategori']) ? $_GET['kategori'] : 'Semua';

// get kategori
$query_kategori = mysqli_query($conn, "SELECT * FROM kategori");
$kategori_array = [];
if ($query_kategori) {
    while ($row = mysqli_fetch_assoc($query_kategori)) {
        $kategori_array[] = $row['kategori'];
    }
}

// get produk
$query_str = "SELECT alat.*, kategori.kategori 
              FROM alat 
              LEFT JOIN kategori ON alat.idkategori = kategori.idkategori 
              WHERE alat.status = 'tersedia'";

if ($kat_filter !== 'Semua') {
    $safe_kat = mysqli_real_escape_string($conn, $kat_filter);
    $query_str .= " AND kategori.kategori = '$safe_kat'";
}

$query_produk = mysqli_query($conn, $query_str);
$produk_array = [];
if ($query_produk) {
    while ($row = mysqli_fetch_assoc($query_produk)) {
        $produk_array[] = [
            'id' => isset($row['idalat']) ? (int)$row['idalat'] : (isset($row['id_alat']) ? (int)$row['id_alat'] : (int)$row['id']),
            'nama' => $row['nama_alat'],
            'harga' => (int)$row['harga_sewa'],
            'kategori' => $row['kategori'] ? $row['kategori'] : 'Umum',
            'gambar' => $row['gambar'] ? $row['gambar'] : '',
            'icon' => 'package'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog - SewaIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F1F5F9;
            overflow: hidden;
        }

        .cartoon-border {
            border: 3px solid #000;
        }

        .cartoon-shadow {
            box-shadow: 6px 6px 0px 0px rgba(0, 0, 0, 1);
        }

        .cartoon-shadow-sm {
            box-shadow: 3px 3px 0px 0px rgba(0, 0, 0, 1);
        }

        .cartoon-button:active {
            transform: translate(3px, 3px);
            box-shadow: 0px 0px 0px 0px rgba(0, 0, 0, 1);
        }

        .text-primary {
            color: #1E3A8A;
        }

        .bg-primary {
            background-color: #1E3A8A;
        }

        .bg-aksen {
            background-color: #14B8A6;
        }

        .custom-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: #000;
            border: 2px solid #F1F5F9;
        }

        .category-btn.active {
            background-color: #1E3A8A;
            color: white;
            border-color: black;
            box-shadow: 3px 3px 0px 0px #000;
        }
    </style>
</head>

<body class="h-screen flex flex-col">

    <nav class="flex-none bg-white border-b-4 border-black z-50">
        <div class="max-w-[1600px] mx-auto px-6 h-16 flex items-center justify-between">
            <a href="dashboardUser.php" class="flex items-center gap-2">
                <div
                    class="w-8 h-8 bg-primary cartoon-border rounded-lg flex items-center justify-center cartoon-shadow-sm">
                    <i data-lucide="layers" class="text-white w-4 h-4"></i>
                </div>
                <span class="text-lg font-black text-slate-900 tracking-tighter uppercase italic">Sewa<span
                        class="text-primary">In</span></span>
            </a>
            <div class="flex items-center gap-6">
                <a href="dashboardUser.php"
                    class="text-xs font-black text-slate-500 hover:text-primary transition-all uppercase italic">Beranda</a>
                <a href="../login.html" id="authContainer">
                    <div
                        class="w-9 h-9 rounded-xl bg-white cartoon-border cartoon-shadow-sm flex items-center justify-center text-slate-900">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                </a>
            </div>
        </div>
    </nav>

    <div class="flex flex-1 overflow-hidden">
        <aside class="w-72 bg-white border-r-4 border-black p-6 hidden lg:flex flex-col">
            <h3 class="text-[18px] font-black text-slate-400 uppercase tracking-widest mb-6 italic">Kategori Alat</h3>
            <nav class="space-y-3 flex-1" id="categoryNav">
                <a href="alatUser.php"
                    class="category-btn <?= $kat_filter === 'Semua' ? 'active' : '' ?> w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-black transition-all border-2 border-transparent hover:border-black uppercase italic text-left">
                    Semua Alat
                </a>
                <?php foreach ($kategori_array as $kat): ?>
                <a href="alatUser.php?kategori=<?= urlencode($kat) ?>"
                    class="category-btn <?= $kat_filter === $kat ? 'active' : '' ?> w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-black transition-all border-2 border-transparent hover:border-black uppercase italic text-left">
                    <?= htmlspecialchars($kat) ?>
                </a>
                <?php endforeach; ?>
            </nav>

            <div class="p-5 bg-yellow-300 cartoon-border cartoon-shadow-sm rounded-2xl mt-auto">
                <p class="text-[9px] font-black text-black uppercase italic">Butuh Bantuan?</p>
                <p class="text-[10px] text-black font-bold mt-1 mb-3">Tim kami siap membantu Anda.</p>
                <button
                    class="w-full py-2 bg-white cartoon-border rounded-lg text-[9px] font-black text-primary cartoon-button uppercase italic">HUBUNGI
                    ADMIN</button>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto custom-scroll p-8 bg-[#F1F5F9]">
            <div class="max-w-[1400px] mx-auto">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 id="kategoriTitle"
                            class="text-3xl font-black text-slate-900 tracking-tight uppercase italic bg-yellow-300 inline-block px-2 cartoon-border">
                            <?= htmlspecialchars($kat_filter === 'Semua' ? 'Semua Alat' : $kat_filter) ?></h1>
                        <p class="text-slate-500 text-xs font-black mt-2 uppercase italic">Menampilkan alat-alat pilihan
                            terbaik</p>
                    </div>
                </div>

                <div id="produkGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-24">
                    <?php if (count($produk_array) === 0): ?>
                        <div class="col-span-full py-16 text-center text-slate-400 font-black uppercase italic">Tidak ada produk di kategori ini.</div>
                    <?php else: ?>
                        <?php foreach ($produk_array as $p): ?>
                            <div class="bg-white rounded-[2rem] p-5 cartoon-border cartoon-shadow hover:translate-y-[-4px] transition-all duration-300 group">
                                <div class="bg-slate-100 cartoon-border rounded-[1.5rem] aspect-square mb-4 flex items-center justify-center relative overflow-hidden">
                                    <?php if ($p['gambar']): ?>
                                        <img src="../uploads/<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                    <?php else: ?>
                                        <i data-lucide="<?= htmlspecialchars($p['icon'] ?? 'package') ?>" class="text-slate-300 w-16 h-16 group-hover:scale-110 transition duration-500"></i>
                                    <?php endif; ?>
                                    <div class="absolute top-3 left-3 bg-aksen text-white text-[8px] font-black px-3 py-1 cartoon-border rounded-full uppercase">Tersedia</div>
                                </div>
                                <div class="px-1">
                                    <p class="text-aksen font-black text-[10px] uppercase tracking-tighter italic"><?= htmlspecialchars($p['kategori']) ?></p>
                                    <h3 class="font-black text-sm text-slate-900 truncate uppercase italic"><?= htmlspecialchars($p['nama']) ?></h3>
                                    <div class="flex items-baseline gap-1 mt-1 mb-4">
                                        <span class="text-xl font-black text-primary italic">Rp<?= ($p['harga'] / 1000) ?>k</span>
                                        <span class="text-slate-400 font-black text-[10px] uppercase">/hari</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <button onclick="openQtyModal(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', <?= $p['harga'] ?>)" class="flex-none bg-yellow-300 text-black p-3 rounded-xl cartoon-border cartoon-shadow-sm cartoon-button transition-all">
                                            <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                        </button>
                                        <a href="detailAlat.html?id=<?= $p['id'] ?>" class="flex-1 bg-primary text-white py-3 rounded-xl font-black text-[9px] flex items-center justify-center gap-2 cartoon-border cartoon-shadow-sm cartoon-button uppercase italic tracking-widest">
                                            DETAIL
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <a href="keranjang.html"
        class="fixed bottom-8 right-8 z-[100] bg-aksen text-white px-8 py-5 rounded-2xl cartoon-border cartoon-shadow flex items-center gap-3 cartoon-button transition-all group">
        <div class="relative">
            <i data-lucide="shopping-bag" class="w-7 h-7 text-white"></i>
            <span id="cartCount"
                class="absolute -top-3 -right-3 bg-red-500 text-white text-[10px] font-black w-6 h-6 rounded-full flex items-center justify-center border-2 border-black animate-bounce">0</span>
        </div>
        <span class="font-black text-sm tracking-tight uppercase italic">Lihat Keranjang</span>
    </a>

    <div id="qtyModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeQtyModal()"></div>
        <div
            class="bg-white w-full max-w-xs rounded-[2.5rem] p-8 cartoon-border cartoon-shadow relative z-20 text-center">
            <h4 id="qtyModalTitle" class="font-black text-slate-900 text-lg mb-2 uppercase italic leading-tight">Jumlah
                Sewa</h4>
            <p id="qtyModalPrice" class="text-primary font-black text-base mb-6 italic bg-yellow-100 inline-block px-2">
                Rp0/hari</p>
            <div class="flex items-center bg-slate-50 rounded-2xl p-2 cartoon-border mb-6">
                <button onclick="changeModalQty(-1)"
                    class="w-12 h-12 flex items-center justify-center text-slate-900 font-black text-2xl">-</button>
                <input type="number" id="modalQtyInput" value="1"
                    class="w-full bg-transparent text-center text-xl font-black text-slate-900 outline-none" readonly>
                <button onclick="changeModalQty(1)"
                    class="w-12 h-12 flex items-center justify-center text-slate-900 font-black text-2xl">+</button>
            </div>
            <button id="confirmAddBtn"
                class="w-full bg-primary text-white py-4 rounded-2xl font-black text-[11px] tracking-widest cartoon-border cartoon-shadow-sm cartoon-button uppercase italic">
                TAMBAHKAN KE KERANJANG
            </button>
        </div>
    </div>

    <script>
        let currentSelectedAlat = null;

        function openQtyModal(id, nama, harga) {
            currentSelectedAlat = { id, nama, harga };
            document.getElementById('qtyModalTitle').innerText = "Sewa " + nama;
            document.getElementById('qtyModalPrice').innerText = "Rp" + harga.toLocaleString('id-ID') + "/hari";
            document.getElementById('modalQtyInput').value = 1;
            document.getElementById('qtyModal').classList.remove('hidden');
        }

        function closeQtyModal() { document.getElementById('qtyModal').classList.add('hidden'); }

        function changeModalQty(delta) {
            const input = document.getElementById('modalQtyInput');
            let val = parseInt(input.value) + delta;
            if (val < 1) val = 1;
            input.value = val;
        }

        function konfirmasiTambah() {
            const qty = parseInt(document.getElementById('modalQtyInput').value);
            let keranjang = JSON.parse(localStorage.getItem('keranjangSewaIn')) || [];
            const index = keranjang.findIndex(item => item.id === currentSelectedAlat.id);

            if (index === -1) {
                keranjang.push({ ...currentSelectedAlat, qty: qty, durasi: 1 });
            } else {
                keranjang[index].qty += qty;
            }

            localStorage.setItem('keranjangSewaIn', JSON.stringify(keranjang));
            updateBadge();
            closeQtyModal();
            const toast = document.createElement('div');
            toast.className = "fixed top-5 left-1/2 -translate-x-1/2 z-[200] bg-yellow-300 cartoon-border cartoon-shadow px-6 py-3 font-black uppercase italic text-xs animate-bounce";
            toast.innerText = `🚀 ${qty} ${currentSelectedAlat.nama} Berhasil Masuk Keranjang!`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2500);
        }

        function updateBadge() {
            let keranjang = JSON.parse(localStorage.getItem('keranjangSewaIn')) || [];
            const totalItems = keranjang.reduce((acc, item) => acc + item.qty, 0);
            const badge = document.getElementById('cartCount');
            if (badge) {
                badge.innerText = totalItems;
                badge.style.display = totalItems > 0 ? 'flex' : 'none';
            }
        }

        document.getElementById('confirmAddBtn').onclick = konfirmasiTambah;

        window.onload = () => {
            localStorage.removeItem('mode_checkout');
            localStorage.removeItem('checkout_cepat');

            updateNavbarProfil();
            updateBadge();
            lucide.createIcons();
        };

        function updateNavbarProfil() {
            const userData = JSON.parse(localStorage.getItem('userSewaIn'));
            const authContainer = document.getElementById('authContainer');

            if (userData && userData.isLogin) {
                authContainer.innerHTML = `
            <div class="flex items-center gap-3 bg-white p-1 pr-4 rounded-full border-2 border-slate-100 shadow-sm">
                <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </div>
                <button onclick="bukaModalProfil()" class="text-[10px] font-black uppercase italic text-slate-900">
                    ${userData.nama}
                </button>
                <button onclick="logout()" class="text-red-500 hover:text-red-700">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </div>
        `;
                authContainer.href = "javascript:void(0)";
            }
        }

        function logout() {
            localStorage.removeItem('userSewaIn');
            location.reload();
        }
    </script>
</body>

</html>
