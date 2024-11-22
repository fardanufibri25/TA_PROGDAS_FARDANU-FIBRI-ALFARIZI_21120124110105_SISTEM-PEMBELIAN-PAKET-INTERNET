<?php
// Proses register user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($phone) || empty($password)) {
        header('Location: register.php?error=Harap isi semua field!');
        exit;
    }

    // Simpan ke database
    $conn = new mysqli('localhost', 'root', '', 'tugas_akhir');
    if ($conn->connect_error) {
        die('Koneksi gagal: ' . $conn->connect_error);
    }

    // Cek apakah nomor telepon sudah terdaftar
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        header('Location: register.php?error=Nomor telepon sudah terdaftar!');
        exit;
    }

    // Jika valid, simpan user baru
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (phone, password, pulsa) VALUES (?, ?, 1000000)");
    $stmt->bind_param("ss", $phone, $hashedPassword);

    if ($stmt->execute()) {
        // Jika berhasil, redirect ke login.php
        header('Location: login.php?register=success');
        exit;
    } else {
        header('Location: register.php?error=Terjadi kesalahan. Coba lagi.');
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
