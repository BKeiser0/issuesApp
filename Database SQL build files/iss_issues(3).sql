-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2025 at 06:00 PM
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
-- Database: `finalproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `iss_issues`
--

CREATE TABLE `iss_issues` (
  `id` int(11) NOT NULL,
  `short_description` varchar(255) NOT NULL,
  `long_description` text NOT NULL,
  `status` enum('Resolved','Not Resolved') NOT NULL DEFAULT 'Not Resolved',
  `open_date` date NOT NULL,
  `close_date` datetime DEFAULT NULL,
  `priority` varchar(255) NOT NULL,
  `org` varchar(255) NOT NULL,
  `project` varchar(255) NOT NULL,
  `per_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `pdf_attachment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iss_issues`
--

INSERT INTO `iss_issues` (`id`, `short_description`, `long_description`, `status`, `open_date`, `close_date`, `priority`, `org`, `project`, `per_id`, `created_by`, `pdf_attachment`) VALUES
(14, 'CS Department Professors', 'The CS Department needs more professors.', 'Not Resolved', '2025-04-07', NULL, 'High', 'SVSU.edu', 'hiring more professors', 0, 2, NULL),
(15, 'White Board Markers', 'We need some more white board markers in the CIS355 classroom', 'Not Resolved', '2025-04-07', NULL, 'Low', 'SVSU', 'more markers', 0, 6, NULL),
(16, 'Leaky Ceiling', 'There was a ceiling leak in the CIS355 Classroom.', 'Resolved', '2025-04-07', NULL, 'Medium', 'SVSU.edu', 'fixing ceiling', 0, 9, NULL),
(17, 'Update Syllabus ', 'This is a test of the PDF functionality.', 'Not Resolved', '2025-04-07', NULL, 'Medium', 'SVSU.edu', 'Testing PDF', 0, 9, './uploads/36f7e42b9b73fe0eea0480209c8b5047.pdf');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `iss_issues`
--
ALTER TABLE `iss_issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `iss_issues`
--
ALTER TABLE `iss_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
