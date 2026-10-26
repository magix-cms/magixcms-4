<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\SettingDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;
use Magepattern\Component\HTTP\Url;
use App\Backend\Db\DomainDb;
use App\Component\Cache\CacheManager;

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

    private function processSave(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $db = new SettingDb();
        $success = true;

        // =========================================================================
        // LISTE BLANCHE DES PARAMÈTRES (Whitelist)
        // Seules ces clés sont autorisées à être modifiées par ce formulaire.
        // La clé 'theme' en est volontairement exclue pour des raisons de sécurité.
        // =========================================================================
        $allowedKeys = [
            'mode',
            'ssl',
            'maintenance',
            'cache',
            'robots',
            'geminiai',
            'product_catalog',
            'calendar_enabled'
            // Si vous avez d'autres champs dans setting/index.tpl, ajoutez-les ici.
        ];

        $booleanKeys = [
            'ssl', 'maintenance', 'geminiai', 'product_catalog', 'calendar_enabled'
        ];

        $postedSettings = $_POST['settings'] ?? [];

        foreach ($booleanKeys as $key) {
            if (!isset($postedSettings[$key])) {
                $postedSettings[$key] = '0';
            }
        }

        foreach ($postedSettings as $name => $value) {

            // 🛡️ SÉCURITÉ : On bloque toute clé qui ne fait pas partie de la liste blanche
            if (!in_array($name, $allowedKeys, true)) {
                continue;
            }

            $cleanValue = FormTool::simpleClean((string)$value);

            if (!$db->updateSetting($name, $cleanValue)) {
                $success = false;
            }
        }

        if ($success && isset($postedSettings['robots']) && in_array('robots', $allowedKeys, true)) {
            $this->generateRobotsTxt($postedSettings);
        }

        if ($success) {
            CacheManager::clearFrontend('settings');

            $this->jsonResponse(true, 'La configuration a été mise à jour avec succès.', [
                'type' => 'update'
            ]);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la mise à jour des paramètres.');
        }
    }

    private function generateRobotsTxt(array $postedSettings): void
    {
        $robotsMode = $postedSettings['robots'];
        $robotsFile = ROOT_DIR . 'robots.txt';
        $content = "User-Agent: *\n";

        if ($robotsMode === 'noindex,nofollow') {
            $content .= "Disallow: /\n";
        } else {
            $content .= "Allow: /\n\n";

            $isSsl = (int)($postedSettings['ssl'] ?? 0);
            $protocol = ($isSsl === 1) ? 'https://' : 'http://';

            $domainDb = new DomainDb();
            $domainsResult = $domainDb->fetchAllDomains(1, 1000, []);
            $domains = $domainsResult['data'] ?? [];

            if (!empty($domains)) {
                foreach ($domains as $domain) {
                    $rawUrl = rtrim($domain['url_domain'], '/');
                    $cleanDomainName = str_replace(['http://', 'https://'], '', $rawUrl);

                    $baseUrl = $protocol . $cleanDomainName;
                    $content .= "Sitemap: {$baseUrl}/sitemap-{$cleanDomainName}.xml\n";
                }
            } else {
                $domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $baseUrl = $protocol . $domainName;
                $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";
            }
        }

        file_put_contents($robotsFile, $content);
    }
}