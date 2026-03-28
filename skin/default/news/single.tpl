{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="head:structured_data"}
    {$news.json_ld|default:'' nofilter}
    {$website_json_ld|default:'' nofilter}
{/block}

{* --- CSS --- *}
{block name="styleSheet" append nocache}
    {$page_css = ["splide.min", "gallery"] scope="parent"}
{/block}

{* 🟢 Utilisation de "article:content" pour garder la balise <article> *}
{block name="article:content"}

    {* --- FIL D'ARIANE --- *}
    {$breadcrumbs = [
    ['url' => "{$base_url}{$current_lang.iso_lang}/news/", 'label' => 'Actualités'],
    ['label' => $news.name]
    ]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    {* --- EN-TÊTE : DATES ET TITRE --- *}
    <header class="page-header mb-5">
        <div class="row">
            <div class="col-12 text-center text-lg-start">

                {* Bloc Infos Évènement ou Date *}
                <div class="mb-3">
                    {if !empty($news.date_start)}
                        <span class="badge bg-warning text-dark shadow-sm mb-2">
                            <i class="bi bi-calendar-event"></i> Évènement
                        </span>
                        <p class="text-muted fw-bold mb-0">
                            Du {$news.date_start|date_format:"%d/%m/%Y à %H:%M"}
                            {if !empty($news.date_end)} au {$news.date_end|date_format:"%d/%m/%Y à %H:%M"}{/if}
                        </p>
                    {else}
                        <span class="text-muted"><i class="bi bi-clock"></i> Publié le {$news.date_publish|date_format:"%d %B %Y"}</span>
                    {/if}
                </div>

                <h1 class="display-4 fw-bold text-primary mb-3">{$news.name}</h1>
                {if $news.resume}
                    <p class="lead text-muted">{$news.resume}</p>
                {/if}
            </div>
        </div>
    </header>

    {* --- CONTENU TEXTE ET GALERIE --- *}
    <section class="page-body mb-5">
        <div class="row">
            {* --- CONTENU TEXTE --- *}
            <div class="col-lg-{$news.gallery|count > 0 ? '6' : '12'} mb-4">
                <div class="content-formatted">
                    {$news.content|default:'' nofilter}
                </div>
            </div>

            {* --- GALERIE D'IMAGES AVEC SPLIDE --- *}
            {if isset($news.gallery) && $news.gallery|count > 0}
                <div class="col-lg-6">
                    <div class="c-gallery c-gallery--page">

                        {* 1. Grande Image (Stacking context) *}
                        <div class="c-gallery__main shadow-sm rounded mb-3">
                            {foreach $news.gallery as $index => $image}
                                <div class="gallery-main-item {if $index == 0}is-active{/if}" id="main-image-{$index}">
                                    {$zoom_url = $image.original.src|default:$image.default.src}
                                    <a href="{$zoom_url}" class="glightbox" data-gallery="news-gallery" data-title="{$image.title}">
                                        {include file="components/img.tpl" img=$image class="img-fluid w-100"}
                                    </a>
                                </div>
                            {/foreach}
                        </div>

                        {* 2. Carousel de vignettes *}
                        {if $news.gallery|count > 1}
                            <div id="thumbnail-slider" class="splide c-gallery__thumbs mt-3">
                                <div class="splide__track">
                                    <ul class="splide__list">
                                        {foreach $news.gallery as $index => $image}
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
    <section class="page-body mb-5">
        {* --- TAGS --- *}
        {if !empty($news.tags)}
            <div class="mt-5 pt-4 border-top">
                <h5 class="mb-3 h6 fw-bold">Mots-clés :</h5>
                {foreach $news.tags as $tag}
                    <a href="{$base_url}{$current_lang.iso_lang}/news/tag/{$tag.id_tag}-{$tag.name_tag|lower|replace:' ':'-'}/" class="badge bg-body-tertiary text-secondary text-decoration-none me-2 mb-2 p-2 border">
                        #{$tag.name_tag}
                    </a>
                {/foreach}
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