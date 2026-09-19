{strip}
    {if isset($data.id)}
        {$data = [$data]}
    {/if}
    {$lazy = $lazy|default:true}

    {$class = ""}
    {if isset($classType) && $classType != "normal" && $classType != ""}
        {$class = "-$classType"}
    {/if}

    {$extraClass = $extraClass|default:""}
    {$truncate = $truncate|default:200}

    {* Options d'animation *}
    {$animate = $animate|default:false}
    {* Choix par défaut parmi : fade-up, fade-down, fade-left, fade-right, zoom-in *}
    {$animType = $animType|default:'fade-up'}
{/strip}

{if isset($data) && $data|count > 0}
    <ul class="pages-list{$class} list-grid mb-6 {$extraClass}">
        {foreach $data as $index => $item}
            {*
               Calcul du délai :
               $index démarre à 0, mais le SCSS démarre à .delay-1
               On utilise le modulo 5 pour faire boucler les délais : 1, 2, 3, 4, 5, 1, 2...
            *}
            {$delay = ($index % 5) + 1}

            <li class="page-card{$class}{if $animate} animate-on-scroll {$animType} delay-{$delay}{/if}">
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
                                {$item.resume|strip_tags|truncate:$truncate:"..."}
                            {else}
                                {$item.content|strip_tags|truncate:$truncate:"..."}
                            {/if}
                        </p>
                    </div>
                </div>
            </li>
        {/foreach}
    </ul>
{/if}