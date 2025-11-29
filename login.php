<?php 
    require "koneksi.php"; // Menghubungkan database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <!-- Css -->
    <link rel="stylesheet" href="style.css">
    <!-- Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="container">
    <div class="row align-items-center">
        <div class="col-md-6">
            <!-- Form login -->
             <div class="mb-3">
                <label for="formGroupExampleInput" class="form-label">Username</label>
                <input type="text" class="form-control" id="formGroupExampleInput" placeholder="Example input placeholder">
            </div>
            <div class="mb-3">
                <label for="formGroupExampleInput2" class="form-label">Password</label>
                <input type="text" class="form-control" id="formGroupExampleInput2" placeholder="Another input placeholder">
            </div>
            <!-- Link ke Create account -->
            <div class="d-flex align-items-center">
                <p class="mb-0 me-2">Don't have an account?</p>
                <a href="#">Sign Up</a>
            </div>
            <!-- Button Login -->
            <div class="d-grid gap-2 col-6 mx-auto">
                <button class="btn btn-primary" type="button">Button</button>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Gambar/visual -->
        </div>
    </div>
</div>

</body>
</html>