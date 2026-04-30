<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélecteur de catégories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fc; padding: 20px; font-size: 0.9rem; }
        .table-hover tbody tr:hover { cursor: pointer; background-color: #e9ecef; }
        .subpage-indicator { color: #adb5bd; margin-right: 8px; border-left: 2px solid #dee2e6; padding-left: 8px; }
        /* Transition pour l'effet de filtre de la recherche */
        tbody tr { transition: opacity 0.2s ease-in-out; }
    </style>
</head>
<body>

<div class="container-fluid p-0">

    <!-- 🟢 EN-TÊTE : Titre + Barre de recherche sur la même ligne -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-primary mb-0">
            <i class="bi bi-folder2-open me-2"></i> Sélectionner une catégorie
            <span class="badge bg-secondary ms-2">{$iso_lang}</span>
        </h5>

        <div class="input-group" style="width: 250px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Filtrer les catégories...">
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <!-- 🟢 DÉFILEMENT : max-height et overflow-y -->
        <div class="card-body p-0 table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0" id="catTable">
                <!-- 🟢 EN-TÊTE FIXE : position-sticky -->
                <thead class="table-light position-sticky top-0" style="z-index: 1;">
                <tr>
                    <th class="ps-3">Arborescence du catalogue</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
                </thead>
                <tbody>
                {if isset($categoriesList) && $categoriesList|@count > 0}
                    {foreach $categoriesList as $cat}
                        <!-- 🟢 CIBLAGE JS : Ajout de la classe cat-row -->
                        <tr class="cat-row" onclick="insertCategory('{$cat.url|escape:'javascript'}', '{$cat.title|escape:'javascript'}')">
                            <td class="ps-3">
                                {* Indentation dynamique *}
                                {if $cat.depth > 0}
                                    <span style="margin-left: {$cat.depth * 20}px;" class="subpage-indicator">&#8627;</span>
                                    <!-- 🟢 CIBLAGE JS : Ajout de la classe cat-title -->
                                    <span class="text-dark cat-title">{$cat.title}</span>
                                {else}
                                    <!-- 🟢 CIBLAGE JS : Ajout de la classe cat-title -->
                                    <span class="fw-bold text-dark cat-title">{$cat.title}</span>
                                {/if}
                            </td>
                            <td class="text-end pe-3">
                                <!-- 🟢 FIX BOUTON : d-inline-flex, align-items-center, text-nowrap, me-1 -->
                                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center text-nowrap">
                                    <i class="bi bi-link-45deg me-1"></i> Insérer
                                </button>
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="2" class="text-center py-4 text-muted">Aucune catégorie disponible.</td>
                    </tr>
                {/if}
                </tbody>
            </table>
            <!-- 🟢 MESSAGE DE RECHERCHE VIDE -->
            <div id="noResultsMsg" class="text-center py-4 text-muted d-none">
                Aucune catégorie ne correspond à votre recherche.
            </div>
        </div>
    </div>
</div>

<script>
    {literal}
    // 1. Fonction d'insertion TinyMCE existante
    function insertCategory(url, title) {
        window.parent.postMessage({
            mceAction: 'insertContent',
            content: `<a href="${url}" title="${title}">${title}</a>`
        }, '*');

        window.parent.postMessage({
            mceAction: 'close'
        }, '*');
    }

    // 2. 🟢 LOGIQUE DE RECHERCHE EN DIRECT
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const rows = document.querySelectorAll('.cat-row');
        const noResultsMsg = document.getElementById('noResultsMsg');

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase().trim();
                let visibleCount = 0;

                rows.forEach(row => {
                    // On filtre en se basant sur le texte contenu dans la balise qui a la classe 'cat-title'
                    const titleText = row.querySelector('.cat-title').textContent.toLowerCase();

                    if (titleText.includes(filter)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Gestion de l'affichage du message si aucun résultat n'est trouvé
                if (visibleCount === 0 && rows.length > 0) {
                    noResultsMsg.classList.remove('d-none');
                } else {
                    noResultsMsg.classList.add('d-none');
                }
            });
        }
    });
    {/literal}
</script>
</body>
</html>