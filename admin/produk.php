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

// Ambil data produk
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$varian_filter = isset($_GET['varian']) ? mysqli_real_escape_string($conn, $_GET['varian']) : '';

$query_produk = "SELECT * FROM products WHERE 1=1";

if (!empty($search)) {
    $query_produk .= " AND (nama LIKE '%$search%')";
}

if (!empty($varian_filter) && $varian_filter != 'all') {
    $query_produk .= " AND varian = '$varian_filter'";
}

$query_produk .= " ORDER BY id DESC";
$result_produk = mysqli_query($conn, $query_produk);

// Hapus produk
if (isset($_GET['delete'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Hapus langsung dari database tanpa cek gambar
    $query_delete = "DELETE FROM products WHERE id = '$product_id'";
    if (mysqli_query($conn, $query_delete)) {
        $_SESSION['success'] = "Produk berhasil dihapus!";
        header("Location: produk.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal menghapus produk: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Chilova Admin</title>
    
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
        <a href="verifikasi_admin.php" class="nav-link">
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
        <h2><i class="bi bi-box-seam me-2"></i>Kelola Produk</h2>
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

    <!-- Filter dan Pencarian -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form method="GET" action="" class="d-flex gap-2">
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control" name="search" placeholder="Cari produk..." 
                                   value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-orange">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        
                        <select name="varian" class="form-select" style="max-width: 150px;">
                            <option value="all" <?= $varian_filter == 'all' ? 'selected' : '' ?>>Semua Varian</option>
                            <option value="Original" <?= $varian_filter == 'Original' ? 'selected' : '' ?>>Original</option>
                            <option value="Daun-Jeruk" <?= $varian_filter == 'Daun-Jeruk' ? 'selected' : '' ?>>Daun Jeruk</option>
                            <option value="Lengkuas" <?= $varian_filter == 'Lengkuas' ? 'selected' : '' ?>>Lengkuas</option>
                        </select>
                        
                        <a href="produk.php" class="btn btn-outline-secondary">Reset</a>
                    </form>
                </div>
                
                <div class="col-md-6 text-end">
                    <a href="tambah_produk.php" class="btn btn-orange">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Produk
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Produk -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">Daftar Produk</h5>
            
            <?php if (mysqli_num_rows($result_produk) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Produk</th>
                                <th>Varian</th>
                                <th>Ukuran</th>
                                <th>Harga</th>
                                <th width="120" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php while ($produk = mysqli_fetch_assoc($result_produk)): 
                                // Tentukan warna badge berdasarkan varian
                                $badge_class = '';
                                switch($produk['varian']) {
                                    case 'Original':
                                        $badge_class = 'badge-original';
                                        break;
                                    case 'Daun-Jeruk':
                                        $badge_class = 'badge-daun-jeruk';
                                        break;
                                    case 'Keju':
                                        $badge_class = 'badge-keju';
                                        break;
                                    default:
                                        $badge_class = 'bg-secondary';
                                }
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($produk['nama']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badge_class ?>">
                                            <?= htmlspecialchars($produk['varian']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($produk['ukuran']) ?></td>
                                    <td class="fw-bold text-success">Rp<?= number_format($produk['harga']) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Tombol Edit -->
                                            <a href="produk_edit.php?id=<?= $produk['id'] ?>" 
                                            class="btn btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <!-- Tombol Hapus -->
                                            <button type="button" class="btn btn-danger" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal<?= $produk['id'] ?>"
                                                    title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Hapus -->
                                <div class="modal fade" id="deleteModal<?= $produk['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <?php if ($produk['gambar']): ?>
                                                    <img src="../<?= htmlspecialchars($produk['gambar']) ?>" 
                                                         alt="<?= htmlspecialchars($produk['nama']) ?>" 
                                                         class="img-fluid rounded mb-3" style="max-height: 150px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded mb-3 d-flex align-items-center justify-content-center" 
                                                         style="height: 150px;">
                                                        <i class="bi bi-box-seam" style="font-size: 3rem; color: #dee2e6;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <h5><?= htmlspecialchars($produk['nama']) ?></h5>
                                                <p>
                                                    <span class="badge <?= $badge_class ?> me-2">
                                                        <?= htmlspecialchars($produk['varian']) ?>
                                                    </span>
                                                    <span class="text-muted">Ukuran: <?= htmlspecialchars($produk['ukuran']) ?></span>
                                                </p>
                                                <p class="fw-bold text-success">Rp<?= number_format($produk['harga']) ?></p>
                                                <div class="alert alert-warning">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                    Yakin ingin menghapus produk ini?
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <a href="produk.php?delete=<?= $produk['id'] ?>" 
                                                   class="btn btn-danger">
                                                    <i class="bi bi-trash me-1"></i> Hapus
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Info jumlah produk -->
                <div class="mt-3 text-muted">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        Total <?= mysqli_num_rows($result_produk) ?> produk ditemukan
                    </small>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-box-seam" style="font-size: 4rem; color: #dee2e6;"></i>
                    <h5 class="text-muted mt-3">Belum ada produk</h5>
                    <p class="text-muted">Mulai dengan menambahkan produk ke toko Anda</p>
                    <a href="produk_tambah.php" class="btn btn-orange btn-lg mt-2">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Produk Pertama
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
</script>

</body>
</html>