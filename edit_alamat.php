<?php
session_start();

require "koneksi.php";
require "function.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

if (!isset($_GET['alamat_id'])) {
    header("Location: alamat_saya.php");
    exit;
}

$alamat_id = $_GET['alamat_id'];

// --- AMBIL DATA ALAMAT DARI DATABASE ---
$stmt = $conn->prepare("SELECT * FROM alamat WHERE alamat_id = ? AND user_id = ?");
$stmt->bind_param("ii", $alamat_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Alamat tidak ditemukan atau bukan milik user ini
    header("Location: alamat_saya.php?error=notfound");
    exit;
}

$alamat = $result->fetch_assoc();

// Proses delete jika tombol hapus ditekan
if (isset($_POST['delete'])) {
    if (deleteAddress($alamat_id, $user_id)) {
        header("Location: alamat_saya.php?success=deleted");
        exit;
    } else {
        $error = "Gagal menghapus alamat!";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alamat</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">

            <h3 class="fw-bold mb-4 text-center">Edit Alamat</h3>

            <!-- FORM EDIT -->
            <form action="update_alamat.php?alamat_id=<?= $alamat_id ?>" method="POST">

                <!-- Nama penerima -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Penerima</label>
                    <input type="text" name="nama_penerima" class="form-control"
                           value="<?= htmlspecialchars($alamat['nama_penerima'] ??'')?>" required>
                </div>

                <!-- Nomor telepon -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor Telepon</label>
                    <input type="text" name="no_telp" class="form-control"
                           value="<?= htmlspecialchars($alamat['no_telp'] ?? '') ?>" required>
                </div>

                <!-- Alamat lengkap -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat Lengkap</label>
                    <textarea name="alamat_lengkap" class="form-control" rows="3" required><?= htmlspecialchars($alamat['alamat_lengkap'] ?? '') ?></textarea>
                </div>

                <!-- Provinsi -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Provinsi</label>
                    <input type="text" name="provinsi" class="form-control"
                           value="<?= htmlspecialchars($alamat['provinsi'] ?? '') ?>" required>
                </div>

                <!-- Kota / Kecamatan -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kota</label>
                    <input type="text" name="kota" class="form-control"
                           value="<?= htmlspecialchars($alamat['kota'] ?? '') ?>" required>
                </div>

                <!-- Kode Pos -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Kode Pos</label>
                    <input type="text" name="kode_pos" class="form-control"
                           value="<?= htmlspecialchars($alamat['kode_pos'] ?? '') ?>" required>
                </div>

                <!-- Tombol SIMPAN -->
                <button type="submit" class="btn btn-primary w-100 py-2">
                    Simpan Perubahan
                </button>

            </form>

            <!-- Tombol HAPUS -->
            <form method="POST" onsubmit="return confirm('Yakin ingin menghapus alamat ini?');">
                <button type="submit" name="delete" class="btn btn-outline-danger w-100 py-2">
                    Hapus Alamat
                </button>
            </form>
            
            <!-- BACK -->
            <div class="text-center mt-3">
                <a href="shipping_address.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Kembali ke Alamat Saya
                </a>
            </div>

        </div>
    </div>
</div>

</body>
</html>
