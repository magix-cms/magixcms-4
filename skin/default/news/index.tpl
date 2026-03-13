{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="article"}
    <div class="container py-5">
        {* --- FIL D'ARIANE DYNAMIQUE --- *}
        {* Si le titre SEO est différent de "Actualités", c'est qu'on est dans un filtre (Tag ou Date) *}
        {if $seo_title != 'Actualités'}
            {$breadcrumbs = [
            ['url' => "{$base_url}{$current_lang.iso_lang}/news/", 'label' => 'Actualités'],
            ['label' => $seo_title]
            ]}
        {else}
            {$breadcrumbs = [
            ['label' => 'Actualités']
            ]}
        {/if}

        {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

        <h1 class="display-5 fw-bold mb-5">{$seo_title}</h1>

        <div class="row g-4">
            {if isset($news_list) && $news_list|count > 0}
                {foreach $news_list as $item}
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-0">
                            {* Image *}
                            <a href="{$item.url}">
                                {include file="components/img.tpl" img=$item.img class="card-img-top" responsiveC=true}
                            </a>

                            <div class="card-body">
                                {* Badge Évènement ou Actualité *}
                                <div class="mb-2">
                                    {if !empty($item.date_start)}
                                        <span class="badge bg-warning text-dark"><i class="bi bi-calendar-event"></i> Évènement</span>
                                        <small class="text-muted d-block mt-1">
                                            Du {$item.date_start|date_format:"%d/%m/%Y"}
                                            {if !empty($item.date_end)} au {$item.date_end|date_format:"%d/%m/%Y"}{/if}
                                        </small>
                                    {else}
                                        <span class="badge bg-primary">Actualité</span>
                                        <small class="text-muted d-block mt-1">Publié le {$item.date_publish|date_format:"%d/%m/%Y"}</small>
                                    {/if}
                                </div>

                                <h3 class="h5 card-title mt-3">
                                    <a href="{$item.url}" class="text-decoration-none text-dark">{$item.name}</a>
                                </h3>

                                <p class="card-text text-muted">
                                    {$item.resume|strip_tags|truncate:120:"..."}
                                </p>
                            </div>

                            {* Affichage des tags s'il y en a *}
                            {if !empty($item.tags)}
                                <div class="card-footer bg-white border-top-0 pt-0">
                                    {foreach $item.tags as $tag}
                                        <a href="{$base_url}{$current_lang.iso_lang}/news/tag/{$tag.id_tag}-{$tag.name_tag|lower|replace:' ':'-'}/" class="badge bg-light text-secondary text-decoration-none">#{$tag.name_tag}</a>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            {else}
                <div class="col-12">
                    <div class="alert alert-info">Aucune actualité trouvée pour le moment.</div>
                </div>
            {/if}
        </div>
    </div>
{/block}