-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: 31-Mar-2025 às 13:02
-- Versão do servidor: 10.3.6-MariaDB
-- PHP Version: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tvintranet`
--

--
-- Extraindo dados da tabela `tab_classes_fiscais`
--

INSERT INTO `tab_classes_fiscais` (`clf_id`, `clf_taxa`, `pla_id`, `pls_id`, `afc_id`, `cft_id`) VALUES
(1, 0.00, 729, NULL, NULL, 1),
(2, 0.00, 730, NULL, NULL, 2),
(3, 0.00, 731, NULL, NULL, 3),
(4, 0.00, 732, NULL, NULL, 4),
(5, 0.00, 734, NULL, NULL, 5),
(6, 0.00, 733, NULL, NULL, 6),
(7, 0.00, 735, NULL, NULL, 7),
(8, 100.00, NULL, NULL, 27, 8),
(10, 0.00, 749, NULL, NULL, 1),
(11, 0.00, 748, NULL, NULL, 11),
(12, 0.00, 752, NULL, NULL, 1),
(13, 100.00, 751, NULL, NULL, 12),
(14, 100.00, 753, NULL, NULL, 12),
(15, 0.00, 752, NULL, NULL, 1),
(16, 0.00, 756, NULL, NULL, 2),
(17, 0.00, 757, NULL, NULL, 4),
(19, 0.00, 765, NULL, NULL, 13),
(20, 0.00, 766, NULL, NULL, 14),
(21, 0.00, 764, NULL, NULL, 15),
(22, 0.00, 763, NULL, NULL, 4),
(25, 0.00, NULL, NULL, 10, 9),
(26, 0.00, NULL, 1, NULL, 8),
(28, 0.00, NULL, 3, NULL, 8);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
