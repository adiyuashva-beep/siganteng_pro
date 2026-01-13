<?php
session_start();
include 'koneksi.php';

// 1. Cek Login Siswa
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != 'siswa') {
    header("location:index.php"); exit();
}

$nisn = $_SESSION['username'];

// 2. Ambil Data Siswa Terbaru
$q = mysqli_query($koneksi, "SELECT * FROM tb_siswa WHERE nisn='$nisn'");
$d = mysqli_fetch_assoc($q);

// 3. Handle Foto Profil
$foto_url = "https://ui-avatars.com/api/?name=".urlencode($d['nama_siswa'])."&background=random&color=fff&size=200";
if (!empty($d['foto_profil'])) {
    $foto_url = "uploads/profil/" . $d['foto_profil'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kartu Pelajar Digital</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f1f5f9; }
        /* Pattern Background Kartu */
        .card-pattern {
            background-color: #4f46e5;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2v-4h4v-2H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .shiny-effect {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-6 pb-20">

    <div class="fixed top-0 left-0 w-full p-4 flex items-center z-50">
        <button onclick="history.back()" class="bg-white/80 backdrop-blur shadow-sm p-3 rounded-full text-slate-700 hover:bg-white transition">
            <i data-lucide="arrow-left" class="w-6 h-6"></i>
        </button>
    </div>

    <div class="text-center mb-6 mt-10">
        <h1 class="text-2xl font-black text-slate-800">Kartu Pelajar</h1>
        <p class="text-slate-500 text-sm">Identitas Resmi Siswa</p>
    </div>

    <div id="areaKartu" class="w-[320px] h-[520px] bg-white rounded-[1.5rem] overflow-hidden shadow-2xl relative flex flex-col shrink-0 transform transition hover:scale-[1.02] duration-300">
        
        <div class="h-40 card-pattern relative overflow-hidden">
            <div class="absolute inset-0 shiny-effect"></div>
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-black/20 to-transparent"></div>

            <div class="relative z-10 p-6 flex flex-col items-center justify-center h-full text-center">
                <div class="bg-white p-2 rounded-full shadow-lg mb-2">
                    <img src="logo_sekolah.png" onerror="this.src='https://via.placeholder.com/50?text=LOGO'" class="w-8 h-8 object-contain">
                </div>
                <h2 class="text-white font-black text-sm tracking-widest uppercase text-shadow">KARTU TANDA SISWA</h2>
                <p class="text-indigo-100 text-[10px] font-bold">SMAN 1 PEJAGOAN</p>
            </div>
        </div>

        <div class="flex-1 flex flex-col items-center -mt-10 relative z-20 px-4 bg-white rounded-t-[2rem]">
            
            <div class="p-1.5 bg-white rounded-full shadow-xl -mt-10 mb-3">
                <img id="fotoSiswa" src="<?php echo $foto_url; ?>" class="w-28 h-28 rounded-full object-cover border-4 border-indigo-500 bg-slate-200" crossorigin="anonymous">
            </div>
            
            <h2 class="font-black text-slate-800 text-xl leading-tight text-center mb-1 px-2">
                <?php echo $d['nama_siswa']; ?>
            </h2>
            
            <div class="flex items-center gap-2 mb-4">
                <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold border border-indigo-100">
                    <?php echo $d['kelas']; ?>
                </span>
                <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-mono text-xs font-bold border border-slate-200">
                    <?php echo $d['nisn']; ?>
                </span>
            </div>

            <div class="bg-white p-3 rounded-2xl border-2 border-dashed border-slate-200 shadow-inner mb-2">
                <div id="qrcode"></div>
            </div>
            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-6">Scan QR untuk Presensi</p>

            <div class="w-12 h-1 bg-slate-200 rounded-full mb-4"></div>
        </div>
        
        <div class="h-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 w-full"></div>
    </div>

    <div class="fixed bottom-0 left-0 w-full p-6 bg-white/80 backdrop-blur-md border-t border-slate-200 z-40">
        <button onclick="downloadKartu()" id="btnDownload" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-4 rounded-2xl font-bold text-lg shadow-xl shadow-slate-900/20 active:scale-95 transition flex items-center justify-center gap-2">
            <i data-lucide="download" class="w-5 h-5"></i> Simpan ke Galeri
        </button>
    </div>

    <script>
        lucide.createIcons();

        // 1. Generate QR Code Otomatis
        window.onload = () => {
            const nisn = "<?php echo $nisn; ?>";
            new QRCode(document.getElementById("qrcode"), {
                text: nisn,
                width: 100,
                height: 100,
                colorDark : "#1e293b",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        };

        // 2. Fungsi Download Gambar
        async function downloadKartu() {
            const btn = document.getElementById('btnDownload');
            const originalText = btn.innerHTML;
            
            // Ubah tombol jadi loading
            btn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Memproses...';
            lucide.createIcons();
            btn.disabled = true;

            try {
                const element = document.getElementById('areaKartu');
                
                // Konversi HTML ke Canvas (Gambar)
                const canvas = await html2canvas(element, {
                    scale: 3, // Resolusi Tinggi (biar HD saat dicetak)
                    useCORS: true, // Wajib biar foto profil terload
                    backgroundColor: null
                });

                // Buat Link Download
                const link = document.createElement('a');
                const namaFile = "ID_Card_<?php echo str_replace(' ', '_', $d['nama_siswa']); ?>.png";
                
                link.download = namaFile;
                link.href = canvas.toDataURL("image/png");
                link.click();

                // Alert Sukses (Pake alert bawaan aja biar ringan filenya)
                alert('Berhasil! Kartu disimpan.');

            } catch (err) {
                console.error(err);
                alert('Gagal menyimpan gambar. Coba lagi atau screenshot manual.');
            } finally {
                // Kembalikan tombol
                btn.innerHTML = originalText;
                btn.disabled = false;
                lucide.createIcons();
            }
        }
    </script>
</body>
</html>