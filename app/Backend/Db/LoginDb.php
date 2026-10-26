<?php

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class LoginDb extends BaseDb
{
    /**
     * Recherche un employé par son e-mail et récupère son état de blocage.
     */
    public function getAdminCredentials(string $email): ?array
    {
        $qb = new QueryBuilder();

        $qb->select([
            'em.id_admin', 'em.email_admin', 'em.keyuniqid_admin', 'em.passwd_admin',
            'em.active_admin', 'em.failed_attempts', 'em.locked_until'
        ])
            ->from('mc_admin_employee', 'em')
            ->join('mc_admin_access_rel', 'rel', 'em.id_admin = rel.id_admin')
            ->where('em.email_admin = :email', ['email' => $email]);

        $result = $this->executeRow($qb);

        return $result ?: null;
    }

    /**
     * Incrémente le compteur d'échecs et verrouille le compte si la limite est atteinte.
     */
    public function recordFailedAttempt(string $email, int $maxAttempts = 5, int $lockoutMinutes = 15): int
    {
        $admin = $this->getAdminCredentials($email);
        if (!$admin) return 0;

        $attempts = (int)($admin['failed_attempts'] ?? 0) + 1;
        $data = ['failed_attempts' => $attempts];

        // Si le maximum d'essais est atteint, on verrouille le compte
        if ($attempts >= $maxAttempts) {
            $data['locked_until'] = date('Y-m-d H:i:s', strtotime("+$lockoutMinutes minutes"));
        }

        $qb = new QueryBuilder();
        $qb->update('mc_admin_employee', $data)
            ->where('email_admin = :email', ['email' => $email]);

        $this->executeUpdate($qb);

        return $attempts;
    }

    /**
     * Remet à zéro les échecs après une connexion réussie.
     */
    public function resetFailedAttempts(string $email): void
    {
        $qb = new QueryBuilder();
        $qb->update('mc_admin_employee', [
            'failed_attempts' => 0,
            'locked_until'    => null
        ])->where('email_admin = :email', ['email' => $email]);

        $this->executeUpdate($qb);
    }

    /**
     * Vérifie si l'e-mail correspond à un administrateur et retourne sa clé unique.
     */
    public function getAdminKeyByEmail(string $email): ?string
    {
        $qb = new QueryBuilder();
        $qb->select(['keyuniqid_admin'])
            ->from('mc_admin_employee')
            ->where('email_admin = :email_forgot', ['email_forgot' => $email]);

        $result = $this->executeRow($qb);
        return $result ? $result['keyuniqid_admin'] : null;
    }

    /**
     * Enregistre le mot de passe temporaire (ou token) de récupération.
     */
    public function updateRecoveryPassword(string $email, string $temporaryPassword): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_admin_employee', [
            'change_passwd' => $temporaryPassword
        ])->where('email_admin = :email_admin', ['email_admin' => $email]);

        return $this->executeUpdate($qb);
    }
}