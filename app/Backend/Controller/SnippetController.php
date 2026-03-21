<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\SnippetDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;

class SnippetController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run(): void
    {
        $action = $_GET['action'] ?? null;

        // 🟢 ROUTE API JSON POUR TINYMCE (Liste les snippets)
        if ($action === 'tinymce') {
            $this->tinymce();
            return;
        }

        // 🟢 ROUTE AFFICHAGE BRUT POUR TINYMCE (Charge le contenu d'un snippet)
        if ($action === 'display') {
            $this->display();
            return;
        }

        // Mini-routeur classique
        if (isset($_GET['edit'])) {
            $action = 'edit';
        }

        if ($action && $action !== 'run' && method_exists($this, $action)) {
            $this->$action();
            return;
        }

        $this->index();
    }

    /**
     * Génère le flux JSON strict attendu par TinyMCE avec les URL asynchrones
     */
    public function tinymce(): void
    {
        if (ob_get_length()) ob_clean();

        $db = new SnippetDb();
        $rawSnippets = $db->getSnippetsForTinymce();
        $snippets = [];

        foreach ($rawSnippets as $s) {
            $snippets[] = [
                'title'       => $s['title'],
                'description' => $s['description'],
                // Construction de l'URL pour charger le contenu
                'url'         => 'index.php?controller=Snippet&action=display&id=' . $s['id_snippet']
            ];
        }

        // Si vous avez toujours besoin de fusionner avec des fichiers physiques (comme setSnippetFiles dans CMS3)
        // C'est ici que vous feriez : $snippets = array_merge($snippets, $this->getSnippetFiles());

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        echo $this->json->encode($snippets);
        exit;
    }

    /**
     * 🟢 NOUVELLE ACTION : Renvoie le code HTML brut du snippet à TinyMCE
     */
    public function display(): void
    {
        // On nettoie le buffer pour ne pas envoyer le layout du CMS
        if (ob_get_length()) ob_clean();

        $idSnippet = (int)($_GET['id'] ?? 0);

        if ($idSnippet > 0) {
            $db = new SnippetDb();
            $snippet = $db->fetchSnippetById($idSnippet);

            if ($snippet) {
                // On envoie le contenu tel quel, sans aucun formatage
                echo $snippet['content_sp'];
                exit;
            }
        }

        // Fallback discret en cas d'erreur
        echo '<p class="text-danger">Snippet introuvable.</p>';
        exit;
    }

    /**
     * Liste des snippets (table-forms)
     */
    private function index(): void
    {
        $db = new SnippetDb();

        // 1. Schéma pour table-forms
        $targetColumns = ['id_snippet', 'order_sp', 'title_sp', 'description_sp', 'date_register'];
        $rawScheme = $db->getTableScheme('mc_snippet');

        $associations = [
            'id_snippet'     => ['title' => 'ID', 'type' => 'text', 'class' => 'text-center text-muted small px-2'],
            'order_sp'       => ['title' => 'Ordre', 'type' => 'text', 'class' => 'text-muted fw-bold text-center'],
            'title_sp'       => ['title' => 'Titre du modèle', 'type' => 'text', 'class' => 'fw-bold'],
            'description_sp' => ['title' => 'Description', 'type' => 'text', 'class' => 'w-50 text-muted'],
            'date_register'  => ['title' => 'Date', 'type' => 'date', 'class' => 'text-center text-nowrap text-muted small']
        ];

        $this->getScheme($rawScheme, $targetColumns, $associations);

        // 2. Données
        $snippets = $db->fetchAllSnippets();
        $this->getItems('snippetList', $snippets, true);

        // 3. Variables Smarty
        $token = $this->session->getToken();
        $this->view->assign([
            'idcolumn'  => 'id_snippet',
            'hashtoken' => $token,
            'url_token' => urlencode($token),
            'sortable'  => true,
            'checkbox'  => true,
            'edit'      => true,
            'dlt'       => true
        ]);

        $this->view->display('appearance/snippet/index.tpl');
    }

    /**
     * Formulaire d'ajout
     */
    public function add(): void
    {
        $this->view->assign([
            'hashtoken' => $this->session->getToken(),
            'snippet'   => ['id_snippet' => 0] // Tableau vide par défaut
        ]);

        $this->view->display('appearance/snippet/form.tpl');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(): void
    {
        $idSnippet = (int)($_GET['edit'] ?? 0);
        $db = new SnippetDb();

        $snippet = $db->fetchSnippetById($idSnippet);

        if (!$snippet) {
            header('Location: index.php?controller=Snippet');
            exit;
        }

        $this->view->assign([
            'hashtoken' => $this->session->getToken(),
            'snippet'   => $snippet
        ]);

        $this->view->display('appearance/snippet/form.tpl');
    }

    /**
     * Sauvegarde (Ajout & Mise à jour)
     */
    public function save(): void
    {
        if (!Request::isMethod('POST')) return;

        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        $idSnippet = (int)($_POST['id_snippet'] ?? 0);
        $isNew = ($idSnippet === 0);

        // On nettoie le titre et la description
        $data = [
            'title_sp'       => FormTool::simpleClean($_POST['title_sp'] ?? ''),
            'description_sp' => FormTool::simpleClean($_POST['description_sp'] ?? ''),
            // ⚠️ On ne passe pas le contenu au simpleClean car c'est du HTML brut pour TinyMCE !
            'content_sp'     => trim($_POST['content_sp'] ?? '')
        ];

        if (empty($data['title_sp'])) {
            $this->jsonResponse(false, 'Le titre du snippet est obligatoire.');
        }

        $db = new SnippetDb();

        if ($isNew) {
            $success = $db->insertSnippet($data);
            $msg = 'Le snippet a été ajouté avec succès.';
        } else {
            $success = $db->updateSnippet($idSnippet, $data);
            $msg = 'Le snippet a été mis à jour.';
        }

        if ($success !== false) {
            $this->jsonResponse(true, $msg, ['type' => $isNew ? 'add' : 'update']);
        } else {
            $this->jsonResponse(false, 'Erreur lors de la sauvegarde du snippet.');
        }
    }

    /**
     * Suppression native compatible avec table-forms.tpl
     */
    public function delete(): void
    {
        if (ob_get_length()) ob_clean();

        $token = $_GET['hashtoken'] ?? '';
        if (!$this->session->validateToken(str_replace(' ', '+', $token))) {
            $this->jsonResponse(false, 'Token invalide.');
        }

        $ids = $_POST['ids'] ?? [$_POST['id'] ?? null];
        $cleanIds = array_filter(array_map('intval', (array)$ids));

        if (!empty($cleanIds)) {
            $db = new SnippetDb();
            $successCount = 0;

            foreach ($cleanIds as $idSnippet) {
                if ($db->deleteSnippet($idSnippet)) {
                    $successCount++;
                }
            }

            if ($successCount > 0) {
                $msg = $successCount > 1 ? 'Les snippets ont été supprimés.' : 'Le snippet a été supprimé.';
                echo $this->json->encode(['success' => true, 'message' => $msg, 'ids' => $cleanIds]);
                exit;
            }
        }

        echo $this->json->encode(['success' => false, 'message' => 'Aucun snippet sélectionné ou erreur de suppression.']);
        exit;
    }

    /**
     * Sauvegarde l'ordre des snippets (Drag & Drop)
     */
    public function reorder(): void
    {
        if (ob_get_length()) ob_clean();

        $token = str_replace(' ', '+', $_GET['hashtoken'] ?? '');
        if (!$this->session->validateToken($token)) {
            echo $this->json->encode(['success' => false, 'message' => 'Token invalide']);
            exit;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (isset($data['order']) && is_array($data['order'])) {
            $db = new SnippetDb();
            try {
                $position = 1;
                foreach ($data['order'] as $id) {
                    $db->updateSnippetOrder((int)$id, $position);
                    $position++;
                }
                echo $this->json->encode(['success' => true, 'message' => 'Ordre mis à jour.']);
                exit;
            } catch (\Exception $e) {
                echo $this->json->encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
                exit;
            }
        }

        echo $this->json->encode(['success' => false, 'message' => 'Données invalides.']);
        exit;
    }
}