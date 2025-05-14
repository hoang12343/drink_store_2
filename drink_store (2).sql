-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 04:26 PM
-- Server version: 8.0.32
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `drink_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(49, 9, 62, 1, '2025-05-11 03:26:22', '2025-05-11 03:26:22'),
(78, 6, 80, 2, '2025-05-13 10:52:47', '2025-05-13 17:32:43'),
(79, 6, 108, 1, '2025-05-13 15:38:37', '2025-05-13 15:38:37'),
(80, 6, 104, 1, '2025-05-13 16:36:01', '2025-05-13 16:36:01'),
(81, 6, 71, 1, '2025-05-13 16:56:06', '2025-05-13 16:56:06'),
(82, 6, 109, 2, '2025-05-14 08:27:04', '2025-05-14 14:24:35'),
(83, 6, 68, 2, '2025-05-14 09:19:11', '2025-05-14 09:31:37'),
(84, 6, 62, 1, '2025-05-14 10:01:45', '2025-05-14 10:01:45'),
(85, 6, 79, 2, '2025-05-14 14:15:45', '2025-05-14 14:25:07');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `display_name`, `description`, `created_at`) VALUES
(1, 'promotion', 'Sản phẩm khuyến mãi', 'Các sản phẩm đang được giảm giá', '2025-04-17 02:43:25'),
(2, 'wine', 'Rượu vang nhập khẩu', 'Rượu vang từ các quốc gia nổi tiếng như Ý, Pháp, Chile', '2025-04-17 02:43:25'),
(3, 'brandy', 'Rượu mạnh', 'Rượu mạnh như Cognac, Brandy thượng hạng', '2025-04-17 02:43:25'),
(4, 'crystal_glasses', 'Ly pha lê', 'Ly pha lê cao cấp dùng cho rượu, như Riedel', '2025-04-17 02:43:25'),
(5, 'whisky', 'Whisky', 'Rượu whisky từ Scotland, Mỹ, Nhật, v.v.', '2025-04-17 02:43:25'),
(6, 'vodka', 'Vodka', 'Vodka tinh khiết từ Nga, Ba Lan, v.v.', '2025-04-17 02:43:25'),
(7, 'beer', 'Bia', 'Bia nhập khẩu từ Bỉ, Hà Lan và bia nội địa', '2025-04-17 02:43:25'),
(8, 'cocktail', 'Cocktail', 'Đồ uống cocktail pha sẵn hoặc nguyên liệu cocktail', '2025-04-17 02:43:25'),
(9, 'gift', 'Quà tặng', 'Hộp quà rượu sang trọng, phù hợp làm quà tặng', '2025-04-17 02:43:25');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `is_important` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`, `is_read`, `is_important`) VALUES
(1, 'Nguyen Van Minh', 'nvminh@example.com', '0901234567', 'Craft Beer Inquiry', 'I am interested in your selection of craft beers. Do you have any IPA varieties from local breweries? Please send me your current list and pricing.', '2025-04-15 14:30:00', 0, 0),
(2, 'Tran Thi Huong', 'tthuong@example.com', '0912345678', 'Wine Tasting Event', 'I would like to register for the wine tasting event on May 5th. Is there still space available? I am particularly interested in the French wines.', '2025-04-16 10:45:00', 0, 0),
(3, 'Le Quang Dat', 'lqdat@example.com', '0923456789', 'Whiskey Collection', 'I am a whiskey enthusiast looking to expand my collection. Do you have any rare Japanese whiskeys in stock? I am willing to pay premium for quality bottles.', '2025-04-17 11:15:00', 0, 0),
(4, 'Pham Ngoc Linh', 'pnlinh@example.com', '0934567890', 'Cocktail Workshop', 'I saw your advertisement for the upcoming cocktail making workshop. Please reserve 2 spots for me and my friend. We are excited to learn mixology techniques.', '2025-04-18 14:20:00', 0, 0),
(5, 'Hoang Thanh Tung', 'httung@example.com', '0945678901', 'Bulk Order for Event', 'Our company is hosting a corporate event next month and we need to order champagne and wine for 50 guests. Can you provide a quote and delivery options?', '2025-04-19 08:10:00', 0, 0),
(6, 'Nguyen Thi Mai', 'ntmai@example.com', NULL, 'Vodka Recommendations', 'I am planning a party and need recommendations for premium vodka brands that would be good for cocktails. What would you suggest for approximately 20 guests?', '2025-04-19 13:25:00', 0, 0),
(7, 'Tran Van Hung', 'tvhung@example.com', '0967890123', 'Rum Tasting Club', 'I am interested in joining your monthly rum tasting club. Could you please send me information about membership fees and the schedule for upcoming tastings?', '2025-04-20 16:40:00', 0, 0),
(8, 'Le Hong Nhung', 'lhnhung@example.com', '0978901234', 'Product Complaint', 'I recently purchased a bottle of your premium gin and noticed the seal was broken. I would like to request a replacement or refund for this product.', '2025-04-20 17:55:00', 0, 0),
(9, 'Pham Quoc Bao', 'pqbao@example.com', NULL, 'Local Brewery Tour', 'I would like to book a tour of your brewery for a group of 6 people this weekend. Do you offer tastings as part of the tour? What are your opening hours?', '2025-04-21 09:05:00', 0, 1),
(10, 'Vo Thi Kim Anh', 'vtkanh@example.com', '0990123456', 'Sake Import Inquiry', 'I am opening a Japanese restaurant and looking for a reliable supplier of premium sake. Please share your catalog and wholesale pricing for regular orders.', '2025-04-21 10:30:00', 0, 0),
(11, 'Lê Hoàng', 'hoanggg12@gmail.com', '0965768629', 'weewij', 'đá', '2025-04-21 15:33:20', 0, 0),
(12, 'bùi nam truong', 'bnt@gmailc.om', '0912018382', 'abc', 'ngon', '2025-04-21 15:51:29', 0, 0),
(13, 'hào', 'hao@gmail.com', '1234665756', 'abc', 'abc', '2025-04-23 14:51:47', 0, 0),
(14, 'hoanglee', 'hoanglee@gmail.com', '1234567889', 'cáhchaisdo', 'fkjhkfjhds', '2025-05-09 08:14:16', 0, 0),
(17, 'Lê Hoàng', 'hoangleedz32@gmail.com', '0965768529', 'fhnfgn', 'ưvsdvsv', '2025-05-10 19:17:54', 0, 0),
(18, 'Lê Hoàng', 'hoanggg322004@gmail.com', '0965768629', 'chào kậu', 'lấy rượu ra đây nhanh', '2025-05-10 19:59:48', 1, 0),
(19, 'Lê Hoàng', 'hoanggg322004@gmail.com', '0965768629', 'chào kậu', 'lấy rượu ra đây nhanh', '2025-05-10 19:59:48', 0, 0),
(20, 'Lê Hoàng', 'hoanggg322004@gmail.com', '0965768629', 'abc', 'xyz', '2025-05-11 09:41:10', 0, 0),
(21, 'Lê Hoàng', 'hoanggg322004@gmail.com', '0965768629', 'àgrhrth', 'egege', '2025-05-13 22:51:45', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `contact_replies`
--

CREATE TABLE `contact_replies` (
  `id` int NOT NULL,
  `contact_id` int NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_replies`
--

INSERT INTO `contact_replies` (`id`, `contact_id`, `subject`, `message`, `created_at`) VALUES
(1, 14, 'Re: c&aacute;hchaisdo', 'abc', '2025-05-10 18:41:40'),
(2, 14, 'Re: c&aacute;hchaisdo', 'abc', '2025-05-10 18:41:40'),
(3, 14, 'Re: c&aacute;hchaisdo', 'bcd', '2025-05-10 18:44:16'),
(4, 14, 'Re: c&aacute;hchaisdo', 'bcd', '2025-05-10 18:44:16'),
(5, 14, 'Re: c&aacute;hchaisdo', 'abcdde', '2025-05-10 18:45:34'),
(6, 14, 'Re: c&aacute;hchaisdo', 'abcdde', '2025-05-10 18:45:34'),
(7, 14, 'Re: c&aacute;hchaisdo', 'hellooo', '2025-05-10 18:57:52'),
(8, 14, 'Re: c&aacute;hchaisdo', 'hellooo', '2025-05-10 18:57:52'),
(9, 14, 'Re: c&aacute;hchaisdo', 'hey', '2025-05-10 18:59:18'),
(10, 14, 'Re: c&aacute;hchaisdo', 'hey', '2025-05-10 18:59:18'),
(17, 18, 'Re: ch&agrave;o kậu', 'fgfd', '2025-05-11 09:06:21'),
(18, 18, 'Re: ch&agrave;o kậu', 'fgfd', '2025-05-11 09:06:25'),
(19, 18, 'Re: ch&agrave;o kậu', 'Cảm ơn v&igrave; đ&atilde; đến', '2025-05-11 09:07:27'),
(20, 18, 'Re: ch&agrave;o kậu', 'Cảm ơn v&igrave; đ&atilde; đến', '2025-05-11 09:07:31'),
(21, 18, 'Re: ch&agrave;o kậu', 'Cảm ơn v&igrave; đ&atilde; đến', '2025-05-11 09:09:03'),
(22, 18, 'Re: ch&agrave;o kậu', 'Cảm ơn v&igrave; đ&atilde; đến', '2025-05-11 09:09:07'),
(23, 18, 'Re: ch&agrave;o kậu', 'Cảm ơn v&igrave; đ&atilde; đến', '2025-05-11 09:09:50'),
(24, 18, 'Re: ch&agrave;o kậu', 'Cảm ơn v&igrave; đ&atilde; đến', '2025-05-11 09:09:54'),
(25, 18, 'Re: ch&agrave;o kậu', 'hello you', '2025-05-11 09:10:08'),
(26, 18, 'Re: ch&agrave;o kậu', 'hello you', '2025-05-11 09:10:13'),
(27, 18, 'Re: ch&agrave;o kậu', 'ch&agrave;o cậu', '2025-05-11 09:15:04'),
(28, 18, 'Re: ch&agrave;o kậu', 'ch&agrave;o cậu', '2025-05-11 09:15:08'),
(29, 18, 'Re: ch&agrave;o kậu', 'h&ecirc;lo', '2025-05-11 09:15:25'),
(30, 18, 'Re: ch&agrave;o kậu', 'h&ecirc;lo', '2025-05-11 09:15:30'),
(31, 18, 'Re: ch&agrave;o kậu', 'abcxyz', '2025-05-11 09:18:13'),
(32, 18, 'Re: ch&agrave;o kậu', 'abcxyz', '2025-05-11 09:18:18'),
(33, 18, 'Re: chào kậu', 'abc', '2025-05-11 09:24:40'),
(34, 18, 'Re: chào kậu', 'abc', '2025-05-11 09:24:45'),
(35, 18, 'Re: chào kậu', 'helo cau', '2025-05-11 09:25:42'),
(36, 18, 'Re: chào kậu', 'helo cau', '2025-05-11 09:25:46'),
(37, 1, 'Re: Craft Beer Inquiry', 'không bic noi gi', '2025-05-11 09:30:31'),
(38, 1, 'Re: Craft Beer Inquiry', 'không bic noi gi', '2025-05-11 09:30:35'),
(39, 2, 'Re: Wine Tasting Event', 'fgf', '2025-05-11 09:31:21'),
(40, 2, 'Re: Wine Tasting Event', 'fgf', '2025-05-11 09:31:25'),
(41, 3, 'Re: Whiskey Collection', 'vsdvsd', '2025-05-11 09:31:57'),
(42, 3, 'Re: Whiskey Collection', 'vsdvsd', '2025-05-11 09:32:01'),
(43, 3, 'Re: Whiskey Collection', 'vsdvsd', '2025-05-11 09:32:18'),
(44, 3, 'Re: Whiskey Collection', 'vsdvsd', '2025-05-11 09:32:22'),
(45, 8, 'Re: Product Complaint', 'bfgbfg', '2025-05-11 09:33:08'),
(46, 8, 'Re: Product Complaint', 'bfgbfg', '2025-05-11 09:33:12'),
(47, 18, 'Re: chào kậu', 'jhvjh', '2025-05-11 09:33:27'),
(48, 18, 'Re: chào kậu', 'jhvjh', '2025-05-11 09:33:31'),
(49, 18, 'Re: chào kậu', 'vxfcvx', '2025-05-11 09:34:29'),
(50, 18, 'Re: chào kậu', 'vxfcvx', '2025-05-11 09:34:33'),
(51, 18, 'Re: chào kậu', 'fsvfsv', '2025-05-11 09:37:03'),
(52, 18, 'Re: chào kậu', 'fsvfsv', '2025-05-11 09:37:08'),
(53, 18, 'Re: chào kậu', 'egerv', '2025-05-11 09:37:35'),
(54, 18, 'Re: chào kậu', 'egerv', '2025-05-11 09:37:40'),
(55, 18, 'Re: chào kậu', 'vsfbsf', '2025-05-11 09:37:48'),
(56, 18, 'Re: chào kậu', 'vsfbsf', '2025-05-11 09:37:53'),
(57, 18, 'Re: chào kậu', 'cadcdac', '2025-05-11 09:38:52'),
(58, 18, 'Re: chào kậu', 'cadcdac', '2025-05-11 09:38:57'),
(59, 18, 'Re: chào kậu', 'àghjlk', '2025-05-11 09:39:38'),
(60, 18, 'Re: chào kậu', 'àghjlk', '2025-05-11 09:39:42'),
(61, 20, 'Re: abc', 'fsgrs', '2025-05-11 10:07:06'),
(62, 20, 'Re: abc', 'fsgrs', '2025-05-11 10:07:10'),
(63, 20, 'Re: abc', 'abc\r\n', '2025-05-13 08:15:07'),
(64, 20, 'Re: abc', 'hello ', '2025-05-13 08:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `quantity`, `location`, `last_updated`) VALUES
(1, 62, 10, 'abc', '2025-04-21 16:01:43'),
(2, 109, 20, 'ac', '2025-04-22 01:20:28'),
(3, 63, 50, 'Kho chính', '2025-05-14 10:06:37'),
(4, 64, 50, 'Kho chính', '2025-05-14 10:06:37'),
(5, 65, 50, 'Kho chính', '2025-05-14 10:06:37'),
(6, 66, 50, 'Kho chính', '2025-05-14 10:06:37'),
(7, 67, 50, 'Kho chính', '2025-05-14 10:06:37'),
(8, 68, 50, 'Kho chính', '2025-05-14 10:06:37'),
(9, 69, 50, 'Kho chính', '2025-05-14 10:06:37'),
(10, 70, 50, 'Kho chính', '2025-05-14 10:06:37'),
(11, 71, 50, 'Kho chính', '2025-05-14 10:06:37'),
(12, 1, 50, 'Kho chính', '2025-05-14 10:06:37'),
(13, 3, 50, 'Kho chính', '2025-05-14 10:06:37'),
(14, 4, 50, 'Kho chính', '2025-05-14 10:06:37'),
(15, 5, 50, 'Kho chính', '2025-05-14 10:06:37'),
(16, 6, 50, 'Kho chính', '2025-05-14 10:06:37'),
(17, 72, 50, 'Kho chính', '2025-05-14 10:06:37'),
(18, 73, 50, 'Kho chính', '2025-05-14 10:06:37'),
(19, 74, 50, 'Kho chính', '2025-05-14 10:06:37'),
(20, 75, 50, 'Kho chính', '2025-05-14 10:06:37'),
(21, 76, 50, 'Kho chính', '2025-05-14 10:06:37'),
(22, 77, 50, 'Kho chính', '2025-05-14 10:06:37'),
(23, 78, 50, 'Kho chính', '2025-05-14 10:06:37'),
(24, 79, 50, 'Kho chính', '2025-05-14 10:06:37'),
(25, 80, 50, 'Kho chính', '2025-05-14 10:06:37'),
(26, 2, 50, 'Kho chính', '2025-05-14 10:06:37'),
(27, 81, 50, 'Kho chính', '2025-05-14 10:06:37'),
(28, 83, 50, 'Kho chính', '2025-05-14 10:06:37'),
(29, 84, 50, 'Kho chính', '2025-05-14 10:06:37'),
(30, 85, 50, 'Kho chính', '2025-05-14 10:06:37'),
(31, 86, 50, 'Kho chính', '2025-05-14 10:06:37'),
(32, 87, 50, 'Kho chính', '2025-05-14 10:06:37'),
(33, 88, 50, 'Kho chính', '2025-05-14 10:06:37'),
(34, 89, 50, 'Kho chính', '2025-05-14 10:06:37'),
(35, 90, 50, 'Kho chính', '2025-05-14 10:06:37'),
(36, 11, 50, 'Kho chính', '2025-05-14 10:06:37'),
(37, 91, 50, 'Kho chính', '2025-05-14 10:06:37'),
(38, 92, 50, 'Kho chính', '2025-05-14 10:06:37'),
(39, 93, 50, 'Kho chính', '2025-05-14 10:06:37'),
(40, 94, 50, 'Kho chính', '2025-05-14 10:06:37'),
(41, 95, 50, 'Kho chính', '2025-05-14 10:06:37'),
(42, 96, 50, 'Kho chính', '2025-05-14 10:06:37'),
(43, 97, 50, 'Kho chính', '2025-05-14 10:06:37'),
(44, 98, 50, 'Kho chính', '2025-05-14 10:06:37'),
(45, 99, 50, 'Kho chính', '2025-05-14 10:06:37'),
(46, 100, 50, 'Kho chính', '2025-05-14 10:06:37'),
(47, 101, 50, 'Kho chính', '2025-05-14 10:06:37'),
(48, 102, 50, 'Kho chính', '2025-05-14 10:06:37'),
(49, 103, 50, 'Kho chính', '2025-05-14 10:06:37'),
(50, 104, 50, 'Kho chính', '2025-05-14 10:06:37'),
(51, 105, 50, 'Kho chính', '2025-05-14 10:06:37'),
(52, 106, 50, 'Kho chính', '2025-05-14 10:06:37'),
(53, 107, 50, 'Kho chính', '2025-05-14 10:06:37'),
(54, 108, 50, 'Kho chính', '2025-05-14 10:06:37'),
(55, 7, 50, 'Kho chính', '2025-05-14 10:06:37'),
(56, 8, 50, 'Kho chính', '2025-05-14 10:06:37'),
(57, 9, 50, 'Kho chính', '2025-05-14 10:06:37'),
(58, 10, 50, 'Kho chính', '2025-05-14 10:06:37');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed','cancelled','processing','shipped','delivered','confirmed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `zalopay_trans_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `payment_method` enum('zalopay','cod') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'zalopay',
  `discount` decimal(10,2) DEFAULT '0.00',
  `shipping` decimal(10,2) DEFAULT '0.00',
  `promo_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `zalopay_trans_id`, `created_at`, `updated_at`, `payment_method`, `discount`, `shipping`, `promo_code`) VALUES
(1, 6, 1160000.00, 'pending', '250423_1', '2025-04-23 07:12:06', '2025-04-23 07:12:07', 'zalopay', 0.00, 0.00, NULL),
(2, 6, 3000000.00, 'pending', '250423_2', '2025-04-23 07:18:29', '2025-04-23 07:18:30', 'zalopay', 0.00, 0.00, NULL),
(3, 6, 640000.00, 'pending', '250423_3', '2025-04-23 07:21:45', '2025-04-23 07:21:45', 'zalopay', 0.00, 0.00, NULL),
(4, 6, 1200000.00, 'pending', '250423_4', '2025-04-23 07:25:07', '2025-04-23 07:25:08', 'zalopay', 0.00, 0.00, NULL),
(5, 6, 60000.00, 'pending', '250423_5', '2025-04-23 07:30:39', '2025-04-23 07:30:39', 'zalopay', 0.00, 0.00, NULL),
(6, 6, 60000.00, 'pending', '250423_6', '2025-04-23 07:34:25', '2025-04-23 07:34:26', 'zalopay', 0.00, 0.00, NULL),
(7, 6, 60000.00, 'pending', '250423_7', '2025-04-23 07:57:27', '2025-04-23 07:57:28', 'zalopay', 0.00, 0.00, NULL),
(8, 6, 900000.00, 'completed', '250423_8', '2025-04-23 08:17:50', '2025-04-23 20:54:54', 'zalopay', 0.00, 0.00, NULL),
(9, 6, 950000.00, 'pending', '250423_9', '2025-04-23 08:25:44', '2025-04-23 08:25:44', 'zalopay', 0.00, 0.00, NULL),
(10, 6, 2000000.00, 'pending', '250423_10', '2025-04-23 08:29:18', '2025-04-23 08:29:19', 'zalopay', 0.00, 0.00, NULL),
(11, 6, 1600000.00, 'cancelled', '250423_11', '2025-04-23 08:34:39', '2025-04-23 20:54:47', 'zalopay', 0.00, 0.00, NULL),
(12, 6, 1100000.00, 'completed', '250423_12', '2025-04-23 08:45:41', '2025-04-23 20:49:37', 'zalopay', 0.00, 0.00, NULL),
(13, 6, 200000.00, 'completed', '250424_13', '2025-04-23 18:09:41', '2025-04-23 20:49:33', 'zalopay', 0.00, 0.00, NULL),
(14, 6, 1100000.00, 'failed', '250424_14', '2025-04-23 18:12:32', '2025-04-23 20:53:48', 'zalopay', 0.00, 0.00, NULL),
(15, 6, 200000.00, 'completed', '250424_15', '2025-04-23 20:32:34', '2025-04-23 20:49:29', 'zalopay', 0.00, 0.00, NULL),
(16, 6, 640000.00, 'completed', '250424_16', '2025-04-23 20:47:50', '2025-04-23 20:49:24', 'zalopay', 0.00, 0.00, NULL),
(17, 6, 950000.00, 'pending', '250424_17', '2025-04-23 21:24:35', '2025-04-23 21:24:36', 'zalopay', 0.00, 0.00, NULL),
(18, 6, 180000.00, 'pending', NULL, '2025-04-24 06:53:28', '2025-04-24 06:53:28', 'zalopay', 0.00, 0.00, NULL),
(19, 6, 2000000.00, 'pending', NULL, '2025-04-24 06:54:03', '2025-04-24 06:54:03', 'zalopay', 0.00, 0.00, NULL),
(20, 6, 1100000.00, 'completed', '250424_20', '2025-04-24 06:56:50', '2025-04-24 07:01:50', 'zalopay', 0.00, 0.00, NULL),
(21, 6, 1000000.00, 'pending', '250424_21', '2025-04-24 07:36:40', '2025-04-24 07:36:41', 'zalopay', 0.00, 0.00, NULL),
(22, 6, 1000000.00, 'completed', '250424_22', '2025-04-24 07:38:27', '2025-05-07 18:31:57', 'zalopay', 0.00, 0.00, NULL),
(23, 6, 640000.00, 'pending', '250424_23', '2025-04-24 07:50:33', '2025-04-24 07:50:34', 'zalopay', 0.00, 0.00, NULL),
(24, 6, 580000.00, 'pending', '250424_24', '2025-04-24 07:58:31', '2025-04-24 07:58:32', 'zalopay', 0.00, 0.00, NULL),
(25, 6, 200000.00, 'pending', '250424_25', '2025-04-24 08:00:27', '2025-04-24 08:00:28', 'zalopay', 0.00, 0.00, NULL),
(26, 6, 1400000.00, 'pending', '250424_26', '2025-04-24 08:30:20', '2025-04-24 08:30:21', 'zalopay', 0.00, 0.00, NULL),
(27, 6, 1200000.00, 'pending', '250425_27', '2025-04-24 19:07:58', '2025-04-24 19:07:59', 'zalopay', 0.00, 0.00, NULL),
(28, 6, 550000.00, 'pending', '250427_28', '2025-04-26 23:09:13', '2025-04-26 23:09:16', 'zalopay', 0.00, 0.00, NULL),
(29, 6, 200000.00, 'pending', '250507_29', '2025-05-07 02:13:53', '2025-05-07 02:13:55', 'zalopay', 0.00, 0.00, NULL),
(30, 6, 180000.00, 'completed', '250508_30', '2025-05-07 18:16:45', '2025-05-07 18:31:49', 'zalopay', 0.00, 0.00, NULL),
(31, 6, 2700000.00, 'completed', '250508_31', '2025-05-07 19:28:21', '2025-05-07 19:43:50', 'zalopay', 0.00, 0.00, NULL),
(32, 6, 1000000.00, 'pending', '250511_32', '2025-05-11 00:49:50', '2025-05-11 00:49:52', 'zalopay', 0.00, 0.00, NULL),
(33, 6, 1000000.00, 'pending', '250511_33', '2025-05-11 00:56:04', '2025-05-11 00:56:05', 'zalopay', 0.00, 0.00, NULL),
(34, 6, 630000.00, 'pending', '250511_34', '2025-05-11 01:01:15', '2025-05-11 01:01:16', 'zalopay', 0.00, 0.00, NULL),
(35, 6, 1400000.00, 'pending', NULL, '2025-05-11 01:10:17', '2025-05-11 01:10:17', 'zalopay', 0.00, 0.00, NULL),
(36, 6, 1870000.00, 'pending', NULL, '2025-05-11 01:10:26', '2025-05-11 01:10:26', 'zalopay', 0.00, 0.00, NULL),
(37, 6, 270000.00, 'pending', NULL, '2025-05-11 01:11:41', '2025-05-11 01:11:41', 'zalopay', 0.00, 0.00, NULL),
(38, 6, 1600000.00, 'pending', NULL, '2025-05-11 01:13:06', '2025-05-11 01:13:06', 'zalopay', 0.00, 0.00, NULL),
(39, 6, 150000.00, 'pending', '250511_39', '2025-05-11 01:16:40', '2025-05-11 01:16:41', 'zalopay', 0.00, 0.00, NULL),
(40, 6, 150000.00, 'pending', '250511_40', '2025-05-11 01:25:32', '2025-05-11 01:25:33', 'zalopay', 0.00, 0.00, NULL),
(41, 6, 880000.00, 'pending', '250511_41', '2025-05-11 01:37:25', '2025-05-11 01:37:25', 'zalopay', 0.00, 0.00, NULL),
(42, 6, 1700000.00, 'pending', '250511_42', '2025-05-11 01:56:56', '2025-05-11 01:56:57', 'zalopay', 0.00, 0.00, NULL),
(43, 6, 930000.00, 'pending', '250511_43', '2025-05-11 02:06:04', '2025-05-11 02:06:05', 'zalopay', 0.00, 0.00, NULL),
(44, 6, 1700000.00, 'pending', '250511_44', '2025-05-11 02:08:04', '2025-05-11 02:08:04', 'zalopay', 0.00, 0.00, NULL),
(45, 6, 2800000.00, 'pending', '250511_45', '2025-05-11 02:13:51', '2025-05-11 02:13:52', 'zalopay', 0.00, 0.00, NULL),
(46, 6, 390000.00, 'completed', '250511_46', '2025-05-11 02:20:07', '2025-05-11 03:04:54', 'zalopay', 0.00, 0.00, NULL),
(47, 6, 2000000.00, 'completed', '250511_47', '2025-05-11 02:28:15', '2025-05-11 03:04:41', 'zalopay', 0.00, 0.00, NULL),
(48, 6, 880000.00, 'completed', '250511_48', '2025-05-11 02:33:24', '2025-05-11 03:04:37', 'zalopay', 0.00, 0.00, NULL),
(50, 6, 790000.00, 'pending', '250511_50', '2025-05-11 09:35:36', '2025-05-11 09:35:37', 'zalopay', 0.00, 0.00, NULL),
(51, 6, 1200000.00, 'pending', '250511_51', '2025-05-11 09:38:16', '2025-05-11 09:38:16', 'zalopay', 0.00, 0.00, NULL),
(52, 6, 880000.00, 'pending', '250513_52', '2025-05-12 18:49:20', '2025-05-12 18:49:21', 'zalopay', 0.00, 0.00, NULL),
(53, 6, 1500000.00, 'pending', NULL, '2025-05-12 19:00:22', '2025-05-12 19:00:22', 'zalopay', 0.00, 0.00, NULL),
(54, 6, 210000.00, 'pending', NULL, '2025-05-12 19:00:45', '2025-05-12 19:00:45', 'zalopay', 0.00, 0.00, NULL),
(55, 6, 980000.00, 'pending', NULL, '2025-05-12 19:04:42', '2025-05-12 19:04:42', 'zalopay', 0.00, 0.00, NULL),
(56, 6, 390000.00, 'pending', NULL, '2025-05-12 19:12:23', '2025-05-12 19:12:23', 'zalopay', 0.00, 0.00, NULL),
(57, 6, 1700000.00, 'pending', NULL, '2025-05-12 19:15:04', '2025-05-12 19:15:04', 'zalopay', 0.00, 0.00, NULL),
(59, 6, 1700000.00, 'pending', NULL, '2025-05-12 19:17:04', '2025-05-12 19:17:04', 'zalopay', 0.00, 0.00, NULL),
(60, 6, 1100000.00, 'pending', '250513_60', '2025-05-12 19:32:26', '2025-05-12 19:32:27', 'zalopay', 0.00, 0.00, NULL),
(61, 6, 1800000.00, 'pending', '250513_61', '2025-05-12 19:34:19', '2025-05-12 19:34:20', 'zalopay', 0.00, 0.00, NULL),
(62, 6, 1400000.00, 'pending', '250513_62', '2025-05-13 03:51:33', '2025-05-13 03:51:34', 'zalopay', 0.00, 0.00, NULL),
(63, 6, 930000.00, 'pending', '250513_63', '2025-05-13 03:51:47', '2025-05-13 03:51:47', 'zalopay', 0.00, 0.00, NULL),
(64, 6, 1400000.00, 'completed', NULL, '2025-05-13 04:04:34', '2025-05-13 04:08:01', 'cod', 0.00, 0.00, NULL),
(65, 6, 210000.00, 'completed', NULL, '2025-05-13 04:09:30', '2025-05-13 04:09:45', 'cod', 0.00, 30000.00, NULL),
(67, 6, 1200000.00, 'confirmed', NULL, '2025-05-13 04:15:02', '2025-05-13 04:15:02', 'cod', 0.00, 0.00, NULL),
(68, 6, 1300000.00, 'confirmed', NULL, '2025-05-13 04:15:35', '2025-05-13 04:15:35', 'cod', 0.00, 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 100, 2, 580000.00),
(2, 2, 95, 5, 600000.00),
(3, 3, 5, 1, 640000.00),
(4, 4, 109, 1, 1200000.00),
(5, 5, 8, 1, 60000.00),
(6, 6, 8, 1, 60000.00),
(7, 7, 8, 1, 60000.00),
(8, 8, 65, 1, 900000.00),
(9, 9, 3, 1, 950000.00),
(10, 10, 74, 1, 2000000.00),
(11, 11, 105, 1, 1600000.00),
(12, 12, 75, 1, 1100000.00),
(13, 13, 76, 1, 200000.00),
(14, 14, 73, 1, 1100000.00),
(15, 15, 76, 1, 200000.00),
(16, 16, 5, 1, 640000.00),
(17, 17, 108, 1, 950000.00),
(18, 18, 67, 1, 180000.00),
(19, 19, 79, 2, 1000000.00),
(20, 20, 75, 1, 1100000.00),
(21, 21, 79, 1, 1000000.00),
(22, 22, 79, 1, 1000000.00),
(23, 23, 5, 1, 640000.00),
(24, 24, 100, 1, 580000.00),
(25, 25, 76, 1, 200000.00),
(26, 26, 77, 1, 1400000.00),
(27, 27, 109, 1, 1200000.00),
(28, 28, 6, 1, 550000.00),
(29, 29, 76, 1, 200000.00),
(30, 30, 67, 1, 180000.00),
(31, 31, 80, 3, 900000.00),
(32, 32, 109, 1, 1200000.00),
(33, 33, 79, 1, 1000000.00),
(34, 34, 89, 1, 600000.00),
(35, 35, 86, 1, 1400000.00),
(36, 36, 1, 1, 1870000.00),
(37, 37, 87, 2, 120000.00),
(38, 38, 105, 1, 1600000.00),
(39, 39, 87, 1, 120000.00),
(40, 40, 87, 1, 120000.00),
(41, 41, 71, 1, 850000.00),
(42, 42, 71, 2, 850000.00),
(43, 43, 80, 1, 900000.00),
(44, 44, 71, 2, 850000.00),
(45, 45, 69, 1, 2800000.00),
(46, 46, 67, 2, 180000.00),
(47, 47, 79, 2, 1000000.00),
(48, 48, 71, 1, 850000.00),
(50, 50, 68, 1, 950000.00),
(51, 51, 64, 1, 1200000.00),
(52, 52, 71, 1, 850000.00),
(53, 53, 66, 1, 1500000.00),
(54, 54, 67, 1, 180000.00),
(55, 55, 108, 1, 950000.00),
(56, 56, 67, 2, 180000.00),
(57, 57, 71, 2, 850000.00),
(58, 59, 71, 2, 850000.00),
(59, 60, 73, 1, 1100000.00),
(60, 61, 80, 2, 900000.00),
(61, 62, 77, 1, 1400000.00),
(62, 63, 80, 1, 900000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `discount` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grape` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abv` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `volume` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '750ml',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rating` decimal(3,1) DEFAULT '0.0',
  `reviews` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `code`, `name`, `category_id`, `price`, `old_price`, `discount`, `stock`, `image`, `grape`, `type`, `brand`, `country`, `abv`, `volume`, `description`, `rating`, `reviews`, `created_at`, `updated_at`) VALUES
(1, 'RV001', 'Rượu vang Ý 60 Sessantanni Limited Edition (24 Karat Gold)', 2, 1870000.00, NULL, '', 25, 'https://winecellar.vn/wp-content/uploads/2023/11/60-sessantanni-limited-edition-24-karat-gold.jpg', 'Primitivo', 'Rượu vang đỏ', 'San Marzano', 'Vang Ý (Italy)', '13%', '750ml', 'Mẫu chai nho 60 năm từ thương hiệu Primitivo. Manduria D.O.P được làm phiên bản vang Ý đặc biệt.', 4.5, 10, '2025-04-17 02:43:25', '2025-04-24 02:22:10'),
(2, 'CG001', 'Cognac Pháp', 3, 2300000.00, NULL, NULL, 12, '/api/placeholder/220/300', NULL, 'Cognac', 'San Marzano', 'Pháp', '40%', '700ml', 'Rượu Cognac cao cấp từ Pháp, hương vị đậm đà.', 4.0, 5, '2025-04-17 02:43:25', '2025-04-17 02:43:25'),
(3, 'RV045', 'Rượu vang trắng &Yacute;', 2, 950000.00, NULL, '', 15, 'https://winevn.com/wp-content/uploads/2021/06/Ruou-Vang-Tavernello-Vino-Bianco-Ditalia-1.jpg', 'Chardonnay', 'Rượu vang trắng', 'San Marzano', 'Vang &Yacute; (Italy)', '12%', '750ml', 'Rượu vang trắng &Yacute;, nhẹ nh&agrave;ng v&agrave; thanh lịch.', 4.2, 8, '2025-04-17 02:43:25', '2025-04-24 02:07:40'),
(4, 'CH001', 'Champagne Pháp', 2, 2100000.00, NULL, NULL, 10, '/api/placeholder/220/300', 'Pinot Noir', 'Rượu vang sủi', 'San Marzano', 'Pháp', '12.5%', '750ml', 'Champagne Pháp cao cấp, lý tưởng cho các dịp lễ.', 4.8, 12, '2025-04-17 02:43:25', '2025-04-17 02:43:25'),
(5, 'RV023', 'Rượu vang Chile', 2, 640000.00, 800000.00, '-20%', 28, 'https://winecellar.vn/wp-content/uploads/2016/05/vistana-cabernet-sauvignon-1.jpg', 'Syrah', 'Rượu vang đỏ', 'San Marzano', 'Chile', '13.5%', '750ml', 'Rượu vang Chile đậm đ&agrave;, gi&aacute; trị tốt.', 4.3, 15, '2025-04-17 02:43:25', '2025-04-24 01:58:26'),
(6, 'RV056', 'Rượu vang Úc', 2, 550000.00, 690000.00, '-20%', 20, '/api/placeholder/220/300', 'Sauvignon Blanc', 'Rượu vang trắng', 'San Marzano', 'Úc', '12%', '750ml', 'Rượu vang Úc tươi mát, dễ uống.', 4.1, 7, '2025-04-17 02:43:25', '2025-04-17 02:43:25'),
(7, 'VK001', 'Vodka Nga Absolut', 6, 450000.00, NULL, '', 30, 'https://sanhruou.com/media/12111/catalog/Vodka-Absolut.jpg', '', 'Vodka', 'Absolut', 'Nga', '40%', '700ml', 'Vodka tinh khiết từ Nga, l&yacute; tưởng cho cocktail.', 4.0, 6, '2025-04-17 02:43:25', '2025-04-24 01:59:03'),
(8, 'BR001', 'Bia Bỉ Hoegaarden', 7, 60000.00, NULL, '', 50, 'https://douongnhapkhau.com/wp-content/uploads/2020/01/5cc189ada3015a5f0310.jpg', '', 'Bia trắng', 'Hoegaarden', 'Bỉ', '4.9%', '330ml', 'Bia trắng Bỉ, tươi m&aacute;t với hương cam qu&yacute;t.', 4.5, 20, '2025-04-17 02:43:25', '2025-04-24 01:52:13'),
(9, 'CK001', 'Cocktail Mojito Pha Sẵn', 8, 120000.00, NULL, NULL, 15, '/api/placeholder/220/300', NULL, 'Cocktail', 'Bacardi', 'Cuba', '5%', '250ml', 'Cocktail Mojito pha sẵn, tiện lợi và sảng khoái.', 4.2, 10, '2025-04-17 02:43:25', '2025-04-17 02:43:25'),
(10, 'GT001', 'Hộp quà rượu vang Ý', 9, 2500000.00, NULL, '', 8, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQhV6e9ne-3jhNzyNNk8k3G8W6Q4Yl7DfAYsw&s', '', 'Hộp quà', 'San Marzano', 'Vang Ý (Italy)', '', '750ml', 'Hộp quà sang trọng gồm 2 chai vang Ý và ly pha lê.', 4.7, 5, '2025-04-17 02:43:25', '2025-05-13 17:28:59'),
(11, 'CG002', 'Ly pha lê Riedel Bordeaux', 4, 800000.00, NULL, NULL, 20, '/api/placeholder/220/300', NULL, 'Ly pha lê', 'Riedel', 'Áo', NULL, NULL, 'Ly pha lê cao cấp dành cho rượu vang đỏ.', 4.6, 8, '2025-04-17 02:43:25', '2025-04-17 02:43:25'),
(62, 'RV002', 'Rượu vang đỏ Pháp Château Lafite', 1, 3200000.00, NULL, NULL, 50, '/images/rv002.jpg', 'Cabernet Sauvignon', 'Red Wine', 'Château Lafite', 'France', '13.5%', '750ml', 'Rượu vang đỏ cao cấp với hương vị đậm đà, cấu trúc mượt mà.', 4.8, 120, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(63, 'WS003', 'Whisky Scotland Macallan 12 năm', 1, 2000000.00, NULL, '', 40, 'https://sanhruou.com/media/1449/catalog/ruou-macallan-12-nam-double-cask.jpg', '', 'Whisky', 'Macallan', 'Scotland', '40%', '700ml', 'Whisky single malt với hương vị tr&aacute;i c&acirc;y kh&ocirc; v&agrave; gia vị.', 4.7, 80, '2025-04-18 15:33:52', '2025-04-24 01:57:54'),
(64, 'VD004', 'Vodka Premium Grey Goose', 1, 1200000.00, NULL, '', 60, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT1AnolHo_OM7ZY6CURv_hNt-CdC5j79UT7jg&s', '', 'Vodka', 'Grey Goose', 'France', '40%', '750ml', 'Vodka mượt m&agrave;, tinh khiết, l&yacute; tưởng cho cocktail.', 4.5, 90, '2025-04-18 15:33:52', '2025-04-24 02:08:11'),
(65, 'GN005', 'Gin London Dry Beefeater', 1, 900000.00, NULL, '', 55, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS0owJLsKBNl-HOLP5BdgJDS7eEpkIkcpzEhw&s', '', 'Gin', 'Beefeater', 'England', '40%', '700ml', 'Gin cổ điển với hương thảo mộc v&agrave; cam qu&yacute;t.', 4.3, 60, '2025-04-18 15:33:52', '2025-04-24 01:56:59'),
(66, 'SK006', 'Rượu Sake Nhật Bản Dassai 23', 1, 1500000.00, NULL, '', 30, 'https://cdn.ruoutuongvy.com/files/2024/05/18/080928-ruou-sake-nhat-ban-dassai-23-magnum-18l.webpMrPRdjp8YDcXq78NItuBs0TnhA8pj5Ej2bw&s', '', 'Sake', 'Dassai', 'Japan', '16%', '720ml', 'Sake cao cấp với hương vị tinh tế, thanh lịch.', 4.9, 100, '2025-04-18 15:33:52', '2025-04-24 02:07:09'),
(67, 'BL007', 'Bia thủ c&ocirc;ng Lager Đức', 1, 180000.00, NULL, '', 80, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT5DSo_TsYiisRfC9-BXHf3oGWv83dUgw_vYQ&s', '', 'Beer', 'Warsteiner', 'Germany', '4.8%', '500ml', 'Bia lager tươi m&aacute;t, dễ uống.', 4.2, 50, '2025-04-18 15:33:52', '2025-04-24 01:56:34'),
(68, 'RV008', 'Rượu vang trắng Ý Santa Margherita', 1, 950000.00, NULL, '', 45, 'https://sanhruou.com/media/6262/catalog/Santa-Margherita-Pinot-Grigio.jpg', 'Pinot Grigio', 'White Wine', 'Santa Margherita', 'Italy', '12.5%', '750ml', 'Rượu vang trắng với hương táo xanh và hoa trắng.', 4.6, 70, '2025-04-18 15:33:52', '2025-04-24 02:17:25'),
(69, 'CH009', 'Champagne Brut Veuve Clicquot', 1, 2800000.00, NULL, NULL, 25, '/images/ch009.jpg', NULL, 'Champagne', 'Veuve Clicquot', 'France', '12%', '750ml', 'Champagne với bọt mịn và hương vị trái cây tươi.', 4.8, 110, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(70, 'TQ010', 'Tequila Blanco Patrón', 1, 1300000.00, NULL, NULL, 35, '/images/tq010.jpg', NULL, 'Tequila', 'Patrón', 'Mexico', '40%', '750ml', 'Tequila tinh khiết với hương agave và cam quýt.', 4.4, 65, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(71, 'RM011', 'Rum Trắng Mount Gay', 1, 850000.00, NULL, NULL, 50, '/images/rm011.jpg', NULL, 'Rum', 'Mount Gay', 'Barbados', '40%', '700ml', 'Rum trắng mượt mà, lý tưởng cho cocktail.', 4.3, 55, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(72, 'WJ012', 'Whisky Nhật Bản Hibiki 17 năm', 2, 3000000.00, NULL, NULL, 30, '/images/wj012.jpg', NULL, 'Whisky', 'Hibiki', 'Japan', '43%', '700ml', 'Whisky blend với hương hoa và gỗ sồi.', 4.8, 130, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(73, 'RV013', 'Rượu vang đỏ Chile Carmenère', 2, 1100000.00, NULL, NULL, 60, '/images/rv013.jpg', 'Carmenère', 'Red Wine', 'Concha y Toro', 'Chile', '13.5%', '750ml', 'Rượu vang đỏ đậm đà với hương ớt chuông và mâm xôi.', 4.5, 90, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(74, 'VD014', 'Vodka Beluga Gold Line', 2, 2000000.00, NULL, NULL, 25, '/images/vd014.jpg', NULL, 'Vodka', 'Beluga', 'Russia', '40%', '750ml', 'Vodka siêu cao cấp với thiết kế sang trọng.', 4.7, 100, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(75, 'GN015', 'Gin Hendrick&rsquo;s', 2, 1100000.00, NULL, '', 50, 'https://sanhruou.com/media/13076/catalog/Hendricks-Gin-1-Lit.jpg', '', 'Gin', 'Hendrick&rsquo;s', 'Scotland', '41.4%', '700ml', 'Gin với hương dưa chuột v&agrave; hoa hồng.', 4.6, 80, '2025-04-18 15:33:52', '2025-04-24 02:08:42'),
(76, 'BS016', 'Bia thủ công Stout Ireland', 2, 200000.00, NULL, NULL, 70, '/images/bs016.jpg', NULL, 'Beer', 'Guinness', 'Ireland', '4.2%', '500ml', 'Bia stout đậm đà với hương cà phê và sô-cô-la.', 4.4, 120, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(77, 'RM017', 'Rum N&acirc;u Appleton Estate 12 năm', 2, 1400000.00, NULL, '', 40, 'https://ruoungoaiald.com/wp-content/uploads/rum-Appleton-Estate-12-nam.jpg', '', 'Rum', 'Appleton Estate', 'Jamaica', '43%', '700ml', 'Rum n&acirc;u với hương vani v&agrave; tr&aacute;i c&acirc;y nhiệt đới.', 4.5, 85, '2025-04-18 15:33:52', '2025-04-24 01:51:25'),
(78, 'CH018', 'Champagne Moët &amp; Chandon Rosé', 2, 2500000.00, NULL, '', 30, 'https://topruou.com/Image/Picture/Hinh%20nen_Vang/Ruou_Champagne_Moet_Chandon_Rose_Imperial.jpg', '', 'Champagne', 'Moët & Chandon', 'France', '12%', '750ml', 'Champagne hồng phấn với hương dâu tây.', 4.8, 140, '2025-04-18 15:33:52', '2025-04-24 02:17:19'),
(79, 'RV019', 'Rượu vang trắng New Zealand Sauvignon Blanc', 2, 1000000.00, NULL, '', 55, 'https://product.hstatic.net/1000358764/product/vidal_estate_sauvignon_blanc__new_zealand_87e6033ed38f4048b751e1f5f4377a05.png', 'Sauvignon Blanc', 'White Wine', 'Cloudy Bay', 'New Zealand', '13%', '750ml', 'Rượu vang trắng tươi m&aacute;t với hương chanh d&acirc;y.', 4.6, 95, '2025-04-18 15:33:52', '2025-04-24 02:06:23'),
(80, 'WI020', 'Whisky Ireland Jameson', 2, 900000.00, NULL, '', 65, 'https://mcgrocer.com/cdn/shop/files/jameson-triple-distilled-blended-irish-whiskey-1l-41369596756206.jpg?v=1742217485', '', 'Whisky', 'Jameson', 'Ireland', '40%', '700ml', 'Whisky mượt m&agrave; với hương vani v&agrave; tr&aacute;i c&acirc;y.', 4.3, 75, '2025-04-18 15:33:52', '2025-04-24 01:49:18'),
(81, 'RV021', 'Rượu vang đỏ Tây Ban Nha Tempranillo', 3, 650000.00, 850000.00, '-24%', 80, 'https://product.hstatic.net/200000311137/product/n-nha-bayanegra-red-tempranillo-750ml_9a02e77ca82649f990ccf04000690d9f_e9a4ab099ded4d5985d7d1a6c0b151a7.jpg', 'Tempranillo', 'Red Wine', 'Bodegas Faustino', 'Spain', '13.5%', '750ml', 'Rượu vang đỏ với hương anh đào và gỗ sồi.', 4.2, 60, '2025-04-18 15:33:52', '2025-04-24 02:17:09'),
(83, 'TQ023', 'Tequila Gold Jose Cuervo', 3, 750000.00, 950000.00, '-21%', 70, '/images/tq023.jpg', NULL, 'Tequila', 'Jose Cuervo', 'Mexico', '38%', '750ml', 'Tequila vàng với hương caramel và agave.', 4.1, 55, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(84, 'RM024', 'Rum Spiced Captain Morgan', 3, 500000.00, 650000.00, '-23%', 85, '/images/rm024.jpg', NULL, 'Rum', 'Captain Morgan', 'Jamaica', '35%', '700ml', 'Rum gia vị với hương vani và quế.', 4.0, 45, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(85, 'RV025', 'Rượu vang trắng Chile Chardonnay', 3, 550000.00, 700000.00, '-21%', 75, 'https://winecellar.vn/wp-content/uploads/2016/08/vistana-chardonnay-2020.jpg', 'Chardonnay', 'White Wine', 'Santa Rita', 'Chile', '13%', '750ml', 'Rượu vang trắng với hương t&aacute;o v&agrave; bơ.', 4.2, 65, '2025-04-18 15:33:52', '2025-04-24 01:50:37'),
(86, 'WS026', 'Whisky Blended Chivas Regal 18 năm', 3, 1400000.00, 1800000.00, '-22%', 50, 'https://maltco.asia/wp-content/uploads/2019/02/CHIVAS-18-750ML-maltco..jpg', '', 'Whisky', 'Chivas Regal', 'Scotland', '40%', '700ml', 'Whisky blend cao cấp với hương s&ocirc;-c&ocirc;-la v&agrave; tr&aacute;i c&acirc;y.', 4.5, 80, '2025-04-18 15:33:52', '2025-04-24 01:50:15'),
(87, 'BH027', 'Bia Hefeweizen Hoegaarden', 3, 120000.00, 160000.00, '-25%', 100, 'https://douongnhapkhau.com/wp-content/uploads/2020/01/7dcea1a38b0f72512b1e.jpg', '', 'Beer', 'Hoegaarden', 'Belgium', '4.9%', '500ml', 'Bia l&uacute;a m&igrave; với hương cam v&agrave; ng&ograve;.', 4.3, 70, '2025-04-18 15:33:52', '2025-04-24 01:49:47'),
(88, 'GN028', 'Gin Tonic Roku', 3, 850000.00, 1100000.00, '-23%', 60, '/images/gn028.jpg', NULL, 'Gin', 'Roku', 'Japan', '43%', '700ml', 'Gin Nhật Bản với hương yuzu và trà xanh.', 4.4, 75, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(89, 'RV029', 'Rượu vang hồng Ý Rosato', 3, 600000.00, 800000.00, '-25%', 65, 'https://winecellar.vn/wp-content/uploads/2022/12/tavernello-sangiovese-rosato-2021.jpg', 'Sangiovese', 'Rosé Wine', 'Antinori', 'Italy', '12.5%', '750ml', 'Rượu vang hồng tươi mát với hương dâu tây.', 4.1, 50, '2025-04-18 15:33:52', '2025-04-24 02:17:00'),
(90, 'SK030', 'Rượu Sake Nhật Bản Kubota', 3, 900000.00, 1200000.00, '-25%', 55, 'https://cdn.ruoutuongvy.com/files/2024/09/05/091158-ruou-sake-nhat-ban-kubota-manjyu-jishakobo-jikomi.webp', '', 'Sake', 'Kubota', 'Japan', '15%', '720ml', 'Sake với hương tr&aacute;i c&acirc;y nhẹ nh&agrave;ng.', 4.3, 60, '2025-04-18 15:33:52', '2025-04-24 01:47:14'),
(91, 'LY001', 'Ly pha l&ecirc; Riedel Bordeaux', 4, 500000.00, NULL, '', 100, 'https://winecellar.vn/wp-content/uploads/2024/09/ly-ruou-vang-do-riedel-superleggero-bordeaux-grand-cru-red-1.jpg', '', 'Glass', 'Riedel', 'Austria', '', '600ml', 'Ly pha l&ecirc; cao cấp d&agrave;nh cho rượu vang đỏ Bordeaux.', 4.8, 200, '2025-04-18 15:33:52', '2025-04-24 01:47:42'),
(92, 'LY031', 'Ly pha lê Riedel Cabernet Sauvignon', 4, 550000.00, NULL, NULL, 90, '/images/ly031.jpg', NULL, 'Glass', 'Riedel', 'Austria', NULL, '650ml', 'Ly pha lê tối ưu cho rượu vang Cabernet.', 4.7, 180, '2025-04-18 15:33:52', '2025-04-18 15:33:52'),
(93, 'LY032', 'Ly rượu vang trắng Riedel', 4, 480000.00, NULL, '', 95, 'https://winecellar.vn/wp-content/uploads/2021/06/ly-pha-le-degustazione-white-wine.png', '', 'Glass', 'Riedel', 'Austria', '', '400ml', 'Ly pha l&ecirc; cho rượu vang trắng, tăng hương vị.', 4.6, 150, '2025-04-18 15:33:52', '2025-04-24 01:46:36'),
(94, 'LY033', 'Ly whisky Glencairn', 4, 350000.00, NULL, '', 120, 'https://winecellar.vn/wp-content/uploads/2024/04/ly-glencairn-200ml-3.jpg', '', 'Glass', 'Glencairn', 'Scotland', '', '200ml', 'Ly whisky chuy&ecirc;n dụng với thiết kế tối ưu.', 4.5, 140, '2025-04-18 15:33:52', '2025-04-24 01:46:10'),
(95, 'LY034', 'Ly champagne Riedel Flute', 4, 600000.00, NULL, '', 80, 'https://sanhvang.com/wp-content/uploads/2020/02/bo-6-ly-riedel-degustazione-champagne-flute-212ml.jpg.webp', '', 'Glass', 'Riedel', 'Austria', '', '250ml', 'Ly pha l&ecirc; cho champagne, giữ bọt l&acirc;u.', 4.7, 160, '2025-04-18 15:33:52', '2025-04-24 01:45:49'),
(96, 'LY035', 'Ly cocktail Riedel Martini', 4, 450000.00, NULL, '', 100, 'https://www.garboglass.com/data/watermark/20210804/610a6851316c3.jpg', '', 'Glass', 'Riedel', 'Austria', '', '300ml', 'Ly pha l&ecirc; cho cocktail martini sang trọng.', 4.6, 130, '2025-04-18 15:33:52', '2025-04-24 01:45:25'),
(97, 'LY036', 'Ly rượu vang đỏ Spiegelau', 4, 520000.00, NULL, '', 85, 'https://down-vn.img.susercontent.com/file/e5104b39a642991436636ddf3a577558', '', 'Glass', 'Spiegelau', 'Germany', '', '600ml', 'Ly pha l&ecirc; bền bỉ cho rượu vang đỏ.', 4.4, 110, '2025-04-18 15:33:52', '2025-04-24 01:44:43'),
(98, 'LY037', 'Ly whisky Spiegelau', 4, 400000.00, NULL, '', 90, 'https://ahangduc.com/wp-content/smush-webp/2020/04/Bo-ly-pha-le-Spiegelau-Nachtmann-Whisky_3.jpg.webp', '', 'Glass', 'Spiegelau', 'Germany', '', '250ml', 'Ly whisky với thiết kế hiện đại.', 4.3, 100, '2025-04-18 15:33:52', '2025-04-24 01:44:14'),
(99, 'LY038', 'Ly rượu vang trắng Spiegelau', 4, 470000.00, NULL, '', 95, 'https://down-vn.img.susercontent.com/file/13b9799d01b4ed19f44c27091216864c', '', 'Glass', 'Spiegelau', 'Germany', '', '400ml', 'Ly pha l&ecirc; cho rượu vang trắng, nhẹ nh&agrave;ng.', 4.5, 120, '2025-04-18 15:33:52', '2025-04-24 01:43:32'),
(100, 'LY039', 'Ly champagne Spiegelau Flute', 4, 580000.00, NULL, '', 80, 'https://thegioigiadungduc.com/wp-content/uploads/2023/12/bo-12-ly-vang-cao-cap-spiegelau-4400192-authentis-glaser-set-12-tlg45.jpg?v=1702264932', '', 'Glass', 'Spiegelau', 'Germany', '', '250ml', 'Ly pha l&ecirc; cho champagne, tinh tế.', 4.6, 140, '2025-04-18 15:33:52', '2025-04-24 01:39:41'),
(101, 'WS040', 'Whisky Scotland Glenlivet 18 năm', 5, 2200000.00, NULL, '', 40, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRV7-EBAU7otAXXXdj-AOBzqW19AYA7MNd4Yg&s', '', 'Whisky', 'Glenlivet', 'Scotland', '40%', '700ml', 'Whisky single malt với hương mật ong v&agrave; gỗ sồi.', 4.7, 90, '2025-04-18 15:33:52', '2025-04-24 01:37:53'),
(102, 'VD041', 'Vodka Absolut Blue', 5, 600000.00, NULL, '', 80, 'https://mcgrocer.com/cdn/shop/files/absolut-blue-original-swedish-vodka-1l-41369456771310.jpg?v=1735641458', '', 'Vodka', 'Absolut', 'Sweden', '40%', '750ml', 'Vodka cổ điển, linh hoạt cho nhiều loại cocktail.', 4.2, 70, '2025-04-18 15:33:52', '2025-04-24 01:37:33'),
(103, 'GN042', 'Gin Monkey 47', 5, 1300000.00, NULL, '', 50, 'https://sanhruou.com/media/1379/catalog/ruou-monkey-47.jpg', '', 'Gin', 'Monkey 47', 'Germany', '47%', '500ml', 'Gin cao cấp với 47 loại thảo mộc.', 4.8, 100, '2025-04-18 15:33:52', '2025-04-24 01:37:03'),
(104, 'RM043', 'Rum Nâu Zacapa 23 năm', 5, 1800000.00, NULL, '', 35, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTXb1oYF111kQ9mWjwVz3SGSZ61gR_KZessmw&s', '', 'Rum', 'Zacapa', 'Guatemala', '40%', '750ml', 'Rum nâu với hương sô-cô-la và trái cây khô.', 4.7, 85, '2025-04-18 15:33:52', '2025-04-24 02:16:51'),
(105, 'TQ044', 'Tequila Don Julio Añejo', 5, 1600000.00, NULL, '', 45, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTzma-4CWbcRl_ocrsddIImDveehJwPagJBxg&s', '', 'Tequila', 'Don Julio', 'Mexico', '40%', '750ml', 'Tequila ủ với hương vani và caramel.', 4.6, 80, '2025-04-18 15:33:52', '2025-04-24 02:16:41'),
(106, 'SK045', 'Rượu Sake Nhật Bản Hakkaisan Junmai', 5, 1100000.00, NULL, '', 60, 'https://cdn.ruoutuongvy.com/files/2024/05/21/100221-ruou-sake-nhat-ban-hakkaisan-junmai-daiginjo.webp', '', 'Sake', 'Hakkaisan', 'Japan', '15.5%', '720ml', 'Sake tinh khiết với hương gạo v&agrave; hoa.', 4.5, 75, '2025-04-18 15:33:52', '2025-04-24 01:34:55'),
(107, 'WS046', 'Whisky Nhật Bản Nikka From The Barrel', 5, 1700000.00, NULL, '', 50, 'https://topruou.com/Image/Picture/Whisky-Nhat/Nikka/Ruou-Nikka-From-Barrel.jpg', '', 'Whisky', 'Nikka', 'Japan', '51.4%', '500ml', 'Whisky đậm đ&agrave; với hương gia vị v&agrave; tr&aacute;i c&acirc;y.', 4.8, 95, '2025-04-18 15:33:52', '2025-04-24 01:33:15'),
(108, 'VD047', 'Vodka Ketel One', 5, 950000.00, NULL, '', 65, 'https://sanhruou.com/media/12303/catalog/Ketel-One.jpg', '', 'Vodka', 'Ketel One', 'Netherlands', '40%', '750ml', 'Vodka mượt m&agrave; với hương cam qu&yacute;t nhẹ.', 4.4, 60, '2025-04-18 15:33:52', '2025-04-24 01:32:03'),
(109, 'GN048', 'Gin The Botanist', 5, 1200000.00, NULL, '', 80, 'https://sanhruou.com/media/5028/catalog/The-Botanist-Gin.jpg', '', 'Gin', 'The Botanist', 'Scotland', '46%', '700ml', 'Gin với 22 loại thảo mộc từ đảo Islay.', 4.7, 90, '2025-04-18 15:33:52', '2025-04-24 01:30:36');

-- --------------------------------------------------------

--
-- Table structure for table `product_comments`
--

CREATE TABLE `product_comments` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_comments`
--

INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment_text`, `created_at`) VALUES
(1, 109, 6, 'abc', '2025-05-11 03:58:41'),
(2, 109, 6, 'abc', '2025-05-11 03:58:41'),
(3, 109, 6, 'hello', '2025-05-11 03:59:18'),
(4, 109, 6, 'hello', '2025-05-11 03:59:18'),
(5, 109, 6, 'hi', '2025-05-11 04:01:26'),
(6, 109, 6, 'hi', '2025-05-11 04:01:26'),
(7, 109, 6, 'bvfvsva', '2025-05-11 04:02:28'),
(8, 109, 6, 'bvfvsva', '2025-05-11 04:02:28'),
(9, 109, 6, 'qfvsfdnf', '2025-05-11 04:03:55'),
(10, 109, 6, 'qfvsfdnf', '2025-05-11 04:03:55'),
(11, 109, 6, 'ghngf', '2025-05-11 04:04:13'),
(12, 109, 6, 'ghngf', '2025-05-11 04:04:13'),
(13, 109, 6, 'cadvad', '2025-05-11 04:04:21'),
(14, 109, 6, 'cadvad', '2025-05-11 04:04:28'),
(15, 109, 6, 'cadvad', '2025-05-11 04:04:33'),
(16, 109, 6, 'cadvad', '2025-05-11 04:04:33'),
(17, 109, 6, 'cadvadvd', '2025-05-11 04:04:33'),
(18, 109, 6, 'cadvadvd', '2025-05-11 04:04:33'),
(19, 109, 6, 'bfsfs', '2025-05-11 04:04:36'),
(20, 109, 6, 'bfsfs', '2025-05-11 04:04:36'),
(21, 71, 6, 'abc', '2025-05-11 04:51:34'),
(22, 71, 6, 'abc', '2025-05-11 04:51:34'),
(23, 71, 6, 'cadf', '2025-05-11 04:51:36'),
(24, 71, 6, 'cadf', '2025-05-11 04:51:36'),
(25, 71, 6, 'fvfs', '2025-05-11 04:51:38'),
(26, 71, 6, 'fvfs', '2025-05-11 04:51:38'),
(27, 71, 6, 'sdfsd', '2025-05-11 04:51:39'),
(28, 71, 6, 'sdfsd', '2025-05-11 04:51:39'),
(29, 71, 6, 'rwgwr', '2025-05-11 04:51:40'),
(30, 71, 6, 'rwgwr', '2025-05-11 04:51:40'),
(31, 71, 6, 'vwvw', '2025-05-11 04:51:42'),
(32, 71, 6, 'vwvw', '2025-05-11 04:51:42');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `created_at`) VALUES
(1, 5, 20.00, '2025-04-01 00:00:00', '2025-04-30 23:59:59', '2025-04-17 02:43:25'),
(2, 6, 20.00, '2025-04-01 00:00:00', '2025-04-30 23:59:59', '2025-04-17 02:43:25');

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int NOT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `min_order_value` decimal(10,2) DEFAULT '0.00',
  `max_discount_value` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `phone`, `address`, `password`, `is_admin`, `created_at`, `updated_at`) VALUES
(2, 'Trần Thị Bình', 'binhthitran', 'tranthibinh@example.com', '0912345678', 'Số 45 Nguyễn Thị Minh Khai, Quận 3, TP HCM', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-04-07 14:22:45', '2025-04-24 02:28:11'),
(3, 'Lê Văn Cường', 'levancuong', 'levancuong@example.com', '0939876543', '78 Lê Duẩn, Quận Hai Bà Trưng, Hà Nội', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-04-06 08:55:12', '2025-04-09 15:23:53'),
(4, 'Phạm Thị Dung', 'phamthidung', 'phamthidung@example.com', '0965432198', 'Lô B2-15, KĐT Mỹ Đình, Nam Từ Liêm, Hà Nội', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-04-05 16:30:40', '2025-04-09 15:23:53'),
(5, 'Admin System', 'admin', 'admin@cuahangdouong.vn', '0909123456', 'Trụ sở công ty, Quận 1, TP HCM', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-04-01 09:00:00', '2025-04-09 15:23:53'),
(6, 'Lê Hoàng', 'hoanglee', 'hoangleedz32@gmail.com', '1234567890', 'Hà Nội', '$2y$10$zbNEAjsAwNBAz2QlP5Y6fOPraCo.4DfFf7kvY3LK3ZkxWv7eiZu4.', 0, '2025-04-09 23:26:27', '2025-05-09 16:32:24'),
(7, 'Đạt', 'nguyendat', 'hoanggg12@gmail.com', '09876543211', 'bac ninh', '$2y$10$xcyemlUB6X/7O8h2QL9HxuijiJQQi7SyjQi5MbcSe0w83CdazfoJS', 0, '2025-04-10 07:34:56', '2025-04-10 00:34:56'),
(8, 'Hoàng', 'leehoang', 'leehoang@gmail.com', '0965768529', 'Lào Cai', '$2y$10$z3Qz7g7X9vX8y5Z1b2c3d.uK8y9vX7w6Z5b4c3d2e1f0g9h8i7j6k', 1, '2025-04-20 06:16:03', '2025-05-09 17:11:24'),
(9, 'nguyễn văn A', 'buinamtruong', 'hoanggg322004@gmail.com\r\n', '0987651223', 'Bắc Ninh', '$2y$10$MeYqKUg960cGn77sdKai0eSzln9t8AVjM.JQUNa68BCVpKq43zgl.', 1, '2025-04-20 11:40:23', '2025-05-09 16:49:56'),
(10, 'Nguyễn Văn A', 'nguyenvana', 'nva@gmail.com', '0912312365', 'Hải Phòng', '$2y$10$ZLYf1E1mLVqJeFFWUeIlp.26Lj.Tj19yeJW5YSEuUtlWwAI0Ni3KG', 0, '2025-04-20 22:46:30', '2025-04-20 15:46:30'),
(11, 'cuong', 'cuong', 'cuong@gmail.com', '129839819', 'bac ninh', '$2y$10$TLgBzd6OB63UiiTXO3FI3eE8CWGKRT/JgxzoGH24X3nnGpuf1CvO6', 0, '2025-04-23 09:46:57', '2025-04-23 02:46:57'),
(15, 'dat', 'dat', 'dat@gmail.com', '1231452523', 'ha noi', '$2y$10$FSOcQaD5E.OHFsIezYxh/.gY6kH8M6evytB9Ub6anZ/n54hoeLSt6', 0, '2025-04-24 09:25:50', '2025-04-24 02:25:50'),
(16, 'tuấn', 'tuan', 'tuan@gmail.com', '2145678864', 'Nghệ An', '$2y$10$5ZI0OLMXXaQfudE67mSSE.rT9DHBjfipj0F7JJntveZE2btUE.5Vy', 0, '2025-05-06 23:21:36', '2025-05-06 16:21:36'),
(17, 'Nguyễn Văn C', 'nguyenvanc', 'nvc@gmail.com', '0923123123', 'Bắc Nịnh', '$2y$10$QAoLiLuHhiwGgKW/RKRwR.Kfp2INS50drIP.6UJSWaYYBBNDwyG0e', 0, '2025-05-08 08:41:36', '2025-05-08 01:41:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inventory_product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD KEY `fk_order_items_order_id` (`order_id`),
  ADD KEY `fk_order_items_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_products_category_id` (`category_id`),
  ADD KEY `idx_products_price` (`price`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
