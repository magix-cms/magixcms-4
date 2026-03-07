{extends file="layout.tpl"}

{block name='head:title'}Gestion des catégories{/block}

{block name='article'}
    {* --- EN-TÊTE DE LA PAGE --- *}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-folder2-open me-2"></i> Gestion des catégories
        </h1>

        {* Bouton pour ajouter une nouvelle catégorie *}
        <a href="index.php?controller=Category&action=add" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Ajouter une catégorie
        </a>
    </div>

    {* --- TABLEAU DE DONNÉES GÉNÉRIQUE --- *}
    {* Note : La variable $categories correspond au premier paramètre
       passé dans $this->getItems('categories', ...) de votre contrôleur.
    *}
    {include file="components/table-forms.tpl" data=$categories controller="Category"}

{/block}