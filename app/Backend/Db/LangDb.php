<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\File\CacheTool;

class LangDb extends BaseDb
{
    /**
     * Récupère la langue par défaut du système.
     * @return array|false Retourne ['id_lang' => X, 'iso_lang' => 'XX'] ou false
     */
    public function getDefaultLanguage(): array|false
    {
        $cache = $this->getSqlCache();
        $qb = new QueryBuilder();

        $qb->select(['id_lang', 'iso_lang'])
            ->from('mc_lang', 'lang')
            // Pas de variables dynamiques ici, donc le tableau de bind est vide
            ->where('lang.default_lang = 1', []);

        // 2. Génération d'une clé UNIQUE liée au tag "lang"
        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'lang');

        // 3. Vérification du cache
        $cachedData = $cache->get($cacheKey);
        if ($cachedData !== null) { // Attention, ton CacheTool retourne null et pas false
            return $cachedData;
        }

        // 4. Exécution SQL si pas de cache
        $data = $this->executeRow($qb);

        if ($data !== false) {
            $cache->set($cacheKey, $data, 86400); // Mise en cache pour 24h
        }

        return $data;
    }
    public function updateDefaultLanguage(int $newIdLang): bool
    {
        $qb = new QueryBuilder();
        // ... Logique pour remettre tous les default_lang à 0
        // ... Logique pour mettre le nouveau default_lang à 1

        // Imaginons que l'update réussisse :
        $success = true; // Résultat de ton Layer->execute()

        if ($success) {
            // C'EST ICI QUE TU VIDES LE CACHE !
            $cacheDir = SQLCACHEADMIN . 'var/sql';
            $cache = new CacheTool($cacheDir);

            // On supprime TOUS les fichiers de cache qui commencent par "lang_"
            $cache->clearByTag('lang');
        }

        return $success;
    }
    /**
     * Récupère toutes les langues pour le frontend formatées pour le dropdown.
     * @return array Tableau associatif [id_lang => iso_lang]
     */
    public function getFrontendLanguages(): array
    {
        $qb = new QueryBuilder();
        $qb->select(['l.id_lang', 'l.iso_lang', 'l.name_lang', 'l.default_lang'])
            ->from('mc_lang', 'l')
            // On trie exactement comme dans ton ancienne requête
            ->orderBy('l.default_lang', 'DESC')
            ->orderBy('l.id_lang', 'ASC');

        // On utilise la nouvelle méthode executeAll de BaseDb
        $result = $this->executeAll($qb);

        if (!$result) {
            return [];
        }

        // On formate le tableau comme ton ancien array_combine($id_lang, $iso_lang)
        $langs = [];
        foreach ($result as $row) {
            $langs[$row['id_lang']] = $row['iso_lang'];
        }

        return $langs;
    }
    /**
     * Compte le nombre de langues disponibles/actives.
     * @return int
     */
    public function countActiveLanguages(): int
    {
        $qb = new QueryBuilder();
        $qb->select(['COUNT(id_lang) AS total'])
            ->from('mc_lang')
            ->where('active_lang = 1');



        $result = $this->executeRow($qb);

        // On retourne l'entier trouvé, ou 0 si la table est vide
        return $result ? (int)$result['total'] : 0;
    }
}