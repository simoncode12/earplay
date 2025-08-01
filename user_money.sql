-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 01 Agu 2025 pada 13.02
-- Versi server: 11.4.7-MariaDB-deb12
-- Versi PHP: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_money`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `ad_campaigns`
--

CREATE TABLE `ad_campaigns` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `campaign_type` enum('video','banner_display','banner_overlay') NOT NULL DEFAULT 'video',
  `vast_tag` text NOT NULL,
  `cpm_rate` decimal(10,4) NOT NULL COMMENT 'Biaya per 1000 impresi',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ad_impressions`
--

CREATE TABLE `ad_impressions` (
  `id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `ad_campaign_id` int(11) NOT NULL,
  `revenue_generated` decimal(20,10) NOT NULL COMMENT 'Pendapatan aktual dari ad server',
  `impression_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `live_sessions`
--

CREATE TABLE `live_sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `video_id` int(11) NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `live_sessions`
--

INSERT INTO `live_sessions` (`id`, `session_id`, `video_id`, `last_update`) VALUES
(1, '4i3rsg1rq7310uksgfr95tt0gn', 1, '2025-07-30 07:28:13'),
(125, '4i3rsg1rq7310uksgfr95tt0gn', 3, '2025-07-30 07:49:41'),
(144, '4i3rsg1rq7310uksgfr95tt0gn', 2, '2025-07-30 08:05:05'),
(167, '75dkd321ck1k2vpur6g27ona3o', 1, '2025-07-30 12:52:25'),
(205, '9u3qalo1adk9ro1m50l3jaiem6', 1, '2025-07-30 13:31:25'),
(285, '681f583paaus5tmdohgv2gppqe', 3, '2025-07-31 06:50:57'),
(398, '75dkd321ck1k2vpur6g27ona3o', 4, '2025-07-30 17:04:22'),
(438, '7p64221k0ii1mvb280015r452v', 4, '2025-07-30 17:27:43'),
(561, '7p64221k0ii1mvb280015r452v', 3, '2025-07-30 17:27:58'),
(562, '7p64221k0ii1mvb280015r452v', 1, '2025-07-30 18:20:08'),
(594, '681f583paaus5tmdohgv2gppqe', 4, '2025-07-31 05:31:30'),
(633, '681f583paaus5tmdohgv2gppqe', 1, '2025-07-31 06:54:44'),
(888, '681f583paaus5tmdohgv2gppqe', 2, '2025-07-31 14:16:22'),
(1383, '75dkd321ck1k2vpur6g27ona3o', 3, '2025-07-31 06:16:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('new_video','system') NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `related_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'new_video', 4, 'anonyme has uploaded a new video: KUMPULAN LAGU POP GALAU AKUSTIK TERBAIK 2025 + LIRIK LAGU || kumpulan lagu sad song download', 1, '2025-07-30 13:20:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payouts`
--

CREATE TABLE `payouts` (
  `id` int(11) NOT NULL,
  `creator_id` int(11) DEFAULT NULL,
  `amount` decimal(20,10) NOT NULL,
  `payout_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `playlists`
--

CREATE TABLE `playlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `playlists`
--

INSERT INTO `playlists` (`id`, `user_id`, `title`, `description`, `created_at`) VALUES
(1, 1, 'test', NULL, '2025-07-30 13:12:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `playlist_videos`
--

CREATE TABLE `playlist_videos` (
  `id` int(11) NOT NULL,
  `playlist_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `playlist_videos`
--

INSERT INTO `playlist_videos` (`id`, `playlist_id`, `video_id`, `added_at`) VALUES
(1, 1, 3, '2025-07-30 13:12:50'),
(2, 1, 2, '2025-07-30 13:13:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('adserver_base_url', 'https://pubads.g.doubleclick.net/gampad/ads?iu={ZONE_ID}'),
('auto_approve_monetization', 'off'),
('default_ad_zone_id', '18'),
('default_vast_tag', ''),
('min_subscribers_for_monetization', '1'),
('reward_rate_per_second', '0.0000001'),
('rtb_endpoint_url', 'https://rtb.svradv.com/rtb-handler.php?key=586d2cded533bded57434d427bad4f88');

-- --------------------------------------------------------

--
-- Struktur dari tabel `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `subscriber_id`, `creator_id`) VALUES
(4, 1, 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','creator','admin') NOT NULL DEFAULT 'user',
  `balance` decimal(20,10) NOT NULL DEFAULT 0.0000000000,
  `creator_earnings` decimal(20,10) NOT NULL DEFAULT 0.0000000000,
  `monetization_status` enum('not_applied','pending','approved','rejected') NOT NULL DEFAULT 'not_applied',
  `ad_zone_id` int(11) DEFAULT NULL,
  `revenue_share` tinyint(3) DEFAULT 55 COMMENT 'Persentase bagi hasil untuk kreator (e.g., 55 untuk 55%)',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `balance`, `creator_earnings`, `monetization_status`, `ad_zone_id`, `revenue_share`, `created_at`) VALUES
(1, 'anonym', 'ari513270@gmail.com', '$2y$11$May9arB4HmX4QSPPLaT8hebZvWDCaRqwNMiBTLMhNevNrAr7IS/f6', 'admin', 0.0015674000, 0.0000000000, 'not_applied', NULL, 55, '2025-07-28 23:43:40'),
(3, 'anonyme', 'syati5457@gmail.com', '$2y$10$/0AFlTr7ju3YE0j/raOYZ.uAXoJ/25HCP/AzDdKhKS14eVKwclRR2', 'creator', 0.0189505000, 0.5107155000, 'approved', NULL, 55, '2025-07-29 10:37:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `youtube_id` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `duration` int(11) NOT NULL DEFAULT 0 COMMENT 'Durasi dalam detik',
  `uploader_id` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `likes` int(11) NOT NULL DEFAULT 0,
  `dislikes` int(11) NOT NULL DEFAULT 0,
  `earnings` decimal(20,10) NOT NULL DEFAULT 0.0000000000,
  `ad_campaign_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `videos`
--

INSERT INTO `videos` (`id`, `youtube_id`, `title`, `description`, `thumbnail_url`, `duration`, `uploader_id`, `views`, `likes`, `dislikes`, `earnings`, `ad_campaign_id`, `created_at`) VALUES
(1, 'e-eGqDkaCbQ', 'DJ TIKTOK TERBARU 2025 || JIKA TIDAK HARI INI MUNGKIN MINGGU DEPAN || FULL SONG DJ EDITRA TAMBA❗❗❗', 'DJ TIKTOK TERBARU 2025 || JIKA TIDAK HARI INI MUNGKIN MINGGU DEPAN || FULL SONG DJ EDITRA TAMBA❗❗❗\r\n\r\nDISCLAIMER:AKUN INI BUKAN AKUN REMIXER ASLI, KAMI HANYA ME REUPLOAD UNTUK IKUT MERAMAIKAN\r\n\r\n JIKA ADA YANG KEBERATAN DENGAN VIDIO INI  BISA HUBUNGI 🙏KAMI SIAP UNTUK MENGHAPUS NYA, APA BILA ANDA TERTARIK KERJA SAMA DENGAN SANGAT SENANG HATI KAMI BERSEDIA🙏\r\n\r\n╔═╦╗╔╦╗╔═╦═╦╦╦╦╗╔═╗\r\n║╚╣║║║╚╣╚╣╔╣╔╣║╚╣═╣ \r\n╠╗║╚╝║║╠╗║╚╣║║║║║═╣\r\n╚═╩══╩═╩═╩═╩╝╚╩═╩═╝\r\n\r\n\r\n🔸Like👍\r\n🔸Share↗\r\n🔸Coment📣\r\n🔸Aktifkan Loncengnya🔔\r\n\r\nBismillah, Buat Yang Subscribe Semoga Rezekinya Lancar \r\n\r\n#djslowbassfullalbum​ #djslowbassterbaru2025​  #djterbaru2025​ #djamarcm​​ #djterbaru​​ #djcampuran​​ #djjedagjedug​​ #djviral​​ #djtiktok​​ #djfullbass​​ #djfyptiktok​​ #jedagjedug​​  #djmengkane​​ #djslowbass​​ #djtiktokterbaru​​ #djtiktok2024​​ #djterbaru2024​​\r\n\r\nTHANKS FOR WATCHING', 'https://i.ytimg.com/vi/e-eGqDkaCbQ/hqdefault.jpg', 3908, 3, 142, 0, 0, 0.2704140000, NULL, '2025-07-28 23:44:02'),
(2, 'dgEi4jHI034', 'DJ VELOCITY NAN KO PAHAM TREND VIRAL REMIX TIKTOK 2025', 'DJ VELOCITY NAN KO PAHAM TREND VIRAL REMIX TIKTOK 2025 \r\nJudul :   Nan Ko Paham\r\nArtis : - Kkz D Blg\r\nVideo Original :  https://www.youtube.com/watch?v=Geh34xz_Syo\r\nRemixer : - DJ CHOKO\r\n                 \r\nSemoga yang subsribe ditambahkan rejekinya\r\n\r\n ➖➖➖➖➖➖➖➖➖➖ \r\n\r\nDukung channel ini agar berkembang dan rajin mengupload music-music lainnya dengan cara 🎶LIKE✔ 🎶COMENT✔ 🎶SHARE✔ 🎶SUBSCRIBE✔ Nyalakan loncengnya agar tidak ketinggalan video terbaru dari channel ini. \r\n\r\nOk terimakasih \r\n. \r\n\r\n➖➖➖➖➖➖➖➖➖➖\r\n\r\nLirik – \r\n\r\n\r\n__ __ __ __ __ __ __ __ __ __\r\n———————————————————————\r\n#nankopaham\r\n#djnankopaham\r\n#viraltiktok \r\n#djtiktok \r\n#trend \r\n#viraltiktokterbaru2025 \r\n#jedagjedug \r\n#remix \r\n#trendtiktok \r\n#slowed', 'https://i.ytimg.com/vi/dgEi4jHI034/hqdefault.jpg', 319, 3, 106, 1, 0, 0.0126000000, NULL, '2025-07-29 09:19:30'),
(3, 'HB-hb7goSeA', 'Review Jujur: Lagu Enak Didengar', 'Di video kali ini, kita akan mengupas tuntas tentang \'lagu enak didengar\'. Sebuah tayangan yang akan mengubah cara pandang Anda.\r\n\r\n--- Rangkuman ---\r\n...\r\n\r\nJangan lupa untuk Like, Share, dan Subscribe untuk konten menarik lainnya!', 'https://i.ytimg.com/vi/HB-hb7goSeA/hqdefault.jpg', 12368, 3, 98, 0, 0, 0.0800000000, NULL, '2025-07-30 07:07:48'),
(4, 'qVgX7XKEZZE', 'KUMPULAN LAGU POP GALAU AKUSTIK TERBAIK 2025 + LIRIK LAGU || kumpulan lagu sad song download', 'KUMPULAN LAGU POP GALAU AKUSTIK TERBAIK 2025 + LIRIK LAGU || kumpulan lagu sad song download\r\n\r\nisi album \r\nKu tak bisa \r\nBukan untuk ku \r\nSelalu sabar \r\nAishiteru 2\r\n7 samudera \r\nDiary depresi ku \r\nBerharap kau kembali \r\nBagai pasir dalam air\r\nTentang perasaanku \r\nSebelum cahaya \r\nHarus pergi \r\nSampai akhir \r\nRindu merana\r\n\r\n#fullalbum2025 #fullalbumtanpaiklan #fullalbum #kumpulanlaguterbaru #kumpulanlaguterpopuler #kumpulanlaguterbaik #laguindonesiahits #laguindonesiapopuler #laguakustik #laguakustikcafe #laguakustikindonesia #lagupopgalau #lagupopindonesiaterpopuler #lagupopindonesia #kumpulanlaguakustik #playlistlagugalau #laguterbaiksepanjangmasa #lagugalau2025 #laguterbaik2025 #laguterpopuler2025 #laguviral2025 #lagusedih2025 #bestlagupopindonesia #agusriansyah #laguviraltiktok', 'https://i.ytimg.com/vi/qVgX7XKEZZE/hqdefault.jpg', 3041, 3, 33, 0, 0, 0.0615515000, NULL, '2025-07-30 13:20:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `video_interactions`
--

CREATE TABLE `video_interactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `interaction_type` enum('like','dislike') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `video_interactions`
--

INSERT INTO `video_interactions` (`id`, `user_id`, `video_id`, `interaction_type`) VALUES
(2, 1, 2, 'like');

-- --------------------------------------------------------

--
-- Struktur dari tabel `watch_stats`
--

CREATE TABLE `watch_stats` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `watched_seconds` int(11) NOT NULL DEFAULT 0,
  `last_update` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `watch_stats`
--

INSERT INTO `watch_stats` (`id`, `user_id`, `video_id`, `watched_seconds`, `last_update`) VALUES
(1, 1, 1, 1704, '2025-07-31 03:01:44'),
(20, 1, 2, 317, '2025-07-31 14:08:27'),
(34, 3, 1, 371, '2025-07-30 12:52:25'),
(72, 1, 3, 170, '2025-07-31 06:18:46'),
(73, 3, 4, 8, '2025-07-30 17:02:45'),
(75, 1, 4, 20, '2025-07-31 05:31:30'),
(299, 3, 3, 1, '2025-07-31 06:16:57');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `ad_campaigns`
--
ALTER TABLE `ad_campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ad_impressions`
--
ALTER TABLE `ad_impressions`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_id` (`video_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `live_sessions`
--
ALTER TABLE `live_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_video_unique` (`session_id`,`video_id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creator_id_idx` (`creator_id`);

--
-- Indeks untuk tabel `playlists`
--
ALTER TABLE `playlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `playlist_videos`
--
ALTER TABLE `playlist_videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `playlist_video_unique` (`playlist_id`,`video_id`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indeks untuk tabel `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscription_unique` (`subscriber_id`,`creator_id`),
  ADD KEY `creator_id` (`creator_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `youtube_id` (`youtube_id`),
  ADD KEY `uploader_id` (`uploader_id`);

--
-- Indeks untuk tabel `video_interactions`
--
ALTER TABLE `video_interactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_video_interaction` (`user_id`,`video_id`),
  ADD KEY `video_id` (`video_id`);

--
-- Indeks untuk tabel `watch_stats`
--
ALTER TABLE `watch_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_video_unique` (`user_id`,`video_id`),
  ADD KEY `video_id` (`video_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `ad_campaigns`
--
ALTER TABLE `ad_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `ad_impressions`
--
ALTER TABLE `ad_impressions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `live_sessions`
--
ALTER TABLE `live_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1551;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `playlists`
--
ALTER TABLE `playlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `playlist_videos`
--
ALTER TABLE `playlist_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `video_interactions`
--
ALTER TABLE `video_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `watch_stats`
--
ALTER TABLE `watch_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=319;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payouts`
--
ALTER TABLE `payouts`
  ADD CONSTRAINT `payouts_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `video_interactions`
--
ALTER TABLE `video_interactions`
  ADD CONSTRAINT `video_interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_interactions_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `watch_stats`
--
ALTER TABLE `watch_stats`
  ADD CONSTRAINT `watch_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `watch_stats_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
