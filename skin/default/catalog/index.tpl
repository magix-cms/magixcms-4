{extends file="layout.tpl"}

{* --- SEO --- *}
{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{* 🟢 Utilisation de "article" pour ÉCRASER la balise <article> du layout *}
{block name="article"}

    {* --- FIL D'ARIANE --- *}
    {$breadcrumbs = [
    ['label' => 'Catalogue']
    ]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    {* --- 1. EN-TÊTE DE LA PAGE D'ACCUEIL DU CATALOGUE --- *}
    <header class="catalog-header mb-5">
        <div class="row">
            <div class="col-12 text-center text-lg-start">
                <h1 class="display-4 fw-bold text-primary mb-4">{$catalog_home.title}</h1>

                {if $catalog_home.content}
                    <div class="content-formatted lead text-muted">
                        {$catalog_home.content|default:'' nofilter}
                    </div>
                {/if}
            </div>
        </div>
    </header>

    {* --- 2. LES RAYONS (AFFICHER TOUT LE TEMPS) --- *}
    <section class="catalog-categories mb-5">
        <div class="row">
            <div class="col-12 mb-4">
                <h2 class="fw-bold text-secondary border-bottom pb-2">Explorez nos rayons</h2>
            </div>

            <div class="col-12">
                {if isset($catalog_home.subdata) && $catalog_home.subdata|count > 0}
                    <ul class="category-list list-grid mb-0">
                        {foreach $catalog_home.subdata as $category}
                            <li class="category-card">
                                <div class="figure transition-hover">
                                    <a href="{$category.url}" class="time-figure rounded-top">
                                        {include file="components/img.tpl" img=$category.img responsiveC=true lazy=true}
                                    </a>
                                    <div class="desc">
                                        <h3>
                                            <a href="{$category.url}" class="text-decoration-none stretched-link">{$category.name}</a>
                                        </h3>
                                        <p class="mb-0 mt-2">
                                            {$clean_resume = $category.resume|strip_tags|replace:'&nbsp;':''|trim}
                                            {if !empty($clean_resume)}
                                                {$clean_resume|truncate:120:"..."}
                                            {else}
                                                {$category.content|strip_tags|replace:'&nbsp;':''|truncate:120:"..."}
                                            {/if}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <div class="alert alert-info text-center mt-3 p-4 shadow-sm border-0">
                        <i class="bi bi-shop fs-3 d-block mb-3"></i>
                        Nos rayons sont en cours d'aménagement !
                    </div>
                {/if}
            </div>
        </div>
    </section>

    {* --- 3. LA BOUTIQUE (MODE GLOBAL ACTIVÉ) --- *}
    {if $show_products}
        <section class="catalog-products mb-5">
            <div class="row">
                <div class="col-12 mb-4">
                    <h2 class="fw-bold text-secondary border-bottom pb-2">Tous nos produits</h2>
                </div>

                <div class="col-12">
                    {if isset($catalog_home.products) && $catalog_home.products|count > 0}
                        {include file="catalog/loop/product-grid.tpl" data=$catalog_home.products}

                        {* --- PAGINATION --- *}
                        {if isset($pagination) && $pagination.total_pages > 1}
                            <nav class="mt-5" aria-label="Navigation des produits">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item {if $pagination.current_page <= 1}disabled{/if}">
                                        <a class="page-link" href="{if $pagination.current_page > 1}{$page_url_base}{$pagination.current_page - 1}{else}#{/if}" tabindex="-1">Précédent</a>
                                    </li>

                                    {for $i=1 to $pagination.total_pages}
                                        <li class="page-item {if $i == $pagination.current_page}active{/if}">
                                            <a class="page-link" href="{$page_url_base}{$i}">{$i}</a>
                                        </li>
                                    {/for}

                                    <li class="page-item {if $pagination.current_page >= $pagination.total_pages}disabled{/if}">
                                        <a class="page-link" href="{if $pagination.current_page < $pagination.total_pages}{$page_url_base}{$pagination.current_page + 1}{else}#{/if}">Suivant</a>
                                    </li>
                                </ul>
                            </nav>
                        {/if}

                    {else}
                        <div class="alert alert-info text-center mt-3 p-4 shadow-sm border-0">
                            <i class="bi bi-box-seam fs-3 d-block mb-3"></i>
                            Notre boutique est en cours de remplissage. De nouveaux produits arrivent très vite !
                        </div>
                    {/if}
                </div>
            </div>
        </section>
    {/if}

{/block}