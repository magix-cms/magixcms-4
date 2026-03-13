{extends file="layout.tpl"}

{* --- SEO --- *}
{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="article"}
    <div class="container py-5">

        {* --- 1. EN-TÊTE DE LA PAGE D'ACCUEIL DU CATALOGUE --- *}
        <div class="row mb-5">
            <div class="col-12 text-center text-lg-start">
                <h1 class="display-4 fw-bold text-primary mb-4">{$catalog_home.title}</h1>

                {if $catalog_home.content}
                    <div class="content-formatted lead text-muted">
                        {$catalog_home.content|default:'' nofilter}
                    </div>
                {/if}
            </div>
        </div>

        {* --- 2. GRILLE DES CATÉGORIES PRINCIPALES (Les Rayons) --- *}
        {if isset($catalog_home.subdata) && $catalog_home.subdata|count > 0}
            <div class="row mb-5">
                <div class="col-12 mb-4">
                    <h2 class="fw-bold text-secondary border-bottom pb-2">Explorez nos rayons</h2>
                </div>

                {foreach $catalog_home.subdata as $category}
                    <div class="col-md-4 col-lg-4 mb-4">
                        {* 🟢 MISE À JOUR : Structure de carte identique à Pages/About *}
                        <div class="card h-100 shadow-sm border-0 transition-hover">
                            <a href="{$category.url}" class="text-decoration-none text-dark">
                                <div class="card-img-top overflow-hidden">
                                    {include file="components/img.tpl" img=$category.img class="img-fluid w-100"}
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-primary">{$category.name}</h5>

                                    {* On récupère le résumé ou on coupe le contenu principal *}
                                    {$description = $category.resume|default:$category.content|strip_tags|truncate:120:"..."}
                                    {if $description}
                                        <p class="card-text small text-muted mt-2">{$description}</p>
                                    {/if}
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-0 pb-3 text-end">
                                    <span class="text-primary small fw-bold">Découvrir <i class="bi bi-arrow-right"></i></span>
                                </div>
                            </a>
                        </div>
                    </div>
                {/foreach}
            </div>
        {else}
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center mt-3 p-4 shadow-sm border-0">
                        <i class="bi bi-shop fs-3 d-block mb-3"></i>
                        Notre catalogue est en cours de préparation. De nouveaux produits arrivent très vite !
                    </div>
                </div>
            </div>
        {/if}

    </div>
{/block}