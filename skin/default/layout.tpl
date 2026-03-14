<!DOCTYPE html>
<html lang="{$current_lang.iso_lang|lower|default:'fr'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$seo_title}</title>
    <meta name="description" content="{$seo_desc}">
    {block name="head:structured_data"}{/block}
    {include file="components/lang_head.tpl"}
    {include file="components/opengraph.tpl"}
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
{block name="main:before"}{/block}
{block name="main"}
    {block name="article"}{/block}
{/block}
{block name="main:after"}{/block}
{include file="layout/footbar.tpl"}

{* 1. On définit les JS globaux du parent *}
{$global_js = [
'defer' => ['vendor/bootstrap.bundle','vendor/glightbox'],
'async' => [],
'normal' => []
]}

{* 2. On exécute un bloc MUET (sans affichage) pour que l'enfant puisse définir $page_js *}
{block name="javascript_data" nocache}{/block}

{* 3. MAINTENANT on génère les balises <script src="..."> (Parent + Enfant) *}
{include file="components/js.tpl"}

{* 4. ENFIN on exécute les scripts inline de l'enfant *}
{block name="javascript" nocache}{/block}

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const footbar = document.getElementById('footbar');
        if (footbar) {
            window.addEventListener('scroll', function() {
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