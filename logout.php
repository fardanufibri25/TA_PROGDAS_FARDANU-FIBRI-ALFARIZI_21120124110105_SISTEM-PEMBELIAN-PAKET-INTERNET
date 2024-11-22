<?php
session_start();
session_destroy(); // Menghapus semua data sesi
header('Location: login.php'); // Arahkan ke halaman login
exit;
?>
