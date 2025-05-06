-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 24, 2025 lúc 06:00 AM
-- Phiên bản máy phục vụ: 8.0.32
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `drink_store`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
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
-- Đang đổ dữ liệu cho bảng `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(9, 6, 6, 8, '2025-04-18 16:42:44', '2025-04-22 13:11:51'),
(10, 6, 80, 12, '2025-04-20 03:36:39', '2025-04-24 01:09:27'),
(27, 6, 79, 2, '2025-04-24 02:36:22', '2025-04-24 02:36:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
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
-- Cấu trúc bảng cho bảng `contacts`
--

CREATE TABLE `contacts` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Đang đổ dữ liệu cho bảng `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`) VALUES
(1, 'Nguyen Van Minh', 'nvminh@example.com', '0901234567', 'Craft Beer Inquiry', 'I am interested in your selection of craft beers. Do you have any IPA varieties from local breweries? Please send me your current list and pricing.', '2025-04-15 14:30:00'),
(2, 'Tran Thi Huong', 'tthuong@example.com', '0912345678', 'Wine Tasting Event', 'I would like to register for the wine tasting event on May 5th. Is there still space available? I am particularly interested in the French wines.', '2025-04-16 10:45:00'),
(3, 'Le Quang Dat', 'lqdat@example.com', '0923456789', 'Whiskey Collection', 'I am a whiskey enthusiast looking to expand my collection. Do you have any rare Japanese whiskeys in stock? I am willing to pay premium for quality bottles.', '2025-04-17 11:15:00'),
(4, 'Pham Ngoc Linh', 'pnlinh@example.com', '0934567890', 'Cocktail Workshop', 'I saw your advertisement for the upcoming cocktail making workshop. Please reserve 2 spots for me and my friend. We are excited to learn mixology techniques.', '2025-04-18 14:20:00'),
(5, 'Hoang Thanh Tung', 'httung@example.com', '0945678901', 'Bulk Order for Event', 'Our company is hosting a corporate event next month and we need to order champagne and wine for 50 guests. Can you provide a quote and delivery options?', '2025-04-19 08:10:00'),
(6, 'Nguyen Thi Mai', 'ntmai@example.com', NULL, 'Vodka Recommendations', 'I am planning a party and need recommendations for premium vodka brands that would be good for cocktails. What would you suggest for approximately 20 guests?', '2025-04-19 13:25:00'),
(7, 'Tran Van Hung', 'tvhung@example.com', '0967890123', 'Rum Tasting Club', 'I am interested in joining your monthly rum tasting club. Could you please send me information about membership fees and the schedule for upcoming tastings?', '2025-04-20 16:40:00'),
(8, 'Le Hong Nhung', 'lhnhung@example.com', '0978901234', 'Product Complaint', 'I recently purchased a bottle of your premium gin and noticed the seal was broken. I would like to request a replacement or refund for this product.', '2025-04-20 17:55:00'),
(9, 'Pham Quoc Bao', 'pqbao@example.com', NULL, 'Local Brewery Tour', 'I would like to book a tour of your brewery for a group of 6 people this weekend. Do you offer tastings as part of the tour? What are your opening hours?', '2025-04-21 09:05:00'),
(10, 'Vo Thi Kim Anh', 'vtkanh@example.com', '0990123456', 'Sake Import Inquiry', 'I am opening a Japanese restaurant and looking for a reliable supplier of premium sake. Please share your catalog and wholesale pricing for regular orders.', '2025-04-21 10:30:00'),
(11, 'Lê Hoàng', 'hoanggg12@gmail.com', '0965768629', 'weewij', 'đá', '2025-04-21 15:33:20'),
(12, 'bùi nam truong', 'bnt@gmailc.om', '0912018382', 'abc', 'ngon', '2025-04-21 15:51:29'),
(13, 'hào', 'hao@gmail.com', '1234665756', 'abc', 'abc', '2025-04-23 14:51:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory`
--

CREATE TABLE `inventory` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `location` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `quantity`, `location`, `last_updated`) VALUES
(1, 62, 10, 'abc', '2025-04-21 16:01:43'),
(2, 109, 20, 'ac', '2025-04-22 01:20:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed','cancelled','processing','shipped','delivered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `zalopay_trans_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `zalopay_trans_id`, `created_at`, `updated_at`) VALUES
(1, 6, 1160000.00, 'pending', '250423_1', '2025-04-23 14:12:06', '2025-04-23 14:12:07'),
(2, 6, 3000000.00, 'pending', '250423_2', '2025-04-23 14:18:29', '2025-04-23 14:18:30'),
(3, 6, 640000.00, 'pending', '250423_3', '2025-04-23 14:21:45', '2025-04-23 14:21:45'),
(4, 6, 1200000.00, 'pending', '250423_4', '2025-04-23 14:25:07', '2025-04-23 14:25:08'),
(5, 6, 60000.00, 'pending', '250423_5', '2025-04-23 14:30:39', '2025-04-23 14:30:39'),
(6, 6, 60000.00, 'pending', '250423_6', '2025-04-23 14:34:25', '2025-04-23 14:34:26'),
(7, 6, 60000.00, 'pending', '250423_7', '2025-04-23 14:57:27', '2025-04-23 14:57:28'),
(8, 6, 900000.00, 'completed', '250423_8', '2025-04-23 15:17:50', '2025-04-24 03:54:54'),
(9, 6, 950000.00, 'pending', '250423_9', '2025-04-23 15:25:44', '2025-04-23 15:25:44'),
(10, 6, 2000000.00, 'pending', '250423_10', '2025-04-23 15:29:18', '2025-04-23 15:29:19'),
(11, 6, 1600000.00, 'cancelled', '250423_11', '2025-04-23 15:34:39', '2025-04-24 03:54:47'),
(12, 6, 1100000.00, 'completed', '250423_12', '2025-04-23 15:45:41', '2025-04-24 03:49:37'),
(13, 6, 200000.00, 'completed', '250424_13', '2025-04-24 01:09:41', '2025-04-24 03:49:33'),
(14, 6, 1100000.00, 'failed', '250424_14', '2025-04-24 01:12:32', '2025-04-24 03:53:48'),
(15, 6, 200000.00, 'completed', '250424_15', '2025-04-24 03:32:34', '2025-04-24 03:49:29'),
(16, 6, 640000.00, 'completed', '250424_16', '2025-04-24 03:47:50', '2025-04-24 03:49:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
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
(16, 16, 5, 1, 640000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `discount` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grape` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abv` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `volume` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '750ml',
  `description` text COLLATE utf8mb4_unicode_ci,
  `rating` decimal(3,1) DEFAULT '0.0',
  `reviews` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
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
(10, 'GT001', 'Hộp qu&agrave; rượu vang &Yacute;', 9, 2500000.00, NULL, '', 8, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQhV6e9ne-3jhNzyNNk8k3G8W6Q4Yl7DfAYsw&s', '', 'Hộp qu&agrave;', 'San Marzano', 'Vang &Yacute; (Italy)', '', '750ml', 'Hộp qu&agrave; sang trọng gồm 2 chai vang &Yacute; v&agrave; ly pha l&ecirc;.', 4.7, 5, '2025-04-17 02:43:25', '2025-04-24 01:57:29'),
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
-- Cấu trúc bảng cho bảng `promotions`
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
-- Đang đổ dữ liệu cho bảng `promotions`
--

INSERT INTO `promotions` (`id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `created_at`) VALUES
(1, 5, 20.00, '2025-04-01 00:00:00', '2025-04-30 23:59:59', '2025-04-17 02:43:25'),
(2, 6, 20.00, '2025-04-01 00:00:00', '2025-04-30 23:59:59', '2025-04-17 02:43:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `phone`, `address`, `password`, `is_admin`, `created_at`, `updated_at`) VALUES
(2, 'Trần Thị Bình', 'binhthitran', 'tranthibinh@example.com', '0912345678', 'Số 45 Nguyễn Thị Minh Khai, Quận 3, TP HCM', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-04-07 14:22:45', '2025-04-24 02:28:11'),
(3, 'Lê Văn Cường', 'levancuong', 'levancuong@example.com', '0939876543', '78 Lê Duẩn, Quận Hai Bà Trưng, Hà Nội', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-04-06 08:55:12', '2025-04-09 15:23:53'),
(4, 'Phạm Thị Dung', 'phamthidung', 'phamthidung@example.com', '0965432198', 'Lô B2-15, KĐT Mỹ Đình, Nam Từ Liêm, Hà Nội', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-04-05 16:30:40', '2025-04-09 15:23:53'),
(5, 'Admin System', 'admin', 'admin@cuahangdouong.vn', '0909123456', 'Trụ sở công ty, Quận 1, TP HCM', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-04-01 09:00:00', '2025-04-09 15:23:53'),
(6, 'Lê Hoàng', 'hoanglee', 'hoanggg322004@gmail.com', '1234567890', 'Hà Nội', '$2y$10$zbNEAjsAwNBAz2QlP5Y6fOPraCo.4DfFf7kvY3LK3ZkxWv7eiZu4.', 0, '2025-04-09 23:26:27', '2025-04-22 14:17:41'),
(7, 'Đạt', 'nguyendat', 'hoanggg12@gmail.com', '09876543211', 'bac ninh', '$2y$10$xcyemlUB6X/7O8h2QL9HxuijiJQQi7SyjQi5MbcSe0w83CdazfoJS', 0, '2025-04-10 07:34:56', '2025-04-10 00:34:56'),
(8, 'Hoàng', 'leehoang', 'leehoang@gmail.com', '0965768529', 'Hà Nội', '$2y$10$z3Qz7g7X9vX8y5Z1b2c3d.uK8y9vX7w6Z5b4c3d2e1f0g9h8i7j6k', 1, '2025-04-20 06:16:03', '2025-04-20 04:35:03'),
(9, 'nguyễn văn A', 'buinamtruong', 'bnt@gmail.com', '0987651223', 'Bắc Ninh', '$2y$10$MeYqKUg960cGn77sdKai0eSzln9t8AVjM.JQUNa68BCVpKq43zgl.', 1, '2025-04-20 11:40:23', '2025-04-20 04:41:14'),
(10, 'Nguyễn Văn A', 'nguyenvana', 'nva@gmail.com', '0912312365', 'Hải Phòng', '$2y$10$ZLYf1E1mLVqJeFFWUeIlp.26Lj.Tj19yeJW5YSEuUtlWwAI0Ni3KG', 0, '2025-04-20 22:46:30', '2025-04-20 15:46:30'),
(11, 'cuong', 'cuong', 'cuong@gmail.com', '129839819', 'bac ninh', '$2y$10$TLgBzd6OB63UiiTXO3FI3eE8CWGKRT/JgxzoGH24X3nnGpuf1CvO6', 0, '2025-04-23 09:46:57', '2025-04-23 02:46:57'),
(15, 'dat', 'dat', 'dat@gmail.com', '1231452523', 'ha noi', '$2y$10$FSOcQaD5E.OHFsIezYxh/.gY6kH8M6evytB9Ub6anZ/n54hoeLSt6', 0, '2025-04-24 09:25:50', '2025-04-24 02:25:50');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_product_name` (`name`),
  ADD KEY `idx_product_code` (`code`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT;

--
-- Các ràng buộc cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
