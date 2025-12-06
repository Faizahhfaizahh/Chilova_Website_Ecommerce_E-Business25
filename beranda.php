<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda</title>
    <!-- Alert -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    .produk-section {
        padding: 50px 20px;
        background-color: #fff8e6;
    }

    .produk-card {
        background: #FFE7A0;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        transition: 0.2s;
        max-width: 350px;
        margin: auto;
    }

    .produk-card:hover {
        transform: scale(1.03);
    }

    .produk-card img {
    max-height: 250px; 
    object-fit: contain;
  }

    .btn-plus {
        background-color: white;
        border: none;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        font-size: 1.3rem;
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

<?php
    $success_message = '';
    if (isset($_SESSION['success'])) {
        $success_message = $_SESSION['success'];
        unset($_SESSION['success']); 
    }
?>

<?php if (!empty($success_message)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: "<?php echo addslashes($success_message); ?>",
            icon: "success",
            draggable: true,
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    });
    </script>
<?php endif; ?>

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
            <input id="searchInput" class="form-control me-2" type="search" placeholder="Cari produk chili oil mu" aria-label="Search"/>
            <button class="btn btn-search" type="submit">Search</button>
          </form>

          <!-- Icons -->
          <div class="d-flex align-items-center gap-3 ms-auto" >
            <!-- Cart -->
            <a href="keranjang.php" class="text-dark" style="font-size: 2rem;">
              <i class="bi bi-cart"></i>
            </a>

            <!-- User -->
            <a href="profile.php" class="text-dark ms-3" style="font-size: 2rem;">
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
            <a href="#produk" class="btn btn-beli">Beli Sekarang</a>
        </div>

        <div class="hero-img-wrapper">
            <img src="images/banner2.png" class="hero-img" alt="Chili Oil Banner">
        </div>

    </div>
</section>


<!-- ===================== SECTION PRODUK ===================== -->
<section id="produk" class="produk-section">
    <div class="container">
        <h2 class="text-center fw-bold mb-4">Produk Chilova</h2>

        <!-- FILTER VARIAN -->
        <div class="mb-4">
            <select id="filterVarian" class="form-select w-auto d-inline-block">
                <option value="" selected disabled>Pilih Varian Rasa Chili Oil</option>
                <option value="all">All varian</option>
                <option value="original">Original</option>
                <option value="daun-jeruk">Daun Jeruk</option>
                <option value="lengkuas">Lengkuas</option>
            </select>
        </div>

        <div class="row g-4">

            <div class="col-12 col-md-6 varian original" data-varian="original">
                <div class="produk-card">
                    <img src="images/original.png" class="img-fluid mb-2">
                    <h5 class="card-title text-danger fw-bold">Original</h5>
                    <p class="mb-0">Ukuran 100 gram</p>
                    <strong>Rp 12.000</strong>
                    <button class="btn-plus mt-2" onclick="addToCart(1)" >+</button>
                </div>
            </div>

            <div class="col-12 col-md-6 varian original" data-varian="original">
                <div class="produk-card">
                    <img src="images/original.png" class="img-fluid mb-2">
                    <h5 class="card-title text-danger fw-bold">Original</h5>
                    <p class="mb-0">Ukuran 150 gram</p>
                    <strong>Rp 15.000</strong>
                    <button class="btn-plus mt-2" onclick="addToCart(2)">+</button>
                </div>
            </div>

            <div class="col-12 col-md-6 varian daun-jeruk" data-varian="daun jeruk">
                <div class="produk-card">
                    <img src="images/daun jeruk.png" class="img-fluid mb-2">
                    <h5 class="card-title text-success fw-bold">Daun Jeruk</h5>
                    <p class="mb-0">Ukuran 100 gram</p>
                    <strong>Rp 12.000</strong>
                    <button class="btn-plus mt-2" onclick="addToCart(3)">+</button>
                </div>
            </div>

            <div class="col-12 col-md-6 varian daun-jeruk" data-varian="daun jeruk">
                <div class="produk-card">
                    <img src="images/daun jeruk.png" class="img-fluid mb-2">
                    <h5 class="card-title text-success fw-bold">Daun Jeruk</h5>
                    <p class="mb-0">Ukuran 150 gram</p>
                    <strong>Rp 15.000</strong>
                    <button class="btn-plus mt-2" onclick="addToCart(4)">+</button>
                </div>
            </div>

            <div class="col-12 col-md-6 varian lengkuas" data-varian="lengkuas">
                <div class="produk-card">
                    <img src="images/lengkuas.png" class="img-fluid mb-2">
                    <h5 class="card-title text-warning fw-bold">Lengkuas</h5>
                    <p class="mb-0">Ukuran 100 gram</p>
                    <strong>Rp 12.000</strong>
                    <button class="btn-plus mt-2" onclick="addToCart(5)">+</button>
                </div>
            </div>

            <div class="col-12 col-md-6 varian lengkuas" data-varian="lengkuas">
                <div class="produk-card">
                    <img src="images/lengkuas.png" class="img-fluid mb-2">
                    <h5 class="card-title text-warning fw-bold">Lengkuas</h5>
                    <p class="mb-0">Ukuran 150 gram</p>
                    <strong>Rp 15.000</strong>
                    <button class="btn-plus mt-2" onclick="addToCart(6)">+</button>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
document.querySelector("form[role='search']").addEventListener("submit", function(e) {
    e.preventDefault();

    let keyword = document.getElementById("searchInput").value.toLowerCase().trim();

    // Scroll otomatis ke produk
    document.getElementById("produk").scrollIntoView({ behavior: "smooth" });

    let items = document.querySelectorAll(".varian");

    items.forEach(item => {
        // Ambil semua class pada card
        let classList = item.classList;

        // Jika search kosong → tampilkan semua item
        if (keyword === "" || keyword === "all") {
            item.style.display = "block";
        }
        // Cocokkan keyword dengan class (original, daun-jeruk, lengkuas)
        else if (classList.contains(keyword.replace(" ", "-"))) {
            item.style.display = "block";
        }
        // Tidak cocok → sembunyikan
        else {
            item.style.display = "none";
        }
    });
});
</script>


<script>
document.getElementById("filterVarian").addEventListener("change", function () {
    let pilihan = this.value;
    let produk = document.querySelectorAll(".varian");

    produk.forEach(card => {
        if (pilihan === "" || pilihan === "all") {
            // Tampilkan semua data
            card.style.display = "block";
        } else {
            // Tampilkan sesuai pilihan
            if (card.classList.contains(pilihan)) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }
        }
    });
});
</script>

<script>
document.querySelector("form[role='search']").addEventListener("submit", function(e) {
    e.preventDefault();

    let keyword = document.getElementById("searchInput").value.toLowerCase().trim();

    // Scroll otomatis ke produk
    document.getElementById("produk").scrollIntoView({ behavior: "smooth" });

    let items = document.querySelectorAll(".varian");

    items.forEach(item => {
        // Ambil semua class pada card
        let classList = item.classList;

        // Jika search kosong → tampilkan semua item
        if (keyword === "" || keyword === "all") {
            item.style.display = "block";
        }
        // Cocokkan keyword dengan class (original, daun-jeruk, lengkuas)
        else if (classList.contains(keyword.replace(" ", "-"))) {
            item.style.display = "block";
        }
        // Tidak cocok → sembunyikan
        else {
            item.style.display = "none";
        }
    });
});
</script>


<!-- Script Tambah ke Keranjang -->
<script>
function addToCart(product_id) {
    fetch("keranjang.php?action=add&product_id=" + product_id)
    .then(response => response.text())
    .then(result => {
        if (result === "success") {
            alert("Produk ditambahkan ke keranjang!");
        } else if (result === "not_logged_in") {
            alert("Silakan login terlebih dahulu!");
            window.location.href = "login.php";
        } else {
            alert("Gagal menambahkan ke keranjang!");
            console.log(result);
        }
    });
}

</script>

</body>
</html>