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
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <h5 class="mb-3 text-primary">
        <i class="bi bi-newspaper me-2"></i> Sélectionner une actualité
        <span class="badge bg-secondary ms-2">{$iso_lang}</span>
    </h5>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">Titre de l'actualité</th>
                    <th>Date</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
                </thead>
                <tbody>
                {if isset($newsList) && $newsList|@count > 0}
                    {foreach $newsList as $news}
                        <!-- Au clic sur la ligne, on déclenche l'insertion -->
                        <tr onclick="insertNews('{$news.url|escape:'javascript'}', '{$news.title|escape:'javascript'}')">
                            <td class="ps-3 fw-bold text-dark">{$news.title}</td>
                            <td class="text-muted small">{$news.date|date_format:"%d/%m/%Y"}</td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-link-45deg"></i> Insérer
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
        </div>
    </div>
</div>

<script>
    {literal}
    function insertNews(url, title) {
        window.parent.postMessage({
            mceAction: 'insertContent',
            // On ajoute le title et on retire la class
            content: `<a href="${url}" title="${title}">${title}</a>`
        }, '*');

        window.parent.postMessage({
            mceAction: 'close'
        }, '*');
    }
    {/literal}
</script>
</body>
</html>