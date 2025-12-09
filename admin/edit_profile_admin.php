<?php
session_start();
require "../koneksi.php";
require "../function.php";

// Cek apakah user sudah login
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
$query_admin = "SELECT * FROM users WHERE user_id = '$user_id'";
$result_admin = mysqli_query($conn, $query_admin);
$admin = mysqli_fetch_assoc($result_admin);

// Pesanan menunggu verifikasi
$query_verifikasi = "SELECT COUNT(*) as total FROM orders WHERE metode_pembayaran = 'DANA' AND status = 'menunggu_verifikasi'";
$result_verifikasi = mysqli_query($conn, $query_verifikasi);
$menunggu_verifikasi = mysqli_fetch_assoc($result_verifikasi)['total'] ?? 0;

// VARIABEL
$current_username = $admin['username'];
$current_profile_picture = getProfilePicturePath($user_id);
$error = '';
$success = '';

// PROSES FORM JIKA ADA SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $username = trim($_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // VALIDASI USERNAME
    if (empty($username)) {
        $error = "Username tidak boleh kosong";
    } 
    elseif (strlen($username) < 3) {
        $error = "Username minimal 3 karakter";
    }
    
    // CEK USERNAME UNIK
    if (empty($error)) {
        $check_username = "SELECT user_id FROM users WHERE username = '$username' AND user_id != '$user_id'";
        $result_check = mysqli_query($conn, $check_username);
        if (mysqli_num_rows($result_check) > 0) {
            $error = "Username sudah digunakan";
        }
    }
    
    // VALIDASI PASSWORD JIKA DIISI
    if (empty($error) && (!empty($new_password) || !empty($confirm_password))) {
        // Cek apakah password saat ini diisi
        if (empty($current_password)) {
            $error = "Password saat ini harus diisi untuk mengubah password";
        }
        // Verifikasi password saat ini
        elseif (!password_verify($current_password, $admin['password'])) {
            $error = "Password saat ini salah";
        }
        // Validasi password baru
        elseif (strlen($new_password) < 6) {
            $error = "Password baru minimal 6 karakter";
        }
        elseif ($new_password !== $confirm_password) {
            $error = "Konfirmasi password tidak cocok";
        }
    }
    
    // PROSES UPLOAD FOTO JIKA ADA
    $profile_picture = $admin['profile_picture']; // Default: gambar lama
    
    if (empty($error) && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['profile_picture']['name'];
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_error = $_FILES['profile_picture']['error'];
        
        // Ekstensi yang diizinkan
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_ext)) {
            if ($file_error === 0) {
                if ($file_size <= 2000000) { // Maks 2MB
                    // Generate nama file unik
                    $new_file_name = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
                    $upload_dir = '../images/';
                    $upload_path = $upload_dir . $new_file_name;
                    
                    // Pastikan folder images/ ada
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        // Hapus gambar lama jika ada dan bukan default.jpg
                        if ($profile_picture && $profile_picture != 'default.jpg' && $profile_picture != 'images/profile.jpg' && file_exists('../' . $profile_picture)) {
                            unlink('../' . $profile_picture);
                        }
                        $profile_picture = 'images/' . $new_file_name;
                    } else {
                        $error = "Gagal mengupload gambar.";
                    }
                } else {
                    $error = "Ukuran file terlalu besar! Maksimal 2MB.";
                }
            } else {
                $error = "Terjadi kesalahan saat upload file.";
            }
        } else {
            $error = "Format file tidak didukung! Hanya JPG, JPEG, PNG, dan GIF.";
        }
    }
    
    // UPDATE DATABASE JIKA TIDAK ADA ERROR
    if (empty($error)) {
        // Jika password diubah, hash password baru
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query_update = "UPDATE users SET 
                            username = '$username',
                            password = '$hashed_password',
                            profile_picture = '$profile_picture'
                            WHERE user_id = '$user_id'";
        } else {
            $query_update = "UPDATE users SET 
                            username = '$username',
                            profile_picture = '$profile_picture'
                            WHERE user_id = '$user_id'";
        }
        
        if (mysqli_query($conn, $query_update)) {
            $_SESSION['success'] = "Profile berhasil diperbarui!";
            header("Location: profile_admin.php");
            exit;
        } else {
            $error = "Gagal memperbarui profile: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Admin - Chilova Admin</title>
    
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
        
        .profile-preview-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .profile-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e0540f;
            transition: all 0.3s ease;
        }
        
        .profile-preview:hover {
            opacity: 0.9;
        }
        
        .camera-overlay {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(224, 84, 15, 0.9);
            color: white;
            width: 40px;
            height: 40px;
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
            font-size: 1.2rem;
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
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .password-section {
            border-left: 4px solid #ffc107;
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil-square me-2"></i>Edit Profil Admin</h2>
        <div class="text-end d-flex align-items-center">
            <div class="me-3">
                <div class="fw-bold"><?= $_SESSION['username'] ?? 'Admin' ?></div>
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
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Card Edit Profil -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">Edit Data Profil</h5>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    
                    <!-- Kolom Kanan: Form Input -->
                    <div class="col-md-8">
                        <!-- Form Username -->
                        <div class="form-group mb-4">
                            <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($current_username) ?>" 
                                   placeholder="Masukkan username" required>
                        </div>
                        
                        <!-- Password Section -->
                        <div class="card border-warning password-section mb-4">
                            <div class="card-header bg-warning bg-opacity-10">
                                <h6 class="mb-0"><i class="bi bi-key me-2"></i>Ubah Password</h6>
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="current_password" class="form-label">Password Saat Ini</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password" name="current_password" 
                                               placeholder="Masukkan password saat ini">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password', 'toggleCurrentIcon')">
                                            <i class="bi bi-eye-slash" id="toggleCurrentIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="new_password" class="form-label">Password Baru</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                                       placeholder="Password baru (min. 6 karakter)">
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password', 'toggleNewIcon')">
                                                    <i class="bi bi-eye-slash" id="toggleNewIcon"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                       placeholder="Ulangi password baru">
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', 'toggleConfirmIcon')">
                                                    <i class="bi bi-eye-slash" id="toggleConfirmIcon"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info small mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Password harus minimal 6 karakter. Gunakan kombinasi huruf, angka, dan simbol untuk keamanan yang lebih baik.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <a href="profile_admin.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Profil
                    </a>
                    <div class="d-flex gap-2">
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
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Toggle show/hide password
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
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