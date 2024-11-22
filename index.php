<?php
session_start();
require_once 'db.php';
require_once 'Paket.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil informasi pengguna
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT phone, pulsa FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Ambil total jumlah internet yang sudah dibeli
$stmt = $conn->prepare("SELECT SUM(jumlah_internet) AS total_internet FROM transaksi WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$totalInternet = $result->fetch_assoc()['total_internet'] ?? 0; // Jika tidak ada, set ke 0

// Daftar paket
$daftarPaket = [
    new Paket(1, "Paket Hemat", 5, 25000),
    new Paket(2, "Paket Biasa", 10, 50000),
    new Paket(3, "Paket Sultan", 30, 100000),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paketId = $_POST['paket'];

    // Pilih paket
    $selectedPaket = null;
    foreach ($daftarPaket as $paket) {
        if ($paket->id == $paketId) {
            $selectedPaket = $paket;
            break;
        }
    }

    if ($selectedPaket) {
        if ($user['pulsa'] >= $selectedPaket->getHarga()) {
            // Potong pulsa
            $newPulsa = $user['pulsa'] - $selectedPaket->getHarga();
            $updateStmt = $conn->prepare("UPDATE users SET pulsa = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $newPulsa, $userId);
            $updateStmt->execute();

             // Simpan transaksi ke database
             $insertStmt = $conn->prepare("INSERT INTO transaksi (user_id, paket_id, jumlah_internet, tanggal_transaksi) VALUES (?, ?, ?, NOW())");
             $insertStmt->bind_param("iii", $userId, $selectedPaket->id, $selectedPaket->internet);
             $insertStmt->execute();
            $pesan = "Transaksi berhasil! Paket " . $selectedPaket->getNamaPaket() . " telah dibeli.";
            $user['pulsa'] = $newPulsa; // Update di sesi saat ini
        } else {
            $pesan = "Saldo pulsa tidak mencukupi.";
        }
    } else {
        $pesan = "Pilih paket dengan benar!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pembelian Paket Internet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> <!-- Pastikan ini ada untuk menghubungkan CSS -->
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Pembelian Paket Internet</h1>
        <h2 class="text-center">Selamat Datang</h2>
        <div class="mb-4">
            <p><strong>Nomor Telepon:</strong> <?= $user['phone']; ?></p>
            <p><strong>Pulsa Anda:</strong> Rp<?= number_format($user['pulsa'], 0, ',', '.'); ?></p>
            <p><strong>Total Internet:</strong> <?= $totalInternet; ?> GB</p> <!-- Menampilkan total internet -->
        </div>
        <form method="POST" class="p-4 border rounded shadow">
            <div class="mb-3">
                <label for="paket" class="form-label">Pilih Paket:</label>
                <select id="paket" name="paket" class="form-select" required>
                    <?php foreach ($daftarPaket as $paket): ?>
                        <option value="<?= $paket->id; ?>">
                            <?= $paket->display(); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary flex-grow-1">Beli Paket</button>
            </div>
        </form>
        <div class="mt-3 d-flex justify-content-end">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
        <?php if (isset($pesan)): ?>
            <div class="alert alert-info mt-3"><?= $pesan; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>