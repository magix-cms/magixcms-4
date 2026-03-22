<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\RevisionsDb;

class RevisionsController extends BaseController
{
    public function run(): void
    {
        // Ce contrôleur agit uniquement comme une API JSON
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        $action = $_GET['action'] ?? null;

        $allowedActions = ['get_list', 'get_content', 'clear_history', 'save_revision'];

        if ($action && in_array($action, $allowedActions) && method_exists($this, $action)) {
            $this->$action();
            exit;
        }

        echo $this->json->encode(['success' => false, 'message' => 'Action inconnue']);
        exit;
    }

    /**
     * Retourne la liste des révisions sous forme de tableau JSON simple (Attendu par le JS)
     */
    public function get_list(): void
    {
        $type   = $_GET['type'] ?? '';
        $itemId = (int)($_GET['item_id'] ?? 0);
        $idLang = (int)($_GET['id_lang'] ?? 0);
        $field  = $_GET['field'] ?? '';

        if ($type === '' || $itemId === 0 || $idLang === 0) {
            echo $this->json->encode([]);
            exit;
        }

        $db = new RevisionsDb();
        $list = $db->getList($type, $itemId, $idLang, $field);

        echo $this->json->encode($list);
    }

    /**
     * Retourne le contenu HTML d'une révision pour restauration
     */
    public function get_content(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $db = new RevisionsDb();
            $content = $db->getContent($id);

            if ($content !== null) {
                echo $this->json->encode(['success' => true, 'content' => $content]);
                exit;
            }
        }

        echo $this->json->encode(['success' => false, 'message' => 'Révision introuvable']);
    }

    /**
     * Vider l'historique d'un champ précis
     */
    public function clear_history(): void
    {
        $type   = $_GET['type'] ?? '';
        $itemId = (int)($_GET['item_id'] ?? 0);
        $idLang = (int)($_GET['id_lang'] ?? 0);
        $field  = $_GET['field'] ?? '';

        if ($type !== '' && $itemId > 0 && $idLang > 0) {
            $db = new RevisionsDb();
            if ($db->clearHistory($type, $itemId, $idLang, $field)) {
                echo $this->json->encode(['success' => true]);
                exit;
            }
        }

        echo $this->json->encode(['success' => false]);
    }

    /**
     * Point d'entrée pour enregistrer une révision (Via Autosave ou Sauvegarde)
     */
    public function save_revision(): void
    {
        // Utilisé si vous faites un appel POST depuis un script Javascript d'autosave
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
                echo $this->json->encode(['success' => true]);
                exit;
            }
        }

        echo $this->json->encode(['success' => false]);
    }
}