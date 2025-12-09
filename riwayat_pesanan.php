<?php
session_start();
require "koneksi.php";
require "function.php";

if(!isset($_SESSION['user_id'])){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil orders dengan status hanya 'Selesai' dan 'Dibatalkan'
$query = "SELECT * FROM orders 
          WHERE user_id = $user_id 
          AND status IN ('Selesai', 'Dibatalkan')
          ORDER BY tanggal_order DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
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
        .status-badge {
            font-size: 0.85rem;
            padding: 5px 12px;
            border-radius: 20px;
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
            <i class="bi bi-receipt"></i> Riwayat Pesanan
        </h2>
    </div>

    <?php if(mysqli_num_rows($result) == 0): ?>
        <div class="text-center py-5">
            <i class="bi bi-receipt" style="font-size: 4rem; color: #ddd;"></i>
            <h4 class="mt-3">Belum ada riwayat pesanan</h4>
            <p class="text-muted">Pesanan yang sudah selesai atau dibatalkan akan muncul di sini</p>
        </div>
    <?php else: ?>
        <?php while($order = mysqli_fetch_assoc($result)): ?>
            <?php
            // Tentukan class badge berdasarkan status
            $status_class = '';
            if($order['status'] == 'Selesai') {
                $status_class = 'status-selesai';
            } elseif($order['status'] == 'Dibatalkan') {
                $status_class = 'status-dibatalkan';
            }
            ?>
            
            <div class="order-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Order #<?php echo htmlspecialchars($order['order_number']); ?></h6>
                        <small class="text-muted"><?php echo date('d M Y H:i', strtotime($order['tanggal_order'])); ?></small>
                    </div>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-1"><strong>Metode Bayar:</strong> <?php echo htmlspecialchars($order['metode_pembayaran']); ?></p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <h5 class="text-danger fw-bold">Rp<?php echo number_format($order['total_harga']); ?></h5>
                    </div>
                </div>
                
                <div class="text-end mt-3">
                    <a href="lihat_detail_riwayat.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-eye me-1"></i> Lihat Detail
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>