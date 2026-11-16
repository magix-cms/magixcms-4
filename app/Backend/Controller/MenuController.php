<?php
declare(strict_types=1);
namespace App\Backend\Controller;

use App\Backend\Db\MenuDb;
use App\Backend\Db\PluginDb; //  AJOUT : On importe la DB des plugins
use Magepattern\Component\Tool\FormTool;

class MenuController extends BaseController {
    /**
     * @return void
     * @throws \Random\RandomException
     * @throws \Smarty\Exception
     */
    public function run(): void
    {
        $action = $_GET['action'] ?? 'index';
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }

    /**
     * @return void
     * @throws \Random\RandomException
     * @throws \Smarty\Exception
     */
    public function index(): void
    {
        $db = new MenuDb();
        $idLang = (int)$this->defaultLang['id_lang'];

        $this->view->assign([
            'links'       => $db->fetchAllLinks($idLang),
            'pages_tree'  => $db->getPagesTree($idLang),
            'about_tree'  => $db->getAboutTree($idLang),
            'cat_tree'    => $db->getCategoryTree($idLang),
            'plugins_list'=> $this->getFrontendPlugins(), //  AJOUT : On envoie la liste des plugins
            'langs'       => $db->fetchLanguages(),
            'token'       => $this->session->getToken()
        ]);

        $this->view->display('appearance/menu/index.tpl');
    }

    /**
     * @return void
     */
    public function add(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session invalide.');
        }

        $db = new MenuDb();
        $type = $_POST['type_link'] ?? 'home';
        $mode = $_POST['mode_link'] ?? 'simple';

        // Sécurité : Vérifier le mode
        if (!in_array($mode, ['simple', 'dropdown', 'mega'])) $mode = 'simple';

        // L'ID cible dépend du type sélectionné par l'utilisateur
        $idPage = 0;
        $pluginFolder = trim($_POST['target_plugin'] ?? '');

        // --- DEBUT DE LA VALIDATION STRICTE DU PLUGIN ---
        if ($type === 'plugin') {
            if (empty($pluginFolder)) {
                $this->jsonResponse(false, "Le dossier du plugin cible est manquant.");
            }

            $manifestPath = ROOT_DIR . 'plugins/' . $pluginFolder . '/manifest.json';

            if (!file_exists($manifestPath)) {
                $this->jsonResponse(false, "Le plugin spécifié n'existe pas.");
            }

            $manifest = json_decode(file_get_contents($manifestPath), true);
            $pluginType = $manifest['type'] ?? 'backend';
            $hasRoute = $manifest['has_route'] ?? false;

            // On rejette strictement si le plugin n'a pas de route ou n'est pas du bon type
            if (!in_array($pluginType, ['frontend', 'hybrid']) || $hasRoute !== true) {
                $this->jsonResponse(false, "Ce plugin ne peut pas être ajouté au menu.");
            }
        }
        // --- FIN DE LA VALIDATION STRICTE DU PLUGIN ---

        if ($type === 'pages') $idPage = (int)($_POST['target_pages'] ?? 0);
        elseif ($type === 'about_page') $idPage = (int)($_POST['target_about'] ?? 0);
        elseif ($type === 'category') $idPage = (int)($_POST['target_category'] ?? 0);

        $idLink = $db->insertMenu([
            'type_link' => $type,
            'id_page'   => $idPage > 0 ? $idPage : null,
            'mode_link' => $mode,
            'order_link'=> 99
        ]);

        if ($idLink) {
            $langs = $db->fetchLanguages();
            foreach ($langs as $idLang => $iso) {

                $urlLink = ''; // Par défaut vide pour les pages internes

                // Logique spécifique pour les plugins (validée précédemment)
                if ($type === 'plugin' && !empty($pluginFolder)) {
                    $cleanName = ucfirst(str_replace('Magix', '', $pluginFolder));
                    $realName  = $cleanName;
                    $urlLink   = strtolower($cleanName); // ex: 'contact'
                } else {
                    $realName = $db->getTargetName($type, $idPage, (int)$idLang);
                }

                $db->insertMenuContent($idLink, (int)$idLang, [
                    'name_link'  => $realName,
                    'title_link' => $realName,
                    'url_link'   => $urlLink
                ]);
            }
            $this->jsonResponse(true, "Lien ajouté au menu", ['success' => true, 'type' => 'add']);
        }
        $this->jsonResponse(false, "Erreur lors de l'ajout", ['success' => false]);
    }

    /**
     * @return void
     */
    public function getContent(): void
    {
        if (ob_get_length()) ob_clean();

        $id = (int)($_GET['id'] ?? 0);
        $db = new MenuDb();

        echo json_encode(['status' => true, 'data' => $db->getMenuContent($id)]);
        exit;
    }

    /**
     * @return void
     */
    public function update(): void
    {
        $id = (int)($_POST['id_link'] ?? 0);
        $mode = $_POST['mode_link'] ?? 'simple';
        $contents = $_POST['content'] ?? [];

        if ($id > 0) {
            $db = new MenuDb();

            // 1. Mise à jour du mode global (mc_menu)
            $db->updateMenuLink($id, ['mode_link' => $mode]);

            // 2. Mise à jour des contenus (mc_menu_content)
            if (!empty($contents)) {
                foreach ($contents as $idLang => $data) {
                    $db->updateMenuContent($id, (int)$idLang, [
                        'name_link'  => trim($data['name_link'] ?? ''),
                        'title_link' => trim($data['title_link'] ?? ''),
                        'url_link'   => trim($data['url_link'] ?? '')
                    ]);
                }
            }
            $this->jsonResponse(true, "Lien mis à jour", ['success' => true]);
        }
    }

    /**
     * @return void
     */
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db = new MenuDb();
            if ($db->deleteMenu($id)) {
                $this->jsonResponse(true, "Lien supprimé avec succès", [
                    'success' => true, 'type' => 'delete', 'result' => ['id' => $id]
                ]);
            }
        }
        $this->jsonResponse(false, "Erreur de suppression");
    }

    /**
     * @return void
     */
    public function reorder(): void
    {
        $order = $_POST['order'] ?? [];
        if (!empty($order) && is_array($order)) {
            $db = new MenuDb();
            foreach ($order as $index => $idLink) {
                $db->updateOrder((int)$idLink, $index + 1);
            }
            $this->jsonResponse(true, "Ordre du menu enregistré");
        }
    }

    /**
     * @return void
     * @throws \Smarty\Exception
     */
    public function getList(): void
    {
        if (ob_get_length()) ob_clean();

        $db = new MenuDb();
        $idLang = (int)$this->defaultLang['id_lang'];

        $this->view->assign('links', $db->fetchAllLinks($idLang));

        // On compile uniquement le petit fichier list.tpl
        $html = $this->view->fetch('appearance/menu/list.tpl');

        $this->jsonResponse(true, 'OK', ['result' => $html]);
    }

    /**
     * Scanne les plugins INSTALLÉS et filtre ceux qui peuvent avoir une URL dans le menu
     */
    /**
     * @return array
     */
    private function getFrontendPlugins(): array
    {
        $pluginDb = new PluginDb();
        $installedPlugins = $pluginDb->fetchInstalledPlugins();

        $frontendPlugins = [];
        $dir = ROOT_DIR . 'plugins/';

        foreach ($installedPlugins as $folderName => $dbData) {
            $manifestPath = $dir . $folderName . '/manifest.json';

            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);

                $type = $manifest['type'] ?? 'backend';

                // AJOUT : Vérification du nouveau flag (par défaut à false si inexistant)
                $hasRoute = $manifest['has_route'] ?? false;

                // MODIFICATION : Le plugin doit être frontend/hybrid ET autoriser sa présence dans le menu
                if (in_array($type, ['frontend', 'hybrid']) && $hasRoute === true) {
                    $frontendPlugins[] = [
                        'folder' => $folderName,
                        'name'   => $manifest['name'] ?? $folderName
                    ];
                }
            }
        }

        return $frontendPlugins;
    }
}