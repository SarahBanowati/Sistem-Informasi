<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POV Admin - Sistem Antrean Klinik Sehat Bersama</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0284c7',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        emeraldCustom: {
                            500: '#10b981',
                            600: '#059669',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .animate-pulse-slow { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .6; } }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col bg-slate-50">

    <!-- Top Navigation Header -->
    <header class="bg-gradient-to-r from-sky-700 via-sky-800 to-teal-800 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            <!-- Logo & Title -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center text-teal-300 font-bold text-xl shadow-inner">
                    <i class="fa-solid font-bold fa-hospital-user"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="font-bold text-lg leading-tight tracking-wide">Klinik Sehat Bersama</h1>
                        <span class="bg-teal-400/20 text-teal-200 border border-teal-300/30 text-xs px-2 py-0.5 rounded-full font-medium">POV Admin</span>
                    </div>
                    <p class="text-xs text-sky-200/90 flex items-center gap-2 mt-0.5">
                        <span><i class="fa-solid fa-location-dot text-teal-300 mr-1"></i>Loket Utama & Polymaster</span>
                        <span>•</span>
                        <span id="liveClockDisplay" class="font-mono text-teal-100">--:--:-- WIB</span>
                    </p>
                </div>
            </div>

            <!-- Profile & Status Controls -->
            <div class="flex items-center justify-between md:justify-end gap-3 border-t md:border-t-0 pt-2 md:pt-0 border-white/10">
                <!-- Shift Toggle -->
                <div class="flex items-center bg-black/20 rounded-lg p-1 border border-white/10">
                    <button id="shiftBtnActive" onclick="setShiftStatus('Aktif')" class="px-3 py-1 rounded-md text-xs font-semibold transition-all bg-emerald-500 text-white shadow-sm">
                        <i class="fa-solid fa-circle text-[8px] mr-1.5 animate-pulse"></i>Shift Aktif
                    </button>
                    <button id="shiftBtnRest" onclick="setShiftStatus('Istirahat')" class="px-3 py-1 rounded-md text-xs font-semibold text-sky-200 hover:text-white transition-all">
                        Istirahat
                    </button>
                </div>

                <!-- Petugas Profile Card -->
                <div class="flex items-center space-x-3 bg-white/10 px-3 py-1.5 rounded-xl border border-white/15">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=100&auto=format&fit=crop&q=80" alt="Doctor" class="w-9 h-9 rounded-full object-cover border-2 border-teal-300">
                        <span id="shiftStatusIndicator" class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-400 border-2 border-sky-900 rounded-full"></span>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-semibold leading-tight text-white">Dr. Albarra Mahatma</p>
                        <p class="text-[11px] text-teal-200" id="currentPoliLabel">Penanggung Jawab Poli</p>
                    </div>
                </div>

                <!-- Reset Data Button -->
                <button onclick="resetMockData()" title="Reset Data Antrean Mockup" class="p-2 text-sky-200 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 flex-1 w-full">

        <!-- Top Summary Cards (KPIs) -->
        <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 hover:shadow-md transition-all">
                <div class="flex items-center justify-between text-slate-500 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Total Antrean</span>
                    <span class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center"><i class="fa-solid fa-users"></i></span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-800" id="statTotal">0</div>
                <p class="text-[11px] text-slate-400 mt-1">Keseluruhan pendaftaran hari ini</p>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 hover:shadow-md transition-all">
                <div class="flex items-center justify-between text-amber-600 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Menunggu</span>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-clock"></i></span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-amber-600" id="statWaiting">0</div>
                <p class="text-[11px] text-slate-400 mt-1">Dalam antrean tunggu</p>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-sky-300 ring-2 ring-sky-100 hover:shadow-md transition-all">
                <div class="flex items-center justify-between text-sky-600 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Sedang Dilayani</span>
                    <span class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center animate-pulse"><i class="fa-solid fa-stethoscope"></i></span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-sky-600" id="statServing">0</div>
                <p class="text-[11px] text-sky-600/80 font-medium mt-1">Di dalam ruang pemeriksaan</p>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 hover:shadow-md transition-all">
                <div class="flex items-center justify-between text-emerald-600 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Selesai</span>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-circle-check"></i></span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600" id="statCompleted">0</div>
                <p class="text-[11px] text-slate-400 mt-1">Pelayanan tuntas</p>
            </div>

            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 hover:shadow-md transition-all col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between text-rose-500 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Dilewati</span>
                    <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-forward"></i></span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-rose-600" id="statSkipped">0</div>
                <p class="text-[11px] text-slate-400 mt-1">Tidak hadir saat dipanggil</p>
            </div>
        </section>

        <!-- Main Controller & Walk-in Form Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Panel Kontrol Panggilan Utama (8 Cols) -->
            <section class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 rounded-full bg-rose-500 animate-ping"></div>
                        <h2 class="font-bold text-base tracking-wide uppercase text-slate-100">Main Queue Call Controller</h2>
                    </div>
                    <!-- Poli Filter for Call Controller -->
                    <div class="flex items-center space-x-2">
                        <label for="callPoliFilter" class="text-xs text-slate-300 font-medium hidden sm:inline">Poli Aktif:</label>
                        <select id="callPoliFilter" onchange="changeActiveCallPoli(this.value)" class="bg-slate-800 text-teal-300 text-xs font-bold rounded-lg px-3 py-1.5 border border-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-400">
                            <option value="U">Poli Umum (U)</option>
                            <option value="G">Poli Gigi (G)</option>
                            <option value="K">Poli KIA / Anak (K)</option>
                            <option value="M">Poli Mata (M)</option>
                        </select>
                    </div>
                </div>

                <div class="p-6 flex-1 flex flex-col justify-between space-y-6">
                    
                    <!-- Display Antrean Besar -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Box Dipanggil / Dilayani Saat Ini -->
                        <div class="bg-gradient-to-br from-sky-50 via-teal-50 to-emerald-50 rounded-2xl p-6 border-2 border-sky-200 flex flex-col items-center justify-center text-center relative overflow-hidden shadow-inner">
                            <span class="bg-sky-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-2" id="currentCallBadge">
                                Sedang Dipanggil / Dilayani
                            </span>
                            <div class="text-5xl sm:text-6xl font-black text-sky-900 font-mono tracking-tight my-2" id="currentCallNum">
                                ---
                            </div>
                            <div class="text-sm font-bold text-slate-700 max-w-full truncate px-2" id="currentCallName">
                                Belum Ada Pasien
                            </div>
                            <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5" id="currentCallSub">
                                <i class="fa-solid fa-hospital-user text-teal-600"></i> Pilih Poli & Tekan Panggil
                            </div>

                            <!-- Live Audio Visualizer Animation Indicator -->
                            <div id="audioAnim" class="hidden absolute bottom-2 right-3 flex items-end space-x-1 h-4">
                                <div class="w-1 bg-teal-500 animate-bounce h-2"></div>
                                <div class="w-1 bg-teal-500 animate-bounce h-4" style="animation-delay: 0.1s"></div>
                                <div class="w-1 bg-teal-500 animate-bounce h-3" style="animation-delay: 0.2s"></div>
                            </div>
                        </div>

                        <!-- Box Pasien Menunggu Berikutnya -->
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 flex flex-col items-center justify-center text-center">
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-2">
                                Antrean Berikutnya
                            </span>
                            <div class="text-4xl sm:text-5xl font-black text-slate-700 font-mono tracking-tight my-2" id="nextCallNum">
                                ---
                            </div>
                            <div class="text-sm font-semibold text-slate-600 max-w-full truncate px-2" id="nextCallName">
                                Tidak Ada Antrean
                            </div>
                            <p class="text-xs text-slate-400 mt-1" id="nextCallPoliInfo">Poli Umum</p>
                        </div>
                    </div>

                    <!-- Tombol Aksi Panggilan Utama -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                        
                        <button onclick="actionNextCall()" class="col-span-2 sm:col-span-2 bg-gradient-to-r from-sky-600 to-teal-600 hover:from-sky-700 hover:to-teal-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center space-x-2 text-sm">
                            <i class="fa-solid fa-bullhorn text-lg"></i>
                            <span>PANGGIL BERIKUTNYA</span>
                        </button>

                        <button onclick="actionRecall()" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3.5 px-3 rounded-xl shadow transition-all flex items-center justify-center space-x-1.5 text-xs">
                            <i class="fa-solid fa-rotate-right"></i>
                            <span>Panggil Ulang</span>
                        </button>

                        <button onclick="actionStartService()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-3 rounded-xl shadow transition-all flex items-center justify-center space-x-1.5 text-xs">
                            <i class="fa-solid fa-play"></i>
                            <span>Mulai Melayani</span>
                        </button>

                        <button onclick="actionCompleteService()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-3 rounded-xl shadow transition-all flex items-center justify-center space-x-1.5 text-xs">
                            <i class="fa-solid fa-check"></i>
                            <span>Selesai</span>
                        </button>

                    </div>

                    <!-- Secondary Control Row -->
                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                        <button onclick="actionSkip()" class="text-rose-600 hover:text-rose-800 hover:bg-rose-50 px-3 py-1.5 rounded-lg font-semibold text-xs transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-forward"></i> Lewati Antrean (Pasien Tidak Hadir)
                        </button>

                        <!-- Web Audio Voice Test Toggle -->
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="audioToggle" checked class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500">
                            <label for="audioToggle" class="text-xs text-slate-600 font-medium cursor-pointer">
                                <i class="fa-solid fa-volume-high text-teal-600 mr-1"></i> Suara Panggilan (Bahasa Indonesia)
                            </label>
                        </div>
                    </div>

                </div>
            </section>

            <!-- Panel Pendaftaran Walk-In On-Site (4 Cols) -->
            <section class="lg:col-span-4 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col">
                <div class="bg-teal-700 text-white px-5 py-4 rounded-t-2xl flex items-center justify-between">
                    <h3 class="font-bold text-sm tracking-wide flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-teal-200"></i> Pendaftaran On-Site / Walk-in
                    </h3>
                    <span class="text-[11px] bg-teal-800 text-teal-200 px-2 py-0.5 rounded font-medium">Direct Register</span>
                </div>

                <form id="walkinForm" onsubmit="handleWalkinSubmit(event)" class="p-5 space-y-4 flex-1 flex flex-col justify-between">
                    <div class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Pasien *</label>
                            <input type="text" id="walkinName" required placeholder="Contoh: Bpk. Sastro Wardoyo" class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. HP / NIK Pasien</label>
                            <input type="text" id="walkinPhone" placeholder="0812xxxx atau 3511xxxx" class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Poli Tujuan *</label>
                                <select id="walkinPoli" required class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none bg-white font-medium">
                                    <option value="U">Poli Umum (U)</option>
                                    <option value="G">Poli Gigi (G)</option>
                                    <option value="K">Poli KIA (K)</option>
                                    <option value="M">Poli Mata (M)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Pembayaran *</label>
                                <select id="walkinType" required class="w-full text-sm rounded-xl border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none bg-white font-medium">
                                    <option value="BPJS">BPJS Kesehatan</option>
                                    <option value="Umum">Umum / Mandiri</option>
                                </select>
                            </div>
                        </div>

                        <div class="p-3 bg-sky-50 rounded-xl border border-sky-100 text-xs text-sky-800 flex items-start gap-2">
                            <i class="fa-solid fa-circle-info text-sky-500 mt-0.5"></i>
                            <span>Nomor antrean cetak akan secara otomatis digenerate mengikuti urutan kuota Poli yang dipilih.</span>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-xl shadow transition-all flex items-center justify-center space-x-2 text-sm mt-4">
                        <i class="fa-solid fa-ticket"></i>
                        <span>Cetak & Tambah Antrean</span>
                    </button>
                </form>
            </section>
        </div>

        <!-- Panel Manajemen Kuota & Pengaturan Poli -->
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-teal-600"></i> Pengaturan Sesi & Batas Kuota Kuota Poli
                    </h3>
                    <p class="text-xs text-slate-500">Atur status pendaftaran dan batas maksimum kuota antrean harian per poli.</p>
                </div>
                <span class="text-xs font-semibold text-sky-700 bg-sky-50 px-3 py-1 rounded-full border border-sky-200 w-fit">
                    Real-time Auto-Sync
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="poliQuotaControlsContainer">
                <!-- Dynamically rendered Poli Quota Cards -->
            </div>
        </section>

        <!-- Tabel Daftar Antrean Pasien Lintas Poli -->
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            <!-- Table Header & Filters -->
            <div class="p-5 border-b border-slate-100 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-sky-600"></i> Daftar & Status Antrean Pasien
                        </h3>
                        <p class="text-xs text-slate-500">Kelola dan pantau seluruh status pergerakan antrean pasien hari ini.</p>
                    </div>

                    <!-- Search Box -->
                    <div class="relative w-full md:w-72">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="searchInput" oninput="renderQueueTable()" placeholder="Cari nama / nomor antrean..." class="w-full text-xs pl-9 pr-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    </div>
                </div>

                <!-- Category Pills Filter -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                    
                    <!-- Poli Filter Tabs -->
                    <div class="flex flex-wrap items-center gap-1.5" id="poliFilterTabs">
                        <button onclick="setTablePoliFilter('ALL')" class="tab-poli-btn active-tab-poli px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-sky-600 text-white" data-poli="ALL">Semua Poli</button>
                        <button onclick="setTablePoliFilter('U')" class="tab-poli-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all" data-poli="U">Poli Umum (U)</button>
                        <button onclick="setTablePoliFilter('G')" class="tab-poli-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all" data-poli="G">Poli Gigi (G)</button>
                        <button onclick="setTablePoliFilter('K')" class="tab-poli-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all" data-poli="K">Poli KIA (K)</button>
                        <button onclick="setTablePoliFilter('M')" class="tab-poli-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all" data-poli="M">Poli Mata (M)</button>
                    </div>

                    <!-- Status Filter Dropdown -->
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-slate-500 font-medium">Filter Status:</span>
                        <select id="statusFilterSelect" onchange="renderQueueTable()" class="text-xs bg-slate-50 border border-slate-300 font-semibold text-slate-700 rounded-lg px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="ALL">Semua Status</option>
                            <option value="Menunggu">Menunggu</option>
                            <option value="Dipanggil">Dipanggil</option>
                            <option value="Sedang Dilayani">Sedang Dilayani</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Dilewati">Dilewati</option>
                        </select>
                    </div>

                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 text-xs uppercase font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">No. Antrean</th>
                            <th class="px-5 py-3.5">Nama Pasien</th>
                            <th class="px-5 py-3.5">Poli Tujuan</th>
                            <th class="px-5 py-3.5">Tipe Pembayaran</th>
                            <th class="px-5 py-3.5">Waktu Daftar</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-center">Aksi Langsung</th>
                        </tr>
                    </thead>
                    <tbody id="queueTableBody" class="divide-y divide-slate-100">
                        <!-- Dynamic Queue Rows -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Stats -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span id="showingCountInfo">Menampilkan 0 antrean</span>
                <span class="italic"><i class="fa-solid fa-sync mr-1"></i> Data tersimpan secara otomatis di LocalStorage</span>
            </div>
        </section>

    </main>

    <!-- Modal Notifications / Dialog Custom -->
    <div id="toastNotification" class="fixed bottom-5 right-5 transform translate-y-20 opacity-0 transition-all duration-300 z-50 bg-slate-900 text-white px-5 py-3 rounded-xl shadow-2xl flex items-center space-x-3">
        <i id="toastIcon" class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
        <span id="toastMessage" class="text-sm font-medium">Notifikasi berhasil</span>
    </div>

    <!-- Application Script Engine -->
    <script>
        // CONFIG & STATE MANAGEMENT
        const STORAGE_KEY = 'PUSKESMAS_ANTREAN_DATA_V1';

        // Default Config Poli
        const POLI_CONFIG = {
            'U': { name: 'Poli Umum', color: 'sky', code: 'U', quota: 30, isOpen: true },
            'G': { name: 'Poli Gigi', color: 'teal', code: 'G', quota: 15, isOpen: true },
            'K': { name: 'Poli KIA / Anak', color: 'indigo', code: 'K', quota: 20, isOpen: true },
            'M': { name: 'Poli Mata', color: 'amber', code: 'M', quota: 10, isOpen: true }
        };

        // Mock Initial Data if storage empty
        const INITIAL_MOCK_QUEUES = [
            { id: '1', ticket: 'U-001', name: 'Bpk. Ahmad Subagyo', phone: '08123456789', poli: 'U', type: 'BPJS', time: '08:05 WIB', status: 'Selesai' },
            { id: '2', ticket: 'U-002', name: 'Ibu Siti Aminah', phone: '08198765432', poli: 'U', type: 'Umum', time: '08:12 WIB', status: 'Sedang Dilayani' },
            { id: '3', ticket: 'U-003', name: 'Bpk. Hendra Gunawan', phone: '08521122334', poli: 'U', type: 'BPJS', time: '08:20 WIB', status: 'Menunggu' },
            { id: '4', ticket: 'U-004', name: 'Sdr. Rian Pratama', phone: '08771234123', poli: 'U', type: 'BPJS', time: '08:35 WIB', status: 'Menunggu' },
            { id: '5', ticket: 'G-001', name: 'Ibu Ratna Sari', phone: '08139988776', poli: 'G', type: 'Umum', time: '08:10 WIB', status: 'Selesai' },
            { id: '6', ticket: 'G-002', name: 'Bpk. Bambang Tri', phone: '08214455667', poli: 'G', type: 'BPJS', time: '08:30 WIB', status: 'Menunggu' },
            { id: '7', ticket: 'K-001', name: 'Ananda Kevin & Ibu', phone: '08531111222', poli: 'K', type: 'BPJS', time: '08:15 WIB', status: 'Menunggu' },
            { id: '8', ticket: 'M-001', name: 'Bpk. Hj. Kholil', phone: '08120000999', poli: 'M', type: 'Umum', time: '08:22 WIB', status: 'Dilewati' }
        ];

        // Global Active Filters
        let activeCallPoli = 'U';
        let activeTablePoliFilter = 'ALL';
        let appData = {
            queues: [],
            poliSettings: { ...POLI_CONFIG },
            shiftStatus: 'Aktif'
        };

        // Initialize App
        window.onload = function() {
            loadDataFromStorage();
            startLiveClock();
            renderPoliQuotaControls();
            renderQueueTable();
            updateDashboardStats();
            updateCallControllerDisplay();

            // Storage sync listener for multi-tab
            window.addEventListener('storage', (e) => {
                if (e.key === STORAGE_KEY) {
                    loadDataFromStorage(false);
                    renderQueueTable();
                    updateDashboardStats();
                    updateCallControllerDisplay();
                    renderPoliQuotaControls();
                }
            });
        };

        // Live Clock
        function startLiveClock() {
            const clockEl = document.getElementById('liveClockDisplay');
            setInterval(() => {
                const now = new Date();
                clockEl.innerText = now.toLocaleTimeString('id-ID') + ' WIB';
            }, 1000);
        }

        // LOAD & SAVE LOCALSTORAGE
        function loadDataFromStorage(shouldSetMockIfEmpty = true) {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                try {
                    appData = JSON.parse(saved);
                } catch(e) {
                    console.error('Failed parsing storage data', e);
                }
            } else if (shouldSetMockIfEmpty) {
                appData.queues = [...INITIAL_MOCK_QUEUES];
                saveDataToStorage();
            }
        }

        function saveDataToStorage() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(appData));
            // Trigger storage update
            updateDashboardStats();
            updateCallControllerDisplay();
        }

        function resetMockData() {
            if(confirm('Apakah Anda yakin ingin mereset seluruh data antrean ke data awal (mockup)?')) {
                appData.queues = [...INITIAL_MOCK_QUEUES];
                appData.poliSettings = JSON.parse(JSON.stringify(POLI_CONFIG));
                saveDataToStorage();
                renderPoliQuotaControls();
                renderQueueTable();
                showToast('Data antrean berhasil direset!');
            }
        }

        // SHIFT STATUS CONTROLLER
        function setShiftStatus(status) {
            appData.shiftStatus = status;
            saveDataToStorage();

            const btnActive = document.getElementById('shiftBtnActive');
            const btnRest = document.getElementById('shiftBtnRest');
            const indicator = document.getElementById('shiftStatusIndicator');

            if (status === 'Aktif') {
                btnActive.className = "px-3 py-1 rounded-md text-xs font-semibold transition-all bg-emerald-500 text-white shadow-sm";
                btnRest.className = "px-3 py-1 rounded-md text-xs font-semibold text-sky-200 hover:text-white transition-all";
                indicator.className = "absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-400 border-2 border-sky-900 rounded-full";
                showToast('Shift Petugas: AKTIF');
            } else {
                btnActive.className = "px-3 py-1 rounded-md text-xs font-semibold text-sky-200 hover:text-white transition-all";
                btnRest.className = "px-3 py-1 rounded-md text-xs font-semibold bg-amber-500 text-white shadow-sm";
                indicator.className = "absolute bottom-0 right-0 w-2.5 h-2.5 bg-amber-400 border-2 border-sky-900 rounded-full";
                showToast('Shift Petugas: ISTIRAHAT');
            }
        }

        // QUEUE CALL CONTROLLER LOGIC
        function changeActiveCallPoli(poliCode) {
            activeCallPoli = poliCode;
            document.getElementById('currentPoliLabel').innerText = `Penanggung Jawab ${POLI_CONFIG[poliCode].name}`;
            updateCallControllerDisplay();
        }

        function getPoliQueues(poliCode) {
            return appData.queues.filter(q => q.poli === poliCode);
        }

        function updateCallControllerDisplay() {
            const poliQueues = getPoliQueues(activeCallPoli);
            
            // Find current active call/serving
            const currentCall = poliQueues.find(q => q.status === 'Dipanggil' || q.status === 'Sedang Dilayani');
            // Find next waiting
            const nextCall = poliQueues.find(q => q.status === 'Menunggu');

            const currentNumEl = document.getElementById('currentCallNum');
            const currentNameEl = document.getElementById('currentCallName');
            const currentBadgeEl = document.getElementById('currentCallBadge');
            const currentSubEl = document.getElementById('currentCallSub');

            if (currentCall) {
                currentNumEl.innerText = currentCall.ticket;
                currentNameEl.innerText = currentCall.name;
                currentBadgeEl.innerText = currentCall.status === 'Dipanggil' ? 'DIPANGGIL ke Loket/Ruang' : 'SEDANG DILAYANI';
                currentBadgeEl.className = currentCall.status === 'Dipanggil' ? 
                    'bg-amber-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-2 animate-pulse' : 
                    'bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-2';
                currentSubEl.innerHTML = `<i class="fa-solid fa-clock text-sky-600"></i> Terdaftar ${currentCall.time} (${currentCall.type})`;
            } else {
                currentNumEl.innerText = '---';
                currentNameEl.innerText = 'Belum Ada Pasien Dipanggil';
                currentBadgeEl.innerText = 'Loket Standby';
                currentBadgeEl.className = 'bg-slate-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full mb-2';
                currentSubEl.innerHTML = `<i class="fa-solid fa-info-circle text-sky-600"></i> Tekan 'Panggil Berikutnya'`;
            }

            // Next Queue
            const nextNumEl = document.getElementById('nextCallNum');
            const nextNameEl = document.getElementById('nextCallName');
            const nextPoliInfoEl = document.getElementById('nextCallPoliInfo');

            if (nextCall) {
                nextNumEl.innerText = nextCall.ticket;
                nextNameEl.innerText = nextCall.name;
                nextPoliInfoEl.innerText = `${POLI_CONFIG[activeCallPoli].name} • Urutan #1`;
            } else {
                nextNumEl.innerText = '---';
                nextNameEl.innerText = 'Tidak Ada Antrean Menunggu';
                nextPoliInfoEl.innerText = POLI_CONFIG[activeCallPoli].name;
            }
        }

        // CALL ACTION BUTTONS
        function actionNextCall() {
            const poliQueues = getPoliQueues(activeCallPoli);
            
            // Check if there is someone currently in 'Dipanggil' state -> complete or prompt
            const currentServing = poliQueues.find(q => q.status === 'Sedang Dilayani');
            if (currentServing) {
                // Auto finish current serving or notify
                currentServing.status = 'Selesai';
            }

            const currentCalled = poliQueues.find(q => q.status === 'Dipanggil');
            if (currentCalled) {
                currentCalled.status = 'Dilewati'; // If called but next pressed without start, mark skip
            }

            // Get next waiting
            const nextWaiting = poliQueues.find(q => q.status === 'Menunggu');

            if (!nextWaiting) {
                showToast(`Tidak ada antrean 'Menunggu' di ${POLI_CONFIG[activeCallPoli].name}`, 'warning');
                saveDataToStorage();
                renderQueueTable();
                return;
            }

            nextWaiting.status = 'Dipanggil';
            saveDataToStorage();
            renderQueueTable();
            updateCallControllerDisplay();

            // Voice synthesis announcement
            playVoiceCall(nextWaiting.ticket, POLI_CONFIG[activeCallPoli].name);
            showToast(`Memanggil ${nextWaiting.ticket} - ${nextWaiting.name}`);
        }

        function actionRecall() {
            const poliQueues = getPoliQueues(activeCallPoli);
            const current = poliQueues.find(q => q.status === 'Dipanggil' || q.status === 'Sedang Dilayani');

            if (!current) {
                showToast('Tidak ada antrean yang sedang dipanggil saat ini', 'warning');
                return;
            }

            playVoiceCall(current.ticket, POLI_CONFIG[activeCallPoli].name);
            showToast(`Memanggil Ulang ${current.ticket}`);
        }

        function actionStartService() {
            const poliQueues = getPoliQueues(activeCallPoli);
            const current = poliQueues.find(q => q.status === 'Dipanggil');

            if (!current) {
                showToast('Panggil antrean terlebih dahulu sebelum memulai pelayanan', 'warning');
                return;
            }

            current.status = 'Sedang Dilayani';
            saveDataToStorage();
            renderQueueTable();
            updateCallControllerDisplay();
            showToast(`Pelayanan ${current.ticket} (${current.name}) Dimulai`);
        }

        function actionCompleteService() {
            const poliQueues = getPoliQueues(activeCallPoli);
            const current = poliQueues.find(q => q.status === 'Sedang Dilayani' || q.status === 'Dipanggil');

            if (!current) {
                showToast('Tidak ada pasien yang sedang dilayani untuk diselesaikan', 'warning');
                return;
            }

            current.status = 'Selesai';
            saveDataToStorage();
            renderQueueTable();
            updateCallControllerDisplay();
            showToast(`Pelayanan ${current.ticket} Selesai!`);
        }

        function actionSkip() {
            const poliQueues = getPoliQueues(activeCallPoli);
            const current = poliQueues.find(q => q.status === 'Dipanggil' || q.status === 'Sedang Dilayani');

            if (!current) {
                showToast('Tidak ada antrean dipanggil untuk dilewati', 'warning');
                return;
            }

            current.status = 'Dilewati';
            saveDataToStorage();
            renderQueueTable();
            updateCallControllerDisplay();
            showToast(`Antrean ${current.ticket} dilewati (Pasien Tidak Hadir)`, 'danger');
        }

        // WEB SPEECH SYNTHESIZER (VOICE CALL)
        function playVoiceCall(ticketCode, poliName) {
            const audioToggle = document.getElementById('audioToggle');
            if (!audioToggle.checked) return;

            // Audio visualizer animation
            const animEl = document.getElementById('audioAnim');
            if (animEl) animEl.classList.remove('hidden');

            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel(); // Stop any pending speech

                // Format ticket e.g. U-003 to "U, 0 0 3" or "U tiga"
                const parts = ticketCode.split('-');
                const letter = parts[0];
                const number = parseInt(parts[1], 10);

                const textToSpeak = `Nomor antrean, ${letter}, ${number}. Silakan menuju, ${poliName}.`;
                const utterance = new SpeechSynthesisUtterance(textToSpeak);
                utterance.lang = 'id-ID';
                utterance.rate = 0.88; // Slightly calm speed
                utterance.pitch = 1;

                utterance.onend = function() {
                    if (animEl) animEl.classList.add('hidden');
                };

                utterance.onerror = function() {
                    if (animEl) animEl.classList.add('hidden');
                }

                window.speechSynthesis.speak(utterance);
            } else {
                console.log('Web Speech API not supported in this browser.');
                if (animEl) setTimeout(() => animEl.classList.add('hidden'), 2000);
            }
        }

        // WALK-IN ON-SITE FORM REGISTRATION
        function handleWalkinSubmit(event) {
            event.preventDefault();

            const name = document.getElementById('walkinName').value.trim();
            const phone = document.getElementById('walkinPhone').value.trim() || 'Walk-in Direct';
            const poli = document.getElementById('walkinPoli').value;
            const type = document.getElementById('walkinType').value;

            // Check if Poli registration is open
            if (!appData.poliSettings[poli].isOpen) {
                showToast(`Pendaftaran ${POLI_CONFIG[poli].name} saat ini sedang DITUTUP!`, 'danger');
                return;
            }

            // Check Quota Limit
            const currentPoliCount = appData.queues.filter(q => q.poli === poli).length;
            if (currentPoliCount >= appData.poliSettings[poli].quota) {
                showToast(`Kuota pendaftaran ${POLI_CONFIG[poli].name} sudah PENUH (${appData.poliSettings[poli].quota})!`, 'danger');
                return;
            }

            // Generate Ticket Number
            const nextNum = currentPoliCount + 1;
            const formattedNum = String(nextNum).padStart(3, '0');
            const ticket = `${poli}-${formattedNum}`;

            const now = new Date();
            const timeStr = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')} WIB`;

            const newQueue = {
                id: Date.now().toString(),
                ticket: ticket,
                name: name,
                phone: phone,
                poli: poli,
                type: type,
                time: timeStr,
                status: 'Menunggu'
            };

            appData.queues.push(newQueue);
            saveDataToStorage();

            // Reset form
            document.getElementById('walkinName').value = '';
            document.getElementById('walkinPhone').value = '';

            renderQueueTable();
            renderPoliQuotaControls();
            updateCallControllerDisplay();

            showToast(`Berhasil mendaftar! No. Antrean: ${ticket}`);
        }

        // POLI QUOTA & TOGGLE CONTROLS
        function renderPoliQuotaControls() {
            const container = document.getElementById('poliQuotaControlsContainer');
            if (!container) return;

            container.innerHTML = '';

            Object.keys(POLI_CONFIG).forEach(code => {
                const conf = POLI_CONFIG[code];
                const setting = appData.poliSettings[code] || conf;
                const count = appData.queues.filter(q => q.poli === code).length;
                const percent = Math.min(100, Math.round((count / setting.quota) * 100));

                const cardHtml = `
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex flex-col justify-between space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-extrabold text-sky-700 bg-sky-100 px-2 py-0.5 rounded">${code}</span>
                                <h4 class="font-bold text-sm text-slate-800 mt-1">${conf.name}</h4>
                            </div>
                            <!-- Switch Toggle -->
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" ${setting.isOpen ? 'checked' : ''} onchange="togglePoliStatus('${code}', this.checked)" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-xs text-slate-600 mb-1">
                                <span>Terisi: <strong class="text-slate-800">${count}</strong> / ${setting.quota} Pasien</span>
                                <span class="font-bold ${percent >= 100 ? 'text-rose-600' : 'text-emerald-600'}">${percent}%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r ${percent >= 100 ? 'from-rose-500 to-red-600' : 'from-teal-500 to-emerald-500'} h-2 rounded-full" style="width: ${percent}%"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                            <span class="text-[11px] font-medium text-slate-500">Batas Kuota:</span>
                            <div class="flex items-center space-x-1">
                                <button onclick="adjustQuota('${code}', -5)" class="w-6 h-6 rounded bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold">-</button>
                                <span class="text-xs font-bold text-slate-800 px-1">${setting.quota}</span>
                                <button onclick="adjustQuota('${code}', 5)" class="w-6 h-6 rounded bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold">+</button>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += cardHtml;
            });
        }

        function togglePoliStatus(code, isOpen) {
            appData.poliSettings[code].isOpen = isOpen;
            saveDataToStorage();
            renderPoliQuotaControls();
            showToast(`${POLI_CONFIG[code].name} sekarang ${isOpen ? 'DIBUKA' : 'DITUTUP'}`);
        }

        function adjustQuota(code, delta) {
            let current = appData.poliSettings[code].quota || 20;
            current = Math.max(5, current + delta);
            appData.poliSettings[code].quota = current;
            saveDataToStorage();
            renderPoliQuotaControls();
            showToast(`Batas kuota ${POLI_CONFIG[code].name} diubah menjadi ${current}`);
        }

        // QUEUE TABLE RENDER & FILTERING
        function setTablePoliFilter(poliCode) {
            activeTablePoliFilter = poliCode;
            
            // Update active styles on buttons
            document.querySelectorAll('.tab-poli-btn').forEach(btn => {
                if (btn.getAttribute('data-poli') === poliCode) {
                    btn.className = "tab-poli-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-sky-600 text-white shadow-sm";
                } else {
                    btn.className = "tab-poli-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all";
                }
            });

            renderQueueTable();
        }

        function renderQueueTable() {
            const tbody = document.getElementById('queueTableBody');
            const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
            const statusFilter = document.getElementById('statusFilterSelect').value;

            let filtered = [...appData.queues];

            // Poli Filter
            if (activeTablePoliFilter !== 'ALL') {
                filtered = filtered.filter(q => q.poli === activeTablePoliFilter);
            }

            // Status Filter
            if (statusFilter !== 'ALL') {
                filtered = filtered.filter(q => q.status === statusFilter);
            }

            // Search
            if (searchVal) {
                filtered = filtered.filter(q => 
                    q.name.toLowerCase().includes(searchVal) || 
                    q.ticket.toLowerCase().includes(searchVal) ||
                    q.phone.toLowerCase().includes(searchVal)
                );
            }

            document.getElementById('showingCountInfo').innerText = `Menampilkan ${filtered.length} dari total ${appData.queues.length} antrean`;

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-slate-400 italic">
                            <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                            Tidak ada data antrean yang sesuai kriteria filter.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = filtered.map(q => {
                const poliName = POLI_CONFIG[q.poli] ? POLI_CONFIG[q.poli].name : q.poli;
                
                // Status Badge Color
                let badgeClass = "bg-slate-100 text-slate-700";
                if (q.status === 'Menunggu') badgeClass = "bg-amber-100 text-amber-800 border border-amber-200";
                else if (q.status === 'Dipanggil') badgeClass = "bg-amber-500 text-white animate-pulse";
                else if (q.status === 'Sedang Dilayani') badgeClass = "bg-sky-600 text-white";
                else if (q.status === 'Selesai') badgeClass = "bg-emerald-100 text-emerald-800 border border-emerald-200";
                else if (q.status === 'Dilewati') badgeClass = "bg-rose-100 text-rose-800 border border-rose-200";

                return `
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-3.5 font-bold font-mono text-slate-900 text-base">
                            ${q.ticket}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-800">${q.name}</div>
                            <div class="text-[11px] text-slate-400"><i class="fa-solid fa-phone text-[10px] mr-1"></i>${q.phone}</div>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-slate-700">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 border border-slate-200">${poliName}</span>
                        </td>
                        <td class="px-5 py-3.5 text-xs font-medium">
                            <span class="${q.type === 'BPJS' ? 'text-emerald-700 bg-emerald-50' : 'text-sky-700 bg-sky-50'} px-2 py-0.5 rounded border border-slate-200">
                                ${q.type}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500">
                            ${q.time}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full inline-block ${badgeClass}">
                                ${q.status}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center space-x-1.5">
                                <button onclick="quickUpdateStatus('${q.id}', 'Dipanggil')" title="Panggil" class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </button>
                                <button onclick="quickUpdateStatus('${q.id}', 'Sedang Dilayani')" title="Layani" class="p-1.5 rounded-lg text-sky-600 hover:bg-sky-50">
                                    <i class="fa-solid fa-stethoscope"></i>
                                </button>
                                <button onclick="quickUpdateStatus('${q.id}', 'Selesai')" title="Selesaikan" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50">
                                    <i class="fa-solid fa-circle-check"></i>
                                </button>
                                <button onclick="deleteQueueRow('${q.id}')" title="Hapus" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function quickUpdateStatus(id, newStatus) {
            const queue = appData.queues.find(q => q.id === id);
            if (!queue) return;

            queue.status = newStatus;
            saveDataToStorage();
            renderQueueTable();
            updateCallControllerDisplay();

            if (newStatus === 'Dipanggil') {
                playVoiceCall(queue.ticket, POLI_CONFIG[queue.poli].name);
            }

            showToast(`Status ${queue.ticket} diubah ke '${newStatus}'`);
        }

        function deleteQueueRow(id) {
            if (confirm('Hapus antrean pasien ini?')) {
                appData.queues = appData.queues.filter(q => q.id !== id);
                saveDataToStorage();
                renderQueueTable();
                renderPoliQuotaControls();
                updateCallControllerDisplay();
                showToast('Antrean berhasil dihapus');
            }
        }

        // UPDATE DASHBOARD STATS
        function updateDashboardStats() {
            const queues = appData.queues;
            document.getElementById('statTotal').innerText = queues.length;
            document.getElementById('statWaiting').innerText = queues.filter(q => q.status === 'Menunggu').length;
            document.getElementById('statServing').innerText = queues.filter(q => q.status === 'Dipanggil' || q.status === 'Sedang Dilayani').length;
            document.getElementById('statCompleted').innerText = queues.filter(q => q.status === 'Selesai').length;
            document.getElementById('statSkipped').innerText = queues.filter(q => q.status === 'Dilewati').length;
        }

        // TOAST NOTIFICATION UTILITY
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toastNotification');
            const icon = document.getElementById('toastIcon');
            const text = document.getElementById('toastMessage');

            text.innerText = msg;

            if (type === 'danger') {
                icon.className = "fa-solid fa-circle-xmark text-rose-400 text-lg";
            } else if (type === 'warning') {
                icon.className = "fa-solid fa-triangle-exclamation text-amber-400 text-lg";
            } else {
                icon.className = "fa-solid fa-circle-check text-emerald-400 text-lg";
            }

            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }
    </script>
</body>
</html>