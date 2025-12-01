<?php 
    require "koneksi.php"; // koneksi database
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alamat Pengiriman</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <!-- Css -->
    <link rel="stylesheet" href="style.css">
    <!-- Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">

            <h3 class="fw-bold mb-4 text-center">Alamat Pengiriman</h3>

            <!-- ALAMAT 1 -->
            <div class="card mb-3 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-1">
                            Fulan Ramadhan 
                            <span class="badge bg-success ms-1">Utama</span>
                        </h6>
                        <p class="text-muted mb-1">+62 812-3456-7890</p>
                        <p class="mb-0">
                            Jl. Melati No. 12, RT 04 RW 08<br>
                            Kec. Sukamaju, Bandung, Jawa Barat
                        </p>
                    </div>

                    <div class="text-end ps-3">
                        <a href="edit_alamat.php?id=1" class="text-primary">
                            <i class="bi bi-pencil-square fs-4"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ALAMAT 2 -->
            <div class="card mb-3 shadow-sm p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-1">Dewi Andini</h6>
                        <p class="text-muted mb-1">+62 851-1234-7890</p>
                        <p class="mb-0">
                            Jl. Kenanga Timur No. 5<br>
                            Surabaya, Jawa Timur
                        </p>
                    </div>

                    <div class="text-end ps-3">
                        <a href="edit_alamat.php?id=2" class="text-primary">
                            <i class="bi bi-pencil-square fs-4"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- BUTTON TAMBAH ALAMAT -->
            <div class="text-center mt-4">
                <a href="tambah_alamat.php" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Alamat Baru
                </a>
            </div>

        </div>
    </div>
</div>

</body>
</html>
