-- 1. Structure principale
CREATE TABLE IF NOT EXISTS `mc_about` (
    `id_about` int(7) unsigned NOT NULL AUTO_INCREMENT,
    `id_parent` int(7) unsigned DEFAULT NULL,
    `menu_about` smallint(1) unsigned DEFAULT '1',
    `order_about` smallint(5) unsigned NOT NULL DEFAULT '0',
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_about`),
    -- Contrainte : si le parent est supprimé, on met l'enfant à NULL (règle du root)
    CONSTRAINT `fk_about_parent` FOREIGN KEY (`id_parent`)
    REFERENCES `mc_about` (`id_about`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2. Contenu multilingue
CREATE TABLE IF NOT EXISTS `mc_about_content` (
    `id_content` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `id_about` int(7) unsigned NOT NULL,
    `id_lang` smallint(3) unsigned NOT NULL,
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
    `published_about` smallint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_about_lang` (`id_about`, `id_lang`),
    -- Contrainte : si l'entité About est supprimée, les traductions disparaissent aussi
    CONSTRAINT `fk_content_about` FOREIGN KEY (`id_about`)
    REFERENCES `mc_about` (`id_about`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 3. Gestion des images
CREATE TABLE IF NOT EXISTS `mc_about_img` (
    `id_img` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_about` int(11) UNSIGNED NOT NULL,
    `name_img` varchar(150) NOT NULL,
    `default_img` smallint(1) NOT NULL DEFAULT 0,
    `order_img` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_img`),
    -- Contrainte : suppression des images si About est supprimé
    CONSTRAINT `fk_img_about` FOREIGN KEY (`id_about`)
    REFERENCES `mc_about` (`id_about`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 4. Contenu des images
CREATE TABLE IF NOT EXISTS `mc_about_img_content` (
    `id_content` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_img` int(11) UNSIGNED NOT NULL,
    `id_lang` smallint(3) UNSIGNED NOT NULL,
    `alt_img` varchar(70) DEFAULT NULL,
    `title_img` varchar(70) DEFAULT NULL,
    `caption_img` varchar(125) DEFAULT NULL,
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_img_lang` (`id_img`, `id_lang`),
    -- Contrainte : suppression des métas si l'image est supprimée
    CONSTRAINT `fk_img_content_id` FOREIGN KEY (`id_img`)
    REFERENCES `mc_about_img` (`id_img`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `mc_config_img`
(`module_img`, `attribute_img`, `width_img`, `height_img`, `type_img`, `prefix_img`, `resize_img`)
VALUES
    ('about', 'about', 300, 200, 'small', 's', 'adaptive'),
    ('about', 'about', 800, 600, 'medium', 'm', 'basic'),
    ('about', 'about', 1200, 900, 'large', 'l', 'basic');

-- Si la table existe déjà, on la renomme simplement :
RENAME TABLE `mc_about` TO `mc_company_info`;

-- OU, si vous devez la créer à neuf :
CREATE TABLE IF NOT EXISTS `mc_company_info` (
    `id_info` smallint(2) unsigned NOT NULL AUTO_INCREMENT,
    `name_info` varchar(30) NOT NULL,
    `value_info` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id_info`),
    KEY `name_info` (`name_info`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insertion des données par défaut
INSERT INTO `mc_company_info` (`id_info`, `name_info`, `value_info`) VALUES
(NULL, 'name', NULL),
(NULL, 'type', 'org'),
(NULL, 'eshop', '0'),
(NULL, 'tva', NULL),
(NULL, 'adress', NULL),
(NULL, 'street', NULL),
(NULL, 'postcode', NULL),
(NULL, 'city', NULL),
(NULL, 'mail', NULL),
(NULL, 'click_to_mail', '0'),
(NULL, 'crypt_mail', '1'),
(NULL, 'phone', NULL),
(NULL, 'mobile', NULL),
(NULL, 'click_to_call', '1'),
(NULL, 'fax', NULL),
(NULL, 'languages', 'French'),
(NULL, 'facebook', NULL),
(NULL, 'twitter', NULL),
(NULL, 'instagram', NULL),
(NULL, 'linkedin', NULL),
(NULL, 'youtube', NULL),
(NULL, 'github', NULL),
(NULL, 'openinghours', '0');