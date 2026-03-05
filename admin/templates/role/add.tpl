{extends file="layout.tpl"}
{block name='head:title'}Créer un rôle{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-shield-plus me-2"></i> Nouveau rôle</h1>
        <a href="index.php?controller=Role" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour</a>
    </div>

    <form action="index.php?controller=Role&action=add" method="post" class="validate_form add_form">
        <input type="hidden" name="hashtoken" value="{$hashtoken}">

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label for="role_name" class="form-label fw-bold">Nom du rôle <span class="text-danger">*</span></label>
                        <input type="text" id="role_name" name="role_name" class="form-control" placeholder="ex: Rédacteur, Modérateur, etc." required>
                    </div>
                    <div class="col-md-6 text-muted small mt-3 mt-md-0 pt-md-3">
                        <i class="bi bi-info-circle me-1"></i> Ce nom sera utilisé pour identifier le groupe de permissions lors de la création d'un administrateur.
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="m-0 fw-bold text-primary">Matrice des permissions</h6>
            </div>
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
                    {if isset($modules)}
                        {foreach $modules as $mod}
                            {$pid = $mod.id_module}
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark text-capitalize">{$mod.name}</span>
                                    <div class="small text-muted font-monospace">Controller: {$mod.name|ucfirst}</div>
                                </td>
                                {foreach ['view', 'append', 'edit', 'del', 'action'] as $right}
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="permissions[{$pid}][{$right}]" value="1">
                                        </div>
                                    </td>
                                {/foreach}
                            </tr>
                        {/foreach}
                    {/if}
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3 text-end">
                <button type="submit" class="btn btn-success px-5">
                    <i class="bi bi-check-lg me-2"></i> Créer ce rôle
                </button>
            </div>
        </div>
    </form>
{/block}