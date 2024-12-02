-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2024 at 10:59 AM
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
-- Database: `db_inventory_system1`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `user_id` int(255) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee') NOT NULL DEFAULT 'employee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`user_id`, `user_name`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$k7OThKJbMRdfzL9GF7UMveT9ZNQeCMKPqCHQcQicN/fPbvpHSAdS2', 'admin'),
(6, 'employee1', '$2y$10$Y.4gBl8iOuMCPSlAoUq0PuRCCg7PzV7mIGwT7cTlvObpqcjsdyaMK', 'employee');

-- --------------------------------------------------------

--
-- Table structure for table `tb_deleted_orders`
--

CREATE TABLE `tb_deleted_orders` (
  `order_id` int(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `quantity` int(255) NOT NULL,
  `order_date` date NOT NULL,
  `status` int(255) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_deleted_orders`
--

INSERT INTO `tb_deleted_orders` (`order_id`, `customer_name`, `product_name`, `size`, `quantity`, `order_date`, `status`, `deleted_at`) VALUES
(123, 'wawa', 'Angle Bar6 (Green)', '1½ x 1½ ', 1, '2024-11-17', 0, '2024-11-30 15:24:35'),
(124, 'wawa', 'Angle Bar5 (Green)', '1x1', 3, '2024-11-17', 0, '2024-11-30 15:24:38'),
(125, 'wawa', 'Purlins1 (1.2)', '2X3', 2, '2024-11-17', 0, '2024-11-30 15:24:41'),
(126, 'wawa', 'Gi-pipes2', '¾', 2, '2024-11-17', 0, '2024-11-30 15:24:45'),
(128, 'des', 'Purlins2 (1.5)', '2x3', 1, '2024-11-18', 0, '2024-11-30 15:24:57'),
(130, 'Omar B', 'Angle Bar5 (Green)', '1x1', 1, '2024-11-17', 0, '2024-11-30 15:24:54'),
(138, 'gabieee', 'Angle Bar2', '1½ x 1½', 1, '2024-11-20', 0, '2024-11-30 15:24:51'),
(139, 'ariel', 'Steel Matting1', '6', 1, '2024-11-20', 0, '2024-11-30 15:24:48'),
(140, 'ariel', 'Purlins2 (1.5)', '2x3', 1, '2024-11-20', 0, '2024-11-30 15:24:47'),
(144, 'hakdog', 'Gi-pipes1', '½', 1, '2024-12-01', 0, '2024-12-02 08:34:55'),
(146, 'HASHNCXA', 'Gi-pipes6', '2', 1, '2024-12-01', 0, '2024-12-01 03:40:27'),
(147, 'HASHNCXA', 'Gi-pipes4', '1 1⁄4', 2, '2024-12-01', 0, '2024-12-01 03:40:28'),
(149, 'dogie', 'Gi-pipes2', '¾', 1, '2025-01-01', 0, '2024-12-01 06:08:53'),
(150, 'wewe', 'Angle Bar1', '1x1', 11, '2024-12-01', 0, '2024-12-02 08:34:28'),
(156, 'employee', 'GI-PIPES2', '¾', 1, '2024-12-02', 0, '2024-12-02 03:07:19'),
(157, 'employee', 'GI-PIPES1', '½', 1, '2024-12-02', 0, '2024-12-02 03:07:20'),
(161, 'loyd', 'GI-PIPES1', '½', 5, '2024-12-02', 0, '2024-12-02 08:34:04');

-- --------------------------------------------------------

--
-- Table structure for table `tb_inventory`
--

CREATE TABLE `tb_inventory` (
  `product_id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `main_category` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_inventory`
--

INSERT INTO `tb_inventory` (`product_id`, `name`, `price`, `category`, `quantity`, `size`, `image_url`, `main_category`) VALUES
(10095, 'GI-PIPES1', '260', 'GI-PIPES', '7', '½', 'uploads/pr5.jpg', 'CONSTRUCTION'),
(10096, 'GI-PIPES2', '360', 'GI-PIPES', '28', '¾', 'uploads/pr5.jpg', 'CONSTRUCTION'),
(10097, 'GI-PIPES3', '480', 'GI-PIPES', '25', '1', 'uploads/pr5.jpg', 'CONSTRUCTION'),
(10098, 'GI-PIPES4', '540', 'GI-PIPES', '30', '1¼', 'uploads/pr5.jpg', 'CONSTRUCTION'),
(10099, 'GI-PIPES5', '780', 'GI-PIPES', '23', '1 ½', 'uploads/pr5.jpg', 'CONSTRUCTION'),
(10100, 'GI-PIPES6', '1150', 'GI-PIPES', '19', '2', 'uploads/pr5.jpg', 'CONSTRUCTION'),
(10101, 'ANGLE BAR1', '350', 'ANGLE BAR', '28', '1x1', 'uploads/23.-SS-ANGLE-BAR.jpg', 'CONSTRUCTION'),
(10102, 'ANGLE BAR2', '480', 'ANGLE BAR', '25', '1 ½ x 1 ½', 'uploads/23.-SS-ANGLE-BAR.jpg', 'CONSTRUCTION'),
(10103, 'ANGLE BAR3', '590', 'ANGLE BAR', '15', '2x2', 'uploads/23.-SS-ANGLE-BAR.jpg', 'CONSTRUCTION'),
(10104, 'ANGLE BAR4 (GREEN)', '700', 'ANGLE BAR', '18', '2x2', 'uploads/23.-SS-ANGLE-BAR.jpg', 'CONSTRUCTION'),
(10106, 'ANGLE BAR5 (GREEN)', '420', 'ANGLE BAR', '19', '1x1', 'uploads/23.-SS-ANGLE-BAR.jpg', 'CONSTRUCTION'),
(10107, 'ANGLE BAR6 (GREEN)', '580', 'ANGLE BAR', '29', '1 ½ x 1 ½', 'uploads/23.-SS-ANGLE-BAR.jpg', 'CONSTRUCTION'),
(10108, 'PURLINS1 (1.2)', '360', 'PURLINS', '23', '2x3', 'uploads/steel-purlins-min.jpg', 'CONSTRUCTION'),
(10109, 'PURLINS2 (1.5)', '460', 'PURLINS', '24', '2x3', 'uploads/steel-purlins-min.jpg', 'CONSTRUCTION'),
(10110, 'PURLINS3 (1.2)', '420', 'PURLINS', '14', '2x4', 'uploads/steel-purlins-min.jpg', 'CONSTRUCTION'),
(10111, 'PURLINS4 (1.5)', '520', 'PURLINS', '28', '2x4', 'uploads/steel-purlins-min.jpg', 'CONSTRUCTION'),
(10112, 'PURLINS5 (1.2)', '620', 'PURLINS', '20', '2x6', 'uploads/steel-purlins-min.jpg', 'CONSTRUCTION'),
(10113, 'PURLINS6 (1.5)', '640', 'PURLINS', '10', '2x6', 'uploads/steel-purlins-min.jpg', 'CONSTRUCTION'),
(10114, 'STEEL MATTING1', '640', 'STEEL MATTING', '24', '6', 'uploads/steelmatting.png', 'CONSTRUCTION'),
(10115, 'STEEL MATTING2', '420', 'STEEL MATTING', '22', '4', 'uploads/steelmatting.png', 'CONSTRUCTION'),
(10116, 'FLAT BAR(RED)', '250', 'FLAT BAR', '21', '1', 'uploads/flat4.jpg', 'CONSTRUCTION'),
(10117, 'FLAT BAR(RED)', '390', 'FLAT BAR', '20', '1 ½', 'uploads/flat4.jpg', 'CONSTRUCTION'),
(10118, 'FLAT BAR(RED)', '460', 'FLAT BAR', '19', '2', 'uploads/flat4.jpg', 'CONSTRUCTION'),
(10119, 'PLYWOOD', '420', 'MARINE PLYWOOD', '20', '4ft x 8ft', 'uploads/plywood.jpg', 'ELECTRICAL');

-- --------------------------------------------------------

--
-- Table structure for table `tb_orders`
--

CREATE TABLE `tb_orders` (
  `order_id` int(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `order_date` date NOT NULL,
  `status` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_orders`
--

INSERT INTO `tb_orders` (`order_id`, `customer_name`, `product_name`, `quantity`, `order_date`, `status`, `size`, `deleted_at`, `amount_paid`) VALUES
(120, 'josh', 'Angle Bar5 (Green)', '1', '2024-11-20', '', '1x1', NULL, 0.00),
(121, 'josh', 'Purlins2 (1.5)', '2', '2024-11-20', '', '2x3', NULL, 0.00),
(122, 'josh', 'Flat Bar2', '2', '2024-11-17', 'Pending', '1 ½', NULL, 0.00),
(132, 'waren', 'Angle Bar6 (Green)', '1', '2024-11-20', 'Pending', '1½ x 1½ ', NULL, 0.00),
(133, 'waren', 'Purlins2 (1.5)', '2', '2024-11-20', 'Pending', '2x3', NULL, 0.00),
(141, 'janeD', 'Purlins4 (1.5)', '1', '2024-12-01', 'Pending', '2x4', NULL, 0.00),
(142, 'janeD', 'Gi-pipes2', '1', '2024-12-01', 'Pending', '¾', NULL, 0.00),
(143, 'bang', 'Gi-pipes1', '1', '2024-12-02', '', '½', NULL, 0.00),
(145, 'hakdog', 'Flat Bar3', '2', '2024-12-01', 'Pending', '2', NULL, 0.00),
(148, 'black', 'Gi-pipes2', '1', '2024-12-01', 'Pending', '¾', NULL, 0.00),
(151, 'jake', 'GI-PIPES1', '2', '2024-12-01', 'Pending', '½', NULL, 0.00),
(152, 'jake', 'GI-PIPES2', '1', '2024-12-01', 'Pending', '¾', NULL, 0.00),
(153, 'jake', 'GI-PIPES6', '1', '2024-12-01', 'Pending', '2', NULL, 0.00),
(154, 'johnny', 'GI-PIPES1', '2', '2024-10-02', 'Pending', '½', NULL, 0.00),
(155, 'johnny', 'ANGLE BAR1', '1', '2024-10-02', 'Pending', '1x1', NULL, 0.00),
(158, 'MJ', 'ANGLE BAR1', '1', '2024-09-02', 'Pending', '1x1', NULL, 0.00),
(159, 'MJ', 'ANGLE BAR5 (GREEN)', '1', '2024-09-02', 'Pending', '1x1', NULL, 0.00),
(160, 'MJ', 'PURLINS3 (1.2)', '1', '2024-09-02', 'Pending', '2x4', NULL, 0.00),
(162, 'joshuaaa', 'GI-PIPES1', '3', '2024-12-02', '', '½', NULL, 0.00),
(163, 'joshuaaa', 'ANGLE BAR6 (GREEN)', '1', '2024-12-02', '', '1 ½ x 1 ½', NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_order_items`
--

CREATE TABLE `tb_order_items` (
  `item_id` int(255) NOT NULL,
  `order_id` int(255) NOT NULL,
  `product_id` int(255) NOT NULL,
  `quantity` int(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_settings`
--

CREATE TABLE `tb_settings` (
  `id` int(255) NOT NULL,
  `setting_name` varchar(255) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_date`, `amount`) VALUES
(1, '2025-01-15', 200.00),
(2, '2025-02-11', 500.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `tb_deleted_orders`
--
ALTER TABLE `tb_deleted_orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `tb_inventory`
--
ALTER TABLE `tb_inventory`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `tb_orders`
--
ALTER TABLE `tb_orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `tb_order_items`
--
ALTER TABLE `tb_order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `tb_settings`
--
ALTER TABLE `tb_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_admin`
--
ALTER TABLE `tb_admin`
  MODIFY `user_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tb_deleted_orders`
--
ALTER TABLE `tb_deleted_orders`
  MODIFY `order_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT for table `tb_inventory`
--
ALTER TABLE `tb_inventory`
  MODIFY `product_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10121;

--
-- AUTO_INCREMENT for table `tb_orders`
--
ALTER TABLE `tb_orders`
  MODIFY `order_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT for table `tb_order_items`
--
ALTER TABLE `tb_order_items`
  MODIFY `item_id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_settings`
--
ALTER TABLE `tb_settings`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_order_items`
--
ALTER TABLE `tb_order_items`
  ADD CONSTRAINT `tb_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `tb_order_items` (`item_id`),
  ADD CONSTRAINT `tb_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `tb_order_items` (`item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
