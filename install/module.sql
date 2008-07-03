-- phpMyAdmin SQL Dump
-- version 3.3.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 16, 2011 at 05:20 PM
-- Server version: 5.1.54
-- PHP Version: 5.3.5-1ubuntu7.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cms`
--

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

CREATE TABLE IF NOT EXISTS `module` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '100',
  `created` int(10) unsigned NOT NULL,
  `modify` int(10) unsigned NOT NULL,
  `expire` int(10) unsigned NOT NULL DEFAULT '31536000',
  `price` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `description` text,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- Dumping data for table `module`
--

INSERT INTO `module` (`id`, `name`, `version`, `created`, `modify`, `expire`, `price`, `title`, `description`, `status`) VALUES
(1, 'main', 200, 1215153316, 1238049316, 31536000, 2000, 'Engine', 'Handles general site configuration for administrators.', 1),
(2, 'menu', 100, 0, 0, 0, 0, 'Menu', 'Allows administrators to customize the site navigation menu.', 1),
(3, 'regional', 100, 0, 0, 31536000, 0, 'Globalization', 'Adds language handling functionality and enables the translation of the user interface to languages other than English.', 1),
(4, 'search', 100, 0, 0, 31536000, 0, 'Search', 'Enables site-wide keyword searching.', 1),
(5, 'feed', 100, 1215153316, 1236152116, 31536000, 6200, 'خوراک', 'گرفتن خورجي به صورت‌هاي ATOM، RSS، OPML، RDF از بخش‌هاي سايت', 1),
(6, 'map', 150, 1217878970, 1236714170, 31536000, 2695, 'Sitemap', '', 1),
(7, 'appearance', 100, 0, 0, 0, 0, 'Appearance', '', 1),
(8, 'user', 170, 1215153316, 1237482923, 31536000, 0, 'User', 'Manages the user registration and login system.', 1),
(9, 'role', 100, 0, 0, 0, 0, 'Role', '', 1),
(10, 'post', 200, 1215153316, 1234469121, 31536000, 15508, 'Post', 'Allows content to be submitted to the site and displayed on pages.', 1),
(11, 'taxonomy', 130, 1215153316, 1222881323, 31536000, 5400, 'Taxonomy', 'Enables the categorization of content.', 1),
(12, 'comment', 130, 1232955316, 1238049316, 31536000, 8200, 'Comment', 'Allows users to comment on and discuss published content.', 1),
(13, 'rate', 100, 1233637200, 1233637200, 31536000, 4150, 'Rate', 'This plugin enables website users to vote contents up or down.', 1),
(14, 'poll', 200, 1217634163, 1237333363, 31536000, 5000, 'Poll', 'Allows your site to capture votes on different topics in the form of multiple choice questions.', 1),
(15, 'link', 100, 1224717320, 1224717320, 31536000, 7100, 'Link', '', 1),
(16, 'statistics', 200, 1217617959, 1237749159, 31536000, 1000, 'Statistics', 'let you see and analyze your traffic data in an entirely new way.', 1),
(17, 'article', 1000, 1222854930, 1222855503, 31536000, 1753, 'Article', 'Use articles for time-specific content like news, press releases or blog posts.', 1),
(18, 'page', 1000, 1222854930, 1222855503, 31536000, 1753, 'Basic page', 'Use <em>basic pages</em> for your static content', 1),
(19, 'emoticons', 100, 1222854930, 1222855503, 31536000, 1753, 'Emoticons', '', 1),
(20, 'ping', 100, 0, 0, 31536000, 0, '', '', 1),
(21, 'exam', 100, 1205964990, 1205964990, 31536000, 10943, 'آزمون‌ساز', 'ايجاد آزمون اينترنتي و سنجيدن افراد', 0),
(22, 'newsletter', 100, 1217542986, 1217542986, 31536000, 1555, 'خبرنامه', 'ارسال خبر براي کاربران', 0),
(23, 'message', 150, 1224879560, 1237434166, 31536000, 7000, 'پيام خصوصي', 'انتقال پيام بين کاربران', 0),
(24, 'profile', 100, 1199488590, 1199488590, 31536000, 6835, 'شناسنامه', 'ايجاد شناسنامه براي کاربران سايت', 0),
(25, 'forum', 100, 0, 0, 31536000, 0, 'انجمن گفتگو', '', 0),
(26, 'backup', 100, 1217904790, 1217906099, 31536000, 3129, 'پشتيبان', 'پشتيبان گرفتن از پايگاه‌داده سايت و برگداندن آن', 0),
(27, 'student', 100, 0, 0, 0, 7419, 'دانش‌آموز', '', 0),
(28, 'schoolMutualHour', 100, 0, 0, 0, 0, 'ساعت توافق مدارس', '', 0),
(29, 'download', 200, 1215153316, 1234407143, 31536000, 8490, 'دانلود', 'ارسال و دريافت پرونده براي قسمت‌ توسعه‌هاي ديگر', 0);
