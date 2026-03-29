<div id="footbar" class="fixed-bottom z-3 at-top">
    <div class="d-flex justify-content-between align-items-center w-100 footbar-container">

        {* Bouton Menu Mobile *}
        <button class="btn d-flex flex-column align-items-center border-0 bg-transparent d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
            <i class="bi bi-list mb-1 fs-4"></i>
            <span class="fw-bold text-uppercase small-text">{#footbar_menu_label#}</span>
        </button>

        {* Sélecteur de Langue (Mobile uniquement) *}
        {if isset($langs) && $langs|count > 1}
            <div class="dropup d-lg-none">
                <button class="btn d-flex flex-column align-items-center border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-translate mb-1 fs-4"></i>
                    <span class="fw-bold text-uppercase small-text">{$current_lang.iso_lang|default:'FR'}</span>
                </button>
                <ul class="dropdown-menu shadow border-0 mb-2">
                    {foreach $langs as $l}
                        <li><a class="dropdown-item {if $l.id_lang == $current_lang.id_lang}active{/if}" href="{$base_url}{$l.iso_lang}/">{$l.iso_lang|upper}</a></li>
                    {/foreach}
                </ul>
            </div>
        {/if}

        {* Menu de Partage *}
        <div class="dropup action-share">
            <button class="btn d-flex flex-column align-items-center border-0 floating-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{#footbar_share_label#}">
                <i class="bi bi-share mb-1 fs-4"></i>
                <span class="fw-bold text-uppercase small-text d-lg-none">{#footbar_share_label#}</span>
            </button>
            <ul class="dropdown-menu shadow border-0 mb-2">
                {$current_share_url = $site_url|cat:$smarty.server.REQUEST_URI|escape:'url'}
                {$share_title = $seo_title|default:$companyData.name|escape:'url'}

                {if isset($shareNetworks) && is_array($shareNetworks) && $shareNetworks|count > 0}
                    {foreach $shareNetworks as $network}
                        {$final_url = $network.url_share|replace:'%URL%':$current_share_url|replace:'%NAME%':$share_title}
                        <li>
                            <a class="dropdown-item" href="{$final_url}" target="_blank" rel="noopener noreferrer">
                                <i class="bi {$network.icon} me-2 text-primary"></i> {$network.name|capitalize}
                            </a>
                        </li>
                    {/foreach}
                {else}
                    <li><span class="dropdown-item text-muted small">{#footbar_share_empty#}</span></li>
                {/if}
            </ul>
        </div>

        {* Bouton Retour en haut *}
        <a href="#top" class="btn d-flex flex-column align-items-center border-0 floating-btn action-totop" title="{#footbar_totop_title#}">
            <i class="bi bi-chevron-up mb-1 fs-4"></i>
            <span class="fw-bold text-uppercase small-text d-lg-none">{#footbar_totop_label#}</span>
        </a>

    </div>
</div>