<?php
session_start();
require "../koneksi.php";

// Cek login dan role admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if ($user['role'] !== 'Admin') {
    header("Location: ../beranda.php");
    exit;
}

// Proses verifikasi HARUS DI ATAS QUERY
if (isset($_GET['verify'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['verify']);
    
    // Debug: Tampilkan informasi
    echo "<script>console.log('Verifying order ID: " . $order_id . "');</script>";
    
    // Update status pesanan
    $query_verify = "UPDATE orders SET status = 'Diproses' WHERE order_id = '$order_id'";
    
    if (mysqli_query($conn, $query_verify)) {
        $_SESSION['success'] = "Pesanan #" . $order_id . " berhasil diverifikasi!";
        // Redirect dengan JavaScript untuk menghindari header issues
        echo "<script>window.location.href = 'verifikasi_dana.php';</script>";
        exit;
    } else {
        $_SESSION['error'] = "Gagal memverifikasi pesanan: " . mysqli_error($conn);
        echo "<script>window.location.href = 'verifikasi_dana.php';</script>";
        exit;
    }
}

// AMBIL PESANAN DANA YANG MENUNGGU VERIFIKASI
$query_pesanan = "SELECT 
    o.order_id,
    o.order_number,
    o.user_id,
    o.total_harga,
    o.tanggal_order,
    o.status,
    u.username,
    a.no_telepon
FROM orders o
JOIN users u ON o.user_id = u.user_id
LEFT JOIN alamat a ON o.user_id = a.user_id AND a.is_default_alamat = 1
WHERE o.metode_pembayaran = 'DANA' 
AND (o.status = 'Menunggu Pembayaran' OR o.status = 'menunggu_verifikasi' OR o.status LIKE '%menunggu%')
ORDER BY o.tanggal_order DESC";

$result_pesanan = mysqli_query($conn, $query_pesanan);

// Hitung jumlah menunggu verifikasi
$query_count = "SELECT COUNT(*) as total FROM orders 
WHERE metode_pembayaran = 'DANA' 
AND (status = 'Menunggu Pembayaran' OR status = 'menunggu_verifikasi' OR status LIKE '%menunggu%')";
$result_count = mysqli_query($conn, $query_count);
$count_data = mysqli_fetch_assoc($result_count);
$menunggu_verifikasi = $count_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi DANA - Admin</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, #FFE9AA 0%, #FFD54F 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar-brand {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .sidebar-brand h4 {
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        
        .sidebar-nav {
            padding: 20px 15px;
        }
        
        .nav-link {
            color: #333;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            text-decoration: none;
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
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        /* Toggle button for mobile */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background-color: #e0540f;
            border: none;
            color: white;
            border-radius: 5px;
            padding: 8px 12px;
        }
        
        /* Overlay for mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
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
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .badge-waiting {
            background-color: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
                padding-top: 70px;
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>

<!-- Overlay for mobile -->
<div class="overlay" id="overlay"></div>

<!-- Mobile Toggle Button -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
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
        <a href="verifikasi_dana.php" class="nav-link active">
            <i class="bi bi-cash-coin"></i> Verifikasi DANA
            <?php if ($menunggu_verifikasi > 0): ?>
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
        <a href="../logout.php" class="nav-link mt-4">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-cash-coin me-2"></i>Verifikasi Pembayaran DANA</h2>
        </div>
        <div class="d-flex align-items-center">
            <span class="me-3 fw-medium"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
            <?php
            require "../function.php";
            $user_id = $_SESSION['user_id'];
            $profile_picture = getProfilePicturePath($user_id);
            ?>
            <a href="profile_admin.php" class="text-decoration-none">
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
                     alt="Profile" 
                     class="rounded-circle border"
                     style="width: 40px; height: 40px; object-fit: cover;"
                     onerror="this.src='../images/profile.jpg'">
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Daftar Pesanan -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">
                <i class="bi bi-clock-history me-2"></i>Pesanan Menunggu Verifikasi
                <span class="badge bg-warning text-dark ms-2"><?= $menunggu_verifikasi ?></span>
            </h5>
            
            <?php if (mysqli_num_rows($result_pesanan) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Kontak</th>
                                <th>Total</th>
                                <th>Tanggal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($pesanan = mysqli_fetch_assoc($result_pesanan)): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($pesanan['order_number']) ?></strong>
                                        <div class="small text-muted">ID: <?= $pesanan['order_id'] ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($pesanan['username']) ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($pesanan['no_telepon'])): ?>
                                            <i class="bi bi-phone me-1"></i><?= htmlspecialchars($pesanan['no_telepon']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-success">
                                        Rp<?= number_format($pesanan['total_harga'], 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($pesanan['tanggal_order'])) ?>
                                        <div class="small text-muted">
                                            <?= date('H:i', strtotime($pesanan['tanggal_order'])) ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="verifikasi_dana.php?verify=<?= $pesanan['order_id'] ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Verifikasi pembayaran pesanan #<?= $pesanan['order_number'] ?>?\n\nPelanggan: <?= htmlspecialchars($pesanan['username']) ?>\nTotal: Rp<?= number_format($pesanan['total_harga'], 0, ',', '.') ?>')">
                                            <i class="bi bi-check-circle me-1"></i> Verifikasi
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Total <?= mysqli_num_rows($result_pesanan) ?> pesanan menunggu verifikasi
                    </small>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-check2-circle" style="font-size: 4rem; color: #28a745;"></i>
                    <h5 class="text-muted mt-3">Tidak ada pesanan</h5>
                    <p class="text-muted">Tidak ada pesanan DANA yang menunggu verifikasi</p>
                    <a href="pesanan.php" class="btn btn-orange mt-2">
                        <i class="bi bi-arrow-left me-1"></i> Lihat Semua Pesanan
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Sidebar toggle for mobile
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    // Toggle sidebar on button click
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    });
    
    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 768) {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        }
    });
    
    // Auto-hide sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    });
    
    // Debug: Log current URL
    console.log('Current URL:', window.location.href);
    console.log('Current Path:', window.location.pathname);
});
</script>

</body>
</html>