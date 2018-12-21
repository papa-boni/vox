-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 21, 2018 at 07:02 AM
-- Server version: 10.1.37-MariaDB-0+deb9u1
-- PHP Version: 7.0.33-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vox`
--

-- --------------------------------------------------------

--
-- Table structure for table `avail`
--

CREATE TABLE `avail` (
  `avail_id` int(11) NOT NULL,
  `time_id` int(11) NOT NULL,
  `ppl_id` int(11) NOT NULL,
  `subj_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim`
--

CREATE TABLE `claim` (
  `claim_id` int(11) NOT NULL,
  `ppl_id` int(11) NOT NULL,
  `avail_id` int(11) NOT NULL,
  `claim_locked` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppl`
--

CREATE TABLE `ppl` (
  `ppl_id` int(11) NOT NULL,
  `ppl_login` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ppl_forename` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ppl_prefix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ppl_surname` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ppl_type` enum('leerling','personeel') COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppl2tag`
--

CREATE TABLE `ppl2tag` (
  `ppl2tag` int(11) NOT NULL,
  `ppl_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `tag_start_date` datetime NOT NULL,
  `tag_end_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subj`
--

CREATE TABLE `subj` (
  `subj_id` int(11) NOT NULL,
  `subj_abbrev` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subj_name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE `tag` (
  `tag_id` int(11) NOT NULL,
  `tag_name` int(11) NOT NULL,
  `tag_type` enum('KLAS','KEUZEVAK','LEERJAAR','NIVEAU') COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time`
--

CREATE TABLE `time` (
  `time_id` int(11) NOT NULL,
  `time_year` int(11) NOT NULL,
  `time_week` int(11) NOT NULL,
  `time_day` enum('ma','di','wo','do','vr','za','zo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_hour` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `avail`
--
ALTER TABLE `avail`
  ADD PRIMARY KEY (`avail_id`),
  ADD UNIQUE KEY `time_id` (`time_id`,`ppl_id`,`subj_id`),
  ADD KEY `subj_id` (`subj_id`),
  ADD KEY `avail_ibfk_2` (`ppl_id`);

--
-- Indexes for table `claim`
--
ALTER TABLE `claim`
  ADD PRIMARY KEY (`claim_id`),
  ADD UNIQUE KEY `ppl_id` (`ppl_id`,`avail_id`),
  ADD KEY `avail_id` (`avail_id`);

--
-- Indexes for table `ppl`
--
ALTER TABLE `ppl`
  ADD PRIMARY KEY (`ppl_id`),
  ADD UNIQUE KEY `ppl_login` (`ppl_login`);

--
-- Indexes for table `ppl2tag`
--
ALTER TABLE `ppl2tag`
  ADD PRIMARY KEY (`ppl2tag`),
  ADD KEY `ppl_id` (`ppl_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `subj`
--
ALTER TABLE `subj`
  ADD PRIMARY KEY (`subj_id`),
  ADD UNIQUE KEY `subj_abbrev` (`subj_abbrev`),
  ADD UNIQUE KEY `subj_name` (`subj_name`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`tag_id`);

--
-- Indexes for table `time`
--
ALTER TABLE `time`
  ADD PRIMARY KEY (`time_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `avail`
--
ALTER TABLE `avail`
  MODIFY `avail_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `claim`
--
ALTER TABLE `claim`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ppl`
--
ALTER TABLE `ppl`
  MODIFY `ppl_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ppl2tag`
--
ALTER TABLE `ppl2tag`
  MODIFY `ppl2tag` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `subj`
--
ALTER TABLE `subj`
  MODIFY `subj_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `time`
--
ALTER TABLE `time`
  MODIFY `time_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `avail`
--
ALTER TABLE `avail`
  ADD CONSTRAINT `avail_ibfk_1` FOREIGN KEY (`time_id`) REFERENCES `time` (`time_id`),
  ADD CONSTRAINT `avail_ibfk_2` FOREIGN KEY (`ppl_id`) REFERENCES `ppl` (`ppl_id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `avail_ibfk_3` FOREIGN KEY (`subj_id`) REFERENCES `subj` (`subj_id`);

--
-- Constraints for table `claim`
--
ALTER TABLE `claim`
  ADD CONSTRAINT `claim_ibfk_1` FOREIGN KEY (`ppl_id`) REFERENCES `ppl` (`ppl_id`) ON UPDATE NO ACTION,
  ADD CONSTRAINT `claim_ibfk_2` FOREIGN KEY (`avail_id`) REFERENCES `avail` (`avail_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
