{extends file="layout.tpl"}
{block name='head:title'}{#snippet_management#|default:'Gestion des Modèles (Snippets)'}{/block}

{block name="article"}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-code-square me-2"></i> {#snippets#|default:'Modèles (Snippets)'}</h1>
        <a href="index.php?controller=Snippet&action=add" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> {#add_snippet#|default:'Ajouter un modèle'}
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">Liste des modèles</h6>
        </div>
        <div class="card-body">
            {* Note : j'ai retiré 'activation=true' car la table mc_snippet n'a pas de colonne pour activer/désactiver *}
            {include file="components/table-forms.tpl"
            data=$snippetList
            idcolumn='id_snippet'
            sortable=$sortable
            controller="Snippet"
            change_offset=true}
        </div>
    </div>
{/block}