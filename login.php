<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validasi nomor telepon
    if (empty($phone) || !preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = "Masukkan nomor telepon yang valid (10-15 angka).";
    } elseif (empty($password)) {
        $error = "Password tidak boleh kosong.";
    } else {
        // Cek di database
        $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['phone'] = $user['phone'];
                header('Location: index.php');
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Nomor telepon tidak terdaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> <!-- Pastikan ini ada untuk menghubungkan CSS -->
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Login</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST" class="p-4 border rounded shadow">            
            <div class="mb-3">
                <label for="phone" class="form-label">Nomor Telepon:</label>
                <input type="text" id="phone" name="phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3">Belum punya akun? <a href="register.php">Daftar di sini</a>.</p>
    </div>
</body>
</html>