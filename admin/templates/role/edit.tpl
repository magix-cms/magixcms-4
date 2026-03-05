{extends file="layout.tpl"}
{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-shield-lock me-2"></i> Permissions du rôle</h1>
        <a href="index.php?controller=Role" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>

    <form action="index.php?controller=Role&action=edit&edit={$id_role}" method="post" class="validate_form">
        <input type="hidden" name="hashtoken" value="{$hashtoken}">

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th class="ps-4">Module / Contrôleur</th>
                        <th class="text-center">Voir</th>
                        <th class="text-center">Ajouter</th>
                        <th class="text-center">Éditer</th>
                        <th class="text-center">Supprimer</th>
                        <th class="text-center pe-4">Actions spéciales</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $modules as $mod}
                        {$pid = $mod.id_module}
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-dark text-capitalize">{$mod.name}</span>
                                <div class="small text-muted font-monospace">Controller: {$mod.name|ucfirst}</div>
                            </td>
                            {* On boucle sur les 5 colonnes de droits *}
                            {foreach ['view', 'append', 'edit', 'del', 'action'] as $right}
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox"
                                               name="permissions[{$pid}][{$right}]" value="1"
                                               {if isset($permissions.$pid.$right) && $permissions.$pid.$right == 1}checked{/if}>
                                    </div>
                                </td>
                            {/foreach}
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3 text-end">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="bi bi-save me-2"></i> Enregistrer les permissions
                </button>
            </div>
        </div>
    </form>
{/block}