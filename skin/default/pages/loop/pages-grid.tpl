{strip}
    {if isset($data.id)}
        {$data = [$data]}
    {/if}
    {if !isset($lazy)}
        {$lazy = true}
    {/if}
{/strip}

{if isset($data) && $data|count > 0}
    <ul class="pages-list list-grid mb-0">
        {foreach $data as $item}
            <li class="page-card">
                <div class="figure transition-hover">
                    <a href="{$item.url}" class="time-figure rounded-top">
                        {include file="components/img.tpl" img=$item.img responsiveC=true lazy=$lazy}
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
                </div>
            </li>
        {/foreach}
    </ul>
{/if}