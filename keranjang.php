<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Proses AJAX
if (isset($_GET['action'])) {
    if ($_GET['action'] === "add") {
        $product_id = intval($_GET['product_id']);
        $cek = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$user_id AND product_id=$product_id");
        
        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($conn, "UPDATE cart SET qty = qty + 1 WHERE user_id=$user_id AND product_id=$product_id");
        } else {
            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, qty) VALUES ($user_id, $product_id, 1)");
        }
        echo "success";
        exit;
    }
    
    if ($_GET['action'] === "delete") {
        $cart_id = intval($_GET['id']);
        mysqli_query($conn, "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
        echo "success";
        exit;
    }
    
    if ($_GET['action'] === "update") {
        $cart_id = intval($_GET['id']);
        $type = $_GET['type'];
        
        if ($type == "plus") {
            mysqli_query($conn, "UPDATE cart SET qty = qty + 1 WHERE id = $cart_id");
        } else {
            mysqli_query($conn, "UPDATE cart SET qty = GREATEST(qty - 1, 1) WHERE id = $cart_id");
        }
        echo "success";
        exit;
    }
}

// Ambil data keranjang
$query = "SELECT cart.id AS cart_id, cart.qty, products.*
          FROM cart 
          JOIN products ON cart.product_id = products.id
          WHERE cart.user_id = $user_id";
$result = mysqli_query($conn, $query);

// ============ PERUBAHAN PERTAMA DI SINI ============
// Hitung total item (jumlah semua quantity)
$total_qty = 0;
$total_harga = 0;
$cart_items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cart_items[] = $row;
    $total_qty += $row['qty'];  // Menjumlahkan semua quantity
    $total_harga += $row['harga'] * $row['qty'];
}
$has_items = count($cart_items) > 0;
// ===================================================
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
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 20px; /* Untuk header */
        }
        
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
/* ============ HEADER RESPONSIF ============ */
.header-responsive {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    position: relative;
}

.header-left {
    flex: 1;
    display: flex;
    justify-content: flex-start;
}

.header-center {
    flex: 1;
    display: flex;
    justify-content: center;
}

/* Untuk mobile */
@media (max-width: 768px) {
    .header-responsive {
        flex-direction: row;
        justify-content: space-between;
    }
    
    .header-left, .header-center {
        flex: none;
    }
    
    .header-left {
        width: 30%;
    }
    
    .header-center {
        width: 70%;
        text-align: center;
    }
}

@media (max-width: 576px) {
    .header-responsive {
        flex-direction: row;
        gap: 10px;
    }
    
    .header-left {
        width: auto;
    }
    
    .header-center {
        width: auto;
        flex: 1;
        text-align: center;
    }
    
    .header-center h3 {
        font-size: 1.5rem;
    }
}

        
        .cart-item {
            background: #FFE7A0;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        
        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .product-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .product-img-sm {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .qty-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid #dee2e6;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .qty-btn:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }
        
        .delete-btn {
            color: #dc3545;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .delete-btn:hover {
            color: #bb2d3b;
        }
        
        .summary-box {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 2rem;
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        @media (max-width: 768px) {
            .cart-container {
                padding: 0 10px;
            }
            
            .cart-item, .summary-box {
                padding: 1rem;
                border-radius: 8px;
            }
            
            .d-none-mobile {
                display: none !important;
            }
            
            .d-mobile {
                display: block !important;
            }
            
            .mobile-row {
                display: flex;
                align-items: start;
                gap: 1rem;
                margin-bottom: 1rem;
            }
            
            .mobile-info {
                flex: 1;
            }
            
            .mobile-actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-top: 1rem;
                border-top: 1px solid #dee2e6;
                margin-top: 1rem;
            }
        }
        
        @media (min-width: 769px) {
            .d-mobile {
                display: none !important;
            }
        }
        
        .btn-custom {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .btn-checkout {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
        }
        
        .btn-checkout:hover {
            background: #218838;
            color: white;
        }
        
        .text-price {
            color: #28a745;
            font-weight: 600;
        }
        
        .badge-variant {
            background: #6c757d;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        
        .cart-icon-black {
            color: #000 !important;
        }
    </style>
</head>

<body>
<div class="container py-4">
    
    <!-- ============ PERUBAHAN KEEMPAT: HEADER BARU ============ -->
    <div class="header-responsive">
        <div class="header-left">
            <a href="beranda.php" class="btn btn-outline-secondary btn-custom">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="header-center">
            <h3 class="fw-bold m-0">
                <i class="bi bi-cart3 cart-icon-black me-2"></i> Keranjang Saya
            </h3>
        </div>
    </div>
    <!-- ========================================================= -->

    <?php 
    if ($has_items):
        foreach ($cart_items as $row):
            $subtotal = $row['harga'] * $row['qty'];
    ?>
    
    <!-- Desktop View -->
    <div class="cart-item d-none-mobile">
        <div class="row align-items-center">
            <div class="col-1">
                <input type="checkbox" class="form-check-input" checked>
            </div>
            <div class="col-2">
                <img src="images/<?= $row['gambar']; ?>" class="product-img" alt="<?= $row['nama']; ?>">
            </div>
            <div class="col-4">
                <h6 class="fw-bold mb-1"><?= $row['nama']; ?></h6>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge-variant"><?= $row['varian']; ?></span>
                    <small class="text-muted"><?= $row['ukuran']; ?></small>
                </div>
                <div class="text-price">Rp<?= number_format($row['harga']); ?></div>
            </div>
            <div class="col-3">
                <div class="d-flex align-items-center gap-3">
                    <button class="qty-btn updateQty" data-id="<?= $row['cart_id']; ?>" data-type="minus">−</button>
                    <span class="fw-bold"><?= $row['qty']; ?></span>
                    <button class="qty-btn updateQty" data-id="<?= $row['cart_id']; ?>" data-type="plus">+</button>
                </div>
            </div>
            <div class="col-2 text-end">
                <div class="fw-bold text-success mb-1">Rp<?= number_format($subtotal); ?></div>
                <i class="bi bi-trash delete-btn deleteItem" data-id="<?= $row['cart_id']; ?>"></i>
            </div>
        </div>
    </div>
    
    <!-- Mobile View -->
    <div class="cart-item d-mobile" style="display: none;">
        <div class="mobile-row">
            <div>
                <input type="checkbox" class="form-check-input" checked>
            </div>
            <img src="images/<?= $row['gambar']; ?>" class="product-img-sm" alt="<?= $row['nama']; ?>">
            <div class="mobile-info">
                <h6 class="fw-bold mb-1"><?= $row['nama']; ?></h6>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge-variant"><?= $row['varian']; ?></span>
                    <small class="text-muted"><?= $row['ukuran']; ?></small>
                </div>
                <div class="text-price">Rp<?= number_format($row['harga']); ?></div>
            </div>
        </div>
        <div class="mobile-actions">
            <div class="d-flex align-items-center gap-3">
                <button class="qty-btn updateQty" data-id="<?= $row['cart_id']; ?>" data-type="minus">−</button>
                <span class="fw-bold"><?= $row['qty']; ?></span>
                <button class="qty-btn updateQty" data-id="<?= $row['cart_id']; ?>" data-type="plus">+</button>
            </div>
            <div>
                <div class="fw-bold text-success">Rp<?= number_format($subtotal); ?></div>
                <i class="bi bi-trash delete-btn deleteItem" data-id="<?= $row['cart_id']; ?>" style="font-size: 1.1rem;"></i>
            </div>
        </div>
    </div>
    
    <?php 
        endforeach;
    else: 
    ?>
    
    <!-- Empty Cart -->
    <div class="empty-cart">
        <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
        <h4 class="fw-bold mt-3">Keranjang Kosong</h4>
        <p class="text-muted">Belum ada produk di keranjang Anda</p>
        <a href="beranda.php" class="btn btn-primary btn-custom mt-2">
            <i class="bi bi-bag me-2"></i> Mulai Belanja
        </a>
    </div>
    
    <?php endif; ?>
    
    <?php if ($has_items): ?>
    <!-- Summary -->
    <div class="summary-box">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="fw-bold mb-3">Ringkasan Belanja</h5>
                <!-- ============ PERUBAHAN KELIMA: MENAMPILKAN TOTAL QTY ============ -->
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Item (Quantity):</span>
                    <span class="fw-bold"><?= $total_qty; ?> produk</span>
                </div>

                <!-- =============================================================== -->
                <div class="d-flex justify-content-between">
                    <span>Total Harga:</span>
                    <span class="fw-bold text-success" style="font-size: 1.2rem;">
                        Rp<?= number_format($total_harga); ?>
                    </span>
                </div>
            </div>
            <div class="col-md-4 mt-3 mt-md-0">
                <a href="checkout.php" class="btn btn-checkout w-100">
                    <i class="bi bi-credit-card me-2"></i> Checkout
                </a>
                <div class="text-center mt-2">
                    <a href="beranda.php" class="text-decoration-none text-secondary">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Produk
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
// Update Quantity
document.querySelectorAll('.updateQty').forEach(btn => {
    btn.addEventListener('click', () => {
        let id = btn.dataset.id;
        let type = btn.dataset.type;
        fetch(`keranjang.php?action=update&id=${id}&type=${type}`)
            .then(() => location.reload());
    });
});

// Delete Item
document.querySelectorAll('.deleteItem').forEach(btn => {
    btn.addEventListener('click', (e) => {
        if(confirm('Hapus item dari keranjang?')) {
            let id = btn.dataset.id;
            fetch(`keranjang.php?action=delete&id=${id}`)
                .then(() => location.reload());
        }
    });
});

// Responsive adjustments
function checkScreenSize() {
    const isMobile = window.innerWidth <= 768;
    document.querySelectorAll('.d-none-mobile').forEach(el => {
        el.style.display = isMobile ? 'none' : '';
    });
    document.querySelectorAll('.d-mobile').forEach(el => {
        el.style.display = isMobile ? 'block' : 'none';
    });
}

window.addEventListener('resize', checkScreenSize);
window.addEventListener('load', checkScreenSize);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>