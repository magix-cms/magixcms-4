<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\QueryHelper; // 🟢 Import pour appliquer les paramètres
use App\Component\Hook\HookManager;             // 🟢 Import pour déclencher les hooks

class AboutDb extends BaseDb
{
    /**
     * Récupère une page "About" spécifique avec son image par défaut
     */
    public function getAboutPage(int $id, int $idLang): ?array
    {
        $qb = new QueryBuilder();
        $qb->select('a.*, ac.*, img.name_img, imgc.alt_img, imgc.title_img, imgc.caption_img')
            ->from('mc_about', 'a')
            ->leftJoin('mc_about_content', 'ac', 'a.id_about = ac.id_about AND ac.id_lang = '.$idLang)
            ->leftJoin('mc_about_img', 'img', 'a.id_about = img.id_about AND img.default_img = 1')
            ->leftJoin('mc_about_img_content', 'imgc', 'img.id_img = imgc.id_img AND imgc.id_lang = '.$idLang)
            ->where('a.id_about = '.$id.' AND ac.published_about = 1');

        // 🟢 OVERRIDE : Un plugin peut ajouter des champs
        $overrides = HookManager::triggerFilter('extendAboutData', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        return $this->executeRow($qb) ?: null;
    }

    /**
     * Récupère toutes les images d'une page (Galerie)
     */
    public function getAboutImages(int $idAbout, int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select('img.*, imgc.*')
            ->from('mc_about_img', 'img')
            ->leftJoin('mc_about_img_content', 'imgc', 'img.id_img = imgc.id_img AND imgc.id_lang = '.$idLang)
            ->where('img.id_about = '.$idAbout)
            ->orderBy('img.order_img', 'ASC');

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère les pages "About" enfants d'un parent spécifique
     */
    public function getAboutChildren(int $idParent, int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select('a.*, ac.*, img.name_img, imgc.alt_img, imgc.title_img')
            ->from('mc_about', 'a')
            ->leftJoin('mc_about_content', 'ac', 'a.id_about = ac.id_about AND ac.id_lang = '.$idLang)
            ->leftJoin('mc_about_img', 'img', 'a.id_about = img.id_about AND img.default_img = 1')
            ->leftJoin('mc_about_img_content', 'imgc', 'img.id_img = imgc.id_img AND imgc.id_lang = '.$idLang)
            ->where('a.id_parent = '.$idParent.' AND ac.published_about = 1')
            ->orderBy('a.order_about', 'ASC');

        // 🟢 OVERRIDE : Pour les listes de pages (enfants)
        $overrides = HookManager::triggerFilter('extendAboutList', []);
        if (!empty($overrides)) {
            foreach ($overrides as $pluginOverride) {
                if (isset($pluginOverride['extendQueryParams'])) {
                    QueryHelper::applyExtendParams($qb, $pluginOverride['extendQueryParams']);
                }
            }
        }

        return $this->executeAll($qb) ?: [];
    }
}