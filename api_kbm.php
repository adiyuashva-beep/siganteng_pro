<?php
// File: api_kbm.php
session_start();
include 'koneksi.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. MONITORING KBM (UTAMA)
if ($action == 'get_monitor') {
    $tgl = $_GET['tanggal']; // YYYY-MM-DD
    
    // A. Ambil Semua Jurnal Guru Hari Ini
    $q_jurnal = mysqli_query($koneksi, "SELECT * FROM tb_jurnal WHERE tanggal='$tgl' ORDER BY waktu DESC");
    $jurnals = [];
    $jurnal_ids = [];
    
    while($r = mysqli_fetch_assoc($q_jurnal)) {
        // Ambil Absensi Mapel (JSON string or separate table logic)
        // Di sistem kita tadi pakai tabel terpisah tb_absen_mapel, kita join manual
        $id_j = $r['id_jurnal'];
        $q_absen = mysqli_query($koneksi, "SELECT nisn, nama_siswa, status FROM tb_absen_mapel WHERE id_jurnal='$id_j' AND status != 'H'");
        $absen_list = [];
        while($a = mysqli_fetch_assoc($q_absen)) { $absen_list[] = $a; }
        
        $r['absensi_mapel'] = $absen_list;
        $jurnals[] = $r;
        $jurnal_ids[] = $id_j;
    }
    
    // B. Ambil Semua Refleksi Siswa Hari Ini (Optimasi Query)
    $refleksi_data = [];
    if (!empty($jurnal_ids)) {
        $ids_str = implode(',', $jurnal_ids);
        $q_ref = mysqli_query($koneksi, "SELECT * FROM tb_refleksi WHERE id_jurnal IN ($ids_str)");
        
        while($r = mysqli_fetch_assoc($q_ref)) {
            $id = $r['id_jurnal'];
            if(!isset($refleksi_data[$id])) {
                $refleksi_data[$id] = ['r4'=>0, 'r3'=>0, 'r2'=>0, 'r1'=>0, 'total'=>0, 'komen'=>[]];
            }
            
            $rating = $r['rating'];
            if($rating == 4) $refleksi_data[$id]['r4']++;
            elseif($rating == 3) $refleksi_data[$id]['r3']++;
            elseif($rating == 2) $refleksi_data[$id]['r2']++;
            else $refleksi_data[$id]['r1']++;
            
            $refleksi_data[$id]['total']++;
            
            // Simpan komentar kalau ada isinya
            if(!empty($r['pesan']) && $r['pesan'] != '-') {
                $refleksi_data[$id]['komen'][] = ['nama'=>$r['nama_siswa'], 'text'=>$r['pesan'], 'rating'=>$rating];
            }
        }
    }
    
    echo json_encode(['jurnals' => $jurnals, 'refleksi' => $refleksi_data]);
    exit;
}
?>