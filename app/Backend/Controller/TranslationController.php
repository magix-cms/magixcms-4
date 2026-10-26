<?php
declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\LangDb;
use App\Backend\Db\ThemeDb;
use App\Backend\Db\PluginDb;

class TranslationController extends BaseController
{
    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }

    public function index(): void
    {
        $langDb = new LangDb();
        $themeDb = new ThemeDb();
        $pluginDb = new PluginDb();

        $rawLangs = $langDb->getFrontendLanguages() ?: [];
        $activeLangs = [];
        $formattedLangs = [];

        foreach ($rawLangs as $key => $val) {
            if (is_array($val)) {
                $id = $val['id_lang'] ?? $key;
                $iso = $val['iso_lang'] ?? 'fr';
                $activeLangs[] = ['id_lang' => $id, 'iso_lang' => $iso];
                $formattedLangs[$id] = $iso;
            } else {
                $activeLangs[] = ['id_lang' => $key, 'iso_lang' => $val];
                $formattedLangs[$key] = $val;
            }
        }

        $rawDefault = $langDb->getDefaultLanguage() ?: ['iso_lang' => 'fr'];
        $defaultLang = is_array($rawDefault) ? $rawDefault : ['iso_lang' => $rawDefault];

        $currentTheme = $themeDb->getCurrentTheme();

        $rawPlugins = $pluginDb->fetchInstalledPlugins();
        $installedPlugins = [];
        $pluginsDir = ROOT_DIR . 'plugins' . DS;

        foreach ($rawPlugins as $pluginName => $dbData) {
            $manifestPath = $pluginsDir . $pluginName . DS . 'manifest.json';

            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                $type = $manifest['type'] ?? 'backend';
                $translatableTypes = ['widget', 'frontend', 'hybrid', 'addon'];
                $hasFrontI18n = is_dir($pluginsDir . $pluginName . DS . 'i18n' . DS . 'front');

                if (in_array($type, $translatableTypes) && $hasFrontI18n) {
                    $installedPlugins[] = [
                        'name' => $pluginName,
                        'type' => $type
                    ];
                }
            }
        }

        // CORRECTION PATH TRAVERSAL
        $rawDomain = $_GET['domain'] ?? 'theme';
        $domain = basename(preg_replace('/[^a-zA-Z0-9_-]/', '', $rawDomain));

        if ($domain === 'theme') {
            $i18nDir = ROOT_DIR . 'skin' . DS . $currentTheme . DS . 'i18n' . DS;
            $domainLabel = "Thème : " . ucfirst($currentTheme);
        } else {
            $i18nDir = ROOT_DIR . 'plugins' . DS . $domain . DS . 'i18n' . DS . 'front' . DS;
            $domainLabel = "Plugin : " . ucfirst(str_replace('Magix', '', $domain));
        }

        if (!is_dir($i18nDir)) {
            @mkdir($i18nDir, 0777, true);
        }

        $defaultConfPath = $i18nDir . $defaultLang['iso_lang'] . '.conf';

        if (!file_exists($defaultConfPath)) {
            file_put_contents($defaultConfPath, "");
        }

        foreach ($activeLangs as $lang) {
            $langPath = $i18nDir . $lang['iso_lang'] . '.conf';
            if (!file_exists($langPath)) {
                copy($defaultConfPath, $langPath);
            }
        }

        $translations = [];
        $structure = [];

        foreach ($activeLangs as $lang) {
            $iso = $lang['iso_lang'];
            $langPath = $i18nDir . $iso . '.conf';

            $parsed = $this->readConf($langPath);
            $translations[$iso] = $parsed;

            foreach ($parsed as $group => $keysArr) {
                if (!isset($structure[$group])) {
                    $structure[$group] = [];
                }
                foreach (array_keys($keysArr) as $k) {
                    if (!in_array($k, $structure[$group])) {
                        $structure[$group][] = $k;
                    }
                }
            }
        }

        $this->view->assign([
            'domain'       => $domain,
            'domain_label' => $domainLabel,
            'langs'        => $formattedLangs,
            'translations' => $translations,
            'structure'    => $structure,
            'plugins'      => $installedPlugins,
            'hashtoken'    => $this->session->getToken()
        ]);

        $this->view->display('translation/index.tpl');
    }

    public function save(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session invalide.');
        }

        $rawDomain = $_POST['domain'] ?? 'theme';
        $domain = basename(preg_replace('/[^a-zA-Z0-9_-]/', '', $rawDomain));

        $contents = $_POST['content'] ?? [];
        $newKey = trim($_POST['new_key'] ?? '');
        $newGroup = trim($_POST['new_group'] ?? 'Général');
        $newValues = $_POST['new_value'] ?? [];

        if ($domain === 'theme') {
            $themeDb = new ThemeDb();
            $currentTheme = basename(preg_replace('/[^a-zA-Z0-9_-]/', '', $themeDb->getCurrentTheme()));
            $baseDir = ROOT_DIR . 'skin' . DS . $currentTheme . DS . 'i18n' . DS;
        } else {
            $baseDir = ROOT_DIR . 'plugins' . DS . $domain . DS . 'i18n' . DS . 'front' . DS;
        }

        $success = true;

        foreach ($contents as $rawIso => $transData) {
            $iso = basename(preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$rawIso));
            if (empty($iso)) {
                continue;
            }

            $filePath = $baseDir . $iso . '.conf';

            // 🛡️ FIX : Nettoyage de TOUTES les traductions soumises
            $cleanTransData = [];
            if (is_array($transData)) {
                foreach ($transData as $group => $keys) {
                    if (is_array($keys)) {
                        foreach ($keys as $k => $v) {
                            $cleanTransData[$group][$k] = $this->sanitizeTranslation((string)$k, (string)$v);
                        }
                    }
                }
            }

            // Ajout de la nouvelle variable dans le bon groupe
            if (!empty($newKey) && isset($newValues[$iso]) && trim($newValues[$iso]) !== '') {
                $cleanKey = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($newKey));
                if (!isset($cleanTransData[$newGroup])) {
                    $cleanTransData[$newGroup] = [];
                }

                // 🛡️ FIX : Nettoyage de la nouvelle valeur ajoutée
                $cleanTransData[$newGroup][$cleanKey] = $this->sanitizeTranslation($cleanKey, (string)$newValues[$iso]);
            }

            // Écriture du fichier structuré avec les données propres
            if (!$this->writeConf($filePath, $cleanTransData)) {
                $success = false;
            }
        }

        if ($success) {
            $this->jsonResponse(true, "Toutes les traductions ont été enregistrées avec succès !");
        } else {
            $this->jsonResponse(false, "Erreur d'écriture. Vérifiez les droits du dossier (CHMOD).");
        }
    }

    /**
     * 🛡️ NOUVEAU FIX : Purifie une chaîne de traduction pour éviter les failles XSS (Stored XSS).
     */
    private function sanitizeTranslation(string $key, string $value): string
    {
        $val = trim($value);

        // 1. Validation stricte si la clé indique une URL (ex: legal_url, footer_website_link)
        if (str_contains(strtolower($key), 'url') || str_contains(strtolower($key), 'link')) {
            // Bloquer les schémas d'exécution de code dangereux (javascript:alert(1))
            if (preg_match('#^(javascript|vbscript|data):#i', $val)) {
                return '#'; // On neutralise le lien
            }
            // Autorise les URLs valides (http, https, mailto, tel) et les chemins relatifs (/)
            return filter_var($val, FILTER_SANITIZE_URL);
        }

        // 2. Nettoyage anti-XSS pour le texte général
        // Permet d'utiliser <strong> ou <br> dans les traductions tout en bloquant les attaques.

        // A. Suppression des balises actives malveillantes
        $val = preg_replace('/<(script|iframe|object|embed|applet|style).*?>.*?<\/\1>/is', '', $val);
        $val = preg_replace('/<(script|iframe|object|embed|applet|style).*?>/is', '', $val);

        // B. Suppression des attributs JavaScript (ex: <img src=x onerror=alert(1)>)
        $val = preg_replace('/(?<=\s)on[a-z]+\s*=\s*(["\']).*?\1/is', '', $val);

        // C. Neutralisation des liens "javascript:" injectés dans des balises HTML existantes
        $val = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/is', 'href="#"', $val);

        return $val;
    }

    private function readConf(string $path): array
    {
        if (!file_exists($path)) return [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $data = [];
        $currentGroup = 'Général';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, ';')) continue;

            if (str_starts_with($line, '#')) {
                $possibleGroup = trim(substr($line, 1));
                if ($possibleGroup !== 'Fichier généré par Magix CMS' && $possibleGroup !== '') {
                    $currentGroup = $possibleGroup;
                }
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val);

                if (str_starts_with($val, '"') && str_ends_with($val, '"')) {
                    $val = substr($val, 1, -1);
                }
                $data[$currentGroup][$key] = stripcslashes($val);
            }
        }
        return $data;
    }

    private function writeConf(string $path, array $data): bool
    {
        $content = "# Fichier généré par Magix CMS\n";

        foreach ($data as $group => $keys) {
            $content .= "\n# {$group}\n";

            foreach ($keys as $key => $val) {
                $cleanVal = (string)$val;
                $cleanVal = str_replace(["\r\n", "\r", "\n"], " ", trim($cleanVal));
                $content .= "{$key} = {$cleanVal}\n";
            }
        }

        return file_put_contents($path, ltrim($content)) !== false;
    }
}