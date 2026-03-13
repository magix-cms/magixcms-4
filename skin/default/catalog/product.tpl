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

{block name="article"}
    <div class="container py-5">

        {* --- FIL D'ARIANE DYNAMIQUE --- *}
        {* On vérifie si la variable cat_name existe et n'est pas vide *}
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

        {* --- EN-TÊTE --- *}
        <div class="row mb-5">
            <div class="col-12 text-center text-lg-start">

                {* Badges : Référence, Marque, etc. *}
                <div class="mb-3">
                    {if !empty($product.ref)}
                        <span class="badge bg-secondary mb-2 fs-6 fw-normal">Réf. {$product.ref}</span>
                    {/if}
                    {if !empty($product.price) && $product.price > 0}
                        {* 🟢 Ajout de |floatval pour satisfaire PHP 8 *}
                        <span class="badge bg-primary mb-2 fs-6 ms-2">{$product.price|number_format:2:',':' '} €</span>
                    {/if}
                </div>

                <h1 class="display-4 fw-bold text-primary mb-3">{$product.name}</h1>

                {if !empty($product.resume)}
                    <p class="lead text-muted">{$product.resume}</p>
                {/if}
            </div>
        </div>

        <div class="row">
            {* --- CONTENU TEXTE --- *}
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
    </div>
{/block}

{* --- SCRIPTS --- *}
{block name="javascript" append}
    {$page_js = ['defer' => ['vendor/splide']] scope="parent"}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof GLightbox !== 'undefined') {
                const lightbox = GLightbox({ selector: '.glightbox' });
            }

            const thumbSlider = document.querySelector('#thumbnail-slider');
            if (thumbSlider && typeof Splide !== 'undefined') {
                const splide = new Splide('#thumbnail-slider', {
                    fixedWidth: 100,
                    fixedHeight: 65,
                    gap: 10,
                    rewind: true,
                    pagination: false,
                    isNavigation: true,
                    arrows: true,
                    breakpoints: {
                        600: { fixedWidth: 60, fixedHeight: 44 }
                    }
                }).mount();

                const mainItems = document.querySelectorAll('.gallery-main-item');
                splide.on('active', function(slide) {
                    mainItems.forEach(item => item.classList.remove('is-active'));
                    const target = document.getElementById('main-image-' + slide.index);
                    if (target) target.classList.add('is-active');
                });
            }
        });
    </script>
{/block}