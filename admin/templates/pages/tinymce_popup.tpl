<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélecteur de pages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fc; padding: 20px; font-size: 0.9rem; }
        .table-hover tbody tr:hover { cursor: pointer; background-color: #e9ecef; }
        .subpage-indicator { color: #adb5bd; margin-right: 8px; border-left: 2px solid #dee2e6; padding-left: 8px; }
        /* Petite transition pour une disparition fluide lors de la recherche */
        tbody tr { transition: opacity 0.2s ease-in-out; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-primary mb-0">
            <i class="bi bi-files me-2"></i> Sélectionner une page
            <span class="badge bg-secondary ms-2">{$iso_lang}</span>
        </h5>

        <!--  NOUVEAU : La barre de recherche -->
        <div class="input-group" style="width: 250px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Filtrer les pages...">
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0" id="pagesTable">
                <thead class="table-light position-sticky top-0" style="z-index: 1;">
                <tr>
                    <th class="ps-3">Structure des pages</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
                </thead>
                <tbody>
                {if isset($pagesList) && $pagesList|@count > 0}
                    {foreach $pagesList as $page}
                        <!--  AJOUT : class="page-row" pour le ciblage JS -->
                        <tr class="page-row" onclick="insertPage('{$page.url|escape:'javascript'}', '{$page.title|escape:'javascript'}')">
                            <td class="ps-3">
                                {* Indentation dynamique selon le niveau de parenté *}
                                {if $page.depth > 0}
                                    <span style="margin-left: {$page.depth * 20}px;" class="subpage-indicator">&#8627;</span>
                                    <!--  AJOUT : class="page-title" pour la recherche -->
                                    <span class="text-dark page-title">{$page.title}</span>
                                {else}
                                    <!--  AJOUT : class="page-title" pour la recherche -->
                                    <span class="fw-bold text-dark page-title">{$page.title}</span>
                                {/if}
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center text-nowrap">
                                    <i class="bi bi-link-45deg me-1"></i> Insérer
                                </button>
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="2" class="text-center py-4 text-muted">Aucune page disponible.</td>
                    </tr>
                {/if}
                </tbody>
            </table>
            <!--  NOUVEAU : Message caché si la recherche ne donne rien -->
            <div id="noResultsMsg" class="text-center py-4 text-muted d-none">
                Aucune page ne correspond à votre recherche.
            </div>
        </div>
    </div>
</div>

<script>
    {literal}
    // 1. Fonction d'insertion TinyMCE existante
    function insertPage(url, title) {
        window.parent.postMessage({
            mceAction: 'insertContent',
            content: `<a href="${url}" title="${title}">${title}</a>`
        }, '*');

        window.parent.postMessage({
            mceAction: 'close'
        }, '*');
    }

    // 2.  NOUVEAU : Logique de filtrage en direct
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const rows = document.querySelectorAll('.page-row');
        const noResultsMsg = document.getElementById('noResultsMsg');

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase().trim();
                let visibleCount = 0;

                rows.forEach(row => {
                    // On cherche le texte uniquement dans l'élément qui contient le titre
                    const titleText = row.querySelector('.page-title').textContent.toLowerCase();

                    if (titleText.includes(filter)) {
                        row.style.display = ''; // Affiche la ligne
                        visibleCount++;
                    } else {
                        row.style.display = 'none'; // Cache la ligne
                    }
                });

                // Gérer l'affichage du message "Aucun résultat"
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