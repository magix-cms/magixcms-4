{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="head:structured_data"}
    {$website_json_ld|default:'' nofilter}
{/block}

{block name="styleSheet" append nocache}
    {$page_css = ["catalog"] scope="parent"}
{/block}

{block name="article"}

    {$breadcrumbs = [['label' => {#catalog_breadcrumb_label#}]]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    {* --- 1. EN-TÊTE DE LA PAGE D'ACCUEIL DU CATALOGUE --- *}
    <header class="catalog-header mb-5">
        <div class="row">
            <div class="col-12 text-center text-lg-start">
                <h1 class="display-4 fw-bold text-primary mb-4">{$catalog_home.title}</h1>

                {if $catalog_home.content}
                    <div class="content-formatted text-muted">
                        {$catalog_home.content|default:'' nofilter}
                    </div>
                {/if}
            </div>
        </div>
    </header>

    <section class="catalog-categories mb-5">
        <div class="row">
            <div class="col-12 mb-4">
                <h2 class="fw-bold text-secondary border-bottom pb-2">
                    {#catalog_categories_title#}
                </h2>
            </div>
            {if isset($catalog_home.subdata) && $catalog_home.subdata|count > 0}
                {include file="catalog/loop/category-grid.tpl" data=$catalog_home.subdata classType="normal"}
            {/if}
        </div>
    </section>

    {if $show_products}
        <section class="catalog-products mb-5">
            <div class="row">
                <div class="col-12 mb-4">
                    <h2 class="fw-bold text-secondary border-bottom pb-2">
                        {#catalog_products_title#}
                    </h2>
                </div>

                <div class="col-12">
                    {if isset($catalog_home.products) && $catalog_home.products|count > 0}
                        {include file="catalog/loop/product-grid.tpl" data=$catalog_home.products classType="normal"}

                        {* --- PAGINATION --- *}
                        {if isset($pagination) && $pagination.total_pages > 1}
                            {include file="components/pagination.tpl" pg=$pagination url=$page_url_base}
                        {/if}

                    {else}
                        {* 🟢 Message d'alerte traduit *}
                        <div class="alert alert-info text-center mt-3 p-4 shadow-sm border-0">
                            <i class="bi bi-box-seam fs-3 d-block mb-3"></i>
                            {#catalog_empty_message#}
                        </div>
                    {/if}
                </div>
            </div>
        </section>
    {/if}

{/block}