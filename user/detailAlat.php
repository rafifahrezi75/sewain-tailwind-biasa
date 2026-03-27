<?php
session_start();
include '../config.php';

$id_alat = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query_str = "SELECT alat.*, kategori.kategori 
              FROM alat 
              LEFT JOIN kategori ON alat.idkategori = kategori.idkategori";

$query_alat = mysqli_query($conn, $query_str);
$alatdb = null;

if ($query_alat) {
    while ($row = mysqli_fetch_assoc($query_alat)) {
        $row_id = isset($row['idalat']) ? (int)$row['idalat'] : (isset($row['id_alat']) ? (int)$row['id_alat'] : (int)$row['id']);
        if ($row_id === $id_alat) {
            $alatdb = $row;
            break;
        }
    }
}

$alat = null;
if ($alatdb) {
    // Menyesuaikan dengan ragam format PK atau column name
    $alatid = isset($alatdb['idalat']) ? (int)$alatdb['idalat'] : (isset($alatdb['id_alat']) ? (int)$alatdb['id_alat'] : (int)$alatdb['id']);
    
    $desc = isset($alatdb['deskripsi']) ? $alatdb['deskripsi'] : (isset($alatdb['desc']) ? $alatdb['desc'] : 'Alat pilihan terbaik siap mempermudah berbagai kebutuhan Anda.');
    
    // get spesifikasi
    $query_spek = mysqli_query($conn, "SELECT * FROM spesifikasi WHERE idalat = $alatid");
    $spek_arr = [];
    if ($query_spek) {
        while ($row_spek = mysqli_fetch_assoc($query_spek)) {
            $spek_arr[] = [
                'spek' => $row_spek['spek'],
                'iconspek' => $row_spek['iconspek'],
                'satuan' => $row_spek['satuan']
            ];
        }
    }

    // get fotodetail
    $query_foto = mysqli_query($conn, "SELECT * FROM fotodetail WHERE idalat = $alatid");
    $fotodetail_arr = [];
    if ($query_foto) {
        while ($row_foto = mysqli_fetch_assoc($query_foto)) {
            if (!empty($row_foto['fotodetail'])) {
                $fotodetail_arr[] = $row_foto['fotodetail'];
            }
        }
    }

    $gambar_utama = (!empty($alatdb['gambar']) && $alatdb['gambar'] != 'null') ? $alatdb['gambar'] : '';

    $alat = [
        'id' => $alatid,
        'nama' => $alatdb['nama_alat'],
        'harga' => (int)$alatdb['harga_sewa'],
        'kategori' => $alatdb['kategori'] ? $alatdb['kategori'] : 'Umum',
        'gambar' => $gambar_utama,
        'icon' => 'package',
        'desc' => $desc,
        'spek_obj' => $spek_arr,
        'fotodetail' => $fotodetail_arr
    ];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Sewa Alat - SewaIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F1F5F9;
        }

        /* CARTOON CORE UI */
        .cartoon-border {
            border: 3px solid #000;
        }

        .cartoon-shadow {
            box-shadow: 6px 6px 0px 0px rgba(0, 0, 0, 1);
        }

        .cartoon-shadow-sm {
            box-shadow: 3px 3px 0px 0px rgba(0, 0, 0, 1);
        }

        .cartoon-accent-shadow {
            box-shadow: 4px 4px 0px 0px rgba(0, 0, 0, 1);
        }

        .btn-cartoon-buy {
            box-shadow: 4px 4px 0px 0px #000;
            transition: all 0.2s;
            border: 3px solid #000;
        }

        .btn-cartoon-buy:active {
            transform: translate(3px, 3px);
            box-shadow: 0px 0px 0px 0px #000;
        }

        .text-primary { color: #1E3A8A; }
        .bg-primary { background-color: #1E3A8A; }
        .text-aksen { color: #14B8A6; }
        .bg-aksen { background-color: #14B8A6; }
        
        /* Colorful blocks */
        .bg-card-yellow { background-color: #FDE047; }
        .bg-card-blue { background-color: #93C5FD; }
        .bg-card-pink { background-color: #F9A8D4; }
        .bg-card-green { background-color: #86EFAC; }
        .bg-card-orange { background-color: #FDBA74; }
    </style>
</head>

<body class="pb-20">

    <nav class="bg-yellow-300 cartoon-border sticky top-0 z-50 cartoon-shadow-sm">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="javascript:history.back()"
                class="flex items-center gap-2 text-black hover:text-primary transition-all bg-white px-3 py-1 cartoon-border rounded-xl cartoon-shadow-sm font-black text-xs uppercase italic btn-cartoon-buy">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
            <span class="text-sm font-black text-black tracking-[0.2em] uppercase italic bg-white px-4 py-1 cartoon-border rounded-xl cartoon-shadow-sm">Detail Produk</span>
            <div class="flex items-center gap-3">
                <div id="authContainer"></div>
            </div>
        </div>
    </nav>

<main class="max-w-6xl mx-auto px-6 py-10">
    <div id="contentLoading" class="text-center py-20 font-black text-slate-300 italic uppercase">Memuat Data Alat...</div>

    <div id="mainContent" class="hidden flex flex-col lg:flex-row gap-24 items-stretch">
        
        <div class="w-full lg:w-1/2 sticky top-24">
            <div class="bg-white cartoon-border cartoon-shadow rounded-[2.5rem] p-8 space-y-6">
                <div class="bg-slate-50 cartoon-border rounded-[2rem] border-dashed border-4 aspect-square flex items-center justify-center overflow-hidden relative mb-4">
                    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-blue-400 via-transparent to-transparent"></div>
                    <i id="alatIcon" data-lucide="chef-hat" class="w-48 h-48 text-black drop-shadow-xl relative z-10 transition-transform duration-500 hover:scale-105"></i>
                </div>
                <div class="flex gap-3 px-1" id="galleryContainer">
                    <div class="w-16 h-16 bg-card-yellow cartoon-border rounded-xl flex items-center justify-center shadow-[3px_3px_0px_0px_#000] cursor-pointer hover:-translate-y-1 transition-transform">
                        <i data-lucide="image" class="w-6 h-6 text-black"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-1/2">
            <div class="bg-white cartoon-border cartoon-shadow rounded-[2.5rem] p-8 space-y-6">
                
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span id="alatKategori" class="bg-card-pink text-black cartoon-border text-[10px] font-black px-3 py-1 rounded-lg uppercase italic">Kategori</span>
                        <div class="flex items-center gap-1.5 bg-card-green cartoon-border px-3 py-1 rounded-lg text-[10px] font-black uppercase">
                            <i data-lucide="circle-check" class="w-3.5 h-3.5"></i> Tersedia
                        </div>
                    </div>
                    <h1 id="alatNama" class="text-4xl font-black text-slate-900 leading-tight italic uppercase">
                        Nama Alat Produksi
                    </h1>
                    <div class="flex items-center gap-2 mt-3">
                        <div class="flex text-yellow-400">
                            <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                            <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                            <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                            <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                            <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-400 uppercase italic">4.9 (Top Rated)</span>
                    </div>
                </div>

                <div class="bg-card-yellow rounded-[2rem] p-6 cartoon-border shadow-[4px_4px_0px_0px_#000]">
                    <h3 class="font-black text-black mb-3 text-xs uppercase italic tracking-wider flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4"></i> Spesifikasi
                    </h3>
                    <p id="alatDeskripsi" class="text-xs text-black font-bold mb-4 italic leading-relaxed bg-white/40 p-3 rounded-xl cartoon-border">
                        Deskripsi singkat alat.
                    </p>
                    <ul id="alatSpek" class="grid grid-cols-2 gap-3 text-[10px] text-black font-black uppercase italic">
                        </ul>
                </div>

                <div class="bg-primary rounded-[1.8rem] p-6 cartoon-border relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 opacity-10">
                        <i data-lucide="shopping-cart" class="w-24 h-24 text-white"></i>
                    </div>
                    
                    <div class="relative z-10 space-y-5">
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-blue-200 text-[10px] font-black uppercase tracking-widest mb-1">Harga Sewa</p>
                                <h2 class="text-3xl font-black text-white italic drop-shadow-[2px_2px_0px_#000]">
                                    <span id="alatHarga">Rp0</span> <span class="text-xs font-bold text-blue-200 uppercase">/ hari</span>
                                </h2>
                            </div>
                            <div class="flex items-center bg-white cartoon-border rounded-xl p-1 shadow-[2px_2px_0px_0px_#000]">
                                <button onclick="changeDetailQty(-1)" class="w-8 h-8 font-black text-lg">-</button>
                                <input type="number" id="detailQtyInput" value="1" class="w-8 text-center font-black text-sm bg-transparent outline-none" readonly>
                                <button onclick="changeDetailQty(1)" class="w-8 h-8 font-black text-lg">+</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button onclick="tambahKeKeranjang()" class="bg-white text-black py-3 rounded-xl font-black text-[10px] flex items-center justify-center gap-2 btn-cartoon-buy uppercase italic">
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i> + Keranjang
                            </button>
                            <button onclick="sewaSekarangLangsung()" class="bg-aksen text-black py-3 rounded-xl font-black text-[10px] tracking-tighter btn-cartoon-buy uppercase italic shadow-[2px_2px_0px_0px_#fff]">
                                SEWA SEKARANG!
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

    <div id="profile-overlay" class="fixed inset-0 bg-black/50 z-[120] hidden transition-opacity duration-300 opacity-0" onclick="toggleProfile()"></div>

    <div id="profile-panel" class="fixed top-0 right-0 h-full w-full sm:w-1/3 lg:w-1/4 bg-white z-[130] border-l-4 border-black translate-x-full transition-transform duration-500 ease-in-out flex flex-col">
        <div class="p-6 border-b-4 border-black bg-yellow-300 flex justify-between items-center">
            <h2 class="text-xl font-black uppercase italic leading-none">Profil Saya</h2>
            <button onclick="toggleProfile()" class="w-10 h-10 bg-white cartoon-border rounded-xl flex items-center justify-center cartoon-shadow-sm active:translate-y-1">
                <i data-lucide="x" class="w-6 h-6 text-black"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="text-center space-y-3">
                <div class="w-24 h-24 bg-primary cartoon-border rounded-3xl mx-auto flex items-center justify-center cartoon-shadow">
                    <i data-lucide="user" class="text-white w-12 h-12"></i>
                </div>
                <div>
                    <h3 id="profile-name" class="font-black text-lg uppercase italic text-black">Guest</h3>
                    <span class="text-[10px] font-bold bg-aksen cartoon-border px-3 py-1 rounded-full uppercase">Penyewa</span>
                </div>
            </div>

            <hr class="border-2 border-black border-dashed">

            <div class="space-y-4">
                <div class="space-y-1">
                    <span class="text-[10px] font-black text-gray-400 uppercase italic">Email</span>
                    <p id="profile-email" class="font-bold text-sm text-black">guest@example.com</p>
                </div>
                <div class="space-y-1">
                    <span class="text-[10px] font-black text-gray-400 uppercase italic">No. Telepon</span>
                    <p id="profile-phone" class="font-bold text-sm text-black">-</p>
                </div>
            </div>

            <div class="pt-4 space-y-3">
                <a href="pengaturan.php" class="flex items-center gap-3 p-4 bg-slate-100 cartoon-border rounded-2xl font-black text-xs uppercase italic hover:bg-yellow-50 transition-colors">
                    <i data-lucide="history" class="w-4 h-4 text-primary"></i> Pengaturan Akun
                </a>
                <a href="riwayat.php" class="flex items-center gap-3 p-4 bg-slate-100 cartoon-border rounded-2xl font-black text-xs uppercase italic hover:bg-yellow-50 transition-colors">
                    <i data-lucide="settings" class="w-4 h-4 text-gray-600"></i> Riwayat Sewa
                </a>
            </div>
        </div>

        <div class="p-6 border-t-4 border-black">
            <a href="../logout.php" class="w-full bg-red-500 text-white py-4 rounded-2xl cartoon-border cartoon-shadow-sm font-black text-center block uppercase italic hover:bg-red-600 transition-colors">
                Keluar
            </a>
        </div>
    </div>

    <a href="keranjang.php"
        class="fixed bottom-8 right-8 z-[100] bg-aksen text-white px-8 py-5 rounded-2xl cartoon-border cartoon-shadow flex items-center gap-3 cartoon-button transition-all group">
        <div class="relative">
            <i data-lucide="shopping-bag" class="w-7 h-7 text-white"></i>
            <span id="cartCount"
                class="absolute -top-3 -right-3 bg-red-500 text-white text-[10px] font-black w-6 h-6 rounded-full flex items-center justify-center border-2 border-black animate-bounce invisible">0</span>
        </div>
        <span class="font-black text-sm tracking-tight uppercase italic">Lihat Keranjang</span>
    </a>

    <script>
        // Menggunakan data dari Output Database PHP
        let currentAlat = <?= $alat ? json_encode($alat) : 'null' ?>;

        function initDetail() {
            if (currentAlat) {
                document.getElementById('contentLoading').classList.add('hidden');
                document.getElementById('mainContent').classList.remove('hidden');

                // Isi Data
                document.getElementById('alatNama').innerText = currentAlat.nama;
                document.getElementById('alatKategori').innerText = currentAlat.kategori;
                document.getElementById('alatHarga').innerText = "Rp" + currentAlat.harga.toLocaleString('id-ID');
                document.getElementById('alatDeskripsi').innerText = currentAlat.desc;

                // Ganti Icon atau Gambar nyata dari database serta galeri
                const galleryContainer = document.getElementById('galleryContainer');
                
                if (currentAlat.fotodetail && currentAlat.fotodetail.length > 0) {
                    const iconContainer = document.getElementById('alatIcon').parentElement;
                    const initialMainImage = (currentAlat.gambar && currentAlat.gambar !== '') ? currentAlat.gambar : currentAlat.fotodetail[0];
                    iconContainer.innerHTML = `<img id="alatImageMain" src="../uploads/${initialMainImage}" alt="${currentAlat.nama}" class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">`;
                    
                    if(galleryContainer) {
                        galleryContainer.innerHTML = currentAlat.fotodetail.map((fotoUrl) => `
                            <div onclick="document.getElementById('alatImageMain').src='../uploads/${fotoUrl}'" class="w-20 h-20 bg-card-yellow cartoon-border rounded-2xl flex items-center justify-center cartoon-shadow-sm hover:-translate-y-1 transition-transform cursor-pointer overflow-hidden relative">
                                <img src="../uploads/${fotoUrl}" class="w-full h-full object-cover" />
                            </div>
                        `).join('');
                    }
                } else if (currentAlat.gambar && currentAlat.gambar !== '') {
                    const iconContainer = document.getElementById('alatIcon').parentElement;
                    iconContainer.innerHTML = `<img id="alatImageMain" src="../uploads/${currentAlat.gambar}" alt="${currentAlat.nama}" class="w-full h-full object-cover transition-transform duration-500 hover:scale-110">`;
                    if(galleryContainer) galleryContainer.innerHTML = '';
                } else {
                    const iconElem = document.getElementById('alatIcon');
                    iconElem.setAttribute('data-lucide', currentAlat.icon || 'package');
                    if(galleryContainer) galleryContainer.innerHTML = '';
                }

                // Isi Spek
                const spekContainer = document.getElementById('alatSpek');
                if (currentAlat.spek_obj && currentAlat.spek_obj.length > 0) {
                    spekContainer.innerHTML = currentAlat.spek_obj.map((item) => `
                        <li class="flex items-center gap-3 bg-white cartoon-border p-2 rounded-xl cartoon-shadow-sm">
                            <div class="p-2 bg-card-blue rounded-lg cartoon-border"><i class="bx ${item.iconspek || 'bx-cube'}" style="font-size:1.25rem;"></i></div> 
                            <span class="mt-1 font-bold text-xs">${item.spek} ${item.satuan ? item.satuan : ''}</span>
                        </li>
                    `).join('');
                } else {
                    spekContainer.innerHTML = `<li class="text-xs text-slate-400 capitalize">Belum ada spesifikasi khusus.</li>`;
                }

                lucide.createIcons(); // render semua icon termasuk yg ada di innerHTML dynamic
            } else {
                document.getElementById('contentLoading').innerText = "Alat tidak ditemukan!";
            }
        }

        function changeDetailQty(delta) {
            const input = document.getElementById('detailQtyInput');
            let val = parseInt(input.value) + delta;
            if (val < 1) val = 1;
            input.value = val;
        }

        // Tombol TAMBAH KE KERANJANG (Biasa)
        function tambahKeKeranjang() {
            const qty = parseInt(document.getElementById('detailQtyInput').value);
            const userData = JSON.parse(localStorage.getItem('userSewaIn'));
            
            if (!userData || !userData.isLogin) {
                const toast = document.createElement('div');
                toast.className = "fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[301] bg-white cartoon-border cartoon-shadow p-10 flex flex-col items-center gap-6 text-center animate-bounce w-full max-w-[400px]";
                toast.innerHTML = `
                    <div class="w-20 h-20 bg-primary cartoon-border rounded-full flex items-center justify-center text-white cartoon-shadow-sm">
                        <i data-lucide="lock" class="w-10 h-10"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-xl uppercase italic mb-2">Akses Terbatas!</h4>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-tight italic">Silakan Masuk Terlebih Dahulu Untuk Menambahkan ke Keranjang</p>
                    </div>
                    <div class="flex flex-col gap-2 w-full">
                        <div class="bg-yellow-300 cartoon-border px-4 py-2 font-black text-[10px] uppercase italic">Mengalihkan ke halaman login...</div>
                    </div>
                `;
                document.body.appendChild(toast);
                lucide.createIcons();
                setTimeout(() => window.location.href = '../login.php', 2000);
                return;
            }

            // AJAX ke add_to_cart.php
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `idalat=<?= $id_alat ?>&jumlah=${qty}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    updateBadge(data.total_items);
                    const toast = document.createElement('div');
                    toast.className = "fixed top-5 left-1/2 -translate-x-1/2 z-[200] bg-yellow-300 cartoon-border cartoon-shadow px-6 py-3 font-black uppercase italic text-xs animate-bounce";
                    toast.innerText = `🚀 ${qty} Alat Berhasil Di Tambahkan!`;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 2500);
                } else {
                    const toast = document.createElement('div');
                    toast.className = "fixed top-5 left-1/2 -translate-x-1/2 z-[200] bg-red-400 text-white cartoon-border cartoon-shadow px-6 py-3 font-black uppercase italic text-xs animate-bounce";
                    toast.innerText = `❌ ${data.message || 'Gagal menambahkan'}`;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 2500);
                }
            });
        }

        function updateBadge(count) {
            const badge = document.getElementById('cartCount');
            if (badge) {
                badge.innerText = count;
                badge.classList.toggle('invisible', count <= 0);
            }
        }

        // Tombol SEWA SEKARANG (Jalur Cepat)
        function sewaSekarangLangsung() {
            const qty = parseInt(document.getElementById('detailQtyInput').value);
            const produkSingle = [{ ...currentAlat, qty: qty, durasi: 1 }];

            localStorage.setItem('checkout_cepat', JSON.stringify(produkSingle));
            localStorage.setItem('mode_checkout', 'langsung'); // SET PENANDA DI SINI

            window.location.href = 'checkout.html';
        }

        function logout() {
            window.location.href = '../logout.php';
        }

        function toggleProfile() {
            const panel = document.getElementById('profile-panel');
            const overlay = document.getElementById('profile-overlay');
            if (panel.classList.contains('translate-x-full')) {
                panel.classList.remove('translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
                document.body.style.overflow = 'hidden';
            } else {
                panel.classList.add('translate-x-full');
                overlay.classList.remove('opacity-100');
                setTimeout(() => {
                    overlay.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }, 300);
            }
        }

        function initProfile() {
            const userData = JSON.parse(localStorage.getItem('userSewaIn'));
            if (userData && userData.isLogin) {
                const nameEl = document.getElementById('profile-name');
                const emailEl = document.getElementById('profile-email');
                const phoneEl = document.getElementById('profile-phone');
                if (nameEl) nameEl.innerText = userData.nama;
                if (emailEl) emailEl.innerText = userData.email;
                if (phoneEl && userData.telepon) phoneEl.innerText = userData.telepon;
            }
        }

        function updateNavbarProfil() {
            const userData = JSON.parse(localStorage.getItem('userSewaIn'));
            const authContainer = document.getElementById('authContainer');

            if (userData && userData.isLogin) {
                authContainer.innerHTML = `
                    <button onclick="toggleProfile()" class="flex items-center gap-3 bg-white p-1 pr-1 sm:pr-4 rounded-full border-2 border-slate-100 shadow-sm cartoon-button transition-all">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <span class="hidden sm:inline text-[10px] font-black uppercase italic text-slate-900">
                            ${userData.nama.split(' ')[0]}
                        </span>
                    </button>
                `;
                initProfile();
                lucide.createIcons();
            } else if (authContainer) {
                 authContainer.innerHTML = `
                    <a href="../login.php" class="flex items-center gap-2 bg-white px-3 py-1 cartoon-border rounded-xl cartoon-shadow-sm font-black text-xs uppercase italic btn-cartoon-buy">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Masuk</span>
                    </a>
                `;
                lucide.createIcons();
            }
        }

        window.onload = () => {
            initDetail();
            updateNavbarProfil();
            fetch('get_cart_count.php')
                .then(res => res.json())
                .then(data => updateBadge(data.count));
        };

    </script>
</body>

</html>
