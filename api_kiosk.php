<?php
// File: api_kiosk.php
session_start();
include 'koneksi.php';
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$action = $data['action'] ?? '';

if ($action == 'scan_kiosk') {
    $nisn = $data['nisn'];
    $tgl = date('Y-m-d');
    $jam_skrg = date('H:i:s');
    $jam_int = (int)date('H'); // Ambil jam saja (0-23)

    // 1. Cek Siswa Ada Gak?
    $q_siswa = mysqli_query($koneksi, "SELECT nama_siswa FROM tb_siswa WHERE nisn='$nisn' AND status_aktif='1'");
    if (mysqli_num_rows($q_siswa) == 0) {
        echo json_encode(['status' => 'error', 'pesan' => 'Siswa Tidak Dikenal']);
        exit;
    }
    $d_siswa = mysqli_fetch_assoc($q_siswa);
    $nama = $d_siswa['nama_siswa'];

    // 2. Tentukan Mode (Masuk / Pulang) Berdasarkan Jam & Riwayat
    // Aturan: < Jam 11 = Masuk, >= Jam 11 = Pulang
    $batas_pulang = 11; 
    
    $q_cek = mysqli_query($koneksi, "SELECT * FROM tb_absensi WHERE nisn='$nisn' AND tanggal='$tgl'");
    $d_absen = mysqli_fetch_assoc($q_cek);

    // LOGIKA ABSENSI CERDAS
    if ($d_absen) {
        // Sudah pernah absen hari ini
        if ($d_absen['jam_pulang'] != NULL && $d_absen['jam_pulang'] != "00:00:00") {
            echo json_encode(['status' => 'error', 'pesan' => 'Anda Sudah Pulang!']);
            exit;
        }

        // Kalau belum pulang, dan jam sekarang > batas pulang
        if ($jam_int >= $batas_pulang) {
            // PROSES PULANG
            mysqli_query($koneksi, "UPDATE tb_absensi SET jam_pulang='$jam_skrg', lokasi_lng='KIOSK' WHERE nisn='$nisn' AND tanggal='$tgl'");
            echo json_encode(['status' => 'success', 'tipe' => 'Pulang', 'nama' => $nama, 'pesan' => "Hati-hati di jalan, $nama"]);
        } else {
            // Kalau masih pagi tapi udah absen masuk
            echo json_encode(['status' => 'error', 'pesan' => 'Sudah Absen Masuk!']);
        }

    } else {
        // Belum pernah absen hari ini
        if ($jam_int >= $batas_pulang) {
            // Telat banget atau cuma mau absen pulang? (Tergantung kebijakan)
            // Disini kita anggap kalau baru scan sore, dia dianggap Masuk Terlambat & Pulang Sekaligus? 
            // Atau tetap dianggap Masuk? 
            // Default: Dianggap Masuk (Terlambat)
            mysqli_query($koneksi, "INSERT INTO tb_absensi (nisn, tanggal, jam_masuk, status_kehadiran, lokasi_lat, lokasi_lng) VALUES ('$nisn', '$tgl', '$jam_skrg', 'Terlambat', 'KIOSK', 'KIOSK')");
            echo json_encode(['status' => 'success', 'tipe' => 'Masuk', 'nama' => $nama, 'pesan' => "Selamat Datang (Terlambat), $nama"]);
        } else {
            // PROSES MASUK NORMAL
            mysqli_query($koneksi, "INSERT INTO tb_absensi (nisn, tanggal, jam_masuk, status_kehadiran, lokasi_lat, lokasi_lng) VALUES ('$nisn', '$tgl', '$jam_skrg', 'Hadir', 'KIOSK', 'KIOSK')");
            echo json_encode(['status' => 'success', 'tipe' => 'Masuk', 'nama' => $nama, 'pesan' => "Selamat Belajar, $nama"]);
        }
    }
}
?>