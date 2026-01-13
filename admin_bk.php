<?php
session_start();
// Cek Login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || !in_array($_SESSION['role'], ['admin','super','bk','kurikulum'])) {
    header("location:login.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Kesiswaan (BK) - SiGanteng</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: #e2e8f0; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .nav-item.active { background: rgba(16, 185, 129, 0.1); border-right: 3px solid #10b981; color: #34d399; }
        .table-sticky th { position: sticky; top: 0; z-index: 30; background: #1e293b; }
        .table-sticky td:first-child, .table-sticky th:first-child { position: sticky; left: 0; z-index: 40; background: #1e293b; border-right: 1px solid #334155; }
        .table-sticky td:nth-child(2), .table-sticky th:nth-child(2) { position: sticky; left: 40px; z-index: 40; background: #1e293b; border-right: 1px solid #334155; }
        
        .bg-weekend { background-color: #334155; color: #475569; }
        .bg-hadir { background-color: #064e3b; color: #a7f3d0; font-size: 0.65rem; vertical-align: middle; line-height: 1.1; }
        .bg-dispen { background-color: #581c87; color: #e9d5ff; font-weight: bold; vertical-align: middle; }
        .bg-sakit { background-color: #1e3a8a; color: #bfdbfe; font-weight: bold; vertical-align: middle; }
        .bg-izin { background-color: #713f12; color: #fef08a; font-weight: bold; vertical-align: middle; }
        .bg-alpha { background-color: #7f1d1d; color: #fca5a5; font-weight: bold; vertical-align: middle; }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-sm">

    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col hidden md:flex">
        <div class="p-6">
            <h1 class="font-black text-2xl tracking-tighter text-emerald-500">SiGanteng</h1>
            <p class="text-xs text-slate-500 tracking-widest uppercase mt-1">Panel Kesiswaan (BK)</p>
        </div>
        <nav class="flex-1 space-y-1 px-3 mt-4 overflow-y-auto hide-scroll">
            <button onclick="nav('dashboard')" id="nav-dashboard" class="nav-item active w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard Harian</button>
            <button onclick="nav('rekap')" id="nav-rekap" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i data-lucide="calendar-days" class="w-5 h-5"></i> Rekap Absensi Detail</button>
            <button onclick="nav('dispen')" id="nav-dispen" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i data-lucide="file-check-2" class="w-5 h-5"></i> Kelola Dispensasi (D)</button>
            <button onclick="nav('mbg')" id="nav-mbg" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i data-lucide="utensils" class="w-5 h-5"></i> Laporan MBG</button>
            <button onclick="nav('libur')" id="nav-libur" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"><i data-lucide="calendar-off" class="w-5 h-5"></i> Kelola Hari Libur</button>
            <div class="my-4 border-t border-slate-800"></div>
            <button onclick="window.location.href='guru.php'" class="nav-item w-full flex items-center gap-3 px-4 py-3 text-emerald-400 hover:text-white hover:bg-emerald-900/30 rounded-lg transition font-bold"><i data-lucide="pen-tool" class="w-5 h-5"></i> Input Jurnal Saya</button>
        </nav>
        <div class="p-4 border-t border-slate-800"><button onclick="logout()" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-500/10 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition font-bold"><i data-lucide="log-out" class="w-4 h-4"></i> Keluar</button></div>
    </aside>

    <main class="flex-1 overflow-y-auto bg-slate-950 relative p-4 md:p-8">
        <div class="md:hidden flex justify-between items-center mb-6"><h1 class="font-bold text-lg text-emerald-500">Kesiswaan BK</h1><button onclick="logout()" class="text-red-500"><i data-lucide="log-out"></i></button></div>

        <div id="view-dashboard" class="space-y-6 fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                <div><h2 class="text-3xl font-bold text-white">Monitoring Siswa</h2><p class="text-slate-400 text-sm">Realtime Update: <span id="jam-real" class="font-mono text-emerald-400">...</span></p></div>
                <div class="flex gap-2"><button onclick="tarikDataSiswa(true)" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2 text-xs transition border border-slate-700"><i data-lucide="refresh-cw" class="w-4 h-4"></i> Refresh Data</button></div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800"><p class="text-slate-500 text-xs font-bold uppercase">Hadir</p><h3 class="text-3xl font-black text-emerald-400 mt-1" id="stat-hadir">0</h3></div>
                <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800"><p class="text-slate-500 text-xs font-bold uppercase">Izin / Sakit</p><h3 class="text-3xl font-black text-blue-400 mt-1" id="stat-izin">0</h3></div>
                <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800"><p class="text-slate-500 text-xs font-bold uppercase">Alpha / Belum</p><h3 class="text-3xl font-black text-red-400 mt-1" id="stat-alpha">0</h3></div>
                <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800"><p class="text-slate-500 text-xs font-bold uppercase">Total Siswa</p><h3 class="text-3xl font-black text-white mt-1" id="stat-total">0</h3></div>
            </div>
            <div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden mt-6">
                <div class="p-4 border-b border-slate-800 flex justify-between items-center"><h3 class="font-bold text-white">Log Aktivitas Terbaru</h3></div>
                <div class="overflow-x-auto max-h-96"><table class="w-full text-left text-slate-400"><thead class="bg-slate-950 text-xs uppercase font-bold text-slate-500 sticky top-0"><tr><th class="p-4">Jam</th><th class="p-4">Nama Siswa</th><th class="p-4">Kelas</th><th class="p-4">Status</th></tr></thead><tbody id="tabel-live" class="divide-y divide-slate-800"></tbody></table></div>
            </div>
        </div>

        <div id="view-rekap" class="hidden space-y-6 fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-4">
                <div><h2 class="text-3xl font-bold text-white">Rekapitulasi Detail</h2><p class="text-xs text-slate-400 mt-1">Laporan Jam Masuk & Pulang Lengkap</p></div>
                <div class="flex flex-wrap gap-2">
                    <select id="pilih-bulan" class="bg-slate-800 text-white text-xs rounded-lg px-3 py-2 outline-none border border-slate-700"></select>
                    <select id="pilih-tahun" class="bg-slate-800 text-white text-xs rounded-lg px-3 py-2 outline-none border border-slate-700"></select>
                    <select id="pilih-kelas" class="bg-slate-800 text-white text-xs rounded-lg px-3 py-2 outline-none border border-slate-700"><option value="">- Pilih Kelas -</option></select>
                    <button onclick="loadRekap()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-xs font-bold">Tampilkan</button>
                    <button onclick="downloadExcel('tabel-rekap')" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg text-xs font-bold"><i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Excel Lengkap</button>
                </div>
            </div>
            <div class="flex-1 bg-slate-900 rounded-2xl border border-slate-800 overflow-auto relative h-[70vh]">
                <table id="tabel-rekap" class="w-full text-center text-[10px] border-collapse table-sticky whitespace-nowrap"><thead class="text-slate-300 font-bold" id="thead-rekap"></thead><tbody id="tbody-rekap" class="text-slate-400 divide-y divide-slate-800"></tbody></table>
            </div>
        </div>

        <div id="view-dispen" class="hidden space-y-6 fade-in">
            <h2 class="text-3xl font-bold text-white">Kelola Dispensasi (D)</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800">
                    <h3 class="font-bold text-white mb-4">Input Dispen Baru</h3>
                    <div class="space-y-4">
                        <div><label class="text-xs text-slate-500 uppercase font-bold">Cari Siswa</label><input type="text" id="inputCariSiswa" onkeyup="cariSiswaLokal()" class="w-full bg-slate-800 text-white p-3 rounded-lg border border-slate-700 mt-1" placeholder="Ketik nama siswa..."><div id="hasilCariSiswa" class="hidden bg-slate-800 border border-slate-700 mt-1 rounded-lg max-h-40 overflow-y-auto absolute w-60 z-50 shadow-xl"></div></div>
                        <input type="hidden" id="inputNisnSiswa"><input type="hidden" id="inputKelasSiswa">
                        <div><label class="text-xs text-slate-500 uppercase font-bold">Siswa Terpilih</label><input type="text" id="displayNamaSiswa" class="w-full bg-slate-950 text-emerald-400 p-3 rounded-lg border border-slate-700 mt-1 font-bold" readonly placeholder="-"></div>
                        <div class="grid grid-cols-2 gap-2"><div><label class="text-xs text-slate-500 uppercase font-bold">Tgl Mulai</label><input type="date" id="inputStart" class="w-full bg-slate-800 text-white p-2 rounded-lg border border-slate-700 mt-1"></div><div><label class="text-xs text-slate-500 uppercase font-bold">Tgl Selesai</label><input type="date" id="inputEnd" class="w-full bg-slate-800 text-white p-2 rounded-lg border border-slate-700 mt-1"></div></div>
                        <div><label class="text-xs text-slate-500 uppercase font-bold">Keterangan</label><select id="inputAlasan" class="w-full bg-slate-800 text-white p-3 rounded-lg border border-slate-700 mt-1"><option value="Lomba Akademik">Lomba Akademik</option><option value="Lomba Non-Akademik">Lomba Non-Akademik</option><option value="Tugas Sekolah">Tugas Sekolah (OSIS)</option><option value="Lainnya">Lainnya</option></select></div>
                        <div><label class="text-xs text-slate-500 uppercase font-bold">Upload Surat (Foto)</label><input type="file" id="inputBuktiSurat" class="w-full bg-slate-800 text-slate-400 text-xs p-2 rounded-lg border border-slate-700 mt-1"></div>
                        <button onclick="simpanDispen()" class="w-full bg-purple-600 hover:bg-purple-500 text-white font-bold py-3 rounded-lg transition">Simpan Dispensasi</button>
                    </div>
                </div>
                <div class="md:col-span-2 bg-slate-900 p-6 rounded-2xl border border-slate-800">
                    <div class="flex justify-between items-center mb-4"><h3 class="font-bold text-white">Daftar Dispensasi Aktif</h3><button onclick="loadDispen()" class="text-xs bg-slate-800 px-3 py-1 rounded text-white"><i data-lucide="refresh-cw" class="w-3 h-3"></i></button></div>
                    <div class="overflow-y-auto max-h-[500px]"><table class="w-full text-left text-sm text-slate-400"><thead class="bg-slate-950 text-xs uppercase font-bold text-slate-500 sticky top-0"><tr><th class="p-3">Siswa</th><th class="p-3">Tanggal</th><th class="p-3">Keterangan</th><th class="p-3 text-right">Aksi</th></tr></thead><tbody id="tbody-dispen" class="divide-y divide-slate-800"></tbody></table></div>
                </div>
            </div>
        </div>

        <div id="view-mbg" class="hidden space-y-6 fade-in">
             <div class="flex items-center justify-between"><h2 class="text-3xl font-bold text-white">Laporan MBG</h2><div class="text-xs text-slate-500 bg-slate-900 border border-slate-800 px-3 py-1 rounded-lg">Data Hari Ini</div></div>
             
             <div id="grid-mbg-buttons" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4"></div>

             <div id="container-tabel-mbg" class="hidden bg-white text-slate-900 rounded-2xl p-6 shadow-xl mt-6 border border-slate-700">
                <div class="flex justify-between items-center mb-4"><h3 class="font-bold text-lg text-slate-800" id="judul-tabel-mbg">Detail Logistik</h3><button onclick="downloadExcel('tabel-mbg-detail')" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2"><i data-lucide="printer" class="w-4 h-4"></i> Download Cetak</button></div>
                <div class="overflow-x-auto"><table id="tabel-mbg-detail" class="w-full text-sm border border-slate-300"><thead class="bg-slate-100"><tr><th class="border p-3 text-left w-32">KELAS</th><th class="border p-3 text-center w-32">TOTAL SISWA</th><th class="border p-3 bg-green-100 text-green-800 text-center font-black text-lg">JATAH MAKAN</th><th class="border p-3 bg-red-100 text-red-800 text-center">SISA</th></tr></thead><tbody id="tbody-mbg" class="text-slate-700 text-center"></tbody><tfoot class="bg-slate-800 text-white font-bold"><tr><td class="border border-slate-600 p-3 text-right">TOTAL</td><td class="border border-slate-600 p-3 text-center" id="mbg-grand-total">0</td><td class="border border-slate-600 p-3 text-center bg-green-900 text-green-300 text-xl" id="mbg-grand-hadir">0</td><td class="border border-slate-600 p-3 text-center bg-red-900 text-red-300" id="mbg-grand-sisa">0</td></tr></tfoot></table></div>
             </div>
        </div>

        <div id="view-libur" class="hidden space-y-6 fade-in">
             <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800"><h3 class="font-bold text-white mb-4 flex items-center gap-2"><i data-lucide="plus-circle" class="w-5 h-5 text-red-500"></i> Tambah Libur</h3><div class="space-y-4"><div><label class="text-xs text-slate-500 uppercase font-bold">Tanggal</label><input type="date" id="inputTglLibur" class="w-full bg-slate-800 text-white p-3 rounded-lg border border-slate-700 mt-1 cursor-pointer"></div><div><label class="text-xs text-slate-500 uppercase font-bold">Keterangan</label><input type="text" id="inputKetLibur" class="w-full bg-slate-800 text-white p-3 rounded-lg border border-slate-700 mt-1" placeholder="Contoh: HUT RI"></div><button onclick="simpanLibur()" id="btnSimpanLibur" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold py-3 rounded-lg transition">Simpan Tanggal</button></div></div>
                <div class="md:col-span-2 bg-slate-900 p-6 rounded-2xl border border-slate-800"><h3 class="font-bold text-white mb-4">Daftar Libur Mendatang</h3><div class="overflow-y-auto max-h-96"><table class="w-full text-left text-sm text-slate-400"><thead class="bg-slate-950 text-xs uppercase font-bold text-slate-500 sticky top-0"><tr><th class="p-3">Tanggal</th><th class="p-3">Keterangan</th><th class="p-3 text-right">Aksi</th></tr></thead><tbody id="tbody-libur" class="divide-y divide-slate-800"></tbody></table></div></div>
             </div>
        </div>
    </main>

    <script>
        let listSiswa=[], mapKelas={}, cacheHariLibur={}, cacheMBG={};
        let jenjangSekolah = 'sma'; // Default

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons(); setupDropdown(); tarikDataSiswa(); pantauRealtime(); loadHariLibur(); loadDispen();
            cekJenjang(); // Cek SD/SMP/SMA
        });

        window.nav = (v) => { document.querySelectorAll('[id^="view-"]').forEach(e => e.classList.add('hidden')); document.querySelectorAll('.nav-item').forEach(e => e.classList.remove('active')); document.getElementById('view-'+v).classList.remove('hidden'); document.getElementById('nav-'+v).classList.add('active'); }
        window.logout = () => window.location.href='logout.php';

        // 0. CEK JENJANG (PENTING BUAT MBG)
        function cekJenjang() {
            fetch('api_admin.php?action=get_settings').then(r=>r.json()).then(d => {
                jenjangSekolah = d.jenjang_sekolah || 'sma';
                setupMBGButtons(jenjangSekolah);
            });
        }

        function setupMBGButtons(jenjang) {
            const container = document.getElementById('grid-mbg-buttons');
            container.innerHTML = '';
            
            let buttons = [];
            if(jenjang === 'sd') {
                buttons = ['1', '2', '3', '4', '5', '6'];
            } else if(jenjang === 'smp') {
                buttons = ['7', '8', '9']; // Bisa juga VII, VIII, IX
            } else {
                buttons = ['X', 'XI', 'XII']; // SMA/SMK
            }

            buttons.forEach(kls => {
                // Warna acak biar keren
                const colors = ['blue', 'green', 'orange', 'purple', 'pink', 'indigo'];
                const color = colors[Math.floor(Math.random() * colors.length)];
                
                container.innerHTML += `
                <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 relative overflow-hidden group hover:border-${color}-500 transition cursor-pointer" onclick="previewMBG('${kls}')">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20"><i data-lucide="utensils" class="w-16 h-16 text-${color}-500"></i></div>
                    <h3 class="text-xl font-black text-white">KELAS ${kls}</h3>
                    <p class="text-xs text-slate-400 mt-2">Lihat Rincian</p>
                </div>`;
            });
            lucide.createIcons();
        }

        // 1. DATA SISWA
        function tarikDataSiswa(force=false) {
            fetch('api_bk.php?action=get_siswa_bk').then(r=>r.json()).then(data => {
                listSiswa = data; mapKelas = {};
                data.forEach(s => {
                    if(s.kelas && s.kelas != '-') {
                        const k = s.kelas.toUpperCase().trim();
                        if(!mapKelas[k]) mapKelas[k] = [];
                        mapKelas[k].push({nama: s.nama_siswa, nisn: s.nisn});
                    }
                });
                updateUIKelas();
                if(force) Swal.fire('Sip','Data siswa direfresh','success');
            });
        }
        function updateUIKelas() { const sel = document.getElementById('pilih-kelas'); sel.innerHTML = '<option value="">- Pilih Kelas -</option>'; Object.keys(mapKelas).sort().forEach(k => sel.add(new Option(k, k))); }

        // 2. DASHBOARD
        function pantauRealtime() {
            fetch('api_bk.php?action=get_dashboard').then(r=>r.json()).then(d => {
                document.getElementById('stat-total').innerText = d.total;
                document.getElementById('stat-hadir').innerText = d.hadir;
                document.getElementById('stat-izin').innerText = d.izin;
                document.getElementById('stat-alpha').innerText = d.alpha;
                document.getElementById('jam-real').innerText = new Date().toLocaleTimeString();
                
                const tb = document.getElementById('tabel-live'); tb.innerHTML = '';
                d.logs.forEach(l => tb.innerHTML += `<tr class="border-b border-slate-800"><td class="p-4 text-emerald-400 font-mono">${l.jam}</td><td class="p-4 text-white">${l.nama}</td><td class="p-4">${l.kelas}</td><td class="p-4">${l.status}</td></tr>`);
            });
        }

        // 3. REKAP
        async function loadRekap() {
            const kls = document.getElementById('pilih-kelas').value; const bln = parseInt(document.getElementById('pilih-bulan').value)+1; const thn = document.getElementById('pilih-tahun').value;
            if(!kls) return Swal.fire('Pilih Kelas','','warning');
            const tbody = document.getElementById('tbody-rekap'); tbody.innerHTML = '<tr><td colspan="40" class="p-4 text-center">Loading...</td></tr>';
            const blnStr = bln<10?'0'+bln:bln; const days = new Date(thn, bln, 0).getDate();
            let hh = '<tr><th class="p-2 w-10 border border-slate-700">No</th><th class="p-2 border border-slate-700 min-w-[200px] text-left">Nama</th><th class="p-2 border border-slate-700">NISN</th>';
            for(let d=1; d<=days; d++) {
                const dateStr = `${thn}-${blnStr}-${d<10?'0'+d:d}`;
                const isMerah = cacheHariLibur[dateStr];
                const day = new Date(thn, bln-1, d).getDay();
                let bg = (day==0||day==6||isMerah) ? 'bg-red-900/50 text-red-200' : 'bg-slate-900';
                hh += `<th class="p-2 border border-slate-700 ${bg} w-10 text-center">${d}</th>`;
            }
            hh += '<th class="p-2 border border-slate-700 bg-emerald-900">H</th><th class="p-2 border border-slate-700 bg-purple-900">D</th><th class="p-2 border border-slate-700 bg-blue-900">S</th><th class="p-2 border border-slate-700 bg-yellow-900">I</th><th class="p-2 border border-slate-700 bg-red-900">A</th></tr>';
            document.getElementById('thead-rekap').innerHTML = hh;

            const res = await fetch(`api_bk.php?action=get_rekap_data&kelas=${kls}&bulan=${blnStr}&tahun=${thn}`).then(r=>r.json());
            const abs = res.absensi; const dis = res.dispen;
            let dataMap = {};
            mapKelas[kls].forEach(u => dataMap[u.nisn] = {nama: u.nama, att: {}, counts:{h:0,d:0,s:0,i:0,a:0}});
            abs.forEach(a => { if(dataMap[a.nisn]) { const t = parseInt(a.tanggal.split('-')[2]); dataMap[a.nisn].att[t] = a; } });
            
            tbody.innerHTML = '';
            mapKelas[kls].sort((a,b)=>a.nama.localeCompare(b.nama)).forEach((u,i) => {
                let r = dataMap[u.nisn];
                let row = `<tr class="hover:bg-slate-800 border-b border-slate-800"><td class="p-2 border border-slate-700">${i+1}</td><td class="p-2 border border-slate-700 text-left sticky left-0 bg-slate-900 font-bold text-slate-300 border-r">${r.nama}</td><td class="p-2 border border-slate-700 font-mono text-xs sticky left-[150px] bg-slate-900">${u.nisn}</td>`;
                let totA = 0;
                for(let d=1; d<=days; d++) {
                    const dateStr = `${thn}-${blnStr}-${d<10?'0'+d:d}`;
                    const isMerah = cacheHariLibur[dateStr];
                    const day = new Date(thn, bln-1, d).getDay();
                    const isWeekend = (day==0||day==6);
                    let cellCls = ""; let cellTxt = "";
                    let isD = dis.some(x => x.nisn == u.nisn && dateStr >= x.tgl_mulai && dateStr <= x.tgl_selesai);
                    if(isD) { cellCls = "bg-dispen"; cellTxt = "D"; r.counts.d++; }
                    else if(r.att[d]) {
                        const st = r.att[d].status_kehadiran;
                        if(st.includes('Hadir')||st.includes('Masuk')) { cellCls = "bg-hadir"; cellTxt = substr(r.att[d].jam_masuk,0,5); r.counts.h++; }
                        else if(st.includes('Sakit')) { cellCls = "bg-sakit"; cellTxt = "S"; r.counts.s++; }
                        else if(st.includes('Izin')) { cellCls = "bg-izin"; cellTxt = "I"; r.counts.i++; }
                    }
                    if(!cellTxt) {
                        if(isMerah) { cellCls="bg-red-900/40 text-red-500 font-bold"; cellTxt="L"; }
                        else if(isWeekend) { cellCls="bg-weekend"; }
                        else if(new Date(dateStr) < new Date().setHours(0,0,0,0)) { cellCls="bg-alpha"; cellTxt="A"; totA++; }
                        else cellTxt="-";
                    }
                    row += `<td class="p-1 border border-slate-700 text-center ${cellCls} leading-tight text-[9px]">${cellTxt}</td>`;
                }
                row += `<td class="p-2 border border-slate-700 font-bold text-emerald-400">${r.counts.h}</td><td class="p-2 border border-slate-700 font-bold text-purple-400">${r.counts.d}</td><td class="p-2 border border-slate-700 font-bold text-blue-400">${r.counts.s}</td><td class="p-2 border border-slate-700 font-bold text-yellow-400">${r.counts.i}</td><td class="p-2 border border-slate-700 font-bold text-red-400">${totA}</td></tr>`;
                tbody.innerHTML += row;
            });
        }
        function substr(s,a,b){return s?s.substring(a,b):'-';}

        // 4. DISPEN & LIBUR
        function cariSiswaLokal() {
            const v = document.getElementById('inputCariSiswa').value.toLowerCase();
            const res = document.getElementById('hasilCariSiswa'); res.innerHTML='';
            if(v.length<3){ res.classList.add('hidden'); return; }
            const hits = listSiswa.filter(s=>s.nama_siswa.toLowerCase().includes(v)).slice(0,5);
            if(hits.length>0) {
                res.classList.remove('hidden');
                hits.forEach(s => res.innerHTML+=`<div onclick="pilihS('${s.nisn}','${s.nama_siswa}','${s.kelas}')" class="p-2 hover:bg-slate-700 cursor-pointer text-xs text-white border-b border-slate-700">${s.nama_siswa} (${s.kelas})</div>`);
            } else res.innerHTML='<div class="p-2 text-xs text-slate-400">Nihil</div>';
        }
        window.pilihS = (n,nm,k) => { document.getElementById('inputNisnSiswa').value=n; document.getElementById('displayNamaSiswa').value=nm; document.getElementById('inputKelasSiswa').value=k; document.getElementById('hasilCariSiswa').classList.add('hidden'); }
        window.simpanDispen = () => {
            const fd = new FormData(); fd.append('action','simpan_dispen');
            fd.append('nisn', document.getElementById('inputNisnSiswa').value);
            fd.append('nama', document.getElementById('displayNamaSiswa').value);
            fd.append('kelas', document.getElementById('inputKelasSiswa').value);
            fd.append('start', document.getElementById('inputStart').value);
            fd.append('end', document.getElementById('inputEnd').value);
            fd.append('alasan', document.getElementById('inputAlasan').value);
            const f = document.getElementById('inputBuktiSurat').files[0];
            if(f) { const r = new FileReader(); r.onload = (e) => { fd.append('file', e.target.result); sendDispen(fd); }; r.readAsDataURL(f); } else { sendDispen(fd); }
        }
        function sendDispen(fd) { fetch('api_bk.php', {method:'POST', body:fd}).then(r=>r.json()).then(res=>{ Swal.fire('Sukses','','success'); loadDispen(); }); }
        function loadDispen() { fetch('api_bk.php?action=get_dispen_aktif').then(r=>r.json()).then(d=>{ const b=document.getElementById('tbody-dispen'); b.innerHTML=''; d.forEach(x=>{ b.innerHTML+=`<tr class="border-b border-slate-800"><td class="p-3 text-white font-bold">${x.nama_siswa}</td><td class="p-3 text-xs text-blue-400 font-mono">${x.tgl_mulai} s.d ${x.tgl_selesai}</td><td class="p-3 text-xs">${x.alasan}</td><td class="p-3 text-right"><button onclick="hapusDispen('${x.id_dispen}')" class="text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button></td></tr>`; }); lucide.createIcons(); }); }
        window.hapusDispen = (id) => { if(confirm('Hapus?')) { const fd=new FormData(); fd.append('action','hapus_dispen'); fd.append('id',id); fetch('api_bk.php',{method:'POST',body:fd}).then(loadDispen); } }
        function loadHariLibur() { fetch('api_bk.php?action=get_libur').then(r=>r.json()).then(d=>{ cacheHariLibur={}; const b=document.getElementById('tbody-libur'); b.innerHTML=''; d.forEach(x=>{ cacheHariLibur[x.tanggal]=x.keterangan; b.innerHTML+=`<tr class="border-b border-slate-800"><td class="p-3 text-white font-mono">${x.tanggal}</td><td class="p-3 text-xs">${x.keterangan}</td><td class="p-3 text-right"><button onclick="hapusLibur('${x.id_libur}')" class="text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button></td></tr>`; }); }); }
        window.simpanLibur = () => { const fd=new FormData(); fd.append('action','simpan_libur'); fd.append('tanggal',document.getElementById('inputTglLibur').value); fd.append('ket',document.getElementById('inputKetLibur').value); fetch('api_bk.php',{method:'POST',body:fd}).then(()=>{loadHariLibur();}); }
        window.hapusLibur = (id) => { if(confirm('Hapus?')) { const fd=new FormData(); fd.append('action','hapus_libur'); fd.append('id',id); fetch('api_bk.php',{method:'POST',body:fd}).then(loadHariLibur); } }

        // 5. MBG (Makan Bergizi Gratis - DINAMIS)
        window.previewMBG = (tk) => {
            fetch('api_bk.php?action=get_laporan_mbg').then(r=>r.json()).then(d => {
                const tbody = document.getElementById('tbody-mbg'); tbody.innerHTML = '';
                const had = d.hadir_per_kelas; const tot = d.total_per_kelas;
                let gt=0, gh=0, gs=0;
                
                Object.keys(tot).sort().forEach(k => {
                    // Logic Filter Pintar: Cek awalan kelas
                    // Jika TK=1, maka kelas harus diawali 1 (misal 1A, 1B)
                    // Jika TK=X, maka kelas harus diawali X (misal X-1, X-2)
                    
                    let match = false;
                    // SD Logic (1,2,3,4,5,6)
                    if(jenjangSekolah === 'sd') {
                        // Regex: Diawali angka TK, diikuti huruf/spasi/dash (bukan angka 0,1,2 biar gak rancu sama 10,11,12)
                        // Contoh: "1A" cocok dengan "1". "10" tidak cocok dengan "1".
                        if (k.startsWith(tk) && !k.startsWith(tk + '0') && !k.startsWith(tk + '1') && !k.startsWith(tk + '2')) {
                            match = true;
                        }
                    } 
                    // SMA/SMP Logic (X, XI, XII or 7, 8, 9)
                    else {
                        // Kalau SMA (X, XI, XII), logika startsWith aman karena XI beda dengan X.
                        // Kalau SMP (7,8,9), aman juga.
                        if (k.startsWith(tk)) match = true;
                        // Special case: X shouldn't match XI or XII
                        if (tk === 'X' && (k.startsWith('XI') || k.startsWith('XII'))) match = false;
                        if (tk === 'I' && (k.startsWith('II') || k.startsWith('III'))) match = false;
                    }

                    if(match) {
                        let t = tot[k] || 0; let h = had[k] || 0; let s = t - h;
                        gt+=t; gh+=h; gs+=s;
                        tbody.innerHTML += `<tr class="border-b hover:bg-slate-50"><td class="border p-2 text-left font-bold">${k}</td><td class="border p-2 font-bold text-center">${t}</td><td class="border p-2 bg-green-100 text-green-700 font-bold text-center text-lg">${h}</td><td class="border p-2 bg-red-100 text-red-600 font-bold text-center">${s}</td></tr>`;
                    }
                });
                document.getElementById('mbg-grand-total').innerText=gt; document.getElementById('mbg-grand-hadir').innerText=gh; document.getElementById('mbg-grand-sisa').innerText=gs;
                document.getElementById('judul-tabel-mbg').innerText = `Detail Logistik - LEVEL ${tk}`;
                document.getElementById('container-tabel-mbg').classList.remove('hidden');
            });
        }

        window.downloadExcel = (id) => { const t=document.getElementById(id); const l=document.createElement("a"); l.href='data:application/vnd.ms-excel;charset=utf-8,'+encodeURIComponent(t.outerHTML.replace(/<table/g,'<table border="1"').replace(/<br>/g, ' - ')); l.download="LAPORAN_BK.xls"; l.click(); }
        function setupDropdown() { const d=new Date(); const m=["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agt","Sep","Okt","Nov","Des"]; m.forEach((b,i)=>{let o=new Option(b,i); if(i===d.getMonth())o.selected=true; document.getElementById('pilih-bulan').add(o);}); for(let y=d.getFullYear()-1;y<=d.getFullYear()+1;y++) document.getElementById('pilih-tahun').add(new Option(y,y)); }
    </script>
</body>
</html>