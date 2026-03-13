<!DOCTYPE html>
<html lang="{$current_lang.iso_lang|lower|default:'fr'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$seo_title}</title>
    <meta name="description" content="{$seo_desc}">
    {block name="head:structured_data"}{/block}
    {include file="components/lang_head.tpl"}
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{$skin_url}/css/glightbox.min.css">
    {include file="components/google_fonts.tpl" fonts=[
    'Roboto' => '300,400,400italic,700',
    'Montserrat' => '700,900'
    ]}
    {* 1. ON DÉFINIT LES CSS GLOBAUX (chargés sur toutes les pages) *}
    {$global_css = ["global"]}

    {* 2. ON APPELLE LE BLOC POUR L'ENFANT *}
    {* On change le nom de la variable de l'enfant pour éviter les conflits *}
    {block name="styleSheet" nocache}{/block}
    {include file="components/css.tpl"}
</head>
<body class="bg-light">
{*<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand" href="/">Magix CMS</a>
            {include file="components/language_switcher.tpl"}
        </div>
    </nav>
</header>*}
{include file="layout/header.tpl"}
{* 3. LE CORPS : C'est ici que le contenu de la page enfant va s'insérer *}
{block name="article"}{/block}
{include file="layout/footbar.tpl"}
{*, 'navigation', 'main-app'*}
{$global_js = [
'defer' => ['vendor/bootstrap.bundle','vendor/glightbox'],
'async' => [],
'normal' => []
]}

{* L'enfant remplit $page_js ici *}
{block name="javascript" nocache}{/block}

{include file="components/js.tpl"}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const footbar = document.getElementById('footbar');

        if (footbar) {
            window.addEventListener('scroll', function() {
                // Si on a scrollé de plus de 100 pixels vers le bas
                if (window.scrollY > 100) {
                    footbar.classList.remove('at-top');
                } else {
                    footbar.classList.add('at-top');
                }
            });
        }
    });
</script>
</body>
</html>