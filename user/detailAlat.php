<?php
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
            <div class="w-8"></div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-10">
        <div id="contentLoading" class="text-center py-20 font-black text-slate-300 italic uppercase">Memuat Data
            Alat...</div>

        <div id="mainContent" class="hidden flex flex-col lg:flex-row gap-12">
            <div class="w-full lg:w-1/2">
                <div class="bg-card-blue rounded-[2.5rem] p-6 cartoon-border cartoon-shadow sticky top-24">
                    <div
                        class="bg-white cartoon-border rounded-[2rem] border-dashed border-4 aspect-square flex items-center justify-center mb-6 overflow-hidden relative">
                        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-blue-400 via-transparent to-transparent"></div>
                        <i id="alatIcon" data-lucide="chef-hat" class="w-40 h-40 text-black drop-shadow-xl relative z-10 transition-transform duration-500 hover:scale-110"></i>
                    </div>
                    <div class="flex flex-wrap gap-4" id="galleryContainer">
                        <div
                            class="w-20 h-20 bg-card-yellow cartoon-border rounded-2xl flex items-center justify-center cartoon-shadow-sm hover:-translate-y-1 transition-transform cursor-pointer">
                            <i data-lucide="image" class="w-8 h-8 text-black"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/2 space-y-8 pl-0 lg:pl-4">
                <div class="bg-white cartoon-border cartoon-shadow rounded-[2rem] p-8">
                    <div class="flex flex-wrap items-center gap-3">
                        <span id="alatKategori"
                            class="bg-card-pink text-black cartoon-border cartoon-shadow-sm text-xs font-black px-4 py-1.5 rounded-xl uppercase italic inline-block">Kategori</span>
                        <div class="flex items-center gap-1.5 bg-card-green cartoon-border px-3 py-1.5 rounded-xl text-xs font-black text-black uppercase tracking-widest"><i data-lucide="circle-check"
                                class="w-4 h-4 text-black"></i> Siap Pakai</div>
                    </div>
                    <h1 id="alatNama" class="text-4xl md:text-5xl font-black text-slate-900 mt-6 leading-tight italic uppercase drop-shadow-[2px_2px_0px_#fff]">Nama Alat
                    </h1>
                </div>

                <div class="bg-card-yellow rounded-[2rem] p-8 cartoon-border cartoon-shadow">
                    <h3 class="font-black text-black mb-5 text-sm uppercase italic tracking-wider flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5"></i> Spesifikasi & Deskripsi
                    </h3>
                    <p id="alatDeskripsi" class="text-sm text-black font-bold mb-6 italic leading-relaxed bg-white/50 p-4 rounded-xl cartoon-border">Deskripsi
                        alat akan muncul di sini.</p>
                    <ul id="alatSpek"
                        class="grid grid-cols-2 gap-4 text-xs text-black font-black uppercase italic">
                    </ul>
                </div>

                <div class="bg-primary rounded-[2rem] p-8 cartoon-border cartoon-shadow relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 opacity-10">
                        <i data-lucide="shopping-cart" class="w-40 h-40 text-white"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <p class="text-blue-200 text-xs font-black uppercase tracking-[0.2em] mb-2">Harga Sewa
                                </p>
                                <h2 class="text-4xl font-black text-white italic drop-shadow-[2px_2px_0px_#000]"><span id="alatHarga">Rp0</span> <span
                                        class="text-sm font-bold text-blue-200">/ hari</span></h2>
                            </div>
                            <div
                                class="bg-card-green text-black cartoon-border cartoon-shadow-sm text-xs font-black px-4 py-2 rounded-xl uppercase">
                                Tersedia</div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-white/10 p-4 rounded-2xl cartoon-border border-white/30">
                                <label
                                    class="block text-[10px] font-black text-blue-100 uppercase mb-3 text-center tracking-[0.2em]">Jumlah
                                    Unit</label>
                                <div class="flex items-center justify-center gap-6">
                                    <button onclick="changeDetailQty(-1)"
                                        class="w-12 h-12 flex items-center justify-center bg-white cartoon-border cartoon-shadow-sm rounded-xl text-black hover:bg-card-pink font-black text-2xl btn-cartoon-buy pb-1">-</button>
                                    <input type="number" id="detailQtyInput" value="1" min="1"
                                        class="w-16 bg-transparent text-center text-3xl font-black text-white outline-none"
                                        readonly>
                                    <button onclick="changeDetailQty(1)"
                                        class="w-12 h-12 flex items-center justify-center bg-white cartoon-border cartoon-shadow-sm rounded-xl text-black hover:bg-card-green font-black text-2xl btn-cartoon-buy pb-1">+</button>
                                </div>
                            </div>

                            <div class="flex flex-col gap-4 pt-2">
                                <button onclick="tambahKeKeranjangDetail()"
                                    class="w-full bg-white text-black py-4 rounded-xl font-black text-xs flex items-center justify-center gap-2 btn-cartoon-buy uppercase italic hover:bg-card-blue transition-colors">
                                    <i data-lucide="shopping-bag" class="w-5 h-5"></i> + Ke Keranjang
                                </button>
                                <button onclick="sewaSekarangLangsung()"
                                    class="w-full bg-aksen text-black py-4 rounded-xl font-black text-sm tracking-widest btn-cartoon-buy uppercase italic drop-shadow-[2px_2px_0px_#fff]">
                                    SEWA SEKARANG!
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
        function tambahKeKeranjangDetail() {
            // Hapus mode langsung jika ada, karena user memilih jalur keranjang biasa
            localStorage.removeItem('mode_checkout');

            const qty = parseInt(document.getElementById('detailQtyInput').value);
            let keranjang = JSON.parse(localStorage.getItem('keranjangSewaIn')) || [];
            
            const index = keranjang.findIndex(item => item.id === currentAlat.id);
            if (index === -1) {
                keranjang.push({ ...currentAlat, qty: qty, durasi: 1 });
            } else {
                keranjang[index].qty += qty;
            }
            localStorage.setItem('keranjangSewaIn', JSON.stringify(keranjang));

            alert("Keren! " + currentAlat.nama + " berhasil masuk keranjang!");
        }

        // Tombol SEWA SEKARANG (Jalur Cepat)
        function sewaSekarangLangsung() {
            const qty = parseInt(document.getElementById('detailQtyInput').value);
            const produkSingle = [{ ...currentAlat, qty: qty, durasi: 1 }];

            localStorage.setItem('checkout_cepat', JSON.stringify(produkSingle));
            localStorage.setItem('mode_checkout', 'langsung'); // SET PENANDA DI SINI

            window.location.href = 'checkout.html';
        }

        function updateNavbarProfil() {
            const userData = JSON.parse(localStorage.getItem('userSewaIn'));
            const authContainer = document.getElementById('authContainer'); // ID div tempat tombol login/profil

            if (authContainer && userData && userData.isLogin) {
                // Jika sudah login, tampilkan tombol Profil
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
            }
        }

        function logout() {
            localStorage.removeItem('userSewaIn');
            location.reload();
        }

        window.onload = () => {
            initDetail();
            updateNavbarProfil();
        };

    </script>
</body>

</html>
