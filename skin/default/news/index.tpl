{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="styleSheet" append nocache}
    {$page_css = ["news"] scope="parent"}
{/block}

{block name="head:structured_data"}
    {$json_ld|default:'' nofilter}
{/block}

{block name="article"}

    {$news_label = {#news_breadcrumb_label#}}

    {if $seo_title != $news_label}
        {$breadcrumbs = [
        ['url' => $reset_url, 'label' => $news_label],
        ['label' => $seo_title|replace:"{$news_label} - ":'' ]
        ]}
    {else}
        {$breadcrumbs = [
        ['label' => $news_label]
        ]}
    {/if}

    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    <header class="news-header mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <h1 class="display-5 fw-bold mb-3 mb-md-0">{$seo_title}</h1>

            <div class="d-flex gap-2">
                {if !empty($all_tags)}
                    <select class="form-select bg-body shadow-sm border-0" onchange="if(this.value) window.location.href=this.value;">
                        <option value="{$reset_url}">{#news_filter_tags_all#}</option>
                        {foreach $all_tags as $t}
                            <option value="{$t.url}" {if $current_tag == $t.id_tag}selected{/if}>
                                {$t.name_tag}
                            </option>
                        {/foreach}
                    </select>
                {/if}

                {if !empty($archives)}
                    <select class="form-select bg-body shadow-sm border-0" onchange="if(this.value) window.location.href=this.value;">
                        <option value="{$reset_url}">{#news_filter_dates_all#}</option>
                        {foreach $archives as $a}
                            <option value="{$a.url}" {if $current_year == $a.year && $current_month == $a.month}selected{/if}>
                                {$a.dummy_date|date_format:"%B %Y"|capitalize} ({$a.count_news})
                            </option>
                        {/foreach}
                    </select>
                {/if}
            </div>
        </div>
    </header>

    <section id="news-list">
        {include file="news/loop/news-grid.tpl" data=$news_list classType="normal"}

        {if isset($pagination) && $pagination.total_pages > 1}
            {include file="components/pagination.tpl" pg=$pagination url=$page_url_base}
        {/if}
    </section>

{/block}