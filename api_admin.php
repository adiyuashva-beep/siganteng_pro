<?php
// File: api_admin.php
session_start();
include 'koneksi.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. DASHBOARD STATS & LIVE LOG
if ($action == 'get_dashboard_stats') {
    // Hitung Total Siswa
    $q_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_siswa WHERE status_aktif='1'");
    $total_siswa = mysqli_fetch_assoc($q_siswa)['total'];
    
    // Hitung Total Guru
    $q_guru = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_user WHERE level IN ('guru','admin','bk','kurikulum')");
    $total_guru = mysqli_fetch_assoc($q_guru)['total'];
    
    // Hitung Hadir Hari Ini
    $tgl = date('Y-m-d');
    $q_hadir = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_absensi WHERE tanggal='$tgl' AND (status_kehadiran LIKE '%Hadir%' OR status_kehadiran LIKE '%Masuk%')");
    $hadir_realtime = mysqli_fetch_assoc($q_hadir)['total'];

    // Ambil Mode GPS & Jenjang
    $q_set = mysqli_query($koneksi, "SELECT mode_gps, jenjang_sekolah FROM tb_pengaturan LIMIT 1");
    $d_set = mysqli_fetch_assoc($q_set);

    // Ambil Live Log (10 Terakhir)
    $q_log = mysqli_query($koneksi, "SELECT a.*, s.nama_siswa, s.kelas FROM tb_absensi a JOIN tb_siswa s ON a.nisn = s.nisn WHERE a.tanggal='$tgl' ORDER BY a.jam_masuk DESC LIMIT 10");
    $logs = [];
    while($r = mysqli_fetch_assoc($q_log)) {
        $logs[] = [
            'jam' => substr($r['jam_masuk'],0,5),
            'nama' => $r['nama_siswa'],
            'kelas' => $r['kelas'],
            'status' => $r['status_kehadiran']
        ];
    }

    echo json_encode([
        'total_siswa' => $total_siswa,
        'total_guru' => $total_guru,
        'hadir_realtime' => $hadir_realtime,
        'mode_gps' => $d_set['mode_gps'],
        'jenjang' => $d_set['jenjang_sekolah'] ?? 'sma',
        'logs' => $logs
    ]);
    exit;
}

// 2. GET ALL USER (Untuk Tab Database & Pencarian)
if ($action == 'get_all_users') {
    $users = [];
    
    // Ambil Siswa
    $q_s = mysqli_query($koneksi, "SELECT nisn as username, nama_siswa as nama, kelas, 'siswa' as role, password FROM tb_siswa WHERE status_aktif='1'");
    while($r = mysqli_fetch_assoc($q_s)) { $users[] = $r; }
    
    // Ambil Guru/Admin
    $q_g = mysqli_query($koneksi, "SELECT username, nama_lengkap as nama, '-' as kelas, level as role, password FROM tb_user");
    while($r = mysqli_fetch_assoc($q_g)) { $users[] = $r; }
    
    echo json_encode($users); exit;
}

// 3. IMPORT DATA DARI EXCEL (Terima JSON dari Frontend)
if ($action == 'import_data') {
    $tipe = $_POST['tipe']; // siswa atau guru
    $data_json = $_POST['data'];
    $data = json_decode($data_json, true);
    
    $sukses = 0;
    
    if ($tipe == 'siswa') {
        foreach ($data as $row) {
            // Format: [Nama, NISN, Password, Kelas]
            $nama = mysqli_real_escape_string($koneksi, $row[0]);
            $nisn = mysqli_real_escape_string($koneksi, $row[1]);
            $pass = mysqli_real_escape_string($koneksi, $row[2]);
            $kelas = mysqli_real_escape_string($koneksi, $row[3]);
            
            if($nisn) {
                $cek = mysqli_query($koneksi, "SELECT nisn FROM tb_siswa WHERE nisn='$nisn'");
                if(mysqli_num_rows($cek) > 0) {
                    mysqli_query($koneksi, "UPDATE tb_siswa SET nama_siswa='$nama', password='$pass', kelas='$kelas' WHERE nisn='$nisn'");
                } else {
                    mysqli_query($koneksi, "INSERT INTO tb_siswa (nama_siswa, nisn, password, kelas) VALUES ('$nama', '$nisn', '$pass', '$kelas')");
                }
                $sukses++;
            }
        }
    // GANTI BAGIAN IMPORT GURU DENGAN INI:
    } else if ($tipe == 'guru') {
        foreach ($data as $row) {
            // Format Excel: [Nama, NIP, Password, Kelas Wali (Opsional)]
            $nama = mysqli_real_escape_string($koneksi, $row[0]);
            $nip = mysqli_real_escape_string($koneksi, $row[1]);
            $pass = mysqli_real_escape_string($koneksi, $row[2]);
            $wali = isset($row[3]) ? mysqli_real_escape_string($koneksi, $row[3]) : ''; // Kolom ke-4
            
            if($nip) {
                $cek = mysqli_query($koneksi, "SELECT username FROM tb_user WHERE username='$nip'");
                if(mysqli_num_rows($cek) > 0) {
                    // Update User Lama
                    mysqli_query($koneksi, "UPDATE tb_user SET nama_lengkap='$nama', password='$pass', kelas_wali='$wali' WHERE username='$nip'");
                } else {
                    // Insert User Baru
                    mysqli_query($koneksi, "INSERT INTO tb_user (nama_lengkap, username, password, level, kelas_wali) VALUES ('$nama', '$nip', '$pass', 'guru', '$wali')");
                }
                $sukses++;
            }
        }
    }
    
    echo json_encode(['status' => 'success', 'pesan' => "Berhasil proses $sukses data."]); exit;
}

// 4. CRUD USER MANUAL (Simpan/Edit/Hapus)
if ($action == 'simpan_manual') {
    $role = $_POST['role'];
    $u = $_POST['username'];
    $n = $_POST['nama'];
    $p = $_POST['password'];
    $k = $_POST['kelas'] ?? '-';
    
    if ($role == 'siswa') {
        $cek = mysqli_query($koneksi, "SELECT nisn FROM tb_siswa WHERE nisn='$u'");
        if(mysqli_num_rows($cek) > 0) { echo json_encode(['status'=>'error', 'pesan'=>'NISN sudah ada!']); exit; }
        $q = "INSERT INTO tb_siswa (nama_siswa, nisn, password, kelas) VALUES ('$n', '$u', '$p', '$k')";
    } else {
        $cek = mysqli_query($koneksi, "SELECT username FROM tb_user WHERE username='$u'");
        if(mysqli_num_rows($cek) > 0) { echo json_encode(['status'=>'error', 'pesan'=>'NIP sudah ada!']); exit; }
        $q = "INSERT INTO tb_user (nama_lengkap, username, password, level) VALUES ('$n', '$u', '$p', 'guru')";
    }
    
    if(mysqli_query($koneksi, $q)) echo json_encode(['status'=>'success']);
    else echo json_encode(['status'=>'error', 'pesan'=>mysqli_error($koneksi)]);
    exit;
}

if ($action == 'hapus_user') {
    $u = $_POST['username'];
    mysqli_query($koneksi, "DELETE FROM tb_siswa WHERE nisn='$u'");
    mysqli_query($koneksi, "DELETE FROM tb_user WHERE username='$u'");
    echo json_encode(['status'=>'success']); exit;
}

// 5. MANAJEMEN MAPEL
if ($action == 'get_mapel') {
    $q = mysqli_query($koneksi, "SELECT * FROM tb_mapel ORDER BY nama_mapel ASC");
    $d=[]; while($r=mysqli_fetch_assoc($q)){ $d[]=$r; } echo json_encode($d); exit;
}
if ($action == 'tambah_mapel') {
    $nama = $_POST['nama'];
    mysqli_query($koneksi, "INSERT INTO tb_mapel (nama_mapel) VALUES ('$nama')");
    echo json_encode(['status'=>'success']); exit;
}
if ($action == 'hapus_mapel') {
    $id = $_POST['id'];
    mysqli_query($koneksi, "DELETE FROM tb_mapel WHERE id_mapel='$id'");
    echo json_encode(['status'=>'success']); exit;
}

// 6. MENU MANAGER (App Builder) - UPDATE FITUR LENGKAP
if ($action == 'get_menu') {
    // Ambil semua menu, urutkan biar rapi
    $q = mysqli_query($koneksi, "SELECT * FROM tb_menu ORDER BY target_user ASC, created_at ASC");
    $d=[]; while($r=mysqli_fetch_assoc($q)){ $d[]=$r; } echo json_encode($d); exit;
}

if ($action == 'simpan_menu') {
    $id = $_POST['id_menu'] ?? ''; // Cek apakah ini mode Edit atau Baru
    $t = $_POST['target']; 
    $j = $_POST['judul']; 
    $i = $_POST['icon']; 
    $l = $_POST['link']; 
    $h = $_POST['highlight']=='true'?1:0;

    if (!empty($id)) {
        // MODE EDIT (UPDATE)
        $q = "UPDATE tb_menu SET target_user='$t', judul='$j', icon='$i', link_url='$l', highlight='$h' WHERE id_menu='$id'";
    } else {
        // MODE BARU (INSERT)
        $q = "INSERT INTO tb_menu (target_user, judul, icon, link_url, highlight, is_active) VALUES ('$t','$j','$i','$l','$h', 1)";
    }

    if(mysqli_query($koneksi, $q)) echo json_encode(['status'=>'success']);
    else echo json_encode(['status'=>'error', 'pesan'=>mysqli_error($koneksi)]);
    exit;
}

if ($action == 'toggle_menu') {
    // Fitur ON/OFF
    $id = $_POST['id'];
    $status = $_POST['status'] == 'true' ? 1 : 0;
    mysqli_query($koneksi, "UPDATE tb_menu SET is_active='$status' WHERE id_menu='$id'");
    echo json_encode(['status'=>'success']); exit;
}

if ($action == 'hapus_menu') {
    $id = $_POST['id'];
    mysqli_query($koneksi, "DELETE FROM tb_menu WHERE id_menu='$id'");
    echo json_encode(['status'=>'success']); exit;
}

if ($action == 'get_menu_detail') {
    // Ambil 1 data untuk ditaruh di form edit
    $id = $_GET['id'];
    $q = mysqli_query($koneksi, "SELECT * FROM tb_menu WHERE id_menu='$id'");
    echo json_encode(mysqli_fetch_assoc($q)); exit;
}

// 7. SETTINGS (GPS + JENJANG)
if ($action == 'get_settings') {
    $q = mysqli_query($koneksi, "SELECT * FROM tb_pengaturan LIMIT 1");
    echo json_encode(mysqli_fetch_assoc($q)); exit;
}
if ($action == 'simpan_settings') {
    $lat = $_POST['lat']; $lng = $_POST['lng']; $rad = $_POST['rad']; 
    $gps = $_POST['gps']=='true'?'strict':'free';
    $kbm = $_POST['kbm']=='true'?1:0; 
    $ref = $_POST['ref']=='true'?1:0;
    
    // UPDATE BARU: Jenjang Sekolah
    $jenjang = $_POST['jenjang']; 
    
    $cek = mysqli_query($koneksi, "SELECT * FROM tb_pengaturan");
    if(mysqli_num_rows($cek)==0) { mysqli_query($koneksi, "INSERT INTO tb_pengaturan (pusat_lat) VALUES ('0')"); }
    
    $q = "UPDATE tb_pengaturan SET pusat_lat='$lat', pusat_lng='$lng', radius_meter='$rad', mode_gps='$gps', kbm_ortu_aktif='$kbm', refleksi_guru_aktif='$ref', jenjang_sekolah='$jenjang'";
    mysqli_query($koneksi, $q);
    echo json_encode(['status'=>'success']); exit;
}

// 8. KELOLA ADMIN
if ($action == 'get_admins') {
    $q = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE level IN ('admin','bk','kurikulum','super')");
    $d=[]; while($r=mysqli_fetch_assoc($q)){ $d[]=$r; } echo json_encode($d); exit;
}
if ($action == 'angkat_admin') {
    $u = $_POST['username']; $role = $_POST['role'];
    $cek = mysqli_query($koneksi, "SELECT username FROM tb_user WHERE username='$u'");
    if(mysqli_num_rows($cek) == 0) { echo json_encode(['status'=>'error', 'pesan'=>'NIP Guru tidak ditemukan!']); exit; }
    
    mysqli_query($koneksi, "UPDATE tb_user SET level='$role' WHERE username='$u'");
    echo json_encode(['status'=>'success']); exit;
}
?>