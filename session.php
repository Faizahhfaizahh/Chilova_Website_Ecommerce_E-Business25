<?php
session_start();

// Jika user belum login → arahkan ke login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Optional: bisa load info user langsung jika diperlukan
require "koneksi.php";

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Jika user tidak ditemukan (misal akun sudah dihapus)
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
