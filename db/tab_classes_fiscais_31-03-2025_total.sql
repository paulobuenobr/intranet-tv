-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: 31-Mar-2025 às 13:15
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

-- --------------------------------------------------------

--
-- Estrutura da tabela `tab_classes_fiscais`
--

CREATE TABLE IF NOT EXISTS `tab_classes_fiscais` (
  `clf_id` tinyint(3) unsigned NOT NULL,
  `clf_taxa` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pla_id` smallint(8) unsigned DEFAULT NULL,
  `pls_id` tinyint(3) unsigned DEFAULT NULL,
  `afc_id` tinyint(3) unsigned DEFAULT NULL,
  `cft_id` tinyint(3) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;

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
(23, 100.00, 736, NULL, NULL, 16),
(25, 0.00, NULL, NULL, 10, 9),
(26, 0.00, NULL, 1, NULL, 8),
(27, 0.00, NULL, 2, NULL, 17),
(28, 0.00, NULL, 3, NULL, 8),
(29, 0.00, NULL, NULL, 33, 17),
(30, 0.00, NULL, NULL, 20, 18),
(31, 0.00, NULL, NULL, 19, 19);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tab_classes_fiscais`
--
ALTER TABLE `tab_classes_fiscais`
  ADD PRIMARY KEY (`clf_id`),
  ADD KEY `pls_id` (`pls_id`),
  ADD KEY `cft_id` (`cft_id`),
  ADD KEY `afc_id` (`afc_id`),
  ADD KEY `pla_id` (`pla_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tab_classes_fiscais`
--
ALTER TABLE `tab_classes_fiscais`
  MODIFY `clf_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=32;
--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `tab_classes_fiscais`
--
ALTER TABLE `tab_classes_fiscais`
  ADD CONSTRAINT `tab_classes_fiscais_afc_id` FOREIGN KEY (`afc_id`) REFERENCES `tab_ajustes_financeiros_categorias` (`afc_id`),
  ADD CONSTRAINT `tab_classes_fiscais_cft_id` FOREIGN KEY (`cft_id`) REFERENCES `tab_classes_fiscais_tipos` (`cft_id`),
  ADD CONSTRAINT `tab_classes_fiscais_pla_id` FOREIGN KEY (`pla_id`) REFERENCES `tab_planos` (`pla_id`),
  ADD CONSTRAINT `tab_classes_fiscais_pls_id` FOREIGN KEY (`pls_id`) REFERENCES `tab_planos_servicos` (`pls_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
