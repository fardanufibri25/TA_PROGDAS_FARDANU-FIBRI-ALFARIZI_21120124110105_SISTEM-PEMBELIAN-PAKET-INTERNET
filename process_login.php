<?php
// Proses login user
session_start(); // Mulai session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($phone) || empty($password)) {
        header('Location: login.php?error=Harap isi semua field!');
        exit;
    }

    // Koneksi ke database
    require_once 'db.php';
    // Cari user berdasarkan nomor telepon
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Login berhasil: simpan data ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['phone'] = $user['phone'];

            // Arahkan ke halaman utama
            header('Location: index.php');
            exit;
        } else {
            // Password salah
            header('Location: login.php?error=Password salah!');
            exit;
        }
    } else {
        // Nomor telepon tidak ditemukan
        header('Location: login.php?error=Nomor telepon tidak terdaftar!');
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>