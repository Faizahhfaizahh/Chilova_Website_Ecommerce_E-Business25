<?php 
session_start();
require "koneksi.php";
require "function.php";

if(!isset($_SESSION['user_id'])){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$alamat_result = getUserAddresses($user_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alamat Pengiriman</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Css -->
    <link rel="stylesheet" href="style.css">
    <!-- Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">
            <div class="mb-4">
                <a href="profile.php" class="text-decoration-none text-dark">
                        <i class="bi bi-arrow-left me-2"></i>
                        <span class="fw-medium">Kembali ke Profile</span>
                </a>
            </div>

            <h3 class="fw-bold mb-4 text-center">Alamat Pengiriman</h3>

            <?php if ($alamat_result->num_rows === 0): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    Belum ada alamat. Silakan tambah alamat baru.
                </div>
            <?php else: ?>
                <?php while($alamat = $alamat_result->fetch_assoc()): ?>
                <div class="card mb-3 shadow-sm p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="fw-bold mb-1">
                                <?= htmlspecialchars($alamat['nama_penerima']) ?>
                                <?php if($alamat['is_default_alamat'] == 1): ?>
                                    <span class="badge bg-success ms-1">Utama</span>
                                <?php endif; ?>
                            </h6>
                            <p class="text-muted mb-1"><?= displayPhoneNumber($alamat['no_telepon']) ?></p>
                            <p class="mb-0">
                                <?= nl2br(htmlspecialchars($alamat['alamat_lengkap'])) ?><br>
                                <?= htmlspecialchars($alamat['kota']) ?>, <?= htmlspecialchars($alamat['provinsi']) ?><br>
                                Kode Pos: <?= htmlspecialchars($alamat['kode_pos']) ?>
                            </p>
                        </div>

                        <div class="text-end ps-3">
                            <a href="edit_alamat.php?alamat_id=<?= $alamat['alamat_id'] ?>" 
                               class="text-primary me-2" title="Edit">
                                <i class="bi bi-pencil-square fs-4"></i>
                            </a>
                            <!-- Tombol set as default jika bukan default -->
                            <?php if($alamat['is_default_alamat'] == 0): ?>
                                <a href="set_default.php?alamat_id=<?= $alamat['alamat_id'] ?>" 
                                   class="text-success" title="Jadikan Alamat Utama">
                                    <!-- <i class="bi bi-star fs-4"></i> -->
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>

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