<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <title>{block name='head:title'}Tableau de bord{/block} | Magix CMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="templates/css/global.css">
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
        <footer class="py-3 bg-body-tertiary border-top flex-shrink-0">
            <div class="container-fluid text-center">
                {* On assigne l'année en cours à une variable pour que ce soit propre *}
                {assign var="current_year" value=$smarty.now|date_format:"%Y"}

                <p class="mb-0 text-muted small">
                    <i class="bi bi-copyright"></i> 2008{if $current_year != '2008'} - {$current_year}{/if}
                    <a href="https://www.magix-cms.com/" class="text-muted text-decoration-none" target="_blank">Magix CMS</a> &mdash; Tous droits réservés.
                </p>
            </div>
        </footer>
    {/block}
</div>
<div id="sidebar-backdrop" class="sidebar-backdrop"></div>
{include file="components/modal-delete.tpl"}
<div id="magix-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;"></div>
<script src="templates/js/vendor/bootstrap.bundle.min.js"></script>
<script src="templates/js/vendor/Sortable.min.js"></script>

{block name="javascripts"}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('aside');
            const backdrop = document.getElementById('sidebar-backdrop');

            if (toggleBtn && sidebar) {
                // Au clic sur le bouton Hamburger
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('is-toggled');

                    // On gère le fond sombre uniquement si on est sur mobile (fenêtre < 992px)
                    if (window.innerWidth < 992) {
                        backdrop.classList.toggle('show');
                    }
                });

                // Au clic sur le fond noir, on ferme tout
                if (backdrop) {
                    backdrop.addEventListener('click', function() {
                        sidebar.classList.remove('is-toggled');
                        backdrop.classList.remove('show');
                    });
                }

                // Sécurité : Si on redimensionne la fenêtre (passage portrait/paysage), on nettoie
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 992) {
                        backdrop.classList.remove('show');
                        // Optionnel : sidebar.classList.remove('is-toggled'); si vous voulez réinitialiser
                    }
                });
            }
        });
    </script>
    {* @todo exemple pour les formulaires*}
    <script src="templates/js/MagixForms.min.js"></script>
    <script src="templates/js/MagixToast.min.js"></script>
    <script src="templates/js/MagixTabManager.min.js"></script>
    <script src="templates/js/MagixTableSorter.min.js"></script>
    <script src="templates/js/MagixTableDeleter.min.js"></script>
    <script src="templates/js/MagixTableSelection.min.js"></script>
    <script>
        // Variables globales pour MagixCMS 4
        const iso = 'fr'; // Ex: 'fr'
        const baseadmin = "{$smarty.const.BASEADMIN}"; // Ton dossier admin (ex: 'admin')
        const contentCSS = ''; // Chemin vers le CSS de ton thème frontend

        // Si tu utilises l'IA Gemini
        window.MagixCMS = {
            ai_enabled: {if $ai_enabled}true{else}false{/if}
        };
    </script>

    <script src="templates/js/vendor/tiny_mce.7.6.1/tinymce.min.js"></script>
    <script src="templates/js/editor.min.js"></script>
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