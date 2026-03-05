{extends file="layout.tpl"}
{block name='head:title'}Gestion des rôles{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-shield-lock me-2"></i> Gestion des rôles
        </h1>
        {* Optionnel: Bouton pour ajouter un rôle si vous créez la méthode add() plus tard *}
        <a href="index.php?controller=Role&action=add" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg me-1"></i> Nouveau rôle</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="m-0 fw-bold text-primary">Liste des rôles d'administration</h6>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 80px;">ID</th>
                        <th>Nom du rôle</th>
                        <th class="text-end pe-4" style="width: 200px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    {if isset($role_list) && $role_list|count > 0}
                        {foreach $role_list as $role}
                            <tr>
                                <td class="ps-4 text-muted font-monospace">#{$role.id_role}</td>
                                <td>
                                    <span class="fw-bold text-dark text-uppercase">{$role.role_name}</span>
                                    {if $role.id_role == 1}
                                        <span class="badge bg-danger ms-2">Super-Admin</span>
                                    {/if}
                                </td>
                                <td class="text-end pe-4">
                                    {* Le Super-Admin (ID 1) a tous les droits par défaut, pas besoin de le brider *}
                                    {if $role.id_role == 1}
                                        <button class="btn btn-sm btn-outline-secondary disabled" title="Le Super-Admin a tous les droits par défaut">
                                            <i class="bi bi-lock-fill"></i> Intouchable
                                        </button>
                                    {else}
                                        <a href="index.php?controller=Role&action=edit&edit={$role.id_role}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-sliders me-1"></i> Permissions
                                        </a>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Aucun rôle trouvé.</td>
                        </tr>
                    {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
{/block}