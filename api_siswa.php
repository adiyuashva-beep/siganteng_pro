<?php
// File: api_siswa.php (FINAL V2 - Support 7 Hebat)
session_start();
include 'koneksi.php';
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$action = $data['action'] ?? $_GET['action'] ?? '';

// FUNGSI JARAK
function hitungJarak($lat1, $lon1, $lat2, $lon2) {
    if(($lat1 == $lat2) && ($lon1 == $lon2)) return 0;
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    return ($miles * 1.609344) * 1000; 
}

// 1. GET PROFIL LENGKAP
if ($action == 'get_profil') {
    $nisn = $_GET['nisn'];
    $q_s = mysqli_query($koneksi, "SELECT * FROM tb_siswa WHERE nisn='$nisn'");
    $d_s = mysqli_fetch_assoc($q_s);
    $q_c = mysqli_query($koneksi, "SELECT * FROM tb_pengaturan LIMIT 1");
    $d_c = mysqli_fetch_assoc($q_c);
    $foto = $d_s['foto_profil'] ? 'uploads/profil/'.$d_s['foto_profil'] : '';
    
    echo json_encode([
        'siswa' => [
            'nisn' => $d_s['nisn'], 'nama' => $d_s['nama_siswa'], 'kelas' => $d_s['kelas'],
            'foto' => $foto, 'wa_siswa' => $d_s['wa_siswa'], 'wa_ortu' => $d_s['wa_ortu'],
            'update_foto_count' => $d_s['update_foto_count']
        ],
        'config' => [
            'lat' => (float)$d_c['pusat_lat'], 'lng' => (float)$d_c['pusat_lng'],
            'radius' => (int)$d_c['radius_meter'], 'mode' => $d_c['mode_gps'],
            'jenjang' => $d_c['jenjang_sekolah']
        ]
    ]); exit;
}

// 2. CEK STATUS REALTIME
if ($action == 'cek_status') {
    $nisn = $_GET['nisn']; $tgl = date('Y-m-d');
    $q = mysqli_query($koneksi, "SELECT * FROM tb_absensi WHERE nisn='$nisn' AND tanggal='$tgl'");
    $d = mysqli_fetch_assoc($q);
    if ($d) {
        echo json_encode([
            'status' => $d['status_kehadiran'],
            'jam_masuk' => $d['jam_masuk'] ? substr($d['jam_masuk'],0,5) : null,
            'jam_pulang' => $d['jam_pulang'] && $d['jam_pulang']!='00:00:00' ? substr($d['jam_pulang'],0,5) : null
        ]);
    } else { echo json_encode(['status' => 'Belum Hadir']); } exit;
}

// 3. PROSES ABSEN
if ($action == 'absen') {
    $nisn = $data['nisn']; $tipe = $data['tipe']; $lat = $data['lat']; $lng = $data['lng'];
    $foto = $data['foto']; $ket = $data['keterangan'] ?? '';
    $tgl = date('Y-m-d'); $jam = date('H:i:s');

    if(in_array($tipe, ['Masuk','Pulang','Kembali'])) {
        $q_set = mysqli_query($koneksi, "SELECT * FROM tb_pengaturan LIMIT 1"); $d_set = mysqli_fetch_assoc($q_set);
        $jarak = hitungJarak($lat, $lng, $d_set['pusat_lat'], $d_set['pusat_lng']);
        if ($d_set['mode_gps'] == 'strict' && $jarak > $d_set['radius_meter']) {
            echo json_encode(['status' => 'error', 'pesan' => 'Diluar Radius! Jarak: '.round($jarak).'m']); exit;
        }
    } else { $jarak = 0; }

    if (!is_dir('uploads/absen')) mkdir('uploads/absen', 0777, true);
    $nama_foto = $nisn . "_" . $tipe . "_" . time() . ".jpg";
    if($foto && $foto != '-') file_put_contents("uploads/absen/" . $nama_foto, base64_decode(explode(',', $foto)[1])); else $nama_foto = '';

    $q_cek = mysqli_query($koneksi, "SELECT id_absen FROM tb_absensi WHERE nisn='$nisn' AND tanggal='$tgl'");
    if (mysqli_num_rows($q_cek) > 0) {
        if ($tipe == 'Pulang') { mysqli_query($koneksi, "UPDATE tb_absensi SET jam_pulang='$jam', foto_pulang='$nama_foto' WHERE nisn='$nisn' AND tanggal='$tgl'"); echo json_encode(['status' => 'success', 'pesan' => 'Hati-hati di jalan!']); } 
        else if ($tipe == 'Izin Keluar') { mysqli_query($koneksi, "UPDATE tb_absensi SET status_kehadiran='Izin Keluar ($ket)' WHERE nisn='$nisn' AND tanggal='$tgl'"); echo json_encode(['status' => 'success', 'pesan' => 'Izin Keluar Tercatat']); }
        else if ($tipe == 'Kembali') { mysqli_query($koneksi, "UPDATE tb_absensi SET status_kehadiran='Hadir' WHERE nisn='$nisn' AND tanggal='$tgl'"); echo json_encode(['status' => 'success', 'pesan' => 'Selamat Datang Kembali']); }
        else { echo json_encode(['status' => 'error', 'pesan' => 'Sudah absen.']); }
    } else {
        $status_db = ($tipe == 'Masuk') ? 'Hadir' : "$tipe: $ket";
        $q = "INSERT INTO tb_absensi (nisn, kelas, tanggal, jam_masuk, status_kehadiran, foto_masuk, lokasi_lat, lokasi_lng, jarak_meter) SELECT '$nisn', kelas, '$tgl', '$jam', '$status_db', '$nama_foto', '$lat', '$lng', '$jarak' FROM tb_siswa WHERE nisn='$nisn'";
        if(mysqli_query($koneksi, $q)) echo json_encode(['status' => 'success', 'pesan' => $tipe . ' Berhasil!']); else echo json_encode(['status' => 'error', 'pesan' => 'Gagal DB']);
    } exit;
}

// 4. TIMELINE KBM
if ($action == 'get_timeline') {
    $kelas = $_GET['kelas']; $nisn = $_GET['nisn']; $tgl = date('Y-m-d');
    $q = mysqli_query($koneksi, "SELECT * FROM tb_jurnal WHERE kelas='$kelas' AND tanggal='$tgl' ORDER BY waktu DESC");
    $data = [];
    while($d = mysqli_fetch_assoc($q)) {
        $id_j = $d['id_jurnal'];
        $q_ref = mysqli_query($koneksi, "SELECT rating FROM tb_refleksi WHERE id_jurnal='$id_j' AND nisn='$nisn'");
        $d['sudah_refleksi'] = (mysqli_num_rows($q_ref) > 0);
        $data[] = $d;
    } echo json_encode($data); exit;
}

// 5. KIRIM REFLEKSI & UPDATE PROFIL
if ($action == 'kirim_refleksi') {
    $nisn = $data['nisn']; $nama = $data['nama']; $id_jurnal = $data['id_jurnal']; $rating = $data['rating']; $pesan = $data['pesan']; $tgl = date('Y-m-d');
    $q_j = mysqli_query($koneksi, "SELECT kelas, mapel FROM tb_jurnal WHERE id_jurnal='$id_jurnal'"); $d_j = mysqli_fetch_assoc($q_j);
    $q = "INSERT INTO tb_refleksi (id_jurnal, nisn, nama_siswa, kelas, mapel, rating, pesan, tanggal) VALUES ('$id_jurnal', '$nisn', '$nama', '{$d_j['kelas']}', '{$d_j['mapel']}', '$rating', '$pesan', '$tgl')";
    if(mysqli_query($koneksi, $q)) echo json_encode(['status'=>'success']); else echo json_encode(['status'=>'error']); exit;
}
if ($action == 'update_profil') {
    $nisn = $data['nisn']; $foto = $data['foto'];
    if (!is_dir('uploads/profil')) mkdir('uploads/profil', 0777, true);
    $nama = "profil_" . $nisn . "_" . time() . ".jpg";
    file_put_contents("uploads/profil/" . $nama, base64_decode(explode(',', $foto)[1]));
    mysqli_query($koneksi, "UPDATE tb_siswa SET foto_profil='$nama', update_foto_count=update_foto_count+1 WHERE nisn='$nisn'");
    echo json_encode(['status'=>'success']); exit;
}

// 6. MENU DINAMIS & SETTINGS LAIN
// Ganti jadi ini:
if ($action == 'get_menu') { 
    // Tambahkan: AND is_active='1'
    $q = mysqli_query($koneksi, "SELECT * FROM tb_menu WHERE target_user IN ('siswa','umum') AND is_active='1' ORDER BY created_at ASC"); 
    $d=[]; while($r=mysqli_fetch_assoc($q)) $d[]=$r; echo json_encode($d); exit; 
}
if ($action == 'update_wa') { $nisn=$data['nisn']; mysqli_query($koneksi, "UPDATE tb_siswa SET wa_siswa='{$data['wa_siswa']}', wa_ortu='{$data['wa_ortu']}' WHERE nisn='$nisn'"); echo json_encode(['status'=>'success']); exit; }
if ($action == 'ganti_password') { $nisn=$data['nisn']; mysqli_query($koneksi, "UPDATE tb_siswa SET password='{$data['pass']}' WHERE nisn='$nisn'"); echo json_encode(['status'=>'success']); exit; }

// 7. SIMPAN 7 KEBIASAAN HEBAT (FITUR BARU)
if ($action == 'simpan_hebat') {
    $nisn = $data['nisn']; $nama = $data['nama']; $kelas = $data['kelas'];
    $tgl = date('Y-m-d'); $waktu = date('Y-m-d H:i:s'); $poin = $data['poin_harian'];

    if (!is_dir('uploads/hebat')) mkdir('uploads/hebat', 0777, true);

    $foto_makan = $data['foto_makan'];
    if (!empty($foto_makan) && strpos($foto_makan, 'base64') !== false) {
        $nm = "makan_" . $nisn . "_" . time() . ".jpg";
        file_put_contents("uploads/hebat/" . $nm, base64_decode(explode(',', $foto_makan)[1]));
        $foto_makan = $nm;
    }
    $foto_belajar = $data['foto_belajar'];
    if (!empty($foto_belajar) && strpos($foto_belajar, 'base64') !== false) {
        $nm = "belajar_" . $nisn . "_" . time() . ".jpg";
        file_put_contents("uploads/hebat/" . $nm, base64_decode(explode(',', $foto_belajar)[1]));
        $foto_belajar = $nm;
    }

    $cek = mysqli_query($koneksi, "SELECT id_hebat FROM tb_7_hebat WHERE nisn='$nisn' AND tanggal='$tgl'");
    
    if (mysqli_num_rows($cek) > 0) {
        $q = "UPDATE tb_7_hebat SET waktu_update='$waktu', poin_harian='$poin', bangun_pagi='{$data['bangun_pagi']}', jam_bangun='{$data['jam_bangun']}', beribadah='{$data['beribadah']}', jenis_ibadah='{$data['jenis_ibadah']}', olahraga='{$data['olahraga']}', jenis_olahraga='{$data['jenis_olahraga']}', makan_sehat='{$data['makan_sehat']}', menu_makan='{$data['menu_makan']}', foto_makan='$foto_makan', gemar_belajar='{$data['gemar_belajar']}', mapel_belajar='{$data['mapel_belajar']}', foto_belajar='$foto_belajar', bermasyarakat='{$data['bermasyarakat']}', keg_sosial='{$data['keg_sosial']}', tidur_cepat='{$data['tidur_cepat']}', jam_tidur='{$data['jam_tidur']}' WHERE nisn='$nisn' AND tanggal='$tgl'";
    } else {
        $q = "INSERT INTO tb_7_hebat (nisn, nama, kelas, tanggal, waktu_update, poin_harian, bangun_pagi, jam_bangun, beribadah, jenis_ibadah, olahraga, jenis_olahraga, makan_sehat, menu_makan, foto_makan, gemar_belajar, mapel_belajar, foto_belajar, bermasyarakat, keg_sosial, tidur_cepat, jam_tidur) VALUES ('$nisn', '$nama', '$kelas', '$tgl', '$waktu', '$poin', '{$data['bangun_pagi']}', '{$data['jam_bangun']}', '{$data['beribadah']}', '{$data['jenis_ibadah']}', '{$data['olahraga']}', '{$data['jenis_olahraga']}', '{$data['makan_sehat']}', '{$data['menu_makan']}', '$foto_makan', '{$data['gemar_belajar']}', '{$data['mapel_belajar']}', '$foto_belajar', '{$data['bermasyarakat']}', '{$data['keg_sosial']}', '{$data['tidur_cepat']}', '{$data['jam_tidur']}')";
    }

    if (mysqli_query($koneksi, $q)) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'pesan' => mysqli_error($koneksi)]);
    exit;
}

// 8. CEK HEBAT HARI INI
if ($action == 'cek_hebat') {
    $nisn = $_GET['nisn']; $tgl = date('Y-m-d');
    $q = mysqli_query($koneksi, "SELECT * FROM tb_7_hebat WHERE nisn='$nisn' AND tanggal='$tgl'");
    $d = mysqli_fetch_assoc($q);
    if ($d) {
        if($d['foto_makan'] && $d['foto_makan'] != '-') $d['foto_makan'] = 'uploads/hebat/'.$d['foto_makan'];
        if($d['foto_belajar'] && $d['foto_belajar'] != '-') $d['foto_belajar'] = 'uploads/hebat/'.$d['foto_belajar'];
        echo json_encode($d);
    } else echo json_encode(null);
    exit;
}
?>