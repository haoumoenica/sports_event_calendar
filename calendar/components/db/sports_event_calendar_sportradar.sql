-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 30, 2024 at 05:07 PM
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
-- Database: `sports_event_calendar`
--

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id` int(11) NOT NULL,
  `date_time` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `_foreignkey_sport_id` int(11) NOT NULL,
  `score` varchar(255) DEFAULT NULL,
  `result_status` enum('win','lose','draw') DEFAULT 'draw',
  `_foreignkey_team1_id` int(11) DEFAULT NULL,
  `_foreignkey_team2_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`id`, `date_time`, `description`, `_foreignkey_sport_id`, `score`, `result_status`, `_foreignkey_team1_id`, `_foreignkey_team2_id`) VALUES
(6, '2024-12-01 20:00:00', 'FC Barcelona vs Real Madrid - Super Copa', 1, NULL, NULL, 11, 12),
(7, '2024-12-03 22:00:00', 'Manchester United vs FC Barcelona - Champions League', 1, NULL, NULL, 13, 11),
(8, '2024-12-03 19:30:00', 'Houston Rockets vs Boston Celtics - NBA Finals', 3, NULL, NULL, 14, 15),
(9, '2024-12-01 21:00:00', 'Red Bull Salzburg  vs Sturm Graz - Bundesliga', 1, NULL, NULL, 1, 2),
(10, '2024-12-04 19:00:00', 'Toronto Maple Leafs vs Boston Bruins - NHL Game', 2, NULL, NULL, 18, 19),
(11, '2024-12-21 20:00:00', 'Chicago Blackhawks vs Los Angeles Kings - NHL Game', 2, NULL, NULL, 20, 21),
(12, '2025-01-05 22:00:00', 'Golden State Warriors vs Boston Celtics - NBA Finals', 3, NULL, NULL, 16, 16),
(13, '2025-01-07 19:00:00', 'Real Madrid vs FC Barcelona - LaLiga ', 1, NULL, NULL, 12, 11),
(14, '2025-02-12 20:00:00', 'FC Barcelona vs Manchester United - UEFA Champions League', 1, NULL, NULL, 11, 13),
(15, '2025-02-15 19:30:00', 'LA Lakers vs Toronto Raptors - NBA Regular Season', 3, NULL, NULL, 5, 17),
(16, '2025-03-02 22:30:00', 'Real Madrid vs FC Barcelona - LaLiga', 1, NULL, NULL, 12, 11),
(17, '2025-03-05 20:00:00', 'Toronto Maple Leafs vs Los Angeles Kings - NHL Game', 2, NULL, NULL, 18, 21),
(18, '2025-03-10 19:00:00', 'Chicago Blackhawks vs Boston Bruins - NHL Game', 2, NULL, NULL, 20, 19),
(20, '2025-01-09 20:00:00', 'Boston Celtics vs Chicago Bulls - NBA Playoffs', 3, NULL, 'draw', 15, 6),
(21, '2024-12-03 20:00:00', 'Boston Red Sox vs NY Yankees - MLB', 5, NULL, 'draw', 9, 10),
(23, '2024-11-30 18:47:00', 'Golden State Warriors vs Toronto Raptors', 3, NULL, 'draw', 16, 17),
(24, '2024-11-30 18:48:00', 'Red Bull Salzburg vs Real Madrid', 1, NULL, 'draw', 1, 12);

-- --------------------------------------------------------

--
-- Table structure for table `event_team`
--

CREATE TABLE `event_team` (
  `id` int(11) NOT NULL,
  `_foreignkey_event_id` int(11) NOT NULL,
  `_foreignkey_team_id` int(11) NOT NULL,
  `score` int(11) DEFAULT 0,
  `result_status` enum('win','lose','draw') DEFAULT 'draw'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_team`
--

INSERT INTO `event_team` (`id`, `_foreignkey_event_id`, `_foreignkey_team_id`, `score`, `result_status`) VALUES
(21, 6, 11, 0, 'draw'),
(22, 6, 12, 0, 'draw'),
(23, 7, 6, 0, 'draw'),
(24, 7, 5, 0, 'draw'),
(25, 8, 3, 0, 'draw'),
(26, 8, 4, 0, 'draw'),
(27, 9, 1, 0, 'draw'),
(28, 9, 3, 0, 'draw'),
(29, 10, 5, 0, 'draw'),
(31, 11, 2, 0, 'draw'),
(32, 11, 1, 0, 'draw'),
(33, 12, 9, 0, 'draw'),
(34, 12, 12, 0, 'draw'),
(35, 13, 10, 0, 'draw');

-- --------------------------------------------------------

--
-- Table structure for table `event_venue`
--

CREATE TABLE `event_venue` (
  `id` int(11) NOT NULL,
  `_foreignkey_event_id` int(11) NOT NULL,
  `_foreignkey_venue_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_venue`
--

INSERT INTO `event_venue` (`id`, `_foreignkey_event_id`, `_foreignkey_venue_id`) VALUES
(11, 6, 8),
(12, 7, 10),
(13, 8, 20),
(14, 9, 1),
(15, 10, 14),
(16, 11, 2),
(17, 12, 6),
(19, 20, 12),
(20, 21, 6),
(22, 23, 13),
(23, 24, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sport`
--

CREATE TABLE `sport` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sport`
--

INSERT INTO `sport` (`id`, `name`) VALUES
(1, 'Football'),
(2, 'Ice Hockey'),
(3, 'Basketball'),
(5, 'Baseball');

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `_foreignkey_sport_id` int(11) NOT NULL,
  `_foreignkey_venue_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`id`, `name`, `logo`, `_foreignkey_sport_id`, `_foreignkey_venue_id`) VALUES
(1, 'Red Bull Salzburg', 'salzburg_logo.png', 1, 1),
(2, 'Sturm Graz', 'sturm_logo.png', 1, 19),
(3, 'KAC', 'kac_logo.png', 2, 21),
(4, 'Vienna Capitals', 'capitals_logo.png', 2, 2),
(5, 'LA Lakers', 'lakers_logo.png', 3, 18),
(6, 'Chicago Bulls', 'bulls_logo.png', 3, 17),
(9, 'Boston Red Sox', 'redsox_logo.png', 5, 6),
(10, 'NY Yankees', 'yankees_logo.png', 5, 7),
(11, 'FC Barcelona', 'barcelona_logo.png', 1, 8),
(12, 'Real Madrid', 'real_madrid_logo.png', 1, 9),
(13, 'Manchester United', 'man_utd_logo.png', 1, 10),
(14, 'Houston Rockets', 'rockets_logo.png', 3, 20),
(15, 'Boston Celtics', 'celtics_logo.png', 3, 12),
(16, 'Golden State Warriors', 'warriors_logo.png', 3, 13),
(17, 'Toronto Raptors', 'raptors_logo.png', 3, 14),
(18, 'Toronto Maple Leafs', 'maple_leafs_logo.png', 2, 14),
(19, 'Boston Bruins', 'bruins_logo.png', 2, 12),
(20, 'Chicago Blackhawks', 'black_hawks_logo.png', 2, 17),
(21, 'Los Angeles Kings', 'kings_logo.png', 2, 18);

-- --------------------------------------------------------

--
-- Table structure for table `venue`
--

CREATE TABLE `venue` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue`
--

INSERT INTO `venue` (`id`, `name`, `location`, `type`) VALUES
(1, 'Red Bull Arena', 'Salzburg', 'stadium'),
(2, 'Erste Bank Arena', 'Vienna', 'arena'),
(3, 'Staples Center', 'Los Angeles', 'stadium'),
(4, 'Madison Square Garden', 'New York', 'arena'),
(6, 'Fenway Park', 'Boston', 'stadium'),
(7, 'Yankee Stadium', 'New York', 'stadium'),
(8, 'Camp Nou', 'Barcelona', NULL),
(9, 'Santiago Bernabéu', 'Madrid', NULL),
(10, 'Old Trafford', 'Manchester', NULL),
(11, 'Staples Center', 'Los Angeles', NULL),
(12, 'TD Garden', 'Boston', NULL),
(13, 'Chase Center', 'San Francisco', NULL),
(14, 'Scotiabank Arena', 'Toronto', NULL),
(15, 'Scotiabank Saddledome', 'Calgary', NULL),
(16, 'TD Garden', 'Boston', NULL),
(17, 'United Center', 'Chicago', NULL),
(18, 'Crypto.com Arena', 'Los Angeles', NULL),
(19, 'Merkur Arena', 'Graz', 'arena'),
(20, 'Toyota Center', 'Houston', 'arena'),
(21, 'Stadthalle Klagenfurt', 'Klagenfurt', 'arena');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sport` (`_foreignkey_sport_id`),
  ADD KEY `fk_team1` (`_foreignkey_team1_id`),
  ADD KEY `fk_team2` (`_foreignkey_team2_id`);

--
-- Indexes for table `event_team`
--
ALTER TABLE `event_team`
  ADD PRIMARY KEY (`id`),
  ADD KEY `_foreignkey_event_id` (`_foreignkey_event_id`),
  ADD KEY `_foreignkey_team_id` (`_foreignkey_team_id`);

--
-- Indexes for table `event_venue`
--
ALTER TABLE `event_venue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `_foreignkey_event_id` (`_foreignkey_event_id`),
  ADD KEY `_foreignkey_venue_id` (`_foreignkey_venue_id`);

--
-- Indexes for table `sport`
--
ALTER TABLE `sport`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`),
  ADD KEY `_foreignkey_sport_id` (`_foreignkey_sport_id`),
  ADD KEY `fk_team_venue` (`_foreignkey_venue_id`);

--
-- Indexes for table `venue`
--
ALTER TABLE `venue`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `event_team`
--
ALTER TABLE `event_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `event_venue`
--
ALTER TABLE `event_venue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `sport`
--
ALTER TABLE `sport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `venue`
--
ALTER TABLE `venue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`_foreignkey_sport_id`) REFERENCES `sport` (`id`),
  ADD CONSTRAINT `fk_sport` FOREIGN KEY (`_foreignkey_sport_id`) REFERENCES `sport` (`id`),
  ADD CONSTRAINT `fk_team1` FOREIGN KEY (`_foreignkey_team1_id`) REFERENCES `team` (`id`),
  ADD CONSTRAINT `fk_team2` FOREIGN KEY (`_foreignkey_team2_id`) REFERENCES `team` (`id`);

--
-- Constraints for table `event_team`
--
ALTER TABLE `event_team`
  ADD CONSTRAINT `event_team_ibfk_1` FOREIGN KEY (`_foreignkey_event_id`) REFERENCES `event` (`id`),
  ADD CONSTRAINT `event_team_ibfk_2` FOREIGN KEY (`_foreignkey_team_id`) REFERENCES `team` (`id`);

--
-- Constraints for table `event_venue`
--
ALTER TABLE `event_venue`
  ADD CONSTRAINT `event_venue_ibfk_1` FOREIGN KEY (`_foreignkey_event_id`) REFERENCES `event` (`id`),
  ADD CONSTRAINT `event_venue_ibfk_2` FOREIGN KEY (`_foreignkey_venue_id`) REFERENCES `venue` (`id`);

--
-- Constraints for table `team`
--
ALTER TABLE `team`
  ADD CONSTRAINT `fk_team_venue` FOREIGN KEY (`_foreignkey_venue_id`) REFERENCES `venue` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `team_ibfk_1` FOREIGN KEY (`_foreignkey_sport_id`) REFERENCES `sport` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
