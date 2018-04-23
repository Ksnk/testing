-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Апр 23 2018 г., 07:59
-- Версия сервера: 5.7.18
-- Версия PHP: 7.1.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `homestead`
--

-- --------------------------------------------------------

--
-- Структура таблицы `api`
--

DROP TABLE IF EXISTS `api`;
CREATE TABLE IF NOT EXISTS `api` (
  `id` int(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT 'битовая маска - GET:1,POST:2',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'url API',
  `endpoints` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apilock` timestamp NULL DEFAULT NULL,
  `maxconn` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `api`
--

INSERT INTO `api` (`id`, `type`, `url`, `endpoints`, `apilock`, `maxconn`) VALUES
(1, 3, 'http://185.231.160.57/api/api_1.php', '0,1,2,3,4,5', NULL, 3),
(2, 2, 'http://185.231.160.57/api/api_2.php', '6,7,8,9,10,11', NULL, 2),
(3, 2, 'http://185.231.160.57/api/api_3.php', '12,13,14,15,16', NULL, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `endpoint_id` int(11) NOT NULL DEFAULT '0',
  `value` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `services`
--

INSERT INTO `services` (`id`, `type`, `endpoint_id`, `value`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 1, '2018-04-03 11:24:37', '2018-04-23 07:52:12'),
(2, 1, 1, 0, '2018-04-03 11:24:37', '2018-04-23 07:52:12'),
(3, 1, 2, 1, '2018-04-03 11:24:37', '2018-04-23 07:52:12'),
(4, 1, 3, 0, '2018-04-03 11:24:37', '2018-04-23 07:52:13'),
(5, 1, 4, 1, '2018-04-03 11:24:37', '2018-04-23 07:52:13'),
(6, 1, 5, 0, '2018-04-03 11:24:37', '2018-04-23 07:52:13'),
(7, 2, 6, 1, '2018-04-03 11:24:37', '2018-04-23 07:52:14'),
(8, 2, 7, 0, '2018-04-03 11:24:37', '2018-04-23 07:52:14'),
(9, 2, 8, 0, '2018-04-03 11:24:37', '2018-04-23 07:52:15'),
(10, 2, 9, 1, '2018-04-03 11:24:37', '2018-04-23 07:52:15'),
(11, 2, 10, 0, '2018-04-03 11:24:37', '2018-04-23 07:52:16'),
(12, 2, 11, 0, '2018-04-03 11:24:37', '2018-04-03 11:24:37'),
(13, 3, 12, 0, '2018-04-03 11:24:37', '2018-04-23 07:52:19'),
(14, 3, 13, 0, '2018-04-03 11:24:37', '2018-04-23 07:52:19'),
(15, 3, 14, 0, '2018-04-03 11:24:37', '2018-04-03 11:24:37'),
(16, 3, 15, 1, '2018-04-03 11:24:37', '2018-04-23 07:52:22'),
(17, 3, 16, 0, '2018-04-03 11:24:37', '2018-04-23 07:52:23'),
(18, 3, 11, 0, '2018-04-23 07:51:38', '2018-04-23 07:52:18');
