{extends file="layout.tpl"}
{block name='head:title'}{#homepage_management#}{/block}

{block name="article"}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-files me-2"></i> Gestion des pages</h1>
        <a href="index.php?controller=Pages&action=add" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Ajouter une page
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">Liste des pages</h6>
        </div>
        <div class="card-body">
            {include file="components/table-forms.tpl" data=$pages idcolumn='id_pages' activation=true sortable=$sortable controller="Pages" change_offset=true}
        </div>
    </div>
{/block}