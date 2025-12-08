<?php
session_start();
require "koneksi.php";

if(!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$order_id = $_POST['order_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Cek apakah order milik user ini
$check_query = "SELECT o.*, p.* 
                FROM orders o
                LEFT JOIN payments p ON o.order_id = p.order_id
                WHERE o.order_id = $order_id 
                AND o.user_id = $user_id
                AND p.metode_pembayaran = 'DANA'";

$result = mysqli_query($conn, $check_query);
$data = mysqli_fetch_assoc($result);

if(!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Order tidak ditemukan']);
    exit;
}

// Cek status payment dari database
$payment_status = $data['status'] ?? 'menunggu';

switch($payment_status) {
    case 'dibayar':
        // Update order status
        mysqli_query($conn, "UPDATE orders SET status = 'Diproses' WHERE order_id = $order_id");
        echo json_encode(['status' => 'dibayar']);
        break;
        
    case 'gagal':
        echo json_encode(['status' => 'gagal']);
        break;
        
    case 'expired':
        mysqli_query($conn, "UPDATE orders SET status = 'Dibatalkan' WHERE order_id = $order_id");
        echo json_encode(['status' => 'kedaluwarsa']);
        break;
        
    case 'menunggu':
    default:
        // SIMULASI: Untuk demo, kita buat 50% chance pembayaran berhasil
        // Di produksi, ganti dengan API DANA sebenarnya
        $random_check = rand(1, 100);
        
        if($random_check <= 50 && $data['payment_code'] != '') {
            // SIMULASI: Pembayaran berhasil
            mysqli_query($conn, "UPDATE payments SET status = 'dibayar' WHERE order_id = $order_id");
            mysqli_query($conn, "UPDATE orders SET status = 'Diproses' WHERE order_id = $order_id");
            
            // Log transaksi
            mysqli_query($conn, "INSERT INTO payment_logs (order_id, status, created_at) 
                                 VALUES ($order_id, 'dibayar', NOW())");
            
            echo json_encode(['status' => 'dibayar']);
        } else {
            echo json_encode(['status' => 'menunggu']);
        }
        break;
}
?>