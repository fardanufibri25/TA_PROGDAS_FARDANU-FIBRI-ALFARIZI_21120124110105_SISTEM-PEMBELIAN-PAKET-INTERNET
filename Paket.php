<?php
class Paket {
    public $id;
    public $nama;
    public $internet;
    public $harga;

    public function __construct($id, $nama, $internet, $harga) {
        $this->id = $id;
        $this->nama = $nama;
        $this->internet = $internet;
        $this->harga = $harga;
    }

    public function getNamaPaket() {
        return $this->nama;
    }

    public function getHarga() {
        return $this->harga;
    }

    public function display() {
        return $this->nama . " - " . $this->internet . "GB - Rp" . number_format($this->harga, 0, ',', '.');
    }
}
?>
