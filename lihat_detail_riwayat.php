<?php
session_start();
require "koneksi.php";
require "function.php";

if(!isset($_SESSION['user_id'])){
    header("location: login.php");
    exit;
}

// Cek apakah ada parameter order_id
if(!isset($_GET['order_id']) || empty($_GET['order_id'])){
    header("location: riwayat_pesanan.php");
    exit;
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Ambil data order berdasarkan order_id dan user_id (untuk keamanan)
$query_order = "SELECT o.*, u.username
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                WHERE o.order_id = '$order_id' AND o.user_id = '$user_id' 
                AND o.status IN ('Selesai', 'Dibatalkan')";
$result_order = mysqli_query($conn, $query_order);

if(mysqli_num_rows($result_order) == 0){
    header("location: riwayat_pesanan.php");
    exit;
}

$order = mysqli_fetch_assoc($result_order);

// Ambil item-item dalam order
$query_items = "SELECT oi.*, p.nama, p.gambar 
                FROM order_item oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = '$order_id'";
$result_items = mysqli_query($conn, $query_items);

// Tentukan class badge berdasarkan status
$status_class = '';
if($order['status'] == 'Selesai') {
    $status_class = 'status-selesai';
} elseif($order['status'] == 'Dibatalkan') {
    $status_class = 'status-dibatalkan';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Riwayat Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .detail-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        .status-selesai {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-dibatalkan {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .item-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 20px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #dee2e6;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #6c757d;
        }
        .timeline-item.completed::before {
            background-color: #28a745;
        }
        .timeline-item.cancelled::before {
            background-color: #dc3545;
        }
        .summary-box {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .no-image {
            width: 80px;
            height: 80px;
            background-color: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #dee2e6;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-center position-relative mb-4">
        <a href="riwayat_pesanan.php" class="btn btn-outline-dark position-absolute start-0">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h2 class="fw-bold text-center d-flex align-items-center gap-2 m-0">
            <i class="bi bi-receipt-cutoff"></i> Detail Pesanan
        </h2>
    </div>

    <!-- INFORMASI ORDER -->
    <div class="detail-card">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="fw-bold">Order #<?php echo htmlspecialchars($order['order_number']); ?></h5>
                <p class="text-muted mb-1">
                    <i class="bi bi-calendar me-1"></i>
                    <?php echo date('d F Y H:i', strtotime($order['tanggal_order'])); ?>
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="status-badge <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars($order['status']); ?>
                </span>
                <p class="text-muted mt-2 mb-0">ID: <?php echo htmlspecialchars($order['order_id']); ?></p>
            </div>
        </div>

        <!-- TIMELINE STATUS -->
        <div class="timeline">
            <!-- Timeline untuk semua status -->
            <div class="timeline-item <?php echo ($order['status'] == 'Selesai' || $order['status'] == 'Dibatalkan') ? 'completed' : ''; ?>">
                <strong>Pesanan Dibuat</strong>
                <p class="text-muted mb-0"><?php echo date('d F Y H:i', strtotime($order['tanggal_order'])); ?></p>
            </div>
            
            <?php if($order['status'] == 'Selesai'): ?>
                <!-- Timeline untuk status Selesai -->
                <div class="timeline-item completed">
                    <strong>Pembayaran Berhasil</strong>
                    <p class="text-muted mb-0">
                        <?php echo htmlspecialchars($order['metode_pembayaran']); ?> • 
                        <?php echo date('d F Y H:i', strtotime($order['tanggal_order'] . ' +10 minutes')); ?>
                    </p>
                </div>
                
                <div class="timeline-item completed">
                    <strong>Pesanan Diproses</strong>
                    <p class="text-muted mb-0"><?php echo date('d F Y H:i', strtotime($order['tanggal_order'] . ' +1 day')); ?></p>
                </div>
                
                <div class="timeline-item completed">
                    <strong>Pesanan Dikirim</strong>
                    <p class="text-muted mb-0"><?php echo date('d F Y H:i', strtotime($order['tanggal_order'] . ' +2 days')); ?></p>
                </div>
                
                <div class="timeline-item completed">
                    <strong>Pesanan Selesai</strong>
                    <p class="text-muted mb-0"><?php echo date('d F Y H:i', strtotime($order['tanggal_order'] . ' +3 days')); ?></p>
                </div>
                
            <?php elseif($order['status'] == 'Dibatalkan'): ?>
                <!-- Timeline untuk status Dibatalkan -->
                <div class="timeline-item cancelled">
                    <strong>Pesanan Dibatalkan</strong>
                    <p class="text-muted mb-0"><?php echo date('d F Y H:i', strtotime($order['tanggal_order'] . ' +1 hour')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ITEM PESANAN -->
    <div class="detail-card">
        <h5 class="fw-bold mb-4">Item Pesanan</h5>
        
        <?php if(mysqli_num_rows($result_items) > 0): ?>
            <?php while($item = mysqli_fetch_assoc($result_items)): ?>
                <div class="item-card">
                    <div class="row align-items-center">
                        <div class="col-3 col-md-2">
                            <?php if(!empty($item['gambar'])): ?>
                                <?php 
                                // Coba beberapa path gambar
                                $image_paths = [
                                    "images/" . $item['gambar'],
                                    "uploads/" . $item['gambar'],
                                    "produk/" . $item['gambar'],
                                    $item['gambar'] // jika sudah full path
                                ];
                                
                                $found_image = false;
                                foreach($image_paths as $path) {
                                    if(file_exists($path)) {
                                        $found_image = $path;
                                        break;
                                    }
                                }
                                ?>
                                
                                <?php if($found_image): ?>
                                    <img src="<?php echo htmlspecialchars($found_image); ?>" 
                                         alt="<?php echo htmlspecialchars($item['nama']); ?>" 
                                         class="item-img"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="no-image" style="display: none;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 col-md-7">
                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['nama']); ?></h6>
                            <p class="text-muted mb-0">Jumlah: <?php echo htmlspecialchars($item['qty']); ?> item</p>
                        </div>
                        <div class="col-3 col-md-3 text-end">
                            <p class="mb-1">Rp<?php echo number_format($item['harga']); ?>/item</p>
                            <h6 class="fw-bold text-danger mb-0">Rp<?php echo number_format($item['subtotal']); ?></h6>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-cart-x" style="font-size: 3rem; color: #ddd;"></i>
                <p class="text-muted mt-2">Tidak ada item ditemukan</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- INFORMASI PENGIRIMAN -->
    <div class="detail-card">
        <h5 class="fw-bold mb-4">Informasi Pengiriman</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6><strong>Penerima</strong></h6>
                <p class="mb-1"><?php echo htmlspecialchars($order['username'] ?? 'Tidak tersedia'); ?></p>
            </div>
            <div class="col-md-6">
                <h6><strong>Alamat Pengiriman</strong></h6>
                <p class="mb-1"><?php echo htmlspecialchars($order['alamat_pengiriman'] ?? 'Tidak tersedia'); ?></p>
            </div>
        </div>
    </div>

    <!-- RINGKASAN PEMBAYARAN -->
    <div class="detail-card">
        <h5 class="fw-bold mb-4">Ringkasan Pembayaran</h5>
        <div class="summary-box">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <span>Rp<?php echo number_format($order['total_harga']); ?></span>
            </div>
            <?php if(isset($order['ongkir']) && $order['ongkir'] > 0): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ongkos Kirim</span>
                    <span>Rp<?php echo number_format($order['ongkir']); ?></span>
                </div>
            <?php endif; ?>
            <?php if(isset($order['diskon']) && $order['diskon'] > 0): ?>
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Diskon</span>
                    <span>-Rp<?php echo number_format($order['diskon']); ?></span>
                </div>
            <?php endif; ?>
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5">
                <span>Total Pembayaran</span>
                <?php 
                $total_pembayaran = $order['total_harga'];
                $total_pembayaran += $order['ongkir'] ?? 0;
                $total_pembayaran -= $order['diskon'] ?? 0;
                ?>
                <span class="text-danger">Rp<?php echo number_format($total_pembayaran); ?></span>
            </div>
            
            <div class="mt-4">
                <h6><strong>Metode Pembayaran</strong></h6>
                <div class="d-flex align-items-center mt-2">
                    <?php 
                    $payment_icon = '';
                    $metode = $order['metode_pembayaran'] ?? '';
                    
                    if(stripos($metode, 'Transfer') !== false) {
                        $payment_icon = 'bi-bank';
                    } elseif(stripos($metode, 'QRIS') !== false) {
                        $payment_icon = 'bi-qr-code';
                    } elseif(stripos($metode, 'COD') !== false || stripos($metode, 'Cash') !== false) {
                        $payment_icon = 'bi-cash';
                    } else {
                        $payment_icon = 'bi-credit-card';
                    }
                    ?>
                    <i class="bi <?php echo $payment_icon; ?> fs-4 me-2"></i>
                    <span><?php echo htmlspecialchars($metode); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>