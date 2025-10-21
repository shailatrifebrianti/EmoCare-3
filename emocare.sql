-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 21 Okt 2025 pada 06.35
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `emocare`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `journals`
--

CREATE TABLE `journals` (
  `journal_id` int(10) UNSIGNED NOT NULL,
  `pengguna_id` int(10) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `judul` varchar(150) NOT NULL,
  `isi` mediumtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `moodtracker`
--

CREATE TABLE `moodtracker` (
  `mood_id` int(10) UNSIGNED NOT NULL,
  `pengguna_id` int(10) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `mood_level` tinyint(3) UNSIGNED NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data untuk tabel `moodtracker`
--

INSERT INTO `moodtracker` (`mood_id`, `pengguna_id`, `tanggal`, `mood_level`, `catatan`, `created_at`) VALUES
(42, 9, '2025-10-15', 5, 'jj', '2025-10-15 10:39:54'),
(43, 9, '2025-10-15', 5, 'jjj', '2025-10-15 10:40:04'),
(44, 9, '2025-10-15', 1, 'n', '2025-10-15 10:42:50'),
(45, 9, '2025-10-17', 5, 'kukejar deadline', '2025-10-17 17:07:26'),
(46, 9, '2025-10-17', 5, '', '2025-10-17 17:08:19'),
(47, 9, '2025-10-17', 1, 'ketemu crush', '2025-10-17 17:24:31'),
(48, 10, '2025-10-20', 3, 'dx', '2025-10-20 07:58:08'),
(49, 10, '2025-10-21', 5, 'njnjn', '2025-10-21 10:29:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `pengguna_id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `role` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`pengguna_id`, `nama`, `email`, `password_hash`, `created_at`, `updated_at`, `role`) VALUES
(9, 'shailalove', 'shailalove@gmail.com', '$2y$10$OhgsvbvGVzZQf8TX.vdw6.gEzXuq1D2bGzioJ0T06GXZn2ZLKsN8O', '2025-10-15 10:19:58', '2025-10-20 08:58:53', 'user'),
(10, 'Admin EmoCare', 'admin@emocare.local', '$2y$10$EgazTxRYI9RCUKhWUmts9Og/8QNqFxuTUxlIuXaiQiQA/OczdXFca', '2025-10-18 16:49:51', '2025-10-20 07:56:19', 'admin');

-- --------------------------------------------------------

--
-- Struktur dari tabel `quiz_options`
--

CREATE TABLE `quiz_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `quiz_options`
--

INSERT INTO `quiz_options` (`id`, `question_id`, `option_text`, `is_correct`) VALUES
(41, 6, 'Tidak Pernah', 0),
(42, 6, 'Jarang', 1),
(43, 6, 'Sering', 0),
(44, 6, 'Sangat Sering', 0),
(45, 7, 'Tidak Pernah', 1),
(46, 7, 'Jarang', 0),
(47, 7, 'Sering', 0),
(48, 7, 'Sering Banget', 0),
(49, 8, 'Tidak Pernah', 0),
(50, 8, 'Jarang', 0),
(51, 8, 'Sering', 0),
(52, 8, 'Sangat Sering', 0),
(53, 9, 'Tidak Pernah', 0),
(54, 9, 'Jarang', 0),
(55, 9, 'Sering', 0),
(56, 9, 'Sangat Sering', 0),
(57, 10, 'Tidak Pernah', 0),
(58, 10, 'Jarang', 0),
(59, 10, 'Sering', 0),
(60, 10, 'Sangat Sering', 0),
(61, 11, 'Tidak Pernah', 0),
(62, 11, 'Jarang', 0),
(63, 11, 'Sering', 0),
(64, 11, 'Sangat Sering', 0),
(65, 12, 'Tidak Pernah', 0),
(66, 12, 'Jarang', 0),
(67, 12, 'Sering', 0),
(68, 12, 'Sangat Sering', 0),
(69, 13, 'Tidak Pernah', 0),
(70, 13, 'Jarang', 0),
(71, 13, 'Sering', 0),
(72, 13, 'Sangat Sering', 0),
(77, 15, 'Tidak Pernah', 0),
(78, 15, 'Jarang', 0),
(79, 15, 'Sering', 0),
(80, 15, 'Sangat Sering', 0),
(81, 16, 'Tidak Pernah', 0),
(82, 16, 'Jarang', 0),
(83, 16, 'Sering', 0),
(84, 16, 'Sangat Sering', 0),
(85, 14, 'Tidak Pernah', 0),
(86, 14, 'Jarang', 0),
(87, 14, 'Sering', 0),
(88, 14, 'Sangat Sering', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` enum('self_esteem','social_anxiety') NOT NULL DEFAULT 'self_esteem'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `question_text`, `is_active`, `created_at`, `category`) VALUES
(6, 'Saya merasa diri saya berharga, terlepas dari nilai raport atau prestasi.', 1, '2025-10-20 04:31:59', 'self_esteem'),
(7, 'Saya bisa menyebutkan minimal tiga hal yang saya sukai dari diri saya.', 1, '2025-10-20 05:02:27', 'self_esteem'),
(8, 'Saya khawatir orang lain menilai saya saat berbicara di kelas.', 1, '2025-10-20 05:05:52', 'social_anxiety'),
(9, 'Tangan saya berkeringat atau bergetar ketika harus memperkenalkan diri.', 1, '2025-10-20 05:06:11', 'social_anxiety'),
(10, 'Saya cukup puas dengan penampilan saya saat ini.', 1, '2025-10-20 05:34:38', 'self_esteem'),
(11, 'Saya berani menyampaikan pendapat meskipun berbeda dengan teman.', 1, '2025-10-20 05:36:00', 'self_esteem'),
(12, 'Saya dapat menerima kegagalan sebagai bagian dari proses belajar.', 1, '2025-10-20 05:36:19', 'self_esteem'),
(13, 'Saya percaya bahwa saya mampu mempelajari hal baru jika berusaha.', 1, '2025-10-20 05:37:30', 'self_esteem'),
(14, 'Saya menghindari bertanya karena takut dianggap “aneh/lebay”.', 1, '2025-10-20 05:39:36', 'social_anxiety'),
(15, 'Saya sulit makan/minum di depan orang banyak karena cemas dinilai.', 1, '2025-10-20 05:40:11', 'social_anxiety'),
(16, 'Saya sering memikirkan ulang (overthinking) apa yang saya ucapkan pada orang lain.', 1, '2025-10-20 05:41:03', 'social_anxiety');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reminders`
--

CREATE TABLE `reminders` (
  `reminder_id` int(10) UNSIGNED NOT NULL,
  `pengguna_id` int(10) UNSIGNED NOT NULL,
  `judul` varchar(150) NOT NULL,
  `reminder_time` time NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_user_stats`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_user_stats` (
`pengguna_id` int(10) unsigned
,`total_journals` bigint(21)
,`total_reminders_active` bigint(21)
,`total_moods` bigint(21)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_user_stats`
--
DROP TABLE IF EXISTS `v_user_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_user_stats`  AS SELECT `p`.`pengguna_id` AS `pengguna_id`, (select count(0) from `journals` `j` where `j`.`pengguna_id` = `p`.`pengguna_id`) AS `total_journals`, (select count(0) from `reminders` `r` where `r`.`pengguna_id` = `p`.`pengguna_id` and `r`.`aktif` = 1) AS `total_reminders_active`, (select count(0) from `moodtracker` `m` where `m`.`pengguna_id` = `p`.`pengguna_id`) AS `total_moods` FROM `pengguna` AS `p` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `journals`
--
ALTER TABLE `journals`
  ADD PRIMARY KEY (`journal_id`),
  ADD KEY `ix_journal_user_date` (`pengguna_id`,`tanggal`,`journal_id`);

--
-- Indeks untuk tabel `moodtracker`
--
ALTER TABLE `moodtracker`
  ADD PRIMARY KEY (`mood_id`),
  ADD KEY `ix_mood_user_date` (`pengguna_id`,`tanggal`,`mood_id`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`pengguna_id`),
  ADD UNIQUE KEY `ux_pengguna_email` (`email`);

--
-- Indeks untuk tabel `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indeks untuk tabel `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`reminder_id`),
  ADD KEY `ix_rem_user_time` (`pengguna_id`,`reminder_time`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `journals`
--
ALTER TABLE `journals`
  MODIFY `journal_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `moodtracker`
--
ALTER TABLE `moodtracker`
  MODIFY `mood_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `pengguna_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `quiz_options`
--
ALTER TABLE `quiz_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT untuk tabel `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `reminders`
--
ALTER TABLE `reminders`
  MODIFY `reminder_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `journals`
--
ALTER TABLE `journals`
  ADD CONSTRAINT `fk_journal_user` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`pengguna_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `moodtracker`
--
ALTER TABLE `moodtracker`
  ADD CONSTRAINT `fk_mood_user` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`pengguna_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD CONSTRAINT `quiz_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `fk_rem_user` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`pengguna_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
