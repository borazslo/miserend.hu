-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: mysql:3306
-- Létrehozás ideje: 2023. Jún 10. 14:19
-- Kiszolgáló verziója: 5.7.42
-- PHP verzió: 8.1.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

INSERT INTO `user` (`uid`, `login`, `jelszo`, `jogok`, `regdatum`, `lastlogin`, `lastactive`, `email`, `notifications`, `becenev`, `nev`, `volunteer`) VALUES
    (1, 'admin', '$2y$10$EBPN6VkozCpnTYjmbo5z/egO.ZozlXbIizohH6MEIN5dU0DPO0cnO', 'miserend-user', '0000-00-00 00:00:00', '2023-08-14 08:41:53', '2023-08-14 08:42:51', 'admin@miserend.nomail', 0, 'Adminka', 'Admin Admin', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
