<?php
session_start();
require "koneksi.php";

// Jika tidak login → redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


// ========================================
// 1. PROSES TAMBAH ITEM
// ========================================
if (isset($_GET['action']) && $_GET['action'] === "add") {

    $product_id = intval($_GET['product_id']);

    // Cek apakah produk sudah ada
    $cek = mysqli_query($con, "SELECT * FROM cart 
                                WHERE user_id=$user_id AND product_id=$product_id");

    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($con, "UPDATE cart 
                             SET qty = qty + 1 
                             WHERE user_id=$user_id AND product_id=$product_id");
    } else {
        mysqli_query($con, "INSERT INTO cart (user_id, product_id, qty) 
                             VALUES ($user_id, $product_id, 1)");
    }

    echo "success";
    exit;
}


// ========================================
// 2. PROSES HAPUS ITEM
// ========================================
if (isset($_GET['action']) && $_GET['action'] === "delete") {
    
    $cart_id = intval($_GET['id']);

    mysqli_query($con, "DELETE FROM cart 
                         WHERE id = $cart_id AND user_id = $user_id");

    echo "success";
    exit;
}


// ========================================
// 3. PROSES UPDATE QTY
// ========================================
if (isset($_GET['action']) && $_GET['action'] === "update") {

    $cart_id = intval($_GET['id']);
    $type = $_GET['type']; // plus / minus

    if ($type == "plus") {
        mysqli_query($con, "UPDATE cart 
                             SET qty = qty + 1 
                             WHERE id = $cart_id");
    } else {
        mysqli_query($con, "UPDATE cart 
                             SET qty = GREATEST(qty - 1, 1) 
                             WHERE id = $cart_id");
    }

    echo "success";
    exit;
}


// ========================================
// 4. TAMPILKAN KERANJANG
// ========================================
$query = "SELECT cart.id AS cart_id, cart.qty, products.*
          FROM cart 
          JOIN products ON cart.product_id = products.id
          WHERE cart.user_id = $user_id";
$result = mysqli_query($con, $query);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f3f6fb;
        }
        .cart-box {
            background: #FFE7A0;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            color: black; /* supaya teks tetap kebaca */
        }
        .qty-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid #ccc;
            background: white;
        }
        .delete-btn {
            color: red;
            cursor: pointer;
            font-size: 1.3rem;
        }
        .product-img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 10px;
        }
    </style>
</head>

<body>

<div class="container py-4">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-center position-relative mb-4">
        <a href="beranda.php" class="btn btn-outline-dark position-absolute start-0">
            <i class="bi bi-arrow-left"></i>
        </a>

        <h2 class="fw-bold text-center d-flex align-items-center gap-2 m-0">
            <i class="bi bi-cart3"></i> Keranjang Saya
        </h2>
    </div>

    <?php 
    $total = 0;
    while ($row = mysqli_fetch_assoc($result)):
        $subtotal = $row['harga'] * $row['qty'];
        $total += $subtotal;
    ?>

    <div class="cart-box">
        <div class="row align-items-center">

            <div class="col-1">
                <input type="checkbox" checked>
            </div>

            <div class="col-2">
                <img src="images/<?= $row['gambar']; ?>" class="product-img">
            </div>

            <div class="col-5">
                <h6 class="mb-1 fw-bold"><?= $row['nama']; ?> - <?= $row['varian']; ?></h6>
                <small class="text-secondary"><?= $row['ukuran']; ?></small>
            </div>

            <div class="col-2 text-danger fw-bold">
                Rp<?= number_format($row['harga']); ?>
            </div>

            <div class="col-2 d-flex align-items-center gap-2">

                <!-- MINUS -->
                <button class="qty-btn updateQty" 
                        data-id="<?= $row['cart_id']; ?>" 
                        data-type="minus">−</button>

                <span><?= $row['qty']; ?></span>

                <!-- PLUS -->
                <button class="qty-btn updateQty" 
                        data-id="<?= $row['cart_id']; ?>" 
                        data-type="plus">+</button>

                <!-- DELETE -->
                <i class="bi bi-trash delete-btn ms-3 deleteItem" 
                   data-id="<?= $row['cart_id']; ?>"></i>
            </div>

        </div>
    </div>

    <?php endwhile; ?>

    <div class="mt-4 p-3 bg-white rounded shadow-sm">
        <h5 class="fw-bold">Total: Rp<?= number_format($total); ?></h5>
        <button class="btn btn-success mt-3 w-100">Checkout</button>
    </div>

</div>

<!-- JS Untuk Update & Delete -->
<script>

// UPDATE QTY
document.querySelectorAll('.updateQty').forEach(btn => {
    btn.addEventListener('click', () => {
        let id = btn.dataset.id;
        let type = btn.dataset.type;

        fetch(`keranjang.php?action=update&id=${id}&type=${type}`)
            .then(() => location.reload());
    });
});

// DELETE ITEM
document.querySelectorAll('.deleteItem').forEach(btn => {
    btn.addEventListener('click', () => {
        let id = btn.dataset.id;

        fetch(`keranjang.php?action=delete&id=${id}`)
            .then(() => location.reload());
    });
});

</script>

</body>
</html>
