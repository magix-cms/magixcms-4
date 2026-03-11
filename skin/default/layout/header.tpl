<header id="header" class="navbar navbar-expand-lg {if isset($touch) && !$touch}at-top{/if}">
    <div class="container d-flex align-items-center justify-content-between h-100">

        {* LOGO (identique à avant) *}
        <div class="site-name">
            {if isset($is_multilang) && $is_multilang && $lang_iso != ''}
                {$home_url = "{$base_url}{$lang_iso}/"}
            {else}
                {$home_url = $base_url}
            {/if}
            <a href="{$home_url}" title="{$logo.title_logo|default:$companyData.name}" class="d-block text-decoration-none">
                {if isset($logo) && !empty($logo.img)}
                    {include file="components/img.tpl" img=$logo.img size="small" responsiveC=true alt=$logo.alt_logo|default:$companyData.name}
                {else}
                    <span class="h3 mb-0 text-primary fw-bold">{$companyData.name|default:'Magix CMS'}</span>
                {/if}
            </a>
        </div>

        {* LANGUE DESKTOP *}
        <div class="d-none d-lg-block">
            {include file="components/language_switcher.tpl"}
        </div>

        {* LE MENU (OFFCANVAS SUR MOBILE, NORMAL SUR BUREAU) *}
        <div class="offcanvas offcanvas-start w-75" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">

            {* Entête du tiroir (Visible uniquement sur mobile) *}
            <div class="offcanvas-header bg-light border-bottom d-lg-none">
                <h5 class="offcanvas-title fw-bold" id="offcanvasMenuLabel">Navigation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
            </div>

            {* Corps du menu *}
            <div class="offcanvas-body">
                {* On charge le menu récursif que nous avons créé tout à l'heure *}
                {include file="layout/menu.tpl"}
            </div>

        </div>

    </div>
</header>