<?php

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Tool\DateTool;
use Magepattern\Component\Security\PasswordTool;

class LoginDb extends BaseDb
{
    /**
     * Authentifie l'administrateur en utilisant les outils Magepattern.
     */
    /**
     * Recherche un employé par son e-mail (sans vérifier le mot de passe ici).
     *
     * @param string $email
     * @return array|null Retourne les données de l'employé ou null si échec.
     */
    public function getAdminCredentials(string $email): ?array
    {
        $qb = new QueryBuilder();

        // Ajout de passwd_admin et active_admin dans la sélection
        $qb->select(['em.id_admin', 'em.email_admin', 'em.keyuniqid_admin', 'em.passwd_admin', 'em.active_admin'])
            ->from('mc_admin_employee', 'em')
            ->join('mc_admin_access_rel', 'rel', 'em.id_admin = rel.id_admin')
            // On cherche UNIQUEMENT par e-mail
            ->where('em.email_admin = :email', ['email' => $email]);

        $result = $this->executeRow($qb);

        return $result ?: null;
    }
    /**
     * Vérifie si l'e-mail correspond à un administrateur et retourne sa clé unique.
     */
    /**
     * @param string $email
     * @return string|null
     */
    public function getAdminKeyByEmail(string $email): ?string
    {
        $qb = new QueryBuilder();
        $qb->select(['keyuniqid_admin'])
            ->from('mc_admin_employee')
            ->where('email_admin = :email_forgot', ['email_forgot' => $email]);

        $result = $this->executeRow($qb);

        // On retourne uniquement la chaîne de la clé, ou null si l'e-mail n'existe pas
        return $result ? $result['keyuniqid_admin'] : null;
    }
    /**
     * Enregistre le mot de passe temporaire (ou token) de récupération.
     */
    /**
     * @param string $email
     * @param string $temporaryPassword
     * @return bool
     */
    public function updateRecoveryPassword(string $email, string $temporaryPassword): bool
    {
        $qb = new QueryBuilder();

        $qb->update('mc_admin_employee', [
            'change_passwd' => $temporaryPassword
        ])
            ->where('email_admin = :email_admin', ['email_admin' => $email]);

        // Fait appel à ta nouvelle méthode abstraite
        return $this->executeUpdate($qb);
    }
}