<?php
// admin.php
session_start();

$admin_password = "admin";

// Proses Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Proses Login
$error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['is_admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error_msg = "Kata sandi salah. Silakan coba lagi.";
    }
}

// halaman login jika belum login
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="foto_produk/logo-bowlicious.png" type="image/x-icon" />
        <title>Login Admin | BOWLICIOUS</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body { font-family: 'Inter', sans-serif; background-color: #191919; height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; color: #fff; }
            .login-box { background: #FFFFFF; color: #191919; padding: 3rem; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center; }
            .login-box h2 { font-family: 'Oswald', sans-serif; font-size: 2rem; margin-bottom: 0.5rem; color: #F28C38; letter-spacing: 1px; }
            .login-box p { color: #666; margin-bottom: 2rem; font-size: 0.95rem; }
            .form-group { text-align: left; margin-bottom: 1.5rem; }
            .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem; }
            .password-wrapper { position: relative; }
            .password-wrapper input { width: 100%; padding: 0.8rem 2.8rem 0.8rem 0.8rem; border: 1px solid #ccc; border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 1rem; box-sizing: border-box; }
            .password-wrapper input:focus { outline: none; border-color: #F28C38; }
            .toggle-password { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 1.05rem; transition: 0.2s; }
            .toggle-password:hover { color: #F28C38; }
            .btn-login { background: #F28C38; color: #fff; border: none; padding: 0.8rem; width: 100%; border-radius: 6px; font-family: 'Oswald', sans-serif; font-size: 1.1rem; cursor: pointer; transition: 0.3s; letter-spacing: 1px; }
            .btn-login:hover { background: #d9772a; }
            .error { color: #dc3545; font-size: 0.85rem; margin-bottom: 1rem; background: #ffe6e6; padding: 8px; border-radius: 4px; border-left: 4px solid #dc3545; text-align: left;}
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>BOWLICIOUS ADMIN</h2>
            <p>Masukkan kata sandi untuk mengakses data.</p>
            <?php if ($error_msg): ?>
                <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error_msg ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required autofocus placeholder="••••••••">
                        <i class="fas fa-eye toggle-password" id="togglePassword" title="Lihat/Sembunyikan Sandi"></i>
                    </div>
                </div>
                <button type="submit" class="btn-login">MASUK</button>
            </form>
        </div>

        <script>
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        </script>
    </body>
    </html>
<?php
    exit;
}

// proses ubah pesanan
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmtUpdate = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmtUpdate->execute([$new_status, $order_id]);
        // Refresh halaman agar data terbaru langsung muncul
        header("Location: admin.php");
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Gagal memperbarui status!');</script>";
    }
}

// panel admin
try {
    $stmt = $pdo->query("
        SELECT o.id AS order_id, o.order_code, o.customer_name, o.whatsapp_number, o.pickup_date, o.total_price, o.notes, o.status,
               o.created_at, p.name AS product_name, oi.quantity
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Gagal mengambil data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="foto_produk/logo-bowlicious.png" type="image/x-icon" />
    <title>Admin Dashboard | BOWLICIOUS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-black: #191919;
            --color-orange: #F28C38;
            --color-cream: #F5F1E8;
            --color-white: #FFFFFF;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--color-cream); margin: 0; padding: 2rem; color: var(--color-black); }
        .admin-container { max-width: 1300px; margin: 0 auto; background: var(--color-white); padding: 2.5rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 3px solid var(--color-orange); padding-bottom: 1.5rem; }
        .header-title { display: flex; align-items: center; gap: 15px; }
        .header-title i { font-size: 2rem; color: var(--color-orange); }
        h1 { font-family: 'Oswald', sans-serif; margin: 0; font-size: 2.2rem; letter-spacing: 1px; }
        .header-actions { display: flex; gap: 10px; }
        .btn-action { color: var(--color-white); text-decoration: none; padding: 0.8rem 1.2rem; border-radius: 6px; font-family: 'Oswald', sans-serif; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s ease; font-size: 0.9rem;}
        .btn-home { background: var(--color-black); }
        .btn-home:hover { background: #333; }
        .btn-logout { background: #dc3545; }
        .btn-logout:hover { background: #c82333; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.95rem; }
        th, td { padding: 1.2rem 1rem; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
        th { background-color: var(--color-black); color: var(--color-white); font-weight: 500; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; white-space: nowrap; }
        tr:hover { background-color: #fcfcfc; }
        .badge-qty { background: var(--color-orange); color: var(--color-white); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; margin-left: 8px; }
        .notes-text { color: #666; font-style: italic; font-size: 0.85rem; max-width: 200px; }
        .wa-link { color: #25D366; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 5px; }
        .wa-link:hover { text-decoration: underline; }
        .status-select { padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-family: 'Inter', sans-serif; font-size: 0.85rem; font-weight: 600; cursor: pointer; outline: none; }
        .status-select:focus { border-color: var(--color-orange); }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="header">
            <div class="header-title">
                <i class="fas fa-clipboard-list"></i>
                <h1>PANEL ADMIN PESANAN</h1>
            </div>
            <div class="header-actions">
                <a href="index.html" class="btn-action btn-home" target="_blank"><i class="fas fa-external-link-alt"></i> Lihat Web</a>
                <a href="admin.php?logout=1" class="btn-action btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </div>

        <?php if (count($orders) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Waktu Pesan</th>
                            <th>Pelanggan</th>
                            <th>WhatsApp</th>
                            <th>Pesanan</th>
                            <th>Tgl Pengambilan</th>
                            <th>Catatan</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $row): ?>
                            <tr>
                                <td><strong style="color: var(--color-orange);"><?= htmlspecialchars($row['order_code']) ?></strong></td>
                                <td><?= date('d/m/Y, H:i', strtotime($row['created_at'])) ?></td>
                                <td><strong><?= htmlspecialchars($row['customer_name']) ?></strong></td>
                                <td>
                                    <a href="https://wa.me/<?= htmlspecialchars($row['whatsapp_number']) ?>" class="wa-link" target="_blank">
                                        <i class="fab fa-whatsapp" style="font-size: 1.1rem;"></i> <?= htmlspecialchars($row['whatsapp_number']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($row['product_name']) ?> <span class="badge-qty"><?= $row['quantity'] ?> porsi</span></td>
                                <td><strong><?= date('d M Y', strtotime($row['pickup_date'])) ?></strong></td>
                                <td class="notes-text"><?= htmlspecialchars($row['notes']) ?: '-' ?></td>
                                <td style="font-weight: 600;">Rp<?= number_format($row['total_price'], 0, ',', '.') ?></td>
                                <td>
                                    <!-- Form Dropdown Status -->
                                    <form method="POST" action="" style="margin: 0;">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                        <select name="status" class="status-select" onchange="this.form.submit()" 
                                            style="background-color: <?= $row['status'] == 'selesai' ? '#d4edda' : ($row['status'] == 'dibatalkan' ? '#f8d7da' : '#fff3cd') ?>;">
                                            <option value="menunggu_konfirmasi" <?= $row['status'] == 'menunggu_konfirmasi' ? 'selected' : '' ?>>Menunggu</option>
                                            <option value="dikonfirmasi" <?= $row['status'] == 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                            <option value="selesai" <?= $row['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                            <option value="dibatalkan" <?= $row['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 2rem;">
                <i class="fas fa-inbox" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3 style="color: #777; font-family: 'Inter', sans-serif;">Belum ada pesanan yang masuk.</h3>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>