<?php
// admin.php
session_start();
require 'koneksi.php';

// ==========================================
// 1. PROSES LOGOUT
// ==========================================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// ==========================================
// 2. PROSES LOGIN DARI DATABASE
// ==========================================
$error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error_msg = "Username dan kata sandi wajib diisi.";
    } else {
        try {
            $stmtUser = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
            $stmtUser->execute([$username]);
            $admin = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['is_admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header("Location: admin.php");
                exit;
            } else {
                $error_msg = "Username atau kata sandi salah.";
            }
        } catch (Exception $e) {
            $error_msg = "Terjadi kesalahan koneksi database.";
        }
    }
}

// ==========================================
// 3. HALAMAN LOGIN (Jika belum login)
// ==========================================
if (!isset($_SESSION['is_admin_logged_in']) || $_SESSION['is_admin_logged_in'] !== true) {
?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="foto produk/logo-bowlicious.png" type="image/x-icon" />
        <title>Login Admin | BOWLICIOUS</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body { font-family: 'Inter', sans-serif; background-color: #191919; height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; color: #fff; }
            .login-box { background: #FFFFFF; color: #191919; padding: 3rem; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center; }
            .login-box h2 { font-family: 'Oswald', sans-serif; font-size: 2rem; margin-bottom: 0.5rem; color: #F28C38; letter-spacing: 1px; }
            .login-box p { color: #666; margin-bottom: 2rem; font-size: 0.95rem; }
            .form-group { text-align: left; margin-bottom: 1.25rem; }
            .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem; }
            .form-group input[type="text"],
            .password-wrapper input { width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 1rem; box-sizing: border-box; }
            .password-wrapper { position: relative; }
            .password-wrapper input { padding-right: 2.8rem; }
            .form-group input:focus { outline: none; border-color: #F28C38; }
            .toggle-password { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888; font-size: 1.05rem; transition: 0.2s; }
            .toggle-password:hover { color: #F28C38; }
            .btn-login { background: #F28C38; color: #fff; border: none; padding: 0.85rem; width: 100%; border-radius: 6px; font-family: 'Oswald', sans-serif; font-size: 1.1rem; cursor: pointer; transition: 0.3s; letter-spacing: 1px; margin-top: 0.5rem; }
            .btn-login:hover { background: #d9772a; }
            .error { color: #dc3545; font-size: 0.85rem; margin-bottom: 1rem; background: #ffe6e6; padding: 10px; border-radius: 4px; border-left: 4px solid #dc3545; text-align: left; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>BOWLICIOUS ADMIN</h2>
            <p>Masuk menggunakan akun administrator.</p>
            <?php if ($error_msg): ?>
                <div class="error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required placeholder="••••••••">
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

// ==========================================
// 4. PROSES GANTI KATA SANDI ADMIN
// ==========================================
$pw_alert = '';
$pw_alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $old_pw = $_POST['old_password'] ?? '';
    $new_pw = $_POST['new_password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $admin_id = $_SESSION['admin_id'] ?? 0;

    if ($old_pw === '' || $new_pw === '' || $confirm_pw === '') {
        $pw_alert = "Semua kolom kata sandi wajib diisi.";
        $pw_alert_type = "error";
    } elseif (strlen($new_pw) < 6) {
        $pw_alert = "Kata sandi baru minimal harus 6 karakter.";
        $pw_alert_type = "error";
    } elseif ($new_pw !== $confirm_pw) {
        $pw_alert = "Konfirmasi kata sandi baru tidak cocok.";
        $pw_alert_type = "error";
    } else {
        try {
            $stmtCheck = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
            $stmtCheck->execute([$admin_id]);
            $currentAdmin = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($currentAdmin && password_verify($old_pw, $currentAdmin['password'])) {
                $hashedNewPw = password_hash($new_pw, PASSWORD_DEFAULT);
                $stmtUpdPw = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmtUpdPw->execute([$hashedNewPw, $admin_id]);

                $pw_alert = "Kata sandi berhasil diperbarui dan terenkripsi di database!";
                $pw_alert_type = "success";
            } else {
                $pw_alert = "Kata sandi lama Anda salah.";
                $pw_alert_type = "error";
            }
        } catch (Exception $e) {
            $pw_alert = "Gagal memperbarui kata sandi: " . $e->getMessage();
            $pw_alert_type = "error";
        }
    }
}

// ==========================================
// 5. PROSES UBAH STATUS PESANAN
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $redirect_query = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
    
    try {
        $stmtUpdate = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmtUpdate->execute([$new_status, $order_id]);
        header("Location: admin.php" . $redirect_query);
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Gagal memperbarui status!');</script>";
    }
}

// ==========================================
// 6. LOGIKA FILTER & PENGAMBILAN DATA
// ==========================================
$filter_bulan = trim($_GET['bulan'] ?? '');
$filter_pelanggan = trim($_GET['pelanggan'] ?? '');

$conditions = [];
$params = [];

if ($filter_bulan !== '') {
    $conditions[] = "DATE_FORMAT(o.created_at, '%Y-%m') = :bulan";
    $params[':bulan'] = $filter_bulan;
}

if ($filter_pelanggan !== '') {
    $conditions[] = "o.customer_name LIKE :pelanggan";
    $params[':pelanggan'] = '%' . $filter_pelanggan . '%';
}

$where_sql = '';
if (count($conditions) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $conditions);
}

try {
    $sql = "
        SELECT o.id AS order_id, o.order_code, o.customer_name, o.whatsapp_number, o.pickup_date, o.total_price, o.notes, o.status,
               o.created_at, p.name AS product_name, oi.quantity
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        $where_sql
        ORDER BY o.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Gagal mengambil data: " . $e->getMessage());
}

// Hitung Total Keseluruhan Harga & Total Porsi (mengecualikan yang dibatalkan)
$total_keseluruhan = 0;
$total_porsi = 0;
foreach ($orders as $item) {
    if ($item['status'] !== 'dibatalkan') {
        $total_keseluruhan += (float)$item['total_price'];
        $total_porsi += (int)$item['quantity'];
    }
}

// ==========================================
// 7. PROSES EXPORT KE EXCEL (.xls)
// ==========================================
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $label_bulan = $filter_bulan !== '' ? '_' . $filter_bulan : '_Semua_Waktu';
    $filename = "Laporan_Pesanan_BOWLICIOUS" . $label_bulan . "_" . date('Ymd') . ".xls";

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    $status_labels = [
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
        'dikonfirmasi' => 'Dikonfirmasi',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan'
    ];

    $grand_total = 0;
    ?>
    <html xmlns:x="urn:schemas-microsoft-com:office:excel">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <table border="1">
            <thead>
                <tr>
                    <th colspan="11" style="font-size: 16px; font-weight: bold; background-color: #F28C38; color: #FFFFFF; height: 35px;">
                        LAPORAN DATA PESANAN - BOWLICIOUS
                    </th>
                </tr>
                <tr>
                    <th colspan="11" style="font-size: 12px; background-color: #F5F1E8; text-align: left;">
                        Periode: <?= $filter_bulan !== '' ? date('F Y', strtotime($filter_bulan . '-01')) : 'Semua Bulan' ?>
                        <?= $filter_pelanggan !== '' ? ' | Filter Pelanggan: ' . htmlspecialchars($filter_pelanggan) : '' ?>
                        | Dicetak pada: <?= date('d/m/Y H:i') ?>
                    </th>
                </tr>
                <tr style="background-color: #191919; color: #FFFFFF; font-weight: bold;">
                    <th>No</th>
                    <th>Kode Pesanan</th>
                    <th>Waktu Pesan</th>
                    <th>Nama Pelanggan</th>
                    <th>Nomor WhatsApp</th>
                    <th>Menu Pesanan</th>
                    <th>Jumlah (Porsi)</th>
                    <th>Tanggal Pengambilan</th>
                    <th>Catatan</th>
                    <th>Total Harga (Rp)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php $no = 1; foreach ($orders as $row): ?>
                        <?php 
                            if ($row['status'] !== 'dibatalkan') {
                                $grand_total += (float)$row['total_price'];
                            }
                        ?>
                        <tr>
                            <td align="center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['order_code']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td style="mso-number-format:'\@';"><?= htmlspecialchars($row['whatsapp_number']) ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td align="center"><?= (int)$row['quantity'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['pickup_date'])) ?></td>
                            <td><?= htmlspecialchars($row['notes'] ?: '-') ?></td>
                            <td align="right"><?= (float)$row['total_price'] ?></td>
                            <td><?= $status_labels[$row['status']] ?? $row['status'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold; background-color: #FFF3CD;">
                        <td colspan="9" align="right">TOTAL KESELURUHAN HARGA (Pesanan Aktif/Selesai):</td>
                        <td align="right"><?= $grand_total ?></td>
                        <td></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="11" align="center">Tidak ada data pesanan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}

$export_params = $_GET;
$export_params['export'] = 'excel';
$export_url = 'admin.php?' . http_build_query($export_params);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="foto produk/logo-bowlicious.png" type="image/x-icon" />
    <title>Admin Dashboard | BOWLICIOUS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
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
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 3px solid var(--color-orange); padding-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .header-title { display: flex; align-items: center; gap: 15px; }
        .header-title i { font-size: 2rem; color: var(--color-orange); }
        h1 { font-family: 'Oswald', sans-serif; margin: 0; font-size: 2.2rem; letter-spacing: 1px; }
        .admin-badge { font-size: 0.85rem; color: #666; margin-top: 2px; }
        .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-action { color: var(--color-white); text-decoration: none; padding: 0.8rem 1.2rem; border-radius: 6px; font-family: 'Oswald', sans-serif; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s ease; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer; }
        .btn-excel { background: #1D6F42; }
        .btn-excel:hover { background: #155331; }
        .btn-pw { background: #495057; }
        .btn-pw:hover { background: #343a40; }
        .btn-home { background: var(--color-black); }
        .btn-home:hover { background: #333; }
        .btn-logout { background: #dc3545; }
        .btn-logout:hover { background: #c82333; }

        /* Notifikasi Ganti Password */
        .alert-banner {
            padding: 1rem 1.2rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }

        /* Kartu Ringkasan Total */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .summary-card {
            background: #f9f8f6;
            border: 1px solid #e5e0d8;
            border-left: 5px solid var(--color-orange);
            padding: 1.1rem 1.4rem;
            border-radius: 8px;
        }
        .summary-card.highlight {
            background: var(--color-black);
            color: var(--color-white);
            border-color: var(--color-black);
            border-left: 5px solid var(--color-orange);
        }
        .summary-card span {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .summary-card strong {
            font-family: 'Oswald', sans-serif;
            font-size: 1.7rem;
            letter-spacing: 0.5px;
        }
        .summary-card.highlight strong {
            color: var(--color-orange);
        }

        /* Styling Bar Filter & Pencarian */
        .filter-bar {
            background-color: #f9f8f6;
            border: 1px solid #e5e0d8;
            padding: 1.2rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 1rem;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            min-width: 200px;
        }
        .filter-group label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #555;
            letter-spacing: 0.5px;
        }
        .filter-group input {
            padding: 0.65rem 0.9rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            background: var(--color-white);
        }
        .filter-group input:focus {
            outline: none;
            border-color: var(--color-orange);
        }
        .filter-buttons {
            display: flex;
            gap: 8px;
        }
        .btn-filter {
            background: var(--color-orange);
            color: var(--color-white);
            border: none;
            padding: 0.7rem 1.3rem;
            border-radius: 6px;
            font-family: 'Oswald', sans-serif;
            font-size: 0.95rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-filter:hover { background: #d9772a; }
        .btn-reset {
            background: #6c757d;
            color: var(--color-white);
            text-decoration: none;
            padding: 0.7rem 1.1rem;
            border-radius: 6px;
            font-family: 'Oswald', sans-serif;
            font-size: 0.95rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-reset:hover { background: #5a6268; }
        .filter-info {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 0.8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; font-size: 0.95rem; }
        th, td { padding: 1.2rem 1rem; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
        th { background-color: var(--color-black); color: var(--color-white); font-weight: 500; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; white-space: nowrap; }
        tbody tr:hover { background-color: #fcfcfc; }

        tfoot tr {
            background-color: #FFF3CD;
            border-top: 2px solid var(--color-orange);
        }
        tfoot td {
            padding: 1.2rem 1rem;
            font-weight: 700;
            color: var(--color-black);
            border-bottom: none;
        }
        .grand-total-label {
            text-align: right;
            font-family: 'Oswald', sans-serif;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .grand-total-value {
            font-family: 'Oswald', sans-serif;
            font-size: 1.35rem;
            color: #d9772a;
            white-space: nowrap;
        }

        .badge-qty { 
            background: var(--color-orange); 
            color: var(--color-white); 
            padding: 4px 10px; 
            border-radius: 20px; 
            font-size: 0.8rem; 
            font-weight: 600; 
            display: inline-block;
            white-space: nowrap;
            margin-top: 4px;
        }
        .notes-text { color: #666; font-style: italic; font-size: 0.85rem; max-width: 200px; }
        .wa-link { color: #25D366; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 5px; }
        .wa-link:hover { text-decoration: underline; }
        .status-select { padding: 6px; border-radius: 4px; border: 1px solid #ccc; font-family: 'Inter', sans-serif; font-size: 0.85rem; font-weight: 600; cursor: pointer; outline: none; }
        .status-select:focus { border-color: var(--color-orange); }

        /* Modal Ganti Password */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            z-index: 999;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }
        .modal-overlay.active { display: flex; }
        .modal-card {
            background: #fff;
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            position: relative;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--color-orange);
            padding-bottom: 0.8rem;
            margin-bottom: 1.5rem;
        }
        .modal-header h3 {
            font-family: 'Oswald', sans-serif;
            margin: 0;
            font-size: 1.4rem;
            letter-spacing: 0.5px;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: #777;
        }
        .modal-close:hover { color: #191919; }
        .modal-group { margin-bottom: 1rem; }
        .modal-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; }
        .modal-group input {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            box-sizing: border-box;
        }
        .modal-group input:focus { outline: none; border-color: var(--color-orange); }
        .btn-save-pw {
            width: 100%;
            background: var(--color-orange);
            color: #fff;
            border: none;
            padding: 0.8rem;
            border-radius: 6px;
            font-family: 'Oswald', sans-serif;
            font-size: 1rem;
            letter-spacing: 1px;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        .btn-save-pw:hover { background: #d9772a; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="header">
            <div class="header-title">
                <i class="fas fa-clipboard-list"></i>
                <div>
                    <h1>PANEL ADMIN PESANAN</h1>
                    <div class="admin-badge">Login sebagai: <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin') ?></strong> (Terhubung Database)</div>
                </div>
            </div>
            <div class="header-actions">
                <a href="<?= htmlspecialchars($export_url) ?>" class="btn-action btn-excel"><i class="fas fa-file-excel"></i> Export Excel</a>
                <button type="button" class="btn-action btn-pw" id="openPwModal"><i class="fas fa-key"></i> Ubah Sandi</button>
                <a href="index.html" class="btn-action btn-home" target="_blank"><i class="fas fa-external-link-alt"></i> Lihat Web</a>
                <a href="admin.php?logout=1" class="btn-action btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </div>

        <?php if ($pw_alert !== ''): ?>
            <div class="alert-banner <?= $pw_alert_type === 'success' ? 'alert-success' : 'alert-error' ?>">
                <i class="fas <?= $pw_alert_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <span><?= htmlspecialchars($pw_alert) ?></span>
            </div>
        <?php endif; ?>

        <!-- Ringkasan Total Pesanan & Pendapatan -->
        <div class="summary-cards">
            <div class="summary-card">
                <span><i class="fas fa-receipt"></i> Jumlah Transaksi</span>
                <strong><?= count($orders) ?> Pesanan</strong>
            </div>
            <div class="summary-card">
                <span><i class="fas fa-bowl-rice"></i> Total Porsi Terjual</span>
                <strong><?= $total_porsi ?> Porsi</strong>
            </div>
            <div class="summary-card highlight">
                <span><i class="fas fa-wallet"></i> Total Keseluruhan Harga</span>
                <strong>Rp<?= number_format($total_keseluruhan, 0, ',', '.') ?></strong>
            </div>
        </div>

        <!-- Form Filter Per Bulan & Pencarian Pelanggan -->
        <form method="GET" action="admin.php" class="filter-bar">
            <div class="filter-group">
                <label for="pelanggan"><i class="fas fa-search"></i> Cari Nama Pelanggan</label>
                <input type="text" id="pelanggan" name="pelanggan" value="<?= htmlspecialchars($filter_pelanggan) ?>" placeholder="Ketik nama pelanggan...">
            </div>
            <div class="filter-group">
                <label for="bulan"><i class="fas fa-calendar-alt"></i> Filter Per Bulan</label>
                <input type="month" id="bulan" name="bulan" value="<?= htmlspecialchars($filter_bulan) ?>">
            </div>
            <div class="filter-buttons">
                <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Terapkan</button>
                <?php if ($filter_bulan !== '' || $filter_pelanggan !== ''): ?>
                    <a href="admin.php" class="btn-reset"><i class="fas fa-times"></i> Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="filter-info">
            <span>
                Menampilkan <strong><?= count($orders) ?></strong> pesanan
                <?php if ($filter_pelanggan !== ''): ?>
                    untuk pelanggan <strong>"<?= htmlspecialchars($filter_pelanggan) ?>"</strong>
                <?php endif; ?>
                <?php if ($filter_bulan !== ''): ?>
                    pada bulan <strong><?= date('F Y', strtotime($filter_bulan . '-01')) ?></strong>
                <?php endif; ?>
            </span>
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
                    <tfoot>
                        <tr>
                            <td colspan="7" class="grand-total-label">
                                Total Keseluruhan Harga (<?= $total_porsi ?> Porsi Aktif/Selesai):
                            </td>
                            <td colspan="2" class="grand-total-value">
                                Rp<?= number_format($total_keseluruhan, 0, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 2rem;">
                <i class="fas fa-search" style="font-size: 3.5rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3 style="color: #777; font-family: 'Inter', sans-serif;">Tidak ada pesanan yang sesuai dengan pencarian/filter.</h3>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Ubah Kata Sandi -->
    <div class="modal-overlay" id="pwModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fas fa-key" style="color: var(--color-orange);"></i> UBAH KATA SANDI ADMIN</h3>
                <button type="button" class="modal-close" id="closePwModal">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                <div class="modal-group">
                    <label for="old_password">Kata Sandi Lama</label>
                    <input type="password" id="old_password" name="old_password" required placeholder="Masukkan sandi saat ini">
                </div>
                <div class="modal-group">
                    <label for="new_password">Kata Sandi Baru (Min. 6 karakter)</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="Masukkan sandi baru">
                </div>
                <div class="modal-group">
                    <label for="confirm_password">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Ulangi sandi baru">
                </div>
                <button type="submit" class="btn-save-pw">SIMPAN KATA SANDI BARU</button>
            </form>
        </div>
    </div>

    <script>
        const pwModal = document.getElementById('pwModal');
        const openPwModal = document.getElementById('openPwModal');
        const closePwModal = document.getElementById('closePwModal');

        openPwModal.addEventListener('click', () => pwModal.classList.add('active'));
        closePwModal.addEventListener('click', () => pwModal.classList.remove('active'));
        pwModal.addEventListener('click', (e) => {
            if (e.target === pwModal) pwModal.classList.remove('active');
        });
    </script>
</body>
</html>