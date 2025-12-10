-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2025 at 03:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chilova_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `alamat`
--

CREATE TABLE `alamat` (
  `alamat_id` int(12) NOT NULL,
  `user_id` int(12) NOT NULL,
  `nama_penerima` varchar(100) NOT NULL,
  `no_telepon` varchar(20) NOT NULL,
  `alamat_lengkap` text NOT NULL,
  `provinsi` varchar(100) NOT NULL,
  `kota` varchar(100) NOT NULL,
  `kode_pos` varchar(10) NOT NULL,
  `is_default_alamat` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alamat`
--

INSERT INTO `alamat` (`alamat_id`, `user_id`, `nama_penerima`, `no_telepon`, `alamat_lengkap`, `provinsi`, `kota`, `kode_pos`, `is_default_alamat`) VALUES
(1, 1, 'Faoziah', '6281999400148', 'Jalan Banda Seraya', 'NTB', 'Mataram', '83116', 0),
(4, 1, 'Coco', '6281999400112', 'Jempong Timur gang H.Yasin', 'Nusa Tenggara Barat', 'Mataram', '83117', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `qty`) VALUES
(57, 1, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(10) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(10) NOT NULL,
  `total_harga` int(50) NOT NULL,
  `metode_pembayaran` enum('DANA','Cash on Delivery (COD)') NOT NULL,
  `catatan` text NOT NULL,
  `status` enum('Diproses','Dikirim','Selesai','Dibatalkan','Menunggu Pembayaran','Menunggu Verifikasi') NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `tanggal_order` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_number`, `user_id`, `total_harga`, `metode_pembayaran`, `catatan`, `status`, `alamat_pengiriman`, `tanggal_order`) VALUES
(1, 'ORD20251207052214599', 1, 39000, 'Cash on Delivery (COD)', '', 'Dibatalkan', 'Faoziah - +62 819 9940 0148\nJalan Banda Seraya, Mataram, NTB 83116', '2025-12-07 12:22:14'),
(2, 'ORD20251207052301119', 1, 39000, 'Cash on Delivery (COD)', '', 'Dibatalkan', 'Faoziah - +62 819 9940 0148\nJalan Banda Seraya, Mataram, NTB 83116', '2025-12-07 12:23:01'),
(3, 'ORD20251207054530551', 1, 15000, 'Cash on Delivery (COD)', '', 'Dibatalkan', 'Coco - +62 819 9940 0112\nJempong Timur gang H.Yasin, Mataram, Nusa Tenggara Barat 83117', '2025-12-07 12:45:30'),
(11, 'ORD20251207102649690', 1, 15000, 'Cash on Delivery (COD)', '', 'Diproses', 'Coco - +62 819 9940 0112\nJempong Timur gang H.Yasin, Mataram, Nusa Tenggara Barat 83117', '2025-12-07 17:26:49'),
(12, 'ORD20251207102816909', 1, 24000, 'Cash on Delivery (COD)', '', 'Diproses', 'Coco - +62 819 9940 0112\nJempong Timur gang H.Yasin, Mataram, Nusa Tenggara Barat 83117', '2025-12-07 17:28:16'),
(13, 'ORD20251207102920436', 1, 15000, 'Cash on Delivery (COD)', '', 'Diproses', 'Coco - +62 819 9940 0112\nJempong Timur gang H.Yasin, Mataram, Nusa Tenggara Barat 83117', '2025-12-07 17:29:20'),
(14, 'ORD20251207103711313', 1, 27000, 'Cash on Delivery (COD)', '', 'Diproses', 'Coco - +62 819 9940 0112\nJempong Timur gang H.Yasin, Mataram, Nusa Tenggara Barat 83117', '2025-12-07 17:37:11'),
(16, 'ORD20251208012137452', 1, 12000, 'DANA', '', 'Diproses', 'Coco - +62 819 9940 0112\nJempong Timur gang H.Yasin, Mataram, Nusa Tenggara Barat 83117', '2025-12-08 08:21:37'),
(17, 'ORD20251208072221370', 1, 27000, 'DANA', '', 'Diproses', 'Coco - +62 819 9940 0112\nJempong Timur gang H.Yasin, Mataram, Nusa Tenggara Barat 83117', '2025-12-08 14:22:21'),
(18, 'ORD20251208110812823', 1, 39000, 'DANA', '', 'Dikirim', 'Coco - +62 819 9940 0112\nJempong Timur gang H.Yasin, Mataram, Nusa Tenggara Barat 83117', '2025-12-08 18:08:12'),
(19, 'ORD20251209105128564', 1, 42000, 'Cash on Delivery (COD)', '', 'Diproses', 'Coco - +62 819 9940 0112\nJempong Timur gang H.Yasin, Mataram, Nusa Tenggara Barat 83117', '2025-12-09 17:51:28'),
(20, 'ORD20251209105956917', 1, 12000, 'DANA', '', 'Selesai', 'Coco - +62 819 9940 0112\nJempong Timur gang H.Yasin, Mataram, Nusa Tenggara Barat 83117', '2025-12-09 17:59:56');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `item_id` int(10) NOT NULL,
  `order_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `varian` enum('Original','Daun Jeruk','Lengkuas') NOT NULL,
  `ukuran` enum('150 g','100 g') NOT NULL,
  `harga` int(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`item_id`, `order_id`, `product_id`, `nama_produk`, `varian`, `ukuran`, `harga`, `qty`, `subtotal`) VALUES
(1, 2, 1, 'Original', 'Original', '', 12000, 2, 24000),
(2, 2, 4, 'Daun Jeruk', 'Daun Jeruk', '', 15000, 1, 15000),
(3, 3, 4, 'Daun Jeruk', 'Daun Jeruk', '', 15000, 1, 15000),
(4, 4, 5, 'Lengkuas', 'Lengkuas', '', 12000, 2, 24000),
(5, 5, 1, 'Original', 'Original', '', 12000, 3, 36000),
(6, 6, 1, 'Original', 'Original', '', 12000, 1, 12000),
(7, 7, 6, 'Lengkuas', 'Lengkuas', '', 15000, 4, 60000),
(8, 8, 1, 'Original', 'Original', '', 12000, 1, 12000),
(9, 9, 2, 'Original', 'Original', '', 15000, 1, 15000),
(10, 9, 1, 'Original', 'Original', '', 12000, 1, 12000),
(11, 10, 1, 'Original', 'Original', '', 12000, 2, 24000),
(12, 11, 4, 'Daun Jeruk', 'Daun Jeruk', '', 15000, 1, 15000),
(13, 12, 1, 'Original', 'Original', '', 12000, 1, 12000),
(14, 12, 3, 'Daun Jeruk', 'Daun Jeruk', '', 12000, 1, 12000),
(15, 13, 2, 'Original', 'Original', '', 15000, 1, 15000),
(16, 14, 1, 'Original', 'Original', '', 12000, 1, 12000),
(17, 14, 6, 'Lengkuas', 'Lengkuas', '', 15000, 1, 15000),
(18, 15, 1, 'Original', 'Original', '', 12000, 3, 36000),
(19, 16, 3, 'Daun Jeruk', 'Daun Jeruk', '', 12000, 1, 12000),
(20, 17, 6, 'Lengkuas', 'Lengkuas', '', 15000, 1, 15000),
(21, 17, 3, 'Daun Jeruk', 'Daun Jeruk', '', 12000, 1, 12000),
(22, 18, 1, 'Original', 'Original', '', 12000, 2, 24000),
(23, 18, 2, 'Original', 'Original', '', 15000, 1, 15000),
(24, 19, 3, 'Daun Jeruk', 'Daun Jeruk', '', 12000, 1, 12000),
(25, 19, 4, 'Daun Jeruk', 'Daun Jeruk', '', 15000, 1, 15000),
(26, 19, 6, 'Lengkuas', 'Lengkuas', '', 15000, 1, 15000),
(27, 20, 1, 'Original', 'Original', '', 12000, 1, 12000);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(10) NOT NULL,
  `order_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `metode_pembayaran` enum('Dana','Cod') NOT NULL,
  `payment_code` varchar(100) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('Menunggu Pembayaran','dibayar','menunggu verifikasi') NOT NULL,
  `proof_image` varchar(255) NOT NULL,
  `catatan` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `verified_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `user_id`, `metode_pembayaran`, `payment_code`, `amount`, `status`, `proof_image`, `catatan`, `created_at`, `expires_at`, `verified_by`) VALUES
(1, 4, 1, 'Dana', 'DANA_1765092167_4', 24000.00, '', '', '', '2025-12-07 15:22:47', '2025-12-07 16:22:47', 0),
(2, 5, 1, 'Dana', 'DANA_1765092241_5', 36000.00, '', '', '', '2025-12-07 15:24:01', '2025-12-07 16:24:01', 0),
(3, 6, 1, 'Dana', 'DANA_1765093428_6', 12000.00, '', '', '', '2025-12-07 15:43:48', '2025-12-07 16:43:48', 0),
(4, 7, 1, 'Dana', 'DANA_1765093897_7', 60000.00, '', '', '', '2025-12-07 15:51:37', '2025-12-07 16:51:37', 0),
(5, 8, 1, 'Dana', 'DANA_1765096897_8', 12000.00, '', '', '', '2025-12-07 16:41:37', '2025-12-07 17:41:37', 0),
(6, 9, 1, 'Dana', 'DANA_1765097326_9', 27000.00, '', '', '', '2025-12-07 16:48:46', '2025-12-07 17:48:46', 0),
(7, 10, 1, 'Dana', 'DANA_1765098260_10', 24000.00, '', '', '', '2025-12-07 17:04:20', '2025-12-07 18:04:20', 0),
(8, 15, 1, '', 'DANA_1765152733_15', 36000.00, 'menunggu verifikasi', 'uploads/bukti_pembayaran/DANA_ORD20251208011213949_1765152757.png', 'ORDER_ORD20251208011213949', '2025-12-08 08:12:13', '2025-12-08 09:12:13', 0),
(9, 16, 1, '', 'DANA_1765153297_16', 12000.00, 'menunggu verifikasi', 'uploads/bukti_pembayaran/DANA_ORD20251208012137452_1765153576.png', 'Order_ORD20251208012137452', '2025-12-08 08:21:37', '2025-12-08 09:21:37', 0),
(10, 17, 1, 'Dana', 'DANA_1765174941_17', 27000.00, '', '', '', '2025-12-08 14:22:21', '2025-12-08 15:22:21', 0),
(11, 18, 1, 'Dana', 'DANA_1765188492_18', 39000.00, '', '', '', '2025-12-08 18:08:12', '2025-12-08 19:08:12', 0),
(12, 20, 1, '', 'DANA_1765274396_20', 12000.00, 'menunggu verifikasi', 'uploads/bukti_pembayaran/DANA_ORD20251209105956917_1765274429.png', 'ORDER_ORD20251209105956917', '2025-12-09 17:59:56', '2025-12-09 18:59:56', 0);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `varian` enum('Original','Daun Jeruk','Lengkuas') NOT NULL,
  `ukuran` varchar(50) NOT NULL,
  `harga` int(11) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `stok` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `nama`, `varian`, `ukuran`, `harga`, `gambar`, `stok`) VALUES
(1, 'Original', 'Original', '100g', 12000, 'original.png', 5),
(2, 'Original', 'Original', '150g', 15000, 'original.png', 7),
(3, 'Daun Jeruk', 'Daun Jeruk', '100g', 12000, 'daun jeruk.png', 6),
(4, 'Daun Jeruk', 'Daun Jeruk', '150g', 15000, 'daun jeruk.png', 10),
(5, 'Lengkuas', 'Lengkuas', '100g', 12000, 'lengkuas.png', 6),
(6, 'Lengkuas', 'Lengkuas', '150g', 15000, 'lengkuas.png', 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(12) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `role` enum('Admin','User') NOT NULL DEFAULT 'User',
  `profile_picture` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `profile_picture`) VALUES
(1, 'chilova', '$2y$10$JEUk.ushrAHXNNfHAQeInucgXbmB/0h1Vhb7GHZnbUKq4v3VdzeQa', 'User', 'profile_1_1765077292.jpg'),
(2, 'faizah', '$2y$10$PIkiMx0Gj3zqI/e3Aw1nZ.4oIGTD9Xap3XvqGFVsNVUVEElOOTvcK', 'User', 'default.jpg'),
(3, 'admin chilova', '$2y$10$BloUofuLbDK578MiA8SkzuO43esbJl1A2HgayeEp/FXuOGfITRXcO', 'Admin', 'profile_3_1765327139.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alamat`
--
ALTER TABLE `alamat`
  ADD PRIMARY KEY (`alamat_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alamat`
--
ALTER TABLE `alamat`
  MODIFY `alamat_id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `item_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
