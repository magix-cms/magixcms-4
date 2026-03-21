<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\SettingDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
// Si vous avez un composant Url pour récupérer l'URL de base, incluez-le
use Magepattern\Component\HTTP\Url;
use App\Backend\Db\DomainDb;

class SettingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';

        if ($action === 'save' && Request::isMethod('POST')) {
            $this->processSave();
            return;
        }

        $this->index();
    }

    /**
     * @return void
     * @throws \Smarty\Exception
     */
    private function index(): void
    {
        $db = new SettingDb();
        $settings = $db->fetchAllSettings();

        $this->view->assign([
            'settings'  => $settings,
            'hashtoken' => $this->session->getToken()
        ]);

        $this->view->display('setting/index.tpl');
    }

    /**
     * @return void
     */
    private function processSave(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new SettingDb();
        $success = true;

        $booleanKeys = [
            'concat', 'ssl',
            'http2', 'service_worker', 'amp', 'maintenance', 'geminiai', 'product_catalog'
        ];

        $postedSettings = $_POST['settings'] ?? [];

        foreach ($booleanKeys as $key) {
            if (!isset($postedSettings[$key])) {
                $postedSettings[$key] = '0';
            }
        }

        foreach ($postedSettings as $name => $value) {
            $cleanValue = FormTool::simpleClean((string)$value);

            if (!$db->updateSetting($name, $cleanValue)) {
                $success = false;
            }
        }

        if ($success && isset($postedSettings['robots'])) {
            $this->generateRobotsTxt($postedSettings);
        }

        if ($success) {
            $this->jsonResponse(true, 'La configuration a été mise à jour avec succès.', [
                'type' => 'update'
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour des paramètres.');
        }
    }

    /**
     * Génère le fichier robots.txt à la racine du projet en incluant tous les sitemaps mères
     */
    /**
     * @param array $postedSettings
     * @return void
     */
    private function generateRobotsTxt(array $postedSettings): void
    {
        $robotsMode = $postedSettings['robots'];
        $robotsFile = ROOT_DIR . 'robots.txt';
        $content = "User-Agent: *\n";

        if ($robotsMode === 'noindex,nofollow') {
            $content .= "Disallow: /\n";
        } else {
            $content .= "Allow: /\n\n";

            // 1. Détermination du protocole avec la donnée fraîchement sauvegardée
            $isSsl = (int)($postedSettings['ssl'] ?? 0);
            $protocol = ($isSsl === 1) ? 'https://' : 'http://';

            // 2. Récupération de tous les domaines configurés
            $domainDb = new DomainDb();
            // On force une limite très haute (ex: 1000) pour récupérer tous les domaines
            // en une seule fois, au cas où votre méthode fetchAllDomains utiliserait la pagination.
            $domainsResult = $domainDb->fetchAllDomains(1, 1000, []);
            $domains = $domainsResult['data'] ?? [];

            // 3. Boucle sur chaque domaine pour inscrire son Sitemap mère
            if (!empty($domains)) {
                foreach ($domains as $domain) {
                    $rawUrl = rtrim($domain['url_domain'], '/');
                    $cleanDomainName = str_replace(['http://', 'https://'], '', $rawUrl);

                    $baseUrl = $protocol . $cleanDomainName;

                    // Exactement le même format que dans votre DomainController
                    $content .= "Sitemap: {$baseUrl}/sitemap-{$cleanDomainName}.xml\n";
                }
            } else {
                // Fallback de sécurité (si aucun domaine n'est défini en BDD)
                $domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $baseUrl = $protocol . $domainName;
                $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";
            }
        }

        // Écriture du fichier avec les droits standards (écrase le contenu précédent)
        file_put_contents($robotsFile, $content);
    }
}