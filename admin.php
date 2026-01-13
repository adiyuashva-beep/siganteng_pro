<?php
session_start();
// Cek Login Admin
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || !in_array($_SESSION['role'], ['admin','super'])) {
    header("location:login.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Control - SiGanteng</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: #e2e8f0; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .nav-item.active { background: rgba(59, 130, 246, 0.1); border-right: 3px solid #3b82f6; color: #60a5fa; }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* --- CSS SAKLAR (TOGGLE SWITCH) --- */
        .toggle-checkbox {
            appearance: none; position: absolute; z-index: 10;
            border-radius: 9999px; cursor: pointer; transition: all 0.3s ease-in-out;
            top: 0; left: 0;
        }
        /* Saat OFF */
        .toggle-checkbox { border-color: #475569; transform: translateX(0); }
        /* Saat ON */
        .toggle-checkbox:checked { border-color: #10b981; transform: translateX(100%); right: 0; left: auto; }
        /* Label Background saat ON */
        .toggle-checkbox:checked + .toggle-label { background-color: #10b981; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-sm">

    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col hidden md:flex">
        <div class="p-6">
            <h1 class="font-black text-2xl tracking-tighter text-blue-500">SiGanteng</h1>
            <p class="text-xs text-slate-500 tracking-widest uppercase mt-1">Master Control (IT)</p>
            <div id="badge-role" class="mt-2 inline-block px-2 py-1 rounded bg-slate-800 text-xs font-bold text-slate-300">ADMINISTRATOR</div>
        </div>

        <nav class="flex-1 space-y-1 px-3 mt-4 overflow-y-auto hide-scroll">
            <p class="px-4 text-[10px] font-bold text-slate-600 uppercase mb-2">Menu Utama</p>
            <button onclick="nav('dashboard')" id="nav-dashboard" class="nav-item active w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard Sistem</button>
            <button onclick="nav('data')" id="nav-data" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i data-lucide="database" class="w-5 h-5"></i> Database User</button>
            <button onclick="nav('mapel')" id="nav-mapel" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i data-lucide="library" class="w-5 h-5"></i> Database Mapel</button>
            
            <p class="px-4 text-[10px] font-bold text-slate-600 uppercase mb-2 mt-4">Pengaturan</p>
            <button onclick="nav('menu-manager')" id="nav-menu-manager" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i data-lucide="layout-grid" class="w-5 h-5"></i> App Store (Tombol)</button>
            <button onclick="nav('settings')" id="nav-settings" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i data-lucide="settings" class="w-5 h-5"></i> Setting Sistem</button>

            <p class="px-4 text-[10px] font-bold text-slate-600 uppercase mb-2 mt-4">Akses Panel Lain</p>
            <button onclick="window.open('admin_bk.php', '_blank')" class="w-full flex items-center gap-3 px-4 py-3 text-emerald-400 hover:text-white hover:bg-emerald-900/30 rounded-lg transition font-bold"><i data-lucide="user-check" class="w-5 h-5"></i> Panel BK / Kesiswaan</button>
            <button onclick="window.open('admin_kbm.php', '_blank')" class="w-full flex items-center gap-3 px-4 py-3 text-yellow-400 hover:text-white hover:bg-yellow-900/30 rounded-lg transition font-bold"><i data-lucide="book-open-check" class="w-5 h-5"></i> Panel Kurikulum (KBM)</button>
            <button onclick="window.open('admin_hebat.php', '_blank')" class="w-full flex items-center gap-3 px-4 py-3 text-indigo-400 hover:text-white hover:bg-indigo-900/30 rounded-lg transition font-bold"><i data-lucide="activity" class="w-5 h-5"></i> Dashboard 7 Hebat</button>
        </nav>

        <div class="p-4 border-t border-slate-800"><button onclick="logout()" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-500/10 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition font-bold"><i data-lucide="log-out" class="w-4 h-4"></i> Keluar</button></div>
    </aside>

    <main class="flex-1 overflow-y-auto bg-slate-950 relative p-4 md:p-8">
        <div class="md:hidden flex justify-between items-center mb-6"><h1 class="font-bold text-lg text-blue-500">Master Control</h1><button onclick="logout()" class="text-red-500"><i data-lucide="log-out"></i></button></div>

        <div id="view-dashboard" class="space-y-6 fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                <div><h2 class="text-3xl font-bold text-white">System Monitor</h2><p class="text-slate-400 text-sm">Status Server: <span class="text-emerald-400 font-bold">ONLINE (Localhost)</span></p></div>
                <button onclick="loadDashboardStats()" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2 text-xs transition border border-slate-700"><i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh Data</button>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800"><p class="text-slate-500 text-xs font-bold uppercase">Total Siswa Aktif</p><h3 class="text-3xl font-black text-white mt-1" id="stat-total">0</h3></div>
                <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800"><p class="text-slate-500 text-xs font-bold uppercase">Siswa Hadir Realtime</p><h3 class="text-3xl font-black text-emerald-400 mt-1" id="stat-hadir">0</h3></div>
                <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800"><p class="text-slate-500 text-xs font-bold uppercase">Guru Terdaftar</p><h3 class="text-3xl font-black text-orange-400 mt-1" id="stat-guru">0</h3></div>
                <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800"><p class="text-slate-500 text-xs font-bold uppercase">Mode Absen</p><h3 class="text-3xl font-black text-blue-400 mt-1" id="stat-mode">GPS</h3></div>
            </div>
            <div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden mt-6">
                <div class="p-4 border-b border-slate-800"><h3 class="font-bold text-white">Live Traffic Log (10 Terakhir)</h3></div>
                <div class="overflow-x-auto max-h-96"><table class="w-full text-left text-slate-400"><thead class="bg-slate-950 text-xs uppercase font-bold text-slate-500 sticky top-0"><tr><th class="p-4">Jam</th><th class="p-4">User</th><th class="p-4">Kelas</th><th class="p-4">Status</th></tr></thead><tbody id="tabel-live" class="divide-y divide-slate-800"></tbody></table></div>
            </div>
        </div>

        <div id="view-data" class="hidden space-y-6 fade-in">
            <h2 class="text-3xl font-bold text-white">Database Management</h2>
            <div class="flex gap-2 mb-6">
                <button onclick="gantiTabInput('excel')" id="btn-tab-excel" class="px-4 py-2 rounded-lg font-bold bg-blue-600 text-white transition">Import Excel</button>
                <button onclick="gantiTabInput('manual')" id="btn-tab-manual" class="px-4 py-2 rounded-lg font-bold bg-slate-800 text-slate-400 transition hover:text-white">Input Manual</button>
                <button onclick="gantiTabInput('manage')" id="btn-tab-manage" class="px-4 py-2 rounded-lg font-bold bg-slate-800 text-slate-400 transition hover:text-white flex items-center gap-2"><i data-lucide="search" class="w-4 h-4"></i> Cari & Edit</button>
            </div>
            <div id="area-input-excel" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800"><h3 class="font-bold text-white text-lg mb-2">Upload Data Siswa</h3><p class="text-xs text-slate-500 mb-4">Format: [Nama, NISN, Password, Kelas]</p><input type="file" id="fileSiswa" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-4"/><button onclick="prosesImport('siswa')" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded-lg">Upload Siswa</button></div>
                <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800"><h3 class="font-bold text-white text-lg mb-2">Upload Guru</h3><p class="text-xs text-slate-500 mb-4">Format: [Nama, NIP, Password, Wali Kelas]</p><input type="file" id="fileGuru" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 mb-4"/><button onclick="prosesImport('guru')" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-bold py-2 rounded-lg">Upload Guru</button></div>
            </div>
            <div id="area-input-manual" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800"><h3 class="font-bold text-white text-lg mb-4 flex items-center gap-2"><i data-lucide="user-plus" class="text-blue-500"></i> Tambah Siswa</h3><div class="space-y-4"><input type="text" id="manualNamaSiswa" class="w-full bg-slate-950 text-white p-3 rounded-lg border border-slate-700" placeholder="Nama Lengkap"><input type="text" id="manualNISN" class="w-full bg-slate-950 text-white p-3 rounded-lg border border-slate-700" placeholder="NISN"><input type="text" id="manualKelas" class="w-full bg-slate-950 text-white p-3 rounded-lg border border-slate-700" placeholder="Kelas"><button onclick="simpanManual('siswa')" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-lg transition">Simpan Siswa</button></div></div>
                <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800"><h3 class="font-bold text-white text-lg mb-4 flex items-center gap-2"><i data-lucide="briefcase" class="text-orange-500"></i> Tambah Guru</h3><div class="space-y-4"><input type="text" id="manualNamaGuru" class="w-full bg-slate-950 text-white p-3 rounded-lg border border-slate-700" placeholder="Nama Guru"><input type="text" id="manualNIP" class="w-full bg-slate-950 text-white p-3 rounded-lg border border-slate-700" placeholder="NIP"><button onclick="simpanManual('guru')" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-bold py-3 rounded-lg transition">Simpan Guru</button></div></div>
            </div>
            <div id="area-input-manage" class="hidden bg-slate-900 p-6 rounded-2xl border border-slate-800"><h3 class="font-bold text-white text-lg mb-4 flex items-center gap-2"><i data-lucide="search" class="text-purple-500"></i> Cari & Edit User</h3><div class="mb-4"><input type="text" id="cariUserBox" onkeyup="cariUser()" class="w-full bg-slate-950 text-white p-3 rounded-lg border border-slate-700" placeholder="Ketik Nama Siswa atau Guru..."></div><div class="overflow-x-auto max-h-96 border border-slate-800 rounded-lg"><table class="w-full text-left text-slate-400"><thead class="bg-slate-950 text-xs uppercase font-bold text-slate-500 sticky top-0"><tr><th class="p-3">Nama</th><th class="p-3">Role</th><th class="p-3">ID/NISN</th><th class="p-3">Kelas</th><th class="p-3 text-right">Aksi</th></tr></thead><tbody id="tabel-hasil-cari" class="divide-y divide-slate-800"><tr><td colspan="5" class="p-4 text-center italic">Ketik nama di atas untuk mencari...</td></tr></tbody></table></div></div>
        </div>

        <div id="view-mapel" class="hidden space-y-6 fade-in">
            <h2 class="text-3xl font-bold text-white">Database Mata Pelajaran</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800"><h3 class="font-bold text-white mb-4">Tambah Mapel</h3><div class="space-y-4"><input type="text" id="inputNamaMapel" class="w-full bg-slate-800 text-white p-3 rounded-lg border border-slate-700" placeholder="Nama Mapel"><button onclick="tambahMapel()" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-lg">Simpan</button></div></div>
                <div class="md:col-span-2 bg-slate-900 p-6 rounded-2xl border border-slate-800"><h3 class="font-bold text-white mb-4">Daftar Mapel Terdaftar</h3><div class="max-h-96 overflow-y-auto"><ul id="list-mapel" class="space-y-2"><li class="text-center text-slate-500 italic">Memuat...</li></ul></div></div>
            </div>
        </div>

        <div id="view-menu-manager" class="hidden space-y-6 fade-in">
            <h2 class="text-3xl font-bold text-white">App Builder (Menu Manager)</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 h-fit">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-white" id="judulFormMenu">Tambah Tombol Baru</h3>
                        <button id="btnResetMenu" onclick="resetFormMenu()" class="text-xs text-red-400 hover:text-red-300 hidden font-bold">Batal Edit</button>
                    </div>
                    <div class="space-y-4">
                        <input type="hidden" id="menuId"> <div><label class="text-xs text-slate-500 uppercase font-bold">Target</label><select id="menuTarget" class="w-full bg-slate-800 text-white p-3 rounded-lg border border-slate-700 mt-1 outline-none"><option value="siswa">Siswa</option><option value="guru">Guru</option><option value="umum">Umum</option></select></div>
                        <div><label class="text-xs text-slate-500 uppercase font-bold">Judul Fitur</label><input type="text" id="menuJudul" class="w-full bg-slate-800 text-white p-3 rounded-lg border border-slate-700 mt-1" placeholder="Contoh: E-Raport"></div>
                        <div><label class="text-xs text-slate-500 uppercase font-bold">Ikon (Emoji)</label><input type="text" id="menuIcon" class="w-full bg-slate-800 text-white p-3 rounded-lg border border-slate-700 mt-1" placeholder="Contoh: 🎓"></div>
                        <div><label class="text-xs text-slate-500 uppercase font-bold">Link File</label><input type="text" id="menuLink" class="w-full bg-slate-800 text-white p-3 rounded-lg border border-slate-700 mt-1" placeholder="Contoh: http://... atau file.php"></div>
                        <div class="flex items-center gap-2 p-2 bg-slate-800/50 rounded-lg border border-slate-700"><input type="checkbox" id="menuWarna" class="w-4 h-4 cursor-pointer accent-blue-600"><label for="menuWarna" class="text-sm text-slate-300 cursor-pointer select-none">Highlight (Warna Warni)</label></div>
                        <button onclick="simpanMenuBaru()" id="btnSimpanMenu" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3 rounded-lg shadow-lg active:scale-95">Terbitkan Tombol</button>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-slate-900 p-6 rounded-2xl border border-slate-800">
                    <h3 class="font-bold text-white mb-4">Daftar Tombol Aktif</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-400">
                            <thead class="bg-slate-950 text-xs uppercase font-bold text-slate-500">
                                <tr>
                                    <th class="p-3">Target</th>
                                    <th class="p-3">Ikon</th>
                                    <th class="p-3">Judul</th>
                                    <th class="p-3 text-center">Status</th>
                                    <th class="p-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-menu-list" class="divide-y divide-slate-800">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="view-settings" class="hidden space-y-6 fade-in">
            <h2 class="text-3xl font-bold text-white flex items-center gap-3"><i data-lucide="settings" class="w-8 h-8 text-blue-500"></i> Pengaturan & Fitur</h2>
            <div class="bg-slate-900 rounded-[2rem] border border-slate-800 p-8 shadow-2xl mb-6 relative overflow-hidden"><h3 class="font-bold text-white text-lg mb-6 flex items-center gap-2"><i data-lucide="shield-check" class="text-blue-500"></i> Manajemen Admin & Pejabat</h3><div class="flex flex-col md:flex-row gap-4 items-end mb-6"><div class="flex-1 w-full"><label class="text-xs text-slate-500 uppercase font-bold">NIP / Username Guru</label><input type="text" id="inputCalonAdmin" class="w-full bg-slate-950 text-white p-3 rounded-lg border border-slate-700 mt-1" placeholder="Masukkan NIP Guru..."></div><div class="w-full md:w-1/3"><label class="text-xs text-slate-500 uppercase font-bold">Pilih Jabatan (Role)</label><select id="inputRoleAdmin" class="w-full bg-slate-950 text-white p-3 rounded-lg border border-slate-700 mt-1 outline-none"><option value="admin">Admin Umum / TU (IT)</option><option value="bk">Koordinator BK (Kesiswaan)</option><option value="kurikulum">Waka Kurikulum</option></select></div><button onclick="angkatAdmin()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg w-full md:w-auto"><i data-lucide="user-check" class="w-4 h-4 inline mr-1"></i> Lantik</button></div><h4 class="text-sm font-bold text-slate-500 mb-2">Daftar Pejabat & Admin Aktif:</h4><div id="list-admin-aktif" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3"></div></div>
            <div class="bg-slate-900 rounded-[2rem] border border-slate-800 p-8 shadow-2xl relative overflow-hidden"><div class="bg-slate-950/50 p-4 rounded-xl border border-slate-700 mb-6"><label class="text-xs text-slate-500 uppercase font-bold">Jenjang Sekolah (Mode Aplikasi)</label><select id="inputJenjang" class="w-full bg-slate-900 text-white p-3 rounded-lg border border-slate-600 mt-1 font-bold text-lg outline-none focus:border-blue-500"><option value="sd">SD (Mode Wali Murid/Pantau)</option><option value="smp">SMP (Mode Absen Mandiri)</option><option value="sma">SMA/SMK (Mode Absen Mandiri)</option></select><p class="text-[10px] text-slate-400 mt-2 italic">*Mode SD akan memprioritaskan fitur Monitoring Orang Tua.</p></div><div class="grid grid-cols-1 md:grid-cols-2 gap-10"><div><div class="flex items-center justify-between mb-6"><div><h3 class="font-bold text-white text-lg">Mode Disiplin GPS</h3><p class="text-xs text-slate-400 mt-1">Jika ON, siswa wajib dalam radius sekolah.</p></div><div class="relative inline-block w-14 h-8 align-middle select-none"><input type="checkbox" id="toggleGPS" class="toggle-checkbox absolute block w-8 h-8 rounded-full bg-white border-4 appearance-none cursor-pointer" onchange="updateLabelStatus()"/><label for="toggleGPS" class="toggle-label block overflow-hidden h-8 rounded-full bg-slate-700 cursor-pointer"></label></div></div><div id="statusGPSBadge" class="inline-block px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest bg-slate-800 text-slate-500">Menunggu Data...</div><div class="mt-6 space-y-4"><div class="flex items-center justify-between p-3 bg-slate-950/50 rounded-xl border border-slate-700"><div><h4 class="font-bold text-white text-sm">Refleksi KBM (Ortu)</h4></div><div class="relative inline-block w-14 h-8 align-middle select-none"><input type="checkbox" id="toggleKBM_Ortu" class="toggle-checkbox absolute block w-8 h-8 rounded-full bg-white border-4 appearance-none cursor-pointer"/><label for="toggleKBM_Ortu" class="toggle-label block overflow-hidden h-8 rounded-full bg-slate-700 cursor-pointer"></label></div></div><div class="flex items-center justify-between p-3 bg-slate-950/50 rounded-xl border border-slate-700"><div><h4 class="font-bold text-white text-sm">Evaluasi Siswa (Guru)</h4></div><div class="relative inline-block w-14 h-8 align-middle select-none"><input type="checkbox" id="toggleRefleksi_Guru" class="toggle-checkbox absolute block w-8 h-8 rounded-full bg-white border-4 appearance-none cursor-pointer"/><label for="toggleRefleksi_Guru" class="toggle-label block overflow-hidden h-8 rounded-full bg-slate-700 cursor-pointer"></label></div></div></div></div><div class="bg-slate-950/50 p-6 rounded-2xl border border-slate-800"><h4 class="font-bold text-white text-sm mb-4 flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-orange-500"></i> Titik Koordinat Sekolah</h4><div class="grid grid-cols-2 gap-4"><div><label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Latitude</label><input type="text" id="inputLat" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-white text-xs font-mono" placeholder="-7.xxxxx"></div><div><label class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Longitude</label><input type="text" id="inputLng" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-white text-xs font-mono" placeholder="110.xxxxx"></div></div><div class="mt-4"><div class="flex justify-between items-end mb-2"><label class="font-bold text-white text-xs">Jarak Toleransi</label><span id="labelRadius" class="text-xl font-black text-blue-500">50m</span></div><input type="range" id="inputRadius" min="10" max="1000" step="10" class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-blue-600" oninput="updateLabelRadius()"></div></div></div><button onclick="simpanConfig()" id="btnSaveConfig" class="mt-8 w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white py-4 rounded-xl font-bold shadow-lg shadow-blue-500/20 active:scale-95 transition flex items-center justify-center gap-2"><i data-lucide="save" class="w-5 h-5"></i> SIMPAN SEMUA PENGATURAN</button></div>
        </div>
    </main>

    <div id="loading" class="fixed inset-0 bg-slate-900 flex flex-col items-center justify-center z-50 text-white hidden"><div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div><p class="font-bold animate-pulse">Memproses...</p></div>

    <script>
        let listUsers = [];

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            loadDashboardStats();
            loadAllUsers();
            loadMapel();
            loadMenuManager();
            loadSettings();
            loadAdmins();
        });

        // 1. NAVIGASI
        window.nav = (v) => { 
            document.querySelectorAll('[id^="view-"]').forEach(e => e.classList.add('hidden')); 
            document.querySelectorAll('.nav-item').forEach(e => e.classList.remove('active')); 
            document.getElementById('view-'+v).classList.remove('hidden'); 
            document.getElementById('nav-'+v).classList.add('active'); 
        }
        
        window.gantiTabInput = (mode) => {
            document.getElementById('area-input-excel').classList.add('hidden'); document.getElementById('area-input-manual').classList.add('hidden'); document.getElementById('area-input-manage').classList.add('hidden');
            document.getElementById('btn-tab-excel').classList.replace('bg-blue-600', 'bg-slate-800'); document.getElementById('btn-tab-excel').classList.replace('text-white', 'text-slate-400');
            document.getElementById('btn-tab-manual').classList.replace('bg-blue-600', 'bg-slate-800'); document.getElementById('btn-tab-manual').classList.replace('text-white', 'text-slate-400');
            document.getElementById('btn-tab-manage').classList.replace('bg-blue-600', 'bg-slate-800'); document.getElementById('btn-tab-manage').classList.replace('text-white', 'text-slate-400');
            document.getElementById(`area-input-${mode}`).classList.remove('hidden'); document.getElementById(`btn-tab-${mode}`).classList.replace('bg-slate-800', 'bg-blue-600'); document.getElementById(`btn-tab-${mode}`).classList.replace('text-slate-400', 'text-white');
        }

        // 2. DASHBOARD & DATA USER
        function loadDashboardStats() { fetch('api_admin.php?action=get_dashboard_stats').then(r=>r.json()).then(d => { document.getElementById('stat-total').innerText = d.total_siswa; document.getElementById('stat-guru').innerText = d.total_guru; document.getElementById('stat-hadir').innerText = d.hadir_realtime; document.getElementById('stat-mode').innerText = d.mode_gps == 'strict' ? 'GPS DISIPLIN' : 'GPS BEBAS'; document.getElementById('stat-mode').className = d.mode_gps == 'strict' ? "text-3xl font-black text-blue-400 mt-1" : "text-3xl font-black text-yellow-400 mt-1"; const tbl = document.getElementById('tabel-live'); tbl.innerHTML = ''; d.logs.forEach(l => { tbl.innerHTML += `<tr class="border-b border-slate-800 hover:bg-slate-800/50"><td class="p-4 text-emerald-400 font-mono text-xs">${l.jam}</td><td class="p-4 text-white font-bold">${l.nama}</td><td class="p-4 text-xs text-slate-400">${l.kelas}</td><td class="p-4 text-xs font-bold text-slate-500 bg-slate-900 rounded">${l.status}</td></tr>`; }); }); }
        function loadAllUsers() { fetch('api_admin.php?action=get_all_users').then(r=>r.json()).then(data => { listUsers = data; }); }
        window.cariUser = () => { const key = document.getElementById('cariUserBox').value.toLowerCase(); const tbody = document.getElementById('tabel-hasil-cari'); tbody.innerHTML = ''; if(key.length < 3) { tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center italic">Ketik minimal 3 huruf...</td></tr>'; return; } const hasil = listUsers.filter(u => u.nama.toLowerCase().includes(key) || u.username.includes(key)); if(hasil.length === 0) { tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-red-400">Tidak ditemukan.</td></tr>'; return; } hasil.slice(0,10).forEach(s => { let badge = s.role == 'siswa' ? 'bg-slate-700' : 'bg-orange-600'; tbody.innerHTML += `<tr class="border-b border-slate-800 hover:bg-slate-800"><td class="p-3 text-white font-bold">${s.nama}</td><td class="p-3"><span class="px-2 py-1 rounded text-[10px] font-bold uppercase ${badge} text-white">${s.role}</span></td><td class="p-3 font-mono text-xs text-blue-400">${s.username}</td><td class="p-3 text-xs text-slate-400">${s.kelas}</td><td class="p-3 text-right flex justify-end gap-2"><button onclick="hapusUser('${s.username}','${s.role}')" class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded text-xs font-bold transition">Hapus</button></td></tr>`; }); }
        window.simpanManual = (role) => { const fd = new FormData(); fd.append('action', 'simpan_manual'); fd.append('role', role); if(role == 'siswa') { fd.append('nama', document.getElementById('manualNamaSiswa').value); fd.append('username', document.getElementById('manualNISN').value); fd.append('password', document.getElementById('manualNISN').value); fd.append('kelas', document.getElementById('manualKelas').value); } else { fd.append('nama', document.getElementById('manualNamaGuru').value); fd.append('username', document.getElementById('manualNIP').value); fd.append('password', document.getElementById('manualNIP').value); } fetch('api_admin.php', {method:'POST', body:fd}).then(r=>r.json()).then(res => { if(res.status == 'success') { Swal.fire('Sukses','','success'); loadAllUsers(); } else Swal.fire('Gagal', res.pesan, 'error'); }); }
        window.hapusUser = (u, r) => { if(confirm('Hapus user ini selamanya?')) { const fd = new FormData(); fd.append('action','hapus_user'); fd.append('username',u); fd.append('role',r); fetch('api_admin.php', {method:'POST', body:fd}).then(r=>r.json()).then(res => { Swal.fire('Terhapus','','success'); loadAllUsers(); }); } }
        window.prosesImport = (tipe) => { const file = document.getElementById(tipe=='siswa'?'fileSiswa':'fileGuru').files[0]; if(!file) return; const reader = new FileReader(); reader.onload = (e) => { const wb = XLSX.read(e.target.result, {type:'array'}); const json = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], {header:1}); json.shift(); const fd = new FormData(); fd.append('action','import_data'); fd.append('tipe',tipe); fd.append('data', JSON.stringify(json)); document.getElementById('loading').classList.remove('hidden'); fetch('api_admin.php', {method:'POST', body:fd}).then(r=>r.json()).then(res => { document.getElementById('loading').classList.add('hidden'); Swal.fire('Selesai', res.pesan, 'success'); loadAllUsers(); }); }; reader.readAsArrayBuffer(file); }
        
        // 3. MAPEL & SETTINGS
        function loadMapel() { fetch('api_admin.php?action=get_mapel').then(r=>r.json()).then(d => { const l = document.getElementById('list-mapel'); l.innerHTML=''; d.forEach(m => l.innerHTML += `<li class="flex justify-between items-center bg-slate-800 p-3 rounded-lg border border-slate-700"><span class="text-white font-bold">${m.nama_mapel}</span><button onclick="hapusMapel('${m.id_mapel}')" class="text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button></li>`); lucide.createIcons(); }); }
        window.tambahMapel = () => { const n = document.getElementById('inputNamaMapel').value; if(n){ const fd=new FormData(); fd.append('action','tambah_mapel'); fd.append('nama',n); fetch('api_admin.php',{method:'POST',body:fd}).then(()=>{loadMapel(); document.getElementById('inputNamaMapel').value='';}); } }
        window.hapusMapel = (id) => { if(confirm('Hapus?')){ const fd=new FormData(); fd.append('action','hapus_mapel'); fd.append('id',id); fetch('api_admin.php',{method:'POST',body:fd}).then(loadMapel); } }
        function loadSettings() { fetch('api_admin.php?action=get_settings').then(r=>r.json()).then(d => { document.getElementById('inputLat').value = d.pusat_lat; document.getElementById('inputLng').value = d.pusat_lng; document.getElementById('inputRadius').value = d.radius_meter; document.getElementById('toggleGPS').checked = d.mode_gps == 'strict'; document.getElementById('toggleKBM_Ortu').checked = d.kbm_ortu_aktif == 1; document.getElementById('toggleRefleksi_Guru').checked = d.refleksi_guru_aktif == 1; if(d.jenjang_sekolah) document.getElementById('inputJenjang').value = d.jenjang_sekolah; updateLabelRadius(); updateLabelStatus(); }); }
        function loadAdmins() { fetch('api_admin.php?action=get_admins').then(r=>r.json()).then(d => { const c = document.getElementById('list-admin-aktif'); c.innerHTML=''; d.forEach(u => { let badge = u.level == 'admin' || u.level == 'super' ? 'bg-blue-600' : (u.level == 'bk' ? 'bg-emerald-600' : 'bg-yellow-600'); c.innerHTML += `<div class="flex justify-between items-center bg-slate-950 p-3 rounded-lg border border-slate-800"><div><p class="text-white font-bold text-sm">${u.nama_lengkap}</p><div class="flex items-center gap-2 mt-1"><p class="text-xs text-slate-500 font-mono">${u.username}</p><span class="text-[9px] ${badge} text-white px-2 py-0.5 rounded font-bold uppercase">${u.level}</span></div></div><button onclick="copotAdmin('${u.username}')" class="text-red-500 hover:bg-red-500/10 p-2 rounded" title="Turunkan Jabatan"><i data-lucide="shield-off" class="w-4 h-4"></i></button></div>`; }); lucide.createIcons(); }); }
        window.simpanConfig = () => { const fd = new FormData(); fd.append('action','simpan_settings'); fd.append('lat', document.getElementById('inputLat').value); fd.append('lng', document.getElementById('inputLng').value); fd.append('rad', document.getElementById('inputRadius').value); fd.append('gps', document.getElementById('toggleGPS').checked); fd.append('kbm', document.getElementById('toggleKBM_Ortu').checked); fd.append('ref', document.getElementById('toggleRefleksi_Guru').checked); fd.append('jenjang', document.getElementById('inputJenjang').value); fetch('api_admin.php', {method:'POST', body:fd}).then(r=>r.json()).then(() => Swal.fire('Tersimpan!','Pengaturan diperbarui','success')); }
        window.angkatAdmin = () => { const u = document.getElementById('inputCalonAdmin').value; const r = document.getElementById('inputRoleAdmin').value; if(!u) return; const fd = new FormData(); fd.append('action','angkat_admin'); fd.append('username',u); fd.append('role',r); fetch('api_admin.php', {method:'POST', body:fd}).then(r=>r.json()).then(res=>{ if(res.status=='success'){ Swal.fire('Dilantik','','success'); loadAdmins(); } else Swal.fire('Gagal',res.pesan,'error'); }); }
        window.copotAdmin = (u) => { if(confirm('Turunkan jabatan jadi Guru Biasa?')) { const fd = new FormData(); fd.append('action','angkat_admin'); fd.append('username',u); fd.append('role','guru'); fetch('api_admin.php', {method:'POST', body:fd}).then(r=>r.json()).then(res=>{ Swal.fire('Dicopot','','success'); loadAdmins(); }); } }
        window.updateLabelRadius = () => document.getElementById('labelRadius').innerText = document.getElementById('inputRadius').value + "m";
        window.updateLabelStatus = () => { const o = document.getElementById('toggleGPS').checked; const b = document.getElementById('statusGPSBadge'); b.innerText = o ? "MODE DISIPLIN: AKTIF" : "MODE DARURAT (BEBAS)"; b.className = o ? "inline-block px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest bg-emerald-900 text-emerald-400" : "inline-block px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest bg-red-900 text-red-400"; }
        window.logout = () => window.location.href='logout.php';

        // 4. MENU MANAGER (LOGIKA EDIT & ON/OFF) - VERSI RAPI
        function loadMenuManager() {
            fetch('api_admin.php?action=get_menu').then(r=>r.json()).then(d => {
                const tb = document.getElementById('tbody-menu-list'); tb.innerHTML = '';
                d.forEach(m => {
                    const statusCheck = m.is_active == 1 ? 'checked' : '';
                    
                    tb.innerHTML += `
                    <tr class="hover:bg-slate-800 border-b border-slate-800">
                        <td class="p-3 uppercase text-blue-400 font-bold text-xs">${m.target_user}</td>
                        <td class="p-3 text-xl">${m.icon}</td>
                        <td class="p-3">
                            <p class="text-white font-bold text-sm">${m.judul}</p>
                            <p class="text-xs text-slate-500 font-mono truncate max-w-[150px]">${m.link_url}</p>
                        </td>
                        <td class="p-3 text-center">
                            <div class="relative inline-block w-10 h-5 align-middle select-none">
                                <input type="checkbox" ${statusCheck} onchange="toggleMenu('${m.id_menu}', this.checked)" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-2 appearance-none cursor-pointer"/>
                                <label class="toggle-label block overflow-hidden h-5 rounded-full bg-slate-700 cursor-pointer"></label>
                            </div>
                        </td>
                        <td class="p-3 text-right">
                            <button onclick="editMenu('${m.id_menu}')" class="text-blue-500 hover:bg-blue-500/10 p-2 rounded mr-1" title="Edit"><i data-lucide="edit" class="w-4 h-4"></i></button>
                            <button onclick="hapusMenu('${m.id_menu}')" class="text-red-500 hover:bg-red-500/10 p-2 rounded" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </td>
                    </tr>`;
                });
                lucide.createIcons();
            });
        }

        window.simpanMenuBaru = () => {
            const fd = new FormData(); fd.append('action','simpan_menu');
            fd.append('id_menu', document.getElementById('menuId').value); // Kirim ID (kosong = baru, ada = edit)
            fd.append('target', document.getElementById('menuTarget').value);
            fd.append('judul', document.getElementById('menuJudul').value);
            fd.append('icon', document.getElementById('menuIcon').value);
            fd.append('link', document.getElementById('menuLink').value);
            fd.append('highlight', document.getElementById('menuWarna').checked);
            
            fetch('api_admin.php',{method:'POST',body:fd}).then(r=>r.json()).then(res => {
                if(res.status == 'success') {
                    Swal.fire('Berhasil','Data tersimpan','success');
                    resetFormMenu();
                    loadMenuManager();
                } else {
                    Swal.fire('Gagal', res.pesan, 'error');
                }
            });
        }

        window.editMenu = (id) => {
            fetch(`api_admin.php?action=get_menu_detail&id=${id}`).then(r=>r.json()).then(d => {
                // Isi Form dengan data lama
                document.getElementById('menuId').value = d.id_menu;
                document.getElementById('menuTarget').value = d.target_user;
                document.getElementById('menuJudul').value = d.judul;
                document.getElementById('menuIcon').value = d.icon;
                document.getElementById('menuLink').value = d.link_url;
                document.getElementById('menuWarna').checked = d.highlight == 1;
                
                // Ubah Tampilan Tombol jadi Mode Edit
                document.getElementById('judulFormMenu').innerText = "Edit Tombol";
                document.getElementById('btnSimpanMenu').innerText = "Simpan Perubahan";
                document.getElementById('btnResetMenu').classList.remove('hidden');
                document.getElementById('btnSimpanMenu').classList.replace('bg-gradient-to-r', 'bg-emerald-600'); // Ganti warna jadi Hijau
            });
        }

        window.resetFormMenu = () => {
            // Kosongkan Form
            document.getElementById('menuId').value = '';
            document.getElementById('menuTarget').value = 'siswa';
            document.getElementById('menuJudul').value = '';
            document.getElementById('menuIcon').value = '';
            document.getElementById('menuLink').value = '';
            document.getElementById('menuWarna').checked = false;
            
            // Kembalikan Tampilan Tombol
            document.getElementById('judulFormMenu').innerText = "Tambah Tombol Baru";
            document.getElementById('btnSimpanMenu').innerText = "Terbitkan Tombol";
            document.getElementById('btnResetMenu').classList.add('hidden');
            document.getElementById('btnSimpanMenu').classList.replace('bg-emerald-600', 'bg-gradient-to-r'); // Balik warna biru
        }

        window.toggleMenu = (id, status) => {
            // Update On/Off tanpa refresh
            const fd = new FormData(); fd.append('action','toggle_menu'); fd.append('id',id); fd.append('status',status);
            fetch('api_admin.php',{method:'POST',body:fd}); 
        }

        window.hapusMenu = (id) => { 
            if(confirm('Hapus menu ini?')){ 
                const fd=new FormData(); fd.append('action','hapus_menu'); fd.append('id',id); 
                fetch('api_admin.php',{method:'POST',body:fd}).then(loadMenuManager); 
            } 
        }
    </script>
</body>
</html>