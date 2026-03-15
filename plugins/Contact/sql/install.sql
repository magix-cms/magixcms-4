-- ========================================================
-- 1. TABLE DES DESTINATAIRES (Les emails)
-- ========================================================
CREATE TABLE IF NOT EXISTS `mc_contact` (
    `id_contact` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `mail_contact` varchar(255) NOT NULL,
    `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0', -- Pratique si vous avez plusieurs services
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_contact`),
    KEY `idx_mail_contact` (`mail_contact`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. TABLE DU CONTENU DES DESTINATAIRES (Dispo par langue + Libellé)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mc_contact_content` (
    `id_content` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_contact` int(10) unsigned NOT NULL,
    `id_lang` smallint(3) unsigned NOT NULL,
    `name_contact` varchar(150) DEFAULT NULL, -- Ex: "Service Commercial", "Support", etc.
    `published_contact` tinyint(1) unsigned NOT NULL DEFAULT '0', -- 1 = reçoit les mails dans cette langue
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_contact_lang` (`id_contact`, `id_lang`),
    CONSTRAINT `fk_contact_content_contact` FOREIGN KEY (`id_contact`) REFERENCES `mc_contact` (`id_contact`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- 3. TABLE DE LA PAGE DE CONTACT (Structure)
-- ========================================================
CREATE TABLE IF NOT EXISTS `mc_contact_page` (
    `id_page` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_page`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. TABLE DU CONTENU DE LA PAGE (Multilingue & SEO)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mc_contact_page_content` (
    `id_content` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_page` int(10) unsigned NOT NULL,
    `id_lang` smallint(3) unsigned NOT NULL,
    `name_page` varchar(255) DEFAULT NULL, -- Titre H1
    `resume_page` text,                    -- Petite intro avant le formulaire
    `content_page` longtext,               -- Contenu WYSIWYG additionnel (ex: infos de la société)
    `seo_title_page` varchar(255) DEFAULT NULL,
    `seo_desc_page` varchar(255) DEFAULT NULL,
    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `published_page` tinyint(1) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id_content`),
    UNIQUE KEY `idx_page_lang` (`id_page`, `id_lang`),
    CONSTRAINT `fk_contact_page_content_page` FOREIGN KEY (`id_page`) REFERENCES `mc_contact_page` (`id_page`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- 5. INSERTION DE LA PAGE PAR DÉFAUT
-- ========================================================
-- On insère immédiatement l'ID 1 pour s'assurer que la page existe dès l'installation du plugin.
INSERT IGNORE INTO `mc_contact_page` (`id_page`) VALUES (1);