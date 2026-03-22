<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\RevisionsDb;
use Magepattern\Component\HTTP\Request;

class RevisionsController extends BaseController
{
    public function run(): void
    {
        $action = $_GET['action'] ?? null;

        // --- 1. ROUTES API JSON (Pour le plugin TinyMCE) ---
        $apiActions = ['get_list', 'get_content', 'clear_history', 'save_revision'];

        if ($action && in_array($action, $apiActions) && method_exists($this, $action)) {
            // C'est une requête API : on nettoie et on force le JSON
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json; charset=utf-8');

            $this->$action();
            exit;
        }

        // --- 2. ROUTES DU BACKEND (Interface d'administration) ---

        // Traitement du vidage complet
        if ($action === 'clearAll' && Request::isMethod('POST')) {
            $this->processClearAll();
            return;
        }

        // Affichage par défaut de la page
        $this->index();
    }

    /**
     * Affiche l'interface de gestion de l'historique
     */
    private function index(): void
    {
        $db = new RevisionsDb();
        $totalRevisions = $db->countTotalRevisions();

        $this->view->assign([
            'total_revisions' => $totalRevisions,
            'hashtoken'       => $this->session->getToken()
        ]);

        $this->view->display('revisions/index.tpl');
    }

    private function processClearAll(): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            echo json_encode(['status' => false, 'notify' => 'Session expirée ou jeton invalide.']);
            exit;
        }

        $db = new RevisionsDb();

        if ($db->truncateEntireHistory()) {
            // 🟢 FIX : json_encode direct sans passer par jsonResponse (pas de session flash)
            echo json_encode([
                'status' => true,
                'notify' => 'L\'historique complet a été supprimé avec succès.',
                'reload' => true // <-- On envoie l'ordre de recharger
            ]);
            exit;
        } else {
            echo json_encode(['status' => false, 'notify' => 'Une erreur est survenue lors de la suppression.']);
            exit;
        }
    }

    // ==========================================================
    // MÉTHODES API STRICTES POUR TINYMCE (Conservées à l'identique)
    // ==========================================================

    public function get_list(): void
    {
        $type   = $_GET['type'] ?? '';
        $itemId = (int)($_GET['item_id'] ?? 0);
        $idLang = (int)($_GET['id_lang'] ?? 0);
        $field  = $_GET['field'] ?? '';

        if ($type === '' || $itemId === 0 || $idLang === 0) {
            echo json_encode([]);
            exit;
        }

        $db = new RevisionsDb();
        $list = $db->getList($type, $itemId, $idLang, $field);

        echo json_encode($list);
    }

    public function get_content(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $db = new RevisionsDb();
            $content = $db->getContent($id);

            if ($content !== null) {
                echo json_encode(['success' => true, 'content' => $content]);
                exit;
            }
        }

        echo json_encode(['success' => false, 'message' => 'Révision introuvable']);
    }

    public function clear_history(): void
    {
        $type   = $_GET['type'] ?? '';
        $itemId = (int)($_GET['item_id'] ?? 0);
        $idLang = (int)($_GET['id_lang'] ?? 0);
        $field  = $_GET['field'] ?? '';

        if ($type !== '' && $itemId > 0 && $idLang > 0) {
            $db = new RevisionsDb();
            if ($db->clearHistory($type, $itemId, $idLang, $field)) {
                echo json_encode(['success' => true]);
                exit;
            }
        }

        echo json_encode(['success' => false]);
    }

    public function save_revision(): void
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?? $_POST;

        $type    = $data['type'] ?? '';
        $itemId  = (int)($data['item_id'] ?? 0);
        $idLang  = (int)($data['id_lang'] ?? 0);
        $field   = $data['field'] ?? '';
        $content = $data['content'] ?? '';

        if ($type !== '' && $itemId > 0 && $idLang > 0 && $content !== '') {
            $db = new RevisionsDb();
            if ($db->saveRevision($type, $itemId, $idLang, $field, $content)) {
                echo json_encode(['success' => true]);
                exit;
            }
        }

        echo json_encode(['success' => false]);
    }
}