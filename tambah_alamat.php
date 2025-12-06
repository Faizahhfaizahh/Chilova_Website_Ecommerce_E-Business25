<?php
session_start();
require "koneksi.php";
require "function.php";

if(!isset($_SESSION['user_id'])){
    header("location: login.php");
    exit;
}

$error = '';
$success = '';

// PROSES FORM JIKA DISUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $nama_penerima = $_POST['nama_penerima'] ?? '';
    $no_telepon = $_POST['no_telepon'] ?? '';
    $alamat_lengkap = $_POST['alamat_lengkap'] ?? '';
    $kota = $_POST['kota'] ?? '';
    $provinsi = $_POST['provinsi'] ?? '';
    $kode_pos = $_POST['kode_pos'] ?? '';
    
    // Validasi
    if (empty($nama_penerima) || empty($no_telepon) || empty($alamat_lengkap) || 
        empty($kota) || empty($provinsi) || empty($kode_pos)) {
        $error = "Semua field harus diisi!";
    } else {
        try {
            // Panggil function addAddress dengan parameter terpisah
            if (addAddress($user_id, $nama_penerima, $no_telepon, $alamat_lengkap, $provinsi, $kota, $kode_pos)) {
                $success = "Alamat berhasil ditambahkan!";
                // Redirect setelah 1.5 detik
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "alamat_saya.php";
                    }, 1500);
                </script>';
            } else {
                $error = "Gagal menyimpan alamat.";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
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
            <!-- TAMPILKAN PESAN ERROR/SUCCESS -->
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm p-4">

                <form action="" method="POST">

                    <!-- NAMA LENGKAP -->
                    <div class="mb-3">
                        <label for="nama_penerima" class="form-label fw-semibold">Nama Penerima</label>
                        <input type="text" id="nama_penerima" name="nama_penerima" 
                               class="form-control" 
                               placeholder="Nama lengkap penerima" 
                               value="<?= isset($_POST['nama_penerima']) ? htmlspecialchars($_POST['nama_penerima']) : '' ?>"
                               required>
                    </div>

                    <!-- NOMOR TELEPON -->
                    <div class="mb-3">
                        <label for="no_telepon" class="form-label">Nomor Telepon</label>
                        <div class="input-group">
                            <span class="input-group-text">+62</span>
                            <input type="tel" class="form-control" id="no_telepon" name="no_telepon" 
                                placeholder="81234567890" 
                                pattern="[0-9]{9,13}" 
                                title="Masukkan 9-13 digit nomor telepon (tanpa +62)" 
                                required>
                        </div>
                        <div class="form-text">Contoh: 81234567890</div>
                    </div>

                    <!-- ALAMAT LENGKAP -->
                    <div class="mb-3">
                        <label for="alamat_lengkap" class="form-label fw-semibold">Alamat Lengkap</label>
                        <textarea id="alamat_lengkap" name="alamat_lengkap" 
                                  class="form-control" rows="3" required 
                                  placeholder="Nama jalan, nomor rumah, RT/RW, patokan..."><?= isset($_POST['alamat_lengkap']) ? htmlspecialchars($_POST['alamat_lengkap']) : '' ?></textarea>
                    </div>

                    <!-- PROVINSI -->
                    <div class="mb-3">
                        <label for="provinsi" class="form-label fw-semibold">Provinsi</label>
                        <input type="text" id="provinsi" name="provinsi" 
                               class="form-control" 
                               value="<?= isset($_POST['provinsi']) ? htmlspecialchars($_POST['provinsi']) : '' ?>"
                               required>
                    </div>

                    <!-- KOTA -->
                    <div class="mb-3">
                        <label for="kota" class="form-label fw-semibold">Kota / Kabupaten</label>
                        <input type="text" id="kota" name="kota" 
                               class="form-control" 
                               value="<?= isset($_POST['kota']) ? htmlspecialchars($_POST['kota']) : '' ?>"
                               required>
                    </div>

                    <!-- KODE POS -->
                    <div class="mb-3">
                        <label for="kode_pos" class="form-label fw-semibold">Kode Pos</label>
                        <input type="text" id="kode_pos" name="kode_pos" 
                               class="form-control" 
                               pattern="[0-9]{5}" 
                               title="5 digit kode pos"
                               value="<?= isset($_POST['kode_pos']) ? htmlspecialchars($_POST['kode_pos']) : '' ?>"
                               required>
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
                    <a href="alamat_saya.php" class="btn btn-outline-secondary w-100 mt-2 py-2">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>

                </form>

            </div>

        </div>
    </div>
</div>

</body>
</html>
