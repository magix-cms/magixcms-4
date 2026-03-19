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
    {if isset($canonical_url) && $canonical_url}
        <link rel="canonical" href="{$canonical_url}" />
    {/if}
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
{include file="layout/header.tpl"}
{* 3. LE CORPS : C'est ici que le contenu de la page enfant va s'insérer *}
{block name="main:before"}{/block}
{block name="main"}
    <main class="flex-grow-1">
        <div class="container py-2">
        {block name='article'}
            <article>
                {block name='article:content'}{/block}
            </article>
        {/block}
        </div>
    </main>
{/block}
{block name="main:after"}{/block}
{include file="layout/footer.tpl"}
{include file="layout/footbar.tpl"}

{* 1. On définit les JS globaux du parent *}
{$global_js = [
'defer' => ['vendor/bootstrap.bundle','vendor/glightbox', 'vendor/masonry.pkgd', 'vendor/imagesloaded.pkgd'],
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

        // --- 1. Gestion de la Footbar ---
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

        // --- 2. Initialisation de Masonry (Footer) ---
        const footerGrid = document.querySelector('#footer-masonry');
        if (footerGrid) {
            // On attend que le logo (et autres images) soient chargés
            imagesLoaded(footerGrid, function() {
                new Masonry(footerGrid, {
                    // Cible bien vos classes de widget (col-lg-4, ou col-12, etc.)
                    // L'astuce c'est de cibler un préfixe commun ou juste de prendre les enfants directs
                    itemSelector: '#footer-masonry > div',
                    percentPosition: true
                });
            });
        }

    });
</script>
{if isset($admin_maintenance_warning) && $admin_maintenance_warning}
    <div class="position-fixed start-0 bottom-0 p-3" style="z-index: 1090; margin-bottom: 80px;">
        <div class="alert alert-warning alert-dismissible fade show shadow-lg mb-0 py-2 pe-5" role="alert" style="border-radius: 50rem;">
            <i class="bi bi-exclamation-triangle-fill text-danger me-2 align-middle"></i>
            <span class="fw-bold small align-middle">Mode Maintenance</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer" style="top: 50%; transform: translateY(-50%); padding: 0.75rem; right: 0.5rem;"></button>
        </div>
    </div>
{/if}
<div id="magix-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>
</body>
</html>