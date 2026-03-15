-- Suppression de la page de contact et de ses traductions (ON DELETE CASCADE)
DROP TABLE IF EXISTS `mc_contact_page_content`;
DROP TABLE IF EXISTS `mc_contact_page`;

-- Suppression des destinataires et de leurs traductions (ON DELETE CASCADE)
DROP TABLE IF EXISTS `mc_contact_content`;
DROP TABLE IF EXISTS `mc_contact`;