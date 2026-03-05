{extends file="layout.tpl"}

{block name='head:title'}Gestion About{/block}

{block name="article"}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-info-circle me-2"></i> Gestion About</h1>
        <a href="index.php?controller=About&action=add" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Ajouter une fiche
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">Liste des fiches About</h6>
        </div>

        <div class="card-body">
            {* Inclusion du composant MagixCMS standard.
               Il va générer le tableau automatiquement grâce aux configs du Contrôleur.
            *}
            {include file="components/table-forms.tpl"
            data=$about_list
            idcolumn='id_about'
            activation=true
            sortable=$sortable
            controller="About"
            change_offset=true
            edit=true
            dlt=true
            }
        </div>
    </div>
{/block}