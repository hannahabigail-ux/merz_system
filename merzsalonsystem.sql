-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2026 at 06:19 AM
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
-- Database: `merzsalonsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Merz', 'hannahabigail2003@gmail.com', '$2y$10$vzezrTTHt.aizR2aMYw1OuT.ntPPYxkxO5FT0hzam6l0wBQsD46P6', 'admin', '2026-05-01 08:12:41');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `customer_id`, `service_id`, `booking_date`, `booking_time`, `status`, `created_at`) VALUES
(39, 4, 31, '2026-05-16', '15:49:00', 'Completed', '2026-05-07 05:48:03'),
(40, 4, 30, '2026-05-08', '14:35:00', 'Pending', '2026-05-07 05:50:29');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`) VALUES
(1, 'Hand and Foot Care'),
(2, 'Waxing and Threading'),
(3, 'Hair Care Treatment'),
(4, 'Hair Rebonding'),
(5, 'Hair Brazilian'),
(6, 'Hair Perming'),
(7, 'Hair Color'),
(8, 'Hair Bleaching'),
(9, 'Hair Treatment'),
(10, 'Other Services');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `email`, `password`, `phone`, `profile_pic`, `created_at`, `reset_code`) VALUES
(4, 'Hannah Badana', 'hannahabigail2003@gmail.com', '$2y$10$jq4GI43ycvblBAaAd2l.Ru.EHHAWNa3NMRYWigZSN4auu2DS859Yq', NULL, NULL, '2026-05-07 05:47:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finances`
--

CREATE TABLE `finances` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('Revenue','Expense') NOT NULL DEFAULT 'Revenue',
  `payment_method` varchar(50) DEFAULT 'Cash',
  `status` enum('Paid','Pending','Refunded') DEFAULT 'Paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finances`
--

INSERT INTO `finances` (`id`, `booking_id`, `customer_id`, `amount`, `type`, `payment_method`, `status`, `created_at`) VALUES
(1, NULL, NULL, 300.00, 'Revenue', 'Cash', 'Paid', '2026-05-05 01:24:25'),
(2, NULL, NULL, 400.00, 'Revenue', 'Cash', 'Paid', '2026-05-05 07:19:44'),
(3, NULL, NULL, 300.00, 'Revenue', 'Cash', 'Paid', '2026-05-05 08:21:24'),
(4, NULL, NULL, 200.00, 'Revenue', 'Cash', 'Paid', '2026-05-05 13:45:26'),
(5, NULL, NULL, 999.00, 'Revenue', 'Cash', 'Paid', '2026-05-05 14:56:17'),
(7, NULL, NULL, 15000.00, 'Expense', 'Keratin', '', '2026-05-05 16:24:15'),
(9, NULL, NULL, 700.00, 'Revenue', 'Cash', 'Paid', '2026-05-05 23:51:45'),
(10, NULL, NULL, 700.00, 'Revenue', 'Cash', 'Paid', '2026-05-06 07:00:06'),
(11, NULL, NULL, 1300.00, 'Revenue', 'Cash', 'Paid', '2026-05-06 07:48:02'),
(12, NULL, NULL, 100.00, 'Revenue', 'Cash', 'Paid', '2026-05-06 08:11:59'),
(13, 39, 4, 999.00, 'Revenue', 'Cash', 'Paid', '2026-05-07 05:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `content` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sender_id` int(11) NOT NULL,
  `sender_role` enum('customer','admin') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_role` enum('customer','admin') NOT NULL,
  `type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `content`, `is_read`, `created_at`, `sender_id`, `sender_role`, `receiver_id`, `receiver_role`, `type`) VALUES
(4, 'Hannah Abigail Badana cancelled booking #29', 1, '2026-05-06 06:36:03', 1, 'customer', 1, 'admin', 'booking_cancelled'),
(5, 'Hannah Abigail Badana booked service #31 on 2026-05-16 at 07:00', 1, '2026-05-06 06:57:36', 1, 'customer', 1, 'admin', 'booking_created'),
(6, 'Hannah Abigail Badana rescheduled booking #31 to 2026-05-20 08:25', 1, '2026-05-06 07:19:41', 1, 'customer', 1, 'admin', 'booking_rescheduled'),
(7, 'Hannah Abigail Badana booked service #19 on 2026-05-16 at 07:50', 1, '2026-05-06 07:46:05', 1, 'customer', 1, 'admin', 'booking_created'),
(8, 'Hannah Abigail Badana booked service #6 on 2026-05-23 at 09:01', 1, '2026-05-06 08:01:52', 1, 'customer', 1, 'admin', 'booking_created'),
(9, 'Your booking for service #6 on 2026-05-23 at 09:01 has been submitted.', 1, '2026-05-06 08:01:52', 1, 'admin', 1, 'customer', 'booking_created'),
(10, 'Hannah Abigail Badana cancelled booking #33', 1, '2026-05-06 08:02:53', 1, 'customer', 1, 'admin', 'booking_cancelled'),
(11, 'Your booking #33 has been cancelled.', 1, '2026-05-06 08:02:53', 1, 'admin', 1, 'customer', 'booking_cancelled'),
(12, 'Hannah Abigail Badana booked service #31 on 2026-05-14 at 08:06', 1, '2026-05-06 08:06:50', 1, 'customer', 1, 'admin', 'booking_created'),
(13, 'Your booking for service #31 on 2026-05-14 at 08:06 has been submitted.', 1, '2026-05-06 08:06:50', 1, 'admin', 1, 'customer', 'booking_created'),
(14, 'Hannah Abigail Badana booked service #13 on 2026-05-22 at 08:14', 1, '2026-05-06 08:11:32', 1, 'customer', 1, 'admin', 'booking_created'),
(15, 'Your booking for service #13 on 2026-05-22 at 08:14 has been submitted.', 1, '2026-05-06 08:11:32', 1, 'admin', 1, 'customer', 'booking_created'),
(16, 'Hannah Abigail Badana booked service #31 on 2026-05-23 at 08:24', 1, '2026-05-06 08:24:43', 1, 'customer', 1, 'admin', 'booking_created'),
(17, 'Your booking for service #31 on 2026-05-23 at 08:24 has been submitted.', 1, '2026-05-06 08:24:43', 1, 'admin', 1, 'customer', 'booking_created'),
(18, 'Hannah Abigail Badana cancelled booking #36', 1, '2026-05-06 08:25:57', 1, 'customer', 1, 'admin', 'booking_cancelled'),
(19, 'Your booking #36 has been cancelled.', 1, '2026-05-06 08:25:57', 1, 'admin', 1, 'customer', 'booking_cancelled'),
(20, 'Hannah Abigail Badana booked service #31 on 2026-05-09 at 04:28', 1, '2026-05-06 08:28:41', 1, 'customer', 1, 'admin', 'booking_created'),
(21, 'Your booking for service #31 on 2026-05-09 at 04:28 has been submitted.', 1, '2026-05-06 08:28:41', 1, 'admin', 1, 'customer', 'booking_created'),
(22, 'Hannah Abigail Badana cancelled booking #37', 1, '2026-05-06 08:40:20', 1, 'customer', 1, 'admin', 'booking_cancelled'),
(23, 'Your booking #37 has been cancelled.', 1, '2026-05-06 08:40:20', 1, 'admin', 1, 'customer', 'booking_cancelled'),
(24, 'Hannah Abigail Badana booked service #30 on 2026-05-09 at 15:41', 1, '2026-05-07 05:40:44', 1, 'customer', 1, 'admin', 'booking_created'),
(25, 'Your booking for service #30 on 2026-05-09 at 15:41 has been submitted.', 1, '2026-05-07 05:40:44', 1, 'admin', 1, 'customer', 'booking_created'),
(26, 'Hannah Badana booked service #31 on 2026-05-16 at 15:49', 1, '2026-05-07 05:48:03', 4, 'customer', 1, 'admin', 'booking_created'),
(27, 'Your booking for service #31 on 2026-05-16 at 15:49 has been submitted.', 1, '2026-05-07 05:48:03', 1, 'admin', 4, 'customer', 'booking_created'),
(28, 'Hannah Badana booked service #30 on 2026-05-08 at 14:35', 1, '2026-05-07 05:50:29', 4, 'customer', 1, 'admin', 'booking_created'),
(29, 'Your booking for service #30 on 2026-05-08 at 14:35 has been submitted.', 1, '2026-05-07 05:50:29', 1, 'admin', 4, 'customer', 'booking_created'),
(30, 'Your booking #39 has been marked complete.', 1, '2026-05-07 05:57:16', 1, 'admin', 4, 'customer', 'booking_completed');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) DEFAULT NULL CHECK (`duration` > 0),
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `category_id`, `service_name`, `description`, `price`, `duration`, `status`) VALUES
(1, 1, 'Manicure/Pedicure', 'Basic hand and foot care', 150.00, 60, 'Inactive'),
(2, 1, 'Footspa with Pedicure', 'Relaxing footspa plus pedicure', 300.00, 90, 'Inactive'),
(3, 1, 'Gel Polish', 'Long-lasting gel polish', 400.00, 60, 'Active'),
(4, 1, 'Nail Extensions', 'Stylish nail enhancements', 600.00, 90, 'Active'),
(5, 1, 'Eyelash Extension', 'Enhance eyelashes', 300.00, 60, 'Active'),
(6, 1, 'Handspa', 'Moisturizing hand spa treatment', 249.00, 60, 'Active'),
(7, 1, 'Footspa', 'Relaxing foot spa treatment', 299.00, 60, 'Active'),
(8, 1, 'Foot Massage', 'Soothing foot massage', 349.00, 60, 'Active'),
(9, 2, 'Eyebrows Threading', 'Shaping and cleaning eyebrows', 150.00, 30, 'Active'),
(10, 2, 'Under Arm Waxing', 'Smooth underarm waxing', 350.00, 30, 'Active'),
(11, 3, 'Hair Color', 'Standard hair coloring', 500.00, 90, 'Active'),
(12, 3, 'Hair Cellophane', 'Adds shine and color', 600.00, 90, 'Active'),
(13, 3, 'Hair Blower', 'Hair drying service', 100.00, 30, 'Active'),
(14, 3, 'Hair Iron', 'Hair straightening with iron', 200.00, 30, 'Active'),
(15, 3, 'Brazilian Rebond', 'Smooth and shiny rebonding', 1500.00, 180, 'Active'),
(16, 3, 'Hair Treatments Brazilian', 'Brazilian hair treatment', 1800.00, 180, 'Active'),
(17, 3, 'Hair Shampoo with Blower Iron', 'Shampoo plus styling', 300.00, 60, 'Active'),
(18, 4, 'Loreal Rebond', 'Loreal brand hair rebonding', 1500.00, 180, 'Active'),
(19, 4, 'Matrix Rebond', 'Matrix brand hair rebonding', 1300.00, 180, 'Active'),
(20, 4, 'Natural Rebond', 'Natural rebonding treatment', 999.00, 180, 'Active'),
(21, 4, 'Wella Rebond', 'Wella brand hair rebonding', 1500.00, 180, 'Active'),
(22, 5, 'Icure Keratin Brazilian', 'Keratin Brazilian treatment', 1500.00, 120, 'Active'),
(23, 5, 'Pure Keratin Brazilian', 'Pure keratin treatment', 1000.00, 120, 'Active'),
(24, 6, 'Loreal Perm', 'Loreal brand hair perming', 1300.00, 120, 'Active'),
(25, 6, 'Wella Perm', 'Wella brand hair perming', 1200.00, 120, 'Active'),
(26, 6, 'Natural Perm', 'Natural perming treatment', 1200.00, 120, 'Active'),
(27, 7, 'Loreal Hair Color', 'Loreal brand hair coloring', 1300.00, 90, 'Active'),
(28, 7, 'Matrix Hair Color', 'Matrix brand hair coloring', 1000.00, 90, 'Active'),
(29, 7, 'Natural Hair Color', 'Natural hair coloring', 800.00, 90, 'Active'),
(30, 8, 'Balayage Bleach', 'Balayage hair bleaching style', 1200.00, 120, 'Active'),
(31, 8, 'Full Bleach', 'Full hair bleaching', 999.00, 120, 'Active'),
(32, 8, 'Highlights', 'Hair highlights bleaching', 700.00, 90, 'Active'),
(33, 9, 'Hair Cellophane Shine Moist', 'Adds shine and moisture', 800.00, 90, 'Active'),
(34, 9, 'Loreal Hair Spa', 'Loreal hair spa treatment', 980.00, 90, 'Active'),
(35, 9, 'Plant Bio Hair Care Treatment', 'Plant-based hair care', 500.00, 90, 'Active'),
(36, 10, 'Hair & Make Up', 'Professional hair and makeup', 600.00, 90, 'Active'),
(37, 10, 'Mens Haircut', 'Haircut for men', 150.00, 30, 'Active'),
(38, 10, 'Womens Haircut', 'Haircut for women', 150.00, 30, 'Active'),
(39, 10, 'Wart Removal & Cauterization', 'Skin treatment service', 1000.00, 60, 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking_feedback` (`customer_id`,`booking_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `finances`
--
ALTER TABLE `finances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `finances`
--
ALTER TABLE `finances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `finances`
--
ALTER TABLE `finances`
  ADD CONSTRAINT `finances_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `finances_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
