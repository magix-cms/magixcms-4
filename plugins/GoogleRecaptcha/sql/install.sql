CREATE TABLE IF NOT EXISTS `mc_googlerecaptcha` (
    `id_recaptcha` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `site_key` varchar(255) DEFAULT NULL,
    `secret_key` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id_recaptcha`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- On insère la ligne par défaut (ID = 1) pour pouvoir faire des UPDATE ensuite
INSERT IGNORE INTO `mc_googlerecaptcha` (`id_recaptcha`, `site_key`, `secret_key`) VALUES (1, '', '');