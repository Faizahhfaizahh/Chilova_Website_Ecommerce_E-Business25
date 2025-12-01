<?php 
    require "koneksi.php"; // koneksi database
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Alamat Baru</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Css -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">

            <h3 class="fw-bold mb-4 text-center">Tambah Alamat Baru</h3>

            <div class="card shadow-sm p-4">

                <form action="proses_add_address.php" method="POST">

                    <!-- NAMA LENGKAP -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Penerima</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama lengkap penerima" required>
                    </div>

                    <!-- NOMOR TELEPON -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor Telepon</label>
                        <input type="tel" name="telepon" class="form-control" placeholder="+62..." required>
                    </div>

                    <!-- ALAMAT LENGKAP -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="3" required placeholder="Nama jalan, nomor rumah, RT/RW, patokan..."></textarea>
                    </div>

                    <!-- KOTA -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kota / Kabupaten</label>
                        <input type="text" name="kota" class="form-control" required>
                    </div>

                    <!-- PROVINSI -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Provinsi</label>
                        <input type="text" name="provinsi" class="form-control" required>
                    </div>

                    <!-- KODE POS -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode Pos</label>
                        <input type="number" name="kode_pos" class="form-control" required>
                    </div>

                    <!-- SET SEBAGAI ALAMAT UTAMA -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" name="utama" id="utama">
                        <label class="form-check-label" for="utama">
                            Jadikan sebagai alamat utama
                        </label>
                    </div>

                    <!-- BUTTON SUBMIT -->
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-save2 me-1"></i> Simpan Alamat
                    </button>

                    <!-- BUTTON KEMBALI -->
                    <a href="shipping_address.php" class="btn btn-outline-secondary w-100 mt-2 py-2">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>

                </form>

            </div>

        </div>
    </div>
</div>

</body>
</html>
