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
    $cek = mysqli_query($conn, "SELECT * FROM cart 
                                WHERE user_id=$user_id AND product_id=$product_id");

    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "UPDATE cart 
                             SET qty = qty + 1 
                             WHERE user_id=$user_id AND product_id=$product_id");
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, qty) 
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

    mysqli_query($conn, "DELETE FROM cart 
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
        mysqli_query($conn, "UPDATE cart 
                             SET qty = qty + 1 
                             WHERE id = $cart_id");
    } else {
        mysqli_query($conn, "UPDATE cart 
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
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f3f6fb;
            font-size: 0.9rem;
        }
        .cart-box {
            background: #FFE7A0;
            border-radius: 15px;
            padding: 20px 15px;
            margin-bottom: 15px;
            color: black;
        }
        .qty-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid #ccc;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .delete-btn {
            color: red;
            cursor: pointer;
            font-size: 1.2rem;
        }
        .product-img {
            width: 100%;
            height: 80px;
            object-fit: contain;
            border-radius: 8px;
        }
        .mobile-header {
            display: none;
        }
        .product-info-mobile {
            display: none;
        }
        
        /* Responsive untuk mobile */
        @media (max-width: 768px) {
            body {
                font-size: 0.85rem;
            }
            .cart-box {
                padding: 15px 10px;
                border-radius: 12px;
                margin-bottom: 12px;
            }
            .container {
                padding-left: 12px;
                padding-right: 12px;
            }
            .desktop-view {
                display: none;
            }
            .mobile-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
                padding-bottom: 10px;
                border-bottom: 1px solid rgba(0,0,0,0.1);
            }
            .product-info-mobile {
                display: block;
                margin-bottom: 10px;
            }
            .qty-control-mobile {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid rgba(0,0,0,0.1);
            }
            .product-img-mobile {
                width: 70px;
                height: 70px;
                object-fit: contain;
                border-radius: 8px;
            }
            .qty-btn-mobile {
                width: 28px;
                height: 28px;
                font-size: 0.9rem;
            }
            .btn {
                font-size: 0.9rem;
            }
            h2 {
                font-size: 1.4rem;
            }
            h5 {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .product-img-mobile {
                width: 60px;
                height: 60px;
            }
            .cart-box {
                padding: 12px 8px;
            }
        }
    </style>
</head>

<body>

<div class="container py-3 py-md-4">

    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-center position-relative mb-3 mb-md-4">
        <a href="beranda.php" class="btn btn-outline-dark position-absolute start-0 d-none d-md-flex">
            <i class="bi bi-arrow-left"></i>
        </a>
        
        <!-- Mobile Back Button -->
        <a href="beranda.php" class="btn btn-outline-dark position-absolute start-0 d-md-none btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>

        <h2 class="fw-bold text-center d-flex align-items-center gap-2 m-0">
            <i class="bi bi-cart3"></i> <span class="d-none d-sm-inline">Keranjang Saya</span>
        </h2>
    </div>

    <?php 
    $total = 0;
    while ($row = mysqli_fetch_assoc($result)):
        $subtotal = $row['harga'] * $row['qty'];
        $total += $subtotal;
    ?>

    <div class="cart-box">
        
        <!-- TAMPILAN MOBILE -->
        <div class="d-md-none">
            <div class="mobile-header">
                <div class="d-flex align-items-center">
                    <input type="checkbox" checked class="me-2">
                    <h6 class="mb-0 fw-bold text-truncate"><?= $row['nama']; ?></h6>
                </div>
                <i class="bi bi-trash delete-btn deleteItem" data-id="<?= $row['cart_id']; ?>"></i>
            </div>
            
            <div class="product-info-mobile">
                <div class="row align-items-center">
                    <div class="col-3">
                        <img src="images/<?= $row['gambar']; ?>" class="product-img-mobile">
                    </div>
                    <div class="col-9">
                        <small class="text-secondary d-block"><?= $row['varian']; ?> - <?= $row['ukuran']; ?></small>
                        <div class="fw-bold text-danger mt-1">Rp<?= number_format($row['harga']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="qty-control-mobile">
                <div class="d-flex align-items-center gap-2">
                    <button class="qty-btn qty-btn-mobile updateQty" 
                            data-id="<?= $row['cart_id']; ?>" 
                            data-type="minus">−</button>
                    <span class="fw-bold mx-2"><?= $row['qty']; ?></span>
                    <button class="qty-btn qty-btn-mobile updateQty" 
                            data-id="<?= $row['cart_id']; ?>" 
                            data-type="plus">+</button>
                </div>
                <div class="fw-bold">
                    Subtotal: Rp<?= number_format($subtotal); ?>
                </div>
            </div>
        </div>
        
        <!-- TAMPILAN DESKTOP -->
        <div class="row align-items-center desktop-view">
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
                <button class="qty-btn updateQty" 
                        data-id="<?= $row['cart_id']; ?>" 
                        data-type="minus">−</button>
                <span><?= $row['qty']; ?></span>
                <button class="qty-btn updateQty" 
                        data-id="<?= $row['cart_id']; ?>" 
                        data-type="plus">+</button>
                <i class="bi bi-trash delete-btn ms-3 deleteItem" 
                   data-id="<?= $row['cart_id']; ?>"></i>
            </div>
        </div>
    </div>

    <?php endwhile; ?>
    
    <?php if(mysqli_num_rows($result) == 0): ?>
    <div class="cart-box text-center py-5">
        <i class="bi bi-cart-x display-4 text-secondary mb-3"></i>
        <h5 class="fw-bold">Keranjang Kosong</h5>
        <p class="text-secondary mb-0">Belum ada produk di keranjang Anda</p>
        <a href="beranda.php" class="btn btn-primary mt-3">Mulai Belanja</a>
    </div>
    <?php endif; ?>

    <?php if(mysqli_num_rows($result) > 0): ?>
    <div class="mt-4 p-3 bg-white rounded shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Total:</h5>
            <h5 class="fw-bold mb-0 text-danger">Rp<?= number_format($total); ?></h5>
        </div>

        <div class="d-grid gap-2">
            <a href="checkout.php" class="btn btn-success btn-lg">Checkout Sekarang</a>
        </div>
    </div>
    <?php endif; ?>

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
        if(confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
            let id = btn.dataset.id;
            fetch(`keranjang.php?action=delete&id=${id}`)
                .then(() => location.reload());
        }
    });
});

// Konfirmasi hapus dengan dialog
document.querySelectorAll('.deleteItem').forEach(btn => {
    btn.addEventListener('click', (e) => {
        if(confirm('Apakah Anda yakin ingin menghapus item ini?')) {
            let id = btn.dataset.id;
            fetch(`keranjang.php?action=delete&id=${id}`)
                .then(() => location.reload());
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>