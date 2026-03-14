{strip}
    {if isset($data.id)}
        {$data = [$data]}
    {/if}
    {if !isset($lazy)}
        {$lazy = true}
    {/if}
{/strip}
{if isset($data) && $data|count > 0}
    <ul class="news-grid-list list-grid mb-0">
        {foreach $data as $item}
            <li class="news-card">
                <div class="figure transition-hover">
                    <a href="{$item.url}" class="time-figure rounded-top">
                        {include file="components/img.tpl" img=$item.img responsiveC=true lazy=$lazy}
                        <div class="date small fw-bold">
                            {if !empty($item.date_start)}
                                <i class="bi bi-calendar-event me-2"></i> Du {$item.date_start|date_format:"%d/%m"}
                            {else}
                                {$item.date_publish|date_format:"%d %b %Y"}
                            {/if}
                        </div>
                    </a>
                    <div class="desc">
                        <h3>
                            <a href="{$item.url}" class="text-decoration-none stretched-link">{$item.name}</a>
                        </h3>
                        <p class="mb-0 mt-2">
                            {if !empty($item.resume)}
                                {$item.resume|strip_tags|truncate:120:"..."}
                            {else}
                                {$item.content|strip_tags|truncate:120:"..."}
                            {/if}
                        </p>
                    </div>
                    <div class="tag-list position-relative z-2">
                        {if !empty($item.tags)}
                            {foreach $item.tags as $tag}
                                <a href="{$base_url}{$current_lang.iso_lang}/news/tag/{$tag.id_tag}-{$tag.name_tag|lower|replace:' ':'-'}/" class="badge bg-light text-secondary text-decoration-none transition-hover me-1">#{$tag.name_tag}</a>
                            {/foreach}
                        {/if}
                    </div>
                </div>
            </li>
        {/foreach}
    </ul>
{else}
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mt-4">
        <i class="bi bi-info-circle fs-4 me-3"></i>
        <div>
            <h5 class="mb-1">Aucune actualité trouvée</h5>
            <p class="mb-0">Essayez de modifier vos filtres ou <a href="{$reset_url}" class="alert-link">retournez à toutes les actualités</a>.</p>
        </div>
    </div>
{/if}