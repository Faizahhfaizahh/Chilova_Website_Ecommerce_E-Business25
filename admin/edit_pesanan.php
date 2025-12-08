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
    header("Location: ../beranda_admin.php");
    exit;
}

// Ambil ID pesanan dari parameter
$order_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

// Ambil data pesanan - HAPUS EMAIL
$query_pesanan = "SELECT o.*, u.username 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.user_id 
                  WHERE o.order_id = '$order_id'";
$result_pesanan = mysqli_query($conn, $query_pesanan);
$pesanan = mysqli_fetch_assoc($result_pesanan);

if (!$pesanan) {
    $_SESSION['error'] = "Pesanan tidak ditemukan!";
    header("Location: pesanan.php");
    exit;
}

// Proses update status
if (isset($_POST['update_status'])) {
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $order_number = mysqli_real_escape_string($conn, $_POST['order_number'] ?? '');
    
    // Query update
    $update_query = "UPDATE orders SET status = '$new_status'";
    if ($order_number) {
        $update_query .= ", order_number = '$order_number'";
    }
    $update_query .= " WHERE order_id = '$order_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Status pesanan berhasil diupdate!";
        header("Location: pesanan.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal mengupdate status pesanan: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pesanan - Chilova Admin</title>
    
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
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #e0540f;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
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
        
        .status-option {
            padding: 10px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .status-option:hover {
            border-color: #e0540f;
            background-color: rgba(224, 84, 15, 0.05);
        }
        
        .status-option.active {
            border-color: #e0540f;
            background-color: rgba(224, 84, 15, 0.1);
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
        <a href="pesanan.php" class="nav-link active">
            <i class="bi bi-cart-check"></i> Pesanan
        </a>
        <a href="verifikasi.php" class="nav-link">
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
        <a href="../login.php" class="nav-link mt-4" style="background-color: rgba(224, 84, 15, 0.1);">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Header -->
    <div class="header">
        <div>
            <h2><i class="bi bi-pencil-square me-2"></i>Edit Status Pesanan</h2>
            <p class="text-muted mb-0">No. Pesanan: <strong><?= $pesanan['order_number'] ?></strong></p>
        </div>
        <div class="header-user">
            <div class="text-end d-flex align-items-center">
                <div class="me-3">
                    <div class="fw-bold"><?= $_SESSION['username'] ?? 'Admin' ?></div>
                    <small class="text-muted">Administrator</small>
                </div>
                <a href="profile_admin.php" class="text-dark">
                    <img src="../images/profile.jpg" 
                         alt="Profile" 
                         class="rounded-circle border"
                         style="width: 45px; height: 45px; object-fit: cover; border-color: #e0540f !important;">
                </a>
            </div>
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

    <div class="row">
        <!-- Informasi Pesanan -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>Informasi Pesanan
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>No. Pesanan:</strong><br>
                            <span class="text-primary fw-bold"><?= $pesanan['order_number'] ?></span>
                        </div>
                        <div class="col-6">
                            <strong>Tanggal Pesanan:</strong><br>
                            <?= date('d/m/Y H:i', strtotime($pesanan['tanggal_order'])) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Pelanggan:</strong><br>
                            <?= $pesanan['username'] ?>
                        </div>
                        <div class="col-6">
                            <strong>Metode Pembayaran:</strong><br>
                            <?php if ($pesanan['metode_pembayaran'] == 'DANA'): ?>
                                <span class="badge bg-primary">DANA</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">COD</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Total Harga:</strong><br>
                            <span class="fw-bold text-success">Rp<?= number_format($pesanan['total_harga']) ?></span>
                        </div>
                        <div class="col-6">
                            <strong>Status Saat Ini:</strong><br>
                            <?php
                            $current_status_class = '';
                            $current_status_text = '';
                            switch ($pesanan['status']) {
                                case 'Menunggu Verifikasi':
                                case 'menunggu_verifikasi':
                                    $current_status_class = 'badge-waiting';
                                    $current_status_text = 'Menunggu Verifikasi';
                                    break;
                                case 'diproses':
                                    $current_status_class = 'badge-process';
                                    $current_status_text = 'Diproses';
                                    break;
                                case 'dikirim':
                                    $current_status_class = 'badge-shipped';
                                    $current_status_text = 'Dikirim';
                                    break;
                                case 'selesai':
                                    $current_status_class = 'badge-success';
                                    $current_status_text = 'Selesai';
                                    break;
                                case 'dibatalkan':
                                    $current_status_class = 'badge-cancelled';
                                    $current_status_text = 'Dibatalkan';
                                    break;
                                default:
                                    $current_status_class = 'badge-secondary';
                                    $current_status_text = $pesanan['status'];
                            }
                            ?>
                            <span class="badge-status <?= $current_status_class ?>"><?= $current_status_text ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($pesanan['alamat_pengiriman'])): ?>
                    <div class="mb-3">
                        <strong>Alamat Pengiriman:</strong><br>
                        <p class="mb-0"><?= nl2br($pesanan['alamat_pengiriman']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pesanan['catatan'])): ?>
                    <div class="mb-3">
                        <strong>Catatan:</strong><br>
                        <p class="mb-0"><?= nl2br($pesanan['catatan']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tombol Kembali -->
            <a href="pesanan.php" class="btn btn-outline-orange w-100">
                <i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar Pesanan
            </a>
        </div>
        
        <!-- Form Update Status -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-gear me-2"></i>Update Status Pesanan
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <!-- Status Saat Ini -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Status Saat Ini:</label>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <span class="badge-status <?= $current_status_class ?>"><?= $current_status_text ?></span>
                            </div>
                        </div>
                        
                        <!-- Pilih Status Baru -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">Pilih Status Baru:</label>
                            
                            <?php 
                            // Tentukan status yang tersedia berdasarkan metode pembayaran
                            if ($pesanan['metode_pembayaran'] == 'DANA') {
                                $available_statuses = [
                                    'Menunggu Verifikasi' => 'Menunggu Verifikasi',
                                    'diproses' => 'Diproses',
                                    'dikirim' => 'Dikirim',
                                    'selesai' => 'Selesai',
                                    'dibatalkan' => 'Dibatalkan'
                                ];
                            } else {
                                $available_statuses = [
                                    'diproses' => 'Diproses',
                                    'dikirim' => 'Dikirim',
                                    'selesai' => 'Selesai',
                                    'dibatalkan' => 'Dibatalkan'
                                ];
                            }
                            
                            foreach ($available_statuses as $value => $label): 
                                $option_class = '';
                                switch ($value) {
                                    case 'Menunggu Verifikasi':
                                    case 'menunggu_verifikasi':
                                        $option_class = 'badge-waiting';
                                        $icon = 'bi-clock';
                                        break;
                                    case 'diproses':
                                        $option_class = 'badge-process';
                                        $icon = 'bi-gear';
                                        break;
                                    case 'dikirim':
                                        $option_class = 'badge-shipped';
                                        $icon = 'bi-truck';
                                        break;
                                    case 'selesai':
                                        $option_class = 'badge-success';
                                        $icon = 'bi-check-circle';
                                        break;
                                    case 'dibatalkan':
                                        $option_class = 'badge-cancelled';
                                        $icon = 'bi-x-circle';
                                        break;
                                    default:
                                        $option_class = 'badge-secondary';
                                        $icon = 'bi-circle';
                                }
                            ?>
                            <div class="status-option" onclick="document.querySelector('#status<?= $value ?>').checked = true;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status<?= $value ?>" 
                                           value="<?= $value ?>" <?= $pesanan['status'] == $value ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="status<?= $value ?>">
                                        <span class="badge-status <?= $option_class ?>">
                                            <i class="bi <?= $icon ?> me-1"></i> <?= $label ?>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Input No. Resi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-receipt me-1"></i> No. Resi / Tracking
                            </label>
                            <input type="text" name="order_number" class="form-control" 
                                   value="<?= htmlspecialchars($pesanan['order_number'] ?? '') ?>" 
                                   placeholder="Masukkan nomor resi untuk pelacakan">
                            <small class="text-muted">Isi jika status diubah menjadi "Dikirim"</small>
                        </div>
                        
                        <!-- Catatan Khusus untuk DANA -->
                        <?php if ($pesanan['metode_pembayaran'] == 'DANA'): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Catatan:</strong> Pesanan dengan metode DANA memerlukan verifikasi pembayaran terlebih dahulu sebelum diproses.
                        </div>
                        <?php endif; ?>
                        
                        <!-- Tombol Simpan -->
                        <div class="d-grid gap-2">
                            <button type="submit" name="update_status" class="btn btn-orange btn-lg">
                                <i class="bi bi-check-circle me-2"></i> Simpan Perubahan Status
                            </button>
                            <a href="pesanan.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Untuk mobile sidebar toggle
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

// Highlight status option on click
document.querySelectorAll('.status-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.status-option').forEach(el => {
            el.classList.remove('active');
        });
        this.classList.add('active');
    });
});

// Show/hide resi field based on status selection
document.querySelectorAll('input[name="status"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const resiField = document.querySelector('input[name="order_number"]');
        if (this.value === 'dikirim') {
            resiField.placeholder = "Wajib diisi: Masukkan nomor resi pengiriman";
            resiField.required = true;
        } else {
            resiField.placeholder = "Masukkan nomor resi untuk pelacakan";
            resiField.required = false;
        }
    });
});
</script>

</body>
</html>