<?php

declare(strict_types=1);

namespace App\Frontend\Db;

use Magepattern\Component\Database\QueryBuilder;

class SettingDb extends BaseDb
{
    /**
     * Récupère toutes les configurations et les indexe par la colonne `name`
     */
    /**
     * Récupère toutes les configurations et les indexe par la colonne `name`
     */
    public function fetchAllSettings(): array
    {
        // 1. On appelle l'outil de cache SQL
        $cache = $this->getSqlCache();

        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_setting');

        // 2. On génère la clé de cache avec le tag "settings"
        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'settings');

        // 3. On regarde si notre tableau formaté est déjà en cache
        $settings = $cache->get($cacheKey);

        if ($settings === null) {
            // Le cache est vide : on interroge la BDD
            $results = $this->executeAll($qb);
            $settings = [];

            if ($results) {
                // On formate le tableau (indexé par le nom du paramètre)
                foreach ($results as $row) {
                    $settings[$row['name']] = $row;
                }
            }

            //  LE BOUCLIER ANTI-POISON
            // On s'assure que la BDD a bien répondu avec les données vitales avant de cacher
            if (!empty($settings) && isset($settings['theme'])) {
                $cache->set($cacheKey, $settings, 86400);
            }
        }

        return $settings;
    }

    /**
     * Récupère l'URL du domaine marqué comme canonique
     */
    public function getCanonicalDomain(): ?string
    {
        $cache = $this->getSqlCache();

        $qb = new QueryBuilder();
        $qb->select(['url_domain'])
            ->from('mc_domain')
            ->where('canonical_domain = 1')
            ->limit(1);

        // Clé de cache avec le tag "domain"
        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'domain');

        $domain = $cache->get($cacheKey);

        if ($domain === null) {
            $result = $this->executeRow($qb);

            // On extrait juste la chaîne de caractères
            $domain = $result ? $result['url_domain'] : null;

            // On met en cache si on a bien trouvé un domaine canonique
            if ($domain !== null) {
                $cache->set($cacheKey, $domain, 86400);
            }
        }

        return $domain;
    }
    /**
     * Récupère la liste de tous les domaines autorisés (Whitelist)
     * Nettoie les valeurs pour ne garder que l'hôte (ex: mon-site.com)
     */
    public function getAllowedDomains(): array
    {
        $cache = $this->getSqlCache();

        $qb = new QueryBuilder();
        $qb->select(['url_domain'])->from('mc_domain');

        $cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams(), 'domains_list');
        $domains = $cache->get($cacheKey);

        if ($domains === null) {
            $results = $this->executeAll($qb);
            $domains = [];

            if ($results) {
                foreach ($results as $row) {
                    // On retire http://, https:// et le / final pour avoir un Host pur
                    $cleanHost = preg_replace('#^https?://#', '', rtrim($row['url_domain'], '/'));
                    // On gère d'éventuels sous-dossiers en ne gardant que le domaine
                    $cleanHost = explode('/', $cleanHost)[0];

                    $domains[] = strtolower($cleanHost);
                }
            }

            $cache->set($cacheKey, $domains, 86400); // En cache pour 24h
        }

        return $domains;
    }
}