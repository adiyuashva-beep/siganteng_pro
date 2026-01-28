<?php
session_start();
// Cek Login Siswa
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != 'siswa') {
    header("location:login.php"); exit();
}
$nisn_session = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Siswa - SiGanteng</title>
    <meta name="theme-color" content="#0f172a">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; -webkit-tap-highlight-color: transparent; }
        .hidden-page { display: none !important; }
        .fade-in { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .story-ring { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); padding: 3px; border-radius: 50%; }
        .glass-panel { background: white; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-radius: 1.5rem; }
        
        /* --- STYLE KAMERA --- */
        .camera-overlay { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px); }
        .camera-box { position: relative; width: 85%; aspect-ratio: 3/4; background: black; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); border: 4px solid white; }
        .flash-effect { position: absolute; inset: 0; background: white; opacity: 0; pointer-events: none; z-index: 50; }
        .flash-active { animation: flashAnim 0.4s ease-out; }
        @keyframes flashAnim { 0% { opacity: 1; } 100% { opacity: 0; } }
        .focus-grid { position: absolute; inset: 0; pointer-events: none; z-index: 10; background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 33% 33%; }
        .fly-to-server { animation: flyUp 0.8s ease-in forwards; }
        @keyframes flyUp { 0% { transform: scale(1) translateY(0); opacity: 1; } 100% { transform: scale(0.1) translateY(-500px); opacity: 0; } }
    </style>
</head>
<body class="text-slate-800 h-screen flex flex-col overflow-hidden bg-slate-50">

    <input type="file" id="fileInputProfil" accept="image/*" style="display:none" onchange="prosesUploadGaleri(this, 'profil')">
    <input type="file" id="fileInputBukti" accept="image/*" style="display:none" onchange="prosesUploadGaleri(this, 'bukti')">

    <main id="main-container" class="flex-1 overflow-y-auto hide-scroll relative">
        
        <div id="page-dashboard" class="p-6 fade-in min-h-screen pb-32">
            <div class="flex flex-col items-center justify-center mb-6 mt-4">
                <div class="relative group cursor-pointer active:scale-95 transition" onclick="pilihFotoGaleri('profil')">
                    <div class="story-ring w-32 h-32 shadow-xl shadow-pink-500/20">
                        <img id="dash-foto" src="" class="w-full h-full rounded-full object-cover border-4 border-white bg-white">
                    </div>
                    <div class="absolute bottom-1 right-1 bg-slate-900 text-white p-2 rounded-full border-4 border-white shadow-lg"><i data-lucide="image-plus" class="w-4 h-4"></i></div>
                </div>
                <p id="info-jatah-foto" class="text-[10px] text-slate-400 font-bold mt-2 bg-slate-100 px-3 py-1 rounded-full">Tap foto untuk ganti</p>
                <div class="text-center mt-2">
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Sugeng Enjang,</p>
                    <h2 class="text-2xl font-black text-slate-800 leading-tight" id="dash-nama">...</h2>
                    <div class="flex items-center justify-center gap-2 mt-2">
                        <span id="dash-kelas" class="text-[10px] font-black bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full uppercase tracking-wider">...</span>
                        <div class="h-1 w-1 bg-slate-300 rounded-full"></div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase">SISWA AKTIF</span>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 rounded-[2rem] p-6 text-white shadow-2xl mb-4 relative overflow-hidden">
                <div class="relative z-10 flex justify-between items-end">
                    <div>
                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mb-2">Status Terakhir</p>
                        <h1 class="text-3xl font-black tracking-tighter" id="hero-status">Memuat...</h1>
                        <p class="text-[10px] text-slate-400 mt-2 font-mono" id="sub-status">Syncing...</p>
                    </div>
                    <div class="bg-white/10 p-3 rounded-2xl backdrop-blur-sm"><i data-lucide="activity" class="w-6 h-6 text-emerald-400"></i></div>
                </div>
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-600 rounded-full blur-[60px] opacity-50"></div>
            </div>

            <div class="glass-panel p-4 mb-8 grid grid-cols-2 gap-4">
                <div class="flex items-center gap-3 border-r border-slate-100">
                    <div class="bg-emerald-100 p-2 rounded-xl text-emerald-600"><i data-lucide="log-in" class="w-5 h-5"></i></div>
                    <div><p class="text-[10px] text-slate-400 font-bold uppercase">Masuk</p><p id="info-jam-masuk" class="text-lg font-black text-slate-800">--:--</p></div>
                </div>
                <div class="flex items-center gap-3 pl-2">
                    <div class="bg-rose-100 p-2 rounded-xl text-rose-600"><i data-lucide="log-out" class="w-5 h-5"></i></div>
                    <div><p class="text-[10px] text-slate-400 font-bold uppercase">Pulang</p><p id="info-jam-pulang" class="text-lg font-black text-slate-800">--:--</p></div>
                </div>
            </div>

            <div id="wadah-menu" class="grid grid-cols-3 gap-3 mb-8"></div>

            <div id="section-menu-dinamis" class="mt-4 hidden">
                <h3 class="font-bold text-slate-700 text-sm mb-4 ml-1 flex items-center gap-2"><i data-lucide="sparkles" class="w-4 h-4 text-orange-500"></i> Pintasan Layanan</h3>
                <div id="grid-menu-dinamis" class="grid grid-cols-2 gap-3">
                    <div class="col-span-2 text-center text-xs text-slate-400 italic py-4">Memuat layanan...</div>
                </div>
            </div>
        </div>

        <div id="page-absen" class="hidden-page p-6 fade-in h-full bg-white flex flex-col">
            <button onclick="kembali()" class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 mb-4"><i data-lucide="chevron-left" class="w-6 h-6"></i></button>
            <div class="text-center mb-4">
                <h1 class="font-black text-5xl text-slate-800 tracking-tighter" id="jamDigital">00:00</h1>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em]" id="tanggalDigital">...</p>
            </div>
            <div class="relative w-full h-52 rounded-[2.5rem] overflow-hidden border-4 border-slate-50 shadow-inner mb-6 shrink-0">
                <div id="mapMini" class="h-full w-full z-0"></div>
                <div class="absolute bottom-3 left-3 right-3 z-10 text-center">
                    <div id="jarakBox" class="inline-block bg-slate-900/80 backdrop-blur text-white text-[10px] font-bold py-2 px-4 rounded-xl shadow-lg border border-white/10">GPS Mendeteksi...</div>
                </div>
            </div>
            <div class="space-y-3 mb-8 flex-1 overflow-y-auto">
                <button id="btnMasuk" onclick="klikMasuk()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-5 rounded-[2rem] shadow-xl flex items-center gap-4 active:scale-95 transition group">
                    <div class="bg-white/20 p-3 rounded-2xl"><i data-lucide="scan-face" class="w-8 h-8"></i></div>
                    <div class="text-left flex-1"><span class="text-blue-100 text-[10px] font-bold uppercase block">Sudah Sampai?</span><span class="font-black text-2xl">ABSEN MASUK</span></div>
                </button>
                <div id="groupDiSekolah" class="hidden space-y-3">
                    <button id="btnPulang" onclick="klikPulang()" class="w-full bg-slate-800 text-white p-5 rounded-[2rem] shadow-xl flex items-center gap-4 active:scale-95 transition">
                        <div class="bg-white/10 p-3 rounded-2xl"><i data-lucide="log-out" class="w-8 h-8"></i></div>
                        <div class="text-left flex-1"><span class="text-slate-400 text-[10px] font-bold uppercase block">Waktunya Pulang?</span><span class="font-black text-2xl">ABSEN PULANG</span></div>
                    </button>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <button id="btnIzinKeluar" onclick="klikIzinKeluar()" class="bg-orange-50 text-orange-600 p-4 rounded-[1.5rem] font-bold text-xs flex flex-col items-center gap-2"><i data-lucide="door-open" class="w-6 h-6"></i> Izin Keluar</button>
                        <button id="btnKembali" onclick="klikKembali()" class="hidden bg-emerald-50 text-emerald-600 p-4 rounded-[1.5rem] font-bold text-xs flex flex-col items-center gap-2"><i data-lucide="map-pin" class="w-6 h-6"></i> Saya Kembali</button>
                    </div>
                </div>
                <button onclick="bukaKartu()" class="w-full bg-white text-slate-800 border border-slate-200 p-4 rounded-[1.5rem] font-bold flex items-center justify-center gap-3"><i data-lucide="qr-code" class="w-5 h-5 text-slate-400"></i><span class="text-sm">Kartu Digital</span></button>
            </div>
        </div>

        <div id="page-sakit" class="hidden-page p-6 fade-in h-full bg-white flex flex-col overflow-y-auto">
             <button onclick="kembali()" class="mb-6 flex items-center gap-2 font-bold text-slate-500 text-sm"><i data-lucide="arrow-left" class="w-5 h-5"></i> Kembali</button>
             <h2 class="text-2xl font-black text-slate-800 mb-6">Lapor Izin / Sakit</h2>
             <div class="grid grid-cols-2 gap-4 mb-6">
                 <button id="btnOptSakit" onclick="pilihJenisIzin('Sakit')" class="p-4 rounded-2xl border-2 border-slate-100 font-bold text-slate-400 flex flex-col items-center gap-2 transition active:scale-95"><i data-lucide="thermometer" class="w-8 h-8"></i><span>SAKIT</span></button>
                 <button id="btnOptIzin" onclick="pilihJenisIzin('Izin')" class="p-4 rounded-2xl border-2 border-slate-100 font-bold text-slate-400 flex flex-col items-center gap-2 transition active:scale-95"><i data-lucide="mail-open" class="w-8 h-8"></i><span>IZIN</span></button>
             </div>
             <div id="areaUploadFoto" class="hidden space-y-4">
                 <div><label class="block text-sm font-bold text-slate-600 mb-2">Keterangan</label><textarea id="ketIzin" rows="3" class="w-full p-4 rounded-2xl border border-slate-200 font-bold text-slate-700 outline-none focus:border-blue-500" placeholder="Contoh: Demam tinggi / Ada acara keluarga..."></textarea></div>
                 <div>
                     <label class="block text-sm font-bold text-slate-600 mb-2">Bukti Foto <span id="labelWajibFoto" class="text-red-500 hidden">(Wajib)</span></label>
                     <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center">
                         <img id="previewBukti" src="" class="hidden w-full h-48 object-cover rounded-xl mb-4">
                         <div class="flex gap-2 justify-center">
                             <button id="btnAmbilBukti" onclick="bukaCam('BuktiSakit')" class="bg-blue-50 text-blue-600 px-4 py-3 rounded-xl font-bold text-xs flex items-center gap-2 hover:bg-blue-100"><i data-lucide="camera" class="w-4 h-4"></i> Kamera</button>
                             <button onclick="pilihFotoGaleri('bukti')" class="bg-indigo-50 text-indigo-600 px-4 py-3 rounded-xl font-bold text-xs flex items-center gap-2 hover:bg-indigo-100"><i data-lucide="image-plus" class="w-4 h-4"></i> Galeri</button>
                         </div>
                         <p class="text-[10px] text-slate-400 mt-2">Upload bukti jika ada (opsional untuk Izin).</p>
                     </div>
                 </div>
                 <button onclick="kirimLaporanIzin()" class="w-full bg-slate-800 text-white p-5 rounded-[2rem] shadow-xl font-bold text-lg mt-8 active:scale-95 transition">KIRIM LAPORAN</button>
             </div>
        </div>

        <div id="page-refleksi" class="hidden-page p-6 fade-in h-full bg-white flex flex-col overflow-y-auto">
            <button onclick="kembali()" class="mb-6 flex items-center gap-2 font-bold text-slate-500 text-sm"><i data-lucide="arrow-left" class="w-5 h-5"></i> Kembali</button>
            <div class="flex justify-between items-end mb-6">
                <div><h2 class="text-2xl font-black text-slate-800">Timeline KBM</h2><p class="text-slate-400 text-xs">Pelajaran di kelasmu hari ini.</p></div>
                <div class="bg-pink-50 p-3 rounded-2xl"><i data-lucide="brain-circuit" class="w-6 h-6 text-pink-500"></i></div>
            </div>
            <div id="timeline-kbm" class="space-y-4"><div class="text-center py-10 text-slate-400 text-xs italic">Memuat jadwal...</div></div>
        </div>

        <div id="page-profil" class="hidden-page p-6 fade-in h-full bg-white overflow-y-auto">
             <button onclick="kembali()" class="mb-6 flex items-center gap-2 font-bold text-slate-500 text-sm"><i data-lucide="arrow-left" class="w-5 h-5"></i> Kembali</button>
             <div class="flex flex-col items-center mb-8">
                <div class="w-28 h-28 relative mb-4">
                     <img id="profil-page-foto" src="" class="w-full h-full rounded-full object-cover border-4 border-slate-100 shadow-xl">
                     <button onclick="pilihFotoGaleri('profil')" class="absolute bottom-0 right-0 bg-blue-600 text-white p-2.5 rounded-full border-4 border-white shadow-sm"><i data-lucide="image-plus" class="w-4 h-4"></i></button>
                </div>
                <h3 class="font-black text-xl text-slate-800" id="profil-page-nama">...</h3>
                <p class="text-slate-400 text-sm font-mono" id="profil-page-nisn">...</p>
             </div>
             <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 space-y-4 mb-6">
                <h4 class="font-bold text-slate-700 text-sm flex items-center gap-2"><i data-lucide="lock" class="w-4 h-4 text-rose-600"></i> Keamanan Akun</h4>
                <div><label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Password Baru</label><input type="password" id="inputPassBaru" class="w-full p-4 rounded-2xl border border-slate-200 font-bold text-slate-700 outline-none transition focus:border-rose-500" placeholder="Minimal 6 karakter..."></div>
                <button onclick="gantiPassword()" class="w-full bg-rose-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-rose-500/20 active:scale-95 transition">Ganti Password</button>
             </div>
             <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 space-y-4 mb-6">
                <h4 class="font-bold text-slate-700 text-sm flex items-center gap-2"><i data-lucide="smartphone" class="w-4 h-4 text-green-600"></i> Database WhatsApp</h4>
                <div><label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">No. WA Siswa</label><input type="tel" id="inputWaSiswa" class="w-full p-4 rounded-2xl border border-slate-200 font-bold text-slate-700 outline-none transition focus:border-blue-500" placeholder="0812..."></div>
                <div><label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">No. WA Orang Tua</label><input type="tel" id="inputWaOrtu" class="w-full p-4 rounded-2xl border border-slate-200 font-bold text-slate-700 outline-none transition focus:border-blue-500" placeholder="0813..."></div>
                <button onclick="simpanDataWA()" class="w-full bg-green-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-green-500/20 active:scale-95 transition">Simpan Kontak WA</button>
             </div>
             <button onclick="logout()" class="w-full text-red-500 font-bold bg-red-50 py-4 rounded-2xl border border-red-100">Logout</button>
        </div>

    </main>

    <div id="modalKamera" class="hidden fixed inset-0 z-50 camera-overlay flex flex-col items-center justify-center fade-in">
        <div class="absolute top-6 w-full flex justify-between items-center px-6 z-30"><h3 class="text-white font-bold text-sm bg-black/40 backdrop-blur-md px-4 py-2 rounded-full border border-white/20" id="judulKamera">KAMERA</h3><button onclick="tutupKamera()" class="p-3 rounded-full bg-white/10 text-white backdrop-blur border border-white/20 hover:bg-white/20 transition"><i data-lucide="x" class="w-6 h-6"></i></button></div>
        <div class="camera-box relative"><video id="videoStream" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video><canvas id="canvasFoto" class="hidden"></canvas><div class="focus-grid"></div><div id="cameraFlash" class="flash-effect"></div><img id="imgAnimasi" src="" class="absolute inset-0 w-full h-full object-cover hidden z-40"></div>
        <div class="mt-8 z-30"><button onclick="jepretFoto()" class="w-20 h-20 rounded-full border-[6px] border-white flex items-center justify-center active:scale-90 transition shadow-2xl bg-transparent"><div class="w-16 h-16 bg-white rounded-full border-4 border-slate-300"></div></button></div>
    </div>

    <div id="modalKartu" class="hidden fixed inset-0 z-50 bg-slate-900/90 backdrop-blur-md flex items-center justify-center p-6">
        <div class="bg-white p-8 rounded-[2.5rem] w-full max-w-xs text-center shadow-2xl relative overflow-hidden"><div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 to-indigo-500"></div><h3 class="font-black text-slate-800 text-xl mb-1 mt-4">KARTU PELAJAR</h3><div class="bg-slate-50 p-4 rounded-3xl border-2 border-dashed border-slate-200 mb-6 inline-block mt-4"><div id="qrcode"></div></div><h2 id="namaDiKartu" class="font-black text-slate-800 text-lg leading-tight mb-1">...</h2><p id="nisnDiKartu" class="font-mono text-slate-600 font-bold text-xs tracking-widest">...</p><button onclick="tutupKartu()" class="mt-8 w-full py-3 text-slate-400 font-bold text-xs uppercase hover:text-red-500">Tutup</button></div>
    </div>

    <div id="loadingOverlay" class="hidden fixed inset-0 z-[60] bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center"><div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div><h5 class="font-black text-slate-800 text-xs tracking-[0.2em] animate-pulse">MEMPROSES...</h5></div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
        import { getFirestore, collection, addDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";
        import { KODE_SEKOLAH } from "./config_sekolah.js";

        // CONFIG FIREBASE (WAJIB SAMA DI SEMUA FILE)
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

        // DATA SESSION PHP
        const NISN_USER = "<?php echo $nisn_session; ?>";
        
        let userSiswa = null; 
        let lokasiUser = null; 
        let map = null; 
        let markerSiswa = null; 
        let streamKamera = null; 
        let tipeAbsenAktif = ""; 
        let tempFotoBukti = null; 
        let configSekolah = {lat:-7.67, lng:109.63, radius:50, mode:'strict'}; 
        let jenisIzinAktif = ""; 

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            initDataSiswa();
            mulaiJam(); 
            loadMenuDinamis();
            setInterval(pantauDataRealtime, 15000); 
            cekGPS();
            
            history.pushState(null, null, location.href);
            window.onpopstate = function () {
                if(!document.getElementById('modalKamera').classList.contains('hidden')) { tutupKamera(); history.pushState(null, null, location.href); return; }
                if(!document.getElementById('modalKartu').classList.contains('hidden')) { tutupKartu(); history.pushState(null, null, location.href); return; }
                if(document.getElementById('page-dashboard').classList.contains('hidden-page')) { kembali(); history.pushState(null, null, location.href); } else { history.pushState(null, null, location.href); }
            };
        });

        function initDataSiswa() {
            fetch(`api_siswa.php?action=get_profil&nisn=${NISN_USER}`).then(r=>r.json()).then(d => {
                userSiswa = d.siswa;
                configSekolah = d.config;
                document.getElementById('dash-nama').innerText = userSiswa.nama;
                document.getElementById('dash-kelas').innerText = userSiswa.kelas;
                document.getElementById('profil-page-nama').innerText = userSiswa.nama;
                document.getElementById('profil-page-nisn').innerText = userSiswa.nisn;
                if(userSiswa.foto) { document.getElementById('dash-foto').src = userSiswa.foto; document.getElementById('profil-page-foto').src = userSiswa.foto; }
                else { document.getElementById('dash-foto').src = `https://ui-avatars.com/api/?name=${userSiswa.nama}&background=random`; }
                document.getElementById('inputWaSiswa').value = userSiswa.wa_siswa || '';
                document.getElementById('inputWaOrtu').value = userSiswa.wa_ortu || '';
                
                renderMenu(configSekolah.jenjang);
                pantauDataRealtime();
            });
        }

        // --- FUNGSI KIRIM DATA (MODIFIKASI HYBRID FIREBASE) ---
        window.kirimData = async (tipe, ket, foto) => {
            document.getElementById('loadingOverlay').classList.remove('hidden');
            
            try {
                // 1. KIRIM KE FIREBASE (JALAN TOL - WAJIB SUKSES)
                // Data ini aman di Cloud, gak bakal hilang walaupun server sekolah down
                await addDoc(collection(db, "presensi_harian"), {
                    id_sekolah: KODE_SEKOLAH,
                    nisn: NISN_USER,
                    nama: userSiswa.nama,
                    kelas: userSiswa.kelas,
                    tipe_absen: tipe, // Masuk, Pulang, Izin, Sakit
                    lat: lokasiUser ? lokasiUser.lat : 0,
                    lng: lokasiUser ? lokasiUser.lng : 0,
                    foto: foto,
                    keterangan: ket,
                    waktu_server: serverTimestamp(),
                    tanggal: new Date().toISOString().split('T')[0]
                });

                // 2. KIRIM KE PHP (JALAN BIASA - USAHAKAN SUKSES)
                // Ini biar dashboard langsung update "JAM MASUK".
                // Kalau server PHP down/lemot, kita abaikan errornya (catch) karena data udah aman di Firebase.
                const pl = { action:'absen', nisn:NISN_USER, tipe:tipe, lat:lokasiUser.lat, lng:lokasiUser.lng, foto:foto, keterangan:ket };
                fetch('api_siswa.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(pl) })
                .then(r=>r.json())
                .catch(err => console.log("PHP Server Busy (Aman, data sudah di Firebase)"));

                // 3. SUKSES!
                document.getElementById('loadingOverlay').classList.add('hidden');
                tutupKamera();
                
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Absensi tercatat di Cloud Server!',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    pantauDataRealtime();
                    if(tipe=='Sakit'||tipe=='Izin') bukaHalaman('page-dashboard');
                });

            } catch (e) {
                // Error Firebase (Internet mati total)
                document.getElementById('loadingOverlay').classList.add('hidden');
                console.error("Firebase Error:", e);
                Swal.fire('Gagal', 'Koneksi internet tidak stabil. Coba lagi!', 'error');
            }
        }

        // --- NAVIGASI & LAINNYA (TETAP SAMA) ---
        window.renderMenu = (jenjang) => {
            let menu = [];
            if (jenjang === 'sd') {
                menu = [
                    { id: 'btnHebat', label: '7 HEBAT', icon: 'trophy', color: 'text-yellow-500', bg: 'bg-yellow-50', action: "window.location.href='hebat.php'" },
                    { id: 'btnRefleksi', label: 'Jurnal KBM', icon: 'book-open', color: 'text-pink-500', bg: 'bg-pink-50', action: "bukaHalaman('page-refleksi'); loadTimelineKBM();" },
                    { id: 'btnProfil', label: 'Profil', icon: 'user', color: 'text-indigo-500', bg: 'bg-indigo-50', action: "bukaHalaman('page-profil')" }
                ];
            } else {
                menu = [ 
                    { id: 'btnAbsen', label: 'PRESENSI', icon: 'scan-face', color: 'text-blue-500', bg: 'bg-blue-50', action: "bukaHalaman('page-absen'); setTimeout(initMap, 500);" }, 
                    { id: 'btnSakit', label: 'Izin/Sakit', icon: 'thermometer', color: 'text-rose-500', bg: 'bg-rose-50', action: "bukaHalaman('page-sakit')" }, 
                    { id: 'btnRefleksi', label: 'Jurnal KBM', icon: 'book-open', color: 'text-pink-500', bg: 'bg-pink-50', action: "bukaHalaman('page-refleksi'); loadTimelineKBM();" }, 
                    { id: 'btnHebat', label: '7 HEBAT', icon: 'trophy', color: 'text-yellow-500', bg: 'bg-yellow-50', action: "window.location.href='hebat.php'" },
                    { id: 'btnProfil', label: 'Profil', icon: 'user', color: 'text-indigo-500', bg: 'bg-indigo-50', action: "bukaHalaman('page-profil')" } 
                ];
            }
            const c = document.getElementById('wadah-menu'); c.innerHTML = '';
            menu.forEach(m => { c.innerHTML += `<button onclick="${m.action}" class="flex flex-col items-center gap-2 p-4 bg-white rounded-2xl border border-slate-100 shadow-sm active:scale-95 transition"><div class="${m.bg} ${m.color} p-3 rounded-xl"><i data-lucide="${m.icon}" class="w-6 h-6"></i></div><span class="text-[10px] font-bold text-slate-600 uppercase tracking-tight">${m.label}</span></button>`; });
            lucide.createIcons();
        }
        window.bukaHalaman = (id) => { document.querySelectorAll('main > div').forEach(d => d.classList.add('hidden-page')); document.getElementById(id).classList.remove('hidden-page'); document.getElementById(id).classList.add('fade-in'); }
        window.kembali = () => { bukaHalaman('page-dashboard'); pantauDataRealtime(); }

        window.loadTimelineKBM = () => {
            const c = document.getElementById('timeline-kbm'); c.innerHTML = '<div class="text-center py-10 text-slate-400 text-xs italic">Memuat...</div>';
            fetch(`api_siswa.php?action=get_timeline&nisn=${NISN_USER}&kelas=${userSiswa.kelas}`).then(r=>r.json()).then(data => {
                c.innerHTML = ''; if(data.length==0) { c.innerHTML='<div class="text-center py-10 text-slate-400 text-xs">Belum ada pelajaran.</div>'; return; }
                data.forEach(d => {
                    const jam = d.waktu.split(' ')[1].substring(0,5);
                    let area = `<div class="mt-3 pt-3 border-t border-slate-100"><textarea id="pesan-${d.id_jurnal}" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-bold text-slate-700 outline-none mb-3" rows="2" placeholder="Tulis catatan..."></textarea><div class="grid grid-cols-4 gap-2"><button onclick="kirimRefleksi('${d.id_jurnal}',4)" class="bg-emerald-50 text-emerald-600 p-2 rounded-xl text-[8px] font-bold uppercase flex flex-col items-center gap-1"><span>🥰</span>Senang</button><button onclick="kirimRefleksi('${d.id_jurnal}',3)" class="bg-amber-50 text-amber-600 p-2 rounded-xl text-[8px] font-bold uppercase flex flex-col items-center gap-1"><span>💡</span>Paham</button><button onclick="kirimRefleksi('${d.id_jurnal}',2)" class="bg-orange-50 text-orange-600 p-2 rounded-xl text-[8px] font-bold uppercase flex flex-col items-center gap-1"><span>😵‍💫</span>Bingung</button><button onclick="kirimRefleksi('${d.id_jurnal}',1)" class="bg-slate-100 text-slate-500 p-2 rounded-xl text-[8px] font-bold uppercase flex flex-col items-center gap-1"><span>😴</span>Ngantuk</button></div></div>`;
                    if(d.sudah_refleksi) area = `<div class="mt-3 text-[10px] text-emerald-600 font-bold bg-emerald-50 p-2 rounded-lg text-center">✅ Refleksi Terkirim</div>`;
                    c.innerHTML += `<div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm mb-4"><div class="flex justify-between items-start mb-2"><span class="bg-indigo-50 text-indigo-600 text-[10px] font-bold px-2 py-1 rounded">${jam}</span><span class="text-[10px] text-slate-400 font-bold uppercase">${d.nama_guru}</span></div><h4 class="font-bold text-slate-800 text-sm">${d.mapel}</h4><p class="text-xs text-slate-500 mt-1 line-clamp-2">${d.materi}</p>${area}</div>`;
                });
            });
        }
        window.kirimRefleksi = (id, rate) => {
            const msg = document.getElementById(`pesan-${id}`).value;
            fetch('api_siswa.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:'kirim_refleksi', nisn:NISN_USER, nama:userSiswa.nama, id_jurnal:id, rating:rate, pesan:msg}) }).then(r=>r.json()).then(res=>{ if(res.status=='success'){ Swal.fire('Sip','Terkirim','success'); loadTimelineKBM(); } });
        }

        function cekGPS() { if(navigator.geolocation) { navigator.geolocation.watchPosition((p)=>{ lokasiUser={lat:p.coords.latitude, lng:p.coords.longitude}; if(map){ if(!markerSiswa) markerSiswa=L.marker(lokasiUser).addTo(map); else markerSiswa.setLatLng(lokasiUser); const d=map.distance(lokasiUser,[configSekolah.lat, configSekolah.lng]); const r=configSekolah.radius; const b=document.getElementById('jarakBox'); if(configSekolah.mode=='strict'&&d>r){ b.innerText=`JAUH: ${Math.round(d)}m`; b.className="bg-rose-500 text-white text-[10px] font-bold py-2.5 px-4 rounded-xl shadow-lg"; } else { b.innerText="DALAM JANGKAUAN"; b.className="bg-emerald-500 text-white text-[10px] font-bold py-2.5 px-4 rounded-xl shadow-lg"; } } }); } }
        window.initMap = () => { if(map) return; map=L.map('mapMini',{zoomControl:false}).setView([configSekolah.lat, configSekolah.lng],17); L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map); L.circle([configSekolah.lat, configSekolah.lng], {radius:configSekolah.radius, color:configSekolah.mode=='strict'?'#ef4444':'#10b981'}).addTo(map); }
        window.validasiGPS = () => { if(!lokasiUser) { Swal.fire('GPS','Tunggu lokasi...','info'); return false; } const d=map.distance(lokasiUser,[configSekolah.lat,configSekolah.lng]); if(configSekolah.mode=='strict'&&d>configSekolah.radius) { Swal.fire('Kejauhan',`Jarak: ${Math.round(d)}m`,'error'); return false; } return true; }

        function pantauDataRealtime() {
            fetch(`api_siswa.php?action=cek_status&nisn=${NISN_USER}`).then(r=>r.json()).then(d => {
                const btnM = document.getElementById('btnMasuk'); const grp = document.getElementById('groupDiSekolah'); const hero = document.getElementById('hero-status');
                document.getElementById('info-jam-masuk').innerText = d.jam_masuk || "--:--";
                document.getElementById('info-jam-pulang').innerText = d.jam_pulang || "--:--";
                document.getElementById('sub-status').innerText = "Update: " + new Date().toLocaleTimeString();
                
                if(d.status == 'Belum Hadir') { hero.innerText = "Belum Hadir"; btnM.classList.remove('hidden'); grp.classList.add('hidden'); }
                else if(d.jam_pulang) { hero.innerText = "Selesai"; btnM.classList.add('hidden'); grp.classList.add('hidden'); }
                else { 
                    hero.innerText = d.status; btnM.classList.add('hidden'); grp.classList.remove('hidden');
                    if(d.status.includes('Izin Keluar')) { document.getElementById('btnIzinKeluar').classList.add('hidden'); document.getElementById('btnKembali').classList.remove('hidden'); }
                    else { document.getElementById('btnIzinKeluar').classList.remove('hidden'); document.getElementById('btnKembali').classList.add('hidden'); }
                }
            });
        }
        
        window.bukaCam = (t) => { tipeAbsenAktif=t; document.getElementById('modalKamera').classList.remove('hidden'); navigator.mediaDevices.getUserMedia({video:{facingMode:'user', aspectRatio:3/4}}).then(s=>{ streamKamera=s; document.getElementById('videoStream').srcObject=s; }); }
        window.tutupKamera = () => { document.getElementById('modalKamera').classList.add('hidden'); if(streamKamera) streamKamera.getTracks().forEach(t=>t.stop()); }
        window.jepretFoto = () => {
            const v=document.getElementById('videoStream'); const c=document.getElementById('canvasFoto'); c.width=480; c.height=640; c.getContext('2d').drawImage(v,0,0,480,640); const f=c.toDataURL('image/jpeg',0.7);
            const imgAnim=document.getElementById('imgAnimasi'); imgAnim.src=f; imgAnim.classList.remove('hidden'); imgAnim.classList.add('fly-to-server');
            setTimeout(() => { 
                if(tipeAbsenAktif=='BuktiSakit') { tempFotoBukti=f; document.getElementById('previewBukti').src=f; document.getElementById('previewBukti').classList.remove('hidden'); tutupKamera(); }
                else if(tipeAbsenAktif=='UpdateProfil') { 
                    fetch('api_siswa.php',{method:'POST',body:JSON.stringify({action:'update_profil',nisn:NISN_USER,foto:f})}).then(()=>{location.reload()});
                } else { kirimData(tipeAbsenAktif, "Hadir", f); }
            }, 800);
        }
        
        window.pilihFotoGaleri = (t) => { if(t=='profil') document.getElementById('fileInputProfil').click(); else document.getElementById('fileInputBukti').click(); }
        window.prosesUploadGaleri = (el, t) => { if(el.files[0]) { const r=new FileReader(); r.onload=(e)=>{ const i=new Image(); i.src=e
