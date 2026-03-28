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
        $structure = []; // 🟢 NOUVEAU : On stocke la structure [Groupe => [clés]]

        foreach ($activeLangs as $lang) {
            $iso = $lang['iso_lang'];
            $langPath = $i18nDir . $iso . '.conf';

            // readConf renvoie maintenant [ 'Groupe' => ['cle' => 'val'] ]
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
            'structure'    => $structure, // 🟢 Remplacement de 'keys' par 'structure'
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
        $contents = $_POST['content'] ?? []; // Format: ['fr' => ['Groupe' => ['key' => 'val']]]

        $newKey = trim($_POST['new_key'] ?? '');
        $newGroup = trim($_POST['new_group'] ?? 'Général'); // 🟢 Le groupe de la nouvelle variable
        $newValues = $_POST['new_value'] ?? [];

        if ($domain === 'theme') {
            $themeDb = new ThemeDb();
            $currentTheme = $themeDb->getCurrentTheme();
            $baseDir = ROOT_DIR . 'skin' . DS . $currentTheme . DS . 'i18n' . DS;
        } else {
            $baseDir = ROOT_DIR . 'plugins' . DS . $domain . DS . 'i18n' . DS . 'front' . DS;
        }

        $success = true;

        foreach ($contents as $iso => $transData) {
            $filePath = $baseDir . $iso . '.conf';

            // Ajout de la nouvelle variable dans le bon groupe
            if (!empty($newKey) && isset($newValues[$iso]) && trim($newValues[$iso]) !== '') {
                $cleanKey = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($newKey));
                if (!isset($transData[$newGroup])) {
                    $transData[$newGroup] = [];
                }
                $transData[$newGroup][$cleanKey] = trim($newValues[$iso]);
            }

            // Écriture du fichier structuré
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

    /**
     * Parse le fichier et groupe les variables en fonction des commentaires #
     */
    private function readConf(string $path): array
    {
        if (!file_exists($path)) return [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $data = [];
        $currentGroup = 'Général'; // Groupe par défaut

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, ';')) continue;

            // 🟢 DÉTECTION DES GROUPES via les commentaires
            if (str_starts_with($line, '#')) {
                $possibleGroup = trim(substr($line, 1));
                // On ignore le commentaire système généré par le CMS
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

    /**
     * Écrit le fichier avec des sauts de lignes et les entêtes de groupes #
     */
    private function writeConf(string $path, array $data): bool
    {
        $content = "# Fichier généré par Magix CMS\n";

        foreach ($data as $group => $keys) {
            // On recrée le commentaire visuel pour le groupe
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