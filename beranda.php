<?php 
    require "koneksi.php"; // Menghubungkan database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <!-- Css -->
    <link rel="stylesheet" href="style.css">
    <!-- Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="images/logo.png" alt="Logo" width="40" height="40" class="d-inline-block align-text-top rounded-circle me-3">
      Chilova
    </a>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <!-- Search -->
      <form class="d-flex mx-auto flex-grow-1" role="search" style="max-width: 600px;">
        <input class="form-control me-2" type="search" placeholder="Cari produk chili oil mu" aria-label="Search"/>
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>

      <!-- Icons -->
      <div class="d-flex align-items-center gap-3 ms-auto" >
        <!-- Cart -->
        <a href="#" class="text-dark" style="font-size: 1.4rem;">
          <i class="bi bi-cart"></i>
        </a>

        <!-- User -->
        <a href="#" class="text-dark" style="font-size: 1.4rem;">
          <i class="bi bi-person-circle"></i>
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- Banner -->
    <!-- <section class="hero text-center text-white banner" id="home">
        <div class="overlay"></div>
        <div class="container d-flex flex-column justify-content-center align-items-center h-100">
            <h1 class="display-3 fw-bold">Welcome to <span class="chilova-hero">Chilova</span> Website</h1>
            <p class="lead mb-4">Sahabat setia semua hidangan.</p>
            <a href="#" class="btn btn-light btn-lg btn-cta">Shop Now</a>
        </div>
    </section> -->

<div class="container-fluid mt-5 px-0">
    <div class="row align-items-center rounded-4 shadow-sm p-4"
         style="background-color: #E0540F;">
        
        <div class="col-md-6 text-white">
            <h2 class="fw-bold">Chilova</h2>
            <p class="lead mt-3">Nikmati sambal premium dengan cita rasa autentik.</p>
            <a href="#" class="btn btn-light mt-3">Shop Now</a>
        </div>

        <div class="col-md-6">
            <img src="images/banner2.png"
                class="img-fluid"
                style="width: 100%; height: 450px; object-fit: cover; border-radius: 10px;">
        </div>
    </div>
</div>




</body>
</html>