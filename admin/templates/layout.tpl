<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <title>{block name='head:title'}Tableau de bord{/block} | Magix CMS</title>
    <meta name="robots" content="no-index">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{$site_url}/{$baseadmin}/templates/img/favicon.png" />
    <!--[if IE]>
    <link rel="shortcut icon" type="image/x-icon" href="{$site_url}/{$baseadmin}/templates/img/favicon.ico" />
    <![endif]-->
    <link rel="stylesheet" href="{$site_url}/{$baseadmin}/templates/css/global.css">
    <link rel="stylesheet" href="{$site_url}/{$baseadmin}/templates/css/glightbox.min.css">
    {*<link rel="stylesheet" href="templates/css/elfinder-flat.css">*}
    {block name="stylesheets"}{/block}
</head>
<body id="{block name='body:id'}layout{/block}" class="d-flex flex-nowrap overflow-hidden">

{block name="aside"}
    {include file='section/menu/sidebar.tpl'}
{/block}

<div class="d-flex flex-column flex-grow-1 w-100" style="height: 100vh;">

    {block name="header"}
        {include file='section/menu/header.tpl'}
    {/block}

    {block name="main"}
        <main class="p-4 overflow-auto bg-body-secondary flex-grow-1">
            {block name='article'}
                <div class="card shadow-sm">
                    {block name='article:content'}
                        <div class="card-body">
                            <h1>Test</h1>
                            <p>Mon test</p>
                        </div>
                    {/block}
                </div>
            {/block}
            {block name="article:after"}{/block}
        </main>
    {/block}

    {block name="footer"}
        {include file='section/footer.tpl'}
    {/block}
</div>
<div id="sidebar-backdrop" class="sidebar-backdrop"></div>
{include file="components/modal-delete.tpl"}
<div id="magix-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;"></div>
<div id="modal-container"></div>

{block name="javascripts"}
    <script src="{$site_url}/{$baseadmin}/templates/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="{$site_url}/{$baseadmin}/templates/js/vendor/Sortable.min.js"></script>
    <script src="{$site_url}/{$baseadmin}/templates/js/vendor/glightbox.min.js"></script>
    <script src="{$site_url}/{$baseadmin}/templates/js/MagixUITools.min.js?v={$smarty.now}"></script>
    {*<script src="{$site_url}/{$baseadmin}/templates/js/MagixUITools.min.js?v={$mc_settings.version.value|default:'4.0.0'}"></script>*}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initialisation de la boite à outils UI
            const uiTools = new MagixUITools({
                sidebarId: 'aside',           // ID de votre sidebar
                toggleId: 'sidebarToggle',    // ID de votre bouton hamburger
                backdropId: 'sidebar-backdrop' // ID du div fond noir (si existant)
            });

            uiTools.init();
        });
    </script>
    {* @todo exemple pour les formulaires*}
    <script src="{$site_url}/{$baseadmin}/templates/js/MagixForms.min.js"></script>
    <script src="{$site_url}/{$baseadmin}/templates/js/MagixToast.min.js"></script>
    <script src="{$site_url}/{$baseadmin}/templates/js/MagixTabManager.min.js"></script>
    <script src="{$site_url}/{$baseadmin}/templates/js/MagixTableSorter.min.js"></script>
    <script src="{$site_url}/{$baseadmin}/templates/js/MagixTableDeleter.min.js"></script>
    <script src="{$site_url}/{$baseadmin}/templates/js/MagixTableSelection.min.js"></script>
    <script src="{$site_url}/{$baseadmin}/templates/js/MagixAjaxManager.min.js"></script>
    <script>
        // Variables globales pour MagixCMS 4
        const iso = 'fr'; // Ex: 'fr'
        const site_url = "{$site_url}/{$baseadmin}/"
        const baseadmin = "{$baseadmin}"; // Ton dossier admin (ex: 'admin')
        const contentCSS = ['templates/css/tinymce.css']; // Chemin vers le CSS de ton thème frontend

        // Si tu utilises l'IA Gemini
        window.MagixCMS = {
            ai_enabled: {if $ai_enabled}true{else}false{/if}
        };
    </script>
    {block name="editor"}
    <script src="{$site_url}/{$baseadmin}/templates/js/vendor/tiny_mce.7.6.1/tinymce.min.js"></script>
    <script src="{$site_url}/{$baseadmin}/templates/js/editor.min.js"></script>
    {/block}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // On récupère le contrôleur actuel depuis Smarty ou l'URL
            const currentController = '{$smarty.get.controller|default:"dashboard"}';

            // Instanciation de la classe magique
            window.MagixAppForms = new MagixForms(currentController);
        });
        document.addEventListener('DOMContentLoaded', function() {
            // 1. On cible tous les éléments qui ont l'attribut data-bs-toggle="tooltip"
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');

            // 2. On les initialise un par un avec la classe native de Bootstrap
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        });
    </script>
{/block}
</body>
</html>