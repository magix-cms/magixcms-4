<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélecteur de produits</title>
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
        <i class="bi bi-box-seam me-2"></i> Sélectionner un produit
        <span class="badge bg-secondary ms-2">{$iso_lang}</span>
    </h5>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">Nom du produit</th>
                    <th>Catégorie par défaut</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
                </thead>
                <tbody>
                {if isset($productList) && $productList|@count > 0}
                    {foreach $productList as $product}
                        <tr onclick="insertProduct('{$product.url|escape:'javascript'}', '{$product.title|escape:'javascript'}')">
                            <td class="ps-3 fw-bold text-dark">{$product.title}</td>
                            <td class="text-muted small"><i class="bi bi-folder2 me-1"></i> {$product.category}</td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-link-45deg"></i> Insérer
                                </button>
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">Aucun produit disponible.</td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    {literal}
    function insertProduct(url, title) {
        window.parent.postMessage({
            mceAction: 'insertContent',
            // 🟢 Lien ultra-propre avec le title pour le SEO
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