-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 26 jun 2014 om 21:54
-- Serverversie: 5.5.37-0ubuntu0.14.04.1
-- PHP-versie: 5.5.9-1ubuntu4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databank: `cloudwalkers_order`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cw_orders`
--

CREATE TABLE IF NOT EXISTS `cw_orders` (
  `o_id` int(11) NOT NULL AUTO_INCREMENT,
  `o_status` enum('INITIALIZED','CREATED') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'INITIALIZED',
  `o_accountname` text COLLATE utf8_unicode_ci NOT NULL,
  `o_email` text COLLATE utf8_unicode_ci NOT NULL,
  `o_firstName` text COLLATE utf8_unicode_ci NOT NULL,
  `o_lastname` text COLLATE utf8_unicode_ci NOT NULL,
  `o_password` text COLLATE utf8_unicode_ci,
  `o_plan` int(11) NOT NULL,
  `o_registration` datetime NOT NULL,
  `o_price` float NOT NULL,
  `o_account_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`o_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cw_payments`
--

CREATE TABLE IF NOT EXISTS `cw_payments` (
  `p_id` int(11) NOT NULL AUTO_INCREMENT,
  `o_id` int(11) NOT NULL,
  `p_price` float NOT NULL,
  `p_fee` float NOT NULL,
  `p_currency` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`p_id`),
  KEY `o_id` (`o_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Beperkingen voor gedumpte tabellen
--

--
-- Beperkingen voor tabel `cw_payments`
--
ALTER TABLE `cw_payments`
  ADD CONSTRAINT `cw_payments_ibfk_1` FOREIGN KEY (`o_id`) REFERENCES `cw_orders` (`o_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
