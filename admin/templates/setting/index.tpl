{extends file="layout.tpl"}

{block name='head:title'}{#setting#}{/block}
{block name='body:id'}setting{/block}

{block name='article'}
    {* --- DÉFINITION DES VARIABLES (Listes déroulantes de l'ancienne version) --- *}
    {assign var="collectionformCache" value=[
    "none"=>"None",
    "files"=>"Files",
    "apc"=>"APC",
    "memcached"=>"Memcached",
    "redis"=>"Redis"
    ]}
    {assign var="collectionformMode" value=[
    "dev"=>"Developpement",
    "prod"=>"Production"
    ]}
    {assign var="collectionformRobots" value=[
    "noindex,nofollow"=>"No index",
    "index,follow,all"=>"Index"
    ]}
    {assign var="collectionformPrice" value=[
    "tinc"=>"taxe incluse",
    "texc"=>"taxe non comprise"
    ]}

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-gear me-2"></i> {#setting#}
        </h1>
    </div>

        <div class="card shadow-sm border-0">
            <header class="card-header bg-white p-0 border-bottom-0">
                <ul class="nav nav-tabs nav-fill" role="tablist" id="settingTab">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-3 fw-bold" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                            {#general_setting#}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 fw-bold" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab" aria-controls="seo" aria-selected="false">
                            {#seo_setting#}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 fw-bold" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced" type="button" role="tab" aria-controls="advanced" aria-selected="false">
                            {#advanced_setting#}
                        </button>
                    </li>
                </ul>
            </header>

            <div class="card-body p-4">
                {* On englobe tous les onglets dans un seul formulaire compatible MagixForms *}
                <form id="edit_setting_form" action="index.php?controller=Setting&action=save" method="post" class="validate_form edit_form">
                    <input type="hidden" name="hashtoken" value="{$hashtoken}">

                    <div class="tab-content">

                        {* ==========================================================
                           ONGLET 1 : GÉNÉRAL (Catalogue & News)
                           ========================================================== *}
                        <div role="tabpanel" class="tab-pane fade show active" id="general" aria-labelledby="general-tab">

                            <fieldset class="mb-4">
                                <legend class="h5 text-primary border-bottom pb-2">{#catalog_setting#}</legend>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="product_per_page">{#product_per_page#}</label>
                                        <input type="number" min="0" id="product_per_page" name="settings[product_per_page]" class="form-control" value="{$settings.product_per_page.value|default:''}" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="vat_rate">{#vat_rate#}</label>
                                        <div class="input-group">
                                            <input type="text" id="vat_rate" name="settings[vat_rate]" class="form-control" value="{$settings.vat_rate.value|default:''}" />
                                            <span class="input-group-text"><i class="bi bi-percent"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="price_display">{#price_display#}</label>
                                        <select name="settings[price_display]" id="price_display" class="form-select" required>
                                            {foreach $collectionformPrice as $key => $val}
                                                <option value="{$key}" {if ($settings.price_display.value|default:'') == $key} selected{/if}>{$val}</option>
                                            {/foreach}
                                        </select>
                                    </div>

                                    {* 🟢 NOUVELLE OPTION : Afficher tous les produits à la racine *}
                                    <div class="col-12 mt-4">
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" role="switch" id="product_catalog" name="settings[product_catalog]" value="1" {if ($settings.product_catalog.value|default:'0') eq '1'} checked{/if} />
                                            <label class="form-check-label fs-6 text-dark" for="product_catalog">
                                                {#product_catalog_setting#|default:'Afficher tous les produits à la racine du catalogue'}
                                                <i class="bi bi-question-circle text-info ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="{#product_catalog_warning#|default:'Au lieu d\'afficher uniquement les catégories mères, l\'accueil du catalogue affichera la liste de tous les produits.'}"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend class="h5 text-primary border-bottom pb-2">{#news_setting#}</legend>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="news_per_page">{#news_per_page#}</label>
                                        <input type="number" min="0" id="news_per_page" name="settings[news_per_page]" class="form-control" value="{$settings.news_per_page.value|default:''}" />
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset class="mt-4">
                                <legend class="h5 text-primary border-bottom pb-2">{#placeholder_setting#|default:'Images de substitution (Holder)'}</legend>
                                <div class="row g-3">

                                    {* 1. Couleur de fond (Hexadécimal) *}
                                    <div class="col-md-6">
                                        <label class="form-label" for="holder_bgcolor">{#holder_bgcolor#|default:'Couleur de fond'}</label>
                                        <div class="d-flex align-items-center gap-2">
                                            {* 🟢 Le sélecteur visuel.
                                               Note: On lui retire toute validation stricte pour ne pas gêner MagixForms *}
                                            <input type="color" id="holder_bgcolor_picker" class="form-control form-control-color border-1 p-0" style="width: 38px; height: 38px;" value="{$settings.holder_bgcolor.value|default:'#ffffff'}" title="Choisir une couleur" onchange="document.getElementById('holder_bgcolor').value = this.value">

                                            {* 🟢 Champ texte (Hex).
                                               Retrait de l'attribut "pattern" qui bloquait MagixForms s'il était vide ou mal formaté au chargement *}
                                            <input type="text" id="holder_bgcolor" name="settings[holder_bgcolor]" class="form-control text-uppercase" placeholder="#FFFFFF" value="{$settings.holder_bgcolor.value|default:'#ffffff'}" oninput="document.getElementById('holder_bgcolor_picker').value = this.value">
                                        </div>
                                        <div class="form-text">Code couleur hexadécimal (ex: #FFFFFF)</div>
                                    </div>

                                    {* 2. Pourcentage du logo *}
                                    <div class="col-md-6">
                                        <label class="form-label" for="logo_percent">{#logo_percent#|default:'Taille du logo intégré'}</label>
                                        <div class="input-group">
                                            {* 🟢 On s'assure qu'une valeur par défaut valide (50) est présente même si la DB est vide *}
                                            <input type="number" min="1" max="100" id="logo_percent" name="settings[logo_percent]" class="form-control" value="{$settings.logo_percent.value|default:'50'}" />
                                            <span class="input-group-text"><i class="bi bi-percent"></i></span>
                                        </div>
                                        <div class="form-text">Espace occupé par le logo sur l'image (1 à 100%)</div>
                                    </div>

                                </div>
                            </fieldset>
                        </div>

                        {* ==========================================================
                           ONGLET 2 : SEO
                           ========================================================== *}
                        <div role="tabpanel" class="tab-pane fade" id="seo" aria-labelledby="seo-tab">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="robots">{#robots_setting#}</label>
                                    <select name="settings[robots]" id="robots" class="form-select" required>
                                        {foreach $collectionformRobots as $key => $val}
                                            <option value="{$key}" {if ($settings.robots.value|default:'') == $key} selected{/if}>{$val}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="analytics">{#analytics_setting#}</label>
                                    <input type="text" id="analytics" name="settings[analytics]" class="form-control" placeholder="{#ph_analytics_setting#|default:'UA-XXXXX'}" value="{$settings.analytics.value|default:''}" />
                                </div>
                            </div>
                        </div>

                        {* ==========================================================
                           ONGLET 3 : AVANCÉ
                           ========================================================== *}
                        <div role="tabpanel" class="tab-pane fade" id="advanced" aria-labelledby="advanced-tab">

                            <fieldset class="mb-4">
                                <legend class="h5 text-primary border-bottom pb-2">{#tinymce_setting#}</legend>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label" for="content_css">{#tinymce_css#}</label>
                                        <input type="text" id="content_css" name="settings[content_css]" class="form-control" placeholder="{#ph_tinymce_css#|default:''}" value="{$settings.content_css.value|default:''}" />
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="mb-4">
                                <legend class="h5 text-primary border-bottom pb-2">{#performance_setting#}</legend>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" role="switch" id="concat" name="settings[concat]" value="1" {if ($settings.concat.value|default:'0') eq '1'} checked{/if} />
                                            <label class="form-check-label fs-6" for="concat">
                                                {#concat_setting#}
                                                <i class="bi bi-question-circle text-warning ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="{#concat_warning#|default:'Attention à la minification'}"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="mode">
                                            {#mode_setting#}
                                            <i class="bi bi-question-circle text-info ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="{#mode_warning#|default:'Mode dev affiche les erreurs'}"></i>
                                        </label>
                                        <select name="settings[mode]" id="mode" class="form-select" required>
                                            {foreach $collectionformMode as $key => $val}
                                                <option value="{$key}" {if ($settings.mode.value|default:'') == $key} selected{/if}>{$val}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="cache">
                                            {#cache_setting#}
                                            <i class="bi bi-question-circle text-info ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="{#cache_warning#|default:'Videz le cache après modification'}"></i>
                                        </label>
                                        <select name="settings[cache]" id="cache" class="form-select" required>
                                            {foreach $collectionformCache as $key => $val}
                                                <option value="{$key}" {if ($settings.cache.value|default:'') == $key} selected{/if}>{$val}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="mb-4">
                                <legend class="h5 text-primary border-bottom pb-2">{#protocol_setting#}</legend>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" role="switch" id="ssl" name="settings[ssl]" value="1" {if ($settings.ssl.value|default:'0') eq '1'} checked{/if} />
                                            <label class="form-check-label fs-6" for="ssl">
                                                {#ssl_setting#}
                                                <i class="bi bi-question-circle text-warning ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="right" data-bs-content="{#ssl_warning#|default:'Force le HTTPS'}"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" role="switch" id="http2" name="settings[http2]" value="1" {if ($settings.http2.value|default:'0') eq '1'} checked{/if} />
                                            <label class="form-check-label fs-6" for="http2">
                                                {#http2_setting#}
                                                <i class="bi bi-question-circle text-warning ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="right" data-bs-content="{#http2_warning#|default:'Nécessite le support HTTP2'}"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="mb-4">
                                <legend class="h5 text-primary border-bottom pb-2">{#additionnal_feature_setting#}</legend>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" role="switch" id="amp" name="settings[amp]" value="1" {if ($settings.amp.value|default:'0') eq '1'} checked{/if} disabled />
                                            <label class="form-check-label fs-6 text-muted" for="amp">
                                                {#amp_setting#}
                                                <i class="bi bi-question-circle text-warning ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="right" data-bs-content="{#amp_warning#|default:'Actuellement inactif'}"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch fs-5">
                                            <input class="form-check-input" type="checkbox" role="switch" id="service_worker" name="settings[service_worker]" value="1" {if ($settings.service_worker.value|default:'0') eq '1'} checked{/if} {if ($settings.ssl.value|default:'0') !== '1'} disabled{/if} />
                                            <label class="form-check-label fs-6 {if ($settings.ssl.value|default:'0') !== '1'}text-muted{/if}" for="service_worker">
                                                {#service_worker_setting#}
                                                <i class="bi bi-question-circle text-warning ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="right" data-bs-content="{#service_worker_warning#|default:'Nécessite le SSL actif'}"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="mb-4">
                                <legend class="h5 text-primary border-bottom pb-2">{#maintenance_setting#}</legend>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" role="switch" id="maintenance" name="settings[maintenance]" value="1" {if ($settings.maintenance.value|default:'0') eq '1'} checked{/if} />
                                    <label class="form-check-label fs-6 text-danger" for="maintenance">
                                        {#enable_maintenance#}
                                        <i class="bi bi-question-circle text-warning ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="right" data-bs-content="{#maintenance_warning#|default:'Bloque les visiteurs'}"></i>
                                    </label>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend class="h5 text-primary border-bottom pb-2">{#geminiai_setting#|default:'Assistant IA'}</legend>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" role="switch" id="geminiai" name="settings[geminiai]" value="1" {if ($settings.geminiai.value|default:'0') eq '1'} checked{/if} />
                                    <label class="form-check-label fs-6 text-primary" for="geminiai">
                                        {#enable_geminiai#|default:'Activer Gemini AI'}
                                        <i class="bi bi-question-circle text-warning ms-1" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="right" data-bs-content="{#geminiai_warning#|default:'Active les aides IA'}"></i>
                                    </label>
                                </div>
                            </fieldset>

                        </div>

                    </div>

                    {* BOUTON SAUVEGARDER GLOBAL *}
                    <hr class="my-4">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary px-5" type="submit" name="action" value="save">
                            <i class="bi bi-save me-2"></i> {#save#}
                        </button>
                    </div>

                </form>
            </div>
        </div>
{/block}

{block name="javascripts" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation des popovers de Bootstrap 5 pour les bulles d'aide
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl)
            });

            // Dynamique : Désactiver le Service Worker si SSL est décoché
            const sslSwitch = document.getElementById('ssl');
            const swSwitch = document.getElementById('service_worker');

            if (sslSwitch && swSwitch) {
                sslSwitch.addEventListener('change', function() {
                    if (!this.checked) {
                        swSwitch.checked = false;
                        swSwitch.disabled = true;
                    } else {
                        swSwitch.disabled = false;
                    }
                });
            }
        });
    </script>
{/block}