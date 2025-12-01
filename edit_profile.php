<?php 
    require "koneksi.php"; // Menghubungkan database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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

            <!-- FORM EDIT PROFILE -->
            <form action="proses_edit_profile.php" method="POST" enctype="multipart/form-data" class="shadow p-4 rounded-4">

                <h4 class="fw-bold mb-3 text-center">Edit Profile</h4>

                <!-- FOTO PROFIL -->
                <div class="text-center mb-4">
                    <div class="position-relative d-inline-block">
                        <img src="images/profile.jpg"
                             class="rounded-circle"
                             style="width:120px; height:120px; object-fit:cover;">
                        
                        <!-- Upload Foto -->
                        <label class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2"
                               style="cursor:pointer;">
                            <i class="bi bi-camera"></i>
                            <input type="file" name="foto" hidden>
                        </label>
                    </div>

                    <!-- Tombol hapus foto -->
                    <div class="mt-2">
                        <button type="submit" name="hapus_foto" class="btn btn-outline-danger btn-sm">
                            Hapus Foto
                        </button>
                    </div>
                </div>

                <!-- INPUT NAMA -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="nama" class="form-control" placeholder="Masukkan username" required>
                </div>

                <!-- UBAH PASSWORD -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Ubah Password (opsional)</label>

                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password baru">
                        <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword()">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <!-- SIMPAN -->
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById("password");
    const icon = document.getElementById("toggleIcon");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    }
}
</script>

</body>
</html>