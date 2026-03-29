{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

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

        <header class="page-header mb-5">

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

        <section class="page-body mb-5">
            <div class="row">
                <div class="col-lg-{$about.gallery|count > 0 ? '6' : '12'} mb-4">
                    <div class="content-formatted">
                        {$about.content nofilter}
                    </div>
                </div>

                {if $about.gallery && $about.gallery|count > 0}
                    <div class="col-lg-6">
                        <div class="c-gallery c-gallery--about">

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

                        </div>
                    </div>
                {/if}
            </div>
        </section>

        {if isset($about.subdata) && $about.subdata|count > 0}
            <section class="page-children mt-5 pt-4 border-top">
                <div class="row">
                    <div class="col-12 mb-4">
                        {* 🟢 Titre de section traduit *}
                        <h3 class="fw-bold text-primary">
                            {#about_learn_more_title#}
                        </h3>
                    </div>
                    {include file="about/loop/about-grid.tpl" data=$about.subdata}
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