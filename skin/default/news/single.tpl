{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="head:structured_data"}
    {$news.json_ld|default:'' nofilter}
    {$website_json_ld|default:'' nofilter}
{/block}

{block name="styleSheet" append nocache}
    {$page_css = ["splide.min", "gallery"] scope="parent"}
{/block}

{block name="article:content"}

    {$breadcrumbs = [
    ['url' => "{$base_url}{$current_lang.iso_lang}/news/", 'label' => {#news_single_breadcrumb#}],
    ['label' => $news.name]
    ]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    <header class="page-header mb-5">
        <div class="row">
            <div class="col-12 text-center text-lg-start">

                <div class="mb-3">
                    {if !empty($news.date_start)}
                        <span class="badge bg-warning text-dark shadow-sm mb-2">
                            <i class="bi bi-calendar-event"></i> {#news_single_event_label#}
                        </span>
                        <p class="text-muted fw-bold mb-0">
                            {#news_single_date_from#} {$news.date_start|date_format:"%d/%m/%Y à %H:%M"}
                            {if !empty($news.date_end)} {#news_single_date_to#} {$news.date_end|date_format:"%d/%m/%Y à %H:%M"}{/if}
                        </p>
                    {else}
                        <span class="text-muted">
                            <i class="bi bi-clock"></i> {#news_single_published_on#} {$news.date_publish|date_format:"%d %B %Y"}
                        </span>
                    {/if}
                </div>

                <h1 class="display-4 fw-bold text-primary mb-3">{$news.name}</h1>
                {if $news.resume}
                    <p class="lead text-muted">{$news.resume}</p>
                {/if}
            </div>
        </div>
    </header>

    <section class="page-body mb-5">
        <div class="row">
            <div class="col-lg-{$news.gallery|count > 0 ? '6' : '12'} mb-4">
                <div class="content-formatted">
                    {$news.content|default:'' nofilter}
                </div>
            </div>

            {if isset($news.gallery) && $news.gallery|count > 0}
                <div class="col-lg-6">
                    <div class="c-gallery c-gallery--page">
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
        {if !empty($news.tags)}
            <div class="mt-5 pt-4 border-top">
                <h5 class="mb-3 h6 fw-bold">{#news_single_tags_title#}</h5>
                {foreach $news.tags as $tag}
                    <a href="{$base_url}{$current_lang.iso_lang}/news/tag/{$tag.id_tag}-{$tag.name_tag|lower|replace:' ':'-'}/" class="badge bg-body-tertiary text-secondary text-decoration-none me-2 mb-2 p-2 border">
                        #{$tag.name_tag}
                    </a>
                {/foreach}
            </div>
        {/if}
    </section>

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