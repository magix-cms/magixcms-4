{extends file="layout.tpl"}

{* --- SEO --- *}
{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{* 🟢 BLOC JSON-LD *}
{block name="head:structured_data"}
    {$about.json_ld|default:'' nofilter}
    {$json_ld|default:'' nofilter}
    {$website_json_ld|default:'' nofilter}
{/block}

{block name="styleSheet" append nocache}
    {$page_css = ["about","splide.min", "gallery"] scope="parent"}
{/block}

{block name="article"}
    <article>

        {* --- EN-TÊTE --- *}
        <header class="page-header mb-5">

            {* --- FIL D'ARIANE --- *}
            {$breadcrumbs = [['label' => $about.name]]}
            {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

            <div class="row mt-3">
                <div class="col-12 text-center text-lg-start">
                    <h1 class="display-4 fw-bold text-primary mb-3">{$about.name}</h1>
                    {if $about.resume}
                        <p class="lead text-muted">{$about.resume}</p>
                    {/if}
                </div>
            </div>
        </header>

        {* --- SECTION 1 : CONTENU PRINCIPAL --- *}
        <section class="page-body mb-5">
            <div class="row">
                {* --- CONTENU TEXTE --- *}
                <div class="col-lg-{$about.gallery|count > 0 ? '6' : '12'} mb-4">
                    <div class="content-formatted">
                        {$about.content nofilter}
                    </div>
                </div>

                {* --- GALERIE D'IMAGES AVEC SPLIDE --- *}
                {if $about.gallery && $about.gallery|count > 0}
                    <div class="col-lg-6">
                        {* WRAPPER GLOBAL *}
                        <div class="c-gallery c-gallery--about">

                            {* 1. Grande Image (Stacking context) *}
                            <div class="c-gallery__main shadow-sm rounded mb-3">
                                {foreach $about.gallery as $index => $image}
                                    <div class="gallery-main-item {if $index == 0}is-active{/if}" id="main-image-{$index}">
                                        {$zoom_url = $image.original.src|default:$image.default.src}
                                        <a href="{$zoom_url}" class="glightbox" data-gallery="about" data-title="{$image.title}">
                                            {include file="components/img.tpl" img=$image class="img-fluid w-100"}
                                        </a>
                                    </div>
                                {/foreach}
                            </div>

                            {* 2. Carousel de vignettes *}
                            {if $about.gallery|count > 1}
                                <div id="thumbnail-slider" class="splide c-gallery__thumbs">
                                    <div class="splide__track">
                                        <ul class="splide__list">
                                            {foreach $about.gallery as $index => $image}
                                                <li class="splide__slide">
                                                    {include file="components/img.tpl" img=$image class="img-fluid" size="small"}
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                </div>
                            {/if}

                        </div> {* Fin du wrapper c-gallery *}
                    </div>
                {/if}
            </div>
        </section>

        {* --- SECTION 2 : SOUS-PAGES --- *}
        {if isset($about.subdata) && $about.subdata|count > 0}
            <section class="page-children mt-5 pt-4 border-top">
                <div class="row">
                    <div class="col-12 mb-4">
                        <h3 class="fw-bold text-primary">En savoir plus</h3>
                    </div>
                    {include file="about/loop/about-grid.tpl" data=$about.subdata}
                    {*{foreach $about.subdata as $child}
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm border-0 transition-hover">
                                <a href="{$child.url}" class="text-decoration-none text-dark">
                                    <div class="card-img-top overflow-hidden">
                                        {include file="components/img.tpl" img=$child.img class="img-fluid w-100"}
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold text-primary">{$child.name}</h5>
                                        {$description = $child.resume|default:$child.content|strip_tags|truncate:120:"..."}
                                        {if $description}<p class="card-text small text-muted">{$description}</p>{/if}
                                    </div>
                                    <div class="card-footer bg-transparent border-0 pt-0 pb-3 text-end">
                                        <span class="text-primary small fw-bold">{#read_more#|default:'Lire la suite'} <i class="bi bi-arrow-right"></i></span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    {/foreach}*}
                </div>
            </section>
        {/if}
    </article>
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