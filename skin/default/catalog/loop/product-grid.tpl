{strip}
    {if isset($data.id)}
        {$data = [$data]}
    {/if}
    {$lazy = $lazy|default:true}
    {$class = ""}
    {if isset($classType)}
        {if $classType == "large"}
            {$class = "-large"}
        {elseif $classType == "normal"}
            {$class = ""}
        {/if}
    {/if}
{/strip}

{if isset($data) && $data|count > 0}
    <ul class="product-list{$class} list-grid mb-0">
        {foreach $data as $item}
            <li class="product-card{$class}">
                <div class="figure bg-body transition-hover">
                    {* 🟢 Ajout de position-relative pour le badge de promo *}
                    <div class="time-figure rounded-top position-relative">

                        {* Badge Promo sur l'image *}
                        {if $item.has_promo}
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2 z-1 shadow-sm">
                                -{$item.promo_percent}%
                            </span>
                        {/if}

                        {include file="components/img.tpl" img=$item.img size="medium" responsiveC=true lazy=$lazy}
                        {if !empty($item.cat_name)}
                            <span class="cat-label small fw-bold">{$item.cat_name}</span>
                        {/if}
                    </div>
                    <div class="desc">
                        <h3>
                            <a href="{$item.url}" class="text-decoration-none stretched-link{* text-reset*}" title="{$item.name}">{$item.name}</a>
                        </h3>
                        <p class="mb-0 mt-2">
                            {if !empty($item.resume)}
                                {$item.resume|strip_tags|truncate:120:"..."}
                            {else}
                                {$item.content|strip_tags|truncate:120:"..."}
                            {/if}
                        </p>
                    </div>
                    {* 🟢 Gestion des prix dans la vignette *}
                    <div class="product-price flex-column gap-1 mt-2">
                        {if !empty($item.price_final) && $item.price_final > 0}
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                {if $item.has_promo}
                                    <span class="price fw-bold text-danger fs-5">
                                        {$item.price_formatted} € <small class="fs-6">{$item.price_suffix}</small>
                                    </span>
                                    <span class="text-decoration-line-through text-muted small">
                                        {$item.price_original_formatted} €
                                    </span>
                                {else}
                                    <span class="price fw-bold text-primary fs-5">
                                        {$item.price_formatted} € <small class="text-muted fs-6">{$item.price_suffix}</small>
                                    </span>
                                {/if}
                            </div>
                        {/if}
                        <a href="{$item.url}" class="btn btn-main-outline btn-sm w-100 mt-2 position-relative z-2">Voir le produit</a>
                    </div>

                </div>
            </li>
        {/foreach}
    </ul>
{/if}