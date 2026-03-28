{* --- LISTE DES ACTUALITÉS --- *}
<div class="row g-4">
    {if isset($news_list) && $news_list|count > 0}
        {foreach $news_list as $item}
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 transition-hover">
                    {* Image *}
                    <a href="{$item.url}" class="overflow-hidden">
                        {include file="components/img.tpl" img=$item.img class="card-img-top w-100" responsiveC=true}
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
                            <a href="{$item.url}" class="text-decoration-none text-dark stretched-link">{$item.name}</a>
                        </h3>

                        <p class="card-text text-muted">
                            {if !empty($item.resume)}
                                {$item.resume|strip_tags|truncate:120:"..."}
                            {else}
                                {$item.content|strip_tags|truncate:120:"..."}
                            {/if}
                        </p>
                    </div>

                    {* Affichage des tags s'il y en a *}
                    {if !empty($item.tags)}
                        <div class="card-footer bg-body border-top-0 pt-0 position-relative z-2">
                            {foreach $item.tags as $tag}
                                {* L'UrlTool a été exécuté côté DB/Controleur, ici on recrée l'URL dynamiquement ou on la génère dans la vue *}
                                <a href="{$base_url}{$current_lang.iso_lang}/news/tag/{$tag.id_tag}-{$tag.name_tag|lower|replace:' ':'-'}/" class="badge bg-body-tertiary text-secondary text-decoration-none transition-hover">#{$tag.name_tag}</a>
                            {/foreach}
                        </div>
                    {/if}
                </div>
            </div>
        {/foreach}
    {else}
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center">
                <i class="bi bi-info-circle fs-4 me-3"></i>
                <div>
                    <h5 class="mb-1">Aucune actualité trouvée</h5>
                    <p class="mb-0">Essayez de modifier vos filtres ou <a href="{$reset_url}" class="alert-link">retournez à toutes les actualités</a>.</p>
                </div>
            </div>
        </div>
    {/if}
</div>
{* --- FIN DE LA LISTE --- *}