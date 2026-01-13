<?php
// File: api_monitor.php
include 'koneksi.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

$action = $_GET['action'] ?? '';

// 1. GET MASTER SISWA (Dipanggil sekali saja saat load awal/refresh cache)
if ($action == 'get_master_siswa') {
    // Ambil hanya NISN, Nama, dan Kelas siswa aktif
    $q = mysqli_query($koneksi, "SELECT nisn as username, nama_siswa as name, kelas FROM tb_siswa WHERE status_aktif='1'");
    $data = [];
    while($r = mysqli_fetch_assoc($q)) {
        $data[] = $r;
    }
    echo json_encode($data);
    exit;
}

// 2. GET LIVE DATA (Dipanggil setiap 3-5 detik)
if ($action == 'get_live_absen') {
    $tgl = date('Y-m-d');
    
    // Ambil data absensi hari ini (Join dengan tb_siswa untuk dapat nama/kelas terbaru jika perlu)
    // Kita ambil foto_masuk sebagai display utama
    $q = mysqli_query($koneksi, "SELECT a.nisn, a.jam_masuk, a.status_kehadiran, a.foto_masuk, s.nama_siswa, s.kelas 
                                 FROM tb_absensi a 
                                 JOIN tb_siswa s ON a.nisn = s.nisn 
                                 WHERE a.tanggal='$tgl'");
    
    $data = [];
    while($r = mysqli_fetch_assoc($q)) {
        // Format jam biar rapi (07:00)
        $jam_clean = $r['jam_masuk'] ? substr($r['jam_masuk'], 0, 8) : "00:00:00"; // format H:i:s
        
        // Handle Foto
        $foto_url = "https://cdn-icons-png.flaticon.com/512/847/847969.png"; // Default
        if ($r['foto_masuk'] && file_exists("uploads/absen/" . $r['foto_masuk'])) {
            $foto_url = "uploads/absen/" . $r['foto_masuk'];
        }
        
        $data[] = [
            'nisn' => $r['nisn'],
            'nama' => $r['nama_siswa'],
            'kelas' => $r['kelas'],
            'jam' => $jam_clean,
            'status' => $r['status_kehadiran'],
            'foto' => $foto_url
        ];
    }
    echo json_encode($data);
    exit;
}
?>