<?php 
    session_start();
    require "koneksi.php"; 
    require "function.php";

    if(!isset($_SESSION['user_id'])){
        header("location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $username = $user['username'];
    $profile_picture = getProfilePicturePath($user_id);
    } else {
        $username = "User"; // jika tidak ditemukan
        $profile_picture = 'images/default.jpg';
    }
    
    $count_query = "SELECT COUNT(*) as count FROM orders 
                    WHERE user_id = $user_id 
                    AND (status = 'Diproses' OR status = 'Menunggu Pembayaran' OR status = 'Menunggu Verifikasi')";
    $count_result = mysqli_query($conn, $count_query);
    $count_data = mysqli_fetch_assoc($count_result);
    $diproses_count = $count_data['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbJTxI" crossorigin="anonymous"></script>
    <!-- Css -->
    <link rel="stylesheet" href="style.css">
    <!-- Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .profile-img {
            width: 120px !important;
            height: 120px !important;
            max-width: none !important;
            object-fit: cover;
        }

        .profile-container {
            max-width: 900px;
        }

        .menu-icon {
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-11 col-12 profile-container">

                <!-- CARD BESAR -->
                <div class="shadow p-4 rounded-4 bg-white">
                    <!-- TOMBOL KEMBALI -->
                    <div class="mb-4">
                        <a href="beranda.php" class="text-decoration-none text-dark">
                                <i class="bi bi-arrow-left me-2"></i>
                                <span class="fw-medium">Kembali ke Beranda</span>
                        </a>
                    </div>

                    <!-- PROFILE HEADER -->
                    <div class="text-center mb-4">
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" 
                            class="profile-img rounded-circle mb-3" 
                            alt="Profile Picture">
                        <h3 class="fw-bold"><?php echo htmlspecialchars($username);?></h3>
                    </div>

                <!-- MENU ORDER ICONS -->
                <div class="menu-card mb-4">
                    <h5 class="fw-bold mb-3">Pesanan Saya</h5>
                    <div class="row text-center">
                        <!-- DIPROSES (LINK KE profile_diproses.php) -->
                        <div class="col-6 col-md-6 mb-6 menu-item">
                            <a href="diproses.php" class="text-decoration-none text-dark d-flex flex-column align-items-center">
                                <div class="position-relative">
                                    <i class="bi bi-box-seam menu-icon"></i>
                                    <?php if($diproses_count > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                                            style="font-size: 0.6rem; padding: 4px 6px;">
                                            <?php echo $diproses_count; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p class="menu-label mb-0 mt-2">Diproses</p>
                            </a>
                        </div>

                        <!-- RIWAYAT (nanti buat profile_riwayat.php) -->
                        <div class="col-6 col-md-6 mb-6 menu-item">
                            <a href="riwayat_pesanan.php" class="text-decoration-none text-dark d-flex flex-column align-items-center">
                                <i class="bi bi-receipt menu-icon"></i>
                                <p class="menu-label mb-0 mt-2">Riwayat</p>
                            </a>
                        </div>
                    </div>
                </div>

                    <ul class="list-group rounded-4 overflow-hidden mb-4">
                        <a href="edit_profile.php" class="text-decoration-none text-dark">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                Edit Profile <i class="bi bi-chevron-right"></i>
                            </li>
                        </a>

                        <a href="alamat_saya.php" class="text-decoration-none text-dark">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                Alamat Saya <i class="bi bi-chevron-right"></i>
                            </li>
                        </a>
                    </ul>

                    <!-- LOGOUT -->
                    <div class="text-center">
                        <a href="landing_page.php">
                            <button class="btn btn-outline-danger px-4 py-2">Logout</button>
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>
</body>

</html>