-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 11:45 AM
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
(20, 5, 7, 'present', '2025-03-04 01:19:13'),
(21, 6, 12, 'present', '2025-03-29 09:46:19'),
(22, 6, 7, 'present', '2025-03-29 09:46:21'),
(23, 6, 8, 'absent', '2025-03-29 09:46:55'),
(24, 6, 6, 'present', '2025-03-29 09:47:02'),
(25, 6, 9, 'present', '2025-03-29 09:47:04'),
(26, 8, 12, 'present', '2025-04-01 09:16:43'),
(27, 8, 7, 'present', '2025-04-01 09:16:44');

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
  `image_url` varchar(255) DEFAULT NULL,
  `bank_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`bank_info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`id`, `name`, `description`, `created_at`, `status`, `image_url`, `bank_info`) VALUES
(1, 'KARATE', 'Đam mê võ thuật bơi vào đây', '2025-02-26 15:36:19', 'active', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741490302/clubs/tpwzospwevjfybpx6ogl.jpg', '{\"account_number\":\"0818388343\",\"account_name\":\"NGUYEN THANH DUY CONG\",\"bank_name\":\"MBBank\",\"bank_bin\":\"970422\",\"code\":\"MB\"}'),
(2, 'BÓNG ĐÁ', 'Mê bóng thì vào', '2025-02-26 15:59:36', 'active', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741490146/clubs/hbjw1sdnha2hskmach3k.jpg', ''),
(3, 'NHẠC KỊCH', 'Ca hát là đam mê, diễn xuất là thượng hạng.', '2025-02-27 07:23:19', 'active', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741490410/clubs/fzmavshxtcloynmaym8o.png', '{\"account_number\":\"0818388343\",\"account_name\":\"NGUYEN THANH DUY CONG\",\"bank_name\":\"MBBank\",\"bank_bin\":\"970422\",\"code\":\"MB\"}'),
(5, 'Mạng Máy Tính', 'Chia sẻ đam mê với chuyên nghành mạng máy tính', '2025-03-04 01:16:25', 'active', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741490322/clubs/lwfhqai5ndmfql6xilzi.jpg', '{\"account_number\":\"9818388343\",\"account_name\":\"NGUYEN THANH DUY CONG\",\"bank_name\":\"Vietcombank\",\"bank_bin\":\"970436\",\"code\":\"VCB\"}');

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
(10, 7, 5, 'approved', '2025-03-04 01:18:44'),
(12, 12, 2, 'pending', '2025-03-11 04:14:18'),
(13, 12, 3, 'approved', '2025-03-11 04:14:23'),
(14, 12, 5, 'approved', '2025-03-11 04:14:26'),
(15, 12, 1, 'approved', '2025-03-11 04:18:47');

-- --------------------------------------------------------

--
-- Table structure for table `club_messages`
--

CREATE TABLE `club_messages` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_messages`
--

INSERT INTO `club_messages` (`id`, `club_id`, `sender_id`, `message`, `created_at`) VALUES
(1, 5, 12, 'Hi bạn', '2025-03-23 18:01:33'),
(2, 5, 12, 'Hello bạn', '2025-03-23 18:03:01'),
(3, 5, 7, 'Chào bạn', '2025-03-23 18:03:08'),
(4, 1, 12, 'Này', '2025-03-23 18:23:06'),
(5, 1, 12, 'Sao đâu hết rồi', '2025-03-24 06:22:21'),
(6, 1, 7, 'Đây, tôi đây', '2025-03-24 06:22:48'),
(7, 1, 12, 'Hihi', '2025-03-29 10:10:23'),
(8, 1, 12, 'Nhớ donate nha mọi người', '2025-04-01 09:21:47');

-- --------------------------------------------------------

--
-- Table structure for table `club_posts`
--

CREATE TABLE `club_posts` (
  `id` int(11) NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `content` longtext NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_posts`
--

INSERT INTO `club_posts` (`id`, `club_id`, `title`, `content`, `image_url`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Đi ngủ', '<p><b>Tiến</b> là một chàng trai trẻ với giấc ngủ <i>\"tuyệt vời\"</i> nhất thế gian. Ngày nào cũng vậy, cứ đến giờ đi ngủ là anh lại như một chiến binh bị mắc kẹt trong trận chiến sinh tử với chính chiếc giường của mình.</p>\n\n    <blockquote>Mọi chuyện bắt đầu vào một buổi tối đẹp trời...</blockquote>\n\n    <p>Tiến bật dậy, lục tìm điện thoại, tra ngay <u>\"Cách ngủ nhanh trong 2 phút\"</u>. Một bài viết khuyên anh hãy đếm cừu.</p>\n\n    <ul>\n        <li>Một con cừu</li>\n        <li>Hai con cừu</li>\n        <li>Ba con cừu...</li>\n    </ul>\n\n    <p>Đến con thứ 100, anh bắt đầu tưởng tượng chúng đang mở party, nhảy múa trên đầu mình.</p>\n\n    <table border=\"1\">\n        <tr><th>Thời gian</th><th>Sự kiện</th></tr>\n        <tr><td>00:00</td><td>Lên giường ngủ</td></tr>\n        <tr><td>02:00</td><td>Vẫn chưa ngủ</td></tr>\n        <tr><td>04:00</td><td>Lướt TikTok</td></tr>\n        <tr><td>06:00</td><td>Trời đã sáng...</td></tr>\n    </table>\n\n    <p>Và thế là, như mọi ngày, Tiến đi làm với đôi mắt thâm quầng như gấu trúc...</p>', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741490302/clubs/tpwzospwevjfybpx6ogl.jpg', 'approved', 5, '2025-03-11 14:57:08', '2025-03-19 13:03:51'),
(3, 1, 'Pro là ai ?', 'Là tôi', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741490302/clubs/tpwzospwevjfybpx6ogl.jpg', 'approved', 5, '2025-03-12 10:56:49', '2025-04-01 08:49:14'),
(6, 1, 'oooooooo', 'iiiiiiiiiii aaaaaaaaaaaaaa', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741779585/club_posts/jab8ndigsovpysffiead.jpg', 'approved', 5, '2025-03-12 11:16:10', '2025-03-12 11:58:46'),
(11, 1, 'Mưa', '<p><b>Tiến</b> là một chàng trai trẻ với giấc ngủ <i>\"tuyệt vời\"</i> nhất thế gian. Ngày nào cũng vậy, cứ đến giờ đi ngủ là anh lại như một chiến binh bị mắc kẹt trong trận chiến sinh tử với chính chiếc giường của mình.</p>\n\n    <blockquote>Mọi chuyện bắt đầu vào một buổi tối đẹp trời...</blockquote>\n\n    <p>Tiến bật dậy, lục tìm điện thoại, tra ngay <u>\"Cách ngủ nhanh trong 2 phút\"</u>. Một bài viết khuyên anh hãy đếm cừu.</p>\n\n    <ul>\n        <li>Một con cừu</li>\n        <li>Hai con cừu</li>\n        <li>Ba con cừu...</li>\n    </ul>\n\n    <p>Đến con thứ 100, anh bắt đầu tưởng tượng chúng đang mở party, nhảy múa trên đầu mình.</p>\n\n    <table border=\"1\">\n        <tr><th>Thời gian</th><th>Sự kiện</th></tr>\n        <tr><td>00:00</td><td>Lên giường ngủ</td></tr>\n        <tr><td>02:00</td><td>Vẫn chưa ngủ</td></tr>\n        <tr><td>04:00</td><td>Lướt TikTok</td></tr>\n        <tr><td>06:00</td><td>Trời đã sáng...</td></tr>\n    </table>\n\n    <p>Và thế là, như mọi ngày, Tiến đi làm với đôi mắt thâm quầng như gấu trúc...</p>', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1742391765/club_posts/pkrdbqf04he8jfmh5ssa.jpg', 'approved', 5, '2025-03-19 13:42:47', '2025-03-19 13:43:55'),
(12, 1, 'Nắng', '<h1>1 NĂM CÓ 4 MÙA</h1><p>Những nước khác thì</p><figure class=\"table\"><table><tbody><tr><td>1</td><td>2</td><td>3</td><td>4</td></tr><tr><td>Xuân</td><td>Hạ</td><td>Thu</td><td>Đông</td></tr></tbody></table></figure><p>Nhưng Việt Nam lại là:</p><figure class=\"table\"><table><tbody><tr><td>1</td><td>2</td><td>3</td><td>4</td></tr><tr><td>Mát</td><td>Cực Nóng</td><td>Nóng</td><td>Mát</td></tr></tbody></table></figure><ul><li>Vì sao ? Tra Google.</li><li>Sao tra Googl ? Tác giả không nhớ</li></ul><ol><li>Một</li><li>Hai</li><li>Ba</li></ol><p><a href=\"https://www.youtube.com/watch?v=dQw4w9WgXcQ\">https://www.youtube.com/watch?v=dQw4w9WgXcQ</a></p><blockquote><p>Một nhà hiền triết đã không nói gì</p></blockquote><p>Tác giả: Hả Hả</p>', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1742392195/club_posts/vflli5uetegbnebjxr4d.png', 'approved', 5, '2025-03-19 13:49:35', '2025-03-19 13:50:23'),
(14, 3, 'Này Này Này', '<p>Tôi test chức năng này nhé</p>', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1743481555/club_posts/es4cthrhjb8ot1wtmb4u.jpg', 'pending', 10, '2025-03-29 10:00:05', '2025-04-01 04:25:56');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `transaction_code` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `club_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `user_id`, `amount`, `message`, `status`, `transaction_code`, `created_at`, `club_id`) VALUES
(7, 12, 30000.00, 'Ủng hộ', 'pending', 'DON_67eb8021963df9.69687390', '2025-04-01 05:57:08', 5),
(8, 12, 30000.00, 'Thank you', 'pending', 'DON_67eb80ddb51e17.83360744', '2025-04-01 06:00:03', 3),
(9, 12, 20000.00, 'Tiếp nè', 'completed', 'DON_67eb81077d04e8.21283900', '2025-04-01 06:00:48', 3),
(38, 12, 5000.00, 'Very like', 'completed', 'DON_67eb908badbed0.70952011', '2025-04-01 07:06:54', 3),
(39, 7, 200000.00, 'Hehe', 'completed', 'DON_67eb914183a700.69508159', '2025-04-01 07:09:57', 3),
(42, 12, 5000.00, 'Cố lên', 'completed', 'DON_67eba54de14e59.27218114', '2025-04-01 08:35:30', 3);

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
(5, 5, 'Đi Bơi', 'Bơi giao lưu vui vẻ giữa các thành viên', '2025-03-08 13:00:00', 'approved', 11, '2025-03-04 01:17:34'),
(6, 1, 'Thường Niên IV', 'Sự kiện tranh đai vô địch hàng năm, hãy tham gia và giành lấy vinh quang nào.', '2025-05-15 17:55:00', 'approved', 5, '2025-03-11 03:29:12'),
(7, 1, 'Giao luu vo thuat', 'Danh th Tien', '2025-03-13 14:43:00', 'approved', 5, '2025-03-12 03:42:01'),
(8, 1, 'Tứ Hùng Tranh Đấu', 'Ngày thi đấu đỉnh cao của bốn đại cao thủ giữa 2 trường, hãy tham gia và cổ vũ cho gà nhà nhé', '2025-04-18 09:00:00', 'approved', 5, '2025-03-29 09:50:04'),
(9, 3, 'Kịch Hí La Lan Bản', 'Một cõi để nhớ...', '2025-05-25 09:00:00', 'pending', 10, '2025-04-01 09:14:30');

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
(8, 5, 11, 'Thi Đấu Cài Win', 'Câu lạc bộ Bảo Mật Mạng muốn so tài với chúng ta, chần chờ gì nữa mà không đi !', '2025-03-11 01:18:34'),
(9, 1, 5, 'Đã lên thông tin sự kiện mới', 'Sự kiện mới bắt đầu mở đăng ký, hãy tham gia nào.', '2025-03-11 03:30:16'),
(10, 5, 11, 'tesst', 'Hả hả, đã z', '2025-03-11 09:20:07'),
(11, 5, 11, 'Này hỡi các con dân, lắng nghe thông báo !', 'Ta sắp đi làm, cần tuyển đầu đàn mới, hình thức thông báo sau !', '2025-03-23 17:33:01'),
(13, 3, 10, 'ĐÃ TÍCH HỢP ĐÓNG GÓP', 'Hệ thống đã tích hợp đóng góp, các bạn nhớ nhoé.', '2025-04-01 03:29:50'),
(15, 3, 4, 'Xác nhận đóng góp', 'Khoản đóng góp của bạn đã được xác nhận. Cảm ơn bạn đã ủng hộ!', '2025-04-01 07:07:54'),
(16, 3, 4, 'Xác nhận đóng góp', 'Khoản đóng góp của bạn đã được xác nhận. Cảm ơn bạn đã ủng hộ!', '2025-04-01 07:10:15'),
(17, 3, 4, 'Xác nhận đóng góp', 'Khoản đóng góp của bạn cho CLB NHẠC KỊCH đã được xác nhận. Cảm ơn bạn đã ủng hộ!', '2025-04-01 08:52:17'),
(18, 3, 4, 'Xác nhận đóng góp', 'Khoản đóng góp của bạn cho CLB NHẠC KỊCH đã được xác nhận. Cảm ơn bạn đã ủng hộ!', '2025-04-01 08:52:47'),
(19, 3, 10, 'LUYỆN TẬP', 'Mọi người hãy luyện tập chăm chỉ cho sự kiện sắp tới nhé', '2025-04-01 09:33:12');

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
(2, 2, 8, 1, '2025-03-11 04:19:09'),
(3, 2, 9, 1, '2025-03-11 04:22:21'),
(4, 2, 7, 1, '2025-03-08 16:50:37'),
(8, 3, 7, 1, '2025-03-08 16:50:22'),
(10, 5, 7, 1, '2025-03-08 17:08:05'),
(11, 6, 7, 1, '2025-03-08 17:19:24'),
(12, 6, 8, 1, '2025-03-11 04:19:09'),
(13, 6, 9, 1, '2025-03-11 04:22:21'),
(14, 7, 6, 0, NULL),
(15, 7, 8, 1, '2025-03-11 04:19:09'),
(16, 7, 9, 1, '2025-03-11 04:22:21'),
(17, 7, 7, 1, '2025-03-09 03:43:49'),
(18, 8, 7, 1, '2025-03-11 01:18:50'),
(19, 9, 6, 0, NULL),
(20, 9, 8, 1, '2025-03-11 04:19:09'),
(21, 9, 9, 1, '2025-03-11 04:22:21'),
(22, 9, 7, 1, '2025-03-11 04:23:24'),
(23, 10, 7, 1, '2025-03-11 09:20:16'),
(24, 11, 7, 1, '2025-03-23 17:33:16'),
(26, 13, 7, 1, '2025-04-01 07:09:09'),
(27, 13, 12, 1, '2025-04-01 03:29:59'),
(30, 15, 12, 1, '2025-04-01 07:08:16'),
(31, 16, 7, 1, '2025-04-01 07:10:38'),
(32, 17, 12, 1, '2025-04-01 08:52:26'),
(33, 18, 12, 1, '2025-04-01 08:52:57'),
(34, 19, 7, 0, NULL),
(35, 19, 12, 1, '2025-04-01 09:38:53');

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
  `avatar_url` varchar(255) DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `avatar_url`, `last_activity`) VALUES
(4, 'Siêu Cấp Quản Trị Viên', 'admin@gmail.com', '$2y$10$TcmIVPd5VvzSIZmoDmane.YuIZhv44Aasmr6F5d7aKepIoSVxfQcm', 'admin', '2025-02-26 15:31:24', NULL, '2025-03-23 18:00:06'),
(5, 'Trưởng CLB Siêu Cấp', 'club@gmail.com', '$2y$10$8NEz7k17kV26e.H4eTz5V.d67CKRBnwpYyMTyCg/VgUjFo5yu326q', 'club_leader', '2025-02-26 15:31:54', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741684737/avatars/ardolyp5eyhkyispa0qj.jpg', '2025-03-23 18:00:06'),
(6, 'Thomas', 'user@gmail.com', '$2y$10$bgcvwQVYAtYzhcfkauu.4eC0tx3BGHr3d2Bb.AfIpLQilwFAlzDmy', 'user', '2025-02-26 15:32:04', NULL, '2025-03-23 18:00:06'),
(7, 'John Mikey', 'john@gmail.com', '$2y$10$78wvX6iwBPe9qgMVE7EiCuWmoqea3YbE3XL37OfZh2R8iA8AnrNFy', 'user', '2025-02-26 17:16:06', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741495050/avatars/zajk92zkbb4n61huhx3v.jpg', '2025-03-24 06:49:03'),
(8, 'Quốc Tiến', 'quoctien@gmail.com', '$2y$10$He0KxuuirPFaRKtN2e6KeOCx5zAfVPc65PBFQOYWQCPUPB28utTwm', 'user', '2025-03-01 10:05:05', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1743491686/avatars/wp5ltzea1yntsoskxlvi.jpg', '2025-04-01 07:14:47'),
(9, 'Thành Tiến', 'thanhtien@gmail.com', '$2y$10$22Wo0vkQ72JfZ4JFTj8VWuNQgl3LAt1/SUV1pkooMWRf6ruYaZMM6', 'user', '2025-03-01 10:05:24', NULL, '2025-03-23 18:00:06'),
(10, 'Công', 'cong@gmail.com', '$2y$10$BYUzx2sft8jawlieUEOq5OhkjLAGG27m1R.RD2P5LLLHoE.P7n9Pm', 'club_leader', '2025-03-03 02:00:26', NULL, '2025-03-29 10:02:42'),
(11, 'Phạm Lê Gia Hân', 'giahan@gmail.com', '$2y$10$6nOjI29BmN5aLq.ZV4CwBezxpYQB7InCHZbLeeiScjoeOTzQY1jgu', 'club_leader', '2025-03-04 01:15:32', NULL, '2025-03-23 18:00:06'),
(12, 'Hà Huy Chiến Thắng', 'chienthang@gmail.com', '$2y$10$kDR4FFyE81aJe1qpF2ki5.mJn6h/5Q1EJy5BECHbr8n5dzDADnEYK', 'user', '2025-03-11 04:13:59', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741666643/avatars/s5rarwf3hirot36fd8ot.jpg', '2025-04-01 09:23:39'),
(17, 'Nguyễn Thành Duy Công', 'duycong2500@gmail.com', '$2y$10$Zg91CDq4RegsAGgbbvc07eqOhNmOhvxDauxy4twAgkXF7MDFVnC5K', 'user', '2025-03-11 09:18:24', NULL, '2025-03-23 18:00:06'),
(19, 'Tiến Nest JS', 'tiennestjs@gmail.com', '$2y$10$U1P9D8d8pYRC93wqC224AuKYp0S03MHmNT6Noc8gqj1/Y0ZX5Un/.', 'user', '2025-03-12 01:16:24', 'https://res.cloudinary.com/dsxpjcve6/image/upload/v1741742311/avatars/vsxe1sqrhvcxozlhkjgk.jpg', '2025-04-01 09:44:13'),
(20, 'Hân Palm', 'phamhanst20@gmail.com', '$2y$10$0.dZX/Pb9W7kUeyc5.JAFeRICZd6mF94KWtZQEX8xMhiFLHbelHpW', 'user', '2025-03-12 03:38:52', NULL, '2025-04-01 09:43:55');

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
-- Indexes for table `club_messages`
--
ALTER TABLE `club_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `club_posts`
--
ALTER TABLE `club_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_club_posts_status` (`status`),
  ADD KEY `idx_club_posts_created_at` (`created_at`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `club_messages`
--
ALTER TABLE `club_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `club_posts`
--
ALTER TABLE `club_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
-- Constraints for table `club_messages`
--
ALTER TABLE `club_messages`
  ADD CONSTRAINT `club_messages_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`),
  ADD CONSTRAINT `club_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `club_posts`
--
ALTER TABLE `club_posts`
  ADD CONSTRAINT `club_posts_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_posts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
