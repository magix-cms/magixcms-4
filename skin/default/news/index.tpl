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

    {* 🟢 CORRECTION : On vérifie s'il y a un filtre actif (Tag ou Archive) pour ajouter le 2ème niveau *}
    {if !empty($current_tag) || !empty($current_year)}
        {$breadcrumbs = [
        ['url' => $reset_url, 'label' => $news_label],
        ['label' => $seo_title|replace:"{$news_label} - ":'' ]
        ]}
    {else}
        {* Sur la racine (Page 1 ou pagination), on reste à 1 seul niveau *}
        {$breadcrumbs = [
        ['label' => $news_label]
        ]}
    {/if}

    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    <header class="news-header mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">

            {* 🟢 MODIFICATION : Affichage du titre H1 *}
            <h1 class="display-5 fw-bold mb-3 mb-md-0">
                {if $is_root && !empty($news_home.title_page)}
                    {$news_home.title_page}
                {else}
                    {$seo_title}
                {/if}
            </h1>

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

        {* 🟢 AJOUT : Le texte de référencement (affiché uniquement sur la racine) *}
        {if $is_root && !empty($news_home.content_page)}
            <div class="news-intro mt-4 content-formatted text-muted">
                {$news_home.content_page nofilter}
            </div>
        {/if}
    </header>

    <section id="news-list">
        {include file="news/loop/news-grid.tpl" data=$news_list classType="normal"}

        {if isset($pagination) && $pagination.total_pages > 1}
            {include file="components/pagination.tpl" pg=$pagination url=$page_url_base}
        {/if}
    </section>

{/block}