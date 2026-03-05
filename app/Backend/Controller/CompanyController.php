<?php

declare(strict_types=1);

namespace App\Backend\Controller;

use App\Backend\Db\CompanyDb;
use Magepattern\Component\HTTP\Request;
use Magepattern\Component\Tool\FormTool;

class CompanyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function run(): void
    {
        $db = new CompanyDb();

        // 1. Traitement du formulaire (POST)
        if (Request::isMethod('POST')) {
            $this->processSave($db);
            return;
        }

        // 2. Affichage (GET)
        $companyData = $db->getCompanyInfo();

        // --- NOUVEAU : Définition des types ---
        $types = [
            'org'    => ['schema' => 'Organization',      'label' => 'Organisation'],
            'locb'   => ['schema' => 'LocalBusiness',     'label' => 'Entreprise locale'],
            'corp'   => ['schema' => 'Corporation',       'label' => 'Société commerciale'],
            'store'  => ['schema' => 'Store',             'label' => 'Magasin'],
            'food'   => ['schema' => 'FoodEstablishment', 'label' => 'Restaurant'],
            'place'  => ['schema' => 'Place',             'label' => 'Lieu'],
            'person' => ['schema' => 'Person',            'label' => 'Personne physique']
        ];

        $this->view->assign([
            'company_data'  => $companyData,
            'company_types' => $types, // <--- On l'envoie à la vue ici
            'hashtoken'     => $this->session->getToken()
        ]);

        $this->view->display('company/index.tpl');
    }

    /**
     * Traite la sauvegarde des configurations
     */
    private function processSave(CompanyDb $db): void
    {
        $token = $_POST['hashtoken'] ?? '';
        if (!$this->session->validateToken($token)) {
            $this->jsonResponse(false, 'Session expirée.');
        }

        // On récupère le tableau 'company' du formulaire
        // ex: <input name="company[email]" ...>
        $data = $_POST['company'] ?? [];

        if (!empty($data) && is_array($data)) {
            try {
                // Mise à jour en masse via CompanyDb
                $db->updateAllInfos($data);

                $this->jsonResponse(true, 'Configuration mise à jour avec succès.');
            } catch (\Exception $e) {
                $this->logger->log($e->getMessage(), 'sql', 'error');
                $this->jsonResponse(false, 'Erreur lors de la mise à jour.');
            }
        }

        $this->jsonResponse(false, 'Aucune donnée reçue.');
    }
}