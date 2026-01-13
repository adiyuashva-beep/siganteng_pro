<?php
// File: api_bk.php
session_start();
include 'koneksi.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. DASHBOARD MONITORING
if ($action == 'get_dashboard') {
    $tgl = date('Y-m-d');
    
    // Hitung Statistik Hari Ini
    $q_tot = mysqli_query($koneksi, "SELECT COUNT(*) as t FROM tb_siswa WHERE status_aktif='1'");
    $total = mysqli_fetch_assoc($q_tot)['t'];
    
    $hadir = 0; $izin = 0;
    $q_abs = mysqli_query($koneksi, "SELECT status_kehadiran FROM tb_absensi WHERE tanggal='$tgl'");
    while($r = mysqli_fetch_assoc($q_abs)) {
        $st = $r['status_kehadiran'];
        if(strpos($st,'Hadir')!==false || strpos($st,'Masuk')!==false) $hadir++;
        else if(strpos($st,'Sakit')!==false || strpos($st,'Izin')!==false) $izin++;
    }
    
    // Live Log (Hanya Siswa)
    $q_log = mysqli_query($koneksi, "SELECT a.jam_masuk, s.nama_siswa, s.kelas, a.status_kehadiran 
                                     FROM tb_absensi a 
                                     JOIN tb_siswa s ON a.nisn = s.nisn 
                                     WHERE a.tanggal='$tgl' 
                                     ORDER BY a.jam_masuk DESC LIMIT 10");
    $logs = [];
    while($r = mysqli_fetch_assoc($q_log)) {
        $logs[] = [
            'jam' => substr($r['jam_masuk'],0,5),
            'nama' => $r['nama_siswa'],
            'kelas' => $r['kelas'],
            'status' => $r['status_kehadiran']
        ];
    }
    
    echo json_encode(['total'=>$total, 'hadir'=>$hadir, 'izin'=>$izin, 'alpha'=>($total-$hadir-$izin), 'logs'=>$logs]);
    exit;
}

// 2. DATA REKAP (KOMPLEKS)
if ($action == 'get_rekap_data') {
    $kelas = $_GET['kelas'];
    $bulan = $_GET['bulan']; // Format: 01, 02
    $tahun = $_GET['tahun']; // Format: 2025
    
    $start = "$tahun-$bulan-01";
    $end = date("Y-m-t", strtotime($start));
    
    // Ambil Data Absensi
    $q_absen = mysqli_query($koneksi, "SELECT nisn, tanggal, status_kehadiran, jam_masuk, jam_pulang 
                                       FROM tb_absensi 
                                       WHERE tanggal BETWEEN '$start' AND '$end' 
                                       AND nisn IN (SELECT nisn FROM tb_siswa WHERE kelas='$kelas')");
    $absensi = [];
    while($r = mysqli_fetch_assoc($q_absen)) {
        $absensi[] = $r;
    }
    
    // Ambil Data Dispensasi
    $q_dispen = mysqli_query($koneksi, "SELECT nisn, tgl_mulai, tgl_selesai FROM tb_dispensasi 
                                        WHERE (tgl_mulai <= '$end' AND tgl_selesai >= '$start') 
                                        AND kelas='$kelas'");
    $dispen = [];
    while($r = mysqli_fetch_assoc($q_dispen)) {
        $dispen[] = $r;
    }
    
    echo json_encode(['absensi'=>$absensi, 'dispen'=>$dispen]);
    exit;
}

// 3. AMBIL DATA SISWA (Untuk Dropdown & Pencarian)
if ($action == 'get_siswa_bk') {
    $q = mysqli_query($koneksi, "SELECT nisn, nama_siswa, kelas FROM tb_siswa WHERE status_aktif='1' ORDER BY nama_siswa ASC");
    $data = []; while($r=mysqli_fetch_assoc($q)){ $data[]=$r; } echo json_encode($data); exit;
}

// 4. KELOLA DISPENSASI
if ($action == 'simpan_dispen') {
    $nisn = $_POST['nisn'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $alasan = $_POST['alasan'];
    
    $url_surat = "-";
    if(isset($_POST['file']) && $_POST['file'] != 'undefined') {
        if (!is_dir('uploads/surat')) mkdir('uploads/surat', 0777, true);
        $nama_file = "surat_" . $nisn . "_" . time() . ".jpg"; // Asumsi JPG/PDF base64 logic handled by frontend
        // Note: Untuk simplifikasi PHP native, kita terima base64 image saja dulu atau text link
        // Kalau upload file asli perlu $_FILES. Kita pakai logika link saja kalau upload via frontend JS FileReader.
        $foto_b64 = $_POST['file']; 
        if (strpos($foto_b64, 'base64') !== false) {
            file_put_contents("uploads/surat/" . $nama_file, base64_decode(explode(',', $foto_b64)[1]));
            $url_surat = "uploads/surat/" . $nama_file;
        }
    }
    
    $q = "INSERT INTO tb_dispensasi (nisn, nama_siswa, kelas, tgl_mulai, tgl_selesai, alasan, link_surat) 
          VALUES ('$nisn', '$nama', '$kelas', '$start', '$end', '$alasan', '$url_surat')";
    
    if(mysqli_query($koneksi, $q)) echo json_encode(['status'=>'success']);
    else echo json_encode(['status'=>'error', 'pesan'=>mysqli_error($koneksi)]);
    exit;
}

if ($action == 'get_dispen_aktif') {
    $q = mysqli_query($koneksi, "SELECT * FROM tb_dispensasi ORDER BY created_at DESC LIMIT 50");
    $d=[]; while($r=mysqli_fetch_assoc($q)){ $d[]=$r; } echo json_encode($d); exit;
}

if ($action == 'hapus_dispen') {
    $id = $_POST['id'];
    mysqli_query($koneksi, "DELETE FROM tb_dispensasi WHERE id_dispen='$id'");
    echo json_encode(['status'=>'success']); exit;
}

// 5. MANAJEMEN HARI LIBUR
if ($action == 'get_libur') {
    $q = mysqli_query($koneksi, "SELECT * FROM tb_hari_libur ORDER BY tanggal ASC");
    $d=[]; while($r=mysqli_fetch_assoc($q)){ $d[]=$r; } echo json_encode($d); exit;
}
if ($action == 'simpan_libur') {
    $tgl = $_POST['tanggal']; $ket = $_POST['ket'];
    mysqli_query($koneksi, "INSERT INTO tb_hari_libur (tanggal, keterangan) VALUES ('$tgl', '$ket')");
    echo json_encode(['status'=>'success']); exit;
}
if ($action == 'hapus_libur') {
    $id = $_POST['id'];
    mysqli_query($koneksi, "DELETE FROM tb_hari_libur WHERE id_libur='$id'");
    echo json_encode(['status'=>'success']); exit;
}

// 6. LAPORAN MBG (Makan Bergizi Gratis)
if ($action == 'get_laporan_mbg') {
    $tgl = date('Y-m-d');
    
    // Ambil data siswa yg hadir hari ini, join dengan tabel siswa untuk dapat kelasnya
    $q = mysqli_query($koneksi, "SELECT s.kelas, s.nisn, a.status_kehadiran 
                                 FROM tb_absensi a 
                                 JOIN tb_siswa s ON a.nisn = s.nisn 
                                 WHERE a.tanggal='$tgl' AND (a.status_kehadiran LIKE '%Hadir%' OR a.status_kehadiran LIKE '%Masuk%')");
    
    $data_hadir = [];
    while($r = mysqli_fetch_assoc($q)) {
        $kls = strtoupper(trim($r['kelas']));
        if(!isset($data_hadir[$kls])) $data_hadir[$kls] = 0;
        $data_hadir[$kls]++;
    }
    
    // Hitung Total Siswa Per Kelas (Untuk menghitung sisa)
    $q_tot = mysqli_query($koneksi, "SELECT kelas, COUNT(*) as total FROM tb_siswa WHERE status_aktif='1' GROUP BY kelas");
    $data_total = [];
    while($r = mysqli_fetch_assoc($q_tot)) {
        $kls = strtoupper(trim($r['kelas']));
        $data_total[$kls] = $r['total'];
    }
    
    echo json_encode(['hadir_per_kelas' => $data_hadir, 'total_per_kelas' => $data_total]);
    exit;
}
?>