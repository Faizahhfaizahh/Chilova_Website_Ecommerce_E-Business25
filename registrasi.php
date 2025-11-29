<?php 
    require "koneksi.php"; // Menghubungkan database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <!-- Css -->
    <link rel="stylesheet" href="style.css">
    <!-- Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            background: linear-gradient(135deg, #FFE9AA 0%, #E0540F55 100%);
            min-height: 100vh;
            padding: 50px 0;
        }

    </style>
</head>
<body>
    <div class="card shadow-lg border-0 rounded-4 p-2" style="max-width: 1100px; margin: 50px auto; max-height:1100px;">
        <div class="container px-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-5" style="color:#e0540f">Chilova</h4>
                <h5 class="mb-4">Create An Account</h5>
                <!-- Form login -->
                <div class="mb-3">
                    <label for="formGroupExampleInput" class="form-label">Username</label>
                    <input type="text"  class="form-control" id="formGroupExampleInput" placeholder="Input your username">
                </div>
                <div class="mb-3">
                    <label for="formGroupExampleInput2" class="form-label">Password</label>
                    <input type="text" class="form-control" id="formGroupExampleInput2" placeholder="Input your password">
                </div>
                <!-- Link ke Create account -->
                <div class="d-flex align-items-center">
                    <p class="mb-0 me-2">Already have an account?</p>
                    <a href="login.php">Login</a>
                </div>
                <!-- Button Login -->
                <div class="d-grid gap-2 col-6 mx-auto mt-3">
                    <button class="btn btn-sign-up mb-3" type="submit">Sign Up</button>
                </div>
            </div>
            <div class="col-md-6 p-0">
                <!-- Gambar/visual -->
                <img src="images/img-product.png" alt="About Chilova" class="rounded shadow-sm" 
                            style="height: 500px; width:100%; object-fit: cover; border-radius:10px;">
            </div>
        </div>
    </div>
</div>

</body>
</html>