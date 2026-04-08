lucide.createIcons();

// Mencegah resubmit form saat halaman di-refresh (F5) dan mengosongkan nilai form
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

if (serverData.error) {
    const errorToast = document.createElement('div');
    errorToast.className = "fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[300] bg-white border-[3px] border-black shadow-[8px_8px_0px_0px_#000] p-10 flex flex-col items-center gap-6 text-center animate-bounce w-full max-w-[400px]";
    
    const safeError = serverData.error.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');

    errorToast.innerHTML = `
        <div class="w-20 h-20 bg-red-100 border-[3px] border-black rounded-full flex items-center justify-center text-red-500 shadow-[4px_4px_0px_0px_#000]">
            <i data-lucide="alert-circle" class="w-10 h-10"></i>
        </div>
        <div>
            <h4 class="font-black text-xl uppercase italic mb-2">Pendaftaran Gagal</h4>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-tight italic">${safeError}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="w-full bg-primary text-white py-3 rounded-xl font-black text-[10px] border-[3px] border-black shadow-[4px_4px_0px_0px_#000] uppercase italic">Coba Lagi</button>
    `;
    document.body.appendChild(errorToast);
    lucide.createIcons();
} else if (serverData.success) {
    const successToast = document.createElement('div');
    successToast.className = "fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[300] bg-white border-[3px] border-black shadow-[8px_8px_0px_0px_#000] p-10 flex flex-col items-center gap-6 text-center animate-bounce w-full max-w-[400px]";
    successToast.innerHTML = `
        <div class="w-20 h-20 bg-emerald-100 border-[3px] border-black rounded-full flex items-center justify-center text-emerald-500 shadow-[4px_4px_0px_0px_#000]">
            <i data-lucide="check-circle" class="w-10 h-10"></i>
        </div>
        <div>
            <h4 class="font-black text-xl uppercase italic mb-2">Akun Berhasil Dibuat! 🎉</h4>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-tight italic">Selamat datang di SewaIn! Silakan login untuk mulai menyewa.</p>
        </div>
        <div class="w-full bg-teal-400 border-[3px] border-black px-4 py-2 font-black text-[10px] uppercase italic text-white">Mengalihkan ke Login...</div>
    `;
    document.body.appendChild(successToast);
    lucide.createIcons();

    setTimeout(() => {
        window.location.href = 'login.php';
    }, 2000);
}
