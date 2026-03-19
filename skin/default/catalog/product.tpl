{extends file="layout.tpl"}

{* --- SEO --- *}
{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}
{block name="head:structured_data"}
    {$product.json_ld|default:'' nofilter}
{/block}
{* --- CSS --- *}
{block name="styleSheet" append nocache}
    {$page_css = ["splide.min", "gallery"] scope="parent"}
{/block}

{* 🟢 Utilisation de article:content pour bénéficier de la balise <article> du layout parent *}
{block name="article:content"}

    {* --- EN-TÊTE --- *}
    <header class="product-header mb-5">

        {* --- FIL D'ARIANE DYNAMIQUE --- *}
        {if !empty($product.cat_name)}
            {$breadcrumbs = [
            ['url' => "{$base_url}{$current_lang.iso_lang}/catalog/", 'label' => 'Catalogue'],
            ['url' => $product.url_cat, 'label' => $product.cat_name],
            ['label' => $product.name]
            ]}
        {else}
            {$breadcrumbs = [
            ['url' => "{$base_url}{$current_lang.iso_lang}/catalog/", 'label' => 'Catalogue'],
            ['label' => $product.name]
            ]}
        {/if}
        {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

        <div class="row mt-3">
            <div class="col-12 text-center text-lg-start">

                {* Badges : Prix, Référence (SKU) et EAN *}
                <div class="mb-3 d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-2">

                    {* 1. Le Prix (En évidence avec gestion de la promo) *}
                    {if !empty($product.price_final) && $product.price_final > 0}
                        {if $product.has_promo}
                            {* Prix en promo (Rouge) *}
                            <span class="badge bg-danger fs-5 py-2 px-3 shadow-sm">
                                {$product.price_formatted} € {$product.price_suffix}
                            </span>
                            {* Ancien prix barré (Gris clair) *}
                            <span class="badge bg-light text-muted text-decoration-line-through fs-5 py-2 px-3 border">
                                {$product.price_original_formatted} €
                            </span>
                            {* Badge de pourcentage (Jaune) *}
                            <span class="badge bg-warning text-dark fs-6 py-2 px-2 ms-1">
                                -{$product.promo_percent}%
                            </span>
                        {else}
                            {* Prix normal (Bleu) *}
                            <span class="badge bg-primary fs-5 py-2 px-3 shadow-sm">
                                {$product.price_formatted} € {$product.price_suffix}
                            </span>
                        {/if}
                    {/if}

                    {* 2. La Référence (SKU) *}
                    {if !empty($product.reference)}
                        <span class="badge bg-secondary fs-6 fw-normal text-uppercase" title="Référence / SKU">
                            <i class="bi bi-hash me-1"></i> Réf: {$product.reference}
                        </span>
                    {/if}

                    {* 3. Le Code EAN (Si disponible) *}
                    {if !empty($product.ean_p)}
                        <span class="badge bg-light text-dark border fs-6 fw-normal" title="Code-barres EAN">
                            <i class="bi bi-upc-scan me-1"></i> EAN: {$product.ean_p}
                        </span>
                    {/if}

                    {* 4. Disponibilité (Optionnel mais très apprécié UX/SEO) *}
                    {if !empty($product.availability_p)}
                        {if $product.availability_p === 'InStock'}
                            <span class="badge bg-success bg-opacity-10 text-success fs-6 fw-normal border border-success">
                                <i class="bi bi-check-circle me-1"></i> En stock
                            </span>
                        {elseif $product.availability_p === 'OutOfStock'}
                            <span class="badge bg-danger bg-opacity-10 text-danger fs-6 fw-normal border border-danger">
                                <i class="bi bi-x-circle me-1"></i> Rupture
                            </span>
                        {/if}
                    {/if}

                </div>

                <h1 class="display-4 fw-bold text-dark mb-3">{$product.name}</h1>

                {if !empty($product.resume)}
                    <p class="lead text-muted">{$product.resume}</p>
                {/if}
            </div>
        </div>
    </header>

    {* --- CONTENU TEXTE ET GALERIE --- *}
    <section class="product-body mb-5">
        <div class="row">
            <div class="col-lg-{$product.gallery|count > 0 ? '6' : '12'} mb-4">
                <div class="content-formatted">
                    {* 🟢 Ajout du nofilter obligatoire pour le HTML généré par TinyMCE *}
                    {$product.content|default:'' nofilter}
                </div>

                {* --- DOCUMENTS JOINTS (Si vous gérez des PDF/Fiches techniques) --- *}
                {if !empty($product.documents)}
                    <div class="mt-5 pt-4 border-top">
                        <h5 class="mb-3 h6 fw-bold">Documents à télécharger :</h5>
                        <ul class="list-unstyled">
                            {foreach $product.documents as $doc}
                                <li class="mb-2">
                                    <a href="{$doc.url}" target="_blank" class="text-decoration-none text-primary">
                                        <i class="bi bi-file-earmark-pdf me-2"></i> {$doc.name}
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
            </div>

            {* --- GALERIE D'IMAGES AVEC SPLIDE --- *}
            {if isset($product.gallery) && $product.gallery|count > 0}
                <div class="col-lg-6">
                    <div class="c-gallery c-gallery--page">

                        {* 1. Grande Image (Stacking context) *}
                        <div class="c-gallery__main shadow-sm rounded mb-3">
                            {foreach $product.gallery as $index => $image}
                                <div class="gallery-main-item {if $index == 0}is-active{/if}" id="main-image-{$index}">
                                    {$zoom_url = $image.original.src|default:$image.default.src}
                                    <a href="{$zoom_url}" class="glightbox" data-gallery="product-gallery" data-title="{$image.title}">
                                        {include file="components/img.tpl" img=$image class="img-fluid w-100"}
                                    </a>
                                </div>
                            {/foreach}
                        </div>

                        {* 2. Carousel de vignettes *}
                        {if $product.gallery|count > 1}
                            <div id="thumbnail-slider" class="splide c-gallery__thumbs mt-3">
                                <div class="splide__track">
                                    <ul class="splide__list">
                                        {foreach $product.gallery as $index => $image}
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