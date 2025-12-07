<?php 
// LINE 1: Mulai session
session_start();

require "koneksi.php";
require "function.php";

// Cek login
if(!isset($_SESSION['user_id'])){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// VARIABEL
$current_username = '';
$error = '';
$success = '';

// AMBIL DATA USER SAAT INI
$stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $current_username = $user['username'];
    $current_profile_picture = getProfilePicturePath($user_id); // Gunakan fungsi
} else {
    $current_profile_picture = 'images/default.jpg'; // Default
}

// PROSES FORM JIKA ADA SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // VALIDASI
    if (empty($username)) {
        $error = "Username tidak boleh kosong";
    } 
    elseif (strlen($username) < 3) {
        $error = "Username minimal 3 karakter";
    }
    elseif (!empty($password) && strlen($password) < 6) {
        $error = "Password minimal 6 karakter";
    }
    
    // CEK USERNAME UNIK
    if (empty($error)) {
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $check->bind_param("si", $username, $user_id);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = "Username sudah digunakan";
        }
    }

    // PROSES UPLOAD FOTO JIKA ADA
    $upload_result = null;
    if (empty($error) && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadProfilePicture($user_id, $_FILES['profile_picture']);
        
        if (is_array($upload_result) && $upload_result['success']) {
            $success .= " Foto profil berhasil diupload. ";
            $current_profile_picture = 'uploads/profile/' . $upload_result['filename'];
        } elseif (is_string($upload_result)) {
            $error = $upload_result;
        }
    }
    
    // UPDATE DATABASE JIKA TIDAK ADA ERROR
    if (empty($error)) {
        // Update username
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
        $stmt->bind_param("si", $username, $user_id);
        
        if ($stmt->execute()) {
            // Update password jika ada
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt2->bind_param("si", $hashed, $user_id);
                $stmt2->execute();
            }
            
            $_SESSION['username'] = $username;
            $success = "Profile berhasil diperbarui!";
            $current_username = $username; // Update tampilan
            
            // Refresh foto profil
            $current_profile_picture = getProfilePicturePath($user_id);
        } else {
            $error = "Gagal memperbarui profile";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Css -->
    <link rel="stylesheet" href="style.css">
    <!-- Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
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
        
        /* Responsive */
        @media (max-width: 576px) {
            .profile-preview {
                width: 120px;
                height: 120px;
            }
            
            .camera-overlay {
                width: 35px;
                height: 35px;
                bottom: 8px;
                right: 8px;
            }
            
            .camera-overlay i {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 400px) {
            .profile-preview {
                width: 100px;
                height: 100px;
            }
            
            .camera-overlay {
                width: 30px;
                height: 30px;
                bottom: 5px;
                right: 5px;
            }
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">
            
            <?php if ($error): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: '<?php echo addslashes($error); ?>',
                        confirmButtonColor: '#dc3545'
                    });
                });
                </script>
            <?php endif; ?>

            <?php if ($success): ?>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '<?php echo addslashes($success); ?>',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'profile.php';
                    });
                });
                </script>
            <?php endif; ?>

            <!-- FORM EDIT PROFILE -->
            <form method="POST" enctype="multipart/form-data" class="shadow p-4 rounded-4">
                <h4 class="fw-bold mb-3 text-center">Edit Profile</h4>

                <!-- FOTO PROFIL DENGAN ICON KAMERA -->
                <div class="text-center mb-4">
                    <div class="profile-preview-container">
                        <img id="profilePreview" src="<?php echo htmlspecialchars($current_profile_picture); ?>" 
                             class="profile-preview mb-3" 
                             alt="Profile Picture"
                             onerror="this.src='images/default.jpg'">
                        
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

                <!-- INPUT USERNAME -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" 
                        value="<?php echo htmlspecialchars($current_username); ?>"
                        placeholder="Masukkan username baru" required>
                </div>

                <!-- UBAH PASSWORD -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Ubah Password (opsional)</label>
                    <p class="text-muted small">Biarkan kosong jika tidak ingin mengubah password</p>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="Masukkan password baru">
                        <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword()">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <!-- SIMPAN -->
                <div class="d-flex flex-column flex-md-row justify-content-between gap-2 gap-md-0 mt-4">
                    <a href="profile.php" class="btn btn-outline-secondary btn-sm btn-md-normal px-3 px-md-4">Batal</a>
                    <button type="submit" class="btn btn-primary btn-sm btn-md-normal px-3 px-md-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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

function togglePassword() {
    const input = document.getElementById("password");
    const icon = document.getElementById("toggleIcon");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    }
}
</script>
</body>
</html>