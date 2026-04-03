<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\DomainDb;
use App\Backend\Db\LangDb;
use App\Component\Routing\UrlTool; // IMPORTANT POUR LE SITEMAP
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\XML\Sitemap; // IMPORTANT POUR LE SITEMAP
use App\Backend\Db\PluginDb; // 🟢 NOUVEAU : Import pour scanner les plugins

class DomainController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run(): void
    {
        $action = $_GET['action'] ?? null;
        if ($action && method_exists($this, $action)) {
            $this->$action();
            return;
        }

        $this->index();
    }

    /**
     * Affiche la liste des domaines et la configuration des modules
     */
    private function index(): void
    {
        $db = new DomainDb();

        // 1. --- GESTION DU TABLEAU DES DOMAINES ---
        $targetColumns = ['id_domain', 'url_domain', 'default_domain', 'canonical_domain'];
        $rawScheme = $db->getTableScheme('mc_domain');

        $associations = [
            'id_domain'        => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'url_domain'       => ['title' => 'URL du Domaine', 'type' => 'text', 'class' => 'fw-bold w-50'],
            'default_domain'   => ['title' => 'Défaut', 'type' => 'bin', 'class' => 'text-center px-3'],
            'canonical_domain' => ['title' => 'Canonique', 'type' => 'bin', 'class' => 'text-center px-3']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        $page   = Request::isGet('page') ? (int)$_GET['page'] : 1;
        $limit  = Request::isGet('offset') ? (int)$_GET['offset'] : 25;
        $search = $_GET['search'] ?? [];

        $result = $db->fetchAllDomains($page, $limit, $search);

        if ($result !== false) {
            $this->getItems('domain_list', $result['data'], true, $result['meta']);
        }

        // 2. --- GESTION DES MODULES (mc_config) ---
        $modulesConfig = $db->fetchModulesConfig();

        $this->view->assign([
            'idcolumn'      => 'id_domain',
            'modulesConfig' => $modulesConfig,
            'hashtoken'     => $this->session->getToken(),
            'url_token'     => urlencode($this->session->getToken()),
            'get_search'    => $search,
            'sortable'      => false,
            'checkbox'      => true,
            'edit'          => true,
            'dlt'           => true
        ]);

        $this->view->display('domain/index.tpl');
    }

    /**
     * Ajouter un domaine
     */
    public function add(): void
    {
        if (Request::isMethod('POST')) {
            $this->processAdd();
            return;
        }

        $this->view->assign(['hashtoken' => $this->session->getToken()]);
        $this->view->display('domain/add.tpl');
    }

    private function processAdd(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new DomainDb();
        $data = [
            'url_domain'       => FormTool::simpleClean($_POST['url_domain'] ?? ''),
            'default_domain'   => (int)($_POST['default_domain'] ?? 0),
            'canonical_domain' => (int)($_POST['canonical_domain'] ?? 0)
        ];

        if (empty($data['url_domain'])) {
            $this->jsonResponse(false, 'L\'URL du domaine est requise.');
        }

        $newId = $db->insertDomain($data);

        if ($newId) {
            $this->jsonResponse(true, 'Le domaine a été ajouté avec succès.', [
                'type' => 'add',
                'id'   => $newId
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la création du domaine.');
        }
    }

    /**
     * Éditer un domaine existant
     */
    public function edit(): void
    {
        $id = (int)($_REQUEST['edit'] ?? $_POST['id_domain'] ?? 0);
        $db = new DomainDb();

        if (Request::isMethod('POST')) {
            $this->processEdit($db, $id);
            return;
        }

        $domain = $db->fetchDomainById($id);
        if (!$domain) return;

        // --- GESTION DYNAMIQUE DU PROTOCOLE (HTTP vs HTTPS) ---
        $isSsl = isset($this->siteSettings['ssl']['value']) ? (int)$this->siteSettings['ssl']['value'] : 0;
        $protocol = ($isSsl === 1) ? 'https://' : 'http://';

        $rawUrl = rtrim($domain['url_domain'], '/');
        $cleanDomainName = str_replace(['http://', 'https://'], '', $rawUrl);

        // On force le bon protocole basé sur la configuration globale
        $baseUrl = $protocol . $cleanDomainName;

        $langDb = new LangDb();
        $allLangs = $langDb->fetchActiveLanguages();
        $domainLangs = $db->fetchDomainLanguages($id);

        $sitemapLangs = !empty($domainLangs) ? $domainLangs : $allLangs;
        
        $indexFileName = "sitemap-{$cleanDomainName}.xml";
        $indexExists = file_exists(ROOT_DIR . '/' . $indexFileName);

        foreach ($sitemapLangs as &$lang) {
            $iso = strtolower($lang['iso_lang'] ?? 'fr');
            $lang['page_sitemap_exists'] = file_exists(ROOT_DIR . '/' . "{$iso}-sitemap-{$cleanDomainName}.xml");
            $lang['img_sitemap_exists']  = file_exists(ROOT_DIR . '/' . "{$iso}-sitemap-image-{$cleanDomainName}.xml");
        }
        unset($lang); // Sécurité après un passage par référence

        $this->view->assign([
            'domain'               => $domain,
            'base_url'             => $baseUrl,
            'clean_domain'         => $cleanDomainName,
            'all_langs'            => $allLangs,
            'domain_langs'         => $domainLangs,
            'sitemap_langs'        => $sitemapLangs,
            'index_sitemap_exists' => $indexExists, // 🟢 Nouvelle variable
            'hashtoken'            => $this->session->getToken()
        ]);

        $this->view->display('domain/edit.tpl');
    }

    private function processEdit(DomainDb $db, int $id): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $data = [
            'url_domain'       => FormTool::simpleClean($_POST['url_domain'] ?? ''),
            'tracking_domain'  => $_POST['tracking_domain'] ?? '',
            'default_domain'   => (int)($_POST['default_domain'] ?? 0),
            'canonical_domain' => (int)($_POST['canonical_domain'] ?? 0)
        ];

        if ($db->updateDomain($id, $data)) {
            $this->jsonResponse(true, 'Le domaine a été mis à jour.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour.');
        }
    }

    /**
     * Suppression
     */
    public function delete(): void
    {
        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            if ((new DomainDb())->deleteDomain($cleanIds)) {
                $this->jsonResponse(true, 'Domaine(s) supprimé(s).');
            }
        }
        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }

    /**
     * Enregistrer l'activation/désactivation des modules (onglet Modules)
     */
    public function saveModules(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new DomainDb();
        $success = true;

        $modulesKeys = ['pages', 'news', 'catalog', 'about'];
        $postedModules = $_POST['modules'] ?? [];

        foreach ($modulesKeys as $module) {
            $status = isset($postedModules[$module]) ? 1 : 0;
            if (!$db->updateModuleConfig($module, $status)) {
                $success = false;
            }
        }

        if ($success) {
            $this->jsonResponse(true, 'L\'état des modules a été mis à jour.', ['type' => 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour des modules.');
        }
    }

    /**
     * Enregistre les langues associées au domaine (Onglet 3)
     */
    public function generateDomainSitemap(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $idDomain = (int)($_POST['id_domain'] ?? 0);
        $db = new DomainDb();
        $domain = $db->fetchDomainById($idDomain);

        if (!$domain) $this->jsonResponse(false, 'Domaine introuvable.');

        // --- GESTION DYNAMIQUE DU PROTOCOLE ---
        $isSsl = isset($this->siteSettings['ssl']['value']) ? (int)$this->siteSettings['ssl']['value'] : 0;
        $protocol = ($isSsl === 1) ? 'https://' : 'http://';

        $rawUrl = rtrim($domain['url_domain'], '/');
        $cleanDomainName = str_replace(['http://', 'https://'], '', $rawUrl);
        $baseUrl = $protocol . $cleanDomainName;

        $urlTool = new UrlTool();
        $domainLangs = $db->fetchDomainLanguages($idDomain);

        if (empty($domainLangs)) {
            $langDb = new LangDb();
            $domainLangs = $langDb->fetchActiveLanguages();
        }

        $indexFileName = "sitemap-{$cleanDomainName}.xml";
        $sitemapIndex = new Sitemap();
        $sitemapIndex->init(ROOT_DIR . '/' . $indexFileName, true);

        $activeModules = ['pages', 'news', 'catalog_cat', 'catalog_pro'];

        // 🟢 NOUVEAU : On récupère tous les plugins installés
        $pluginDb = new PluginDb();
        $installedPlugins = $pluginDb->fetchInstalledPlugins();

        foreach ($domainLangs as $lang) {
            $iso = strtolower($lang['iso_lang'] ?? 'fr');
            $idLang = (int)$lang['id_lang'];

            $pageSitemapName = "{$iso}-sitemap-{$cleanDomainName}.xml";
            $pageSitemap = new Sitemap();
            $pageSitemap->init(ROOT_DIR . '/' . $pageSitemapName);

            $imgSitemapName = "{$iso}-sitemap-image-{$cleanDomainName}.xml";
            $imgSitemap = null;
            $hasImages = false;

            // ==========================================
            // 1. LES MODULES NATIFS DU CMS (Core)
            // ==========================================
            foreach ($activeModules as $module) {
                $items = $db->getSitemapData($module, $idLang);

                foreach ($items as $item) {
                    $uri = '';
                    if ($module === 'catalog_pro') {
                        $uri = $urlTool->buildUrl([
                            'iso' => $iso, 'type' => 'product', 'id' => $item['id'],
                            'url' => $item['url_pro'], 'id_category' => $item['default_category_id'] ?? 0, 'url_category' => $item['url_cat'] ?? ''
                        ]);
                    } elseif ($module === 'catalog_cat') {
                        $uri = $urlTool->buildUrl(['iso' => $iso, 'type' => 'category', 'id' => $item['id'], 'url' => $item['url']]);
                    } elseif ($module === 'news') {
                        $rawDate = (!empty($item['date']) && !str_starts_with($item['date'], '0000')) ? $item['date'] : 'now';
                        $datePublish = date('Y-m-d', strtotime($rawDate));
                        $uri = '/' . $iso . '/news/' . $datePublish . '/' . $item['id'] . '-' . $item['url'] . '/';
                    } elseif ($module === 'pages') {
                        $uri = $urlTool->buildUrl(['iso' => $iso, 'type' => 'pages', 'id' => $item['id'], 'url' => $item['url']]);
                    }

                    $fullUrl = str_starts_with($uri, 'http') ? $uri : $baseUrl . '/' . ltrim($uri, '/');
                    $priority = ($module === 'catalog_pro') ? 0.8 : (($module === 'catalog_cat') ? 0.7 : 0.6);
                    $pageSitemap->addUrl($fullUrl, $item['date'] ?? 'now', 'weekly', $priority);

                    // --- AJOUT DES IMAGES ---
                    if (!empty($item['images'])) {
                        if (!$hasImages) {
                            $imgSitemap = new Sitemap();
                            $imgSitemap->init(ROOT_DIR . '/' . $imgSitemapName, false, true);
                            $hasImages = true;
                        }

                        $formattedImages = array_map(function($img) use ($baseUrl, $module, $item) {
                            $folderMap = [
                                'catalog_pro' => 'product',
                                'catalog_cat' => 'category',
                                'news'        => 'news',
                                'pages'       => 'pages'
                            ];
                            $folderName = $folderMap[$module] ?? $module;
                            $imgTitle = !empty(trim($img['title'] ?? '')) ? trim($img['title']) : trim($item['title'] ?? '');
                            $imgCaption = !empty(trim($img['caption'] ?? '')) ? trim($img['caption']) : trim($item['title'] ?? '');

                            return [
                                'loc'     => "{$baseUrl}/upload/{$folderName}/{$item['id']}/{$img['loc']}",
                                'title'   => $imgTitle,
                                'caption' => $imgCaption
                            ];
                        }, $item['images']);

                        $imgSitemap->addUrl($fullUrl, $item['date'] ?? 'now', 'monthly', 0.5, $formattedImages);
                    }
                }
            }

            // ==========================================
            // 2. 🟢 LES PLUGINS DYNAMIQUES
            // ==========================================
            foreach ($installedPlugins as $pluginName => $pluginData) {
                // On vérifie si le plugin a créé une classe exprès pour le sitemap
                $sitemapClass = "\\Plugins\\{$pluginName}\\src\\SitemapProvider";

                if (class_exists($sitemapClass) && method_exists($sitemapClass, 'getUrls')) {

                    $provider = new $sitemapClass();
                    // Le plugin nous renvoie un tableau propre de ses URLs
                    $pluginUrls = $provider->getUrls($idLang, $iso, $baseUrl);

                    if (is_array($pluginUrls)) {
                        foreach ($pluginUrls as $pUrl) {
                            // On sécurise l'URL finale
                            $fullUrl = str_starts_with($pUrl['loc'], 'http') ? $pUrl['loc'] : rtrim($baseUrl, '/') . '/' . ltrim($pUrl['loc'], '/');

                            $pageSitemap->addUrl(
                                $fullUrl,
                                $pUrl['date'] ?? 'now',
                                $pUrl['changefreq'] ?? 'weekly',
                                $pUrl['priority'] ?? 0.5
                            );

                            // --- AJOUT DES IMAGES DU PLUGIN SI PRÉSENTES ---
                            if (!empty($pUrl['images']) && is_array($pUrl['images'])) {
                                if (!$hasImages) {
                                    $imgSitemap = new Sitemap();
                                    $imgSitemap->init(ROOT_DIR . '/' . $imgSitemapName, false, true);
                                    $hasImages = true;
                                }
                                $imgSitemap->addUrl($fullUrl, $pUrl['date'] ?? 'now', 'monthly', 0.5, $pUrl['images']);
                            }
                        }
                    }
                }
            }

            // ==========================================
            // SAUVEGARDE FINALE
            // ==========================================
            $pageSitemap->save();
            $sitemapIndex->addSitemap("{$baseUrl}/{$pageSitemapName}", 'now');

            if ($hasImages && $imgSitemap !== null) {
                $imgSitemap->save();
                $sitemapIndex->addSitemap("{$baseUrl}/{$imgSitemapName}", 'now');
            } else {
                $oldFile = ROOT_DIR . '/' . $imgSitemapName;
                if (file_exists($oldFile)) @unlink($oldFile);
            }
        }

        if ($sitemapIndex->save()) {
            $this->jsonResponse(true, "Les fichiers Sitemaps ont été générés à la racine du site.");
        } else {
            $this->jsonResponse(false, "Erreur d'écriture. Vérifiez les permissions du dossier.");
        }
    }
}