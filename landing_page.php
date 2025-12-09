<?php 
    require "koneksi.php"; // Menghubungkan database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <!-- Css -->
    <link rel="stylesheet" href="style.css">
    <!-- Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg fixed-top" style="background-color: #ffe9aa;">
        <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand" href="#">
                <img src="images/logo.png" alt="chilova logo" width="40" height="40" class="d-inline-block align-text-top rounded-circle">
            </a>
            <!-- Hamburger Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Menu -->
            <div class="collapse navbar-collapse d-lg-flex" id="navbarNav">
                <ul class="navbar-nav mb-2 mb-lg-0 flex-grow-1 justify-content-center">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#our_product">Product</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about_us">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#footer">Contact</a>
                    </li>
                </ul>
                <!-- Button Login -->
                <div class="ms-lg-auto">
                    <button class="btn btn-login" style="width: 120px;" type="button" onclick="window.location.href='login.php'" >Login</button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero text-center text-white" id="home" style="margin-top: 70px;">
        <div class="overlay"></div>
        <div class="container d-flex flex-column justify-content-center align-items-center h-100">
            <h1 class="display-3 fw-bold">Welcome to <span class="chilova-hero">Chilova</span> Website</h1>
            <p class="lead mb-4">Sahabat setia semua hidangan.</p>
            <a href="login.php" class="btn btn-light btn-lg btn-cta">Shop Now</a>
        </div>
    </section>

    <!-- Product Section -->
    <section class="product py-5" id="our_product">
        <div class="container text-center">
            <h2 class="mb-5 fw-bold">Our Product</h2>
            <!-- Card 1 -->
             <div class="row justify-content-center g-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="images/daun jeruk.png" class="card-img-top" alt="product1">
                        <div class="card-body text-start">
                            <h5 class="card-title">Chili Oil Daun Jeruk</h5>
                            <p class="card-text">Perpaduan pedas yang segar dan harum, Chili Oil Daun Jeruk menghadirkan sensasi rasa unik yang bikin setiap hidangan lebih hidup. Cocok untuk tumisan, mie, atau sebagai cocolan spesial.</p>
                            <a href="#" class=" btn btn-view-more">View More</a>
                        </div>
                    </div>
                </div>
                <!-- card 2 -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="images/original.png" class="card-img-top" alt="product2">
                        <div class="card-body text-start">
                            <h5 class="card-title">Chili Oil Original</h5>
                            <p class="card-text">Rasa pedas klasik yang kaya dan gurih, Chili Oil Original adalah teman setia bagi pecinta rasa bold. Tambahkan sedikit, dan hidanganmu langsung penuh karakter.</p>
                            <a href="#" class="btn btn-view-more">View More</a>
                        </div>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="images/lengkuas.png" class="card-img-top" alt="product3">
                        <div class="card-body text-start">
                            <h5 class="card-title">Chili Oil Lengkuas</h5>
                            <p class="card-text">Aroma lengkuas yang hangat berpadu dengan pedas yang pas, Chili Oil Lengkuas membawa cita rasa otentik Nusantara ke setiap suapan. Ideal untuk memperkuat rasa sup, tumisan, atau saus favoritmu.</p>
                            <a href="#" class="btn btn-view-more">View More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us -->
          <section class="py-5" id="about_us">
        <div class="container text-center">
            <div class="row align-items-center">
                <!-- Image -->
                <div class="col-md-6 text-center mb-4 mb-md-0">
                    <img src="images/img-product.png" alt="About Chilova" class="rounded shadow-sm" 
                        style="height: 480px; width:100%; object-fit: cover; border-radius:10px;">
                </div>
                <!-- Description -->
                <div class="col-md-6">
                 <h2 class="fw-bold mb-4">About Us</h2>
                <p class="lead text-start mb-3">Chilova menghadirkan sensasi pedas yang unik dengan 3 varian rasa Chili Oil:</p>
                <ul class="text-start mb-3" style="line-height: 1.7;">
                    <li><strong>Daun Jeruk</strong> – segar, harum, dan bikin hidanganmu lebih hidup.</li>
                    <li><strong>Original</strong> – rasa pedas klasik yang bold dan gurih, cocok untuk semua masakan.</li>
                    <li><strong>Lengkuas</strong> – aroma hangat khas Nusantara yang memperkaya cita rasa setiap suapan.</li>
                </ul>
                <p class="text-start">
                    Tersedia dalam kemasan <strong>150 gram</strong> dan <strong>100 gram</strong>, Chilova siap menemani setiap momen memasak, dari tumisan cepat hingga hidangan spesial.
                    Dengan Chili Oil Chilova, setiap hidangan menjadi lebih nikmat dan penuh karakter.
                </p>
                </div>
            </div>
        </div>
     </section>

     <!-- Footer -->
    <footer class="bg-dark text-white py-5" id="footer">
        <div class="container">
            <div class="row">
            <div class="col-md-4 mb-3">
                <h5>Chilova</h5>
                <p>Chilova – Sahabat setia semua hidangan.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Links</h5>
                <ul class="list-unstyled">
                <li class="mb-2"><a href="#home" class="text-white text-decoration-none">Home</a></li>
                <li class="mb-2"><a href="#our_product" class="text-white text-decoration-none">Product</a></li>
                <li class="mb-2"><a href="#about_us" class="text-white text-decoration-none">About</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Follow Us</h5>
                <a href="https://www.instagram.com/chilova.taste?igsh=c2ZxeGllZmQ5YXZo" class="text-white me-2"><i class="bi bi-instagram"></i></a>
            </div>
            </div>
            <div class="text-center mt-3">
            <small>&copy; 2025 Chilova. All rights reserved.</small>
            </div>
        </div>
    </footer>


</body>
</html>
