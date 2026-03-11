{extends file="layout.tpl"}
{block name='head:title'}Réseaux de partage{/block}

{block name="article"}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-share me-2"></i> Réseaux de partage</h1>
        <a href="index.php?controller=Share&action=add" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Ajouter un réseau
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">Liste des réseaux de partage disponibles</h6>
        </div>
        <div class="card-body">
            {include file="components/table-forms.tpl" data=$share_list idcolumn=$idcolumn activation=true sortable=$sortable controller="Share" change_offset=true}
        </div>
    </div>
{/block}