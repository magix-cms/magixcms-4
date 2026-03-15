<?php

declare(strict_types=1);

namespace Plugins\Contact\db;

use App\Backend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class ContactDb extends BaseDb
{
    // ==========================================
    // GESTION DE LA PAGE (SEO & Contenu)
    // ==========================================
    public function getPageContent(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')
            ->from('mc_contact_page_content')
            ->where('id_page = 1 AND id_lang = :lang', ['lang' => $idLang]);

        return $this->executeRow($qb) ?: [];
    }

    public function savePageContent(int $idLang, array $data): bool
    {
        $qbCheck = new QueryBuilder();
        $qbCheck->select('id_content')->from('mc_contact_page_content')
            ->where('id_page = 1 AND id_lang = :lang', ['lang' => $idLang]);

        $exists = $this->executeRow($qbCheck);

        $qb = new QueryBuilder();
        if ($exists) {
            $qb->update('mc_contact_page_content', $data)
                ->where('id_page = 1 AND id_lang = :lang', ['lang' => $idLang]);
            return $this->executeUpdate($qb);
        } else {
            $data['id_page'] = 1;
            $data['id_lang'] = $idLang;
            $qb->insert('mc_contact_page_content', $data);
            return $this->executeInsert($qb);
        }
    }

    // ==========================================
    // GESTION DES DESTINATAIRES
    // ==========================================

    public function getContactsList(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select(['c.id_contact', 'c.mail_contact', 'c.is_default', 'cc.name_contact', 'cc.published_contact'])
            ->from('mc_contact', 'c')
            ->leftJoin('mc_contact_content', 'cc', 'c.id_contact = cc.id_contact AND cc.id_lang = ' . $idLang)
            ->orderBy('c.id_contact', 'ASC');

        return $this->executeAll($qb) ?: [];
    }

    public function deleteContact(int $idContact): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_contact')->where('id_contact = :id', ['id' => $idContact]);
        return $this->executeDelete($qb);
    }

    /**
     * Retire le statut "par défaut" de tous les contacts
     */
    public function resetDefaultContacts(): void
    {
        $qb = new QueryBuilder();
        $qb->update('mc_contact', ['is_default' => 0]);
        $this->executeUpdate($qb);
    }

    /**
     * Sauvegarde ou insère un contact et ses traductions
     */
    public function saveContact(int $idContact, array $mainData, array $contentData): bool
    {
        // 1. Mise à jour ou insertion de la base du contact
        $qbMain = new QueryBuilder();
        if ($idContact > 0) {
            $qbMain->update('mc_contact', $mainData)->where('id_contact = :id', ['id' => $idContact]);
            $this->executeUpdate($qbMain);
        } else {
            $qbMain->insert('mc_contact', $mainData);
            if ($this->executeInsert($qbMain)) {
                $idContact = $this->getLastInsertId();
            } else {
                return false;
            }
        }

        // 2. Gestion des traductions (Disponibilité et libellé)
        foreach ($contentData as $idLang => $data) {
            $qbCheck = new QueryBuilder();
            $qbCheck->select('id_content')->from('mc_contact_content')
                ->where('id_contact = :id AND id_lang = :lang', ['id' => $idContact, 'lang' => $idLang]);

            if ($this->executeRow($qbCheck)) {
                $qbUp = new QueryBuilder();
                $qbUp->update('mc_contact_content', $data)
                    ->where('id_contact = :id AND id_lang = :lang', ['id' => $idContact, 'lang' => $idLang]);
                $this->executeUpdate($qbUp);
            } else {
                $data['id_contact'] = $idContact;
                $data['id_lang']    = $idLang;
                $qbIn = new QueryBuilder();
                $qbIn->insert('mc_contact_content', $data);
                $this->executeInsert($qbIn);
            }
        }

        return true;
    }
    /**
     * Récupère un contact complet avec toutes ses traductions (pour l'édition AJAX)
     */
    public function getContactFull(int $idContact): array
    {
        // 1. Infos principales
        $qbMain = new QueryBuilder();
        $qbMain->select('*')->from('mc_contact')->where('id_contact = :id', ['id' => $idContact]);
        $contact = $this->executeRow($qbMain);

        if (!$contact) {
            return [];
        }

        // 2. Traductions
        $qbLang = new QueryBuilder();
        $qbLang->select('*')->from('mc_contact_content')->where('id_contact = :id', ['id' => $idContact]);
        $langs = $this->executeAll($qbLang);

        $contact['translations'] = [];
        if ($langs) {
            foreach ($langs as $l) {
                // On indexe par ID de langue pour que le JS s'y retrouve facilement
                $contact['translations'][$l['id_lang']] = $l;
            }
        }

        return $contact;
    }
}