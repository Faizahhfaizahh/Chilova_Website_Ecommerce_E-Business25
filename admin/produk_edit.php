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

// Pastikan folder uploads/products/ ada
$upload_dir = '../uploads/products/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Ambil ID produk dari URL
$product_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;

// Ambil data produk
$query_produk = "SELECT * FROM products WHERE id = '$product_id'";
$result_produk = mysqli_query($conn, $query_produk);
$produk = mysqli_fetch_assoc($result_produk);

// Jika produk tidak ditemukan
if (!$produk) {
    $_SESSION['error'] = "Produk tidak ditemukan!";
    header("Location: produk.php");
    exit;
}

// Proses update produk
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $varian = mysqli_real_escape_string($conn, $_POST['varian']);
    $ukuran = mysqli_real_escape_string($conn, $_POST['ukuran']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok']);
    
    // Handle upload gambar baru
    $gambar = $produk['gambar']; // Default: gambar lama
    
    if (!empty($_FILES['gambar']['name'])) {
        $file_name = $_FILES['gambar']['name'];
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_size = $_FILES['gambar']['size'];
        $file_error = $_FILES['gambar']['error'];
        
        // Ekstensi yang diizinkan
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_ext)) {
            if ($file_error === 0) {
                if ($file_size <= 2000000) { // Maks 2MB
                    // Generate nama file unik
                    $new_file_name = uniqid('produk_', true) . '.' . $file_ext;
                    $upload_path = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        // Hapus gambar lama jika ada dan bukan default
                        if ($gambar && file_exists('../' . $gambar) && !str_contains($gambar, 'default_product.jpg')) {
                            unlink('../' . $gambar);
                        }
                        $gambar = 'uploads/products/' . $new_file_name;
                    } else {
                        $_SESSION['error'] = "Gagal mengupload gambar. Pastikan folder uploads/products/ dapat ditulis.";
                    }
                } else {
                    $_SESSION['error'] = "Ukuran file terlalu besar! Maksimal 2MB.";
                }
            } else {
                $_SESSION['error'] = "Terjadi kesalahan saat upload file.";
            }
        } else {
            $_SESSION['error'] = "Format file tidak didukung! Hanya JPG, JPEG, PNG, dan GIF.";
        }
    }
    
    // Update data produk
    $query_update = "UPDATE products SET 
                    nama = '$nama',
                    varian = '$varian',
                    ukuran = '$ukuran',
                    harga = '$harga',
                    stok = '$stok',
                    gambar = '$gambar'
                    WHERE id = '$product_id'";
    
    if (mysqli_query($conn, $query_update)) {
        $_SESSION['success'] = "Produk berhasil diperbarui!";
        header("Location: produk.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal memperbarui produk: " . mysqli_error($conn);
    }
}

// Pesanan menunggu verifikasi
$query_verifikasi = "SELECT COUNT(*) as total FROM orders WHERE metode_pembayaran = 'DANA' AND status = 'menunggu_verifikasi'";
$result_verifikasi = mysqli_query($conn, $query_verifikasi);
$menunggu_verifikasi = mysqli_fetch_assoc($result_verifikasi)['total'] ?? 0;
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
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
            padding: 5px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
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
        <h2><i class="bi bi-pencil-square me-2"></i>Edit Produk</h2>
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
            <h5 class="card-title mb-4">Edit Data Produk</h5>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <!-- Kolom Kiri: Form Input -->
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nama" class="form-label fw-bold">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" 
                                   value="<?= htmlspecialchars($produk['nama']) ?>" 
                                   placeholder="Contoh: Keripik Singkong Pedas" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="varian" class="form-label fw-bold">Varian <span class="text-danger">*</span></label>
                                    <select class="form-select" id="varian" name="varian" required>
                                        <option value="Original" <?= $produk['varian'] == 'Original' ? 'selected' : '' ?>>Original</option>
                                        <option value="Daun-Jeruk" <?= $produk['varian'] == 'Daun-Jeruk' ? 'selected' : '' ?>>Daun Jeruk</option>
                                        <option value="Lengkuas" <?= $produk['varian'] == 'Lengkuas' ? 'selected' : '' ?>>Lengkuas</option>
                                        <option value="Pedas" <?= $produk['varian'] == 'Pedas' ? 'selected' : '' ?>>Pedas</option>
                                        <option value="Keju" <?= $produk['varian'] == 'Keju' ? 'selected' : '' ?>>Keju</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ukuran" class="form-label fw-bold">Ukuran <span class="text-danger">*</span></label>
                                    <select class="form-select" id="ukuran" name="ukuran" required>
                                        <option value="100g" <?= $produk['ukuran'] == '100g' ? 'selected' : '' ?>>100g</option>
                                        <option value="150g" <?= $produk['ukuran'] == '150g' ? 'selected' : '' ?>>150g</option>
                                        <option value="200g" <?= $produk['ukuran'] == '200g' ? 'selected' : '' ?>>200g</option>
                                        <option value="500g" <?= $produk['ukuran'] == '500g' ? 'selected' : '' ?>>500g</option>
                                        <option value="1kg" <?= $produk['ukuran'] == '1kg' ? 'selected' : '' ?>>1kg</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="harga" class="form-label fw-bold">Harga (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="harga" name="harga" 
                                           value="<?= $produk['harga'] ?>" 
                                           placeholder="Contoh: 15000" required min="1000" step="500">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stok" class="form-label fw-bold">Stok <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="stok" name="stok" 
                                           value="<?= $produk['stok'] ?>" 
                                           placeholder="Contoh: 10" required min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kolom Kanan: Gambar -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="gambar" class="form-label fw-bold">Gambar Produk</label>
                            
                            <!-- Preview Gambar -->
                            <div class="mb-3 text-center">
                                <?php if ($produk['gambar']): ?>
                                    <img id="imagePreview" src="../<?= htmlspecialchars($produk['gambar']) ?>" 
                                         alt="Preview" class="preview-image">
                                <?php else: ?>
                                    <div id="imagePreview" class="preview-image d-flex align-items-center justify-content-center bg-light">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Input File -->
                            <input type="file" class="form-control" id="gambar" name="gambar" 
                                   accept="image/*" onchange="previewImage(event)">
                            
                            <div class="form-text">
                                Ukuran maksimal: 2MB. Format: JPG, JPEG, PNG, GIF
                            </div>
                            
                            <!-- Info gambar saat ini -->
                            <?php if ($produk['gambar']): ?>
                                <div class="alert alert-info mt-2 p-2 small">
                                    <i class="bi bi-info-circle"></i> Gambar saat ini: <?= basename($produk['gambar']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <a href="produk.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                    <div class="d-flex gap-2">
                        <button type="reset" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle me-2"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-orange">
                            <i class="bi bi-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Preview gambar saat dipilih
function previewImage(event) {
    const reader = new FileReader();
    const preview = document.getElementById('imagePreview');
    
    reader.onload = function() {
        if (preview.tagName === 'IMG') {
            preview.src = reader.result;
        } else {
            // Jika sebelumnya div, ubah menjadi img
            const img = document.createElement('img');
            img.src = reader.result;
            img.className = 'preview-image';
            img.id = 'imagePreview';
            preview.parentNode.replaceChild(img, preview);
        }
    }
    
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}

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