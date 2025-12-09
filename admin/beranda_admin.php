<?php
session_start();
require "../koneksi.php";

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Cek apakah user adalah admin
$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Jika bukan admin, redirect ke beranda user
if ($user['role'] !== 'Admin') {
    header("Location: ../beranda.php");
    exit;
}

$today = date('Y-m-d');
$query_today = "SELECT COUNT(*) as total_harga FROM orders WHERE DATE(tanggal_order) = '$today'";
$result_today = mysqli_query($conn, $query_today);
$total_pesanan_hari_ini = mysqli_fetch_assoc($result_today)['total_harga'];

// Pesanan menunggu verifikasi (DANA)
$query_verifikasi = "SELECT COUNT(*) as total_harga FROM orders WHERE metode_pembayaran = 'DANA' AND status = 'menunggu_verifikasi'";
$result_verifikasi = mysqli_query($conn, $query_verifikasi);
$menunggu_verifikasi = mysqli_fetch_assoc($result_verifikasi)['total_harga'];

// Total pendapatan bulan ini
$current_month = date('m');
$current_year = date('Y');
$query_pendapatan = "SELECT SUM(total_harga) as total_harga FROM orders 
                     WHERE MONTH(tanggal_order) = '$current_month' 
                     AND YEAR(tanggal_order) = '$current_year'
                     AND status IN ('diproses', 'dikirim', 'selesai')";
$result_pendapatan = mysqli_query($conn, $query_pendapatan);
$pendapatan_bulan_ini = mysqli_fetch_assoc($result_pendapatan)['total_harga'] ?? 0;

// Total produk
$query_produk = "SELECT COUNT(*) as harga FROM products";
$result_produk = mysqli_query($conn, $query_produk);
$total_produk = mysqli_fetch_assoc($result_produk)['harga'];

// Ambil pesanan terbaru (5 pesanan)
$query_pesanan_terbaru = "SELECT o.*, u.username 
                          FROM orders o 
                          JOIN users u ON o.user_id = u.user_id 
                          ORDER BY tanggal_order DESC 
                          LIMIT 5";
$pesanan_terbaru = mysqli_query($conn, $query_pesanan_terbaru);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Chilova</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #FFE9AA 0%, #FFD54F 100%);
            color: #333;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-brand h4 {
            font-weight: 700;
            color: #333;
        }
        
        .sidebar-nav {
            padding: 0 15px;
        }
        
        .nav-link {
            color: #333;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(224, 84, 15, 0.1);
            color: #e0540f;
        }
        
        .nav-link i {
            width: 25px;
            font-size: 1.1rem;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h2 {
            margin: 0;
            color: #2c3e50;
            font-weight: 700;
        }
        
        .header-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-text {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table th {
            border-top: none;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-waiting {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-process {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        .btn-logout {
            background: rgba(224, 84, 15, 0.1);
            border: 1px solid rgba(224, 84, 15, 0.3);
            color: #e0540f;
        }
        
        .btn-logout:hover {
            background: rgba(224, 84, 15, 0.2);
            color: #e0540f;
            border-color: rgba(224, 84, 15, 0.4);
        }
        
        .text-orange {
            color: #e0540f;
        }
        
        .btn-orange {
            background-color: #e0540f;
            border-color: #e0540f;
            color: white;
        }
        
        .btn-orange:hover {
            background-color: #c2490d;
            border-color: #c2490d;
            color: white;
        }
        
        .btn-outline-orange {
            color: #e0540f;
            border-color: #e0540f;
        }
        
        .btn-outline-orange:hover {
            background-color: #e0540f;
            color: white;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-none d-md-block">
    <div class="sidebar-brand">
        <h4><i class="bi bi-shop me-2"></i>Chilova Admin</h4>
    </div>
    
    <div class="sidebar-nav">
        <a href="beranda_admin.php" class="nav-link active">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="pesanan.php" class="nav-link">
            <i class="bi bi-cart-check"></i> Pesanan
            <?php if ($menunggu_verifikasi > 0): ?>
            <span class="badge bg-danger float-end"><?= $menunggu_verifikasi ?></span>
            <?php endif; ?>
        </a>
        <a href="verifikasi_dana.php" class="nav-link">
            <i class="bi bi-cash-coin"></i> Verifikasi DANA
            <?php if ($menunggu_verifikasi > 0): ?>
            <span class="badge bg-danger float-end"><?= $menunggu_verifikasi ?></span>
            <?php endif; ?>
        </a>
        <a href="produk.php" class="nav-link">
            <i class="bi bi-box-seam"></i> Produk
        </a>
        <a href="pelanggan.php" class="nav-link">
            <i class="bi bi-people"></i> Pelanggan
        </a>
        <a href="profile_admin.php" class="nav-link">
            <i class="bi bi-person"></i> Profil Admin
        </a>
        <a href="../login.php" class="nav-link btn-logout mt-4">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Header -->
    <div class="header">
        <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</h2>
        <div class="header-user">
            <div class="text-end d-flex align-items-center">
                <div class="me-3">
                    <div class="fw-bold"><?= $_SESSION['username'] ?? 'Admin' ?></div>
                    <small class="text-muted">Administrator</small>
                </div>
                <?php
                require "../function.php";
                $user_id = $_SESSION['user_id'];
                $profile_picture = getProfilePicturePath($user_id);
                ?>
                <a href="profile_admin.php" class="text-dark">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
                        alt="Profile" 
                        class="rounded-circle border" 
                        style="width: 45px; height: 45px; object-fit: cover; border-color: #e0540f !important;"
                        onerror="this.src='../images/profile.jpg'">
                </a>
            </div>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(224, 84, 15, 0.1); color: #e0540f;">
                    <i class="bi bi-cart"></i>
                </div>
                <div class="stat-number"><?= $total_pesanan_hari_ini ?></div>
                <div class="stat-text">Pesanan Hari Ini</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(255, 193, 7, 0.1); color: #ff9800;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-number"><?= $menunggu_verifikasi ?></div>
                <div class="stat-text">Menunggu Verifikasi</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(76, 175, 80, 0.1); color: #4caf50;">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-number">Rp<?= number_format($pendapatan_bulan_ini) ?></div>
                <div class="stat-text">Pendapatan Bulan Ini</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: rgba(156, 39, 176, 0.1); color: #9c27b0;">
                    <i class="bi bi-box"></i>
                </div>
                <div class="stat-number"><?= $total_produk ?></div>
                <div class="stat-text">Total Produk</div>
            </div>
        </div>
    </div>

    <!-- Pesanan Terbaru -->
    <div class="table-container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="m-0"><i class="bi bi-list-ul me-2"></i>Pesanan Terbaru</h4>
            <a href="pesanan.php" class="btn btn-outline-orange btn-sm">Lihat Semua</a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($pesanan_terbaru) > 0): ?>
                        <?php while ($pesanan = mysqli_fetch_assoc($pesanan_terbaru)): ?>
                            <?php
                            $status_class = '';
                            $status_text = '';
                            switch ($pesanan['status']) {
                                case 'menunggu_verifikasi':
                                    $status_class = 'badge-waiting';
                                    $status_text = 'Verifikasi';
                                    break;
                                case 'menunggu_pembayaran':
                                    $status_class = 'badge-waiting';
                                    $status_text = 'Bayar';
                                    break;
                                case 'diproses':
                                    $status_class = 'badge-process';
                                    $status_text = 'Diproses';
                                    break;
                                case 'dikirim':
                                    $status_class = 'badge-process';
                                    $status_text = 'Dikirim';
                                    break;
                                case 'selesai':
                                    $status_class = 'badge-success';
                                    $status_text = 'Selesai';
                                    break;
                                default:
                                    $status_class = 'badge-secondary';
                                    $status_text = $pesanan['status'];
                            }
                            ?>
                            <tr>
                                <td><strong><?= $pesanan['order_number'] ?></strong></td>
                                <td><?= $pesanan['username'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($pesanan['tanggal_order'])) ?></td>
                                <td class="fw-bold text-success">Rp<?= number_format($pesanan['total_harga']) ?></td>
                                <td>
                                    <?php if ($pesanan['metode_pembayaran'] == 'DANA'): ?>
                                        <span class="badge bg-primary">DANA</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">COD</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-status <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                                <p class="mt-2">Belum ada pesanan</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Informasi Toko Sederhana -->
    <div class="table-container mt-4">
        <h5 class="mb-4"><i class="bi bi-info-circle me-2"></i>Informasi Toko</h5>
        <div class="row">
            <div class="col-md-12">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-shop me-2"></i>Nama Toko:</span>
                        <strong class="text-orange">Chilova</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-person me-2"></i>Admin:</span>
                        <strong><?= $_SESSION['username'] ?? 'Admin' ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-wallet me-2"></i>Rekening DANA:</span>
                        <strong class="text-success">0877-4577-0076</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Mobile Sidebar Toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth < 768) {
        const sidebar = document.querySelector('.sidebar');
        const header = document.querySelector('.header');
        
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'btn btn-orange mb-3';
        toggleBtn.innerHTML = '<i class="bi bi-list"></i> Menu';
        toggleBtn.onclick = function() {
            sidebar.classList.toggle('d-none');
        };
        
        header.insertBefore(toggleBtn, header.firstChild);
    }
});
</script>

</body>
</html>