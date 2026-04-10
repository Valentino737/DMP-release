-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Pát 10. dub 2026, 03:27
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `dmp`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `brand`
--

CREATE TABLE `brand` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `builds`
--

CREATE TABLE `builds` (
  `id` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `image_mime_type` varchar(50) DEFAULT NULL,
  `image_uploaded_at` timestamp NULL DEFAULT NULL,
  `isPublic` tinyint(1) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `builds`
--

INSERT INTO `builds` (`id`, `userId`, `name`, `description`, `image_path`, `image_mime_type`, `image_uploaded_at`, `isPublic`, `createdAt`, `updatedAt`) VALUES
(15, 19, 'Druhý herní počítač 2026', 'Tento rok jsem již postavil druhý herní počítač. Tuto sestavu jsem vybral se záměrem využít moderní komponenty, s celkem relativně přijatelnou cenou.', NULL, NULL, NULL, 1, '2026-04-07 10:48:35', '2026-04-07 10:48:35'),
(16, 19, 'Levná herní / kancelářská sestava', 'Sestava pro někoho, kdo nemá velký \"budget\", ale chce si něco zahrát po udělaní své práce.', NULL, NULL, NULL, 1, '2026-04-07 10:51:14', '2026-04-07 10:51:14'),
(18, 21, 'Herní bestie 2025', 'Vysokovýkonná herní sestava postavená na platformě AM5 s procesorem Ryzen 7 7800X3D a grafickou kartou RTX 4090. Ideální pro hraní v rozlišení 4K při maximálních detailech.', NULL, NULL, NULL, 1, '2025-12-15 09:00:00', '2025-12-15 09:00:00'),
(19, 23, 'Kancelářský tichoun', 'Tichý a úsporný počítač pro kancelářskou práci, prohlížení webu a sledování videí. Skvělý poměr cena/výkon pro každodenní použití.', NULL, NULL, NULL, 1, '2026-01-10 13:30:00', '2026-01-10 13:30:00'),
(20, 25, 'Rozpočtovka pro studenta', 'Cenově dostupná sestava pro studenty, která zvládne jak školní práci, tak občasné hraní her ve Full HD. Postavena na osvědčené platformě AM4.', 'assets/images/builds/build_20_1775718694.jpg', 'image/jpeg', '2026-04-09 07:11:34', 1, '2026-02-01 08:15:00', '2026-02-01 08:15:00'),
(21, 24, 'Streaming & tvorba obsahu', 'Výkonná pracovní stanice pro streamování, střih videa a tvorbu obsahu. 16jádrový procesor Ryzen 9 9950X v kombinaci s RTX 4080 zajistí plynulý multitasking.', NULL, NULL, NULL, 1, '2026-02-10 10:45:00', '2026-02-10 10:45:00'),
(22, 26, 'Retro herní klasika', 'Nenáročná herní sestava pro starší i novější tituly. Ryzen 5 3600 a GTX 1660 Super zvládnou většinu her na střední detaily bez problémů.', NULL, NULL, NULL, 1, '2026-02-20 15:00:00', '2026-02-20 15:00:00'),
(23, 22, 'Intel Ultra Gaming', 'Herní sestava s procesorem Intel Core i7-14700K a RTX 4070 Ti. Vynikající volba pro hraní v rozlišení 1440p s vysokým snímkovým poměrem.', NULL, NULL, NULL, 1, '2026-03-01 07:30:00', '2026-03-01 07:30:00'),
(24, 27, 'Mini ITX Powerhouse', 'Kompaktní a výkonná sestava v malém formátu Mini-ITX. Důkaz, že i malý počítač může mít velký výkon díky Ryzen 5 9600X a Radeon RX 7700 XT.', NULL, NULL, NULL, 1, '2026-03-10 11:00:00', '2026-03-10 11:00:00'),
(25, 28, 'AMD Red Team Dream', 'Kompletně AMD sestava – Ryzen 9 7950X s Radeon RX 7900 XTX. Pro fanoušky červeného týmu, kteří chtějí maximální výkon bez kompromisů.', 'assets/images/builds/build_25_1775718557.webp', 'image/webp', '2026-04-09 07:09:17', 1, '2026-03-15 14:20:00', '2026-03-15 14:20:00');

-- --------------------------------------------------------

--
-- Struktura tabulky `case`
--

CREATE TABLE `case` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `max_gpu` int(11) DEFAULT NULL,
  `mboard_type` set('Mini-ITX','Micro-ATX','ATX','E-ATX') NOT NULL,
  `psu_type` enum('ATX','SFX','TFX','EPS') NOT NULL,
  `case_type` enum('SFF','Mini','Micro','Mid','Full') NOT NULL,
  `max_cooler` int(11) DEFAULT NULL,
  `expansion_slots` int(11) DEFAULT NULL,
  `front_rad` int(11) DEFAULT NULL,
  `top_rad` int(11) DEFAULT NULL,
  `max_psu` int(11) DEFAULT NULL,
  `rear_rad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `case`
--

INSERT INTO `case` (`id`, `name`, `price`, `max_gpu`, `mboard_type`, `psu_type`, `case_type`, `max_cooler`, `expansion_slots`, `front_rad`, `top_rad`, `max_psu`, `rear_rad`) VALUES
(1, 'NZXT H510', 1990, 381, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 165, 7, 280, 120, 200, 120),
(2, 'NZXT H7 Flow', 3290, 400, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 185, 7, 360, 360, 220, 140),
(3, 'Corsair 4000D Airflow', 2490, 360, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 170, 7, 360, 280, 180, 120),
(4, 'Corsair 5000D Airflow', 3990, 420, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 170, 7, 360, 360, 225, 120),
(5, 'Corsair 7000D Airflow', 5990, 450, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Full', 190, 8, 420, 420, 225, 140),
(6, 'Fractal Design Meshify C', 2490, 315, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 170, 7, 360, 240, 175, 120),
(7, 'Fractal Design Meshify 2', 3990, 491, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 185, 7, 360, 360, 250, 140),
(8, 'Fractal Torrent', 4990, 461, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 188, 7, 420, 360, 230, 140),
(9, 'be quiet! Pure Base 500DX', 2590, 369, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 190, 7, 360, 240, 225, 140),
(10, 'be quiet! Silent Base 802', 4590, 432, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 185, 7, 420, 360, 288, 140),
(11, 'Lian Li Lancool 215', 1990, 370, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 166, 7, 360, 240, 210, 120),
(12, 'Lian Li Lancool III', 3990, 435, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 187, 8, 420, 360, 220, 140),
(13, 'Lian Li O11 Dynamic EVO', 4290, 426, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 167, 8, 360, 360, 220, 120),
(14, 'Phanteks Eclipse P300A', 1590, 355, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 160, 7, 280, 120, 200, 120),
(15, 'Phanteks Eclipse P400A', 2190, 420, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 160, 7, 360, 240, 270, 120),
(16, 'Phanteks Enthoo Pro 2', 3890, 503, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Full', 195, 8, 480, 360, 220, 140),
(17, 'Cooler Master NR200P', 2190, 330, 'Mini-ITX', 'SFX', 'SFF', 155, 3, 280, 240, 130, 92),
(18, 'Cooler Master MasterBox Q300L', 1290, 360, 'Mini-ITX,Micro-ATX', 'ATX', 'Micro', 159, 4, 240, 120, 160, 120),
(19, 'Cooler Master HAF 700', 8990, 490, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Full', 190, 8, 480, 420, 200, 140),
(20, 'Thermaltake Versa H18', 990, 350, 'Mini-ITX,Micro-ATX', 'ATX', 'Micro', 155, 4, 240, 120, 220, 120),
(21, 'Thermaltake Core P3', 3490, 450, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 180, 8, 360, 360, 200, 120),
(22, 'Deepcool Matrexx 55', 1390, 370, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 168, 7, 360, 240, 170, 120),
(23, 'Deepcool CH560', 2390, 380, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 175, 7, 360, 360, 170, 140),
(24, 'SilverStone SG13', 1490, 266, 'Mini-ITX', 'SFX', 'SFF', 61, 2, 120, 0, 150, 0),
(25, 'SilverStone RL06', 1890, 348, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 158, 7, 360, 240, 200, 120),
(26, 'ASUS TUF GT301', 2490, 320, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 160, 7, 360, 240, 160, 120),
(27, 'ASUS ROG Hyperion GR701', 9990, 460, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Full', 190, 9, 420, 420, 240, 140),
(28, 'Kolink Observatory Lite', 1190, 330, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 160, 7, 360, 240, 200, 120),
(29, 'Kolink Citadel Mesh', 1290, 345, 'Mini-ITX,Micro-ATX', 'ATX', 'Micro', 162, 4, 240, 240, 200, 120),
(30, 'InWin A1 Plus', 4990, 320, 'Mini-ITX', 'SFX', 'Mini', 160, 2, 240, 120, 160, 120),
(31, 'InWin 303', 2890, 350, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 160, 7, 360, 0, 200, 120),
(32, 'Antec NX410', 1590, 335, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 168, 7, 360, 240, 165, 120),
(33, 'Antec P120 Crystal', 3290, 450, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 185, 7, 360, 360, 200, 120),
(34, 'Gigabyte C200 Glass', 1690, 330, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 165, 7, 360, 240, 160, 120),
(35, 'Gigabyte Aorus C500', 3990, 420, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Full', 190, 7, 420, 360, 200, 140),
(100, 'MiniTest SFF', 999, 220, 'Mini-ITX', 'SFX', 'SFF', 120, 2, 120, 0, 100, 80),
(101, 'Corsair 7000D Airflow', 5490, 450, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Full', 190, 8, 360, 420, 230, 140),
(102, 'be quiet! Dark Base Pro 901', 6990, 450, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Full', 185, 8, 420, 360, 225, 140),
(103, 'Phanteks Enthoo Pro II', 4990, 503, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Full', 195, 10, 480, 420, 280, 140),
(104, 'Lian Li O11D EVO XL', 5290, 460, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Full', 167, 8, 360, 360, 220, 140),
(105, 'Corsair 4000D Airflow', 2190, 360, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 170, 7, 360, 280, 180, 120),
(106, 'Corsair 5000D Airflow', 3490, 420, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 170, 7, 360, 360, 225, 120),
(107, 'NZXT H7 Flow', 3290, 400, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 185, 7, 360, 360, 230, 120),
(108, 'NZXT H5 Flow', 2490, 365, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 165, 7, 280, 240, 180, 120),
(109, 'Lian Li Lancool III', 2990, 435, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 187, 8, 360, 360, 210, 140),
(110, 'Lian Li O11 Dynamic EVO', 3490, 420, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 167, 8, 360, 360, 200, 140),
(111, 'be quiet! Pure Base 500DX', 2490, 369, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 190, 7, 360, 240, 225, 140),
(112, 'Fractal Design North', 2990, 355, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 170, 7, 280, 240, 200, 120),
(113, 'Fractal Design Meshify 2', 3490, 467, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 185, 7, 360, 360, 250, 140),
(114, 'Fractal Design Torrent', 4290, 461, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 188, 7, 360, 0, 200, 120),
(115, 'Phanteks Eclipse G360A', 2190, 380, 'Mini-ITX,Micro-ATX,ATX', 'ATX', 'Mid', 163, 7, 360, 240, 200, 120),
(116, 'Phanteks NV7', 4990, 440, 'Mini-ITX,Micro-ATX,ATX,E-ATX', 'ATX', 'Mid', 185, 8, 420, 360, 220, 140),
(117, 'Fractal Design Meshify 2 Mini', 2290, 338, 'Mini-ITX,Micro-ATX', 'ATX', 'Micro', 169, 5, 280, 240, 175, 120),
(118, 'Cooler Master NR200P Max', 4990, 336, 'Mini-ITX', 'SFX', 'Mini', 155, 3, 0, 280, 130, 0),
(119, 'NZXT H1 V2', 5490, 324, 'Mini-ITX', 'SFX', 'SFF', 0, 2, 0, 0, 0, 140),
(120, 'Lian Li A4-H2O', 3990, 322, 'Mini-ITX', 'SFX', 'SFF', 55, 3, 0, 0, 130, 240);

-- --------------------------------------------------------

--
-- Struktura tabulky `component_submissions`
--

CREATE TABLE `component_submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `componentType` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejectionReason` text DEFAULT NULL,
  `isPriority` tinyint(1) DEFAULT 0,
  `reviewedBy` int(10) UNSIGNED DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `component_submissions`
--

INSERT INTO `component_submissions` (`id`, `userId`, `componentType`, `name`, `brand`, `price`, `color`, `specifications`, `status`, `rejectionReason`, `isPriority`, `reviewedBy`, `createdAt`, `reviewedAt`) VALUES
(4, 23, 'gpu', 'testing', NULL, NULL, NULL, NULL, 'approved', NULL, 0, 30, '2026-04-09 07:39:16', '2026-04-09 07:45:08'),
(5, 30, 'psu', 'TestPSU', 'intel', NULL, NULL, NULL, 'rejected', 'neni', 1, 30, '2026-04-09 09:11:18', '2026-04-09 09:11:36'),
(6, 30, 'psu', 'testrtteadasdas', NULL, NULL, NULL, NULL, 'approved', NULL, 1, 30, '2026-04-09 09:12:19', '2026-04-09 09:12:28');

-- --------------------------------------------------------

--
-- Struktura tabulky `cooler`
--

CREATE TABLE `cooler` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `type` enum('Air','AIO') NOT NULL,
  `socket_support` set('AM4','AM5','LGA1151','LGA1200','LGA1700') NOT NULL,
  `height` smallint(5) UNSIGNED DEFAULT NULL,
  `radiator_size` smallint(5) UNSIGNED DEFAULT NULL,
  `fan_size` smallint(5) UNSIGNED DEFAULT NULL,
  `noise_level` decimal(4,1) DEFAULT NULL,
  `tdp` smallint(5) UNSIGNED DEFAULT NULL,
  `rgb` tinyint(1) NOT NULL,
  `color` varchar(50) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `cooler`
--

INSERT INTO `cooler` (`id`, `name`, `price`, `type`, `socket_support`, `height`, `radiator_size`, `fan_size`, `noise_level`, `tdp`, `rgb`, `color`, `brand`, `createdAt`, `updatedAt`) VALUES
(1, 'Cooler Master Hyper 212 Black', 1000.00, 'Air', 'AM4,AM5,LGA1151,LGA1200,LGA1700', 159, NULL, 120, 26.0, 150, 0, 'Black', 'Cooler Master', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(2, 'be quiet! Pure Rock 2', 1120.00, 'Air', 'AM4,AM5,LGA1151,LGA1200,LGA1700', 155, NULL, 120, 26.8, 150, 0, 'Black', 'be quiet!', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(3, 'be quiet! Dark Rock 4', 1870.00, 'Air', 'AM4,AM5,LGA1151,LGA1200,LGA1700', 159, NULL, 135, 21.4, 200, 0, 'Black', 'be quiet!', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(4, 'Noctua NH-U12S', 1750.00, 'Air', 'AM4,AM5,LGA1151,LGA1200,LGA1700', 158, NULL, 120, 22.4, 180, 0, 'Brown', 'Noctua', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(5, 'Noctua NH-D15', 2750.00, 'Air', 'AM4,AM5,LGA1151,LGA1200,LGA1700', 165, NULL, 140, 24.6, 250, 0, 'Brown', 'Noctua', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(6, 'DeepCool AK400', 870.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 155, NULL, 120, 29.0, 220, 0, 'Black', 'DeepCool', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(7, 'DeepCool AK620', 1750.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 160, NULL, 120, 28.0, 260, 0, 'Black', 'DeepCool', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(8, 'Arctic Freezer 34 eSports DUO', 1070.00, 'Air', 'AM4,AM5,LGA1151,LGA1200,LGA1700', 157, NULL, 120, 24.0, 200, 1, 'Black', 'Arctic', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(9, 'Scythe Fuma 2 Rev.B', 1620.00, 'Air', 'AM4,AM5,LGA1151,LGA1200,LGA1700', 155, NULL, 120, 25.0, 220, 0, 'Black', 'Scythe', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(10, 'Thermalright Peerless Assassin 120', 1250.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 157, NULL, 120, 25.6, 260, 0, 'Black', 'Thermalright', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(11, 'Corsair H60x RGB Elite', 2250.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 120, 120, 30.0, 180, 1, 'Black', 'Corsair', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(12, 'NZXT Kraken 120', 2500.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 120, 120, 30.5, 180, 1, 'Black', 'NZXT', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(13, 'Corsair H100x RGB Elite', 3000.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 240, 120, 32.0, 250, 1, 'Black', 'Corsair', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(14, 'NZXT Kraken 240', 4000.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 240, 120, 33.0, 280, 1, 'Black', 'NZXT', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(15, 'Arctic Liquid Freezer II 240', 2750.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 240, 120, 22.5, 300, 0, 'Black', 'Arctic', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(16, 'DeepCool LS520', 3000.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 240, 120, 32.9, 280, 1, 'Black', 'DeepCool', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(17, 'Corsair H115i RGB Elite', 4250.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 280, 140, 34.0, 300, 1, 'Black', 'Corsair', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(18, 'NZXT Kraken 280', 5000.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 280, 140, 34.0, 300, 1, 'Black', 'NZXT', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(19, 'Corsair H150i RGB Elite', 5000.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 35.0, 320, 1, 'Black', 'Corsair', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(20, 'NZXT Kraken 360', 5750.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 36.0, 320, 1, 'Black', 'NZXT', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(21, 'Arctic Liquid Freezer II 360', 3500.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 22.5, 350, 0, 'Black', 'Arctic', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(22, 'DeepCool LS720', 3750.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 32.9, 320, 1, 'Black', 'DeepCool', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(23, 'Corsair H150i RGB Elite White', 5250.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 35.0, 320, 1, 'White', 'Corsair', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(24, 'NZXT Kraken 240 White', 4250.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 240, 120, 33.0, 280, 1, 'White', 'NZXT', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(25, 'DeepCool AK400 White', 1000.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 155, NULL, 120, 29.0, 220, 0, 'White', 'DeepCool', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(26, 'Thermalright Peerless Assassin 120 White', 1370.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 157, NULL, 120, 25.6, 260, 0, 'White', 'Thermalright', '2026-04-02 15:28:44', '2026-04-08 12:44:41'),
(27, 'Corsair iCUE H170i Elite LCD', 6990.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 420, 140, 30.0, 350, 1, 'Black', 'Corsair', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(28, 'Corsair iCUE H150i Elite LCD', 5490.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 30.0, 300, 1, 'Black', 'Corsair', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(29, 'Corsair iCUE H100i Elite LCD', 4490.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 240, 120, 28.0, 250, 1, 'Black', 'Corsair', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(30, 'NZXT Kraken Elite 360', 5990.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 27.0, 300, 1, 'Black', 'NZXT', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(31, 'NZXT Kraken Elite 280', 5290.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 280, 140, 26.0, 280, 1, 'Black', 'NZXT', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(32, 'NZXT Kraken 240', 3490.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 240, 120, 28.0, 250, 1, 'Black', 'NZXT', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(33, 'Lian Li Galahad II Trinity 360', 4490.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 28.5, 300, 1, 'Black', 'Lian Li', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(34, 'Lian Li Galahad II Trinity 240', 3290.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 240, 120, 27.5, 250, 1, 'White', 'Lian Li', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(35, 'Arctic Liquid Freezer III 360', 2990.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 26.0, 300, 0, 'Black', 'Arctic', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(36, 'Arctic Liquid Freezer III 280', 2690.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 280, 140, 25.0, 280, 0, 'Black', 'Arctic', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(37, 'Arctic Liquid Freezer III 240', 2290.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 240, 120, 25.5, 250, 0, 'Black', 'Arctic', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(38, 'ASUS ROG Ryujin III 360 ARGB', 6490.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 29.0, 300, 1, 'Black', 'ASUS', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(39, 'MSI MEG CoreLiquid S360', 5990.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 28.0, 300, 1, 'Black', 'MSI', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(40, 'be quiet! Silent Loop 2 360', 3990.00, 'AIO', 'AM4,AM5,LGA1200,LGA1700', NULL, 360, 120, 24.0, 300, 0, 'Black', 'be quiet!', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(41, 'Noctua NH-D15 G2', 3490.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 168, NULL, 150, 24.6, 280, 0, 'Brown', 'Noctua', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(42, 'Noctua NH-D15S chromax.black', 3290.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 160, NULL, 150, 24.6, 260, 0, 'Black', 'Noctua', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(43, 'Noctua NH-U12S chromax.black', 2190.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 158, NULL, 120, 22.4, 200, 0, 'Black', 'Noctua', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(44, 'be quiet! Dark Rock Pro 5', 2290.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 168, NULL, 135, 24.3, 270, 0, 'Black', 'be quiet!', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(45, 'be quiet! Dark Rock Elite', 2690.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 168, NULL, 135, 23.0, 280, 0, 'Black', 'be quiet!', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(46, 'be quiet! Pure Rock 2', 1090.00, 'Air', 'AM4,AM5,LGA1151,LGA1200,LGA1700', 155, NULL, 120, 26.8, 150, 0, 'Black', 'be quiet!', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(47, 'Deepcool AK620 Digital', 1890.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 160, NULL, 120, 28.0, 260, 1, 'Black', 'Deepcool', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(48, 'Deepcool Assassin IV', 2490.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 164, NULL, 140, 25.0, 280, 0, 'Black', 'Deepcool', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(49, 'Thermalright Peerless Assassin 120 SE', 990.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 155, NULL, 120, 25.6, 220, 0, 'Silver', 'Thermalright', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(50, 'Thermalright Frost Commander 140', 1290.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 163, NULL, 140, 26.0, 260, 0, 'Black', 'Thermalright', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(51, 'Arctic Freezer 36', 990.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 159, NULL, 120, 25.0, 210, 0, 'Black', 'Arctic', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(52, 'Cooler Master Hyper 212 Halo', 1190.00, 'Air', 'AM4,AM5,LGA1151,LGA1200,LGA1700', 157, NULL, 120, 27.0, 180, 1, 'Black', 'Cooler Master', '2026-04-09 16:02:21', '2026-04-09 16:02:21'),
(53, 'ID-COOLING SE-226-XT', 990.00, 'Air', 'AM4,AM5,LGA1200,LGA1700', 154, NULL, 120, 26.5, 220, 0, 'Black', 'ID-COOLING', '2026-04-09 16:02:21', '2026-04-09 16:02:21');

-- --------------------------------------------------------

--
-- Struktura tabulky `cpu`
--

CREATE TABLE `cpu` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `socket` varchar(20) NOT NULL,
  `microarchitecture` varchar(50) DEFAULT NULL,
  `cores` int(10) UNSIGNED DEFAULT NULL,
  `threads` int(10) UNSIGNED DEFAULT NULL,
  `core_clock` decimal(3,1) DEFAULT NULL,
  `boost_clock` decimal(3,1) DEFAULT NULL,
  `ram` enum('DDR2','DDR3','DDR4','DDR5') DEFAULT NULL,
  `ram_count` tinyint(3) UNSIGNED DEFAULT NULL,
  `tdp` smallint(5) UNSIGNED DEFAULT NULL,
  `graphics` varchar(50) DEFAULT NULL,
  `l2_cache` int(10) UNSIGNED DEFAULT NULL,
  `l3_cache` int(10) UNSIGNED DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `cpu`
--

INSERT INTO `cpu` (`id`, `name`, `price`, `socket`, `microarchitecture`, `cores`, `threads`, `core_clock`, `boost_clock`, `ram`, `ram_count`, `tdp`, `graphics`, `l2_cache`, `l3_cache`, `brand`, `color`) VALUES
(2, 'AMD Ryzen 7 9800X3D', 10400, 'AM5', 'Zen 5', 8, 16, 4.7, 5.2, 'DDR5', 2, 120, 'Radeon', 8, 96, 'AMD', NULL),
(3, 'AMD Ryzen 7 7800X3D', 7820, 'AM5', 'Zen 4', 8, 16, 4.2, 5.0, 'DDR5', 2, 120, 'Radeon', 8, 96, 'AMD', NULL),
(4, 'AMD Ryzen 5 7600X', 3910, 'AM5', 'Zen 4', 6, 12, 4.7, 5.3, 'DDR5', 2, 105, 'Radeon', 6, 32, 'AMD', NULL),
(5, 'AMD Ryzen 5 9600X', 4720, 'AM5', 'Zen 5', 6, 12, 3.9, 5.4, 'DDR5', 2, 65, 'Radeon', 6, 32, 'AMD', NULL),
(6, 'AMD Ryzen 7 7700X', 5590, 'AM5', 'Zen 4', 8, 16, 4.5, 5.4, 'DDR5', 2, 105, 'Radeon', 8, 32, 'AMD', NULL),
(7, 'AMD Ryzen 9 9950X3D', 14950, 'AM5', 'Zen 5', 16, 32, 4.3, 5.7, 'DDR5', 2, 170, 'Radeon', 16, 128, 'AMD', NULL),
(8, 'AMD Ryzen 5 5500', 1700, 'AM4', 'Zen 3', 6, 12, 3.6, 4.2, 'DDR4', 2, 65, '', 3, 32, 'AMD', NULL),
(9, 'AMD Ryzen 7 9700X', 7040, 'AM5', 'Zen 5', 8, 16, 3.8, 5.5, 'DDR5', 2, 65, 'Radeon', 8, 32, 'AMD', NULL),
(10, 'AMD Ryzen 5 5600X', 3660, 'AM4', 'Zen 3', 6, 12, 3.7, 4.6, 'DDR4', 2, 65, '', 3, 32, 'AMD', NULL),
(11, 'AMD Ryzen 5 5600', 2900, 'AM4', 'Zen 3', 6, 12, 3.5, 4.4, 'DDR4', 2, 65, '', 3, 32, 'AMD', NULL),
(12, 'AMD Ryzen 7 5800X', 4140, 'AM4', 'Zen 3', 8, 16, 3.8, 4.7, 'DDR4', 2, 105, '', 4, 32, 'AMD', NULL),
(13, 'Intel Core i7-14700K', 6830, 'LGA1700', 'Raptor Lake Refresh', 20, 40, 3.4, 5.6, 'DDR5', 2, 125, 'Intel UHD Graphics 770', 28, 33, 'Intel', NULL),
(14, 'Intel Core i9-14900K', 10100, 'LGA1700', 'Raptor Lake Refresh', 24, 48, 3.2, 6.0, 'DDR5', 2, 125, 'Intel UHD Graphics 770', 32, 36, 'Intel', NULL),
(15, 'Intel Core i5-12400F', 2510, 'LGA1700', 'Alder Lake', 6, 12, 2.5, 4.4, 'DDR5', 2, 65, '', 8, 18, 'Intel', NULL),
(16, 'AMD Ryzen 5 3600', 1840, 'AM4', 'Zen 2', 6, 12, 3.6, 4.2, 'DDR4', 2, 65, '', 3, 32, 'AMD', NULL),
(17, 'AMD Ryzen 5 7600', 4530, 'AM5', 'Zen 4', 6, 12, 3.8, 5.1, 'DDR5', 2, 65, 'Radeon', 6, 32, 'AMD', NULL),
(18, 'AMD Ryzen 7 5700X', 3570, 'AM4', 'Zen 3', 8, 16, 3.4, 4.6, 'DDR4', 2, 65, '', 4, 32, 'AMD', NULL),
(19, 'AMD Ryzen 9 7900X', 7220, 'AM5', 'Zen 4', 12, 24, 4.7, 5.6, 'DDR5', 2, 170, 'Radeon', 12, 64, 'AMD', NULL),
(20, 'AMD Ryzen 9 9900X', 8260, 'AM5', 'Zen 5', 12, 24, 4.4, 5.6, 'DDR5', 2, 120, 'Radeon', 12, 64, 'AMD', NULL),
(21, 'Intel Core i5-14600K', 4370, 'LGA1700', 'Raptor Lake Refresh', 14, 28, 3.5, 5.3, 'DDR5', 2, 125, 'Intel UHD Graphics 770', 20, 24, 'Intel', NULL),
(22, 'Intel Core Ultra 7 265K', 6210, 'LGA1700', 'Arrow Lake', 20, 40, 3.9, 5.5, 'DDR5', 2, 125, 'Intel Xe', 36, 30, 'Intel', NULL),
(23, 'AMD Ryzen 7 5700X3D', 7200, 'AM4', 'Zen 3', 8, 16, 3.0, 4.1, 'DDR4', 2, 105, '', 4, 96, 'AMD', NULL),
(24, 'Intel Core i5-14400F', 2900, 'LGA1700', 'Raptor Lake Refresh', 10, 20, 2.5, 4.7, 'DDR5', 2, 65, '', 10, 20, 'Intel', NULL),
(25, 'AMD Ryzen 9 9950X', 12280, 'AM5', 'Zen 5', 16, 32, 4.3, 5.7, 'DDR5', 2, 170, 'Radeon', 16, 64, 'AMD', NULL),
(26, 'AMD Ryzen 5 7500F', 4600, 'AM5', 'Zen 4', 6, 12, 3.7, 5.0, 'DDR5', 2, 65, '', 6, 32, 'AMD', NULL),
(27, 'AMD Ryzen 7 5800XT', 3220, 'AM4', 'Zen 3', 8, 16, 3.8, 4.8, 'DDR4', 2, 105, '', 4, 32, 'AMD', NULL),
(28, 'Intel Core Ultra 9 285K', 12880, 'LGA1700', 'Arrow Lake', 24, 48, 3.7, 5.7, 'DDR5', 2, 125, 'Intel Xe', 40, 36, 'Intel', NULL),
(29, 'Intel Core i7-12700K', 4920, 'LGA1700', 'Alder Lake', 12, 24, 3.6, 5.0, 'DDR5', 2, 125, 'Intel UHD Graphics 770', 12, 25, 'Intel', NULL),
(30, 'AMD Ryzen 5 7600X3D', 9980, 'AM5', 'Zen 4', 6, 12, 4.1, 4.7, 'DDR5', 2, 65, 'Radeon', 6, 96, 'AMD', NULL),
(31, 'AMD Ryzen 5 5600G', 3110, 'AM4', 'Zen 3', 6, 12, 3.9, 4.4, 'DDR4', 2, 65, 'Radeon Vega 7', 3, 32, 'AMD', NULL),
(32, 'AMD Ryzen 7 7700', 6760, 'AM5', 'Zen 4', 8, 16, 3.6, 5.3, 'DDR5', 2, 65, 'Radeon', 8, 32, 'AMD', NULL),
(33, 'Intel Core i5-13400F', 3470, 'LGA1700', 'Raptor Lake', 10, 20, 2.5, 4.6, 'DDR5', 2, 65, '', 10, 20, 'Intel', NULL),
(34, 'AMD Ryzen 5 7500F', 4600, 'AM5', 'Zen 4', 6, 12, 3.7, 5.0, 'DDR5', 2, 65, '', 6, 32, 'AMD', NULL),
(35, 'Intel Core i7-12700KF', 4030, 'LGA1700', 'Alder Lake', 12, 24, 3.6, 5.0, 'DDR5', 2, 125, '', 12, 25, 'Intel', NULL),
(36, 'AMD Ryzen 9 5900X', 5870, 'AM4', 'Zen 3', 12, 24, 3.7, 4.8, 'DDR4', 2, 105, '', 6, 64, 'AMD', NULL),
(37, 'AMD Ryzen 7 3700X', 3290, 'AM4', 'Zen 2', 8, 16, 3.6, 4.4, 'DDR4', 2, 65, '', 4, 32, 'AMD', NULL),
(38, 'Intel Core i5-12600KF', 2990, 'LGA1700', 'Alder Lake', 10, 20, 3.7, 4.9, 'DDR5', 2, 125, '', 10, 20, 'Intel', NULL),
(39, 'AMD Ryzen 5 8400F', 3170, 'AM5', 'Zen 4', 6, 12, 4.2, 4.7, 'DDR5', 2, 65, '', 6, 16, 'AMD', NULL),
(40, 'AMD Ryzen 5 8500G', 3520, 'AM5', 'Zen 4', 6, 12, 4.1, 5.0, 'DDR5', 2, 65, 'Radeon 740M', 6, 16, 'AMD', NULL),
(41, 'AMD Threadripper 3990X', 62100, 'sTRX4', 'Zen 2', 64, 128, 2.9, 4.3, 'DDR4', 2, 280, '', 32, 256, 'AMD', NULL),
(42, 'Intel Core i5-12600K', 3400, 'LGA1700', 'Alder Lake', 10, 20, 3.7, 4.9, 'DDR5', 2, 125, 'Intel UHD Graphics 770', 10, 20, 'Intel', NULL),
(43, 'AMD Ryzen 7 5700G', 4030, 'AM4', 'Zen 3', 8, 16, 3.8, 4.6, 'DDR4', 2, 65, 'Radeon Vega 8', 4, 32, 'AMD', NULL),
(44, 'AMD Ryzen 9 5950X', 6330, 'AM4', 'Zen 3', 16, 32, 3.4, 4.9, 'DDR4', 2, 105, '', 8, 64, 'AMD', NULL),
(45, 'AMD Ryzen 7 5800X3D', 16220, 'AM4', 'Zen 3', 8, 16, 3.4, 4.5, 'DDR4', 2, 105, '', 4, 96, 'AMD', NULL),
(46, 'Intel Core i3-12100F', 1820, 'LGA1700', 'Alder Lake', 4, 8, 3.3, 4.3, 'DDR5', 2, 58, '', 5, 12, 'Intel', NULL),
(47, 'AMD Ryzen 9 7950X', 10350, 'AM5', 'Zen 4', 16, 32, 4.5, 5.7, 'DDR5', 2, 170, 'Radeon', 16, 64, 'AMD', NULL),
(48, 'Intel Core i5-14600KF', 4550, 'LGA1700', 'Raptor Lake Refresh', 14, 28, 3.5, 5.3, 'DDR5', 2, 125, '', 20, 24, 'Intel', NULL),
(49, 'AMD Ryzen 5 4500', 1790, 'AM4', 'Zen 2', 6, 12, 3.6, 4.1, 'DDR4', 2, 65, '', 3, 8, 'AMD', NULL),
(50, 'AMD Ryzen 5 8600G', 5180, 'AM5', 'Zen 4', 6, 12, 4.3, 5.0, 'DDR5', 2, 65, 'Radeon 760M', 6, 16, 'AMD', NULL),
(51, 'AMD Ryzen 7 5700', 3110, 'AM4', 'Zen 3', 8, 16, 3.7, 4.6, 'DDR4', 2, 65, '', 4, 32, 'AMD', NULL),
(52, 'Intel Core i7-14700KF', 7220, 'LGA1700', 'Raptor Lake Refresh', 20, 40, 3.4, 5.6, 'DDR5', 2, 125, '', 28, 33, 'Intel', NULL),
(53, 'Intel Core i9-12900K', 6580, 'LGA1700', 'Alder Lake', 16, 32, 3.2, 5.2, 'DDR5', 2, 125, 'Intel UHD Graphics 770', 14, 30, 'Intel', NULL),
(54, 'Intel Core Ultra 7 265KF', 6370, 'LGA1700', 'Arrow Lake', 20, 40, 3.9, 5.5, 'DDR5', 2, 125, '', 36, 30, 'Intel', NULL),
(55, 'AMD Ryzen 9 9900X3D', 12420, 'AM5', 'Zen 5', 12, 24, 4.4, 5.5, 'DDR5', 2, 120, 'Radeon', 12, 128, 'AMD', NULL),
(56, 'AMD Ryzen 9 7950X3D', 17690, 'AM5', 'Zen 4', 16, 32, 4.2, 5.7, 'DDR5', 2, 120, 'Radeon', 16, 128, 'AMD', NULL),
(57, 'AMD Ryzen 7 8700G', 6420, 'AM5', 'Zen 4', 8, 16, 4.2, 5.1, 'DDR5', 2, 65, 'Radeon 780M', 8, 16, 'AMD', NULL),
(58, 'Intel Core i9-14900KF', 9660, 'LGA1700', 'Raptor Lake Refresh', 24, 48, 3.2, 6.0, 'DDR5', 2, 125, '', 32, 36, 'Intel', NULL),
(59, 'Intel Core i7-13700K', 8970, 'LGA1700', 'Raptor Lake', 16, 32, 3.4, 5.4, 'DDR5', 2, 125, 'Intel UHD Graphics 770', 24, 30, 'Intel', NULL),
(60, 'Intel Core i5-10400F', 3150, 'LGA1200', 'Comet Lake', 6, 12, 2.9, 4.3, 'DDR4', 2, 65, '', 2, 12, 'Intel', NULL),
(61, 'AMD Ryzen 7 8700F', 5520, 'AM5', 'Zen 4', 8, 16, 4.1, 5.0, 'DDR5', 2, 65, '', 8, 16, 'AMD', NULL),
(62, 'AMD Ryzen 5 2600', 3450, 'AM4', 'Zen+', 6, 12, 3.4, 3.9, 'DDR4', 2, 65, '', 3, 16, 'AMD', NULL),
(63, 'Intel Core i9-14900KS', 14720, 'LGA1700', 'Raptor Lake Refresh', 24, 48, 3.2, 6.2, 'DDR5', 2, 150, 'Intel UHD Graphics 770', 32, 36, 'Intel', NULL),
(64, 'AMD Ryzen 5 5600GT', 3110, 'AM4', 'Zen 3', 6, 12, 3.6, 4.6, 'DDR4', 2, 65, 'Radeon Vega 7', 3, 32, 'AMD', NULL),
(65, 'Intel Core i5-13600KF', 4510, 'LGA1700', 'Raptor Lake', 14, 28, 3.5, 5.1, 'DDR5', 2, 125, '', 20, 24, 'Intel', NULL),
(66, 'AMD Ryzen 9 5900XT', 5750, 'AM4', 'Zen 3', 16, 32, 3.3, 4.8, 'DDR4', 2, 105, '', 8, 64, 'AMD', NULL),
(67, 'Intel Core i7-14700F', 6790, 'LGA1700', 'Raptor Lake Refresh', 20, 40, 2.1, 5.4, 'DDR5', 2, 65, '', 28, 33, 'Intel', NULL),
(68, 'AMD Ryzen 9 7900', 7890, 'AM5', 'Zen 4', 12, 24, 3.7, 5.4, 'DDR5', 2, 65, 'Radeon', 12, 64, 'AMD', NULL),
(69, 'Intel Core i7-9700K', 5500, 'LGA1151', 'Coffee Lake Refresh', 8, 16, 3.6, 4.9, 'DDR4', 2, 95, 'Intel UHD Graphics 630', 2, 12, 'Intel', NULL),
(70, 'Intel Core i7-8700K', 4120, 'LGA1151', 'Coffee Lake', 6, 12, 3.7, 4.7, 'DDR4', 2, 95, 'Intel UHD Graphics 630', 2, 12, 'Intel', NULL),
(71, 'AMD Ryzen 3 3200G', 1680, 'AM4', 'Zen+', 4, 8, 3.6, 4.0, 'DDR4', 2, 65, 'Radeon Vega 8', 2, 4, 'AMD', NULL),
(72, 'AMD Ryzen 5 3400G', 1960, 'AM4', 'Zen+', 4, 8, 3.7, 4.2, 'DDR4', 2, 65, 'Radeon Vega 11', 2, 4, 'AMD', NULL),
(73, 'Intel Core i9-13900K', 9980, 'LGA1700', 'Raptor Lake', 24, 48, 3.0, 5.8, 'DDR5', 2, 125, 'Intel UHD Graphics 770', 32, 36, 'Intel', NULL),
(74, 'Intel Core i5-12400', 3310, 'LGA1700', 'Alder Lake', 6, 12, 2.5, 4.4, 'DDR5', 2, 65, 'Intel UHD Graphics 730', 8, 18, 'Intel', NULL),
(75, 'AMD Ryzen 5 3600X', 3960, 'AM4', 'Zen 2', 6, 12, 3.8, 4.4, 'DDR4', 2, 95, '', 3, 32, 'AMD', NULL),
(76, 'Intel Core i9-9900K', 9180, 'LGA1151', 'Coffee Lake Refresh', 8, 16, 3.6, 5.0, 'DDR4', 2, 95, 'Intel UHD Graphics 630', 2, 16, 'Intel', NULL),
(77, 'AMD Ryzen 7 2700X', 3930, 'AM4', 'Zen+', 8, 16, 3.7, 4.3, 'DDR4', 2, 105, '', 4, 16, 'AMD', NULL),
(78, 'Intel Core i3-14100F', 2070, 'LGA1700', 'Raptor Lake Refresh', 4, 8, 3.5, 4.7, 'DDR5', 2, 58, '', 5, 12, 'Intel', NULL),
(79, 'Intel Core i9-12900KF', 6120, 'LGA1700', 'Alder Lake', 16, 32, 3.2, 5.2, 'DDR5', 2, 125, '', 14, 30, 'Intel', NULL),
(80, 'AMD Ryzen 5 5600XT', 3340, 'AM4', 'Zen 3', 6, 12, 3.7, 4.7, 'DDR4', 2, 65, '', 3, 32, 'AMD', NULL),
(81, 'Intel Core i5-13600K', 6900, 'LGA1700', 'Raptor Lake', 14, 28, 3.5, 5.1, 'DDR5', 2, 125, 'Intel UHD Graphics 770', 20, 24, 'Intel', NULL),
(82, 'Intel Core i7-7700K', 3500, 'LGA1151', 'Kaby Lake', 4, 8, 4.2, 4.5, 'DDR4', 2, 91, 'Intel HD Graphics 630', 1, 8, 'Intel', NULL),
(83, 'AMD Ryzen 3 4100', 1730, 'AM4', 'Zen 2', 4, 8, 3.8, 4.0, 'DDR4', 2, 65, '', 2, 4, 'AMD', NULL),
(84, 'Intel Core Ultra 5 245K', 5980, 'LGA1700', 'Arrow Lake', 14, 28, 4.2, 5.2, 'DDR5', 2, 125, 'Intel Xe', 26, 24, 'Intel', NULL),
(85, 'AMD Ryzen 3 3100', 2210, 'AM4', 'Zen 2', 4, 8, 3.6, 3.9, 'DDR4', 2, 65, '', 2, 16, 'AMD', NULL),
(86, 'Intel Core i5-9400F', 2140, 'LGA1151', 'Coffee Lake Refresh', 6, 12, 2.9, 4.1, 'DDR4', 2, 65, '', 2, 9, 'Intel', NULL),
(87, 'Intel Core i7-13700KF', 6880, 'LGA1700', 'Raptor Lake', 16, 32, 3.4, 5.4, 'DDR5', 2, 125, '', 24, 30, 'Intel', NULL),
(88, 'Intel Core i7-10700K', 8030, 'LGA1200', 'Comet Lake', 8, 16, 3.8, 5.1, 'DDR4', 2, 125, 'Intel UHD Graphics 630', 2, 16, 'Intel', NULL),
(89, 'Intel Core i5-14400', 4050, 'LGA1700', 'Raptor Lake Refresh', 10, 20, 2.5, 4.7, 'DDR5', 2, 65, 'Intel UHD Graphics 730', 10, 20, 'Intel', NULL),
(90, 'AMD Ryzen 9 3900X', 6900, 'AM4', 'Zen 2', 12, 24, 3.8, 4.6, 'DDR4', 2, 105, '', 6, 64, 'AMD', NULL),
(91, 'AMD Ryzen 7 7700', 9040, 'AM5', 'Zen 4', 8, 16, 3.6, 5.3, 'DDR5', 2, 65, 'Radeon', 8, 32, 'AMD', NULL),
(92, 'Intel Core i7-6700K', 5270, 'LGA1151', 'Skylake', 4, 8, 4.0, 4.2, 'DDR4', 2, 91, 'Intel HD Graphics 530', 1, 8, 'Intel', NULL),
(93, 'AMD Ryzen 7 7800X3D', 9940, 'AM5', 'Zen 4', 8, 16, 4.2, 5.0, 'DDR5', 2, 120, 'Radeon', 8, 96, 'AMD', NULL),
(94, 'AMD Ryzen 9 7900X3D', 10580, 'AM5', 'Zen 4', 12, 24, 4.4, 5.6, 'DDR5', 2, 120, 'Radeon', 12, 128, 'AMD', NULL),
(95, 'Intel Core i5-11400F', 4420, 'LGA1200', 'Rocket Lake', 6, 12, 2.6, 4.4, 'DDR4', 2, 65, '', 3, 12, 'Intel', NULL),
(96, 'AMD Threadripper 3970X', 39450, 'sTRX4', 'Zen 2', 32, 64, 3.7, 4.5, 'DDR4', 2, 280, '', 16, 128, 'AMD', NULL),
(97, 'AMD Ryzen 7 3800X', 6210, 'AM4', 'Zen 2', 8, 16, 3.9, 4.5, 'DDR4', 2, 105, '', 4, 32, 'AMD', NULL),
(98, 'AMD Ryzen 5 2600X', 2650, 'AM4', 'Zen+', 6, 12, 3.6, 4.2, 'DDR4', 2, 95, '', 3, 16, 'AMD', NULL),
(99, 'Intel Core i7-12700F', 5680, 'LGA1700', 'Alder Lake', 12, 24, 2.1, 4.9, 'DDR5', 2, 65, '', 12, 25, 'Intel', NULL),
(100, 'Intel Core i5-9600K', 3820, 'LGA1151', 'Coffee Lake Refresh', 6, 12, 3.7, 4.6, 'DDR4', 2, 95, 'Intel UHD Graphics 630', 2, 9, 'Intel', NULL),
(101, 'Intel Core Ultra 5 225F', 4390, 'LGA1700', 'Arrow Lake', 10, 20, 3.3, 4.9, 'DDR5', 2, 65, '', 12, 18, 'Intel', NULL),
(102, 'Intel Core i7-11700K', 6760, 'LGA1200', 'Rocket Lake', 8, 16, 3.6, 5.0, 'DDR4', 2, 125, 'Intel UHD Graphics 750', 4, 16, 'Intel', NULL),
(103, 'Intel Core i3-12100', 2620, 'LGA1700', 'Alder Lake', 4, 8, 3.3, 4.3, 'DDR5', 2, 60, 'Intel UHD Graphics 730', 5, 12, 'Intel', NULL),
(104, 'Intel Core i5-10400', 3220, 'LGA1200', 'Comet Lake', 6, 12, 2.9, 4.3, 'DDR4', 2, 65, 'Intel UHD Graphics 630', 2, 12, 'Intel', NULL),
(105, 'Intel Core i7-8700', 4320, 'LGA1151', 'Coffee Lake', 6, 12, 3.2, 4.6, 'DDR4', 2, 65, 'Intel UHD Graphics 630', 2, 12, 'Intel', NULL),
(106, 'Intel Core i3-14100', 2760, 'LGA1700', 'Raptor Lake Refresh', 4, 8, 3.5, 4.7, 'DDR5', 2, 60, 'Intel UHD Graphics 730', 5, 12, 'Intel', NULL),
(107, 'Intel Core i3-13100F', 1960, 'LGA1700', 'Raptor Lake', 4, 8, 3.4, 4.5, 'DDR5', 2, 58, '', 5, 12, 'Intel', NULL),
(108, 'Intel Pentium E5700', 350, 'LGA775', 'Wolfdale', 2, 4, 3.0, 3.0, 'DDR4', 2, 65, '', 2, 0, 'Intel', NULL),
(109, 'Intel Core i9-10900K', 12630, 'LGA1200', 'Comet Lake', 10, 20, 3.7, 5.3, 'DDR4', 2, 125, 'Intel UHD Graphics 630', 3, 20, 'Intel', NULL),
(110, 'Intel Core i5-13400', 3770, 'LGA1700', 'Raptor Lake', 10, 20, 2.5, 4.6, 'DDR5', 2, 65, 'Intel UHD Graphics 730', 10, 20, 'Intel', NULL),
(111, 'Intel Core i7-7700', 2370, 'LGA1151', 'Kaby Lake', 4, 8, 3.6, 4.2, 'DDR4', 2, 65, 'Intel HD Graphics 630', 1, 8, 'Intel', NULL),
(112, 'Intel Core i7-13700F', 5870, 'LGA1700', 'Raptor Lake', 16, 32, 2.1, 5.2, 'DDR5', 2, 65, '', 24, 30, 'Intel', NULL),
(113, 'Intel Core i5-6500', 2000, 'LGA1151', 'Skylake', 4, 8, 3.2, 3.6, 'DDR4', 2, 65, 'Intel HD Graphics 530', 1, 6, 'Intel', NULL),
(114, 'AMD Ryzen 5 5500GT', 3270, 'AM4', 'Zen 3', 6, 12, 3.6, 4.4, 'DDR4', 2, 65, 'Radeon Vega 7', 3, 32, 'AMD', NULL),
(115, 'Intel Core i9-13900KF', 10010, 'LGA1700', 'Raptor Lake', 24, 48, 3.0, 5.8, 'DDR5', 2, 125, '', 32, 36, 'Intel', NULL),
(116, 'AMD Ryzen 5 4600G', 2530, 'AM4', 'Zen 2', 6, 12, 3.7, 4.2, 'DDR4', 2, 65, 'Radeon Vega 7', 3, 8, 'AMD', NULL),
(117, 'AMD Ryzen 5 9600', 10760, 'AM5', 'Zen 5', 6, 12, 3.8, 5.2, 'DDR5', 2, 65, 'Radeon', 6, 32, 'AMD', NULL),
(118, 'Intel Core Ultra 5 235', 6100, 'LGA1700', 'Arrow Lake', 14, 28, 3.4, 5.0, 'DDR5', 2, 65, 'Intel Xe', 26, 24, 'Intel', NULL),
(119, 'Intel Core i7-4790K', 2300, 'LGA1150', 'Haswell Refresh', 4, 8, 4.0, 4.4, 'DDR4', 2, 88, 'Intel HD Graphics 4600', 1, 8, 'Intel', NULL),
(120, 'Intel Core i5-8400', 4120, 'LGA1151', 'Coffee Lake', 6, 12, 2.8, 4.0, 'DDR4', 2, 65, 'Intel UHD Graphics 630', 2, 9, 'Intel', NULL),
(121, 'Intel Core i7-6700', 2830, 'LGA1151', 'Skylake', 4, 8, 3.4, 4.0, 'DDR4', 2, 65, 'Intel HD Graphics 530', 1, 8, 'Intel', NULL),
(122, 'Intel Core Ultra 5 225', 4900, 'LGA1700', 'Arrow Lake', 10, 20, 3.3, 4.9, 'DDR5', 2, 65, 'Intel Xe', 12, 18, 'Intel', NULL),
(123, 'AMD Ryzen 5 3500X', 2300, 'AM4', 'Zen 2', 6, 12, 3.6, 4.1, 'DDR4', 2, 65, '', 3, 16, 'AMD', NULL),
(124, 'Intel Core i7-14700', 7060, 'LGA1700', 'Raptor Lake Refresh', 20, 40, 2.1, 5.4, 'DDR5', 2, 65, 'Intel UHD Graphics 770', 28, 33, 'Intel', NULL),
(125, 'AMD Ryzen 5 1600 (14nm)', 2650, 'AM4', 'Zen', 6, 12, 3.2, 3.6, 'DDR4', 2, 65, '', 3, 16, 'AMD', NULL),
(126, 'Intel Core i9-11900K', 10330, 'LGA1200', 'Rocket Lake', 8, 16, 3.5, 5.3, 'DDR4', 2, 125, 'Intel UHD Graphics 750', 4, 16, 'Intel', NULL),
(127, 'Intel Core i7-10700F', 7480, 'LGA1200', 'Comet Lake', 8, 16, 2.9, 4.8, 'DDR4', 2, 65, '', 2, 16, 'Intel', NULL),
(128, 'Intel Core i3-10100F', 2300, 'LGA1200', 'Comet Lake', 4, 8, 3.6, 4.3, 'DDR4', 2, 65, '', 1, 6, 'Intel', NULL),
(129, 'Intel Core i7-11700F', 5290, 'LGA1200', 'Rocket Lake', 8, 16, 2.5, 4.9, 'DDR4', 2, 65, '', 4, 16, 'Intel', NULL),
(130, 'Intel Core i5-13500', 5410, 'LGA1700', 'Raptor Lake', 14, 28, 2.5, 4.8, 'DDR5', 2, 65, 'Intel UHD Graphics 770', 12, 24, 'Intel', NULL),
(131, 'Intel Pentium E2220', 300, 'LGA775', 'Core', 2, 4, 2.4, 2.4, 'DDR4', 2, 65, '', 1, 0, 'Intel', NULL),
(132, 'Intel Core i7-12700', 6670, 'LGA1700', 'Alder Lake', 12, 24, 2.1, 4.9, 'DDR5', 2, 65, 'Intel UHD Graphics 770', 12, 25, 'Intel', NULL),
(133, 'AMD Ryzen 5 3600', 2530, 'AM4', 'Zen 2', 6, 12, 3.6, 4.2, 'DDR4', 2, 65, '', 3, 32, 'AMD', NULL),
(134, 'Intel Core i5-10600K', 4030, 'LGA1200', 'Comet Lake', 6, 12, 4.1, 4.8, 'DDR4', 2, 125, 'Intel UHD Graphics 630', 2, 12, 'Intel', NULL),
(135, 'Intel Core i5-6600K', 2070, 'LGA1151', 'Skylake', 4, 8, 3.5, 3.9, 'DDR4', 2, 91, 'Intel HD Graphics 530', 1, 6, 'Intel', NULL),
(136, 'Intel Core Ultra 9 285', 13340, 'LGA1700', 'Arrow Lake', 24, 48, 2.5, 5.6, 'DDR5', 2, 65, 'Intel Xe', 40, 36, 'Intel', NULL),
(137, 'Intel Core i7-4770K', 2280, 'LGA1150', 'Haswell', 4, 8, 3.5, 3.9, 'DDR4', 2, 84, 'Intel HD Graphics 4600', 1, 8, 'Intel', NULL),
(138, 'Intel Core i7-3770', 2280, 'LGA1155', 'Ivy Bridge', 4, 8, 3.4, 3.9, 'DDR4', 2, 77, 'Intel HD Graphics 4000', 1, 8, 'Intel', NULL),
(139, 'Intel Core i7-10700', 9200, 'LGA1200', 'Comet Lake', 8, 16, 2.9, 4.8, 'DDR4', 2, 65, 'Intel UHD Graphics 630', 2, 16, 'Intel', NULL),
(140, 'AMD Ryzen 9 3950X', 10490, 'AM5', 'Zen 2', 16, 32, 3.5, 4.7, 'DDR4', 2, 105, '', 8, 64, 'AMD', NULL),
(141, 'Intel Core i3-6100', 690, 'LGA1151', 'Skylake', 2, 4, 3.7, 3.7, 'DDR4', 2, 51, 'Intel HD Graphics 530', 1, 3, 'Intel', NULL),
(142, 'AMD Threadripper 3960X', 29140, 'sTRX4', 'Zen 2', 24, 48, 3.8, 4.5, 'DDR4', 2, 280, '', 12, 128, 'AMD', NULL),
(143, 'AMD Ryzen 7 2700', 3730, 'AM4', 'Zen+', 8, 16, 3.2, 4.1, 'DDR4', 2, 65, '', 4, 16, 'AMD', NULL),
(144, 'Intel Core i5-7500', 1450, 'LGA1151', 'Kaby Lake', 4, 8, 3.4, 3.8, 'DDR4', 2, 65, 'Intel HD Graphics 630', 1, 6, 'Intel', NULL),
(145, 'Intel Core i5-14500', 5470, 'LGA1700', 'Raptor Lake Refresh', 14, 28, 2.6, 5.0, 'DDR5', 2, 65, 'Intel UHD Graphics 770', 12, 24, 'Intel', NULL),
(146, 'Intel Core i7-9700', 6880, 'LGA1151', 'Coffee Lake Refresh', 8, 16, 3.0, 4.7, 'DDR4', 2, 65, 'Intel HD Graphics 630', 2, 12, 'Intel', NULL),
(147, 'Intel Core Ultra 7 265', 8630, 'LGA1700', 'Arrow Lake', 20, 40, 2.4, 5.3, 'DDR5', 2, 65, 'Intel Xe', 36, 30, 'Intel', NULL),
(148, 'Intel Core i5-11400', 3680, 'LGA1200', 'Rocket Lake', 6, 12, 2.6, 4.4, 'DDR4', 2, 65, 'Intel UHD Graphics 730', 3, 12, 'Intel', NULL),
(149, 'Intel Core i9-10850K', 5750, 'LGA1200', 'Comet Lake', 10, 20, 3.6, 5.2, 'DDR4', 2, 125, 'Intel UHD Graphics 630', 3, 20, 'Intel', NULL),
(150, 'Intel Core i5-7400', 1380, 'LGA1151', 'Kaby Lake', 4, 8, 3.0, 3.5, 'DDR4', 2, 65, 'Intel HD Graphics 630', 1, 6, 'Intel', NULL),
(151, 'Intel Core i9-13900KS', 9870, 'LGA1700', 'Raptor Lake', 24, 48, 3.0, 6.0, 'DDR5', 2, 150, 'Intel UHD Graphics 770', 32, 36, 'Intel', NULL),
(152, 'Intel Core Ultra 7 265F', 6210, 'LGA1700', 'Arrow Lake', 20, 40, 2.4, 5.3, 'DDR5', 2, 65, '', 36, 30, 'Intel', NULL),
(153, 'Intel Core i3-8100', 1400, 'LGA1151', 'Coffee Lake', 4, 8, 3.6, 3.6, 'DDR4', 2, 65, 'Intel UHD Graphics 630', 1, 6, 'Intel', NULL),
(154, 'Intel Core i7-4790', 3450, 'LGA1150', 'Haswell Refresh', 4, 8, 3.6, 4.0, 'DDR4', 2, 84, 'Intel HD Graphics 4600', 1, 8, 'Intel', NULL),
(155, 'Intel Xeon E5-2699 V4', 7660, 'LGA2011-3', 'Broadwell', 22, 44, 2.2, 3.6, 'DDR4', 2, 145, '', 6, 55, 'Intel', NULL),
(156, 'Intel Core i5-4690K', 1240, 'LGA1150', 'Haswell Refresh', 4, 8, 3.5, 3.9, 'DDR4', 2, 88, 'Intel HD Graphics 4600', 1, 6, 'Intel', NULL),
(157, 'Intel Xeon E5-2680 V4', 45430, 'LGA2011-3', 'Broadwell', 14, 28, 2.4, 3.3, 'DDR4', 2, 120, '', 4, 35, 'Intel', NULL),
(158, 'Intel Core i9-12900KS', 8740, 'LGA1700', 'Alder Lake', 16, 32, 3.4, 5.5, 'DDR5', 2, 150, 'Intel UHD Graphics 770', 14, 30, 'Intel', NULL),
(159, 'AMD Ryzen 7 1700', 3800, 'AM4', 'Zen', 8, 16, 3.0, 3.7, 'DDR4', 2, 65, '', 4, 16, 'AMD', NULL),
(160, 'Intel Core i5-8600K', 7520, 'LGA1151', 'Coffee Lake', 6, 12, 3.6, 4.3, 'DDR4', 2, 95, 'Intel UHD Graphics 630', 2, 9, 'Intel', NULL),
(161, 'Intel Core i3-10100', 2300, 'LGA1200', 'Comet Lake', 4, 8, 3.6, 4.3, 'DDR4', 2, 65, 'Intel UHD Graphics 630', 1, 6, 'Intel', NULL),
(162, 'Intel Pentium G640', 440, 'LGA1155', 'Sandy Bridge', 2, 4, 2.8, 2.8, 'DDR4', 2, 65, 'Intel HD Graphics', 1, 3, 'Intel', NULL),
(163, 'Intel Core i9-9900KF', 7570, 'LGA1151', 'Coffee Lake Refresh', 8, 16, 3.6, 5.0, 'DDR4', 2, 95, '', 2, 16, 'Intel', NULL),
(164, 'Intel Core i7-9700F', 5960, 'LGA1151', 'Coffee Lake Refresh', 8, 16, 3.0, 4.7, 'DDR4', 2, 65, '', 2, 12, 'Intel', NULL),
(165, 'AMD EPYC 4564P', 16100, 'AM5', 'Zen 4', 16, 32, 4.5, 5.7, 'DDR5', 2, 170, 'Radeon', 16, 64, 'AMD', NULL),
(166, 'AMD Ryzen 3 2200G', 1610, 'AM4', 'Zen', 4, 8, 3.5, 3.7, 'DDR4', 2, 65, 'Radeon Vega 8', 2, 4, 'AMD', NULL),
(167, 'AMD Ryzen 5 2400G', 2650, 'AM4', 'Zen', 4, 8, 3.6, 3.9, 'DDR4', 2, 65, 'Radeon Vega 11', 2, 4, 'AMD', NULL),
(168, 'Intel Core i5-12500', 4140, 'LGA1700', 'Alder Lake', 6, 12, 3.0, 4.6, 'DDR5', 2, 65, 'Intel UHD Graphics 770', 8, 18, 'Intel', NULL),
(169, 'AMD Ryzen 5 5600X3D', 3680, 'AM4', 'Zen 3', 6, 12, 3.3, 4.4, 'DDR4', 2, 105, '', 3, 96, 'AMD', NULL),
(170, 'Intel Core i3-9100F', 1730, 'LGA1151', 'Coffee Lake Refresh', 4, 8, 3.6, 4.2, 'DDR4', 2, 65, '', 1, 6, 'Intel', NULL),
(171, 'Intel Core i5-8500', 3910, 'LGA1151', 'Coffee Lake', 6, 12, 3.0, 4.1, 'DDR4', 2, 65, 'Intel UHD Graphics 630', 2, 9, 'Intel', NULL),
(172, 'Intel Core i5-11600K', 4140, 'LGA1200', 'Rocket Lake', 6, 12, 3.9, 4.9, 'DDR4', 2, 125, 'Intel UHD Graphics 750', 3, 12, 'Intel', NULL),
(173, 'Intel Core i5-7500T', 1270, 'LGA1151', 'Kaby Lake', 4, 8, 2.7, 3.3, 'DDR4', 2, 35, 'Intel HD Graphics 630', 1, 6, 'Intel', NULL),
(174, 'Intel Core i5-4590', 920, 'LGA1150', 'Haswell Refresh', 4, 8, 3.3, 3.7, 'DDR4', 2, 84, 'Intel HD Graphics 4600', 1, 6, 'Intel', NULL),
(175, 'AMD Ryzen 5 1600 (12nm)', 2280, 'AM4', 'Zen+', 6, 12, 3.2, 3.6, 'DDR4', 2, 65, '', 3, 16, 'AMD', NULL),
(176, 'AMD Athlon 3000G (14nm)', 2280, 'AM4', 'Zen', 2, 4, 3.5, 3.5, 'DDR4', 2, 35, 'Radeon Vega 3', 1, 4, 'AMD', NULL),
(177, 'Intel Core i7-10700KF', 5640, 'LGA1200', 'Comet Lake', 8, 16, 3.8, 5.1, 'DDR4', 2, 125, '', 2, 16, 'Intel', NULL),
(178, 'Intel Core i7-3770K', 1150, 'LGA1155', 'Ivy Bridge', 4, 8, 3.5, 3.9, 'DDR4', 2, 77, 'Intel HD Graphics 4000', 1, 8, 'Intel', NULL),
(179, 'AMD Ryzen 9 3900XT', 7360, 'AM4', 'Zen 2', 12, 24, 3.8, 4.7, 'DDR4', 2, 105, '', 6, 64, 'AMD', NULL),
(180, 'Intel Core i3-10105F', 3310, 'LGA1200', 'Comet Lake', 4, 8, 3.7, 4.4, 'DDR4', 2, 65, '', 1, 6, 'Intel', NULL),
(181, 'Intel Core i5-7600', 1290, 'LGA1151', 'Kaby Lake', 4, 8, 3.5, 4.1, 'DDR4', 2, 65, 'Intel HD Graphics 630', 1, 6, 'Intel', NULL),
(182, 'Intel Core i7-9700KF', 9090, 'LGA1151', 'Coffee Lake Refresh', 8, 16, 3.6, 4.9, 'DDR4', 2, 95, '', 2, 12, 'Intel', NULL),
(183, 'Intel Core i7-4770', 4070, 'LGA1150', 'Haswell', 4, 8, 3.4, 3.9, 'DDR4', 2, 84, 'Intel HD Graphics 4600', 1, 8, 'Intel', NULL),
(184, 'Intel Core i5-3470', 2190, 'LGA1155', 'Ivy Bridge', 4, 8, 3.2, 3.6, 'DDR4', 2, 77, 'Intel HD Graphics 2500', 1, 6, 'Intel', NULL),
(185, 'AMD FX-8350', 1150, 'AM3+', 'Piledriver', 8, 16, 4.0, 4.2, 'DDR4', 2, 125, '', 8, 8, 'AMD', NULL),
(186, 'AMD Ryzen 7 3700X', 5060, 'AM4', 'Zen 2', 8, 16, 3.6, 4.4, 'DDR4', 2, 65, '', 4, 32, 'AMD', NULL),
(187, 'AMD Ryzen 5 1600X', 2280, 'AM4', 'Zen', 6, 12, 3.6, 4.0, 'DDR4', 2, 95, '', 3, 16, 'AMD', NULL),
(188, 'AMD 2650', 690, 'AM1', 'Jaguar', 2, 4, 1.5, 1.5, 'DDR4', 2, 25, 'Radeon HD 8240', 1, 0, 'AMD', NULL),
(189, 'Intel Core i7-11700KF', 8170, 'LGA1200', 'Rocket Lake', 8, 16, 3.6, 5.0, 'DDR4', 2, 125, '', 4, 16, 'Intel', NULL),
(190, 'Intel Core i3-4150', 810, 'LGA1150', 'Haswell Refresh', 2, 4, 3.5, 3.5, 'DDR4', 2, 54, 'Intel HD Graphics 4400', 1, 3, 'Intel', NULL),
(191, 'Intel Core i7-11700', 8690, 'LGA1200', 'Rocket Lake', 8, 16, 2.5, 4.9, 'DDR4', 2, 65, 'Intel UHD Graphics 750', 4, 16, 'Intel', NULL),
(192, 'Intel Core Ultra 5 245KF', 6560, 'LGA1700', 'Arrow Lake', 14, 28, 4.2, 5.2, 'DDR5', 2, 125, '', 26, 24, 'Intel', NULL),
(193, 'Intel Core i7-6950X', 11500, 'LGA2011-3', 'Broadwell', 10, 20, 3.0, 3.5, 'DDR4', 2, 140, '', 3, 25, 'Intel', NULL),
(194, 'Intel Core i5-7600K', 4210, 'LGA1151', 'Kaby Lake', 4, 8, 3.8, 4.2, 'DDR4', 2, 91, 'Intel HD Graphics 630', 1, 6, 'Intel', NULL),
(195, 'AMD Ryzen 5 3500', 2070, 'AM4', 'Zen 2', 6, 12, 3.6, 4.1, 'DDR4', 2, 65, '', 3, 16, 'AMD', NULL),
(196, 'Intel Core i3-13100', 2880, 'LGA1700', 'Raptor Lake', 4, 8, 3.4, 4.5, 'DDR5', 2, 60, 'Intel UHD Graphics 730', 5, 12, 'Intel', NULL),
(197, 'Intel Core i9-11900KF', 9180, 'LGA1200', 'Rocket Lake', 8, 16, 3.5, 5.3, 'DDR4', 2, 125, '', 4, 16, 'Intel', NULL),
(198, 'AMD Ryzen 7 1700X', 9870, 'AM4', 'Zen', 8, 16, 3.4, 3.8, 'DDR4', 2, 95, '', 4, 16, 'AMD', NULL),
(199, 'AMD Ryzen 7 3800XT', 5520, 'AM4', 'Zen 2', 8, 16, 3.9, 4.7, 'DDR4', 2, 105, '', 4, 32, 'AMD', NULL),
(200, 'Intel Core i5-9400F', 2760, 'LGA1151', 'Coffee Lake Refresh', 6, 12, 2.9, 4.1, 'DDR4', 2, 65, '', 2, 9, 'Intel', NULL),
(201, 'Intel Core i7-13700', 7360, 'LGA1700', 'Raptor Lake', 16, 32, 2.1, 5.2, 'DDR5', 2, 65, 'Intel UHD Graphics 770', 24, 30, 'Intel', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `forum_comments`
--

CREATE TABLE `forum_comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `postId` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `isVisible` tinyint(1) DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `forum_comments`
--

INSERT INTO `forum_comments` (`id`, `postId`, `userId`, `content`, `isVisible`, `createdAt`, `updatedAt`) VALUES
(5, 4, 22, 'Parádní sestava! Jak jsi na tom s teplotami u 4090? Neměl jsi problém s délkou karty v té skříni?', 1, '2025-12-17 07:00:00', '2025-12-17 07:00:00'),
(6, 4, 24, 'Super výběr, 7800X3D je pro gaming absolutní špička. Plánuješ někdy upgrade na 9800X3D?', 1, '2025-12-17 13:30:00', '2025-12-17 13:30:00'),
(7, 4, 21, 'Díky za komentáře! Teploty jsou v pohodě, Corsair 4000D má skvělý airflow. Karta se tam vejde pohodlně. A na 9800X3D zatím nepřecházím, 7800X3D stačí.', 1, '2025-12-17 18:00:00', '2025-12-17 18:00:00'),
(8, 5, 23, 'Gratuluju k první sestavě! Ten Ryzen 5 5600 za tu cenu je fakt skvělá volba, já mám taky AM4 platformu.', 1, '2026-02-03 09:00:00', '2026-02-03 09:00:00'),
(9, 5, 26, 'Dobrá volba ten RX 6600, na Full HD hry je naprosto dostačující. Jaké hry na tom hraješ?', 1, '2026-02-03 14:45:00', '2026-02-03 14:45:00'),
(10, 5, 25, 'Hraju hlavně Valorant a CS2, tam mám stabilně přes 200 FPS. Občas i něco náročnějšího jako Cyberpunk – tam na střední detaily asi 60 FPS.', 1, '2026-02-04 07:30:00', '2026-02-04 07:30:00'),
(11, 6, 28, 'Dobrý tip s NVENC, hodně streamerů to podceňuje. Ten 9950X je ale na streaming skoro overkill, ne?', 1, '2026-02-12 10:00:00', '2026-02-12 10:00:00'),
(12, 6, 22, 'Souhlasím s Jakubem, ale pro střih videa v DaVinci Resolve se těch 16 jader hodí.', 1, '2026-02-12 15:30:00', '2026-02-12 15:30:00'),
(13, 7, 29, 'Mini ITX buildy mě vždycky fascinovaly. Jakou máš teplotu CPU při plné zátěži?', 1, '2026-03-12 08:00:00', '2026-03-12 08:00:00'),
(14, 7, 27, 'Při plné zátěži se CPU drží kolem 72 °C díky tomu AIO 240mm. V idle je to asi 35 °C. Na tak malou skříň super.', 1, '2026-03-12 13:00:00', '2026-03-12 13:00:00'),
(15, 8, 26, 'Jak je na tom RX 7900 XTX v ray tracingu ve srovnání s RTX 4080?', 1, '2026-03-17 09:00:00', '2026-03-17 09:00:00'),
(16, 8, 28, 'V ray tracingu je RTX 4080 lepší, to přiznávám. Ale v rasterizaci je 7900 XTX srovnatelná nebo i lepší, a to za nižší cenu.', 1, '2026-03-17 17:00:00', '2026-03-17 17:00:00');

-- --------------------------------------------------------

--
-- Struktura tabulky `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `buildId` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `isVisible` tinyint(1) DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `forum_posts`
--

INSERT INTO `forum_posts` (`id`, `userId`, `buildId`, `title`, `content`, `isVisible`, `createdAt`, `updatedAt`) VALUES
(4, 21, 18, 'Moje nová herní sestava – co říkáte?', 'Právě jsem dokončil svou novou herní sestavu s RTX 4090 a Ryzen 7 7800X3D. Výkon je naprosto neuvěřitelný – všechny hry ve 4K na ultra detailech jedou nad 100 FPS. Pokud máte nějaké dotazy ohledně výběru komponent nebo stavby, klidně se ptejte!', 1, '2025-12-16 17:30:00', '2025-12-16 17:30:00'),
(5, 25, 20, 'Sestavila jsem si první PC – tipy pro začátečníky', 'Ahoj, jsem studentka a právě jsem si postavila svůj první počítač. Šla jsem do rozpočtové varianty s Ryzen 5 5600 a RX 6600. Celkově to vyšlo pod 20 000 Kč a jsem spokojená. Pokud jste taky začátečník, nebojte se – konfigurátor hodně pomůže s kompatibilitou.', 1, '2026-02-02 19:00:00', '2026-02-02 19:00:00'),
(6, 24, 21, 'Sestava pro streaming a střih videa', 'Streamuji na Twitchi a potřeboval jsem upgrade. Ryzen 9 9950X s RTX 4080 je pro streaming absolutně dostatečný. Kódování přes NVENC šetří CPU a mohu klidně hrát a streamovat současně bez jakéhokoliv lagování.', 1, '2026-02-11 11:00:00', '2026-02-11 11:00:00'),
(7, 27, 24, 'Mini ITX build – malý, ale šílený výkon', 'Chtěla jsem malý počítač na stůl, který nebude zabírat moc místa, ale zároveň bude výkonný. Mini ITX s NR200P a Ryzen 5 9600X + RX 7700 XT splňuje obojí. Jediný problém byly teploty v tak malé skříni, ale AIO 240mm to vyřešilo.', 1, '2026-03-11 09:30:00', '2026-03-11 09:30:00'),
(8, 28, 25, 'Full AMD build – proč jsem zvolil červený tým', 'Vždy jsem byl fanoušek AMD a tato sestava je toho důkazem. Ryzen 9 7950X + RX 7900 XTX – kompletně AMD. Smart Access Memory funguje skvěle a výkon je na úrovni konkurence. Kdo zvažuje plně AMD sestavu, doporučuji.', 1, '2026-03-16 08:00:00', '2026-03-16 08:00:00');

-- --------------------------------------------------------

--
-- Struktura tabulky `forum_reports`
--

CREATE TABLE `forum_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `postId` int(10) UNSIGNED DEFAULT NULL,
  `commentId` int(10) UNSIGNED DEFAULT NULL,
  `reportedByUserId` int(10) UNSIGNED NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','resolved','dismissed') DEFAULT 'pending',
  `adminNotes` text DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolvedAt` timestamp NULL DEFAULT NULL,
  `isPriority` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `forum_reports`
--

INSERT INTO `forum_reports` (`id`, `postId`, `commentId`, `reportedByUserId`, `reason`, `description`, `status`, `adminNotes`, `createdAt`, `resolvedAt`, `isPriority`) VALUES
(6, 7, NULL, 25, 'misinformation', 'Nesouhlasím s paní Němcovou, že tato sestava má šílený výkon. Považuji to jako dezinformaci méně znalého uživatele.', 'pending', NULL, '2026-04-09 07:07:05', NULL, 0),
(7, 4, NULL, 22, 'inappropriate-content', 'Tento příspěvek na fóru mi přijde nevhodný.', 'pending', NULL, '2026-04-09 07:08:19', NULL, 1),
(8, 4, NULL, 23, 'harassment', 'Tento příspěvek mě obtěžuje.', 'dismissed', 'Nevidím důvod, čím tento příspěvek je obtěžující.', '2026-04-09 08:30:14', '2026-04-09 08:31:14', 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `gpu`
--

CREATE TABLE `gpu` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `chipset` varchar(50) DEFAULT NULL,
  `vram_size` smallint(5) UNSIGNED DEFAULT NULL,
  `vram_type` enum('GDDR5','GDDR5X','GDDR6','GDDR6X','GDDR7','HBM3','HBM3e','HBM2','HBM2e') DEFAULT NULL,
  `vram_clock` decimal(3,1) UNSIGNED DEFAULT NULL,
  `core_clock` smallint(5) UNSIGNED DEFAULT NULL,
  `boost_clock` smallint(5) UNSIGNED DEFAULT NULL,
  `hdmi_count` tinyint(3) UNSIGNED DEFAULT NULL,
  `dp_count` tinyint(3) UNSIGNED DEFAULT NULL,
  `vga_count` tinyint(3) UNSIGNED DEFAULT NULL,
  `dvi_count` tinyint(3) UNSIGNED DEFAULT NULL,
  `max_monitors` tinyint(3) UNSIGNED DEFAULT NULL,
  `length` smallint(5) UNSIGNED DEFAULT NULL,
  `width` smallint(5) UNSIGNED DEFAULT NULL,
  `height` smallint(5) UNSIGNED DEFAULT NULL,
  `tdp` smallint(5) UNSIGNED DEFAULT NULL,
  `connector` enum('none','4-pin','6-pin','8-pin','12-pin','14-pin','16-pin') NOT NULL,
  `connector_count` tinyint(3) UNSIGNED DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `gpu`
--

INSERT INTO `gpu` (`id`, `name`, `price`, `chipset`, `vram_size`, `vram_type`, `vram_clock`, `core_clock`, `boost_clock`, `hdmi_count`, `dp_count`, `vga_count`, `dvi_count`, `max_monitors`, `length`, `width`, `height`, `tdp`, `connector`, `connector_count`, `brand`, `color`) VALUES
(1, 'NVIDIA RTX 4090 Founders Edition', 46000.00, 'AD102', 24, 'GDDR6X', 21.0, 2235, 2520, 1, 3, 0, 0, 4, 304, 137, 61, 450, '16-pin', 1, 'NVIDIA', 'Black'),
(2, 'NVIDIA RTX 4080 Founders Edition', 29900.00, 'AD103', 16, 'GDDR6X', 19.5, 2205, 2505, 1, 3, 0, 0, 4, 304, 137, 61, 320, '16-pin', 1, 'NVIDIA', 'Black'),
(3, 'NVIDIA RTX 4070 Ti', 20700.00, 'AD104', 12, 'GDDR6X', 21.0, 2310, 2610, 1, 3, 0, 0, 4, 267, 120, 40, 285, '16-pin', 1, 'NVIDIA', 'Black'),
(4, 'NVIDIA RTX 4070', 17250.00, 'AD104', 12, 'GDDR6X', 21.0, 1920, 2475, 1, 3, 0, 0, 4, 242, 120, 40, 200, '8-pin', 2, 'NVIDIA', 'Black'),
(5, 'NVIDIA RTX 4060 Ti', 9200.00, 'AD106', 8, 'GDDR6', 18.0, 2310, 2535, 1, 3, 0, 0, 4, 242, 120, 40, 160, '8-pin', 1, 'NVIDIA', 'Black'),
(6, 'NVIDIA RTX 4060', 7590.00, 'AD106', 8, 'GDDR6', 18.0, 1800, 2475, 1, 3, 0, 0, 4, 220, 100, 35, 115, '8-pin', 1, 'NVIDIA', 'Black'),
(7, 'NVIDIA RTX 4050', 5750.00, 'AD107', 6, 'GDDR6', 17.5, 1700, 2350, 1, 3, 0, 0, 4, 192, 100, 35, 100, '8-pin', 1, 'NVIDIA', 'Black'),
(8, 'AMD Radeon RX 7900 XTX', 23000.00, 'RDNA3', 24, 'GDDR6', 20.0, 2500, 2600, 1, 3, 0, 0, 4, 336, 120, 60, 355, '16-pin', 1, 'AMD', 'Black'),
(9, 'AMD Radeon RX 7900 XT', 20700.00, 'RDNA3', 20, 'GDDR6', 20.0, 2100, 2500, 1, 3, 0, 0, 4, 314, 120, 55, 300, '16-pin', 1, 'AMD', 'Black'),
(10, 'AMD Radeon RX 7800 XT', 16100.00, 'RDNA3', 16, 'GDDR6', 18.5, 2100, 2450, 1, 3, 0, 0, 4, 284, 120, 50, 260, '8-pin', 2, 'AMD', 'Black'),
(11, 'AMD Radeon RX 7700 XT', 10350.00, 'RDNA3', 12, 'GDDR6', 17.0, 2100, 2400, 1, 3, 0, 0, 4, 250, 110, 45, 230, '8-pin', 2, 'AMD', 'Black'),
(12, 'AMD Radeon RX 7600', 6900.00, 'RDNA3', 8, 'GDDR6', 16.0, 2000, 2350, 1, 3, 0, 0, 4, 230, 100, 40, 160, '8-pin', 1, 'AMD', 'Black'),
(13, 'NVIDIA RTX 3090 Ti', 43700.00, 'GA102', 24, 'GDDR6X', 19.5, 1560, 1860, 1, 3, 0, 0, 4, 336, 140, 61, 450, '16-pin', 1, 'NVIDIA', 'Black'),
(14, 'NVIDIA RTX 3090', 34500.00, 'GA102', 24, 'GDDR6X', 19.5, 1400, 1700, 1, 3, 0, 0, 4, 313, 140, 55, 350, '16-pin', 1, 'NVIDIA', 'Black'),
(15, 'NVIDIA RTX 3080 Ti', 27600.00, 'GA102', 12, 'GDDR6X', 19.0, 1365, 1665, 1, 3, 0, 0, 4, 285, 120, 55, 350, '12-pin', 1, 'NVIDIA', 'Black'),
(16, 'NVIDIA RTX 3080', 20700.00, 'GA102', 10, 'GDDR6X', 19.0, 1440, 1710, 1, 3, 0, 0, 4, 285, 120, 55, 320, '12-pin', 1, 'NVIDIA', 'Black'),
(17, 'NVIDIA RTX 3070 Ti', 16100.00, 'GA104', 8, 'GDDR6X', 19.0, 1575, 1770, 1, 3, 0, 0, 4, 242, 112, 40, 290, '8-pin', 2, 'NVIDIA', 'Black'),
(18, 'NVIDIA RTX 3070', 13800.00, 'GA104', 8, 'GDDR6', 18.0, 1500, 1725, 1, 3, 0, 0, 4, 242, 112, 40, 220, '8-pin', 2, 'NVIDIA', 'Black'),
(19, 'NVIDIA RTX 3060 Ti', 10350.00, 'GA104', 8, 'GDDR6', 18.0, 1410, 1665, 1, 3, 0, 0, 4, 242, 112, 40, 200, '8-pin', 1, 'NVIDIA', 'Black'),
(20, 'NVIDIA RTX 3060', 7590.00, 'GA106', 12, 'GDDR6', 18.0, 1320, 1777, 1, 3, 0, 0, 4, 242, 112, 40, 170, '8-pin', 1, 'NVIDIA', 'Black'),
(21, 'NVIDIA RTX 3050', 5750.00, 'GA106', 8, 'GDDR6', 14.0, 1550, 1777, 1, 3, 0, 0, 4, 192, 100, 35, 130, '8-pin', 1, 'NVIDIA', 'Black'),
(22, 'AMD Radeon RX 6950 XT', 18400.00, 'RDNA2', 16, 'GDDR6', 18.0, 2110, 2310, 1, 3, 0, 0, 4, 336, 140, 60, 335, '8-pin', 2, 'AMD', 'Black'),
(23, 'AMD Radeon RX 6900 XT', 16100.00, 'RDNA2', 16, 'GDDR6', 16.0, 2015, 2250, 1, 3, 0, 0, 4, 267, 120, 55, 300, '8-pin', 2, 'AMD', 'Black'),
(24, 'AMD Radeon RX 6800 XT', 14950.00, 'RDNA2', 16, 'GDDR6', 16.0, 2015, 2250, 1, 3, 0, 0, 4, 267, 120, 55, 300, '8-pin', 2, 'AMD', 'Black'),
(25, 'AMD Radeon RX 6800', 13340.00, 'RDNA2', 16, 'GDDR6', 16.0, 1815, 2105, 1, 3, 0, 0, 4, 267, 120, 55, 250, '8-pin', 2, 'AMD', 'Black'),
(26, 'AMD Radeon RX 6750 XT', 10350.00, 'RDNA2', 12, 'GDDR6', 16.0, 2150, 2600, 1, 3, 0, 0, 4, 267, 120, 55, 230, '8-pin', 2, 'AMD', 'Black'),
(27, 'AMD Radeon RX 6700 XT', 9200.00, 'RDNA2', 12, 'GDDR6', 16.0, 2320, 2581, 1, 3, 0, 0, 4, 267, 120, 55, 230, '8-pin', 2, 'AMD', 'Black'),
(28, 'AMD Radeon RX 6650 XT', 6900.00, 'RDNA2', 8, 'GDDR6', 17.0, 2410, 2635, 1, 3, 0, 0, 4, 267, 120, 55, 180, '8-pin', 1, 'AMD', 'Black'),
(29, 'AMD Radeon RX 6600 XT', 6210.00, 'RDNA2', 8, 'GDDR6', 16.0, 2359, 2589, 1, 3, 0, 0, 4, 267, 120, 55, 160, '8-pin', 1, 'AMD', 'Black'),
(30, 'AMD Radeon RX 6600', 5060.00, 'RDNA2', 8, 'GDDR6', 14.0, 2044, 2491, 1, 3, 0, 0, 4, 267, 120, 55, 132, '8-pin', 1, 'AMD', 'Black'),
(31, 'NVIDIA RTX 2080 Ti', 20700.00, 'TU102', 11, 'GDDR6', 14.0, 1350, 1545, 1, 3, 0, 0, 4, 285, 112, 40, 250, '8-pin', 2, 'NVIDIA', 'Black'),
(32, 'NVIDIA RTX 2080 Super', 16100.00, 'TU104', 8, 'GDDR6', 14.0, 1650, 1815, 1, 3, 0, 0, 4, 285, 112, 40, 225, '8-pin', 2, 'NVIDIA', 'Black'),
(33, 'NVIDIA RTX 2070 Super', 11500.00, 'TU104', 8, 'GDDR6', 14.0, 1605, 1770, 1, 3, 0, 0, 4, 242, 112, 40, 215, '8-pin', 2, 'NVIDIA', 'Black'),
(34, 'NVIDIA RTX 2070', 9200.00, 'TU106', 8, 'GDDR6', 14.0, 1410, 1620, 1, 3, 0, 0, 4, 242, 112, 40, 175, '8-pin', 2, 'NVIDIA', 'Black'),
(35, 'NVIDIA RTX 2060 Super', 8050.00, 'TU106', 8, 'GDDR6', 14.0, 1470, 1650, 1, 3, 0, 0, 4, 242, 112, 40, 175, '8-pin', 1, 'NVIDIA', 'Black'),
(36, 'NVIDIA RTX 2060', 6900.00, 'TU106', 6, 'GDDR6', 14.0, 1365, 1680, 1, 3, 0, 0, 4, 242, 112, 40, 160, '8-pin', 1, 'NVIDIA', 'Black'),
(37, 'AMD Radeon RX 590', 5060.00, 'Polaris 30', 8, 'GDDR5', 8.0, 1469, 1545, 1, 3, 0, 0, 4, 270, 110, 40, 175, '8-pin', 1, 'AMD', 'Black'),
(38, 'AMD Radeon RX 580', 4600.00, 'Polaris 20', 8, 'GDDR5', 8.0, 1257, 1340, 1, 3, 0, 0, 4, 270, 110, 40, 185, '8-pin', 1, 'AMD', 'Black'),
(39, 'AMD Radeon RX 570', 3910.00, 'Polaris 20', 4, 'GDDR5', 7.0, 1168, 1244, 1, 3, 0, 0, 4, 244, 110, 40, 150, '8-pin', 1, 'AMD', 'Black'),
(40, 'AMD Radeon RX 5600 XT', 6440.00, 'Navi 10', 6, 'GDDR6', 12.0, 1375, 1560, 1, 3, 0, 0, 4, 230, 110, 40, 160, '8-pin', 1, 'AMD', 'Black'),
(41, 'AMD Radeon RX 5700 XT', 9200.00, 'Navi 10', 8, 'GDDR6', 14.0, 1605, 1905, 1, 3, 0, 0, 4, 270, 120, 50, 225, '8-pin', 1, 'AMD', 'Black'),
(42, 'AMD Radeon RX 5700', 8050.00, 'Navi 10', 8, 'GDDR6', 14.0, 1465, 1725, 1, 3, 0, 0, 4, 270, 120, 50, 180, '8-pin', 1, 'AMD', 'Black'),
(43, 'NVIDIA GTX 1660 Ti', 6440.00, 'TU116', 6, 'GDDR6', 12.0, 1500, 1770, 1, 3, 0, 0, 4, 230, 100, 40, 120, '8-pin', 1, 'NVIDIA', 'Black'),
(44, 'NVIDIA GTX 1660 Super', 5520.00, 'TU116', 6, 'GDDR6', 12.0, 1530, 1785, 1, 3, 0, 0, 4, 230, 100, 40, 120, '8-pin', 1, 'NVIDIA', 'Black'),
(45, 'NVIDIA GTX 1660', 5060.00, 'TU116', 6, 'GDDR5', 8.0, 1530, 1785, 1, 3, 0, 0, 4, 230, 100, 40, 120, '8-pin', 1, 'NVIDIA', 'Black'),
(46, 'NVIDIA GTX 1650 Super', 4140.00, 'TU116', 4, 'GDDR6', 12.0, 1530, 1725, 1, 3, 0, 0, 4, 220, 100, 35, 100, '6-pin', 1, 'NVIDIA', 'Black'),
(47, 'NVIDIA GTX 1650', 3680.00, 'TU117', 4, 'GDDR5', 8.0, 1485, 1665, 1, 3, 0, 0, 4, 220, 100, 35, 75, '6-pin', 1, 'NVIDIA', 'Black'),
(48, 'AMD Radeon RX 6500 XT', 3450.00, 'Navi 24', 4, 'GDDR6', 11.0, 2610, 2815, 1, 3, 0, 0, 4, 191, 90, 35, 107, '6-pin', 1, 'AMD', 'Black'),
(49, 'AMD Radeon RX 6400', 2990.00, 'Navi 24', 4, 'GDDR6', 11.0, 2039, 2321, 1, 3, 0, 0, 4, 167, 80, 35, 53, '6-pin', 1, 'AMD', 'Black'),
(50, 'NVIDIA GTX 1050 Ti', 3220.00, 'GP107', 4, 'GDDR5', 7.0, 1290, 1392, 1, 1, 0, 0, 2, 229, 111, 38, 75, '6-pin', 1, 'NVIDIA', 'Black'),
(56, 'ASUS TUF Gaming RTX 5070', 14990.00, 'RTX 5070', 12, 'GDDR7', NULL, 1980, 2512, 1, 3, NULL, NULL, NULL, 305, NULL, NULL, 250, '16-pin', 1, 'ASUS', 'Black'),
(57, 'MSI Gaming X Slim RTX 5070', 15490.00, 'RTX 5070', 12, 'GDDR7', NULL, 1980, 2540, 1, 3, NULL, NULL, NULL, 298, NULL, NULL, 250, '16-pin', 1, 'MSI', 'Black'),
(58, 'Gigabyte Windforce RTX 5070', 14490.00, 'RTX 5070', 12, 'GDDR7', NULL, 1980, 2500, 1, 3, NULL, NULL, NULL, 300, NULL, NULL, 250, '16-pin', 1, 'Gigabyte', 'Black'),
(59, 'EVGA FTW3 RTX 5070', 15290.00, 'RTX 5070', 12, 'GDDR7', NULL, 1980, 2530, 1, 3, NULL, NULL, NULL, 310, NULL, NULL, 250, '16-pin', 1, 'EVGA', 'Black'),
(60, 'Zotac Twin Edge RTX 5070', 13990.00, 'RTX 5070', 12, 'GDDR7', NULL, 1980, 2480, 1, 3, NULL, NULL, NULL, 270, NULL, NULL, 250, '16-pin', 1, 'Zotac', 'Black'),
(61, 'ASUS ROG Strix RTX 5070 Ti', 22990.00, 'RTX 5070 Ti', 16, 'GDDR7', NULL, 2162, 2610, 1, 3, NULL, NULL, NULL, 336, NULL, NULL, 300, '16-pin', 1, 'ASUS', 'Black'),
(62, 'MSI Suprim X RTX 5070 Ti', 23490.00, 'RTX 5070 Ti', 16, 'GDDR7', NULL, 2162, 2625, 1, 3, NULL, NULL, NULL, 340, NULL, NULL, 300, '16-pin', 1, 'MSI', 'Black'),
(63, 'Gigabyte Aorus Master RTX 5070 Ti', 23990.00, 'RTX 5070 Ti', 16, 'GDDR7', NULL, 2162, 2640, 1, 3, NULL, NULL, NULL, 342, NULL, NULL, 300, '16-pin', 1, 'Gigabyte', 'Black'),
(64, 'ASUS ROG Strix RTX 5080', 29990.00, 'RTX 5080', 16, 'GDDR7', NULL, 2295, 2720, 1, 3, NULL, NULL, NULL, 348, NULL, NULL, 360, '16-pin', 1, 'ASUS', 'Black'),
(65, 'MSI Gaming Trio RTX 5080', 28990.00, 'RTX 5080', 16, 'GDDR7', NULL, 2295, 2700, 1, 3, NULL, NULL, NULL, 340, NULL, NULL, 360, '16-pin', 1, 'MSI', 'Black'),
(66, 'Gigabyte Aorus Elite RTX 5080', 28490.00, 'RTX 5080', 16, 'GDDR7', NULL, 2295, 2695, 1, 3, NULL, NULL, NULL, 335, NULL, NULL, 360, '16-pin', 1, 'Gigabyte', 'Black'),
(67, 'ASUS ROG Strix RTX 5090', 54990.00, 'RTX 5090', 32, 'GDDR7', NULL, 2017, 2407, 1, 3, NULL, NULL, NULL, 356, NULL, NULL, 575, '16-pin', 1, 'ASUS', 'Black'),
(68, 'MSI Suprim Liquid RTX 5090', 56990.00, 'RTX 5090', 32, 'GDDR7', NULL, 2017, 2430, 1, 3, NULL, NULL, NULL, 280, NULL, NULL, 575, '16-pin', 1, 'MSI', 'Black'),
(69, 'Sapphire Nitro+ RX 9070 XT', 16490.00, 'RX 9070 XT', 16, 'GDDR6', NULL, 2200, 2560, 1, 3, NULL, NULL, NULL, 315, NULL, NULL, 300, '8-pin', 2, 'Sapphire', 'Black'),
(70, 'XFX Speedster MERC RX 9070 XT', 15990.00, 'RX 9070 XT', 16, 'GDDR6', NULL, 2200, 2540, 1, 3, NULL, NULL, NULL, 320, NULL, NULL, 300, '8-pin', 2, 'XFX', 'Black'),
(71, 'PowerColor Red Devil RX 9070 XT', 16790.00, 'RX 9070 XT', 16, 'GDDR6', NULL, 2200, 2570, 1, 3, NULL, NULL, NULL, 332, NULL, NULL, 300, '8-pin', 2, 'PowerColor', 'Red/Black'),
(72, 'ASRock Taichi RX 9070 XT', 16290.00, 'RX 9070 XT', 16, 'GDDR6', NULL, 2200, 2550, 1, 3, NULL, NULL, NULL, 310, NULL, NULL, 300, '8-pin', 2, 'ASRock', 'Black'),
(73, 'Sapphire Pulse RX 9070', 13490.00, 'RX 9070', 16, 'GDDR6', NULL, 2000, 2394, 1, 3, NULL, NULL, NULL, 280, NULL, NULL, 250, '8-pin', 2, 'Sapphire', 'Black'),
(74, 'XFX Speedster SWFT RX 9070', 12990.00, 'RX 9070', 16, 'GDDR6', NULL, 2000, 2374, 1, 3, NULL, NULL, NULL, 275, NULL, NULL, 250, '8-pin', 2, 'XFX', 'Black'),
(75, 'PowerColor Fighter RX 9070', 12790.00, 'RX 9070', 16, 'GDDR6', NULL, 2000, 2360, 1, 3, NULL, NULL, NULL, 268, NULL, NULL, 250, '8-pin', 2, 'PowerColor', 'Black'),
(76, 'ASUS Dual RTX 4060', 8490.00, 'RTX 4060', 8, 'GDDR6', NULL, 1830, 2460, 1, 3, NULL, NULL, NULL, 240, NULL, NULL, 115, '8-pin', 1, 'ASUS', 'White'),
(77, 'MSI Ventus 2X RTX 4060', 7990.00, 'RTX 4060', 8, 'GDDR6', NULL, 1830, 2430, 1, 3, NULL, NULL, NULL, 232, NULL, NULL, 115, '8-pin', 1, 'MSI', 'Black'),
(78, 'Gigabyte Eagle RTX 4060', 7890.00, 'RTX 4060', 8, 'GDDR6', NULL, 1830, 2415, 1, 3, NULL, NULL, NULL, 235, NULL, NULL, 115, '8-pin', 1, 'Gigabyte', 'Black'),
(79, 'Sapphire Pulse RX 7600', 6990.00, 'RX 7600', 8, 'GDDR6', NULL, 1720, 2655, 1, 3, NULL, NULL, NULL, 240, NULL, NULL, 165, '8-pin', 1, 'Sapphire', 'Black'),
(80, 'XFX Speedster SWFT 210 RX 7600', 6790.00, 'RX 7600', 8, 'GDDR6', NULL, 1720, 2625, 1, 3, NULL, NULL, NULL, 235, NULL, NULL, 165, '8-pin', 1, 'XFX', 'Black');

-- --------------------------------------------------------

--
-- Struktura tabulky `motherboard`
--

CREATE TABLE `motherboard` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `socket` varchar(20) DEFAULT NULL,
  `chipset` varchar(255) DEFAULT NULL,
  `form_factor` enum('Mini-ITX','Micro-ATX','ATX','E-ATX') DEFAULT NULL,
  `max_ram` smallint(5) UNSIGNED DEFAULT NULL,
  `ram_slots` tinyint(3) UNSIGNED DEFAULT NULL,
  `ram_type` enum('DDR2','DDR3','DDR4','DDR5') DEFAULT NULL,
  `ram_speed` int(11) DEFAULT NULL,
  `pcie16_slots` int(11) DEFAULT NULL,
  `pcie1_slots` int(11) DEFAULT NULL,
  `m2_slots` int(11) DEFAULT NULL,
  `sata_slots` int(11) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `motherboard`
--

INSERT INTO `motherboard` (`id`, `name`, `price`, `socket`, `chipset`, `form_factor`, `max_ram`, `ram_slots`, `ram_type`, `ram_speed`, `pcie16_slots`, `pcie1_slots`, `m2_slots`, `sata_slots`, `color`, `brand`) VALUES
(1, 'Asus PRIME B650-PLUS WIFI', 3680.00, 'AM5', 'B650', 'ATX', 192, 4, 'DDR5', 5600, 1, 1, 2, 4, 'Black / Silver', 'Asus'),
(2, 'MSI B650 GAMING PLUS WIFI', 3910.00, 'AM5', 'B650', 'ATX', 192, 4, 'DDR5', 6000, 1, 1, 2, 4, 'Black', 'MSI'),
(3, 'MSI MAG B650 TOMAHAWK WIFI', 4360.00, 'AM5', 'B650', 'ATX', 256, 4, 'DDR5', 6000, 1, 1, 2, 4, 'Black', 'MSI'),
(4, 'Gigabyte X870E AORUS ELITE WIFI7', 7480.00, 'AM5', 'X870E', 'ATX', 256, 4, 'DDR5', 6400, 1, 3, 3, 6, 'Black', 'Gigabyte'),
(5, 'Asus PRIME B550M-A WIFI II', 2300.00, 'AM4', 'B550', 'Micro-ATX', 128, 4, 'DDR4', 3600, 1, 1, 2, 4, 'Blue / Silver', 'Asus'),
(6, 'Gigabyte B650 EAGLE AX', 3630.00, 'AM5', 'B650', 'ATX', 192, 4, 'DDR5', 5600, 1, 1, 2, 4, 'Gray / Black', 'Gigabyte'),
(7, 'Asus TUF GAMING B850-PLUS WIFI', 5010.00, 'AM5', 'B850', 'ATX', 192, 4, 'DDR5', 6000, 1, 1, 2, 4, 'Black / Silver', 'Asus'),
(8, 'ASRock B650M Pro RS WiFi', 3220.00, 'AM5', 'B650', 'Micro-ATX', 256, 4, 'DDR5', 5600, 1, 1, 2, 4, 'Black / Silver', 'ASRock'),
(9, 'MSI MAG X870 TOMAHAWK WIFI', 6210.00, 'AM5', 'X870', 'ATX', 256, 4, 'DDR5', 6400, 1, 3, 3, 6, 'Black', 'MSI'),
(10, 'Gigabyte A520M K V2', 1570.00, 'AM4', 'A520', 'Micro-ATX', 64, 2, 'DDR4', 3200, 1, 1, 2, 4, 'Brown / Black', 'Gigabyte'),
(11, 'Asus B650E MAX GAMING WIFI W', 3450.00, 'AM5', 'B650E', 'ATX', 256, 4, 'DDR5', 6000, 1, 2, 3, 4, 'White', 'Asus'),
(12, 'MSI PRO B650-S WIFI', 3130.00, 'AM5', 'B650', 'ATX', 192, 4, 'DDR5', 5600, 1, 1, 2, 4, 'Black', 'MSI'),
(13, 'Gigabyte B550M K', 2300.00, 'AM4', 'B550', 'Micro-ATX', 128, 4, 'DDR4', 3600, 1, 1, 2, 4, 'Brown / Silver', 'Gigabyte'),
(14, 'ASRock B850I Lightning WiFi', 5830.00, 'AM5', 'B850', 'Mini-ITX', 128, 2, 'DDR5', 5600, 1, 0, 1, 2, 'Black', 'ASRock'),
(15, 'MSI B760 GAMING PLUS WIFI', 3910.00, 'LGA1700', 'B760', 'ATX', 192, 4, 'DDR4', 3600, 1, 1, 2, 4, 'Black / Silver', 'MSI'),
(16, 'MSI PRO B550M-VC WIFI', 2300.00, 'AM4', 'B550', 'Micro-ATX', 128, 4, 'DDR4', 3600, 1, 1, 2, 4, 'Black', 'MSI'),
(17, 'MSI PRO Z790-A MAX WIFI', 5060.00, 'LGA1700', 'Z790', 'ATX', 192, 4, 'DDR5', 5600, 2, 2, 4, 4, 'Silver / Black', 'MSI'),
(18, 'Asus TUF GAMING B650-PLUS WIFI', 3680.00, 'AM5', 'B650', 'ATX', 128, 4, 'DDR5', 5600, 1, 1, 2, 4, 'Black', 'Asus'),
(19, 'Gigabyte X870 EAGLE WIFI7', 5290.00, 'AM5', 'X870', 'ATX', 256, 4, 'DDR5', 6400, 1, 3, 3, 6, 'Black', 'Gigabyte'),
(20, 'Gigabyte X870 AORUS ELITE WIFI7 ICE', 6900.00, 'AM5', 'X870', 'ATX', 256, 4, 'DDR5', 6400, 1, 3, 3, 6, 'White', 'Gigabyte'),
(21, 'MSI B550M PRO-VDH WIFI', 2530.00, 'AM4', 'B550', 'Micro-ATX', 128, 4, 'DDR4', 3600, 1, 1, 2, 4, 'Black', 'MSI'),
(22, 'Asus ROG STRIX X870E-E GAMING WIFI', 10990.00, 'AM5', 'X870E', 'ATX', 192, 4, 'DDR5', 6400, 2, 4, 4, 6, 'Black', 'Asus'),
(23, 'ASRock B850M-X WiFi R2.0', 2990.00, 'AM5', 'B850', 'Micro-ATX', 128, 2, 'DDR5', 5600, 1, 1, 2, 4, 'Black / White', 'ASRock'),
(24, 'Gigabyte B850 EAGLE WIFI6', 4140.00, 'AM5', 'B850', 'ATX', 256, 4, 'DDR5', 6000, 1, 1, 2, 4, 'Gray / Black', 'Gigabyte'),
(25, 'MSI MAG B850 TOMAHAWK MAX WIFI', 5060.00, 'AM5', 'B850', 'ATX', 256, 4, 'DDR5', 6400, 1, 1, 3, 6, 'Black / Green', 'MSI'),
(26, 'Gigabyte B760M GAMING PLUS WIFI DDR4', 3210.00, 'LGA1700', 'B760', 'Micro-ATX', 128, 4, 'DDR4', 3600, 1, 1, 2, 4, 'Black / Silver', 'Gigabyte'),
(27, 'Asus ROG STRIX B650-A GAMING WIFI', 3790.00, 'AM5', 'B650', 'ATX', 192, 4, 'DDR5', 6000, 1, 2, 2, 4, 'Black / Silver', 'Asus'),
(28, 'Gigabyte B650M GAMING PLUS WIFI', 3680.00, 'AM5', 'B650', 'Micro-ATX', 192, 4, 'DDR5', 5600, 1, 1, 2, 4, 'Silver / Black', 'Gigabyte'),
(29, 'MSI MAG B550 TOMAHAWK MAX WIFI', 3910.00, 'AM4', 'B550', 'ATX', 128, 4, 'DDR4', 3600, 1, 1, 2, 4, 'Silver / Black', 'MSI'),
(30, 'Gigabyte B850 AORUS ELITE WIFI7', 5350.00, 'AM5', 'B850', 'ATX', 256, 4, 'DDR5', 6400, 1, 2, 3, 6, 'Black', 'Gigabyte'),
(31, 'Gigabyte B650 GAMING X AX V2', 4240.00, 'AM5', 'B650', 'ATX', 192, 4, 'DDR5', 5600, 1, 1, 2, 4, 'Gray / Black', 'Gigabyte'),
(32, 'Asus ROG STRIX X870-A GAMING WIFI', 7340.00, 'AM5', 'X870', 'ATX', 192, 4, 'DDR5', 6400, 1, 3, 3, 6, 'White', 'Asus'),
(33, 'ASRock A620I LIGHTNING WIFI', 3680.00, 'AM5', 'A620', 'Mini-ITX', 96, 2, 'DDR5', 5600, 1, 0, 1, 2, 'Black', 'ASRock'),
(34, 'Asus TUF GAMING B550-PLUS WIFI II', 3430.00, 'AM4', 'B550', 'ATX', 128, 4, 'DDR4', 3600, 1, 1, 2, 4, 'Black / Gray', 'Asus'),
(35, 'Asus TUF GAMING B650-E WIFI', 4600.00, 'AM5', 'B650E', 'ATX', 192, 4, 'DDR5', 6000, 1, 2, 3, 4, 'Black / Orange', 'Asus'),
(36, 'Gigabyte Z790 EAGLE AX', 3910.00, 'LGA1700', 'Z790', 'ATX', 192, 4, 'DDR5', 5600, 2, 2, 4, 4, 'Gray / Black', 'Gigabyte'),
(37, 'MSI B450M-A PRO MAX II', 2230.00, 'AM4', 'B450', 'Micro-ATX', 64, 2, 'DDR4', 3200, 1, 1, 2, 4, 'Black', 'MSI'),
(38, 'MSI PRO B650M-P', 3450.00, 'AM5', 'B650', 'Micro-ATX', 192, 4, 'DDR5', 5600, 1, 1, 2, 4, 'Black / Silver', 'MSI'),
(39, 'ASRock B450M/ac R2.0', 1720.00, 'AM4', 'B450', 'Micro-ATX', 128, 4, 'DDR4', 3200, 1, 1, 2, 4, 'Black / Silver', 'ASRock'),
(40, 'ASUS ROG Strix X670E-E Gaming WiFi', 11490.00, 'AM5', 'X670E', 'ATX', 128, 4, 'DDR5', 6400, 2, 1, 4, 4, 'Black', 'ASUS'),
(41, 'ASUS TUF Gaming B650-Plus WiFi', 5490.00, 'AM5', 'B650', 'ATX', 128, 4, 'DDR5', 6400, 2, 1, 2, 4, 'Black', 'ASUS'),
(42, 'MSI MAG B650 Tomahawk WiFi', 5990.00, 'AM5', 'B650', 'ATX', 128, 4, 'DDR5', 6400, 2, 1, 2, 4, 'Black', 'MSI'),
(43, 'MSI MEG X670E ACE', 14990.00, 'AM5', 'X670E', 'E-ATX', 128, 4, 'DDR5', 6600, 3, 1, 4, 6, 'Black', 'MSI'),
(44, 'Gigabyte B650 Aorus Elite AX', 5290.00, 'AM5', 'B650', 'ATX', 128, 4, 'DDR5', 6400, 2, 1, 2, 4, 'Black', 'Gigabyte'),
(45, 'Gigabyte X670E Aorus Master', 12490.00, 'AM5', 'X670E', 'ATX', 128, 4, 'DDR5', 6600, 2, 1, 4, 4, 'Black', 'Gigabyte'),
(46, 'ASRock B650M Pro RS WiFi', 3990.00, 'AM5', 'B650', 'Micro-ATX', 128, 2, 'DDR5', 6200, 1, 1, 2, 4, 'Black', 'ASRock'),
(47, 'ASRock X670E Taichi', 13490.00, 'AM5', 'X670E', 'ATX', 128, 4, 'DDR5', 6600, 3, 1, 4, 8, 'Black', 'ASRock'),
(48, 'ASUS ROG Maximus Z790 Hero', 14990.00, 'LGA1700', 'Z790', 'ATX', 128, 4, 'DDR5', 7200, 2, 1, 5, 6, 'Black', 'ASUS'),
(49, 'ASUS Prime B760M-A WiFi', 3490.00, 'LGA1700', 'B760', 'Micro-ATX', 128, 2, 'DDR5', 5600, 1, 1, 2, 4, 'White', 'ASUS'),
(50, 'MSI MAG Z790 Tomahawk WiFi', 7490.00, 'LGA1700', 'Z790', 'ATX', 128, 4, 'DDR5', 7200, 2, 1, 4, 6, 'Black', 'MSI'),
(51, 'MSI PRO B760M-A WiFi', 3290.00, 'LGA1700', 'B760', 'Micro-ATX', 128, 2, 'DDR5', 5600, 1, 1, 2, 4, 'Black', 'MSI'),
(52, 'Gigabyte Z790 Aorus Elite AX', 6990.00, 'LGA1700', 'Z790', 'ATX', 128, 4, 'DDR5', 7000, 2, 1, 4, 4, 'Black', 'Gigabyte'),
(53, 'Gigabyte B760M DS3H AX', 2990.00, 'LGA1700', 'B760', 'Micro-ATX', 64, 2, 'DDR5', 5200, 1, 1, 2, 4, 'Black', 'Gigabyte'),
(54, 'ASUS ROG Strix B550-F Gaming WiFi II', 4290.00, 'AM4', 'B550', 'ATX', 128, 4, 'DDR4', 5100, 2, 1, 2, 6, 'Black', 'ASUS'),
(55, 'MSI MAG B550 Tomahawk', 3990.00, 'AM4', 'B550', 'ATX', 128, 4, 'DDR4', 4866, 2, 1, 2, 6, 'Black', 'MSI'),
(56, 'Gigabyte B550 Aorus Pro V2', 3790.00, 'AM4', 'B550', 'ATX', 128, 4, 'DDR4', 5100, 2, 1, 2, 6, 'Black', 'Gigabyte'),
(57, 'ASRock B550M Steel Legend', 2990.00, 'AM4', 'B550', 'Micro-ATX', 128, 4, 'DDR4', 4733, 1, 1, 2, 6, 'Silver', 'ASRock'),
(58, 'ASUS ROG Strix X570-E Gaming WiFi II', 7490.00, 'AM4', 'X570', 'ATX', 128, 4, 'DDR4', 5100, 2, 1, 2, 8, 'Black', 'ASUS'),
(59, 'MSI MEG X570 Unify', 7990.00, 'AM4', 'X570', 'ATX', 128, 4, 'DDR4', 5000, 2, 1, 3, 8, 'Black', 'MSI');

-- --------------------------------------------------------

--
-- Struktura tabulky `parts`
--

CREATE TABLE `parts` (
  `id` int(10) UNSIGNED NOT NULL,
  `partId_cpu` int(10) UNSIGNED DEFAULT NULL,
  `partId_gpu` int(10) UNSIGNED DEFAULT NULL,
  `partId_ram` bigint(10) UNSIGNED DEFAULT NULL,
  `partId_mboard` bigint(20) UNSIGNED DEFAULT NULL,
  `partId_storage` bigint(10) UNSIGNED DEFAULT NULL,
  `partId_psu` int(10) UNSIGNED DEFAULT NULL,
  `partId_case` int(11) UNSIGNED DEFAULT NULL,
  `partId_cooler` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `brandId` int(10) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `typeId` int(10) UNSIGNED NOT NULL,
  `wattage` int(10) UNSIGNED DEFAULT NULL,
  `tdp` int(10) UNSIGNED DEFAULT NULL,
  `releasedAt` date DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `parts`
--

INSERT INTO `parts` (`id`, `partId_cpu`, `partId_gpu`, `partId_ram`, `partId_mboard`, `partId_storage`, `partId_psu`, `partId_case`, `partId_cooler`, `name`, `brandId`, `description`, `price`, `color`, `typeId`, `wattage`, `tdp`, `releasedAt`, `createdAt`, `updatedAt`) VALUES
(1, 185, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD FX-8350', 1, NULL, 1150.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:44:41'),
(2, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'NVIDIA RTX 4070 Ti', 1, NULL, 20700.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:07:42'),
(3, NULL, NULL, 51, NULL, NULL, NULL, NULL, NULL, 'Test RAM 128GB (2x64GB)', 1, NULL, 23000.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:07:42'),
(4, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'ASRock B650M Pro RS WiFi', 1, NULL, 3220.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:07:42'),
(5, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:07:42'),
(6, NULL, NULL, NULL, NULL, 6, NULL, NULL, NULL, 'Crucial MX500 2TB', 1, NULL, 4000.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:07:42'),
(7, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, 'Crucial MX500 1TB', 1, NULL, 2120.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:07:42'),
(8, NULL, NULL, NULL, NULL, 33, NULL, NULL, NULL, 'Corsair MP600 Core XT 2TB', 1, NULL, 4500.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:07:42'),
(9, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, 'Crucial MX500 500GB', 1, NULL, 1370.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:07:42'),
(10, NULL, NULL, NULL, NULL, 30, NULL, NULL, NULL, 'ADATA XPG SX8200 Pro 1TB', 1, NULL, 2750.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:07:42'),
(11, NULL, NULL, NULL, NULL, NULL, 100, NULL, NULL, 'Test SFX 300W', 1, NULL, 1000.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-04-08 12:07:42'),
(12, NULL, NULL, NULL, NULL, NULL, NULL, 100, NULL, 'MiniTest SFF', 1, NULL, 999.00, NULL, 1, NULL, NULL, NULL, '2026-03-02 17:18:48', '2026-03-02 17:18:48'),
(13, 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 3 3200G', 1, NULL, 1680.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 06:47:45', '2026-04-08 12:07:42'),
(14, NULL, 39, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 570', 1, NULL, 3910.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 06:47:45', '2026-04-08 12:07:42'),
(15, NULL, NULL, 7, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum 32GB (2x16GB) DDR4', 1, NULL, 4830.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 06:47:45', '2026-04-08 12:07:42'),
(16, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 06:47:45', '2026-04-08 12:07:42'),
(17, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 06:47:45', '2026-04-08 12:07:42'),
(18, NULL, NULL, NULL, NULL, 6, NULL, NULL, NULL, 'Crucial MX500 2TB', 1, NULL, 4000.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 06:47:45', '2026-04-08 12:07:42'),
(19, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 06:47:45', '2026-04-08 12:07:42'),
(20, NULL, NULL, NULL, NULL, NULL, NULL, 32, NULL, 'Antec NX410', 1, NULL, 1590.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 06:47:45', '2026-03-03 06:47:45'),
(21, 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 3 3200G', 1, NULL, 1680.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:05:59', '2026-04-08 12:07:42'),
(22, NULL, 39, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 570', 1, NULL, 3910.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:05:59', '2026-04-08 12:07:42'),
(23, NULL, NULL, 7, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum 32GB (2x16GB) DDR4', 1, NULL, 4830.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:05:59', '2026-04-08 12:07:42'),
(24, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:05:59', '2026-04-08 12:07:42'),
(25, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:05:59', '2026-04-08 12:07:42'),
(26, NULL, NULL, NULL, NULL, 6, NULL, NULL, NULL, 'Crucial MX500 2TB', 1, NULL, 4000.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:05:59', '2026-04-08 12:07:42'),
(27, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:05:59', '2026-04-08 12:07:42'),
(28, NULL, NULL, NULL, NULL, NULL, NULL, 27, NULL, 'ASUS ROG Hyperion GR701', 1, NULL, 9990.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:05:59', '2026-03-03 07:05:59'),
(29, 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 3 3200G', 1, NULL, 1680.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:07:11', '2026-04-08 12:07:42'),
(30, NULL, 39, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 570', 1, NULL, 3910.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:07:11', '2026-04-08 12:07:42'),
(31, NULL, NULL, 7, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum 32GB (2x16GB) DDR4', 1, NULL, 4830.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:07:11', '2026-04-08 12:07:42'),
(32, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:07:11', '2026-04-08 12:07:42'),
(33, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:07:11', '2026-04-08 12:07:42'),
(34, NULL, NULL, NULL, NULL, 6, NULL, NULL, NULL, 'Crucial MX500 2TB', 1, NULL, 4000.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:07:11', '2026-04-08 12:07:42'),
(35, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:07:11', '2026-04-08 12:07:42'),
(36, NULL, NULL, NULL, NULL, NULL, NULL, 27, NULL, 'ASUS ROG Hyperion GR701', 1, NULL, 9990.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:07:11', '2026-03-03 07:07:11'),
(37, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Athlon 3000G (14nm)', 1, NULL, 2280.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:10:36', '2026-04-08 12:07:42'),
(38, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:10:36', '2026-04-08 12:07:42'),
(39, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:10:36', '2026-04-08 12:07:42'),
(40, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 'ASRock A620I LIGHTNING WIFI', 1, NULL, 3680.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:10:36', '2026-04-08 12:07:42'),
(41, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:10:36', '2026-04-08 12:07:42'),
(42, NULL, NULL, NULL, NULL, NULL, 11, NULL, NULL, 'be quiet! Pure Power 12 M 750W', 1, NULL, 3250.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:10:36', '2026-04-08 12:07:42'),
(43, NULL, NULL, NULL, NULL, NULL, NULL, 33, NULL, 'Antec P120 Crystal', 1, NULL, 3290.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:10:36', '2026-03-03 07:10:36'),
(44, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 15, 'Arctic Liquid Freezer II 240', 1, NULL, 2750.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:10:36', '2026-04-08 12:07:42'),
(45, 188, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD 2650', 1, NULL, 690.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:14:30', '2026-04-08 12:44:41'),
(46, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5600 XT', 1, NULL, 6440.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:14:30', '2026-04-08 12:07:42'),
(47, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:14:30', '2026-04-08 12:07:42'),
(48, NULL, NULL, NULL, 14, NULL, NULL, NULL, NULL, 'ASRock B850I Lightning WiFi', 1, NULL, 5830.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:14:30', '2026-04-08 12:07:42'),
(49, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, 'Corsair MP600 Pro 1TB', 1, NULL, 4250.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:14:30', '2026-04-08 12:07:42'),
(50, NULL, NULL, NULL, NULL, NULL, 11, NULL, NULL, 'be quiet! Pure Power 12 M 750W', 1, NULL, 3250.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:14:30', '2026-04-08 12:07:42'),
(51, NULL, NULL, NULL, NULL, NULL, NULL, 32, NULL, 'Antec NX410', 1, NULL, 1590.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:14:30', '2026-03-03 07:14:30'),
(52, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:14:30', '2026-04-08 12:07:42'),
(53, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Athlon 3000G (14nm)', 1, NULL, 2280.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:19:20', '2026-04-08 12:07:42'),
(54, NULL, 39, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 570', 1, NULL, 3910.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:19:20', '2026-04-08 12:07:42'),
(55, NULL, NULL, 7, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum 32GB (2x16GB) DDR4', 1, NULL, 4830.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:19:20', '2026-04-08 12:07:42'),
(56, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 'ASRock A620I LIGHTNING WIFI', 1, NULL, 3680.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:19:20', '2026-04-08 12:07:42'),
(57, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:19:20', '2026-04-08 12:07:42'),
(58, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:19:20', '2026-04-08 12:07:42'),
(59, NULL, NULL, NULL, NULL, NULL, NULL, 32, NULL, 'Antec NX410', 1, NULL, 1590.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:19:20', '2026-03-03 07:19:20'),
(60, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'be quiet! Pure Rock 2', 1, NULL, 1120.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:19:20', '2026-04-08 12:07:42'),
(61, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Athlon 3000G (14nm)', 1, NULL, 2280.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:21:55', '2026-04-08 12:07:42'),
(62, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5600 XT', 1, NULL, 6440.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:21:55', '2026-04-08 12:07:42'),
(63, NULL, NULL, 7, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum 32GB (2x16GB) DDR4', 1, NULL, 4830.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:21:55', '2026-04-08 12:07:42'),
(64, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 'ASRock A620I LIGHTNING WIFI', 1, NULL, 3680.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:21:55', '2026-04-08 12:07:42'),
(65, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:21:55', '2026-04-08 12:07:42'),
(66, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:21:55', '2026-04-08 12:07:42'),
(67, NULL, NULL, NULL, NULL, NULL, NULL, 32, NULL, 'Antec NX410', 1, NULL, 1590.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:21:55', '2026-03-03 07:21:55'),
(68, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:21:55', '2026-04-08 12:07:42'),
(69, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Athlon 3000G (14nm)', 1, NULL, 2280.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:29:06', '2026-04-08 12:07:42'),
(70, NULL, NULL, 7, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum 32GB (2x16GB) DDR4', 1, NULL, 4830.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:29:06', '2026-04-08 12:07:42'),
(71, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 'ASRock A620I LIGHTNING WIFI', 1, NULL, 3680.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:29:06', '2026-04-08 12:07:42'),
(72, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:29:06', '2026-04-08 12:07:42'),
(73, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:29:06', '2026-04-08 12:07:42'),
(74, NULL, NULL, NULL, NULL, NULL, NULL, 32, NULL, 'Antec NX410', 1, NULL, 1590.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:29:06', '2026-03-03 07:29:06'),
(75, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-03-03 07:29:06', '2026-04-08 12:07:42'),
(76, 165, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD EPYC 4564P', 1, NULL, 16100.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:10:27', '2026-04-08 12:07:42'),
(77, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:10:27', '2026-04-08 12:07:42'),
(78, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:10:27', '2026-04-08 12:07:42'),
(79, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:10:27', '2026-04-08 12:07:42'),
(80, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:10:27', '2026-04-08 12:07:42'),
(81, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, 'Corsair MP600 Pro 1TB', 1, NULL, 4250.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:10:27', '2026-04-08 12:07:42'),
(82, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, 'be quiet! Dark Power 13 1000W', 1, NULL, 8000.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:10:27', '2026-04-08 12:07:42'),
(83, NULL, NULL, NULL, NULL, NULL, NULL, 9, NULL, 'be quiet! Pure Base 500DX', 1, NULL, 2590.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:10:27', '2026-03-04 08:10:27'),
(84, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:10:27', '2026-04-08 12:07:42'),
(85, 165, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD EPYC 4564P', 1, NULL, 16100.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:12:03', '2026-04-08 12:07:42'),
(86, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:12:03', '2026-04-08 12:07:42'),
(87, NULL, NULL, 30, NULL, NULL, NULL, NULL, NULL, 'Corsair Vengeance LPX 32GB (2x16GB) DDR5', 1, NULL, 5520.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:12:03', '2026-04-08 12:07:42'),
(88, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:12:03', '2026-04-08 12:07:42'),
(89, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:12:03', '2026-04-08 12:07:42'),
(90, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, 'Corsair MP600 Pro 1TB', 1, NULL, 4250.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:12:03', '2026-04-08 12:07:42'),
(91, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, 'be quiet! Dark Power 13 1000W', 1, NULL, 8000.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:12:03', '2026-04-08 12:07:42'),
(92, NULL, NULL, NULL, NULL, NULL, NULL, 9, NULL, 'be quiet! Pure Base 500DX', 1, NULL, 2590.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:12:03', '2026-03-04 08:12:03'),
(93, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:12:03', '2026-04-08 12:07:42'),
(94, 185, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD FX-8350', 1, NULL, 1150.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:53:15', '2026-04-08 12:44:41'),
(95, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5600 XT', 1, NULL, 6440.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:53:15', '2026-04-08 12:07:42'),
(96, NULL, NULL, 14, NULL, NULL, NULL, NULL, NULL, 'Corsair Vengeance LPX 32GB (2x16GB) DDR4', 1, NULL, 3680.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:53:15', '2026-04-08 12:07:42'),
(97, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'ASRock B650M Pro RS WiFi', 1, NULL, 3220.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:53:15', '2026-04-08 12:07:42'),
(98, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:53:15', '2026-04-08 12:07:42'),
(99, NULL, NULL, NULL, NULL, 7, NULL, NULL, NULL, 'Kingston A400 480GB', 1, NULL, 870.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:53:15', '2026-04-08 12:07:42'),
(100, NULL, NULL, NULL, NULL, NULL, 11, NULL, NULL, 'be quiet! Pure Power 12 M 750W', 1, NULL, 3250.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:53:15', '2026-04-08 12:07:42'),
(101, NULL, NULL, NULL, NULL, NULL, NULL, 32, NULL, 'Antec NX410', 1, NULL, 1590.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:53:15', '2026-03-04 08:53:15'),
(102, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 08:53:15', '2026-04-08 12:07:42'),
(103, 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 3 3200G', 1, NULL, 1680.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 10:51:26', '2026-04-08 12:07:42'),
(104, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5600 XT', 1, NULL, 6440.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 10:51:26', '2026-04-08 12:07:42'),
(105, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 10:51:26', '2026-04-08 12:07:42'),
(106, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 'Asus PRIME B550M-A WIFI II', 1, NULL, 2300.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 10:51:26', '2026-04-08 12:07:42'),
(107, NULL, NULL, NULL, NULL, 33, NULL, NULL, NULL, 'Corsair MP600 Core XT 2TB', 1, NULL, 4500.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 10:51:26', '2026-04-08 12:07:42'),
(108, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, 'be quiet! Dark Power 13 1000W', 1, NULL, 8000.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 10:51:26', '2026-04-08 12:07:42'),
(109, NULL, NULL, NULL, NULL, NULL, NULL, 32, NULL, 'Antec NX410', 1, NULL, 1590.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 10:51:26', '2026-03-04 10:51:26'),
(110, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 10:51:26', '2026-04-08 12:07:42'),
(111, 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 3 3200G', 1, NULL, 1680.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(112, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(113, NULL, NULL, 30, NULL, NULL, NULL, NULL, NULL, 'Corsair Vengeance LPX 32GB (2x16GB) DDR5', 1, NULL, 5520.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(114, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'ASRock B650M Pro RS WiFi', 1, NULL, 3220.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(115, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, 'Crucial MX500 1TB', 1, NULL, 2120.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(116, NULL, NULL, NULL, NULL, 33, NULL, NULL, NULL, 'Corsair MP600 Core XT 2TB', 1, NULL, 4500.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(117, NULL, NULL, NULL, NULL, 16, NULL, NULL, NULL, 'Crucial P3 1TB', 1, NULL, 1620.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(118, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, 'Corsair MP600 Pro 1TB', 1, NULL, 4250.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(119, NULL, NULL, NULL, NULL, 6, NULL, NULL, NULL, 'Crucial MX500 2TB', 1, NULL, 4000.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(120, NULL, NULL, NULL, NULL, NULL, 12, NULL, NULL, 'be quiet! Straight Power 11 850W', 1, NULL, 4750.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(121, NULL, NULL, NULL, NULL, NULL, NULL, 33, NULL, 'Antec P120 Crystal', 1, NULL, 3290.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-03-04 11:05:49'),
(122, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-03-04 11:05:49', '2026-04-08 12:07:42'),
(123, 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 3 3200G', 1, NULL, 1680.00, NULL, 1, NULL, NULL, NULL, '2026-03-06 19:25:51', '2026-04-08 12:07:42'),
(124, NULL, 37, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 590', 1, NULL, 5060.00, NULL, 1, NULL, NULL, NULL, '2026-03-06 19:25:51', '2026-04-08 12:07:42'),
(125, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-03-06 19:25:51', '2026-04-08 12:07:42'),
(126, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-03-06 19:25:51', '2026-04-08 12:07:42'),
(127, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, 'Corsair MP600 Pro 1TB', 1, NULL, 4250.00, NULL, 1, NULL, NULL, NULL, '2026-03-06 19:25:51', '2026-04-08 12:07:42'),
(128, NULL, NULL, NULL, NULL, 16, NULL, NULL, NULL, 'Crucial P3 1TB', 1, NULL, 1620.00, NULL, 1, NULL, NULL, NULL, '2026-03-06 19:25:51', '2026-04-08 12:07:42'),
(129, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, 'be quiet! Dark Power 13 1000W', 1, NULL, 8000.00, NULL, 1, NULL, NULL, NULL, '2026-03-06 19:25:51', '2026-04-08 12:07:42'),
(130, NULL, NULL, NULL, NULL, NULL, NULL, 33, NULL, 'Antec P120 Crystal', 1, NULL, 3290.00, NULL, 1, NULL, NULL, NULL, '2026-03-06 19:25:51', '2026-03-06 19:25:51'),
(131, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-03-06 19:25:51', '2026-04-08 12:07:42'),
(132, 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 3 3200G', 1, NULL, 1680.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:20:48', '2026-04-08 12:07:42'),
(133, NULL, 37, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 590', 1, NULL, 5060.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:20:48', '2026-04-08 12:07:42'),
(134, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:20:48', '2026-04-08 12:07:42'),
(135, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:20:48', '2026-04-08 12:07:42'),
(136, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, 'Corsair MP600 Pro 1TB', 1, NULL, 4250.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:20:48', '2026-04-08 12:07:42'),
(137, NULL, NULL, NULL, NULL, 16, NULL, NULL, NULL, 'Crucial P3 1TB', 1, NULL, 1620.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:20:48', '2026-04-08 12:07:42'),
(138, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, 'be quiet! Dark Power 13 1000W', 1, NULL, 8000.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:20:48', '2026-04-08 12:07:42'),
(139, NULL, NULL, NULL, NULL, NULL, NULL, 33, NULL, 'Antec P120 Crystal', 1, NULL, 3290.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:20:48', '2026-04-02 13:20:48'),
(140, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:20:48', '2026-04-08 12:07:42'),
(141, 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 3 3200G', 1, NULL, 1680.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:23:56', '2026-04-08 12:07:42'),
(142, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5600 XT', 1, NULL, 6440.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:23:56', '2026-04-08 12:07:42'),
(143, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:23:56', '2026-04-08 12:07:42'),
(144, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 'Asus PRIME B550M-A WIFI II', 1, NULL, 2300.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:23:56', '2026-04-08 12:07:42'),
(145, NULL, NULL, NULL, NULL, 33, NULL, NULL, NULL, 'Corsair MP600 Core XT 2TB', 1, NULL, 4500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:23:56', '2026-04-08 12:07:42'),
(146, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, 'be quiet! Dark Power 13 1000W', 1, NULL, 8000.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:23:56', '2026-04-08 12:07:42'),
(147, NULL, NULL, NULL, NULL, NULL, NULL, 32, NULL, 'Antec NX410', 1, NULL, 1590.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:23:56', '2026-04-02 13:23:56'),
(148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:23:56', '2026-04-08 12:07:42'),
(149, 71, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 3 3200G', 1, NULL, 1680.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:24:08', '2026-04-08 12:07:42'),
(150, NULL, 40, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5600 XT', 1, NULL, 6440.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:24:08', '2026-04-08 12:07:42'),
(151, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:24:08', '2026-04-08 12:07:42'),
(152, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 'Asus PRIME B550M-A WIFI II', 1, NULL, 2300.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:24:08', '2026-04-08 12:07:42'),
(153, NULL, NULL, NULL, NULL, 33, NULL, NULL, NULL, 'Corsair MP600 Core XT 2TB', 1, NULL, 4500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:24:08', '2026-04-08 12:07:42'),
(154, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, 'be quiet! Dark Power 13 1000W', 1, NULL, 8000.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:24:08', '2026-04-08 12:07:42'),
(155, NULL, NULL, NULL, NULL, NULL, NULL, 32, NULL, 'Antec NX410', 1, NULL, 1590.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:24:08', '2026-04-02 13:24:08'),
(156, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:24:08', '2026-04-08 12:07:42'),
(157, 185, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD FX-8350', 1, NULL, 1150.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:25:01', '2026-04-08 12:44:41'),
(158, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:25:01', '2026-04-08 12:07:42'),
(159, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:25:01', '2026-04-08 12:07:42'),
(160, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:25:01', '2026-04-08 12:07:42'),
(161, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:25:01', '2026-04-08 12:07:42'),
(162, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:25:01', '2026-04-08 12:07:42'),
(163, NULL, NULL, NULL, NULL, NULL, NULL, 33, NULL, 'Antec P120 Crystal', 1, NULL, 3290.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:25:01', '2026-04-02 13:25:01'),
(164, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:25:01', '2026-04-08 12:07:42'),
(165, 185, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD FX-8350', 1, NULL, 1150.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:28:57', '2026-04-08 12:44:41'),
(166, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:28:57', '2026-04-08 12:07:42'),
(167, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:28:57', '2026-04-08 12:07:42'),
(168, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:28:57', '2026-04-08 12:07:42'),
(169, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:28:57', '2026-04-08 12:07:42'),
(170, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:28:57', '2026-04-08 12:07:42'),
(171, NULL, NULL, NULL, NULL, NULL, NULL, 33, NULL, 'Antec P120 Crystal', 1, NULL, 3290.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:28:57', '2026-04-02 13:28:57'),
(172, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 13:28:57', '2026-04-08 12:07:42'),
(173, 185, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD FX-8350', 1, NULL, 1150.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:37:25', '2026-04-08 12:44:41'),
(174, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:37:25', '2026-04-08 12:07:42'),
(175, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:37:25', '2026-04-08 12:07:42'),
(176, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:37:25', '2026-04-08 12:07:42'),
(177, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:37:25', '2026-04-08 12:07:42'),
(178, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:37:25', '2026-04-08 12:07:42'),
(179, NULL, NULL, NULL, NULL, NULL, NULL, 33, NULL, 'Antec P120 Crystal', 1, NULL, 3290.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:37:25', '2026-04-02 15:37:25'),
(180, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:37:25', '2026-04-08 12:07:42'),
(181, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 5 7600', 1, NULL, 4530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:38:15', '2026-04-08 12:07:42'),
(182, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:38:15', '2026-04-08 12:07:42'),
(183, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR5 32GB (2x16GB)', 1, NULL, 6210.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:38:15', '2026-04-08 12:07:42'),
(184, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'ASRock B650M Pro RS WiFi', 1, NULL, 3220.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:38:15', '2026-04-08 12:07:42'),
(185, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:38:15', '2026-04-08 12:07:42'),
(186, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:38:15', '2026-04-08 12:07:42'),
(187, NULL, NULL, NULL, NULL, NULL, NULL, 27, NULL, 'ASUS ROG Hyperion GR701', 1, NULL, 9990.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:38:15', '2026-04-02 15:38:15'),
(188, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:38:15', '2026-04-08 12:07:42'),
(189, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 5 7600', 1, NULL, 4530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:45:25', '2026-04-08 12:07:42'),
(190, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:45:25', '2026-04-08 12:07:42'),
(191, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR5 32GB (2x16GB)', 1, NULL, 6210.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:45:25', '2026-04-08 12:07:42'),
(192, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'ASRock B650M Pro RS WiFi', 1, NULL, 3220.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:45:25', '2026-04-08 12:07:42'),
(193, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:45:25', '2026-04-08 12:07:42'),
(194, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:45:25', '2026-04-08 12:07:42'),
(195, NULL, NULL, NULL, NULL, NULL, NULL, 27, NULL, 'ASUS ROG Hyperion GR701', 1, NULL, 9990.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:45:25', '2026-04-02 15:45:25'),
(196, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:45:25', '2026-04-08 12:07:42'),
(197, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 5 7600', 1, NULL, 4530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:46:35', '2026-04-08 12:07:42'),
(198, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:46:35', '2026-04-08 12:07:42'),
(199, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR5 32GB (2x16GB)', 1, NULL, 6210.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:46:35', '2026-04-08 12:07:42'),
(200, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'ASRock B650M Pro RS WiFi', 1, NULL, 3220.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:46:35', '2026-04-08 12:07:42'),
(201, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:46:35', '2026-04-08 12:07:42'),
(202, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:46:35', '2026-04-08 12:07:42'),
(203, NULL, NULL, NULL, NULL, NULL, NULL, 27, NULL, 'ASUS ROG Hyperion GR701', 1, NULL, 9990.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:46:35', '2026-04-02 15:46:35'),
(204, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:46:35', '2026-04-08 12:07:42'),
(205, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 5 7600', 1, NULL, 4530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:46', '2026-04-08 12:07:42'),
(206, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:46', '2026-04-08 12:07:42'),
(207, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR5 32GB (2x16GB)', 1, NULL, 6210.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:46', '2026-04-08 12:07:42'),
(208, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'ASRock B650M Pro RS WiFi', 1, NULL, 3220.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:46', '2026-04-08 12:07:42'),
(209, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:46', '2026-04-08 12:07:42'),
(210, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:46', '2026-04-08 12:07:42'),
(211, NULL, NULL, NULL, NULL, NULL, NULL, 27, NULL, 'ASUS ROG Hyperion GR701', 1, NULL, 9990.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:46', '2026-04-02 15:48:46'),
(212, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:46', '2026-04-08 12:07:42'),
(213, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 5 7600', 1, NULL, 4530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:56', '2026-04-08 12:07:42'),
(214, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:56', '2026-04-08 12:07:42'),
(215, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR5 32GB (2x16GB)', 1, NULL, 6210.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:56', '2026-04-08 12:07:42'),
(216, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'ASRock B650M Pro RS WiFi', 1, NULL, 3220.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:56', '2026-04-08 12:07:42'),
(217, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:56', '2026-04-08 12:07:42'),
(218, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:56', '2026-04-08 12:07:42'),
(219, NULL, NULL, NULL, NULL, NULL, NULL, 27, NULL, 'ASUS ROG Hyperion GR701', 1, NULL, 9990.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:56', '2026-04-02 15:48:56'),
(220, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:48:56', '2026-04-08 12:07:42'),
(221, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 5 7600', 1, NULL, 4530.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:57:55', '2026-04-08 12:07:42'),
(222, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:57:55', '2026-04-08 12:07:42'),
(223, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR5 32GB (2x16GB)', 1, NULL, 6210.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:57:55', '2026-04-08 12:07:42'),
(224, NULL, NULL, NULL, 8, NULL, NULL, NULL, NULL, 'ASRock B650M Pro RS WiFi', 1, NULL, 3220.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:57:55', '2026-04-08 12:07:42'),
(225, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:57:55', '2026-04-08 12:07:42'),
(226, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:57:55', '2026-04-08 12:07:42'),
(227, NULL, NULL, NULL, NULL, NULL, NULL, 27, NULL, 'ASUS ROG Hyperion GR701', 1, NULL, 9990.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:57:55', '2026-04-02 15:57:55'),
(228, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-02 15:57:55', '2026-04-08 12:07:42'),
(229, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Athlon 3000G (14nm)', 1, NULL, 2280.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:16:26', '2026-04-08 12:07:42'),
(230, NULL, 39, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 570', 1, NULL, 3910.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:16:26', '2026-04-08 12:07:42'),
(231, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:16:26', '2026-04-08 12:07:42'),
(232, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:16:26', '2026-04-08 12:07:42'),
(233, NULL, NULL, NULL, NULL, 30, NULL, NULL, NULL, 'ADATA XPG SX8200 Pro 1TB', 1, NULL, 2750.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:16:26', '2026-04-08 12:07:42'),
(234, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:16:26', '2026-04-08 12:07:42'),
(235, NULL, NULL, NULL, NULL, NULL, NULL, 27, NULL, 'ASUS ROG Hyperion GR701', 1, NULL, 9990.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:16:26', '2026-04-06 21:16:26'),
(236, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:16:26', '2026-04-08 12:07:42'),
(237, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Athlon 3000G (14nm)', 1, NULL, 2280.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:44:41', '2026-04-08 12:07:42'),
(238, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:44:41', '2026-04-08 12:07:42'),
(239, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:44:41', '2026-04-08 12:07:42'),
(240, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:44:41', '2026-04-08 12:07:42'),
(241, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:44:41', '2026-04-08 12:07:42'),
(242, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, 'be quiet! Dark Power 13 1000W', 1, NULL, 8000.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:44:41', '2026-04-08 12:07:42'),
(243, NULL, NULL, NULL, NULL, NULL, NULL, 33, NULL, 'Antec P120 Crystal', 1, NULL, 3290.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:44:41', '2026-04-06 21:44:41'),
(244, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-06 21:44:41', '2026-04-08 12:07:42'),
(245, 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 5 7600', 1, NULL, 4530.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:48:35', '2026-04-08 12:07:42'),
(246, NULL, 41, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700 XT', 1, NULL, 9200.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:48:35', '2026-04-08 12:07:42'),
(247, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR5 32GB (2x16GB)', 1, NULL, 6210.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:48:35', '2026-04-08 12:07:42'),
(248, NULL, NULL, NULL, 14, NULL, NULL, NULL, NULL, 'ASRock B850I Lightning WiFi', 1, NULL, 5830.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:48:35', '2026-04-08 12:07:42'),
(249, NULL, NULL, NULL, NULL, 31, NULL, NULL, NULL, 'ADATA Legend 800 1TB', 1, NULL, 1750.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:48:35', '2026-04-08 12:07:42'),
(250, NULL, NULL, NULL, NULL, 29, NULL, NULL, NULL, 'Samsung 860 EVO 1TB', 1, NULL, 2120.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:48:35', '2026-04-08 12:07:42'),
(251, NULL, NULL, NULL, NULL, 7, NULL, NULL, NULL, 'Kingston A400 480GB', 1, NULL, 870.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:48:35', '2026-04-08 12:07:42'),
(252, NULL, NULL, NULL, NULL, NULL, 9, NULL, NULL, 'Seasonic Prime TX-850', 1, NULL, 7250.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:48:35', '2026-04-08 12:07:42'),
(253, NULL, NULL, NULL, NULL, NULL, NULL, 9, NULL, 'be quiet! Pure Base 500DX', 1, NULL, 2590.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:48:35', '2026-04-07 10:48:35'),
(254, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19, 'Corsair H150i RGB Elite', 1, NULL, 5000.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:48:35', '2026-04-08 12:07:42'),
(255, 85, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 3 3100', 1, NULL, 2210.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:14', '2026-04-08 12:07:42'),
(256, NULL, 47, NULL, NULL, NULL, NULL, NULL, NULL, 'NVIDIA GTX 1650', 1, NULL, 3680.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:14', '2026-04-08 12:07:42'),
(257, NULL, NULL, 45, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 1, NULL, 2530.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:14', '2026-04-08 12:07:42'),
(258, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:14', '2026-04-08 12:07:42'),
(259, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, 'Crucial MX500 1TB', 1, NULL, 2120.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:14', '2026-04-08 12:07:42'),
(260, NULL, NULL, NULL, NULL, 13, NULL, NULL, NULL, 'WD Blue SN570 1TB', 1, NULL, 1870.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:14', '2026-04-08 12:07:42'),
(261, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:14', '2026-04-08 12:07:42'),
(262, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 'Fractal Design Meshify C', 1, NULL, 2490.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:14', '2026-04-07 10:51:14'),
(263, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, 'Arctic Freezer 34 eSports DUO', 1, NULL, 1070.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:14', '2026-04-08 12:07:42'),
(264, 176, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Athlon 3000G (14nm)', 1, NULL, 2280.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:48', '2026-04-08 12:07:42'),
(265, NULL, 42, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 5700', 1, NULL, 8050.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:48', '2026-04-08 12:07:42'),
(266, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR5 32GB (2x16GB)', 1, NULL, 6210.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:48', '2026-04-08 12:07:42'),
(267, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:48', '2026-04-08 12:07:42'),
(268, NULL, NULL, NULL, NULL, 30, NULL, NULL, NULL, 'ADATA XPG SX8200 Pro 1TB', 1, NULL, 2750.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:48', '2026-04-08 12:07:42'),
(269, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:48', '2026-04-08 12:07:42'),
(270, NULL, NULL, NULL, NULL, NULL, NULL, 27, NULL, 'ASUS ROG Hyperion GR701', 1, NULL, 9990.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:48', '2026-04-07 10:51:48'),
(271, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2026-04-07 10:51:48', '2026-04-08 12:07:42'),
(272, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 7 7800X3D', 1, NULL, 7820.00, NULL, 1, NULL, NULL, NULL, '2025-12-15 09:00:00', '2026-04-08 12:07:42'),
(273, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'NVIDIA RTX 4090 Founders Edition', 1, NULL, 46000.00, NULL, 1, NULL, NULL, NULL, '2025-12-15 09:00:00', '2026-04-08 12:07:42'),
(274, NULL, NULL, 24, NULL, NULL, NULL, NULL, NULL, 'G.Skill Trident Z5 RGB 32GB DDR5', 1, NULL, 5750.00, NULL, 1, NULL, NULL, NULL, '2025-12-15 09:00:00', '2026-04-08 12:07:42'),
(275, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 'MSI MAG B650 TOMAHAWK WIFI', 1, NULL, 4360.00, NULL, 1, NULL, NULL, NULL, '2025-12-15 09:00:00', '2026-04-08 12:07:42'),
(276, NULL, NULL, NULL, NULL, 12, NULL, NULL, NULL, 'Samsung 990 Pro 2TB', 1, NULL, 6500.00, NULL, 1, NULL, NULL, NULL, '2025-12-15 09:00:00', '2026-04-08 12:07:42'),
(277, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, 'Corsair RM850x (2021)', 1, NULL, 4250.00, NULL, 1, NULL, NULL, NULL, '2025-12-15 09:00:00', '2026-04-08 12:07:42'),
(278, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, 'Corsair 4000D Airflow', 1, NULL, 2490.00, NULL, 1, NULL, NULL, NULL, '2025-12-15 09:00:00', '2025-12-15 09:00:00'),
(279, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, 'Arctic Liquid Freezer II 360', 1, NULL, 3500.00, NULL, 1, NULL, NULL, NULL, '2025-12-15 09:00:00', '2026-04-08 12:07:42'),
(280, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Intel Core i5-12400F', 1, NULL, 2510.00, NULL, 1, NULL, NULL, NULL, '2026-01-10 13:30:00', '2026-04-08 12:07:42'),
(281, NULL, 47, NULL, NULL, NULL, NULL, NULL, NULL, 'NVIDIA GTX 1650', 1, NULL, 3680.00, NULL, 1, NULL, NULL, NULL, '2026-01-10 13:30:00', '2026-04-08 12:07:42'),
(282, NULL, NULL, 37, NULL, NULL, NULL, NULL, NULL, 'Kingston FURY Beast DDR4 16GB (2x8GB)', 1, NULL, 2070.00, NULL, 1, NULL, NULL, NULL, '2026-01-10 13:30:00', '2026-04-08 12:07:42'),
(283, NULL, NULL, NULL, 15, NULL, NULL, NULL, NULL, 'MSI B760 GAMING PLUS WIFI', 1, NULL, 3910.00, NULL, 1, NULL, NULL, NULL, '2026-01-10 13:30:00', '2026-04-08 12:07:42'),
(284, NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, 'Crucial MX500 1TB', 1, NULL, 2120.00, NULL, 1, NULL, NULL, NULL, '2026-01-10 13:30:00', '2026-04-08 12:07:42'),
(285, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'be quiet! Pure Power 12 M 550W', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-01-10 13:30:00', '2026-04-08 12:07:42'),
(286, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'NZXT H510', 1, NULL, 1990.00, NULL, 1, NULL, NULL, NULL, '2026-01-10 13:30:00', '2026-01-10 13:30:00'),
(287, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'be quiet! Pure Rock 2', 1, NULL, 1120.00, NULL, 1, NULL, NULL, NULL, '2026-01-10 13:30:00', '2026-04-08 12:07:42'),
(288, 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 5 5600', 1, NULL, 2900.00, NULL, 1, NULL, NULL, NULL, '2026-02-01 08:15:00', '2026-04-08 12:07:42'),
(289, NULL, 30, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 6600', 1, NULL, 5060.00, NULL, 1, NULL, NULL, NULL, '2026-02-01 08:15:00', '2026-04-08 12:07:42'),
(290, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'Corsair Vengeance LPX 16GB DDR4', 1, NULL, 2070.00, NULL, 1, NULL, NULL, NULL, '2026-02-01 08:15:00', '2026-04-08 12:07:42'),
(291, NULL, NULL, NULL, 5, NULL, NULL, NULL, NULL, 'Asus PRIME B550M-A WIFI II', 1, NULL, 2300.00, NULL, 1, NULL, NULL, NULL, '2026-02-01 08:15:00', '2026-04-08 12:07:42'),
(292, NULL, NULL, NULL, NULL, 7, NULL, NULL, NULL, 'Kingston A400 480GB', 1, NULL, 870.00, NULL, 1, NULL, NULL, NULL, '2026-02-01 08:15:00', '2026-04-08 12:07:42'),
(293, NULL, NULL, NULL, NULL, 13, NULL, NULL, NULL, 'WD Blue SN570 1TB', 1, NULL, 1870.00, NULL, 1, NULL, NULL, NULL, '2026-02-01 08:15:00', '2026-04-08 12:07:42');
INSERT INTO `parts` (`id`, `partId_cpu`, `partId_gpu`, `partId_ram`, `partId_mboard`, `partId_storage`, `partId_psu`, `partId_case`, `partId_cooler`, `name`, `brandId`, `description`, `price`, `color`, `typeId`, `wattage`, `tdp`, `releasedAt`, `createdAt`, `updatedAt`) VALUES
(294, NULL, NULL, NULL, NULL, NULL, 6, NULL, NULL, 'Seasonic Focus GX-550', 1, NULL, 2620.00, NULL, 1, NULL, NULL, NULL, '2026-02-01 08:15:00', '2026-04-08 12:07:42'),
(295, NULL, NULL, NULL, NULL, NULL, NULL, 20, NULL, 'Thermaltake Versa H18', 1, NULL, 990.00, NULL, 1, NULL, NULL, NULL, '2026-02-01 08:15:00', '2026-02-01 08:15:00'),
(296, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 8, 'Arctic Freezer 34 eSports DUO', 1, NULL, 1070.00, NULL, 1, NULL, NULL, NULL, '2026-02-01 08:15:00', '2026-04-08 12:07:42'),
(297, 25, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 9 9950X', 1, NULL, 12280.00, NULL, 1, NULL, NULL, NULL, '2026-02-10 10:45:00', '2026-04-08 12:07:42'),
(298, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'NVIDIA RTX 4080 Founders Edition', 1, NULL, 29900.00, NULL, 1, NULL, NULL, NULL, '2026-02-10 10:45:00', '2026-04-08 12:07:42'),
(299, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR5 32GB', 1, NULL, 6210.00, NULL, 1, NULL, NULL, NULL, '2026-02-10 10:45:00', '2026-04-08 12:07:42'),
(300, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, 'Gigabyte X870E AORUS ELITE WIFI7', 1, NULL, 7480.00, NULL, 1, NULL, NULL, NULL, '2026-02-10 10:45:00', '2026-04-08 12:07:42'),
(301, NULL, NULL, NULL, NULL, 11, NULL, NULL, NULL, 'Samsung 990 Pro 1TB', 1, NULL, 3750.00, NULL, 1, NULL, NULL, NULL, '2026-02-10 10:45:00', '2026-04-08 12:07:42'),
(302, NULL, NULL, NULL, NULL, 12, NULL, NULL, NULL, 'Samsung 990 Pro 2TB', 1, NULL, 6500.00, NULL, 1, NULL, NULL, NULL, '2026-02-10 10:45:00', '2026-04-08 12:07:42'),
(303, NULL, NULL, NULL, NULL, NULL, 29, NULL, NULL, 'be quiet! Dark Power 13 1000W', 1, NULL, 8000.00, NULL, 1, NULL, NULL, NULL, '2026-02-10 10:45:00', '2026-04-08 12:07:42'),
(304, NULL, NULL, NULL, NULL, NULL, NULL, 13, NULL, 'Lian Li O11 Dynamic EVO', 1, NULL, 4290.00, NULL, 1, NULL, NULL, NULL, '2026-02-10 10:45:00', '2026-02-10 10:45:00'),
(305, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 19, 'Corsair H150i RGB Elite', 1, NULL, 5000.00, NULL, 1, NULL, NULL, NULL, '2026-02-10 10:45:00', '2026-04-08 12:07:42'),
(306, 16, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 5 3600', 1, NULL, 1840.00, NULL, 1, NULL, NULL, NULL, '2026-02-20 15:00:00', '2026-04-08 12:07:42'),
(307, NULL, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'NVIDIA GTX 1660 Super', 1, NULL, 5520.00, NULL, 1, NULL, NULL, NULL, '2026-02-20 15:00:00', '2026-04-08 12:07:42'),
(308, NULL, NULL, 8, NULL, NULL, NULL, NULL, NULL, 'G.Skill Ripjaws V 16GB DDR4', 1, NULL, 1840.00, NULL, 1, NULL, NULL, NULL, '2026-02-20 15:00:00', '2026-04-08 12:07:42'),
(309, NULL, NULL, NULL, 39, NULL, NULL, NULL, NULL, 'ASRock B450M/ac R2.0', 1, NULL, 1720.00, NULL, 1, NULL, NULL, NULL, '2026-02-20 15:00:00', '2026-04-08 12:07:42'),
(310, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 'Samsung 870 EVO 500GB', 1, NULL, 1500.00, NULL, 1, NULL, NULL, NULL, '2026-02-20 15:00:00', '2026-04-08 12:07:42'),
(311, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'Corsair RM550x (2021)', 1, NULL, 2750.00, NULL, 1, NULL, NULL, NULL, '2026-02-20 15:00:00', '2026-04-08 12:07:42'),
(312, NULL, NULL, NULL, NULL, NULL, NULL, 14, NULL, 'Phanteks Eclipse P300A', 1, NULL, 1590.00, NULL, 1, NULL, NULL, NULL, '2026-02-20 15:00:00', '2026-02-20 15:00:00'),
(313, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'be quiet! Pure Rock 2', 1, NULL, 1120.00, NULL, 1, NULL, NULL, NULL, '2026-02-20 15:00:00', '2026-04-08 12:07:42'),
(314, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Intel Core i7-14700K', 1, NULL, 6830.00, NULL, 1, NULL, NULL, NULL, '2026-03-01 07:30:00', '2026-04-08 12:07:42'),
(315, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'NVIDIA RTX 4070 Ti', 1, NULL, 20700.00, NULL, 1, NULL, NULL, NULL, '2026-03-01 07:30:00', '2026-04-08 12:07:42'),
(316, NULL, NULL, 24, NULL, NULL, NULL, NULL, NULL, 'G.Skill Trident Z5 RGB 32GB DDR5', 1, NULL, 5750.00, NULL, 1, NULL, NULL, NULL, '2026-03-01 07:30:00', '2026-04-08 12:07:42'),
(317, NULL, NULL, NULL, 17, NULL, NULL, NULL, NULL, 'MSI PRO Z790-A MAX WIFI', 1, NULL, 5060.00, NULL, 1, NULL, NULL, NULL, '2026-03-01 07:30:00', '2026-04-08 12:07:42'),
(318, NULL, NULL, NULL, NULL, 15, NULL, NULL, NULL, 'WD Black SN850X 2TB', 1, NULL, 6000.00, NULL, 1, NULL, NULL, NULL, '2026-03-01 07:30:00', '2026-04-08 12:07:42'),
(319, NULL, NULL, NULL, NULL, NULL, 15, NULL, NULL, 'EVGA SuperNOVA 850 G6', 1, NULL, 4500.00, NULL, 1, NULL, NULL, NULL, '2026-03-01 07:30:00', '2026-04-08 12:07:42'),
(320, NULL, NULL, NULL, NULL, NULL, NULL, 12, NULL, 'Lian Li Lancool III', 1, NULL, 3990.00, NULL, 1, NULL, NULL, NULL, '2026-03-01 07:30:00', '2026-03-01 07:30:00'),
(321, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, 'Noctua NH-D15', 1, NULL, 2750.00, NULL, 1, NULL, NULL, NULL, '2026-03-01 07:30:00', '2026-04-08 12:07:42'),
(322, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 5 9600X', 1, NULL, 4720.00, NULL, 1, NULL, NULL, NULL, '2026-03-10 11:00:00', '2026-04-08 12:07:42'),
(323, NULL, 11, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 7700 XT', 1, NULL, 10350.00, NULL, 1, NULL, NULL, NULL, '2026-03-10 11:00:00', '2026-04-08 12:07:42'),
(324, NULL, NULL, 49, NULL, NULL, NULL, NULL, NULL, 'Corsair Vengeance LPX DDR5 16GB', 1, NULL, 2760.00, NULL, 1, NULL, NULL, NULL, '2026-03-10 11:00:00', '2026-04-08 12:07:42'),
(325, NULL, NULL, NULL, 33, NULL, NULL, NULL, NULL, 'ASRock A620I LIGHTNING WIFI', 1, NULL, 3680.00, NULL, 1, NULL, NULL, NULL, '2026-03-10 11:00:00', '2026-04-08 12:07:42'),
(326, NULL, NULL, NULL, NULL, 11, NULL, NULL, NULL, 'Samsung 990 Pro 1TB', 1, NULL, 3750.00, NULL, 1, NULL, NULL, NULL, '2026-03-10 11:00:00', '2026-04-08 12:07:42'),
(327, NULL, NULL, NULL, NULL, NULL, 25, NULL, NULL, 'Corsair SF750 (SFX)', 1, NULL, 4500.00, NULL, 1, NULL, NULL, NULL, '2026-03-10 11:00:00', '2026-04-08 12:07:42'),
(328, NULL, NULL, NULL, NULL, NULL, NULL, 17, NULL, 'Cooler Master NR200P', 1, NULL, 2190.00, NULL, 1, NULL, NULL, NULL, '2026-03-10 11:00:00', '2026-03-10 11:00:00'),
(329, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 15, 'Arctic Liquid Freezer II 240', 1, NULL, 2750.00, NULL, 1, NULL, NULL, NULL, '2026-03-10 11:00:00', '2026-04-08 12:07:42'),
(330, 47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Ryzen 9 7950X', 1, NULL, 10350.00, NULL, 1, NULL, NULL, NULL, '2026-03-15 14:20:00', '2026-04-08 12:07:42'),
(331, NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'AMD Radeon RX 7900 XTX', 1, NULL, 23000.00, NULL, 1, NULL, NULL, NULL, '2026-03-15 14:20:00', '2026-04-08 12:07:42'),
(332, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 'Corsair Dominator Platinum DDR5 32GB', 1, NULL, 6210.00, NULL, 1, NULL, NULL, NULL, '2026-03-15 14:20:00', '2026-04-08 12:07:42'),
(333, NULL, NULL, NULL, 22, NULL, NULL, NULL, NULL, 'Asus ROG STRIX X870E-E GAMING WIFI', 1, NULL, 10990.00, NULL, 1, NULL, NULL, NULL, '2026-03-15 14:20:00', '2026-04-08 12:07:42'),
(334, NULL, NULL, NULL, NULL, 32, NULL, NULL, NULL, 'Corsair MP600 Pro 1TB', 1, NULL, 4250.00, NULL, 1, NULL, NULL, NULL, '2026-03-15 14:20:00', '2026-04-08 12:07:42'),
(335, NULL, NULL, NULL, NULL, 22, NULL, NULL, NULL, 'Seagate BarraCuda 4TB', 1, NULL, 2500.00, NULL, 1, NULL, NULL, NULL, '2026-03-15 14:20:00', '2026-04-08 12:07:42'),
(336, NULL, NULL, NULL, NULL, NULL, 28, NULL, NULL, 'Seasonic Vertex GX-1200', 1, NULL, 8250.00, NULL, 1, NULL, NULL, NULL, '2026-03-15 14:20:00', '2026-04-08 12:07:42'),
(337, NULL, NULL, NULL, NULL, NULL, NULL, 8, NULL, 'Fractal Torrent', 1, NULL, 4990.00, NULL, 1, NULL, NULL, NULL, '2026-03-15 14:20:00', '2026-03-15 14:20:00'),
(338, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 20, 'NZXT Kraken 360', 1, NULL, 5750.00, NULL, 1, NULL, NULL, NULL, '2026-03-15 14:20:00', '2026-04-08 12:07:42');

-- --------------------------------------------------------

--
-- Struktura tabulky `psu`
--

CREATE TABLE `psu` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `power` smallint(5) UNSIGNED DEFAULT NULL,
  `type` enum('ATX','SFX','TFX','EPS') DEFAULT NULL,
  `efficiency` enum('80+','80+ Bronze','80+ Silver','80+ Gold','80+ Platinum','80+ Titanium') DEFAULT NULL,
  `modular` enum('No','Semi','Full') DEFAULT NULL,
  `form_factor` enum('ATX','SFX','TFX') DEFAULT NULL,
  `length` smallint(5) UNSIGNED DEFAULT NULL,
  `molex` smallint(5) UNSIGNED DEFAULT NULL,
  `sata` smallint(5) UNSIGNED DEFAULT NULL,
  `6pin` smallint(5) UNSIGNED DEFAULT NULL,
  `6_2pin` smallint(5) UNSIGNED DEFAULT NULL,
  `4_4pin` smallint(5) UNSIGNED DEFAULT NULL,
  `24pin` smallint(5) UNSIGNED DEFAULT NULL,
  `16pin` smallint(5) UNSIGNED DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `psu`
--

INSERT INTO `psu` (`id`, `name`, `price`, `power`, `type`, `efficiency`, `modular`, `form_factor`, `length`, `molex`, `sata`, `6pin`, `6_2pin`, `4_4pin`, `24pin`, `16pin`, `color`, `brand`) VALUES
(1, 'Corsair RM550x (2021)', 2750.00, 550, 'ATX', '80+ Gold', 'Full', NULL, 160, 3, 7, 0, 2, 1, 1, 0, 'Black', 'Corsair'),
(2, 'Corsair RM650x (2021)', 3000.00, 650, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 8, 0, 4, 1, 1, 0, 'Black', 'Corsair'),
(3, 'Corsair RM750x (2021)', 3500.00, 750, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 10, 0, 4, 2, 1, 0, 'Black', 'Corsair'),
(4, 'Corsair RM850x (2021)', 4250.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 180, 4, 12, 0, 6, 2, 1, 0, 'Black', 'Corsair'),
(5, 'Corsair RM1000x (2021)', 5500.00, 1000, 'ATX', '80+ Gold', 'Full', NULL, 180, 4, 14, 0, 8, 2, 1, 0, 'Black', 'Corsair'),
(6, 'Seasonic Focus GX-550', 2620.00, 550, 'ATX', '80+ Gold', 'Full', NULL, 140, 3, 6, 0, 2, 1, 1, 0, 'Black', 'Seasonic'),
(7, 'Seasonic Focus GX-650', 2870.00, 650, 'ATX', '80+ Gold', 'Full', NULL, 140, 3, 8, 0, 4, 1, 1, 0, 'Black', 'Seasonic'),
(8, 'Seasonic Focus GX-750', 3370.00, 750, 'ATX', '80+ Gold', 'Full', NULL, 140, 3, 10, 0, 4, 2, 1, 0, 'Black', 'Seasonic'),
(9, 'Seasonic Prime TX-850', 7250.00, 850, 'ATX', '80+ Titanium', 'Full', NULL, 170, 5, 12, 0, 6, 2, 1, 0, 'Black', 'Seasonic'),
(10, 'be quiet! Pure Power 12 M 550W', 2500.00, 550, 'ATX', '80+ Gold', 'Full', NULL, 160, 2, 6, 0, 2, 1, 1, 1, 'Black', 'be quiet!'),
(11, 'be quiet! Pure Power 12 M 750W', 3250.00, 750, 'ATX', '80+ Gold', 'Full', NULL, 160, 3, 9, 0, 4, 2, 1, 1, 'Black', 'be quiet!'),
(12, 'be quiet! Straight Power 11 850W', 4750.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 170, 4, 11, 0, 4, 2, 1, 0, 'Black', 'be quiet!'),
(13, 'EVGA SuperNOVA 650 G6', 3250.00, 650, 'ATX', '80+ Gold', 'Full', NULL, 140, 4, 9, 0, 4, 1, 1, 0, 'Black', 'EVGA'),
(14, 'EVGA SuperNOVA 750 G6', 3750.00, 750, 'ATX', '80+ Gold', 'Full', NULL, 140, 4, 9, 0, 4, 2, 1, 0, 'Black', 'EVGA'),
(15, 'EVGA SuperNOVA 850 G6', 4500.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 150, 4, 12, 0, 6, 2, 1, 0, 'Black', 'EVGA'),
(16, 'Cooler Master MWE Gold 650 V2', 2620.00, 650, 'ATX', '80+ Gold', 'Semi', NULL, 160, 3, 8, 0, 4, 1, 1, 0, 'Black', 'Cooler Master'),
(17, 'Cooler Master MWE Gold 750 V2', 3000.00, 750, 'ATX', '80+ Gold', 'Semi', NULL, 160, 3, 8, 0, 4, 2, 1, 0, 'Black', 'Cooler Master'),
(18, 'Cooler Master V850 Gold V2', 4250.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 12, 0, 6, 2, 1, 0, 'Black', 'Cooler Master'),
(19, 'MSI MPG A750GF', 3250.00, 750, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 8, 0, 6, 2, 1, 0, 'Black', 'MSI'),
(20, 'MSI MPG A850G PCIe5', 4500.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 12, 0, 6, 2, 1, 1, 'Black', 'MSI'),
(21, 'Thermaltake Toughpower GF1 650W', 3000.00, 650, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 9, 0, 4, 1, 1, 0, 'Black', 'Thermaltake'),
(22, 'Thermaltake Toughpower GF1 850W', 4000.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 12, 0, 6, 2, 1, 0, 'Black', 'Thermaltake'),
(23, 'Fractal Design Ion Gold 650W', 3000.00, 650, 'ATX', '80+ Gold', 'Full', NULL, 150, 3, 8, 0, 4, 1, 1, 0, 'Black', 'Fractal Design'),
(24, 'Fractal Design Ion Gold 850W', 4000.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 150, 4, 10, 0, 6, 2, 1, 0, 'Black', 'Fractal Design'),
(25, 'Corsair SF750 (SFX)', 4500.00, 750, 'SFX', '80+ Platinum', 'Full', NULL, 100, 2, 8, 0, 4, 1, 1, 0, 'Black', 'Corsair'),
(26, 'Cooler Master V850 SFX Gold', 4750.00, 850, 'SFX', '80+ Gold', 'Full', NULL, 100, 2, 8, 0, 4, 1, 1, 0, 'Black', 'Cooler Master'),
(27, 'Corsair RM1000e (2023)', 5000.00, 1000, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 12, 0, 8, 2, 1, 1, 'Black', 'Corsair'),
(28, 'Seasonic Vertex GX-1200', 8250.00, 1200, 'ATX', '80+ Gold', 'Full', NULL, 160, 5, 14, 0, 8, 2, 1, 1, 'Black', 'Seasonic'),
(29, 'be quiet! Dark Power 13 1000W', 8000.00, 1000, 'ATX', '80+ Titanium', 'Full', NULL, 200, 5, 14, 0, 8, 2, 1, 1, 'Black', 'be quiet!'),
(102, 'Corsair HX1500i', 8990.00, 1500, 'ATX', '80+ Platinum', 'Full', NULL, 200, 4, 12, 0, 8, 2, 1, 1, 'Black', 'Corsair'),
(103, 'Corsair RM1200x SHIFT', 5990.00, 1200, 'ATX', '80+ Gold', 'Full', NULL, 180, 4, 12, 0, 6, 2, 1, 1, 'Black', 'Corsair'),
(104, 'Corsair RM1000x', 4490.00, 1000, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 12, 0, 6, 2, 1, 1, 'Black', 'Corsair'),
(105, 'Corsair RM850x 2024', 3290.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 8, 0, 4, 1, 1, 1, 'Black', 'Corsair'),
(106, 'Corsair RM750x 2024', 2790.00, 750, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 8, 0, 4, 1, 1, 0, 'White', 'Corsair'),
(107, 'Seasonic PRIME TX-1600', 12990.00, 1600, 'ATX', '80+ Titanium', 'Full', NULL, 210, 5, 14, 0, 8, 2, 1, 1, 'Black', 'Seasonic'),
(108, 'Seasonic Focus GX-850', 3490.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 140, 3, 8, 0, 4, 1, 1, 0, 'Black', 'Seasonic'),
(109, 'Seasonic Focus GX-1000', 4290.00, 1000, 'ATX', '80+ Gold', 'Full', NULL, 160, 4, 10, 0, 6, 2, 1, 1, 'Black', 'Seasonic'),
(110, 'be quiet! Dark Power Pro 13 1300W', 8490.00, 1300, 'ATX', '80+ Titanium', 'Full', NULL, 200, 4, 14, 0, 8, 2, 1, 1, 'Black', 'be quiet!'),
(111, 'be quiet! Straight Power 12 1000W', 4690.00, 1000, 'ATX', '80+ Platinum', 'Full', NULL, 160, 4, 12, 0, 6, 2, 1, 1, 'Black', 'be quiet!'),
(112, 'be quiet! Pure Power 12 M 850W', 2990.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 160, 3, 8, 0, 4, 1, 1, 0, 'Black', 'be quiet!'),
(113, 'be quiet! Pure Power 12 M 650W', 2290.00, 650, 'ATX', '80+ Gold', 'Full', NULL, 150, 2, 6, 0, 3, 1, 1, 0, 'Black', 'be quiet!'),
(114, 'EVGA SuperNOVA 1000 G7', 4590.00, 1000, 'ATX', '80+ Gold', 'Full', NULL, 150, 4, 12, 0, 6, 2, 1, 0, 'Black', 'EVGA'),
(115, 'EVGA SuperNOVA 850 G7', 3390.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 150, 3, 8, 0, 4, 1, 1, 0, 'Black', 'EVGA'),
(116, 'MSI MEG Ai1300P PCIE5', 7990.00, 1300, 'ATX', '80+ Platinum', 'Full', NULL, 190, 5, 14, 0, 8, 2, 1, 1, 'Black', 'MSI'),
(117, 'MSI MAG A850GL PCIE5', 3190.00, 850, 'ATX', '80+ Gold', 'Full', NULL, 150, 3, 8, 0, 4, 1, 1, 1, 'Black', 'MSI'),
(118, 'Corsair CV650', 1490.00, 650, 'ATX', '80+ Bronze', 'No', NULL, 140, 2, 6, 0, 2, 1, 1, 0, 'Black', 'Corsair'),
(119, 'Corsair CV550', 1190.00, 550, 'ATX', '80+ Bronze', 'No', NULL, 140, 2, 5, 0, 2, 1, 1, 0, 'Black', 'Corsair'),
(120, 'EVGA 600 BR', 1290.00, 600, 'ATX', '80+ Bronze', 'No', NULL, 140, 2, 6, 0, 2, 1, 1, 0, 'Black', 'EVGA'),
(121, 'be quiet! System Power 10 550W', 1390.00, 550, 'ATX', '80+ Bronze', 'No', NULL, 140, 2, 5, 0, 2, 1, 1, 0, 'Black', 'be quiet!'),
(122, 'Corsair SF750 Platinum', 4490.00, 750, 'SFX', '80+ Platinum', 'Full', NULL, 100, 2, 4, 0, 2, 1, 1, 0, 'Black', 'Corsair'),
(123, 'Cooler Master V850 SFX Gold', 3990.00, 850, 'SFX', '80+ Gold', 'Full', NULL, 100, 2, 6, 0, 4, 1, 1, 0, 'Black', 'Cooler Master'),
(124, 'Silverstone SX700-G', 3290.00, 700, 'SFX', '80+ Gold', 'Full', NULL, 100, 2, 4, 0, 2, 1, 1, 0, 'Black', 'Silverstone'),
(125, 'Lian Li SP750', 3190.00, 750, 'SFX', '80+ Gold', 'Full', NULL, 100, 2, 5, 0, 3, 1, 1, 0, 'Black', 'Lian Li');

-- --------------------------------------------------------

--
-- Struktura tabulky `ram`
--

CREATE TABLE `ram` (
  `id` bigint(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `speed` int(10) UNSIGNED DEFAULT NULL,
  `modules` smallint(5) UNSIGNED DEFAULT NULL,
  `stick_gb` smallint(5) UNSIGNED DEFAULT NULL,
  `capacity` smallint(5) UNSIGNED DEFAULT NULL,
  `type` enum('DDR2','DDR3','DDR4','DDR5') DEFAULT NULL,
  `cl` smallint(5) UNSIGNED DEFAULT NULL,
  `trcd` smallint(5) UNSIGNED DEFAULT NULL,
  `trp` smallint(5) UNSIGNED DEFAULT NULL,
  `tras` smallint(5) UNSIGNED DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `ram`
--

INSERT INTO `ram` (`id`, `name`, `price`, `speed`, `modules`, `stick_gb`, `capacity`, `type`, `cl`, `trcd`, `trp`, `tras`, `color`, `brand`) VALUES
(1, 'Corsair Vengeance LPX 16GB (2x8GB) DDR4', 2070.00, 3200, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'Black', 'Corsair'),
(2, 'G.Skill Trident Z RGB 32GB (2x16GB) DDR4', 4370.00, 3600, 2, 16, 32, 'DDR4', 16, 18, 18, 38, 'RGB', 'G.Skill'),
(3, 'Kingston FURY Beast 16GB (2x8GB) DDR4', 1840.00, 3000, 2, 8, 16, 'DDR4', 15, 17, 17, 35, 'Red', 'Kingston'),
(4, 'Crucial Ballistix 32GB (2x16GB) DDR4', 3680.00, 3200, 2, 16, 32, 'DDR4', 16, 18, 18, 36, 'White', 'Crucial'),
(5, 'Patriot Viper Steel 16GB (2x8GB) DDR4', 1950.00, 3600, 2, 8, 16, 'DDR4', 18, 20, 20, 40, 'Silver', 'Patriot'),
(6, 'Teamgroup T-Force Delta RGB 16GB (2x8GB) DDR4', 2180.00, 3200, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'RGB', 'Teamgroup'),
(7, 'Corsair Dominator Platinum 32GB (2x16GB) DDR4', 4830.00, 3600, 2, 16, 32, 'DDR4', 16, 18, 18, 38, 'Black', 'Corsair'),
(8, 'G.Skill Ripjaws V 16GB (2x8GB) DDR4', 1840.00, 3000, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'Red', 'G.Skill'),
(9, 'Kingston HyperX Predator 32GB (2x16GB) DDR4', 4600.00, 3600, 2, 16, 32, 'DDR4', 16, 18, 18, 38, 'Black', 'Kingston'),
(10, 'Crucial Ballistix MAX 16GB (2x8GB) DDR4', 2300.00, 4000, 2, 8, 16, 'DDR4', 18, 20, 20, 42, 'Red', 'Crucial'),
(11, 'Corsair Vengeance RGB Pro 16GB (2x8GB) DDR4', 2180.00, 3600, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'RGB', 'Corsair'),
(12, 'G.Skill Trident Z Neo 32GB (2x16GB) DDR4', 4830.00, 3800, 2, 16, 32, 'DDR4', 18, 20, 20, 40, 'Silver', 'G.Skill'),
(13, 'Teamgroup T-Force Night Hawk RGB 16GB (2x8GB) DDR4', 2070.00, 3600, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'RGB', 'Teamgroup'),
(14, 'Corsair Vengeance LPX 32GB (2x16GB) DDR4', 3680.00, 3200, 2, 16, 32, 'DDR4', 16, 18, 18, 36, 'Black', 'Corsair'),
(15, 'Patriot Viper RGB 32GB (2x16GB) DDR4', 4020.00, 3600, 2, 16, 32, 'DDR4', 16, 18, 18, 36, 'RGB', 'Patriot'),
(16, 'Kingston FURY Beast 32GB (2x16GB) DDR4', 3680.00, 3200, 2, 16, 32, 'DDR4', 16, 18, 18, 36, 'Black', 'Kingston'),
(17, 'Crucial Ballistix 16GB (1x16GB) DDR4', 1840.00, 3000, 1, 16, 16, 'DDR4', 16, 18, 18, 36, 'White', 'Crucial'),
(18, 'G.Skill Ripjaws V 32GB (2x16GB) DDR4', 4370.00, 3600, 2, 16, 32, 'DDR4', 16, 18, 18, 38, 'Red', 'G.Skill'),
(19, 'Corsair Dominator Platinum RGB 16GB (2x8GB) DDR4', 2530.00, 3600, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'RGB', 'Corsair'),
(20, 'Teamgroup T-Force Vulcan Z 16GB (2x8GB) DDR4', 1720.00, 3000, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'Gray', 'Teamgroup'),
(21, 'Corsair Vengeance RGB Pro SL 16GB (2x8GB) DDR4', 2070.00, 3600, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'RGB', 'Corsair'),
(22, 'G.Skill Trident Z RGB 16GB (2x8GB) DDR4', 2300.00, 3600, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'RGB', 'G.Skill'),
(23, 'Corsair Vengeance LPX 16GB (2x8GB) DDR5', 2760.00, 5200, 2, 8, 16, 'DDR5', 36, 40, 40, 76, 'Black', 'Corsair'),
(24, 'G.Skill Trident Z5 RGB 32GB (2x16GB) DDR5', 5750.00, 6000, 2, 16, 32, 'DDR5', 36, 40, 40, 76, 'RGB', 'G.Skill'),
(25, 'Kingston FURY Beast DDR5 16GB (2x8GB)', 2990.00, 5600, 2, 8, 16, 'DDR5', 38, 42, 42, 80, 'Red', 'Kingston'),
(26, 'Crucial Ballistix DDR5 32GB (2x16GB)', 5520.00, 5200, 2, 16, 32, 'DDR5', 36, 40, 40, 76, 'White', 'Crucial'),
(27, 'Corsair Dominator Platinum DDR5 32GB (2x16GB)', 6210.00, 6000, 2, 16, 32, 'DDR5', 36, 40, 40, 76, 'Black', 'Corsair'),
(28, 'G.Skill Ripjaws DDR5 16GB (2x8GB)', 3220.00, 5600, 2, 8, 16, 'DDR5', 36, 40, 40, 76, 'Red', 'G.Skill'),
(29, 'Teamgroup T-Force Delta DDR5 16GB (2x8GB)', 2990.00, 5200, 2, 8, 16, 'DDR5', 36, 40, 40, 76, 'RGB', 'Teamgroup'),
(30, 'Corsair Vengeance LPX 32GB (2x16GB) DDR5', 5520.00, 5200, 2, 16, 32, 'DDR5', 36, 40, 40, 76, 'Black', 'Corsair'),
(31, 'G.Skill Trident Z5 RGB 16GB (2x8GB) DDR5', 3220.00, 6000, 2, 8, 16, 'DDR5', 36, 40, 40, 76, 'RGB', 'G.Skill'),
(32, 'Kingston FURY Beast DDR5 32GB (2x16GB)', 5750.00, 5600, 2, 16, 32, 'DDR5', 36, 40, 40, 76, 'Red', 'Kingston'),
(33, 'Crucial Ballistix DDR5 16GB (2x8GB)', 2760.00, 5200, 2, 8, 16, 'DDR5', 36, 40, 40, 76, 'White', 'Crucial'),
(34, 'Teamgroup T-Force Vulcan DDR5 32GB (2x16GB)', 5290.00, 5200, 2, 16, 32, 'DDR5', 36, 40, 40, 76, 'Gray', 'Teamgroup'),
(35, 'Corsair Dominator Platinum RGB DDR5 16GB (2x8GB)', 3450.00, 6000, 2, 8, 16, 'DDR5', 36, 40, 40, 76, 'RGB', 'Corsair'),
(36, 'G.Skill Ripjaws V DDR4 16GB (2x8GB)', 1840.00, 3000, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'Black', 'G.Skill'),
(37, 'Kingston FURY Beast DDR4 16GB (2x8GB)', 2070.00, 3200, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'Red', 'Kingston'),
(38, 'Crucial Ballistix DDR4 16GB (2x8GB)', 1840.00, 3000, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'White', 'Crucial'),
(39, 'Corsair Vengeance LPX DDR4 16GB (2x8GB)', 1950.00, 3200, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'Black', 'Corsair'),
(40, 'G.Skill Trident Z Neo DDR4 32GB (2x16GB)', 4830.00, 3800, 2, 16, 32, 'DDR4', 18, 20, 20, 40, 'Silver', 'G.Skill'),
(41, 'Teamgroup T-Force Night Hawk DDR4 16GB (2x8GB)', 2070.00, 3600, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'RGB', 'Teamgroup'),
(42, 'Corsair Vengeance RGB Pro DDR4 32GB (2x16GB)', 4140.00, 3600, 2, 16, 32, 'DDR4', 16, 18, 18, 36, 'RGB', 'Corsair'),
(43, 'Patriot Viper Steel DDR4 16GB (2x8GB)', 1950.00, 3600, 2, 8, 16, 'DDR4', 18, 20, 20, 40, 'Silver', 'Patriot'),
(44, 'Kingston HyperX Predator DDR4 32GB (2x16GB)', 4600.00, 3600, 2, 16, 32, 'DDR4', 16, 18, 18, 36, 'Black', 'Kingston'),
(45, 'Corsair Dominator Platinum DDR4 16GB (2x8GB)', 2530.00, 3600, 2, 8, 16, 'DDR4', 16, 18, 18, 36, 'Black', 'Corsair'),
(46, 'G.Skill Ripjaws V DDR5 32GB (2x16GB)', 5750.00, 5600, 2, 16, 32, 'DDR5', 36, 40, 40, 76, 'Red', 'G.Skill'),
(47, 'Kingston FURY Beast DDR5 16GB (2x8GB)', 2990.00, 5600, 2, 8, 16, 'DDR5', 38, 42, 42, 80, 'Red', 'Kingston'),
(48, 'Crucial Ballistix DDR5 32GB (2x16GB)', 5520.00, 5200, 2, 16, 32, 'DDR5', 36, 40, 40, 76, 'White', 'Crucial'),
(49, 'Corsair Vengeance LPX DDR5 16GB (2x8GB)', 2760.00, 5200, 2, 8, 16, 'DDR5', 36, 40, 40, 76, 'Black', 'Corsair'),
(50, 'Teamgroup T-Force Delta RGB DDR5 32GB (2x16GB)', 5750.00, 5200, 2, 16, 32, 'DDR5', 36, 40, 40, 76, 'RGB', 'Teamgroup'),
(51, 'Test RAM 128GB (2x64GB)', 23000.00, 6000, 2, 64, 128, 'DDR5', 30, 24, 24, 56, 'Black', 'Test'),
(52, 'G.Skill Trident Z5 RGB 32GB (2x16GB) DDR5-6400', 3290.00, 6400, 2, 16, 32, 'DDR5', 32, 39, 39, 102, 'Black', 'G.Skill'),
(53, 'G.Skill Trident Z5 RGB 32GB (2x16GB) DDR5-7200', 3990.00, 7200, 2, 16, 32, 'DDR5', 34, 42, 42, 108, 'Silver', 'G.Skill'),
(54, 'G.Skill Trident Z5 RGB 64GB (2x32GB) DDR5-6000', 5990.00, 6000, 2, 32, 64, 'DDR5', 30, 36, 36, 96, 'Black', 'G.Skill'),
(55, 'Corsair Dominator Titanium 32GB (2x16GB) DDR5-7200', 4490.00, 7200, 2, 16, 32, 'DDR5', 34, 42, 42, 108, 'Titanium', 'Corsair'),
(56, 'Corsair Dominator Titanium 64GB (2x32GB) DDR5-6400', 7490.00, 6400, 2, 32, 64, 'DDR5', 32, 39, 39, 102, 'Titanium', 'Corsair'),
(57, 'Corsair Vengeance RGB 32GB (2x16GB) DDR5-6000', 2690.00, 6000, 2, 16, 32, 'DDR5', 30, 36, 36, 96, 'Black', 'Corsair'),
(58, 'Corsair Vengeance RGB 32GB (2x16GB) DDR5-5600', 2290.00, 5600, 2, 16, 32, 'DDR5', 28, 34, 34, 90, 'White', 'Corsair'),
(59, 'Kingston Fury Beast 32GB (2x16GB) DDR5-6000', 2390.00, 6000, 2, 16, 32, 'DDR5', 30, 36, 36, 96, 'Black', 'Kingston'),
(60, 'Kingston Fury Beast 64GB (2x32GB) DDR5-5600', 4490.00, 5600, 2, 32, 64, 'DDR5', 28, 34, 34, 90, 'Black', 'Kingston'),
(61, 'Kingston Fury Renegade 32GB (2x16GB) DDR5-7600', 4990.00, 7600, 2, 16, 32, 'DDR5', 36, 44, 44, 112, 'Black', 'Kingston'),
(62, 'TeamGroup T-Force Delta RGB 32GB (2x16GB) DDR5-6400', 2590.00, 6400, 2, 16, 32, 'DDR5', 32, 39, 39, 102, 'White', 'TeamGroup'),
(63, 'TeamGroup T-Force Delta RGB 32GB (2x16GB) DDR5-7200', 3290.00, 7200, 2, 16, 32, 'DDR5', 34, 42, 42, 108, 'Black', 'TeamGroup'),
(64, 'Crucial Pro 32GB (2x16GB) DDR5-5600', 1990.00, 5600, 2, 16, 32, 'DDR5', 28, 34, 34, 90, 'Black', 'Crucial'),
(65, 'Crucial Pro 64GB (2x32GB) DDR5-5600', 3790.00, 5600, 2, 32, 64, 'DDR5', 28, 34, 34, 90, 'Black', 'Crucial'),
(66, 'Corsair Vengeance LPX 16GB (2x8GB) DDR4-3200', 890.00, 3200, 2, 8, 16, 'DDR4', 16, 20, 20, 38, 'Black', 'Corsair'),
(67, 'Corsair Vengeance LPX 32GB (2x16GB) DDR4-3600', 1690.00, 3600, 2, 16, 32, 'DDR4', 18, 22, 22, 42, 'Black', 'Corsair'),
(68, 'G.Skill Ripjaws V 16GB (2x8GB) DDR4-3600', 990.00, 3600, 2, 8, 16, 'DDR4', 18, 22, 22, 42, 'Black', 'G.Skill'),
(69, 'G.Skill Ripjaws V 32GB (2x16GB) DDR4-3200', 1490.00, 3200, 2, 16, 32, 'DDR4', 16, 20, 20, 38, 'Black', 'G.Skill'),
(70, 'Kingston Fury Beast 16GB (2x8GB) DDR4-3200', 850.00, 3200, 2, 8, 16, 'DDR4', 16, 20, 20, 38, 'Black', 'Kingston'),
(71, 'Kingston Fury Beast 32GB (2x16GB) DDR4-3600', 1590.00, 3600, 2, 16, 32, 'DDR4', 18, 22, 22, 42, 'Black', 'Kingston');

-- --------------------------------------------------------

--
-- Struktura tabulky `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'User'),
(2, 'Admin\r\n'),
(3, 'Moderator');

-- --------------------------------------------------------

--
-- Struktura tabulky `storage`
--

CREATE TABLE `storage` (
  `id` bigint(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `form_factor` enum('2.5"','3.5"','M.2') DEFAULT NULL,
  `type` enum('HDD','SSD','M.2') DEFAULT NULL,
  `capacity` smallint(5) UNSIGNED DEFAULT NULL,
  `interface` enum('SATA2','SATA3','PCIe 3.0','PCIe 4.0','PCIe 5.0') DEFAULT NULL,
  `read_speed` smallint(5) UNSIGNED DEFAULT NULL,
  `write_speed` smallint(5) UNSIGNED DEFAULT NULL,
  `tdp` tinyint(3) UNSIGNED DEFAULT NULL,
  `lifespan` smallint(5) UNSIGNED DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `storage`
--

INSERT INTO `storage` (`id`, `name`, `price`, `form_factor`, `type`, `capacity`, `interface`, `read_speed`, `write_speed`, `tdp`, `lifespan`, `brand`, `color`) VALUES
(1, 'Samsung 870 EVO 500GB', 1500.00, '2.5\"', 'SSD', 500, 'SATA3', 560, 530, 3, 300, 'Samsung', 'Black'),
(2, 'Samsung 870 EVO 1TB', 2250.00, '2.5\"', 'SSD', 1000, 'SATA3', 560, 530, 3, 600, 'Samsung', 'Black'),
(3, 'Samsung 870 EVO 2TB', 4250.00, '2.5\"', 'SSD', 2000, 'SATA3', 560, 530, 3, 1200, 'Samsung', 'Black'),
(4, 'Crucial MX500 500GB', 1370.00, '2.5\"', 'SSD', 500, 'SATA3', 560, 510, 3, 180, 'Crucial', 'Black'),
(5, 'Crucial MX500 1TB', 2120.00, '2.5\"', 'SSD', 1000, 'SATA3', 560, 510, 3, 360, 'Crucial', 'Black'),
(6, 'Crucial MX500 2TB', 4000.00, '2.5\"', 'SSD', 2000, 'SATA3', 560, 510, 3, 700, 'Crucial', 'Black'),
(7, 'Kingston A400 480GB', 870.00, '2.5\"', 'SSD', 480, 'SATA3', 500, 450, 2, 160, 'Kingston', 'Black'),
(8, 'Kingston A400 960GB', 1500.00, '2.5\"', 'SSD', 960, 'SATA3', 500, 450, 2, 300, 'Kingston', 'Black'),
(9, 'Samsung 980 1TB', 2250.00, 'M.2', 'M.2', 1000, 'PCIe 3.0', 3500, 3000, 5, 600, 'Samsung', 'Black'),
(10, 'Samsung 980 2TB', 4250.00, 'M.2', 'M.2', 2000, 'PCIe 3.0', 3500, 3000, 5, 1200, 'Samsung', 'Black'),
(11, 'Samsung 990 Pro 1TB', 3750.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 7450, 6900, 6, 600, 'Samsung', 'Black'),
(12, 'Samsung 990 Pro 2TB', 6500.00, 'M.2', 'M.2', 2000, 'PCIe 4.0', 7450, 6900, 6, 1200, 'Samsung', 'Black'),
(13, 'WD Blue SN570 1TB', 1870.00, 'M.2', 'M.2', 1000, 'PCIe 3.0', 3500, 3000, 4, 600, 'WD', 'Black'),
(14, 'WD Black SN850X 1TB', 3500.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 7300, 6300, 6, 600, 'WD', 'Black'),
(15, 'WD Black SN850X 2TB', 6000.00, 'M.2', 'M.2', 2000, 'PCIe 4.0', 7300, 6600, 6, 1200, 'WD', 'Black'),
(16, 'Crucial P3 1TB', 1620.00, 'M.2', 'M.2', 1000, 'PCIe 3.0', 3500, 3000, 4, 220, 'Crucial', 'Black'),
(17, 'Crucial P5 Plus 1TB', 3000.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 6600, 5000, 5, 600, 'Crucial', 'Black'),
(18, 'Kingston NV2 1TB', 1500.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 3500, 2800, 4, 320, 'Kingston', 'Black'),
(19, 'Kingston KC3000 1TB', 3250.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 7000, 6000, 6, 800, 'Kingston', 'Black'),
(20, 'Seagate BarraCuda 1TB', 1120.00, '3.5\"', 'HDD', 1000, 'SATA3', 210, 210, 6, 300, 'Seagate', 'Black'),
(21, 'Seagate BarraCuda 2TB', 1500.00, '3.5\"', 'HDD', 2000, 'SATA3', 220, 220, 6, 600, 'Seagate', 'Black'),
(22, 'Seagate BarraCuda 4TB', 2500.00, '3.5\"', 'HDD', 4000, 'SATA3', 220, 220, 7, 800, 'Seagate', 'Black'),
(23, 'WD Blue 1TB HDD', 1120.00, '3.5\"', 'HDD', 1000, 'SATA3', 210, 210, 6, 300, 'WD', 'Black'),
(24, 'WD Blue 2TB HDD', 1620.00, '3.5\"', 'HDD', 2000, 'SATA3', 210, 210, 6, 600, 'WD', 'Black'),
(25, 'WD Blue 4TB HDD', 2750.00, '3.5\"', 'HDD', 4000, 'SATA3', 210, 210, 7, 800, 'WD', 'Black'),
(26, 'WD Red Plus 4TB', 3500.00, '3.5\"', 'HDD', 4000, 'SATA3', 180, 180, 7, 1000, 'WD', 'Black'),
(27, 'Seagate IronWolf 4TB', 3250.00, '3.5\"', 'HDD', 4000, 'SATA3', 180, 180, 7, 1000, 'Seagate', 'Black'),
(28, 'Samsung 860 EVO 500GB', 1370.00, '2.5\"', 'SSD', 500, 'SATA3', 550, 520, 3, 300, 'Samsung', 'Black'),
(29, 'Samsung 860 EVO 1TB', 2120.00, '2.5\"', 'SSD', 1000, 'SATA3', 550, 520, 3, 600, 'Samsung', 'Black'),
(30, 'ADATA XPG SX8200 Pro 1TB', 2750.00, 'M.2', 'M.2', 1000, 'PCIe 3.0', 3500, 3000, 5, 640, 'ADATA', 'Black'),
(31, 'ADATA Legend 800 1TB', 1750.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 3500, 2800, 4, 400, 'ADATA', 'Black'),
(32, 'Corsair MP600 Pro 1TB', 4250.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 7000, 5500, 6, 700, 'Corsair', 'Black'),
(33, 'Corsair MP600 Core XT 2TB', 4500.00, 'M.2', 'M.2', 2000, 'PCIe 4.0', 5000, 4400, 5, 900, 'Corsair', 'Black'),
(34, 'Samsung 990 EVO Plus 2TB', 4490.00, 'M.2', 'M.2', 2000, 'PCIe 5.0', 7250, 6300, 7, 1200, 'Samsung', 'Black'),
(35, 'Samsung 990 EVO Plus 1TB', 2490.00, 'M.2', 'M.2', 1000, 'PCIe 5.0', 7250, 6300, 7, 600, 'Samsung', 'Black'),
(36, 'WD Black SN850X 2TB', 3990.00, 'M.2', 'M.2', 2000, 'PCIe 4.0', 7300, 6600, 7, 1200, 'Western Digital', 'Black'),
(37, 'WD Black SN850X 1TB', 2290.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 7300, 6300, 7, 600, 'Western Digital', 'Black'),
(38, 'WD Black SN850X 4TB', 8990.00, 'M.2', 'M.2', 4000, 'PCIe 4.0', 7300, 6600, 7, 2400, 'Western Digital', 'Black'),
(39, 'Crucial T700 2TB', 5290.00, 'M.2', 'M.2', 2000, 'PCIe 5.0', 12400, 11800, 11, 1200, 'Crucial', 'Black'),
(40, 'Crucial T700 1TB', 3290.00, 'M.2', 'M.2', 1000, 'PCIe 5.0', 11700, 9500, 11, 600, 'Crucial', 'Black'),
(41, 'Crucial T500 2TB', 3790.00, 'M.2', 'M.2', 2000, 'PCIe 4.0', 7400, 7000, 8, 1200, 'Crucial', 'Black'),
(42, 'Crucial T500 1TB', 2190.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 7300, 6800, 8, 600, 'Crucial', 'Black'),
(43, 'Corsair MP700 Pro 2TB', 5790.00, 'M.2', 'M.2', 2000, 'PCIe 5.0', 12400, 11800, 11, 1400, 'Corsair', 'Black'),
(44, 'Corsair MP700 Pro 1TB', 3490.00, 'M.2', 'M.2', 1000, 'PCIe 5.0', 11700, 9500, 11, 700, 'Corsair', 'Black'),
(45, 'Kingston Fury Renegade 2TB', 3690.00, 'M.2', 'M.2', 2000, 'PCIe 4.0', 7300, 7000, 7, 1000, 'Kingston', 'Black'),
(46, 'Kingston Fury Renegade 4TB', 7990.00, 'M.2', 'M.2', 4000, 'PCIe 4.0', 7300, 7000, 7, 2000, 'Kingston', 'Black'),
(47, 'Samsung 980 Pro 1TB', 1990.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 7000, 5000, 6, 600, 'Samsung', 'Black'),
(48, 'Samsung 870 EVO 1TB', 1690.00, '2.5\"', 'SSD', 1000, 'SATA3', 560, 530, 3, 600, 'Samsung', 'Black'),
(49, 'Samsung 870 EVO 2TB', 2990.00, '2.5\"', 'SSD', 2000, 'SATA3', 560, 530, 3, 1200, 'Samsung', 'Black'),
(50, 'Crucial MX500 1TB', 1390.00, '2.5\"', 'SSD', 1000, 'SATA3', 560, 510, 3, 360, 'Crucial', 'Black'),
(51, 'Crucial MX500 2TB', 2590.00, '2.5\"', 'SSD', 2000, 'SATA3', 560, 510, 3, 700, 'Crucial', 'Black'),
(52, 'WD Blue SN580 1TB', 1490.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 4150, 4150, 5, 600, 'Western Digital', 'Blue'),
(53, 'WD Blue SN580 2TB', 2690.00, 'M.2', 'M.2', 2000, 'PCIe 4.0', 4150, 4150, 5, 900, 'Western Digital', 'Blue'),
(54, 'Kingston NV2 1TB', 1090.00, 'M.2', 'M.2', 1000, 'PCIe 4.0', 3500, 2100, 5, 320, 'Kingston', 'Black'),
(55, 'Kingston NV2 2TB', 1990.00, 'M.2', 'M.2', 2000, 'PCIe 4.0', 3500, 2800, 5, 640, 'Kingston', 'Black'),
(56, 'Seagate Barracuda 2TB', 1290.00, '3.5\"', 'HDD', 2000, 'SATA3', 220, 220, 6, NULL, 'Seagate', 'Silver'),
(57, 'Seagate Barracuda 4TB', 2190.00, '3.5\"', 'HDD', 4000, 'SATA3', 190, 190, 6, NULL, 'Seagate', 'Silver'),
(58, 'WD Blue 2TB', 1190.00, '3.5\"', 'HDD', 2000, 'SATA3', 175, 175, 5, NULL, 'Western Digital', 'Blue'),
(59, 'WD Blue 4TB', 2090.00, '3.5\"', 'HDD', 4000, 'SATA3', 175, 175, 5, NULL, 'Western Digital', 'Blue'),
(60, 'Toshiba P300 2TB', 1090.00, '3.5\"', 'HDD', 2000, 'SATA3', 190, 190, 6, NULL, 'Toshiba', 'Silver'),
(61, 'Toshiba X300 4TB', 2290.00, '3.5\"', 'HDD', 4000, 'SATA3', 200, 200, 7, NULL, 'Toshiba', 'Silver');

-- --------------------------------------------------------

--
-- Struktura tabulky `type`
--

CREATE TABLE `type` (
  `id` int(10) UNSIGNED NOT NULL,
  `name_short` varchar(255) NOT NULL,
  `name_long` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `used_parts`
--

CREATE TABLE `used_parts` (
  `id` int(10) UNSIGNED NOT NULL,
  `buildId` int(10) UNSIGNED NOT NULL,
  `partId` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `used_parts`
--

INSERT INTO `used_parts` (`id`, `buildId`, `partId`) VALUES
(237, 15, 245),
(238, 15, 246),
(239, 15, 247),
(240, 15, 248),
(241, 15, 249),
(242, 15, 250),
(243, 15, 251),
(244, 15, 252),
(245, 15, 253),
(246, 15, 254),
(247, 16, 255),
(248, 16, 256),
(249, 16, 257),
(250, 16, 258),
(251, 16, 259),
(252, 16, 260),
(253, 16, 261),
(254, 16, 262),
(255, 16, 263),
(264, 18, 272),
(265, 18, 273),
(266, 18, 274),
(267, 18, 275),
(268, 18, 276),
(269, 18, 277),
(270, 18, 278),
(271, 18, 279),
(272, 19, 280),
(273, 19, 281),
(274, 19, 282),
(275, 19, 283),
(276, 19, 284),
(277, 19, 285),
(278, 19, 286),
(279, 19, 287),
(280, 20, 288),
(281, 20, 289),
(282, 20, 290),
(283, 20, 291),
(284, 20, 292),
(285, 20, 293),
(286, 20, 294),
(287, 20, 295),
(288, 20, 296),
(289, 21, 297),
(290, 21, 298),
(291, 21, 299),
(292, 21, 300),
(293, 21, 301),
(294, 21, 302),
(295, 21, 303),
(296, 21, 304),
(297, 21, 305),
(298, 22, 306),
(299, 22, 307),
(300, 22, 308),
(301, 22, 309),
(302, 22, 310),
(303, 22, 311),
(304, 22, 312),
(305, 22, 313),
(306, 23, 314),
(307, 23, 315),
(308, 23, 316),
(309, 23, 317),
(310, 23, 318),
(311, 23, 319),
(312, 23, 320),
(313, 23, 321),
(314, 24, 322),
(315, 24, 323),
(316, 24, 324),
(317, 24, 325),
(318, 24, 326),
(319, 24, 327),
(320, 24, 328),
(321, 24, 329),
(322, 25, 330),
(323, 25, 331),
(324, 25, 332),
(325, 25, 333),
(326, 25, 334),
(327, 25, 335),
(328, 25, 336),
(329, 25, 337),
(330, 25, 338);

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `verification_token_expires` datetime DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_token_expires` datetime DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `subscription` tinyint(3) NOT NULL,
  `roleId` int(10) UNSIGNED NOT NULL,
  `is_banned` tinyint(1) DEFAULT 0,
  `bannedAt` timestamp NULL DEFAULT NULL,
  `banReason` text DEFAULT NULL,
  `bannedBy` int(10) UNSIGNED DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `email_verified`, `verification_token`, `verification_token_expires`, `password_reset_token`, `password_reset_token_expires`, `password`, `subscription`, `roleId`, `is_banned`, `bannedAt`, `banReason`, `bannedBy`, `createdAt`, `updatedAt`) VALUES
(19, 'Profesionalni_Konfigurator', 'pcprofesional@pocitace.com', 1, '', '2026-04-08 12:45:45', NULL, NULL, '$2y$10$vEiF3Fmg2t3iB4lh6zpWkOgbDKYE0pTzk8NBOKkYGaz8GyTKjkkMO', 3, 1, 0, NULL, NULL, NULL, '2026-04-07 10:45:45', '2026-04-07 10:45:45'),
(21, 'JanNovak', 'jan.novak@email.cz', 1, NULL, NULL, NULL, NULL, '$2y$10$rWT65fK97.FJXHAb4L0IqunlwvefIDyGa7oOuO8fQuQNm/H8RWRT.', 1, 1, 0, NULL, NULL, NULL, '2025-11-10 07:15:00', '2025-11-10 07:15:00'),
(22, 'PetrDvorak', 'petr.dvorak@seznam.cz', 1, NULL, NULL, NULL, NULL, '$2y$10$rWT65fK97.FJXHAb4L0IqunlwvefIDyGa7oOuO8fQuQNm/H8RWRT.', 2, 1, 0, NULL, NULL, NULL, '2025-11-15 13:30:00', '2025-11-15 13:30:00'),
(23, 'LucieSvobodova', 'lucie.svobodova@gmail.com', 1, NULL, NULL, NULL, NULL, '$2y$10$rWT65fK97.FJXHAb4L0IqunlwvefIDyGa7oOuO8fQuQNm/H8RWRT.', 1, 1, 0, NULL, NULL, NULL, '2025-12-01 08:45:00', '2025-12-01 08:45:00'),
(24, 'TomasCerny', 'tomas.cerny@centrum.cz', 1, NULL, NULL, NULL, NULL, '$2y$10$rWT65fK97.FJXHAb4L0IqunlwvefIDyGa7oOuO8fQuQNm/H8RWRT.', 3, 1, 0, NULL, NULL, NULL, '2025-12-10 10:00:00', '2025-12-10 10:00:00'),
(25, 'KaterinaMarkova', 'katerina.markova@email.cz', 1, NULL, NULL, NULL, NULL, '$2y$10$rWT65fK97.FJXHAb4L0IqunlwvefIDyGa7oOuO8fQuQNm/H8RWRT.', 1, 1, 0, NULL, NULL, NULL, '2026-01-05 15:20:00', '2026-01-05 15:20:00'),
(26, 'MartinProchazka', 'martin.prochazka@post.cz', 1, NULL, NULL, NULL, NULL, '$2y$10$rWT65fK97.FJXHAb4L0IqunlwvefIDyGa7oOuO8fQuQNm/H8RWRT.', 2, 1, 0, NULL, NULL, NULL, '2026-01-20 09:10:00', '2026-01-20 09:10:00'),
(27, 'EvaNemcova', 'eva.nemcova@outlook.cz', 1, NULL, NULL, NULL, NULL, '$2y$10$rWT65fK97.FJXHAb4L0IqunlwvefIDyGa7oOuO8fQuQNm/H8RWRT.', 1, 1, 0, NULL, NULL, NULL, '2026-02-03 12:00:00', '2026-02-03 12:00:00'),
(28, 'JakubVesely', 'jakub.vesely@email.cz', 1, NULL, NULL, NULL, NULL, '$2y$10$rWT65fK97.FJXHAb4L0IqunlwvefIDyGa7oOuO8fQuQNm/H8RWRT.', 3, 3, 0, NULL, NULL, NULL, '2025-10-01 05:00:00', '2025-10-01 05:00:00'),
(29, 'AnnaKralova', 'anna.kralova@centrum.cz', 1, NULL, NULL, NULL, NULL, '$2y$10$rWT65fK97.FJXHAb4L0IqunlwvefIDyGa7oOuO8fQuQNm/H8RWRT.', 3, 3, 0, NULL, NULL, NULL, '2025-10-05 07:30:00', '2025-10-05 07:30:00'),
(30, 'FilipSimek', 'filip.simek@dmpconfig.cz', 1, NULL, NULL, NULL, NULL, '$2y$10$rWT65fK97.FJXHAb4L0IqunlwvefIDyGa7oOuO8fQuQNm/H8RWRT.', 3, 2, 0, NULL, NULL, NULL, '2025-09-15 04:00:00', '2025-09-15 04:00:00'),
(31, 'tester', 'averagealt@seznam.cz', 1, NULL, NULL, NULL, NULL, '$2y$10$d41.kv5wwT0XVrpmWLK5QeiGFVoD692MeFgfCDxgvs5tOXhytyEqG', 0, 1, 0, NULL, NULL, NULL, '2026-04-09 21:47:29', '2026-04-09 21:47:29');

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `brand`
--
ALTER TABLE `brand`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `builds`
--
ALTER TABLE `builds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `builds_userid_foreign` (`userId`);

--
-- Indexy pro tabulku `case`
--
ALTER TABLE `case`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `component_submissions`
--
ALTER TABLE `component_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `reviewedBy` (`reviewedBy`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_componentType` (`componentType`);

--
-- Indexy pro tabulku `cooler`
--
ALTER TABLE `cooler`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `cpu`
--
ALTER TABLE `cpu`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `postId` (`postId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `createdAt` (`createdAt`);

--
-- Indexy pro tabulku `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buildId` (`buildId`),
  ADD KEY `createdAt` (`createdAt`),
  ADD KEY `userId` (`userId`),
  ADD KEY `isVisible` (`isVisible`);

--
-- Indexy pro tabulku `forum_reports`
--
ALTER TABLE `forum_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reportedByUserId` (`reportedByUserId`),
  ADD KEY `status` (`status`),
  ADD KEY `createdAt` (`createdAt`),
  ADD KEY `postId` (`postId`),
  ADD KEY `commentId` (`commentId`);

--
-- Indexy pro tabulku `gpu`
--
ALTER TABLE `gpu`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `motherboard`
--
ALTER TABLE `motherboard`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `psu`
--
ALTER TABLE `psu`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `ram`
--
ALTER TABLE `ram`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `storage`
--
ALTER TABLE `storage`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `type`
--
ALTER TABLE `type`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `used_parts`
--
ALTER TABLE `used_parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `used_parts_buildid_foreign` (`buildId`),
  ADD KEY `used_parts_partid_foreign` (`partId`);

--
-- Indexy pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_roleid_foreign` (`roleId`),
  ADD KEY `idx_verification_token` (`verification_token`),
  ADD KEY `idx_password_reset_token` (`password_reset_token`),
  ADD KEY `idx_email_verified` (`email_verified`),
  ADD KEY `idx_is_banned` (`is_banned`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `brand`
--
ALTER TABLE `brand`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `builds`
--
ALTER TABLE `builds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pro tabulku `case`
--
ALTER TABLE `case`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT pro tabulku `component_submissions`
--
ALTER TABLE `component_submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pro tabulku `cooler`
--
ALTER TABLE `cooler`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT pro tabulku `cpu`
--
ALTER TABLE `cpu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=367;

--
-- AUTO_INCREMENT pro tabulku `forum_comments`
--
ALTER TABLE `forum_comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pro tabulku `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pro tabulku `forum_reports`
--
ALTER TABLE `forum_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pro tabulku `gpu`
--
ALTER TABLE `gpu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT pro tabulku `motherboard`
--
ALTER TABLE `motherboard`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT pro tabulku `parts`
--
ALTER TABLE `parts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=339;

--
-- AUTO_INCREMENT pro tabulku `psu`
--
ALTER TABLE `psu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT pro tabulku `ram`
--
ALTER TABLE `ram`
  MODIFY `id` bigint(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT pro tabulku `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `storage`
--
ALTER TABLE `storage`
  MODIFY `id` bigint(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT pro tabulku `type`
--
ALTER TABLE `type`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `used_parts`
--
ALTER TABLE `used_parts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=331;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `builds`
--
ALTER TABLE `builds`
  ADD CONSTRAINT `builds_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `component_submissions`
--
ALTER TABLE `component_submissions`
  ADD CONSTRAINT `component_submissions_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `component_submissions_ibfk_2` FOREIGN KEY (`reviewedBy`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Omezení pro tabulku `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD CONSTRAINT `forum_comments_ibfk_1` FOREIGN KEY (`postId`) REFERENCES `forum_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_comments_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_posts_ibfk_2` FOREIGN KEY (`buildId`) REFERENCES `builds` (`id`) ON DELETE SET NULL;

--
-- Omezení pro tabulku `forum_reports`
--
ALTER TABLE `forum_reports`
  ADD CONSTRAINT `forum_reports_ibfk_1` FOREIGN KEY (`postId`) REFERENCES `forum_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_reports_ibfk_2` FOREIGN KEY (`commentId`) REFERENCES `forum_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_reports_ibfk_3` FOREIGN KEY (`reportedByUserId`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `used_parts`
--
ALTER TABLE `used_parts`
  ADD CONSTRAINT `used_parts_buildid_foreign` FOREIGN KEY (`buildId`) REFERENCES `builds` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `used_parts_partid_foreign` FOREIGN KEY (`partId`) REFERENCES `parts` (`id`);

--
-- Omezení pro tabulku `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_roleid_foreign` FOREIGN KEY (`roleId`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
