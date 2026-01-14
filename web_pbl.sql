-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 05:57 PM
-- Server version: 10.1.32-MariaDB
-- PHP Version: 5.6.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `web_pbl`
--

-- --------------------------------------------------------

--
-- Table structure for table `daftar_peserta`
--

CREATE TABLE `daftar_peserta` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `nik` varchar(30) NOT NULL,
  `jurusan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `daftar_peserta`
--

INSERT INTO `daftar_peserta` (`id`, `fullname`, `nik`, `jurusan`) VALUES
(2, 'Annisa Rizqi Adelia', '3312511094', 'Teknik Informatika'),
(3, 'oifejkk', '795823798', 'Teknik Mesin'),
(4, 'Aulia', '33125110', 'Manajemen'),
(5, 'ika insan', '567391', 'Teknik Mesin'),
(6, 'indah suci', '3312511095', 'Teknik Informatika');

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `hari_tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `agenda` varchar(255) NOT NULL,
  `daftar_peserta` text NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`id`, `hari_tanggal`, `waktu`, `lokasi`, `agenda`, `daftar_peserta`, `status`) VALUES
(1, '2025-11-03', '09:00:00', 'Ruangan Lt 3', 'Pembahasan Teknologi', 'Indah Yanti, Rizky Saputra, Budi Santoso, Siti Aminah', 'Selesai'),
(2, '2025-10-21', '15:00:00', 'Ruangan Lt 2', 'Pembahasan inftastruktur', 'iza, raiz, baya', 'Dibatalkan');

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `lokasi` varchar(150) DEFAULT NULL,
  `agenda` text,
  `peserta` text,
  `ppt` varchar(255) DEFAULT NULL,
  `status` enum('selesai','tertunda','mendatang') DEFAULT 'mendatang',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`id`, `project_id`, `judul`, `tanggal`, `waktu`, `lokasi`, `agenda`, `peserta`, `ppt`, `status`, `created_at`) VALUES
(2, 4, 'PBL', '2025-12-15', '10:00:00', 'Tecno Politeknikbatam', 'Mendiskusikan final projek PBL', 'annisa, indah, fais', '', 'mendatang', '2025-12-14 11:22:29'),
(4, 5, 'Evaluasi', '2026-01-15', '09:35:00', 'Ruangan Lt 2', 'Evaluasi kinerja tiap divisi, pembahasan kendala, dan rencana perbaikan bulan berikutnya', 'Direktur, Manajer Operasional, Manajer Keuangan, Supervisor, Staf Administrasi', '', 'mendatang', '2026-01-04 09:38:08'),
(5, 6, 'Sistem Informasi', '2026-01-17', '11:40:00', 'Ruangan Lt 3', 'Laporan kemajuan sistem pengembangan, identifikasi kendala teknis, dan penentuan target selanjutnya', 'Project Manager, Backend Developer, Frontend Developer, UI/UX Designer, QA Engineer', 'https://bit.ly/progres-proyek-si', 'mendatang', '2026-01-04 09:39:50'),
(6, 7, 'Koordinasi', '2026-01-19', '10:41:00', 'Ruangan Lt 4', 'Koordinasi pekerjaan mingguan, pembagian tugas, dan penyampaian informasi penting', 'Aulia, ika insan', 'https://bit.ly/koordinasi-mingguan', 'mendatang', '2026-01-04 09:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `status`) VALUES
(4, 'PBL', 'diskusi final PBL', 'Selesai'),
(5, 'Evaluasi', 'Rapat Evaluasi Kinerja Bulanan', 'Dibatalkan'),
(6, 'Sistem Informasi', 'Rapat Kemajuan Sistem Informasi', 'Tertunda'),
(7, 'Koordinasi', 'Rapat Koordinasi Mingguan', 'Mendatang');

-- --------------------------------------------------------

--
-- Table structure for table `rooms_meeting`
--

CREATE TABLE `rooms_meeting` (
  `id` int(11) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` enum('tersedia','terbooking') DEFAULT 'tersedia',
  `photo_name` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT '../assets/',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rooms_meeting`
--

INSERT INTO `rooms_meeting` (`id`, `room_name`, `capacity`, `status`, `photo_name`, `photo_path`, `created_at`, `updated_at`) VALUES
(1, 'Tecno Politeknikbatam', 100, '', 'https://uptown.id/wp-content/uploads/2020/03/U-Shape-Meeting-Room-Jakarta.jpg', '../assets/', '2026-01-04 10:33:54', '2026-01-04 11:15:54'),
(2, 'Ruangan Lt 2', 12, '', 'https://ifcjakarta.co.id/blog/uploads/berita/20230816153735_bg_ruang_meeting_kantor_(1).jpg', '../assets/', '2026-01-04 11:06:14', '2026-01-04 11:06:43'),
(3, 'Ruangan Lt 4', 7, '', 'https://voffice.co.id/storage/img/GALERI/GST/Meeting-Room-GST.jpg', '../assets/', '2026-01-04 11:07:51', '2026-01-04 11:07:51'),
(4, 'Ruangan Lt 3', 6, '', 'https://voffice.co.id/blog/wp-content/uploads/2024/08/meeting3-2.jpg', '../assets/', '2026-01-04 11:09:12', '2026-01-04 11:15:40'),
(5, 'Ruangan Lt 6', 7, '', 'https://uptown.id/wp-content/uploads/2018/12/DSC00685-1024x576.jpg', '../assets/', '2026-01-04 11:10:08', '2026-01-04 16:48:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `jurusan` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nik` varchar(30) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`jurusan`, `email`, `nik`, `fullname`, `username`, `password`, `role`) VALUES
('', 'indh@gmail.com', '', '', 'a', '$2y$10$mNl./nM7RceKky70uUqUs.ItyFz1j4CYCQIZ8KX153YAoyYRMQxmi', 'user'),
('', 'admin@gmail.com', '', '', 'I', '$2y$10$2m0FigU3XkDWyOOizXRg.e05Fp1jBLtcvVnvR.p24Wh7DXtJkOHca', 'admin'),
('Teknik Mesin', 'ika@gmail.com', '567391', 'ika insan', 'ika', '$2y$10$JfExvwtFF/Mdg9aKllISpuCOLdwstXyVIKOs/UVQA/6ZRXvpO28rO', 'user'),
('Teknik Informatika', 'idya1005@gmail.com', '3312511095', 'indah suci', 'indh', '$2y$10$BNXmcA3FSITCzQyD4avyu.J7U6JvCL8JqkEmVKUzDFnehp6Qpwnte', 'user'),
('Manajemen', 'aulia@gmail.com', '33125110', 'Aulia', 'li', '$2y$10$z2YiZvtl6touMkQ0xruIj.jF6Y8WXaUNfPCk.tOmuYmr05Z7vCMpa', 'user'),
('Teknik Mesin', 'ih3r2oi', '795823798', 'oifejkk', 'm', '$2y$10$kCJK4e62e.Oi9QfEwozymeuu/mi1G5phX//FlfHobqyt.krdTWpqa', 'user'),
('Teknik Informatika', 'annisa@gmail.com', '3312511094', 'Annisa Rizqi Adelia', 'n', '$2y$10$Z1onQhxG.MRCc8sBQO4Biu6cSnC.5hOe8evLJjsxM0JEZWlBmb9my', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daftar_peserta`
--
ALTER TABLE `daftar_peserta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_project` (`project_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms_meeting`
--
ALTER TABLE `rooms_meeting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daftar_peserta`
--
ALTER TABLE `daftar_peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `rooms_meeting`
--
ALTER TABLE `rooms_meeting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meetings`
--
ALTER TABLE `meetings`
  ADD CONSTRAINT `fk_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
