<?php

class Transaksi
{
    private $paket;
    private $userId;
    private $conn;

    public function __construct($paket, $userId, $conn)
    {
        $this->paket = $paket;
        $this->userId = $userId;
        $this->conn = $conn;
    }

    public function prosesTransaksi()
{
    // Ambil data pengguna dari database
    $stmt = $this->conn->prepare("SELECT pulsa FROM users WHERE id = ?");
    $stmt->bind_param("i", $this->userId);
    if (!$stmt->execute()) {
        return "Kesalahan saat mengambil data pengguna: " . $stmt->error;
    }
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        return "Pengguna tidak ditemukan.";
    }

    $pulsa = $user['pulsa'];
    $hargaPaket = $this->paket->getHarga();

    if ($pulsa < $hargaPaket) {
        return "Pulsa tidak cukup untuk membeli paket ini.";
    }

    // Potong pulsa pengguna
    $newPulsa = $pulsa - $hargaPaket;
    $updateStmt = $this->conn->prepare("UPDATE users SET pulsa = ? WHERE id = ?");
    $updateStmt->bind_param("ii", $newPulsa, $this->userId);
    if (!$updateStmt->execute()) {
        return "Terjadi kesalahan saat memproses transaksi: " . $updateStmt->error;
    }

    return "Pembelian paket " . $this->paket->getNamaPaket() . " berhasil!";
}
}
