<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivitas Sewa - SewaIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F1F5F9;
        }

        .cartoon-border {
            border: 3px solid #000;
        }

        .cartoon-shadow {
            box-shadow: 6px 6px 0px 0px rgba(0, 0, 0, 1);
        }

        .cartoon-shadow-sm {
            box-shadow: 4px 4px 0px 0px rgba(0, 0, 0, 1);
        }

        .tab-active {
            background-color: #FACC15 !important; /* Kuning Terang */
            transform: translate(-2px, -2px);
            box-shadow: 4px 4px 0px 0px #000;
        }

        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="p-4 md:p-8" x-data="{ tab: 'aktif', modalOpen: false }">

    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-10">
            <a href="dashboardUser.php"
                class="w-12 h-12 bg-white cartoon-border rounded-2xl flex items-center justify-center cartoon-shadow-sm hover:bg-yellow-50 transition-all">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </a>
            <div>
                <h1 class="text-3xl font-black uppercase italic tracking-tighter text-slate-900">Aktivitas Sewa</h1>
                <p class="text-xs font-bold text-slate-500 uppercase italic">Kelola alat yang sedang kamu gunakan</p>
            </div>
        </div>

        <div class="flex gap-4 mb-8">
            <button @click="tab = 'aktif'" 
                :class="tab === 'aktif' ? 'tab-active' : ''"
                class="px-6 py-3 bg-white cartoon-border rounded-xl font-black text-xs uppercase italic transition-all">
                Sewa Aktif (2)
            </button>
            <button @click="tab = 'selesai'" 
                :class="tab === 'selesai' ? 'tab-active' : ''"
                class="px-6 py-3 bg-white cartoon-border rounded-xl font-black text-xs uppercase italic hover:bg-slate-50 transition-all">
                Riwayat Selesai
            </button>
        </div>

        <div x-show="tab === 'aktif'" class="space-y-6" x-transition.opacity>
            <div class="bg-white cartoon-border cartoon-shadow rounded-[2.5rem] p-6 flex flex-col md:flex-row items-center gap-6 border-b-[8px]">
                <div class="w-24 h-24 bg-blue-100 cartoon-border rounded-[1.5rem] flex items-center justify-center shrink-0">
                    <i data-lucide="oven" class="w-12 h-12 text-blue-600"></i>
                </div>

                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-wrap justify-center md:justify-start items-center gap-2 mb-2">
                        <span class="bg-blue-100 text-blue-600 text-[9px] font-black px-3 py-1 rounded-full cartoon-border uppercase tracking-widest">Sedang Digunakan</span>
                        <span class="text-[9px] font-black text-slate-400 uppercase italic">#INV-00325</span>
                    </div>
                    <h3 class="text-xl font-black uppercase italic text-slate-900">Oven Deck Manual - Gas</h3>
                    <div class="flex items-center justify-center md:justify-start gap-4 mt-2">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span class="text-[10px] font-bold text-slate-500 uppercase">Deadline: 28 Mar</span>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-auto">
                    <button @click="modalOpen = true"
                        class="w-full bg-red-700 text-white px-8 py-4 rounded-2xl cartoon-border cartoon-shadow-sm font-black text-xs uppercase italic hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all active:scale-95">
                        Kembalikan Alat
                    </button>
                </div>
            </div>

            <div class="bg-slate-50 opacity-80 cartoon-border rounded-[2.5rem] p-6 flex flex-col md:flex-row items-center gap-6 border-dashed border-slate-300">
                <div class="w-24 h-24 bg-white cartoon-border rounded-[1.5rem] flex items-center justify-center shrink-0 grayscale">
                    <i data-lucide="blender" class="w-12 h-12 text-slate-400"></i>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-wrap justify-center md:justify-start items-center gap-2 mb-2">
                        <span class="bg-yellow-100 text-yellow-600 text-[9px] font-black px-3 py-1 rounded-full cartoon-border uppercase animate-pulse italic">Menunggu Validasi Admin</span>
                    </div>
                    <h3 class="text-xl font-black uppercase italic text-slate-400 tracking-tighter">Heavy Duty Blender</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-1 italic leading-tight">Unit sedang dalam pengecekan QC di gudang.</p>
                </div>
                <div class="w-full md:w-auto">
                    <div class="px-6 py-4 bg-slate-200 cartoon-border rounded-2xl font-black text-[10px] text-slate-400 uppercase italic text-center">
                        PROSES QC
                    </div>
                </div>
            </div>
        </div>

        <div x-show="tab === 'selesai'" class="space-y-6" x-transition.opacity x-cloak>
            <div class="bg-white cartoon-border rounded-[2.5rem] p-6 flex flex-col md:flex-row items-center gap-6 opacity-70 grayscale">
                <div class="w-20 h-20 bg-slate-100 cartoon-border rounded-2xl flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-10 h-10 text-slate-400"></i>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <span class="text-[9px] font-black text-slate-400 uppercase italic">#INV-00210</span>
                    <h3 class="text-lg font-black uppercase italic text-slate-700 leading-none mb-1">Planetary Mixer 20L</h3>
                    <p class="text-[10px] font-bold text-emerald-600 uppercase italic">Dikembalikan: 12 Feb 2026</p>
                </div>
                <button class="w-full md:w-auto bg-white cartoon-border cartoon-shadow-sm px-6 py-3 rounded-xl font-black text-[10px] uppercase italic hover:bg-yellow-300 transition-all">
                    Sewa Lagi
                </button>
            </div>
        </div>

        <div class="mt-12 bg-white cartoon-border border-dashed p-6 rounded-[2rem] flex items-start gap-4">
            <i data-lucide="help-circle" class="text-blue-500 w-8 h-8 shrink-0"></i>
            <div>
                <h5 class="font-black uppercase italic text-sm text-slate-900">Butuh bantuan pengembalian?</h5>
                <p class="text-[11px] font-bold text-slate-600 mt-1 italic leading-relaxed">
                    Silakan antar unit ke Gudang SewaIn. Jika alat terlalu berat untuk diantar sendiri (seperti Oven besar), 
                    silakan hubungi admin via WhatsApp untuk penjemputan unit ke lokasi Anda.
                </p>
            </div>
        </div>
    </div>

    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" x-cloak x-transition.opacity>
    <div @click.outside="modalOpen = false" class="bg-white cartoon-border cartoon-shadow rounded-[3rem] w-full max-w-sm p-8 relative overflow-hidden" x-transition.scale.90>
        <div class="text-center">
            <div class="w-20 h-20 bg-yellow-400 cartoon-border rounded-full flex items-center justify-center mx-auto mb-6 shadow-[4px_4px_0px_0px_#000]">
                <i data-lucide="map-pin" class="w-10 h-10 text-black"></i>
            </div>
            <h3 class="text-2xl font-black uppercase italic text-slate-900 mb-2 leading-none tracking-tighter">Cara Kembalikan</h3>
            <p class="text-[11px] font-bold text-slate-500 mb-8 uppercase italic leading-tight px-4">
                Antar alat langsung ke **Gudang SewaIn (Blok B-12)**. <br>Admin akan cek fisik & validasi di tempat.
            </p>
            
            <div class="space-y-4">
                <button @click="modalOpen = false" class="w-full bg-blue-500 text-white py-4 rounded-2xl cartoon-border shadow-[4px_4px_0px_0px_#000] font-black text-xs uppercase italic hover:bg-blue-600 transition-all active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                    OKE, SIAP!
                </button>

                <a href="https://wa.me/6287776600292?text=*Halo%20Admin%20SewaIn%2C%20saya%20butuh%20bantuan%20penjemputan%20alat%20untuk%20unit%20Oven%20Deck%20Manual%20dengan%20nomor%20Invoice%20%23INV-00325.%20Terima%20kasih.*" 
                   target="_blank"
                   class="w-full bg-white border-2 border-black py-4 rounded-2xl flex items-center justify-center gap-2 font-black text-xs uppercase italic hover:bg-slate-50 transition-all shadow-[4px_4px_0px_0px_#000] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none">
                    BUTUH JEMPUTAN
                </a>
            </div>
        </div>
    </div>
</div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>