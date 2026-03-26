/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- STRUCTURE ET DONNÉES DES TABLES
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `mc_about` (
    `id_about` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_parent` int UNSIGNED DEFAULT NULL,
    `menu_about` tinyint(1) UNSIGNED DEFAULT '1',
    `order_about` smallint UNSIGNED NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_about`),
    KEY `fk_about_parent` (`id_parent`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_about_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_about` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `name_about` varchar(150) DEFAULT NULL,
    `longname_about` varchar(150) DEFAULT NULL,
    `url_about` varchar(150) DEFAULT NULL,
    `resume_about` text,
    `content_about` text,
    `link_label_about` varchar(125) DEFAULT NULL,
    `link_title_about` varchar(125) DEFAULT NULL,
    `seo_title_about` varchar(180) DEFAULT NULL,
    `seo_desc_about` text,
    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `published_about` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_about_lang` (`id_about`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_about_img` (
    `id_img` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_about` int UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `order_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_img`),
    KEY `fk_img_about` (`id_about`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_about_img_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_img_lang` (`id_img`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_about_op` (
    `id_day` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `day_abbr` varchar(2) NOT NULL,
    `open_day` tinyint(1) UNSIGNED DEFAULT '0',
    `noon_time` tinyint(1) UNSIGNED DEFAULT '0',
    `open_time` varchar(5) DEFAULT NULL,
    `close_time` varchar(5) DEFAULT NULL,
    `noon_start` varchar(5) DEFAULT NULL,
    `noon_end` varchar(5) DEFAULT NULL,
    PRIMARY KEY (`id_day`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `mc_about_op` (`id_day`, `day_abbr`, `open_day`, `noon_time`, `open_time`, `close_time`, `noon_start`, `noon_end`) VALUES
(1, 'Mo', 1, 1, NULL, NULL, NULL, NULL),
(2, 'Tu', 0, 0, NULL, NULL, NULL, NULL),
(3, 'We', 0, 0, NULL, NULL, NULL, NULL),
(4, 'Th', 0, 0, NULL, NULL, NULL, NULL),
(5, 'Fr', 0, 0, NULL, NULL, NULL, NULL),
(6, 'Sa', 0, 0, NULL, NULL, NULL, NULL),
(7, 'Su', 0, 0, NULL, NULL, NULL, NULL);

CREATE TABLE IF NOT EXISTS `mc_about_op_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_lang` smallint UNSIGNED NOT NULL,
    `text_mo` text,
    `text_tu` text,
    `text_we` text,
    `text_th` text,
    `text_fr` text,
    `text_sa` text,
    `text_su` text,
    PRIMARY KEY (`id_content`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_module` (
    `id_module` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`id_module`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `mc_module` (`id_module`, `name`) VALUES
(1, 'dashboard'),(2, 'employee'),(3, 'role'),(4, 'lang'),(5, 'country'),
(6, 'domain'),(7, 'setting'),(8, 'homepage'),(9, 'pages'),(10, 'newstag'),
(11, 'about'),(12, 'news'),(13, 'mailsetting'),(14, 'category'),(15, 'catalog'),
(16, 'product'),(17, 'theme'),(18, 'plugin'),(19, 'translate'),(20, 'logo'),
(21, 'snippet'),(22, 'company'),(23, 'menu'),(24, 'layout'),(25, 'share'),
(26, 'imageconfig'),(27, 'holder'),(28, 'translation'),(29, 'revisions');

CREATE TABLE IF NOT EXISTS `mc_admin_access` (
    `id_access` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_role` smallint UNSIGNED NOT NULL,
    `id_module` int UNSIGNED NOT NULL,
    `view` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `append` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `edit` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `del` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `action` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_access`),
    KEY `fk_admin_access_role` (`id_role`),
    KEY `fk_admin_access_module` (`id_module`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `mc_admin_access` (`id_role`, `id_module`, `view`, `append`, `edit`, `del`, `action`) VALUES
(1, 1, 1, 1, 1, 1, 1), (1, 2, 1, 1, 1, 1, 1), (1, 3, 1, 1, 1, 1, 1),
(1, 4, 1, 1, 1, 1, 1), (1, 5, 1, 1, 1, 1, 1), (1, 6, 1, 1, 1, 1, 1),
(1, 7, 1, 1, 1, 1, 1), (1, 8, 1, 1, 1, 1, 1), (1, 9, 1, 1, 1, 1, 1),
(1, 10, 1, 1, 1, 1, 1), (1, 11, 1, 1, 1, 1, 1), (1, 12, 1, 1, 1, 1, 1),
(1, 13, 1, 1, 1, 1, 1), (1, 14, 1, 1, 1, 1, 1), (1, 15, 1, 1, 1, 1, 1),
(1, 16, 1, 1, 1, 1, 1), (1, 17, 1, 1, 1, 1, 1), (1, 18, 1, 1, 1, 1, 1),
(1, 19, 1, 1, 1, 1, 1), (1, 20, 1, 1, 1, 1, 1), (1, 21, 1, 1, 1, 1, 1),
(1, 22, 1, 1, 1, 1, 1), (1, 23, 1, 1, 1, 1, 1), (1, 24, 1, 1, 1, 1, 1),
(1, 25, 1, 1, 1, 1, 1), (1, 26, 1, 1, 1, 1, 1), (1, 27, 1, 1, 1, 1, 1),
(1, 28, 1, 1, 1, 1, 1),(1, 29, 1, 1, 1, 1, 1);

CREATE TABLE IF NOT EXISTS `mc_admin_role_user` (
    `id_role` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_name` varchar(50) NOT NULL,
    PRIMARY KEY (`id_role`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `mc_admin_role_user` (`id_role`, `role_name`) VALUES (1, 'administrator');

CREATE TABLE IF NOT EXISTS `mc_admin_employee` (
    `id_admin` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `keyuniqid_admin` varchar(50) NOT NULL,
    `title_admin` enum('m','w') NOT NULL DEFAULT 'm',
    `lastname_admin` varchar(50) DEFAULT NULL,
    `firstname_admin` varchar(50) DEFAULT NULL,
    `pseudo_admin` varchar(50) DEFAULT NULL,
    `email_admin` varchar(150) NOT NULL,
    `phone_admin` varchar(150) DEFAULT NULL,
    `address_admin` varchar(200) DEFAULT NULL,
    `postcode_admin` varchar(8) DEFAULT NULL,
    `city_admin` varchar(100) DEFAULT NULL,
    `country_admin` varchar(120) DEFAULT NULL,
    `passwd_admin` varchar(80) NOT NULL,
    `last_change_admin` timestamp NULL DEFAULT NULL,
    `change_passwd` varchar(32) DEFAULT NULL,
    `active_admin` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_admin`),
    UNIQUE KEY `idx_email_admin` (`email_admin`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `mc_admin_access_rel` (
    `id_access_rel` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_admin` smallint UNSIGNED NOT NULL,
    `id_role` smallint UNSIGNED NOT NULL,
    PRIMARY KEY (`id_access_rel`),
    KEY `id_admin` (`id_admin`),
    KEY `id_role` (`id_role`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `mc_admin_dashboard` (
    `id_admin` smallint UNSIGNED NOT NULL,
    `widget_name` varchar(50) NOT NULL,
    `position` int NOT NULL,
    PRIMARY KEY (`id_admin`,`widget_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_admin_session` (
    `id_admin_session` varchar(150) NOT NULL,
    `id_admin` smallint UNSIGNED NOT NULL,
    `keyuniqid_admin` varchar(50) NOT NULL,
    `ip_session` varchar(25) NOT NULL,
    `browser_admin` varchar(50) NOT NULL,
    `last_modified_session` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id_admin_session`),
    KEY `id_admin` (`id_admin`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog` (
    `id_catalog` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` int UNSIGNED NOT NULL,
    `id_cat` int UNSIGNED NOT NULL,
    `default_c` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `order_p` int UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_catalog`),
    UNIQUE KEY `idx_catalog_product_cat` (`id_product`,`id_cat`),
    KEY `id_cat` (`id_cat`),
    KEY `idx_catalog_cat` (`id_cat`,`default_c`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog_cat` (
    `id_cat` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_parent` int UNSIGNED DEFAULT NULL,
    `menu_cat` tinyint(1) UNSIGNED DEFAULT '1',
    `order_cat` smallint UNSIGNED NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_cat`),
    KEY `id_parent` (`id_parent`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog_cat_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cat` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL DEFAULT '1',
    `name_cat` varchar(150) DEFAULT NULL,
    `longname_cat` varchar(150) DEFAULT NULL,
    `url_cat` varchar(150) DEFAULT NULL,
    `resume_cat` text,
    `content_cat` text,
    `link_label_cat` varchar(125) DEFAULT NULL,
    `link_title_cat` varchar(125) DEFAULT NULL,
    `seo_title_cat` varchar(180) DEFAULT NULL,
    `seo_desc_cat` text,
    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `published_cat` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_cat_lang` (`id_cat`,`id_lang`),
    KEY `id_lang` (`id_lang`),
    KEY `url_cat` (`url_cat`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `mc_catalog_cat_img` (
    `id_img` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cat` int UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `order_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_img`),
    KEY `id_cat` (`id_cat`),
    KEY `idx_cat_default` (`id_cat`,`default_img`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog_cat_img_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_img_lang` (`id_img`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog_home` (
    `id_catalog_home` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_catalog_home`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog_home_content` (
    `id_content` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_catalog_home` smallint UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `title_page` varchar(150) NOT NULL,
    `content_page` text,
    `seo_title_page` varchar(180) DEFAULT NULL,
    `seo_desc_page` text,
    `published` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_home_lang` (`id_catalog_home`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog_product` (
    `id_product` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `price_p` decimal(20,2) NOT NULL DEFAULT '0.00',
    `price_promo_p` decimal(20,2) NOT NULL DEFAULT '0.00',
    `reference_p` varchar(32) DEFAULT NULL,
    `ean_p` varchar(20) DEFAULT NULL,
    `width_p` decimal(10,2) NOT NULL DEFAULT '0.00',
    `height_p` decimal(10,2) NOT NULL DEFAULT '0.00',
    `depth_p` decimal(10,2) NOT NULL DEFAULT '0.00',
    `weight_p` decimal(10,2) NOT NULL DEFAULT '0.00',
    `availability_p` varchar(30) DEFAULT 'InStock',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_product`),
    KEY `idx_reference_p` (`reference_p`),
    KEY `idx_availability_p` (`availability_p`),
    KEY `ean_p` (`ean_p`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog_product_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL DEFAULT '1',
    `name_p` varchar(125) DEFAULT NULL,
    `longname_p` varchar(125) DEFAULT NULL,
    `url_p` varchar(125) DEFAULT NULL,
    `resume_p` text,
    `content_p` text,
    `link_label_p` varchar(125) DEFAULT NULL,
    `link_title_p` varchar(125) DEFAULT NULL,
    `seo_title_p` varchar(180) DEFAULT NULL,
    `seo_desc_p` text,
    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `published_p` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_product_lang` (`id_product`,`id_lang`),
    KEY `id_lang` (`id_lang`),
    KEY `idx_url_p` (`url_p`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog_product_img` (
    `id_img` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` int UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `order_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_img`),
    KEY `id_product` (`id_product`),
    KEY `idx_product_img_default` (`id_product`,`default_img`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog_product_img_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_img_lang` (`id_img`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_catalog_product_rel` (
    `id_rel` int NOT NULL AUTO_INCREMENT,
    `id_product` int UNSIGNED NOT NULL,
    `id_product_2` int UNSIGNED NOT NULL,
    PRIMARY KEY (`id_rel`),
    KEY `idx_product_source` (`id_product`),
    KEY `idx_product_target` (`id_product_2`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_cms_page` (
    `id_pages` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_parent` int UNSIGNED DEFAULT NULL,
    `menu_pages` tinyint(1) UNSIGNED DEFAULT '1',
    `order_pages` smallint UNSIGNED NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_pages`),
    KEY `id_parent` (`id_parent`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_cms_page_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_pages` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL DEFAULT '1',
    `name_pages` varchar(150) DEFAULT NULL,
    `longname_pages` varchar(150) DEFAULT NULL,
    `url_pages` varchar(150) DEFAULT NULL,
    `resume_pages` text,
    `content_pages` text,
    `link_label_pages` varchar(125) DEFAULT NULL,
    `link_title_pages` varchar(125) DEFAULT NULL,
    `seo_title_pages` varchar(180) DEFAULT NULL,
    `seo_desc_pages` text,
    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `published_pages` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_cms_page_lang` (`id_pages`,`id_lang`),
    KEY `id_lang` (`id_lang`),
    KEY `idx_url_pages` (`url_pages`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_cms_page_img` (
    `id_img` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_pages` int UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `order_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_img`),
    KEY `id_pages` (`id_pages`),
    KEY `idx_cms_page_img_default` (`id_pages`,`default_img`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_cms_page_img_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_img_lang` (`id_img`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_company_info` (
    `id_info` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_info` varchar(30) NOT NULL,
    `value_info` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id_info`),
    UNIQUE KEY `name_info` (`name_info`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `mc_company_info` (`id_info`, `name_info`, `value_info`) VALUES
(1, 'name', NULL), (2, 'type', 'org'), (3, 'eshop', '0'),
(4, 'tva', NULL), (5, 'adress', NULL), (6, 'street', NULL),
(7, 'postcode', NULL), (8, 'city', NULL), (9, 'mail', NULL),
(10, 'click_to_mail', '0'), (11, 'crypt_mail', '0'), (12, 'phone', NULL),
(13, 'mobile', NULL), (14, 'click_to_call', '0'), (15, 'fax', NULL),
(16, 'languages', 'French'), (17, 'facebook', NULL), (18, 'twitter', NULL),
(19, 'instagram', NULL), (20, 'linkedin', NULL), (21, 'youtube', NULL),
(22, 'github', NULL), (23, 'openinghours', '0');

CREATE TABLE IF NOT EXISTS `mc_config` (
    `idconfig` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `attr_name` varchar(20) NOT NULL,
    `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`idconfig`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `mc_config` (`idconfig`, `attr_name`, `status`) VALUES
(1, 'pages', 1), (2, 'news', 1), (3, 'catalog', 1), (4, 'about', 1);

CREATE TABLE IF NOT EXISTS `mc_config_img` (
    `id_config_img` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_img` varchar(40) NOT NULL,
    `attribute_img` varchar(40) NOT NULL,
    `width_img` decimal(4,0) NOT NULL,
    `height_img` decimal(4,0) NOT NULL,
    `type_img` varchar(80) NOT NULL,
    `prefix_img` varchar(50) NOT NULL,
    `resize_img` enum('basic','adaptive') NOT NULL,
    PRIMARY KEY (`id_config_img`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `mc_config_img` (`module_img`, `attribute_img`, `width_img`, `height_img`, `type_img`, `prefix_img`, `resize_img`) VALUES
('pages', 'pages', '340', '210', 'small', 's', 'adaptive'),
('pages', 'pages', '680', '420', 'medium', 'm', 'adaptive'),
('pages', 'pages', '1200', '1200', 'large', 'l', 'basic'),
('about', 'about', '340', '210', 'small', 's', 'adaptive'),
('about', 'about', '680', '420', 'medium', 'm', 'adaptive'),
('about', 'about', '1200', '1200', 'large', 'l', 'basic'),
('news', 'news', '340', '210', 'small', 's', 'adaptive'),
('news', 'news', '680', '420', 'medium', 'm', 'adaptive'),
('news', 'news', '1200', '1200', 'large', 'l', 'basic'),
('catalog', 'category', '340', '210', 'small', 's', 'adaptive'),
('catalog', 'category', '680', '420', 'medium', 'm', 'adaptive'),
('catalog', 'category', '1200', '1200', 'large', 'l', 'basic'),
('catalog', 'product', '340', '210', 'small', 's', 'adaptive'),
('catalog', 'product', '680', '420', 'medium', 'm', 'adaptive'),
('catalog', 'product', '1200', '1200', 'large', 'l', 'basic'),
('logo', 'logo', '229', '50', 'small', 's', 'adaptive'),
('logo', 'logo', '480', '105', 'medium', 'm', 'adaptive'),
('logo', 'logo', '500', '121', 'large', 'l', 'adaptive');

CREATE TABLE IF NOT EXISTS `mc_country` (
    `id_country` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `iso_country` varchar(5) NOT NULL,
    `name_country` varchar(125) NOT NULL,
    `order_country` int UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_country`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_domain` (
    `id_domain` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `url_domain` varchar(175) NOT NULL,
    `tracking_domain` text,
    `default_domain` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `canonical_domain` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_domain`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_domain_language` (
    `id_domain_lg` int NOT NULL AUTO_INCREMENT,
    `id_domain` smallint UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `default_lang` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_domain_lg`),
    UNIQUE KEY `idx_domain_lang` (`id_domain`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_home_page` (
    `id_page` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_page`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_home_page_content` (
    `id_content` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_page` smallint UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `title_page` varchar(150) NOT NULL,
    `content_page` text,
    `seo_title_page` varchar(180) DEFAULT NULL,
    `seo_desc_page` text,
    `published` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_home_lang` (`id_page`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_hook` (
    `id_hook` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `title` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci,
    PRIMARY KEY (`id_hook`),
    UNIQUE KEY `idx_hook_name` (`name`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mc_hook` (`id_hook`, `name`, `title`, `description`) VALUES
(1, 'displayHomeTop', 'Haut de page (Accueil)', 'Zone située juste sous le slider ou le header'),
(2, 'displayHomeBottom', 'Bas de page (Accueil)', 'Zone pour les produits phares ou réassurance'),
(3, 'displayFooter', 'Pied de page', 'Zone pour les widgets du footer'),
(4, 'displayLeftColumn', 'Colonne de gauche', 'Utilisée sur les pages catégories ou CMS'),
(5, 'displayFooterBottom', 'Bas du pied de page', 'Zone pleine largeur sous les colonnes du footer (idéal pour les liens légaux ou le copyright).');

CREATE TABLE IF NOT EXISTS `mc_hook_item` (
    `id_item` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_hook` int UNSIGNED NOT NULL,
    `module_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `position` int UNSIGNED NOT NULL DEFAULT '0',
    `active` tinyint(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`id_item`),
    KEY `idx_hook` (`id_hook`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mc_lang` (
    `id_lang` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `iso_lang` varchar(10) NOT NULL,
    `name_lang` varchar(40) DEFAULT NULL,
    `default_lang` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `active_lang` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_lang`),
    UNIQUE KEY `idx_iso_lang` (`iso_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `mc_lang` (`id_lang`, `iso_lang`, `name_lang`, `default_lang`, `active_lang`) VALUES
    (1, 'fr', 'French', 1, 1);

CREATE TABLE IF NOT EXISTS `mc_logo` (
    `id_logo` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `img_logo` varchar(125) DEFAULT NULL,
    `active_logo` tinyint(1) NOT NULL DEFAULT '0',
    `active_footer` tinyint(1) NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_logo`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_logo_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_logo` smallint UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL DEFAULT '1',
    `alt_logo` varchar(70) DEFAULT NULL,
    `title_logo` varchar(70) DEFAULT NULL,
    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_logo_lang` (`id_logo`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_menu` (
    `id_link` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_parent` int UNSIGNED DEFAULT NULL,
    `type_link` enum('home','pages','about','about_page','catalog','category','news','plugin','external') NOT NULL,
    `id_page` int UNSIGNED DEFAULT NULL,
    `mode_link` enum('simple','dropdown','mega') NOT NULL DEFAULT 'simple',
    `order_link` int UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_link`),
    KEY `id_parent` (`id_parent`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_menu_content` (
    `id_link_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_link` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `name_link` varchar(50) DEFAULT NULL,
    `title_link` varchar(180) DEFAULT NULL,
    `url_link` varchar(250) DEFAULT NULL,
    PRIMARY KEY (`id_link_content`),
    UNIQUE KEY `idx_menu_lang` (`id_link`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_news` (
    `id_news` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `date_publish` timestamp NULL DEFAULT NULL,
    `date_event_start` timestamp NULL DEFAULT NULL,
    `date_event_end` timestamp NULL DEFAULT NULL,
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_news`),
    KEY `idx_news_dates` (`date_publish`,`date_event_start`,`date_event_end`),
    KEY `idx_date_publish` (`date_publish`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_news_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_news` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `name_news` varchar(150) DEFAULT NULL,
    `longname_news` varchar(150) DEFAULT NULL,
    `url_news` varchar(150) DEFAULT NULL,
    `resume_news` text,
    `content_news` text,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    `link_label_news` varchar(125) DEFAULT NULL,
    `link_title_news` varchar(125) DEFAULT NULL,
    `seo_title_news` varchar(180) DEFAULT NULL,
    `seo_desc_news` text,
    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `published_news` tinyint(1) UNSIGNED DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_news_lang` (`id_news`,`id_lang`),
    KEY `id_lang` (`id_lang`),
    KEY `idx_url_news` (`url_news`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_news_img` (
    `id_img` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_news` int UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `order_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_img`),
    KEY `id_news` (`id_news`),
    KEY `idx_news_img_default` (`id_news`,`default_img`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_news_img_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_img_lang` (`id_img`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_news_tag` (
    `id_tag` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_lang` smallint UNSIGNED NOT NULL,
    `name_tag` varchar(50) NOT NULL,
    PRIMARY KEY (`id_tag`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_news_tag_rel` (
    `id_rel` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_news` int UNSIGNED NOT NULL,
    `id_tag` int UNSIGNED NOT NULL,
    PRIMARY KEY (`id_rel`),
    UNIQUE KEY `idx_news_tag_rel_lookup` (`id_news`,`id_tag`),
    KEY `id_tag` (`id_tag`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_plugins` (
    `id_plugins` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(200) NOT NULL,
    `version` varchar(10) NOT NULL,
    `has_config` tinyint(1) NOT NULL DEFAULT '1', -- 🟢 LA LIGNE À NE PAS OUBLIER
    `home` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `about` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `pages` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `news` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `catalog` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `category` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `product` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    `seo` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_plugins`),
    UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_plugins_module` (
    `id_module` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `plugin_name` varchar(200) NOT NULL,
    `module_name` varchar(200) NOT NULL,
    `active` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_module`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_revisions_editor` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `item_id` int UNSIGNED NOT NULL,
    `item_type` varchar(50) NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `editor_id` varchar(50) NOT NULL,
    `content` longtext NOT NULL,
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lookup` (`item_type`,`item_id`,`id_lang`,`editor_id`),
    KEY `idx_date_register` (`date_register`),
    KEY `fk_revisions_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mc_setting` (
    `id_setting` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `value` text,
    `type` varchar(8) NOT NULL DEFAULT 'string',
    `label` text,
    `category` varchar(20) NOT NULL,
    PRIMARY KEY (`id_setting`),
    UNIQUE KEY `idx_setting_name` (`name`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `mc_setting` (`name`, `value`, `type`, `label`, `category`) VALUES
('theme', 'default', 'string', 'site theme', 'theme'),
('analytics', NULL, 'string', 'google analytics', 'google'),
('magix_version', '4.0.0', 'string', 'Version Magix CMS', 'release'),
('vat_rate', '21', 'float', 'VAT Rate', 'catalog'),
('price_display', 'tinc', 'string', 'Price display with or without tax included', 'catalog'),
('product_per_page', '12', 'int', 'Number of product per page in the pages of the catalog', 'catalog'),
('product_catalog', '0', 'int', 'Product in catalog root', 'catalog'),
('news_per_page', '12', 'int', 'Number of news per page in the news pages', 'news'),
('mail_sender', NULL, 'string', 'Mail sender', 'mail'),
('smtp_enabled', '0', 'int', 'Smtp enabled', 'mail'),
('set_host', NULL, 'string', 'Set host', 'mail'),
('set_port', NULL, 'string', 'Set port', 'mail'),
('set_encryption', NULL, 'string', 'Set encryption', 'mail'),
('set_username', NULL, 'string', 'Set username', 'mail'),
('set_password', NULL, 'string', 'Set password', 'mail'),
('content_css', NULL, 'string', 'css from skin for tinyMCE', 'advanced'),
('concat', '0', 'int', 'concat URL', 'advanced'),
('cache', 'none', 'string', 'Cache template', 'advanced'),
('robots', 'noindex,nofollow', 'string', 'metas robots', 'advanced'),
('mode', 'dev', 'string', 'Environment types', 'advanced'),
('ssl', '0', 'int', 'SSL protocol', 'advanced'),
('http2', '0', 'int', 'HTTP2 protocol', 'advanced'),
('service_worker', '0', 'int', 'Service Worker', 'advanced'),
('amp', '0', 'int', 'amp', 'advanced'),
('maintenance', '0', 'int', 'Mode maintenance', 'advanced'),
('holder_bgcolor', '#ffffff', 'string', 'color bg replacement image', 'advanced'),
('logo_percent', '50', 'int', 'Logo size percentage', 'advanced'),
('geminiai', '0', 'int', 'Gemini AI', 'advanced');

CREATE TABLE IF NOT EXISTS `mc_share_network` (
    `id_share` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `url_share` varchar(400) NOT NULL,
    `icon` varchar(50) NOT NULL,
    `is_active` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
    `order_share` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_share`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `mc_share_network` (`id_share`, `name`, `url_share`, `icon`, `is_active`, `order_share`) VALUES
(1, 'facebook', 'https://www.facebook.com/sharer/sharer.php?u=%URL%', 'bi-facebook', 1, 1),
(2, 'twitter', 'https://twitter.com/intent/tweet?text=%NAME%&url=%URL%', 'bi-twitter-x', 1, 2),
(3, 'linkedin', 'https://www.linkedin.com/sharing/share-offsite/?url=%URL%', 'bi-linkedin', 1, 3),
(4, 'whatsapp', 'https://api.whatsapp.com/send?text=%NAME%%20%URL%', 'bi-whatsapp', 1, 4),
(5, 'pinterest', 'https://pinterest.com/pin/create/button/?url=%URL%&description=%NAME%', 'bi-pinterest', 0, 5);

CREATE TABLE IF NOT EXISTS `mc_snippet` (
    `id_snippet` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title_sp` varchar(150) NOT NULL,
    `description_sp` varchar(255) DEFAULT NULL,
    `content_sp` text NOT NULL,
    `order_sp` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_snippet`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `mc_webservice` (
    `id_ws` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `key_ws` varchar(125) DEFAULT NULL,
    `status_ws` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_ws`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------
-- CONTRAINTES DES TABLES (CLÉS ÉTRANGÈRES)
-- --------------------------------------------------------

-- mc_about
ALTER TABLE `mc_about`
    ADD CONSTRAINT `fk_about_parent` FOREIGN KEY (`id_parent`) REFERENCES `mc_about` (`id_about`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `mc_about_content`
    ADD CONSTRAINT `fk_content_about` FOREIGN KEY (`id_about`) REFERENCES `mc_about` (`id_about`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_content_about_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_about_img`
    ADD CONSTRAINT `fk_img_about` FOREIGN KEY (`id_about`) REFERENCES `mc_about` (`id_about`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_about_img_content`
    ADD CONSTRAINT `fk_img_content_id` FOREIGN KEY (`id_img`) REFERENCES `mc_about_img` (`id_img`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_img_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_about_op_content`
    ADD CONSTRAINT `mc_about_op_content_ibfk_1` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Administration
ALTER TABLE `mc_admin_access`
    ADD CONSTRAINT `fk_admin_access_module` FOREIGN KEY (`id_module`) REFERENCES `mc_module` (`id_module`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_admin_access_role` FOREIGN KEY (`id_role`) REFERENCES `mc_admin_role_user` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_admin_access_rel`
    ADD CONSTRAINT `fk_admin_access_rel_admin` FOREIGN KEY (`id_admin`) REFERENCES `mc_admin_employee` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_admin_access_rel_role` FOREIGN KEY (`id_role`) REFERENCES `mc_admin_role_user` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_admin_dashboard`
    ADD CONSTRAINT `fk_admin_dashboard_emp` FOREIGN KEY (`id_admin`) REFERENCES `mc_admin_employee` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_admin_session`
    ADD CONSTRAINT `fk_admin_session_emp` FOREIGN KEY (`id_admin`) REFERENCES `mc_admin_employee` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Catalog
ALTER TABLE `mc_catalog`
    ADD CONSTRAINT `fk_mc_catalog_cat_new` FOREIGN KEY (`id_cat`) REFERENCES `mc_catalog_cat` (`id_cat`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `mc_catalog_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `mc_catalog_product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_catalog_cat`
    ADD CONSTRAINT `fk_catalog_cat_parent` FOREIGN KEY (`id_parent`) REFERENCES `mc_catalog_cat` (`id_cat`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `mc_catalog_cat_content`
    ADD CONSTRAINT `fk_catalog_cat_content_cat` FOREIGN KEY (`id_cat`) REFERENCES `mc_catalog_cat` (`id_cat`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_catalog_cat_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_catalog_cat_img`
    ADD CONSTRAINT `fk_catalog_cat_img_cat` FOREIGN KEY (`id_cat`) REFERENCES `mc_catalog_cat` (`id_cat`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_catalog_cat_img_content`
    ADD CONSTRAINT `fk_catalog_cat_img_content_img` FOREIGN KEY (`id_img`) REFERENCES `mc_catalog_cat_img` (`id_img`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_catalog_cat_img_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_catalog_home_content`
    ADD CONSTRAINT `fk_catalog_home_content_home` FOREIGN KEY (`id_catalog_home`) REFERENCES `mc_catalog_home` (`id_catalog_home`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_catalog_home_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_catalog_product_content`
    ADD CONSTRAINT `mc_catalog_product_content_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `mc_catalog_product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_product_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_catalog_product_img`
    ADD CONSTRAINT `mc_catalog_product_img_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `mc_catalog_product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_catalog_product_img_content`
    ADD CONSTRAINT `mc_catalog_product_img_content_ibfk_1` FOREIGN KEY (`id_img`) REFERENCES `mc_catalog_product_img` (`id_img`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `mc_catalog_product_img_content_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_catalog_product_rel`
    ADD CONSTRAINT `fk_rel_prod_1` FOREIGN KEY (`id_product`) REFERENCES `mc_catalog_product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_rel_prod_2` FOREIGN KEY (`id_product_2`) REFERENCES `mc_catalog_product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE;

-- CMS Pages
ALTER TABLE `mc_cms_page`
    ADD CONSTRAINT `mc_cms_page_ibfk_1` FOREIGN KEY (`id_parent`) REFERENCES `mc_cms_page` (`id_pages`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `mc_cms_page_content`
    ADD CONSTRAINT `mc_cms_page_content_ibfk_1` FOREIGN KEY (`id_pages`) REFERENCES `mc_cms_page` (`id_pages`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_cms_page_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_cms_page_img`
    ADD CONSTRAINT `mc_cms_page_img_ibfk_1` FOREIGN KEY (`id_pages`) REFERENCES `mc_cms_page` (`id_pages`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_cms_page_img_content`
    ADD CONSTRAINT `mc_cms_page_img_content_ibfk_1` FOREIGN KEY (`id_img`) REFERENCES `mc_cms_page_img` (`id_img`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `mc_cms_page_img_content_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Domains
ALTER TABLE `mc_domain_language`
    ADD CONSTRAINT `mc_domain_language_ibfk_1` FOREIGN KEY (`id_domain`) REFERENCES `mc_domain` (`id_domain`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `mc_domain_language_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Home Page
ALTER TABLE `mc_home_page_content`
    ADD CONSTRAINT `fk_home_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_home_content_page` FOREIGN KEY (`id_page`) REFERENCES `mc_home_page` (`id_page`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Hooks
ALTER TABLE `mc_hook_item`
    ADD CONSTRAINT `fk_mc_hook_item` FOREIGN KEY (`id_hook`) REFERENCES `mc_hook` (`id_hook`) ON DELETE CASCADE;

-- Logo
ALTER TABLE `mc_logo_content`
    ADD CONSTRAINT `mc_logo_content_ibfk_1` FOREIGN KEY (`id_logo`) REFERENCES `mc_logo` (`id_logo`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_logo_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Menu
ALTER TABLE `mc_menu`
    ADD CONSTRAINT `fk_menu_parent` FOREIGN KEY (`id_parent`) REFERENCES `mc_menu` (`id_link`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `mc_menu_content`
    ADD CONSTRAINT `mc_menu_content_ibfk_1` FOREIGN KEY (`id_link`) REFERENCES `mc_menu` (`id_link`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `mc_menu_content_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

-- News
ALTER TABLE `mc_news_content`
    ADD CONSTRAINT `mc_news_content_ibfk_1` FOREIGN KEY (`id_news`) REFERENCES `mc_news` (`id_news`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_news_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_news_img`
    ADD CONSTRAINT `mc_news_img_ibfk_1` FOREIGN KEY (`id_news`) REFERENCES `mc_news` (`id_news`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_news_img_content`
    ADD CONSTRAINT `mc_news_img_content_ibfk_1` FOREIGN KEY (`id_img`) REFERENCES `mc_news_img` (`id_img`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_news_img_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_news_tag`
    ADD CONSTRAINT `fk_news_tag_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mc_news_tag_rel`
    ADD CONSTRAINT `mc_news_tag_rel_ibfk_1` FOREIGN KEY (`id_tag`) REFERENCES `mc_news_tag` (`id_tag`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `mc_news_tag_rel_ibfk_2` FOREIGN KEY (`id_news`) REFERENCES `mc_news` (`id_news`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Revisions
ALTER TABLE `mc_revisions_editor`
    ADD CONSTRAINT `fk_revisions_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;


SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;