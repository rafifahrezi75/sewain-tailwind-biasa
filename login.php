<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim(mysqli_real_escape_string($conn, $_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        $query = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email' LIMIT 1");
        if ($query && mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_assoc($query);
            if (password_verify($password, $user['password'])) {
                // Login Berhasil
                $_SESSION['is_login'] = true;
                $_SESSION['id_user']  = $user['id_user'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['notelp']  = $user['notelp'];

                $success = true;
            } else {
                $error = 'Kata sandi yang Anda masukkan salah.';
            }
        } else {
            $error = 'Email tidak terdaftar atau akun tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SewaIn</title>
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

        .text-primary { color: #1E3A8A; }
        .bg-primary { background-color: #1E3A8A; }
        .bg-aksen { background-color: #14B8A6; }
        .text-aksen { color: #14B8A6; }

        .focus-ring:focus {
            border-color: #14B8A6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
        }
    </style>
</head>
<body>

<div class="min-h-screen flex items-center justify-center p-6">
    <div
        class="max-w-4xl w-full bg-white rounded-[2.5rem] shadow-2xl shadow-blue-900/10 overflow-hidden flex flex-col md:flex-row">

        <div class="hidden md:flex md:w-[45%] bg-primary p-10 flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-aksen/20 rounded-full -ml-24 -mb-24 blur-3xl"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-8">
                    <div class="w-9 h-9 bg-white rounded-xl flex items-center justify-center shadow-lg">
                        <i data-lucide="layers" class="text-primary w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-bold text-white tracking-tighter">SewaIn.</span>
                </div>
                <h2 class="text-3xl font-bold text-white leading-tight mb-5">
                    Sewa Alat Produksi <br><span class="text-aksen">Dalam Satu Genggaman.</span>
                </h2>
                <p class="text-blue-100/80 text-md leading-relaxed">
                    Masuk untuk Mencari dan Menyewa Alat Produksi Lebih Lanjut.
                </p>
            </div>

            <div class="relative z-10">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 p-5 rounded-2xl">
                    <p class="text-white text-xs italic mb-4">"Sistem ini sangat membantu saya untuk mecari alat
                        produksi."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center">
                            <i data-lucide="user" class="text-primary w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-white text-xs font-bold">Ibu Mizama</p>
                            <p class="text-blue-200 text-[10px]">Penyuka Baking</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-[55%] p-8 md:p-12 relative">

            <div class="mb-6">
                <a href="user/dashboardUser.php"
                    class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-primary transition-colors group">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5 group-hover:-translate-x-1 transition-transform"></i>
                    Kembali ke Beranda
                </a>
            </div>

            <div class="mb-8">
                <h1 class="text-2xl font-black text-slate-900 mb-1 tracking-tight">Selamat Datang!</h1>
                <p class="text-slate-500 text-sm font-medium">Silakan masuk ke akun <span
                        class="text-primary font-bold">SewaIn</span> Anda.</p>
            </div>

            <form action="login.php" method="POST" class="space-y-5">
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
                    <div class="flex justify-between items-center mb-1.5 ml-1">
                        <label class="block text-[11px] font-bold text-slate-700">Kata Sandi</label>
                        <a href="#" class="text-[10px] font-bold text-aksen hover:underline">Lupa Password?</a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus-ring transition-all text-sm font-medium text-slate-900">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="remember"
                        class="w-3.5 h-3.5 rounded border-slate-300 text-primary focus:ring-primary">
                    <label for="remember" class="text-[11px] font-medium text-slate-600">Ingat saya di perangkat
                        ini</label>
                </div>

                <button type="submit"
                    class="w-full bg-primary text-white py-3.5 rounded-xl font-bold text-md hover:bg-opacity-90 transition-all shadow-lg shadow-blue-900/20 active:scale-[0.98] flex items-center justify-center gap-2">
                    Masuk Sekarang
                </button>
            </form>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-slate-100"></span>
                </div>
                <div class="relative flex justify-center text-[10px] uppercase font-bold text-slate-400">
                    <span class="bg-white px-3 italic">Atau</span>
                </div>
            </div>

            <p class="text-center text-slate-600 text-sm font-medium">
                Belum punya akun?
                <a href="daftar.php"
                    class="text-primary font-black hover:underline ml-1 uppercase tracking-tight text-xs">Daftar
                    Sekarang</a>
            </p>
        </div>
    </div>
</div>

<script>
    const serverData = {
        error: <?= $error ? json_encode($error) : 'null' ?>,
        success: <?= isset($success) && $success ? 'true' : 'false' ?>,
        user: <?= isset($_SESSION['id_user']) ? json_encode([
            'id' => $_SESSION['id_user'],
            'nama' => $_SESSION['nama'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role'],
            'token' => md5($_SESSION['email'] . time())
        ]) : 'null' ?>,
        redirectUrl: '<?= isset($_SESSION['role']) ? ($_SESSION['role'] === 'admin' ? "admin/alat.php" : "user/dashboardUser.php") : "" ?>'
    };
</script>
<script src="login.js"></script>
</body>
</html>
