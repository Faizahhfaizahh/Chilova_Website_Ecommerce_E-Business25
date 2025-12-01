<?php 
    require "koneksi.php"; // Menghubungkan database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
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

                    <!-- PROFILE HEADER -->
                    <div class="text-center mb-4">
                        <img src="images/profile.jpg" class="profile-img rounded-circle mb-3" alt="Profile Picture">
                        <h3 class="fw-bold">Nama User</h3>
                    </div>

                    <!-- MENU ORDER ICONS -->
                    <div class="menu-card mb-4">
                        <h5 class="fw-bold mb-3">Pesanan Saya</h5>

                        <div class="row text-center">
                            <div class="col-4 col-md-4 mb-4 menu-item">
                                <i class="bi bi-box-seam menu-icon"></i>
                                <p class="menu-label mb-0">Diproses</p>
                            </div>

                            <div class="col-4 col-md-4 mb-4 menu-item">
                                <i class="bi bi-receipt menu-icon"></i>
                                <p class="menu-label mb-0">Riwayat</p>
                            </div>

                            <div class="col-4 col-md-4 mb-4 menu-item">
                                <i class="bi bi-heart menu-icon"></i>
                                <p class="menu-label mb-0">Favorit</p>
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