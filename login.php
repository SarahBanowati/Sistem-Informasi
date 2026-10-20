<?php
session_start();
require_once 'config/database.php';

$error_message = '';

// Jika petugas sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['id_petugas'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan kata sandi wajib diisi!';
    } else {
        try {
            // Ambil data petugas berdasarkan username dari tabel petugas
            $stmt = $pdo->prepare("SELECT * FROM petugas WHERE username = ?");
            $stmt->execute([$username]);
            $petugas = $stmt->fetch();

            // Verifikasi password menggunakan password_verify
            if ($petugas && password_verify($password, $petugas['password'])) {
                // Set sesi login
                $_SESSION['id_petugas'] = $petugas['id_petugas'];
                $_SESSION['username'] = $petugas['username'];
                $_SESSION['nama_lengkap'] = $petugas['nama_lengkap'];
                $_SESSION['role'] = $petugas['role'];
                $_SESSION['id_poli'] = $petugas['id_poli'];

                header("Location: dashboard.php");
                exit;
            } else {
                $error_message = 'Username atau kata sandi salah!';
            }
        } catch (PDOException $e) {
            $error_message = 'Terjadi kesalahan pada sistem database.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI-ANTREAN KLINIK</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 min-h-full flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 md:p-8 space-y-6 relative overflow-hidden border border-slate-200 dark:border-slate-700">
        
        <!-- Branding Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-teal-100 dark:bg-teal-900/50 text-teal-600 dark:text-teal-400 mb-2 shadow-inner">
                <i class="fa-solid fa-hospital-user text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">SI-ANTREAN KLINIK</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Portal Akses Petugas & Dokter Puskesmas Modern</p>
        </div>

        <!-- Notifikasi Error jika login gagal -->
        <?php if (!empty($error_message)): ?>
            <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400 text-xs font-medium text-center">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="username" required placeholder="Masukkan username" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-teal-500 outline-none text-sm transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Kata Sandi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-white focus:ring-2 focus:ring-teal-500 outline-none text-sm transition">
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-lg shadow-teal-600/30 transition transform active:scale-95 flex items-center justify-center space-x-2">
                <span>Masuk Dasbor</span>
                <i class="fa-solid fa-right-to-bracket"></i>
            </button>
        </form>

        <!-- Informasi Akun Uji Coba dari Database -->
        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
            <p class="text-xs text-center text-slate-500 dark:text-slate-400 mb-2 font-medium">Info Akun Bawaan (Password: <code class="bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded text-teal-600 dark:text-teal-400 font-bold">admin123</code>):</p>
            <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600 dark:text-slate-300">
                <div class="bg-slate-50 dark:bg-slate-700/50 p-2 rounded-lg border border-slate-200 dark:border-slate-600">
                    <strong class="block text-teal-600 dark:text-teal-400">superadmin</strong> Admin Sistem
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/50 p-2 rounded-lg border border-slate-200 dark:border-slate-600">
                    <strong class="block text-blue-600 dark:text-blue-400">dr_budi</strong> Poli Umum
                </div>
            </div>
        </div>

    </div>

</body>
</html>