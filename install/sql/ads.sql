CREATE TABLE IF NOT EXISTS `adsky_ads` (
  `id` int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `username` text COLLATE utf8_unicode_ci NOT NULL,
  `interval` int NOT NULL,
  `until` bigint NOT NULL,
  `type` int NOT NULL,
  `duration` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;