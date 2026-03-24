{extends file="layout.tpl"}

{* --- SEO --- *}
{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="head:structured_data"}
    {$website_json_ld|default:'' nofilter}
{/block}

{* On injecte les variables CSS dans le bloc prévu en haut *}
{block name="styleSheet" append nocache}
    {$page_css = ["catalog"] scope="parent"}
{/block}

{* 🟢 Utilisation de "article" pour ÉCRASER la balise <article> du layout *}
{block name="article"}

    {* 🟢 CORRECTION : Le fil d'Ariane retrouve sa place naturelle, au-dessus du header *}
    {* --- FIL D'ARIANE --- *}
    {$breadcrumbs = [['label' => 'Catalogue']]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    {* --- 1. EN-TÊTE DE LA PAGE D'ACCUEIL DU CATALOGUE --- *}
    <header class="catalog-header mb-5">
        <div class="row"> {* Retrait du mt-3 qui n'est plus nécessaire *}
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
                <h2 class="fw-bold text-secondary border-bottom pb-2">Explorez nos rayons</h2>
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
                    <h2 class="fw-bold text-secondary border-bottom pb-2">Tous nos produits</h2>
                </div>

                <div class="col-12">
                    {if isset($catalog_home.products) && $catalog_home.products|count > 0}
                        {include file="catalog/loop/product-grid.tpl" data=$catalog_home.products classType="normal"}

                        {* --- PAGINATION (Via Composant Centralisé) --- *}
                        {if isset($pagination) && $pagination.total_pages > 1}
                            {include file="components/pagination.tpl" pg=$pagination url=$page_url_base}
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