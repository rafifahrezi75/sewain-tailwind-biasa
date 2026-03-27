<?php
session_start();
include '../config.php';

// Proteksi: Harus login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// --- FETCH DATA KERANJANG ---
$query_keranjang = mysqli_query($conn, "
    SELECT k.*, a.nama_alat, a.harga_sewa, a.gambar 
    FROM keranjang k 
    JOIN alat a ON k.idalat = a.idalat 
    WHERE k.iduser = $id_user
");

$items = [];
$subtotal_alat = 0;
while ($row = mysqli_fetch_assoc($query_keranjang)) {
    $items[] = $row;
    $subtotal_alat += ($row['harga_sewa'] * $row['jumlah']);
}

// Jika keranjang kosong, kembali
if (count($items) == 0) {
    header("Location: keranjang.php");
    exit();
}

// Get User Profile for autofill
$query_user = mysqli_query($conn, "SELECT * FROM user WHERE id_user = $id_user");
$user_data = mysqli_fetch_assoc($query_user);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - SewaIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
        .cartoon-border { border: 3px solid #000; }
        .cartoon-shadow { box-shadow: 8px 8px 0px 0px #000; }
        .cartoon-shadow-sm { box-shadow: 4px 4px 0px 0px #000; }
        .cartoon-accent-shadow { box-shadow: 4px 4px 0px 0px rgba(0, 0, 0, 0.05); border: 1.5px solid #E2E8F0; }
        
        .btn-cartoon-buy {
            box-shadow: 4px 4px 0px 0px #1E3A8A;
            transition: all 0.2s;
        }
        .btn-cartoon-buy:active {
            transform: translate(2px, 2px);
            box-shadow: 0px 0px 0px 0px #1E3A8A;
        }
        .text-primary { color: #1E3A8A; }
        .bg-primary { background-color: #1E3A8A; }
        .bg-aksen { background-color: #14B8A6; }

        /* Custom scrollbar for better look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #1E3A8A; border-radius: 10px; }
    </style>
</head>

<body class="pb-20">

    <nav class="bg-white border-b border-slate-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="keranjang.php" class="flex items-center gap-2 text-slate-500 hover:text-primary transition-all font-bold uppercase italic text-xs">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
                <span>Kembali</span>
            </a>
            <span class="text-sm font-black text-slate-900 tracking-tighter uppercase italic">Checkout Pesanan</span>
            <div class="w-8"></div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">
        <form id="checkoutForm" onsubmit="event.preventDefault(); submitOrder();" enctype="multipart/form-data">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

            <!-- LEFT COLUMN: Order Review & Dates -->
            <div class="lg:col-span-7 space-y-8">
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-blue-100 rounded-xl text-primary"><i data-lucide="shopping-bag" class="w-5 h-5"></i></div>
                        <h3 class="font-black text-slate-900 uppercase italic">Tinjau Pesanan</h3>
                    </div>

                    <div id="checkoutList" class="space-y-4">
                        <?php foreach($items as $item): ?>
                        <div class="bg-white rounded-3xl p-4 border border-slate-100 flex items-center gap-4 shadow-sm hover:shadow-md transition-all">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center overflow-hidden flex-none border border-slate-100">
                                <?php if($item['gambar']): ?>
                                    <img src="../uploads/<?= $item['gambar'] ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i data-lucide="package" class="w-6 h-6 text-slate-200"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xs font-black text-slate-900 uppercase italic"><?= htmlspecialchars($item['nama_alat']) ?></h4>
                                <p class="text-[10px] font-bold text-primary italic">Rp<?= number_format($item['harga_sewa'], 0, ',', '.') ?> / hari</p>
                            </div>
                            <div class="text-right">
                                <span class="bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 text-[10px] font-black italic">
                                    <?= $item['jumlah'] ?> Unit
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm space-y-6">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar" class="w-4 h-4 text-primary"></i>
                        <h4 class="text-xs font-black uppercase italic text-slate-900">Periode Sewa</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase ml-1">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="startDate" required onchange="calculateDuration()"
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-xs font-bold outline-none focus:border-aksen transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase ml-1">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="endDate" required onchange="calculateDuration()"
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-xs font-bold outline-none focus:border-aksen transition-all">
                        </div>
                    </div>
                    <div id="durationBadge" class="hidden">
                        <span class="bg-blue-100 text-primary text-[10px] font-black px-5 py-2.5 rounded-full uppercase italic border border-blue-200">
                            Total Durasi: <span id="daysCount">0</span> Hari
                        </span>
                        <input type="hidden" name="durasi" id="durasiHidden" value="0">
                    </div>
                </div>

                <!-- ADDED: KTP UPLOAD SECTION -->
                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm space-y-6">
                    <div class="flex items-center gap-2 text-brand-500">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <h4 class="text-xs font-black uppercase italic">Verifikasi Identitas</h4>
                    </div>
                    <div class="relative group">
                        <input type="file" name="gambar_ktp" id="gambar_ktp" accept="image/*" required class="hidden" onchange="previewKTP(this)">
                        <label for="gambar_ktp" class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-[2rem] p-8 cursor-pointer hover:border-primary hover:bg-slate-50 transition-all">
                            <div id="ktpPlaceholder" class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-brand-50 text-primary rounded-2xl flex items-center justify-center">
                                    <i data-lucide="camera" class="w-8 h-8"></i>
                                </div>
                                <div class="text-center">
                                    <p class="text-[11px] font-black text-primary uppercase italic">Upload Foto KTP</p>
                                    <p class="text-[9px] font-bold text-red-400 uppercase italic mt-1">Wajib Untuk Keperluan Keamanan Sewa</p>
                                </div>
                            </div>
                            <img id="ktpPreview" class="hidden max-h-48 rounded-2xl shadow-lg border border-slate-200">
                        </label>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Location & Payment Info -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-[2.5rem] p-8 cartoon-accent-shadow sticky top-24 space-y-8">
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-4">
                            <i data-lucide="user" class="w-4 h-4 text-primary"></i>
                            <h4 class="text-xs font-black uppercase italic text-slate-900">Data Penyewa</h4>
                        </div>
                        <input type="text" name="nama_penyewa" value="<?= htmlspecialchars($user_data['nama'] ?? $_SESSION['nama'] ?? '') ?>" 
                            class="w-full bg-slate-100 border border-slate-200 rounded-2xl p-4 text-xs font-black outline-none italic <?= isset($user_data['nama']) ? 'cursor-not-allowed' : '' ?>" 
                            <?= isset($user_data['nama']) ? 'readonly' : '' ?> placeholder="Nama Lengkap">

                        <input type="text" name="telepon_penyewa" value="<?= htmlspecialchars($user_data['notelp'] ?? $_SESSION['notelp'] ?? '') ?>" 
                            class="w-full bg-slate-100 border border-slate-200 rounded-2xl p-4 text-xs font-black outline-none italic <?= isset($user_data['notelp']) ? 'cursor-not-allowed' : '' ?>" 
                            <?= isset($user_data['notelp']) ? 'readonly' : '' ?> placeholder="Nomor WhatsApp">
                            
                        <textarea id="userAddress" name="alamat_sewa" required placeholder="Titik lokasi di peta atau isi alamat lengkap..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-xs font-bold outline-none focus:border-aksen h-24 placeholder:italic placeholder:font-normal"></textarea>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-4">
                            <i data-lucide="truck" class="w-4 h-4 text-primary"></i>
                            <h4 class="text-xs font-black uppercase italic text-slate-900">Metode Pengiriman</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pengiriman" value="Ambil Sendiri" class="hidden peer" checked onchange="updateShipMethod('pickup')">
                                <div class="p-3 border-2 border-slate-100 rounded-2xl text-center peer-checked:border-aksen peer-checked:bg-teal-50 transition-all font-black text-[10px] uppercase italic text-slate-400 peer-checked:text-aksen">
                                    Ambil Sendiri</div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pengiriman" value="Antar Lokasi" class="hidden peer" onchange="updateShipMethod('delivery')">
                                <div class="p-3 border-2 border-slate-100 rounded-2xl text-center peer-checked:border-aksen peer-checked:bg-teal-50 transition-all font-black text-[10px] uppercase italic text-slate-400 peer-checked:text-aksen">
                                    Antar Lokasi</div>
                            </label>
                        </div>

                        <div id="mapSection" class="space-y-3 pt-2">
                             <div class="flex items-center justify-between">
                                 <p class="text-[9px] font-black text-slate-400 uppercase italic">Pilih Titik Lokasi Anda 📍</p>
                                 <button type="button" onclick="getCurrentLocation()" class="text-[9px] font-black text-primary uppercase italic hover:underline">Gunakan Lokasi Saat Ini</button>
                             </div>
                            <div id="map" class="w-full h-52 bg-slate-100 rounded-2xl border border-slate-200 overflow-hidden shadow-inner z-0"></div>
                            <input type="hidden" name="lat_sewa" id="lat_sewa">
                            <input type="hidden" name="lon_sewa" id="lon_sewa">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100">
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-[11px] font-bold">
                                <span class="text-slate-400 uppercase italic">Subtotal Sewa Alat</span>
                                <span class="text-slate-700 italic">Rp<?= number_format($subtotal_alat, 0, ',', '.') ?> <span class="text-[9px] text-slate-300">/ hari</span></span>
                            </div>
                            <div class="flex justify-between text-[11px] font-bold">
                                <span id="durasiLabel" class="text-slate-400 uppercase italic">Durasi (1 Hari)</span>
                                <span id="totalSewaAlatText" class="text-slate-700 italic">Rp<?= number_format($subtotal_alat, 0, ',', '.') ?></span>
                            </div>
                            <div id="ongkirRow" class="hidden justify-between text-[11px] font-bold text-teal-600">
                                <span class="uppercase italic">Biaya Antar</span>
                                <span id="ongkirText">Rp0</span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-sm font-black text-slate-900 uppercase italic">Total Akhir</span>
                                <span id="totalAkhirText" class="text-2xl font-black text-primary italic">Rp<?= number_format($subtotal_alat, 0, ',', '.') ?></span>
                                <input type="hidden" name="total_biaya" id="totalBiayaHidden" value="<?= $subtotal_alat ?>">
                                <input type="hidden" name="ongkir" id="ongkirHidden" value="0">
                            </div>
                        </div>

                        <button type="submit" id="btnSubmit"
                            class="w-full bg-primary text-white py-5 rounded-[1.5rem] font-black text-sm tracking-widest btn-cartoon-buy uppercase italic flex items-center justify-center gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                            BUAT PESANAN
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </main>

    <!-- Overlay Loading -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white/80 backdrop-blur-sm z-[999] hidden flex-col items-center justify-center">
        <div class="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p class="mt-4 font-black uppercase italic text-xs tracking-widest">Memproses Pesanan...</p>
    </div>

    <script>
        const lokasiToko = [-7.4793906, 112.6027756];
        const tarifPerKm = 5000;
        let map, userMarker, ongkir = 0, totalDays = 1;
        const subtotalAlatPerHari = <?= $subtotal_alat ?>;

        function initMap() {
            map = L.map('map').setView(lokasiToko, 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            
            // Marker Toko
            L.marker(lokasiToko).addTo(map).bindPopup("<b>Lokasi Toko SewaIn</b>").openPopup();
            
            map.on('click', onMapClick);
        }

        function onMapClick(e) {
            updateMarker(e.latlng);
        }

        function updateMarker(latlng) {
            if (userMarker) {
                userMarker.setLatLng(latlng);
            } else {
                userMarker = L.marker(latlng, { draggable: true }).addTo(map);
                userMarker.on('dragend', function(e) {
                    const pos = userMarker.getLatLng();
                    updateLocationDetails(pos);
                });
            }
            updateLocationDetails(latlng);
        }

        function updateLocationDetails(latlng) {
            document.getElementById('lat_sewa').value = latlng.lat;
            document.getElementById('lon_sewa').value = latlng.lng;
            
            // Reverse Geocoding
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latlng.lat}&lon=${latlng.lng}`)
                .then(res => res.json())
                .then(data => {
                    if (data.display_name) {
                        document.getElementById('userAddress').value = data.display_name;
                    }
                })
                .catch(err => console.error("Geocode error:", err));

            // Hitung Ongkir jika mode Delivery
            const isDelivery = document.querySelector('input[name="metode_pengiriman"]:checked').value === "Antar Lokasi";
            if (isDelivery) {
                calculateDistance(latlng);
            }
        }

        function calculateDistance(latlng) {
            const url = `https://router.project-osrm.org/route/v1/driving/${lokasiToko[1]},${lokasiToko[0]};${latlng.lng},${latlng.lat}?overview=false`;
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    const km = data.routes[0].distance / 1000;
                    ongkir = Math.ceil(km) * tarifPerKm;
                    document.getElementById('ongkirText').innerText = "Rp" + ongkir.toLocaleString('id-ID');
                    document.getElementById('ongkirHidden').value = ongkir;
                    calculateTotal();
                })
                .catch(err => console.warn("OSRM error, using fallback distance"));
        }

        function updateShipMethod(method) {
            const ongRow = document.getElementById('ongkirRow');
            if (method === 'delivery') {
                ongRow.classList.replace('hidden', 'flex');
                if (userMarker) calculateDistance(userMarker.getLatLng());
            } else {
                ongRow.classList.replace('flex', 'hidden');
                ongkir = 0;
                document.getElementById('ongkirHidden').value = 0;
                calculateTotal();
            }
        }

        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    const latlng = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                    map.setView(latlng, 15);
                    updateMarker(latlng);
                });
            }
        }

        function calculateDuration() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            if (start && end) {
                const d1 = new Date(start);
                const d2 = new Date(end);
                const diffDays = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
                
                if (diffDays > 0) {
                    totalDays = diffDays;
                    document.getElementById('durationBadge').classList.remove('hidden');
                    document.getElementById('daysCount').innerText = totalDays;
                    document.getElementById('durasiHidden').value = totalDays;
                    document.getElementById('durasiLabel').innerText = `Durasi (${totalDays} Hari)`;
                } else {
                    alert("Tanggal selesai tidak boleh kurang dari tanggal mulai!");
                    document.getElementById('endDate').value = "";
                    totalDays = 1;
                    document.getElementById('durationBadge').classList.add('hidden');
                }
            }
            calculateTotal();
        }

        function calculateTotal() {
            const totalSewa = subtotalAlatPerHari * totalDays;
            const absoluteTotal = totalSewa + ongkir;
            
            document.getElementById('totalSewaAlatText').innerText = "Rp" + totalSewa.toLocaleString('id-ID');
            document.getElementById('totalAkhirText').innerText = "Rp" + absoluteTotal.toLocaleString('id-ID');
            document.getElementById('totalBiayaHidden').value = absoluteTotal;
        }

        function previewKTP(input) {
            const preview = document.getElementById('ktpPreview');
            const placeholder = document.getElementById('ktpPlaceholder');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        async function submitOrder() {
            const formData = new FormData(document.getElementById('checkoutForm'));
            
            // Basic validation
            if (!formData.get('lat_sewa') || !formData.get('lon_sewa')) {
                alert("Harap pilih titik lokasi pengiriman/rumah Anda di peta!");
                return;
            }

            document.getElementById('loadingOverlay').classList.replace('hidden', 'flex');
            
            try {
                const response = await fetch('proses_checkout.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Redirect to success page or WhatsApp
                    const msg = result.whatsapp_msg;
                    window.location.href = `checkout_success.php?id=${result.idsewa}`;
                } else {
                    alert("Gagal memproses pesanan: " + result.message);
                }
            } catch (error) {
                console.error("Submit error:", error);
                alert("Terjadi kesalahan jaringan.");
            } finally {
                document.getElementById('loadingOverlay').classList.replace('flex', 'hidden');
            }
        }

        window.onload = () => {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('startDate').value = today;
            document.getElementById('startDate').setAttribute('min', today);
            document.getElementById('endDate').setAttribute('min', today);
            
            initMap();
            lucide.createIcons();
        };
    </script>
</body>
</html>
