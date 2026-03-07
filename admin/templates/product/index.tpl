{extends file="layout.tpl"}

{block name='head:title'}Gestion des produits{/block}

{block name='article'}
    {* --- EN-TÊTE DE LA PAGE --- *}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-box-seam me-2"></i> Gestion des produits
        </h1>

        {* Boutons d'actions *}
        <div class="d-flex gap-2">
            <a href="index.php?controller=Product&action=add" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Ajouter un produit
            </a>
        </div>
    </div>

    {* --- TABLEAU DE DONNÉES GÉNÉRIQUE --- *}
    {include file="components/table-forms.tpl" data=$products controller="Product"}

{/block}