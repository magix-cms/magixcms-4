{*{extends file="layout.tpl"}

{block name='article'}
    <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
        <div class="card-body p-4">
            <h1 class="h3 mb-1">Tableau de bord</h1>
            <p class="opacity-75 mb-0">Ravi de vous revoir ! Voici un aperçu de l'activité aujourd'hui.</p>
            <span class="badge-magix status-success">
                <i class="ico ico-check"></i> Payé
            </span>
            <span class="badge-magix status-warning">
                <i class="ico ico-schedule"></i> En attente
            </span>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Ventes du jour</h6>
                        <p class="h3 fw-bold mb-0">1 240 €</p>
                    </div>
                    <div class="ms-3">
                        <i class="ico ico-shopping-cart fs-1 text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Nouveaux clients</h6>
                        <p class="h3 fw-bold mb-0">+ 12</p>
                    </div>
                    <div class="ms-3">
                        <i class="ico ico-verified_user fs-1 text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted small text-uppercase fw-bold mb-1">Messages</h6>
                        <p class="h3 fw-bold mb-0">4</p>
                    </div>
                    <div class="ms-3">
                        <i class="ico ico-email fs-1 text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-uppercase small">Pages CMS Actives</h6>
                    <h2 class="display-6 fw-bold">42</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6 class="text-uppercase small">Produits en Stock</h6>
                    <h2 class="display-6 fw-bold">128</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <h6 class="text-uppercase small">Langues Actives</h6>
                    <h2 class="display-6 fw-bold">{$total_langs}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6 class="text-uppercase small">Fichiers Médias</h6>
                    <h2 class="display-6 fw-bold">1.2 Go</h2>
                </div>
            </div>
        </div>
    </div>
{/block}

{*{extends file="layout.tpl"}

{block name='article'}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Tableau de bord</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">MagixCMS</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Nouveau Produit</span>
        </button>
    </div>

    <div class="row g-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Commandes</h6>
                            <h4 class="mb-0 fw-bold">145</h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                            <i class="bi bi-cart-check fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 small">
                        <span class="text-success"><i class="bi bi-arrow-up"></i> 12%</span>
                        <span class="text-muted ms-1">vs mois dernier</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Revenus</h6>
                            <h4 class="mb-0 fw-bold">8 450 €</h4>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                            <i class="bi bi-currency-euro fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 small">
                        <span class="text-success"><i class="bi bi-arrow-up"></i> 5.4%</span>
                        <span class="text-muted ms-1">vs mois dernier</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Clients</h6>
                            <h4 class="mb-0 fw-bold">1 204</h4>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 small">
                        <span class="text-danger"><i class="bi bi-arrow-down"></i> 2%</span>
                        <span class="text-muted ms-1">vs mois dernier</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Performance</h6>
                            <h4 class="mb-0 fw-bold">98%</h4>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-3">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 small">
                        <span class="text-muted">Stable</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
{/block}

{*<h1>Tableau de bord - Magix CMS 4</h1>

<div class="debug-section">
    <h3>🌐 Configuration Langue (via BaseController + Cache)</h3>
    <pre>{$default_lang|print_r}</pre>
</div>

<div class="stats-section">
    <h3>📊 Informations Système</h3>
    <ul>
        <li>Version CMS : {$stats.version_cms}</li>
        <li>Version PHP : {$stats.php_version}</li>
        <li>Serveur : {$stats.server}</li>
    </ul>
</div>

<div class="test-cache">
    <h3>⚡ Test CacheTool</h3>
    <p>Langue active : <strong>{$default_lang.iso_lang|upper}</strong> (ID: {$default_lang.id_lang})</p>
</div>*}
{extends file="layout.tpl"}
{block name="stylesheets"}
    <link rel="stylesheet" href="templates/css/dashboard.css">
{/block}
{block name='article'}
    <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
        <div class="card-body p-4">
            <h1 class="h3 mb-1">Tableau de bord</h1>
            <p class="opacity-75 mb-0">Ravi de vous revoir ! Voici un aperçu de l'activité aujourd'hui.</p>
        </div>
    </div>

    {* --- ZONE 1 : COMPTEURS TOP --- *}
    <div class="row g-3 mb-4" id="dashboard-top-zone">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small">Pages CMS Actives</h6>
                    {* On remplace 42 par la variable dynamique *}
                    <h2 class="display-6 fw-bold">{$total_pages}</h2>
                </div>
            </div>
        </div>

        {* ... le reste des compteurs ... *}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small">Langues Actives</h6>
                    <h2 class="display-6 fw-bold">{$total_langs}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-dark h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small">Produits</h6>
                    <h2 class="display-6 fw-bold">{$total_products}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body">
                    <h6 class="text-uppercase small">Fichiers Upload</h6>
                    <h2 class="display-6 fw-bold">{$total_media_size}</h2>
                </div>
            </div>
        </div>
    </div>

    {* --- ZONE 2 : WIDGETS PRINCIPAUX --- *}
    <div class="row g-4" id="dashboard-main-zone">

        {assign var="displayed_widgets" value=[]}

        {* 1. Widgets triés (AJOUT DE nofilter) *}
        {if isset($saved_order) && !empty($saved_order)}
            {foreach $saved_order as $pluginName => $position}
                {if isset($widgets_main[$pluginName])}
                    <div class="col-12 col-xl-6 widget-item" data-widget="{$pluginName}">
                        {* ICI : On ajoute nofilter pour interpréter le HTML *}
                        {$widgets_main[$pluginName] nofilter}
                    </div>
                    {$displayed_widgets[$pluginName] = true}
                {/if}
            {/foreach}
        {/if}

        {* 2. Nouveaux widgets (AJOUT DE nofilter) *}
        {if isset($widgets_main) && !empty($widgets_main)}
            {foreach $widgets_main as $pluginName => $html}
                {if !isset($displayed_widgets[$pluginName])}
                    <div class="col-12 col-xl-6 widget-item" data-widget="{$pluginName}">
                        {* ICI : On ajoute nofilter pour interpréter le HTML *}
                        {$html nofilter}
                    </div>
                {/if}
            {/foreach}
        {/if}
        {*<div class="col-12 col-xl-6 widget-item" data-widget="TestWidget">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3" style="cursor: grab;">
                    <h6 class="m-0 fw-bold text-muted">Widget de Test (Statique)</h6>
                </div>
                <div class="card-body">
                    <p>Ce widget sert à tester le glisser-déposer.</p>
                </div>
            </div>
        </div>*}
    </div>
{/block}

{block name="javascripts" append}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mainZone = document.getElementById('dashboard-main-zone');
            if (mainZone) {
                new Sortable(mainZone, {
                    animation: 150,
                    handle: '.card-header', // La poignée
                    draggable: '.widget-item', // IMPORTANT : précise ce qui peut être déplacé
                    ghostClass: 'sortable-ghost', // La classe appliquée au "fantôme" (emplacement vide)
                    dragClass: 'sortable-drag', // La classe appliquée à l'élément qu'on tient
                    forceFallback: true, // Améliore la compatibilité avec Bootstrap (force le drag custom)
                    fallbackTolerance: 3, // Évite les déclenchements accidentels au clic
                    onEnd: function () {
                        // On récupère le nouvel ordre
                        const items = mainZone.querySelectorAll('.widget-item');
                        const order = Array.from(items).map((el, index) => {
                            return { name: el.dataset.widget, pos: index };
                        });

                        // Envoi au contrôleur
                        fetch('index.php?controller=Dashboard&action=saveOrder', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ order: order })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status && typeof MagixToast !== 'undefined') {
                                    MagixToast.success(data.message);
                                }
                            })
                            .catch(error => console.error('Erreur sauvegarde dashboard:', error));
                    }
                });
            }
        });
    </script>
{/block}