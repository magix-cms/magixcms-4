<?php

declare(strict_types=1);

namespace Plugins\Contact\db;

use App\Frontend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class ContactFrontDb extends BaseDb
{
    /**
     * Récupère les données SEO et texte de la page de contact
     */
    public function getPageContent(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_contact_page_content')
            ->where('id_page = 1 AND id_lang = :lang AND published_page = 1', ['lang' => $idLang]);

        return $this->executeRow($qb) ?: [];
    }

    /**
     * Récupère la liste des services disponibles dans la langue courante
     */
    public function getActiveContacts(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select(['c.id_contact', 'cc.name_contact'])
            ->from('mc_contact', 'c')
            ->join('mc_contact_content', 'cc', 'c.id_contact = cc.id_contact')
            ->where('cc.id_lang = :lang AND cc.published_contact = 1', ['lang' => $idLang])
            ->orderBy('c.is_default', 'DESC'); // Met le contact par défaut en premier

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère l'adresse email brute d'un destinataire selon son ID
     */
    public function getContactEmail(int $idContact): string
    {
        $qb = new QueryBuilder();
        $qb->select('mail_contact')->from('mc_contact')->where('id_contact = :id', ['id' => $idContact]);
        $result = $this->executeRow($qb);

        return $result ? $result['mail_contact'] : '';
    }
}