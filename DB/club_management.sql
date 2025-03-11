-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2025 at 03:52 AM
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
-- Database: `club_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('present','absent') DEFAULT 'absent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `event_id`, `user_id`, `status`, `created_at`) VALUES
(13, 1, 7, 'absent', '2025-03-01 10:08:15'),
(14, 1, 8, 'present', '2025-03-01 10:08:20'),
(15, 1, 9, 'present', '2025-03-01 10:08:20'),
(16, 2, 7, 'present', '2025-03-01 11:21:46'),
(17, 2, 8, 'present', '2025-03-01 11:21:48'),
(18, 2, 6, 'absent', '2025-03-01 11:21:49'),
(19, 2, 9, 'present', '2025-03-01 11:21:51'),
(20, 5, 7, 'present', '2025-03-04 01:19:13');

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`id`, `name`, `description`, `created_at`, `status`, `image_url`) VALUES
(1, 'KARATE', 'Đam mê võ thuật bơi vào đây', '2025-02-26 15:36:19', 'active', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741490302/clubs/tpwzospwevjfybpx6ogl.jpg'),
(2, 'BÓNG ĐÁ', 'Mê bóng thì vào', '2025-02-26 15:59:36', 'active', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741490146/clubs/hbjw1sdnha2hskmach3k.jpg'),
(3, 'NHẠC KỊCH', 'Ca hát là đam mê, diễn xuất là thượng hạng.', '2025-02-27 07:23:19', 'active', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741490410/clubs/fzmavshxtcloynmaym8o.png'),
(5, 'Mạng Máy Tính', 'Chia sẻ đam mê với chuyên nghành mạng máy tính', '2025-03-04 01:16:25', 'active', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741490322/clubs/lwfhqai5ndmfql6xilzi.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `club_leaders`
--

CREATE TABLE `club_leaders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `club_id` int(11) DEFAULT NULL,
  `appointed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_leaders`
--

INSERT INTO `club_leaders` (`id`, `user_id`, `club_id`, `appointed_at`) VALUES
(1, 5, 1, '2025-02-26 15:40:34'),
(2, 6, 2, '2025-02-26 16:51:06'),
(3, 10, 3, '2025-03-03 02:00:47'),
(4, 11, 5, '2025-03-04 01:16:42');

-- --------------------------------------------------------

--
-- Table structure for table `club_members`
--

CREATE TABLE `club_members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `club_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_members`
--

INSERT INTO `club_members` (`id`, `user_id`, `club_id`, `status`, `joined_at`) VALUES
(1, 6, 1, 'approved', '2025-02-26 15:37:24'),
(2, 7, 2, 'approved', '2025-02-26 17:16:21'),
(3, 8, 1, 'approved', '2025-03-01 10:05:47'),
(4, 8, 2, 'approved', '2025-03-01 10:05:50'),
(5, 9, 1, 'approved', '2025-03-01 10:06:18'),
(6, 9, 2, 'approved', '2025-03-01 10:06:20'),
(7, 7, 1, 'approved', '2025-03-01 11:18:37'),
(8, 7, 3, 'approved', '2025-03-01 11:18:40'),
(10, 7, 5, 'approved', '2025-03-04 01:18:44');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `club_id`, `title`, `description`, `event_date`, `status`, `created_by`, `created_at`) VALUES
(1, 2, 'Hạm đội 1', 'Đá đê', '2025-03-08 08:30:00', 'approved', 6, '2025-02-26 17:30:12'),
(2, 1, 'Giao Lưu FPT', 'Giao lưu vui vẻ', '2025-03-04 08:00:00', 'approved', 5, '2025-02-27 07:26:24'),
(3, 2, 'Vinh Quang FBT', 'Vinh quang cho FBT', '2025-03-03 06:00:00', 'approved', 6, '2025-03-01 10:10:09'),
(4, 3, 'Lương Sơn Bàn Truyện', 'Với sự tham gia của các nghệ nhân: Tiến Gầy, Tiến Béo, Labubu,...', '2025-03-23 08:15:00', 'approved', 10, '2025-03-03 02:14:21'),
(5, 5, 'Đi Bơi', 'Bơi giao lưu vui vẻ giữa các thành viên', '2025-03-08 13:00:00', 'approved', 11, '2025-03-04 01:17:34');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `club_id`, `sender_id`, `title`, `message`, `created_at`) VALUES
(2, 1, 5, 'Đi giao lưu võ thuật', 'Khu E có thách đấu chúng ta, hãy đi thôi', '2025-03-08 16:21:40'),
(3, 5, 11, 'Mang Đồ Bơi', 'Đi bơi không thể thiếu đồ bơi đúng không nào, nhớ mang nhé !', '2025-03-08 16:40:21'),
(5, 5, 11, 'HUỶ ĐI BƠI', 'Cuộc vui nào cũng có lúc tàn, nay nắng quá, ở nhà nha, đi đen lắm !', '2025-03-08 17:07:19'),
(6, 2, 6, 'Tình Trạng', 'Tôi mệt quá', '2025-03-08 17:19:17'),
(7, 1, 5, 'Lên Đồ', 'Mọi người lên đồ chuẩn bị đi giao lưu vào ngày mai.', '2025-03-09 03:43:35'),
(8, 5, 11, 'Thi Đấu Cài Win', 'Câu lạc bộ Bảo Mật Mạng muốn so tài với chúng ta, chần chờ gì nữa mà không đi !', '2025-03-11 01:18:34');

-- --------------------------------------------------------

--
-- Table structure for table `notification_recipients`
--

CREATE TABLE `notification_recipients` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_recipients`
--

INSERT INTO `notification_recipients` (`id`, `notification_id`, `user_id`, `is_read`, `read_at`) VALUES
(1, 2, 6, 0, NULL),
(2, 2, 8, 0, NULL),
(3, 2, 9, 0, NULL),
(4, 2, 7, 1, '2025-03-08 16:50:37'),
(8, 3, 7, 1, '2025-03-08 16:50:22'),
(10, 5, 7, 1, '2025-03-08 17:08:05'),
(11, 6, 7, 1, '2025-03-08 17:19:24'),
(12, 6, 8, 0, NULL),
(13, 6, 9, 0, NULL),
(14, 7, 6, 0, NULL),
(15, 7, 8, 0, NULL),
(16, 7, 9, 0, NULL),
(17, 7, 7, 1, '2025-03-09 03:43:49'),
(18, 8, 7, 1, '2025-03-11 01:18:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','club_leader','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `avatar_url`) VALUES
(4, 'Siêu Cấp Quản Trị Viên', 'admin@gmail.com', '$2y$10$TcmIVPd5VvzSIZmoDmane.YuIZhv44Aasmr6F5d7aKepIoSVxfQcm', 'admin', '2025-02-26 15:31:24', NULL),
(5, 'Trưởng CLB Siêu Cấp', 'club@gmail.com', '$2y$10$8NEz7k17kV26e.H4eTz5V.d67CKRBnwpYyMTyCg/VgUjFo5yu326q', 'club_leader', '2025-02-26 15:31:54', NULL),
(6, 'Thomas', 'user@gmail.com', '$2y$10$bgcvwQVYAtYzhcfkauu.4eC0tx3BGHr3d2Bb.AfIpLQilwFAlzDmy', 'user', '2025-02-26 15:32:04', NULL),
(7, 'John Mikey', 'john@gmail.com', '$2y$10$78wvX6iwBPe9qgMVE7EiCuWmoqea3YbE3XL37OfZh2R8iA8AnrNFy', 'club_leader', '2025-02-26 17:16:06', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741495050/avatars/zajk92zkbb4n61huhx3v.jpg'),
(8, 'Quốc Tiến', 'quoctien@gmail.com', '$2y$10$He0KxuuirPFaRKtN2e6KeOCx5zAfVPc65PBFQOYWQCPUPB28utTwm', 'user', '2025-03-01 10:05:05', NULL),
(9, 'Thành Tiến', 'thanhtien@gmail.com', '$2y$10$22Wo0vkQ72JfZ4JFTj8VWuNQgl3LAt1/SUV1pkooMWRf6ruYaZMM6', 'user', '2025-03-01 10:05:24', NULL),
(10, 'Công', 'cong@gmail.com', '$2y$10$BYUzx2sft8jawlieUEOq5OhkjLAGG27m1R.RD2P5LLLHoE.P7n9Pm', 'club_leader', '2025-03-03 02:00:26', NULL),
(11, 'Phạm Lê Gia Hân', 'giahan@gmail.com', '$2y$10$6nOjI29BmN5aLq.ZV4CwBezxpYQB7InCHZbLeeiScjoeOTzQY1jgu', 'club_leader', '2025-03-04 01:15:32', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `club_leaders`
--
ALTER TABLE `club_leaders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_club_leader` (`club_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `club_members`
--
ALTER TABLE `club_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_id` (`notification_id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `club_leaders`
--
ALTER TABLE `club_leaders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `club_members`
--
ALTER TABLE `club_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_leaders`
--
ALTER TABLE `club_leaders`
  ADD CONSTRAINT `club_leaders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_leaders_ibfk_2` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_members`
--
ALTER TABLE `club_members`
  ADD CONSTRAINT `club_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_members_ibfk_2` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD CONSTRAINT `notification_recipients_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_recipients_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
