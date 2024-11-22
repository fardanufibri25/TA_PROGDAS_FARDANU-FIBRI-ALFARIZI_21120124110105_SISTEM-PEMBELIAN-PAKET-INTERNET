<?php
session_start();
require_once 'db.php';
require_once 'Transaksi.php';
require_once 'Paket.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userPhone = $_SESSION['phone'];

// Ambil data pengguna dari database
$stmt = $conn->prepare("SELECT phone, pulsa FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Daftar paket internet
$daftarPaket = [
    new Paket(1, "Paket Hemat", 5, 25000),
    new Paket(2, "Paket Biasa", 10, 50000),
    new Paket(3, "Paket Sultan", 30, 100000),
];

// Proses pembelian paket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paketId = $_POST['paket'];
    $selectedPaket = null;

    // Cari paket berdasarkan ID
    foreach ($daftarPaket as $paket) {
        if ($paket->id == $paketId) {
            $selectedPaket = $paket;
            break;
        }
    }

    if ($selectedPaket) {
        if ($user['pulsa'] >= $selectedPaket->getHarga()) {
            // Potong pulsa pengguna
            $newPulsa = $user['pulsa'] - $selectedPaket->getHarga();
            $updateStmt = $conn->prepare("UPDATE users SET pulsa = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $newPulsa, $userId);
            $updateStmt->execute();

            // Simpan transaksi ke database
            $insertStmt = $conn->prepare("INSERT INTO transaksi (user_id, paket_id, jumlah_internet, tanggal_transaksi) VALUES (?, ?, ?, NOW())");
            $insertStmt->bind_param("iii", $userId, $selectedPaket->id, $selectedPaket->internet);
            $insertStmt->execute();

            // Update data pengguna di sesi ini
            $user['pulsa'] = $newPulsa;

            $pesan = "Berhasil membeli " . $selectedPaket->getNamaPaket() . "!";
        } else {
            $pesan = "Pulsa Anda tidak cukup untuk membeli paket ini.";
        }
    } else {
        $pesan = "Paket yang dipilih tidak valid.";
    }
} else {
    $pesan = "Silakan pilih paket dan tekan tombol Beli Paket.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Pembelian Paket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Proses Pembelian Paket Internet</h1>
        <div class="text-center mb-4">
            <p><strong>Nomor Telepon:</strong> <?= $user['phone']; ?></p>
            <p><strong>Pulsa Anda:</strong> Rp<?= number_format($user['pulsa'], 0, ',', '.'); ?></p>
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
            <button type="submit" class="btn btn-primary w-100">Beli Paket</button>
        </form>

        <?php if (isset($pesan)): ?>
            <div class="alert alert-info mt-3"><?= $pesan; ?></div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-secondary">Kembali ke Index</a>
        </div>
    </div>
</body>
</html>