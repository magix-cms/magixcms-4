{* 0. INITIALISATION ULTRA-SÉCURISÉE DES VARIABLES *}
{$base_url = ''}
{if isset($site_url)}{$base_url = $site_url}{/if}

{$controller = 'home'}
{if isset($smarty.get.controller)}{$controller = $smarty.get.controller}{/if}

{$is_amp = (isset($amp) && $amp)}
{$is_amp_enabled = (isset($mc_settings.amp.value) && $mc_settings.amp.value == 1)}


{* --- 1. BALISE CANONICAL --- *}
{if isset($smarty.server.REQUEST_URI)}
    {$clean_uri = $smarty.server.REQUEST_URI|replace:'/amp/':'/'}
    <link rel="canonical" href="{$base_url}{$clean_uri}">
{/if}


{* --- 2. BALISE AMP HTML --- *}
{if $is_amp_enabled && !$is_amp}
    {$current_iso = 'fr'}
    {if isset($current_lang.iso_lang)}{$current_iso = $current_lang.iso_lang|lower}{/if}

    <link rel="amphtml" href="{$base_url}/{$current_iso}/amp/{if $controller != 'home'}{$controller}/{/if}">
{/if}


{* --- 3. BALISES HREFLANG --- *}
{if isset($langs) && is_array($langs) && count($langs) > 1}

    {* Balise x-default par défaut *}
    <link rel="alternate" href="{$base_url}/{if $is_amp}amp/{/if}" hreflang="x-default">

    {foreach $langs as $lang}
        {$iso = $lang.iso_lang|lower}
        {$target_url = ''}

        {if isset($lang.id_lang) && isset($hreflang[$lang.id_lang])}
            {$target_url = "{$base_url}{$hreflang[$lang.id_lang]}"}
        {else}
            {$amp_path = ''}
            {if $is_amp}{$amp_path = 'amp/'}{/if}

            {if $controller !== 'home'}
                {$target_url = "{$base_url}/{$iso}/{$amp_path}{$controller}/"}
            {else}
                {$target_url = "{$base_url}/{$iso}/{$amp_path}"}
            {/if}
        {/if}

        {if $target_url != ''}
            <link rel="alternate" href="{$target_url}" hreflang="{$iso}">
        {/if}
    {/foreach}
{/if}