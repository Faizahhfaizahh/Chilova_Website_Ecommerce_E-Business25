<?php
session_start();

require "koneksi.php";
require "function.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['user_id'] = $data['user_id'];
        $_SESSION['username'] = $data['username']; 
        
        // Cek jika user adalah admin
        if (isset($data['role']) && $data['role'] === 'Admin') {
            header("Location: admin/beranda_admin.php");
            exit;
        } else {
            $_SESSION['success'] = "Login berhasil! Selamat datang, $username";
            header("Location: beranda.php");
            exit;
        }
    } else {
        $error = "Username atau password salah!";
    }
}
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

    <style>
        body {
            background: linear-gradient(135deg, #FFE9AA 0%, #E0540F55 100%);
            min-height: 100vh;
            padding: 50px 0;
        }

    </style>
</head>
<body>
    <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 1100px; margin: 50px auto;">
        <div class="row align-items-center">

            <div class="col-md-6">
                <h4 class="mb-5" style="color:#e0540f">Chilova</h4>
                <h5 class="mb-4">Login</h5>

                <!-- Tampilkan error kalau salah -->
                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger py-2"><?= $error ?></div>
                <?php endif; ?>

                <!-- FORM LOGIN -->
                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="Input your username">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Input your password">
                    </div>

                    <div class="d-flex align-items-center">
                        <p class="mb-0 me-2">Don't have an account?</p>
                        <a href="registrasi.php">Sign Up</a>
                    </div>

                    <div class="d-grid col-6 mx-auto mt-4 mb-3">
                        <button class="btn btn-sign-in" type="submit">Login</button>
                    </div>

                </form>
            </div>

            <div class="col-md-6 p-0">
                <img src="images/img-product.png" class="img-fluid rounded" 
                    style="height: 500px; width:100%; object-fit: cover;">
            </div>

        </div>
    </div>

</body>
</html>