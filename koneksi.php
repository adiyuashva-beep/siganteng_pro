<?php
// FILE: koneksi.php
// Ini adalah KUNCI INGGRIS buat nyambung ke Database

$host = "127.0.0.1";
$user = "root";      // User standar XAMPP (biasanya root)
$pass = "";          // Password standar XAMPP (biasanya kosong)
$db   = "db_siganteng"; // Nama database yang tadi kita buat

// Perintah nyambung
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek kalau gagal nyambung
if (!$koneksi) {
    die("Gagal Terhubung ke Database: " . mysqli_connect_error());
} 
?>