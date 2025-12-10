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

// Pesanan menunggu verifikasi (DANA) - SAMA SEPERTI DI BERANDA_ADMIN
$query_verifikasi = "SELECT COUNT(*) as total_harga FROM orders WHERE metode_pembayaran = 'DANA' AND status = 'menunggu_verifikasi'";
$result_verifikasi = mysqli_query($conn, $query_verifikasi);
$menunggu_verifikasi = mysqli_fetch_assoc($result_verifikasi)['total_harga'];

// Filter status - DIMODIFIKASI UNTUK KONSISTENSI
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'semua';
$status_condition = "";
if ($status_filter !== 'semua') {
    // Gunakan status yang sama dengan beranda_admin.php
    $status_condition = "WHERE o.status = '$status_filter'";
}

// Ambil data pesanan dengan filter - SAMA DENGAN BERANDA_ADMIN TAPI TANPA LIMIT
$query_pesanan = "SELECT o.*, u.username 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.user_id 
                  $status_condition 
                  ORDER BY o.tanggal_order DESC";
$result_pesanan = mysqli_query($conn, $query_pesanan);

// Hitung jumlah per status - DIPERBAIKI AGAR KONSISTEN
$query_count_all = "SELECT COUNT(*) as total FROM orders";
$query_count_menunggu = "SELECT COUNT(*) as total FROM orders WHERE status = 'menunggu_verifikasi'"; // DIPERBAIKI: lowercase
$query_count_diproses = "SELECT COUNT(*) as total FROM orders WHERE status = 'diproses'";
$query_count_dikirim = "SELECT COUNT(*) as total FROM orders WHERE status = 'dikirim'";
$query_count_selesai = "SELECT COUNT(*) as total FROM orders WHERE status = 'selesai'";
$query_count_dibatalkan = "SELECT COUNT(*) as total FROM orders WHERE status = 'dibatalkan'";

$count_all = mysqli_fetch_assoc(mysqli_query($conn, $query_count_all))['total'];
$count_menunggu = mysqli_fetch_assoc(mysqli_query($conn, $query_count_menunggu))['total'];
$count_diproses = mysqli_fetch_assoc(mysqli_query($conn, $query_count_diproses))['total'];
$count_dikirim = mysqli_fetch_assoc(mysqli_query($conn, $query_count_dikirim))['total'];
$count_selesai = mysqli_fetch_assoc(mysqli_query($conn, $query_count_selesai))['total'];
$count_dibatalkan = mysqli_fetch_assoc(mysqli_query($conn, $query_count_dibatalkan))['total'];

// Proses update status
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $order_number = $_POST['order_number'] ?? '';
    
    $update_query = "UPDATE orders SET status = '$new_status'";
    if ($order_number) {
        $update_query .= ", order_number = '$order_number'";
    }
    $update_query .= " WHERE order_id = '$order_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Status pesanan berhasil diupdate!";
        header("Location: pesanan.php?status=" . urlencode($new_status));
        exit;
    } else {
        $_SESSION['error'] = "Gagal mengupdate status pesanan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Chilova Admin</title>
    
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
        
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .table th {
            border-top: none;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            background-color: #f8f9fa;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        /* STYLE SAMA DENGAN BERANDA_ADMIN.PHP */
        .badge-waiting {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-process {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .badge-shipped {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .filter-badge {
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .filter-badge.active {
            background-color: #e0540f !important;
            color: white !important;
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
            
            .table-responsive {
                font-size: 0.85rem;
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
        
        .modal-header-orange {
            background-color: #e0540f;
            color: white;
        }
        
        .search-box {
            max-width: 300px;
        }
        
        .pagination .page-link {
            color: #e0540f;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #e0540f;
            border-color: #e0540f;
            color: white;
        }
    </style>
</head>
<body>

<!-- Sidebar - SAMA DENGAN BERANDA_ADMIN.PHP -->
<div class="sidebar d-none d-md-block">
    <div class="sidebar-brand">
        <h4><i class="bi bi-shop me-2"></i>Chilova Admin</h4>
    </div>
    
    <div class="sidebar-nav">
        <a href="beranda_admin.php" class="nav-link">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="pesanan.php" class="nav-link active">
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
        <h2><i class="bi bi-cart-check me-2"></i>Kelola Pesanan</h2>
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

    <!-- Filter Status -->
    <div class="table-container">
        <h5 class="mb-3"><i class="bi bi-filter me-2"></i>Filter Pesanan</h5>
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="pesanan.php?status=semua" 
               class="badge filter-badge <?= $status_filter === 'semua' ? 'active bg-primary' : 'bg-secondary' ?> text-decoration-none px-3 py-2">
                <i class="bi bi-grid me-1"></i> Semua
                <span class="badge bg-white text-dark ms-1"><?= $count_all ?></span>
            </a>
            <a href="pesanan.php?status=menunggu_verifikasi" 
               class="badge filter-badge <?= $status_filter === 'menunggu_verifikasi' ? 'active' : '' ?> bg-warning text-dark text-decoration-none px-3 py-2">
                <i class="bi bi-clock me-1"></i> Menunggu Verifikasi
                <span class="badge bg-white text-dark ms-1"><?= $count_menunggu ?></span>
            </a>
            <a href="pesanan.php?status=diproses" 
               class="badge filter-badge <?= $status_filter === 'diproses' ? 'active' : '' ?> bg-info text-decoration-none px-3 py-2">
                <i class="bi bi-gear me-1"></i> Diproses
                <span class="badge bg-white text-dark ms-1"><?= $count_diproses ?></span>
            </a>
            <a href="pesanan.php?status=dikirim" 
               class="badge filter-badge <?= $status_filter === 'dikirim' ? 'active' : '' ?> bg-primary text-decoration-none px-3 py-2">
                <i class="bi bi-truck me-1"></i> Dikirim
                <span class="badge bg-white text-dark ms-1"><?= $count_dikirim ?></span>
            </a>
            <a href="pesanan.php?status=selesai" 
               class="badge filter-badge <?= $status_filter === 'selesai' ? 'active' : '' ?> bg-success text-decoration-none px-3 py-2">
                <i class="bi bi-check-circle me-1"></i> Selesai
                <span class="badge bg-white text-dark ms-1"><?= $count_selesai ?></span>
            </a>
            <a href="pesanan.php?status=dibatalkan" 
               class="badge filter-badge <?= $status_filter === 'dibatalkan' ? 'active' : '' ?> bg-danger text-decoration-none px-3 py-2">
                <i class="bi bi-x-circle me-1"></i> Dibatalkan
                <span class="badge bg-white text-dark ms-1"><?= $count_dibatalkan ?></span>
            </a>
        </div>
        
        <!-- Search Box -->
        <div class="input-group search-box mb-4">
            <input type="text" class="form-control" placeholder="Cari nomor pesanan atau nama pelanggan..." id="searchInput">
            <button class="btn btn-outline-orange" type="button" id="searchButton">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>

    <!-- Tabel Pesanan -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="m-0">
                <i class="bi bi-list-ul me-2"></i>
                <?php
                $status_text = [
                    'semua' => 'Semua Pesanan',
                    'menunggu_verifikasi' => 'Pesanan Menunggu Verifikasi',
                    'diproses' => 'Pesanan Diproses',
                    'dikirim' => 'Pesanan Dikirim',
                    'selesai' => 'Pesanan Selesai',
                    'dibatalkan' => 'Pesanan Dibatalkan'
                ];
                echo $status_text[$status_filter] ?? 'Semua Pesanan';
                ?>
                <span class="badge bg-secondary ms-2"><?= mysqli_num_rows($result_pesanan) ?></span>
            </h5>
        </div>
        
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
        
        <div class="table-responsive">
            <table class="table table-hover" id="ordersTable">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_pesanan) > 0): ?>
                        <?php while ($pesanan = mysqli_fetch_assoc($result_pesanan)): ?>
                            <?php
                            // LOGIKA STATUS SAMA DENGAN BERANDA_ADMIN.PHP
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
                                <td>
                                    <strong><?= $pesanan['order_number'] ?></strong>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= $pesanan['username'] ?></div>
                                </td>
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
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailModal<?= $pesanan['order_id'] ?>">
                                            <a href="lihat_detail_pesanan.php?id=<?= $pesanan['order_id'] ?>" class="btn-outline-orange">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </button>
                                        <button type="button" class="btn btn-outline-orange" data-bs-toggle="modal" data-bs-target="#updateModal<?= $pesanan['order_id'] ?>">
                                            <a href="edit_pesanan.php?id=<?= $pesanan['order_id'] ?>" class="btn-outline-orange">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </button>
                                    </div>
                                    
                                    <!-- Modal Detail -->

                                    
                                    <!-- Modal Update Status -->
                                    <div class="modal fade" id="updateModal<?= $pesanan['order_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header modal-header-orange">
                                                        <h5 class="modal-title">Update Status Pesanan</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="order_id" value="<?= $pesanan['order_id'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">No. Pesanan</label>
                                                            <input type="text" class="form-control" value="<?= $pesanan['order_number'] ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Status Saat Ini</label>
                                                            <input type="text" class="form-control" value="<?= $status_text ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Update Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="menunggu_verifikasi" <?= $pesanan['status'] == 'menunggu_verifikasi' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                                                                <option value="diproses" <?= $pesanan['status'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                                                <option value="dikirim" <?= $pesanan['status'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                                                <option value="selesai" <?= $pesanan['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                                <option value="dibatalkan" <?= $pesanan['status'] == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">No. Resi (jika dikirim)</label>
                                                            <input type="text" name="order_number" class="form-control" value="<?= $pesanan['order_number'] ?? '' ?>" placeholder="Masukkan nomor resi...">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="update_status" class="btn btn-orange">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                                <p class="mt-2">Tidak ada pesanan dengan status ini</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php
        $total_pesanan = mysqli_num_rows($result_pesanan);
        if ($total_pesanan > 0): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Mobile Sidebar Toggle - SAMA DENGAN BERANDA_ADMIN.PHP -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Untuk mobile, kita bisa menambahkan toggle button
    if (window.innerWidth < 768) {
        const sidebar = document.querySelector('.sidebar');
        const header = document.querySelector('.header');
        
        // Create toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'btn btn-orange mb-3';
        toggleBtn.innerHTML = '<i class="bi bi-list"></i> Menu';
        toggleBtn.onclick = function() {
            sidebar.classList.toggle('d-none');
        };
        
        header.insertBefore(toggleBtn, header.firstChild);
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const tableRows = document.querySelectorAll('#ordersTable tbody tr');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        let visibleCount = 0;
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update counter
        const counterBadge = document.querySelector('.table-container h5 .badge');
        if (counterBadge) {
            counterBadge.textContent = visibleCount;
        }
    }
    
    searchButton.addEventListener('click', filterTable);
    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            filterTable();
        }
    });
});
</script>

</body>
</html>