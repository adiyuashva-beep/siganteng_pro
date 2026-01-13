<?php
// File: api_guru.php (FINAL)
session_start();
include 'koneksi.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. INIT DATA
if ($action == 'init_data') {
    $nip = $_GET['nip'];
    
    // Ambil Data Guru (Termasuk Level/Role)
    $q_guru = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username='$nip'");
    $d_guru = mysqli_fetch_assoc($q_guru);
    
    // Daftar Kelas & Mapel
    $q_kelas = mysqli_query($koneksi, "SELECT DISTINCT kelas FROM tb_siswa WHERE status_aktif='1' ORDER BY kelas ASC");
    $kelas = []; while($r=mysqli_fetch_assoc($q_kelas)){ if($r['kelas']) $kelas[]=$r['kelas']; }
    
    $q_mapel = mysqli_query($koneksi, "SELECT nama_mapel FROM tb_mapel ORDER BY nama_mapel ASC");
    $mapel = []; while($r=mysqli_fetch_assoc($q_mapel)){ $mapel[]=$r['nama_mapel']; }
    
    // Menu Dinamis
    $q_menu = mysqli_query($koneksi, "SELECT * FROM tb_menu WHERE target_user IN ('guru', 'umum')");
    $menu = []; while($r=mysqli_fetch_assoc($q_menu)){ $menu[]=$r; }

    // WALI KELAS
    $wali_data = null;
    if ($d_guru['wali_kelas'] && $d_guru['wali_kelas'] != '-') {
        $kls = $d_guru['wali_kelas']; $tgl = date('Y-m-d');
        $q_tot = mysqli_query($koneksi, "SELECT COUNT(*) as t FROM tb_siswa WHERE kelas='$kls'");
        $tot = mysqli_fetch_assoc($q_tot)['t'];
        
        $hadir=0; $izin=0;
        $q_abs = mysqli_query($koneksi, "SELECT status_kehadiran FROM tb_absensi WHERE tanggal='$tgl' AND kelas='$kls'");
        while($r=mysqli_fetch_assoc($q_abs)){
            $st = $r['status_kehadiran'];
            if(strpos($st,'Hadir')!==false || strpos($st,'Masuk')!==false) $hadir++;
            elseif(strpos($st,'Sakit')!==false || strpos($st,'Izin')!==false) $izin++;
        }
        $wali_data = ['kelas'=>$kls, 'hadir'=>$hadir, 'izin'=>$izin, 'alpha'=>($tot-$hadir-$izin)];
    }

    echo json_encode([
        'guru' => $d_guru, // Level ada di sini ($d_guru['level'])
        'kelas' => $kelas, 
        'mapel' => $mapel, 
        'menu' => $menu,
        'wali_data' => $wali_data
    ]);
    exit;
}

// 2. GET SISWA KELAS (Untuk Absen)
if ($action == 'get_siswa_kelas') {
    $kelas = $_GET['kelas'];
    $q = mysqli_query($koneksi, "SELECT nisn, nama_siswa FROM tb_siswa WHERE kelas='$kelas' AND status_aktif='1' ORDER BY nama_siswa ASC");
    $d = []; while($r=mysqli_fetch_assoc($q)) $d[]=$r; echo json_encode($d); exit;
}

// 3. GET DETAIL WALI
if ($action == 'get_detail_wali') {
    $kelas = $_GET['kelas']; $tgl = date('Y-m-d');
    $q = mysqli_query($koneksi, "SELECT s.nama_siswa, s.nisn, a.status_kehadiran, a.jam_masuk FROM tb_siswa s LEFT JOIN tb_absensi a ON s.nisn=a.nisn AND a.tanggal='$tgl' WHERE s.kelas='$kelas' ORDER BY s.nama_siswa ASC");
    $d = []; 
    while($r=mysqli_fetch_assoc($q)){
        $st = $r['status_kehadiran'] ?? 'Belum Absen';
        if($r['jam_masuk'] && strpos($st,'Hadir')!==false) $st = "Masuk: ".substr($r['jam_masuk'],0,5);
        $r['status_display'] = $st; $d[]=$r;
    }
    echo json_encode($d); exit;
}

// 4. SIMPAN JURNAL
if ($action == 'simpan_jurnal') {
    $nip=$_POST['nip']; $nama=$_POST['nama_guru']; $kls=$_POST['kelas']; $mpl=$_POST['mapel']; $mtr=$_POST['materi']; $jam=$_POST['jam_ke']; $foto=$_POST['foto'];
    
    if (!is_dir('uploads/jurnal')) mkdir('uploads/jurnal', 0777, true);
    $fname = "jurnal_" . time() . "_" . rand(100,999) . ".jpg";
    file_put_contents("uploads/jurnal/" . $fname, base64_decode(explode(',', $foto)[1]));
    $tgl = date('Y-m-d');
    
    $q = "INSERT INTO tb_jurnal (id_guru, nama_guru, kelas, mapel, materi, foto_kegiatan, tanggal, waktu, jam_ke) VALUES ('$nip', '$nama', '$kls', '$mpl', '$mtr', '$fname', '$tgl', NOW(), '$jam')";
    
    if (mysqli_query($koneksi, $q)) {
        $idj = mysqli_insert_id($koneksi);
        $siswa = json_decode($_POST['absen_siswa'], true);
        if(is_array($siswa)) {
            foreach($siswa as $s) {
                $n=$s['nisn']; $nm=$s['nama']; $st=$s['status'];
                mysqli_query($koneksi, "INSERT INTO tb_absen_mapel (id_jurnal, nisn, nama_siswa, status) VALUES ('$idj', '$n', '$nm', '$st')");
            }
        }
        echo json_encode(['status'=>'success']);
    } else echo json_encode(['status'=>'error', 'pesan'=>mysqli_error($koneksi)]);
    exit;
}

// 5. RIWAYAT & PROFIL
if ($action == 'get_riwayat') {
    $nip=$_GET['nip']; $tgl=date('Y-m-d');
    $q = mysqli_query($koneksi, "SELECT * FROM tb_jurnal WHERE id_guru='$nip' AND tanggal='$tgl' ORDER BY id_jurnal DESC");
    $d=[]; while($r=mysqli_fetch_assoc($q)) $d[]=$r; echo json_encode($d); exit;
}
if ($action == 'update_profil') {
    $nip=$_POST['nip']; $foto=$_POST['foto'];
    if (!is_dir('uploads/profil_guru')) mkdir('uploads/profil_guru', 0777, true);
    $fname = "guru_" . $nip . "_" . time() . ".jpg";
    file_put_contents("uploads/profil_guru/" . $fname, base64_decode(explode(',', $foto)[1]));
    mysqli_query($koneksi, "UPDATE tb_user SET foto_profil='$fname' WHERE username='$nip'");
    echo json_encode(['status'=>'success']); exit;
}
?>