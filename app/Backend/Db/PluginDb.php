<?php

declare(strict_types=1);

namespace App\Backend\Db;

use Magepattern\Component\Database\QueryBuilder;

class PluginDb extends BaseDb
{
    /**
     * Récupère tous les plugins enregistrés en base de données
     */
    public function fetchInstalledPlugins(): array
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_plugins');
        $results = $this->executeAll($qb);

        $plugins = [];
        if ($results) {
            foreach ($results as $row) {
                // On indexe par nom pour faciliter la comparaison avec le dossier physique
                $plugins[$row['name']] = $row;
            }
        }
        return $plugins;
    }

    /**
     * Enregistre un nouveau plugin lors de son installation
     */
    public function insertPlugin(array $data): int|false
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_plugins', $data);
        if ($this->executeInsert($qb)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    /**
     * Lie le plugin à un module spécifique (mc_plugins_module)
     */
    public function linkPluginToModule(string $pluginName, string $moduleName): bool
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_plugins_module', [
            'plugin_name' => $pluginName,
            'module_name' => $moduleName,
            'active'      => 1
        ]);
        return $this->executeInsert($qb);
    }

    /**
     * Enregistre le plugin dans le système de rôles (en minuscule)
     * ET attribue automatiquement les pleins droits au rôle SuperAdmin (ID 1)
     */
    public function registerModuleRBAC(string $pluginName): void
    {
        $moduleNameDb = strtolower($pluginName);

        $qbCheck = new QueryBuilder();
        $qbCheck->select('id_module')->from('mc_module')->where('name = :name', ['name' => $moduleNameDb]);
        $existingModule = $this->executeRow($qbCheck);

        // Si le module n'existe pas, on le crée
        if (!$existingModule) {
            $qbInsert = new QueryBuilder();
            $qbInsert->insert('mc_module', [
                'name' => $moduleNameDb
            ]);
            $this->executeInsert($qbInsert);

            // On récupère le nouvel ID généré
            $idModule = $this->getLastInsertId();
        } else {
            $idModule = (int)$existingModule['id_module'];
        }

        // 🟢 AJOUT : Attribution des droits par défaut à l'Admin principal (ID Role = 1)
        if ($idModule > 0) {
            $qbCheckPerms = new QueryBuilder();
            $qbCheckPerms->select('id_access')
                ->from('mc_admin_access')
                ->where('id_role = 1 AND id_module = :id', ['id' => $idModule]);

            // S'il n'a pas encore de permissions pour ce module, on les crée !
            if (!$this->executeRow($qbCheckPerms)) {
                $qbInsertPerms = new QueryBuilder();
                $qbInsertPerms->insert('mc_admin_access', [
                    'id_role'   => 1,
                    'id_module' => $idModule,
                    'view'      => 1,
                    'append'    => 1, // 🟢 CORRIGÉ : C'est 'append' et non 'add' !
                    'edit'      => 1,
                    'del'       => 1,
                    'action'    => 1  // 🟢 AJOUT : La colonne action
                ]);
                $this->executeInsert($qbInsertPerms);
            }
        }
    }

    /**
     * Supprime le plugin du gestionnaire de rôles (en minuscule)
     */
    public function unregisterModuleRBAC(string $pluginName): bool
    {
        $moduleNameDb = strtolower($pluginName);

        $qbGet = new QueryBuilder();
        $qbGet->select('id_module')->from('mc_module')->where('name = :name', ['name' => $moduleNameDb]);
        $module = $this->executeRow($qbGet);

        if ($module) {
            $idModule = (int)$module['id_module'];

            $qbDelPerms = new QueryBuilder();
            $qbDelPerms->delete('mc_admin_access')->where('id_module = :id', ['id' => $idModule]);
            $this->executeDelete($qbDelPerms);

            $qbDelMod = new QueryBuilder();
            $qbDelMod->delete('mc_module')->where('id_module = :id', ['id' => $idModule]);
            return $this->executeDelete($qbDelMod);
        }
        return false;
    }

    /**
     * Supprime le plugin de la table principale
     */
    public function deletePlugin(string $pluginName): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_plugins')->where('name = :name', ['name' => $pluginName]);
        return $this->executeDelete($qb);
    }

    /**
     * Supprime toutes les liaisons du plugin avec les modules Core
     */
    public function unlinkPluginFromAllModules(string $pluginName): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_plugins_module')->where('plugin_name = :name', ['name' => $pluginName]);
        return $this->executeDelete($qb);
    }

    /**
     * 🟢 NOUVEAU : Supprime le plugin des zones d'affichage (Frontend Layout)
     */
    public function removePluginFromFrontendLayout(string $pluginName): bool
    {
        $qb = new QueryBuilder();
        $qb->delete('mc_hook_item')->where('module_name = :module_name', ['module_name' => $pluginName]);
        return $this->executeDelete($qb);
    }
}