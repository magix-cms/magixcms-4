{function name="renderMenu" items=[] depth=0 max_depth=2}
    {foreach $items as $item}

        {$link_name = $item.name_link|default:$item.title|default:''}
        {$link_url = $item.url_link|default:$item.url|default:'#'}
        {$has_children = isset($item.subdata) && $item.subdata|count > 0 && $depth < $max_depth}
        {$is_mega = isset($item.mode_link) && $item.mode_link === 'mega'}
        {$target_blank = ($item.type_link|default:'' == 'external') ? 'target="_blank" rel="noopener noreferrer"' : ''}

        {if $depth == 0}
            <li class="nav-item {if $has_children}dropdown-hover{if $is_mega} position-static{/if}{/if}">
                <a class="nav-link {if $has_children}dropdown-toggle{/if}" href="{$link_url}" {$target_blank}>
                    {$link_name}
                </a>
                {if $has_children}
                    <ul class="dropdown-menu {if $is_mega}w-100 mega-menu{/if}">
                        {call name="renderMenu" items=$item.subdata depth=$depth+1 max_depth=$max_depth}
                    </ul>
                {/if}
            </li>
        {else}
            <li class="{if $has_children}dropend dropdown-hover{/if}">
                <a class="dropdown-item {if $has_children}dropdown-toggle{/if}" href="{$link_url}" {$target_blank}>
                    {$link_name}
                </a>
                {if $has_children}
                    <ul class="dropdown-menu">
                        {call name="renderMenu" items=$item.subdata depth=$depth+1 max_depth=$max_depth}
                    </ul>
                {/if}
            </li>
        {/if}

    {/foreach}
{/function}
<ul class="navbar-nav me-auto mb-2 mb-lg-0 justify-content-end w-100">
    {if isset($menuData) && is_array($menuData) && $menuData|count > 0}
        {call name="renderMenu" items=$menuData depth=0 max_depth=2}
    {/if}
</ul>