<?php
session_start();
require "koneksi.php";
require "function.php";

if(!isset($_SESSION['user_id'])){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// AMBIL DATA KERANJANG USER
$cart_query = "SELECT cart.id AS cart_id, cart.qty, products.*
              FROM cart 
              JOIN products ON cart.product_id = products.id
              WHERE cart.user_id = $user_id";
$cart_result = mysqli_query($conn, $cart_query);

// Cek jika keranjang kosong
if(mysqli_num_rows($cart_result) == 0) {
    header("location: keranjang.php");
    exit;
}

// HITUNG TOTAL
$total = 0;
$cart_items = [];
while($row = mysqli_fetch_assoc($cart_result)) {
    $subtotal = $row['harga'] * $row['qty'];
    $total += $subtotal;
    $cart_items[] = $row;
}

// AMBIL ALAMAT UTAMA USER
$alamat_utama = getDefaultAddress($user_id);

// PROSES CHECKOUT
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    
    // VALIDASI
    if(empty($metode_pembayaran)) {
        $error = "Pilih metode pembayaran!";
    } elseif(!$alamat_utama) {
        $error = "Atur alamat utama terlebih dahulu di 'Alamat Saya'!";
    } else {
        // GENERATE NOMOR ORDER
        $order_number = 'ORD' . date('YmdHis') . rand(100, 999);
        
        // SIMPAN KE TABEL ORDERS
        $order_query = "INSERT INTO orders (order_number, user_id, total_harga, metode_pembayaran, catatan, status, alamat_pengiriman, tanggal_order) 
                       VALUES (?, ?, ?, ?, ?, 'Diproses', ?, NOW())";
        $stmt = $conn->prepare($order_query);
        
        // Format alamat untuk ditampilkan
        $alamat_display = $alamat_utama['nama_penerima'] . " - " . 
                         displayPhoneNumber($alamat_utama['no_telepon']) . "\n" .
                         $alamat_utama['alamat_lengkap'] . ", " .
                         $alamat_utama['kota'] . ", " .
                         $alamat_utama['provinsi'] . " " .
                         $alamat_utama['kode_pos'];

        if($metode_pembayaran == 'DANA') {
            $status = 'Menunggu Pembayaran';
        } else if($metode_pembayaran == 'Cash on Delivery (COD)') {
            $status = 'Diproses';
        } else {
            $status = 'Diproses';
        }
        
        $stmt->bind_param("siisss", $order_number, $user_id, $total, $metode_pembayaran, $catatan, $alamat_display);
        
        if($stmt->execute()) {
            $order_id = $stmt->insert_id;
            
            // SIMPAN DETAIL ITEMS KE ORDER_ITEM
            foreach($cart_items as $item) {
                $item_query = "INSERT INTO order_item (order_id, product_id, nama_produk, varian, ukuran, harga, qty, subtotal) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $item_stmt = $conn->prepare($item_query);
                $subtotal_item = $item['harga'] * $item['qty'];
                $item_stmt->bind_param("iisssiii", $order_id, $item['id'], $item['nama'], $item['varian'], 
                                      $item['ukuran'], $item['harga'], $item['qty'], $subtotal_item);
                $item_stmt->execute();
            }
            
            // KOSONGKAN KERANJANG
            mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");
            
            if($metode_pembayaran == 'DANA') {
                // Generate unique payment code
                $payment_code = "DANA_" . time() . "_" . $order_id;
                
                // Simpan ke tabel payments
                $payment_query = "INSERT INTO payments SET
                    order_id = '$order_id',
                    user_id = '$user_id',
                    metode_pembayaran = 'Dana',
                    payment_code = '$payment_code',
                    amount = '$total',
                    status = 'menunggu',
                    created_at = NOW(),
                    expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR)";
                
                mysqli_query($conn, $payment_query);

                // Update status order juga ke 'Menunggu Pembayaran'
                mysqli_query($conn, "UPDATE orders SET status = 'Menunggu Pembayaran' WHERE order_id = '$order_id'");
                
                // Redirect ke halaman pembayaran DANA
                $_SESSION['success'] = "Pesanan berhasil dibuat! Silakan selesaikan pembayaran via DANA.";
                header("location: payment_dana.php?order_id=" . $order_id);
                exit;
                
            } else if($metode_pembayaran == 'COD' || $metode_pembayaran == 'Cash on Delivery (COD)') {
                $_SESSION['success'] = "Pesanan berhasil dibuat! No. Order: $order_number";
                header("location: diproses.php?success=1&order_id=" . $order_id);
                exit;
            }
        } else {
            $error = "Gagal membuat pesanan. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .checkout-container {
            max-width: 1000px;
            margin: auto;
        }
        .card-checkout {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header-checkout {
            background-color: #e0540f;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
            font-weight: bold;
        }
        .product-img-small {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 8px;
        }
        .address-box {
            background: #fff8e6;
            border-left: 4px solid #e0540f;
            padding: 15px;
            border-radius: 8px;
        }
        .payment-method {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover, .payment-method.selected {
            border-color: #e0540f;
            background-color: #fff8e6;
        }
        .payment-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .total-box {
            background: linear-gradient(135deg, #e0540f, #ff7b3a);
            color: white;
            padding: 20px;
            border-radius: 15px;
        }
        .btn-checkout {
            background: #e0540f;
            color: white;
            padding: 12px;
            font-weight: bold;
            border-radius: 10px;
            width: 100%;
            border: none;
            transition: all 0.3s;
        }
        .btn-checkout:hover {
            background: #c9480c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(224, 84, 15, 0.3);
        }
        .empty-cart {
            text-align: center;
            padding: 50px 20px;
        }
        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
        }
    </style>
</head>
<body>

<div class="container py-4 checkout-container">
    
    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-center position-relative mb-4">
        <a href="keranjang.php" class="btn btn-outline-dark position-absolute start-0">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h2 class="fw-bold text-center d-flex align-items-center gap-2 m-0">
            <i class="bi bi-cart-check"></i> Checkout
        </h2>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <!-- ALAMAT PENGIRIMAN -->
        <div class="card card-checkout mb-4">
            <div class="card-header card-header-checkout">
                <i class="bi bi-geo-alt me-2"></i> Alamat Pengiriman
            </div>
            <div class="card-body">
                <?php if($alamat_utama): ?>
                    <div class="address-box">
                        <h6 class="fw-bold"><?php echo htmlspecialchars($alamat_utama['nama_penerima']); ?></h6>
                        <p class="mb-1"><?php echo displayPhoneNumber($alamat_utama['no_telepon']); ?></p>
                        <p class="mb-0 text-muted">
                            <?php echo htmlspecialchars($alamat_utama['alamat_lengkap']); ?>, 
                            <?php echo htmlspecialchars($alamat_utama['kota']); ?>, 
                            <?php echo htmlspecialchars($alamat_utama['provinsi']); ?> 
                            <?php echo htmlspecialchars($alamat_utama['kode_pos']); ?>
                        </p>
                    </div>
                    <div class="text-end mt-3">
                        <a href="alamat_saya.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i> Ubah Alamat
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Anda belum memiliki alamat utama. 
                        <a href="alamat_saya.php" class="alert-link">Atur alamat utama</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DAFTAR PRODUK -->
        <div class="card card-checkout mb-4">
            <div class="card-header card-header-checkout">
                <i class="bi bi-box-seam me-2"></i> Produk Dipesan
            </div>
            <div class="card-body">
                <?php foreach($cart_items as $item): ?>
                    <div class="row align-items-center mb-3 pb-3 border-bottom">
                        <div class="col-1">
                            <img src="images/<?php echo htmlspecialchars($item['gambar']); ?>" 
                                 class="product-img-small" alt="<?php echo htmlspecialchars($item['nama']); ?>">
                        </div>
                        <div class="col-5">
                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($item['nama']); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars($item['varian']); ?> - <?php echo htmlspecialchars($item['ukuran']); ?></small>
                        </div>
                        <div class="col-2 text-center">
                            <span class="badge bg-secondary"><?php echo $item['qty']; ?> pcs</span>
                        </div>
                        <div class="col-2 text-end">
                            <small class="text-muted">Rp<?php echo number_format($item['harga']); ?>/pcs</small>
                        </div>
                        <div class="col-2 text-end fw-bold text-danger">
                            Rp<?php echo number_format($item['harga'] * $item['qty']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- METODE PEMBAYARAN -->
        <div class="card card-checkout mb-4">
            <div class="card-header card-header-checkout">
                <i class="bi bi-credit-card me-2"></i> Metode Pembayaran
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="payment-method" onclick="selectPayment('dana')">
                            <input type="radio" name="metode_pembayaran" value="DANA" id="dana" class="d-none">
                            <div class="d-flex align-items-center">
                                <div class="payment-icon text-success">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">DANA</h6>
                                    <small class="text-muted">Transfer via DANA</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="payment-method" onclick="selectPayment('cash')">
                            <input type="radio" name="metode_pembayaran" value="Cash on Delivery (COD)" id="cash" class="d-none">
                            <div class="d-flex align-items-center">
                                <div class="payment-icon text-warning">
                                    <i class="bi bi-cash"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Cash on Delivery</h6>
                                    <small class="text-muted">Bayar saat barang diterima</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="total_harga" value="<?php echo $total; ?>">
                
                <!-- CATATAN PESANAN -->
                <div class="mt-4">
                    <label class="form-label fw-semibold">Catatan Pesanan (Opsional)</label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Tolong dikirim sebelum jam 5 sore..."></textarea>
                </div>
            </div>
        </div>

        <!-- RINGKASAN PEMBAYARAN -->
        <div class="card card-checkout mb-4">
            <div class="card-header card-header-checkout">
                <i class="bi bi-receipt me-2"></i> Ringkasan Pembayaran
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal Produk</span>
                            <span>Rp<?php echo number_format($total); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Ongkos Kirim</span>
                            <span class="text-success">Gratis</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Biaya Layanan</span>
                            <span>Rp0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total Pembayaran</span>
                            <span class="text-danger">Rp<?php echo number_format($total); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="total-box text-center">
                            <h5 class="fw-bold">Total Bayar</h5>
                            <h2 class="fw-bold mb-3">Rp<?php echo number_format($total); ?></h2>
                            <button type="submit" class="btn-checkout">
                                <i class="bi bi-lock me-2"></i> Bayar Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Pilih metode pembayaran
function selectPayment(method) {
    // Hapus selected class dari semua
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Tambah selected class ke yang dipilih
    const selectedEl = document.querySelector(`[onclick="selectPayment('${method}')"]`);
    selectedEl.classList.add('selected');
    
    // Check radio button
    document.getElementById(method).checked = true;
}

// Validasi sebelum submit
document.querySelector('form').addEventListener('submit', function(e) {
    const metode = document.querySelector('input[name="metode_pembayaran"]:checked');
    const alamat = <?php echo $alamat_utama ? 'true' : 'false'; ?>;
    
    if(!metode) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Metode Pembayaran',
            text: 'Silakan pilih metode pembayaran terlebih dahulu.',
            confirmButtonColor: '#e0540f'
        });
    } else if(!alamat) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Alamat Belum Diatur',
            text: 'Silakan atur alamat utama terlebih dahulu.',
            confirmButtonColor: '#e0540f'
        });
    }
});
</script>

</body>
</html>