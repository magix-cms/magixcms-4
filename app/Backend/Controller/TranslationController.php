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
        $formattedLangs = []; // 🟢 Format spécifique pour le dropdown-lang.tpl [id => iso]

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

        // 🟢 LOGIQUE : FILTRAGE INTELLIGENT DES PLUGINS (Type + Dossier i18n)
        $rawPlugins = $pluginDb->fetchInstalledPlugins();
        $installedPlugins = [];
        $pluginsDir = ROOT_DIR . 'plugins' . DS;

        foreach ($rawPlugins as $pluginName => $dbData) {
            $manifestPath = $pluginsDir . $pluginName . DS . 'manifest.json';

            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);

                // Rigueur absolue : si pas de type, c'est du backend par défaut
                $type = $manifest['type'] ?? 'backend';

                // Les types qui ont une présence frontend
                $translatableTypes = ['widget', 'frontend', 'hybrid'];

                // 🟢 LA NOUVELLE CONDITION : Le dossier des traductions publiques doit exister
                $hasFrontI18n = is_dir($pluginsDir . $pluginName . DS . 'i18n' . DS . 'front');

                // On n'ajoute le plugin à la liste QUE s'il remplit les deux conditions
                if (in_array($type, $translatableTypes) && $hasFrontI18n) {
                    $installedPlugins[] = [
                        'name' => $pluginName,
                        'type' => $type
                    ];
                }
            }
        }

        $domain = $_GET['domain'] ?? 'theme';

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
        $keys = [];

        foreach ($activeLangs as $lang) {
            $iso = $lang['iso_lang'];
            $langPath = $i18nDir . $iso . '.conf';
            $parsed = $this->readConf($langPath);
            $translations[$iso] = $parsed;

            foreach (array_keys($parsed) as $k) {
                if (!in_array($k, $keys)) {
                    $keys[] = $k;
                }
            }
        }

        $this->view->assign([
            'domain'       => $domain,
            'domain_label' => $domainLabel,
            'langs'        => $formattedLangs, // 🟢 Transmis au composant dropdown
            'translations' => $translations,
            'keys'         => $keys,
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

        $domain = $_POST['domain'] ?? 'theme';
        $contents = $_POST['content'] ?? []; // 🟢 Reçoit toutes les langues : ['fr' => [...], 'en' => [...]]
        $newKey = trim($_POST['new_key'] ?? '');
        $newValues = $_POST['new_value'] ?? []; // 🟢 Reçoit les nouvelles traductions : ['fr' => 'val', 'en' => 'val']

        if ($domain === 'theme') {
            $themeDb = new ThemeDb();
            $currentTheme = $themeDb->getCurrentTheme();
            $baseDir = ROOT_DIR . 'skin' . DS . $currentTheme . DS . 'i18n' . DS;
        } else {
            $baseDir = ROOT_DIR . 'plugins' . DS . $domain . DS . 'i18n' . DS . 'front' . DS;
        }

        $success = true;

        // On boucle sur les langues envoyées depuis le formulaire
        foreach ($contents as $iso => $transData) {
            $filePath = $baseDir . $iso . '.conf';

            // Si l'utilisateur a rempli une nouvelle clé ET une valeur pour cette langue
            if (!empty($newKey) && isset($newValues[$iso]) && trim($newValues[$iso]) !== '') {
                $cleanKey = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($newKey));
                $transData[$cleanKey] = trim($newValues[$iso]);
            }

            // On écrase le fichier .conf avec les nouvelles données
            if (!$this->writeConf($filePath, $transData)) {
                $success = false;
            }
        }

        if ($success) {
            $this->jsonResponse(true, "Toutes les traductions ont été enregistrées avec succès !");
        } else {
            $this->jsonResponse(false, "Erreur d'écriture. Vérifiez les droits du dossier (CHMOD).");
        }
    }

    private function readConf(string $path): array
    {
        if (!file_exists($path)) return [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $data = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#') || str_starts_with($line, ';')) continue;

            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val);

                if (str_starts_with($val, '"') && str_ends_with($val, '"')) {
                    $val = substr($val, 1, -1);
                }
                $data[$key] = stripcslashes($val);
            }
        }
        return $data;
    }

    /**
     * Écrit un tableau clé => valeur dans un fichier .conf compatible Smarty
     * (Sans guillemets pour permettre le HTML brut)
     */
    private function writeConf(string $path, array $data): bool
    {
        $content = "# Fichier généré par Magix CMS\n\n";

        foreach ($data as $key => $val) {
            // On s'assure de travailler sur une chaîne de caractères
            $cleanVal = (string)$val;

            // 🟢 SECURITÉ : Sans guillemets, Smarty ne supporte pas les sauts de ligne physiques.
            // On remplace les "Entrées" du textarea par de simples espaces.
            $cleanVal = str_replace(["\r\n", "\r", "\n"], " ", trim($cleanVal));

            // On écrit au format strict demandé : cle = valeur (sans guillemets)
            $content .= "{$key} = {$cleanVal}\n";
        }

        return file_put_contents($path, $content) !== false;
    }
}