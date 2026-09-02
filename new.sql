-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 09, 2025 at 09:02 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u630254704_hotelsystem_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','manager') DEFAULT 'admin',
  `profile_picture` varchar(255) DEFAULT NULL,
  `authy_id` varchar(50) DEFAULT NULL,
  `authy_secret` varchar(255) DEFAULT NULL,
  `authy_enabled` tinyint(1) DEFAULT 0,
  `authy_setup_complete` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`, `role`, `profile_picture`, `authy_id`, `authy_secret`, `authy_enabled`, `authy_setup_complete`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin@example.com', '2025-11-02 08:09:18', 'manager', 'admin_1_1762273326.jpg', NULL, 'GPA7ZXOXYMYEPOR426E4AWHXR7542XT5', 1, 1),
(4, 'rajdeep', 'e10adc3949ba59abbe56e057f20f883e', 'rajdeep@gmail.com', '2025-11-04 05:26:12', 'manager', 'admin_4_1762273311.jpeg', NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out` date NOT NULL,
  `check_out_time` time DEFAULT NULL,
  `nights` int(11) DEFAULT NULL,
  `adults` int(11) NOT NULL DEFAULT 1,
  `children` int(11) NOT NULL DEFAULT 0,
  `rooms` int(11) NOT NULL DEFAULT 1,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `arrival_status` enum('not_arrived','arrived','checked_out') DEFAULT 'not_arrived',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `booking_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `name`, `email`, `phone`, `check_in`, `check_in_time`, `check_out`, `check_out_time`, `nights`, `adults`, `children`, `rooms`, `special_requests`, `status`, `payment_status`, `arrival_status`, `created_at`, `booking_number`) VALUES
(1, 3, 'Rajdeep Pandit', 'helloanshu.dev@gmail.com', '6295253239', '2025-11-03', '17:11:00', '2025-11-04', '17:11:00', 1, 2, 1, 1, '', 'confirmed', 'paid', 'arrived', '2025-11-02 09:38:29', 'BK2025652099'),
(2, 4, 'tester1', 'tester1@gmail.com', '+911234567891', '2025-11-04', NULL, '2025-11-05', NULL, NULL, 1, 0, 1, '', 'confirmed', 'paid', 'not_arrived', '2025-11-02 10:16:51', 'BK2025606751'),
(3, 4, 'tester1', 'tester1@gmail.com', '+911234567891', '2025-11-02', '12:54:00', '2025-11-05', '12:54:00', NULL, 2, 0, 1, '', 'confirmed', 'paid', 'not_arrived', '2025-11-02 10:19:41', 'BK20251102123103'),
(4, 4, 'tester1', 'tester1@gmail.com', '+911234567891', '2025-11-03', NULL, '2025-11-04', NULL, NULL, 2, 1, 2, '', 'confirmed', 'paid', 'not_arrived', '2025-11-02 10:20:54', 'BK20251102924701'),
(5, 5, 'John Doe', 'john.doe@example.com', '1234567890', '2025-10-01', NULL, '2025-10-05', NULL, NULL, 2, 0, 1, 'Near window room preferred', 'confirmed', 'paid', 'not_arrived', '2025-11-02 11:02:18', 'BK20251001001'),
(6, 5, 'John Doe', 'john.doe@example.com', '1234567890', '2025-11-10', NULL, '2025-11-15', NULL, NULL, 2, 1, 1, 'Birthday celebration', 'confirmed', 'paid', 'not_arrived', '2025-11-02 11:02:18', 'BK20251110001'),
(7, 6, 'Jane Smith', 'jane.smith@example.com', '0987654321', '2025-09-15', NULL, '2025-09-20', NULL, NULL, 1, 0, 1, '', 'confirmed', 'paid', 'not_arrived', '2025-11-02 11:02:18', 'BK20250915001'),
(8, 6, 'Jane Smith', 'jane.smith@example.com', '0987654321', '2025-10-25', NULL, '2025-10-30', NULL, NULL, 1, 0, 1, 'Quiet room needed', 'confirmed', 'paid', 'not_arrived', '2025-11-02 11:02:18', 'BK20251025001'),
(9, 7, 'Robert Johnson', 'robert.j@example.com', '1112223333', '2025-08-20', NULL, '2025-08-25', NULL, NULL, 2, 2, 2, 'Family vacation', 'confirmed', 'paid', 'not_arrived', '2025-11-02 11:02:18', 'BK20250820001'),
(10, 8, 'Emily Davis', 'emily.davis@example.com', '4445556666', '2025-07-10', NULL, '2025-07-15', NULL, NULL, 1, 0, 1, 'Honeymoon suite', 'confirmed', 'paid', 'not_arrived', '2025-11-02 11:02:18', 'BK20250710001'),
(11, 8, 'Emily Davis', 'emily.davis@example.com', '4445556666', '2025-11-05', NULL, '2025-11-10', NULL, NULL, 2, 0, 1, '', 'confirmed', 'paid', 'not_arrived', '2025-11-02 11:02:18', 'BK20251105001'),
(12, 9, 'Michael Wilson', 'michael.w@example.com', '7778889999', '2025-06-05', NULL, '2025-06-10', NULL, NULL, 1, 0, 1, 'Business trip', 'confirmed', 'paid', 'not_arrived', '2025-11-02 11:02:18', 'BK20250605001'),
(13, 9, 'Michael Wilson', 'michael.w@example.com', '7778889999', '2025-09-30', NULL, '2025-10-05', NULL, NULL, 1, 0, 1, 'Conference attendance', 'confirmed', 'paid', 'not_arrived', '2025-11-02 11:02:18', 'BK20250930001'),
(14, 9, 'Michael Wilson', 'michael.w@example.com', '7778889999', '2025-12-15', NULL, '2025-12-20', NULL, NULL, 1, 0, 1, 'Christmas vacation', 'confirmed', 'paid', 'not_arrived', '2025-11-02 11:02:18', 'BK20251215001'),
(15, 4, 'tester1', 'tester1@gmail.com', '+911234567891', '2025-11-03', '18:00:00', '2025-11-05', '08:00:00', 3, 2, 0, 1, '', 'pending', 'paid', 'checked_out', '2025-11-02 17:02:01', 'BK20251102106729'),
(16, 4, 'tester1', 'tester1@gmail.com', '+911234567891', '2025-11-04', NULL, '2025-11-06', NULL, NULL, 2, 1, 1, '', 'pending', 'pending', 'not_arrived', '2025-11-04 15:33:44', 'BK20251104807373');

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `name`, `description`, `address`, `phone`, `email`, `created_at`) VALUES
(1, 'Demo Hotel & Resort', 'Experience luxury and tranquility in the heart of Demo City. Our resort offers stunning views of the Himalayas, amenities, and exceptional service.', 'Demo Hotel & Resort 123 Demo Street, Demo City, Demo State', '1234567890', 'info@demohotel.com', '2025-11-02 08:09:18');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_images`
--

CREATE TABLE `hotel_images` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `usage_type` enum('carousel','cover','both') DEFAULT 'carousel',
  `carousel_position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotel_images`
--

INSERT INTO `hotel_images` (`id`, `hotel_id`, `image_path`, `caption`, `created_at`, `usage_type`, `carousel_position`) VALUES
(1, 1, 'uploads/IMG-20251102-WA0008.jpg', 'Hotel Image', '2025-11-02 08:18:19', 'carousel', 10),
(2, 1, 'uploads/IMG-20251102-WA0009.jpg', 'Hotel Image', '2025-11-02 08:18:19', 'carousel', 6),
(9, 1, 'uploads/IMG-20251102-WA0016.jpg', 'Hotel Image', '2025-11-02 08:18:19', 'carousel', 4),
(10, 1, 'uploads/IMG-20251102-WA0017.jpg', 'Hotel Image', '2025-11-02 08:18:19', 'carousel', 1),
(11, 1, 'uploads/IMG-20251102-WA0018.jpg', 'Hotel Image', '2025-11-02 08:18:19', 'carousel', 7),
(25, 1, 'uploads/IMG-20251102-WA0032.jpg', 'Hotel Image', '2025-11-02 08:18:19', 'carousel', 9),
(27, 1, 'uploads/IMG-20251102-WA0034.jpg', 'Hotel Image', '2025-11-02 08:18:19', 'carousel', 8),
(29, 1, 'uploads/IMG-20251102-WA0036.jpg', 'Hotel Image', '2025-11-02 08:18:19', 'carousel', 9),
(30, 1, 'uploads/IMG-20251102-WA0037.jpg', 'Hotel Image', '2025-11-02 08:18:19', 'carousel', 2),
(31, 1, 'uploads/IMG-20251102-WA0038.jpg', 'Hotel Image', '2025-11-02 08:18:19', 'carousel', 5);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `name`, `subscribed_at`) VALUES
(1, 'helloanshu.dev@gmail.com', 'Rajdeep', '2025-11-02 10:42:56'),
(2, 'helloanshu.dev1@gmail.com', 'Rajdeep', '2025-11-02 10:47:52');

-- --------------------------------------------------------

--
-- Table structure for table `popups`
--

CREATE TABLE `popups` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `show_after_seconds` int(11) DEFAULT 5,
  `show_frequency` enum('always','once_per_session','once_per_day') DEFAULT 'once_per_session',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `popups`
--

INSERT INTO `popups` (`id`, `title`, `image_path`, `link_url`, `is_active`, `show_after_seconds`, `show_frequency`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 'Testing', 'uploads/popups/6909c30203928_60ad19a5c27cac2682df8663d7ec8bd7.jpg', 'https://www.youtube.com/watch?v=PceRL9Az6GM&ab_channel=BhajanMarg', 1, 1, 'always', '2025-11-04 14:55:00', '2025-11-05 14:59:00', '2025-11-04 09:10:26', '2025-11-04 09:25:19');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `booking_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(11, 5, 5, 5, 'Absolutely wonderful experience! The staff was incredibly friendly and the room was spotless. Will definitely be coming back.', 'approved', '2025-11-02 11:10:27'),
(13, 6, 7, 5, 'Perfect location and amazing hospitality. The hotel exceeded all our expectations. Highly recommended!', 'approved', '2025-11-02 11:10:27'),
(15, 7, 9, 4, 'Family-friendly hotel with great amenities. The kids loved the pool area. Staff was very accommodating.', 'approved', '2025-11-02 11:10:27'),
(16, 8, 10, 5, 'Romantic getaway was perfect. The honeymoon suite was beautiful and the service was impeccable.', 'approved', '2025-11-02 11:10:27'),
(18, 9, 12, 4, 'Good business hotel with reliable Wi-Fi and comfortable rooms. Convenient location for meetings.', 'approved', '2025-11-02 11:10:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `country`, `created_at`) VALUES
(3, 'Rajdeep Pandit', 'helloanshu.dev@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', '6295253239', 'India', '2025-11-02 09:26:43'),
(4, 'tester1', 'tester1@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', '+911234567891', 'India', '2025-11-02 10:12:36'),
(5, 'Smith ', 'john.doe@example.com', '482c811da5d5b4bc6d497ffa98491e38', '1234567890', 'United States', '2025-11-02 11:02:18'),
(6, 'Smirti', 'jane.smith@example.com', '482c811da5d5b4bc6d497ffa98491e38', '0987654321', 'United Kingdom', '2025-11-02 11:02:18'),
(7, 'Krish', 'robert.j@example.com', '482c811da5d5b4bc6d497ffa98491e38', '1112223333', 'Canada', '2025-11-02 11:02:18'),
(8, 'Raghu', 'emily.davis@example.com', '482c811da5d5b4bc6d497ffa98491e38', '4445556666', 'Australia', '2025-11-02 11:02:18'),
(9, 'Saurav', 'michael.w@example.com', '482c811da5d5b4bc6d497ffa98491e38', '7778889999', 'Germany', '2025-11-02 11:02:18'),
(10, 'Sourav Jyoti Hazarika', 'assamica.tt@gmail.com', 'e0a8aa81eb1762d529783cf587f6f422', '98536290', 'India', '2025-11-08 07:45:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_number` (`booking_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotel_images`
--
ALTER TABLE `hotel_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `popups`
--
ALTER TABLE `popups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_booking_review` (`user_id`,`booking_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hotel_images`
--
ALTER TABLE `hotel_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `popups`
--
ALTER TABLE `popups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hotel_images`
--
ALTER TABLE `hotel_images`
  ADD CONSTRAINT `hotel_images_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
