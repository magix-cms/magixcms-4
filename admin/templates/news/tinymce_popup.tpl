<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélecteur d'actualités</title>
    <!-- On charge uniquement Bootstrap pour un rendu propre -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fc; padding: 20px; font-size: 0.9rem; }
        .table-hover tbody tr:hover { cursor: pointer; background-color: #e9ecef; }
        /* Transition pour l'effet de filtre de la recherche */
        tbody tr { transition: opacity 0.2s ease-in-out; }
    </style>
</head>
<body>

<div class="container-fluid p-0">

    <!--  EN-TÊTE : Titre + Barre de recherche sur la même ligne -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-primary mb-0">
            <i class="bi bi-newspaper me-2"></i> Sélectionner une actualité
            <span class="badge bg-secondary ms-2">{$iso_lang}</span>
        </h5>

        <div class="input-group" style="width: 250px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Filtrer les actualités...">
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <!--  DÉFILEMENT : max-height et overflow-y -->
        <div class="card-body p-0 table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover table-striped align-middle mb-0" id="newsTable">
                <!--  EN-TÊTE FIXE : position-sticky -->
                <thead class="table-light position-sticky top-0" style="z-index: 1;">
                <tr>
                    <th class="ps-3">Titre de l'actualité</th>
                    <th>Date</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
                </thead>
                <tbody>
                {if isset($newsList) && $newsList|@count > 0}
                    {foreach $newsList as $news}
                        <!--  CIBLAGE JS : Ajout de la classe news-row -->
                        <tr class="news-row" onclick="insertNews('{$news.url|escape:'javascript'}', '{$news.title|escape:'javascript'}')">
                            <!--  CIBLAGE JS : Ajout de la classe news-title -->
                            <td class="ps-3 fw-bold text-dark news-title">{$news.title}</td>
                            <td class="text-muted small">{$news.date|date_format:"%d/%m/%Y"}</td>
                            <td class="text-end pe-3">
                                <!--  FIX BOUTON : d-inline-flex, align-items-center, text-nowrap, me-1 -->
                                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center text-nowrap">
                                    <i class="bi bi-link-45deg me-1"></i> Insérer
                                </button>
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">Aucune actualité disponible.</td>
                    </tr>
                {/if}
                </tbody>
            </table>
            <!--  MESSAGE DE RECHERCHE VIDE -->
            <div id="noResultsMsg" class="text-center py-4 text-muted d-none">
                Aucune actualité ne correspond à votre recherche.
            </div>
        </div>
    </div>
</div>

<script>
    {literal}
    // 1. Fonction d'insertion TinyMCE existante
    function insertNews(url, title) {
        window.parent.postMessage({
            mceAction: 'insertContent',
            content: `<a href="${url}" title="${title}">${title}</a>`
        }, '*');

        window.parent.postMessage({
            mceAction: 'close'
        }, '*');
    }

    // 2.  LOGIQUE DE RECHERCHE EN DIRECT
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const rows = document.querySelectorAll('.news-row');
        const noResultsMsg = document.getElementById('noResultsMsg');

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase().trim();
                let visibleCount = 0;

                rows.forEach(row => {
                    // On filtre en se basant sur le texte contenu dans la balise qui a la classe 'news-title'
                    const titleText = row.querySelector('.news-title').textContent.toLowerCase();

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