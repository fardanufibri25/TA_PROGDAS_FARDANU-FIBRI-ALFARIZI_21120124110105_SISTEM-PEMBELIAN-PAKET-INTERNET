<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'tugas_akhir';

// Membuat koneksi ke database
$conn = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
