<?php
session_start();
require "koneksi.php";
require "function.php";

if(!isset($_SESSION['user_id'])){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT o.*, p.status as payment_status, p.payment_code 
          FROM orders o
          LEFT JOIN payments p ON o.order_id = p.order_id
          WHERE o.user_id = $user_id 
          AND (o.status = 'Diproses' 
               OR o.status = 'Menunggu Pembayaran' 
               OR o.status = 'Menunggu Verifikasi')  
          ORDER BY 
            CASE 
                WHEN o.status = 'Menunggu Pembayaran' THEN 1
                WHEN o.status = 'Menunggu Verifikasi' THEN 2  
                WHEN o.status = 'Diproses' THEN 3
                ELSE 4
            END,
            o.tanggal_order DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Diproses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .order-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .product-img-small {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 5px 12px;
            border-radius: 20px;
        }
        .status-diproses {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-menunggu {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .status-menunggu-verifikasi {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .border-kadaluwarsa {
            border-left: 5px solid #dc3545;
        }
        .border-diproses {
            border-left: 5px solid #e0540f;
        }
        .border-menunggu {
            border-left: 5px solid #007bff;
        }
        .border-warning {
            border-left: 5px solid #ffc107;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-center position-relative mb-4">
        <a href="profile.php" class="btn btn-outline-dark position-absolute start-0">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h2 class="fw-bold text-center d-flex align-items-center gap-2 m-0">
            <i class="bi bi-box-seam"></i> Pesanan Saya
        </h2>
    </div>

    <?php if(mysqli_num_rows($result) == 0): ?>
        <div class="empty-state">
            <i class="bi bi-box-seam"></i>
            <h4 class="mt-3">Belum ada pesanan</h4>
            <p class="text-muted">Pesanan yang sedang diproses akan muncul di sini</p>
            <a href="beranda.php" class="btn btn-primary">
                <i class="bi bi-cart-plus me-2"></i> Belanja Sekarang
            </a>
        </div>
    <?php else: ?>
        <?php while($order = mysqli_fetch_assoc($result)): 
            // Tentukan border color berdasarkan status
            $border_class = '';
            $status_icon = '';
            $status_class = '';
            
            if($order['status'] == 'Menunggu Pembayaran') {
                $border_class = 'border-menunggu';
                $status_icon = 'bi-clock';
                $status_class = 'status-menunggu';
            } else if ($order['status'] == 'Menunggu Verifikasi'){
                $border_class = 'border-warning';
                $status_icon = 'bi-hourglass-split';
                $status_class = 'status-menunggu-verifikasi';
            } else if($order['status'] == 'Diproses') {
                $border_class = 'border-diproses';
                $status_icon = 'bi-clock-history';
                $status_class = 'status-diproses';
            } else if($order['status'] == 'Dibatalkan') {
                $border_class = 'border-kadaluwarsa';
                $status_icon = 'bi-x-circle';
                $status_class = 'status-kadaluwarsa';
            }
        ?>
            <div class="order-card <?php echo $border_class; ?>">
                <!-- HEADER ORDER -->
                <div class="order-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-1 text-primary">ORDER_<?php echo htmlspecialchars($order['order_number']); ?></h6>
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            <?php echo date('d M Y H:i', strtotime($order['tanggal_order'])); ?>
                        </small>
                    </div>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <i class="bi <?php echo $status_icon; ?> me-1"></i>
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </div>
                
                <!-- INFO ORDER -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1">
                            <strong><i class="bi bi-credit-card me-2"></i>Metode Bayar:</strong> 
                            <?php echo htmlspecialchars($order['metode_pembayaran']); ?>
                        </p>
                        
                        <?php if(!empty($order['catatan'])): ?>
                            <p class="mb-1">
                                <strong><i class="bi bi-chat-text me-2"></i>Catatan:</strong> 
                                <span class="text-muted">"<?php echo htmlspecialchars($order['catatan']); ?>"</span>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h5 class="text-danger fw-bold">
                            <i class="bi bi-cash-stack me-2"></i>
                            Total: Rp<?php echo number_format($order['total_harga']); ?>
                        </h5>
                    </div>
                </div>
                
                <!-- ALAMAT PENGIRIMAN -->
                <div class="mb-3">
                    <div class="d-flex align-items-start mb-2">
                        <i class="bi bi-geo-alt text-muted me-2 mt-1"></i>
                        <div>
                            <small class="text-muted d-block mb-1">Alamat Pengiriman:</small>
                            <div style="white-space: pre-line; background: #f8f9fa; padding: 12px; border-radius: 8px; font-size: 0.9rem;">
                                <?php echo nl2br(htmlspecialchars($order['alamat_pengiriman'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- DETAIL ITEMS -->
                <?php
                $items_query = "SELECT * FROM order_item WHERE order_id = " . $order['order_id'];
                $items_result = mysqli_query($conn, $items_query);
                ?>
                
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-list-check me-2"></i>Detail Produk
                </h6>
                
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="50%">Produk</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div style="min-width: 50px;">
                                                <?php 
                                                // Cari gambar dari products berdasarkan product_id
                                                $img_query = mysqli_query($conn, "SELECT gambar FROM products WHERE id = " . $item['product_id']);
                                                $img_data = mysqli_fetch_assoc($img_query);
                                                $gambar = $img_data['gambar'] ?? 'default-product.jpg';
                                                ?>
                                                <img src="images/<?php echo htmlspecialchars($gambar); ?>" 
                                                     class="product-img-small" 
                                                     alt="<?php echo htmlspecialchars($item['nama_produk']); ?>">
                                            </div>
                                            <div class="ms-3">
                                                <small class="fw-semibold d-block"><?php echo htmlspecialchars($item['nama_produk']); ?></small>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($item['varian']); ?> - 
                                                    <?php echo htmlspecialchars($item['ukuran']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <small>Rp<?php echo number_format($item['harga']); ?></small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-secondary"><?php echo $item['qty']; ?></span>
                                    </td>
                                    <td class="text-end align-middle fw-bold">
                                        Rp<?php echo number_format($item['subtotal']); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold text-danger">
                                    Rp<?php echo number_format($order['total_harga']); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- TOMBOL AKSI -->
                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <?php if($order['status'] == 'Menunggu Pembayaran' && ($order['metode_pembayaran'] == 'Dana' || $order['metode_pembayaran'] == 'DANA')): ?>
                        <!-- Untuk order DANA yang belum dibayar -->
                        <a href="payment_dana.php?order_id=<?php echo $order['order_id']; ?>" 
                        class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-wallet2 me-1"></i> Bayar via DANA
                        </a>
                        
                        <div>
                            <button class="btn btn-outline-danger btn-sm me-2" 
                                    onclick="cancelPayment(<?php echo $order['order_id']; ?>)">
                                <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan
                            </button>
                            
                            <a href="https://wa.me/6287745770076" 
                               target="_blank" 
                               class="btn btn-outline-success btn-sm">
                                <i class="bi bi-whatsapp me-1"></i> Hubungi penjual
                            </a>
                        </div>
                        
                    <?php elseif($order['status'] == 'Menunggu Pembayaran' && $order['metode_pembayaran'] == 'Cash on Delivery (COD)'): ?>
                        <!-- Untuk COD yang menunggu -->
                        <div>
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="bi bi-clock me-1"></i> Menunggu Verifikasi
                            </button>
                        </div>
                        
                        <div>
                            <button class="btn btn-outline-danger btn-sm me-2" 
                                    onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan
                            </button>
                            
                            <a href="https://wa.me/6287745770076" 
                               target="_blank" 
                               class="btn btn-outline-success btn-sm">
                                <i class="bi bi-whatsapp me-1"></i> Hubungi Penjual
                            </a>
                        </div>

                    <?php elseif($order['status'] == 'Menunggu Verifikasi'): ?>
                        <!-- Untuk order yang sudah upload bukti, menunggu verifikasi admin -->
                        <div>
                            <button class="btn btn-warning btn-sm" disabled>
                                <i class="bi bi-hourglass-split me-1"></i> Menunggu Verifikasi Admin
                            </button>
                            <small class="d-block text-muted mt-1">
                                Bukti pembayaran sudah diupload. Admin akan verifikasi dalam 1x24 jam.
                            </small>
                        </div>
                        
                        <div>  
                            <a href="https://wa.me/6287745770076" 
                            target="_blank" 
                            class="btn btn-outline-success btn-sm">
                                <i class="bi bi-whatsapp me-1"></i> Tanya Status
                            </a>
                        </div> 
                                            
                    <?php elseif($order['status'] == 'Diproses'): ?>
                        <!-- Untuk order yang sudah diproses -->
                        <div>
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="bi bi-check-circle me-1"></i> Pesanan Diproses
                            </button>
                        </div>
                        
                        <div>
                            <button class="btn btn-outline-danger btn-sm me-2" 
                                    onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan
                            </button>
                            
                            <a href="https://wa.me/6287745770076" 
                               target="_blank" 
                               class="btn btn-outline-success btn-sm">
                                <i class="bi bi-whatsapp me-1"></i> Hubungi Penjual
                            </a>
                        </div>
                        
                    <?php elseif($order['status'] == 'Dibatalkan'): ?>
                        <!-- Untuk order yang dibatalkan -->
                        <div>
                            <button class="btn btn-outline-danger btn-sm" disabled>
                                <i class="bi bi-x-circle me-1"></i> Pesanan Dibatalkan
                            </button>
                        </div>
                        
                        <a href="beranda.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-cart-plus me-1"></i> Pesan Lagi
                        </a>
                        
                    <?php else: ?>
                        <!-- Default fallback -->
                        <div>
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="bi bi-question-circle me-1"></i> Butuh Bantuan?
                            </button>
                        </div>
                        
                        <div>
                            <a href="https://wa.me/6287745770076" 
                               target="_blank" 
                               class="btn btn-outline-success btn-sm">
                                <i class="bi bi-whatsapp me-1"></i> Hubungi Penjual
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script>
// Fungsi untuk membatalkan pesanan
function cancelOrder(orderId) {
    if(confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
        // AJAX request untuk membatalkan order
        fetch('cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + orderId + '&action=cancel'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Pesanan berhasil dibatalkan');
                location.reload();
            } else {
                alert('Gagal membatalkan pesanan: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}

// Fungsi untuk membatalkan pembayaran DANA
function cancelPayment(orderId) {
    if(confirm('Apakah Anda yakin ingin membatalkan pembayaran DANA ini?')) {
        // AJAX request untuk membatalkan pembayaran
        fetch('cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + orderId + '&action=cancel_payment'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Pembayaran DANA berhasil dibatalkan');
                location.reload();
            } else {
                alert('Gagal membatalkan pembayaran: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}

// Tambahkan CSS untuk status kadaluwarsa
const style = document.createElement('style');
style.textContent = `
    .status-kadaluwarsa {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .btn-check:disabled, .btn-check.disabled, fieldset:disabled .btn-check {
        pointer-events: none;
        opacity: 0.65;
    }
`;
document.head.appendChild(style);
</script>

</body>
</html>