{* --- FAVICONS & APP ICONS --- *}
{if isset($has_favicon) && $has_favicon}
    {* Standard pour navigateurs de bureau *}
    <link rel="icon" type="image/png" sizes="32x32" href="{$base_url}img/favicon/favicon-32x32.png?v={$favicon_version}">
    {* Apple iOS (iPhone / iPad) *}
    <link rel="apple-touch-icon" href="{$base_url}img/favicon/apple-touch-icon.png?v={$favicon_version}">
    {* Android / Chrome *}
    <link rel="icon" type="image/png" sizes="192x192" href="{$base_url}img/favicon/android-chrome-192x192.png?v={$favicon_version}">
{else}
    {* Évite que le navigateur cherche un /favicon.ico inexistant *}
    <link rel="icon" href="{$skin_url}/img/favicon.png">
{/if}