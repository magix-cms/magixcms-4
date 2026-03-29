{* Fichier : plugins/MagixFooterMenu/views/front/widget.tpl *}
<div id="footermenu" class="col-12 col-md-6 col-lg-4 mb-4">
    <h5 class="text-uppercase mb-4 fw-bold text-white border-bottom border-secondary pb-2">
        {#footer_menu_title#}
    </h5>
    {* Fonction récursive adaptée pour une colonne de footer *}
    {function name="renderFooterMenu" items=[] depth=0 max_depth=1}
        <ul class="{if $depth == 0}list-unstyled mb-0{else}list-unstyled ms-3 mt-2 border-start border-secondary ps-3{/if}">
            {foreach $items as $item}

                {$link_name = $item.name_link|default:$item.title|default:''}
                {$link_url = $item.url_link|default:$item.url|default:'#'}
                {$has_children = isset($item.subdata) && $item.subdata|count > 0 && $depth < $max_depth}
                {$target_blank = ($item.type_link|default:'' == 'external') ? 'target="_blank" rel="noopener noreferrer"' : ''}

                <li class="mb-2">
                    <a href="{$link_url}" {$target_blank} class="text-decoration-none d-inline-flex align-items-center">
                        {if $depth > 0}
                            <i class="bi bi-chevron-right small me-2 text-muted"></i>
                        {/if}
                        {$link_name}
                    </a>
                    {* Affichage des sous-éléments (subdata) *}
                    {if $has_children}
                        {call name="renderFooterMenu" items=$item.subdata depth=$depth+1 max_depth=$max_depth}
                    {/if}
                </li>

            {/foreach}
        </ul>
    {/function}
    {* Lancement de la génération avec la variable globale $menuData *}
    {if isset($menuData) && is_array($menuData) && $menuData|count > 0}
        {call name="renderFooterMenu" items=$menuData depth=0 max_depth=1}
    {else}
        <p class="text-muted small">{#footer_menu_empty#}</p>
    {/if}
</div>