-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 30, 2025 at 09:50 AM
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
-- Database: `personalvault`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `created_at`, `email`) VALUES
(1, 'Heviinash Parugavelu', 'heviinash22', '$2y$10$t5/64d3WK6p21o4PAqhb5OM.QPzfZE4yuz3BoqeHv2Z5iiX0YDx7m', '2025-07-29 13:51:13', 'heviinash@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `vault_files`
--

CREATE TABLE `vault_files` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` text NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vault_files`
--

INSERT INTO `vault_files` (`id`, `folder_id`, `filename`, `filepath`, `uploaded_at`) VALUES
(26, NULL, 'IBM Explore Emerging Tech Badge.pdf', '../uploads/1754238519_IBM_Explore_Emerging_Tech_Badge.pdf', '2025-08-04 00:28:39');

-- --------------------------------------------------------

--
-- Table structure for table `vault_folders`
--

CREATE TABLE `vault_folders` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vault_folders`
--

INSERT INTO `vault_folders` (`id`, `name`, `created_at`) VALUES
(7, 'Test', '2025-08-03 13:36:16'),
(8, 'CyberVault', '2025-08-04 00:32:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `vault_files`
--
ALTER TABLE `vault_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `folder_id` (`folder_id`);

--
-- Indexes for table `vault_folders`
--
ALTER TABLE `vault_folders`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vault_files`
--
ALTER TABLE `vault_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `vault_folders`
--
ALTER TABLE `vault_folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `vault_files`
--
ALTER TABLE `vault_files`
  ADD CONSTRAINT `vault_files_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `vault_folders` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
