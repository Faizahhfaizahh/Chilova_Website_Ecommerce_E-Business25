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

if ($user['role'] !== 'Admin') {
    header("Location: ../beranda.php");
    exit;
}

// Ambil ID pesanan dari parameter URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: pesanan.php");
    exit;
}

$order_id = $_GET['id'];

// Ambil data pesanan utama
$query_pesanan = "SELECT o.*, u.username
                  FROM orders o 
                  JOIN users u ON o.user_id = u.user_id 
                  WHERE o.order_id = '$order_id'";
$result_pesanan = mysqli_query($conn, $query_pesanan);

if (mysqli_num_rows($result_pesanan) == 0) {
    header("Location: pesanan.php");
    exit;
}

$pesanan = mysqli_fetch_assoc($result_pesanan);

// Tentukan status badge
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

// Ambil detail item pesanan
$query_items = "SELECT oi.*, p.nama, p.harga, p.gambar
                FROM order_item oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = '$order_id'";
$result_items = mysqli_query($conn, $query_items);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $pesanan['order_number'] ?> - Chilova Admin</title>
    
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
        
        .content-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
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
        
        .info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #e0540f;
        }
        
        .product-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .product-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
            color: black;
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
        
        .total-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 20px;
            border: 2px solid #dee2e6;
        }
        
        .status-badge-large {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: 600;
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
        <a href="beranda_admin.php" class="nav-link">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="pesanan.php" class="nav-link">
            <i class="bi bi-cart-check"></i> Pesanan
        </a>
        <a href="verifikasi_dana.php" class="nav-link">
            <i class="bi bi-cash-coin"></i> Verifikasi DANA
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
        <div>
            <h2><i class="bi bi-file-text me-2"></i>Detail Pesanan</h2>
            <nav aria-label="breadcrumb" class="mt-2">

            </nav>
        </div>
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

    <!-- Informasi Utama Pesanan -->
    <div class="content-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="m-0">
                <i class="bi bi-receipt me-2"></i>Pesanan #<?= $pesanan['order_number'] ?>
            </h4>
            <div>
                <span class="status-badge-large <?= $status_class ?>"><?= $status_text ?></span>
            </div>
        </div>
        
        <!-- Alert untuk pesan sukses/error -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="row">
            <!-- Informasi Pelanggan -->
            <div class="col-md-6">
                <div class="info-card">
                    <h5><i class="bi bi-person-circle me-2"></i>Informasi Pelanggan</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Nama:</strong></p>
                            <p class="mb-0"><strong>User ID:</strong></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><?= htmlspecialchars($pesanan['username']) ?></p>
                            <p class="mb-0"><?= $pesanan['user_id'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informasi Pesanan -->
            <div class="col-md-6">
                <div class="info-card">
                    <h5><i class="bi bi-cart-check me-2"></i>Informasi Pesanan</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Tanggal Pesan:</strong></p>
                            <p class="mb-2"><strong>Metode Pembayaran:</strong></p>
                            <p class="mb-0"><strong>No. Pesanan:</strong></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><?= date('d/m/Y H:i', strtotime($pesanan['tanggal_order'])) ?></p>
                            <p class="mb-2">
                                <?php if ($pesanan['metode_pembayaran'] == 'DANA'): ?>
                                    <span class="badge bg-primary">DANA</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">COD</span>
                                <?php endif; ?>
                            </p>
                            <p class="mb-0"><?= $pesanan['order_number'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alamat Pengiriman -->
        <?php if (!empty($pesanan['alamat_pengiriman'])): ?>
        <div class="info-card">
            <h5><i class="bi bi-geo-alt me-2"></i>Alamat Pengiriman</h5>
            <hr>
            <p class="mb-0"><?= nl2br(htmlspecialchars($pesanan['alamat_pengiriman'])) ?></p>
        </div>
        <?php endif; ?>
        
        <!-- Catatan Pesanan -->
        <?php if (!empty($pesanan['catatan'])): ?>
        <div class="info-card">
            <h5><i class="bi bi-chat-text me-2"></i>Catatan Pesanan</h5>
            <hr>
            <p class="mb-0"><?= nl2br(htmlspecialchars($pesanan['catatan'])) ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Detail Produk -->
    <div class="content-container">
        <h4 class="mb-4"><i class="bi bi-box-seam me-2"></i>Produk yang Dipesan</h4>
        
        <?php if (mysqli_num_rows($result_items) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50%">Produk</th>
                            <th>Harga Satuan</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($result_items)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['gambar']): ?>
                                            <img src="../images/<?= htmlspecialchars($item['gambar']) ?>" 
                                                 alt="<?= htmlspecialchars($item['nama']) ?>" 
                                                 class="me-3 rounded" 
                                                 style="width: 70px; height: 70px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="me-3 rounded bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 70px; height: 70px;">
                                                <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($item['nama']) ?></div>
                                            <small class="text-muted">
                                                Varian: <?= htmlspecialchars($item['varian'] ?? '-') ?><br>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">Rp<?= number_format($item['harga']) ?></td>
                                <td class="align-middle"><?= $item['qty'] ?></td>
                                <td class="align-middle fw-bold text-success">Rp<?= number_format($item['harga'] * $item['qty']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-box" style="font-size: 3rem;"></i>
                <p class="mt-3">Tidak ada produk dalam pesanan ini</p>
            </div>
        <?php endif; ?>
        
        <!-- Total -->
        <div class="row mt-4">
            <div class="col-md-6 offset-md-6">
                <div class="total-box">
                    <h5 class="mb-3">Ringkasan Pembayaran</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal Produk:</span>
                        <span>Rp<?= number_format($pesanan['total_harga']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ongkos Kirim:</span>
                        <span>Rp0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Diskon:</span>
                        <span>Rp0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total Pembayaran:</span>
                        <span class="text-orange">Rp<?= number_format($pesanan['total_harga']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tombol Aksi -->
    <div class="content-container">
        <div class="d-flex justify-content-between">
            <div>
                <a href="pesanan.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pesanan
                </a>
            </div>
            <div class="btn-group">
                <a href="edit_pesanan.php?id=<?= $order_id ?>" class="btn btn-orange">
                    <i class="bi bi-pencil me-1"></i> Edit Status
                </a>
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