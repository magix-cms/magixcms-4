<!DOCTYPE html>
<html lang="{$current_lang.iso_lang|lower|default:'fr'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$seo_title}</title>
    <meta name="description" content="{$seo_desc}">

    {* On utilise {$skin_url} pour charger les fichiers du thème *}
    <link href="{$skin_url}/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5 text-center">
    <h1 class="display-4 fw-bold text-primary mb-3">
        {$home_data.title_page|default:$seo_title}
    </h1>

    {* 1. HOOK HAUT : Idéal pour un Slider *}
    {hook name="displayHomeTop"}

    {if isset($home_data.content_page) && $home_data.content_page != ''}
        <div class="text-start bg-white p-4 shadow-sm rounded mt-4">
            {$home_data.content_page nofilter}
        </div>
    {/if}

    {* 2. HOOK BAS : Idéal pour "Nos derniers produits" ou "Dernières actus" *}
    {hook name="displayHomeBottom"}

</div>

</body>
</html>