<?php
session_start();
include 'koneksi.php';

// Cek Login Ortu
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != 'ortu') {
    header("location:index.php"); exit();
}

$nisn_anak = $_SESSION['username']; // Login Ortu simpan NISN anak
$nama_anak = $_SESSION['nama'];     // Nama anak (misal: "Wali Murid Budi")

// Ambil Data Anak
$q_siswa = mysqli_query($koneksi, "SELECT * FROM tb_siswa WHERE nisn='$nisn_anak'");
$d_siswa = mysqli_fetch_assoc($q_siswa);
$foto_anak = $d_siswa['foto_profil'] ? 'uploads/profil/'.$d_siswa['foto_profil'] : 'https://ui-avatars.com/api/?name='.urlencode($d_siswa['nama_siswa']);

// Ambil Absensi Hari Ini
$tgl = date('Y-m-d');
$q_absen = mysqli_query($koneksi, "SELECT * FROM tb_absensi WHERE nisn='$nisn_anak' AND tanggal='$tgl'");
$d_absen = mysqli_fetch_assoc($q_absen);

$jam_masuk = $d_absen['jam_masuk'] ? substr($d_absen['jam_masuk'],0,5) : "--:--";
$jam_pulang = $d_absen['jam_pulang'] && $d_absen['jam_pulang']!='00:00:00' ? substr($d_absen['jam_pulang'],0,5) : "--:--";
$status = $d_absen['status_kehadiran'] ?? "Belum Ada Kabar";

// Ambil Timeline Pelajaran Hari Ini (Berdasarkan Kelas Anak)
$kelas_anak = $d_siswa['kelas'];
$q_kbm = mysqli_query($koneksi, "SELECT * FROM tb_jurnal WHERE kelas='$kelas_anak' AND tanggal='$tgl' ORDER BY waktu DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Monitoring Ortu - SiGanteng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Outfit',sans-serif;background:#f0fdf4;}</style>
</head>
<body class="pb-20">

    <div class="bg-emerald-600 p-6 rounded-b-[2.5rem] shadow-xl text-white relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="flex justify-between items-start relative z-10">
            <div>
                <p class="text-emerald-100 text-xs font-bold uppercase tracking-widest mb-1">Panel Orang Tua</p>
                <h1 class="text-2xl font-black">SiGanteng <span class="text-yellow-300">Family</span></h1>
            </div>
            <a href="logout.php" class="bg-white/20 p-2 rounded-full hover:bg-red-500 transition"><i data-lucide="log-out" class="w-5 h-5"></i></a>
        </div>

        <div class="mt-6 flex items-center gap-4 relative z-10">
            <img src="<?php echo $foto_anak; ?>" class="w-16 h-16 rounded-full border-4 border-white/30 bg-white object-cover">
            <div>
                <h2 class="font-bold text-lg leading-tight"><?php echo $d_siswa['nama_siswa']; ?></h2>
                <span class="bg-emerald-800 text-emerald-100 text-[10px] px-2 py-1 rounded-full font-bold uppercase"><?php echo $d_siswa['kelas']; ?> • <?php echo $d_siswa['nisn']; ?></span>
            </div>
        </div>
    </div>

    <div class="px-6 -mt-8 relative z-20">
        <div class="bg-white p-5 rounded-3xl shadow-lg border border-slate-100 text-center">
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">Status Hari Ini</p>
            <h3 class="text-2xl font-black text-slate-800 mb-1"><?php echo $status; ?></h3>
            <p class="text-xs text-slate-500 italic"><?php echo date('l, d F Y'); ?></p>
            
            <div class="grid grid-cols-2 gap-4 mt-6">
                <div class="bg-blue-50 p-3 rounded-2xl">
                    <div class="flex items-center justify-center gap-2 text-blue-600 mb-1"><i data-lucide="log-in" class="w-4 h-4"></i> <span class="text-[10px] font-bold uppercase">Masuk</span></div>
                    <span class="text-xl font-black text-slate-700"><?php echo $jam_masuk; ?></span>
                </div>
                <div class="bg-orange-50 p-3 rounded-2xl">
                    <div class="flex items-center justify-center gap-2 text-orange-600 mb-1"><i data-lucide="log-out" class="w-4 h-4"></i> <span class="text-[10px] font-bold uppercase">Pulang</span></div>
                    <span class="text-xl font-black text-slate-700"><?php echo $jam_pulang; ?></span>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="font-bold text-slate-700 text-sm mb-4 flex items-center gap-2"><i data-lucide="book-open" class="w-4 h-4 text-emerald-600"></i> Kegiatan Belajar Hari Ini</h3>
            
            <div class="space-y-4">
                <?php if(mysqli_num_rows($q_kbm) == 0): ?>
                    <div class="text-center py-10 text-slate-400 text-xs italic bg-white rounded-2xl border border-dashed border-slate-300">Belum ada kegiatan belajar tercatat hari ini.</div>
                <?php else: ?>
                    <?php while($k = mysqli_fetch_assoc($q_kbm)): 
                        $jam_kbm = substr($k['waktu'], 11, 5); // Ambil jam dari datetime
                    ?>
                    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
                        <div class="flex justify-between items-start mb-2 pl-2">
                            <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded"><?php echo $jam_kbm; ?></span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wide"><?php echo $k['nama_guru']; ?></span>
                        </div>
                        <div class="pl-2">
                            <h4 class="font-bold text-slate-800 text-sm leading-tight mb-1"><?php echo $k['mapel']; ?></h4>
                            <p class="text-xs text-slate-500 line-clamp-2 bg-slate-50 p-2 rounded-lg border border-slate-100">"<?php echo $k['materi']; ?>"</p>
                            
                            <?php if($k['foto_kegiatan']): ?>
                                <div class="mt-3">
                                    <button onclick="Swal.fire({imageUrl: 'uploads/jurnal/<?php echo $k['foto_kegiatan']; ?>', showConfirmButton: false, width:'80%'})" class="text-[10px] flex items-center gap-1 text-blue-500 font-bold hover:underline">
                                        <i data-lucide="image" class="w-3 h-3"></i> Lihat Foto Kegiatan
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>