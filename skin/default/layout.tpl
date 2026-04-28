<!DOCTYPE html>
<html lang="{$current_lang.iso_lang|lower|default:'fr'}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$seo_title}</title>
    <meta name="description" content="{$seo_desc}">

    {block name="head:structured_data"}{/block}
    {include file="components/lang_head.tpl"}
    {include file="components/opengraph.tpl"}

    {if isset($canonical_url) && $canonical_url}
        <link rel="canonical" href="{$canonical_url}" />
    {/if}

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="{$skin_url}/css/glightbox.min.css" media="print" onload="this.media='all'">
    <link rel="preload" href="{$skin_url}/fonts/bootstrap-icons.woff2?24e3eb84d0bcaf83d77f904c78ac1f47" as="font" type="font/woff2" crossorigin>
    <noscript>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link rel="stylesheet" href="{$skin_url}/css/glightbox.min.css">
    </noscript>

    {nocache}
        {if isset($consentedCookies.ggWebfontCookies) && $consentedCookies.ggWebfontCookies == true}
            {include file="components/google_fonts.tpl" fonts=[
            'Roboto' => '300,400,400italic,700',
            'Montserrat' => '700,900'
            ]}
        {/if}
    {/nocache}

    {$global_css = ["global"]}

    {block name="styleSheet"}{/block}
    {include file="components/css.tpl"}

    {nocache}
        {if isset($consentedCookies.analyticCookies) && $consentedCookies.analyticCookies == true}
            {include file="components/analytics.tpl"}
        {/if}
    {/nocache}

    {include file="components/favicon.tpl"}

    {event name="displayHead" nocache}
</head>
<body class="bg-body-tertiary">

{include file="layout/header.tpl"}

{block name="main:before"}{/block}
{block name="main"}
    <main class="flex-grow-1">
        <div class="container py-2">
            {block name='article'}
                <article>
                    {block name='article:content'}{/block}
                </article>
            {/block}
        </div>
    </main>
{/block}
{block name="main:after"}{/block}

{include file="layout/footer.tpl"}
{include file="layout/footbar.tpl"}

{include file="components/cookies.tpl" nocache}

{$global_js = [
'defer' => ['vendor/bootstrap.bundle','vendor/glightbox', 'vendor/imagesloaded.pkgd', 'CookieConsent', 'MagixCore'],
'async' => [],
'normal' => []
]}

{block name="javascript_primary"}{/block}

{block name="javascript_data"}{/block}

{include file="components/js.tpl"}

{block name="javascript"}{/block}

{nocache}
    {if isset($admin_maintenance_warning) && $admin_maintenance_warning}
        <div class="position-fixed start-0 bottom-0 p-3" style="z-index: 1090; margin-bottom: 80px;">
            <div class="alert alert-warning alert-dismissible fade show shadow-lg mb-0 py-2 pe-5" role="alert" style="border-radius: 50rem;">
                <i class="bi bi-exclamation-triangle-fill text-danger me-2 align-middle"></i>
                <span class="fw-bold small align-middle">Mode Maintenance</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer" style="top: 50%; transform: translateY(-50%); padding: 0.75rem; right: 0.5rem;"></button>
            </div>
        </div>
    {/if}
{/nocache}

<div id="magix-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>
</body>
</html>