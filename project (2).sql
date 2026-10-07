-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2026 at 04:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `dormitories`
--

CREATE TABLE `dormitories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `price_unit` enum('เดือน','วัน') NOT NULL DEFAULT 'เดือน',
  `water_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `electricity_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `contact_link` varchar(500) DEFAULT '',
  `status` enum('รอการอนุมัติ','อนุมัติแล้ว','ถูกปฏิเสธ') NOT NULL DEFAULT 'รอการอนุมัติ',
  `view_count` int(11) NOT NULL DEFAULT 0,
  `reject_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dormitories`
--

INSERT INTO `dormitories` (`id`, `user_id`, `type_id`, `name`, `price`, `price_unit`, `water_rate`, `electricity_rate`, `contact_link`, `status`, `view_count`, `reject_reason`, `created_at`) VALUES
(1, 6, 1, 'ตรีเพลส', 3500, 'เดือน', 25.00, 7.00, 'https://www.facebook.com/share/1CJSQ1LvEY/', 'อนุมัติแล้ว', 6, NULL, '2026-09-08 13:27:45'),
(2, 6, 1, 'เปี่ยมชัย', 1500, 'เดือน', 25.00, 7.00, 'https://www.facebook.com/share/1CJSQ1LvEY/', 'อนุมัติแล้ว', 1, NULL, '2026-09-08 13:27:45'),
(3, 6, 3, 'Baan Thew Talay Aquamarine ', 18000, 'เดือน', 25.00, 7.00, 'https://www.facebook.com/share/1CJSQ1LvEY/', 'อนุมัติแล้ว', 1, NULL, '2026-09-08 13:27:45'),
(4, 6, 4, 'Issara Village', 25000, 'เดือน', 25.00, 7.00, 'https://www.facebook.com/share/1CJSQ1LvEY/', 'อนุมัติแล้ว', 5, NULL, '2026-09-08 13:27:45'),
(8, 7, 1, 'มณีมันตรา', 2000, 'เดือน', 20.00, 7.00, '0', 'อนุมัติแล้ว', 4, '', '2026-09-10 06:56:55'),
(9, 7, 2, 'พรนภา อพาร์ทเม้นท์', 4000, 'เดือน', 25.00, 8.00, 'https://www.facebook.com/share/1CJSQ1LvEY/', 'อนุมัติแล้ว', 6, '', '2026-09-10 07:18:11'),
(10, 7, 2, 'J-House Phetchaburi', 4500, 'เดือน', 25.00, 7.00, 'https://www.facebook.com/share/1CJSQ1LvEY/', 'อนุมัติแล้ว', 5, '', '2026-09-10 07:26:32'),
(11, 7, 3, 'APE CONDO', 5500, 'เดือน', 25.00, 8.00, 'https://www.facebook.com/share/1CJSQ1LvEY/', 'อนุมัติแล้ว', 5, '', '2026-09-10 07:41:36'),
(12, 7, 3, 'Royal Beach Condo', 25000, 'เดือน', 25.00, 8.00, 'https://www.facebook.com/share/1CJSQ1LvEY/', 'อนุมัติแล้ว', 3, '', '2026-09-10 07:46:50'),
(13, 7, 4, 'บ้านพักลุงโจ๊ย หาดเจ้าสำราญ', 1500, 'วัน', 0.00, 0.00, 'https://www.facebook.com/share/1CJSQ1LvEY/', 'อนุมัติแล้ว', 6, '', '2026-09-10 08:05:16'),
(14, 7, 4, 'มาลี หาดเจ้าสำราญ', 1500, 'วัน', 0.00, 0.00, 'https://www.facebook.com/share/1CJSQ1LvEY/', 'อนุมัติแล้ว', 24, '', '2026-09-10 08:07:16');

-- --------------------------------------------------------

--
-- Table structure for table `dorm_facilities`
--

CREATE TABLE `dorm_facilities` (
  `id` int(11) NOT NULL,
  `dorm_id` int(11) NOT NULL,
  `facility_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dorm_facilities`
--

INSERT INTO `dorm_facilities` (`id`, `dorm_id`, `facility_name`) VALUES
(15, 3, 'สระว่ายน้ำส่วนกลาง'),
(16, 3, 'ฟิตเนส / ห้องออกกำลังกาย'),
(17, 3, 'คลับเฮาส์'),
(18, 3, 'สวนพักผ่อนและพื้นที่สีเขียว'),
(19, 3, 'ที่จอดรถ'),
(20, 3, 'ระบบรักษาความปลอดภัย 24 ชั่วโมง'),
(21, 3, 'กล้องวงจรปิด (CCTV)'),
(22, 3, 'ลิฟต์ภายในอาคาร'),
(23, 3, 'พื้นที่ส่วนกลางสำหรับพักผ่อน'),
(24, 4, 'สระว่ายน้ำส่วนกลาง'),
(25, 4, 'ระบบรักษาความปลอดภัย 24 ชั่วโมง'),
(26, 4, 'CCTV'),
(27, 4, 'สวนพักผ่อน'),
(28, 4, 'ที่จอดรถ'),
(81, 9, 'สระว่ายน้ำส่วนกลาง'),
(82, 9, 'ระบบรักษาความปลอดภัย 24 ชั่วโมง'),
(83, 9, 'CCTV'),
(84, 9, 'สวนพักผ่อน'),
(85, 9, 'ที่จอดรถ'),
(86, 8, 'แอร์'),
(87, 8, 'พัดลม'),
(88, 8, 'WiFi ฟรี'),
(89, 8, 'สัญญาณดาวเทียม'),
(90, 8, 'CCTV 24 ชั่วโมง'),
(91, 8, 'เฟอร์นิเจอร์'),
(92, 8, 'ระบบสแกนลายนิ้วมือ'),
(114, 11, 'แอร์'),
(115, 11, 'เครื่องทำน้ำอุ่น'),
(116, 11, 'ตู้เย็น'),
(117, 11, 'TV'),
(118, 11, 'ไมโครเวฟ'),
(119, 11, 'เตียง'),
(120, 11, 'ตู้เสื้อผ้า'),
(121, 11, 'โซฟา'),
(122, 11, 'สระว่ายน้ำ'),
(123, 11, 'ที่จอดรถ'),
(124, 11, 'WiFi'),
(125, 11, 'ฟิตเนส'),
(126, 11, 'CCTV'),
(141, 12, 'สระว่ายน้ำ'),
(142, 12, 'ฟิตเนส'),
(143, 12, 'สนามเทนนิส'),
(144, 12, 'สนามบาสเกตบอล'),
(145, 12, 'ที่จอดรถ'),
(146, 12, 'CCTV'),
(147, 12, 'รปภ. 24 ชั่วโมง'),
(148, 13, 'บ้านพักติดทะเล'),
(149, 13, 'ที่จอดรถ'),
(150, 13, 'น้ำอุ่น'),
(151, 13, 'ผ้าเช็ดตัว'),
(152, 13, 'แอร์'),
(153, 14, 'Free WiFi'),
(154, 14, 'ติดทะเล'),
(155, 10, 'แอร์'),
(156, 10, 'พัดลม'),
(157, 10, 'WiFi ฟรี'),
(158, 10, 'สัญญาณดาวเทียม'),
(159, 10, 'CCTV 24 ชั่วโมง'),
(160, 10, 'เฟอร์นิเจอร์'),
(161, 10, 'ระบบสแกนลายนิ้วมือ'),
(162, 2, 'WiFi ฟรี'),
(163, 2, 'แอร์'),
(164, 2, 'เครื่องทำน้ำอุ่น'),
(165, 2, 'ทีวี'),
(166, 2, 'ตู้เย็น'),
(167, 2, 'เฟอร์นิเจอร์'),
(168, 2, 'ที่จอดรถ'),
(169, 2, 'CCTV'),
(170, 2, 'คีย์การ์ด'),
(171, 2, 'เครื่องซักผ้า'),
(172, 1, 'WiFi ฟรี'),
(173, 1, 'แอร์'),
(174, 1, 'เครื่องทำน้ำอุ่น'),
(175, 1, 'ทีวี'),
(176, 1, 'ตู้เย็น'),
(177, 1, 'เฟอร์นิเจอร์'),
(178, 1, 'ที่จอดรถ'),
(179, 1, 'CCTV'),
(180, 1, 'คีย์การ์ด'),
(181, 1, 'เครื่องซักผ้า');

-- --------------------------------------------------------

--
-- Table structure for table `dorm_images`
--

CREATE TABLE `dorm_images` (
  `id` int(11) NOT NULL,
  `dorm_id` int(11) NOT NULL,
  `image_path` varchar(300) NOT NULL,
  `is_main` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dorm_images`
--

INSERT INTO `dorm_images` (`id`, `dorm_id`, `image_path`, `is_main`) VALUES
(10, 3, 'documents/824772988/BTTA.jpg', 1),
(11, 4, 'documents/291042022/1772016499_main_IssaraVillage.jpg', 1),
(57, 9, 'documents/750478386/1789024691_main________________.jpg', 1),
(58, 8, 'documents/1740124941/1789023415_main____________________________.jpg', 1),
(59, 8, 'documents/1740124941/1789023415_0____________________________2.jpg', 0),
(66, 11, 'documents/1336968445/1789026096_main_APE.jpg', 1),
(71, 12, 'documents/1300586238/1789026410_main_________________________.jpg', 1),
(72, 12, 'documents/1300586238/1789026410_0_________________________2.jpg', 0),
(73, 12, 'documents/1300586238/1789026410_1_________________________1.jpg', 0),
(74, 13, 'documents/91596252/1789027516_main________________.jpg', 1),
(75, 14, 'documents/358711403/1789027636_main_____________.jpg', 1),
(76, 10, 'documents/275420498/1789025192_main_j_house.jpg', 1),
(77, 10, 'documents/275420498/1789025192_0_jhouse1.jpg', 0),
(78, 2, 'documents/1715267376/1789032672_main__________________________________.jpg', 1),
(79, 2, 'documents/1715267376/1789032672_0__________________________________2.jpg', 0),
(80, 2, 'documents/1715267376/1789032672_1_____________________________________3.jpg', 0),
(81, 1, 'documents/2052064074/1772178970_main_treeplace.jpg', 1),
(82, 1, 'documents/2052064074/1772179161_0_treeplace8.jpg', 0),
(83, 1, 'documents/2052064074/1772179161_1_treeplace7.jpg', 0),
(84, 1, 'documents/2052064074/1772179161_2_treeplace6.jpg', 0),
(85, 1, 'documents/2052064074/1772179161_3_treeplace5.jpg', 0),
(86, 1, 'documents/2052064074/1772179161_4_treeplace4.jpg', 0),
(87, 1, 'documents/2052064074/1772179161_5_treeplace3.jpg', 0),
(88, 1, 'documents/2052064074/1772179161_6_treeplace2.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `dorm_locations`
--

CREATE TABLE `dorm_locations` (
  `id` int(11) NOT NULL,
  `dorm_id` int(11) NOT NULL,
  `address` varchar(500) DEFAULT NULL,
  `area` varchar(200) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dorm_locations`
--

INSERT INTO `dorm_locations` (`id`, `dorm_id`, `address`, `area`, `latitude`, `longitude`) VALUES
(1, 1, '225/1 หมู่ 6 ต.บ้านหม้อ อ.เมือง จ.เพชรบุรี 76000', 'ตลาดเพชรไพบูลย์,โรงพยาบาลมหาชัยเพชรรัชต์,Big c ', 13.09321194, 99.93610924),
(2, 2, ' 59 ตำบล โพไร่หวาน เมือง เพชรบุรี 76000', '59/4 หมู่ 3 ซอยข้างวัดเพรียง ถนนลาดโพธิ์ ต.โพไร่หวาน อ.เมืองเพชรบุรี จ.เพชรบุรี', 13.07399974, 99.97483893),
(3, 3, 'ที่อยู่: 779 ถนนเพชรเกษม ตำบลชะอำ อำเภอชะอำ จังหวัดเพชรบุรี 76120', 'หาดชะอำ,Makro / Big C ชะอำ,Palm Hills Golf Club,หัวหิน', 12.75800000, 99.96600000),
(4, 4, '819 ถนนมุ่งทะเล ตำบลชะอำ อำเภอชะอำ จังหวัดเพชรบุรี 76120', 'ใกล้หาดชะอำ,Regent Beach,Family Mart ชะอำ,สนามบินหัวหิน', 12.74011000, 99.96305000),
(8, 8, '162 หมู่ 4 ต.โพไร่หวาน อ.เมืองเพชรบุรี จ.เพชรบุรี 76000', 'มหาวิทยาลัยราชภัฏเพชรบุรี', 13.07159164, 99.96847688),
(9, 9, 'ต.ต้นมะม่วง อ.เมืองเพชรบุรี จ.เพชรบุรี 76000', 'ตลาดเพชรไพบูลย์,โรงพยาบาลมหาชัยเพชรรัชต์,Big c ', 13.10104877, 99.93604736),
(10, 10, 'ต.ท่าราบ อ.เมืองเพชรบุรี', 'ตลาดพรีเมียร์,โรงพยาบาลกรุงเทพ,Big c ', 13.08732480, 99.94983272),
(11, 11, '144/32 ต.โพไร่หวาน/พื้นที่ใกล้ ต.นาวุ้ง อ.เมืองเพชรบุรี จ.เพชรบุรี 76000', 'มหาวิทยาลัยราชภัฏเพชรบุรี', 13.06618575, 99.97913307),
(12, 12, 'ต.หาดเจ้าสำราญ อ.เมืองเพชรบุรี จ.เพชรบุรี 76100', 'หาดเจ้าสำราญ', 12.99857918, 100.05953777),
(13, 13, 'หาดเจ้าสำราญ จ.เพชรบุรี', 'หาดเจ้าสำราญ', 13.01591336, 100.07317803),
(14, 14, 'ซอยเทศบาล ต.หาดเจ้าสำราญ อ.เมืองเพชรบุรี จ.เพชรบุรี', 'หาดเจ้าสำราญ', 13.01536004, 100.07338182);

-- --------------------------------------------------------

--
-- Table structure for table `dorm_types`
--

CREATE TABLE `dorm_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dorm_types`
--

INSERT INTO `dorm_types` (`id`, `type_name`) VALUES
(1, 'หอพัก'),
(2, 'อพาร์ทเม้นท์'),
(3, 'คอนโด'),
(4, 'บ้านพัก');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dorm_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `dorm_id`, `message`, `is_read`, `created_at`) VALUES
(1, 7, 5, 'ที่พัก \"ตรีเพลส\" ของคุณได้รับการอนุมัติแล้ว', 1, '2026-09-09 08:43:56'),
(2, 7, 6, 'ที่พัก \"ตรีเพลส2\" ของคุณถูกปฏิเสธ เหตุผล: ไม่ตรง', 1, '2026-09-09 08:48:38'),
(3, 7, 7, 'ที่พัก \"หอพักเปี่ยมชัย\" ของคุณได้รับการอนุมัติแล้ว', 1, '2026-09-10 06:57:13'),
(4, 7, 8, 'ที่พัก \"หอพักมณีมันตรา\" ของคุณได้รับการอนุมัติแล้ว', 1, '2026-09-10 06:57:16'),
(5, 7, 9, 'ที่พัก \"พรนภา อพาร์ทเม้นท์\" ของคุณได้รับการอนุมัติแล้ว', 1, '2026-09-10 07:26:54'),
(6, 7, 10, 'ที่พัก \"J-House Phetchaburi\" ของคุณได้รับการอนุมัติแล้ว', 1, '2026-09-10 07:26:57'),
(7, 7, 11, 'ที่พัก \"APE CONDO\" ของคุณได้รับการอนุมัติแล้ว', 1, '2026-09-10 08:07:47'),
(8, 7, 12, 'ที่พัก \"Royal Beach Condo\" ของคุณได้รับการอนุมัติแล้ว', 1, '2026-09-10 08:08:32'),
(9, 7, 13, 'ที่พัก \"บ้านพักลุงโจ๊ย หาดเจ้าสำราญ\" ของคุณได้รับการอนุมัติแล้ว', 1, '2026-09-10 08:08:35'),
(10, 7, 14, 'ที่พัก \"มาลี หาดเจ้าสำราญ\" ของคุณได้รับการอนุมัติแล้ว', 1, '2026-09-10 08:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `photo` varchar(100) DEFAULT NULL,
  `role` enum('admin','owner') NOT NULL DEFAULT 'owner',
  `phone` varchar(50) DEFAULT NULL,
  `reset_otp` varchar(6) DEFAULT NULL,
  `reset_otp_expire` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expire` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `photo`, `role`, `phone`, `reset_otp`, `reset_otp_expire`, `reset_token`, `reset_token_expire`) VALUES
(6, 'Nattawut008', '003nat300@gmail.com', '$2y$10$myJok/SzZv0Gd8iN3gHlzukoR0isV9dy/JwTl7.2O2bbpt4PZ5p4.', 'เป็ดแบร่.png', 'owner', '0981789044', '940180', '2026-09-03 01:01:44', NULL, NULL),
(7, 'Nattawut007', '123456@gmail.com', '$2y$10$B5XDzZaGvweaLeDcXWRJIeptxAbzOCbVhn1I1.XJ1zd0KAQEBxhte', 'เป็ดง่วง.png', 'owner', NULL, NULL, NULL, NULL, NULL),
(8, 'admin', 'support@dormhub.in.th', '$2y$10$AbfH11w1UpVKdyH1uPFK3.chhFlWxTRYzdKk11XDtfusa37rzket2', 'เป็ดขำ.png', 'admin', NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dormitories`
--
ALTER TABLE `dormitories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dormitories_user` (`user_id`),
  ADD KEY `fk_dormitories_type` (`type_id`);

--
-- Indexes for table `dorm_facilities`
--
ALTER TABLE `dorm_facilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_facilities_dorm` (`dorm_id`);

--
-- Indexes for table `dorm_images`
--
ALTER TABLE `dorm_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_images_dorm` (`dorm_id`);

--
-- Indexes for table `dorm_locations`
--
ALTER TABLE `dorm_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_locations_dorm` (`dorm_id`);

--
-- Indexes for table `dorm_types`
--
ALTER TABLE `dorm_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dormitories`
--
ALTER TABLE `dormitories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `dorm_facilities`
--
ALTER TABLE `dorm_facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT for table `dorm_images`
--
ALTER TABLE `dorm_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `dorm_locations`
--
ALTER TABLE `dorm_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `dorm_types`
--
ALTER TABLE `dorm_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dormitories`
--
ALTER TABLE `dormitories`
  ADD CONSTRAINT `fk_dormitories_type` FOREIGN KEY (`type_id`) REFERENCES `dorm_types` (`id`),
  ADD CONSTRAINT `fk_dormitories_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dorm_facilities`
--
ALTER TABLE `dorm_facilities`
  ADD CONSTRAINT `fk_facilities_dorm` FOREIGN KEY (`dorm_id`) REFERENCES `dormitories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dorm_images`
--
ALTER TABLE `dorm_images`
  ADD CONSTRAINT `fk_images_dorm` FOREIGN KEY (`dorm_id`) REFERENCES `dormitories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dorm_locations`
--
ALTER TABLE `dorm_locations`
  ADD CONSTRAINT `fk_locations_dorm` FOREIGN KEY (`dorm_id`) REFERENCES `dormitories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
