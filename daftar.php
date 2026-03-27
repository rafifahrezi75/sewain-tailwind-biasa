<?php
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim(mysqli_real_escape_string($conn, $_POST['nama'] ?? ''));
    $email    = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $terms    = isset($_POST['terms']);

    if (!$nama || !$email || !$password) {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } elseif (!$terms) {
        $error = 'Anda harus menyetujui syarat & ketentuan.';
    } else {
        $cek = mysqli_query($conn, "SELECT id_user FROM user WHERE email = '$email' LIMIT 1");
        if ($cek && mysqli_num_rows($cek) > 0) {
            $error = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_query($conn, "INSERT INTO user (nama, email, password, role) VALUES ('$nama', '$email', '$hashed', 'user')");
            if ($insert) {
                $success = 'Akun berhasil dibuat! Silakan login.';
            } else {
                $error = 'Gagal mendaftar: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #F1F5F9;
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

    .text-aksen {
        color: #14B8A6;
    }

    .focus-ring:focus {
        border-color: #14B8A6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
    }
</style>

<div class="min-h-screen flex items-center justify-center p-6">
    <div
        class="max-w-4xl w-full bg-white rounded-[2.5rem] shadow-2xl shadow-blue-900/10 overflow-hidden flex flex-col md:flex-row-reverse">

        <div class="hidden md:flex md:w-[45%] bg-primary p-10 flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 w-64 h-64 bg-white/5 rounded-full -ml-32 -mt-32"></div>
            <div class="absolute bottom-0 right-0 w-48 h-48 bg-aksen/20 rounded-full -mr-24 -mb-24 blur-3xl"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-8">
                    <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center shadow-lg">
                        <i data-lucide="layers" class="text-primary w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-bold text-white tracking-tighter">SewaIn.</span>
                </div>
                <h2 class="text-3xl font-bold text-white leading-tight mb-5">
                    Mulai Perjalanan <br><span class="text-aksen">Bisnismu Bersama Kami.</span>
                </h2>
                <ul class="space-y-3 text-blue-100/90 text-md">
                    <li class="flex items-center gap-3"><i data-lucide="check-circle" class="text-aksen w-4 h-4"></i>
                        Akses 100+ Alat Produksi</li>
                    <li class="flex items-center gap-3"><i data-lucide="check-circle" class="text-aksen w-4 h-4"></i>
                        Cek Stok Alat Produksi</li>
                    <li class="flex items-center gap-3"><i data-lucide="check-circle" class="text-aksen w-4 h-4"></i>
                        Proses Peminjaman mudah dan cepat</li>
                </ul>
            </div>

            <div class="relative z-10 text-center p-3 bg-white/5 border border-white/10 rounded-xl">
                <p class="text-white/80 text-[10px] font-medium uppercase tracking-widest">Wujudkan Impian Bisnismu Di
                    SewaIn</p>
            </div>
        </div>

        <div class="w-full md:w-[55%] p-8 md:p-12">
            <div class="mb-6">
                <a href="dashboardUser.php"
                    class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-primary transition-colors group">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5 group-hover:-translate-x-1 transition-transform"></i>
                    Kembali ke Beranda
                </a>
            </div>

            <div class="mb-8">
                <h1 class="text-2xl font-black text-slate-900 mb-1 tracking-tight">Buat Akun Baru</h1>
                <p class="text-slate-500 text-sm font-medium">Lengkapi data untuk mulai menyewa.</p>
            </div>

            <form action="daftar.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1.5 ml-1">Nama Lengkap</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="nama" required placeholder="Andi Pratama"
                            value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus-ring transition-all text-sm font-medium text-slate-900">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1.5 ml-1">Alamat Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input type="email" name="email" required placeholder="nama@email.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus-ring transition-all text-sm font-medium text-slate-900">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1.5 ml-1">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus-ring transition-all text-sm font-medium text-slate-900">
                    </div>
                </div>

                <div class="flex items-start gap-2 pt-1">
                    <input type="checkbox" name="terms" required id="terms"
                        class="mt-1 w-3.5 h-3.5 rounded border-slate-300 text-primary focus:ring-primary">
                    <label for="terms" class="text-[10px] font-medium text-slate-500 leading-tight">
                        Saya setuju dengan <a href="#" class="text-primary font-bold hover:underline">Syarat &
                            Ketentuan</a> serta <a href="#" class="text-primary font-bold hover:underline">Kebijakan
                            Privasi</a> SewaIn.
                    </label>
                </div>

                <button type="submit"
                    class="w-full bg-primary text-white py-3.5 rounded-xl font-bold text-md hover:bg-opacity-90 transition-all shadow-lg shadow-blue-900/20 active:scale-[0.98] flex items-center justify-center gap-2">
                    Daftar Sekarang
                </button>
            </form>

            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-slate-100"></span>
                </div>
                <div class="relative flex justify-center text-[10px] uppercase font-bold text-slate-400">
                    <span class="bg-white px-3 italic">Sudah punya akun?</span>
                </div>
            </div>

            <a href="login.php"
                class="w-full flex items-center justify-center gap-2 border-2 border-slate-100 text-slate-600 py-3 rounded-xl font-bold hover:bg-slate-50 transition-all tracking-tight uppercase text-[10px]">
                Kembali ke Halaman Masuk
            </a>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    // Mencegah resubmit form saat halaman di-refresh (F5) dan mengosongkan nilai form
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    <?php if ($error): ?>
    const errorToast = document.createElement('div');
    errorToast.className = "fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[300] bg-white border-[3px] border-black shadow-[8px_8px_0px_0px_#000] p-10 flex flex-col items-center gap-6 text-center animate-bounce w-full max-w-[400px]";
    errorToast.innerHTML = `
        <div class="w-20 h-20 bg-red-100 border-[3px] border-black rounded-full flex items-center justify-center text-red-500 shadow-[4px_4px_0px_0px_#000]">
            <i data-lucide="alert-circle" class="w-10 h-10"></i>
        </div>
        <div>
            <h4 class="font-black text-xl uppercase italic mb-2">Pendaftaran Gagal</h4>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-tight italic"><?= addslashes(htmlspecialchars($error)) ?></p>
        </div>
        <button onclick="this.parentElement.remove()" class="w-full bg-primary text-white py-3 rounded-xl font-black text-[10px] border-[3px] border-black shadow-[4px_4px_0px_0px_#000] uppercase italic">Coba Lagi</button>
    `;
    document.body.appendChild(errorToast);
    lucide.createIcons();
    <?php elseif ($success): ?>
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
    <?php endif; ?>
</script>
