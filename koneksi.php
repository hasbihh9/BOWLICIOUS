<?php
// koneksi.php
date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';
$dbname = 'bowlicious_db';
$username = 'root'; // Sesuaikan saat diupload ke hosting
$password = '';     // Sesuaikan saat diupload ke hosting

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal. Pastikan server database berjalan.");
}
?>