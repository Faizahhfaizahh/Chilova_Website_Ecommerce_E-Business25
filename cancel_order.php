<?php
session_start();
require "koneksi.php";

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    // Validasi: cek apakah order milik user ini
    $check_query = "SELECT * FROM orders WHERE order_id = $order_id AND user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    $order = mysqli_fetch_assoc($check_result);
    
    if(!$order) {
        echo json_encode(['success' => false, 'message' => 'Order tidak ditemukan']);
        exit;
    }
    
    if($action == 'cancel') {
        // Batalkan pesanan biasa
        mysqli_query($conn, "UPDATE orders SET status = 'Dibatalkan' WHERE order_id = $order_id");
        
        // Jika ada payment DANA, update juga
        mysqli_query($conn, "UPDATE payments SET status = 'dibatalkan' WHERE order_id = $order_id");
        
        echo json_encode(['success' => true, 'message' => 'Pesanan berhasil dibatalkan']);
        
    } elseif($action == 'cancel_payment') {
        // Khusus batalkan pembayaran DANA
        mysqli_query($conn, "UPDATE orders SET status = 'Dibatalkan' WHERE order_id = $order_id");
        mysqli_query($conn, "UPDATE payments SET status = 'dibatalkan' WHERE order_id = $order_id");
        
        echo json_encode(['success' => true, 'message' => 'Pembayaran DANA berhasil dibatalkan']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    }
}
?>