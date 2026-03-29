{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="head:structured_data"}
    {$pages.json_ld|default:'' nofilter}
    {$json_ld|default:'' nofilter}
    {$website_json_ld|default:'' nofilter}
{/block}

{block name="styleSheet" append nocache}
    {$page_css = ["pages","splide.min", "gallery"] scope="parent"}
{/block}

{block name="article:content"}
    <article>

        <header class="page-header mb-5">

            {$breadcrumbs = [['label' => $pages.name]]}
            {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

            <div class="row mt-3">
                <div class="col-12 text-center text-lg-start">
                    <h1 class="display-4 fw-bold text-primary mb-3">{$pages.name}</h1>
                    {if $pages.resume}
                        <p class="lead text-muted">{$pages.resume}</p>
                    {/if}
                </div>
            </div>
        </header>

        <section class="page-body mb-5">
            <div class="row">
                <div class="col-lg-{$pages.gallery|count > 0 ? '6' : '12'} mb-4">
                    <div class="content-formatted">
                        {$pages.content|default:'' nofilter}
                    </div>
                </div>

                {if $pages.gallery && $pages.gallery|count > 0}
                    <div class="col-lg-6">
                        <div class="c-gallery c-gallery--page">

                            <div class="c-gallery__main shadow-sm rounded mb-3">
                                {foreach $pages.gallery as $index => $image}
                                    <div class="gallery-main-item {if $index == 0}is-active{/if}" id="main-image-{$index}">
                                        {$zoom_url = $image.original.src|default:$image.default.src}
                                        <a href="{$zoom_url}" class="glightbox" data-gallery="pages" data-title="{$image.title}">
                                            {include file="components/img.tpl" img=$image class="img-fluid w-100"}
                                        </a>
                                    </div>
                                {/foreach}
                            </div>

                            {if $pages.gallery|count > 1}
                                <div id="thumbnail-slider" class="splide c-gallery__thumbs mt-3">
                                    <div class="splide__track">
                                        <ul class="splide__list">
                                            {foreach $pages.gallery as $index => $image}
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

        {hook name='displayPageBottom' id_pages=$pages.id}

        {if isset($pages.subdata) && $pages.subdata|count > 0}
            <section class="page-children mt-5 pt-4 border-top">
                <div class="row">
                    <div class="col-12 mb-4">
                        <h3 class="fw-bold text-primary">En savoir plus</h3>
                    </div>
                    {include file="pages/loop/pages-grid.tpl" data=$pages.subdata classType="normal"}
                </div>
            </section>
        {/if}

    </article>

{/block}

{block name="javascript_data"}
    {$page_js = [
    'defer' => ['vendor/splide', 'GalleryManager']
    ] scope="parent"}
{/block}

{block name="javascript" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof GalleryManager !== 'undefined') {
                new GalleryManager();
            }
        });
    </script>
{/block}