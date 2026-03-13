{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{* --- CSS --- *}
{block name="styleSheet" append nocache}
    {$page_css = ["splide.min", "gallery"] scope="parent"}
{/block}

{block name="article"}
    <div class="container py-5">

        {* --- FIL D'ARIANE --- *}
        {$breadcrumbs = [
        ['url' => "{$base_url}{$current_lang.iso_lang}/news/", 'label' => 'Actualités'],
        ['label' => $news.name]
        ]}
        {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

        {* --- EN-TÊTE : DATES ET TITRE --- *}
        <div class="row mb-5">
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

        <div class="row">
            {* --- CONTENU TEXTE --- *}
            <div class="col-lg-{$news.gallery|count > 0 ? '6' : '12'} mb-4">
                <div class="content-formatted">
                    {$news.content|default:'' nofilter}
                </div>

                {* --- TAGS --- *}
                {if !empty($news.tags)}
                    <div class="mt-5 pt-4 border-top">
                        <h5 class="mb-3 h6 fw-bold">Mots-clés :</h5>
                        {foreach $news.tags as $tag}
                            <a href="{$base_url}{$current_lang.iso_lang}/news/tag/{$tag.id_tag}-{$tag.name_tag|lower|replace:' ':'-'}/" class="badge bg-light text-secondary text-decoration-none me-2 mb-2 p-2 border">
                                #{$tag.name_tag}
                            </a>
                        {/foreach}
                    </div>
                {/if}
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