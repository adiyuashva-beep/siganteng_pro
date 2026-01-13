<?php
// File: api_import.php
// UPDATE TERBARU: Sesuai Format Excel Mas Adi (Siswa & Guru)

include 'koneksi.php';

// Terima data JSON dari Javascript
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'pesan' => 'Data kosong']);
    exit;
}

$tipe = $data['tipe'];
$isi_data = $data['data'];
$berhasil = 0; 
$gagal = 0;

// ==========================================
// 1. IMPORT DATA SISWA
// ==========================================
if ($tipe == 'siswa') {
    foreach ($isi_data as $row) {
        // MAPPING KOLOM (Sesuai Gambar Excel Siswa):
        // [0] nama      : ABDUL...
        // [1] username  : 008538... (Ini NISN)
        // [2] password  : 123456 (KITA ABAIKAN, Pake NISN)
        // [3] kelas     : XII G
        // [4] role      : SISWA
        
        // Cek data kosong
        if(empty($row[0]) || empty($row[1])) continue;

        $nama  = mysqli_real_escape_string($koneksi, $row[0]);
        $nisn  = mysqli_real_escape_string($koneksi, $row[1]); 
        
        // Bersihkan NISN (hapus spasi/tanda petik kalau ada)
        $nisn  = str_replace([' ', "'", '"', '-'], '', $nisn);

        $kelas = mysqli_real_escape_string($koneksi, $row[3]); // Ambil kolom ke-4 (Index 3)
        
        // KESEPAKATAN: Password Siswa = NISN
        $password = $nisn; 
        
        // Deteksi Jurusan Otomatis (Opsional)
        $jurusan = "UMUM"; // Default Kurikulum Merdeka
        $kelas_upper = strtoupper($kelas);
        if(strpos($kelas_upper, "MIPA") !== false) $jurusan = "MIPA";
        else if(strpos($kelas_upper, "IPS") !== false) $jurusan = "IPS";
        else if(strpos($kelas_upper, "BAHASA") !== false) $jurusan = "BAHASA";

        // Cek apakah data sudah ada (berdasarkan NISN)
        $cek = mysqli_query($koneksi, "SELECT id_siswa FROM tb_siswa WHERE nisn='$nisn'");
        
        if (mysqli_num_rows($cek) > 0) {
            // Kalau ada -> UPDATE datanya (Password diset ulang jadi NISN)
            $q = "UPDATE tb_siswa SET nama_siswa='$nama', kelas='$kelas', jurusan='$jurusan', password='$password' WHERE nisn='$nisn'";
        } else {
            // Kalau belum ada -> INSERT BARU
            // Kolom NIS kita samakan dengan NISN dulu biar tidak error
            $q = "INSERT INTO tb_siswa (nis, nisn, nama_siswa, kelas, jurusan, password, status_aktif) 
                  VALUES ('$nisn', '$nisn', '$nama', '$kelas', '$jurusan', '$password', '1')";
        }

        if (mysqli_query($koneksi, $q)) $berhasil++; else $gagal++;
    }
}

// ==========================================
// 2. IMPORT DATA GURU
// ==========================================
elseif ($tipe == 'guru') {
    foreach ($isi_data as $row) {
        // MAPPING KOLOM (Sesuai Gambar Excel Guru):
        // [0] Nama Lengkap : Afri Lismaya...
        // [1] NIP          : 199104... (Jadi Username)
        // [2] PASWORD      : 123456 (KITA ABAIKAN, Default Sistem 123456)
        // [3] ROLE         : Guru
        // [4] WALI KELAS   : X B
        
        if(empty($row[0]) || empty($row[1])) continue;

        $nama = mysqli_real_escape_string($koneksi, $row[0]);
        
        // Bersihkan NIP dari spasi, strip, titik (Biar jadi angka bersih)
        $username = mysqli_real_escape_string($koneksi, str_replace([' ', '-', '.', "'"], '', $row[1])); 
        
        // Ambil Role (Jika kosong default 'guru')
        $role_excel = isset($row[3]) ? strtoupper($row[3]) : 'GURU';
        
        // Mapping Role Excel ke Database MySQL
        $level = 'guru'; // Default
        if(strpos($role_excel, 'ADMIN') !== false) $level = 'admin';
        else if(strpos($role_excel, 'BK') !== false) $level = 'bk';
        else if(strpos($role_excel, 'KURIKULUM') !== false) $level = 'kurikulum';
        else if(strpos($role_excel, 'KARYAWAN') !== false || strpos($role_excel, 'TU') !== false) $level = 'guru'; 
        
        // KESEPAKATAN: Password Guru Default = 123456
        $password = "123456"; 

        // Cek apakah guru sudah ada (berdasarkan NIP/Username)
        $cek = mysqli_query($koneksi, "SELECT id_user FROM tb_user WHERE username='$username'");

        if (mysqli_num_rows($cek) > 0) {
            // Update nama & level jika sudah ada
            $q = "UPDATE tb_user SET nama_lengkap='$nama', level='$level' WHERE username='$username'";
        } else {
            // Insert baru
            $q = "INSERT INTO tb_user (username, password, nama_lengkap, level) VALUES ('$username', '$password', '$nama', '$level')";
        }

        if (mysqli_query($koneksi, $q)) $berhasil++; else $gagal++;
    }
}

// Kirim Laporan ke Admin
echo json_encode([
    'status' => 'sukses', 
    'pesan' => "Selesai! Berhasil: $berhasil, Gagal: $gagal"
]);
?>