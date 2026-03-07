{extends file="layout.tpl"}

{block name='head:title'}Édition du Rôle{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-shield-lock me-2"></i> Permissions du Rôle
        </h1>
        <a href="index.php?controller=Role" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour aux rôles
        </a>
    </div>

    {* Le formulaire pointe vers l'action edit, car votre contrôleur intercepte le POST via Request::isMethod('POST') *}
    <form action="index.php?controller=Role&action=edit&edit={$id_role}" method="POST" class="validate_form">
        <input type="hidden" name="hashtoken" value="{$hashtoken}">

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary">Matrice d'accès (Core & Extensions)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Module / Plugin</th>
                            <th class="text-center" style="width: 120px;">Voir</th>
                            <th class="text-center" style="width: 120px;">Ajouter</th>
                            <th class="text-center" style="width: 120px;">Modifier</th>
                            <th class="text-center" style="width: 120px;">Supprimer</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $modules as $mod}
                            {* On vérifie si ce module a déjà des permissions enregistrées pour ce rôle *}
                            {assign var="perms" value=($permissions[$mod.id_module]|default:[])}

                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    <i class="bi bi-box me-2 opacity-50"></i> {$mod.name|ucfirst}
                                </td>
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        {* On utilise les clés exactes attendues par votre DB : view, append, edit, del *}
                                        <input class="form-check-input fs-5" type="checkbox" name="permissions[{$mod.id_module}][view]" value="1" {if isset($perms.view) && $perms.view == 1}checked{/if}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input class="form-check-input fs-5" type="checkbox" name="permissions[{$mod.id_module}][append]" value="1" {if isset($perms.append) && $perms.append == 1}checked{/if}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input class="form-check-input fs-5" type="checkbox" name="permissions[{$mod.id_module}][edit]" value="1" {if isset($perms.edit) && $perms.edit == 1}checked{/if}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input class="form-check-input fs-5" type="checkbox" name="permissions[{$mod.id_module}][del]" value="1" {if isset($perms.del) && $perms.del == 1}checked{/if}>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light text-end py-3 pe-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-2"></i> Enregistrer les permissions
                </button>
            </div>
        </div>
    </form>
{/block}