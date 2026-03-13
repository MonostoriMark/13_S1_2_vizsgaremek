-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 11, 2026 at 01:14 PM
-- Server version: 8.0.42
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotelflow_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint UNSIGNED NOT NULL,
  `users_id` bigint UNSIGNED NOT NULL,
  `hotels_id` bigint UNSIGNED NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `totalPrice` int NOT NULL,
  `checkInToken` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkInstatus` enum('checkedOut','checkedIn') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkInTime` timestamp NULL DEFAULT NULL,
  `checkOutTime` timestamp NULL DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','finished') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `users_id`, `hotels_id`, `startDate`, `endDate`, `totalPrice`, `checkInToken`, `checkInstatus`, `checkInTime`, `checkOutTime`, `status`, `createdAt`) VALUES
(1, 1, 21, '2026-03-19', '2026-02-27', 716, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:46'),
(2, 1, 36, '2026-01-28', '2026-03-05', 261, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:46'),
(3, 1, 17, '2026-02-15', '2026-03-05', 1410, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:46'),
(4, 2, 16, '2026-02-13', '2026-02-28', 800, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:46'),
(5, 2, 32, '2026-03-17', '2026-03-08', 343, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:46'),
(6, 2, 34, '2026-01-31', '2026-02-27', 873, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:46'),
(7, 3, 14, '2026-03-04', '2026-03-02', 790, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:46'),
(8, 4, 24, '2026-03-13', '2026-02-26', 1600, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:46'),
(9, 4, 26, '2026-01-26', '2026-03-04', 358, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:46'),
(10, 4, 14, '2026-03-01', '2026-03-05', 56, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:46'),
(11, 5, 17, '2026-03-11', '2026-03-10', 726, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:46'),
(12, 6, 4, '2026-02-27', '2026-03-05', 1792, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:46'),
(13, 7, 24, '2026-01-28', '2026-02-26', 482, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(14, 7, 24, '2026-02-04', '2026-02-28', 1205, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(15, 7, 33, '2026-03-24', '2026-03-06', 428, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(16, 8, 6, '2026-02-18', '2026-03-07', 708, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(17, 9, 25, '2026-03-04', '2026-03-02', 940, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(18, 9, 34, '2026-03-01', '2026-02-25', 248, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(19, 9, 6, '2026-02-10', '2026-03-08', 1025, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(20, 10, 39, '2026-02-19', '2026-03-04', 740, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(21, 11, 27, '2026-03-14', '2026-03-01', 293, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(22, 11, 2, '2026-02-11', '2026-03-01', 2230, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(23, 12, 13, '2026-01-25', '2026-02-28', 418, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(24, 12, 4, '2026-03-03', '2026-03-05', 232, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(25, 12, 25, '2026-03-06', '2026-03-08', 705, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(26, 13, 12, '2026-03-01', '2026-03-01', 612, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(27, 13, 18, '2026-03-14', '2026-03-09', 213, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(28, 14, 36, '2026-02-07', '2026-02-27', 904, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(29, 14, 21, '2026-02-18', '2026-03-07', 1136, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(30, 14, 16, '2026-03-13', '2026-02-28', 1152, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(31, 15, 40, '2026-02-13', '2026-02-27', 1810, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(32, 15, 37, '2026-01-29', '2026-03-08', 304, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(33, 15, 19, '2026-02-28', '2026-03-09', 1260, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(34, 16, 35, '2026-02-26', '2026-02-27', 272, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(35, 17, 21, '2026-01-27', '2026-03-03', 1428, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(36, 17, 33, '2026-02-08', '2026-03-04', 77, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(37, 18, 41, '2026-02-08', '2026-03-03', 1287, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(38, 18, 34, '2026-01-25', '2026-03-09', 680, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(39, 19, 4, '2026-03-10', '2026-02-25', 1344, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(40, 20, 5, '2026-02-24', '2026-03-08', 1255, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(41, 21, 5, '2026-02-05', '2026-03-01', 195, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(42, 21, 22, '2026-03-12', '2026-03-04', 1595, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(43, 22, 36, '2026-01-31', '2026-03-04', 261, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(44, 22, 35, '2026-02-15', '2026-03-06', 1374, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(45, 23, 25, '2026-02-14', '2026-03-01', 858, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(46, 24, 35, '2026-02-21', '2026-02-28', 776, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(47, 24, 39, '2026-02-03', '2026-03-07', 555, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(48, 24, 6, '2026-01-30', '2026-03-05', 305, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(49, 25, 20, '2026-03-11', '2026-03-09', 219, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(50, 25, 40, '2026-02-06', '2026-03-02', 366, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(51, 25, 7, '2026-02-02', '2026-02-27', 138, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(52, 26, 32, '2026-02-08', '2026-03-04', 695, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(53, 26, 17, '2026-03-16', '2026-03-01', 634, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(54, 27, 8, '2026-03-08', '2026-03-07', 276, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(55, 27, 26, '2026-02-21', '2026-03-02', 792, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(56, 27, 28, '2026-02-08', '2026-03-03', 872, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(57, 28, 16, '2026-02-10', '2026-02-25', 1256, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(58, 28, 15, '2026-03-16', '2026-02-26', 580, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(59, 29, 34, '2026-03-19', '2026-03-07', 510, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(60, 30, 3, '2026-03-10', '2026-02-26', 237, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(61, 31, 5, '2026-02-08', '2026-03-01', 1004, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(62, 31, 43, '2026-03-23', '2026-03-02', 1448, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(63, 32, 27, '2026-03-13', '2026-03-02', 324, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(64, 32, 11, '2026-03-08', '2026-03-06', 1952, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(65, 32, 25, '2026-02-14', '2026-03-07', 951, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(66, 33, 26, '2026-03-13', '2026-02-27', 579, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(67, 33, 32, '2026-01-31', '2026-02-27', 1372, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(68, 33, 18, '2026-01-31', '2026-02-26', 1191, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(69, 34, 33, '2026-03-23', '2026-02-28', 1712, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(70, 34, 39, '2026-01-31', '2026-03-03', 652, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(71, 34, 22, '2026-01-30', '2026-03-05', 564, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(72, 35, 14, '2026-02-21', '2026-02-26', 344, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(73, 35, 23, '2026-02-20', '2026-03-10', 338, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(74, 36, 3, '2026-02-20', '2026-03-03', 248, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(75, 37, 30, '2026-03-15', '2026-03-08', 241, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(76, 37, 14, '2026-02-17', '2026-02-27', 474, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(77, 38, 14, '2026-01-29', '2026-03-02', 474, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(78, 38, 22, '2026-01-27', '2026-02-25', 1595, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(79, 38, 29, '2026-03-15', '2026-03-03', 436, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(80, 39, 32, '2026-03-24', '2026-03-07', 343, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(81, 40, 7, '2026-02-08', '2026-03-06', 645, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(82, 40, 41, '2026-03-04', '2026-03-08', 650, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(83, 41, 40, '2026-03-21', '2026-02-26', 177, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(84, 42, 18, '2026-02-07', '2026-02-28', 284, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(85, 42, 43, '2026-03-02', '2026-03-08', 84, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(86, 43, 35, '2026-01-30', '2026-03-01', 388, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(87, 43, 6, '2026-02-09', '2026-03-04', 915, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(88, 44, 23, '2026-02-10', '2026-03-01', 1045, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(89, 45, 31, '2026-03-23', '2026-03-09', 884, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(90, 45, 20, '2026-02-07', '2026-02-27', 1048, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(91, 45, 8, '2026-01-25', '2026-03-09', 276, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(92, 46, 39, '2026-02-11', '2026-03-06', 55, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(93, 46, 19, '2026-01-26', '2026-03-03', 723, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(94, 46, 41, '2026-02-08', '2026-02-28', 855, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(95, 47, 34, '2026-02-17', '2026-03-03', 1140, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(96, 48, 14, '2026-02-21', '2026-03-07', 112, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(97, 48, 32, '2026-01-29', '2026-02-25', 293, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(98, 48, 41, '2026-02-24', '2026-02-26', 201, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(99, 49, 2, '2026-02-15', '2026-03-09', 1625, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(100, 49, 30, '2026-01-30', '2026-03-06', 1000, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(102, 50, 25, '2026-03-22', '2026-03-04', 928, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(103, 50, 36, '2026-03-22', '2026-03-09', 156, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(104, 50, 14, '2026-03-02', '2026-03-04', 642, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(105, 51, 41, '2026-02-27', '2026-03-10', 1716, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(106, 52, 32, '2026-03-07', '2026-02-27', 204, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(107, 53, 39, '2026-03-23', '2026-03-10', 326, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(108, 53, 10, '2026-03-14', '2026-02-28', 1176, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(109, 54, 35, '2026-02-03', '2026-03-04', 272, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(110, 54, 16, '2026-02-01', '2026-02-28', 628, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(111, 55, 30, '2026-02-20', '2026-03-05', 1000, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(112, 55, 35, '2026-02-12', '2026-03-07', 1796, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(113, 56, 30, '2026-02-02', '2026-03-07', 1460, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(114, 56, 24, '2026-01-30', '2026-03-03', 1200, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(115, 56, 37, '2026-03-15', '2026-02-28', 192, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(116, 57, 24, '2026-03-22', '2026-03-06', 2435, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(117, 58, 35, '2026-01-30', '2026-03-08', 428, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(118, 58, 23, '2026-03-13', '2026-03-02', 1425, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(119, 59, 20, '2026-02-11', '2026-03-03', 164, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(120, 60, 19, '2026-03-17', '2026-02-25', 2100, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(121, 61, 21, '2026-03-16', '2026-03-09', 537, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(122, 61, 33, '2026-01-25', '2026-02-28', 600, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(123, 61, 38, '2026-02-12', '2026-03-09', 513, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(124, 62, 26, '2026-02-09', '2026-03-09', 335, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(125, 62, 23, '2026-01-28', '2026-02-25', 1690, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(126, 62, 6, '2026-02-10', '2026-03-02', 336, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(127, 63, 14, '2026-02-19', '2026-03-02', 1376, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(128, 63, 27, '2026-02-18', '2026-03-09', 740, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(129, 64, 8, '2026-02-01', '2026-03-05', 1305, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(130, 64, 11, '2026-03-21', '2026-03-06', 2410, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(131, 65, 3, '2026-02-01', '2026-03-10', 576, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(132, 65, 15, '2026-03-13', '2026-02-27', 348, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(133, 65, 33, '2026-03-18', '2026-03-03', 305, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(134, 66, 23, '2026-03-09', '2026-02-26', 1156, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(135, 66, 16, '2026-02-03', '2026-02-28', 1440, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(136, 67, 5, '2026-02-08', '2026-03-03', 405, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(137, 67, 11, '2026-02-23', '2026-02-27', 286, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(138, 68, 41, '2026-02-07', '2026-03-03', 194, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(139, 68, 29, '2026-03-13', '2026-03-07', 482, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(140, 68, 14, '2026-02-25', '2026-02-26', 790, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(141, 69, 26, '2026-02-18', '2026-02-28', 364, NULL, NULL, NULL, NULL, 'confirmed', '2026-02-24 07:16:47'),
(142, 69, 42, '2026-02-20', '2026-02-28', 286, NULL, NULL, NULL, NULL, 'cancelled', '2026-02-24 07:16:47'),
(143, 70, 4, '2026-02-10', '2026-02-27', 562, NULL, NULL, NULL, NULL, 'pending', '2026-02-24 07:16:47'),
(146, 76, 46, '2026-03-11', '2026-03-12', 245, '2WanL9S9gjYc5Px6', NULL, NULL, NULL, 'confirmed', '2026-03-11 13:07:48');

-- --------------------------------------------------------

--
-- Table structure for table `bookingsrelation`
--

CREATE TABLE `bookingsrelation` (
  `booking_id` bigint UNSIGNED NOT NULL,
  `rooms_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookingsrelation`
--

INSERT INTO `bookingsrelation` (`booking_id`, `rooms_id`) VALUES
(99, 4),
(99, 5),
(22, 6),
(22, 7),
(74, 8),
(60, 9),
(131, 9),
(131, 10),
(24, 13),
(39, 13),
(12, 14),
(39, 14),
(143, 14),
(12, 15),
(24, 16),
(40, 17),
(61, 17),
(40, 18),
(41, 18),
(61, 18),
(136, 20),
(16, 22),
(126, 22),
(48, 23),
(87, 23),
(126, 23),
(19, 24),
(48, 24),
(87, 24),
(81, 27),
(51, 28),
(81, 28),
(51, 29),
(54, 30),
(91, 30),
(129, 30),
(129, 31),
(108, 37),
(108, 38),
(130, 41),
(64, 42),
(137, 42),
(64, 43),
(130, 43),
(26, 46),
(23, 51),
(23, 52),
(10, 55),
(72, 55),
(96, 55),
(104, 55),
(127, 55),
(72, 56),
(127, 56),
(7, 57),
(76, 57),
(77, 57),
(104, 57),
(140, 57),
(58, 59),
(132, 59),
(4, 61),
(30, 61),
(57, 61),
(110, 61),
(135, 61),
(30, 62),
(135, 62),
(57, 63),
(110, 63),
(3, 64),
(11, 64),
(3, 65),
(53, 65),
(11, 66),
(53, 66),
(68, 70),
(68, 71),
(27, 72),
(84, 72),
(120, 73),
(33, 74),
(93, 74),
(120, 74),
(33, 75),
(49, 76),
(90, 76),
(119, 76),
(49, 77),
(90, 78),
(29, 81),
(35, 81),
(35, 82),
(1, 83),
(121, 83),
(29, 84),
(42, 85),
(71, 85),
(78, 85),
(42, 86),
(78, 86),
(71, 87),
(118, 88),
(73, 89),
(125, 89),
(73, 90),
(88, 90),
(125, 90),
(134, 91),
(118, 92),
(13, 93),
(14, 93),
(8, 94),
(114, 94),
(116, 94),
(116, 95),
(8, 96),
(13, 96),
(14, 96),
(114, 96),
(17, 97),
(25, 97),
(65, 97),
(17, 99),
(25, 99),
(45, 99),
(65, 100),
(102, 100),
(45, 101),
(55, 102),
(141, 102),
(9, 103),
(55, 104),
(66, 105),
(124, 106),
(21, 108),
(63, 108),
(21, 109),
(128, 109),
(56, 112),
(56, 114),
(139, 115),
(79, 116),
(79, 118),
(113, 119),
(75, 120),
(100, 121),
(111, 121),
(89, 124),
(5, 125),
(52, 125),
(67, 125),
(80, 125),
(5, 126),
(67, 126),
(80, 126),
(97, 126),
(106, 126),
(97, 127),
(15, 128),
(69, 128),
(122, 128),
(15, 129),
(69, 129),
(133, 129),
(36, 130),
(133, 130),
(95, 131),
(6, 132),
(38, 132),
(59, 132),
(18, 133),
(6, 135),
(18, 135),
(44, 136),
(44, 137),
(117, 137),
(46, 138),
(86, 138),
(112, 138),
(112, 139),
(34, 140),
(109, 140),
(28, 142),
(103, 143),
(2, 144),
(43, 144),
(28, 145),
(115, 146),
(32, 148),
(123, 152),
(20, 153),
(47, 153),
(70, 154),
(107, 154),
(70, 155),
(92, 155),
(107, 155),
(50, 156),
(83, 157),
(31, 158),
(31, 159),
(37, 160),
(82, 160),
(105, 160),
(37, 161),
(98, 161),
(105, 161),
(82, 162),
(94, 162),
(138, 162),
(94, 163),
(142, 165),
(62, 169),
(85, 169),
(62, 172),
(146, 176);

-- --------------------------------------------------------

--
-- Table structure for table `booking_invoice_details`
--

CREATE TABLE `booking_invoice_details` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `customer_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'private',
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_invoice_details`
--

INSERT INTO `booking_invoice_details` (`id`, `booking_id`, `customer_type`, `full_name`, `email`, `company_name`, `tax_number`, `country`, `city`, `postal_code`, `address_line`, `note`, `created_at`, `updated_at`) VALUES
(3, 146, 'private', 'Szabó Máté', 'szabo.mate@diak.szbi-pg.hu', NULL, NULL, 'Magyarország', 'Kiskunfélegyháza', '6100', 'Alma utca 2', NULL, '2026-03-11 13:07:48', '2026-03-11 13:07:48');

-- --------------------------------------------------------

--
-- Table structure for table `booking_payments`
--

CREATE TABLE `booking_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `method` enum('bank_transfer','card') COLLATE utf8mb4_unicode_ci DEFAULT 'bank_transfer',
  `status` enum('pending','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `confirmed_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_payments`
--

INSERT INTO `booking_payments` (`id`, `booking_id`, `method`, `status`, `confirmed_at`, `confirmed_by_user_id`, `created_at`, `updated_at`) VALUES
(3, 146, 'bank_transfer', 'paid', '2026-03-11 13:08:36', 77, '2026-03-11 13:07:48', '2026-03-11 13:08:36');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-random_hotels_10', 'a:3:{s:6:\"hotels\";a:10:{i:0;a:10:{s:2:\"id\";i:18;s:4:\"name\";s:27:\"Wiegand, Casper and Goyette\";s:8:\"location\";s:9:\"Ferryberg\";s:4:\"type\";s:9:\"apartment\";s:10:\"starRating\";i:5;s:11:\"description\";s:49:\"Doloribus ea laudantium sunt facere quis aperiam.\";s:11:\"cover_image\";s:41:\"https://picsum.photos/800/600?random=4838\";s:15:\"price_per_night\";s:5:\"71.00\";s:19:\"availability_status\";s:9:\"available\";s:4:\"tags\";a:0:{}}i:1;a:10:{s:2:\"id\";i:42;s:4:\"name\";s:9:\"Berge LLC\";s:8:\"location\";s:9:\"Hauckside\";s:4:\"type\";s:5:\"hotel\";s:10:\"starRating\";i:3;s:11:\"description\";s:28:\"Autem quas ea dolorem ea et.\";s:11:\"cover_image\";s:41:\"https://picsum.photos/800/600?random=4512\";s:15:\"price_per_night\";s:5:\"62.00\";s:19:\"availability_status\";s:9:\"available\";s:4:\"tags\";a:0:{}}i:2;a:10:{s:2:\"id\";i:5;s:4:\"name\";s:13:\"Wilkinson LLC\";s:8:\"location\";s:11:\"North Brown\";s:4:\"type\";s:5:\"other\";s:10:\"starRating\";i:5;s:11:\"description\";s:57:\"Veniam asperiores rerum qui rerum perferendis non itaque.\";s:11:\"cover_image\";s:41:\"https://picsum.photos/800/600?random=8019\";s:15:\"price_per_night\";s:5:\"65.00\";s:19:\"availability_status\";s:9:\"available\";s:4:\"tags\";a:0:{}}i:3;a:10:{s:2:\"id\";i:17;s:4:\"name\";s:30:\"VonRueden, Kerluke and Wuckert\";s:8:\"location\";s:14:\"North Vanmouth\";s:4:\"type\";s:5:\"hotel\";s:10:\"starRating\";i:1;s:11:\"description\";s:42:\"Ducimus et rerum cum ex occaecati ducimus.\";s:11:\"cover_image\";s:41:\"https://picsum.photos/800/600?random=5147\";s:15:\"price_per_night\";s:5:\"57.00\";s:19:\"availability_status\";s:9:\"available\";s:4:\"tags\";a:0:{}}i:4;a:10:{s:2:\"id\";i:31;s:4:\"name\";s:14:\"Parisian-Swift\";s:8:\"location\";s:12:\"North Moises\";s:4:\"type\";s:5:\"villa\";s:10:\"starRating\";i:5;s:11:\"description\";s:39:\"Debitis qui inventore eum eos corrupti.\";s:11:\"cover_image\";s:41:\"https://picsum.photos/800/600?random=4105\";s:15:\"price_per_night\";s:6:\"221.00\";s:19:\"availability_status\";s:9:\"available\";s:4:\"tags\";a:0:{}}i:5;a:10:{s:2:\"id\";i:45;s:4:\"name\";s:14:\"Hotel Platamon\";s:8:\"location\";s:10:\"Platamonas\";s:4:\"type\";s:5:\"hotel\";s:10:\"starRating\";i:4;s:11:\"description\";s:16:\"Ez egy leírás.\";s:11:\"cover_image\";N;s:15:\"price_per_night\";s:6:\"120.00\";s:19:\"availability_status\";s:9:\"available\";s:4:\"tags\";a:0:{}}i:6;a:10:{s:2:\"id\";i:33;s:4:\"name\";s:8:\"Veum LLC\";s:8:\"location\";s:10:\"North Mara\";s:4:\"type\";s:5:\"other\";s:10:\"starRating\";i:5;s:11:\"description\";s:52:\"Eum nulla ipsam dolorem hic ut explicabo recusandae.\";s:11:\"cover_image\";s:41:\"https://picsum.photos/800/600?random=2508\";s:15:\"price_per_night\";s:5:\"77.00\";s:19:\"availability_status\";s:9:\"available\";s:4:\"tags\";a:0:{}}i:7;a:10:{s:2:\"id\";i:22;s:4:\"name\";s:25:\"Walsh, Renner and Volkman\";s:8:\"location\";s:10:\"Hammesfort\";s:4:\"type\";s:5:\"hotel\";s:10:\"starRating\";i:1;s:11:\"description\";s:37:\"Ullam tenetur magni non dolore quasi.\";s:11:\"cover_image\";s:41:\"https://picsum.photos/800/600?random=3951\";s:15:\"price_per_night\";s:5:\"59.00\";s:19:\"availability_status\";s:9:\"available\";s:4:\"tags\";a:0:{}}i:8;a:10:{s:2:\"id\";i:11;s:4:\"name\";s:26:\"Raynor, Towne and Ondricka\";s:8:\"location\";s:13:\"East Orieview\";s:4:\"type\";s:5:\"other\";s:10:\"starRating\";i:4;s:11:\"description\";s:51:\"Alias repellat ea eum sed consequatur ut quia nisi.\";s:11:\"cover_image\";s:41:\"https://picsum.photos/800/600?random=4799\";s:15:\"price_per_night\";s:6:\"124.00\";s:19:\"availability_status\";s:9:\"available\";s:4:\"tags\";a:0:{}}i:9;a:10:{s:2:\"id\";i:10;s:4:\"name\";s:17:\"Daugherty-Gaylord\";s:8:\"location\";s:17:\"East Cassidyhaven\";s:4:\"type\";s:5:\"hotel\";s:10:\"starRating\";i:4;s:11:\"description\";s:38:\"Necessitatibus ipsam est minima dolor.\";s:11:\"cover_image\";s:41:\"https://picsum.photos/800/600?random=3801\";s:15:\"price_per_night\";s:5:\"71.00\";s:19:\"availability_status\";s:9:\"available\";s:4:\"tags\";a:0:{}}}s:5:\"count\";i:10;s:8:\"has_more\";b:0;}', 1773234486);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hotels_id` bigint UNSIGNED DEFAULT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `name`, `hotels_id`, `token`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Eszköz', 46, '0039624a0d519b6278241ff1eb09806e561475dba9cad8bb16f9862111488afc', 0, '2026-03-11 13:10:58', '2026-03-11 13:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` bigint UNSIGNED NOT NULL,
  `idNumber` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dateOfBirth` date NOT NULL,
  `bookings_id` bigint UNSIGNED NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `idNumber`, `name`, `dateOfBirth`, `bookings_id`, `createdAt`) VALUES
(1, 'YH608470', 'Willard Cummerata', '2001-06-23', 1, '2026-02-24 07:16:46'),
(2, 'LM599813', 'Yoshiko Heidenreich', '1970-11-02', 1, '2026-02-24 07:16:46'),
(3, 'MH612144', 'Berniece Fay', '1979-07-29', 2, '2026-02-24 07:16:46'),
(4, 'VB203532', 'Mr. Marlon Runolfsson', '1977-07-14', 3, '2026-02-24 07:16:46'),
(5, 'SH685288', 'Stanford Littel', '1976-08-20', 3, '2026-02-24 07:16:46'),
(6, 'UQ433434', 'Dr. Schuyler Schaefer III', '1999-03-20', 4, '2026-02-24 07:16:46'),
(7, 'YY732901', 'Arianna Watsica', '2007-05-13', 5, '2026-02-24 07:16:46'),
(8, 'PW712320', 'Mr. Doris Toy MD', '1999-02-10', 5, '2026-02-24 07:16:46'),
(9, 'BP369011', 'Mrs. Deanna Von III', '1973-01-09', 5, '2026-02-24 07:16:46'),
(10, 'DG564082', 'Ms. Mariela Steuber', '1986-01-02', 5, '2026-02-24 07:16:46'),
(11, 'HY977724', 'Keshaun Franecki V', '1978-02-26', 6, '2026-02-24 07:16:46'),
(12, 'XB273209', 'Lauretta Schumm', '1980-09-23', 6, '2026-02-24 07:16:46'),
(13, 'YI480386', 'Edwin Rath', '2001-04-08', 7, '2026-02-24 07:16:46'),
(14, 'MH218650', 'Prof. Brook Bernier', '2006-11-09', 7, '2026-02-24 07:16:46'),
(15, 'ZI716229', 'Charlotte Haley', '1989-01-08', 8, '2026-02-24 07:16:46'),
(16, 'IO755287', 'Annetta Hermiston', '1994-04-13', 8, '2026-02-24 07:16:46'),
(17, 'PE015184', 'Eudora Runolfsson', '1993-07-11', 9, '2026-02-24 07:16:46'),
(18, 'IZ635955', 'Mylene Crooks', '1990-12-10', 10, '2026-02-24 07:16:46'),
(19, 'ZW115025', 'Ms. Mariana Morar Jr.', '1970-04-30', 10, '2026-02-24 07:16:46'),
(20, 'QL484518', 'Virgie Jast V', '1995-11-19', 11, '2026-02-24 07:16:46'),
(21, 'CB158006', 'Santina Boyle DDS', '1982-02-02', 11, '2026-02-24 07:16:46'),
(22, 'VX655723', 'Mariah Boyle', '1984-07-28', 12, '2026-02-24 07:16:47'),
(23, 'SL621500', 'Kelly Dicki', '1994-01-06', 13, '2026-02-24 07:16:47'),
(24, 'WG337725', 'Juliet Gulgowski', '1998-11-21', 14, '2026-02-24 07:16:47'),
(25, 'TP646612', 'Ali Russel', '1973-03-17', 15, '2026-02-24 07:16:47'),
(26, 'GW422792', 'Keshaun Zulauf DDS', '1995-05-10', 15, '2026-02-24 07:16:47'),
(27, 'GL989810', 'Genevieve Turner', '1978-09-06', 15, '2026-02-24 07:16:47'),
(28, 'QN688828', 'Miss Gracie Brown DVM', '1992-06-28', 16, '2026-02-24 07:16:47'),
(29, 'BS604124', 'Forest Kling', '1980-05-27', 16, '2026-02-24 07:16:47'),
(30, 'FE239242', 'Kiera Kilback', '1991-09-03', 16, '2026-02-24 07:16:47'),
(31, 'KI583664', 'Prof. Burnice Douglas', '1977-04-15', 17, '2026-02-24 07:16:47'),
(32, 'ZM247193', 'Kennedi Harber Sr.', '1983-04-17', 17, '2026-02-24 07:16:47'),
(33, 'EE247538', 'Kiel McKenzie', '1999-11-15', 18, '2026-02-24 07:16:47'),
(34, 'JB126508', 'Alva Cummings DDS', '1980-04-13', 18, '2026-02-24 07:16:47'),
(35, 'TK627631', 'Elouise Hintz', '1974-01-17', 19, '2026-02-24 07:16:47'),
(36, 'WU277693', 'Dr. Dessie Lowe', '1989-03-24', 19, '2026-02-24 07:16:47'),
(37, 'BE341343', 'Antonia Jacobson', '1990-11-10', 20, '2026-02-24 07:16:47'),
(38, 'BU272002', 'Zachariah Pouros DDS', '1982-07-04', 20, '2026-02-24 07:16:47'),
(39, 'AI248917', 'Conner Rowe MD', '1979-02-08', 20, '2026-02-24 07:16:47'),
(40, 'YI584109', 'Katelyn Romaguera', '1973-07-14', 21, '2026-02-24 07:16:47'),
(41, 'VL287456', 'Prof. Gene Nienow', '1994-01-08', 22, '2026-02-24 07:16:47'),
(42, 'UD958224', 'Scottie Williamson', '1995-07-19', 22, '2026-02-24 07:16:47'),
(43, 'ZA472379', 'Eric Mayer', '2007-12-12', 22, '2026-02-24 07:16:47'),
(44, 'SW434792', 'Alba D\'Amore', '1977-04-18', 22, '2026-02-24 07:16:47'),
(45, 'WG759982', 'Wyman Anderson Jr.', '1976-07-17', 23, '2026-02-24 07:16:47'),
(46, 'CA734166', 'Dr. Brent Barrows PhD', '1978-08-15', 23, '2026-02-24 07:16:47'),
(47, 'DF121554', 'Dr. Kali Corwin', '1999-02-01', 23, '2026-02-24 07:16:47'),
(48, 'FZ834824', 'Mekhi Abbott V', '2002-09-29', 23, '2026-02-24 07:16:47'),
(49, 'RG746175', 'Kennith Bednar', '2007-12-28', 23, '2026-02-24 07:16:47'),
(50, 'AQ634563', 'Buford Sauer', '1976-11-14', 23, '2026-02-24 07:16:47'),
(51, 'RF293413', 'Aditya Kuphal', '1974-11-01', 24, '2026-02-24 07:16:47'),
(52, 'KM208738', 'Dr. Brannon Kuvalis MD', '1970-11-04', 24, '2026-02-24 07:16:47'),
(53, 'ZH716896', 'Alexanne Spinka III', '1990-11-25', 24, '2026-02-24 07:16:47'),
(54, 'VL607301', 'Prof. Caleb Balistreri I', '1989-03-06', 25, '2026-02-24 07:16:47'),
(55, 'CG252917', 'Amely Brakus IV', '1978-07-27', 25, '2026-02-24 07:16:47'),
(56, 'XM161646', 'Gennaro Parisian', '1976-02-20', 26, '2026-02-24 07:16:47'),
(57, 'ZO129742', 'Marge Veum', '1998-06-21', 27, '2026-02-24 07:16:47'),
(58, 'ZW617706', 'Ms. Mina Ebert V', '1984-05-15', 27, '2026-02-24 07:16:47'),
(59, 'BM773984', 'William Nikolaus', '1980-12-23', 28, '2026-02-24 07:16:47'),
(60, 'YH971872', 'Walter Moen', '2007-04-11', 29, '2026-02-24 07:16:47'),
(61, 'DL397376', 'Dylan Gulgowski', '1993-03-21', 30, '2026-02-24 07:16:47'),
(62, 'KK826717', 'Mr. Jacques Macejkovic', '1985-01-16', 30, '2026-02-24 07:16:47'),
(63, 'QR497384', 'Dr. Lourdes Bosco V', '1986-01-21', 30, '2026-02-24 07:16:47'),
(64, 'QD318256', 'Destany Cruickshank III', '1984-02-23', 30, '2026-02-24 07:16:47'),
(65, 'ZH611399', 'Prof. Aurore Bins DDS', '1981-11-21', 30, '2026-02-24 07:16:47'),
(66, 'MB238739', 'Prof. Erling Rohan V', '1973-07-15', 30, '2026-02-24 07:16:47'),
(67, 'DJ348031', 'Margarette Hintz MD', '2001-03-01', 31, '2026-02-24 07:16:47'),
(68, 'UW712088', 'Marlene Prohaska', '2006-04-22', 31, '2026-02-24 07:16:47'),
(69, 'RB715198', 'Arielle Prohaska', '1987-02-23', 32, '2026-02-24 07:16:47'),
(70, 'UC844093', 'Letha Lindgren', '1975-03-13', 33, '2026-02-24 07:16:47'),
(71, 'AI517798', 'Leora Ryan', '1994-07-31', 33, '2026-02-24 07:16:47'),
(72, 'LJ965319', 'Dina Steuber', '1994-05-30', 33, '2026-02-24 07:16:47'),
(73, 'QV915697', 'Miss Shanon Kuhic', '1999-11-06', 33, '2026-02-24 07:16:47'),
(74, 'QI444718', 'Pietro Quitzon', '1990-02-13', 34, '2026-02-24 07:16:47'),
(75, 'SH927672', 'Dewitt Lowe PhD', '1974-09-28', 35, '2026-02-24 07:16:47'),
(76, 'AO478022', 'Bernice Feest', '1980-07-17', 35, '2026-02-24 07:16:47'),
(77, 'GK553859', 'Prof. Janelle Tromp I', '1988-04-03', 35, '2026-02-24 07:16:47'),
(78, 'QE829513', 'Sarina Farrell', '2002-09-24', 36, '2026-02-24 07:16:47'),
(79, 'QF370452', 'Jeffry Leffler', '1972-08-11', 37, '2026-02-24 07:16:47'),
(80, 'DV334216', 'Frederik Prosacco', '2007-09-30', 37, '2026-02-24 07:16:47'),
(81, 'WB212309', 'Dr. Cristal Hermiston III', '1992-08-09', 37, '2026-02-24 07:16:47'),
(82, 'DU267887', 'Liam Mills', '1977-12-10', 38, '2026-02-24 07:16:47'),
(83, 'NM617596', 'Dr. Austyn Jast', '1972-04-08', 39, '2026-02-24 07:16:47'),
(84, 'VB302128', 'Ms. Hailie Will III', '2002-02-05', 39, '2026-02-24 07:16:47'),
(85, 'GA160258', 'Miss Abagail Keeling III', '1983-04-28', 39, '2026-02-24 07:16:47'),
(86, 'XI923180', 'Jadyn Armstrong V', '2000-06-05', 40, '2026-02-24 07:16:47'),
(87, 'NZ888253', 'Dr. Cletus Spencer III', '2003-03-29', 41, '2026-02-24 07:16:47'),
(88, 'ID924953', 'Flavio Abbott DDS', '2001-08-13', 41, '2026-02-24 07:16:47'),
(89, 'FT833319', 'Kaela Swaniawski', '1984-02-03', 41, '2026-02-24 07:16:47'),
(90, 'HA175139', 'Jerel Nader', '1971-11-04', 41, '2026-02-24 07:16:47'),
(91, 'PH603460', 'Ruthie Leffler', '1999-09-18', 42, '2026-02-24 07:16:47'),
(92, 'ZP023814', 'Myrtie Wuckert', '1973-05-21', 43, '2026-02-24 07:16:47'),
(93, 'ZA096321', 'Mr. Johnathan Rodriguez', '1978-09-16', 44, '2026-02-24 07:16:47'),
(94, 'EN067306', 'Raleigh Thiel', '1995-02-21', 44, '2026-02-24 07:16:47'),
(95, 'XD813355', 'Kira Rutherford', '1999-03-24', 44, '2026-02-24 07:16:47'),
(96, 'SO632283', 'Jarret Wyman', '1972-01-08', 44, '2026-02-24 07:16:47'),
(97, 'FX660728', 'Sabina Grant', '1983-08-01', 45, '2026-02-24 07:16:47'),
(98, 'ZA794780', 'Antoinette Koelpin', '1989-12-02', 46, '2026-02-24 07:16:47'),
(99, 'QM046304', 'Trycia Hirthe', '1991-06-26', 46, '2026-02-24 07:16:47'),
(100, 'BE764438', 'Hilma Senger', '1991-06-23', 47, '2026-02-24 07:16:47'),
(101, 'PO379655', 'Cyrus Corkery', '1975-10-01', 47, '2026-02-24 07:16:47'),
(102, 'BN372582', 'Loma Huels', '1986-02-02', 47, '2026-02-24 07:16:47'),
(103, 'PU606515', 'Prof. Brandi Stracke', '1993-03-31', 47, '2026-02-24 07:16:47'),
(104, 'PR555082', 'Prof. Uriah Runte', '1982-05-16', 48, '2026-02-24 07:16:47'),
(105, 'EM300925', 'Abdul Watsica', '1984-01-24', 48, '2026-02-24 07:16:47'),
(106, 'HV403263', 'Marlee O\'Keefe', '1984-08-05', 49, '2026-02-24 07:16:47'),
(107, 'BB270256', 'Willow Marks', '1985-06-29', 50, '2026-02-24 07:16:47'),
(108, 'EG607356', 'Miss Myriam Pollich III', '1980-02-26', 50, '2026-02-24 07:16:47'),
(109, 'VB788272', 'Sabina Nikolaus IV', '1992-11-10', 50, '2026-02-24 07:16:47'),
(110, 'YC464925', 'Esther Kuphal V', '2007-10-05', 50, '2026-02-24 07:16:47'),
(111, 'LK938045', 'Leanne Jones', '1989-11-30', 51, '2026-02-24 07:16:47'),
(112, 'VX937960', 'Leland Bauch I', '1981-08-30', 51, '2026-02-24 07:16:47'),
(113, 'LD686468', 'Eldred Tillman', '1993-10-25', 51, '2026-02-24 07:16:47'),
(114, 'NP707878', 'Maye Kertzmann', '1999-05-04', 52, '2026-02-24 07:16:47'),
(115, 'KW851336', 'Dr. Zita Lockman', '1981-01-30', 52, '2026-02-24 07:16:47'),
(116, 'BW688667', 'Dallas Zulauf', '1980-09-02', 53, '2026-02-24 07:16:47'),
(117, 'BM337627', 'Miss Lauryn Kemmer', '1982-11-13', 53, '2026-02-24 07:16:47'),
(118, 'WF155863', 'Dorthy Olson', '1982-04-18', 54, '2026-02-24 07:16:47'),
(119, 'FM132580', 'Melvina Mraz', '1972-04-26', 55, '2026-02-24 07:16:47'),
(120, 'LM068797', 'Ms. Olga Kessler', '1972-03-14', 55, '2026-02-24 07:16:47'),
(121, 'HV024897', 'Yasmin Murray', '1977-07-03', 56, '2026-02-24 07:16:47'),
(122, 'YP200020', 'Prof. Isaiah Sporer DDS', '1988-02-25', 56, '2026-02-24 07:16:47'),
(123, 'SZ615114', 'Mr. Kendall Koss I', '1972-03-19', 56, '2026-02-24 07:16:47'),
(124, 'PZ805387', 'Gerardo Becker', '2005-02-04', 57, '2026-02-24 07:16:47'),
(125, 'AN796145', 'Arnold Sauer', '1971-09-05', 57, '2026-02-24 07:16:47'),
(126, 'BG222911', 'Tyrique Brakus', '1980-11-21', 57, '2026-02-24 07:16:47'),
(127, 'YV570143', 'Dr. Don Thompson', '1988-04-03', 57, '2026-02-24 07:16:47'),
(128, 'QL353678', 'Miss Kara Dooley Jr.', '1980-11-05', 57, '2026-02-24 07:16:47'),
(129, 'WL205000', 'Katelyn Wilkinson', '1998-12-12', 58, '2026-02-24 07:16:47'),
(130, 'KY030589', 'Wayne Walker PhD', '1976-11-23', 58, '2026-02-24 07:16:47'),
(131, 'DP755296', 'Jaden Jenkins', '2006-01-09', 58, '2026-02-24 07:16:47'),
(132, 'JO936470', 'Carlie Cormier', '1983-12-14', 59, '2026-02-24 07:16:47'),
(133, 'QA811304', 'Hilbert Braun', '1989-08-16', 60, '2026-02-24 07:16:47'),
(134, 'UE422144', 'Dr. Jaron Langosh', '1981-09-13', 60, '2026-02-24 07:16:47'),
(135, 'WU252853', 'Bernadine Sauer', '1988-04-16', 60, '2026-02-24 07:16:47'),
(136, 'OJ502722', 'Prof. Dovie Von', '1974-12-06', 61, '2026-02-24 07:16:47'),
(137, 'TJ877785', 'Miss Gretchen Turner', '1992-11-02', 61, '2026-02-24 07:16:47'),
(138, 'PE887074', 'Tevin Cronin', '2004-04-27', 62, '2026-02-24 07:16:47'),
(139, 'ZF133850', 'Talon Lindgren', '1986-08-01', 62, '2026-02-24 07:16:47'),
(140, 'OU937556', 'Hulda Denesik', '1996-11-12', 62, '2026-02-24 07:16:47'),
(141, 'YC921547', 'Ms. Sincere Lehner', '1986-09-14', 63, '2026-02-24 07:16:47'),
(142, 'XP939113', 'Olin Mertz V', '1975-08-24', 63, '2026-02-24 07:16:47'),
(143, 'TD065590', 'Magnus Cruickshank', '2004-12-06', 64, '2026-02-24 07:16:47'),
(144, 'CM145437', 'Dr. Roxanne Ebert Sr.', '1981-09-17', 64, '2026-02-24 07:16:47'),
(145, 'SC554889', 'Guy Lemke', '1979-04-29', 64, '2026-02-24 07:16:47'),
(146, 'ZQ982310', 'Hudson Champlin', '1970-05-08', 64, '2026-02-24 07:16:47'),
(147, 'GG119525', 'Elmo Robel', '1985-01-07', 65, '2026-02-24 07:16:47'),
(148, 'IN069626', 'Dr. Wilbert Olson II', '1977-11-27', 65, '2026-02-24 07:16:47'),
(149, 'QK437010', 'Edgar Cremin V', '1986-11-18', 65, '2026-02-24 07:16:47'),
(150, 'LG475719', 'Prof. Amani Ebert Jr.', '1995-06-28', 66, '2026-02-24 07:16:47'),
(151, 'QB493548', 'Carolyne Von', '1983-04-16', 66, '2026-02-24 07:16:47'),
(152, 'DF609032', 'Dulce Wunsch', '2002-03-31', 66, '2026-02-24 07:16:47'),
(153, 'FH315383', 'Sheila Klocko', '1988-11-17', 67, '2026-02-24 07:16:47'),
(154, 'AY686098', 'Maxwell Jacobi', '1997-06-11', 67, '2026-02-24 07:16:47'),
(155, 'NY623626', 'Prof. Natalie Koelpin I', '2000-05-23', 67, '2026-02-24 07:16:47'),
(156, 'LR902847', 'Dr. Everette Beer', '1976-12-30', 67, '2026-02-24 07:16:47'),
(157, 'SD109225', 'Prof. Audreanne Bosco', '1993-11-18', 67, '2026-02-24 07:16:47'),
(158, 'NB338400', 'Enoch Hammes', '1979-09-19', 67, '2026-02-24 07:16:47'),
(159, 'XH643431', 'Angus Ward', '2008-02-19', 67, '2026-02-24 07:16:47'),
(160, 'LA602650', 'Mr. Don Von', '1999-08-05', 68, '2026-02-24 07:16:47'),
(161, 'GY541232', 'Dr. Lawson Morar I', '2000-03-17', 68, '2026-02-24 07:16:47'),
(162, 'ZF699030', 'Jovany Streich', '1990-04-02', 68, '2026-02-24 07:16:47'),
(163, 'PX352219', 'Dr. Alan Ortiz I', '2001-05-18', 68, '2026-02-24 07:16:47'),
(164, 'FY261950', 'Grayson Steuber', '1971-06-17', 69, '2026-02-24 07:16:47'),
(165, 'KX622642', 'Augustus Auer I', '1997-09-11', 69, '2026-02-24 07:16:47'),
(166, 'ST832916', 'Dr. Tatyana Luettgen V', '1972-09-17', 69, '2026-02-24 07:16:47'),
(167, 'WJ161663', 'Della Dare', '1994-06-19', 69, '2026-02-24 07:16:47'),
(168, 'ME222242', 'Prof. Nikolas Moore', '1986-01-20', 70, '2026-02-24 07:16:47'),
(169, 'QN179929', 'Chloe Raynor', '1982-11-10', 70, '2026-02-24 07:16:47'),
(170, 'KJ357962', 'Mia Aufderhar', '1974-12-19', 71, '2026-02-24 07:16:47'),
(171, 'FN044058', 'Genoveva Skiles', '1995-06-19', 71, '2026-02-24 07:16:47'),
(172, 'KG696230', 'Ila Greenholt', '1995-08-24', 72, '2026-02-24 07:16:47'),
(173, 'HP223285', 'Mr. Clyde Breitenberg', '1984-06-01', 72, '2026-02-24 07:16:47'),
(174, 'VZ423255', 'Prof. Kendrick Gleichner Sr.', '1988-01-04', 72, '2026-02-24 07:16:47'),
(175, 'EF001896', 'Jack Lueilwitz', '1998-03-03', 72, '2026-02-24 07:16:47'),
(176, 'RB781935', 'Jennifer Smitham DDS', '1974-10-26', 72, '2026-02-24 07:16:47'),
(177, 'ZL166472', 'Prof. Tia Gislason IV', '1993-05-06', 73, '2026-02-24 07:16:47'),
(178, 'GN867557', 'Grayce Krajcik MD', '2006-09-15', 74, '2026-02-24 07:16:47'),
(179, 'VG598498', 'Ronaldo Bartoletti', '1976-07-07', 74, '2026-02-24 07:16:47'),
(180, 'MM623558', 'Ronny Langosh', '1985-04-03', 75, '2026-02-24 07:16:47'),
(181, 'WX848799', 'Lillian Kuhn', '2000-01-10', 75, '2026-02-24 07:16:47'),
(182, 'HH300003', 'Lyric Skiles V', '1999-12-28', 75, '2026-02-24 07:16:47'),
(183, 'HJ760366', 'Dr. Jalon Vandervort III', '1974-12-01', 76, '2026-02-24 07:16:47'),
(184, 'ZY859369', 'Abraham Schuppe', '1998-12-21', 76, '2026-02-24 07:16:47'),
(185, 'CQ665672', 'Maudie Hintz', '1981-06-28', 77, '2026-02-24 07:16:47'),
(186, 'IU100160', 'Mr. Zackery Hamill I', '1989-02-03', 77, '2026-02-24 07:16:47'),
(187, 'DV753216', 'Curt Wiza PhD', '1992-01-29', 78, '2026-02-24 07:16:47'),
(188, 'IB186264', 'Prof. Trey Goldner', '2003-08-06', 78, '2026-02-24 07:16:47'),
(189, 'VM950413', 'Roscoe McDermott', '1975-11-12', 79, '2026-02-24 07:16:47'),
(190, 'TM844026', 'Ellen Greenholt', '1983-08-24', 80, '2026-02-24 07:16:47'),
(191, 'DM355221', 'Warren Ryan', '1973-03-28', 80, '2026-02-24 07:16:47'),
(192, 'KR549822', 'Candida Hettinger', '1998-11-10', 80, '2026-02-24 07:16:47'),
(193, 'ES603166', 'Juliet Smitham', '1972-05-07', 80, '2026-02-24 07:16:47'),
(194, 'LM489901', 'Mr. Emanuel Leannon V', '1973-09-29', 81, '2026-02-24 07:16:47'),
(195, 'LK997306', 'Freddie Lindgren', '1970-09-20', 81, '2026-02-24 07:16:47'),
(196, 'JE064336', 'Mr. Napoleon Kihn PhD', '1992-05-03', 81, '2026-02-24 07:16:47'),
(197, 'EA840839', 'Earnestine Padberg', '1988-04-07', 82, '2026-02-24 07:16:47'),
(198, 'WQ871811', 'Darren Renner II', '1996-01-05', 83, '2026-02-24 07:16:47'),
(199, 'HP210271', 'Murray Wiza PhD', '1997-08-07', 84, '2026-02-24 07:16:47'),
(200, 'FK064302', 'Esperanza Hilpert', '1978-07-18', 84, '2026-02-24 07:16:47'),
(201, 'ON799106', 'Maci Purdy', '1975-02-26', 85, '2026-02-24 07:16:47'),
(202, 'FW907959', 'Miss Agnes Hayes III', '1985-02-21', 86, '2026-02-24 07:16:47'),
(203, 'RN106577', 'Mr. Rickey DuBuque V', '2007-01-20', 86, '2026-02-24 07:16:47'),
(204, 'LK307303', 'Miss Elisabeth Schuppe', '1976-05-17', 86, '2026-02-24 07:16:47'),
(205, 'EZ859342', 'Dr. Mariah Leuschke DDS', '1980-01-06', 87, '2026-02-24 07:16:47'),
(206, 'PQ386133', 'Delfina Blanda', '1983-08-10', 88, '2026-02-24 07:16:47'),
(207, 'OY209709', 'Adella Considine', '1991-12-06', 89, '2026-02-24 07:16:47'),
(208, 'CE714252', 'Lawson Runte', '1994-04-20', 90, '2026-02-24 07:16:47'),
(209, 'GV387852', 'Concepcion Rempel', '1989-01-30', 91, '2026-02-24 07:16:47'),
(210, 'RC228997', 'Percy White', '1998-11-20', 91, '2026-02-24 07:16:47'),
(211, 'XO677782', 'Carli Gusikowski', '1978-04-05', 92, '2026-02-24 07:16:47'),
(212, 'SL681006', 'Rocky Jakubowski', '1978-11-27', 93, '2026-02-24 07:16:47'),
(213, 'JZ506192', 'Adah Windler Jr.', '2003-01-28', 93, '2026-02-24 07:16:47'),
(214, 'TH013503', 'Kristy Watsica', '2002-05-29', 93, '2026-02-24 07:16:47'),
(215, 'SC394454', 'Octavia Douglas', '1987-04-23', 94, '2026-02-24 07:16:47'),
(216, 'HG574764', 'Velda Schroeder', '2000-11-12', 94, '2026-02-24 07:16:47'),
(217, 'IG941151', 'Mallory Schuster', '1991-01-03', 94, '2026-02-24 07:16:47'),
(218, 'TG828887', 'Dr. Linnea Zulauf', '1989-01-08', 94, '2026-02-24 07:16:47'),
(219, 'HI220463', 'Breana Harvey', '1998-11-07', 94, '2026-02-24 07:16:47'),
(220, 'JA428722', 'Loy Spinka', '1971-03-12', 94, '2026-02-24 07:16:47'),
(221, 'AO095068', 'Prof. Darien Kuvalis', '1993-10-09', 95, '2026-02-24 07:16:47'),
(222, 'IR843223', 'Valentine Swift', '2006-11-30', 95, '2026-02-24 07:16:47'),
(223, 'IE869684', 'Dr. Wilton Gleason', '1992-01-26', 95, '2026-02-24 07:16:47'),
(224, 'SY198997', 'Triston Heathcote', '1986-08-15', 96, '2026-02-24 07:16:47'),
(225, 'XQ798847', 'Paige Dicki', '2007-01-15', 97, '2026-02-24 07:16:47'),
(226, 'GW676338', 'Vinnie Emard II', '1994-05-14', 97, '2026-02-24 07:16:47'),
(227, 'LR498664', 'Ben Kilback DDS', '1996-09-06', 97, '2026-02-24 07:16:47'),
(228, 'DH722098', 'Gracie Rempel', '1974-03-02', 97, '2026-02-24 07:16:47'),
(229, 'AD833845', 'Stuart Anderson', '2000-11-21', 98, '2026-02-24 07:16:47'),
(230, 'HB237632', 'Prof. Breanna Kub', '1998-04-02', 99, '2026-02-24 07:16:47'),
(231, 'XY986219', 'Dr. Gino Cole I', '1996-03-07', 99, '2026-02-24 07:16:47'),
(232, 'GL298897', 'Tessie Legros', '1979-07-10', 100, '2026-02-24 07:16:47'),
(235, 'JO051434', 'Norval Bailey DDS', '1997-12-17', 102, '2026-02-24 07:16:47'),
(236, 'ZL827340', 'Karina Greenfelder', '2004-12-18', 103, '2026-02-24 07:16:47'),
(237, 'JO954952', 'Kaycee Murazik', '2007-09-17', 104, '2026-02-24 07:16:47'),
(238, 'XC286086', 'Prof. Annette Trantow IV', '1989-04-18', 105, '2026-02-24 07:16:47'),
(239, 'OP176133', 'Mireya Huels', '1998-05-26', 105, '2026-02-24 07:16:47'),
(240, 'AB136499', 'Dawn Adams', '1993-07-06', 105, '2026-02-24 07:16:47'),
(241, 'UI375451', 'Ms. Shany Wisozk III', '1979-01-14', 106, '2026-02-24 07:16:47'),
(242, 'SM068448', 'Valerie Kulas', '1995-11-03', 106, '2026-02-24 07:16:47'),
(243, 'LK104395', 'Ruth Wolf', '1977-01-25', 106, '2026-02-24 07:16:47'),
(244, 'ZK132749', 'Mrs. Sydnee Will', '1989-04-25', 106, '2026-02-24 07:16:47'),
(245, 'KS111821', 'Felipa Steuber', '1978-06-28', 107, '2026-02-24 07:16:47'),
(246, 'FZ290474', 'Bernardo Deckow', '1982-11-11', 107, '2026-02-24 07:16:47'),
(247, 'AM325350', 'Uriah Price DDS', '1977-10-04', 108, '2026-02-24 07:16:47'),
(248, 'AG487699', 'Jevon Rau', '1997-11-04', 108, '2026-02-24 07:16:47'),
(249, 'TB578562', 'Georgette Metz', '1991-11-28', 108, '2026-02-24 07:16:47'),
(250, 'MU698574', 'Prof. Santiago Wisozk', '2005-08-06', 109, '2026-02-24 07:16:47'),
(251, 'CF814301', 'Rick Casper PhD', '1994-03-18', 110, '2026-02-24 07:16:47'),
(252, 'AW867877', 'Dr. Sydnie Considine Sr.', '1993-11-11', 110, '2026-02-24 07:16:47'),
(253, 'QP776064', 'Billy Osinski', '1994-07-05', 110, '2026-02-24 07:16:47'),
(254, 'HG616919', 'Amari Spinka', '1990-05-03', 111, '2026-02-24 07:16:47'),
(255, 'JR159396', 'Dr. Holden Hintz DVM', '1975-02-11', 112, '2026-02-24 07:16:47'),
(256, 'VM394633', 'Rubye Nikolaus Sr.', '1982-07-28', 112, '2026-02-24 07:16:47'),
(257, 'UV343664', 'Jules Jacobi', '2000-09-19', 113, '2026-02-24 07:16:47'),
(258, 'FI548908', 'Bret Towne', '1973-03-12', 113, '2026-02-24 07:16:47'),
(259, 'AB871073', 'Brock Nienow', '2003-01-17', 114, '2026-02-24 07:16:47'),
(260, 'GC596419', 'Mallie McClure', '1980-12-17', 114, '2026-02-24 07:16:47'),
(261, 'EG390982', 'Mr. German Towne PhD', '1991-09-10', 114, '2026-02-24 07:16:47'),
(262, 'DS361064', 'Sigmund Leffler', '2001-05-28', 114, '2026-02-24 07:16:47'),
(263, 'QF637551', 'Prof. Cornelius Grady I', '1998-07-29', 115, '2026-02-24 07:16:47'),
(264, 'EP935532', 'Ms. Marta Kirlin PhD', '1978-09-06', 116, '2026-02-24 07:16:47'),
(265, 'ZS768975', 'Mr. Mac Nienow', '2004-05-01', 116, '2026-02-24 07:16:47'),
(266, 'JL519420', 'Prof. Keon Stehr II', '1999-08-07', 116, '2026-02-24 07:16:47'),
(267, 'EU314979', 'Arch Koelpin', '1999-04-26', 116, '2026-02-24 07:16:47'),
(268, 'MF152675', 'Sim Labadie', '1975-06-08', 117, '2026-02-24 07:16:47'),
(269, 'OR598406', 'Abraham Schuppe', '1985-09-09', 117, '2026-02-24 07:16:47'),
(270, 'LX540119', 'Prof. Minnie Johns DVM', '1996-01-08', 118, '2026-02-24 07:16:47'),
(271, 'KF821939', 'Kamren Smith', '1989-10-04', 118, '2026-02-24 07:16:47'),
(272, 'DD755819', 'Prof. Shanelle Langworth IV', '1974-03-04', 118, '2026-02-24 07:16:47'),
(273, 'ZO461123', 'Mr. Jeffery Ziemann', '1973-01-02', 119, '2026-02-24 07:16:47'),
(274, 'UZ437594', 'Jaleel King DDS', '1996-11-22', 120, '2026-02-24 07:16:47'),
(275, 'GU439870', 'Ms. Kitty Kihn V', '1987-11-12', 121, '2026-02-24 07:16:47'),
(276, 'EB074646', 'Tanner Daugherty', '1979-06-03', 121, '2026-02-24 07:16:47'),
(277, 'CL940149', 'Kareem Little DDS', '1982-09-25', 121, '2026-02-24 07:16:47'),
(278, 'WY433704', 'Verona Feest', '1976-06-01', 122, '2026-02-24 07:16:47'),
(279, 'NZ867236', 'Jerod Stokes', '1990-04-20', 122, '2026-02-24 07:16:47'),
(280, 'ER558089', 'Kallie Kihn', '1971-01-09', 123, '2026-02-24 07:16:47'),
(281, 'HV314477', 'Dr. Lottie Murazik', '2004-01-18', 124, '2026-02-24 07:16:47'),
(282, 'SB898768', 'Sim Medhurst', '1986-11-01', 124, '2026-02-24 07:16:47'),
(283, 'JZ751709', 'Agustin Durgan', '2003-01-17', 124, '2026-02-24 07:16:47'),
(284, 'RM316114', 'Titus Moen IV', '1985-07-31', 124, '2026-02-24 07:16:47'),
(285, 'UI473501', 'Ida Ratke', '1986-10-13', 125, '2026-02-24 07:16:47'),
(286, 'LX709022', 'Kayla Kirlin', '1986-07-25', 125, '2026-02-24 07:16:47'),
(287, 'GZ607535', 'Miss Helen Von I', '2007-12-06', 125, '2026-02-24 07:16:47'),
(288, 'JD358919', 'Angelina Mraz', '1996-11-29', 126, '2026-02-24 07:16:47'),
(289, 'UZ028661', 'Frederique Sawayn', '1992-04-02', 127, '2026-02-24 07:16:47'),
(290, 'BA888956', 'Blaise Heaney', '1989-06-19', 128, '2026-02-24 07:16:47'),
(291, 'XN275193', 'Sonia Kreiger Sr.', '1970-07-06', 129, '2026-02-24 07:16:47'),
(292, 'WA325845', 'Prof. Reuben Kohler', '1996-02-22', 129, '2026-02-24 07:16:47'),
(293, 'QC848368', 'Cade Goyette PhD', '2005-01-22', 130, '2026-02-24 07:16:47'),
(294, 'LE467917', 'Benedict Sporer', '1997-04-28', 130, '2026-02-24 07:16:47'),
(295, 'FN760795', 'Elisabeth Gorczany DDS', '1988-01-04', 130, '2026-02-24 07:16:47'),
(296, 'CT547361', 'Prof. Annie Connelly', '1975-01-30', 130, '2026-02-24 07:16:47'),
(297, 'EJ229340', 'Norbert Shields', '2003-04-11', 131, '2026-02-24 07:16:47'),
(298, 'ZX660353', 'Ramiro Cronin', '1995-01-28', 131, '2026-02-24 07:16:47'),
(299, 'OL061927', 'Meaghan Kling V', '1977-04-02', 132, '2026-02-24 07:16:47'),
(300, 'BC733642', 'Breanne Gulgowski', '1977-04-10', 133, '2026-02-24 07:16:47'),
(301, 'QV311625', 'Fay Daugherty PhD', '1971-09-12', 133, '2026-02-24 07:16:47'),
(302, 'JK107594', 'Sandrine Yundt', '1979-06-29', 133, '2026-02-24 07:16:47'),
(303, 'ZZ571699', 'Phyllis Haag PhD', '1970-03-10', 133, '2026-02-24 07:16:47'),
(304, 'XZ971693', 'Ignatius Barrows', '2007-12-19', 133, '2026-02-24 07:16:47'),
(305, 'KW688955', 'Lonny Braun', '1989-10-06', 134, '2026-02-24 07:16:47'),
(306, 'AE741040', 'Amie Kassulke', '1984-07-15', 135, '2026-02-24 07:16:47'),
(307, 'MG612993', 'Lavonne Marks', '1985-10-05', 135, '2026-02-24 07:16:47'),
(308, 'OS566461', 'Trystan Becker', '1991-10-18', 135, '2026-02-24 07:16:47'),
(309, 'CI109485', 'Emmanuel Howe', '1996-02-14', 135, '2026-02-24 07:16:47'),
(310, 'DX674584', 'Prof. Vince Wuckert Jr.', '1976-09-08', 135, '2026-02-24 07:16:47'),
(311, 'SY828428', 'Giovanni Kuhlman', '1997-12-18', 135, '2026-02-24 07:16:47'),
(312, 'DK594228', 'Mr. Armani Hyatt', '2001-10-08', 136, '2026-02-24 07:16:47'),
(313, 'JA336178', 'Aubrey Ondricka', '1986-07-23', 137, '2026-02-24 07:16:47'),
(314, 'GQ382465', 'Ernest Trantow', '1989-09-17', 137, '2026-02-24 07:16:47'),
(315, 'XZ692326', 'Vena Connelly', '1999-07-02', 138, '2026-02-24 07:16:47'),
(316, 'JS216491', 'Lesly Collier Jr.', '1981-04-10', 138, '2026-02-24 07:16:47'),
(317, 'EJ758722', 'Angie Stanton II', '1988-11-11', 139, '2026-02-24 07:16:47'),
(318, 'TX494991', 'Dr. Quinten Rath PhD', '1979-11-18', 139, '2026-02-24 07:16:47'),
(319, 'TR864525', 'Leta Prosacco', '1979-08-17', 140, '2026-02-24 07:16:47'),
(320, 'LT942410', 'Prof. Garnett Cremin', '1975-05-25', 140, '2026-02-24 07:16:47'),
(321, 'BI803967', 'Prof. Monserrate Koch', '1987-07-02', 141, '2026-02-24 07:16:47'),
(322, 'TH877491', 'Armando Watsica III', '1987-02-28', 142, '2026-02-24 07:16:47'),
(323, 'PM957346', 'Jordan McGlynn I', '1978-07-05', 143, '2026-02-24 07:16:47'),
(324, 'MH695900', 'Vallie Bergstrom', '1991-07-06', 143, '2026-02-24 07:16:47');

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('hotel','apartment','villa','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `starRating` int DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `tax_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `eu_tax_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `user_id`, `name`, `location`, `description`, `type`, `starRating`, `is_approved`, `tax_number`, `bank_account`, `eu_tax_number`, `cover_image`, `createdAt`) VALUES
(2, 51, 'Skiles, Hyatt and Runolfsdottir', 'South Chaimside', 'Asperiores quam illum est tempora doloremque beatae ex nostrum.', 'villa', 5, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(3, 51, 'Lubowitz, Waters and Gottlieb', 'Ahmedhaven', 'Omnis illum magnam laborum.', 'other', 4, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(4, 52, 'Thiel Group', 'New Freeda', 'Sed sequi eum voluptatibus animi odit.', 'other', 3, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(5, 53, 'Wilkinson LLC', 'North Brown', 'Veniam asperiores rerum qui rerum perferendis non itaque.', 'other', 5, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(6, 53, 'Volkman and Sons', 'North Rosa', 'Natus nisi hic ipsam nam autem nesciunt numquam.', 'villa', 1, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(7, 53, 'Gulgowski, Dach and Reichert', 'Sauerfurt', 'Et tempore dolor aliquid ut reprehenderit autem iusto.', 'apartment', 3, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(8, 54, 'Ernser and Sons', 'New Doris', 'Incidunt eaque alias quis ut est vel temporibus.', 'hotel', 3, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(9, 54, 'Rath, Langosh and Homenick', 'South Jay', 'Nihil eum aperiam ut ipsam blanditiis qui.', 'hotel', 1, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(10, 54, 'Daugherty-Gaylord', 'East Cassidyhaven', 'Necessitatibus ipsam est minima dolor.', 'hotel', 4, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(11, 55, 'Raynor, Towne and Ondricka', 'East Orieview', 'Alias repellat ea eum sed consequatur ut quia nisi.', 'other', 4, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(12, 56, 'Beer-Jakubowski', 'Lake Maya', 'Impedit exercitationem est consequatur id assumenda nulla quis.', 'other', 3, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(13, 56, 'Lehner Group', 'North Rigobertoview', 'Eum corporis minima adipisci quia.', 'hotel', 3, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(14, 56, 'Howell-Krajcik', 'Runolfsdottirland', 'Officia deleniti adipisci et.', 'hotel', 4, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(15, 57, 'Purdy Group', 'North Baylee', 'Quo labore temporibus tempora ducimus labore facilis libero modi.', 'apartment', 3, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(16, 58, 'Renner-Abbott', 'Prosaccoshire', 'Rem voluptas totam asperiores beatae aut ea dignissimos quae.', 'villa', 4, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(17, 58, 'VonRueden, Kerluke and Wuckert', 'North Vanmouth', 'Ducimus et rerum cum ex occaecati ducimus.', 'hotel', 1, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(18, 58, 'Wiegand, Casper and Goyette', 'Ferryberg', 'Doloribus ea laudantium sunt facere quis aperiam.', 'apartment', 5, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(19, 59, 'Volkman-Schowalter', 'Lakinport', 'Eaque quia culpa pariatur.', 'villa', 5, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(20, 60, 'Rau-Bartell', 'Volkmanborough', 'Odio in atque est labore ea.', 'hotel', 1, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(21, 61, 'Hill-Corwin', 'Madelynntown', 'Maxime libero adipisci ut et ut odio pariatur accusamus.', 'hotel', 2, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(22, 61, 'Walsh, Renner and Volkman', 'Hammesfort', 'Ullam tenetur magni non dolore quasi.', 'hotel', 1, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(23, 61, 'Pagac Ltd', 'Walterland', 'Et quod odio sit accusamus beatae.', 'other', 1, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(24, 62, 'Anderson-Schneider', 'Corneliusberg', 'Inventore ea itaque omnis vero qui.', 'villa', 1, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(25, 62, 'Hettinger-Gorczany', 'Hageneston', 'Qui sequi et esse qui aliquam quibusdam harum.', 'villa', 1, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(26, 63, 'Cole-Wunsch', 'Minniestad', 'Dolores enim et porro beatae.', 'villa', 5, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(27, 63, 'Nolan, Langworth and Cremin', 'Rueckerburgh', 'Ducimus suscipit aperiam blanditiis iusto optio.', 'apartment', 3, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(28, 63, 'Yost-Prosacco', 'West Ardellaborough', 'Nobis sequi in voluptas beatae assumenda provident cumque.', 'other', 5, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(29, 64, 'Kessler-Stanton', 'Benton', 'Eius quidem ullam quo est a minus voluptatum.', 'hotel', 3, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(30, 65, 'McDermott and Sons', 'Port Carolside', 'Quibusdam hic culpa sit consequatur.', 'hotel', 2, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(31, 65, 'Parisian-Swift', 'North Moises', 'Debitis qui inventore eum eos corrupti.', 'villa', 5, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(32, 65, 'Boyle-Steuber', 'Schowaltermouth', 'Voluptatum quas laudantium ab adipisci sit explicabo qui.', 'other', 2, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(33, 66, 'Veum LLC', 'North Mara', 'Eum nulla ipsam dolorem hic ut explicabo recusandae.', 'other', 5, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(34, 66, 'Willms-Roob', 'South Maxie', 'Autem dolores quae nam sunt aut quia ducimus.', 'apartment', 2, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(35, 66, 'Leuschke, Jenkins and Champlin', 'Hammesfurt', 'Fugit dolor voluptatum laborum quas deserunt praesentium sed.', 'other', 3, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(36, 67, 'Zieme-Swaniawski', 'Port Gaylordville', 'Qui quia quas cum.', 'villa', 4, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(37, 67, 'Zemlak and Sons', 'Port Doug', 'Reiciendis voluptatem possimus sunt sint.', 'hotel', 2, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(38, 67, 'Cassin-Hackett', 'Gerlachfort', 'Soluta qui sapiente laborum.', 'hotel', 1, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(39, 68, 'Dickens, Lynch and Ziemann', 'Goldnerbury', 'Quia aut quos quidem ut.', 'apartment', 5, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(40, 68, 'Howell Group', 'West Keelyfort', 'Voluptas quia ipsum enim debitis.', 'apartment', 4, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(41, 68, 'Hyatt Inc', 'South Hermanmouth', 'Voluptate iure vel et sequi enim.', 'apartment', 2, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(42, 69, 'Berge LLC', 'Hauckside', 'Autem quas ea dolorem ea et.', 'hotel', 3, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(43, 70, 'Dach, Dibbert and Rempel', 'Hilpertfort', 'Molestiae ducimus doloremque pariatur sed.', 'other', 2, 1, NULL, NULL, NULL, NULL, '2026-02-24 07:16:46'),
(46, 77, 'Hotel Budapest', 'Budapest', NULL, 'hotel', 4, 1, '12345678', '1177332202676502', 'EU12345678', NULL, '2026-03-11 13:04:08');

-- --------------------------------------------------------

--
-- Table structure for table `hoteltagrelation`
--

CREATE TABLE `hoteltagrelation` (
  `hotels_id` bigint UNSIGNED NOT NULL,
  `serviceTags_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` bigint UNSIGNED NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`id`, `url`) VALUES
(1, 'https://picsum.photos/800/600?random=7363'),
(2, 'https://picsum.photos/800/600?random=6994'),
(3, 'https://picsum.photos/800/600?random=82'),
(4, 'https://picsum.photos/800/600?random=24'),
(5, 'https://picsum.photos/800/600?random=626'),
(6, 'https://picsum.photos/800/600?random=5473'),
(7, 'https://picsum.photos/800/600?random=5804'),
(8, 'https://picsum.photos/800/600?random=1312'),
(9, 'https://picsum.photos/800/600?random=44'),
(10, 'https://picsum.photos/800/600?random=2066'),
(11, 'https://picsum.photos/800/600?random=6862'),
(12, 'https://picsum.photos/800/600?random=2346'),
(13, 'https://picsum.photos/800/600?random=8130'),
(14, 'https://picsum.photos/800/600?random=8524'),
(15, 'https://picsum.photos/800/600?random=8895'),
(16, 'https://picsum.photos/800/600?random=9656'),
(17, 'https://picsum.photos/800/600?random=8019'),
(18, 'https://picsum.photos/800/600?random=7658'),
(19, 'https://picsum.photos/800/600?random=8157'),
(20, 'https://picsum.photos/800/600?random=5956'),
(21, 'https://picsum.photos/800/600?random=5111'),
(22, 'https://picsum.photos/800/600?random=7643'),
(23, 'https://picsum.photos/800/600?random=1849'),
(24, 'https://picsum.photos/800/600?random=8918'),
(25, 'https://picsum.photos/800/600?random=6994'),
(26, 'https://picsum.photos/800/600?random=4291'),
(27, 'https://picsum.photos/800/600?random=9530'),
(28, 'https://picsum.photos/800/600?random=4592'),
(29, 'https://picsum.photos/800/600?random=9899'),
(30, 'https://picsum.photos/800/600?random=9756'),
(31, 'https://picsum.photos/800/600?random=1403'),
(32, 'https://picsum.photos/800/600?random=3107'),
(33, 'https://picsum.photos/800/600?random=3546'),
(34, 'https://picsum.photos/800/600?random=4578'),
(35, 'https://picsum.photos/800/600?random=1047'),
(36, 'https://picsum.photos/800/600?random=5228'),
(37, 'https://picsum.photos/800/600?random=5973'),
(38, 'https://picsum.photos/800/600?random=2286'),
(39, 'https://picsum.photos/800/600?random=3420'),
(40, 'https://picsum.photos/800/600?random=9260'),
(41, 'https://picsum.photos/800/600?random=3801'),
(42, 'https://picsum.photos/800/600?random=8715'),
(43, 'https://picsum.photos/800/600?random=4118'),
(44, 'https://picsum.photos/800/600?random=4799'),
(45, 'https://picsum.photos/800/600?random=2538'),
(46, 'https://picsum.photos/800/600?random=751'),
(47, 'https://picsum.photos/800/600?random=488'),
(48, 'https://picsum.photos/800/600?random=6359'),
(49, 'https://picsum.photos/800/600?random=5828'),
(50, 'https://picsum.photos/800/600?random=8727'),
(51, 'https://picsum.photos/800/600?random=7335'),
(52, 'https://picsum.photos/800/600?random=9196'),
(53, 'https://picsum.photos/800/600?random=4071'),
(54, 'https://picsum.photos/800/600?random=6274'),
(55, 'https://picsum.photos/800/600?random=9606'),
(56, 'https://picsum.photos/800/600?random=2082'),
(57, 'https://picsum.photos/800/600?random=4852'),
(58, 'https://picsum.photos/800/600?random=1723'),
(59, 'https://picsum.photos/800/600?random=2655'),
(60, 'https://picsum.photos/800/600?random=4356'),
(61, 'https://picsum.photos/800/600?random=1919'),
(62, 'https://picsum.photos/800/600?random=1654'),
(63, 'https://picsum.photos/800/600?random=4770'),
(64, 'https://picsum.photos/800/600?random=8905'),
(65, 'https://picsum.photos/800/600?random=7785'),
(66, 'https://picsum.photos/800/600?random=5909'),
(67, 'https://picsum.photos/800/600?random=6586'),
(68, 'https://picsum.photos/800/600?random=9155'),
(69, 'https://picsum.photos/800/600?random=5147'),
(70, 'https://picsum.photos/800/600?random=948'),
(71, 'https://picsum.photos/800/600?random=2196'),
(72, 'https://picsum.photos/800/600?random=4838'),
(73, 'https://picsum.photos/800/600?random=8880'),
(74, 'https://picsum.photos/800/600?random=3700'),
(75, 'https://picsum.photos/800/600?random=7530'),
(76, 'https://picsum.photos/800/600?random=7667'),
(77, 'https://picsum.photos/800/600?random=5977'),
(78, 'https://picsum.photos/800/600?random=5055'),
(79, 'https://picsum.photos/800/600?random=6841'),
(80, 'https://picsum.photos/800/600?random=9212'),
(81, 'https://picsum.photos/800/600?random=52'),
(82, 'https://picsum.photos/800/600?random=8564'),
(83, 'https://picsum.photos/800/600?random=1893'),
(84, 'https://picsum.photos/800/600?random=4146'),
(85, 'https://picsum.photos/800/600?random=3025'),
(86, 'https://picsum.photos/800/600?random=646'),
(87, 'https://picsum.photos/800/600?random=7531'),
(88, 'https://picsum.photos/800/600?random=7966'),
(89, 'https://picsum.photos/800/600?random=5223'),
(90, 'https://picsum.photos/800/600?random=2625'),
(91, 'https://picsum.photos/800/600?random=2276'),
(92, 'https://picsum.photos/800/600?random=7601'),
(93, 'https://picsum.photos/800/600?random=3951'),
(94, 'https://picsum.photos/800/600?random=2674'),
(95, 'https://picsum.photos/800/600?random=4287'),
(96, 'https://picsum.photos/800/600?random=5852'),
(97, 'https://picsum.photos/800/600?random=8937'),
(98, 'https://picsum.photos/800/600?random=7063'),
(99, 'https://picsum.photos/800/600?random=4099'),
(100, 'https://picsum.photos/800/600?random=3431'),
(101, 'https://picsum.photos/800/600?random=8045'),
(102, 'https://picsum.photos/800/600?random=3318'),
(103, 'https://picsum.photos/800/600?random=6746'),
(104, 'https://picsum.photos/800/600?random=4055'),
(105, 'https://picsum.photos/800/600?random=5273'),
(106, 'https://picsum.photos/800/600?random=1772'),
(107, 'https://picsum.photos/800/600?random=7468'),
(108, 'https://picsum.photos/800/600?random=4455'),
(109, 'https://picsum.photos/800/600?random=6817'),
(110, 'https://picsum.photos/800/600?random=1776'),
(111, 'https://picsum.photos/800/600?random=2567'),
(112, 'https://picsum.photos/800/600?random=5402'),
(113, 'https://picsum.photos/800/600?random=1441'),
(114, 'https://picsum.photos/800/600?random=5887'),
(115, 'https://picsum.photos/800/600?random=7412'),
(116, 'https://picsum.photos/800/600?random=5987'),
(117, 'https://picsum.photos/800/600?random=9849'),
(118, 'https://picsum.photos/800/600?random=9939'),
(119, 'https://picsum.photos/800/600?random=438'),
(120, 'https://picsum.photos/800/600?random=8123'),
(121, 'https://picsum.photos/800/600?random=2641'),
(122, 'https://picsum.photos/800/600?random=4253'),
(123, 'https://picsum.photos/800/600?random=9157'),
(124, 'https://picsum.photos/800/600?random=6411'),
(125, 'https://picsum.photos/800/600?random=3331'),
(126, 'https://picsum.photos/800/600?random=8747'),
(127, 'https://picsum.photos/800/600?random=7307'),
(128, 'https://picsum.photos/800/600?random=5759'),
(129, 'https://picsum.photos/800/600?random=3474'),
(130, 'https://picsum.photos/800/600?random=7800'),
(131, 'https://picsum.photos/800/600?random=5365'),
(132, 'https://picsum.photos/800/600?random=9977'),
(133, 'https://picsum.photos/800/600?random=4037'),
(134, 'https://picsum.photos/800/600?random=5485'),
(135, 'https://picsum.photos/800/600?random=7322'),
(136, 'https://picsum.photos/800/600?random=2065'),
(137, 'https://picsum.photos/800/600?random=4246'),
(138, 'https://picsum.photos/800/600?random=9518'),
(139, 'https://picsum.photos/800/600?random=7420'),
(140, 'https://picsum.photos/800/600?random=4105'),
(141, 'https://picsum.photos/800/600?random=5546'),
(142, 'https://picsum.photos/800/600?random=1278'),
(143, 'https://picsum.photos/800/600?random=1152'),
(144, 'https://picsum.photos/800/600?random=156'),
(145, 'https://picsum.photos/800/600?random=1231'),
(146, 'https://picsum.photos/800/600?random=6207'),
(147, 'https://picsum.photos/800/600?random=3623'),
(148, 'https://picsum.photos/800/600?random=2508'),
(149, 'https://picsum.photos/800/600?random=4901'),
(150, 'https://picsum.photos/800/600?random=9130'),
(151, 'https://picsum.photos/800/600?random=3505'),
(152, 'https://picsum.photos/800/600?random=5058'),
(153, 'https://picsum.photos/800/600?random=8874'),
(154, 'https://picsum.photos/800/600?random=7331'),
(155, 'https://picsum.photos/800/600?random=8853'),
(156, 'https://picsum.photos/800/600?random=2601'),
(157, 'https://picsum.photos/800/600?random=6840'),
(158, 'https://picsum.photos/800/600?random=8291'),
(159, 'https://picsum.photos/800/600?random=8478'),
(160, 'https://picsum.photos/800/600?random=6482'),
(161, 'https://picsum.photos/800/600?random=9682'),
(162, 'https://picsum.photos/800/600?random=2689'),
(163, 'https://picsum.photos/800/600?random=4742'),
(164, 'https://picsum.photos/800/600?random=3'),
(165, 'https://picsum.photos/800/600?random=334'),
(166, 'https://picsum.photos/800/600?random=4781'),
(167, 'https://picsum.photos/800/600?random=1871'),
(168, 'https://picsum.photos/800/600?random=9447'),
(169, 'https://picsum.photos/800/600?random=7305'),
(170, 'https://picsum.photos/800/600?random=4415'),
(171, 'https://picsum.photos/800/600?random=4441'),
(172, 'https://picsum.photos/800/600?random=6547'),
(173, 'https://picsum.photos/800/600?random=2638'),
(174, 'https://picsum.photos/800/600?random=4257'),
(175, 'https://picsum.photos/800/600?random=2426'),
(176, 'https://picsum.photos/800/600?random=4608'),
(177, 'https://picsum.photos/800/600?random=7284'),
(178, 'https://picsum.photos/800/600?random=7058'),
(179, 'https://picsum.photos/800/600?random=2321'),
(180, 'https://picsum.photos/800/600?random=1248'),
(181, 'https://picsum.photos/800/600?random=661'),
(182, 'https://picsum.photos/800/600?random=8035'),
(183, 'https://picsum.photos/800/600?random=1671'),
(184, 'https://picsum.photos/800/600?random=4512'),
(185, 'https://picsum.photos/800/600?random=8789'),
(186, 'https://picsum.photos/800/600?random=4140'),
(187, 'https://picsum.photos/800/600?random=9'),
(188, 'https://picsum.photos/800/600?random=7385'),
(189, 'https://picsum.photos/800/600?random=4494'),
(190, 'https://picsum.photos/800/600?random=6200'),
(191, 'https://picsum.photos/800/600?random=2070'),
(192, 'https://picsum.photos/800/600?random=3118'),
(193, 'https://picsum.photos/800/600?random=2497'),
(194, '/storage/room_images/img_69b1641036fc35.39827041.jpg'),
(195, '/storage/room_images/img_69b16410aef346.44242168.jpg'),
(196, '/storage/room_images/img_69b164113415d3.26305294.webp'),
(197, '/storage/room_images/img_69b16411adf236.77472230.jpg'),
(198, '/storage/room_images/img_69b16412667b27.75061013.webp'),
(199, '/storage/room_images/img_69b1642549d386.50791431.jpg'),
(200, '/storage/room_images/img_69b16425b862e1.73219509.webp'),
(201, '/storage/room_images/img_69b16426494336.66347758.jpg'),
(202, '/storage/room_images/img_69b164f5603237.06096748.jpg'),
(203, '/storage/room_images/img_69b164f5d79644.24900288.jpg'),
(204, '/storage/room_images/img_69b164f687d0e4.42986430.webp');

-- --------------------------------------------------------

--
-- Table structure for table `imagesrelation`
--

CREATE TABLE `imagesrelation` (
  `images_id` bigint UNSIGNED NOT NULL,
  `rooms_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `imagesrelation`
--

INSERT INTO `imagesrelation` (`images_id`, `rooms_id`) VALUES
(6, 4),
(8, 4),
(6, 5),
(8, 6),
(7, 7),
(8, 7),
(10, 8),
(9, 9),
(10, 9),
(11, 9),
(9, 10),
(10, 11),
(11, 11),
(12, 11),
(11, 12),
(13, 13),
(14, 13),
(16, 13),
(13, 14),
(15, 14),
(16, 14),
(13, 16),
(14, 16),
(17, 17),
(18, 17),
(20, 18),
(22, 18),
(17, 19),
(20, 19),
(20, 20),
(17, 21),
(19, 21),
(21, 21),
(22, 21),
(23, 22),
(23, 23),
(24, 23),
(25, 24),
(26, 25),
(30, 25),
(26, 26),
(30, 26),
(31, 26),
(27, 27),
(26, 28),
(28, 28),
(29, 28),
(30, 29),
(32, 30),
(33, 30),
(34, 30),
(35, 30),
(32, 31),
(32, 32),
(35, 32),
(38, 33),
(39, 33),
(40, 33),
(36, 34),
(38, 34),
(39, 34),
(40, 34),
(36, 35),
(36, 36),
(37, 36),
(38, 36),
(39, 36),
(41, 37),
(42, 38),
(43, 38),
(41, 39),
(43, 39),
(44, 40),
(48, 40),
(44, 41),
(48, 41),
(44, 42),
(46, 42),
(47, 42),
(48, 42),
(45, 43),
(51, 44),
(49, 46),
(50, 46),
(51, 46),
(49, 48),
(50, 48),
(53, 49),
(55, 50),
(54, 52),
(55, 52),
(52, 53),
(54, 53),
(55, 53),
(57, 54),
(58, 54),
(56, 55),
(57, 55),
(60, 55),
(58, 56),
(60, 56),
(59, 57),
(60, 57),
(61, 58),
(62, 58),
(61, 59),
(62, 59),
(63, 59),
(64, 59),
(65, 59),
(62, 60),
(63, 60),
(64, 60),
(66, 61),
(67, 61),
(67, 62),
(68, 62),
(67, 63),
(68, 63),
(69, 64),
(70, 64),
(70, 65),
(69, 66),
(71, 66),
(70, 67),
(71, 67),
(72, 69),
(73, 69),
(76, 69),
(72, 70),
(74, 70),
(72, 71),
(74, 71),
(75, 71),
(76, 71),
(74, 72),
(75, 72),
(76, 72),
(78, 73),
(79, 73),
(80, 73),
(77, 74),
(78, 74),
(80, 74),
(77, 75),
(78, 75),
(79, 75),
(81, 76),
(84, 77),
(81, 78),
(82, 78),
(83, 78),
(81, 79),
(82, 79),
(84, 80),
(86, 81),
(89, 81),
(87, 82),
(90, 82),
(86, 83),
(87, 83),
(88, 83),
(89, 83),
(85, 84),
(88, 84),
(89, 84),
(93, 85),
(95, 85),
(91, 86),
(93, 86),
(95, 86),
(96, 86),
(92, 87),
(93, 87),
(94, 87),
(95, 87),
(99, 88),
(97, 89),
(99, 89),
(98, 90),
(101, 90),
(98, 91),
(99, 91),
(100, 91),
(97, 92),
(98, 92),
(100, 92),
(101, 92),
(103, 93),
(104, 93),
(105, 93),
(102, 94),
(102, 95),
(103, 95),
(104, 95),
(102, 96),
(106, 97),
(109, 97),
(111, 97),
(107, 98),
(109, 98),
(108, 99),
(110, 99),
(111, 99),
(108, 100),
(111, 101),
(112, 102),
(114, 103),
(115, 103),
(113, 104),
(115, 104),
(117, 104),
(112, 105),
(114, 105),
(116, 105),
(119, 107),
(120, 107),
(121, 107),
(120, 108),
(118, 109),
(118, 110),
(119, 110),
(121, 110),
(120, 111),
(121, 111),
(122, 112),
(123, 112),
(124, 112),
(126, 112),
(124, 113),
(125, 113),
(126, 113),
(127, 113),
(122, 114),
(123, 114),
(124, 114),
(125, 114),
(126, 114),
(129, 115),
(130, 115),
(132, 115),
(133, 115),
(128, 116),
(129, 116),
(130, 116),
(129, 117),
(130, 117),
(131, 117),
(131, 118),
(134, 119),
(135, 119),
(137, 119),
(138, 119),
(135, 120),
(136, 120),
(137, 120),
(138, 120),
(139, 120),
(135, 121),
(138, 121),
(140, 122),
(142, 122),
(140, 123),
(141, 123),
(143, 123),
(140, 124),
(141, 124),
(143, 124),
(144, 125),
(146, 125),
(146, 126),
(144, 127),
(145, 127),
(146, 127),
(148, 128),
(149, 128),
(147, 129),
(149, 129),
(149, 130),
(151, 131),
(152, 131),
(153, 131),
(155, 131),
(151, 132),
(153, 132),
(154, 132),
(155, 132),
(151, 133),
(150, 134),
(153, 134),
(155, 134),
(150, 135),
(154, 135),
(156, 136),
(159, 136),
(156, 137),
(157, 137),
(157, 139),
(159, 139),
(158, 140),
(159, 140),
(162, 143),
(160, 144),
(163, 144),
(160, 145),
(161, 145),
(165, 146),
(166, 146),
(165, 147),
(164, 148),
(165, 148),
(166, 148),
(167, 149),
(169, 149),
(167, 150),
(168, 150),
(168, 151),
(167, 152),
(169, 152),
(170, 153),
(172, 153),
(170, 154),
(171, 154),
(170, 155),
(173, 156),
(174, 156),
(177, 156),
(176, 157),
(173, 158),
(174, 158),
(175, 158),
(176, 158),
(174, 159),
(176, 159),
(177, 159),
(181, 160),
(182, 160),
(178, 161),
(179, 161),
(181, 161),
(182, 161),
(178, 162),
(179, 162),
(181, 162),
(182, 162),
(183, 162),
(178, 163),
(180, 163),
(184, 164),
(187, 164),
(188, 164),
(184, 165),
(186, 165),
(189, 165),
(184, 166),
(185, 166),
(186, 166),
(188, 166),
(185, 167),
(187, 167),
(185, 168),
(186, 168),
(190, 169),
(191, 169),
(192, 169),
(190, 170),
(191, 170),
(192, 170),
(193, 170),
(190, 171),
(192, 171),
(193, 171),
(191, 172),
(193, 172);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','approved','sent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `tax_rate` int NOT NULL DEFAULT '27',
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `pdf_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `booking_id`, `invoice_number`, `status`, `subtotal`, `tax_amount`, `total_amount`, `tax_rate`, `issue_date`, `due_date`, `pdf_path`, `payment_token`, `approved_at`, `sent_at`, `created_at`, `updated_at`) VALUES
(2, 146, 'EU2026/00001', 'sent', 245.00, 0.00, 245.00, 0, '2026-03-11', '2026-03-19', 'invoices/EU2026/00001.pdf', NULL, '2026-03-11 13:08:10', '2026-03-11 13:08:12', '2026-03-11 13:08:09', '2026-03-11 13:08:12');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_01_14_093246_create_hotels_table', 1),
(5, '2025_01_15_000001_update_rfid_keys_table', 1),
(6, '2025_01_15_000002_create_rfid_assignments_table', 1),
(7, '2025_11_12_084638_create_personal_access_tokens_table', 1),
(8, '2025_12_22_141410_create_pending_handshakes_table', 1),
(9, '2026_01_13_132041_add_email_verification_token_to_users_table', 1),
(10, '2026_01_13_173850_create_invoices_table', 1),
(11, '2026_01_13_174437_add_invoice_fields_to_users_table', 1),
(12, '2026_01_13_190055_add_two_factor_to_users_table', 1),
(13, '2026_01_13_205959_add_cover_image_to_hotels_table', 1),
(14, '2026_01_20_000001_add_reservation_dates_to_rfid_assignments_table', 1),
(15, '2026_01_20_000002_create_booking_payments_table', 1),
(16, '2026_01_20_000003_create_booking_invoice_details_table', 1),
(17, '2026_01_20_000004_create_two_factor_recovery_tokens_table', 1),
(18, '2026_01_27_075748_update_devices_table_for_registration', 1),
(19, '2026_02_01_000003_make_booking_id_nullable_in_rfid_assignments', 1),
(20, '2026_02_01_000004_add_name_to_rfid_keys_table', 1),
(21, '2026_02_01_000005_add_type_to_rfid_keys_table', 1),
(22, '2026_02_03_075855_add_card_to_booking_payments_method_enum', 1),
(23, '2026_02_03_081444_add_billing_fields_to_hotels_table', 1),
(24, '2026_02_11_144156_add_user_id_to_service_tags_table', 1),
(25, '2026_02_11_145030_add_is_approved_to_hotels_table', 1),
(26, '2026_02_11_190254_add_payment_token_to_invoices_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pending_handshakes`
--

CREATE TABLE `pending_handshakes` (
  `id` bigint UNSIGNED NOT NULL,
  `hotel_id` bigint UNSIGNED NOT NULL,
  `endpoint` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tries` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(4, 'App\\Models\\User', 74, 'api_token', '088e8316811a565fe860dd7448495607f6657670a0fe8fb078b3749d7701a119', '[\"*\"]', '2026-02-24 08:40:46', NULL, '2026-02-24 08:40:44', '2026-02-24 08:40:46'),
(5, 'App\\Models\\User', 74, 'api_token', '98d7933d731c7a555a10c520de13c4df2d2f633eb8b35839eb4bd603faeb3fd1', '[\"*\"]', '2026-02-24 08:42:51', NULL, '2026-02-24 08:42:48', '2026-02-24 08:42:51'),
(6, 'App\\Models\\User', 74, 'api_token', '006b674bf280a1d6aa5056e7c09289360df40056fdd1ac95465ce920f935ccf9', '[\"*\"]', '2026-02-24 08:47:59', NULL, '2026-02-24 08:47:57', '2026-02-24 08:47:59'),
(7, 'App\\Models\\User', 74, 'api_token', '6ed5b73308c976cb3efbd283127fa72e02cd7e8221bdf1f68ce5f103048a92c0', '[\"*\"]', '2026-02-24 08:48:52', NULL, '2026-02-24 08:48:50', '2026-02-24 08:48:52'),
(8, 'App\\Models\\User', 74, 'api_token', 'b85c4683582cac2f5abdd6d0e0645d118b38a74afa392a94d7a859539ead30ac', '[\"*\"]', '2026-02-24 08:54:39', NULL, '2026-02-24 08:54:37', '2026-02-24 08:54:39'),
(9, 'App\\Models\\User', 74, 'api_token', '7c0130f277f7b26a43dc8a4619ece31d62cfb0086264e2bb01afd155dc32e2dc', '[\"*\"]', '2026-02-24 08:56:21', NULL, '2026-02-24 08:56:19', '2026-02-24 08:56:21'),
(10, 'App\\Models\\User', 74, 'api_token', 'c6a611cfb03615a1d68a1706a3f37b12b4bbfcded6830244b2c896d92a6362e3', '[\"*\"]', '2026-02-24 08:59:40', NULL, '2026-02-24 08:59:38', '2026-02-24 08:59:40'),
(11, 'App\\Models\\User', 74, 'api_token', 'ab079290c9ce4944ed310e412e52b0843f88d25f070317b779277f588e3b4278', '[\"*\"]', NULL, NULL, '2026-02-24 09:02:24', '2026-02-24 09:02:24'),
(12, 'App\\Models\\User', 74, 'api_token', 'c56c20e91bad9db756d42f68024d83c1ac2c9b3b02030fa3a0028e4fc85e4064', '[\"*\"]', '2026-02-24 09:04:59', NULL, '2026-02-24 09:04:57', '2026-02-24 09:04:59'),
(13, 'App\\Models\\User', 74, 'api_token', 'dbeca5fb1d403bcd7742575fbe43825650a4cf9b38b7cb320b2f22b699cbcc09', '[\"*\"]', '2026-02-24 09:07:44', NULL, '2026-02-24 09:07:42', '2026-02-24 09:07:44'),
(14, 'App\\Models\\User', 74, 'api_token', 'b7a0ca3e0111aba6d8ca6fed74ed5ef3b2257c25444343a93da8be91d784ead5', '[\"*\"]', '2026-02-24 09:09:20', NULL, '2026-02-24 09:09:18', '2026-02-24 09:09:20'),
(15, 'App\\Models\\User', 74, 'api_token', '475cb385424670d36e119ea5971bd683cb3acdb5c13b0077c7006ee1a5b65325', '[\"*\"]', '2026-02-24 09:11:09', NULL, '2026-02-24 09:11:07', '2026-02-24 09:11:09'),
(16, 'App\\Models\\User', 74, 'api_token', '0179a848d87cfaa5306f43c4503bc0776b905e02d2df34b1c315376bebd20be7', '[\"*\"]', '2026-02-24 09:13:01', NULL, '2026-02-24 09:12:59', '2026-02-24 09:13:01'),
(17, 'App\\Models\\User', 74, 'api_token', '9fcc88612d80996d7aa1066e5359a3d948839b064c7856031d8f544130668879', '[\"*\"]', '2026-02-24 09:14:37', NULL, '2026-02-24 09:14:35', '2026-02-24 09:14:37'),
(18, 'App\\Models\\User', 74, 'api_token', 'c606f7a03388135d5d1066165780eac94cd1f891f4aee8022e621a51ce73265e', '[\"*\"]', '2026-02-24 09:15:56', NULL, '2026-02-24 09:15:54', '2026-02-24 09:15:56'),
(19, 'App\\Models\\User', 74, 'api_token', '211822bcf59542b69b00f01da002bd700c5b23dda463f886a69dc6df8dcdb7cc', '[\"*\"]', '2026-02-24 09:19:30', NULL, '2026-02-24 09:19:28', '2026-02-24 09:19:30'),
(20, 'App\\Models\\User', 74, 'api_token', 'b4865476f5c43af5779ab7fd92a08a4bcda95c7f401c3cffb358728f7b52075c', '[\"*\"]', '2026-02-24 09:20:00', NULL, '2026-02-24 09:19:58', '2026-02-24 09:20:00'),
(21, 'App\\Models\\User', 74, 'api_token', '2570588646bb7856fcabc55d3dad97555fabc7d8a34ec0af69c86f4eb2e8bee2', '[\"*\"]', '2026-02-24 09:21:23', NULL, '2026-02-24 09:21:21', '2026-02-24 09:21:23'),
(22, 'App\\Models\\User', 74, 'api_token', 'bb15afb2e783dc85c7408f0486824d1cd220171d18ebb738d091eab4a98b32e3', '[\"*\"]', '2026-02-24 09:23:33', NULL, '2026-02-24 09:23:30', '2026-02-24 09:23:33'),
(24, 'App\\Models\\User', 74, 'api_token', '0cc7de9ff6ce0e704deee510b895f68f040c78ba5f60696dbdbac0fd30e13ecd', '[\"*\"]', '2026-02-24 09:27:08', NULL, '2026-02-24 09:27:04', '2026-02-24 09:27:08'),
(25, 'App\\Models\\User', 74, 'api_token', 'e34779802b32b537a6f522f51b8be30d5e8395f61af2c95e8c1916295e1c53cc', '[\"*\"]', '2026-02-24 09:28:12', NULL, '2026-02-24 09:28:05', '2026-02-24 09:28:12'),
(26, 'App\\Models\\User', 74, 'api_token', 'e94ea6aa615ea9357afc0bc047be03f9e34c7809e551e759430ce88e3f007956', '[\"*\"]', '2026-02-24 09:43:10', NULL, '2026-02-24 09:43:06', '2026-02-24 09:43:10'),
(27, 'App\\Models\\User', 74, 'api_token', '8f09d4f03679a635735d65dd73c688aab7e8ac3ab93e832ccbebef27fe525cba', '[\"*\"]', '2026-02-24 09:43:44', NULL, '2026-02-24 09:43:40', '2026-02-24 09:43:44'),
(28, 'App\\Models\\User', 73, 'api_token', '2721ea06164c444809d308570f60073008f042c68ff4a6a71e91f7beaa16b45b', '[\"*\"]', '2026-02-25 13:01:18', NULL, '2026-02-25 11:40:44', '2026-02-25 13:01:18'),
(29, 'App\\Models\\User', 73, 'api_token', '159463959769ad0889d1b0467442e5978fbdde32fb993352ca2a4630c16e4c1e', '[\"*\"]', '2026-03-11 10:53:07', NULL, '2026-02-25 12:24:10', '2026-03-11 10:53:07'),
(30, 'App\\Models\\User', 74, 'api_token', '5bc9926eef6fd4c70febfbf9b26abe68a1f145d45efb4720266d0faeb55f011a', '[\"*\"]', '2026-02-25 13:35:04', NULL, '2026-02-25 13:02:15', '2026-02-25 13:35:04'),
(32, 'App\\Models\\User', 73, 'api_token', '436f42f8ec0d30d863b2463d7dfd6cd576ece54f67c1120c5258f6ae5fd812f0', '[\"*\"]', NULL, NULL, '2026-02-27 07:20:22', '2026-02-27 07:20:22'),
(34, 'App\\Models\\User', 73, 'api_token', '2cbfe85237d61e787df3a907171f6c300c172b9a04d0d896ccaf20118264e440', '[\"*\"]', '2026-02-27 07:40:50', NULL, '2026-02-27 07:40:49', '2026-02-27 07:40:50'),
(35, 'App\\Models\\User', 74, 'api_token', 'd28f98ebd8eaecc6e5d165bb7e1ec0be96adde8d5f18f00abe958a806cece6ac', '[\"*\"]', '2026-02-27 07:42:16', NULL, '2026-02-27 07:42:12', '2026-02-27 07:42:16'),
(36, 'App\\Models\\User', 73, 'api_token', '5bce7b85c8913bdc15c23cc2d733a442f849b851c46027ca227e6a48811f3c6a', '[\"*\"]', '2026-02-27 07:44:37', NULL, '2026-02-27 07:44:19', '2026-02-27 07:44:37'),
(38, 'App\\Models\\User', 74, 'api_token', '94850fc51079e84e4069616675299f8e6158ff7d990996f36590826939d6887d', '[\"*\"]', '2026-03-10 07:02:15', NULL, '2026-03-10 06:59:05', '2026-03-10 07:02:15'),
(39, 'App\\Models\\User', 74, 'api_token', '56b416faf28ad7376c26a37bf0c2a33ba5e3d7851292362a055e994db0692cd1', '[\"*\"]', '2026-03-11 13:00:51', NULL, '2026-03-11 10:53:57', '2026-03-11 13:00:51'),
(41, 'App\\Models\\User', 72, 'api_token', 'f8dc5e62c6b5555354f78fe8817eb462c2d70cb00a043b6d2d4b74dc044e0a9f', '[\"*\"]', '2026-03-11 13:10:58', NULL, '2026-03-11 11:34:23', '2026-03-11 13:10:58'),
(43, 'App\\Models\\User', 73, 'api_token', '003067382a25c61538ba292a202999be83438d59c393de8a1a11b4c8c9db6102', '[\"*\"]', '2026-03-11 12:37:06', NULL, '2026-03-11 12:36:55', '2026-03-11 12:37:06'),
(44, 'App\\Models\\User', 76, 'api_token', '2bd67aeec41ae120ace5ed6b8e1536264283227b1363f8547d38b075fc976146', '[\"*\"]', '2026-03-11 13:07:54', NULL, '2026-03-11 12:41:47', '2026-03-11 13:07:54'),
(45, 'App\\Models\\User', 77, 'api_token', 'e6adec2bf995bc155f31778057b7fb213e52355dc4efe4340b0e1b70131a94ef', '[\"*\"]', '2026-03-11 13:08:39', NULL, '2026-03-11 13:04:25', '2026-03-11 13:08:39');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `rating` int NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfidkeyconnection`
--

CREATE TABLE `rfidkeyconnection` (
  `id` bigint UNSIGNED NOT NULL,
  `rfidKeys_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rooms_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rfidkeyconnection`
--

INSERT INTO `rfidkeyconnection` (`id`, `rfidKeys_id`, `rooms_id`) VALUES
(5, 'F4E4C928', 176);

-- --------------------------------------------------------

--
-- Table structure for table `rfidkeys`
--

CREATE TABLE `rfidkeys` (
  `id` bigint UNSIGNED NOT NULL,
  `hotels_id` bigint UNSIGNED NOT NULL,
  `isUsed` tinyint(1) NOT NULL DEFAULT '0',
  `rfidKey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'guest',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('available','assigned','lost','disabled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rfidkeys`
--

INSERT INTO `rfidkeys` (`id`, `hotels_id`, `isUsed`, `rfidKey`, `name`, `type`, `label`, `status`, `created_at`, `updated_at`) VALUES
(6, 2, 0, '77668409', NULL, 'guest', NULL, 'available', NULL, NULL),
(7, 2, 0, '85374497', NULL, 'guest', NULL, 'available', NULL, NULL),
(8, 2, 0, '66597962', NULL, 'guest', NULL, 'available', NULL, NULL),
(9, 2, 0, '96291051', NULL, 'guest', NULL, 'available', NULL, NULL),
(10, 2, 0, '65044660', NULL, 'guest', NULL, 'available', NULL, NULL),
(11, 3, 0, '74417642', NULL, 'guest', NULL, 'available', NULL, NULL),
(12, 3, 0, '76473559', NULL, 'guest', NULL, 'available', NULL, NULL),
(13, 3, 0, '15039865', NULL, 'guest', NULL, 'available', NULL, NULL),
(14, 3, 0, '12562588', NULL, 'guest', NULL, 'available', NULL, NULL),
(15, 3, 0, '57430471', NULL, 'guest', NULL, 'available', NULL, NULL),
(16, 3, 0, '28937059', NULL, 'guest', NULL, 'available', NULL, NULL),
(17, 3, 0, '37017315', NULL, 'guest', NULL, 'available', NULL, NULL),
(18, 3, 0, '47685411', NULL, 'guest', NULL, 'available', NULL, NULL),
(19, 4, 0, '06598001', NULL, 'guest', NULL, 'available', NULL, NULL),
(20, 4, 0, '17942749', NULL, 'guest', NULL, 'available', NULL, NULL),
(21, 4, 0, '26026998', NULL, 'guest', NULL, 'available', NULL, NULL),
(22, 4, 0, '54121577', NULL, 'guest', NULL, 'available', NULL, NULL),
(23, 4, 0, '56162252', NULL, 'guest', NULL, 'available', NULL, NULL),
(24, 4, 0, '17604071', NULL, 'guest', NULL, 'available', NULL, NULL),
(25, 4, 0, '92812140', NULL, 'guest', NULL, 'available', NULL, NULL),
(26, 5, 0, '54218429', NULL, 'guest', NULL, 'available', NULL, NULL),
(27, 5, 0, '84026615', NULL, 'guest', NULL, 'available', NULL, NULL),
(28, 5, 0, '78570621', NULL, 'guest', NULL, 'available', NULL, NULL),
(29, 5, 0, '48212238', NULL, 'guest', NULL, 'available', NULL, NULL),
(30, 5, 0, '05762496', NULL, 'guest', NULL, 'available', NULL, NULL),
(31, 5, 0, '33937449', NULL, 'guest', NULL, 'available', NULL, NULL),
(32, 5, 0, '71783297', NULL, 'guest', NULL, 'available', NULL, NULL),
(33, 5, 0, '82814191', NULL, 'guest', NULL, 'available', NULL, NULL),
(34, 5, 0, '48102665', NULL, 'guest', NULL, 'available', NULL, NULL),
(35, 5, 0, '42860100', NULL, 'guest', NULL, 'available', NULL, NULL),
(36, 6, 0, '99425819', NULL, 'guest', NULL, 'available', NULL, NULL),
(37, 6, 0, '11636870', NULL, 'guest', NULL, 'available', NULL, NULL),
(38, 6, 0, '64322728', NULL, 'guest', NULL, 'available', NULL, NULL),
(39, 6, 0, '56254537', NULL, 'guest', NULL, 'available', NULL, NULL),
(40, 6, 0, '78525871', NULL, 'guest', NULL, 'available', NULL, NULL),
(41, 6, 0, '26538742', NULL, 'guest', NULL, 'available', NULL, NULL),
(42, 6, 0, '76432345', NULL, 'guest', NULL, 'available', NULL, NULL),
(43, 6, 0, '54799049', NULL, 'guest', NULL, 'available', NULL, NULL),
(44, 7, 0, '79865155', NULL, 'guest', NULL, 'available', NULL, NULL),
(45, 7, 0, '71901212', NULL, 'guest', NULL, 'available', NULL, NULL),
(46, 7, 0, '99558483', NULL, 'guest', NULL, 'available', NULL, NULL),
(47, 7, 0, '70411761', NULL, 'guest', NULL, 'available', NULL, NULL),
(48, 7, 0, '96931742', NULL, 'guest', NULL, 'available', NULL, NULL),
(49, 8, 0, '42879451', NULL, 'guest', NULL, 'available', NULL, NULL),
(50, 8, 0, '04668114', NULL, 'guest', NULL, 'available', NULL, NULL),
(51, 8, 0, '64800502', NULL, 'guest', NULL, 'available', NULL, NULL),
(52, 8, 0, '66236962', NULL, 'guest', NULL, 'available', NULL, NULL),
(53, 8, 0, '21444604', NULL, 'guest', NULL, 'available', NULL, NULL),
(54, 9, 0, '77099570', NULL, 'guest', NULL, 'available', NULL, NULL),
(55, 9, 0, '60795952', NULL, 'guest', NULL, 'available', NULL, NULL),
(56, 9, 0, '81665482', NULL, 'guest', NULL, 'available', NULL, NULL),
(57, 9, 0, '76784199', NULL, 'guest', NULL, 'available', NULL, NULL),
(58, 9, 0, '10215973', NULL, 'guest', NULL, 'available', NULL, NULL),
(59, 9, 0, '57832759', NULL, 'guest', NULL, 'available', NULL, NULL),
(60, 9, 0, '63577195', NULL, 'guest', NULL, 'available', NULL, NULL),
(61, 9, 0, '23973791', NULL, 'guest', NULL, 'available', NULL, NULL),
(62, 9, 0, '32997227', NULL, 'guest', NULL, 'available', NULL, NULL),
(63, 10, 0, '14962131', NULL, 'guest', NULL, 'available', NULL, NULL),
(64, 10, 0, '38595699', NULL, 'guest', NULL, 'available', NULL, NULL),
(65, 10, 0, '16873171', NULL, 'guest', NULL, 'available', NULL, NULL),
(66, 10, 0, '64838790', NULL, 'guest', NULL, 'available', NULL, NULL),
(67, 10, 0, '24842942', NULL, 'guest', NULL, 'available', NULL, NULL),
(68, 11, 0, '54747807', NULL, 'guest', NULL, 'available', NULL, NULL),
(69, 11, 0, '49930013', NULL, 'guest', NULL, 'available', NULL, NULL),
(70, 11, 0, '75089072', NULL, 'guest', NULL, 'available', NULL, NULL),
(71, 11, 0, '87390678', NULL, 'guest', NULL, 'available', NULL, NULL),
(72, 11, 0, '43123088', NULL, 'guest', NULL, 'available', NULL, NULL),
(73, 11, 0, '87170576', NULL, 'guest', NULL, 'available', NULL, NULL),
(74, 11, 0, '49351909', NULL, 'guest', NULL, 'available', NULL, NULL),
(75, 11, 0, '92103650', NULL, 'guest', NULL, 'available', NULL, NULL),
(76, 11, 0, '84303467', NULL, 'guest', NULL, 'available', NULL, NULL),
(77, 11, 0, '54393737', NULL, 'guest', NULL, 'available', NULL, NULL),
(78, 12, 0, '18443469', NULL, 'guest', NULL, 'available', NULL, NULL),
(79, 12, 0, '25839310', NULL, 'guest', NULL, 'available', NULL, NULL),
(80, 12, 0, '59624876', NULL, 'guest', NULL, 'available', NULL, NULL),
(81, 12, 0, '98239854', NULL, 'guest', NULL, 'available', NULL, NULL),
(82, 12, 0, '44775055', NULL, 'guest', NULL, 'available', NULL, NULL),
(83, 12, 0, '20046225', NULL, 'guest', NULL, 'available', NULL, NULL),
(84, 12, 0, '49200114', NULL, 'guest', NULL, 'available', NULL, NULL),
(85, 12, 0, '03448621', NULL, 'guest', NULL, 'available', NULL, NULL),
(86, 12, 0, '59743037', NULL, 'guest', NULL, 'available', NULL, NULL),
(87, 13, 0, '53554699', NULL, 'guest', NULL, 'available', NULL, NULL),
(88, 13, 0, '93643177', NULL, 'guest', NULL, 'available', NULL, NULL),
(89, 13, 0, '69351865', NULL, 'guest', NULL, 'available', NULL, NULL),
(90, 13, 0, '93652466', NULL, 'guest', NULL, 'available', NULL, NULL),
(91, 13, 0, '78467219', NULL, 'guest', NULL, 'available', NULL, NULL),
(92, 13, 0, '83429875', NULL, 'guest', NULL, 'available', NULL, NULL),
(93, 14, 0, '64434876', NULL, 'guest', NULL, 'available', NULL, NULL),
(94, 14, 0, '79760705', NULL, 'guest', NULL, 'available', NULL, NULL),
(95, 14, 0, '96347371', NULL, 'guest', NULL, 'available', NULL, NULL),
(96, 14, 0, '05721516', NULL, 'guest', NULL, 'available', NULL, NULL),
(97, 14, 0, '76597518', NULL, 'guest', NULL, 'available', NULL, NULL),
(98, 14, 0, '01430342', NULL, 'guest', NULL, 'available', NULL, NULL),
(99, 14, 0, '04587693', NULL, 'guest', NULL, 'available', NULL, NULL),
(100, 14, 0, '47491394', NULL, 'guest', NULL, 'available', NULL, NULL),
(101, 14, 0, '06206565', NULL, 'guest', NULL, 'available', NULL, NULL),
(102, 14, 0, '92466692', NULL, 'guest', NULL, 'available', NULL, NULL),
(103, 15, 0, '99930022', NULL, 'guest', NULL, 'available', NULL, NULL),
(104, 15, 0, '58257249', NULL, 'guest', NULL, 'available', NULL, NULL),
(105, 15, 0, '55057341', NULL, 'guest', NULL, 'available', NULL, NULL),
(106, 15, 0, '23141068', NULL, 'guest', NULL, 'available', NULL, NULL),
(107, 15, 0, '87117362', NULL, 'guest', NULL, 'available', NULL, NULL),
(108, 16, 0, '36265825', NULL, 'guest', NULL, 'available', NULL, NULL),
(109, 16, 0, '20651723', NULL, 'guest', NULL, 'available', NULL, NULL),
(110, 16, 0, '71166488', NULL, 'guest', NULL, 'available', NULL, NULL),
(111, 16, 0, '29447930', NULL, 'guest', NULL, 'available', NULL, NULL),
(112, 16, 0, '53215015', NULL, 'guest', NULL, 'available', NULL, NULL),
(113, 17, 0, '56803532', NULL, 'guest', NULL, 'available', NULL, NULL),
(114, 17, 0, '08251934', NULL, 'guest', NULL, 'available', NULL, NULL),
(115, 17, 0, '60658374', NULL, 'guest', NULL, 'available', NULL, NULL),
(116, 17, 0, '55360885', NULL, 'guest', NULL, 'available', NULL, NULL),
(117, 17, 0, '11164239', NULL, 'guest', NULL, 'available', NULL, NULL),
(118, 17, 0, '01445260', NULL, 'guest', NULL, 'available', NULL, NULL),
(119, 17, 0, '30991904', NULL, 'guest', NULL, 'available', NULL, NULL),
(120, 17, 0, '87780142', NULL, 'guest', NULL, 'available', NULL, NULL),
(121, 17, 0, '77366169', NULL, 'guest', NULL, 'available', NULL, NULL),
(122, 17, 0, '17760573', NULL, 'guest', NULL, 'available', NULL, NULL),
(123, 18, 0, '65296958', NULL, 'guest', NULL, 'available', NULL, NULL),
(124, 18, 0, '31999770', NULL, 'guest', NULL, 'available', NULL, NULL),
(125, 18, 0, '25250316', NULL, 'guest', NULL, 'available', NULL, NULL),
(126, 18, 0, '85378888', NULL, 'guest', NULL, 'available', NULL, NULL),
(127, 18, 0, '04075259', NULL, 'guest', NULL, 'available', NULL, NULL),
(128, 19, 0, '20771556', NULL, 'guest', NULL, 'available', NULL, NULL),
(129, 19, 0, '18508376', NULL, 'guest', NULL, 'available', NULL, NULL),
(130, 19, 0, '29412748', NULL, 'guest', NULL, 'available', NULL, NULL),
(131, 19, 0, '43625982', NULL, 'guest', NULL, 'available', NULL, NULL),
(132, 19, 0, '99071874', NULL, 'guest', NULL, 'available', NULL, NULL),
(133, 19, 0, '32809960', NULL, 'guest', NULL, 'available', NULL, NULL),
(134, 19, 0, '17335185', NULL, 'guest', NULL, 'available', NULL, NULL),
(135, 19, 0, '12428126', NULL, 'guest', NULL, 'available', NULL, NULL),
(136, 19, 0, '46404116', NULL, 'guest', NULL, 'available', NULL, NULL),
(137, 20, 0, '35131266', NULL, 'guest', NULL, 'available', NULL, NULL),
(138, 20, 0, '12227887', NULL, 'guest', NULL, 'available', NULL, NULL),
(139, 20, 0, '36205342', NULL, 'guest', NULL, 'available', NULL, NULL),
(140, 20, 0, '38291903', NULL, 'guest', NULL, 'available', NULL, NULL),
(141, 20, 0, '85907862', NULL, 'guest', NULL, 'available', NULL, NULL),
(142, 20, 0, '16956928', NULL, 'guest', NULL, 'available', NULL, NULL),
(143, 20, 0, '38435464', NULL, 'guest', NULL, 'available', NULL, NULL),
(144, 21, 0, '20022809', NULL, 'guest', NULL, 'available', NULL, NULL),
(145, 21, 0, '16553724', NULL, 'guest', NULL, 'available', NULL, NULL),
(146, 21, 0, '66663944', NULL, 'guest', NULL, 'available', NULL, NULL),
(147, 21, 0, '52709468', NULL, 'guest', NULL, 'available', NULL, NULL),
(148, 21, 0, '63843523', NULL, 'guest', NULL, 'available', NULL, NULL),
(149, 21, 0, '65251839', NULL, 'guest', NULL, 'available', NULL, NULL),
(150, 21, 0, '24422323', NULL, 'guest', NULL, 'available', NULL, NULL),
(151, 21, 0, '39416006', NULL, 'guest', NULL, 'available', NULL, NULL),
(152, 21, 0, '67503852', NULL, 'guest', NULL, 'available', NULL, NULL),
(153, 21, 0, '47024449', NULL, 'guest', NULL, 'available', NULL, NULL),
(154, 22, 0, '49811542', NULL, 'guest', NULL, 'available', NULL, NULL),
(155, 22, 0, '02896302', NULL, 'guest', NULL, 'available', NULL, NULL),
(156, 22, 0, '28712193', NULL, 'guest', NULL, 'available', NULL, NULL),
(157, 22, 0, '65316449', NULL, 'guest', NULL, 'available', NULL, NULL),
(158, 22, 0, '47363544', NULL, 'guest', NULL, 'available', NULL, NULL),
(159, 23, 0, '47815918', NULL, 'guest', NULL, 'available', NULL, NULL),
(160, 23, 0, '41998808', NULL, 'guest', NULL, 'available', NULL, NULL),
(161, 23, 0, '64873626', NULL, 'guest', NULL, 'available', NULL, NULL),
(162, 23, 0, '15215478', NULL, 'guest', NULL, 'available', NULL, NULL),
(163, 23, 0, '90828879', NULL, 'guest', NULL, 'available', NULL, NULL),
(164, 24, 0, '16052124', NULL, 'guest', NULL, 'available', NULL, NULL),
(165, 24, 0, '67430125', NULL, 'guest', NULL, 'available', NULL, NULL),
(166, 24, 0, '32353536', NULL, 'guest', NULL, 'available', NULL, NULL),
(167, 24, 0, '87316480', NULL, 'guest', NULL, 'available', NULL, NULL),
(168, 24, 0, '21430935', NULL, 'guest', NULL, 'available', NULL, NULL),
(169, 25, 0, '10754865', NULL, 'guest', NULL, 'available', NULL, NULL),
(170, 25, 0, '57924752', NULL, 'guest', NULL, 'available', NULL, NULL),
(171, 25, 0, '79914523', NULL, 'guest', NULL, 'available', NULL, NULL),
(172, 25, 0, '64343573', NULL, 'guest', NULL, 'available', NULL, NULL),
(173, 25, 0, '49331700', NULL, 'guest', NULL, 'available', NULL, NULL),
(174, 25, 0, '39888267', NULL, 'guest', NULL, 'available', NULL, NULL),
(175, 25, 0, '47999202', NULL, 'guest', NULL, 'available', NULL, NULL),
(176, 25, 0, '26606820', NULL, 'guest', NULL, 'available', NULL, NULL),
(177, 26, 0, '66432720', NULL, 'guest', NULL, 'available', NULL, NULL),
(178, 26, 0, '68777952', NULL, 'guest', NULL, 'available', NULL, NULL),
(179, 26, 0, '11657335', NULL, 'guest', NULL, 'available', NULL, NULL),
(180, 26, 0, '05553200', NULL, 'guest', NULL, 'available', NULL, NULL),
(181, 26, 0, '28998759', NULL, 'guest', NULL, 'available', NULL, NULL),
(182, 26, 0, '84728157', NULL, 'guest', NULL, 'available', NULL, NULL),
(183, 27, 0, '33384429', NULL, 'guest', NULL, 'available', NULL, NULL),
(184, 27, 0, '49052879', NULL, 'guest', NULL, 'available', NULL, NULL),
(185, 27, 0, '55708198', NULL, 'guest', NULL, 'available', NULL, NULL),
(186, 27, 0, '79844910', NULL, 'guest', NULL, 'available', NULL, NULL),
(187, 27, 0, '01779520', NULL, 'guest', NULL, 'available', NULL, NULL),
(188, 27, 0, '08787969', NULL, 'guest', NULL, 'available', NULL, NULL),
(189, 27, 0, '69817864', NULL, 'guest', NULL, 'available', NULL, NULL),
(190, 27, 0, '17253321', NULL, 'guest', NULL, 'available', NULL, NULL),
(191, 27, 0, '36186702', NULL, 'guest', NULL, 'available', NULL, NULL),
(192, 27, 0, '30998650', NULL, 'guest', NULL, 'available', NULL, NULL),
(193, 28, 0, '07991633', NULL, 'guest', NULL, 'available', NULL, NULL),
(194, 28, 0, '52645119', NULL, 'guest', NULL, 'available', NULL, NULL),
(195, 28, 0, '43316879', NULL, 'guest', NULL, 'available', NULL, NULL),
(196, 28, 0, '48090078', NULL, 'guest', NULL, 'available', NULL, NULL),
(197, 28, 0, '19927674', NULL, 'guest', NULL, 'available', NULL, NULL),
(198, 28, 0, '76088149', NULL, 'guest', NULL, 'available', NULL, NULL),
(199, 28, 0, '04001382', NULL, 'guest', NULL, 'available', NULL, NULL),
(200, 28, 0, '16098706', NULL, 'guest', NULL, 'available', NULL, NULL),
(201, 29, 0, '93435714', NULL, 'guest', NULL, 'available', NULL, NULL),
(202, 29, 0, '83152872', NULL, 'guest', NULL, 'available', NULL, NULL),
(203, 29, 0, '95196890', NULL, 'guest', NULL, 'available', NULL, NULL),
(204, 29, 0, '46770810', NULL, 'guest', NULL, 'available', NULL, NULL),
(205, 29, 0, '76137602', NULL, 'guest', NULL, 'available', NULL, NULL),
(206, 29, 0, '47860165', NULL, 'guest', NULL, 'available', NULL, NULL),
(207, 30, 0, '51867424', NULL, 'guest', NULL, 'available', NULL, NULL),
(208, 30, 0, '13094461', NULL, 'guest', NULL, 'available', NULL, NULL),
(209, 30, 0, '49367601', NULL, 'guest', NULL, 'available', NULL, NULL),
(210, 30, 0, '81463943', NULL, 'guest', NULL, 'available', NULL, NULL),
(211, 30, 0, '43604292', NULL, 'guest', NULL, 'available', NULL, NULL),
(212, 30, 0, '07628702', NULL, 'guest', NULL, 'available', NULL, NULL),
(213, 30, 0, '67439085', NULL, 'guest', NULL, 'available', NULL, NULL),
(214, 31, 0, '38866659', NULL, 'guest', NULL, 'available', NULL, NULL),
(215, 31, 0, '99846979', NULL, 'guest', NULL, 'available', NULL, NULL),
(216, 31, 0, '30691953', NULL, 'guest', NULL, 'available', NULL, NULL),
(217, 31, 0, '43727772', NULL, 'guest', NULL, 'available', NULL, NULL),
(218, 31, 0, '11691298', NULL, 'guest', NULL, 'available', NULL, NULL),
(219, 31, 0, '99203682', NULL, 'guest', NULL, 'available', NULL, NULL),
(220, 32, 0, '16163742', NULL, 'guest', NULL, 'available', NULL, NULL),
(221, 32, 0, '89130146', NULL, 'guest', NULL, 'available', NULL, NULL),
(222, 32, 0, '98991263', NULL, 'guest', NULL, 'available', NULL, NULL),
(223, 32, 0, '42213166', NULL, 'guest', NULL, 'available', NULL, NULL),
(224, 32, 0, '92579879', NULL, 'guest', NULL, 'available', NULL, NULL),
(225, 32, 0, '36764359', NULL, 'guest', NULL, 'available', NULL, NULL),
(226, 33, 0, '14525001', NULL, 'guest', NULL, 'available', NULL, NULL),
(227, 33, 0, '33860577', NULL, 'guest', NULL, 'available', NULL, NULL),
(228, 33, 0, '60257450', NULL, 'guest', NULL, 'available', NULL, NULL),
(229, 33, 0, '81837201', NULL, 'guest', NULL, 'available', NULL, NULL),
(230, 33, 0, '28371084', NULL, 'guest', NULL, 'available', NULL, NULL),
(231, 33, 0, '45716823', NULL, 'guest', NULL, 'available', NULL, NULL),
(232, 33, 0, '83571344', NULL, 'guest', NULL, 'available', NULL, NULL),
(233, 33, 0, '87544619', NULL, 'guest', NULL, 'available', NULL, NULL),
(234, 33, 0, '28244816', NULL, 'guest', NULL, 'available', NULL, NULL),
(235, 34, 0, '14231869', NULL, 'guest', NULL, 'available', NULL, NULL),
(236, 34, 0, '02468151', NULL, 'guest', NULL, 'available', NULL, NULL),
(237, 34, 0, '52897373', NULL, 'guest', NULL, 'available', NULL, NULL),
(238, 34, 0, '11271287', NULL, 'guest', NULL, 'available', NULL, NULL),
(239, 34, 0, '15240386', NULL, 'guest', NULL, 'available', NULL, NULL),
(240, 34, 0, '95053230', NULL, 'guest', NULL, 'available', NULL, NULL),
(241, 35, 0, '74630381', NULL, 'guest', NULL, 'available', NULL, NULL),
(242, 35, 0, '69538686', NULL, 'guest', NULL, 'available', NULL, NULL),
(243, 35, 0, '71102725', NULL, 'guest', NULL, 'available', NULL, NULL),
(244, 35, 0, '56751584', NULL, 'guest', NULL, 'available', NULL, NULL),
(245, 35, 0, '89655233', NULL, 'guest', NULL, 'available', NULL, NULL),
(246, 35, 0, '31771066', NULL, 'guest', NULL, 'available', NULL, NULL),
(247, 35, 0, '13244179', NULL, 'guest', NULL, 'available', NULL, NULL),
(248, 36, 0, '99610781', NULL, 'guest', NULL, 'available', NULL, NULL),
(249, 36, 0, '84429577', NULL, 'guest', NULL, 'available', NULL, NULL),
(250, 36, 0, '85362167', NULL, 'guest', NULL, 'available', NULL, NULL),
(251, 36, 0, '67108865', NULL, 'guest', NULL, 'available', NULL, NULL),
(252, 36, 0, '03465351', NULL, 'guest', NULL, 'available', NULL, NULL),
(253, 36, 0, '98732480', NULL, 'guest', NULL, 'available', NULL, NULL),
(254, 36, 0, '11095463', NULL, 'guest', NULL, 'available', NULL, NULL),
(255, 36, 0, '32847704', NULL, 'guest', NULL, 'available', NULL, NULL),
(256, 36, 0, '77359013', NULL, 'guest', NULL, 'available', NULL, NULL),
(257, 36, 0, '19741146', NULL, 'guest', NULL, 'available', NULL, NULL),
(258, 37, 0, '10662977', NULL, 'guest', NULL, 'available', NULL, NULL),
(259, 37, 0, '07998069', NULL, 'guest', NULL, 'available', NULL, NULL),
(260, 37, 0, '30109491', NULL, 'guest', NULL, 'available', NULL, NULL),
(261, 37, 0, '52196718', NULL, 'guest', NULL, 'available', NULL, NULL),
(262, 37, 0, '08533131', NULL, 'guest', NULL, 'available', NULL, NULL),
(263, 37, 0, '30239511', NULL, 'guest', NULL, 'available', NULL, NULL),
(264, 37, 0, '50426673', NULL, 'guest', NULL, 'available', NULL, NULL),
(265, 37, 0, '45185412', NULL, 'guest', NULL, 'available', NULL, NULL),
(266, 37, 0, '13703074', NULL, 'guest', NULL, 'available', NULL, NULL),
(267, 37, 0, '07899775', NULL, 'guest', NULL, 'available', NULL, NULL),
(268, 38, 0, '08830137', NULL, 'guest', NULL, 'available', NULL, NULL),
(269, 38, 0, '06283441', NULL, 'guest', NULL, 'available', NULL, NULL),
(270, 38, 0, '89492010', NULL, 'guest', NULL, 'available', NULL, NULL),
(271, 38, 0, '92011496', NULL, 'guest', NULL, 'available', NULL, NULL),
(272, 38, 0, '43440956', NULL, 'guest', NULL, 'available', NULL, NULL),
(273, 38, 0, '65507234', NULL, 'guest', NULL, 'available', NULL, NULL),
(274, 38, 0, '41731518', NULL, 'guest', NULL, 'available', NULL, NULL),
(275, 38, 0, '46056325', NULL, 'guest', NULL, 'available', NULL, NULL),
(276, 39, 0, '88198826', NULL, 'guest', NULL, 'available', NULL, NULL),
(277, 39, 0, '69829870', NULL, 'guest', NULL, 'available', NULL, NULL),
(278, 39, 0, '60646747', NULL, 'guest', NULL, 'available', NULL, NULL),
(279, 39, 0, '39820252', NULL, 'guest', NULL, 'available', NULL, NULL),
(280, 39, 0, '81736452', NULL, 'guest', NULL, 'available', NULL, NULL),
(281, 39, 0, '61725430', NULL, 'guest', NULL, 'available', NULL, NULL),
(282, 40, 0, '97848040', NULL, 'guest', NULL, 'available', NULL, NULL),
(283, 40, 0, '41576514', NULL, 'guest', NULL, 'available', NULL, NULL),
(284, 40, 0, '72484440', NULL, 'guest', NULL, 'available', NULL, NULL),
(285, 40, 0, '33052529', NULL, 'guest', NULL, 'available', NULL, NULL),
(286, 40, 0, '38775565', NULL, 'guest', NULL, 'available', NULL, NULL),
(287, 41, 0, '36975378', NULL, 'guest', NULL, 'available', NULL, NULL),
(288, 41, 0, '25818694', NULL, 'guest', NULL, 'available', NULL, NULL),
(289, 41, 0, '12196166', NULL, 'guest', NULL, 'available', NULL, NULL),
(290, 41, 0, '96355006', NULL, 'guest', NULL, 'available', NULL, NULL),
(291, 41, 0, '27002468', NULL, 'guest', NULL, 'available', NULL, NULL),
(292, 41, 0, '07751788', NULL, 'guest', NULL, 'available', NULL, NULL),
(293, 41, 0, '13207981', NULL, 'guest', NULL, 'available', NULL, NULL),
(294, 42, 0, '47095005', NULL, 'guest', NULL, 'available', NULL, NULL),
(295, 42, 0, '69969943', NULL, 'guest', NULL, 'available', NULL, NULL),
(296, 42, 0, '12622311', NULL, 'guest', NULL, 'available', NULL, NULL),
(297, 42, 0, '91152875', NULL, 'guest', NULL, 'available', NULL, NULL),
(298, 42, 0, '35557133', NULL, 'guest', NULL, 'available', NULL, NULL),
(299, 42, 0, '12021489', NULL, 'guest', NULL, 'available', NULL, NULL),
(300, 42, 0, '48723019', NULL, 'guest', NULL, 'available', NULL, NULL),
(301, 42, 0, '64363293', NULL, 'guest', NULL, 'available', NULL, NULL),
(302, 42, 0, '35784370', NULL, 'guest', NULL, 'available', NULL, NULL),
(303, 43, 0, '91620550', NULL, 'guest', NULL, 'available', NULL, NULL),
(304, 43, 0, '63819681', NULL, 'guest', NULL, 'available', NULL, NULL),
(305, 43, 0, '09390735', NULL, 'guest', NULL, 'available', NULL, NULL),
(306, 43, 0, '82926641', NULL, 'guest', NULL, 'available', NULL, NULL),
(307, 43, 0, '92016739', NULL, 'guest', NULL, 'available', NULL, NULL),
(308, 43, 0, '02811546', NULL, 'guest', NULL, 'available', NULL, NULL),
(309, 43, 0, '51135999', NULL, 'guest', NULL, 'available', NULL, NULL),
(310, 43, 0, '42635893', NULL, 'guest', NULL, 'available', NULL, NULL),
(318, 46, 0, 'F4E4C928', NULL, 'guest', NULL, 'available', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rfid_assignments`
--

CREATE TABLE `rfid_assignments` (
  `id` bigint UNSIGNED NOT NULL,
  `rfid_key_id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED DEFAULT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `reserved_from` date DEFAULT NULL,
  `reserved_to` date DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `released_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rfid_assignments`
--

INSERT INTO `rfid_assignments` (`id`, `rfid_key_id`, `booking_id`, `room_id`, `reserved_from`, `reserved_to`, `assigned_at`, `released_at`, `created_at`, `updated_at`) VALUES
(5, 318, 146, 176, '2026-03-11', '2026-03-12', '2026-03-11 13:07:48', NULL, '2026-03-11 13:07:48', '2026-03-11 13:07:48');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint UNSIGNED NOT NULL,
  `hotels_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `pricePerNight` int NOT NULL,
  `capacity` int NOT NULL,
  `basePrice` decimal(10,2) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `hotels_id`, `name`, `description`, `pricePerNight`, `capacity`, `basePrice`, `createdAt`) VALUES
(4, 2, 'Room 116', 'Praesentium vel id in aut maxime.', 266, 3, 179.28, '2026-02-24 07:16:46'),
(5, 2, 'Room 885', 'Rerum suscipit rerum vel ut ipsa.', 59, 3, 69.71, '2026-02-24 07:16:46'),
(6, 2, 'Room 974', 'Totam ad quam voluptatem nesciunt.', 239, 3, 89.46, '2026-02-24 07:16:46'),
(7, 2, 'Room 897', 'Et ut est vitae labore.', 207, 2, 160.81, '2026-02-24 07:16:46'),
(8, 3, 'Room 781', 'Omnis rerum dolores nostrum.', 248, 2, 205.99, '2026-02-24 07:16:46'),
(9, 3, 'Room 674', 'Fugit aut molestias et fugiat.', 79, 3, 180.96, '2026-02-24 07:16:46'),
(10, 3, 'Room 168', 'Ut error eum dicta distinctio.', 65, 3, 140.54, '2026-02-24 07:16:46'),
(11, 3, 'Room 558', 'Sint magnam ipsum numquam.', 87, 2, 288.53, '2026-02-24 07:16:46'),
(12, 3, 'Room 658', 'Non unde unde fuga quidem praesentium maxime accusamus numquam.', 176, 4, 286.05, '2026-02-24 07:16:46'),
(13, 4, 'Room 777', 'Consequatur aspernatur tenetur rerum.', 55, 1, 198.34, '2026-02-24 07:16:46'),
(14, 4, 'Room 100', 'Inventore iste aliquid aut porro est tempora dicta.', 281, 2, 212.54, '2026-02-24 07:16:46'),
(15, 4, 'Room 982', 'Temporibus et occaecati fugiat aliquam.', 167, 1, 268.91, '2026-02-24 07:16:46'),
(16, 4, 'Room 291', 'Et ab aliquam excepturi quaerat qui nam.', 61, 3, 262.67, '2026-02-24 07:16:46'),
(17, 5, 'Room 632', 'Cum et inventore at modi.', 186, 2, 195.06, '2026-02-24 07:16:46'),
(18, 5, 'Room 430', 'Quis nostrum omnis ut debitis corrupti.', 65, 4, 292.71, '2026-02-24 07:16:46'),
(19, 5, 'Room 947', 'Voluptatem quia aut et nihil eos omnis.', 194, 4, 150.79, '2026-02-24 07:16:46'),
(20, 5, 'Room 864', 'Porro possimus velit ab quas.', 135, 1, 261.69, '2026-02-24 07:16:46'),
(21, 5, 'Room 621', 'Optio temporibus fugit occaecati cum.', 217, 1, 271.56, '2026-02-24 07:16:46'),
(22, 6, 'Room 806', 'Quasi ipsam nemo eum ea quo.', 236, 3, 94.88, '2026-02-24 07:16:46'),
(23, 6, 'Room 969', 'Aut sit voluptas fugiat est a sit.', 100, 2, 92.08, '2026-02-24 07:16:46'),
(24, 6, 'Room 395', 'Corrupti qui molestiae fugit quo.', 205, 2, 249.11, '2026-02-24 07:16:46'),
(25, 7, 'Room 206', 'Reiciendis id autem eos vel voluptas et.', 131, 3, 186.77, '2026-02-24 07:16:46'),
(26, 7, 'Room 935', 'Numquam excepturi et sint aut vitae rerum laboriosam quia.', 90, 2, 87.29, '2026-02-24 07:16:46'),
(27, 7, 'Room 376', 'Quia modi quia aspernatur aut.', 139, 3, 259.62, '2026-02-24 07:16:46'),
(28, 7, 'Room 759', 'Inventore quam est sint illo veniam quidem.', 76, 3, 237.50, '2026-02-24 07:16:46'),
(29, 7, 'Room 311', 'Labore in sint nisi voluptas nobis et.', 62, 3, 168.81, '2026-02-24 07:16:46'),
(30, 8, 'Room 288', 'Blanditiis occaecati culpa doloribus inventore aut.', 276, 2, 143.24, '2026-02-24 07:16:46'),
(31, 8, 'Room 881', 'Id provident ut odit autem.', 159, 4, 205.85, '2026-02-24 07:16:46'),
(32, 8, 'Room 199', 'Illo ipsam aperiam aspernatur dicta illo.', 287, 2, 193.11, '2026-02-24 07:16:46'),
(33, 9, 'Room 174', 'Repellat non error id enim deleniti maiores sint.', 223, 1, 268.90, '2026-02-24 07:16:46'),
(34, 9, 'Room 495', 'Excepturi consequuntur sapiente sed.', 187, 1, 203.61, '2026-02-24 07:16:46'),
(35, 9, 'Room 880', 'Dolor commodi neque reiciendis iure.', 260, 3, 198.77, '2026-02-24 07:16:46'),
(36, 9, 'Room 105', 'Totam fugit sint ducimus mollitia in aut asperiores expedita.', 154, 2, 236.86, '2026-02-24 07:16:46'),
(37, 10, 'Room 537', 'Occaecati eius vitae enim consectetur.', 115, 3, 278.79, '2026-02-24 07:16:46'),
(38, 10, 'Room 870', 'Placeat et voluptatem ex saepe aut autem officiis.', 179, 1, 124.01, '2026-02-24 07:16:46'),
(39, 10, 'Room 121', 'Facere dolorem odit eveniet aut aliquam recusandae blanditiis.', 71, 2, 128.44, '2026-02-24 07:16:46'),
(40, 11, 'Room 636', 'Labore corporis dolor saepe odit.', 124, 4, 168.02, '2026-02-24 07:16:46'),
(41, 11, 'Room 825', 'Modi accusamus corrupti sed animi nulla et ut voluptas.', 280, 2, 212.00, '2026-02-24 07:16:46'),
(42, 11, 'Room 425', 'Voluptas enim quia rerum doloremque quasi laudantium.', 286, 2, 231.57, '2026-02-24 07:16:46'),
(43, 11, 'Room 862', 'Sint esse debitis officia ut nobis ratione sequi.', 202, 4, 51.49, '2026-02-24 07:16:46'),
(44, 12, 'Room 230', 'Voluptate tempore eos expedita doloremque.', 157, 1, 166.68, '2026-02-24 07:16:46'),
(45, 12, 'Room 724', 'Nihil non quae mollitia est dolorem.', 166, 4, 175.26, '2026-02-24 07:16:46'),
(46, 12, 'Room 238', 'Eligendi soluta ipsam necessitatibus necessitatibus nesciunt eos.', 204, 4, 277.74, '2026-02-24 07:16:46'),
(47, 12, 'Room 638', 'Temporibus provident adipisci nihil nulla et et eligendi.', 72, 2, 212.63, '2026-02-24 07:16:46'),
(48, 12, 'Room 185', 'Molestiae expedita voluptas quis error autem dolorem.', 76, 2, 211.01, '2026-02-24 07:16:46'),
(49, 13, 'Room 113', 'Dolore itaque eveniet sint ut qui sit vitae.', 217, 3, 62.01, '2026-02-24 07:16:46'),
(50, 13, 'Room 414', 'Est velit est aut qui voluptatibus sed nam.', 192, 1, 273.67, '2026-02-24 07:16:46'),
(51, 13, 'Room 134', 'Similique et corrupti quod cupiditate.', 241, 4, 285.16, '2026-02-24 07:16:46'),
(52, 13, 'Room 898', 'Quisquam qui officiis aut neque autem vero.', 177, 4, 256.25, '2026-02-24 07:16:46'),
(53, 13, 'Room 771', 'Consequuntur quasi expedita ut est facere ducimus.', 138, 3, 268.21, '2026-02-24 07:16:46'),
(54, 14, 'Room 435', 'Consequatur suscipit molestias aut tempora et.', 72, 2, 277.33, '2026-02-24 07:16:46'),
(55, 14, 'Room 260', 'Qui exercitationem nisi ut consequuntur vero a expedita est.', 56, 3, 173.88, '2026-02-24 07:16:46'),
(56, 14, 'Room 297', 'Et vitae ducimus sit consequatur.', 288, 3, 249.31, '2026-02-24 07:16:46'),
(57, 14, 'Room 390', 'Totam sapiente quam qui dolorem.', 158, 2, 274.29, '2026-02-24 07:16:46'),
(58, 15, 'Room 424', 'Enim iure iure aut animi praesentium neque quaerat.', 131, 4, 154.61, '2026-02-24 07:16:46'),
(59, 15, 'Room 510', 'Tempora voluptate in excepturi omnis ab provident.', 116, 4, 212.80, '2026-02-24 07:16:46'),
(60, 15, 'Room 889', 'Maxime tempore nihil at totam.', 199, 1, 210.63, '2026-02-24 07:16:46'),
(61, 16, 'Room 549', 'Sunt iste est quas.', 200, 4, 219.58, '2026-02-24 07:16:46'),
(62, 16, 'Room 517', 'Voluptatem aut est sapiente provident ut.', 88, 3, 253.34, '2026-02-24 07:16:46'),
(63, 16, 'Room 157', 'Dolor ad qui dicta ipsa expedita quae.', 114, 2, 109.10, '2026-02-24 07:16:46'),
(64, 17, 'Room 394', 'Quam numquam error facere excepturi atque ut et.', 164, 1, 70.38, '2026-02-24 07:16:46'),
(65, 17, 'Room 873', 'Exercitationem impedit molestias culpa repellat voluptatem saepe.', 118, 1, 221.92, '2026-02-24 07:16:46'),
(66, 17, 'Room 444', 'Ut cum facere recusandae molestiae.', 199, 4, 77.87, '2026-02-24 07:16:46'),
(67, 17, 'Room 326', 'Non eius officia occaecati unde enim atque quia.', 254, 1, 184.08, '2026-02-24 07:16:46'),
(68, 17, 'Room 743', 'Aut vel sit adipisci temporibus ut iste tempore.', 57, 2, 180.87, '2026-02-24 07:16:46'),
(69, 18, 'Room 472', 'Recusandae molestias quod quasi hic et id quia quo.', 76, 2, 282.35, '2026-02-24 07:16:46'),
(70, 18, 'Room 746', 'Non quis voluptas doloremque.', 259, 3, 148.92, '2026-02-24 07:16:46'),
(71, 18, 'Room 992', 'Eaque aliquam reiciendis itaque sed facere omnis.', 138, 3, 136.52, '2026-02-24 07:16:46'),
(72, 18, 'Room 572', 'Reprehenderit consectetur dolore voluptatem harum.', 71, 2, 148.64, '2026-02-24 07:16:46'),
(73, 19, 'Room 609', 'Incidunt atque soluta dignissimos.', 284, 4, 194.85, '2026-02-24 07:16:46'),
(74, 19, 'Room 132', 'Nam ullam perspiciatis quo et laudantium.', 241, 3, 271.99, '2026-02-24 07:16:46'),
(75, 19, 'Room 208', 'Impedit in sint atque et.', 179, 1, 263.81, '2026-02-24 07:16:46'),
(76, 20, 'Room 697', 'Consequatur sit nulla accusamus vel voluptates.', 164, 1, 118.64, '2026-02-24 07:16:46'),
(77, 20, 'Room 783', 'Ut quia delectus odio officiis.', 55, 1, 259.04, '2026-02-24 07:16:46'),
(78, 20, 'Room 371', 'Voluptatem consequatur nemo ipsam qui voluptates.', 98, 2, 156.72, '2026-02-24 07:16:46'),
(79, 20, 'Room 441', 'Minus praesentium enim iusto id laborum.', 187, 4, 77.28, '2026-02-24 07:16:46'),
(80, 20, 'Room 844', 'Ipsum nesciunt est rerum.', 257, 2, 200.05, '2026-02-24 07:16:46'),
(81, 21, 'Room 793', 'Vitae aliquid voluptatum a aut facere unde.', 86, 1, 76.24, '2026-02-24 07:16:46'),
(82, 21, 'Room 544', 'Aut odio nam qui explicabo.', 271, 3, 118.06, '2026-02-24 07:16:46'),
(83, 21, 'Room 812', 'Quia ut ad qui velit facere.', 179, 4, 240.61, '2026-02-24 07:16:46'),
(84, 21, 'Room 303', 'Repellendus quibusdam ex nihil quisquam quidem tempora vel.', 198, 1, 265.07, '2026-02-24 07:16:46'),
(85, 22, 'Room 703', 'Consectetur nobis rerum aut explicabo officiis rerum cumque.', 223, 1, 126.09, '2026-02-24 07:16:46'),
(86, 22, 'Room 722', 'Laborum aut placeat qui ut repellat reiciendis.', 96, 2, 286.79, '2026-02-24 07:16:46'),
(87, 22, 'Room 531', 'Fugiat delectus numquam tempore officia ut labore explicabo.', 59, 3, 173.11, '2026-02-24 07:16:46'),
(88, 23, 'Room 219', 'Molestiae ea aspernatur est.', 291, 3, 117.95, '2026-02-24 07:16:46'),
(89, 23, 'Room 312', 'Excepturi qui minima cumque nemo tempora iure.', 129, 1, 264.08, '2026-02-24 07:16:46'),
(90, 23, 'Room 789', 'Ut autem maiores et corporis omnis ad eum voluptatum.', 209, 3, 135.74, '2026-02-24 07:16:46'),
(91, 23, 'Room 358', 'Id corrupti quidem reprehenderit et autem.', 289, 1, 296.01, '2026-02-24 07:16:46'),
(92, 23, 'Room 264', 'Natus atque excepturi et asperiores accusamus eos vel.', 184, 4, 267.60, '2026-02-24 07:16:46'),
(93, 24, 'Room 389', 'Porro odio voluptas possimus adipisci.', 138, 1, 168.06, '2026-02-24 07:16:46'),
(94, 24, 'Room 571', 'Asperiores optio qui amet nisi minus sunt.', 297, 1, 286.50, '2026-02-24 07:16:46'),
(95, 24, 'Room 482', 'Quo omnis totam dolor adipisci accusamus.', 190, 3, 180.04, '2026-02-24 07:16:46'),
(96, 24, 'Room 277', 'Doloremque quod dolores vel.', 103, 4, 299.08, '2026-02-24 07:16:46'),
(97, 25, 'Room 552', 'Quod saepe sapiente sed inventore commodi ab voluptas.', 85, 1, 128.87, '2026-02-24 07:16:46'),
(98, 25, 'Room 228', 'Porro autem quos non nulla.', 146, 2, 176.56, '2026-02-24 07:16:46'),
(99, 25, 'Room 535', 'Aut voluptatum omnis fuga rem cupiditate modi odit quas.', 150, 1, 276.63, '2026-02-24 07:16:46'),
(100, 25, 'Room 672', 'Voluptas qui dolores dolores deleniti expedita.', 232, 3, 271.08, '2026-02-24 07:16:46'),
(101, 25, 'Room 240', 'Dolores voluptatem quia totam atque reiciendis dicta tempora.', 136, 3, 107.74, '2026-02-24 07:16:46'),
(102, 26, 'Room 819', 'Aspernatur quia quis vel consequuntur.', 182, 2, 254.49, '2026-02-24 07:16:46'),
(103, 26, 'Room 211', 'Aspernatur quia rem molestiae facere.', 179, 4, 50.95, '2026-02-24 07:16:46'),
(104, 26, 'Room 501', 'Ipsum possimus labore omnis accusamus quasi ullam sint.', 82, 4, 235.61, '2026-02-24 07:16:46'),
(105, 26, 'Room 196', 'Maxime repellendus dicta animi delectus.', 193, 3, 151.99, '2026-02-24 07:16:46'),
(106, 26, 'Room 874', 'Harum qui nihil aspernatur est eos vero.', 67, 4, 241.23, '2026-02-24 07:16:46'),
(107, 27, 'Room 623', 'Dicta natus vitae nihil fugit ab.', 191, 3, 290.19, '2026-02-24 07:16:46'),
(108, 27, 'Room 648', 'Recusandae consectetur debitis sed non illo necessitatibus unde.', 108, 2, 288.97, '2026-02-24 07:16:46'),
(109, 27, 'Room 232', 'Eligendi et mollitia quidem possimus.', 185, 3, 126.44, '2026-02-24 07:16:46'),
(110, 27, 'Room 155', 'Est non fugiat et nemo similique id tenetur.', 104, 1, 159.26, '2026-02-24 07:16:46'),
(111, 27, 'Room 110', 'Ea maxime reprehenderit quos beatae.', 226, 3, 166.17, '2026-02-24 07:16:46'),
(112, 28, 'Room 692', 'Sunt et et harum alias.', 164, 1, 116.00, '2026-02-24 07:16:46'),
(113, 28, 'Room 473', 'Debitis velit est error.', 197, 4, 252.42, '2026-02-24 07:16:46'),
(114, 28, 'Room 175', 'Dolore vero eaque soluta id.', 54, 2, 258.82, '2026-02-24 07:16:46'),
(115, 29, 'Room 458', 'Veritatis qui ad officia et quis occaecati.', 241, 2, 181.48, '2026-02-24 07:16:46'),
(116, 29, 'Room 481', 'Eos architecto ea eius odit quas a qui.', 238, 1, 159.43, '2026-02-24 07:16:46'),
(117, 29, 'Room 316', 'Totam nihil sit consequatur possimus totam.', 57, 1, 259.35, '2026-02-24 07:16:46'),
(118, 29, 'Room 605', 'Voluptatem sit ipsum et non molestiae ipsa repudiandae.', 198, 1, 293.75, '2026-02-24 07:16:46'),
(119, 30, 'Room 895', 'Dolores consequatur occaecati consequatur molestiae ut nulla vel.', 292, 2, 169.44, '2026-02-24 07:16:46'),
(120, 30, 'Room 629', 'Ut fugit consectetur unde nisi quis consectetur qui.', 241, 3, 158.26, '2026-02-24 07:16:46'),
(121, 30, 'Room 493', 'Libero autem cum eos sequi consectetur aspernatur laudantium sint.', 200, 1, 84.08, '2026-02-24 07:16:46'),
(122, 31, 'Room 614', 'Dicta optio molestiae ad vero voluptas blanditiis rerum.', 225, 4, 175.76, '2026-02-24 07:16:46'),
(123, 31, 'Room 560', 'Dolores sed laboriosam magni labore deleniti.', 250, 4, 233.30, '2026-02-24 07:16:46'),
(124, 31, 'Room 816', 'Porro dolorum asperiores voluptas molestias et minima.', 221, 1, 213.93, '2026-02-24 07:16:46'),
(125, 32, 'Room 256', 'Harum et soluta cupiditate et alias voluptatem quia.', 139, 4, 267.47, '2026-02-24 07:16:46'),
(126, 32, 'Room 689', 'Laborum beatae omnis est maxime recusandae est.', 204, 4, 190.64, '2026-02-24 07:16:46'),
(127, 32, 'Room 750', 'Veniam minus est quaerat pariatur vel est.', 89, 3, 162.24, '2026-02-24 07:16:46'),
(128, 33, 'Room 237', 'Magni nemo aperiam sint.', 200, 2, 180.47, '2026-02-24 07:16:46'),
(129, 33, 'Room 353', 'Corrupti qui et nisi in.', 228, 4, 109.92, '2026-02-24 07:16:46'),
(130, 33, 'Room 154', 'Sed fugiat culpa facere sed sit soluta exercitationem nam.', 77, 3, 167.47, '2026-02-24 07:16:46'),
(131, 34, 'Room 627', 'Exercitationem sed nemo quisquam quos magnam.', 228, 3, 277.00, '2026-02-24 07:16:46'),
(132, 34, 'Room 567', 'Sapiente id eius tempore dolores dicta.', 170, 1, 157.16, '2026-02-24 07:16:46'),
(133, 34, 'Room 894', 'Neque ex facere dolore quos.', 127, 2, 229.11, '2026-02-24 07:16:46'),
(134, 34, 'Room 282', 'Natus aut deleniti asperiores cupiditate excepturi magnam atque voluptatem.', 237, 4, 61.86, '2026-02-24 07:16:46'),
(135, 34, 'Room 122', 'Fugiat soluta laudantium asperiores explicabo eum.', 121, 2, 74.74, '2026-02-24 07:16:46'),
(136, 35, 'Room 920', 'Ut aliquam et modi sit.', 244, 1, 135.14, '2026-02-24 07:16:46'),
(137, 35, 'Room 412', 'Magni vel non quis unde sint.', 214, 3, 251.74, '2026-02-24 07:16:46'),
(138, 35, 'Room 688', 'Quis nesciunt aut omnis voluptatum et maxime ullam distinctio.', 194, 4, 241.20, '2026-02-24 07:16:46'),
(139, 35, 'Room 931', 'Rerum laboriosam maxime labore omnis et.', 255, 2, 257.93, '2026-02-24 07:16:46'),
(140, 35, 'Room 462', 'Consequatur quis harum est aut tempora non.', 68, 1, 233.95, '2026-02-24 07:16:46'),
(141, 36, 'Room 822', 'Id voluptatem sed culpa dolores minima.', 194, 2, 147.22, '2026-02-24 07:16:46'),
(142, 36, 'Room 378', 'Earum voluptate est deserunt iste.', 145, 4, 145.49, '2026-02-24 07:16:46'),
(143, 36, 'Room 149', 'Unde saepe non dolorum voluptas assumenda.', 78, 2, 248.45, '2026-02-24 07:16:46'),
(144, 36, 'Room 871', 'Fuga itaque incidunt placeat quis facere.', 87, 1, 222.96, '2026-02-24 07:16:46'),
(145, 36, 'Room 592', 'Explicabo nulla occaecati quae itaque eos.', 81, 2, 282.48, '2026-02-24 07:16:46'),
(146, 37, 'Room 140', 'Id illo libero aut.', 192, 2, 191.20, '2026-02-24 07:16:46'),
(147, 37, 'Room 821', 'Vel aut iste sit et quidem voluptatibus.', 61, 4, 271.12, '2026-02-24 07:16:46'),
(148, 37, 'Room 824', 'Maiores ut necessitatibus deleniti facere.', 76, 2, 158.91, '2026-02-24 07:16:46'),
(149, 38, 'Room 588', 'Hic eos harum minima est tempora quia suscipit voluptas.', 170, 2, 116.04, '2026-02-24 07:16:46'),
(150, 38, 'Room 397', 'Nemo et rerum quam unde dignissimos aut.', 81, 3, 83.95, '2026-02-24 07:16:46'),
(151, 38, 'Room 939', 'Ut quaerat animi amet repudiandae alias.', 130, 3, 124.72, '2026-02-24 07:16:46'),
(152, 38, 'Room 612', 'Vel sunt sint fuga eos reprehenderit distinctio porro.', 171, 2, 158.33, '2026-02-24 07:16:46'),
(153, 39, 'Room 503', 'Distinctio consequatur ut possimus consequatur et.', 185, 4, 112.25, '2026-02-24 07:16:46'),
(154, 39, 'Room 778', 'Cumque dolor non quia quos molestiae numquam nihil.', 108, 3, 211.30, '2026-02-24 07:16:46'),
(155, 39, 'Room 646', 'Fugiat minima voluptatem totam corrupti aliquid dicta sint qui.', 55, 2, 256.64, '2026-02-24 07:16:46'),
(156, 40, 'Room 975', 'Deserunt eligendi incidunt quo dolor.', 183, 4, 68.26, '2026-02-24 07:16:46'),
(157, 40, 'Room 999', 'Reprehenderit aut ut fugit facere.', 177, 1, 234.61, '2026-02-24 07:16:46'),
(158, 40, 'Room 740', 'Qui et architecto consequatur enim nemo maxime.', 201, 1, 149.73, '2026-02-24 07:16:46'),
(159, 40, 'Room 287', 'Reiciendis est provident sunt eligendi reiciendis.', 161, 1, 92.06, '2026-02-24 07:16:46'),
(160, 41, 'Room 649', 'Enim adipisci ratione aut quis qui.', 228, 2, 182.38, '2026-02-24 07:16:46'),
(161, 41, 'Room 755', 'Optio non consequatur debitis voluptates nihil.', 201, 1, 118.08, '2026-02-24 07:16:46'),
(162, 41, 'Room 678', 'Soluta sit est qui nemo quo porro.', 97, 2, 275.64, '2026-02-24 07:16:46'),
(163, 41, 'Room 995', 'Nostrum rem est iure sit.', 74, 4, 154.13, '2026-02-24 07:16:46'),
(164, 42, 'Room 553', 'Magnam deserunt eaque iusto eius et harum.', 75, 3, 197.37, '2026-02-24 07:16:46'),
(165, 42, 'Room 253', 'Perferendis delectus quisquam similique sed veritatis quae et sunt.', 286, 1, 63.62, '2026-02-24 07:16:46'),
(166, 42, 'Room 133', 'Sit est inventore ducimus ea harum facere commodi est.', 62, 3, 288.88, '2026-02-24 07:16:46'),
(167, 42, 'Room 448', 'Quia beatae neque pariatur soluta ipsa.', 244, 1, 132.04, '2026-02-24 07:16:46'),
(168, 42, 'Room 296', 'Quia cum magnam esse dolor.', 170, 1, 291.84, '2026-02-24 07:16:46'),
(169, 43, 'Room 652', 'Ex natus temporibus ipsam enim enim adipisci possimus.', 84, 1, 90.37, '2026-02-24 07:16:46'),
(170, 43, 'Room 809', 'Perferendis atque ea quis ut rerum itaque omnis.', 215, 4, 202.60, '2026-02-24 07:16:46'),
(171, 43, 'Room 176', 'Consequatur ratione soluta omnis et et quasi natus.', 170, 3, 154.90, '2026-02-24 07:16:46'),
(172, 43, 'Room 201', 'Recusandae aut est sint aut autem aspernatur.', 278, 2, 183.46, '2026-02-24 07:16:46'),
(176, 46, 'Room 101', 'Room 101', 120, 1, 125.00, '2026-03-11 13:06:02'),
(177, 46, 'Room 102', 'Room 102', 125, 2, 100.00, '2026-03-11 13:06:23');

-- --------------------------------------------------------

--
-- Table structure for table `roomtagrelation`
--

CREATE TABLE `roomtagrelation` (
  `rooms_id` bigint UNSIGNED NOT NULL,
  `serviceTags_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` int NOT NULL,
  `hotels_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `hotels_id`) VALUES
(1, 'perferendis sed', 'Et repudiandae doloremque dolor ducimus veniam quis.', 44, 2),
(2, 'sed sit', 'Voluptates odit est inventore qui.', 79, 2),
(3, 'velit aspernatur', 'Aliquam iste ducimus dolor dolore est quos.', 67, 2),
(4, 'et voluptate', 'Amet voluptatem voluptas tempore vel eaque temporibus laudantium.', 37, 2),
(5, 'enim voluptatem', 'Quo pariatur doloremque asperiores quia placeat illum atque fuga.', 40, 3),
(6, 'reprehenderit fugit', 'Quae sed totam voluptatem maiores consectetur sed.', 47, 3),
(7, 'et quis', 'Autem rerum eius iure eveniet impedit distinctio in.', 18, 3),
(8, 'aut molestiae', 'Aliquid non qui est ut unde vel.', 35, 4),
(9, 'et expedita', 'Unde dolorem officia numquam nihil officia in.', 93, 4),
(10, 'ipsam dolores', 'Exercitationem fugiat et eius unde repudiandae.', 42, 4),
(11, 'dolorem accusamus', 'Ut numquam ipsum atque dicta dolorem cumque occaecati.', 93, 5),
(12, 'tempora voluptatem', 'Ipsam at dicta nihil ad.', 12, 5),
(13, 'ut amet', 'Voluptatem est est inventore sit sed reprehenderit vitae.', 99, 5),
(14, 'corrupti iure', 'Et doloribus optio repellat necessitatibus necessitatibus magnam sint.', 90, 6),
(15, 'impedit inventore', 'Aut qui omnis autem nemo.', 48, 6),
(16, 'ipsa sed', 'Et incidunt occaecati laboriosam.', 56, 6),
(17, 'nesciunt qui', 'Velit omnis unde id aperiam dolore sint ut.', 74, 7),
(18, 'doloribus est', 'Odio quo nulla nihil.', 21, 7),
(19, 'aliquid beatae', 'Et et ut est dolorem officia voluptate.', 63, 7),
(20, 'ratione earum', 'Ratione esse cupiditate voluptates et dignissimos aut dolor.', 17, 7),
(21, 'optio iure', 'Voluptatem libero rerum et aut ratione et blanditiis.', 26, 8),
(22, 'suscipit exercitationem', 'Voluptas sint cum odio enim facere est mollitia.', 51, 8),
(23, 'aliquam aliquid', 'Velit vitae id qui consectetur quam atque mollitia.', 99, 9),
(24, 'voluptas voluptatem', 'Deleniti atque saepe et amet qui et qui.', 40, 9),
(25, 'aut maxime', 'Consequuntur officia voluptates porro omnis architecto praesentium.', 50, 10),
(26, 'provident sapiente', 'Ipsum quae id itaque cum eaque consequuntur.', 23, 10),
(27, 'qui in', 'Qui perferendis vel voluptas ipsa.', 58, 11),
(28, 'quisquam velit', 'Et iure fugit magni et.', 98, 11),
(29, 'ut sint', 'Labore optio praesentium optio illo sed temporibus error.', 11, 12),
(30, 'est laudantium', 'Qui repellendus quas quidem ex autem ea in.', 69, 12),
(31, 'odio error', 'Natus nesciunt quisquam rerum dicta quia.', 19, 12),
(32, 'natus dicta', 'Est culpa doloribus libero deserunt quia itaque consequatur nobis.', 44, 13),
(33, 'illo rem', 'Quaerat ad cupiditate occaecati voluptas.', 17, 13),
(34, 'voluptates cum', 'Praesentium amet nobis quo reiciendis.', 58, 14),
(35, 'nostrum repellendus', 'Qui itaque et quisquam perspiciatis exercitationem quasi.', 73, 14),
(36, 'perferendis velit', 'Atque optio excepturi sunt.', 83, 15),
(37, 'quidem delectus', 'Hic ab perspiciatis modi corporis sed quo porro voluptatem.', 52, 15),
(38, 'neque ab', 'Quae sint magnam beatae.', 61, 16),
(39, 'eos fugiat', 'Et quos sapiente quo sit consequatur.', 77, 16),
(40, 'qui modi', 'Est veniam non quidem dolores fugiat sed qui.', 13, 16),
(41, 'adipisci nisi', 'Est esse laborum voluptatem provident cum et amet.', 91, 17),
(42, 'et eum', 'Vero voluptatem quia optio commodi culpa culpa nulla.', 91, 17),
(43, 'non quaerat', 'Et consequatur ut incidunt eligendi quisquam omnis.', 11, 17),
(44, 'veniam ut', 'Et ut voluptatem quia.', 24, 18),
(45, 'voluptatem vitae', 'Beatae exercitationem sint qui nesciunt repellat assumenda.', 59, 18),
(46, 'dignissimos voluptatum', 'Est rerum est vero amet vel.', 15, 19),
(47, 'porro molestiae', 'In reprehenderit ut cum illum illum pariatur et.', 42, 19),
(48, 'ut qui', 'Est modi voluptatem voluptatibus voluptatibus sint quibusdam qui recusandae.', 46, 20),
(49, 'eum dolor', 'Incidunt architecto temporibus numquam hic doloremque reprehenderit.', 61, 20),
(50, 'alias occaecati', 'Eveniet excepturi aperiam nesciunt est dolor quis id.', 39, 21),
(51, 'ut consectetur', 'Amet sit soluta illum quia explicabo laudantium facilis modi.', 34, 21),
(52, 'aut quisquam', 'Repudiandae voluptas et cupiditate quia similique est.', 75, 21),
(53, 'aliquam ea', 'Nostrum quia porro tenetur debitis aut non.', 80, 21),
(54, 'quia repudiandae', 'Itaque eligendi itaque et ut voluptatem ut officiis.', 81, 22),
(55, 'dolore non', 'Ipsam ut sit in.', 42, 22),
(56, 'laborum quia', 'Possimus vero inventore adipisci maxime et.', 34, 22),
(57, 'eos voluptatem', 'Delectus mollitia rerum et nam sit eligendi quae.', 56, 23),
(58, 'odio architecto', 'Dolorem sequi temporibus nam quia earum necessitatibus.', 21, 23),
(59, 'est eos', 'Laboriosam quaerat porro ea a culpa non incidunt veniam.', 50, 23),
(60, 'ipsam ullam', 'Est beatae ut aperiam quo dignissimos vel.', 43, 23),
(61, 'reprehenderit numquam', 'Dolores eum voluptatem non id quasi fugit ullam.', 19, 24),
(62, 'non praesentium', 'Deserunt labore voluptatem ut eaque ut.', 46, 24),
(63, 'architecto velit', 'Odit ratione dolorem quia.', 46, 25),
(64, 'illo nostrum', 'Molestias sit reiciendis et libero voluptas quis omnis qui.', 99, 25),
(65, 'nostrum corrupti', 'Tempore dolorum vero voluptas voluptatum voluptates cum.', 85, 26),
(66, 'id voluptatem', 'Cupiditate itaque illum sed tempore cupiditate rerum impedit voluptates.', 41, 26),
(67, 'quam eaque', 'Sed totam laboriosam commodi tempore at.', 76, 26),
(68, 'et in', 'Sed laudantium ut neque corporis.', 83, 26),
(69, 'enim excepturi', 'Aut blanditiis et aspernatur repudiandae maiores aperiam facilis.', 70, 27),
(70, 'dicta blanditiis', 'Explicabo ut explicabo nisi et omnis.', 88, 27),
(71, 'corrupti delectus', 'Consectetur est sequi doloribus quis autem non necessitatibus aperiam.', 97, 28),
(72, 'aliquam saepe', 'Ipsa aperiam quia a sint animi.', 84, 28),
(73, 'dolor sed', 'Est alias aut quibusdam ullam tempore rem quibusdam.', 96, 29),
(74, 'non dignissimos', 'Delectus fugit ipsam soluta est sed.', 73, 29),
(75, 'aperiam ut', 'Reprehenderit eos repudiandae tempore sit voluptas excepturi.', 67, 30),
(76, 'pariatur nostrum', 'Inventore molestiae mollitia sit consequatur optio dolorem.', 86, 30),
(77, 'voluptatem ipsum', 'Eos nemo quo labore.', 21, 31),
(78, 'incidunt unde', 'Nostrum ipsam voluptas voluptatibus voluptates aut quibusdam nobis.', 31, 31),
(79, 'blanditiis nostrum', 'Ratione in omnis officiis ratione ut dolor laborum et.', 72, 32),
(80, 'laudantium dolor', 'Blanditiis qui omnis perspiciatis.', 39, 32),
(81, 'est harum', 'Optio velit nulla voluptatem assumenda laboriosam eum sit.', 13, 32),
(82, 'omnis alias', 'Repudiandae eaque nemo ullam.', 83, 33),
(83, 'doloribus deleniti', 'Sit possimus illo assumenda autem et.', 99, 33),
(84, 'necessitatibus nisi', 'Et voluptatem qui quaerat saepe.', 47, 33),
(85, 'nihil assumenda', 'Et corporis vero placeat esse aut quas reiciendis sit.', 85, 33),
(86, 'rem ipsa', 'Ut minima eveniet non est magnam ea similique.', 78, 34),
(87, 'et repellendus', 'Amet quidem possimus maiores voluptas dicta cumque.', 77, 34),
(88, 'eligendi sed', 'Quod atque a laudantium nam possimus consequatur quasi totam.', 98, 34),
(89, 'quia asperiores', 'Fugiat unde corrupti et.', 27, 34),
(90, 'dolore aperiam', 'Consequatur et consequuntur eos facere voluptatem quibusdam consequatur.', 22, 35),
(91, 'molestiae reiciendis', 'Pariatur qui temporibus maiores necessitatibus veritatis ad.', 38, 35),
(92, 'blanditiis sed', 'Magni excepturi sequi impedit maiores fugiat eum et.', 84, 36),
(93, 'ex odit', 'Molestias ut repellendus ab dolores ut similique fuga sed.', 13, 36),
(94, 'sed deleniti', 'Quod velit velit unde quos qui maxime iusto.', 68, 36),
(95, 'excepturi numquam', 'Consequatur eum alias omnis et eveniet possimus nam.', 77, 37),
(96, 'omnis est', 'Illum debitis neque sint corrupti quod.', 18, 37),
(97, 'sunt nihil', 'Et earum ratione repellat explicabo fugiat.', 30, 37),
(98, 'exercitationem possimus', 'Magnam vero tenetur rerum libero exercitationem fugit est ipsa.', 13, 37),
(99, 'et asperiores', 'Corporis assumenda iste dolor maxime quis architecto qui eius.', 41, 38),
(100, 'voluptatum ea', 'Dolore ex laudantium tenetur iste quia voluptas est.', 85, 38),
(101, 'pariatur consequuntur', 'Aperiam repudiandae enim veritatis velit.', 63, 39),
(102, 'soluta dolores', 'Est et cumque beatae aut.', 95, 39),
(103, 'autem explicabo', 'Quis commodi eaque et exercitationem.', 25, 39),
(104, 'laudantium reprehenderit', 'Aut dolorem quasi quaerat officia.', 60, 40),
(105, 'ut quidem', 'Eius deserunt cumque amet dolores molestiae accusantium.', 19, 40),
(106, 'rerum voluptatem', 'Cupiditate et fugiat consectetur modi nesciunt optio architecto.', 50, 41),
(107, 'voluptates ut', 'Ipsum consequuntur quas voluptates dolorem id.', 66, 41),
(108, 'et recusandae', 'Accusantium ut quo sed sed dicta id.', 72, 41),
(109, 'et laudantium', 'Consectetur ipsa est iusto exercitationem.', 10, 41),
(110, 'laboriosam quae', 'Eum qui earum minus aut.', 66, 42),
(111, 'occaecati rerum', 'Et est aspernatur beatae accusantium.', 67, 42),
(112, 'omnis vel', 'Et ut et omnis aperiam sed.', 38, 43),
(113, 'id ea', 'Inventore minima pariatur similique sapiente ea.', 80, 43),
(114, 'consectetur sunt', 'Quo dolore voluptates voluptatem qui et consequatur.', 50, 43);

-- --------------------------------------------------------

--
-- Table structure for table `servicesrelation`
--

CREATE TABLE `servicesrelation` (
  `services_id` bigint UNSIGNED NOT NULL,
  `bookings_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servicetags`
--

CREATE TABLE `servicetags` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `servicetags`
--

INSERT INTO `servicetags` (`id`, `user_id`, `name`) VALUES
(1, NULL, 'WiFi'),
(2, NULL, 'TV'),
(3, NULL, 'Légkondicionáló'),
(4, NULL, 'Büfé'),
(5, NULL, 'Parkoló');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `session_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `two_factor_recovery_tokens`
--

CREATE TABLE `two_factor_recovery_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `eu_tax_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isVerified` tinyint(1) NOT NULL DEFAULT '0',
  `email_verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `tax_number`, `bank_account`, `eu_tax_number`, `two_factor_secret`, `two_factor_enabled`, `password`, `isVerified`, `email_verification_token`, `email_verified_at`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Lyla Lindgren', 'fadel.sammy@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$oi.iJ1REE6Q8jmi2UQyUUOnHT0ev27so7026cZTfzuoF9twSlwUGq', 0, NULL, NULL, 'user', '2026-02-24 07:16:32', '2026-02-24 07:16:32'),
(2, 'Dr. Bobby Treutel', 'orn.elouise@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$A2Gda3tsrpQlawAaTzu0zu83USjK.oi6B2cnjGU0h7BnSJ7iT1i6q', 0, NULL, NULL, 'user', '2026-02-24 07:16:32', '2026-02-24 07:16:32'),
(3, 'Dr. Ernestina Ruecker IV', 'senger.william@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$opYhXwW3pqT.kVwSXb6tme3b4weUaS9/Sd61F2rWQL4wBUJhYALgy', 0, NULL, NULL, 'user', '2026-02-24 07:16:33', '2026-02-24 07:16:33'),
(4, 'Mrs. Josefina Zieme', 'becker.ambrose@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$4lmpZOu2HJ6dVNG/wNYmO.lJ61BQtOYMe04ydin9w7oVpy6bvcH12', 0, NULL, NULL, 'user', '2026-02-24 07:16:33', '2026-02-24 07:16:33'),
(5, 'Prof. Jennifer Labadie Sr.', 'zchristiansen@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$uGgDHw8eyaGexi/7pqbB.uRbfSsWxy9uRINNA8IIPDZHuDNY4jzdi', 0, NULL, NULL, 'user', '2026-02-24 07:16:33', '2026-02-24 07:16:33'),
(6, 'Isom Schultz IV', 'tweimann@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$vH1HAseatS9l6X2nSDhK2.KRWfo2goYlZVbB2x8o4.JnolHROL.8K', 0, NULL, NULL, 'user', '2026-02-24 07:16:33', '2026-02-24 07:16:33'),
(7, 'Dameon Morar', 'jaleel.bailey@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$/h0pC5K4JdCvSemWTnr4IOrQiCF86fLmKWiYIFaCPyeibybU5/oO2', 0, NULL, NULL, 'user', '2026-02-24 07:16:33', '2026-02-24 07:16:33'),
(8, 'Dr. Elmo Sauer', 'kcremin@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$jTY15lk2RunMx7juZHMlM.SyDQw8xvJPgasREroNhHmlrVUzv98Qi', 0, NULL, NULL, 'user', '2026-02-24 07:16:34', '2026-02-24 07:16:34'),
(9, 'Annamae Dicki', 'meaghan.brakus@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$E8MxIzJq2NGN3rpL8Aflf.Xc7cs73EenUIRty/87q4.9/Mps9Mpju', 0, NULL, NULL, 'user', '2026-02-24 07:16:34', '2026-02-24 07:16:34'),
(10, 'Arvid Pfannerstill', 'miller.dexter@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$3lUXYDzhrFmsX781BSdzeu5S0Sa9BcqGmPwbUBdpARf8XKvdla2sK', 0, NULL, NULL, 'user', '2026-02-24 07:16:34', '2026-02-24 07:16:34'),
(11, 'Prof. Ansel Christiansen Sr.', 'htillman@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$cYBtQLP8X6lS1y3Va8M9o.Uwhr/Hk3T1aJ/65gP/kiHJkztQRraQy', 0, NULL, NULL, 'user', '2026-02-24 07:16:34', '2026-02-24 07:16:34'),
(12, 'Miss Burdette Pollich Sr.', 'lillian48@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$RUlucZOGgmAP9A80GTKu5OKhD6D3I1x.P7MWihy3O1mvD1PXGknJ2', 0, NULL, NULL, 'user', '2026-02-24 07:16:34', '2026-02-24 07:16:34'),
(13, 'Garnett Lockman', 'halle97@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$6Gjt6PGMyu7FJWMPJHaFLeiV5iA0KZHiLHPPCT8CTvgLJEQw4NmTW', 0, NULL, NULL, 'user', '2026-02-24 07:16:35', '2026-02-24 07:16:35'),
(14, 'Angelo Lynch', 'crona.helmer@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$WWM8qcG1tZR1G/VDTHjccO.iC3JRX4TQ9WtwNXJjsZ2VaagSBJWIe', 0, NULL, NULL, 'user', '2026-02-24 07:16:35', '2026-02-24 07:16:35'),
(15, 'Jammie O\'Keefe', 'willis.jacobson@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$8jz5urrO.KdP9lIlrBQ2/ecy/OYBl2aXjCx.Z5K8/1XNfTmEnZFFe', 0, NULL, NULL, 'user', '2026-02-24 07:16:35', '2026-02-24 07:16:35'),
(16, 'Prof. Aaron Glover MD', 'juwan57@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$FKkwDAAyjTWH0foCR9EwA.xkwrwYOcgd3z06nc1mE7Q4Uv4reWcFW', 0, NULL, NULL, 'user', '2026-02-24 07:16:35', '2026-02-24 07:16:35'),
(17, 'Prof. Kylee Johnson', 'kellen.bayer@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$NecM4YCIj9ej0EQlm6AHeOD21d921e/GH7gOznZM8uLSEcdPaByy.', 0, NULL, NULL, 'user', '2026-02-24 07:16:35', '2026-02-24 07:16:35'),
(18, 'Ms. Brigitte Lockman', 'bjohns@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$l0dpT3yNEtq1j7f0C/OvgOCVH7IymhnSzyQqZgRm5YkcaHAOYNSIW', 0, NULL, NULL, 'user', '2026-02-24 07:16:35', '2026-02-24 07:16:35'),
(19, 'Mrs. Alyson Dooley', 'vidal.mann@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$Xl/JM8zz6RJO.cd4dZUGKeVdtzrScJBRvAx5kFxjldwn2I7cKQAHO', 0, NULL, NULL, 'user', '2026-02-24 07:16:36', '2026-02-24 07:16:36'),
(20, 'Zella Russel', 'keenan15@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$sMteI166FY9mNrREMilmie98CvdywrgSouO5y7uTpA9WkJhMtRGIO', 0, NULL, NULL, 'user', '2026-02-24 07:16:36', '2026-02-24 07:16:36'),
(21, 'Warren Zieme', 'gerald87@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$tMqL0VHKjueEFJvYgbnlR.WOjf02kFBhgpAYw4PvY8kzY3I03CZ9a', 0, NULL, NULL, 'user', '2026-02-24 07:16:36', '2026-02-24 07:16:36'),
(22, 'Gideon Ward', 'tzulauf@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$g4qzxTFXNGrAIQ5K9UBN8OfJGqFkAzqqyuDxPY3CHr52PTh0YbCZu', 0, NULL, NULL, 'user', '2026-02-24 07:16:36', '2026-02-24 07:16:36'),
(23, 'Arnaldo Daniel', 'susanna.zulauf@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$wgM2fseUg6xg4lG0z8uameeda7V8d8J.jG57O.FrI76/r5ECww6Dm', 0, NULL, NULL, 'user', '2026-02-24 07:16:36', '2026-02-24 07:16:36'),
(24, 'Percival Swaniawski', 'legros.eino@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$6HQtHfTqLca7zJP92Gfs4OfmcL3HCPzb23LlJy43.Vehgr6eOQjtS', 0, NULL, NULL, 'user', '2026-02-24 07:16:37', '2026-02-24 07:16:37'),
(25, 'Delaney Willms', 'block.erling@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$rcHj7hdN9c26B4T2pNfcguDZWusErCE6B3fbuEcMmMB7pk5.PeWby', 0, NULL, NULL, 'user', '2026-02-24 07:16:37', '2026-02-24 07:16:37'),
(26, 'Vivienne Kovacek', 'kemmer.sallie@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$iOgS.H.2CGiO0cqUshTNEO9z1ojSQMQJQqs.DH/vy9khRPUOhLwhi', 0, NULL, NULL, 'user', '2026-02-24 07:16:37', '2026-02-24 07:16:37'),
(27, 'Yessenia Dach', 'brandt.hirthe@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$mx26MdznA0/cnDNY68y4NOKB3Nqizbt2X53PDvzL46zC1TcE3jiX6', 0, NULL, NULL, 'user', '2026-02-24 07:16:37', '2026-02-24 07:16:37'),
(28, 'Johnpaul Sauer', 'dietrich.robin@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$js2E9LGICwqrqPVkEi/tQujCXruuJdZ2WZZIu2dWohGItcqZBFMoi', 0, NULL, NULL, 'user', '2026-02-24 07:16:37', '2026-02-24 07:16:37'),
(29, 'Dr. Einar Waelchi V', 'gustave.senger@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$sxjXOTnR5lx/GpwyrFN/HeeyYccVJ9qFuosd8AFI2wIgm.afCY2O6', 0, NULL, NULL, 'user', '2026-02-24 07:16:38', '2026-02-24 07:16:38'),
(30, 'Fleta Prosacco IV', 'carmine87@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$QzhgaVXd88MuL0tnT3EYSOrlfecAxepNhPT4G3SKQfdCSwAb24722', 0, NULL, NULL, 'user', '2026-02-24 07:16:38', '2026-02-24 07:16:38'),
(31, 'Rosemary Wyman', 'otis01@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$XFKXOmakbtw9nMKPhuPBUuxA9t5VdROIbXllZ6kGQHp5cDwHWu9ii', 0, NULL, NULL, 'user', '2026-02-24 07:16:38', '2026-02-24 07:16:38'),
(32, 'Brendon Spinka', 'kwill@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$5ZHrTUq.HEDTqYPkIuSfGOsbU3krfNgpRo1sS6um.uEWGFrgK7vw6', 0, NULL, NULL, 'user', '2026-02-24 07:16:38', '2026-02-24 07:16:38'),
(33, 'Aaron Larkin', 'kling.larry@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$lF5J5y/ovv65o2mDGSGU3OkR4MW4C7c6Kr54X2lXkLrsyycUG/yZe', 0, NULL, NULL, 'user', '2026-02-24 07:16:38', '2026-02-24 07:16:38'),
(34, 'Noe Kunde', 'langosh.jeffery@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$oO372bJb/v9SL0Ocz/b8munxGsh9uyt1jzyBsvH3oJfX9DMJ5Hf/e', 0, NULL, NULL, 'user', '2026-02-24 07:16:39', '2026-02-24 07:16:39'),
(35, 'Dejah Sauer', 'ddickens@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$Sp/mVt7Wg86YT/MZX4CDn.wMOsNN98yW5IZnq1z88W757a9Gl2CHS', 0, NULL, NULL, 'user', '2026-02-24 07:16:39', '2026-02-24 07:16:39'),
(36, 'Dr. Javon Keeling DVM', 'dietrich.karianne@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$D0Ox9jtETxCb1KD0LIT7z.NOTAdY6erF7nO08d4ju16FHYADPMzuu', 0, NULL, NULL, 'user', '2026-02-24 07:16:39', '2026-02-24 07:16:39'),
(37, 'Mrs. Elisha Lindgren', 'ddach@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$AmCWAaAgN6nHg7Loub8theYjP5iMWoRQy7OkX7XEokYUE8n0HvJcm', 0, NULL, NULL, 'user', '2026-02-24 07:16:39', '2026-02-24 07:16:39'),
(38, 'Terry Davis', 'tbotsford@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$BlegrHtwhMRm/51F89SIc.WHFDgbmdNSSqEE3G4lGpj4NFl3mWHSS', 0, NULL, NULL, 'user', '2026-02-24 07:16:39', '2026-02-24 07:16:39'),
(39, 'Dr. Janet Parisian', 'johnson.ethel@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$hjgiuJcu9aVBwCT4N6xvIuuv/mziccjtx6HVcdbq34UrH1Go85ys.', 0, NULL, NULL, 'user', '2026-02-24 07:16:39', '2026-02-24 07:16:39'),
(40, 'Dr. Bria Lockman', 'willms.theresia@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$zi8F7Marh9aJAjGnST3xjOQnR8ADsnbg0Pm6wxzBKgE9I3gk/q.Oq', 0, NULL, NULL, 'user', '2026-02-24 07:16:40', '2026-02-24 07:16:40'),
(41, 'Michele Mayert', 'eino.corwin@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$2.axaCjtcN2cZaPFtGZ5UeH02N4HFHm2gi54Ok.yRk5EMB0L1AhY2', 0, NULL, NULL, 'user', '2026-02-24 07:16:40', '2026-02-24 07:16:40'),
(42, 'Mr. Milford Weimann Jr.', 'heaven.shanahan@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$Hs2tK36vezgnwzYZBBwjWurEyRYtLxYSwgE2JsNc51rUQ5umnv2DS', 0, NULL, NULL, 'user', '2026-02-24 07:16:40', '2026-02-24 07:16:40'),
(43, 'Dr. Billy Predovic', 'kovacek.hettie@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$3RZ1mfHjRSWUfqtBhAowI.shSyjabY0uvaDlJNXlvtfuiBLtEbkT6', 0, NULL, NULL, 'user', '2026-02-24 07:16:40', '2026-02-24 07:16:40'),
(44, 'Ebba Hodkiewicz', 'iwyman@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$XM3pGhT2Wnyy27dtSYvI6OMY4w2DhTr0EHgjInl3PcgPOuTCjD8Ba', 0, NULL, NULL, 'user', '2026-02-24 07:16:40', '2026-02-24 07:16:40'),
(45, 'Lauriane Hessel', 'francisca38@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$mwUMK3DudA4jCUd0kDnsZ.hB4kPjKoL92tmbQLDpUpFPWv8Cy1Tk2', 0, NULL, NULL, 'user', '2026-02-24 07:16:41', '2026-02-24 07:16:41'),
(46, 'Evie Mraz', 'iva.osinski@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$HWqHK6Aarl.FwAavjKa0q.zA5vTbftaaX8EA/ouoiJJ4HWnqrHZRu', 0, NULL, NULL, 'user', '2026-02-24 07:16:41', '2026-02-24 07:16:41'),
(47, 'Mrs. Jessica Ebert DVM', 'jhuels@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$l/ZRJ6wDbuNHrUDZ7sZo5et8Tbuyrg/FTnCQxkX/YenOmu/VnWHt2', 0, NULL, NULL, 'user', '2026-02-24 07:16:41', '2026-02-24 07:16:41'),
(48, 'Autumn Donnelly IV', 'ransom.powlowski@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$1Vv8gnZPWwq1sq6lzO.6wOct3XeMH9vRXqU.FngmTtrKdU1W.dlk2', 0, NULL, NULL, 'user', '2026-02-24 07:16:41', '2026-02-24 07:16:41'),
(49, 'Miss Nola Dicki', 'xgerhold@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$U.ssFxQmK4TAkQIN1AECMeXVtkC9QJLcV0dPvnLqSYfE.vSUqUWkK', 0, NULL, NULL, 'user', '2026-02-24 07:16:41', '2026-02-24 07:16:41'),
(50, 'Erik Metz', 'graham.albertha@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$1pCPsETtq3EkcdlBc073LuDYS063n5E9pXGc1sc8rQOmNl6lM1Q3u', 0, NULL, NULL, 'user', '2026-02-24 07:16:42', '2026-02-24 07:16:42'),
(51, 'Prof. Makenna Hane V', 'woberbrunner@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$4OkAdp6arfqs3WZcePzYhuHmeQc81uAVlroO5HMt6T.ja9HWdPL4G', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:42', '2026-02-24 07:16:42'),
(52, 'Marco Anderson', 'torp.zelma@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$Kz89GzMoGlBkoP2cm5Eql.kuhj/ISIooVTIGRQEWxjF/VKBILCnDa', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:42', '2026-02-24 07:16:42'),
(53, 'Jules Adams III', 'emelia.kessler@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$H9TLz3NtY5o6xe8C82MCg.xg2jd4BpPGnrI7ZuzVm7tC7oA2EvmB6', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:42', '2026-02-24 07:16:42'),
(54, 'Camila Johns', 'eroberts@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$lGAct3h65Y9mIBW9dSK8JeuAa4mu3r.kPyLc7p17YYUTN/lKPEifG', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:42', '2026-02-24 07:16:42'),
(55, 'Desmond Kilback', 'murazik.monroe@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$ISliBWLl/xVM2.fiv6YEZOwj3KyNasztqYYo6qh5h70qp4QkPsP0O', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:43', '2026-02-24 07:16:43'),
(56, 'Rebeca McDermott', 'satterfield.kenneth@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$weQnlUL3FyDgWed4Kj5RU.SnhaT4r4DypDe0PEJ5FnS.HxZDv4jGS', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:43', '2026-02-24 07:16:43'),
(57, 'Cornell Williamson Jr.', 'hdamore@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$aVNAHsy2.BZTIz6K9DuVkucV1DSMAtRJ/ol7rKq..vpbyytu.kXBC', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:43', '2026-02-24 07:16:43'),
(58, 'Carleton Cartwright V', 'deven09@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$iO4kxBMxZVH.PRcU.PWl4.b2jRn.yGPm50fgQvWWN8xH/noQQ6hfe', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:43', '2026-02-24 07:16:43'),
(59, 'Natasha Bergstrom', 'heidenreich.nona@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$5EFp535KT5Ig408h6imfO.PTGJ6yxdkxW6zJElRncQ6PQr3uXvDPy', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:43', '2026-02-24 07:16:43'),
(60, 'Vallie Hackett DVM', 'xreilly@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$JkrrtaFHq9vC1ITAhmycnelK/nF179SO0SPJ18q5XJNoxkaHOTki.', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:43', '2026-02-24 07:16:43'),
(61, 'Lester Deckow Sr.', 'judson.littel@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$xLPamEAxWE3/YixWedCiUu/hYK.Mv3Q5Ep0ydkay9N/xMvD/DP.p2', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:44', '2026-02-24 07:16:44'),
(62, 'Genoveva Beier Sr.', 'clang@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$xPrYghiEYqhWJlQ0jbWKj.bO7UtroDDw16ZqRwg4IZdtpr4iJLWxu', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:44', '2026-02-24 07:16:44'),
(63, 'Margaretta Reichert', 'anita52@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$SAnI.2wJ3wFu.ipk9eOMWex3GwiqysFAITFpyXhhWsVbM98Gxwtdi', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:44', '2026-02-24 07:16:44'),
(64, 'Ilene Bins', 'bradly21@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$onRXRT0yNG0Ook5kdKqmJONaKTfwG8lwJIIRyp.wSmcwqxJMeUGDq', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:44', '2026-02-24 07:16:44'),
(65, 'Rey Brown', 'nharber@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$47rh3ILRfnbbGrYPK3FW6.AFfDXKDI131/IydFx0bB8Zw0yQKPnJy', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:44', '2026-02-24 07:16:44'),
(66, 'Zane Gulgowski Sr.', 'terrence95@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$EVzscflyEiVetMGTZB2F0eiyUJUl8rOhvXicMLE.1WR5kx4hKjCvy', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:45', '2026-02-24 07:16:45'),
(67, 'Eva Effertz', 'swift.gunnar@example.org', NULL, NULL, NULL, NULL, 0, '$2y$12$1XPkz1OOaT7fKCc.TaDj9Oj/1dQMpnptrVE29ttXVHuEazCwwab5u', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:45', '2026-02-24 07:16:45'),
(68, 'Ms. Madie Satterfield III', 'london.muller@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$rG3NW.AcJ5oBbztwzZg2OOIi9S6wVkQy.vZv4ckd7bw2dI62Ve8em', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:45', '2026-02-24 07:16:45'),
(69, 'Mr. Omer Ernser', 'okeebler@example.net', NULL, NULL, NULL, NULL, 0, '$2y$12$LI33fnTp2ZkTuvkCKVTe/eUbpHFUNdMCQkvDNs1ACGCiUScUIBPfi', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:45', '2026-02-24 07:16:45'),
(70, 'Elena Yundt', 'funk.madelynn@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$eIQlccoynLUXWmD4wwXx7eVzXcDkXhEgV6J0BIEikw52iY1DBrVhO', 0, NULL, NULL, 'hotel', '2026-02-24 07:16:45', '2026-02-24 07:16:45'),
(71, 'Monostori Márk', 'monostorimark05@gmail.com', NULL, NULL, NULL, NULL, 0, '$2y$12$55VCMlLY7d1evcDoeMRN6uz.K4fT5TrQe2qtLmd0P6C8u1XzTqdWu', 1, NULL, '2026-03-11 11:16:35', 'user', '2026-02-24 07:16:46', '2026-03-11 11:16:35'),
(72, 'Admin', 'optikartofficial@gmail.com', NULL, NULL, NULL, 'ZGZNQU5MYJCTSU3F', 1, '$2y$12$mU7IHR98EGVASr9l0Vxqqur5ADKVI9psLdjNuJz6DXP9poQYuY8Ya', 1, NULL, NULL, 'super_admin', '2026-02-24 07:16:46', '2026-03-11 11:34:03'),
(75, 'Kerti Kutya', 'user@example.com', NULL, NULL, NULL, NULL, 0, '$2y$12$HcrQekbmR6WO4nNjPn3THekcpUXKXo4l48uJP/7glzV9ZKu0gx7ia', 0, 'q4OgL8Oy9fNj1TUVoyAHsc55EjY54kdcMAsTjHH0ZVQVfrr0sKlRfgOIASVIMLQB', NULL, 'user', '2026-02-24 09:32:28', '2026-02-24 09:32:28'),
(76, 'Szabó Máté', 'szabo.mate@diak.szbi-pg.hu', NULL, NULL, NULL, NULL, 0, '$2y$12$uy0xHQ36qHLUIp1HcPrcJuWULfBh0rkUe/A8b6vaBpDDGo8IrBAHu', 1, NULL, '2026-03-11 12:41:09', 'user', '2026-03-11 12:40:56', '2026-03-11 12:41:09'),
(77, 'Szabó Máté', 'szabomate403@gmail.com', NULL, NULL, NULL, '2XUOZIEA7EYHQQGX', 1, '$2y$12$8obFKhL7MsTtbZDmXdCT1u43Ysk7JbwAr2LNc/bCfkKpo2i7m7xm2', 1, NULL, '2026-03-11 13:04:18', 'hotel', '2026-03-11 13:04:08', '2026-03-11 13:04:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_users_id_foreign` (`users_id`),
  ADD KEY `bookings_hotels_id_foreign` (`hotels_id`);

--
-- Indexes for table `bookingsrelation`
--
ALTER TABLE `bookingsrelation`
  ADD PRIMARY KEY (`booking_id`,`rooms_id`),
  ADD KEY `bookingsrelation_rooms_id_foreign` (`rooms_id`);

--
-- Indexes for table `booking_invoice_details`
--
ALTER TABLE `booking_invoice_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_invoice_details_booking_id_foreign` (`booking_id`);

--
-- Indexes for table `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_payments_booking_id_foreign` (`booking_id`),
  ADD KEY `booking_payments_confirmed_by_user_id_foreign` (`confirmed_by_user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `devices_token_unique` (`token`),
  ADD KEY `devices_hotels_id_foreign` (`hotels_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guests_bookings_id_foreign` (`bookings_id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotels_user_id_foreign` (`user_id`);

--
-- Indexes for table `hoteltagrelation`
--
ALTER TABLE `hoteltagrelation`
  ADD PRIMARY KEY (`hotels_id`,`serviceTags_id`),
  ADD KEY `hoteltagrelation_servicetags_id_foreign` (`serviceTags_id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `imagesrelation`
--
ALTER TABLE `imagesrelation`
  ADD PRIMARY KEY (`images_id`,`rooms_id`),
  ADD KEY `imagesrelation_rooms_id_foreign` (`rooms_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD UNIQUE KEY `invoices_payment_token_unique` (`payment_token`),
  ADD KEY `invoices_booking_id_foreign` (`booking_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `password_reset_tokens_token_unique` (`token`),
  ADD KEY `password_reset_tokens_user_id_foreign` (`user_id`);

--
-- Indexes for table `pending_handshakes`
--
ALTER TABLE `pending_handshakes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pending_handshakes_hotel_id_foreign` (`hotel_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_booking_id_foreign` (`booking_id`);

--
-- Indexes for table `rfidkeyconnection`
--
ALTER TABLE `rfidkeyconnection`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rfidkeyconnection_rfidkeys_id_foreign` (`rfidKeys_id`),
  ADD KEY `rfidkeyconnection_rooms_id_foreign` (`rooms_id`);

--
-- Indexes for table `rfidkeys`
--
ALTER TABLE `rfidkeys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfidkeys_rfidkey_unique` (`rfidKey`),
  ADD KEY `rfidkeys_hotels_id_foreign` (`hotels_id`);

--
-- Indexes for table `rfid_assignments`
--
ALTER TABLE `rfid_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rfid_assignments_booking_id_foreign` (`booking_id`),
  ADD KEY `rfid_assignments_room_id_foreign` (`room_id`),
  ADD KEY `rfid_assignments_rfid_key_id_released_at_index` (`rfid_key_id`,`released_at`),
  ADD KEY `rfid_assignments_rfid_key_id_reserved_from_reserved_to_index` (`rfid_key_id`,`reserved_from`,`reserved_to`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rooms_hotels_id_foreign` (`hotels_id`);

--
-- Indexes for table `roomtagrelation`
--
ALTER TABLE `roomtagrelation`
  ADD PRIMARY KEY (`rooms_id`,`serviceTags_id`),
  ADD KEY `roomtagrelation_servicetags_id_foreign` (`serviceTags_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `services_hotels_id_foreign` (`hotels_id`);

--
-- Indexes for table `servicesrelation`
--
ALTER TABLE `servicesrelation`
  ADD PRIMARY KEY (`services_id`,`bookings_id`),
  ADD KEY `servicesrelation_bookings_id_foreign` (`bookings_id`);

--
-- Indexes for table `servicetags`
--
ALTER TABLE `servicetags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servicetags_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sessions_session_token_unique` (`session_token`),
  ADD KEY `sessions_user_id_foreign` (`user_id`);

--
-- Indexes for table `two_factor_recovery_tokens`
--
ALTER TABLE `two_factor_recovery_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `two_factor_recovery_tokens_token_hash_unique` (`token_hash`),
  ADD KEY `two_factor_recovery_tokens_user_id_expires_at_index` (`user_id`,`expires_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `booking_invoice_details`
--
ALTER TABLE `booking_invoice_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `booking_payments`
--
ALTER TABLE `booking_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=327;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pending_handshakes`
--
ALTER TABLE `pending_handshakes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rfidkeyconnection`
--
ALTER TABLE `rfidkeyconnection`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rfidkeys`
--
ALTER TABLE `rfidkeys`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=319;

--
-- AUTO_INCREMENT for table `rfid_assignments`
--
ALTER TABLE `rfid_assignments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `servicetags`
--
ALTER TABLE `servicetags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `two_factor_recovery_tokens`
--
ALTER TABLE `two_factor_recovery_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_hotels_id_foreign` FOREIGN KEY (`hotels_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookingsrelation`
--
ALTER TABLE `bookingsrelation`
  ADD CONSTRAINT `bookingsrelation_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookingsrelation_rooms_id_foreign` FOREIGN KEY (`rooms_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_invoice_details`
--
ALTER TABLE `booking_invoice_details`
  ADD CONSTRAINT `booking_invoice_details_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD CONSTRAINT `booking_payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_payments_confirmed_by_user_id_foreign` FOREIGN KEY (`confirmed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_hotels_id_foreign` FOREIGN KEY (`hotels_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guests`
--
ALTER TABLE `guests`
  ADD CONSTRAINT `guests_bookings_id_foreign` FOREIGN KEY (`bookings_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hotels`
--
ALTER TABLE `hotels`
  ADD CONSTRAINT `hotels_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hoteltagrelation`
--
ALTER TABLE `hoteltagrelation`
  ADD CONSTRAINT `hoteltagrelation_hotels_id_foreign` FOREIGN KEY (`hotels_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hoteltagrelation_servicetags_id_foreign` FOREIGN KEY (`serviceTags_id`) REFERENCES `servicetags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `imagesrelation`
--
ALTER TABLE `imagesrelation`
  ADD CONSTRAINT `imagesrelation_images_id_foreign` FOREIGN KEY (`images_id`) REFERENCES `images` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `imagesrelation_rooms_id_foreign` FOREIGN KEY (`rooms_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pending_handshakes`
--
ALTER TABLE `pending_handshakes`
  ADD CONSTRAINT `pending_handshakes_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rfidkeyconnection`
--
ALTER TABLE `rfidkeyconnection`
  ADD CONSTRAINT `rfidkeyconnection_rfidkeys_id_foreign` FOREIGN KEY (`rfidKeys_id`) REFERENCES `rfidkeys` (`rfidKey`) ON DELETE CASCADE,
  ADD CONSTRAINT `rfidkeyconnection_rooms_id_foreign` FOREIGN KEY (`rooms_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rfidkeys`
--
ALTER TABLE `rfidkeys`
  ADD CONSTRAINT `rfidkeys_hotels_id_foreign` FOREIGN KEY (`hotels_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rfid_assignments`
--
ALTER TABLE `rfid_assignments`
  ADD CONSTRAINT `rfid_assignments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rfid_assignments_rfid_key_id_foreign` FOREIGN KEY (`rfid_key_id`) REFERENCES `rfidkeys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rfid_assignments_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_hotels_id_foreign` FOREIGN KEY (`hotels_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `roomtagrelation`
--
ALTER TABLE `roomtagrelation`
  ADD CONSTRAINT `roomtagrelation_rooms_id_foreign` FOREIGN KEY (`rooms_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `roomtagrelation_servicetags_id_foreign` FOREIGN KEY (`serviceTags_id`) REFERENCES `servicetags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_hotels_id_foreign` FOREIGN KEY (`hotels_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `servicesrelation`
--
ALTER TABLE `servicesrelation`
  ADD CONSTRAINT `servicesrelation_bookings_id_foreign` FOREIGN KEY (`bookings_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `servicesrelation_services_id_foreign` FOREIGN KEY (`services_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `servicetags`
--
ALTER TABLE `servicetags`
  ADD CONSTRAINT `servicetags_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `two_factor_recovery_tokens`
--
ALTER TABLE `two_factor_recovery_tokens`
  ADD CONSTRAINT `two_factor_recovery_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
