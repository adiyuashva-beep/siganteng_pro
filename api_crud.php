<?php
// File: api_crud.php
// UPDATE: Support Mode GPS (Strict/Free)

include 'koneksi.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// 1. DATA SISWA
if ($action == 'get_siswa') {
    $keyword = $_POST['keyword'] ?? '';
    $query = "SELECT * FROM tb_siswa WHERE status_aktif='1' AND (nama_siswa LIKE '%$keyword%' OR nama_siswa LIKE '%$keyword%') ORDER BY kelas ASC, nama_siswa ASC LIMIT 50";
    $result = mysqli_query($koneksi, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) { $data[] = $row; }
    echo json_encode($data);
    exit;
}

// 2. DATA GURU
if ($action == 'get_guru') {
    $query = "SELECT * FROM tb_user ORDER BY level ASC, nama_lengkap ASC";
    $result = mysqli_query($koneksi, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) { $data[] = $row; }
    echo json_encode($data);
    exit;
}

// 3. TAMBAH / HAPUS USER & SISWA
if ($action == 'tambah_siswa') {
    $nama = $_POST['nama']; $nisn = $_POST['nisn']; $kelas = $_POST['kelas'];
    $cek = mysqli_query($koneksi, "SELECT id_siswa FROM tb_siswa WHERE nisn='$nisn'");
    if(mysqli_num_rows($cek) > 0) { echo json_encode(['status' => 'error', 'pesan' => 'NISN sudah ada']); } 
    else {
        $q = "INSERT INTO tb_siswa (nama_siswa, nisn, nis, kelas, jurusan, password, status_aktif) VALUES ('$nama', '$nisn', '$nisn', '$kelas', '".$_POST['jurusan']."', '$nisn', '1')";
        if(mysqli_query($koneksi, $q)) echo json_encode(['status'=>'success']); else echo json_encode(['status'=>'error', 'pesan'=>'Gagal DB']);
    }
    exit;
}
if ($action == 'tambah_guru') {
    $nama = $_POST['nama']; $username = $_POST['username']; $level = $_POST['level']; $pass = $username;
    $cek = mysqli_query($koneksi, "SELECT id_user FROM tb_user WHERE username='$username'");
    if(mysqli_num_rows($cek) > 0) { echo json_encode(['status' => 'error', 'pesan' => 'Username/NIP dipakai']); } 
    else {
        $q = "INSERT INTO tb_user (nama_lengkap, username, password, level) VALUES ('$nama', '$username', '$pass', '$level')";
        if(mysqli_query($koneksi, $q)) echo json_encode(['status'=>'success']); else echo json_encode(['status'=>'error']);
    }
    exit;
}
if ($action == 'hapus_siswa') { mysqli_query($koneksi, "UPDATE tb_siswa SET status_aktif='0' WHERE id_siswa='".$_POST['id']."'"); echo json_encode(['status'=>'success']); exit; }
if ($action == 'hapus_guru') { mysqli_query($koneksi, "DELETE FROM tb_user WHERE id_user='".$_POST['id']."'"); echo json_encode(['status'=>'success']); exit; }
if ($action == 'edit_guru') { mysqli_query($koneksi, "UPDATE tb_user SET level='".$_POST['level']."' WHERE id_user='".$_POST['id']."'"); echo json_encode(['status'=>'success']); exit; }

// ==========================================================
// 4. FITUR SETTINGS GPS (RADIUS & MODE) - UPDATE BARU
// ==========================================================
if ($action == 'get_settings') {
    $q = mysqli_query($koneksi, "SELECT * FROM tb_pengaturan LIMIT 1");
    $d = mysqli_fetch_assoc($q);
    echo json_encode($d); 
    exit;
}

if ($action == 'save_settings') {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $rad = $_POST['radius'];
    $mode = $_POST['mode']; // Tangkap Mode GPS
    
    $q = "UPDATE tb_pengaturan SET pusat_lat='$lat', pusat_lng='$lng', radius_meter='$rad', mode_gps='$mode' WHERE id_pengaturan=1";
    if(mysqli_query($koneksi, $q)) echo json_encode(['status'=>'success']);
    else echo json_encode(['status'=>'error']);
    exit;
}

// ==========================================================
// 5. FITUR APP BUILDER
// ==========================================================
if ($action == 'get_menus') {
    $q = mysqli_query($koneksi, "SELECT * FROM tb_menu ORDER BY id_menu DESC");
    $data = []; while ($row = mysqli_fetch_assoc($q)) { $data[] = $row; }
    echo json_encode($data); exit;
}
if ($action == 'add_menu') {
    $q = "INSERT INTO tb_menu (judul, icon, link_url, highlight, target_user) VALUES ('".$_POST['judul']."', '".$_POST['icon']."', '".$_POST['link']."', '".$_POST['highlight']."', '".$_POST['target']."')";
    if(mysqli_query($koneksi, $q)) echo json_encode(['status'=>'success']); else echo json_encode(['status'=>'error']); exit;
}
if ($action == 'hapus_menu') { mysqli_query($koneksi, "DELETE FROM tb_menu WHERE id_menu='".$_POST['id']."'"); echo json_encode(['status'=>'success']); exit; }
?>