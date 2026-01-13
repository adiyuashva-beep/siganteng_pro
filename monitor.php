<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Live - SiGanteng (PHP Version)</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        :root { 
            --bg-dark: #0b1120; 
            --card-bg: #151e32; 
            --neon-blue: #0ea5e9; 
            --neon-green: #10b981; 
            --neon-red: #ef4444; 
            --neon-gold: #f59e0b; 
        }
        body { margin: 0; font-family: 'Outfit', sans-serif; background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%); color: white; overflow: hidden; height: 100vh; display: flex; flex-direction: column; }
        body::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: linear-gradient(rgba(14, 165, 233, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(14, 165, 233, 0.03) 1px, transparent 1px); background-size: 40px 40px; z-index: -1; pointer-events: none; }
        
        .header { height: 12vh; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); display: flex; justify-content: space-between; align-items: center; padding: 0 30px; border-bottom: 1px solid rgba(14, 165, 233, 0.3); box-shadow: 0 10px 40px rgba(0,0,0,0.5); z-index: 20; position: relative; }
        .header::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 30%; height: 2px; background: var(--neon-blue); box-shadow: 0 0 15px var(--neon-blue); animation: scanningLine 5s linear infinite; }
        @keyframes scanningLine { 0% { left: 0; width: 0; opacity: 0; } 50% { width: 50%; opacity: 1; } 100% { left: 100%; width: 0; opacity: 0; } }

        .brand-left { display: flex; align-items: center; gap: 20px; }
        .logo-sekolah { height: 65px; width: auto; filter: drop-shadow(0 0 10px rgba(255,255,255,0.3)); }
        .text-sekolah h1 { margin: 0; font-size: 1.6em; font-weight: 900; color: white; line-height: 1; letter-spacing: 1px; }
        .badge-system { color: #94a3b8; font-weight: 600; letter-spacing: 3px; font-size: 0.8em; display: block; margin-top: 5px; }

        .header-right { display: flex; align-items: center; gap: 25px; }
        .logo-siganteng { height: 55px; width: auto; filter: drop-shadow(0 0 8px var(--neon-blue)); animation: floatLogo 4s ease-in-out infinite; }
        @keyframes floatLogo { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }

        .jam-digital { text-align: right; border-left: 2px solid rgba(255,255,255,0.1); padding-left: 25px; }
        .waktu { font-family: 'Share Tech Mono', monospace; font-size: 2.5em; font-weight: 700; line-height: 1; color: var(--neon-blue); text-shadow: 0 0 15px rgba(14, 165, 233, 0.6); }
        .tanggal { font-size: 0.85em; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; }
        
        .main-content { flex: 1; display: flex; padding: 20px; gap: 20px; height: 83vh; overflow: hidden; }
        .card-box { background: var(--card-bg); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 20px 50px rgba(0,0,0,0.3); display: flex; flex-direction: column; overflow: hidden; position: relative; }
        .card-box::before { content: ''; position: absolute; top: 0; left: 0; width: 15px; height: 15px; border-top: 2px solid var(--neon-blue); border-left: 2px solid var(--neon-blue); border-radius: 4px 0 0 0; }
        .card-box::after { content: ''; position: absolute; bottom: 0; right: 0; width: 15px; height: 15px; border-bottom: 2px solid var(--neon-blue); border-right: 2px solid var(--neon-blue); border-radius: 0 0 4px 0; }

        .card-header { padding: 15px; font-weight: 800; font-size: 0.9em; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; text-transform: uppercase; letter-spacing: 1px; background: rgba(0,0,0,0.2); }
        
        /* KOLOM KIRI */
        .col-gasik { flex: 1; border-top: 2px solid var(--neon-green); }
        .gasik-header { color: var(--neon-green); }
        .list-container { flex: 1; overflow-y: auto; padding: 10px; }
        .rank-item { display: flex; align-items: center; padding: 10px; margin-bottom: 8px; background: linear-gradient(90deg, rgba(255,255,255,0.03), transparent); border-radius: 8px; border-left: 2px solid transparent; transition: 0.3s; }
        .rank-item:hover { background: rgba(255,255,255,0.08); border-left-color: var(--neon-green); }
        .rank-num { width: 28px; height: 28px; background: #334155; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; margin-right: 12px; font-size: 0.9em; flex-shrink: 0; font-family: 'Share Tech Mono', monospace; }
        .rank-1 .rank-num { background: var(--neon-gold); color: black; box-shadow: 0 0 15px var(--neon-gold); }
        .rank-2 .rank-num { background: #cbd5e1; color: black; }
        .rank-3 .rank-num { background: #b45309; color: white; }
        .avatar-img { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.1); margin-right: 12px; flex-shrink: 0; background: #000; }
        .rank-nama { font-weight: 600; flex: 1; font-size: 0.9em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #e2e8f0; }
        .rank-jam { color: var(--neon-green); font-weight: 700; font-family: 'Share Tech Mono', monospace; font-size: 1.1em; text-shadow: 0 0 5px rgba(16, 185, 129, 0.5); }

        /* KOLOM TENGAH */
        .col-center { flex: 1.3; display: flex; flex-direction: column; gap: 20px; }
        .stats-wrapper { flex: 1.5; background: var(--card-bg); border-radius: 16px; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.05); position: relative; }
        .reactor-ring { position: absolute; width: 210px; height: 210px; border-radius: 50%; border: 2px dashed rgba(255, 255, 255, 0.1); animation: spin 20s linear infinite; z-index: 0; }
        .reactor-ring::before { content: ''; position: absolute; top: -2px; left: 50%; width: 10px; height: 10px; background: var(--neon-blue); border-radius: 50%; box-shadow: 0 0 15px var(--neon-blue); transform: translateX(-50%); }
        .donut-chart { position: relative; width: 180px; height: 180px; border-radius: 50%; background: conic-gradient(var(--c) var(--p), #1e293b 0deg); display: flex; align-items: center; justify-content: center; margin-bottom: 20px; transition: 1s; box-shadow: 0 0 30px rgba(0,0,0,0.5); z-index: 1; }
        .donut-inner { width: 88%; height: 88%; background: var(--card-bg); border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .logo-center-donut { position: absolute; width: 60%; height: 60%; opacity: 0.3; animation: breathe 3s ease-in-out infinite; z-index: 0; filter: grayscale(100%) drop-shadow(0 0 5px var(--neon-blue)); }
        .big-percent { font-size: 2.8em; font-weight: 900; color: white; line-height: 1; position: relative; z-index: 2; text-shadow: 0 0 20px black; font-family: 'Share Tech Mono', monospace; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes breathe { 0%, 100% { transform: scale(1); opacity: 0.3; } 50% { transform: scale(1.1); opacity: 0.5; } }
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; width: 100%; gap: 10px; text-align: center; margin-top: 10px; }
        .stat-item { background: rgba(255,255,255,0.03); padding: 10px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); }
        .stat-item h4 { margin: 0 0 5px 0; font-size: 0.7em; color: #94a3b8; font-weight: 800; letter-spacing: 1px; }
        .stat-item p { margin: 0; font-size: 1.4em; font-weight: 700; font-family: 'Share Tech Mono', monospace; }
        .c-hadir { color: var(--neon-green); text-shadow: 0 0 10px rgba(16, 185, 129, 0.4); } 
        .c-izin { color: var(--neon-gold); } .c-alpha { color: var(--neon-red); }

        .box-belum { flex: 1; border-top: 2px solid #64748b; }
        .belum-header { color: #94a3b8; }
        .scroller-content { flex: 1; overflow: hidden; padding: 10px; position: relative; }
        .alpha-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.02); margin-bottom: 5px; border-left: 2px solid #64748b; }
        .alpha-nama { font-weight: 600; font-size: 0.9em; color: #cbd5e1; }

        /* KOLOM KANAN */
        .col-right-split { flex: 1.5; display: flex; flex-direction: column; gap: 20px; }
        .box-telat { flex: 1; border: 1px solid rgba(239, 68, 68, 0.3); border-top: 2px solid var(--neon-red); box-shadow: 0 0 20px rgba(239, 68, 68, 0.1) inset; }
        .telat-header { color: var(--neon-red); }
        .telat-row { display: flex; align-items: center; padding: 8px; margin-bottom: 5px; background: rgba(239, 68, 68, 0.05); border-left: 3px solid var(--neon-red); border-radius: 6px; }
        .telat-img { width: 35px; height: 35px; border-radius: 50%; border: 1px solid var(--neon-red); margin-right: 10px; filter: grayscale(1) contrast(1.2); }
        .telat-time { margin-left: auto; font-family: 'Share Tech Mono', monospace; color: var(--neon-red); font-weight: 700; }

        .box-live { flex: 2.2; border-top: 2px solid var(--neon-blue); }
        .live-header-big { padding: 15px; font-size: 1.1em; font-weight: 800; color: var(--neon-blue); border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(14, 165, 233, 0.05); display: flex; align-items: center; gap: 10px; }
        .live-card-big { display: flex; align-items: center; padding: 12px; margin-bottom: 8px; background: linear-gradient(90deg, rgba(255,255,255,0.05), transparent); border-radius: 12px; border-left: 4px solid var(--neon-green); transition: transform 0.3s; }
        .live-card-big.is-telat { border-left-color: var(--neon-red); background: rgba(239, 68, 68, 0.05); }
        .live-img { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; border: 1px solid rgba(255,255,255,0.2); margin-right: 15px; flex-shrink: 0; background: black; }
        .live-name { font-weight: 700; font-size: 1.1em; color: white; margin-bottom: 2px; text-shadow: 0 2px 4px black; }
        .live-class { font-size: 0.85em; color: var(--neon-blue); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .live-time { font-family: 'Share Tech Mono', monospace; font-size: 1.4em; font-weight: 800; color: var(--neon-green); text-shadow: 0 0 10px rgba(16, 185, 129, 0.5); }
        .live-card-big.is-telat .live-time { color: var(--neon-red); text-shadow: 0 0 10px rgba(239, 68, 68, 0.5); }

        .marquee-vertical { animation: scrollUp linear infinite; }
        @keyframes scrollUp { 0% { transform: translateY(0); } 100% { transform: translateY(-100%); } }

        .footer-marquee { height: 5vh; background: #020617; display: flex; align-items: center; border-top: 1px solid var(--neon-blue); z-index: 10; box-shadow: 0 -10px 30px rgba(0,0,0,0.5); }
        marquee { font-size: 1.2em; font-weight: 500; color: #f1f5f9; letter-spacing: 1px; font-family: 'Share Tech Mono', monospace; text-shadow: none; }
        
        .refresh-btn { position: fixed; top: 10px; left: 10px; z-index: 100; background: rgba(0,0,0,0.5); color: #fff; border: 1px solid #333; padding: 5px 10px; font-size: 10px; cursor: pointer; border-radius: 4px; opacity: 0.2; transition: opacity 0.3s; }
        .refresh-btn:hover { opacity: 1; }
    </style>
</head>
<body>

    <button onclick="forceRefreshMaster()" class="refresh-btn">🔄 Reset Cache v3</button>

    <div class="header">
        <div class="brand-left">
            <img src="logo_sekolah.png" class="logo-sekolah" alt="SMAN 1" onerror="this.style.display='none'">
            <div class="text-sekolah">
                <h1>SMAN 1 PEJAGOAN</h1>
                <span class="badge-system">MONITORING KEDISIPLINAN</span>
            </div>
        </div>
        
        <div class="header-right">
            <img src="logo.png" class="logo-siganteng" alt="SiGanteng" onerror="this.src='https://ui-avatars.com/api/?name=SG&background=transparent&color=fff&size=128'">
            <div class="jam-digital">
                <div class="waktu" id="jam-real">00:00</div>
                <div class="tanggal" id="tgl-real">...</div>
            </div>
        </div>
    </div>

    <div class="main-content">
        
        <div class="card-box col-gasik">
            <div class="card-header gasik-header"><i class='bx bxs-trophy'></i> TOP 10 GASIK</div>
            <div class="list-container" id="gasik-list">
                <p style="text-align:center; padding:20px; opacity:0.5;">Menunggu data...</p>
            </div>
        </div>

        <div class="col-center">
            <div class="stats-wrapper">
                <div class="reactor-ring"></div>
                <div class="donut-chart" id="chart-absen" style="--p: 0deg; --c: var(--neon-red);">
                    <div class="donut-inner">
                        <img src="logo.png" class="logo-center-donut" alt="Logo">
                        <span class="big-percent" id="persen-text">0%</span>
                        <small style="color:#94a3b8; font-size:0.7em; z-index:2; font-weight:700; letter-spacing:1px;">KEHADIRAN</small>
                    </div>
                </div>
                
                <div style="margin-bottom: 10px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; width: 100%;">
                    <span style="font-size: 0.8em; color: #94a3b8; font-weight: 600; letter-spacing:1px;">TOTAL POPULASI SISWA</span>
                    <div style="font-size: 1.8em; font-weight: 800; color: white; font-family: 'Share Tech Mono', monospace;" id="total-populasi">...</div>
                </div>
                
                <div class="stat-grid">
                    <div class="stat-item"><h4 class="c-hadir">HADIR</h4><p id="total-hadir">0</p></div>
                    <div class="stat-item"><h4 class="c-izin">IZIN</h4><p id="total-izin">0</p></div>
                    <div class="stat-item"><h4 class="c-alpha">BELUM</h4><p id="total-alpha">0</p></div>
                </div>
                
                <div id="data-source-indicator" style="position:absolute; bottom:5px; right:10px; font-size:9px; color:rgba(255,255,255,0.2);">Waiting...</div>
            </div>

            <div class="box-belum">
                <div class="belum-header">
                    <span><i class='bx bx-user-x'></i> BELUM HADIR</span>
                    <span style="background:rgba(255,255,255,0.1); padding:2px 8px; border-radius:4px; font-family:'Share Tech Mono';" id="badge-belum">0</span>
                </div>
                <div class="scroller-content">
                    <div class="marquee-vertical" id="list-belum"></div>
                    <div id="msg-too-many" style="display:none; text-align:center; padding:20px; color:#64748b; font-size:0.8em;">
                        Data masih terlalu banyak.<br>List ditampilkan saat sisa < 50.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-right-split">
            <div class="box-telat">
                <div class="telat-header">
                    <span><i class='bx bxs-alarm-exclamation'></i> TERLAMBAT</span>
                    <span style="background:rgba(255,0,0,0.2); padding:2px 8px; border-radius:4px; font-family:'Share Tech Mono';" id="badge-telat">0</span>
                </div>
                <div class="scroller-content">
                    <div class="marquee-vertical" id="list-telat"></div>
                </div>
            </div>

            <div class="box-live">
                <div class="live-header-big">
                    <span><i class='bx bxs-camera-movie'></i> LIVE PRESENSI</span>
                    <span style="font-size:0.7em; font-weight:400; color:#94a3b8; margin-left:auto;"><i class='bx bxs-circle' style="color:red; animation: breathe 1s infinite;"></i> REC</span>
                </div>
                <div class="scroller-content">
                    <div class="marquee-vertical" id="live-feed-list"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-marquee">
        <marquee scrollamount="8">
            🚀 <strong>SELAMAT DATANG DI PUSAT MONITORING KEDISIPLINAN SMAN 1 PEJAGOAN</strong> &nbsp;&nbsp; ••• &nbsp;&nbsp; 
            <span style="color: var(--neon-blue);">SiGANTENG</span> ADALAH: <strong>SISTEM INFORMASI GATEWAY ADMINISTRASI NOTIFIKASI TERINTEGRASI ELEKTRONIK NETWORK GLOBAL</strong> &nbsp;&nbsp; ••• &nbsp;&nbsp;
            DATA ABSENSI DIPERBAHARUI SECARA <span style="color: var(--neon-green);">REALTIME (LIVE)</span> &nbsp;&nbsp; ••• &nbsp;&nbsp; 
            MARI WUJUDKAN GENERASI YANG DISIPLIN, BERKARAKTER, DAN BERPRESTASI 🔥
        </marquee>
    </div>

    <script>
        const JAM_BATAS_TERLAMBAT = "07:00:00"; 
        const DEFAULT_IMG = "https://cdn-icons-png.flaticon.com/512/847/847969.png";

        let allSiswa = [];        
        let dataAbsenHariIni = []; 
        let validStudentNISNs = new Set();

        // 1. LOAD MASTER SISWA (CACHE SYSTEM)
        async function loadMasterSiswa() {
            const cacheKey = 'cache_master_siswa_v3_php';
            const cachedSiswa = localStorage.getItem(cacheKey);
            const indicator = document.getElementById('data-source-indicator');
            
            if(cachedSiswa) {
                const parsed = JSON.parse(cachedSiswa);
                const now = new Date().getTime();
                if (now - parsed.timestamp < 24 * 60 * 60 * 1000) {
                    console.log("⚡ [HEMAT] Local Cache used");
                    indicator.innerText = "Source: Local Cache";
                    allSiswa = parsed.data;
                    allSiswa.forEach(s => validStudentNISNs.add(s.username));
                    document.getElementById('total-populasi').innerText = allSiswa.length;
                    startMonitorAbsen();
                    return;
                }
            }

            console.log("⚠️ Fetching Master Data from PHP...");
            indicator.innerText = "Source: Server Fetching...";
            indicator.style.color = "orange";

            try {
                const res = await fetch('api_monitor.php?action=get_master_siswa').then(r=>r.json());
                allSiswa = res;
                validStudentNISNs.clear();
                allSiswa.forEach(s => validStudentNISNs.add(s.username));
                
                localStorage.setItem(cacheKey, JSON.stringify({
                    timestamp: new Date().getTime(),
                    data: allSiswa
                }));

                indicator.innerText = "Source: Server (Cached)";
                indicator.style.color = "#10b981";
                document.getElementById('total-populasi').innerText = allSiswa.length;
                startMonitorAbsen();
            } catch (error) { 
                console.error(error); 
                indicator.innerText = "Error Fetching Data";
            }
        }

        window.forceRefreshMaster = () => {
            if(confirm("Download ulang data siswa terbaru?")) {
                localStorage.removeItem('cache_master_siswa_v3_php');
                location.reload();
            }
        }

        // 2. POLLING REALTIME (Pengganti WebSocket Firebase)
        function startMonitorAbsen() {
            // Panggil pertama kali
            fetchLiveData();
            // Lalu panggil setiap 3 detik
            setInterval(fetchLiveData, 3000);
        }

        function fetchLiveData() {
            fetch('api_monitor.php?action=get_live_absen')
                .then(r => r.json())
                .then(data => {
                    dataAbsenHariIni = data;
                    updateTampilanLayar();
                })
                .catch(e => console.error("Polling Error:", e));
        }

        function updateTampilanLayar() {
            const listHadir = dataAbsenHariIni.filter(d => d.status.includes("Masuk") || d.status.includes("Hadir") || d.status.includes("Pulang"));
            const listIzin = dataAbsenHariIni.filter(d => d.status.includes("Sakit") || d.status.includes("Izin"));
            const nisnHadir = dataAbsenHariIni.map(d => d.nisn); 
            
            const totalPopulasi = allSiswa.length > 0 ? allSiswa.length : 0; 
            const countIzin = listIzin.length;
            const countHadir = listHadir.length;
            const totalMasuk = countHadir + countIzin;
            
            const listBelum = allSiswa.filter(s => !nisnHadir.includes(s.username));

            document.getElementById('total-populasi').innerText = totalPopulasi;
            document.getElementById('total-hadir').innerText = countHadir;
            document.getElementById('total-izin').innerText = countIzin;
            document.getElementById('total-alpha').innerText = listBelum.length;

            let rawPercent = totalPopulasi > 0 ? (totalMasuk / totalPopulasi) * 100 : 0;
            let derajat = rawPercent * 3.6;
            if (rawPercent > 100) rawPercent = 100;

            let warnaDonat = rawPercent > 75 ? '#10b981' : (rawPercent > 40 ? '#f59e0b' : '#ef4444'); 
            
            const elText = document.getElementById('persen-text');
            if(elText) {
                elText.innerText = rawPercent.toFixed(1) + "%";
                elText.style.color = warnaDonat;
                elText.style.textShadow = `0 0 10px ${warnaDonat}`;
            }

            const elChart = document.getElementById('chart-absen');
            if(elChart) { elChart.style.background = `conic-gradient(${warnaDonat} ${derajat}deg, #0f172a 0deg)`; }

            // RENDER LISTS
            const containerBelum = document.getElementById('list-belum');
            const msgTooMany = document.getElementById('msg-too-many');
            document.getElementById('badge-belum').innerText = listBelum.length;

            if(listBelum.length > 50) {
                containerBelum.innerHTML = ''; 
                containerBelum.style.display = 'none';
                msgTooMany.style.display = 'block';
                msgTooMany.innerHTML = `Masih ada <strong>${listBelum.length}</strong> siswa belum hadir.<br>List disembunyikan agar performa lancar.`;
            } else {
                containerBelum.style.display = 'block';
                msgTooMany.style.display = 'none';
                renderScrollList(listBelum, 'list-belum', 'belum');
            }

            let sortedByTime = [...listHadir].sort((a,b) => a.jam.localeCompare(b.jam));
            renderGasik(sortedByTime.slice(0, 10));

            const listTelat = listHadir.filter(d => d.jam > JAM_BATAS_TERLAMBAT);
            listTelat.sort((a,b) => b.jam.localeCompare(a.jam));
            renderScrollList(listTelat, 'list-telat', 'telat');
            document.getElementById('badge-telat').innerText = listTelat.length;

            const listLive = [...listHadir, ...listIzin].sort((a,b) => b.jam.localeCompare(a.jam));
            renderLiveFeed(listLive);
        }
        
        function renderGasik(data) {
            const el = document.getElementById('gasik-list');
            el.innerHTML = '';
            if(data.length === 0) { el.innerHTML = '<p style="text-align:center; padding:20px; opacity:0.5;">Belum ada data.</p>'; return; }
            data.forEach((d, i) => {
                let cls = i===0 ? 'rank-1' : (i===1 ? 'rank-2' : (i===2 ? 'rank-3' : ''));
                el.insertAdjacentHTML('beforeend', `<div class="rank-item ${cls}"><div class="rank-num">${i+1}</div><img src="${d.foto}" class="avatar-img" onerror="this.src='${DEFAULT_IMG}'"><div class="rank-nama">${d.nama}<br><span style="font-size:0.75em; opacity:0.7;">${d.kelas}</span></div><div class="rank-jam">${d.jam.substring(0,5)}</div></div>`);
            });
        }

        function renderScrollList(data, id, type) {
            const container = document.getElementById(id);
            container.innerHTML = ''; 
            if(data.length === 0) {
                let msg = type === 'telat' ? 'Nihil (Tertib Semua)' : 'Semua Hadir!';
                container.innerHTML = `<div style="text-align:center; padding:20px; color:#64748b; font-style:italic;">${msg}</div>`;
                container.style.animation = 'none'; return;
            }
            let durasi = data.length * 2; if(durasi < 10) durasi = 10; 
            container.style.animation = `scrollUp ${durasi}s linear infinite`;
            data.forEach(d => {
                if(type === 'telat') {
                    container.insertAdjacentHTML('beforeend', `<div class="telat-row"><img src="${d.foto}" class="telat-img" onerror="this.src='${DEFAULT_IMG}'"><div><div class="alpha-nama">${d.nama}</div><div class="alpha-kelas">${d.kelas}</div></div><div class="telat-time">${d.jam.substring(0,5)}</div></div>`);
                } else {
                    container.insertAdjacentHTML('beforeend', `<div class="alpha-row"><div><div class="alpha-nama">${d.name}</div><div class="alpha-kelas" style="font-size:0.7em; color:#94a3b8;">${d.kelas}</div></div></div>`);
                }
            });
            if(data.length > 5) container.innerHTML += container.innerHTML;
        }

        function renderLiveFeed(data) {
            const container = document.getElementById('live-feed-list');
            container.innerHTML = '';
            if(data.length === 0) { container.innerHTML = '<div style="text-align:center; padding:20px; opacity:0.5;">Menunggu presensi...</div>'; return; }
            let durasi = data.length * 3; if(durasi < 15) durasi = 15;
            container.style.animation = `scrollUp ${durasi}s linear infinite`;
            data.forEach(d => {
                const isTelat = d.jam > JAM_BATAS_TERLAMBAT && !d.status.includes("Izin") && !d.status.includes("Sakit");
                const statusClass = isTelat ? 'is-telat' : '';
                container.insertAdjacentHTML('beforeend', `<div class="live-card-big ${statusClass}"><img src="${d.foto}" class="live-img" onerror="this.src='${DEFAULT_IMG}'"><div class="live-info"><div class="live-name">${d.nama}</div><div class="live-class">${d.kelas}</div></div><div class="live-time">${d.jam.substring(0,5)}</div></div>`);
            });
            if(data.length > 6) container.innerHTML += container.innerHTML;
        }

        setInterval(() => { const now = new Date(); document.getElementById('jam-real').innerText = now.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}); document.getElementById('tgl-real').innerText = now.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long'}); }, 1000);
        
        document.addEventListener("DOMContentLoaded", loadMasterSiswa);
    </script>
</body>
</html>