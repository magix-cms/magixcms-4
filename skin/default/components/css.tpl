{strip}
    {* 3. ON FUSIONNE LES DEUX TABLEAUX *}
    {$final_css = $global_css}
    {if isset($page_css) && is_array($page_css)}
        {$final_css = array_merge($global_css, $page_css)}
    {/if}

    {* 4. ON GÉNÈRE LES LIENS AVEC LE TABLEAU FINAL *}
    {$is_dev = ($mc_settings.mode.value == 'dev')}
    {$suffix = $is_dev ? '' : '.min'}

    {foreach $final_css as $css}
        {if str_starts_with($css, 'http') || str_starts_with($css, '//')}
            {$css_path = $css}
        {else}
            {if strpos($css, '.min') !== false}
                {$css_path = "{$skin_url}/css/{$css}.css"}
            {else}
                {$css_path = "{$skin_url}/css/{$css}{$suffix}.css"}
            {/if}
        {/if}

        {if in_array($css, $global_css)}
            {* CSS Global : 1 seule balise avec 100% de la priorité réseau initiale *}
            <link rel="stylesheet" href="{$css_path}" fetchpriority="high" />
        {else}
            {* CSS Modulaires : Non-bloquant ET priorité réseau basse pour ne pas freiner global.min.css *}
            <link rel="stylesheet" href="{$css_path}" media="print" onload="this.media='all';this.onload=null;" />
            <noscript><link rel="stylesheet" href="{$css_path}" /></noscript>
        {/if}
    {/foreach}
{/strip}