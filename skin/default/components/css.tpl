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
        {$css_path = "{$skin_url}/css/{$css}{$suffix}.css"}
    {/if}
    <link rel="preload" href="{$css_path}" as="style" />
    <link rel="stylesheet" href="{$css_path}" />
{/foreach}