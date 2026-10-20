<?php
session_start();
if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit;
}
require_once 'config/database.php';

// Ambil data poli untuk dropdown form input
$stmtPoli = $pdo->query("SELECT * FROM poli WHERE status_aktif = 'buka'");
$listPoli = $stmtPoli->fetchAll();

$pesan_sukses = '';
$pesan_error = '';

// Proses jika form pendaftaran pasien baru (input) disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_antrean'])) {
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $id_poli = $_POST['id_poli'] ?? '';
    $tanggal = date('Y-m-d');
    $tipe = 'walk_in';

    if (empty($nama) || empty($telepon) || empty($id_poli)) {
        $pesan_error = "Semua field input wajib diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("CALL sp_daftar_antrean(?, ?, ?, ?, ?, @p_tiket, @p_code, @p_msg)");
            $stmt->execute([$nama, $telepon,$id_poli, $tanggal,$tipe]);
            
            $output =$pdo->query("SELECT @p_tiket AS tiket, @p_code AS code, @p_msg AS msg")->fetch();
            
            if ($output && $output['code'] === 'SUCCESS') {$pesan_sukses = "Pendaftaran berhasil! Nomor Tiket Pasien: " . $output['tiket'];
            } else {
                $pesan_error = "Gagal: " . ($output['msg'] ?? 'Terjadi kesalahan sistem');
            }
        } catch (PDOException $e) {$pesan_error = "Kesalahan Database: " . $e->getMessage();
        }
    }
}

// Ambil data antrean dari database dengan aman
$stmtAntrean =$pdo->prepare("
    SELECT a.*, p.nama_lengkap, p.no_telepon, po.nama_poli 
    FROM antrean a
    JOIN pasien p ON a.id_pasien = p.id_pasien
    JOIN poli po ON a.id_poli = po.id_poli
    ORDER BY a.id_antrean DESC
");
$stmtAntrean->execute();
$dataAntreanDB =$stmtAntrean->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik secara aman dari database
$total_hari_ini = count($dataAntreanDB);
$menunggu = count(array_filter($dataAntreanDB, fn($q) => strtolower($q['status'] ?? $q['status_antrean'] ?? '') === 'menunggu'));$dipanggil = count(array_filter($dataAntreanDB, fn($q) => in_array(strtolower($q['status'] ?? $q['status_antrean'] ?? ''), ['dipanggil', 'melayani'])));
$selesai = count(array_filter($dataAntreanDB, fn($q) => strtolower($q['status'] ?? $q['status_antrean'] ?? '') === 'selesai'));$dilewati = count(array_filter($dataAntreanDB, fn($q) => in_array(strtolower($q['status'] ?? $q['status_antrean'] ?? ''), ['dilewati', 'batal'])));
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Antrean Klinik / Puskesmas - Dashboard Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        medical: { 50: '#f0fdfa', 100: '#ccfbf1', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 800: '#115e59', 900: '#134e4a' },
                        clinical: { 50: '#f0f9ff', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1' }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 min-h-full transition-colors duration-200">

    <div id="app" class="min-h-screen flex flex-col">

        <!-- HEADER / NAVBAR -->
        <header class="sticky top-0 z-30 bg-white/90 dark:bg-slate-800/90 backdrop-blur border-b border-slate-200 dark:border-slate-700 px-4 lg:px-8 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-hospital text-xl"></i>
                </div>
                <div>
                    <h2 class="font-bold text-slate-800 dark:text-white leading-tight">Puskesmas Sehat Bersama</h2>
                    <div class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sistem Antrean Realtime Active</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button onclick="toggleAudio()" id="btnAudioToggle" title="Audio Suara Pemanggilan" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition">
                    <i id="audioIcon" class="fa-solid fa-volume-high text-teal-600 dark:text-teal-400"></i>
                </button>
                <button onclick="toggleDarkMode()" title="Ganti Tema" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition">
                    <i id="themeIcon" class="fa-solid fa-moon"></i>
                </button>
                <div class="pl-3 border-l border-slate-200 dark:border-slate-700 flex items-center space-x-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-slate-800 dark:text-white"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Petugas'); ?></p>
                        <p class="text-xs text-teal-600 dark:text-teal-400 font-medium"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'staff')); ?></p>
                    </div>
                    <a href="logout.php" class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 text-rose-600 border border-rose-200 transition flex items-center space-x-2" title="Keluar">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span class="text-xs font-semibold hidden md:inline">Keluar</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- DASHBOARD BODY CONTAINER -->
        <main class="flex-1 p-4 lg:p-8 space-y-6 max-w-7xl w-full mx-auto">

            <!-- NOTIFIKASI HASIL INPUT -->
            <?php if (!empty($pesan_sukses)): ?>
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold flex items-center justify-between shadow">
                    <span><i class="fa-solid fa-circle-check mr-2"></i> <?php echo htmlspecialchars($pesan_sukses); ?></span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($pesan_error)): ?>
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-semibold flex items-center justify-between shadow">
                    <span><i class="fa-solid fa-triangle-exclamation mr-2"></i> <?php echo htmlspecialchars($pesan_error); ?></span>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800"><i class="fa-solid fa-xmark"></i></button>
                </div>
            <?php endif; ?>

            <!-- ACTIVE SESSION INFORMATION BAR + TOMBOL INPUT -->
            <div class="flex flex-col md:flex-row md:items-center justify-between bg-gradient-to-r from-teal-700 to-clinical-700 text-white rounded-2xl p-4 md:p-6 shadow-xl relative overflow-hidden">
                <div class="relative z-10 space-y-1">
                    <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-semibold text-teal-100 uppercase tracking-wide">
                        LOGIN SEBAGAI: <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? ''); ?>
                    </span>
                    <h1 class="text-xl md:text-2xl font-bold">Dasbor Konsul & Pemanggilan Antrean Pasien</h1>
                </div>
                <div class="mt-4 md:mt-0 relative z-10 flex items-center space-x-3">
                    <button onclick="openInputModal()" class="px-4 py-2.5 bg-white text-teal-800 hover:bg-teal-50 font-bold rounded-xl text-xs md:text-sm shadow-lg transition flex items-center space-x-2">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>+ Input Pasien Baru</span>
                    </button>
                </div>
                <i class="fa-solid fa-hospital-user absolute -right-6 -bottom-6 text-9xl text-white/10 pointer-events-none"></i>
            </div>

            <!-- REALTIME STATS WIDGETS -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4">
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/40 text-blue-600 rounded-xl"><i class="fa-solid fa-users text-xl"></i></div>
                    <div><p class="text-xs text-slate-500 font-medium">Total Hari Ini</p><h3 class="text-xl font-bold text-slate-800 dark:text-white"><?php echo $total_hari_ini; ?></h3></div>
                </div>
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3">
                    <div class="p-3 bg-amber-100 dark:bg-amber-900/40 text-amber-600 rounded-xl"><i class="fa-solid fa-hourglass-start text-xl"></i></div>
                    <div><p class="text-xs text-slate-500 font-medium">Menunggu</p><h3 class="text-xl font-bold text-amber-600"><?php echo $menunggu; ?></h3></div>
                </div>
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3">
                    <div class="p-3 bg-teal-100 dark:bg-teal-900/40 text-teal-600 rounded-xl"><i class="fa-solid fa-user-doctor text-xl"></i></div>
                    <div><p class="text-xs text-slate-500 font-medium">Sedang Dilayani</p><h3 class="text-xl font-bold text-teal-600"><?php echo $dipanggil; ?></h3></div>
                </div>
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3">
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 rounded-xl"><i class="fa-solid fa-circle-check text-xl"></i></div>
                    <div><p class="text-xs text-slate-500 font-medium">Selesai</p><h3 class="text-xl font-bold text-emerald-600"><?php echo $selesai; ?></h3></div>
                </div>
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3 col-span-2 lg:col-span-1">
                    <div class="p-3 bg-rose-100 dark:bg-rose-900/40 text-rose-600 rounded-xl"><i class="fa-solid fa-forward text-xl"></i></div>
                    <div><p class="text-xs text-slate-500 font-medium">Dilewati / Batal</p><h3 class="text-xl font-bold text-rose-600"><?php echo $dilewati; ?></h3></div>
                </div>
            </div>

            <!-- CALL CONSOLE & ACTIVE QUEUE PANEL -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- CALL CONSOLE -->
                <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm flex flex-col justify-between space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Konsol Pemanggil Utama</span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">LOKET AKTIF</span>
                    </div>

                    <div class="text-center py-6 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-1">Nomor Antrean Aktif</p>
                        <h2 id="displayQueueNum" class="text-5xl lg:text-6xl font-extrabold text-teal-600">---</h2>
                        <p id="displayPatientName" class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-2">Belum ada pasien dipanggil</p>
                        <p id="displayPatientPoli" class="text-xs text-slate-400">-</p>
                    </div>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <button onclick="actionCallNext()" class="py-3 px-4 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow transition flex items-center justify-center space-x-2 text-sm">
                                <i class="fa-solid fa-bullhorn"></i><span>Panggil Berikutnya</span>
                            </button>
                            <button onclick="actionRecallCurrent()" class="py-3 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow transition flex items-center justify-center space-x-2 text-sm">
                                <i class="fa-solid fa-rotate-right"></i><span>Panggil Ulang</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- QUEUE DATA TABLE -->
                <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm flex flex-col justify-between space-y-4">
                    <div class="flex flex-col sm:flex-row gap-3 justify-between items-center">
                        <div class="relative w-full sm:w-64">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400"><i class="fa-solid fa-magnifying-glass text-xs"></i></span>
                            <input type="text" id="searchQuery" oninput="filterTable()" placeholder="Cari Pasien / No. Antrean..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none">
                        </div>
                        <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                            <select id="filterStatus" onchange="filterTable()" class="px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white">
                                <option value="ALL">Semua Status</option>
                                <option value="menunggu">Menunggu</option>
                                <option value="dipanggil">Dipanggil</option>
                                <option value="melayani">Melayani</option>
                                <option value="selesai">Selesai</option>
                                <option value="dilewati">Dilewati</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto flex-1 max-h-[380px] overflow-y-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 dark:bg-slate-900/80 sticky top-0 text-[11px] font-bold text-slate-500 uppercase">
                                <tr>
                                    <th class="py-3 px-3">No. Antrean</th>
                                    <th class="py-3 px-3">Pasien</th>
                                    <th class="py-3 px-3">Poli</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3 text-right">Aksi Manual</th>
                                </tr>
                            </thead>
                            <tbody id="queueTableBody" class="divide-y divide-slate-100 dark:divide-slate-700 text-xs">
                                <?php if (empty($dataAntreanDB)): ?>
                                    <tr><td colspan="5" class="py-4 text-center text-slate-400">Belum ada data antrean.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($dataAntreanDB as$row): ?>
                                        <?php 
                                            $st = strtolower($row['status'] ?? $row['status_antrean'] ?? 'menunggu');
                                            $nomor =$row['nomor_antrean'] ?? $row['no_antrean'] ?? $row['kode_antrean'] ?? $row['tiket'] ?? $row['nomor'] ?? $row['no_tiket'] ?? ('A-' . str_pad($row['id_antrean'] ?? 1, 3, '0', STR_PAD_LEFT));
                                            $nama =$row['nama_lengkap'] ?? '-';
                                            $telepon =$row['no_telepon'] ?? '-';
                                            $poli =$row['nama_poli'] ?? '-';
                                        ?>
                                        <tr class="queue-row" data-status="<?php echo $st; ?>" data-search="<?php echo strtolower(htmlspecialchars($nomor . ' ' .$nama)); ?>">
                                            <td class="py-3 px-3 font-bold text-slate-800 dark:text-white"><?php echo htmlspecialchars($nomor); ?></td>
                                            <td class="py-3 px-3 font-semibold">
                                                <?php echo htmlspecialchars($nama); ?><br>
                                                <span class="text-[10px] text-slate-400"><?php echo htmlspecialchars($telepon); ?></span>
                                            </td>
                                            <td class="py-3 px-3"><?php echo htmlspecialchars($poli); ?></td>
                                            <td class="py-3 px-3">
                                                <?php 
                                                    $badge = "bg-amber-100 text-amber-700";
                                                    if ($st === 'dipanggil')$badge = "bg-teal-100 text-teal-700 animate-pulse";
                                                    if ($st === 'selesai')$badge = "bg-emerald-100 text-emerald-700";
                                                    if ($st === 'dilewati')$badge = "bg-rose-100 text-rose-700";
                                                ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $badge; ?>"><?php echo ucfirst($st); ?></span>
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                <button onclick="panggilPasien('<?php echo htmlspecialchars($nomor); ?>', '<?php echo htmlspecialchars($nama); ?>', '<?php echo htmlspecialchars($poli); ?>')" class="p-1.5 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100" title="Panggil">
                                                    <i class="fa-solid fa-bullhorn text-xs"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL INPUT: FORM PENDAFTARAN PASIEN BARU -->
    <div id="inputModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                <h3 class="font-bold text-slate-800 dark:text-white flex items-center space-x-2">
                    <i class="fa-solid fa-user-plus text-teal-600"></i><span>Form Input Pendaftaran Pasien</span>
                </h3>
                <button onclick="closeInputModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            
            <form action="dashboard.php" method="POST" class="space-y-4 text-xs">
                <input type="hidden" name="tambah_antrean" value="1">
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap Pasien</label>
                    <input type="text" name="nama_lengkap" required placeholder="Contoh: Budi Santoso" class="w-full p-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="no_telepon" required placeholder="Contoh: 081234567890" class="w-full p-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Pilih Poliklinik Tujuan</label>
                    <select name="id_poli" required class="w-full p-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white">
                        <option value="">-- Pilih Poli --</option>
                        <?php foreach ($listPoli as$poli): ?>
                            <option value="<?php echo $poli['id_poli']; ?>"><?php echo htmlspecialchars($poli['nama_poli']); ?> (Kuota: <?php echo$poli['kuota_harian']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" onclick="closeInputModal()" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow">Simpan & Ambil Tiket</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        let isAudioMuted = false;
        const queueData = <?php echo json_encode($dataAntreanDB ?? []); ?>;

        function openInputModal() {
            document.getElementById('inputModal').classList.remove('hidden');
            document.getElementById('inputModal').classList.add('flex');
        }
        function closeInputModal() {
            document.getElementById('inputModal').classList.add('hidden');
            document.getElementById('inputModal').classList.remove('flex');
        }

        function filterTable() {
            const query = document.getElementById('searchQuery').value.toLowerCase().trim();
            const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
            const rows = document.querySelectorAll('.queue-row');

            rows.forEach(row => {
                const text = row.getAttribute('data-search');
                const status = row.getAttribute('data-status');
                
                const matchesSearch = text.includes(query);
                const matchesStatus = (statusFilter === 'all' || status === statusFilter);

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function announceText(text) {
            if (isAudioMuted) return;
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                utterance.rate = 0.9;
                window.speechSynthesis.speak(utterance);
            }
        }

        function panggilPasien(num, name, poli) {
            document.getElementById('displayQueueNum').innerText = num;
            document.getElementById('displayPatientName').innerText = name;
            document.getElementById('displayPatientPoli').innerText = poli + " • Dipanggil";
            announceText(`Nomor antrean, ${num.replace(/-/g, ' ')}, atas nama ${name}, silakan menuju ${poli}`);
        }

        function actionCallNext() {
            const nextPatient = queueData.find(item => {
                const st = (item.status || item.status_antrean || '').toLowerCase();
                return st === 'menunggu';
            });

            if (nextPatient) {
                const num = nextPatient.nomor_antrean || nextPatient.no_antrean || nextPatient.kode_antrean || nextPatient.tiket || nextPatient.nomor || nextPatient.no_tiket || ('A-' + String(nextPatient.id_antrean || '1').padStart(3, '0'));
                const name = nextPatient.nama_lengkap || '-';
                const poli = nextPatient.nama_poli || '-';

                document.getElementById('displayQueueNum').innerText = num;
                document.getElementById('displayPatientName').innerText = name;
                document.getElementById('displayPatientPoli').innerText = poli + " • Dipanggil";
                nextPatient.status = 'dipanggil';

                announceText(`Nomor antrean, ${num.replace(/-/g, ' ')}, atas nama ${name}, silakan menuju ${poli}`);
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Antrean Habis',
                    text: 'Tidak ada pasien dalam status menunggu saat ini.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }

        function actionRecallCurrent() {
            const currentNum = document.getElementById('displayQueueNum').innerText;
            const currentName = document.getElementById('displayPatientName').innerText;
            const currentPoli = document.getElementById('displayPatientPoli').innerText.replace(" • Dipanggil", "");

            if (currentNum === '---' || currentName === 'Belum ada pasien dipanggil') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Ada Panggilan',
                    text: 'Silakan tekan Panggil Berikutnya terlebih dahulu.',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }

            announceText(`Panggilan ulang. Nomor antrean, ${currentNum.replace(/-/g, ' ')}, atas nama ${currentName}, silakan menuju ${currentPoli}`);
        }

        function toggleDarkMode() { document.documentElement.classList.toggle('dark'); }
        function toggleAudio() { 
            isAudioMuted = !isAudioMuted; 
            document.getElementById('audioIcon').className = isAudioMuted ? 'fa-solid fa-volume-xmark text-rose-500' : 'fa-solid fa-volume-high text-teal-600';
        }
    </script>
</body>
</html>