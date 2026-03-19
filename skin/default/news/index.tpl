{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="styleSheet" append nocache}
    {$page_css = ["news"] scope="parent"}
{/block}

{* 🟢 INJECTION DU JSON-LD DE LA LISTE *}
{block name="head:structured_data"}
    {$json_ld|default:'' nofilter}
{/block}

{* 🟢 Utilisation de "article" pour écraser la balise <article> *}
{block name="article"}

    {* --- FIL D'ARIANE DYNAMIQUE --- *}
    {if $seo_title != 'Actualités'}
        {$breadcrumbs = [
        ['url' => $reset_url, 'label' => 'Actualités'],
        ['label' => $seo_title|replace:'Actualités - ':'' ]
        ]}
    {else}
        {$breadcrumbs = [
        ['label' => 'Actualités']
        ]}
    {/if}

    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    {* --- EN-TÊTE AVEC FILTRES --- *}
    <header class="news-header mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <h1 class="display-5 fw-bold mb-3 mb-md-0">{$seo_title}</h1>

            {* --- BARRE DE FILTRES --- *}
            <div class="d-flex gap-2">
                {* Filtre par Tags *}
                {if !empty($all_tags)}
                    <select class="form-select bg-white shadow-sm border-0" onchange="if(this.value) window.location.href=this.value;">
                        <option value="{$reset_url}">Tous les tags</option>
                        {foreach $all_tags as $t}
                            <option value="{$t.url}" {if $current_tag == $t.id_tag}selected{/if}>
                                {$t.name_tag}
                            </option>
                        {/foreach}
                    </select>
                {/if}

                {* Filtre par Date (Mois/Année) *}
                {if !empty($archives)}
                    <select class="form-select bg-white shadow-sm border-0" onchange="if(this.value) window.location.href=this.value;">
                        <option value="{$reset_url}">Toutes les dates</option>
                        {foreach $archives as $a}
                            {* Utilisation de la date factice formatée par Smarty *}
                            <option value="{$a.url}" {if $current_year == $a.year && $current_month == $a.month}selected{/if}>
                                {$a.dummy_date|date_format:"%B %Y"|capitalize} ({$a.count_news})
                            </option>
                        {/foreach}
                    </select>
                {/if}
            </div>
        </div>
    </header>

    {* --- LISTE DES ACTUALITÉS --- *}
    <section class="news-list">
        {include file="news/loop/news-grid.tpl" data=$news_list}
        {include file="components/pagination.tpl" pg=$pagination url=$page_url_base}
    </section>

{/block}