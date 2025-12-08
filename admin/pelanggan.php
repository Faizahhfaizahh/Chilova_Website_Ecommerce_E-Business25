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

// Pesanan menunggu verifikasi
$query_verifikasi = "SELECT COUNT(*) as total FROM orders WHERE metode_pembayaran = 'DANA' AND status = 'menunggu_verifikasi'";
$result_verifikasi = mysqli_query($conn, $query_verifikasi);
$menunggu_verifikasi = mysqli_fetch_assoc($result_verifikasi)['total'] ?? 0;

// Ambil data pelanggan
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query untuk ambil data pelanggan dengan produk yang dipesan
$query_pelanggan = "SELECT 
    u.user_id,
    u.username,
    a.no_telepon,
    a.nama_penerima,
    COUNT(DISTINCT o.order_id) as total_pesanan,
    SUM(o.total_harga) as total_pembelian,
    GROUP_CONCAT(DISTINCT p.nama SEPARATOR ', ') as produk_dipesan
FROM users u
LEFT JOIN orders o ON u.user_id = o.user_id
LEFT JOIN order_item oi ON o.order_id = oi.order_id
LEFT JOIN products p ON oi.product_id = p.id
LEFT JOIN alamat a ON u.user_id = a.user_id AND a.is_default_alamat = 1
WHERE u.role = 'User'";

if (!empty($search)) {
    $query_pelanggan .= " AND (u.username LIKE '%$search%' OR a.no_telepon LIKE '%$search%' OR a.nama_penerima LIKE '%$search%')";
}

$query_pelanggan .= " GROUP BY u.user_id ORDER BY u.user_id DESC";
$result_pelanggan = mysqli_query($conn, $query_pelanggan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pelanggan - Chilova Admin</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
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
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .badge-pesanan {
            background-color: #e3f2fd;
            color: #1565c0;
            font-size: 0.8rem;
        }
        
        .produk-list {
            max-height: 80px;
            overflow-y: auto;
            font-size: 0.85rem;
        }
        
        .produk-item {
            display: inline-block;
            background: #f8f9fa;
            padding: 2px 8px;
            border-radius: 4px;
            margin: 2px;
            border-left: 3px solid #e0540f;
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
        <a href="pelanggan.php" class="nav-link active">
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
<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people me-2"></i>Pelanggan</h2>
        <div>
            <span class="me-3"><?= $_SESSION['username'] ?? 'Admin' ?></span>
            <?php
            require "../function.php";
            $user_id = $_SESSION['user_id'];
            $profile_picture = getProfilePicturePath($user_id);
            ?>
            <a href="profile_admin.php">
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
                    alt="Profile" 
                    class="rounded-circle border" 
                    style="width: 40px; height: 40px; object-fit: cover;"
                    onerror="this.src='../images/profile.jpg'">
            </a>
        </div>
    </div>

    <!-- Pencarian -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari username atau nama..." 
                               value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-orange">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <?php if (!empty($search)): ?>
                        <a href="pelanggan.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Pelanggan -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Daftar Pelanggan</h5>
            
            <?php if (mysqli_num_rows($result_pelanggan) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Username</th>
                                <th>Nama</th>
                                <th>Telepon</th>
                                <th class="text-center">Pesanan</th>
                                <th>Produk yang Dipesan</th>
                                <th class="text-end">Total Belanja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($pelanggan = mysqli_fetch_assoc($result_pelanggan)): 
                                // Format produk yang dipesan
                                $produk_dipesan = '';
                                if (!empty($pelanggan['produk_dipesan'])) {
                                    $produk_array = explode(', ', $pelanggan['produk_dipesan']);
                                    $produk_unik = array_unique($produk_array);
                                    $produk_dipesan = implode(', ', $produk_unik);
                                }
                            ?>
                                <tr>
                                    <td class="fw-bold"><?= $pelanggan['user_id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($pelanggan['username']) ?></strong>
                                    </td>
                                    <td>
                                        <?= !empty($pelanggan['nama_penerima']) ? htmlspecialchars($pelanggan['nama_penerima']) : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pelanggan['no_telepon'])): ?>
                                            <i class="bi bi-phone me-1"></i><?= htmlspecialchars($pelanggan['no_telepon']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($pelanggan['total_pesanan'] > 0): ?>
                                            <span class="badge bg-primary rounded-pill">
                                                <?= $pelanggan['total_pesanan'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="produk-list">
                                            <?php if (!empty($produk_dipesan)): ?>
                                                <?php 
                                                $produk_list = explode(', ', $produk_dipesan);
                                                foreach ($produk_list as $produk): 
                                                    if (!empty(trim($produk))):
                                                ?>
                                                    <span class="produk-item"><?= htmlspecialchars($produk) ?></span>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        <?php if ($pelanggan['total_pembelian'] > 0): ?>
                                            Rp<?= number_format($pelanggan['total_pembelian']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Total <?= mysqli_num_rows($result_pelanggan) ?> pelanggan
                    </small>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-people" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h5 class="text-muted mt-3">Belum ada pelanggan</h5>
                    <p class="text-muted">Tidak ada pelanggan yang ditemukan</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Mobile sidebar toggle
if (window.innerWidth < 768) {
    const sidebar = document.querySelector('.sidebar');
    const header = document.querySelector('.main-content h2');
    
    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'btn btn-orange mb-3';
    toggleBtn.innerHTML = '<i class="bi bi-list"></i> Menu';
    toggleBtn.onclick = function() {
        sidebar.classList.toggle('d-none');
    };
    
    header.parentNode.insertBefore(toggleBtn, header);
}
</script>

</body>
</html>