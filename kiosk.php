<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Kiosk - SiGanteng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background: #0f172a; color: white; overflow: hidden; }
        .scanner-frame {
            position: relative; width: 100%; max-width: 600px; aspect-ratio: 4/3;
            background: #000; border-radius: 20px; overflow: hidden;
            border: 4px solid #3b82f6; box-shadow: 0 0 30px rgba(59, 130, 246, 0.3);
        }
        #reader video { object-fit: cover; width: 100% !important; height: 100% !important; }
        .scan-line {
            position: absolute; width: 100%; height: 4px; background: #10b981;
            top: 0; left: 0; box-shadow: 0 0 10px #10b981; animation: scan 2s infinite linear; z-index: 10;
        }
        @keyframes scan { 0% {top: 0;} 50% {top: 100%;} 100% {top: 0;} }
    </style>
</head>
<body class="h-screen flex flex-col items-center justify-center p-4">

    <div class="text-center mb-6 relative w-full max-w-2xl">
        <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400 tracking-tight">SMART KIOSK</h1>
        <p class="text-slate-400 text-sm tracking-widest uppercase mt-1">SMAN 1 PEJAGOAN</p>
        
        <button onclick="gantiKamera()" class="absolute right-0 top-2 bg-slate-800 hover:bg-slate-700 text-white p-2 rounded-lg border border-slate-600 shadow-lg flex items-center gap-2 text-xs font-bold transition z-50">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Switch Cam
        </button>
    </div>

    <div class="scanner-frame mb-6 relative">
        <div class="scan-line"></div>
        <div id="reader" class="w-full h-full bg-black"></div>
        
        <div id="camLoading" class="absolute inset-0 flex items-center justify-center bg-black z-20">
            <div class="text-center">
                <i data-lucide="camera" class="w-10 h-10 text-blue-500 animate-bounce mx-auto mb-2"></i>
                <p class="text-xs text-blue-400">Menghubungkan Mata Jarvis...</p>
            </div>
        </div>
    </div>

    <div id="statusBox" class="bg-slate-800 border border-slate-700 p-4 rounded-2xl w-full max-w-md text-center shadow-lg">
        <p class="text-slate-400 text-sm animate-pulse">Menunggu Kamera atau Alat Scan...</p>
    </div>

    <script>
        // INIT
        let html5QrcodeScanner;
        let isProcessing = false;
        let cameras = [];        
        let currentCamIndex = 0; 
        
        // SCANNER FISIK (KEYBOARD BUFFER)
        let barcodeBuffer = "";
        let barcodeTimer;

        // 1. SUARA JARVIS
        function bicara(teks) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(teks);
                utterance.lang = 'id-ID'; utterance.rate = 1; 
                window.speechSynthesis.cancel(); 
                window.speechSynthesis.speak(utterance);
            }
        }

        // 2. LISTENER SCANNER FISIK
        document.addEventListener('keydown', (e) => {
            if(isProcessing) return; 
            if(e.key === 'Enter') {
                if(barcodeBuffer.length > 3) onScanSuccess(barcodeBuffer);
                barcodeBuffer = ""; 
            } else {
                if(e.key.length === 1) barcodeBuffer += e.key;
                clearTimeout(barcodeTimer);
                barcodeTimer = setTimeout(() => barcodeBuffer = "", 200); 
            }
        });

        // 3. KAMERA SWITCH
        window.gantiKamera = function() {
            if (cameras.length < 2) {
                Swal.fire('Info', 'Hanya ada 1 kamera terdeteksi.', 'info');
                return;
            }
            currentCamIndex = (currentCamIndex + 1) % cameras.length;
            const camId = cameras[currentCamIndex].id;

            html5QrcodeScanner.stop().then(() => {
                startScanner(camId);
                Swal.fire({ toast: true, position: 'top', icon: 'success', title: `Kamera Diubah`, timer: 1000, showConfirmButton: false });
            }).catch(err => console.error(err));
        }

        function startScanner(cameraId) {
            document.getElementById('camLoading').style.display = 'flex';
            html5QrcodeScanner.start(
                cameraId, 
                { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 }, 
                onScanSuccess
            ).then(() => {
                document.getElementById('camLoading').style.display = 'none';
                document.getElementById('statusBox').innerHTML = `<p class="text-green-400 font-bold text-sm">SIAP MEMINDAI (Cam ${currentCamIndex + 1}/${cameras.length})</p>`;
            }).catch(err => {
                console.error(err);
                document.getElementById('statusBox').innerHTML = `<p class="text-yellow-400 font-bold text-sm">Error Kamera. Coba Refresh.</p>`;
            });
        }

        // 4. LOGIKA ABSEN KE PHP
        function onScanSuccess(nisn) {
            if (isProcessing) return; 
            isProcessing = true;
            try { html5QrcodeScanner.pause(); } catch(e){} 
            prosesAbsensi(nisn);
        }

        async function prosesAbsensi(nisn) {
            const statusBox = document.getElementById('statusBox');
            statusBox.innerHTML = `<span class="text-blue-400 font-bold animate-pulse">Memproses: ${nisn}...</span>`;

            try {
                const req = await fetch('api_kiosk.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'scan_kiosk', nisn: nisn })
                });
                const res = await req.json();

                if (res.status == 'success') {
                    statusBox.innerHTML = `<span class="text-green-400 font-bold">✅ ${res.tipe} Berhasil</span>`;
                    bicara(res.pesan);
                    Swal.fire({
                        icon: 'success', title: res.nama,
                        text: res.pesan, timer: 2000, showConfirmButton: false, background: '#1e293b', color: '#fff'
                    }).then(() => resetScanner());
                } else {
                    throw new Error(res.pesan);
                }

            } catch (error) {
                const msg = error.message || "Gagal Koneksi";
                bicara("Maaf, Gagal memproses");
                statusBox.innerHTML = `<span class="text-red-400 font-bold">❌ ${msg}</span>`;
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg, timer: 2000, showConfirmButton: false, background: '#1e293b', color: '#fff' }).then(() => resetScanner());
            }
        }

        function resetScanner() {
            isProcessing = false;
            try { html5QrcodeScanner.resume(); } catch(e) {}
            document.getElementById('statusBox').innerHTML = `<p class="text-slate-400 text-sm animate-pulse">Siap Scan Barcode...</p>`;
        }

        // INIT SAAT LOAD
        window.addEventListener('load', function() {
            lucide.createIcons();
            html5QrcodeScanner = new Html5Qrcode("reader");

            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    cameras = devices;
                    currentCamIndex = 0; 
                    startScanner(cameras[currentCamIndex].id);
                } else {
                    document.getElementById('camLoading').style.display = 'none';
                    document.getElementById('statusBox').innerHTML = `<p class="text-yellow-400">Kamera tidak ada. Gunakan Alat Scan.</p>`;
                }
            }).catch(err => {
                document.getElementById('camLoading').style.display = 'none';
                document.getElementById('statusBox').innerHTML = `<p class="text-yellow-400">Izin Kamera Ditolak / Error.</p>`;
            });
        });
    </script>
</body>
</html>