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

// Ambil semua produk dari database untuk ditampilkan
$query_produk = "SELECT * FROM products ORDER BY nama, varian, ukuran";
$result_produk = mysqli_query($conn, $query_produk);

// Proses form tambah stok
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $tambah_stok = mysqli_real_escape_string($conn, $_POST['tambah_stok']);
    
    // Validasi input
    $errors = [];
    
    if (empty($product_id)) $errors[] = "Produk harus dipilih!";
    if (!is_numeric($tambah_stok) || $tambah_stok <= 0) $errors[] = "Jumlah stok harus berupa angka positif!";
    
    if (empty($errors)) {
        // Ambil stok saat ini
        $query_current = "SELECT stok FROM products WHERE id = '$product_id'";
        $result_current = mysqli_query($conn, $query_current);
        $current = mysqli_fetch_assoc($result_current);
        $current_stok = $current['stok'];
        
        // Hitung stok baru
        $new_stok = $current_stok + $tambah_stok;
        
        // Update stok produk
        $query_update = "UPDATE products SET stok = '$new_stok' WHERE id = '$product_id'";
        
        if (mysqli_query($conn, $query_update)) {
            $_SESSION['success'] = "Stok berhasil ditambahkan! Stok sekarang: " . $new_stok . " pcs";
            header("Location: tambah_produk.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambahkan stok: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Stok Produk - Chilova Admin</title>
    
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
        
        /* Styling untuk badge varian */
        .badge-original {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .badge-daun-jeruk {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-keju {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .product-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .stok-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .current-stock {
            font-weight: bold;
            color: #198754;
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
        <a href="verifikasi.php" class="nav-link">
            <i class="bi bi-cash-coin"></i> Verifikasi DANA
            <?php if ($menunggu_verifikasi > 0): ?>
            <span class="badge bg-danger float-end"><?= $menunggu_verifikasi ?></span>
            <?php endif; ?>
        </a>
        <a href="produk.php" class="nav-link">
            <i class="bi bi-box-seam"></i> Produk
        </a>
        <a href="tambah_produk.php" class="nav-link active">
            <i class="bi bi-plus-circle"></i> Tambah Stok
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
<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-plus-circle me-2"></i>Tambah Stok Produk</h2>
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

    <!-- Alert Notifikasi -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Form Tambah Stok -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-4">
                <i class="bi bi-cart-plus me-2"></i>Tambah Stok Produk
            </h5>
            
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="product_id" class="form-label">Pilih Produk *</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">-- Pilih Produk --</option>
                            <?php while ($produk = mysqli_fetch_assoc($result_produk)): 
                                $badge_class = '';
                                switch($produk['varian']) {
                                    case 'Original': $badge_class = 'badge-original'; break;
                                    case 'Daun-Jeruk': $badge_class = 'badge-daun-jeruk'; break;
                                    case 'Keju': $badge_class = 'badge-keju'; break;
                                    default: $badge_class = 'bg-secondary';
                                }
                            ?>
                                <option value="<?= $produk['id'] ?>">
                                    <?= htmlspecialchars($produk['nama']) ?> - 
                                    <?= htmlspecialchars($produk['varian']) ?> - 
                                    <?= htmlspecialchars($produk['ukuran']) ?> 
                                    (Stok: <?= $produk['stok'] ?> pcs)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="tambah_stok" class="form-label">Jumlah Stok yang Ditambahkan *</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="tambah_stok" name="tambah_stok" 
                                   min="1" max="1000" value="" required>
                            <span class="input-group-text">pcs</span>
                        </div>
                        <div class="form-text">Masukkan jumlah stok yang ingin ditambahkan</div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="produk.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Produk
                    </a>
                    <button type="submit" class="btn btn-orange">
                        <i class="bi bi-save me-1"></i> Tambah Stok
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Produk -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">
                <i class="bi bi-list-check me-2"></i>Daftar Produk Tersedia
            </h5>
            
            <?php 
            // Reset pointer result
            mysqli_data_seek($result_produk, 0);
            ?>
            
            <?php if (mysqli_num_rows($result_produk) > 0): ?>
                <div class="row">
                    <?php while ($produk = mysqli_fetch_assoc($result_produk)): 
                        $badge_class = '';
                        switch($produk['varian']) {
                            case 'Original': $badge_class = 'badge-original'; break;
                            case 'Daun-Jeruk': $badge_class = 'badge-daun-jeruk'; break;
                            case 'Keju': $badge_class = 'badge-keju'; break;
                            default: $badge_class = 'bg-secondary';
                        }
                    ?>
                        <div class="col-md-6 mb-3">
                            <div class="product-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($produk['nama']) ?></h6>
                                        <p class="mb-1">
                                            <span class="badge <?= $badge_class ?> me-2">
                                                <?= htmlspecialchars($produk['varian']) ?>
                                            </span>
                                            <span class="badge bg-light text-dark">
                                                <?= htmlspecialchars($produk['ukuran']) ?>
                                            </span>
                                        </p>
                                        <p class="mb-1">Rp<?= number_format($produk['harga']) ?></p>
                                    </div>
                                    <div class="text-end">
                                        <div class="current-stock"><?= $produk['stok'] ?> pcs</div>
                                        <div class="stok-info">Stok tersedia</div>
                                    </div>
                                </div>
                                <div class="mt-2 text-end">
                                    <small class="text-muted">ID: #<?= $produk['id'] ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="mt-3 text-muted">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        Total <?= mysqli_num_rows($result_produk) ?> produk
                    </small>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-box-seam" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h5 class="text-muted mt-3">Belum ada produk</h5>
                    <p class="text-muted">Silakan tambah produk baru terlebih dahulu</p>
                    <a href="produk.php" class="btn btn-orange btn-sm mt-2">
                        <i class="bi bi-plus-circle me-2"></i>Kelola Produk
                    </a>
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

// Auto-select produk jika ada parameter URL
const urlParams = new URLSearchParams(window.location.search);
const productId = urlParams.get('id');
if (productId) {
    document.getElementById('product_id').value = productId;
}
</script>

</body>
</html>