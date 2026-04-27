{strip}
    {* 3. ON FUSIONNE LES DEUX TABLEAUX *}
    {$final_css = $global_css}
    {if isset($page_css) && is_array($page_css)}
        {$final_css = array_merge($global_css, $page_css)}
    {/if}

    {* 4. ON GÉNÈRE LES LIENS AVEC LE TABLEAU FINAL *}
    {$is_dev = (isset($mc_settings.mode.value) && $mc_settings.mode.value == 'dev')}
    {$suffix = $is_dev ? '' : '.min'}

    {foreach $final_css as $css}
        {if str_starts_with($css, 'http') || str_starts_with($css, '//')}
            {$css_path = $css}
        {else}
            {* 🟢 CORRECTION : Si le fichier possède déjà ".min", on ignore le suffixe *}
            {if strpos($css, '.min') !== false}
                {$css_path = "{$skin_url}/css/{$css}.css"}
            {else}
                {$css_path = "{$skin_url}/css/{$css}{$suffix}.css"}
            {/if}
        {/if}
        <link rel="preload" href="{$css_path}" as="style" />
        <link rel="stylesheet" href="{$css_path}" />
    {/foreach}
{/strip}