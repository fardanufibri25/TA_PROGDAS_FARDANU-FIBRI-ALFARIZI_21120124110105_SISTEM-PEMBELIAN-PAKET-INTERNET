<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password']; // Ambil password dari form

    // Validasi nomor telepon
    if (empty($phone) || !preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = "Masukkan nomor telepon yang valid (10-15 angka).";
    } elseif (empty($password) || strlen($password) < 6) { // Validasi password
        $error = "Password harus diisi dan minimal 6 karakter.";
    } else {
        // Koneksi ke database
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
            $error = "Nomor telepon sudah terdaftar.";
        } else {
            // Jika valid, simpan user baru
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash password
            $stmt = $conn->prepare("INSERT INTO users (phone, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $phone, $hashedPassword);

            if ($stmt->execute()) {
                header('Location: login.php?success=Registrasi berhasil!');
                exit;
            } else {
                $error = "Terjadi kesalahan. Coba lagi.";
            }
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> <!-- Pastikan ini ada untuk menghubungkan CSS -->
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Registrasi</h1>
        <form method="POST" class="p-4 border rounded shadow">
            <div class="mb-3">
                <label for="phone" class="form-label">Nomor Telepon:</label>
                <input type="tel" id="phone" name="phone" class="form-control" 
                       pattern="[0-9]{10,15}" 
                       title="Masukkan nomor telepon yang valid (10-15 angka)" 
                       required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" 
                       minlength="6" 
                       title="Password minimal 6 karakter" 
                       required>
            </div>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary w-100">Daftar</button>
        </form>
        <p class="text-center mt-3">Sudah punya akun? <a href="login.php">Masuk di sini</a>.</p>
    </div>
</body>
</html>