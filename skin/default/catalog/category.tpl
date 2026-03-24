{extends file="layout.tpl"}

{* --- SEO --- *}
{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{* --- CSS (Chargé uniquement si une galerie existe) --- *}
{block name="styleSheet" append nocache}
    {$page_css = ["catalog","vendor/splide", "gallery"] scope="parent"}
    {*{if isset($category.gallery) && $category.gallery|count > 0}
        {$page_css = ["vendor/splide", "gallery"] scope="parent"}
    {/if}*}
{/block}

{block name="head:structured_data"}
    {* 1. L'identité de la catégorie (générée par CategoryPresenter) *}
    {$category.json_ld|default:'' nofilter}

    {* 2. La liste des produits qu'elle contient (générée par le SeoHelper du Controller) *}
    {$json_ld|default:'' nofilter}
{/block}

{* 🟢 Utilisation de "article" pour ÉCRASER la balise <article> du layout (car ce n'est pas un article) *}
{block name="article"}

    {* --- 1. EN-TÊTE DE LA CATÉGORIE --- *}
    <header class="category-header mb-5">

        {* --- FIL D'ARIANE --- *}
        {$breadcrumbs = [
            ['url' => "{$base_url}{$current_lang.iso_lang}/catalog/", 'label' => 'Catalogue'],
            ['label' => $category.name]
        ]}
        {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

        <div class="row mt-3">
            <div class="col-12 text-center text-lg-start">
                <h1 class="display-4 fw-bold text-primary mb-3">{$category.name}</h1>
                {if $category.resume}
                    <p class="lead text-muted">{$category.resume}</p>
                {/if}
            </div>
        </div>
    </header>

    {* --- 2. CONTENU TEXTE & GALERIE --- *}
    {if $category.content || (isset($category.gallery) && $category.gallery|count > 0)}
        <section class="category-body mb-5">
            <div class="row">
                <div class="col-lg-{if isset($category.gallery) && $category.gallery|count > 0}6{else}12{/if} mb-4">
                    <div class="content-formatted">
                        {$category.content|default:'' nofilter}
                    </div>
                </div>

                {* Galerie de la catégorie (identique au module Pages) *}
                {if isset($category.gallery) && $category.gallery|count > 0}
                    <div class="col-lg-6">
                        <div class="c-gallery c-gallery--category">
                            <div class="c-gallery__main shadow-sm rounded mb-3">
                                {foreach $category.gallery as $index => $image}
                                    <div class="gallery-main-item {if $index == 0}is-active{/if}" id="main-image-{$index}">
                                        {$zoom_url = $image.original.src|default:$image.default.src}
                                        <a href="{$zoom_url}" class="glightbox" data-gallery="category" data-title="{$image.title}">
                                            {include file="components/img.tpl" img=$image class="img-fluid w-100"}
                                        </a>
                                    </div>
                                {/foreach}
                            </div>
                            {if $category.gallery|count > 1}
                                <div id="thumbnail-slider" class="splide c-gallery__thumbs mt-3">
                                    <div class="splide__track">
                                        <ul class="splide__list">
                                            {foreach $category.gallery as $index => $image}
                                                <li class="splide__slide">
                                                    <div class="thumb-wrapper p-1">
                                                        {include file="components/img.tpl" img=$image class="img-fluid rounded" size="small"}
                                                    </div>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                {/if}
            </div>
        </section>
    {/if}

    {* --- 3. SOUS-CATÉGORIES (Rayons enfants) --- *}
    {if isset($category.subdata) && $category.subdata|count > 0}
        <section class="category-children mb-5">
            <div class="row">
                <div class="col-12 mb-4">
                    <h3 class="fw-bold text-secondary border-bottom pb-2">Affiner votre recherche</h3>
                </div>
                {include file="catalog/loop/category-grid.tpl" data=$category.subdata classType="normal"}
            </div>
        </section>
    {/if}

    {* --- 4. PRODUITS (Le design de votre widget) --- *}
    <section class="category-products mt-4">
        {if isset($category.products) && $category.products|count > 0}
            <div class="row">
                <div class="col-12 mb-4">
                    <h2 class="fw-bold text-dark mb-4">Produits disponibles</h2>
                </div>
                {include file="catalog/loop/product-grid.tpl" data=$category.products classType="normal"}
            </div>
        {elseif (!isset($category.subdata) || $category.subdata|count == 0)}
            {* Message affiché uniquement si la catégorie n'a NI sous-catégories NI produits *}
            <div class="alert alert-info text-center mt-5 p-4 shadow-sm border-0">
                <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                Aucun produit disponible dans cette catégorie pour le moment.
            </div>
        {/if}
    </section>

{/block}

{* 1. L'enfant déclare ses fichiers JS requis *}
{block name="javascript_data"}
    {$page_js = [
    'defer' => ['vendor/splide', 'GalleryManager']
    ] scope="parent"}
{/block}

{* 2. L'enfant écrit son code d'initialisation *}
{block name="javascript" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof GalleryManager !== 'undefined') {
                new GalleryManager();
            }
        });
    </script>
{/block}