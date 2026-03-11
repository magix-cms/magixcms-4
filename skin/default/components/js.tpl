{* components/js.tpl *}

{* On initialise final_js avec les globaux s'ils existent *}
{$final_js = $global_js|default:[]}

{* On fusionne avec les scripts de la page s'ils existent *}
{if isset($page_js) && is_array($page_js)}
    {foreach ['normal', 'async', 'defer'] as $method}
        {$p_list = $page_js.$method|default:[]}
        {$g_list = $final_js.$method|default:[]}

        {if $p_list|count > 0 || $g_list|count > 0}
            {$final_js.$method = $g_list|array_merge:$p_list|array_unique}
        {/if}
    {/foreach}
{/if}

{* Rendu des balises *}
{foreach $final_js as $loading_method => $files}
    {foreach $files as $js}
        {if $js|str_starts_with:'http' || $js|str_starts_with:'//'}
            {$js_path = $js}
        {elseif $js|str_starts_with:'vendor/'}
            {$js_path = "{$skin_url}/js/{$js}.min.js"}
        {else}
            {$is_dev = ($mc_settings.mode.value == 'dev')}
            {if $is_dev}
                {$js_path = "{$skin_url}/js/src/{$js}.js"}
            {else}
                {$js_path = "{$skin_url}/js/{$js}.min.js"}
            {/if}
        {/if}

        <script src="{$js_path}" {if $loading_method != 'normal'}{$loading_method}{/if}></script>
    {/foreach}
{/foreach}