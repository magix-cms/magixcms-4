{strip}
    {if isset($data.id)}
        {$data = [$data]}
    {/if}
    {if !isset($lazy)}
        {$lazy = true}
    {/if}
{/strip}
{if isset($category.products) && $category.products|count > 0}
    {foreach $category.products as $product}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100 border-0 bg-light transition-hover">

                {* LE LIEN ET L'IMAGE AVEC VOTRE COMPOSANT *}
                <a href="{$product.url}" title="{$product.name}">
                    {include file="components/img.tpl" img=$product.img size="medium" responsiveC=true lazy=true}
                </a>

                {* d-flex flex-column pour aligner les boutons en bas *}
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title text-dark">
                        <a href="{$product.url}" class="text-decoration-none text-dark">{$product.name}</a>
                    </h5>
                    {if $product.cat_name}
                        <small class="text-muted d-block mb-2">{$product.cat_name}</small>
                    {/if}

                    {* mt-auto repousse le prix et le bouton vers le bas de la carte *}
                    {if !empty($product.price_final) && $product.price_final > 0}
                        <p class="card-text fw-bold text-primary fs-4 mt-auto">
                            {$product.price_formatted} € <small class="text-muted fs-6">{$product.price_suffix}</small>
                        </p>
                    {/if}
                    <a href="{$product.url}" class="btn btn-outline-dark">Voir le produit</a>
                </div>

            </div>
        </div>
    {/foreach}
{/if}