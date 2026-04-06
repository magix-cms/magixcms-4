<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\SeoRedirectDb;
use Magepattern\Component\Tool\FormTool;

class SeoRedirectController extends BaseController
{
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
     * Affiche la liste des redirections via le composant table-forms
     */
    private function index(): void
    {
        $db = new SeoRedirectDb();

        // 1. Définition stricte des colonnes et du schéma
        $targetColumns = ['id_redirect', 'old_url', 'new_url', 'type_redirect', 'active'];
        $rawScheme = $db->getTableScheme('mc_seo_redirect');

        $associations = [
            'id_redirect'   => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'old_url'       => ['title' => 'Ancienne URL', 'type' => 'text', 'class' => 'fw-bold'],
            'new_url'       => ['title' => 'Nouvelle URL', 'type' => 'text', 'class' => 'text-success'],
            'type_redirect' => ['title' => 'Type', 'type' => 'badge', 'class' => 'text-center'],
            'active'        => ['title' => 'Statut', 'type' => 'bin', 'class' => 'text-center']
        ];

        // 2. Initialisation du Helper pour formater les données correctement
        $this->getScheme($rawScheme, $targetColumns, $associations);

        // 3. Récupération des données paginées
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 25;
        $redirectData = $db->getPaginatedList($page, $limit);

        $meta = [];

        // 4. Formatage strict des données avec getItems() pour éviter l'erreur id_redirect
        if ($redirectData !== false) {
            $this->getItems('redirect_list', $redirectData['data'], true, $redirectData['meta']);
            $meta = $redirectData['meta'];
        }

        // 5. Assignation à la vue
        $this->view->assign([
            'idcolumn'   => 'id_redirect',
            'hashtoken'  => $this->session->getToken(),
            'url_token'  => urlencode($this->session->getToken()),
            'sortable'   => false,
            'checkbox'   => true, // Permet la sélection multiple
            'edit'       => true,
            'dlt'        => true,
            'meta'       => $meta
        ]);

        $this->view->display('seo_redirect/index.tpl');
    }

    /**
     * Traitement de l'ajout massif via un textarea (copié-collé depuis Excel)
     */
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $massInput = $_POST['mass_redirects'] ?? '';
            $defaultType = (int)($_POST['default_type'] ?? 301);

            if (empty(trim($massInput))) {
                $this->jsonResponse(false, 'Le champ de redirections est vide.');
            }

            $lines = explode("\n", str_replace("\r", "", $massInput));
            $validRedirects = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $parts = preg_split('/[\t;,]+/', $line);

                if (count($parts) >= 2) {
                    $oldUrl = parse_url(trim($parts[0]), PHP_URL_PATH);
                    $newUrl = trim($parts[1]);
                    $type = isset($parts[2]) ? (int)trim($parts[2]) : $defaultType;

                    if (!empty($oldUrl) && !empty($newUrl)) {
                        $validRedirects[] = [
                            'old_url'       => $oldUrl,
                            'new_url'       => $newUrl,
                            'type_redirect' => in_array($type, [301, 302]) ? $type : 301,
                            'active'        => 1
                        ];
                    }
                }
            }

            if (empty($validRedirects)) {
                $this->jsonResponse(false, 'Aucune redirection valide trouvée dans votre texte. Vérifiez le format.');
            }

            $db = new SeoRedirectDb();
            $count = $db->insertMassiveRedirects($validRedirects);

            // 🟢 Correction JS : Ajout de la clé 'redirect' pour le validate_form
            $this->jsonResponse(true, "Succès ! {$count} redirection(s) importée(s) ou mise(s) à jour.", [
                'type' => 'redirect',
                'redirect' => 'index.php?controller=SeoRedirect',
                'url' => 'index.php?controller=SeoRedirect'
            ]);
        }

        $this->view->display('seo_redirect/add.tpl');
    }

    /**
     * Traitement unitaire : Édition ou Création d'une seule redirection
     */
    public function edit(): void
    {
        $db = new SeoRedirectDb();
        $id = isset($_REQUEST['id_redirect']) ? (int)$_REQUEST['id_redirect'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'old_url'       => parse_url(FormTool::simpleClean($_POST['old_url'] ?? ''), PHP_URL_PATH),
                'new_url'       => FormTool::simpleClean($_POST['new_url'] ?? ''),
                'type_redirect' => in_array((int)($_POST['type_redirect'] ?? 301), [301, 302]) ? (int)$_POST['type_redirect'] : 301,
            ];

            if (empty($data['old_url']) || empty($data['new_url'])) {
                $this->jsonResponse(false, 'Les champs Ancienne URL et Nouvelle URL sont obligatoires.');
            }

            if ($id > 0) {
                // Mise à jour
                if ($db->updateRedirect($id, $data)) {
                    // 🟢 Correction JS : Ajout de 'redirect'
                    $this->jsonResponse(true, 'La redirection a été mise à jour avec succès.', [
                        'type' => 'redirect',
                        'redirect' => 'index.php?controller=SeoRedirect',
                        'url' => 'index.php?controller=SeoRedirect'
                    ]);
                }
            } else {
                // Création unitaire
                $data['active'] = 1;
                if ($db->insertRedirect($data)) {
                    // 🟢 Correction JS : Ajout de 'redirect'
                    $this->jsonResponse(true, 'La redirection a été ajoutée avec succès.', [
                        'type' => 'redirect',
                        'redirect' => 'index.php?controller=SeoRedirect',
                        'url' => 'index.php?controller=SeoRedirect'
                    ]);
                }
            }

            $this->jsonResponse(false, 'Une erreur est survenue lors de l\'enregistrement.');
        }

        // En mode édition, on charge les données
        if ($id > 0) {
            $this->view->assign('redirect_data', $db->getRedirectById($id));
        }

        $this->view->display('seo_redirect/edit.tpl');
    }

    /**
     * Activation / Désactivation à la volée via le tableau
     */
    public function state(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $state = (int)($_POST['state'] ?? 0);

        if ($id > 0) {
            $db = new SeoRedirectDb();
            if ($db->updateState($id, $state)) {
                $this->jsonResponse(true, 'Le statut de la redirection a été mis à jour.');
            }
        }
        $this->jsonResponse(false, 'Impossible de modifier le statut.');
    }

    /**
     * Suppression simple ou massive (via cases à cocher)
     */
    public function delete(): void
    {
        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            $db = new SeoRedirectDb();
            if ($db->deleteRedirects($cleanIds)) {
                $this->jsonResponse(true, 'Les redirections sélectionnées ont été supprimées.', ['type' => 'update']);
            }
        }

        $this->jsonResponse(false, 'Erreur lors de la suppression.');
    }
}