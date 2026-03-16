<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class LogoDb extends BaseDb
{
    /**
     * Récupère tous les logos avec leur contenu multilingue
     */
    public function fetchAllLogos(int $idLang = 1): array
    {
        $qb = new QueryBuilder();
        $qb->select('l.id_logo, l.img_logo AS name_img, l.active_logo, l.active_footer, c.alt_logo, c.title_logo') // 🟢 AJOUT ICI
        ->from('mc_logo', 'l')
            ->leftJoin('mc_logo_content', 'c', 'l.id_logo = c.id_logo AND c.id_lang = ' . $idLang)
            ->orderBy('l.id_logo', 'DESC');

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Insère un nouveau logo et son contenu de base
     */
    /**
     * Insère un logo et boucle sur toutes les langues pour le contenu
     */
    public function insertLogo(string $filename, array $contents): bool
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_logo', [
            'img_logo'      => $filename,
            'active_logo'   => 0,
            'active_footer' => 0 // 🟢 AJOUT ICI
        ]);

        if ($this->executeInsert($qb)) {
            $idLogo = $this->getLastInsertId();

            foreach ($contents as $idLang => $data) {
                $qbContent = new QueryBuilder();
                $qbContent->insert('mc_logo_content', [
                    'id_logo'    => $idLogo,
                    'id_lang'    => (int)$idLang,
                    'alt_logo'   => $data['alt_logo'] ?? '',
                    'title_logo' => $data['title_logo'] ?? ''
                ]);
                $this->executeInsert($qbContent);
            }
            return true;
        }
        return false;
    }

    /**
     * Récupère tout le contenu SEO d'un logo pour le modal d'édition
     */
    public function getLogoContents(int $idLogo): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_logo_content')->where('id_logo = :id', ['id' => $idLogo]);

        $results = $this->executeAll($qb);
        $formatted = [];
        if ($results) {
            foreach ($results as $row) {
                $formatted[$row['id_lang']] = [
                    'alt_logo'   => $row['alt_logo'],
                    'title_logo' => $row['title_logo']
                ];
            }
        }
        return $formatted;
    }
    // Ajoutez ceci dans App\Backend\Db\LogoDb

    /**
     * Met à jour les balises SEO d'un logo pour une langue donnée
     */
    public function updateLogoContent(int $idLogo, int $idLang, string $alt, string $title): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_logo_content', [
            'alt_logo'   => $alt,
            'title_logo' => $title
        ])->where('id_logo = :id_logo AND id_lang = :id_lang', [
            'id_logo' => $idLogo,
            'id_lang' => $idLang
        ]);

        return $this->executeUpdate($qb);
    }
    /**
     * Définit un logo comme actif et désactive tous les autres
     */
    public function activateLogo(int $idLogo): bool
    {
        // 1. On passe tout à 0
        $qbReset = new QueryBuilder();
        $qbReset->update('mc_logo', ['active_logo' => 0]);
        $this->executeUpdate($qbReset);

        // 2. On active l'élu
        $qbSet = new QueryBuilder();
        $qbSet->update('mc_logo', ['active_logo' => 1])
            ->where('id_logo = :id', ['id' => $idLogo]);

        return $this->executeUpdate($qbSet);
    }

    /**
     * Supprime le logo de la BDD (Le delete physique sera géré dans le contrôleur)
     */
    public function deleteLogo(int $idLogo): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_logo')->where('id_logo = :id', ['id' => $idLogo]);
        return $this->executeDelete($qb);
    }

    /**
     * Récupère le nom du fichier pour pouvoir le supprimer du disque
     */
    public function getLogoFilename(int $idLogo): ?string
    {
        $qb = new QueryBuilder();
        $qb->select('img_logo')->from('mc_logo')->where('id_logo = :id', ['id' => $idLogo]);
        $res = $this->executeRow($qb);
        return $res['img_logo'] ?? null;
    }

    /**
     * Met à jour uniquement le nom du fichier image
     */
    public function updateLogoFilename(int $idLogo, string $newFilename): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_logo', ['img_logo' => $newFilename])
            ->where('id_logo = :id', ['id' => $idLogo]);

        return $this->executeUpdate($qb);
    }
    /**
     * Définit un logo comme actif pour le FOOTER et désactive tous les autres
     */
    public function activateFooterLogo(int $idLogo): bool
    {
        // 1. On passe tout à 0 pour le footer
        $qbReset = new QueryBuilder();
        $qbReset->update('mc_logo', ['active_footer' => 0]);
        $this->executeUpdate($qbReset);

        // 2. On active l'élu
        $qbSet = new QueryBuilder();
        $qbSet->update('mc_logo', ['active_footer' => 1])
            ->where('id_logo = :id', ['id' => $idLogo]);

        return $this->executeUpdate($qbSet);
    }
}