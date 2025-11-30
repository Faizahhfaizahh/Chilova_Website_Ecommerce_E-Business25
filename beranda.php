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

    <style>
    .hero-banner {
        background-color: #e0540f;
        padding: 40px 0;
        overflow: hidden;
        margin-top: 70px;
    }

    .hero-container {
        max-width: 1400px;
        margin: auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 40px;
    }

    .hero-text {
        color: white;
        max-width: 45%;
    }

    .hero-text h1 {
        font-size: 3rem;
        font-weight: bold;
        line-height: 1.2;
    }

    .hero-text p {
        margin: 20px 0;
        font-size: 1.2rem;
    }

    .beli-btn {
        background: #fff;
        color: #e0540f;
        padding: 10px 22px;
        border-radius: 10px;
    }

    .hero-img-wrapper {
        max-width: 45%;         
        display: flex;
        justify-content: flex-end;
        overflow: visible;
    }

    .hero-img {
        width: 120%;            
        height: auto;
        object-fit: contain;
        position: relative;
        right: -60px;            
        top: -120px;
    }

    .btn-search {
      border: 1px solid #e0540f ;
      color: #e0540f ;
    }

    .btn-search:hover {
      background-color: #e0540f ;
      color: white;
    }

    .btn-beli {
      background-color: white;
      width: 160px;
      color: #e0540f;
      border: 2px solid white;
      font-weight: 500;
      border-radius: 50px;
      transition: 0.25s ease;
    }

    .btn-beli:hover {
      background-color: #e0540f;
      color: white;
      border-color: white;
    }


    /* ====================== RESPONSIVE ====================== */
    @media (max-width: 992px) {
        .hero-container {
            flex-direction: column;
            text-align: center;
        }

        .hero-text {
            max-width: 100%;
        }

        .hero-img-wrapper {
            max-width: 80%;
            justify-content: center;
            margin-top: 20px;
        }

        .hero-img {
            width: 100%;
            right: 0;
            top: 0;
        }
    }

    @media (max-width: 576px) {
        .hero-text h1 {
            font-size: 2rem;
        }

        .hero-img-wrapper {
            max-width: 100%;
        }
    }
    </style>

</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top" style="background-color: #ffe9aa;">
      <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="images/logo.png" alt="Logo" width="40" height="40" class="d-inline-block align-text-top rounded-circle me-3">
          Chilova
        </a>
            <!-- Hamburger Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

        <div class="collapse navbar-collapse d-lg-flex" id="navbarSupportedContent">
          <!-- Search -->
          <form class="d-flex mx-auto flex-grow-1" role="search" style="max-width: 600px;">
            <input class="form-control me-2" type="search" placeholder="Cari produk chili oil mu" aria-label="Search"/>
            <button class="btn btn-search" type="submit">Search</button>
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

<!-- Hero Banner -->
<section class="hero-banner">
    <div class="hero-container">

        <div class="hero-text">
            <h1>Bikin Setiap Makan Jadi Lebih Istimewa Dengan Chilova</h1>
            <p>Sahabat setia semua hidangan</p>
            <a href="#" class="btn btn-beli">Beli Sekarang</a>
        </div>

        <div class="hero-img-wrapper">
            <img src="images/banner2.png" class="hero-img" alt="Chili Oil Banner">
        </div>

    </div>
</section>





</body>
</html>