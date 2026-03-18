--
-- Table structure for table `mc_about`
--

CREATE TABLE IF NOT EXISTS `mc_about` (
    `id_about` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_parent` int UNSIGNED DEFAULT NULL,
    `menu_about` smallint UNSIGNED DEFAULT '1',
    `order_about` smallint UNSIGNED NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_about`),
    KEY `fk_about_parent` (`id_parent`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_about_content`
--

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
    `published_about` smallint NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_about_lang` (`id_about`,`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_about_img`
--

CREATE TABLE IF NOT EXISTS `mc_about_img` (
    `id_img` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_about` int UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` smallint NOT NULL DEFAULT '0',
    `order_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_img`),
    KEY `fk_img_about` (`id_about`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mc_about_img_content`
--

CREATE TABLE IF NOT EXISTS `mc_about_img_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_img_lang` (`id_img`,`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_about_op`
--

CREATE TABLE IF NOT EXISTS `mc_about_op` (
    `id_day` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `day_abbr` varchar(2) NOT NULL,
    `open_day` smallint UNSIGNED DEFAULT '0',
    `noon_time` smallint UNSIGNED DEFAULT '0',
    `open_time` varchar(5) DEFAULT NULL,
    `close_time` varchar(5) DEFAULT NULL,
    `noon_start` varchar(5) DEFAULT NULL,
    `noon_end` varchar(5) DEFAULT NULL,
    PRIMARY KEY (`id_day`)
    ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mc_about_op`
--

INSERT INTO `mc_about_op` (`id_day`, `day_abbr`, `open_day`, `noon_time`, `open_time`, `close_time`, `noon_start`, `noon_end`) VALUES
(1, 'Mo', 1, 1, NULL, NULL, NULL, NULL),
(2, 'Tu', 0, 0, NULL, NULL, NULL, NULL),
(3, 'We', 0, 0, NULL, NULL, NULL, NULL),
(4, 'Th', 0, 0, NULL, NULL, NULL, NULL),
(5, 'Fr', 0, 0, NULL, NULL, NULL, NULL),
(6, 'Sa', 0, 0, NULL, NULL, NULL, NULL),
(7, 'Su', 0, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mc_about_op_content`
--

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

-- --------------------------------------------------------


--
-- Table structure for table `mc_module`
--

CREATE TABLE IF NOT EXISTS `mc_module` (
    `id_module` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`id_module`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mc_module`
--

INSERT INTO `mc_module` (`id_module`, `name`) VALUES
(NULL, 'dashboard'),
(NULL, 'employee'),
(NULL, 'role'),
(NULL, 'lang'),
(NULL, 'country'),
(NULL, 'domain'),
(NULL, 'setting'),
(NULL, 'homepage'),
(NULL, 'pages'),
(NULL, 'newstag'),
(NULL, 'about'),
(NULL, 'news'),
(NULL, 'mailsetting'),
(NULL, 'category'),
(NULL, 'catalog'),
(NULL, 'product'),
(NULL, 'theme'),
(NULL, 'plugin'),
(NULL, 'translate'),
(NULL, 'logo'),
(NULL, 'snippet'),
(NULL, 'company'),
(NULL, 'menu'),
(NULL, 'layout'),
(NULL, 'share'),
(NULL, 'imageconfig'),
(NULL, 'holder'),
(NULL, 'translation');

-- --------------------------------------------------------

--
-- Table structure for table `mc_admin_access`
--

CREATE TABLE IF NOT EXISTS `mc_admin_access` (
    `id_access` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_role` smallint UNSIGNED NOT NULL,
    `id_module` int UNSIGNED NOT NULL,
    `view` smallint UNSIGNED NOT NULL DEFAULT '0',
    `append` smallint UNSIGNED NOT NULL DEFAULT '0',
    `edit` smallint UNSIGNED NOT NULL DEFAULT '0',
    `del` smallint UNSIGNED NOT NULL DEFAULT '0',
    `action` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_access`),
    KEY `fk_admin_access_role` (`id_role`),
    KEY `fk_admin_access_module` (`id_module`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mc_admin_access`
--

INSERT INTO `mc_admin_access` (`id_access`, `id_role`, `id_module`, `view`, `append`, `edit`, `del`, `action`) VALUES
(NULL, 1, 1, 1, 1, 1, 1, 1),
(NULL, 1, 2, 1, 1, 1, 1, 1),
(NULL, 1, 3, 1, 1, 1, 1, 1),
(NULL, 1, 4, 1, 1, 1, 1, 1),
(NULL, 1, 5, 1, 1, 1, 1, 1),
(NULL, 1, 6, 1, 1, 1, 1, 1),
(NULL, 1, 7, 1, 1, 1, 1, 1),
(NULL, 1, 8, 1, 1, 1, 1, 1),
(NULL, 1, 9, 1, 1, 1, 1, 1),
(NULL, 1, 10, 1, 1, 1, 1, 1),
(NULL, 1, 11, 1, 1, 1, 1, 1),
(NULL, 1, 12, 1, 1, 1, 1, 1),
(NULL, 1, 13, 1, 1, 1, 1, 1),
(NULL, 1, 14, 1, 1, 1, 1, 1),
(NULL, 1, 15, 1, 1, 1, 1, 1),
(NULL, 1, 16, 1, 1, 1, 1, 1),
(NULL, 1, 17, 1, 1, 1, 1, 1),
(NULL, 1, 18, 1, 1, 1, 1, 1),
(NULL, 1, 19, 1, 1, 1, 1, 1),
(NULL, 1, 20, 1, 1, 1, 1, 1),
(NULL, 1, 21, 1, 1, 1, 1, 1),
(NULL, 1, 22, 1, 1, 1, 1, 1),
(NULL, 1, 23, 1, 1, 1, 1, 1),
(NULL, 1, 24, 1, 1, 1, 1, 1),
(NULL, 1, 25, 1, 1, 1, 1, 1),
(NULL, 1, 26, 1, 1, 1, 1, 1),
(NULL, 1, 27, 1, 1, 1, 1, 1),
(NULL, 1, 28, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mc_admin_access_rel`
--

CREATE TABLE IF NOT EXISTS `mc_admin_access_rel` (
    `id_access_rel` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_admin` smallint UNSIGNED NOT NULL,
    `id_role` smallint UNSIGNED NOT NULL,
    PRIMARY KEY (`id_access_rel`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mc_admin_access_rel`
--

INSERT INTO `mc_admin_access_rel` (`id_access_rel`, `id_admin`, `id_role`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mc_admin_dashboard`
--

CREATE TABLE IF NOT EXISTS `mc_admin_dashboard` (
    `id_admin` int NOT NULL,
    `widget_name` varchar(50) NOT NULL,
    `position` int NOT NULL,
    PRIMARY KEY (`id_admin`,`widget_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------

--
-- Table structure for table `mc_admin_employee`
--

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
    `active_admin` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_admin`),
    UNIQUE KEY `idx_email_admin` (`email_admin`)


-- --------------------------------------------------------

--
-- Table structure for table `mc_admin_role_user`
--

CREATE TABLE IF NOT EXISTS `mc_admin_role_user` (
    `id_role` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_name` varchar(50) NOT NULL,
    PRIMARY KEY (`id_role`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mc_admin_role_user`
--

INSERT INTO `mc_admin_role_user` (`id_role`, `role_name`) VALUES
    (1, 'administrator');

-- --------------------------------------------------------

--
-- Table structure for table `mc_admin_session`
--

CREATE TABLE IF NOT EXISTS `mc_admin_session` (
    `id_admin_session` varchar(150) NOT NULL,
    `id_admin` smallint UNSIGNED NOT NULL,
    `keyuniqid_admin` varchar(50) NOT NULL,
    `ip_session` varchar(25) NOT NULL,
    `browser_admin` varchar(50) NOT NULL,
    `last_modified_session` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id_admin_session`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


--
-- Table structure for table `mc_catalog`
--

CREATE TABLE IF NOT EXISTS `mc_catalog` (
    `id_catalog` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` int UNSIGNED NOT NULL,
    `id_cat` int UNSIGNED NOT NULL,
    `default_c` smallint UNSIGNED NOT NULL DEFAULT '0',
    `order_p` int UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_catalog`),
    KEY `id_product` (`id_product`,`id_cat`),
    KEY `id_cat` (`id_cat`),
    KEY `idx_catalog_product_cat` (`id_product`,`id_cat`),
    KEY `idx_catalog_cat` (`id_cat`,`default_c`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_cat`
--

CREATE TABLE IF NOT EXISTS `mc_catalog_cat` (
    `id_cat` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_parent` int UNSIGNED DEFAULT NULL,
    `menu_cat` smallint UNSIGNED DEFAULT '1',
    `order_cat` smallint UNSIGNED NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_cat`),
    KEY `id_parent` (`id_parent`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_cat_content`
--

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
    `published_cat` smallint NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_cat_lang` (`id_cat`,`id_lang`),
    KEY `id_cat` (`id_cat`),
    KEY `id_lang` (`id_lang`),
    KEY `url_cat` (`url_cat`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_cat_img`
--

CREATE TABLE IF NOT EXISTS `mc_catalog_cat_img` (
    `id_img` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cat` int UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` smallint NOT NULL DEFAULT '0',
    `order_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_img`),
    KEY `id_cat` (`id_cat`),
    KEY `idx_cat_default` (`id_cat`,`default_img`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_cat_img_content`
--

CREATE TABLE IF NOT EXISTS `mc_catalog_cat_img_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_img_lang` (`id_img`,`id_lang`),
    KEY `id_img` (`id_img`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_home`
--

CREATE TABLE IF NOT EXISTS `mc_catalog_home` (
    `id_catalog_home` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_catalog_home`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_home_content`
--

CREATE TABLE IF NOT EXISTS `mc_catalog_home_content` (
    `id_content` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_catalog_home` smallint UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `title_page` varchar(150) NOT NULL,
    `content_page` text,
    `seo_title_page` varchar(180) DEFAULT NULL,
    `seo_desc_page` text,
    `published` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    KEY `id_catalog_home` (`id_catalog_home`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_product`
--

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
    ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_product_content`
--

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
    `published_p` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    KEY `id_product` (`id_product`),
    KEY `idx_product_content_product_lang` (`id_product`,`id_lang`),
    KEY `idx_url_p` (`url_p`)
    ) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_product_img`
--

CREATE TABLE IF NOT EXISTS `mc_catalog_product_img` (
    `id_img` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` int UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` smallint NOT NULL DEFAULT '0',
    `order_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_img`),
    KEY `id_product` (`id_product`),
    KEY `idx_product_img_default` (`id_product`,`default_img`)
    ) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_product_img_content`
--

CREATE TABLE IF NOT EXISTS `mc_catalog_product_img_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    KEY `id_img` (`id_img`,`id_lang`),
    KEY `id_lang` (`id_lang`),
    KEY `idx_product_img_content_lang` (`id_lang`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_catalog_product_rel`
--

CREATE TABLE IF NOT EXISTS `mc_catalog_product_rel` (
    `id_rel` int NOT NULL AUTO_INCREMENT,
    `id_product` int UNSIGNED NOT NULL,
    `id_product_2` int UNSIGNED NOT NULL,
    PRIMARY KEY (`id_rel`),
    KEY `idx_product_source` (`id_product`),
    KEY `idx_product_target` (`id_product_2`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mc_cms_page`
--

CREATE TABLE IF NOT EXISTS `mc_cms_page` (
    `id_pages` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_parent` int UNSIGNED DEFAULT NULL,
    `menu_pages` smallint UNSIGNED DEFAULT '1',
    `order_pages` smallint UNSIGNED NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_pages`),
    KEY `id_parent` (`id_parent`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_cms_page_content`
--

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
    `published_pages` smallint NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    KEY `id_pages` (`id_pages`),
    KEY `idx_cms_page_content_lang` (`id_pages`,`id_lang`),
    KEY `idx_url_pages` (`url_pages`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_cms_page_img`
--

CREATE TABLE IF NOT EXISTS `mc_cms_page_img` (
    `id_img` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_pages` int UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` smallint NOT NULL DEFAULT '0',
    `order_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_img`),
    KEY `id_pages` (`id_pages`),
    KEY `idx_cms_page_img_default` (`id_pages`,`default_img`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_cms_page_img_content`
--

CREATE TABLE IF NOT EXISTS `mc_cms_page_img_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    KEY `id_img` (`id_img`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_company_info`
--

CREATE TABLE IF NOT EXISTS `mc_company_info` (
    `id_info` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_info` varchar(30) NOT NULL,
    `value_info` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id_info`),
    KEY `name_info` (`name_info`)
    ) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mc_company_info`
--

INSERT INTO `mc_company_info` (`id_info`, `name_info`, `value_info`) VALUES
(1, 'name', NULL),
(2, 'type', 'org'),
(3, 'eshop', '0'),
(4, 'tva', NULL),
(5, 'adress', NULL),
(6, 'street', NULL),
(7, 'postcode', NULL),
(8, 'city', NULL),
(9, 'mail', NULL),
(10, 'click_to_mail', '0'),
(11, 'crypt_mail', '0'),
(12, 'phone', NULL),
(13, 'mobile', NULL),
(14, 'click_to_call', '0'),
(15, 'fax', NULL),
(16, 'languages', 'French'),
(17, 'facebook', NULL),
(18, 'twitter', NULL),
(19, 'instagram', NULL),
(20, 'linkedin', NULL),
(21, 'youtube', NULL),
(22, 'github', NULL),
(23, 'openinghours', '0');

-- --------------------------------------------------------

--
-- Table structure for table `mc_config`
--

CREATE TABLE IF NOT EXISTS `mc_config` (
    `idconfig` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `attr_name` varchar(20) NOT NULL,
    `status` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`idconfig`)
    ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mc_config`
--

INSERT INTO `mc_config` (`idconfig`, `attr_name`, `status`) VALUES
(1, 'pages', 1),
(2, 'news', 1),
(3, 'catalog', 1),
(4, 'about', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mc_config_img`
--

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

--
-- Dumping data for table `mc_config_img`
--

INSERT INTO `mc_config_img` (`id_config_img`, `module_img`, `attribute_img`, `width_img`, `height_img`, `type_img`, `prefix_img`, `resize_img`) VALUES
(NULL, 'pages', 'pages', '340', '210', 'small', 's', 'adaptive'),
(NULL, 'pages', 'pages', '680', '420', 'medium', 'm', 'adaptive'),
(NULL, 'pages', 'pages', '1200', '1200', 'large', 'l', 'basic'),
(NULL, 'about', 'about', '340', '210', 'small', 's', 'adaptive'),
(NULL, 'about', 'about', '680', '420', 'medium', 'm', 'adaptive'),
(NULL, 'about', 'about', '1200', '1200', 'large', 'l', 'basic'),
(NULL, 'news', 'news', '340', '210', 'small', 's', 'adaptive'),
(NULL, 'news', 'news', '680', '420', 'medium', 'm', 'adaptive'),
(NULL, 'news', 'news', '1200', '1200', 'large', 'l', 'basic'),
(NULL, 'catalog', 'category', '340', '210', 'small', 's', 'adaptive'),
(NULL, 'catalog', 'category', '680', '420', 'medium', 'm', 'adaptive'),
(NULL, 'catalog', 'category', '1200', '1200', 'large', 'l', 'basic'),
(NULL, 'catalog', 'product', '340', '210', 'small', 's', 'adaptive'),
(NULL, 'catalog', 'product', '680', '420', 'medium', 'm', 'adaptive'),
(NULL, 'catalog', 'product', '1200', '1200', 'large', 'l', 'basic'),
(NULL, 'logo', 'logo', '229', '50', 'small', 's', 'adaptive'),
(NULL, 'logo', 'logo', '480', '105', 'medium', 'm', 'adaptive'),
(NULL, 'logo', 'logo', '500', '121', 'large', 'l', 'adaptive');


-- --------------------------------------------------------


--
-- Table structure for table `mc_country`
--

CREATE TABLE IF NOT EXISTS `mc_country` (
    `id_country` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `iso_country` varchar(5) NOT NULL,
    `name_country` varchar(125) NOT NULL,
    `order_country` int UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_country`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_domain`
--

CREATE TABLE IF NOT EXISTS `mc_domain` (
    `id_domain` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `url_domain` varchar(175) NOT NULL,
    `tracking_domain` text,
    `default_domain` smallint UNSIGNED NOT NULL DEFAULT '0',
    `canonical_domain` smallint NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_domain`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_domain_language`
--

CREATE TABLE IF NOT EXISTS `mc_domain_language` (
    `id_domain_lg` int NOT NULL AUTO_INCREMENT,
    `id_domain` smallint UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `default_lang` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_domain_lg`),
    KEY `id_lang` (`id_lang`),
    KEY `id_domain` (`id_domain`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------


--
-- Table structure for table `mc_home_page`
--

CREATE TABLE IF NOT EXISTS `mc_home_page` (
  `id_page` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_page`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_home_page_content`
--

CREATE TABLE IF NOT EXISTS `mc_home_page_content` (
  `id_content` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_page` smallint UNSIGNED NOT NULL,
  `id_lang` smallint UNSIGNED NOT NULL,
  `title_page` varchar(150) NOT NULL,
  `content_page` text,
  `seo_title_page` varchar(180) DEFAULT NULL,
  `seo_desc_page` text,
  `published` smallint UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_content`),
  KEY `fk_home_content_page` (`id_page`),
  KEY `fk_home_content_lang` (`id_lang`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_hook`
--

CREATE TABLE IF NOT EXISTS `mc_hook` (
    `id_hook` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `title` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci,
    PRIMARY KEY (`id_hook`),
    UNIQUE KEY `idx_hook_name` (`name`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mc_hook`
--

INSERT INTO `mc_hook` (`id_hook`, `name`, `title`, `description`) VALUES
(NULL, 'displayHomeTop', 'Haut de page (Accueil)', 'Zone située juste sous le slider ou le header'),
(NULL, 'displayHomeBottom', 'Bas de page (Accueil)', 'Zone pour les produits phares ou réassurance'),
(NULL, 'displayFooter', 'Pied de page', 'Zone pour les widgets du footer'),
(NULL, 'displayLeftColumn', 'Colonne de gauche', 'Utilisée sur les pages catégories ou CMS'),
(NULL, 'displayFooterBottom', 'Bas du pied de page', 'Zone pleine largeur sous les colonnes du footer (idéal pour les liens légaux ou le copyright).');

-- --------------------------------------------------------

--
-- Table structure for table `mc_hook_item`
--

CREATE TABLE IF NOT EXISTS `mc_hook_item` (
    `id_item` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_hook` int UNSIGNED NOT NULL,
    `module_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `position` int UNSIGNED NOT NULL DEFAULT '0',
    `active` tinyint(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`id_item`),
    KEY `idx_hook` (`id_hook`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mc_lang`
--

CREATE TABLE IF NOT EXISTS `mc_lang` (
    `id_lang` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `iso_lang` varchar(10) NOT NULL,
    `name_lang` varchar(40) DEFAULT NULL,
    `default_lang` smallint UNSIGNED NOT NULL DEFAULT '0',
    `active_lang` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_lang`),
    UNIQUE KEY `idx_iso_lang` (`iso_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mc_lang`
--

INSERT INTO `mc_lang` (`id_lang`, `iso_lang`, `name_lang`, `default_lang`, `active_lang`) VALUES
(NULL, 'fr', 'French', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `mc_logo`
--

CREATE TABLE IF NOT EXISTS `mc_logo` (
    `id_logo` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `img_logo` varchar(125) DEFAULT NULL,
    `active_logo` smallint NOT NULL DEFAULT '0',
    `active_footer` smallint NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_logo`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_logo_content`
--

CREATE TABLE IF NOT EXISTS `mc_logo_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_logo` smallint UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL DEFAULT '1',
    `alt_logo` varchar(70) DEFAULT NULL,
    `title_logo` varchar(70) DEFAULT NULL,
    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_content`),
    KEY `id_logo` (`id_logo`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_menu`
--

CREATE TABLE IF NOT EXISTS `mc_menu` (
    `id_link` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_parent` int UNSIGNED DEFAULT NULL,
    `type_link` enum('home','pages','about','about_page','catalog','category','news','plugin','external') NOT NULL,
    `id_page` int UNSIGNED DEFAULT NULL,
    `mode_link` enum('simple','dropdown','mega') NOT NULL DEFAULT 'simple',
    `order_link` int UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_link`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_menu_content`
--

CREATE TABLE IF NOT EXISTS `mc_menu_content` (
    `id_link_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_link` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `name_link` varchar(50) DEFAULT NULL,
    `title_link` varchar(180) DEFAULT NULL,
    `url_link` varchar(250) DEFAULT NULL,
    PRIMARY KEY (`id_link_content`),
    KEY `id_link` (`id_link`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_news`
--

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


-- --------------------------------------------------------

--
-- Table structure for table `mc_news_content`
--

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
    `published_news` smallint UNSIGNED DEFAULT '0',
    PRIMARY KEY (`id_content`),
    KEY `id_news` (`id_news`),
    KEY `idx_news_content_lang` (`id_news`,`id_lang`),
    KEY `idx_url_news` (`url_news`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_news_img`
--

CREATE TABLE IF NOT EXISTS `mc_news_img` (
    `id_img` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_news` int UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    `order_img` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_img`),
    KEY `id_news` (`id_news`),
    KEY `idx_news_img_default` (`id_news`,`default_img`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_news_img_content`
--

CREATE TABLE IF NOT EXISTS `mc_news_img_content` (
    `id_content` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int UNSIGNED NOT NULL,
    `id_lang` smallint UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    KEY `id_img` (`id_img`,`id_lang`),
    KEY `id_lang` (`id_lang`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_news_tag`
--

CREATE TABLE IF NOT EXISTS `mc_news_tag` (
    `id_tag` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_lang` smallint UNSIGNED NOT NULL,
    `name_tag` varchar(50) NOT NULL,
    PRIMARY KEY (`id_tag`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_news_tag_rel`
--

CREATE TABLE IF NOT EXISTS `mc_news_tag_rel` (
    `id_rel` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_news` int UNSIGNED NOT NULL,
    `id_tag` int UNSIGNED NOT NULL,
    PRIMARY KEY (`id_rel`),
    KEY `id_tag` (`id_tag`),
    KEY `id_news` (`id_news`),
    KEY `idx_news_tag_rel_lookup` (`id_news`,`id_tag`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_plugins`
--

CREATE TABLE IF NOT EXISTS `mc_plugins` (
    `id_plugins` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(200) NOT NULL,
    `version` varchar(10) NOT NULL,
    `home` smallint UNSIGNED NOT NULL DEFAULT '0',
    `about` smallint UNSIGNED NOT NULL DEFAULT '0',
    `pages` smallint UNSIGNED NOT NULL DEFAULT '0',
    `news` smallint UNSIGNED NOT NULL DEFAULT '0',
    `catalog` smallint UNSIGNED NOT NULL DEFAULT '0',
    `category` smallint UNSIGNED NOT NULL DEFAULT '0',
    `product` smallint UNSIGNED NOT NULL DEFAULT '0',
    `seo` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_plugins`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `mc_plugins_module`
--

CREATE TABLE IF NOT EXISTS `mc_plugins_module` (
    `id_module` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `plugin_name` varchar(200) NOT NULL,
    `module_name` varchar(200) NOT NULL,
    `active` smallint NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_module`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------


--
-- Table structure for table `mc_revisions_editor`
--

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


-- --------------------------------------------------------


--
-- Table structure for table `mc_setting`
--

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

--
-- Dumping data for table `mc_setting`
--


INSERT INTO `mc_setting` (`id_setting`, `name`, `value`, `type`, `label`, `category`) VALUES
(NULL, 'theme', 'default', 'string', 'site theme', 'theme'),
(NULL, 'analytics', NULL, 'string', 'google analytics', 'google'),
(NULL, 'magix_version', '4.0.0', 'string', 'Version Magix CMS', 'release'),
(NULL, 'vat_rate', '21', 'float', 'VAT Rate', 'catalog'),
(NULL, 'price_display', 'tinc', 'string', 'Price display with or without tax included', 'catalog'),
(NULL, 'product_per_page', 12, 'int', 'Number of product per page in the pages of the catalog', 'catalog'),
(NULL, 'product_catalog', '0', 'int', 'Product in catalog root', 'catalog'),
(NULL, 'news_per_page', 12, 'int', 'Number of news per page in the news pages', 'news'),
(NULL, 'mail_sender', NULL, 'string', 'Mail sender', 'mail'),
(NULL, 'smtp_enabled', '0', 'int', 'Smtp enabled', 'mail'),
(NULL, 'set_host', NULL, 'string', 'Set host', 'mail'),
(NULL, 'set_port', NULL, 'string', 'Set port', 'mail'),
(NULL, 'set_encryption', NULL, 'string', 'Set encryption', 'mail'),
(NULL, 'set_username', NULL, 'string', 'Set username', 'mail'),
(NULL, 'set_password', NULL, 'string', 'Set password', 'mail'),
(NULL, 'content_css', NULL, 'string', 'css from skin for tinyMCE', 'advanced'),
(NULL, 'concat', '0', 'int', 'concat URL', 'advanced'),
(NULL, 'cache', 'none', 'string', 'Cache template', 'advanced'),
(NULL, 'robots', 'noindex,nofollow', 'string', 'metas robots', 'advanced'),
(NULL, 'mode', 'dev', 'string', 'Environment types', 'advanced'),
(NULL, 'ssl', '0', 'int', 'SSL protocol', 'advanced'),
(NULL, 'http2', '0', 'int', 'HTTP2 protocol', 'advanced'),
(NULL, 'service_worker', '0', 'int', 'Service Worker', 'advanced'),
(NULL, 'amp', '0', 'int', 'amp', 'advanced'),
(NULL, 'maintenance', '0', 'int', 'Mode maintenance', 'advanced'),
(NULL, 'holder_bgcolor', '#ffffff', 'string', 'color bg replacement image', 'advanced'),
(NULL, 'logo_percent', '50', 'int', 'Logo size percentage', 'advanced'),
(NULL, 'geminiai', '0', 'int', 'Gemini AI', 'advanced');
-- --------------------------------------------------------

--
-- Table structure for table `mc_share_network`
--

CREATE TABLE IF NOT EXISTS `mc_share_network` (
    `id_share` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `url_share` varchar(400) NOT NULL,
    `icon` varchar(50) NOT NULL,
    `is_active` tinyint UNSIGNED NOT NULL DEFAULT '1',
    `order_share` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_share`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mc_share_network`
--

INSERT INTO `mc_share_network` (`id_share`, `name`, `url_share`, `icon`, `is_active`, `order_share`) VALUES
(1, 'facebook', 'https://www.facebook.com/sharer/sharer.php?u=%URL%', 'bi-facebook', 1, 1),
(2, 'twitter', 'https://twitter.com/intent/tweet?text=%NAME%&url=%URL%', 'bi-twitter-x', 1, 2),
(3, 'linkedin', 'https://www.linkedin.com/sharing/share-offsite/?url=%URL%', 'bi-linkedin', 1, 3),
(4, 'whatsapp', 'https://api.whatsapp.com/send?text=%NAME%%20%URL%', 'bi-whatsapp', 1, 4),
(5, 'pinterest', 'https://pinterest.com/pin/create/button/?url=%URL%&description=%NAME%', 'bi-pinterest', 0, 5);

-- --------------------------------------------------------

--
-- Table structure for table `mc_snippet`
--

CREATE TABLE IF NOT EXISTS `mc_snippet` (
    `id_snippet` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `title_sp` varchar(30) DEFAULT NULL,
    `description_sp` varchar(30) DEFAULT NULL,
    `content_sp` text,
    `order_sp` smallint UNSIGNED NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_snippet`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `mc_snippet`
--

INSERT INTO `mc_snippet` (`id_snippet`, `title_sp`, `description_sp`, `content_sp`, `order_sp`, `date_register`) VALUES
(NULL, 'Texte 2 colonnes', NULL, '<div class=\"row\">\r\n<div class=\"col-12 col-xs-6\">\r\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Architecto aspernatur at atque commodi dolor dolores est eveniet laudantium libero magni, mollitia nemo nisi pariatur recusandae suscipit. Dolorem reprehenderit veniam voluptatem.</p>\r\n</div>\r\n<div class=\"col-12 col-xs-6\">\r\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae dicta dolorum excepturi exercitationem fugit inventore itaque provident quae quidem! Cumque dignissimos mollitia placeat, quam quis repellat tempora ullam velit vero!</p>\r\n</div>\r\n</div>\r\n<p> </p>', 1, '2023-04-05 12:54:39'),
(NULL, 'Texte 3 colonnes', NULL, '<div class=\"row\">\r\n<div class=\"col-12 col-sm-4\">\r\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Architecto aspernatur at atque commodi dolor dolores est eveniet laudantium libero magni, mollitia nemo nisi pariatur recusandae suscipit. Dolorem reprehenderit veniam voluptatem.</p>\r\n</div>\r\n<div class=\"col-12 col-sm-4\">\r\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae dicta dolorum excepturi exercitationem fugit inventore itaque provident quae quidem! Cumque dignissimos mollitia placeat, quam quis repellat tempora ullam velit vero!</p>\r\n</div>\r\n<div class=\"col-12 col-sm-4\">\r\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Beatae dicta dolorum excepturi exercitationem fugit inventore itaque provident quae quidem! Cumque dignissimos mollitia placeat, quam quis repellat tempora ullam velit vero!</p>\r\n</div>\r\n</div>\r\n<p> </p>', 2, '2023-04-05 12:55:20'),
(NULL, 'Texte et image', NULL, '<div class=\"display\">\r\n<div class=\"container\">\r\n<div class=\"row row-reversed\">\r\n<div class=\"col-12 col-sm-6\"><img class=\"img-responsive\" src=\"http://via.placeholder.com/802x535\" alt=\"Placeholder\" width=\"802\" height=\"535\" /></div>\r\n<div class=\"col-12 col-sm-6\">\r\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci, amet cum deleniti deserunt doloremque inventore ipsa libero maiores, nam rem sequi soluta sunt? Ad commodi, deserunt doloribus illum reiciendis sapiente.</p>\r\n<p> </p>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<p> </p>', 3, '2023-04-05 12:55:44'),
(NULL, 'Image et texte', NULL, '<div class=\"display\">\r\n<div class=\"container\">\r\n<div class=\"row\">\r\n<div class=\"col-12 col-sm-6\"><img class=\"img-responsive\" src=\"http://via.placeholder.com/802x535\" alt=\"Placeholder\" width=\"802\" height=\"535\" /></div>\r\n<div class=\"col-12 col-sm-6\">\r\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci, amet cum deleniti deserunt doloremque inventore ipsa libero maiores, nam rem sequi soluta sunt? Ad commodi, deserunt doloribus illum reiciendis sapiente.</p>\r\n<p> </p>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<p> </p>', 4, '2023-04-05 12:56:00'),
(NULL, 'Texte et vidéo', NULL, '<div class=\"display\">\r\n<div class=\"container\">\r\n<div class=\"row row-reversed\">\r\n<div class=\"col-12 col-sm-6\">\r\n<div class=\"embed-responsive embed-responsive-16by9\"><iframe src=\"https://www.youtube.com/embed/kBgsZ-iTGHs?rel=0&amp;hd=1\" width=\"\" height=\"\" class=\"embed-responsive-item\"> </iframe></div>\r\n</div>\r\n<div class=\"col-12 col-sm-6\">\r\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci, amet cum deleniti deserunt doloremque inventore ipsa libero maiores, nam rem sequi soluta sunt? Ad commodi, deserunt doloribus illum reiciendis sapiente.</p>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<p> </p>', 5, '2023-04-05 12:56:42'),
(NULL, 'Vidéo et texte', NULL, '<div class=\"display\">\r\n<div class=\"container\">\r\n<div class=\"row\">\r\n<div class=\"col-12 col-sm-6\">\r\n<div class=\"embed-responsive embed-responsive-16by9\"><iframe src=\"https://www.youtube.com/embed/kBgsZ-iTGHs?rel=0&amp;hd=1\" width=\"\" height=\"\" class=\"embed-responsive-item\"> </iframe></div>\r\n</div>\r\n<div class=\"col-12 col-sm-6\">\r\n<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci, amet cum deleniti deserunt doloremque inventore ipsa libero maiores, nam rem sequi soluta sunt? Ad commodi, deserunt doloribus illum reiciendis sapiente.</p>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<p> </p>', 6, '2023-04-05 12:57:11'),
(NULL, 'Galerie d\'image manuelle', NULL, '<div class=\"cms-img-gallery\">\r\n<div class=\"col-12 col-xs-6 col-sm-4 col-xl-3\"><a class=\"img-gallery\" title=\"\" href=\"/skin/default/img/snippet/working.jpg\"> <img class=\"img-responsive\" src=\"/skin/default/img/snippet/working.jpg\" alt=\"\" width=\"640\" height=\"426\" /> </a></div>\r\n<div class=\"col-12 col-xs-6 col-sm-4 col-xl-3\"><a class=\"img-gallery\" title=\"\" href=\"/skin/default/img/snippet/working.jpg\"> <img class=\"img-responsive\" src=\"/skin/default/img/snippet/working.jpg\" alt=\"\" width=\"640\" height=\"426\" /> </a></div>\r\n<div class=\"col-12 col-xs-6 col-sm-4 col-xl-3\"><a class=\"img-gallery\" title=\"\" href=\"/skin/default/img/snippet/working.jpg\"> <img class=\"img-responsive\" src=\"/skin/default/img/snippet/working.jpg\" alt=\"\" width=\"640\" height=\"426\" /> </a></div>\r\n<div class=\"col-12 col-xs-6 col-sm-4 col-xl-3\"><a class=\"img-gallery\" title=\"\" href=\"/skin/default/img/snippet/working.jpg\"> <img class=\"img-responsive\" src=\"/skin/default/img/snippet/working.jpg\" alt=\"\" width=\"640\" height=\"426\" /> </a></div>\r\n<div class=\"col-12 col-xs-6 col-sm-4 col-xl-3\"><a class=\"img-gallery\" title=\"\" href=\"/skin/default/img/snippet/working.jpg\"> <img class=\"img-responsive\" src=\"/skin/default/img/snippet/working.jpg\" alt=\"\" width=\"640\" height=\"426\" /> </a></div>\r\n<div class=\"col-12 col-xs-6 col-sm-4 col-xl-3\"><a class=\"img-gallery\" title=\"\" href=\"/skin/default/img/snippet/working.jpg\"> <img class=\"img-responsive\" src=\"/skin/default/img/snippet/working.jpg\" alt=\"\" width=\"640\" height=\"426\" /> </a></div>\r\n<div class=\"col-12 col-xs-6 col-sm-4 col-xl-3\"><a class=\"img-gallery\" title=\"\" href=\"/skin/default/img/snippet/working.jpg\"> <img class=\"img-responsive\" src=\"/skin/default/img/snippet/working.jpg\" alt=\"\" width=\"640\" height=\"426\" /> </a></div>\r\n<div class=\"col-12 col-xs-6 col-sm-4 col-xl-3\"><a class=\"img-gallery\" title=\"\" href=\"/skin/default/img/snippet/working.jpg\"> <img class=\"img-responsive\" src=\"/skin/default/img/snippet/working.jpg\" alt=\"\" width=\"640\" height=\"426\" /> </a></div>\r\n<p> </p>\r\n</div>', 7, '2023-04-05 13:00:02'),
(NULL, 'Image de galerie manuelle', NULL, '<div class=\"col-12 col-xs-6 col-sm-4 col-xl-3\"><a class=\"img-gallery\" title=\"\" href=\"/skin/default/img/snippet/working.jpg\"> <img class=\"img-responsive\" src=\"/skin/default/img/snippet/working.jpg\" alt=\"\" width=\"640\" height=\"426\" /> </a></div>\r\n<p> </p>', 8, '2023-04-05 13:00:26');

-- --------------------------------------------------------

--
-- Table structure for table `mc_webservice`
--

CREATE TABLE IF NOT EXISTS `mc_webservice` (
    `id_ws` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
    `key_ws` varchar(125) DEFAULT NULL,
    `status_ws` smallint UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_ws`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


--
-- Constraints for dumped tables
--

--
-- Constraints for table `mc_about`
--
ALTER TABLE `mc_about`
    ADD CONSTRAINT `fk_about_parent` FOREIGN KEY (`id_parent`) REFERENCES `mc_about` (`id_about`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `mc_about_content`
--
ALTER TABLE `mc_about_content`
    ADD CONSTRAINT `fk_content_about` FOREIGN KEY (`id_about`) REFERENCES `mc_about` (`id_about`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_about_img`
--
ALTER TABLE `mc_about_img`
    ADD CONSTRAINT `fk_img_about` FOREIGN KEY (`id_about`) REFERENCES `mc_about` (`id_about`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_about_img_content`
--
ALTER TABLE `mc_about_img_content`
    ADD CONSTRAINT `fk_img_content_id` FOREIGN KEY (`id_img`) REFERENCES `mc_about_img` (`id_img`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_about_op_content`
--
ALTER TABLE `mc_about_op_content`
    ADD CONSTRAINT `mc_about_op_content_ibfk_1` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_admin_access`
--
ALTER TABLE `mc_admin_access`
    ADD CONSTRAINT `fk_admin_access_module` FOREIGN KEY (`id_module`) REFERENCES `mc_module` (`id_module`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_admin_access_role` FOREIGN KEY (`id_role`) REFERENCES `mc_admin_role_user` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_catalog`
--
ALTER TABLE `mc_catalog`
    ADD CONSTRAINT `fk_mc_catalog_cat_new` FOREIGN KEY (`id_cat`) REFERENCES `mc_catalog_cat` (`id_cat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mc_catalog_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `mc_catalog_product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_catalog_product_content`
--
ALTER TABLE `mc_catalog_product_content`
    ADD CONSTRAINT `mc_catalog_product_content_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `mc_catalog_product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_catalog_product_img`
--
ALTER TABLE `mc_catalog_product_img`
    ADD CONSTRAINT `mc_catalog_product_img_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `mc_catalog_product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_catalog_product_img_content`
--
ALTER TABLE `mc_catalog_product_img_content`
    ADD CONSTRAINT `mc_catalog_product_img_content_ibfk_1` FOREIGN KEY (`id_img`) REFERENCES `mc_catalog_product_img` (`id_img`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mc_catalog_product_img_content_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_catalog_product_rel`
--
ALTER TABLE `mc_catalog_product_rel`
    ADD CONSTRAINT `fk_rel_prod_1` FOREIGN KEY (`id_product`) REFERENCES `mc_catalog_product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rel_prod_2` FOREIGN KEY (`id_product_2`) REFERENCES `mc_catalog_product` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_cms_page`
--
ALTER TABLE `mc_cms_page`
    ADD CONSTRAINT `mc_cms_page_ibfk_1` FOREIGN KEY (`id_parent`) REFERENCES `mc_cms_page` (`id_pages`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `mc_cms_page_content`
--
ALTER TABLE `mc_cms_page_content`
    ADD CONSTRAINT `mc_cms_page_content_ibfk_1` FOREIGN KEY (`id_pages`) REFERENCES `mc_cms_page` (`id_pages`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_cms_page_img`
--
ALTER TABLE `mc_cms_page_img`
    ADD CONSTRAINT `mc_cms_page_img_ibfk_1` FOREIGN KEY (`id_pages`) REFERENCES `mc_cms_page` (`id_pages`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_cms_page_img_content`
--
ALTER TABLE `mc_cms_page_img_content`
    ADD CONSTRAINT `mc_cms_page_img_content_ibfk_1` FOREIGN KEY (`id_img`) REFERENCES `mc_cms_page_img` (`id_img`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mc_cms_page_img_content_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_contact_content`
--
ALTER TABLE `mc_contact_content`
    ADD CONSTRAINT `fk_contact_content_contact` FOREIGN KEY (`id_contact`) REFERENCES `mc_contact` (`id_contact`) ON DELETE CASCADE;

--
-- Constraints for table `mc_contact_page_content`
--
ALTER TABLE `mc_contact_page_content`
    ADD CONSTRAINT `fk_contact_page_content_page` FOREIGN KEY (`id_page`) REFERENCES `mc_contact_page` (`id_page`) ON DELETE CASCADE;

--
-- Constraints for table `mc_domain_language`
--
ALTER TABLE `mc_domain_language`
    ADD CONSTRAINT `mc_domain_language_ibfk_1` FOREIGN KEY (`id_domain`) REFERENCES `mc_domain` (`id_domain`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mc_domain_language_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_home_page_content`
--
ALTER TABLE `mc_home_page_content`
    ADD CONSTRAINT `fk_home_content_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_home_content_page` FOREIGN KEY (`id_page`) REFERENCES `mc_home_page` (`id_page`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_hook_item`
--
ALTER TABLE `mc_hook_item`
    ADD CONSTRAINT `fk_mc_hook_item` FOREIGN KEY (`id_hook`) REFERENCES `mc_hook` (`id_hook`) ON DELETE CASCADE;

--
-- Constraints for table `mc_logo_content`
--
ALTER TABLE `mc_logo_content`
    ADD CONSTRAINT `mc_logo_content_ibfk_1` FOREIGN KEY (`id_logo`) REFERENCES `mc_logo` (`id_logo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_menu_content`
--
ALTER TABLE `mc_menu_content`
    ADD CONSTRAINT `mc_menu_content_ibfk_1` FOREIGN KEY (`id_link`) REFERENCES `mc_menu` (`id_link`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mc_menu_content_ibfk_2` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_news_content`
--
ALTER TABLE `mc_news_content`
    ADD CONSTRAINT `mc_news_content_ibfk_1` FOREIGN KEY (`id_news`) REFERENCES `mc_news` (`id_news`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_news_img`
--
ALTER TABLE `mc_news_img`
    ADD CONSTRAINT `mc_news_img_ibfk_1` FOREIGN KEY (`id_news`) REFERENCES `mc_news` (`id_news`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_news_img_content`
--
ALTER TABLE `mc_news_img_content`
    ADD CONSTRAINT `mc_news_img_content_ibfk_1` FOREIGN KEY (`id_img`) REFERENCES `mc_news_img` (`id_img`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_news_tag_rel`
--
ALTER TABLE `mc_news_tag_rel`
    ADD CONSTRAINT `mc_news_tag_rel_ibfk_1` FOREIGN KEY (`id_tag`) REFERENCES `mc_news_tag` (`id_tag`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mc_news_tag_rel_ibfk_2` FOREIGN KEY (`id_news`) REFERENCES `mc_news` (`id_news`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_revisions_editor`
--
ALTER TABLE `mc_revisions_editor`
    ADD CONSTRAINT `fk_revisions_lang` FOREIGN KEY (`id_lang`) REFERENCES `mc_lang` (`id_lang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mc_seo_content`
--
ALTER TABLE `mc_seo_content`
    ADD CONSTRAINT `mc_seo_content_ibfk_1` FOREIGN KEY (`id_seo`) REFERENCES `mc_seo` (`id_seo`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
