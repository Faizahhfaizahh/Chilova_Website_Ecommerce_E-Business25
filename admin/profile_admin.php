<?php
session_start();
require "../koneksi.php";
require "../function.php";

// Cek login
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

// Ambil data admin
$stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
    $current_profile_picture = getProfilePicturePath($user_id); // Gunakan fungsi yang sama
} else {
    $username = "Admin";
    $current_profile_picture = 'images/profile.jpg';
}

// Pesanan menunggu verifikasi
$query_verifikasi = "SELECT COUNT(*) as total FROM orders WHERE metode_pembayaran = 'DANA' AND status = 'menunggu_verifikasi'";
$result_verifikasi = mysqli_query($conn, $query_verifikasi);
$menunggu_verifikasi = mysqli_fetch_assoc($result_verifikasi)['total'] ?? 0;

// PROSES UPLOAD FOTO PROFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    // Gunakan fungsi uploadProfilePicture dari function.php
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadProfilePicture($user_id, $_FILES['profile_picture']);
        
        if (is_array($upload_result) && $upload_result['success']) {
            $_SESSION['success'] = "Foto profil berhasil diperbarui!";
            
            // Update tampilan langsung
            $current_profile_picture = 'images/' . $upload_result['filename'];
            
            // Redirect untuk refresh
            header("Location: profile_admin.php");
            exit;
        } elseif (is_string($upload_result)) {
            $_SESSION['error'] = $upload_result;
        }
    } else {
        // Handle error codes
        $error_code = $_FILES['profile_picture']['error'];
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar. Maksimal 2MB.',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar.',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang dipilih.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menyimpan file.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.'
        ];
        
        $_SESSION['error'] = $error_messages[$error_code] ?? 'Error upload file.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Chilova Admin</title>
    
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
            background-color: #f8f9fa;
            min-height: 100vh;
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
        
        /* Styling dari edit_profile.php */
        .profile-preview-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .profile-preview {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #e0540f;
            transition: all 0.3s ease;
        }
        
        .profile-preview:hover {
            opacity: 0.9;
        }
        
        .camera-overlay {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(224, 84, 15, 0.9);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .camera-overlay:hover {
            background: #e0540f;
            transform: scale(1.1);
        }
        
        .camera-overlay i {
            font-size: 1.3rem;
        }
        
        .upload-btn input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
        }
        
        .info-card {
            background-color: #f8f9fa;
            border-left: 4px solid #e0540f;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .card-simple {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .section-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .upload-form-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .profile-preview {
                width: 140px;
                height: 140px;
            }
            
            .camera-overlay {
                width: 40px;
                height: 40px;
                bottom: 10px;
                right: 10px;
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
        <a href="profile_admin.php" class="nav-link active">
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person me-2"></i>Profil Admin</h2>
        <div class="text-end d-flex align-items-center">
            <div class="me-3">
                <div class="fw-bold"><?= htmlspecialchars($username) ?></div>
                <small class="text-muted">Administrator</small>
            </div>
            <a href="profile_admin.php">
                <img src="<?= htmlspecialchars($current_profile_picture) ?>" 
                    alt="Profile" 
                    class="rounded-circle border" 
                    style="width: 50px; height: 50px; object-fit: cover; border-color: #e0540f !important;"
                    onerror="this.src='images/profile.jpg'">
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

    <!-- PROFILE CARD -->
    <div class="card-simple">
        <div class="text-center mb-4">
            <h3 class="fw-bold mb-0"><?= htmlspecialchars($username) ?></h3>
            <p class="text-muted">Administrator</p>
            
            <!-- Upload Form -->
            <form method="POST" enctype="multipart/form-data" class="upload-form-container">
                <!-- FOTO PROFIL DENGAN ICON KAMERA -->
                <div class="text-center mb-4">
                    <div class="profile-preview-container">
                        <img id="profilePreview" src="<?= htmlspecialchars($current_profile_picture) ?>" 
                             class="profile-preview mb-3" 
                             alt="Profile Picture"
                             onerror="this.src='images/profile.jpg'">
                        
                        <!-- Icon Kamera Overlay -->
                        <label class="camera-overlay upload-btn">
                            <i class="bi bi-camera"></i>
                            <input type="file" name="profile_picture" id="profile_picture" 
                                   accept="image/*" 
                                   onchange="previewImage(this)">
                        </label>
                    </div>
                    <div class="form-text small mt-2">Klik icon kamera untuk ganti foto. Max 2MB (JPG, PNG)</div>
                </div>
                
                <!-- Tombol Submit -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-orange w-100">
                        <i class="bi bi-upload me-2"></i>Simpan Perubahan Foto
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Admin -->
        <div class="info-card">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Admin</h6>
            <ul class="list-unstyled">
                <li><strong>ID Admin:</strong> <?= $user_id ?></li>
                <li><strong>Username:</strong> <?= htmlspecialchars($username) ?></li>
                <li><strong>Role:</strong> <span class="badge bg-success">Admin</span></li>
            </ul>
        </div>
    </div>

    <!-- Menu Lainnya -->
    <div class="card-simple">
        <h5 class="section-title"><i class="bi bi-gear me-2"></i>Pengaturan Lainnya</h5>
        <div class="list-group list-group-flush">
            <a href="edit_profile_admin.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                <div>
                    <i class="bi bi-pencil-square me-3 text-primary"></i>
                    Edit Profil
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Preview gambar saat dipilih (dari edit_profile.php)
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
        
        // Tampilkan nama file (opsional)
        const fileName = input.files[0].name;
        console.log("File selected: " + fileName);
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