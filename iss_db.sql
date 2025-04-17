-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2025 at 03:35 AM
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
-- Database: `iss_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `iss_comments`
--

CREATE TABLE `iss_comments` (
  `id` int(11) NOT NULL,
  `per_id` int(11) NOT NULL,
  `iss_id` int(11) NOT NULL,
  `short_comment` varchar(255) NOT NULL,
  `long_comment` text NOT NULL,
  `posted_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iss_comments`
--

INSERT INTO `iss_comments` (`id`, `per_id`, `iss_id`, `short_comment`, `long_comment`, `posted_date`) VALUES
(16, 7, 14, 'Admin Comment', 'If you are not the admin, you should not be able to edit or delete this. This should be visible in the newly-added comment list section.', '2025-04-16'),
(17, 2, 17, '[User Deleted] it works!', 'This is great.', '2025-04-16'),
(18, 9, 17, 'I was deleted', 'This person commenting was deleted, see, no more issues!', '2025-04-16'),
(19, 2, 21, 'Can comments be edited?', 'Yes they can!!!', '2025-04-17'),
(20, 5, 21, 'This is great!', 'I am now able to edit my own comments again!!! Even in the comment list!', '2025-04-17');

-- --------------------------------------------------------

--
-- Table structure for table `iss_issues`
--

CREATE TABLE `iss_issues` (
  `id` int(11) NOT NULL,
  `short_description` varchar(255) NOT NULL,
  `long_description` text NOT NULL,
  `open_date` date NOT NULL,
  `close_date` date NOT NULL,
  `priority` varchar(255) NOT NULL,
  `org` varchar(255) NOT NULL,
  `project` varchar(255) NOT NULL,
  `per_id` int(11) NOT NULL,
  `pdf_attachment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iss_issues`
--

INSERT INTO `iss_issues` (`id`, `short_description`, `long_description`, `open_date`, `close_date`, `priority`, `org`, `project`, `per_id`, `pdf_attachment`) VALUES
(14, 'Admin Test', 'If you are not the admin, you should not be able to edit or delete this.', '2025-04-16', '2025-04-16', 'C', 'School', 'Final Program', 7, ''),
(15, 'Priority Test', 'As there is no valid close date, the priority in the issue_list should be visible. Try pressing resolve to see this change.', '2025-04-16', '0000-00-00', 'A', 'School', 'Final Program', 7, ''),
(16, 'Edit Admin', 'Admins can change the status of other person into admins or strip their power. With great power comes great responsibility.', '2025-04-16', '0000-00-00', 'E', 'School', 'Comment Test', 2, ''),
(17, 'Person Deleted', 'Although the person was deleted, their issues remain. However, if an issue has comments, all their comments will be deleted along with them.', '2025-04-16', '2025-04-16', 'D', 'School', 'Comment Test', 8, ''),
(21, 'Issues can now be edited', 'Issues cannot be edited in the issue.php, modal won\'t show up.', '2025-04-17', '2025-04-17', 'D', 'School', 'project', 10, ''),
(22, 'Sorting not working', 'I am unable to sort.', '2025-04-17', '0000-00-00', 'D', 'School', 'Sorting test', 5, ''),
(23, 'this person deleted', 'no problems with admin editing their comments', '2025-04-17', '0000-00-00', 'B', 'fsg', 'fsg', 11, ''),
(24, 'PDF Functionality', 'PDF\'s work. Please attach a pdf and it shall function properly.', '2025-04-17', '0000-00-00', 'A', 'School', 'project', 7, './uploads/e275854492284c767eebc0042ec82d30.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `iss_persons`
--

CREATE TABLE `iss_persons` (
  `id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pwd_hash` varchar(255) NOT NULL,
  `pwd_salt` varchar(255) NOT NULL,
  `admin` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iss_persons`
--

INSERT INTO `iss_persons` (`id`, `fname`, `lname`, `mobile`, `email`, `pwd_hash`, `pwd_salt`, `admin`) VALUES
(2, 'Lukas', 'White', '911', 'l@w', '$2y$10$ue0FjArYOvnwO/tXjs2EO.L7cvGsnuAWRB3KjZJvMRyTyNg65MPpG', '$2y$10$ue0FjArYOvnwO/tXjs2EO.', '1'),
(5, 'Connor', 'Oard', '100', 'connor@email.com', '$2y$10$Fi7q6KTwKyt6SPItvPhfPOeKQEvHL.P7n0b.58rttDWr.9uDsRC96', '$2y$10$Fi7q6KTwKyt6SPItvPhfPO', ''),
(6, 'Test', 'Debug', '200', 'l@w', '$2y$10$XpavehmwpeH/wV1SjO7D/.fINC.oVi0fiR4A6Lf5h2q17aKIKt8nm', '$2y$10$XpavehmwpeH/wV1SjO7D/.', ''),
(7, 'Admin', 'Awesome', '911', 'admin', '$2y$10$yLuPdFMltjhBIwY2pCpKgO99CLK0lUcqeQR4mfcFYFQ02otdcRRea', '$2y$10$yLuPdFMltjhBIwY2pCpKgO', '1'),
(10, 'George', 'Corser', '989.780.3168', 'gpcorser@svsu.edu', '$2y$10$4aGnUDWV2Dap2S6o4A5Nb.uGf9n.EY5VjwQiHe5kY5GhK9ycV10Qa', '$2y$10$4aGnUDWV2Dap2S6o4A5Nb.', ''),
(12, 'Normal', 'Norman', '999999', 'normal', '$2y$10$umAS6NKl1vGxnoy0PzRcFeXiKyeQgDaiA7NH7WdE.4Wx/l.ar0Zba', '$2y$10$umAS6NKl1vGxnoy0PzRcFe', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `iss_comments`
--
ALTER TABLE `iss_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `iss_issues`
--
ALTER TABLE `iss_issues`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `iss_persons`
--
ALTER TABLE `iss_persons`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `iss_comments`
--
ALTER TABLE `iss_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `iss_issues`
--
ALTER TABLE `iss_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `iss_persons`
--
ALTER TABLE `iss_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
