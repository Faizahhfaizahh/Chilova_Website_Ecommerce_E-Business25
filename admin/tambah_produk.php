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

// Ambil data produk berdasarkan ID
$product_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;
$query_produk = "SELECT * FROM products WHERE id = '$product_id'";
$result_produk = mysqli_query($conn, $query_produk);


$produk = mysqli_fetch_assoc($result_produk);

// Proses form edit produk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $varian = mysqli_real_escape_string($conn, $_POST['varian']);
    $ukuran = mysqli_real_escape_string($conn, $_POST['ukuran']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok'] ?? 0);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama)) $errors[] = "Nama produk harus diisi!";
    if (empty($varian)) $errors[] = "Varian harus dipilih!";
    if (empty($ukuran)) $errors[] = "Ukuran harus dipilih!";
    if (empty($harga)) $errors[] = "Harga harus diisi!";
    if (!is_numeric($harga) || $harga <= 0) $errors[] = "Harga harus berupa angka positif!";
    if (!is_numeric($stok) || $stok < 0) $errors[] = "Stok harus berupa angka tidak negatif!";
    
    if (empty($errors)) {
        // Update produk
        $query_update = "UPDATE products SET 
                         nama = '$nama',
                         varian = '$varian',
                         ukuran = '$ukuran',
                         harga = '$harga',
                         stok = '$stok'
                         WHERE id = '$product_id'";
        
        if (mysqli_query($conn, $query_update)) {
            $_SESSION['success'] = "Produk berhasil diperbarui!";
            header("Location: produk.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal memperbarui produk: " . mysqli_error($conn);
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
    <title>Edit Produk - Chilova Admin</title>
    
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
        
        .form-container {
            max-width: 600px;
            margin: 0 auto;
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
        <a href="produk.php" class="nav-link active">
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
<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil me-2"></i>Edit Produk</h2>
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

    <!-- Form Edit Produk -->
    <div class="card">
        <div class="card-body">
            <div class="form-container">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Produk *</label>
                        <input type="text" class="form-control" id="nama" name="nama" 
                               value="<?= htmlspecialchars($produk['nama']) ?>" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="varian" class="form-label">Varian *</label>
                            <select class="form-select" id="varian" name="varian" required>
                                <option value="Original" <?= $produk['varian'] == 'Original' ? 'selected' : '' ?>>Original</option>
                                <option value="Daun-Jeruk" <?= $produk['varian'] == 'Daun-Jeruk' ? 'selected' : '' ?>>Daun Jeruk</option>
                                <option value="Keju" <?= $produk['varian'] == 'Keju' ? 'selected' : '' ?>>Keju</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="ukuran" class="form-label">Ukuran *</label>
                            <select class="form-select" id="ukuran" name="ukuran" required>
                                <option value="Kecil" <?= $produk['ukuran'] == 'Kecil' ? 'selected' : '' ?>>Kecil</option>
                                <option value="Besar" <?= $produk['ukuran'] == 'Besar' ? 'selected' : '' ?>>Besar</option>
                                <option value="Custom" <?= $produk['ukuran'] == 'Custom' ? 'selected' : '' ?>>Custom</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="harga" class="form-label">Harga (Rp) *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga" name="harga" 
                                       value="<?= $produk['harga'] ?>" min="1000" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="stok" class="form-label">Jumlah Stok *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="stok" name="stok" 
                                       value="<?= $produk['stok'] ?? 0 ?>" min="0" required>
                                <span class="input-group-text">pcs</span>
                            </div>
                            <div class="form-text">Jumlah produk yang tersedia</div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="produk.php" class="btn btn-secondary me-md-2">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-orange">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Info Produk -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="bi bi-info-circle me-2"></i>Informasi Produk
            </h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Detail Produk:</h6>
                            <p><strong>ID Produk:</strong> #<?= $produk['id'] ?></p>
                            <p><strong>Varian:</strong> 
                                <span class="badge <?php 
                                    switch($produk['varian']) {
                                        case 'Original': echo 'badge-original'; break;
                                        case 'Daun-Jeruk': echo 'badge-daun-jeruk'; break;
                                        case 'Keju': echo 'badge-keju'; break;
                                        default: echo 'bg-secondary';
                                    }
                                ?>">
                                    <?= htmlspecialchars($produk['varian']) ?>
                                </span>
                            </p>
                            <p><strong>Ukuran:</strong> <?= htmlspecialchars($produk['ukuran']) ?></p>
                            <p><strong>Harga Saat Ini:</strong> <span class="fw-bold text-success">Rp<?= number_format($produk['harga']) ?></span></p>
                            <p><strong>Stok Saat Ini:</strong> <span class="fw-bold"><?= $produk['stok'] ?? 0 ?> pcs</span></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Rekomendasi Stok:</h6>
                            <div class="alert alert-info">
                                <i class="bi bi-lightbulb me-2"></i>
                                <strong>Saran:</strong> Set stok awal sesuai kebutuhan penjualan.<br>
                                <small class="text-muted">
                                    Contoh: 50 pcs untuk produk baru, 100 pcs untuk produk laris
                                </small>
                            </div>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Perhatian:</strong> Pastikan stok cukup untuk memenuhi pesanan pelanggan.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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