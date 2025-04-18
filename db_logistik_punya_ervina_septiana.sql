-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Apr 2025 pada 08.53
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_logistik_punya_ervina_septiana`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_delivery`
--

CREATE TABLE `data_delivery` (
  `id_up` int(11) NOT NULL,
  `part_no` varchar(100) NOT NULL,
  `minor` varchar(50) DEFAULT NULL,
  `part_name` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `delivery_type` varchar(100) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `packing_type` varchar(100) DEFAULT NULL,
  `qty_pack` int(11) NOT NULL,
  `l` decimal(10,2) NOT NULL,
  `w` decimal(10,2) NOT NULL,
  `h` decimal(10,2) NOT NULL,
  `date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_delivery`
--

INSERT INTO `data_delivery` (`id_up`, `part_no`, `minor`, `part_name`, `supplier`, `delivery_type`, `destination`, `packing_type`, `qty_pack`, `l`, `w`, `h`, `date`) VALUES
(105, '0001-XY01', 'Y0', 'KNOB SUB-ASSY, SHIFT LEVER', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'TP-332', 60, 335.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(106, '0001-XY02', 'Y1', 'COVER, STEERING COLUMN, UPR', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 10, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(107, '0001-XY03', 'Y1', 'COVER, STEERING COLUMN, UPR', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 6, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(108, '0001-XY04', 'Y1', 'COVER, STEERING COLUMN, LWR', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 5, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(109, '0001-XY05', 'XX', 'COVER, STEERING COLUMN, LWR', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 6, 620.00, 430.00, 310.00, '2025-04-15 07:14:39'),
(110, '0002-XY02', 'Y0', 'SPACER, CAB FR MOUNTING', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'TP-362', 60, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(111, '0002-XY03', 'Y0', 'SHAFT, GLOVE COMPARTMENT DOOR HINGE', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'TP-331', 200, 335.00, 335.00, 100.00, '2025-04-15 07:14:39'),
(112, '0002-XY04', 'XX', 'COVER, PARKING BRAKE HOLE', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 8, 670.00, 503.00, 380.00, '2025-04-15 07:14:39'),
(113, '0002-XY05', 'XX', 'CLAMP, DOOR LOCK LINK', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'TP-332', 200, 335.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(114, '0011-Y0X1', 'X1', 'COVER, FR SEAT BELT HOLE', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 25, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(115, '0011-Y0X2', 'X3', 'COVER, FR SEAT BELT HOLE, LH', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 25, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(116, '0011-Y0X3', 'X3', 'GRIP, ASSIST', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'TP-332', 90, 335.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(117, '0011-Y0X4', 'Y0', 'HOOK, COAT', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'TP-332', 120, 335.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(118, '0011-Y0X5', 'Y1', 'COVER, RELAY BLOCK', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 260, 335.00, 335.00, 103.00, '2025-04-15 07:14:39'),
(119, '0101-01Y2', 'Y2', 'CLAMP, WIRING HARNESS', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'TP-332', 200, 335.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(120, '0101-01Y3', 'Y3', 'HOLDER, FR WASHER NOZZLE', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'TP-331', 100, 335.00, 335.00, 100.00, '2025-04-15 07:14:39'),
(121, '0101-2Y1X', 'Y5', 'MIRROR ASSY, INNER RR VIEW W/ROOM LAMP', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'TP-362', 12, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(122, '0101-2Y2X', 'XX', 'LENS, INNER RR VIEW MIRROR ROOM LAMP', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'TP-332', 20, 335.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(123, '0101-2Y3X', 'Y5', 'MIRROR ASSY, OUTER RR VIEW, RH', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 1, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(124, '0101-2Y4X', 'Y2', 'MIRROR ASSY, OUTER RR VIEW, RH', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 1, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(125, '0101-2Y5X', 'Y0', 'MIRROR ASSY, OUTER RR VIEW, RH', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 1, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(126, '0101-2Y6X', 'Y2', 'MIRROR ASSY, OUTER RR VIEW, LH', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 1, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(127, '0101-2Y7X', 'Y2', 'MIRROR ASSY, OUTER RR VIEW, LH', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 1, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(128, '0101-2Y8X', 'X1', 'MIRROR ASSY, OUTER RR VIEW, LH', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 1, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(129, '0101-2Y9X', 'X1', 'MIRROR ASSY, OUTER RR VIEW, LH', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 1, 670.00, 335.00, 195.00, '2025-04-15 07:14:39'),
(130, '0101-2Y00X', 'YY', 'COVER, ENGINE CONTROL COMPUTER, NO.1', 'LSP-APL', 'MR 05 COMBINE', 'HMMI-FINAL', 'POLLYBOX', 12, 335.00, 335.00, 195.00, '2025-04-15 07:14:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `delivery_schedule`
--

CREATE TABLE `delivery_schedule` (
  `id` int(11) NOT NULL,
  `delivery_date` date NOT NULL,
  `time` time NOT NULL,
  `cycle` int(11) NOT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `delivery_schedule`
--

INSERT INTO `delivery_schedule` (`id`, `delivery_date`, `time`, `cycle`, `status`) VALUES
(1, '2025-04-15', '08:00:00', 1, 'Scheduled');

-- --------------------------------------------------------

--
-- Struktur dari tabel `packing_calculation`
--

CREATE TABLE `packing_calculation` (
  `id_pack` int(11) NOT NULL,
  `calculation_date` date NOT NULL,
  `total_volume` decimal(10,3) NOT NULL,
  `required_trucks` int(11) NOT NULL,
  `last_truck_remaining_capacity` decimal(10,3) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `packing_calculation`
--

INSERT INTO `packing_calculation` (`id_pack`, `calculation_date`, `total_volume`, `required_trucks`, `last_truck_remaining_capacity`, `created_at`) VALUES
(5, '2025-04-15', 21.026, 1, 2.974, '2025-04-15 05:15:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `truck_volume`
--

CREATE TABLE `truck_volume` (
  `id` int(11) NOT NULL,
  `volume` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `truck_volume`
--

INSERT INTO `truck_volume` (`id`, `volume`) VALUES
(1, 24.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$f0KOpR9R8jY5KY7nrRME1uBXNTjOXUi.GBLkW.Y7VZn9vlTI6469y', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `data_delivery`
--
ALTER TABLE `data_delivery`
  ADD PRIMARY KEY (`id_up`),
  ADD KEY `idx_part_no` (`part_no`),
  ADD KEY `idx_date` (`date`);

--
-- Indeks untuk tabel `delivery_schedule`
--
ALTER TABLE `delivery_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `packing_calculation`
--
ALTER TABLE `packing_calculation`
  ADD PRIMARY KEY (`id_pack`);

--
-- Indeks untuk tabel `truck_volume`
--
ALTER TABLE `truck_volume`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `data_delivery`
--
ALTER TABLE `data_delivery`
  MODIFY `id_up` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT untuk tabel `delivery_schedule`
--
ALTER TABLE `delivery_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `packing_calculation`
--
ALTER TABLE `packing_calculation`
  MODIFY `id_pack` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `truck_volume`
--
ALTER TABLE `truck_volume`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
