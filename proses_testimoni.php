<?php
require 'koneksi.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    
    $is_anonymous = (int)($_POST['is_anonymous'] ?? 0);

    if(empty($name) || $rating < 1 || $rating > 5 || empty($comment)) {
        echo json_encode(["status" => "error", "message" => "Data ulasan tidak lengkap."]);
        exit;
    }

    if ($is_anonymous === 1) {
        $name = "Pelanggan BOWLICIOUS (Anonim)"; 
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO testimonials (customer_name, rating, message, status) VALUES (?, ?, ?, 'approved')");
        // Gunakan variabel $name yang sudah diproses
        $stmt->execute([htmlspecialchars($name), $rating, htmlspecialchars($comment)]);
        
        echo json_encode(["status" => "success", "message" => "Terima kasih! Ulasan Anda telah berhasil dipublikasikan."]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Terjadi kesalahan saat menyimpan ulasan."]);
    }
}
?>