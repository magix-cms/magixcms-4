{extends file="layout.tpl"}
{block name='head:title'}Gestion des administrateurs{/block}

{block name="article"}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-people me-2"></i> Administrateurs</h1>
        {* On affiche le bouton Ajouter seulement si l'utilisateur en a le droit (ou si non défini = super-admin) *}
        {if !isset($user_permissions) || $user_permissions.append == 1}
            <a href="index.php?controller=Employee&action=add" class="btn btn-primary shadow-sm">
                <i class="bi bi-person-plus-fill me-1"></i> Ajouter un administrateur
            </a>
        {/if}
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="m-0 fw-bold text-primary">Liste des comptes</h6>

            <form action="index.php" method="get" class="d-flex mb-0">
                <input type="hidden" name="controller" value="Employee">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="search[email_admin]" value="{$get_search.email_admin|default:''}" placeholder="Rechercher un email...">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body p-4">
            {* Utilisation de votre composant générique de tableau magique *}
            {include file="components/table-forms.tpl" data=$employee_list idcolumn=$idcolumn activation=true sortable=$sortable controller="Employee" change_offset=true}
        </div>
    </div>
{/block}