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

$stmt = $conn->prepare("SELECT * FROM alamat WHERE alamat_id = ? AND user_id = ?");
$stmt->bind_param("ii", $alamat_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: alamat_saya.php?error=notfound");
    exit;
}

$alamat = $result->fetch_assoc();

$no_telepon_display = $alamat['no_telepon'];
if (substr($no_telepon_display, 0, 2) === '62') {
    $no_telepon_display = substr($no_telepon_display, 2);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nama_penerima = $_POST['nama_penerima'] ?? '';
    $no_telepon = $_POST['no_telepon'] ?? '';
    $alamat_lengkap = $_POST['alamat_lengkap'] ?? '';
    $provinsi = $_POST['provinsi'] ?? '';
    $kota = $_POST['kota'] ?? '';
    $kode_pos = $_POST['kode_pos'] ?? '';

    $is_utama = isset($_POST['utama']) ? 1 : 0;
    
    // Validasi
    if (empty($nama_penerima) || empty($no_telepon) || empty($alamat_lengkap) || 
        empty($provinsi) || empty($kota) || empty($kode_pos)) {
        $error = "Semua field harus diisi!";
    } else {
        $phone_digits = preg_replace('/[^0-9]/', '', $no_telepon);
        if (strlen($phone_digits) < 11 || strlen($phone_digits) > 12){
            $error = "Nomor telepon harus 11-12 digit (contoh: 81234567890)";
        } elseif (!preg_match('/^\d{5}$/', $kode_pos)) {
            $error = "Kode pos harus 5 digit angka";
        } else {
            try {
                if (updateAddress($alamat_id, $user_id, $nama_penerima, $no_telepon, $alamat_lengkap, $provinsi, $kota, $kode_pos)) {

                if ($is_utama == 1 && $alamat['is_default_alamat'] == 0) {
                    setDefaultAddress($alamat_id, $user_id);
                }
                    $success = "Alamat berhasil diperbarui!";
                    // Refresh data
                    $stmt = $conn->prepare("SELECT * FROM alamat WHERE alamat_id = ? AND user_id = ?");
                    $stmt->bind_param("ii", $alamat_id, $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $alamat = $result->fetch_assoc();
                    // Update juga display phone
                    $no_telepon_display = $alamat['no_telepon'];
                    if (substr($no_telepon_display, 0, 2) === '62') {
                        $no_telepon_display = substr($no_telepon_display, 2);
                    }
                } else {
                    $error = "Gagal memperbarui alamat.";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

if (isset($_POST['set_default'])) {
    try {
        if (setDefaultAddress($alamat_id, $user_id)) {
            $success = "Alamat berhasil dijadikan alamat utama!";
            // Refresh data
            $stmt = $conn->prepare("SELECT * FROM alamat WHERE alamat_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $alamat_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $alamat = $result->fetch_assoc();
        } else {
            $error = "Gagal mengatur alamat utama.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <div class="mb-4">
                <a href="alamat_saya.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Alamat Saya
                </a>
            </div>

            <h3 class="fw-bold mb-4 text-center">Edit Alamat</h3>

            <!-- FORM EDIT -->
            <form action="" method="POST" id="editForm">

                <!-- Nama penerima -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Penerima</label>
                    <input type="text" name="nama_penerima" class="form-control"
                           value="<?= htmlspecialchars($alamat['nama_penerima'] ?? '') ?>" 
                           required>
                </div>

                <!-- Nomor telepon -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor Telepon</label>
                    <div class="input-group">
                        <span class="input-group-text">+62</span>
                        <input type="text" name="no_telepon" class="form-control" id="no_telepon"
                           value="<?= htmlspecialchars($no_telepon_display) ?>" 
                           placeholder="81234567890"
                           pattern="[0-9]{11,12}" 
                           title="Masukkan 11-12 digit nomor telepon (tanpa +62)" 
                           required>
                    </div>
                    <div class="form-text">Contoh: 81234567890 (11-12 digit tanpa +62)</div>
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
                    <input type="text" name="kode_pos" class="form-control" id="kode_pos"
                           value="<?= htmlspecialchars($alamat['kode_pos'] ?? '') ?>" 
                           pattern="[0-9]{5}" 
                           title="5 digit kode pos"
                           required>
                    <div class="form-text">5 digit angka (contoh: 12345)</div>
                </div>

                <!-- SET SEBAGAI ALAMAT UTAMA -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" name="utama" id="utama"
                    <?= $alamat['is_default_alamat'] == 1 ? 'checked disabled' : '' ?>>
                    <label class="form-check-label" for="utama">
                        Jadikan sebagai alamat utama
                        <?php if($alamat['is_default_alamat'] == 1): ?>
                            <span class="badge bg-success ms-1">Saat Ini Utama</span>
                        <?php endif; ?>
                    </label>
                </div>

                <!-- Tombol SIMPAN -->
                <button type="submit" name="update" class="btn btn-primary w-100 py-2 mb-3">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>

            </form>

            <form method="POST" id="deleteForm">
                <button type="button" id="deleteBtn" class="btn btn-outline-danger w-100 py-2">
                    <i class="bi bi-trash me-1"></i> Hapus Alamat
                </button>
            </form>

        </div>
    </div>
</div>

<script>
// Format nomor telepon otomatis (hapus semua non-digit)
document.getElementById('no_telepon').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    // Batasi maksimal 12 digit
    if (value.length > 12) {
        value = value.substring(0, 12);
    }
    
    e.target.value = value;
});

// Validasi sebelum submit - SAMA DENGAN tambah_alamat.php
document.getElementById('editForm').addEventListener('submit', function(e) {
    const phone = document.getElementById('no_telepon').value.replace(/\D/g, '');
    const kodePos = document.getElementById('kode_pos').value.trim();
    
    // Validasi panjang nomor telepon (11-12 digit)
    if (phone.length < 11 || phone.length > 12) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Nomor Telepon Tidak Valid',
            text: 'Nomor telepon harus 11-12 digit\nContoh: 81234567890',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
        });
        document.getElementById('no_telepon').focus();
        return false;
    }
    
    // Validasi kode pos (5 digit)
    if (!/^\d{5}$/.test(kodePos)) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Kode Pos Tidak Valid',
            text: 'Kode pos harus 5 digit angka (contoh: 12345)',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
        });
        document.getElementById('kode_pos').focus();
        return false;
    }
    
    return true;
});

// SweetAlert untuk konfirmasi hapus
document.getElementById('deleteBtn').addEventListener('click', function() {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: "Alamat yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Buat form submit secara manual
            const form = document.getElementById('deleteForm');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    });
});
</script>

<?php if($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menyimpan',
            text: '<?= addslashes($error) ?>',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'OK'
        });
    </script>
<?php endif; ?>

<?php if($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= addslashes($success) ?>',
            showConfirmButton: true,
            confirmButtonColor: '#198754',
            timer: 1500,
            timerProgressBar: true,
            willClose: () => {
                window.location.href = 'alamat_saya.php';
            }
        });
    </script>
<?php endif; ?>

</body>
</html>