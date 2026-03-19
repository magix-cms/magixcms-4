{* 🟢 Ajout de p-0 pour annuler les marges internes de .navbar *}
<header id="header" class="navbar navbar-expand-lg p-0 {if isset($touch) && !$touch}at-top{/if}">
    {* 🟢 Ajout de position-relative au container *}
    <div class="container position-relative d-flex align-items-center justify-content-between h-100">

        {* LOGO *}
        <div class="site-name">
            {if isset($is_multilang) && $is_multilang && $lang_iso != ''}
                {$home_url = "{$base_url}{$lang_iso}/"}
            {else}
                {$home_url = $base_url}
            {/if}
            <a href="{$home_url}" title="{$logo.title_logo|default:$companyData.name}" class="text-decoration-none">
                {if isset($logo) && !empty($logo.img)}
                    {* Note : assurez-vous que la taille de l'image générée est bien plus grande que la hauteur du header *}
                    {include file="components/img.tpl" img=$logo.img size="medium" responsiveC=true alt=$logo.alt_logo|default:$companyData.name}
                {else}
                    <span class="h3 mb-0 text-primary fw-bold">{$companyData.name|default:'Magix CMS'}</span>
                {/if}
            </a>
        </div>

        {* LANGUE DESKTOP *}
        <div class="d-none d-lg-block">
            {include file="components/language_switcher.tpl"}
        </div>

        {* LE MENU OFFCANVAS *}
        <div class="offcanvas offcanvas-start w-75" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">

            {* HEADER DE L'OFFCANVAS (Haut) *}
            <div class="offcanvas-header bg-light border-bottom d-lg-none">
                <h5 class="offcanvas-title fw-bold" id="offcanvasMenuLabel">Navigation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
            </div>

            {* CORPS DE L'OFFCANVAS (Scrollable) *}
            <div class="offcanvas-body">
                {include file="layout/menu.tpl"}
            </div>

            {* 🟢 AJOUT : FOOTER DE L'OFFCANVAS (Bas) *}
            <div class="offcanvas-footer border-top p-3 d-lg-none bg-light">
                <button type="button" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center border-0" data-bs-dismiss="offcanvas">
                    <i class="bi bi-x-circle fs-4 me-2"></i>
                    <span class="fw-bold text-uppercase small-text">Fermer le menu</span>
                </button>
            </div>

        </div>

    </div>
</header>