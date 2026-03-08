<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class MenuDb extends BaseDb
{
    /**
     * Outil de debug QueryBuilder (À garder comme modèle)
     */
    public function debugQuery(QueryBuilder $qb): void
    {
        echo "<div style='background:#f8f9fa; border:1px solid #ddd; padding:15px; margin:10px 0; font-family:monospace; font-size:12px;'>";
        echo "<strong style='color:#d63384;'>[DEBUG QUERYBUILDER]</strong><br><br>";

        if (method_exists($qb, 'getSQL')) {
            echo "<strong>SQL :</strong> " . $qb->getSQL() . "<br>";
        } elseif (method_exists($qb, 'getQuery')) {
            echo "<strong>SQL :</strong> " . $qb->getQuery() . "<br>";
        } else {
            echo "<strong>Structure de l'objet :</strong><pre>";
            print_r($qb);
            echo "</pre>";
        }
        echo "</div>";
    }

    public function fetchAllLinks(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select('m.*, mc.name_link, mc.title_link, mc.url_link')
            ->from('mc_menu', 'm')
            ->leftJoin('mc_menu_content', 'mc', 'm.id_link = mc.id_link AND mc.id_lang = ' . (int)$idLang)
            ->orderBy('m.order_link'); // Sans ASC manuel

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère le nom réel d'un contenu pour l'utiliser comme nom de lien par défaut
     */
    public function getTargetName(string $type, int $idTarget, int $idLang): string
    {
        $qb = new QueryBuilder();

        // Concaténation directe des entiers pour éviter les erreurs de format de tableau (Binding)
        switch ($type) {
            case 'pages':
                $qb->select('name_pages as name')->from('mc_cms_page_content')
                    ->where('id_pages = ' . $idTarget . ' AND id_lang = ' . $idLang);
                break;
            case 'about_page':
                $qb->select('name_about as name')->from('mc_about_content')
                    ->where('id_about = ' . $idTarget . ' AND id_lang = ' . $idLang);
                break;
            case 'category':
                $qb->select('name_cat as name')->from('mc_catalog_cat_content')
                    ->where('id_cat = ' . $idTarget . ' AND id_lang = ' . $idLang);
                break;
            default:
                return 'Lien ' . ucfirst($type);
        }

        // executeAll()[0] est souvent plus résistant que executeRow() sur certaines vieilles requêtes
        $res = $this->executeAll($qb);
        return !empty($res[0]['name']) ? $res[0]['name'] : 'Lien ' . ucfirst($type);
    }

    public function getPagesTree(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select('p.id_pages, p.id_parent, c.name_pages')
            ->from('mc_cms_page', 'p')
            ->leftJoin('mc_cms_page_content', 'c', 'p.id_pages = c.id_pages AND c.id_lang = ' . (int)$idLang)
            ->orderBy('p.id_parent, p.id_pages');

        $data = $this->executeAll($qb);
        return $this->buildTree(is_array($data) ? $data : [], 'id_pages', 'name_pages');
    }

    public function getAboutTree(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select('a.id_about, a.id_parent, c.name_about')
            ->from('mc_about', 'a')
            ->leftJoin('mc_about_content', 'c', 'a.id_about = c.id_about AND c.id_lang = ' . (int)$idLang)
            ->orderBy('a.id_parent, a.id_about');

        $data = $this->executeAll($qb);
        return $this->buildTree(is_array($data) ? $data : [], 'id_about', 'name_about');
    }

    public function getCategoryTree(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select('c.id_cat, c.id_parent, cc.name_cat')
            ->from('mc_catalog_cat', 'c')
            ->leftJoin('mc_catalog_cat_content', 'cc', 'c.id_cat = cc.id_cat AND cc.id_lang = ' . (int)$idLang)
            ->orderBy('c.id_parent, c.id_cat');

        $data = $this->executeAll($qb);
        return $this->buildTree(is_array($data) ? $data : [], 'id_cat', 'name_cat');
    }

    private function buildTree(array $elements, string $idKey, string $nameKey): array
    {
        if (empty($elements)) return [];
        $indexed = [];
        $tree = [];

        foreach ($elements as $el) {
            $id = (int)($el[$idKey] ?? 0);
            $indexed[$id] = [
                'id'        => $id,
                'id_parent' => (int)($el['id_parent'] ?? 0),
                'name'      => $el[$nameKey] ?? 'Sans nom',
                'subdata'   => []
            ];
        }

        foreach ($indexed as $id => &$node) {
            $parentId = $node['id_parent'];
            if ($parentId === 0 || !isset($indexed[$parentId])) {
                $tree[$id] = &$node;
            } else {
                $indexed[$parentId]['subdata'][$id] = &$node;
            }
        }

        return json_decode(json_encode(array_values($tree)), true) ?: [];
    }

    public function insertMenu(array $data): int
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_menu', $data);
        return $this->executeInsert($qb) ? (int)$this->getLastInsertId() : 0;
    }

    public function insertMenuContent(int $idLink, int $idLang, array $data): bool
    {
        $data['id_link'] = $idLink;
        $data['id_lang'] = $idLang;
        $qb = new QueryBuilder();
        $qb->insert('mc_menu_content', $data);
        return $this->executeInsert($qb);
    }
    // --- LECTURE, MISE À JOUR, SUPPRESSION ET TRI ---

    public function getMenuContent(int $idLink): array
    {
        $qb = new QueryBuilder();
        // On récupère tout : le lien principal ET ses contenus
        $qb->select('m.mode_link, mc.*')
            ->from('mc_menu', 'm')
            ->leftJoin('mc_menu_content', 'mc', 'm.id_link = mc.id_link')
            ->where('m.id_link = ' . $idLink);

        $res = $this->executeAll($qb);
        $data = [
            'info' => [], // Pour stocker mode_link
            'langs' => [] // Pour les traductions
        ];

        if ($res) {
            $data['info'] = ['mode_link' => $res[0]['mode_link']];
            foreach ($res as $row) {
                $data['langs'][$row['id_lang']] = $row;
            }
        }
        return $data;
    }

    public function updateMenuLink(int $idLink, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_menu', $data)
            ->where('id_link = ' . $idLink);

        return $this->executeUpdate($qb);
    }

    public function updateMenuContent(int $idLink, int $idLang, array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_menu_content', $data)
            ->where('id_link = ' . $idLink . ' AND id_lang = ' . $idLang);

        return $this->executeUpdate($qb);
    }

    public function deleteMenu(int $idLink): bool
    {
        // On supprime le contenu traduit
        $qbContent = new QueryBuilder();
        $qbContent->delete('mc_menu_content')->where('id_link = ' . $idLink);
        $this->executeDelete($qbContent);

        // On supprime le lien parent
        $qbLink = new QueryBuilder();
        $qbLink->delete('mc_menu')->where('id_link = ' . $idLink);
        return $this->executeDelete($qbLink);
    }

    public function updateOrder(int $idLink, int $order): void
    {
        $qb = new QueryBuilder();
        $qb->update('mc_menu', ['order_link' => $order])
            ->where('id_link = ' . $idLink);
        $this->executeUpdate($qb);
    }
}