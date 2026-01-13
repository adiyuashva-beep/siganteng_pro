<?php
session_start();
// CEK KEAMANAN: Cuma Admin yang boleh masuk sini
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Dashboard - 7 Kebiasaan Hebat</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: #e2e8f0; } /* Tema Gelap biar Elegan */
        .glass-card { 
            background: rgba(30, 41, 59, 0.7); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 1rem; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3); 
        }
        .loading-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(5px); z-index: 50; display: none; align-items: center; justify-content: center; flex-direction: column; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-sm">

    <div id="loading" class="loading-overlay">
        <div class="animate-spin w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full mb-4"></div>
        <p class="font-bold text-indigo-400 animate-pulse">Sedang Menganalisis Data...</p>
    </div>

    <aside class="w-20 bg-slate-900 border-r border-slate-800 flex flex-col items-center py-6 gap-6 z-40 hidden md:flex">
        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-500/30">SG</div>
        <button onclick="window.location.href='admin.php'" class="p-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition" title="Kembali ke Admin Panel"><i data-lucide="layout-dashboard" class="w-6 h-6"></i></button>
        <button class="p-3 text-white bg-indigo-600 rounded-xl shadow-lg shadow-indigo-500/50" title="Dashboard 7 Hebat"><i data-lucide="activity" class="w-6 h-6"></i></button>
        <button onclick="window.location.href='logout.php'" class="mt-auto p-3 text-rose-400 hover:text-rose-200 hover:bg-rose-900/50 rounded-xl" title="Logout"><i data-lucide="log-out" class="w-6 h-6"></i></button>
    </aside>

    <main class="flex-1 p-6 md:p-8 overflow-y-auto bg-slate-950">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-indigo-500/20 text-indigo-300 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border border-indigo-500/30">Monitoring Karakter</span>
                    <span class="text-slate-500 text-xs font-bold" id="waktu-update">Update: -</span>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tight">7 Kebiasaan Hebat</h1>
                <p class="text-slate-400 text-sm mt-1">Analisis visual perkembangan karakter siswa (Realtime Firebase).</p>
            </div>
            
            <div class="flex flex-wrap gap-3 bg-slate-900 p-2 rounded-2xl border border-slate-800 items-end">
                <div class="px-3 border-r border-slate-800">
                    <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1">DARI TANGGAL</label>
                    <input type="date" id="tgl-mulai" class="text-sm font-bold text-white outline-none bg-transparent cursor-pointer w-full [color-scheme:dark]">
                </div>
                <div class="px-3 border-r border-slate-800">
                    <label class="text-[10px] font-bold text-slate-500 uppercase block mb-1">SAMPAI TANGGAL</label>
                    <input type="date" id="tgl-selesai" class="text-sm font-bold text-white outline-none bg-transparent cursor-pointer w-full [color-scheme:dark]">
                </div>
                
                <button onclick="loadDataRange()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-indigo-500/20 transition flex items-center gap-2 h-10 border border-indigo-500">
                    <i data-lucide="search" class="w-4 h-4"></i> Filter
                </button>
                <button onclick="exportExcel()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-emerald-500/30 transition flex items-center gap-2 h-10 border border-emerald-500">
                    <i data-lucide="sheet" class="w-4 h-4"></i> Excel
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="glass-card p-5 relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Total Laporan Masuk</p>
                    <h2 class="text-4xl font-black text-white" id="kpi-total">0</h2>
                    <p class="text-xs text-emerald-400 font-bold mt-2 flex items-center gap-1"><i data-lucide="file-check" class="w-3 h-3"></i> Data Terverifikasi</p>
                </div>
                <div class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-indigo-500/10 to-transparent"></div>
            </div>

            <div class="glass-card p-5 relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Skor Rata-rata</p>
                    <h2 class="text-4xl font-black text-white" id="kpi-avg">0.0</h2>
                    <p class="text-xs text-slate-500 font-bold mt-2">Dari Skala Sempurna 7.0</p>
                </div>
                <div class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-amber-500/10 to-transparent"></div>
            </div>

            <div class="glass-card p-5 relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Kebiasaan Terkuat</p>
                    <h2 class="text-xl font-black text-emerald-400 leading-tight mt-1" id="kpi-top">-</h2>
                    <p class="text-xs text-slate-500 mt-2" id="kpi-top-pct">0% Penerapan</p>
                </div>
                <div class="absolute right-4 top-4 text-emerald-500/20"><i data-lucide="thumbs-up" class="w-12 h-12"></i></div>
            </div>

            <div class="glass-card p-5 relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Perlu Ditingkatkan</p>
                    <h2 class="text-xl font-black text-rose-500 leading-tight mt-1" id="kpi-low">-</h2>
                    <p class="text-xs text-slate-500 mt-2" id="kpi-low-pct">0% Penerapan</p>
                </div>
                <div class="absolute right-4 top-4 text-rose-500/20"><i data-lucide="alert-circle" class="w-12 h-12"></i></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <div class="glass-card p-6 lg:col-span-1 flex flex-col items-center justify-center">
                <h3 class="font-bold text-white mb-4 flex items-center gap-2 self-start">
                    <i data-lucide="radar" class="w-5 h-5 text-indigo-500"></i> Peta Karakter Siswa
                </h3>
                <div class="w-full aspect-square relative">
                    <canvas id="radarChart"></canvas>
                </div>
                <p class="text-[10px] text-slate-500 mt-4 text-center italic">*Semakin luas area biru, semakin baik karakter siswa.</p>
            </div>

            <div class="lg:col-span-2 flex flex-col gap-6">
                <div class="glass-card p-6 flex-1">
                    <h3 class="font-bold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="trending-up" class="w-5 h-5 text-emerald-500"></i> Tren Rata-rata Skor Harian
                    </h3>
                    <div class="relative w-full h-48">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>

                <div class="glass-card p-6 flex-1">
                    <h3 class="font-bold text-white mb-4 flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-amber-500"></i> Partisipasi per Kelas (Top 10)
                    </h3>
                    <div class="relative w-full h-48">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="p-6 border-b border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4">
                <h3 class="font-bold text-white">Detail Input Siswa</h3>
                <div class="relative w-full md:w-64">
                    <input type="text" id="searchTable" onkeyup="filterTable()" placeholder="Cari Nama / Kelas..." class="w-full pl-9 pr-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold outline-none focus:border-indigo-500 text-white placeholder-slate-600">
                    <i data-lucide="search" class="w-4 h-4 text-slate-600 absolute left-3 top-2.5"></i>
                </div>
            </div>
            <div class="overflow-x-auto max-h-[500px]">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-slate-900 z-20 shadow-lg">
                        <tr class="text-slate-500 text-[10px] uppercase font-bold tracking-wider">
                            <th class="p-4 border-b border-slate-800">Tanggal</th>
                            <th class="p-4 border-b border-slate-800">Nama Siswa</th>
                            <th class="p-4 border-b border-slate-800">Kelas</th>
                            <th class="p-4 border-b border-slate-800 text-center">Skor</th>
                            <th class="p-4 border-b border-slate-800 text-center">Capaian Indikator</th>
                            <th class="p-4 border-b border-slate-800 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="text-sm text-slate-300 divide-y divide-slate-800">
                        <tr><td colspan="6" class="p-8 text-center text-slate-500 italic">Pilih tanggal dan klik Filter untuk memuat data.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800 bg-slate-900/50 text-xs text-slate-500 text-center" id="footer-table">
                Menampilkan 0 data
            </div>
        </div>

    </main>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
        import { getFirestore, collection, query, where, getDocs, orderBy } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";
        
        // --- IMPORT KODE SEKOLAH (PENYARING) ---
        import { KODE_SEKOLAH } from "./config_sekolah.js";

        // --- KONFIGURASI FIREBASE ---
        const firebaseConfig = { 
            apiKey: "AIzaSyBXWR-_aJyoMrUjTeNQYlcPD8p3eu58yOo", 
            authDomain: "siganteng-absensi.firebaseapp.com", 
            databaseURL: "https://siganteng-absensi-default-rtdb.asia-southeast1.firebasedatabase.app", 
            projectId: "siganteng-absensi", 
            storageBucket: "siganteng-absensi.firebasestorage.app", 
            messagingSenderId: "917873420012", 
            appId: "1:917873420012:web:0fe1a9eddc5f94959ba7c9" 
        };
        
        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);

        let globalData = []; 
        let radarChartInstance = null;
        let lineChartInstance = null;
        let barChartInstance = null;

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tgl-mulai').value = today;
            document.getElementById('tgl-selesai').value = today;
            loadDataRange();
        });

        window.loadDataRange = async () => {
            const loading = document.getElementById('loading');
            loading.style.display = 'flex';
            
            const start = document.getElementById('tgl-mulai').value;
            const end = document.getElementById('tgl-selesai').value;
            
            if(!start || !end) {
                loading.style.display = 'none';
                return Swal.fire('Tanggal Belum Dipilih', 'Silakan pilih rentang tanggal.', 'warning');
            }

            // --- QUERY DENGAN FILTER ID_SEKOLAH ---
            const q = query(
                collection(db, "jurnal_7_hebat"), 
                where("id_sekolah", "==", KODE_SEKOLAH), // <--- PENYARING AKTIF DI SINI
                where("tanggal", ">=", start), 
                where("tanggal", "<=", end)
            );
            
            try {
                const snap = await getDocs(q);
                globalData = [];
                
                let stats = {
                    bangun: 0, ibadah: 0, olahraga: 0, makan: 0, belajar: 0, sosial: 0, tidur: 0,
                    totalSkor: 0,
                    dailyAvg: {}, // Untuk Line Chart
                    byKelas: {}   // Untuk Bar Chart
                };

                snap.forEach(doc => {
                    const d = doc.data();
                    d.id = doc.id;
                    globalData.push(d);

                    // Hitung Statistik Radar
                    if(d.bangun_pagi) stats.bangun++;
                    if(d.beribadah) stats.ibadah++;
                    if(d.olahraga) stats.olahraga++;
                    if(d.makan_sehat) stats.makan++;
                    if(d.gemar_belajar) stats.belajar++;
                    if(d.bermasyarakat) stats.sosial++;
                    if(d.tidur_cepat) stats.tidur++;
                    
                    stats.totalSkor += (d.poin_harian || 0);

                    // Hitung Statistik Harian (Line Chart)
                    if(!stats.dailyAvg[d.tanggal]) {
                        stats.dailyAvg[d.tanggal] = { total: 0, count: 0 };
                    }
                    stats.dailyAvg[d.tanggal].total += (d.poin_harian || 0);
                    stats.dailyAvg[d.tanggal].count++;

                    // Hitung Statistik Kelas (Bar Chart)
                    const kls = d.kelas || "TIDAK ADA KELAS";
                    if(!stats.byKelas[kls]) stats.byKelas[kls] = 0;
                    stats.byKelas[kls]++;
                });

                updateKPI(globalData.length, stats);
                renderCharts(globalData.length, stats);
                renderTable(globalData);
                
                document.getElementById('waktu-update').innerText = "Update: " + new Date().toLocaleTimeString();

            } catch (e) {
                console.error(e);
                Swal.fire('Error', 'Gagal memuat data dari Firebase: ' + e.message, 'error');
            } finally {
                loading.style.display = 'none';
            }
        }

        function updateKPI(total, stats) {
            document.getElementById('kpi-total').innerText = total;
            const avg = total > 0 ? (stats.totalSkor / total).toFixed(1) : 0;
            document.getElementById('kpi-avg').innerText = avg;

            const habits = [
                {label: 'Bangun Pagi', val: stats.bangun}, {label: 'Ibadah', val: stats.ibadah},
                {label: 'Olahraga', val: stats.olahraga}, {label: 'Makan Sehat', val: stats.makan},
                {label: 'Belajar', val: stats.belajar}, {label: 'Bermasyarakat', val: stats.sosial},
                {label: 'Tidur Cepat', val: stats.tidur}
            ];
            
            habits.sort((a,b) => b.val - a.val);
            
            const top = habits[0];
            const low = habits[habits.length - 1];

            document.getElementById('kpi-top').innerText = total > 0 ? top.label : "-";
            document.getElementById('kpi-top-pct').innerText = total > 0 ? Math.round((top.val/total)*100) + "% Penerapan" : "0%";
            
            document.getElementById('kpi-low').innerText = total > 0 ? low.label : "-";
            document.getElementById('kpi-low-pct').innerText = total > 0 ? Math.round((low.val/total)*100) + "% Penerapan" : "0%";
        }

        function renderCharts(total, stats) {
            // Setup Warna Grafik Biar Glowing
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = '#334155';

            // 1. RADAR CHART (Peta Karakter)
            const ctxRadar = document.getElementById('radarChart').getContext('2d');
            const dataRadar = [
                stats.bangun, stats.ibadah, stats.olahraga, 
                stats.makan, stats.belajar, stats.sosial, stats.tidur
            ].map(v => total > 0 ? Math.round((v/total)*100) : 0);

            if(radarChartInstance) radarChartInstance.destroy();
            radarChartInstance = new Chart(ctxRadar, {
                type: 'radar',
                data: {
                    labels: ['Bangun', 'Ibadah', 'Olahraga', 'Makan', 'Belajar', 'Sosial', 'Tidur'],
                    datasets: [{
                        label: '% Penerapan',
                        data: dataRadar,
                        backgroundColor: 'rgba(99, 102, 241, 0.4)', // Indigo Glowing
                        borderColor: '#818cf8',
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#818cf8',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            suggestedMin: 0, suggestedMax: 100,
                            grid: { color: '#334155' },
                            angleLines: { color: '#334155' },
                            pointLabels: { color: '#e2e8f0', font: { size: 10, weight: 'bold' } },
                            ticks: { display: false } // Sembunyikan angka di jaring laba-laba biar bersih
                        }
                    },
                    plugins: { legend: { display: false } }
                }
            });

            // 2. LINE CHART (Tren Harian)
            const ctxLine = document.getElementById('lineChart').getContext('2d');
            const sortedDates = Object.keys(stats.dailyAvg).sort();
            const dailyScores = sortedDates.map(date => {
                const dayStat = stats.dailyAvg[date];
                return (dayStat.total / dayStat.count).toFixed(2);
            });

            if(lineChartInstance) lineChartInstance.destroy();
            lineChartInstance = new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: sortedDates,
                    datasets: [{
                        label: 'Rata-rata Skor',
                        data: dailyScores,
                        borderColor: '#10b981', // Emerald
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    scales: { y: { beginAtZero: true, max: 7.5, grid: { color: '#1e293b' } }, x: { grid: { display: false } } }, 
                    plugins: { legend: { display: false } } 
                }
            });

            // 3. BAR CHART (Top Kelas)
            const ctxBar = document.getElementById('barChart').getContext('2d');
            // Urutkan kelas berdasarkan partisipasi terbanyak, ambil top 10
            const kelasKeys = Object.keys(stats.byKelas).sort((a,b) => stats.byKelas[b] - stats.byKelas[a]).slice(0, 10);
            const kelasVals = kelasKeys.map(k => stats.byKelas[k]);

            if(barChartInstance) barChartInstance.destroy();
            barChartInstance = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: kelasKeys,
                    datasets: [{
                        label: 'Jml Laporan',
                        data: kelasVals,
                        backgroundColor: '#f59e0b', // Amber
                        borderRadius: 4
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    scales: { y: { beginAtZero: true, grid: { color: '#1e293b' } }, x: { grid: { display: false } } },
                    plugins: { legend: { display: false } }
                }
            });
        }

        function renderTable(data) {
            const tbody = document.getElementById('table-body');
            tbody.innerHTML = '';

            if(data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-slate-500 italic">Tidak ada data ditemukan untuk rentang tanggal ini.</td></tr>`;
                document.getElementById('footer-table').innerText = "Menampilkan 0 data";
                return;
            }

            // Urutkan data terbaru di atas
            data.sort((a,b) => (b.waktu_update?.seconds || 0) - (a.waktu_update?.seconds || 0));

            data.slice(0, 100).forEach(d => { 
                const jam = d.waktu_update ? new Date(d.waktu_update.seconds*1000).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) : '-';
                
                // Indikator Titik Warna-Warni
                const dot = (active) => `<span class="inline-block w-2.5 h-2.5 rounded-full ${active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]' : 'bg-slate-700'} mr-1"></span>`;
                const visualCapaian = `<div class="flex justify-center">${dot(d.bangun_pagi)}${dot(d.beribadah)}${dot(d.olahraga)}${dot(d.makan_sehat)}${dot(d.gemar_belajar)}${dot(d.bermasyarakat)}${dot(d.tidur_cepat)}</div>`;
                
                const safeData = encodeURIComponent(JSON.stringify(d));

                const tr = document.createElement('tr');
                tr.className = "border-b border-slate-800 hover:bg-slate-800/50 transition";
                tr.innerHTML = `
                    <td class="p-4 font-mono font-bold text-slate-400">${d.tanggal}<br><span class="text-[10px] font-normal text-slate-600">${jam}</span></td>
                    <td class="p-4 font-bold text-white">${d.nama}</td>
                    <td class="p-4"><span class="bg-indigo-900/50 text-indigo-300 border border-indigo-500/30 px-2 py-1 rounded text-xs font-bold">${d.kelas}</span></td>
                    <td class="p-4 text-center font-black text-lg ${d.poin_harian == 7 ? 'text-emerald-400' : 'text-slate-400'}">${d.poin_harian}</td>
                    <td class="p-4 text-center">${visualCapaian}</td>
                    <td class="p-4 text-center"><button onclick="lihatDetail('${safeData}')" class="text-blue-400 hover:text-white hover:bg-blue-600 p-2 rounded-lg font-bold text-xs transition border border-blue-500/30">Detail</button></td>
                `;
                tbody.appendChild(tr);
            });
            document.getElementById('footer-table').innerText = `Menampilkan ${Math.min(data.length, 100)} dari ${data.length} data`;
        }

        window.filterTable = () => {
            const input = document.getElementById('searchTable').value.toUpperCase();
            const trs = document.getElementById('table-body').getElementsByTagName('tr');
            for (let i = 0; i < trs.length; i++) {
                const tdNama = trs[i].getElementsByTagName("td")[1];
                const tdKelas = trs[i].getElementsByTagName("td")[2];
                if (tdNama || tdKelas) {
                    const txtVal = (tdNama.textContent || tdNama.innerText) + " " + (tdKelas.textContent || tdKelas.innerText);
                    trs[i].style.display = txtVal.toUpperCase().indexOf(input) > -1 ? "" : "none";
                }        
            }
        }

        window.lihatDetail = (encodedJson) => {
            const d = JSON.parse(decodeURIComponent(encodedJson));
            const row = (lbl, val) => `<div class="flex justify-between border-b border-slate-700 py-2"><span class="text-slate-400 text-xs uppercase font-bold">${lbl}</span><span class="text-white font-bold text-sm text-right">${val}</span></div>`;
            const imgRow = (lbl, url) => {
                if(!url || url === '-' || !url.includes('http')) return row(lbl, '<span class="text-slate-600 italic">Tidak ada foto</span>');
                return `<div class="py-2"><p class="text-slate-400 text-xs uppercase font-bold mb-2">${lbl}</p><img src="${url}" class="w-full h-40 object-cover rounded-xl border border-slate-600"></div>`;
            };
            
            Swal.fire({
                title: `<span class="text-lg font-black text-white">${d.nama}</span>`,
                html: `<div class="text-left space-y-1 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                        ${row('Kelas', d.kelas)}
                        ${row('Skor', d.poin_harian + ' / 7')}
                        ${row('Bangun Pagi', d.jam_bangun)}
                        ${row('Ibadah', d.jenis_ibadah)}
                        ${row('Olahraga', d.jenis_olahraga)}
                        ${row('Menu Makan', d.menu_makan)}
                        ${imgRow('Foto Makan', d.foto_makan)}
                        ${row('Mapel Belajar', d.mapel_belajar)}
                        ${imgRow('Foto Belajar', d.foto_belajar)}
                        ${row('Kegiatan Sosial', d.keg_sosial)}
                        ${row('Jam Tidur', d.jam_tidur)}
                      </div>`,
                background: '#1e293b',
                color: '#fff',
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Tutup',
                width: 450
            });
        }

        window.exportExcel = () => {
            if(globalData.length === 0) return Swal.fire('Kosong', 'Tidak ada data untuk didownload.', 'info');
            const dataExcel = globalData.map(d => ({
                "Tanggal": d.tanggal, "Waktu Input": d.waktu_update ? new Date(d.waktu_update.seconds*1000).toLocaleTimeString('id-ID') : '-',
                "NISN": d.nisn, "Nama Siswa": d.nama, "Kelas": d.kelas, "Skor Total": d.poin_harian,
                "Jam Bangun": d.jam_bangun, "Jenis Ibadah": d.jenis_ibadah, "Jenis Olahraga": d.jenis_olahraga,
                "Menu Makan": d.menu_makan, "Link Foto Makan": d.foto_makan || '-',
                "Mapel Belajar": d.mapel_belajar, "Link Foto Belajar": d.foto_belajar || '-',
                "Kegiatan Sosial": d.keg_sosial, "Jam Tidur": d.jam_tidur
            }));
            const ws = XLSX.utils.json_to_sheet(dataExcel);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Rekap 7 Hebat");
            XLSX.writeFile(wb, `Rekap_7Hebat_${document.getElementById('tgl-mulai').value}_sd_${document.getElementById('tgl-selesai').value}.xlsx`);
        }
    </script>
</body>
</html>