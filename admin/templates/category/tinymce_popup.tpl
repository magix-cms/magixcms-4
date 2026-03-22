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
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <h5 class="mb-3 text-primary">
        <i class="bi bi-folder2-open me-2"></i> Sélectionner une catégorie
        <span class="badge bg-secondary ms-2">{$iso_lang}</span>
    </h5>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">Arborescence du catalogue</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
                </thead>
                <tbody>
                {if isset($categoriesList) && $categoriesList|@count > 0}
                    {foreach $categoriesList as $cat}
                        <tr onclick="insertCategory('{$cat.url|escape:'javascript'}', '{$cat.title|escape:'javascript'}')">
                            <td class="ps-3">
                                {* Indentation dynamique *}
                                {if $cat.depth > 0}
                                    <span style="margin-left: {$cat.depth * 20}px;" class="subpage-indicator">&#8627;</span>
                                    <span class="text-dark">{$cat.title}</span>
                                {else}
                                    <span class="fw-bold text-dark">{$cat.title}</span>
                                {/if}
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-link-45deg"></i> Insérer
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
        </div>
    </div>
</div>

<script>
    {literal}
    function insertCategory(url, title) {
        window.parent.postMessage({
            mceAction: 'insertContent',
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