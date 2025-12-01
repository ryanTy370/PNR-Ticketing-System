-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 02:07 PM
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
-- Database: `bicol_express_online_ticketing_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `AdminID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `AdminRole` enum('Super Admin','Operator','Support') NOT NULL DEFAULT 'Operator',
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`AdminID`, `UserID`, `AdminRole`, `Created_at`) VALUES
(2, 30, 'Super Admin', '2025-04-01 13:19:49');

-- --------------------------------------------------------

--
-- Stand-in structure for view `bookingdetails`
-- (See below for the actual view)
--
CREATE TABLE `bookingdetails` (
`TicketID` int(11)
,`BookDate` timestamp
,`DateTravel` datetime
,`TicketStatus` enum('Reserved','Confirmed','Cancelled','Pending Cancellation')
,`DepartureTime` datetime
,`ArrivalTime` datetime
,`DepartureStation` varchar(199)
,`ArrivalStation` varchar(199)
,`Amount` decimal(10,2)
,`PaymentStatus` enum('Pending','Completed','Reserved')
,`ModeOfPayment` enum('Over-the-counter','Gcash','Bank Transfer','Beep Card')
,`FleetType` enum('Economy','Reclining Aircon','Family Sleeper','Executive Sleeper','Regular')
,`TrainName` varchar(299)
,`TrainNumber` int(11)
,`UserID` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `cancellation_requests`
--

CREATE TABLE `cancellation_requests` (
  `RequestID` int(11) NOT NULL,
  `TicketID` int(11) NOT NULL,
  `RequestDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `CancellationReason` text NOT NULL,
  `Status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `AdminID` int(11) DEFAULT NULL,
  `AdminResponse` text DEFAULT NULL,
  `AdminActionDate` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancellation_requests`
--

INSERT INTO `cancellation_requests` (`RequestID`, `TicketID`, `RequestDate`, `CancellationReason`, `Status`, `AdminID`, `AdminResponse`, `AdminActionDate`) VALUES
(1, 99, '2025-04-19 12:20:46', 'davrweasdadadada', 'Approved', 2, '', '2025-04-19 12:54:44'),
(2, 98, '2025-04-19 12:26:31', 'adadasdasd', 'Approved', 2, 'okay na', '2025-04-19 13:31:27'),
(3, 97, '2025-04-19 12:47:23', 'adasdasdadsadadasdad', 'Approved', 2, '', '2025-04-20 11:01:53'),
(4, 96, '2025-04-19 13:21:09', 'adaasfvedvwRQWERVWERWV', 'Pending', NULL, NULL, NULL);

--
-- Triggers `cancellation_requests`
--
DELIMITER $$
CREATE TRIGGER `after_cancellation_request_update` AFTER UPDATE ON `cancellation_requests` FOR EACH ROW BEGIN
    IF NEW.Status != OLD.Status THEN
        INSERT INTO `systemlogs` (`AdminID`, `Action`, `Timestamp`)
        VALUES (
            NEW.AdminID,
            CASE 
                WHEN NEW.Status = 'Approved' THEN CONCAT('Approved cancellation request for ticket ID: ', NEW.TicketID)
                WHEN NEW.Status = 'Rejected' THEN CONCAT('Rejected cancellation request for ticket ID: ', NEW.TicketID)
            END,
            CURRENT_TIMESTAMP
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `LocationID` int(11) NOT NULL,
  `City` varchar(199) NOT NULL,
  `Province` varchar(199) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`LocationID`, `City`, `Province`) VALUES
(5, 'Legazpi', 'Albay'),
(6, 'Naga', 'Camarines Sur'),
(9, 'Legazpi', 'Albay'),
(10, 'Naga', 'Camarines Sur'),
(12, 'Tabaco', 'Albay'),
(13, 'Iriga', 'Camarines Sur'),
(17, 'Daraga', 'Albay'),
(18, 'Ligao', 'Albay'),
(19, 'Oas', 'Albay'),
(20, 'Polangui', 'Albay'),
(21, 'Bato', 'Camarines Sur'),
(22, 'Baao', 'Camarines Sur'),
(23, 'Pili', 'Camarines Sur');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PaymentID` int(11) NOT NULL,
  `TicketID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentStatus` enum('Pending','Completed','Reserved') NOT NULL DEFAULT 'Pending',
  `DateOfPayment` timestamp NOT NULL DEFAULT current_timestamp(),
  `ModeOfPayment` enum('Over-the-counter','Gcash','Bank Transfer','Beep Card') NOT NULL DEFAULT 'Over-the-counter'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`PaymentID`, `TicketID`, `Amount`, `PaymentStatus`, `DateOfPayment`, `ModeOfPayment`) VALUES
(14, 96, 120.00, 'Completed', '2025-04-18 04:36:00', 'Bank Transfer'),
(15, 97, 120.00, 'Completed', '2025-04-18 04:51:24', 'Gcash'),
(16, 98, 110.00, 'Completed', '2025-04-18 07:26:10', 'Gcash'),
(17, 99, 200.00, 'Completed', '2025-04-19 11:07:05', 'Gcash'),
(18, 100, 110.00, 'Pending', '2025-04-20 11:37:19', 'Over-the-counter'),
(19, 101, 200.00, 'Completed', '2025-04-20 12:48:12', 'Gcash');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `RouteID` int(11) NOT NULL,
  `DepartureStationID` int(11) NOT NULL,
  `ArrivalStationID` int(11) NOT NULL,
  `Distance` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`RouteID`, `DepartureStationID`, `ArrivalStationID`, `Distance`) VALUES
(33, 9, 10, 101.00),
(34, 10, 9, 100.00),
(57, 9, 31, 20.00),
(58, 32, 27, 69.00),
(59, 10, 30, 50.00),
(60, 28, 32, 66.00),
(61, 9, 23, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `ScheduleID` int(11) NOT NULL,
  `TrainID` int(11) NOT NULL,
  `RouteID` int(11) NOT NULL,
  `DepartureTime` datetime NOT NULL,
  `ArrivalTime` datetime NOT NULL,
  `Fare` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`ScheduleID`, `TrainID`, `RouteID`, `DepartureTime`, `ArrivalTime`, `Fare`) VALUES
(48, 3, 33, '2025-04-20 18:00:00', '2025-04-20 20:30:00', 120.00),
(68, 3, 33, '2025-04-19 02:23:00', '2025-04-19 03:23:00', 200.00),
(81, 3, 33, '2025-04-22 17:17:00', '2025-04-22 18:17:00', 200.00),
(82, 16, 58, '2025-04-26 20:19:00', '2025-04-26 21:19:00', 220.00),
(83, 16, 58, '2025-04-25 20:19:00', '2025-04-25 21:19:00', 120.00),
(84, 4, 33, '2025-04-29 18:32:00', '2025-04-29 19:32:00', 110.00),
(85, 4, 57, '2025-04-28 12:36:00', '2025-04-28 19:36:00', 100.00),
(86, 17, 60, '2025-04-30 17:38:00', '2025-04-30 18:38:00', 121.00),
(87, 4, 33, '2025-04-21 02:01:00', '2025-04-21 04:01:00', 110.00),
(88, 3, 33, '2025-05-01 07:25:00', '2025-05-01 08:25:00', 90.00),
(89, 3, 33, '2025-05-20 16:17:00', '2025-05-20 18:17:00', 110.00),
(90, 3, 33, '2025-05-19 16:17:00', '2025-05-19 18:17:00', 110.00),
(91, 3, 33, '2025-05-22 16:17:00', '2025-05-22 18:17:00', 110.00),
(92, 3, 33, '2025-05-21 16:17:00', '2025-05-21 18:17:00', 110.00),
(94, 3, 61, '2025-05-22 01:44:00', '2025-05-22 14:44:00', 90.00),
(95, 3, 33, '2025-05-16 07:32:00', '2025-05-16 08:32:00', 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `systemlogs`
--

CREATE TABLE `systemlogs` (
  `LogID` int(11) NOT NULL,
  `AdminID` int(11) NOT NULL,
  `Action` text NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `systemlogs`
--

INSERT INTO `systemlogs` (`LogID`, `AdminID`, `Action`, `Timestamp`) VALUES
(1, 2, 'Deleted user: John Doe (admin@example.com) (ID: 33)', '2025-04-04 10:08:35'),
(2, 2, 'Updated user: Leon Kennedy (ID: 30)', '2025-04-04 10:30:25'),
(3, 2, 'Updated user: Leon Kennedy (ID: 30)', '2025-04-04 10:30:35'),
(4, 2, 'Updated user: Leon Kennedy (ID: 30)', '2025-04-04 10:30:43'),
(5, 2, 'Updated user: z z (ID: 22)', '2025-04-04 10:31:12'),
(6, 2, 'Updated user: z z (ID: 22)', '2025-04-04 10:31:23'),
(7, 2, 'Updated user: z z (ID: 22)', '2025-04-04 10:31:33'),
(8, 2, 'Updated user: Leon Kennedy (ID: 30)', '2025-04-04 10:31:43'),
(9, 2, 'Updated user: Leon Kennedy (ID: 30)', '2025-04-04 10:34:16'),
(10, 2, 'Updated user: z z (ID: 22), Status: Inactive', '2025-04-04 10:45:58'),
(11, 2, 'Updated user: z z (ID: 22), Status: Inactive', '2025-04-04 10:46:00'),
(12, 2, 'Updated user: z z (ID: 22), Status: Inactive', '2025-04-04 10:46:10'),
(13, 2, 'Updated user: c c (ID: 8), Status: Inactive', '2025-04-04 10:46:24'),
(14, 2, 'Updated user: z z (ID: 22), Status: Inactive', '2025-04-04 10:47:49'),
(15, 2, 'Updated user: z z (ID: 22), Status: Inactive', '2025-04-04 10:47:52'),
(16, 2, 'Updated user: z z (ID: 22), Status: Inactive', '2025-04-04 10:47:55'),
(17, 2, 'Updated user: c c (ID: 8), Status: Inactive', '2025-04-04 10:49:54'),
(18, 2, 'Updated user: c c (ID: 8), Status: Inactive', '2025-04-04 10:49:57'),
(19, 2, 'Updated user: arjay cabrillas (ID: 1), Status: Inactive', '2025-04-04 10:56:39'),
(20, 2, 'Updated user: arjay cabrillas (ID: 1), Status: Inactive', '2025-04-08 01:36:28'),
(21, 2, 'Updated user: arjay cabrillas (ID: 1), Status: Inactive', '2025-04-08 01:36:32'),
(22, 2, 'Added new user: mm mm (ID: 34)', '2025-04-08 01:58:14'),
(23, 2, 'Deleted booking ID: 53', '2025-04-08 02:03:15'),
(24, 2, 'Deleted booking ID: 52', '2025-04-08 02:03:26'),
(25, 2, 'Updated train ID: 9', '2025-04-08 02:35:38'),
(26, 2, 'Updated train ID: 9', '2025-04-08 02:35:43'),
(27, 2, 'Updated train ID: 9', '2025-04-08 02:35:48'),
(28, 2, 'Deleted train ID: 15', '2025-04-08 03:02:56'),
(29, 2, 'Updated train ID: 14', '2025-04-08 03:03:39'),
(30, 2, 'Deleted user: z z (ID: 22) with 0 tickets and their related payments', '2025-04-08 03:04:20'),
(31, 2, 'Deleted booking ID: 51', '2025-04-09 01:22:10'),
(32, 2, 'Deleted booking ID: 45', '2025-04-09 01:22:16'),
(33, 2, 'Deleted booking ID: 36', '2025-04-09 01:22:25'),
(34, 2, 'Deleted booking ID: 35', '2025-04-09 01:22:30'),
(35, 2, 'Deleted booking ID: 37', '2025-04-09 01:22:34'),
(36, 2, 'Added new station: dxcz', '2025-04-09 09:34:22'),
(37, 2, 'Added new station: ccv', '2025-04-09 09:37:22'),
(38, 2, 'Deleted station: ccv (ID: 12)', '2025-04-09 09:37:32'),
(39, 2, 'Added new station: xvx', '2025-04-09 09:37:38'),
(40, 2, 'Updated train ID: 12', '2025-04-10 00:14:56'),
(41, 2, 'Viewed booking details for PNR-000066', '2025-04-10 00:24:43'),
(42, 2, 'Viewed booking details for PNR-000064', '2025-04-10 00:24:57'),
(43, 2, 'Viewed booking details for PNR-000064', '2025-04-10 00:25:01'),
(44, 2, 'Viewed booking details for PNR-000064', '2025-04-10 00:26:15'),
(45, 2, 'Viewed booking details for PNR-000066', '2025-04-10 00:27:32'),
(46, 2, 'Viewed booking details for PNR-000066', '2025-04-10 00:28:52'),
(47, 2, 'Viewed booking details for PNR-000066', '2025-04-10 00:28:53'),
(48, 2, 'Viewed booking details for PNR-000066', '2025-04-10 00:30:44'),
(49, 2, 'Viewed booking details for PNR-000064', '2025-04-10 00:30:55'),
(50, 2, 'Viewed booking details for PNR-000062', '2025-04-10 00:30:59'),
(51, 2, 'Viewed booking details for PNR-000066', '2025-04-10 00:41:31'),
(52, 2, 'Viewed booking details for PNR-000064', '2025-04-10 00:41:36'),
(53, 2, 'Viewed booking details for PNR-000063', '2025-04-10 00:41:41'),
(54, 2, 'Viewed booking details for PNR-000058', '2025-04-10 00:41:46'),
(55, 2, 'Updated booking details for PNR-000066', '2025-04-10 00:42:00'),
(56, 2, 'Updated booking details for PNR-000066', '2025-04-10 00:42:08'),
(57, 2, 'Viewed booking details for PNR-000066', '2025-04-10 00:42:13'),
(58, 2, 'Viewed booking details for PNR-000066', '2025-04-10 00:43:11'),
(59, 2, 'Viewed booking details for PNR-000064', '2025-04-10 00:43:15'),
(60, 2, 'Updated train ID: 12', '2025-04-10 00:43:32'),
(61, 2, 'Updated train ID: 9', '2025-04-10 01:44:33'),
(62, 2, 'Viewed booking details for PNR-000066', '2025-04-11 04:21:38'),
(63, 2, 'Viewed booking details for PNR-000066', '2025-04-11 04:21:52'),
(64, 2, 'Viewed booking details for PNR-000064', '2025-04-11 04:21:53'),
(65, 2, 'Viewed booking details for PNR-000066', '2025-04-11 04:22:06'),
(66, 2, 'Viewed booking details for PNR-000050', '2025-04-11 04:26:39'),
(67, 2, 'Viewed booking details for PNR-000066', '2025-04-11 04:36:12'),
(68, 2, 'Viewed booking details for PNR-000066', '2025-04-11 04:48:31'),
(69, 2, 'Viewed booking details for PNR-000066', '2025-04-11 04:55:57'),
(70, 2, 'Viewed booking details for PNR-000066', '2025-04-11 04:55:57'),
(71, 2, 'Viewed booking details for PNR-000066', '2025-04-11 04:55:58'),
(72, 2, 'Viewed booking details for PNR-000066', '2025-04-11 04:56:00'),
(73, 2, 'Viewed booking details for PNR-000064', '2025-04-11 04:56:11'),
(74, 2, 'Viewed booking details for PNR-000063', '2025-04-11 04:56:14'),
(75, 2, 'Viewed booking details for PNR-000066', '2025-04-11 04:56:17'),
(76, 2, 'Viewed booking details for PNR-000066', '2025-04-11 05:04:20'),
(77, 2, 'Viewed booking details for PNR-000066', '2025-04-11 05:04:20'),
(78, 2, 'Viewed booking details for PNR-000066', '2025-04-11 05:04:26'),
(79, 2, 'Viewed booking details for PNR-000066', '2025-04-11 05:04:29'),
(80, 2, 'Viewed booking details for PNR-000066', '2025-04-11 05:44:32'),
(81, 2, 'Viewed booking details for PNR-000066', '2025-04-11 05:44:33'),
(82, 2, 'Viewed booking details for PNR-000066', '2025-04-11 05:44:36'),
(83, 2, 'Viewed booking details for PNR-000066', '2025-04-11 05:44:46'),
(84, 2, 'Updated user: c c (ID: 8), Status: Inactive', '2025-04-11 23:11:44'),
(85, 2, 'Viewed booking details for PNR-000067', '2025-04-11 23:11:58'),
(86, 2, 'Viewed booking details for PNR-000063', '2025-04-11 23:12:14'),
(87, 2, 'Updated schedule ID: 15', '2025-04-11 23:18:34'),
(88, 2, 'Updated schedule ID: 15', '2025-04-11 23:18:41'),
(89, 2, 'Updated schedule ID: 14', '2025-04-11 23:19:01'),
(90, 2, 'Updated schedule ID: 15', '2025-04-11 23:20:30'),
(91, 2, 'Viewed booking details for PNR-000068', '2025-04-11 23:30:06'),
(92, 2, 'Updated schedule ID: 15', '2025-04-11 23:40:47'),
(93, 2, 'Updated schedule ID: 15', '2025-04-11 23:47:38'),
(94, 2, 'Updated schedule ID: 15', '2025-04-12 00:16:05'),
(95, 2, 'Updated schedule ID: 15', '2025-04-12 00:34:04'),
(96, 2, 'Viewed booking details for PNR-000079', '2025-04-12 07:09:39'),
(97, 2, 'Updated booking details for PNR-000079', '2025-04-12 07:09:53'),
(98, 2, 'Viewed booking details for PNR-000079', '2025-04-12 07:09:57'),
(99, 2, 'Viewed booking details for PNR-000079', '2025-04-12 07:15:04'),
(100, 2, 'Viewed booking details for PNR-000080', '2025-04-12 07:15:33'),
(101, 2, 'Viewed booking details for PNR-000080', '2025-04-12 07:17:15'),
(102, 2, 'Viewed booking details for PNR-000081', '2025-04-12 07:17:43'),
(103, 2, 'Deleted booking ID: 81', '2025-04-12 07:17:59'),
(104, 2, 'Viewed booking details for PNR-000073', '2025-04-12 07:19:16'),
(105, 2, 'Viewed booking details for PNR-000080', '2025-04-12 07:19:18'),
(106, 2, 'Viewed booking details for PNR-000080', '2025-04-12 07:22:34'),
(107, 2, 'Viewed booking details for PNR-000080', '2025-04-12 07:23:24'),
(108, 2, 'Viewed booking details for PNR-000082', '2025-04-12 07:23:59'),
(109, 2, 'Viewed booking details for PNR-000082', '2025-04-12 07:24:15'),
(110, 2, 'Updated schedule ID: 15', '2025-04-12 08:18:00'),
(111, 2, 'Deleted train ID: 14', '2025-04-12 09:39:11'),
(112, 2, 'Deleted user: ARJAY Cabrillas (ID: 9) with 0 tickets and their related payments', '2025-04-12 09:45:16'),
(113, 2, 'Deleted user: aa cc (ID: 13) with 0 tickets and their related payments', '2025-04-12 09:45:19'),
(114, 2, 'Updated user: Claire Redfield (ID: 34), Status: Active', '2025-04-12 09:45:52'),
(115, 2, 'Deleted train ID: 9', '2025-04-12 09:51:06'),
(116, 2, 'Deleted train ID: 10', '2025-04-12 09:51:09'),
(117, 2, 'Deleted train ID: 12', '2025-04-12 09:51:13'),
(118, 2, 'Deleted train ID: 13', '2025-04-12 09:51:16'),
(119, 2, 'Updated train ID: 16', '2025-04-12 09:51:27'),
(120, 2, 'Updated train ID: 17', '2025-04-12 09:51:34'),
(121, 2, 'Updated train ID: 4', '2025-04-12 09:51:41'),
(122, 2, 'Updated train ID: 3', '2025-04-12 10:49:43'),
(123, 2, 'Added new station: Kapantawan', '2025-04-13 10:57:35'),
(124, 2, 'Deleted station: Kapantawan (ID: 20)', '2025-04-13 11:07:33'),
(125, 2, 'Added new station: Washington Drive', '2025-04-13 11:10:30'),
(126, 2, 'Added new station: Bagtang', '2025-04-13 11:11:54'),
(127, 2, 'Added new station: Daraga', '2025-04-13 11:12:07'),
(128, 2, 'Added new station: Travesia', '2025-04-13 11:12:21'),
(129, 2, 'Added new station: Ligao', '2025-04-13 11:12:35'),
(130, 2, 'Added new station: Oas', '2025-04-13 11:12:44'),
(131, 2, 'Added new station: Polangui', '2025-04-13 11:13:03'),
(132, 2, 'Added new station: Matacon', '2025-04-13 11:13:16'),
(133, 2, 'Added new station: Bato', '2025-04-13 11:13:33'),
(134, 2, 'Added new station: Lourdes (Old)', '2025-04-13 11:13:51'),
(135, 2, 'Added new station: Baao', '2025-04-13 11:14:04'),
(136, 2, 'Added new station: Pili', '2025-04-13 11:14:15'),
(137, 2, 'Added new station: test', '2025-04-13 11:17:14'),
(138, 2, 'Deleted station: test (ID: 33)', '2025-04-13 11:17:19'),
(139, 2, 'Added new station: tetst', '2025-04-13 11:19:08'),
(140, 2, 'Deleted station: tetst (ID: 34)', '2025-04-13 11:19:14'),
(141, 2, 'Updated train ID: 18', '2025-04-13 11:22:47'),
(142, 2, 'Updated train ID: 17', '2025-04-13 11:22:53'),
(143, 2, 'Updated train ID: 16', '2025-04-13 11:23:02'),
(144, 2, 'Updated train ID: 16', '2025-04-13 11:23:09'),
(145, 2, 'Updated train ID: 16', '2025-04-13 11:23:15'),
(146, 2, 'Updated train ID: 17', '2025-04-13 11:23:19'),
(147, 2, 'Updated train ID: 18', '2025-04-13 11:23:23'),
(148, 2, 'Added new station: xvx', '2025-04-13 11:29:40'),
(149, 2, 'Deleted station: xvx (ID: 35)', '2025-04-13 11:36:05'),
(150, 2, 'Updated schedule ID: 18', '2025-04-13 11:38:14'),
(151, 2, 'Updated schedule ID: 18', '2025-04-13 11:38:32'),
(152, 2, 'Updated schedule ID: 18', '2025-04-13 11:44:19'),
(153, 2, 'Updated schedule ID: 18', '2025-04-13 11:46:16'),
(154, 2, 'Viewed booking details for PNR-000084', '2025-04-13 11:53:54'),
(155, 2, 'Viewed booking details for PNR-000084', '2025-04-13 11:53:58'),
(156, 2, 'Updated train ID: 3', '2025-04-13 11:54:06'),
(157, 2, 'Updated schedule ID: 18', '2025-04-13 11:58:26'),
(158, 2, 'Updated schedule ID: 17', '2025-04-13 12:50:31'),
(159, 2, 'Updated schedule ID: 17', '2025-04-13 12:54:45'),
(160, 2, 'Viewed booking details for PNR-000084', '2025-04-13 13:51:48'),
(161, 2, 'Viewed booking details for PNR-000084', '2025-04-13 13:51:58'),
(162, 2, 'Deleted booking ID: 84', '2025-04-13 13:53:02'),
(163, 2, 'Updated schedule ID: 23', '2025-04-13 14:03:19'),
(164, 2, 'Updated schedule ID: 23', '2025-04-13 14:03:29'),
(165, 2, 'Deleted schedule ID: 25', '2025-04-13 14:12:41'),
(166, 2, 'Deleted schedule ID: 23', '2025-04-13 14:13:21'),
(167, 2, 'Deleted schedule ID: 21', '2025-04-13 14:13:23'),
(168, 2, 'Updated schedule ID: 22', '2025-04-13 14:22:39'),
(169, 2, 'Updated schedule ID: 24', '2025-04-13 14:22:47'),
(170, 2, 'Deleted schedule ID: 19', '2025-04-13 14:23:32'),
(171, 2, 'Deleted schedule ID: 22', '2025-04-13 14:23:33'),
(172, 2, 'Deleted schedule ID: 20', '2025-04-13 14:23:35'),
(173, 2, 'Deleted station: Washington Drive (ID: 21)', '2025-04-13 14:24:39'),
(174, 2, 'Deleted schedule ID: 24', '2025-04-13 14:27:13'),
(175, 2, 'Updated schedule ID: 26', '2025-04-13 14:27:18'),
(176, 2, 'Added new station: j', '2025-04-13 14:27:30'),
(177, 2, 'Deleted station: j (ID: 36)', '2025-04-13 14:27:34'),
(178, 2, 'Added new station: cvbcb', '2025-04-13 14:27:51'),
(179, 2, 'Updated train ID: 19', '2025-04-13 14:30:02'),
(180, 2, 'Deleted train ID: 19', '2025-04-13 14:30:07'),
(181, 2, 'Deleted station: cvbcb (ID: 37)', '2025-04-13 14:30:28'),
(182, 2, 'Added new station: asd', '2025-04-13 14:37:36'),
(183, 2, 'Deleted station: asd (ID: 38)', '2025-04-13 14:37:40'),
(184, 2, 'Added new station: asd', '2025-04-13 14:40:56'),
(185, 2, 'Deleted station: asd (ID: 39)', '2025-04-13 14:41:00'),
(186, 2, 'Added new station: asd', '2025-04-13 14:41:06'),
(187, 2, 'Updated station: asds (ID: 40)', '2025-04-13 14:46:34'),
(188, 2, 'Deleted station: asds (ID: 40)', '2025-04-13 14:48:25'),
(189, 2, 'Deleted schedule ID: 17', '2025-04-13 14:50:47'),
(190, 2, 'Viewed booking details for PNR-000089', '2025-04-13 15:36:08'),
(191, 2, 'Updated booking details for PNR-000089', '2025-04-13 15:37:06'),
(192, 2, 'Viewed booking details for PNR-000086', '2025-04-13 15:51:52'),
(193, 2, 'Updated booking details for PNR-000086', '2025-04-13 15:51:56'),
(194, 2, 'Updated booking details for PNR-000089', '2025-04-13 15:54:02'),
(195, 2, 'Viewed booking details for PNR-000086', '2025-04-13 15:54:16'),
(196, 2, 'Updated booking details for PNR-000086', '2025-04-13 15:54:21'),
(197, 2, 'Viewed booking details for PNR-000086', '2025-04-13 15:54:26'),
(198, 2, 'Viewed booking details for PNR-000085', '2025-04-13 15:54:32'),
(199, 2, 'Updated schedule ID: 26', '2025-04-13 16:14:42'),
(200, 2, 'Deleted schedule ID: 29', '2025-04-14 03:54:16'),
(201, 2, 'Updated schedule ID: 30', '2025-04-14 03:54:27'),
(202, 2, 'Viewed booking details for PNR-000092', '2025-04-14 04:19:48'),
(203, 2, 'Viewed booking details for PNR-000090', '2025-04-14 04:19:52'),
(204, 2, 'Deleted booking ID: 90', '2025-04-14 04:19:58'),
(205, 2, 'Viewed booking details for PNR-000091', '2025-04-14 04:20:02'),
(206, 2, 'Viewed booking details for PNR-000092', '2025-04-14 04:20:03'),
(207, 2, 'Viewed booking details for PNR-000089', '2025-04-14 04:20:05'),
(208, 2, 'Viewed booking details for PNR-000088', '2025-04-14 04:20:07'),
(209, 2, 'Viewed booking details for PNR-000086', '2025-04-14 04:20:10'),
(210, 2, 'Viewed booking details for PNR-000085', '2025-04-14 04:20:13'),
(211, 2, 'Deleted booking ID: 86', '2025-04-14 04:20:17'),
(212, 2, 'Updated schedule ID: 18', '2025-04-14 05:18:04'),
(213, 2, 'Updated schedule ID: 18', '2025-04-14 05:18:08'),
(214, 2, 'Viewed booking details for PNR-000089', '2025-04-14 05:19:23'),
(215, 2, 'Viewed booking details for PNR-000092', '2025-04-14 05:32:16'),
(216, 2, 'Viewed booking details for PNR-000092', '2025-04-14 05:37:59'),
(217, 2, 'Deleted booking ID: 89', '2025-04-14 05:40:21'),
(218, 2, 'Deleted booking ID: 92', '2025-04-14 05:54:32'),
(219, 2, 'Deleted booking ID: 91', '2025-04-14 05:54:34'),
(220, 2, 'Deleted booking ID: 88', '2025-04-14 05:54:36'),
(221, 2, 'Deleted booking ID: 87', '2025-04-14 05:54:49'),
(222, 2, 'Deleted booking ID: 85', '2025-04-14 05:54:56'),
(223, 2, 'Updated schedule ID: 26', '2025-04-14 05:55:50'),
(224, 2, 'Deleted schedule ID: 18', '2025-04-16 08:10:47'),
(225, 2, 'Deleted schedule ID: 26', '2025-04-16 08:12:01'),
(226, 2, 'Deleted schedule ID: 39', '2025-04-16 08:14:24'),
(227, 2, 'Updated schedule ID: 52', '2025-04-16 08:14:51'),
(228, 2, 'Updated schedule ID: 52', '2025-04-16 08:14:51'),
(229, 2, 'Deleted schedule ID: 50', '2025-04-16 08:15:17'),
(230, 2, 'Updated schedule ID: 38', '2025-04-16 08:16:58'),
(231, 2, 'Updated schedule ID: 38', '2025-04-16 08:16:58'),
(232, 2, 'Updated train ID: 18', '2025-04-16 08:17:17'),
(233, 2, 'Added new station: l', '2025-04-16 08:17:31'),
(234, 2, 'Updated station: l (ID: 41)', '2025-04-16 08:17:39'),
(235, 2, 'Deleted station: l (ID: 41)', '2025-04-16 08:17:43'),
(236, 2, 'Deleted schedule ID: 38', '2025-04-16 08:41:35'),
(237, 2, 'Deleted schedule ID: 37', '2025-04-16 08:45:49'),
(238, 2, 'Updated schedule ID: 53', '2025-04-16 08:50:05'),
(239, 2, 'Updated schedule ID: 53', '2025-04-16 08:50:05'),
(240, 2, 'Updated schedule ID: 55', '2025-04-16 08:58:40'),
(241, 2, 'Updated schedule ID: 55', '2025-04-16 08:58:40'),
(242, 2, 'Deleted schedule ID: 28', '2025-04-16 09:01:14'),
(243, 2, 'Updated schedule ID: 53', '2025-04-16 09:01:28'),
(244, 2, 'Deleted schedule ID: 43', '2025-04-16 09:11:23'),
(245, 2, 'Deleted schedule ID: 44', '2025-04-16 09:11:27'),
(246, 2, 'Viewed booking details for PNR-000094', '2025-04-16 09:15:01'),
(247, 2, 'Deleted schedule ID: 51', '2025-04-16 09:17:56'),
(248, 2, 'Deleted schedule ID: 34', '2025-04-16 09:19:09'),
(249, 2, 'Deleted schedule ID: 52', '2025-04-16 09:32:13'),
(250, 2, 'Batch deleted schedule IDs: 30', '2025-04-17 02:23:13'),
(251, 2, 'Batch deleted schedule IDs: 31, 32', '2025-04-17 02:28:59'),
(252, 2, 'Batch deleted schedule IDs: 33, 58, 35', '2025-04-17 02:29:22'),
(253, 2, 'Batch deleted schedule IDs: 54, 49', '2025-04-17 02:31:26'),
(254, 2, 'Batch deleted schedule IDs: 53, 36, 27', '2025-04-17 02:31:47'),
(255, 2, 'Batch deleted schedule IDs: 40', '2025-04-17 02:33:01'),
(256, 2, 'Batch deleted schedule IDs: 41', '2025-04-17 02:37:10'),
(257, 2, 'Batch deleted schedule IDs: 42', '2025-04-17 02:37:47'),
(258, 2, 'Batch deleted schedule IDs: 45, 46', '2025-04-17 02:37:50'),
(259, 2, 'Batch deleted schedule IDs: 47', '2025-04-17 03:38:46'),
(260, 2, 'Updated schedule ID: 48', '2025-04-17 04:33:15'),
(261, 2, 'Updated schedule ID: 48', '2025-04-17 04:33:38'),
(262, 2, 'Updated schedule ID: 48', '2025-04-17 04:34:15'),
(263, 2, 'Updated schedule ID: 48', '2025-04-17 04:34:20'),
(264, 2, 'Deleted schedule ID: 57', '2025-04-17 04:34:25'),
(265, 2, 'Updated schedule ID: 48', '2025-04-17 05:08:06'),
(266, 2, 'Updated schedule ID: 55', '2025-04-17 05:08:30'),
(267, 2, 'Updated schedule ID: 55', '2025-04-17 05:11:36'),
(268, 2, 'Updated schedule ID: 55', '2025-04-17 05:11:36'),
(269, 2, 'Deleted schedule ID: 55', '2025-04-17 05:27:55'),
(270, 2, 'Deleted schedule ID: 59', '2025-04-17 05:30:21'),
(271, 2, 'Deleted schedule ID: 56', '2025-04-17 05:55:52'),
(272, 2, 'Deleted schedule ID: 61', '2025-04-17 06:01:22'),
(273, 2, 'Deleted schedule ID: 64', '2025-04-17 06:01:24'),
(274, 2, 'Deleted schedule ID: 63', '2025-04-17 06:01:29'),
(275, 2, 'Deleted schedule ID: 62', '2025-04-17 06:01:31'),
(276, 2, 'Deleted schedule ID: 60', '2025-04-17 06:01:33'),
(277, 2, 'Deleted schedule ID: 65', '2025-04-17 06:03:44'),
(278, 2, 'Deleted schedule ID: 66', '2025-04-17 06:04:32'),
(279, 2, 'Deleted schedule ID: 67', '2025-04-17 06:23:23'),
(280, 2, 'Batch deleted schedule IDs: 69, 70', '2025-04-17 06:28:55'),
(281, 2, 'Deleted schedule ID: 71', '2025-04-17 06:29:40'),
(282, 2, 'Deleted schedule ID: 72', '2025-04-17 06:42:45'),
(283, 2, 'Batch deleted schedule IDs: 76, 75, 74, 73', '2025-04-17 06:59:43'),
(284, 2, 'Deleted schedule ID: 77', '2025-04-17 07:08:16'),
(285, 2, 'Deleted schedule ID: 78', '2025-04-17 07:18:42'),
(286, 2, 'Deleted schedule ID: 79', '2025-04-17 07:18:47'),
(287, 2, 'Updated schedule ID: 48', '2025-04-17 08:27:49'),
(288, 2, 'Updated schedule ID: 48', '2025-04-17 08:27:49'),
(289, 2, 'Viewed booking details for PNR-000095', '2025-04-18 04:21:12'),
(290, 2, 'Viewed booking details for PNR-000095', '2025-04-18 04:29:16'),
(291, 2, 'Viewed booking details for PNR-000095', '2025-04-18 04:34:26'),
(292, 2, 'Updated booking details for PNR-000095', '2025-04-18 04:34:38'),
(293, 2, 'Updated booking details for PNR-000095', '2025-04-18 04:35:01'),
(294, 2, 'Updated booking details for PNR-000095', '2025-04-18 04:36:31'),
(295, 2, 'Updated booking details for PNR-000095', '2025-04-18 04:36:42'),
(296, 2, 'Viewed booking details for PNR-000096', '2025-04-18 04:36:54'),
(297, 2, 'Viewed booking details for PNR-000096', '2025-04-18 04:45:39'),
(298, 2, 'Viewed booking details for PNR-000095', '2025-04-18 04:45:40'),
(299, 2, 'Viewed booking details for PNR-000096', '2025-04-18 04:50:24'),
(300, 2, 'Viewed booking details for PNR-000095', '2025-04-18 04:50:27'),
(301, 2, 'Deleted booking ID: 95', '2025-04-18 04:50:38'),
(302, 2, 'Viewed booking details for PNR-000097', '2025-04-18 04:51:31'),
(303, 2, 'Viewed booking details for PNR-000096', '2025-04-18 04:51:36'),
(304, 2, 'Viewed booking details for PNR-000097', '2025-04-18 04:59:12'),
(305, 2, 'Viewed booking details for PNR-000096', '2025-04-18 05:03:30'),
(306, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:03:36'),
(307, 2, 'Viewed booking details for PNR-000096', '2025-04-18 05:03:59'),
(308, 2, 'Viewed booking details for PNR-000096', '2025-04-18 05:05:01'),
(309, 2, 'Viewed booking details for PNR-000096', '2025-04-18 05:05:06'),
(310, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:06:09'),
(311, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:06:19'),
(312, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:07:31'),
(313, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:13:39'),
(314, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:16:07'),
(315, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:16:10'),
(316, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:19:39'),
(317, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:19:43'),
(318, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:20:25'),
(319, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:20:29'),
(320, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:20:33'),
(321, 2, 'Viewed booking details for PNR-000096', '2025-04-18 05:20:39'),
(322, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:20:42'),
(323, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:28:45'),
(324, 2, 'Viewed booking details for PNR-000097', '2025-04-18 05:28:49'),
(325, 2, 'Deleted schedule ID: 80', '2025-04-18 06:08:58'),
(326, 2, 'Viewed booking details for PNR-000097', '2025-04-18 06:10:40'),
(327, 2, 'Viewed booking details for PNR-000096', '2025-04-18 06:10:44'),
(328, 2, 'Viewed booking details for PNR-000097', '2025-04-18 06:21:31'),
(329, 2, 'Viewed booking details for PNR-000096', '2025-04-18 06:21:36'),
(330, 2, 'Updated schedule ID: 86', '2025-04-18 07:08:03'),
(331, 2, 'Updated schedule ID: 86', '2025-04-18 07:08:03'),
(332, 2, 'Updated schedule ID: 82', '2025-04-18 07:08:18'),
(333, 2, 'Updated schedule ID: 82', '2025-04-18 07:08:18'),
(334, 2, 'Viewed booking details for PNR-000098', '2025-04-18 07:26:43'),
(335, 2, 'Viewed booking details for PNR-000098', '2025-04-18 07:26:53'),
(336, 2, 'Viewed booking details for PNR-000098', '2025-04-18 07:27:08'),
(337, 2, 'Deleted station: Daet Central Station (ID: 17)', '2025-04-18 09:05:58'),
(338, 2, 'Deleted station: Masbate Port Station (ID: 19)', '2025-04-18 09:06:02'),
(339, 2, 'Deleted route ID: 56', '2025-04-18 09:06:26'),
(340, 2, 'Deleted station: Virac Rail Terminal (ID: 18)', '2025-04-18 09:06:31'),
(341, 2, 'Deleted station: Sorsogon City Terminal (ID: 14)', '2025-04-18 10:25:56'),
(342, 2, 'Viewed booking details for PNR-000098', '2025-04-19 11:03:14'),
(343, 2, 'Viewed booking details for PNR-000098', '2025-04-19 11:03:14'),
(344, 2, 'Viewed booking details for PNR-000098', '2025-04-19 11:03:18'),
(345, 2, 'Viewed booking details for PNR-000098', '2025-04-19 11:03:18'),
(346, 2, 'Viewed booking details for PNR-000099', '2025-04-19 12:19:23'),
(347, 2, 'Viewed booking details for PNR-000099', '2025-04-19 12:30:56'),
(348, 2, 'Viewed booking details for PNR-000097', '2025-04-19 12:34:52'),
(349, 2, 'Viewed booking details for PNR-000099', '2025-04-19 12:51:44'),
(350, 2, 'Approved cancellation request for ticket ID: 99', '2025-04-19 12:54:44'),
(351, 2, 'Approved cancellation request #1 for ticket #99', '2025-04-19 12:54:44'),
(352, 2, 'Viewed booking details for PNR-000098', '2025-04-19 13:03:52'),
(353, 2, 'Viewed booking details for PNR-000098', '2025-04-19 13:03:53'),
(354, 2, 'Viewed booking details for PNR-000099', '2025-04-19 13:17:25'),
(355, 2, 'Viewed booking details for PNR-000099', '2025-04-19 13:17:31'),
(356, 2, 'Viewed booking details for PNR-000098', '2025-04-19 13:20:17'),
(357, 2, 'Approved cancellation request for ticket ID: 98', '2025-04-19 13:31:27'),
(358, 2, 'Approved cancellation request #2 for ticket #98', '2025-04-19 13:31:27'),
(359, 2, 'Approved cancellation request for ticket ID: 97', '2025-04-20 11:01:53'),
(360, 2, 'Approved cancellation request #3 for ticket #97', '2025-04-20 11:01:53'),
(361, 2, 'Viewed booking details for PNR-000096', '2025-04-20 11:03:52'),
(362, 2, 'Viewed booking details for PNR-000097', '2025-04-20 11:03:58'),
(363, 2, 'Viewed booking details for PNR-000098', '2025-04-20 11:04:01'),
(364, 2, 'Viewed booking details for PNR-000099', '2025-04-20 11:04:04'),
(365, 2, 'Viewed booking details for PNR-000098', '2025-04-20 11:58:10'),
(366, 2, 'Deleted schedule ID: 93', '2025-05-05 05:40:56'),
(367, 2, 'Updated schedule ID: 85', '2025-05-05 11:39:12'),
(368, 2, 'Updated schedule ID: 85', '2025-05-05 11:39:12'),
(369, 2, 'Updated schedule ID: 95', '2025-05-05 11:39:34'),
(370, 2, 'Updated schedule ID: 95', '2025-05-05 11:39:34'),
(371, 2, 'Updated schedule ID: 95', '2025-05-05 11:44:10'),
(372, 2, 'Updated schedule ID: 95', '2025-05-05 11:44:10'),
(373, 2, 'Updated schedule ID: 95', '2025-05-05 11:44:15'),
(374, 2, 'Updated schedule ID: 95', '2025-05-05 11:44:15'),
(375, 2, 'Updated schedule ID: 95', '2025-05-05 11:44:20'),
(376, 2, 'Updated schedule ID: 95', '2025-05-05 11:44:20'),
(377, 2, 'Updated train ID: 3', '2025-05-05 11:47:16'),
(378, 2, 'Updated train ID: 3', '2025-05-05 11:47:39'),
(379, 2, 'Viewed booking details for PNR-000097', '2025-05-05 11:53:35');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `TicketID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `ScheduleID` int(11) NOT NULL,
  `BookDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `DateTravel` datetime NOT NULL,
  `TicketStatus` enum('Reserved','Confirmed','Cancelled','Pending Cancellation') NOT NULL DEFAULT 'Reserved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`TicketID`, `UserID`, `ScheduleID`, `BookDate`, `DateTravel`, `TicketStatus`) VALUES
(96, 2, 83, '2025-04-18 04:36:00', '2025-04-25 00:00:00', 'Pending Cancellation'),
(97, 2, 48, '2025-04-18 04:51:24', '2025-04-20 00:00:00', 'Cancelled'),
(98, 2, 84, '2025-04-18 07:26:10', '2025-04-29 00:00:00', 'Cancelled'),
(99, 30, 81, '2025-04-19 11:07:05', '2025-04-22 00:00:00', 'Cancelled'),
(100, 30, 87, '2025-04-20 11:37:19', '2025-04-21 00:00:00', 'Reserved'),
(101, 2, 81, '2025-04-20 12:48:12', '2025-04-22 00:00:00', 'Reserved');

-- --------------------------------------------------------

--
-- Table structure for table `trains`
--

CREATE TABLE `trains` (
  `TrainID` int(11) NOT NULL,
  `TrainNumber` int(11) NOT NULL,
  `TrainName` varchar(299) NOT NULL,
  `Capacity` int(11) NOT NULL,
  `FleetType` enum('Economy','Reclining Aircon','Family Sleeper','Executive Sleeper','Regular') NOT NULL DEFAULT 'Economy',
  `Status` enum('Active','Inactive','Maintenance') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trains`
--

INSERT INTO `trains` (`TrainID`, `TrainNumber`, `TrainName`, `Capacity`, `FleetType`, `Status`) VALUES
(3, 101, 'Bicol Express', 151, 'Reclining Aircon', 'Active'),
(4, 102, 'Mayon Limited', 150, 'Economy', 'Active'),
(16, 103, 'Pili Shuttle', 180, 'Economy', 'Active'),
(17, 104, 'Pacific Coastal', 120, 'Economy', 'Active'),
(18, 105, 'Sorsogon Link', 161, 'Economy', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `trainstations`
--

CREATE TABLE `trainstations` (
  `StationID` int(11) NOT NULL,
  `StationName` varchar(199) NOT NULL,
  `LocationID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainstations`
--

INSERT INTO `trainstations` (`StationID`, `StationName`, `LocationID`) VALUES
(9, 'Legazpi Central Station', 5),
(10, 'Naga Railway Hub', 6),
(15, 'Tabaco City Station', 12),
(16, 'Iriga Transit Center', 13),
(22, 'Bagtang', 5),
(23, 'Daraga', 17),
(24, 'Travesia', 17),
(25, 'Ligao', 18),
(26, 'Oas', 19),
(27, 'Polangui', 20),
(28, 'Matacon', 20),
(29, 'Bato', 21),
(30, 'Lourdes (Old)', 13),
(31, 'Baao', 22),
(32, 'Pili', 23);

-- --------------------------------------------------------

--
-- Stand-in structure for view `userbookingsummary`
-- (See below for the actual view)
--
CREATE TABLE `userbookingsummary` (
`UserID` int(11)
,`FirstName` varchar(299)
,`LastName` varchar(299)
,`TicketID` int(11)
,`DateTravel` datetime
,`TicketStatus` enum('Reserved','Confirmed','Cancelled','Pending Cancellation')
,`DepartureStation` varchar(199)
,`ArrivalStation` varchar(199)
,`Amount` decimal(10,2)
,`PaymentStatus` enum('Pending','Completed','Reserved')
,`ModeOfPayment` enum('Over-the-counter','Gcash','Bank Transfer','Beep Card')
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Email` varchar(299) NOT NULL,
  `FirstName` varchar(299) NOT NULL,
  `MiddleInitial` varchar(5) DEFAULT NULL,
  `LastName` varchar(299) NOT NULL,
  `Password_hash` varchar(299) NOT NULL,
  `DOB` date NOT NULL,
  `PhoneNumber` varchar(13) NOT NULL,
  `Address` varchar(500) NOT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Status` enum('Active','Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Email`, `FirstName`, `MiddleInitial`, `LastName`, `Password_hash`, `DOB`, `PhoneNumber`, `Address`, `Created_at`, `Status`) VALUES
(1, 'arc010101010@gmail.com', 'arjay', 'r', 'cabrillas', '$2y$10$PX9jMxq7Rmtf92JnvkR6MOjW03IFyJMreN7YkwwawjoJuUWz5iupi', '2025-03-22', '09912343432', 'libon', '2025-03-08 12:33:07', 'Inactive'),
(2, 'arcabrillas@my.cspc.edu.ph', 'Arjay', 'R', 'Cabrillas', '$2y$10$.OOSQey8UYPLbmaGTzHMZ.CdNPDOu5VQiKueuaSutWW9McLJ8rbiK', '2025-03-03', '09123456781', 'Libon', '2025-03-08 12:35:22', 'Active'),
(8, 'ads@gmail.com', 'c', 'c', 'c', '$2y$10$VjN/63ms7jr8FqDOjokMsODQNf0IYhCSDuRD7IHU2jpWFAX3H9/ii', '2025-03-19', '0984564212', 'kJ87Hgsushcusb', '2025-03-08 14:20:06', 'Inactive'),
(30, 'leon@gmail.com', 'Leon', 'S', 'Kennedy', '$2y$10$LNjfMoVsAdP76mlZsbksC.aawR4/7N6t7AhLhTKN2glT.OsIbsfHG', '2025-03-11', '09364786432', 'ARKLAY', '2025-03-11 11:08:39', 'Active'),
(34, 'admin@gmail.com', 'admin', 'S', 'Redfield', '$2y$10$sdeb8PPInDwJ6y6neBU2kex0RZUTmxHxiwGnwHFv0cDQrrzIjrPRC', '2025-04-08', '03254987154', 'Racoon City', '2025-04-08 01:58:14', 'Active'),
(35, 'test@gmail.com', 'test', 't', 'TEST', '$2y$10$3cePXEM6QeZ24linQFPPy.wfhlvfNhjDZ7zsuMXuwYJkDC.degzIy', '2008-02-15', '09949652135', 'Test', '2025-04-14 05:57:11', 'Active');

-- --------------------------------------------------------

--
-- Structure for view `bookingdetails`
--
DROP TABLE IF EXISTS `bookingdetails`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `bookingdetails`  AS SELECT `t`.`TicketID` AS `TicketID`, `t`.`BookDate` AS `BookDate`, `t`.`DateTravel` AS `DateTravel`, `t`.`TicketStatus` AS `TicketStatus`, `s`.`DepartureTime` AS `DepartureTime`, `s`.`ArrivalTime` AS `ArrivalTime`, `ts_dep`.`StationName` AS `DepartureStation`, `ts_arr`.`StationName` AS `ArrivalStation`, `p`.`Amount` AS `Amount`, `p`.`PaymentStatus` AS `PaymentStatus`, `p`.`ModeOfPayment` AS `ModeOfPayment`, `tr`.`FleetType` AS `FleetType`, `tr`.`TrainName` AS `TrainName`, `tr`.`TrainNumber` AS `TrainNumber`, `t`.`UserID` AS `UserID` FROM ((((((`tickets` `t` join `schedules` `s` on(`t`.`ScheduleID` = `s`.`ScheduleID`)) join `routes` `r` on(`s`.`RouteID` = `r`.`RouteID`)) join `trains` `tr` on(`s`.`TrainID` = `tr`.`TrainID`)) join `trainstations` `ts_dep` on(`r`.`DepartureStationID` = `ts_dep`.`StationID`)) join `trainstations` `ts_arr` on(`r`.`ArrivalStationID` = `ts_arr`.`StationID`)) left join `payments` `p` on(`t`.`TicketID` = `p`.`TicketID`)) ;

-- --------------------------------------------------------

--
-- Structure for view `userbookingsummary`
--
DROP TABLE IF EXISTS `userbookingsummary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `userbookingsummary`  AS SELECT `u`.`UserID` AS `UserID`, `u`.`FirstName` AS `FirstName`, `u`.`LastName` AS `LastName`, `t`.`TicketID` AS `TicketID`, `t`.`DateTravel` AS `DateTravel`, `t`.`TicketStatus` AS `TicketStatus`, `ts_dep`.`StationName` AS `DepartureStation`, `ts_arr`.`StationName` AS `ArrivalStation`, `p`.`Amount` AS `Amount`, `p`.`PaymentStatus` AS `PaymentStatus`, `p`.`ModeOfPayment` AS `ModeOfPayment` FROM ((((((`users` `u` join `tickets` `t` on(`u`.`UserID` = `t`.`UserID`)) join `schedules` `s` on(`t`.`ScheduleID` = `s`.`ScheduleID`)) join `routes` `r` on(`s`.`RouteID` = `r`.`RouteID`)) join `trainstations` `ts_dep` on(`r`.`DepartureStationID` = `ts_dep`.`StationID`)) join `trainstations` `ts_arr` on(`r`.`ArrivalStationID` = `ts_arr`.`StationID`)) left join `payments` `p` on(`t`.`TicketID` = `p`.`TicketID`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`AdminID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `cancellation_requests`
--
ALTER TABLE `cancellation_requests`
  ADD PRIMARY KEY (`RequestID`),
  ADD KEY `TicketID` (`TicketID`),
  ADD KEY `AdminID` (`AdminID`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`LocationID`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `TicketID` (`TicketID`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`RouteID`),
  ADD KEY `DepartureStationID` (`DepartureStationID`),
  ADD KEY `ArrivalStationID` (`ArrivalStationID`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`ScheduleID`),
  ADD KEY `RouteID` (`RouteID`),
  ADD KEY `TrainID` (`TrainID`);

--
-- Indexes for table `systemlogs`
--
ALTER TABLE `systemlogs`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `AdminID` (`AdminID`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`TicketID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `ScheduleID` (`ScheduleID`);

--
-- Indexes for table `trains`
--
ALTER TABLE `trains`
  ADD PRIMARY KEY (`TrainID`),
  ADD UNIQUE KEY `TrainNumber` (`TrainNumber`);

--
-- Indexes for table `trainstations`
--
ALTER TABLE `trainstations`
  ADD PRIMARY KEY (`StationID`),
  ADD KEY `LocationID` (`LocationID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `Password_hash` (`Password_hash`),
  ADD UNIQUE KEY `PhoneNumber` (`PhoneNumber`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cancellation_requests`
--
ALTER TABLE `cancellation_requests`
  MODIFY `RequestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `LocationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `RouteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `systemlogs`
--
ALTER TABLE `systemlogs`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=380;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `TicketID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `trains`
--
ALTER TABLE `trains`
  MODIFY `TrainID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `trainstations`
--
ALTER TABLE `trainstations`
  MODIFY `StationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `Admins_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `cancellation_requests`
--
ALTER TABLE `cancellation_requests`
  ADD CONSTRAINT `CancellationRequests_ibfk_1` FOREIGN KEY (`TicketID`) REFERENCES `tickets` (`TicketID`) ON DELETE CASCADE,
  ADD CONSTRAINT `CancellationRequests_ibfk_2` FOREIGN KEY (`AdminID`) REFERENCES `admins` (`AdminID`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `Payments_ibfk_1` FOREIGN KEY (`TicketID`) REFERENCES `tickets` (`TicketID`) ON DELETE CASCADE;

--
-- Constraints for table `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `Routes_ibfk_1` FOREIGN KEY (`DepartureStationID`) REFERENCES `trainstations` (`StationID`) ON DELETE CASCADE,
  ADD CONSTRAINT `Routes_ibfk_2` FOREIGN KEY (`ArrivalStationID`) REFERENCES `trainstations` (`StationID`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `Schedules_ibfk_1` FOREIGN KEY (`RouteID`) REFERENCES `routes` (`RouteID`) ON DELETE CASCADE,
  ADD CONSTRAINT `Schedules_ibfk_2` FOREIGN KEY (`TrainID`) REFERENCES `trains` (`TrainID`) ON DELETE CASCADE;

--
-- Constraints for table `systemlogs`
--
ALTER TABLE `systemlogs`
  ADD CONSTRAINT `SystemLogs_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `admins` (`AdminID`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `Tickets_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  ADD CONSTRAINT `Tickets_ibfk_2` FOREIGN KEY (`ScheduleID`) REFERENCES `schedules` (`ScheduleID`) ON DELETE CASCADE;

--
-- Constraints for table `trainstations`
--
ALTER TABLE `trainstations`
  ADD CONSTRAINT `TrainStations_ibfk_1` FOREIGN KEY (`LocationID`) REFERENCES `locations` (`LocationID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
