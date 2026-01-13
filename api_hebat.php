<?php
// File: api_hebat.php
session_start();
include 'koneksi.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action == 'get_dashboard') {
    $tgl = $_GET['tanggal']; // Format: YYYY-MM-DD
    
    $q = mysqli_query($koneksi, "SELECT * FROM tb_7_hebat WHERE tanggal='$tgl'");
    
    $data = [];
    $stats = [
        'bangun' => 0, 'ibadah' => 0, 'olahraga' => 0, 
        'makan' => 0, 'belajar' => 0, 'sosial' => 0, 'tidur' => 0,
        'total_poin' => 0,
        'by_kelas' => []
    ];
    
    while($r = mysqli_fetch_assoc($q)) {
        // Proses Data Foto agar lengkap URL-nya
        if($r['foto_makan']) $r['foto_makan'] = 'uploads/hebat/'.$r['foto_makan'];
        if($r['foto_belajar']) $r['foto_belajar'] = 'uploads/hebat/'.$r['foto_belajar'];
        
        $data[] = $r;
        
        // Hitung Statistik untuk Radar Chart
        if($r['bangun_pagi']) $stats['bangun']++;
        if($r['beribadah']) $stats['ibadah']++;
        if($r['olahraga']) $stats['olahraga']++;
        if($r['makan_sehat']) $stats['makan']++;
        if($r['gemar_belajar']) $stats['belajar']++;
        if($r['bermasyarakat']) $stats['sosial']++;
        if($r['tidur_cepat']) $stats['tidur']++;
        
        $stats['total_poin'] += $r['poin_harian'];
        
        // Hitung Statistik per Kelas
        $kls = $r['kelas'];
        if(!isset($stats['by_kelas'][$kls])) $stats['by_kelas'][$kls] = 0;
        $stats['by_kelas'][$kls]++;
    }
    
    echo json_encode(['data' => $data, 'stats' => $stats]);
    exit;
}
?>